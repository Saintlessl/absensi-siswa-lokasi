<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Kelas;

class AuthController extends Controller
{
    public function index()
    {
        $kelas = Kelas::all();
        return view('auth.login', compact('kelas'));
    }

    public function proseslogin(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
            'id_kelas' => ['nullable', 'exists:kelas,id'],
        ]);

        $remember = $request->has('remember');
        $id_kelas = $request->id_kelas;

        if (Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']], $remember)) {
            $user = Auth::user();

            // If id_kelas is selected, verify it matches student's class
            if ($id_kelas && $user->role == 'siswa') {
                $siswa = \App\Models\Siswa::where('user_id', $user->id)->first();
                if (!$siswa || $siswa->kelas_id != $id_kelas) {
                    Auth::logout();
                    return back()->with('error', 'Akun Anda tidak terdaftar di kelas yang dipilih!')
                        ->withInput($request->only('email', 'remember', 'id_kelas'));
                }
            }

            $request->session()->regenerate();

            return redirect()->route('dashboard');
        }

        return back()->with('error', 'Email atau password yang Anda masukkan salah!')
            ->withInput($request->only('email', 'remember', 'id_kelas'));
    }

    public function proseslogout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
