<?php

namespace App\Http\Controllers;

use App\Models\LokasiSekolah;
use Illuminate\Http\Request;

class LokasiSekolahController extends Controller
{
    public function index()
    {
        if (!in_array(\Illuminate\Support\Facades\Auth::user()->role, ['superadmin', 'admin'])) {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak: Pengaturan lokasi sekolah hanya dapat diakses oleh Admin atau Superadmin.');
        }

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
        if (!in_array(\Illuminate\Support\Facades\Auth::user()->role, ['superadmin', 'admin'])) {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak: Hanya Admin atau Superadmin yang berwenang mengubah koordinat & radius lokasi sekolah.');
        }

        $lokasi = LokasiSekolah::first();
        
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'required|integer|min:1|max:5000',
        ]);

        $data = $request->only(['latitude', 'longitude', 'radius']);

        // No silent no-op when the row does not exist yet.
        if ($lokasi) {
            $lokasi->update($data);
        } else {
            \App\Models\LokasiSekolah::create($data);
        }

        return redirect()->back()->with('success', 'Pengaturan lokasi sekolah berhasil diperbarui');
    }
}
