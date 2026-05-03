<?php

namespace App\Http\Controllers;

use App\Models\LokasiSekolah;
use Illuminate\Http\Request;

class LokasiSekolahController extends Controller
{
    public function index()
    {
        $lokasi = LokasiSekolah::first();
        
        // Buat default jika belum ada
        if (!$lokasi) {
            $lokasi = LokasiSekolah::create([
                'latitude' => -0.5413156, // default Gorontalo/Piloliyanga or something
                'longitude' => 123.059495,
                'radius' => 100
            ]);
        }

        return view('lokasi_sekolah.index', compact('lokasi'));
    }

    public function update(Request $request)
    {
        $lokasi = LokasiSekolah::first();
        
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius' => 'required|integer|min:1',
        ]);

        if ($lokasi) {
            $lokasi->update($request->all());
        }

        return redirect()->back()->with('success', 'Pengaturan lokasi sekolah berhasil diperbarui');
    }
}
