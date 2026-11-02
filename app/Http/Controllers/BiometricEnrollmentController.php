<?php

namespace App\Http\Controllers;

use App\Models\BiometricEnrollmentLog;
use App\Models\Siswa;
use App\Models\SiswaFaceBiometric;
use App\Services\FaceBiometricService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BiometricEnrollmentController extends Controller
{
    protected FaceBiometricService $biometricService;

    public function __construct(FaceBiometricService $biometricService)
    {
        $this->biometricService = $biometricService;
    }

    /**
     * Show First Login Biometric Face Enrollment wizard page
     */
    public function show()
    {
        $user = Auth::user();
        if ($user->role !== 'siswa') {
            return redirect()->route('dashboard');
        }

        $siswa = Siswa::with(['kelas.jurusan', 'faceBiometric'])->where('user_id', $user->id)->first();
        if (!$siswa) {
            return redirect()->route('login')->with('error', 'Data profil siswa tidak ditemukan.');
        }

        // If already enrolled, send directly to dashboard
        if ($siswa->isBiometricEnrolled()) {
            return redirect()->route('dashboard')->with('info', 'Anda sudah menyelesaikan pendaftaran biometrik.');
        }

        // Set status to pending if it was not_enrolled
        if ($siswa->biometric_status === 'not_enrolled') {
            $siswa->update(['biometric_status' => 'pending']);
        }

        return view('siswa.biometric-enrollment', compact('siswa'));
    }

    /**
     * Request Dynamic Challenge Nonce for Liveness Verification during Enrollment
     */
    public function challenge(Request $request)
    {
        $user = Auth::user();
        $siswa = Siswa::where('user_id', $user->id)->first();

        if (!$siswa) {
            return response()->json([
                'success' => false,
                'message' => 'Identitas siswa tidak ditemukan.'
            ], 403);
        }

        $challenge = $this->biometricService->generateEnrollmentPoseChallenge($siswa);

        return response()->json([
            'success' => true,
            'data' => $challenge,
            'challenge_token' => $challenge['token'],
            'challenges' => $challenge['challenges'],
        ]);
    }

    /**
     * Process & Securely Save Student's Enrolled Biometric Template
     */
    public function process(Request $request)
    {
        // Support both foto and image keys
        if (!$request->has('foto') && $request->has('image')) {
            $request->merge(['foto' => $request->image]);
        }

        // Support descriptors array if single face_descriptor is nested
        if (!$request->has('face_descriptor') && $request->has('descriptors')) {
            $descList = $request->descriptors;
            if (is_array($descList) && !empty($descList)) {
                // Front-facing descriptor is the primary descriptor
                $request->merge(['face_descriptor' => is_array($descList[0]) ? $descList[0] : $descList]);
            }
        }

        $request->validate([
            'face_descriptor'   => 'required|array|size:128',
            'face_descriptor.*' => 'numeric',
            'foto'              => 'required|string',
            'challenge_token'   => 'required|string',
            'liveness_proof'    => 'required|array',
            'quality_score'     => 'nullable|numeric',
        ]);

        $user = Auth::user();
        $siswa = Siswa::where('user_id', $user->id)->first();

        if (!$siswa) {
            return response()->json([
                'success' => false,
                'message' => 'Identitas akun siswa tidak valid.'
            ], 403);
        }

        // 1. Verify Challenge Token (Anti-Replay & Liveness Session)
        $cacheKey = 'biometric_challenge_' . $request->challenge_token;
        $challengeData = Cache::get($cacheKey);

        if (!$challengeData) {
            $this->logEnrollmentAttempt($siswa->id, 'failed_liveness', 'Sesi tantangan liveness kadaluarsa (Anti-Replay).', null, null);

            return response()->json([
                'success' => false,
                'message' => 'Sesi tantangan kadaluarsa. Silakan ulangi perekaman wajah aktif.'
            ], 422);
        }

        // Consume token immediately (Anti-replay single-use)
        Cache::forget($cacheKey);

        if ($challengeData['siswa_id'] !== $siswa->id) {
            $this->logEnrollmentAttempt($siswa->id, 'failed_liveness', 'Identitas siswa tidak cocok dengan token.', null, null);

            return response()->json([
                'success' => false,
                'message' => 'Token biometrik tidak cocok dengan identitas Anda.'
            ], 403);
        }

        // 2. Validate Liveness Proof Structure & Human Timing
        $expectedChallenges = $challengeData['challenges'];
        $livenessProof = $request->liveness_proof;

        if (empty($livenessProof['completed_challenges']) || !is_array($livenessProof['completed_challenges'])) {
            $this->logEnrollmentAttempt($siswa->id, 'failed_liveness', 'Bukti liveness tidak valid.', null, null);

            return response()->json([
                'success' => false,
                'message' => 'Bukti tantangan keaktifan (liveness) tidak lengkap.'
            ], 422);
        }

        $completed = $livenessProof['completed_challenges'];
        if (count($completed) < count($expectedChallenges)) {
            $this->logEnrollmentAttempt($siswa->id, 'failed_liveness', 'Tantangan liveness tidak lengkap.', null, null);

            return response()->json([
                'success' => false,
                'message' => 'Anda harus menyelesaikan seluruh instruksi gerakan liveness.'
            ], 422);
        }

        for ($i = 0; $i < count($expectedChallenges); $i++) {
            $expectedType = $expectedChallenges[$i]['type'];
            if (!isset($completed[$i]['type']) || $completed[$i]['type'] !== $expectedType) {
                $this->logEnrollmentAttempt($siswa->id, 'failed_liveness', 'Urutan tantangan tidak sesuai.', null, null);

                return response()->json([
                    'success' => false,
                    'message' => 'Urutan gerakan liveness tidak sesuai dengan instruksi sistem.'
                ], 422);
            }

            $durationMs = $completed[$i]['duration_ms'] ?? 0;
            if ($durationMs < 100) {
                $this->logEnrollmentAttempt($siswa->id, 'failed_liveness', 'Durasi respon tidak wajar (indikasi bot/manipulasi).', null, null);

                return response()->json([
                    'success' => false,
                    'message' => 'Durasi respon tidak wajar. Pastikan gerakan dilakukan secara alami di depan kamera.'
                ], 422);
            }
        }

        // 3. Quality Score Verification
        $qualityScore = floatval($request->quality_score ?? 85.0);
        if ($qualityScore < 60.0) {
            $this->logEnrollmentAttempt($siswa->id, 'failed_quality', "Kualitas foto tidak memadai ({$qualityScore}%).", $qualityScore, null);

            return response()->json([
                'success' => false,
                'message' => 'Kualitas gambar wajah belum optimal (' . round($qualityScore) . '%). Pastikan pencahayaan cukup dan wajah lurus ke depan.'
            ], 422);
        }

        $livenessScore = floatval($livenessProof['liveness_score'] ?? 95.0);

        // 4. Save Sample Photo
        $fotoData = $request->foto;
        $fileName = null;
        if (preg_match('/^data:image\/(\w+);base64,/', $fotoData, $type)) {
            $fotoData = substr($fotoData, strpos($fotoData, ',') + 1);
            $type = strtolower($type[1]);
            $decoded = base64_decode($fotoData);

            if ($decoded !== false) {
                $fileName = 'biometrics/enrolled_' . $siswa->id . '_' . now()->format('Ymd_His') . '.' . ($type ?? 'jpg');
                Storage::disk('public')->put($fileName, $decoded);
            }
        }

        // 5. Atomic Database Transaction: Save Encrypted Template + Update Student Status
        DB::beginTransaction();
        try {
            // Save encrypted biometric template
            $biometric = SiswaFaceBiometric::updateOrCreate(
                ['siswa_id' => $siswa->id],
                [
                    'face_descriptor'   => $request->face_descriptor,
                    'quality_score'     => $qualityScore,
                    'sample_photo'      => $fileName,
                    'registered_at'     => now(),
                    'is_active'         => true,
                    'registered_by'     => $user->id,
                    'enrollment_status' => 'enrolled',
                    'model_version'     => 'face-api-v1',
                    'status_reason'     => 'Pendaftaran biometrik login pertama berhasil diselesaikan siswa.',
                    'enrolled_at'       => now(),
                    'last_verified_at'  => now(),
                ]
            );

            // Update student status to enrolled
            $siswa->update([
                'biometric_status' => 'enrolled'
            ]);

            // Create Enrollment Log
            BiometricEnrollmentLog::create([
                'siswa_id'       => $siswa->id,
                'attempt_type'   => 'first_login_enrollment',
                'status'         => 'success',
                'quality_score'  => $qualityScore,
                'liveness_score' => $livenessScore,
                'model_version'  => 'face-api-v1',
                'failure_reason' => null,
                'ip_address'     => $request->ip(),
                'user_agent'     => $request->userAgent(),
                'performed_by'   => $user->id,
            ]);

            // Also create Unified Audit Log
            $this->biometricService->logVerification(
                $siswa->id,
                'registrasi',
                'success',
                100.0,
                0.0,
                $qualityScore,
                'Pendaftaran biometrik login pertama (First Login Enrollment) berhasil disimpan.',
                $request->ip(),
                $request->userAgent(),
                $fileName
            );

            DB::commit();

            return response()->json([
                'success'      => true,
                'message'      => 'Registrasi Wajah Berhasil! Data biometrik Anda telah terdaftar secara aman.',
                'redirect_url' => route('dashboard')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logEnrollmentAttempt($siswa->id, 'failed_validation', 'Kesalahan penyimpanan database: ' . $e->getMessage(), $qualityScore, $livenessScore);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan internal saat menyimpan template biometrik. Silakan coba kembali.'
            ], 500);
        }
    }

    /**
     * Helper to log enrollment attempts
     */
    protected function logEnrollmentAttempt(
        int $siswaId,
        string $status,
        string $reason,
        ?float $qualityScore = null,
        ?float $livenessScore = null
    ): void {
        try {
            BiometricEnrollmentLog::create([
                'siswa_id'       => $siswaId,
                'attempt_type'   => 'first_login_enrollment',
                'status'         => $status,
                'quality_score'  => $qualityScore,
                'liveness_score' => $livenessScore,
                'model_version'  => 'face-api-v1',
                'failure_reason' => $reason,
                'ip_address'     => request()->ip(),
                'user_agent'     => request()->userAgent(),
                'performed_by'   => Auth::id(),
            ]);
        } catch (\Exception $e) {
            // Ignore logging error to not block user flow
        }
    }
}
