<?php

namespace App\Http\Controllers;

use App\Exports\SiswaExport;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\OrangTua;
use App\Models\Siswa;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class SiswaController extends Controller
{
    public function index(Request $request)
    {
        $query = Siswa::with(['kelas.jurusan', 'user']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'LIKE', '%' . $search . '%')
                  ->orWhere('nis', 'LIKE', '%' . $search . '%');
            });
        }

        if ($request->filled('jurusan_id')) {
            $query->whereHas('kelas', function ($q) use ($request) {
                $q->where('jurusan_id', $request->jurusan_id);
            });
        }

        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }

        $siswa = $query->orderBy('nama')->paginate(10)->appends($request->query());
        $kelas = Kelas::with('jurusan')->orderBy('nama_kelas')->get();
        $jurusans = Jurusan::orderBy('kode_jurusan')->get();

        return view('siswa.index', compact('siswa', 'kelas', 'jurusans') + [
            'search'     => $request->search ?? '',
            'kelas_id'   => $request->kelas_id ?? '',
            'jurusan_id' => $request->jurusan_id ?? '',
        ]);
    }

    public function store(Request $request)
    {
        // Enforce fixed domain @myskanic.com for student accounts
        $rawUsername = strtolower(trim($request->username ?? $request->email ?? ''));
        if (str_contains($rawUsername, '@')) {
            $rawUsername = explode('@', $rawUsername)[0];
        }
        $rawUsername = preg_replace('/[^a-z0-9._-]/', '', $rawUsername);

        if (empty($rawUsername)) {
            return back()->with('error', 'Username email siswa wajib diisi (hanya huruf, angka, titik, strip, underscore).')->withInput();
        }

        $finalEmail = $rawUsername . '@myskanic.com';
        $request->merge([
            'email'    => $finalEmail,
            'username' => $rawUsername,
        ]);

        $request->validate([
            'nisn'             => 'nullable|string|max:20|unique:siswas,nisn',
            'nis'              => 'required|string|max:20|unique:siswas,nis',
            'nama'             => 'required|string|max:255',
            'jenis_kelamin'    => 'required|in:L,P',
            'kelas_id'         => 'required|exists:kelas,id',
            'no_telp_siswa'    => 'nullable|string|max:25',
            'no_telp_orangtua' => 'required|string|max:25',
            'email'            => 'required|email|unique:users,email',
            'password'         => 'required|min:6',
        ], [
            'email.unique'              => "Username '{$rawUsername}' sudah digunakan oleh siswa lain (@myskanic.com).",
            'no_telp_orangtua.required' => 'Nomor WhatsApp / telepon orang tua wajib diisi untuk notifikasi presensi.',
            'nis.unique'                => 'NIS sudah terdaftar.',
            'nisn.unique'               => 'NISN sudah terdaftar.',
        ]);

        DB::beginTransaction();
        try {
            $user = User::create([
                'name'     => $request->nama,
                'email'    => $finalEmail,
                'password' => Hash::make($request->password),
                'role'     => 'siswa',
            ]);

            $siswa = Siswa::create([
                'user_id'          => $user->id,
                'nisn'             => $request->nisn,
                'nis'              => $request->nis,
                'nama'             => $request->nama,
                'jenis_kelamin'    => $request->jenis_kelamin,
                'kelas_id'         => $request->kelas_id,
                'no_telp_siswa'    => $request->no_telp_siswa,
                'no_telp_orangtua' => $request->no_telp_orangtua,
                'biometric_status' => 'not_enrolled',
            ]);

            // Sync with orang_tuas table
            OrangTua::updateOrCreate(
                ['siswa_id' => $siswa->id],
                [
                    'nama'    => 'Orang Tua / Wali ' . $request->nama,
                    'telepon' => $request->no_telp_orangtua,
                    'alamat'  => '-',
                ]
            );

            DB::commit();
            return redirect()->back()->with('success', 'Data Siswa berhasil ditambahkan dengan akun ' . $finalEmail);
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menambahkan data siswa: ' . $e->getMessage())->withInput();
        }
    }

    public function edit(Request $request)
    {
        $siswa = Siswa::with(['user', 'kelas.jurusan'])->findOrFail($request->id);
        $kelas = Kelas::with('jurusan')->orderBy('nama_kelas')->get();
        $jurusans = Jurusan::orderBy('kode_jurusan')->get();
        return view('siswa.edit', compact('siswa', 'kelas', 'jurusans'));
    }

    public function update(Request $request, $id)
    {
        $siswa = Siswa::with('user')->findOrFail($id);

        // Enforce fixed domain @myskanic.com for student accounts
        $rawUsername = strtolower(trim($request->username ?? $request->email ?? ''));
        if (str_contains($rawUsername, '@')) {
            $rawUsername = explode('@', $rawUsername)[0];
        }
        $rawUsername = preg_replace('/[^a-z0-9._-]/', '', $rawUsername);

        if (empty($rawUsername)) {
            return back()->with('error', 'Username email siswa wajib diisi.')->withInput();
        }

        $finalEmail = $rawUsername . '@myskanic.com';
        $request->merge([
            'email'    => $finalEmail,
            'username' => $rawUsername,
        ]);

        $request->validate([
            'nisn'             => ['nullable', 'string', 'max:20', Rule::unique('siswas', 'nisn')->ignore($siswa->id)],
            'nis'              => ['required', 'string', 'max:20', Rule::unique('siswas', 'nis')->ignore($siswa->id)],
            'nama'             => 'required|string|max:255',
            'jenis_kelamin'    => 'required|in:L,P',
            'kelas_id'         => 'required|exists:kelas,id',
            'no_telp_siswa'    => 'nullable|string|max:25',
            'no_telp_orangtua' => 'required|string|max:25',
            'email'            => ['required', 'email', Rule::unique('users', 'email')->ignore($siswa->user_id)],
            'password'         => 'nullable|min:6',
        ], [
            'email.unique'              => "Username '{$rawUsername}' sudah digunakan oleh siswa lain (@myskanic.com).",
            'no_telp_orangtua.required' => 'Nomor WhatsApp / telepon orang tua wajib diisi untuk notifikasi presensi.',
        ]);

        DB::beginTransaction();
        try {
            $userData = [
                'name'  => $request->nama,
                'email' => $finalEmail,
            ];

            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }

            if ($siswa->user) {
                $siswa->user->update($userData);
            }

            $siswa->update([
                'nisn'             => $request->nisn,
                'nis'              => $request->nis,
                'nama'             => $request->nama,
                'jenis_kelamin'    => $request->jenis_kelamin,
                'kelas_id'         => $request->kelas_id,
                'no_telp_siswa'    => $request->no_telp_siswa,
                'no_telp_orangtua' => $request->no_telp_orangtua,
            ]);

            OrangTua::updateOrCreate(
                ['siswa_id' => $siswa->id],
                [
                    'telepon' => $request->no_telp_orangtua,
                ]
            );

            DB::commit();
            return redirect()->back()->with('success', 'Data Siswa berhasil diperbarui');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memperbarui data siswa: ' . $e->getMessage());
        }
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
