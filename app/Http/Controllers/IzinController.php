<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IzinController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $siswa = Siswa::where('user_id', $user->id)->first();

        if (!$siswa) {
            return redirect()->route('dashboard')->with('error', 'Anda tidak terdaftar sebagai Siswa aktif.');
        }

        // Cek absen hari ini
        $absenHariIni = Absensi::where('siswa_id', $siswa->id)
            ->whereDate('tanggal', date('Y-m-d'))
            ->first();

        return view('izin.index', compact('siswa', 'absenHariIni'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $siswa = Siswa::where('user_id', $user->id)->first();

        if (!$siswa) {
            return redirect()->route('dashboard')->with('error', 'Anda bukan siswa');
        }

        $request->validate([
            'status' => 'required|in:izin,sakit',
            'keterangan' => 'required|string|max:255',
        ]);

        $cekAbsen = Absensi::where('siswa_id', $siswa->id)
            ->whereDate('tanggal', date('Y-m-d'))
            ->exists();

        if ($cekAbsen) {
            return redirect()->back()->with('error', 'Anda sudah melakukan rekam kehadiran (Absen/Izin) hari ini.');
        }

        Absensi::create([
            'siswa_id' => $siswa->id,
            'tanggal' => date('Y-m-d'),
            'status' => $request->status,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->back()->with('success', 'Pengajuan ' . ucfirst($request->status) . ' berhasil dikirim.');
    }
}
