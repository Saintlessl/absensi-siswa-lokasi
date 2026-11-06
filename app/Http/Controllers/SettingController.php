<?php

namespace App\Http\Controllers;

use App\Helpers\SettingHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingController extends Controller
{
    public function index()
    {
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Hanya admin yang dapat mengakses halaman ini.');
        }

        $settings = [
            'kuota_alpha_SP1' => SettingHelper::get('kuota_alpha_SP1', 5),
            'kuota_alpha_SP2' => SettingHelper::get('kuota_alpha_SP2', 10),
            'kuota_alpha_SP3' => SettingHelper::get('kuota_alpha_SP3', 15),
            'nama_sekolah'   => SettingHelper::get('nama_sekolah', 'SMP IT Yapura'),
            'kepala_sekolah' => SettingHelper::get('kepala_sekolah', ''),
            'nip_kepsek'     => SettingHelper::get('nip_kepsek', ''),
            'late_threshold' => SettingHelper::get('late_threshold', '07:30:00'),
        ];

        return view('setting.index', compact('settings'));
    }

    public function update(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak.');
        }

        $request->validate([
            'kuota_alpha_SP1' => 'required|integer|min:1|max:50',
            'kuota_alpha_SP2' => 'required|integer|min:1|max:50',
            'kuota_alpha_SP3' => 'required|integer|min:1|max:50',
            'nama_sekolah'   => 'required|string|max:255',
            'kepala_sekolah' => 'nullable|string|max:255',
            'nip_kepsek'     => 'nullable|string|max:100',
            'late_threshold' => 'required|date_format:H:i:s',
        ]);

        // BUG-037: quotas must be strictly ordered or the SP ladder breaks
        // (a student can cross SP3 quota before SP1 ever triggers).
        if (!($request->kuota_alpha_SP1 < $request->kuota_alpha_SP2 && $request->kuota_alpha_SP2 < $request->kuota_alpha_SP3)) {
            return redirect()->back()->withInput()->with(
                'error', 'Kuota alpha harus berurutan: SP1 < SP2 < SP3 (contoh: 5, 10, 15).'
            );
        }

        SettingHelper::set('kuota_alpha_SP1', $request->kuota_alpha_SP1);
        SettingHelper::set('kuota_alpha_SP2', $request->kuota_alpha_SP2);
        SettingHelper::set('kuota_alpha_SP3', $request->kuota_alpha_SP3);
        SettingHelper::set('nama_sekolah', $request->nama_sekolah);
        SettingHelper::set('kepala_sekolah', $request->kepala_sekolah);
        SettingHelper::set('nip_kepsek', $request->nip_kepsek);
        SettingHelper::set('late_threshold', $request->late_threshold);

        return redirect()->back()->with('success', 'Pengaturan berhasil disimpan.');
    }
}
