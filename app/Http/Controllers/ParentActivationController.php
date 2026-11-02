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
        abort_unless($activation, 404, 'Tautan aktivasi tidak valid atau sudah kedaluwarsa.');
        return view('auth.parent-activate', compact('token'));
    }

    public function store(Request $request, string $token)
    {
        $request->validate(['password' => 'required|string|min:8|confirmed']);
        $activation = $this->findValid($token);
        abort_unless($activation, 422, 'Tautan aktivasi tidak valid atau sudah kedaluwarsa.');

        DB::transaction(function () use ($activation, $request) {
            $activation->user->update(['password' => Hash::make($request->password)]);
            $activation->update(['activated_at' => now()]);
        });

        return redirect()->route('login')->with('success', 'Akun orang tua sudah aktif. Silakan masuk menggunakan alamat email yang diberikan sekolah.');
    }

    private function findValid(string $token): ?ParentAccountActivation
    {
        return ParentAccountActivation::with('user')
            ->where('token_hash', hash('sha256', $token))
            ->whereNull('activated_at')
            ->where('expires_at', '>', now())
            ->first();
    }
}
