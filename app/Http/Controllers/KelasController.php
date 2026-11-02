<?php

namespace App\Http\Controllers;

use App\Models\Jurusan;
use App\Models\Kelas;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class KelasController extends Controller
{
    public function index(Request $request)
    {
        $query = Kelas::with('jurusan');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_kelas', 'LIKE', '%' . $search . '%')
                  ->orWhere('wali_kelas', 'LIKE', '%' . $search . '%')
                  ->orWhereHas('jurusan', function ($qJ) use ($search) {
                      $qJ->where('kode_jurusan', 'LIKE', '%' . $search . '%')
                         ->orWhere('nama_jurusan', 'LIKE', '%' . $search . '%');
                  });
            });
        }

        if ($request->filled('jurusan_id')) {
            $query->where('jurusan_id', $request->jurusan_id);
        }

        $kelas = $query->orderBy('nama_kelas')->paginate(10)->appends($request->query());
        $jurusans = Jurusan::orderBy('kode_jurusan')->get();

        return view('kelas.index', compact('kelas', 'jurusans') + [
            'search'     => $request->search ?? '',
            'jurusan_id' => $request->jurusan_id ?? '',
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_kelas'   => 'nullable|string|max:255',
            'jurusan_id'   => 'required|exists:jurusans,id',
            'tingkat'      => 'required|in:10,11,12',
            'nomor_rombel' => 'required|integer|min:1|max:99',
            'tahun_ajaran' => 'required|string|max:20',
            'jumlah_siswa' => 'required|integer',
            'wali_kelas'   => 'required|string|max:255',
        ]);

        $jurusan = Jurusan::whereKey($request->jurusan_id)->where('is_active', true)->firstOrFail();
        Kelas::create([
            'nama_kelas'   => "{$request->tingkat} {$jurusan->kode_jurusan} {$request->nomor_rombel}",
            'jurusan_id'   => $request->jurusan_id,
            'jumlah_siswa' => $request->jumlah_siswa,
            'wali_kelas'   => $request->wali_kelas,
            'tingkat' => $request->tingkat, 'nomor_rombel' => $request->nomor_rombel, 'tahun_ajaran' => $request->tahun_ajaran, 'is_active' => true,
        ]);

        return redirect()->back()->with('success', 'Data Kelas berhasil ditambahkan');
    }

    public function edit(Request $request)
    {
        $kelas = Kelas::findOrFail($request->id);
        $jurusans = Jurusan::orderBy('kode_jurusan')->get();
        return view('kelas.edit', compact('kelas', 'jurusans'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_kelas'   => 'nullable|string|max:255',
            'jurusan_id'   => 'required|exists:jurusans,id',
            'tingkat'      => 'required|in:10,11,12',
            'nomor_rombel' => 'required|integer|min:1|max:99',
            'tahun_ajaran' => 'required|string|max:20',
            'jumlah_siswa' => 'required|integer',
            'wali_kelas'   => 'required|string|max:255',
        ]);

        try {
            $kelas = Kelas::findOrFail($id);
            $jurusan = Jurusan::whereKey($request->jurusan_id)->where('is_active', true)->firstOrFail();
            $kelas->update([
                'nama_kelas'   => "{$request->tingkat} {$jurusan->kode_jurusan} {$request->nomor_rombel}",
                'jurusan_id'   => $request->jurusan_id,
                'jumlah_siswa' => $request->jumlah_siswa,
                'wali_kelas'   => $request->wali_kelas,
                'tingkat' => $request->tingkat, 'nomor_rombel' => $request->nomor_rombel, 'tahun_ajaran' => $request->tahun_ajaran,
            ]);

            return redirect()->route('kelas')->with('success', 'Data kelas berhasil diperbarui!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    public function destroy($id)
    {
        try {
            $kelas = Kelas::findOrFail($id);

            if ($kelas->siswa()->exists()) {
                return redirect()->back()->with('error', 'Kelas tidak bisa dihapus karena masih memiliki data siswa.');
            }

            if ($kelas->jadwal()->exists()) {
                return redirect()->back()->with('error', 'Kelas tidak bisa dihapus karena masih terdaftar dalam jadwal pelajaran.');
            }

            $kelas->delete();

            return redirect()->route('kelas')->with('success', 'Data kelas berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }
}
