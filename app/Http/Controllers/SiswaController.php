<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\User;
use App\Models\Kelas;
use App\Exports\SiswaExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class SiswaController extends Controller
{
    public function index(Request $request)
    {
        $query = Siswa::with(['kelas', 'user']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'LIKE', '%' . $search . '%')
                  ->orWhere('nis', 'LIKE', '%' . $search . '%');
            });
        }

        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }

        $siswa = $query->orderBy('nama')->paginate(10)->appends($request->query());
        $kelas = Kelas::all();
        return view('siswa.index', compact('siswa', 'kelas') + ['search' => $request->search ?? '', 'kelas_id' => $request->kelas_id ?? '']);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nis' => 'required|unique:siswas,nis',
            'nama' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:L,P',
            'kelas_id' => 'required|exists:kelas,id',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
        ]);

        $user = User::create([
            'name' => $request->nama,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'siswa',
        ]);

        Siswa::create([
            'user_id' => $user->id,
            'nis' => $request->nis,
            'nama' => $request->nama,
            'jenis_kelamin' => $request->jenis_kelamin,
            'kelas_id' => $request->kelas_id,
        ]);

        return redirect()->back()->with('success', 'Data Siswa berhasil ditambahkan');
    }

    public function edit(Request $request)
    {
        $siswa = Siswa::with('user')->findOrFail($request->id);
        $kelas = Kelas::all();
        return view('siswa.edit', compact('siswa', 'kelas'));
    }

    public function update(Request $request, $id)
    {
        $siswa = Siswa::findOrFail($id);
        
        $request->validate([
            'nis' => ['required', Rule::unique('siswas', 'nis')->ignore($siswa->id)],
            'nama' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:L,P',
            'kelas_id' => 'required|exists:kelas,id',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($siswa->user_id)],
            'password' => 'nullable|min:6',
        ]);

        $userData = [
            'name' => $request->nama,
            'email' => $request->email,
        ];
        
        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        $siswa->user->update($userData);

        $siswa->update([
            'nis' => $request->nis,
            'nama' => $request->nama,
            'jenis_kelamin' => $request->jenis_kelamin,
            'kelas_id' => $request->kelas_id,
        ]);

        return redirect()->back()->with('success', 'Data Siswa berhasil diperbarui');
    }

    public function destroy($id)
    {
        $siswa = Siswa::findOrFail($id);
        if ($siswa->user) {
            $siswa->user->delete(); // This should cascade and delete Siswa as well based on migration
        } else {
            $siswa->delete();
        }

        return redirect()->back()->with('success', 'Data Siswa berhasil dihapus');
    }

    /**
     * Export siswa data to PDF
     */
    public function exportPdf(Request $request)
    {
        $query = Siswa::with(['kelas', 'user']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'LIKE', '%' . $search . '%')
                  ->orWhere('nis', 'LIKE', '%' . $search . '%');
            });
        }

        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }

        $siswa = $query->orderBy('nama')->get();
        $search = $request->search ?? '';
        $filterKelas = '';
        if ($request->filled('kelas_id')) {
            $kelas = Kelas::find($request->kelas_id);
            $filterKelas = $kelas ? $kelas->nama_kelas : '';
        }

        $pdf = Pdf::loadView('exports.siswa-pdf', compact('siswa', 'search', 'filterKelas'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('Data_Siswa.pdf');
    }

    /**
     * Export siswa data to Excel
     */
    public function exportExcel(Request $request)
    {
        return Excel::download(
            new SiswaExport($request->search ?? '', $request->kelas_id ?? ''),
            'Data_Siswa.xlsx'
        );
    }
}
