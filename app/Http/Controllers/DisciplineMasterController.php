<?php

namespace App\Http\Controllers;

use App\Models\DisciplineCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DisciplineMasterController extends Controller
{
    /**
     * Display master data list for Pelanggaran & Prestasi
     */
    public function index(Request $request)
    {
        $query = DisciplineCategory::with('creator');

        if ($request->filled('tipe')) {
            $query->where('tipe', $request->tipe);
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('nama', 'like', "%{$s}%")
                  ->orWhere('deskripsi', 'like', "%{$s}%");
            });
        }

        $categories = $query->orderBy('tipe')->orderBy('nama')->paginate(20)->withQueryString();

        $stats = [
            'total_pelanggaran_aktif' => DisciplineCategory::where('tipe', 'pelanggaran')->where('is_active', true)->count(),
            'total_prestasi_aktif'    => DisciplineCategory::where('tipe', 'prestasi')->where('is_active', true)->count(),
            'total_master'            => DisciplineCategory::count(),
            'total_nonaktif'          => DisciplineCategory::where('is_active', false)->count(),
        ];

        return view('discipline.master', compact('categories', 'stats'));
    }

    /**
     * Store new master item
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama'         => 'required|string|max:150',
            'tipe'         => 'required|in:pelanggaran,prestasi',
            'default_poin' => 'required|integer',
            'deskripsi'    => 'nullable|string',
            'is_active'    => 'nullable|boolean',
        ], [
            'nama.required'         => 'Nama standar pelanggaran / prestasi wajib diisi.',
            'tipe.required'         => 'Jenis standar (Pelanggaran atau Prestasi) wajib dipilih.',
            'default_poin.required' => 'Poin default wajib ditentukan.',
            'default_poin.integer'  => 'Poin default harus berupa angka bulat (bisa negatif, 0, atau positif).',
        ]);

        $validated['is_active'] = $request->has('is_active') ? (bool) $request->is_active : true;
        $validated['created_by'] = Auth::id();

        $category = DisciplineCategory::create($validated);

        return redirect()->route('discipline.master.index')
            ->with('success', "Master {$category->tipe} '{$category->nama}' ({$category->formatted_default_poin} poin) berhasil ditambahkan.");
    }

    /**
     * Update existing master item
     */
    public function update(Request $request, $id)
    {
        $category = DisciplineCategory::findOrFail($id);

        $validated = $request->validate([
            'nama'         => 'required|string|max:150',
            'tipe'         => 'required|in:pelanggaran,prestasi',
            'default_poin' => 'required|integer',
            'deskripsi'    => 'nullable|string',
            'is_active'    => 'nullable|boolean',
        ], [
            'nama.required'         => 'Nama standar pelanggaran / prestasi wajib diisi.',
            'tipe.required'         => 'Jenis standar (Pelanggaran atau Prestasi) wajib dipilih.',
            'default_poin.required' => 'Poin default wajib ditentukan.',
            'default_poin.integer'  => 'Poin default harus berupa angka bulat (bisa negatif, 0, atau positif).',
        ]);

        $validated['is_active'] = $request->has('is_active') ? (bool) $request->is_active : false;

        $category->update($validated);

        return redirect()->route('discipline.master.index')
            ->with('success', "Master data '{$category->nama}' berhasil diperbarui. Histori catatan terdahulu tetap dipertahankan.");
    }

    /**
     * Toggle active/inactive status
     */
    public function toggleStatus($id)
    {
        $category = DisciplineCategory::findOrFail($id);
        $category->is_active = !$category->is_active;
        $category->save();

        $statusLabel = $category->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->route('discipline.master.index')
            ->with('success', "Master standar '{$category->nama}' berhasil {$statusLabel}.");
    }

    /**
     * Delete master item safely
     */
    public function destroy($id)
    {
        $category = DisciplineCategory::findOrFail($id);

        if ($category->isUsedInRecords()) {
            return redirect()->route('discipline.master.index')
                ->with('error', "Master data '{$category->nama}' tidak dapat dihapus permanen karena sudah tercatat dalam riwayat kedisiplinan/prestasi siswa. Silakan nonaktifkan master ini agar tidak muncul pada pencatatan baru.");
        }

        $nama = $category->nama;
        $category->delete();

        return redirect()->route('discipline.master.index')
            ->with('success', "Master standar '{$nama}' berhasil dihapus secara permanen.");
    }
}
