<?php

namespace App\Http\Controllers;

use App\Models\Jurusan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class JurusanController extends Controller
{
    public function index(Request $request)
    {
        $query = Jurusan::withCount(['kelas', 'siswa']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('kode_jurusan', 'LIKE', '%' . $search . '%')
                  ->orWhere('nama_jurusan', 'LIKE', '%' . $search . '%')
                  ->orWhere('deskripsi', 'LIKE', '%' . $search . '%');
            });
        }

        $jurusans = $query->orderBy('kode_jurusan')->paginate(10)->appends($request->query());

        return view('jurusan.index', compact('jurusans') + ['search' => $request->search ?? '']);
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_jurusan' => 'required|string|max:20|unique:jurusans,kode_jurusan',
            'nama_jurusan' => 'required|string|max:255',
            'deskripsi'    => 'nullable|string',
        ]);

        Jurusan::create([
            'kode_jurusan' => strtoupper(trim($request->kode_jurusan)),
            'nama_jurusan' => $request->nama_jurusan,
            'deskripsi'    => $request->deskripsi,
        ]);

        return redirect()->back()->with('success', 'Data Jurusan berhasil ditambahkan');
    }

    public function edit(Request $request)
    {
        $jurusan = Jurusan::findOrFail($request->id);
        return view('jurusan.edit', compact('jurusan'));
    }

    public function update(Request $request, $id)
    {
        $jurusan = Jurusan::findOrFail($id);

        $request->validate([
            'kode_jurusan' => ['required', 'string', 'max:20', Rule::unique('jurusans', 'kode_jurusan')->ignore($jurusan->id)],
            'nama_jurusan' => 'required|string|max:255',
            'deskripsi'    => 'nullable|string',
        ]);

        $jurusan->update([
            'kode_jurusan' => strtoupper(trim($request->kode_jurusan)),
            'nama_jurusan' => $request->nama_jurusan,
            'deskripsi'    => $request->deskripsi,
        ]);

        return redirect()->route('jurusan')->with('success', 'Data Jurusan berhasil diperbarui!');
    }

    public function destroy($id)
    {
        try {
            $jurusan = Jurusan::findOrFail($id);

            if ($jurusan->kelas()->exists()) {
                return redirect()->back()->with('error', 'Jurusan tidak bisa dihapus karena masih memiliki data kelas terkait.');
            }

            $jurusan->delete();

            return redirect()->route('jurusan')->with('success', 'Data Jurusan berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }
}
