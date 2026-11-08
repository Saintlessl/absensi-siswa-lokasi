<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordChanged
{
    /**
     * Handle an incoming request.
     *
     * Intercepts authenticated users who are required to change their default
     * password (must_change_password = true) and restricts them to the profile
     * password update and logout routes until they complete the change.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        if (!$user->must_change_password) {
            return $next($request);
        }

        $allowedRouteNames = [
            'profile',
            'profile.password',
            'profile.update',
            'proseslogout',
        ];

        $currentRouteName = $request->route() ? $request->route()->getName() : '';

        $isAllowed = in_array($currentRouteName, $allowedRouteNames, true) ||
                     $request->is('profile*') ||
                     $request->is('proseslogout');

        if ($isAllowed) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Kata sandi default wajib diubah sebelum mengakses fitur ini.',
                'must_change_password' => true,
            ], 403);
        }

        return redirect()->route('profile')->with('warning', 'Demi keamanan akun Anda, silakan ubah kata sandi default terlebih dahulu.');
    }
}
