<?php

namespace App\Http\Controllers;

use App\Services\TeacherAccountService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeacherActivationController extends Controller
{
    public function __construct(protected TeacherAccountService $accountService)
    {
    }

    public function show(string $token)
    {
        $activation = $this->accountService->getValidActivation($token);

        if (!$activation) {
            return view('auth.teacher-activate', [
                'token'     => $token,
                'isInvalid' => true,
                'guru'      => null,
            ]);
        }

        return view('auth.teacher-activate', [
            'token'     => $token,
            'isInvalid' => false,
            'guru'      => $activation->user->guru,
            'user'      => $activation->user,
        ]);
    }

    public function store(Request $request, string $token)
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = $this->accountService->activateAccount($token, $request->password);

        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'Akun guru Anda berhasil diaktifkan! Selamat datang di sistem presensi dan akademik sekolah.');
    }
}
