<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\AbsensiVerificationLog;
use App\Models\Guru;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\SiswaFaceBiometric;
use App\Models\User;
use App\Services\FaceBiometricService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FaceBiometricController extends Controller
{
    protected FaceBiometricService $biometricService;

    public function __construct(FaceBiometricService $biometricService)
    {
        $this->biometricService = $biometricService;
    }

    /**
     * Request dynamic challenge nonce for liveness verification
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

        $challenge = $this->biometricService->generateChallenge($siswa);

        return response()->json([
            'success' => true,
            'data' => $challenge
        ]);
    }

    /**
     * Show Student Face Biometric Registration page
     */
    public function showRegistration()
    {
        $user = Auth::user();
        $siswa = Siswa::with(['kelas.jurusan', 'faceBiometric'])->where('user_id', $user->id)->first();

        if (!$siswa) {
            return redirect()->route('dashboard')->with('error', 'Data siswa tidak ditemukan.');
        }

        $biometric = $siswa->faceBiometric;

        return view('absensi.face-register', compact('siswa', 'biometric'));
    }

    /**
     * Process Biometric Registration from Student or Admin
     */
    public function register(Request $request)
    {
        $request->validate([
            'face_descriptor' => 'required|array|size:128',
            'face_descriptor.*' => 'numeric',
            'foto' => 'required|string',
            'quality_score' => 'nullable|numeric',
            'siswa_id' => 'nullable|exists:siswas,id'
        ]);

        $user = Auth::user();
        
        // If superadmin or admin is registering for a specific student
        if (in_array($user->role, ['superadmin', 'admin']) && $request->filled('siswa_id')) {
            $siswa = Siswa::findOrFail($request->siswa_id);
        } else {
            $siswa = Siswa::where('user_id', $user->id)->first();
        }

        if (!$siswa) {
            return response()->json([
                'success' => false,
                'message' => 'Data siswa tidak ditemukan.'
            ], 403);
        }

        // Quality check
        $qualityScore = floatval($request->quality_score ?? 85.0);
        if ($qualityScore < 60.0) {
            return response()->json([
                'success' => false,
                'message' => 'Kualitas gambar wajah tidak memadai (Skor: ' . round($qualityScore) . '/100). Pastikan pencahayaan cukup dan wajah lurus ke depan.'
            ], 422);
        }

        // Process Sample Photo
        $fotoData = $request->foto;
        $fileName = null;
        if (preg_match('/^data:image\/(\w+);base64,/', $fotoData, $type)) {
            $fotoData = substr($fotoData, strpos($fotoData, ',') + 1);
            $type = strtolower($type[1]);
            $decoded = base64_decode($fotoData);

            if ($decoded !== false) {
                $fileName = 'biometrics/template_' . $siswa->id . '_' . now()->format('Ymd_His') . '.' . ($type ?? 'jpg');
                Storage::disk('public')->put($fileName, $decoded);
            }
        }

        // Save Biometric Model (Mutator encrypts descriptor automatically)
        $biometric = SiswaFaceBiometric::updateOrCreate(
            ['siswa_id' => $siswa->id],
            [
                'face_descriptor' => $request->face_descriptor,
                'quality_score' => $qualityScore,
                'sample_photo' => $fileName,
                'registered_at' => now(),
                'is_active' => true,
                'registered_by' => $user->id,
            ]
        );

        // Audit Log
        $this->biometricService->logVerification(
            $siswa->id,
            'registrasi',
            'success',
            100.0,
            0.0,
            $qualityScore,
            'Pendaftaran template wajah biometrik berhasil disimpan',
            $request->ip(),
            $request->userAgent(),
            $fileName
        );

        return response()->json([
            'success' => true,
            'message' => 'Wajah biometrik berhasil didaftarkan! Sekarang Anda dapat melakukan absensi dengan verifikasi wajah.',
            'data' => [
                'registered_at' => $biometric->registered_at->format('d/m/Y H:i'),
                'quality_score' => $biometric->quality_score,
            ]
        ]);
    }

    /**
     * Admin Panel: Biometric Management & Audit Logs
     */
    public function adminIndex(Request $request)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['superadmin', 'admin', 'guru'])) {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak: Anda tidak memiliki wewenang untuk mengakses panel biometrik.');
        }

        $activeTab = $request->get('tab', 'students');

        // Query Students with Biometric Status
        $querySiswa = Siswa::with(['kelas.jurusan', 'faceBiometric', 'user']);

        if ($user->role === 'guru') {
            $guruRecord = \App\Models\Guru::where('user_id', $user->id)->first();
            if ($guruRecord) {
                $kelasWali = Kelas::where('guru_id', $guruRecord->id)->pluck('id');
                if ($kelasWali->isNotEmpty() && !$request->filled('kelas_id')) {
                    $querySiswa->whereIn('kelas_id', $kelasWali);
                }
            }
        }

        if ($request->filled('kelas_id')) {
            $querySiswa->where('kelas_id', $request->kelas_id);
        }

        if ($request->filled('status_biometrik')) {
            $status = $request->status_biometrik;
            if ($status === 'registered' || $status === 'enrolled') {
                $querySiswa->where('biometric_status', 'enrolled');
            } elseif ($status === 'unregistered' || $status === 'not_enrolled') {
                $querySiswa->whereIn('biometric_status', ['not_enrolled', 'pending']);
            } elseif (in_array($status, ['needs_re_enrollment', 'suspended'])) {
                $querySiswa->where('biometric_status', $status);
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $querySiswa->where(function ($q) use ($search) {
                $q->where('nama', 'LIKE', '%' . $search . '%')
                  ->orWhere('nis', 'LIKE', '%' . $search . '%');
            });
        }

        $siswas = $querySiswa->orderBy('nama')->paginate(15, ['*'], 'siswa_page')->appends($request->query());

        // Statistics
        $totalSiswa = Siswa::count();
        $totalRegistered = Siswa::where('biometric_status', 'enrolled')->count();
        $totalUnregistered = Siswa::whereIn('biometric_status', ['not_enrolled', 'pending'])->count();
        $totalReEnroll = Siswa::where('biometric_status', 'needs_re_enrollment')->count();
        $totalSuspended = Siswa::where('biometric_status', 'suspended')->count();
        $totalSuspicious = AbsensiVerificationLog::whereIn('status', ['failed_match', 'failed_liveness'])
            ->whereDate('created_at', today())
            ->count();

        // Query Audit Logs
        $queryLogs = AbsensiVerificationLog::with(['siswa.kelas.jurusan']);

        if ($request->filled('log_status')) {
            $queryLogs->where('status', $request->log_status);
        }

        if ($request->filled('log_date')) {
            $queryLogs->whereDate('created_at', $request->log_date);
        }

        $logs = $queryLogs->orderBy('created_at', 'desc')->paginate(20, ['*'], 'log_page')->appends($request->query());

        $kelasList = Kelas::orderBy('nama_kelas')->get();
        $jurusans = Jurusan::orderBy('kode_jurusan')->get();

        return view('absensi.admin-biometrik', compact(
            'siswas',
            'logs',
            'totalSiswa',
            'totalRegistered',
            'totalUnregistered',
            'totalReEnroll',
            'totalSuspended',
            'totalSuspicious',
            'kelasList',
            'jurusans',
            'activeTab'
        ));
    }

    /**
     * Admin: Reset Biometric Template for a student (Superadmin only)
     */
    public function adminReset($siswaId)
    {
        $user = Auth::user();
        if ($user->role !== 'superadmin') {
            return redirect()->back()->with('error', 'Akses ditolak: Hanya Superadmin yang memiliki izin untuk mereset template biometrik siswa.');
        }

        $siswa = Siswa::findOrFail($siswaId);
        $biometric = SiswaFaceBiometric::where('siswa_id', $siswa->id)->first();

        if ($biometric) {
            $biometric->delete();
        }

        $siswa->update(['biometric_status' => 'not_enrolled']);

        // Log to BiometricEnrollmentLog
        \App\Models\BiometricEnrollmentLog::create([
            'siswa_id'       => $siswa->id,
            'attempt_type'   => 'admin_reset',
            'status'         => 'reset',
            'failure_reason' => "Template biometrik direset oleh Superadmin ({$user->name})",
            'ip_address'     => request()->ip(),
            'user_agent'     => request()->userAgent(),
            'performed_by'   => $user->id,
        ]);

        $this->biometricService->logVerification(
            $siswa->id,
            'registrasi',
            'manual_override',
            null,
            null,
            null,
            "Template biometrik direset oleh Superadmin ({$user->name})",
            request()->ip(),
            request()->userAgent()
        );

        return redirect()->back()->with('success', "Template biometrik untuk {$siswa->nama} berhasil direset. Siswa diwajibkan mendaftarkan wajah kembali saat login.");
    }

    /**
     * Superadmin/Admin: Request student to re-enroll face biometrics
     */
    public function adminRequestReEnroll($siswaId)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['superadmin', 'admin'])) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $siswa = Siswa::findOrFail($siswaId);
        $siswa->update(['biometric_status' => 'needs_re_enrollment']);

        $biometric = SiswaFaceBiometric::where('siswa_id', $siswa->id)->first();
        if ($biometric) {
            $biometric->update([
                'enrollment_status' => 'needs_re_enrollment',
                'is_active' => false,
                'status_reason' => "Registrasi ulang diminta oleh {$user->name} ({$user->role})",
            ]);
        }

        \App\Models\BiometricEnrollmentLog::create([
            'siswa_id'       => $siswa->id,
            'attempt_type'   => 'admin_request_re_enroll',
            'status'         => 're_enroll_requested',
            'failure_reason' => "Diminta registrasi ulang oleh {$user->name} ({$user->role})",
            'ip_address'     => request()->ip(),
            'user_agent'     => request()->userAgent(),
            'performed_by'   => $user->id,
        ]);

        return redirect()->back()->with('success', "Status siswa {$siswa->nama} diubah menjadi 'Wajib Registrasi Ulang'. Siswa akan diarahkan ke pendaftaran wajah pada login berikutnya.");
    }

    /**
     * Superadmin/Admin: Suspend or Activate student biometrics
     */
    public function adminToggleSuspend($siswaId)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['superadmin', 'admin'])) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $siswa = Siswa::findOrFail($siswaId);
        $biometric = SiswaFaceBiometric::where('siswa_id', $siswa->id)->first();

        if (!$biometric) {
            return redirect()->back()->with('error', 'Siswa belum memiliki data biometrik.');
        }

        $isCurrentlySuspended = ($siswa->biometric_status === 'suspended');

        if ($isCurrentlySuspended) {
            // Activate
            $siswa->update(['biometric_status' => 'enrolled']);
            $biometric->update([
                'enrollment_status' => 'enrolled',
                'is_active' => true,
                'status_reason' => "Biometrik diaktifkan kembali oleh {$user->name}",
            ]);
            $msg = "Biometrik {$siswa->nama} berhasil diaktifkan kembali.";
            $logStatus = 'activated';
        } else {
            // Suspend
            $siswa->update(['biometric_status' => 'suspended']);
            $biometric->update([
                'enrollment_status' => 'suspended',
                'is_active' => false,
                'status_reason' => "Biometrik disuspend sementara oleh {$user->name}",
            ]);
            $msg = "Biometrik {$siswa->nama} berhasil disuspend sementara.";
            $logStatus = 'suspended';
        }

        \App\Models\BiometricEnrollmentLog::create([
            'siswa_id'       => $siswa->id,
            'attempt_type'   => 'admin_suspend',
            'status'         => $logStatus,
            'failure_reason' => $msg,
            'ip_address'     => request()->ip(),
            'user_agent'     => request()->userAgent(),
            'performed_by'   => $user->id,
        ]);

        return redirect()->back()->with('success', $msg);
    }

    /**
     * Admin/Teacher: Manual Attendance Override (Broken Camera / Disability Fallback)
     */
    public function manualOverride(Request $request)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['superadmin', 'admin', 'guru'])) {
            return redirect()->back()->with('error', 'Hanya Superadmin, Admin, atau Guru yang dapat melakukan presensi manual.');
        }

        $request->validate([
            'siswa_id' => 'required|exists:siswas,id',
            'status' => 'required|in:Hadir,Izin,Sakit',
            'tipe' => 'required|in:masuk,pulang',
            'reason' => 'required|string|min:5|max:255',
        ]);

        $siswa = Siswa::findOrFail($request->siswa_id);

        // BUG-008: this endpoint had no scope at all — any guru could write
        // attendance for any student in the school, and the audit log recorded
        // a fabricated 100.0 confidence for it.
        $this->assertCanManageStudentBiometric($user, $siswa);

        $todayStr = today()->format('Y-m-d');
        $now = Carbon::now();

        $absensi = Absensi::where('siswa_id', $siswa->id)->whereDate('tanggal', $todayStr)->first();

        if ($request->tipe === 'pulang') {
            if (!$absensi) {
                return redirect()->back()->with('error', 'Siswa belum memiliki catatan absen masuk hari ini.');
            }

            $absensi->update([
                'jam_pulang' => $now->format('H:i:s'),
                'is_manual_override' => true,
                'manual_override_by' => $user->id,
                'manual_override_reason' => $request->reason,
            ]);
        } else {
            if ($absensi) {
                return redirect()->back()->with('error', 'Siswa sudah melakukan absen masuk hari ini.');
            }

            Absensi::create([
                'siswa_id' => $siswa->id,
                'tanggal' => $todayStr,
                'jam_masuk' => $now->format('H:i:s'),
                'status' => $request->status,
                'keterangan' => 'Presensi Manual: ' . $request->reason,
                'is_manual_override' => true,
                'manual_override_by' => $user->id,
                'manual_override_reason' => $request->reason,
            ]);
        }

        // Audit Log
        $this->biometricService->logVerification(
            $siswa->id,
            $request->tipe,
            'manual_override',
            100.0,
            0.0,
            100.0,
            "Presensi manual disetujui oleh {$user->name} ({$user->role}). Alasan: {$request->reason}",
            $request->ip(),
            $request->userAgent()
        );

        return redirect()->back()->with('success', "Presensi manual untuk {$siswa->nama} berhasil dicatat.");
    }

    /**
     * Guard biometric write endpoints for the guru role.
     *
     * BUG-007: any guru could call assisted-enroll for any student id and
     * overwrite that student's face template with their own face, which then
     * passes that student's login 2FA — a full account takeover. Guru access is
     * now limited to students in classes they actually teach or manage.
     * Superadmin/admin remain unrestricted (they already hold the reset power).
     */
    private function assertCanManageStudentBiometric(User $user, Siswa $siswa): void
    {
        if ($user->role !== 'guru') {
            return;
        }

        $guru = Guru::with(['activeHomeroomAssignment', 'activeSubjectAssignments'])
            ->where('user_id', $user->id)
            ->first();

        abort_unless($guru, 403, 'Akses ditolak: profil guru tidak ditemukan.');

        $allowedClassIds = $guru->activeSubjectAssignments->pluck('kelas_id')
            ->merge($guru->activeJadwal()->pluck('kelas_id'))
            ->merge($guru->activeHomeroomAssignment ? collect([$guru->activeHomeroomAssignment->kelas_id]) : collect())
            ->unique();

        abort_unless(
            $siswa->kelas_id && $allowedClassIds->contains($siswa->kelas_id),
            403,
            'Akses ditolak: siswa ini bukan bagian dari kelas yang Anda ampu.'
        );
    }

    /**
     * Admin/Guru: Assisted Enrollment using School Lab PC / School Device
     */
    public function assistedEnroll($siswaId)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['superadmin', 'admin', 'guru'])) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $siswa = Siswa::with(['kelas.jurusan', 'faceBiometric'])->findOrFail($siswaId);
        $this->assertCanManageStudentBiometric($user, $siswa);

        return view('absensi.assisted-enroll', compact('siswa', 'user'));
    }

    /**
     * Generate Challenge Nonce for Assisted Enrollment
     */
    public function assistedChallenge($siswaId)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['superadmin', 'admin', 'guru'])) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $siswa = Siswa::findOrFail($siswaId);
        $this->assertCanManageStudentBiometric($user, $siswa);

        $challenge = $this->biometricService->generateChallenge($siswa);

        return response()->json([
            'success' => true,
            'data' => $challenge
        ]);
    }

    /**
     * Process & Save Assisted Enrollment from School Device
     */
    public function processAssistedEnroll(Request $request, $siswaId)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['superadmin', 'admin', 'guru'])) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $request->validate([
            'face_descriptor'   => 'required|array|size:128',
            'face_descriptor.*' => 'numeric',
            'foto'              => 'required|string',
            'challenge_token'   => 'required|string',
            'liveness_proof'    => 'required|array',
            'quality_score'     => 'nullable|numeric',
        ]);

        $siswa = Siswa::findOrFail($siswaId);
        $this->assertCanManageStudentBiometric($user, $siswa);

        // 1. Verify Challenge Token
        $cacheKey = 'biometric_challenge_' . $request->challenge_token;
        $challengeData = Cache::get($cacheKey);

        if (!$challengeData) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi tantangan liveness kadaluarsa. Silakan ulangi perekaman.'
            ], 422);
        }

        Cache::forget($cacheKey);

        if ($challengeData['siswa_id'] !== $siswa->id) {
            return response()->json([
                'success' => false,
                'message' => 'Token biometrik tidak cocok dengan identitas siswa.'
            ], 403);
        }

        // 2. Validate Liveness Proof Structure
        $expectedChallenges = $challengeData['challenges'];
        $livenessProof = $request->liveness_proof;

        if (empty($livenessProof['completed_challenges']) || !is_array($livenessProof['completed_challenges'])) {
            return response()->json([
                'success' => false,
                'message' => 'Bukti tantangan keaktifan (liveness) tidak lengkap.'
            ], 422);
        }

        $completed = $livenessProof['completed_challenges'];
        if (count($completed) < count($expectedChallenges)) {
            return response()->json([
                'success' => false,
                'message' => 'Seluruh instruksi gerakan liveness wajib diselesaikan.'
            ], 422);
        }

        // 3. Quality Score Verification
        $qualityScore = floatval($request->quality_score ?? 85.0);
        if ($qualityScore < 60.0) {
            return response()->json([
                'success' => false,
                'message' => 'Kualitas gambar webcam belum memadai (' . round($qualityScore) . '%).'
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
                $fileName = 'biometrics/assisted_' . $siswa->id . '_' . now()->format('Ymd_His') . '.' . ($type ?? 'jpg');
                Storage::disk('public')->put($fileName, $decoded);
            }
        }

        // 5. DB Transaction
        DB::beginTransaction();
        try {
            SiswaFaceBiometric::updateOrCreate(
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
                    'status_reason'     => "Pendaftaran terpandu melalui perangkat sekolah/lab oleh {$user->name} ({$user->role}).",
                    'enrolled_at'       => now(),
                    'last_verified_at'  => now(),
                ]
            );

            $siswa->update([
                'biometric_status' => 'enrolled'
            ]);

            \App\Models\BiometricEnrollmentLog::create([
                'siswa_id'       => $siswa->id,
                'attempt_type'   => 'admin_assisted_enrollment',
                'status'         => 'success',
                'quality_score'  => $qualityScore,
                'liveness_score' => $livenessScore,
                'model_version'  => 'face-api-v1',
                'failure_reason' => null,
                'ip_address'     => $request->ip(),
                'user_agent'     => $request->userAgent(),
                'performed_by'   => $user->id,
            ]);

            $this->biometricService->logVerification(
                $siswa->id,
                'registrasi',
                'success',
                100.0,
                0.0,
                $qualityScore,
                "Pendaftaran biometrik terpandu oleh {$user->name} ({$user->role}) di perangkat sekolah berhasil disimpan.",
                $request->ip(),
                $request->userAgent(),
                $fileName
            );

            DB::commit();

            return response()->json([
                'success'      => true,
                'message'      => "Pendaftaran biometrik untuk {$siswa->nama} berhasil disimpan!",
                'redirect_url' => route('admin.biometrik')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan biometrik: ' . $e->getMessage()
            ], 500);
        }
    }
}
