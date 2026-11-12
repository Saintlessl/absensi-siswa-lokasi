<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\StudentPasswordResetOtp;
use App\Models\User;
use App\Services\WhatsAppNotificationService;
use App\Services\WhatsAppOtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StudentForgotPasswordController extends Controller
{
    /**
     * STEP 1 - Show Account Identification Form (Email or NISN)
     */
    public function showIdentifyForm()
    {
        return view('auth.passwords.identify');
    }

    /**
     * STEP 1 - Process Account Identification & Send WhatsApp OTP
     */
    public function processIdentify(Request $request, WhatsAppOtpService $otpService)
    {
        $request->validate([
            'identifier' => ['required', 'string', 'max:255'],
        ], [
            'identifier.required' => 'Email atau NISN wajib diisi.',
        ]);

        $rawInput = trim($request->input('identifier', ''));
        $user = null;
        $siswa = null;

        // A. If input looks like an email
        if (str_contains($rawInput, '@')) {
            $user = User::where('email', strtolower($rawInput))->first();
            if ($user && $user->role === 'siswa') {
                $siswa = $user->siswa ?: Siswa::where('user_id', $user->id)->first();
            }
        } else {
            // B. If input is NISN or NIS
            $siswa = Siswa::with('user')
                ->where('nisn', $rawInput)
                ->orWhere('nis', $rawInput)
                ->first();

            if ($siswa && $siswa->user && $siswa->user->role === 'siswa') {
                $user = $siswa->user;
            }
        }

        // Generic error message if account not found, not a student, or inactive
        // This prevents role/account enumeration attacks
        $genericError = 'Akun siswa dengan email atau NISN tersebut tidak ditemukan atau tidak aktif.';

        if (!$user || !$siswa || $user->role !== 'siswa') {
            return back()->withInput()->with('error', $genericError);
        }

        // Ensure user account is active
        if ($user->status === 'suspended' || $user->status === 'deactivated') {
            return back()->withInput()->with('error', 'Akun Anda sedang ditangguhkan atau dinonaktifkan. Silakan hubungi pihak sekolah.');
        }

        // Validate personal student phone number
        $rawPhone = $siswa->no_telp_siswa;
        $normalizedPhone = (new WhatsAppNotificationService())->normalizePhoneNumber($rawPhone);

        if (empty($normalizedPhone)) {
            return back()->withInput()->with(
                'error',
                'Nomor telepon WhatsApp pribadi belum terdaftar atau tidak valid pada data siswa Anda. Silakan hubungi Administrator sekolah untuk memperbarui data nomor telepon Anda terlebih dahulu.'
            );
        }

        // STEP 3: Generate 6-digit cryptographically secure OTP
        $otp = (string) random_int(100000, 999999);
        $otpHash = Hash::make($otp);

        // Invalidate any existing pending OTPs for this student
        StudentPasswordResetOtp::where('user_id', $user->id)
            ->whereNull('used_at')
            ->delete();

        // Save new OTP record
        $otpRecord = StudentPasswordResetOtp::create([
            'user_id'             => $user->id,
            'siswa_id'            => $siswa->id,
            'no_telp'             => $normalizedPhone,
            'otp_hash'            => $otpHash,
            'attempts'            => 0,
            'expires_at'          => now()->addMinutes(5),
            'resend_available_at' => now()->addSeconds(60),
            'ip_address'          => $request->ip(),
            'user_agent'          => $request->userAgent(),
        ]);

        // Send OTP via Fonnte Gateway Service
        $sendResult = $otpService->sendOtp($normalizedPhone, $otp);

        if (!$sendResult['success']) {
            $otpRecord->delete();
            return back()->withInput()->with('error', $sendResult['message']);
        }

        // Setup session for OTP verification step
        $maskedPhone = StudentPasswordResetOtp::maskPhone($normalizedPhone);

        session([
            'password_reset_flow' => [
                'otp_id'       => $otpRecord->id,
                'user_id'      => $user->id,
                'siswa_id'     => $siswa->id,
                'masked_phone' => $maskedPhone,
                'expires_at'   => $otpRecord->expires_at->timestamp,
                'resend_at'    => $otpRecord->resend_available_at->timestamp,
            ]
        ]);

        return redirect()->route('password.otp')
            ->with('success', "Kode verifikasi 6 digit telah dikirimkan via WhatsApp ke {$maskedPhone}.");
    }

    /**
     * STEP 2 - Show 6-digit OTP Verification Form
     */
    public function showOtpForm()
    {
        $flow = session('password_reset_flow');

        if (!$flow || empty($flow['otp_id'])) {
            return redirect()->route('password.forgot')
                ->with('error', 'Sesi verifikasi tidak ditemukan. Silakan masukkan identitas akun Anda.');
        }

        $otpRecord = StudentPasswordResetOtp::find($flow['otp_id']);

        if (!$otpRecord || $otpRecord->isUsed()) {
            session()->forget('password_reset_flow');
            return redirect()->route('password.forgot')
                ->with('error', 'Permintaan OTP tidak valid atau sudah digunakan.');
        }

        $now = now()->timestamp;
        $remainingSeconds = max(0, $flow['expires_at'] - $now);
        $resendCooldown = max(0, $flow['resend_at'] - $now);

        return view('auth.passwords.verify-otp', [
            'maskedPhone'      => $flow['masked_phone'],
            'remainingSeconds' => $remainingSeconds,
            'resendCooldown'   => $resendCooldown,
            'attempts'         => $otpRecord->attempts,
        ]);
    }

    /**
     * STEP 2 - Verify Submitted 6-digit OTP
     */
    public function verifyOtp(Request $request)
    {
        $flow = session('password_reset_flow');

        if (!$flow || empty($flow['otp_id'])) {
            return redirect()->route('password.forgot')
                ->with('error', 'Sesi verifikasi Anda telah berakhir. Silakan ulangi identifikasi akun.');
        }

        // Combine 6 individual digit inputs or single input
        $otp = $request->input('otp');
        if (empty($otp) && $request->filled('digit_1')) {
            $otp = $request->digit_1 . $request->digit_2 . $request->digit_3 . $request->digit_4 . $request->digit_5 . $request->digit_6;
        }

        $otp = trim((string) $otp);

        if (strlen($otp) !== 6 || !ctype_digit($otp)) {
            return back()->with('error', 'Kode OTP harus berupa 6 digit angka.');
        }

        $redirect = null;

        DB::transaction(function () use ($flow, $otp, &$redirect) {
            $otpRecord = StudentPasswordResetOtp::where('id', $flow['otp_id'])
                ->where('user_id', $flow['user_id'])
                ->lockForUpdate()
                ->first();

            if (!$otpRecord || $otpRecord->isUsed()) {
                $redirect = redirect()->route('password.forgot')
                    ->with('error', 'Permintaan OTP tidak valid atau sudah kadaluarsa.');
                return;
            }

            if ($otpRecord->isMaxAttemptsExceeded()) {
                $redirect = back()->with(
                    'error',
                    'Batas maksimal 5 kali percobaan verifikasi telah tercapai. Silakan minta kode OTP baru.'
                );
                return;
            }

            if ($otpRecord->isExpired()) {
                $redirect = back()->with(
                    'error',
                    'Kode OTP telah kadaluarsa (berlaku 5 menit). Silakan klik Kirim Ulang Kode.'
                );
                return;
            }

            // Increment attempt count
            $otpRecord->increment('attempts');

            // Verify hash
            if (!$otpRecord->verifyOtp($otp)) {
                $remaining = max(0, 5 - $otpRecord->attempts);
                $redirect = back()->with(
                    'error',
                    "Kode OTP yang Anda masukkan salah. Sisa kesempatan mencoba: {$remaining} kali."
                );
                return;
            }

            // Valid! Generate cryptographically secure one-time reset token
            $resetToken = Str::random(64);
            $otpRecord->update([
                'verified_at' => now(),
                'reset_token' => $resetToken,
            ]);

            session([
                'password_reset_flow' => array_merge($flow, [
                    'reset_token' => $resetToken,
                    'verified'    => true,
                ])
            ]);

            $redirect = redirect()->route('password.reset.form')
                ->with('success', 'Kode OTP berhasil diverifikasi. Silakan buat password baru Anda.');
        });

        return $redirect;
    }

    /**
     * Resend 6-digit WhatsApp OTP with 60-second cooldown
     */
    public function resendOtp(Request $request, WhatsAppOtpService $otpService)
    {
        $flow = session('password_reset_flow');

        if (!$flow || empty($flow['otp_id'])) {
            return redirect()->route('password.forgot')
                ->with('error', 'Sesi tidak valid. Silakan ulangi dari awal.');
        }

        $otpRecord = StudentPasswordResetOtp::where('id', $flow['otp_id'])->first();

        if (!$otpRecord) {
            return redirect()->route('password.forgot')
                ->with('error', 'Data verifikasi tidak ditemukan.');
        }

        if (!$otpRecord->canResend()) {
            $remaining = now()->diffInSeconds($otpRecord->resend_available_at);
            return back()->with('error', "Mohon tunggu {$remaining} detik sebelum meminta pengiriman ulang kode OTP.");
        }

        // Invalidate old OTP
        $targetPhone = $otpRecord->no_telp;
        $otpRecord->delete();

        // Generate new OTP
        $newOtp = (string) random_int(100000, 999999);
        $newRecord = StudentPasswordResetOtp::create([
            'user_id'             => $flow['user_id'],
            'siswa_id'            => $flow['siswa_id'],
            'no_telp'             => $targetPhone,
            'otp_hash'            => Hash::make($newOtp),
            'attempts'            => 0,
            'expires_at'          => now()->addMinutes(5),
            'resend_available_at' => now()->addSeconds(60),
            'ip_address'          => $request->ip(),
            'user_agent'          => $request->userAgent(),
        ]);

        $sendResult = $otpService->sendOtp($targetPhone, $newOtp);

        if (!$sendResult['success']) {
            $newRecord->delete();
            return back()->with('error', $sendResult['message']);
        }

        session([
            'password_reset_flow' => array_merge($flow, [
                'otp_id'     => $newRecord->id,
                'expires_at' => $newRecord->expires_at->timestamp,
                'resend_at'  => $newRecord->resend_available_at->timestamp,
            ])
        ]);

        return back()->with('success', 'Kode OTP baru telah berhasil dikirimkan ke nomor WhatsApp Anda.');
    }

    /**
     * STEP 3 - Show Reset Password Form
     */
    public function showResetForm()
    {
        $flow = session('password_reset_flow');

        if (!$flow || empty($flow['reset_token']) || empty($flow['verified'])) {
            return redirect()->route('password.forgot')
                ->with('error', 'Akses ditolak: Anda harus menyelesaikan verifikasi kode OTP terlebih dahulu.');
        }

        $otpRecord = StudentPasswordResetOtp::where('id', $flow['otp_id'])
            ->where('reset_token', $flow['reset_token'])
            ->whereNotNull('verified_at')
            ->whereNull('used_at')
            ->first();

        if (!$otpRecord) {
            session()->forget('password_reset_flow');
            return redirect()->route('password.forgot')
                ->with('error', 'Token verifikasi tidak valid atau telah kadaluarsa.');
        }

        return view('auth.passwords.reset');
    }

    /**
     * STEP 3 - Store New Password and Invalidate Reset Token
     */
    public function storeNewPassword(Request $request)
    {
        $flow = session('password_reset_flow');

        if (!$flow || empty($flow['reset_token']) || empty($flow['otp_id'])) {
            return redirect()->route('password.forgot')
                ->with('error', 'Sesi pengaturan ulang password tidak valid. Silakan ulangi proses dari awal.');
        }

        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'password.required'  => 'Password baru wajib diisi.',
            'password.min'       => 'Password minimal harus 8 karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        DB::transaction(function () use ($flow, $request) {
            $otpRecord = StudentPasswordResetOtp::where('id', $flow['otp_id'])
                ->where('reset_token', $flow['reset_token'])
                ->whereNotNull('verified_at')
                ->whereNull('used_at')
                ->lockForUpdate()
                ->firstOrFail();

            // Update user password and invalidate remember me tokens
            $user = User::lockForUpdate()->findOrFail($otpRecord->user_id);
            $user->update([
                'password'             => Hash::make($request->password),
                'must_change_password' => false,
                'remember_token'       => Str::random(60),
            ]);

            // Invalidate OTP record permanently
            $otpRecord->update([
                'used_at'     => now(),
                'reset_token' => null,
            ]);
        });

        // Clear password reset session
        session()->forget('password_reset_flow');

        return redirect()->route('login')
            ->with('success', 'Password Anda berhasil diperbarui! Silakan login menggunakan password baru Anda.');
    }
}
