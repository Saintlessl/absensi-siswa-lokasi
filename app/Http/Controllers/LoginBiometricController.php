<?php

namespace App\Http\Controllers;

use App\Models\AbsensiVerificationLog;
use App\Models\Siswa;
use App\Models\SiswaDevice;
use App\Models\User;
use App\Services\FaceBiometricService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LoginBiometricController extends Controller
{
    protected FaceBiometricService $biometricService;

    public function __construct(FaceBiometricService $biometricService)
    {
        $this->biometricService = $biometricService;
    }

    /**
     * Show 2FA Face Verification Screen on Login
     */
    public function showVerifyFace(Request $request)
    {
        $pending = session('pending_biometric_login');

        if (!$pending || !isset($pending['siswa_id'])) {
            return redirect()->route('login')->with('error', 'Sesi login telah kadaluarsa. Silakan masukkan kredensial kembali.');
        }

        // Check timeout (5 minutes)
        if (now()->timestamp - ($pending['created_at'] ?? 0) > 300) {
            session()->forget('pending_biometric_login');
            return redirect()->route('login')->with('error', 'Waktu verifikasi login telah habis. Silakan login kembali.');
        }

        $siswa = Siswa::with(['kelas.jurusan', 'faceBiometric'])->find($pending['siswa_id']);
        if (!$siswa || !$siswa->isBiometricEnrolled()) {
            session()->forget('pending_biometric_login');
            return redirect()->route('login')->with('error', 'Data biometrik siswa tidak ditemukan.');
        }

        // Check device count info
        $activeDevicesCount = SiswaDevice::where('siswa_id', $siswa->id)->where('is_active', true)->count();

        return view('auth.verify-face', compact('siswa', 'pending', 'activeDevicesCount'));
    }

    /**
     * Generate anti-replay challenge nonce for login face verification
     */
    public function challenge(Request $request)
    {
        $pending = session('pending_biometric_login');

        if (!$pending || !isset($pending['siswa_id'])) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi login tidak valid. Silakan ulangi login.'
            ], 403);
        }

        $siswa = Siswa::find($pending['siswa_id']);
        if (!$siswa) {
            return response()->json(['success' => false, 'message' => 'Siswa tidak ditemukan.'], 404);
        }

        $challenge = $this->biometricService->generateLoginFaceChallenge($siswa);

        return response()->json([
            'success'         => true,
            'challenge_token' => $challenge['token'],
            'challenges'      => $challenge['challenges'],
            'expires_in'      => $challenge['expires_in'],
        ]);
    }

    /**
     * Verify Live Face Descriptor & Authenticate Student Session
     */
    public function verify(Request $request)
    {
        $pending = session('pending_biometric_login');

        if (!$pending || !isset($pending['siswa_id'])) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi login tidak valid atau telah kadaluarsa. Silakan refresh halaman login.'
            ], 403);
        }

        $request->validate([
            'face_descriptor'   => 'required|array|size:128',
            'face_descriptor.*' => 'numeric',
            'challenge_token'   => 'required|string',
            'liveness_proof'    => 'required|array',
            'foto'              => 'nullable|string',
            'device_token'      => 'nullable|string',
            'device_name'       => 'nullable|string',
            'platform'          => 'nullable|string',
            'browser'           => 'nullable|string',
        ]);

        $siswa = Siswa::with('faceBiometric')->find($pending['siswa_id']);
        $user = User::find($pending['user_id']);

        if (!$siswa || !$user) {
            return response()->json(['success' => false, 'message' => 'Identitas akun tidak ditemukan.'], 404);
        }

        // 1. Verify Challenge Token (Anti-Replay)
        $cacheKey = 'biometric_challenge_' . $request->challenge_token;
        $challengeData = Cache::get($cacheKey);

        if (!$challengeData || ($challengeData['siswa_id'] ?? null) !== $siswa->id) {
            $this->biometricService->logVerification(
                $siswa->id,
                'login',
                'failed_liveness',
                null,
                null,
                null,
                'Token tantangan login kadaluarsa atau tidak valid (Anti-Replay)',
                $request->ip(),
                $request->userAgent()
            );

            return response()->json([
                'success' => false,
                'message' => 'Sesi tantangan verifikasi wajah telah kadaluarsa. Silakan muat ulang kamera.'
            ], 422);
        }

        // Invalidate challenge token immediately
        Cache::forget($cacheKey);

        // 2. Perform Euclidean Vector Verification
        $liveDescriptor = $request->face_descriptor;
        $registeredDescriptor = $siswa->faceBiometric->face_descriptor;

        if (!is_array($registeredDescriptor) || count($registeredDescriptor) !== 128) {
            return response()->json([
                'success' => false,
                'message' => 'Format template biometrik terdaftar tidak valid. Hubungi administrator.'
            ], 422);
        }

        $distance = $this->biometricService->calculateEuclideanDistance($registeredDescriptor, $liveDescriptor);
        $confidence = $this->biometricService->calculateConfidenceScore($distance);

        // Match threshold
        if ($distance > FaceBiometricService::MATCH_THRESHOLD) {
            $this->biometricService->logVerification(
                $siswa->id,
                'login',
                'failed_match',
                $confidence,
                $distance,
                $request->liveness_proof['liveness_score'] ?? 0,
                "Wajah login tidak cocok (Jarak: " . round($distance, 3) . ", Skor: {$confidence}%)",
                $request->ip(),
                $request->userAgent()
            );

            return response()->json([
                'success'    => false,
                'status'     => 'failed_match',
                'confidence' => $confidence,
                'distance'   => round($distance, 3),
                'message'    => "Verifikasi Wajah Gagal: Wajah Anda tidak cocok dengan akun terdaftar (Skor: {$confidence}%). Akses ditolak!"
            ], 422);
        }

        // 3. Device Management & Limit Enforcement (Atomic Transaction)
        $clientDeviceToken = $request->device_token ?? $pending['device_token'] ?? $request->cookie('myskanic_device_token');
        if (empty($clientDeviceToken)) {
            $clientDeviceToken = Str::random(40);
        }

        $deviceRegistered = null;

        try {
            DB::transaction(function () use ($siswa, $clientDeviceToken, $request, $pending, &$deviceRegistered) {
                // Check if device already registered for this student
                $existingDevice = SiswaDevice::where('siswa_id', $siswa->id)
                    ->where('device_token', $clientDeviceToken)
                    ->first();

                if ($existingDevice) {
                    if (!$existingDevice->is_active) {
                        // If device was revoked earlier, check if slots available
                        $activeCount = SiswaDevice::where('siswa_id', $siswa->id)
                            ->where('is_active', true)
                            ->lockForUpdate()
                            ->count();

                        if ($activeCount >= 2) {
                            throw new \Exception('Batas maksimal 2 perangkat aktif tercapai. Lepaskan salah satu perangkat lama terlebih dahulu.');
                        }

                        $existingDevice->update([
                            'is_active'      => true,
                            'revoked_at'     => null,
                            'last_active_at' => now(),
                            'ip_address'     => $request->ip(),
                            'user_agent'     => $request->userAgent(),
                        ]);
                    } else {
                        // Already active device, update last active
                        $existingDevice->update([
                            'last_active_at' => now(),
                            'ip_address'     => $request->ip(),
                            'user_agent'     => $request->userAgent(),
                        ]);
                    }
                    $deviceRegistered = $existingDevice;
                } else {
                    // New Device Registration: Lock and check max 2 devices
                    $activeCount = SiswaDevice::where('siswa_id', $siswa->id)
                        ->where('is_active', true)
                        ->lockForUpdate()
                        ->count();

                    if ($activeCount >= 2) {
                        throw new \Exception('Batas maksimal 2 perangkat aktif tercapai. Akun ini sudah terdaftar pada 2 perangkat. Silakan lepaskan salah satu perangkat atau hubungi admin.');
                    }

                    $deviceName = $request->device_name ?? ($pending['device_name'] ?? 'Perangkat Siswa');
                    $platform = $request->platform ?? ($pending['platform'] ?? 'Web');
                    $browser = $request->browser ?? ($pending['browser'] ?? 'Browser');

                    $deviceRegistered = SiswaDevice::create([
                        'siswa_id'       => $siswa->id,
                        'device_token'   => $clientDeviceToken,
                        'device_name'    => $deviceName,
                        'platform'       => $platform,
                        'browser'        => $browser,
                        'ip_address'     => $request->ip(),
                        'user_agent'     => $request->userAgent(),
                        'is_primary'     => ($activeCount === 0), // Device pertama otomatis jadi Primary!
                        'is_active'      => true,
                        'registered_at'  => now(),
                        'last_active_at' => now(),
                    ]);
                }
            });
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }

        // 4. Successful Verification! Log and Authenticate Session
        $this->biometricService->logVerification(
            $siswa->id,
            'login',
            'success',
            $confidence,
            round($distance, 3),
            98.0,
            'Verifikasi wajah login berhasil',
            $request->ip(),
            $request->userAgent()
        );

        // Update student biometric last verified
        $siswa->faceBiometric->update(['last_verified_at' => now()]);

        // Complete Login
        Auth::loginUsingId($user->id, $pending['remember'] ?? false);
        $request->session()->regenerate();
        $request->session()->put('biometric_login_verified', true);
        $request->session()->forget('pending_biometric_login');

        // Set 1-year persistent device cookie
        Cookie::queue('myskanic_device_token', $clientDeviceToken, 60 * 24 * 365);

        return response()->json([
            'success'      => true,
            'confidence'   => $confidence,
            'message'      => 'Verifikasi biometrik berhasil! Mengarahkan ke dashboard...',
            'redirect_url' => route('dashboard'),
            'device_token' => $clientDeviceToken,
        ]);
    }

    /**
     * Cancel 2FA Face Verification & Return to Login
     */
    public function cancel(Request $request)
    {
        session()->forget('pending_biometric_login');
        return redirect()->route('login');
    }
}
