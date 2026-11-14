<?php

namespace App\Http\Middleware;

use App\Models\Siswa;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureBiometricEnrolled
{
    /**
     * Handle an incoming request.
     *
     * Ensures that any student accessing the system must complete biometric
     * face enrollment on their first login before they can access the dashboard or any other features.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        // Non-student accounts (Superadmin, Admin, Guru, Orang Tua) are not restricted by student enrollment
        if ($user->role !== 'siswa') {
            return $next($request);
        }

        $siswa = Siswa::with('faceBiometric')->where('user_id', $user->id)->first();

        if (!$siswa || $siswa->is_biometric_exempt) {
            // Student record not found or exempt from biometric verification
            return $next($request);
        }

        // Routes permitted during enrollment
        $allowedEnrollmentRouteNames = [
            'siswa.enrollment',
            'siswa.enrollment.challenge',
            'siswa.enrollment.process',
            'proseslogout',
            'logout',
            'profile',
            'profile.password',
            'profile.update',
            'settings.profile',
            'settings.profile.password',
            'settings.profile.info',
        ];

        $currentRouteName = $request->route() ? $request->route()->getName() : '';
        $isEnrollmentPath = $request->is('siswa/biometric-enrollment*') ||
                            $request->is('proseslogout') ||
                            $request->is('logout') ||
                            $request->is('profile*') ||
                            $request->is('pengaturan/profil*') ||
                            in_array($currentRouteName, $allowedEnrollmentRouteNames, true);

        $isEnrolled = $siswa->isBiometricEnrolled();

        // Scenario 1: Student is already fully enrolled
        if ($isEnrolled) {
            // If they try to access the enrollment wizard, redirect them to dashboard
            if (in_array($currentRouteName, ['siswa.enrollment', 'siswa.enrollment.process']) || $request->is('siswa/biometric-enrollment*')) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'enrolled' => true,
                        'message' => 'Anda sudah menyelesaikan registrasi biometrik wajah.',
                        'redirect_url' => route('dashboard')
                    ]);
                }
                return redirect()->route('dashboard')->with('info', 'Anda sudah menyelesaikan registrasi biometrik wajah.');
            }

            return $next($request);
        }

        // Scenario 2: Student has NOT enrolled yet (First login or pending/needs_re_enrollment)
        if ($isEnrollmentPath) {
            return $next($request);
        }

        // Intercept any attempt to access dashboard, attendance, etc.
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'requires_enrollment' => true,
                'message' => 'Anda harus menyelesaikan registrasi biometrik wajah terlebih dahulu.',
                'redirect_url' => route('siswa.enrollment')
            ], 403);
        }

        return redirect()->route('siswa.enrollment')->with('warning', 'Pendaftaran Wajib: Anda harus mendaftarkan biometrik wajah Anda terlebih dahulu sebelum dapat mengakses menu lainnya.');
    }
}
