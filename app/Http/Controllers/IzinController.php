<?php

namespace App\Http\Controllers;

use App\Helpers\SettingHelper;
use App\Models\Absensi;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IzinController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $siswa = null;

        if ($user->role == 'orangtua') {
            $dataOrangTua = \App\Models\OrangTua::where('user_id', $user->id)->first();
            if ($dataOrangTua) {
                $siswa = Siswa::find($dataOrangTua->siswa_id);
            }
        } else {
            // Point 3: Fitur izin hanya bisa dilakukan oleh orang tua
            return redirect()->route('dashboard')->with('error', 'Pengajuan izin hanya dapat dilakukan oleh Orang Tua.');
        }

        if (!$siswa) {
            return redirect()->route('dashboard')->with('error', 'Data siswa tidak ditemukan.');
        }

        // Cek absen hari ini
        $absenHariIni = Absensi::where('siswa_id', $siswa->id)
            ->whereDate('tanggal', date('Y-m-d'))
            ->first();

        // Cek alpha threshold (dynamic from settings)
        $alfaCount = $siswa->getTotalAlpha();
        
        $kuotaSP1 = SettingHelper::getAlphaQuota('SP1');
        $hasSP = \App\Models\SuratPeringatan::where('siswa_id', $siswa->id)->exists();
        $needsAttachment = ($alfaCount >= $kuotaSP1) || $hasSP;

        return view('izin.index', compact('siswa', 'absenHariIni', 'needsAttachment', 'alfaCount', 'kuotaSP1'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $siswa = null;

        if ($user->role == 'orangtua') {
            $dataOrangTua = \App\Models\OrangTua::where('user_id', $user->id)->first();
            if ($dataOrangTua) {
                $siswa = Siswa::find($dataOrangTua->siswa_id);
            }
        } else {
            return redirect()->route('dashboard')->with('error', 'Hanya Orang Tua yang dapat mengajukan izin.');
        }

        if (!$siswa) {
            return redirect()->route('dashboard')->with('error', 'Data siswa tidak ditemukan.');
        }

        $alfaCount = $siswa->getTotalAlpha();
        
        $kuotaSP1 = SettingHelper::getAlphaQuota('SP1');
        $hasSP = \App\Models\SuratPeringatan::where('siswa_id', $siswa->id)->exists();
        $needsAttachment = ($alfaCount >= $kuotaSP1) || $hasSP;

        $rules = [
            'status' => 'required|in:izin,sakit',
            'keterangan' => 'required|string|max:255',
            'lampiran' => ($request->status == 'sakit' || $needsAttachment) ? 'required|file|mimes:jpg,jpeg,png,pdf|max:2048' : 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ];

        $request->validate($rules);

        $cekAbsen = Absensi::where('siswa_id', $siswa->id)
            ->whereDate('tanggal', date('Y-m-d'))
            ->exists();

        if ($cekAbsen) {
            return redirect()->back()->with('error', 'Rekam kehadiran sudah ada untuk hari ini.');
        }

        $fileName = null;
        if ($request->hasFile('lampiran')) {
            $file = $request->file('lampiran');
            $fileName = $siswa->nis . "_lampiran_" . date('Ymd_His') . "." . $file->getClientOriginalExtension();
            $file->storeAs('public/absensi/lampiran', $fileName);
        }

        Absensi::create([
            'siswa_id' => $siswa->id,
            'tanggal' => date('Y-m-d'),
            'status' => $request->status,
            'keterangan' => $request->keterangan,
            'lampiran' => $fileName,
        ]);

        return redirect()->back()->with('success', 'Pengajuan ' . ucfirst($request->status) . ' berhasil dikirim.');
    }
}
