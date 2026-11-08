<?php

namespace App\Http\Controllers;

use App\Models\SchoolSetting;
use Illuminate\Http\Request;

class SchoolSettingController extends Controller
{
    /**
     * Show the school settings form (jam masuk / jam pulang).
     */
    public function index()
    {
        $settings = SchoolSetting::all()->pluck('value', 'key');

        return view('settings.index', [
            'jamMasuk'  => $settings['jam_masuk'] ?? '07:15',
            'jamPulang' => $settings['jam_pulang'] ?? '14:55',
            'namaSekolah' => $settings['nama_sekolah'] ?? 'SMK Negri 1 Ciomas',
        ]);
    }

    /**
     * Persist school settings.
     */
    public function update(Request $request)
    {
        $request->validate([
            'jam_masuk'   => 'required|date_format:H:i',
            'jam_pulang'  => 'required|date_format:H:i|after:jam_masuk',
            'nama_sekolah' => 'nullable|string|max:150',
        ], [
            'jam_pulang.after' => 'Jam pulang harus lebih akhir dari jam masuk.',
        ]);

        $data = [
            'jam_masuk'  => $request->jam_masuk,
            'jam_pulang' => $request->jam_pulang,
        ];

        if ($request->filled('nama_sekolah')) {
            $data['nama_sekolah'] = $request->nama_sekolah;
        }

        foreach ($data as $key => $value) {
            SchoolSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return back()->with('success', 'Pengaturan sekolah berhasil disimpan.');
    }
}
