<?php

namespace App\Http\Controllers;

use App\Models\ParentAccountActivation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ParentActivationController extends Controller
{
    public function show(string $token)
    {
        $activation = $this->findValid($token);

        if (!$activation) {
            return view('auth.parent-activate', [
                'token'     => $token,
                'isInvalid' => true,
                'user'      => null,
            ]);
        }

        return view('auth.parent-activate', [
            'token'     => $token,
            'isInvalid' => false,
            'user'      => $activation->user,
        ]);
    }

    public function store(Request $request, string $token)
    {
        $activation = $this->findValid($token);

        if (!$activation) {
            return redirect()->route('parent.activate.show', $token)
                ->with('error', 'Tautan aktivasi tidak valid, telah kedaluwarsa, atau sudah pernah digunakan.');
        }

        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ], [
            'password.required'  => 'Kata sandi baru wajib diisi.',
            'password.min'       => 'Kata sandi minimal harus terdiri dari 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        DB::transaction(function () use ($activation, $request) {
            $activation->user->update([
                'password'             => Hash::make($request->password),
                'status'               => 'active',
                'must_change_password' => false,
            ]);

            $activation->update([
                'activated_at' => now(),
            ]);
        });

        return redirect()->route('login')->with('success', 'Akun orang tua Anda berhasil diaktifkan. Silakan masuk menggunakan alamat email yang terdaftar.');
    }

    private function findValid(string $token): ?ParentAccountActivation
    {
        return ParentAccountActivation::with(['user.orangTua.siswa'])
            ->where('token_hash', hash('sha256', $token))
            ->whereNull('activated_at')
            ->where('expires_at', '>', now())
            ->first();
    }
}
