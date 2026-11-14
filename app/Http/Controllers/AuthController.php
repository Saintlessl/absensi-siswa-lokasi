<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\SiswaDevice;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function index(Request $request)
    {
        $initialRole = $request->query('role', 'siswa');
        if (!in_array($initialRole, ['siswa', 'guru'], true)) {
            $initialRole = 'siswa';
        }

        return view('auth.login', compact('initialRole'));
    }

    public function indexGuru()
    {
        return view('auth.login', ['initialRole' => 'guru']);
    }

    public function indexSiswa()
    {
        return view('auth.login', ['initialRole' => 'siswa']);
    }

    public function proseslogin(Request $request)
    {
        $rawInput = trim($request->input('email', ''));
        $resolvedEmail = strtolower($rawInput);

        // Smart Identifier Resolution
        $existing = User::where('email', $resolvedEmail)->first();

        if (!$existing && !empty($rawInput)) {
            if (!str_contains($rawInput, '@')) {
                $targetRole = strtolower($rawInput);
                if ($targetRole === 'ortu') {
                    $targetRole = 'orangtua';
                }

                $roleMatch = User::where('role', $targetRole)->first();
                if ($roleMatch) {
                    $resolvedEmail = $roleMatch->email;
                } else {
                    $prefixMatch = User::where('email', 'like', $rawInput . '%')->first()
                        ?? User::where('email', 'like', '%' . $rawInput . '%')->first();
                    if ($prefixMatch) {
                        $resolvedEmail = $prefixMatch->email;
                    } else {
                        $resolvedEmail = strtolower($rawInput) . '@myskanic.com';
                    }
                }
            } else {
                $local = explode('@', $rawInput)[0];
                $localMatch = User::where('email', 'like', $local . '@%')->first()
                    ?? User::where('email', 'like', $local . '%')->first();
                if ($localMatch) {
                    $resolvedEmail = $localMatch->email;
                }
            }
        }

        $request->merge(['email' => $resolvedEmail]);

        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Validate credentials against users table
        if (!Auth::validate($credentials)) {
            return back()->with('error', 'Email atau password yang Anda masukkan salah!')
                ->withInput($request->only('email', 'remember'));
        }

        $user = User::where('email', $credentials['email'])->first();

        // Check Account Status (active, pending, suspended, deactivated)
        if ($user->status === 'suspended') {
            return back()->with('error', 'Akun Anda sedang ditangguhkan (suspended). Silakan hubungi pihak administrator sekolah.')
                ->withInput($request->only('email'));
        }

        if ($user->status === 'deactivated') {
            return back()->with('error', 'Akun Anda telah dinonaktifkan (deactivated). Akses ke aplikasi ditolak.')
                ->withInput($request->only('email'));
        }

        if ($user->status === 'pending' && in_array($user->role, ['guru', 'orangtua'])) {
            $roleLabel = $user->role === 'guru' ? 'guru' : 'orang tua';
            return back()->with('error', "Akun {$roleLabel} Anda belum diaktifkan. Silakan selesaikan proses aktivasi akun melalui tautan yang diberikan sekolah.")
                ->withInput($request->only('email'));
        }

        // Resolve portal role from input, query, or referer
        $portalRole = strtolower(trim((string) $request->input('portal_role', '')));
        if (empty($portalRole)) {
            $referer = (string) $request->header('referer', '');
            if (str_contains($referer, 'role=guru') || str_contains($referer, '/login/guru')) {
                $portalRole = 'guru';
            } else {
                $portalRole = 'siswa';
            }
        }

        // Strict Role-Based Portal Access Enforcement
        if ($portalRole === 'siswa') {
            // Portal Siswa accepts: Siswa, Orang Tua, Admin, Super Admin.
            // Explicitly rejects: Guru, Kaprog.
            if (in_array($user->role, ['guru', 'kaprog'], true)) {
                return back()->with('error', 'Akun ini tidak dapat digunakan melalui portal login siswa. Silakan gunakan portal login guru.')
                    ->withInput($request->only('email', 'remember'));
            }
        } elseif ($portalRole === 'guru') {
            // Portal Guru accepts: Guru, Kaprog, Admin, Super Admin.
            // Explicitly rejects: Siswa, Orang Tua.
            if (in_array($user->role, ['siswa', 'orangtua'], true)) {
                return back()->with('error', 'Akun ini tidak dapat digunakan melalui portal login guru. Silakan gunakan portal login siswa.')
                    ->withInput($request->only('email', 'remember'));
            }
        }

        // 1. Non-student accounts (Superadmin, Admin, Guru, Kaprog, Orang Tua) log in directly
        if ($user->role !== 'siswa') {
            Auth::login($user, $request->has('remember'));
            $request->session()->regenerate();
            return redirect()->route('dashboard')->with('success', 'Selamat datang kembali, ' . $user->salutation_name . '!');
        }

        // 2. Student Account Authentication
        $siswa = Siswa::with('faceBiometric')->where('user_id', $user->id)->first();

        if (!$siswa) {
            return back()->with('error', 'Profil data siswa tidak ditemukan. Hubungi pihak sekolah.');
        }

        // Case A0: Student is officially exempt from biometric face verification (Testing / Special Exemption)
        if ($siswa->is_biometric_exempt) {
            Auth::login($user, $request->has('remember'));
            $request->session()->regenerate();

            return redirect()->route('dashboard')->with('success', 'Selamat datang kembali, ' . $siswa->nama . '!');
        }

        // Case A: Student has NOT completed First-Login Biometric Enrollment yet
        if (!$siswa->isBiometricEnrolled()) {
            Auth::login($user, $request->has('remember'));
            $request->session()->regenerate();

            return redirect()->route('siswa.enrollment')
                ->with('info', 'Selamat datang! Anda diwajibkan melakukan pendaftaran biometrik wajah sebelum dapat menggunakan sistem.');
        }

        // Case B: Student is enrolled -> Check Device Limit (Max 2 devices)
        $clientDeviceToken = $request->device_token ?? $request->cookie('myskanic_device_token');

        $isDeviceRegistered = false;
        if (!empty($clientDeviceToken)) {
            $isDeviceRegistered = SiswaDevice::where('siswa_id', $siswa->id)
                ->where('device_token', $clientDeviceToken)
                ->where('is_active', true)
                ->exists();
        }

        // If not recognized as an already-registered device, check available slots
        if (!$isDeviceRegistered) {
            $activeCount = SiswaDevice::where('siswa_id', $siswa->id)
                ->where('is_active', true)
                ->count();

            if ($activeCount >= 2) {
                return back()->with('error', 'Batas maksimal 2 perangkat aktif tercapai. Akun ini sudah terdaftar pada 2 perangkat. Silakan lepaskan salah satu perangkat lama atau hubungi administrator untuk mereset perangkat.')
                    ->withInput($request->only('email'));
            }
        }

        // Case C: Initiate 2FA Face Verification on Login
        session([
            'pending_biometric_login' => [
                'user_id'      => $user->id,
                'siswa_id'     => $siswa->id,
                'device_token' => $clientDeviceToken ?? Str::random(40),
                'device_name'  => $request->device_name,
                'platform'     => $request->platform,
                'browser'      => $request->browser,
                'remember'     => $request->has('remember'),
                'created_at'   => now()->timestamp,
            ]
        ]);

        return redirect()->route('login.verify_face');
    }

    public function proseslogout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
