<?php

namespace App\Http\Controllers;

use App\Helpers\SettingHelper;
use App\Models\Absensi;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\SuratPeringatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SuratPeringatanController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['admin', 'guru'])) {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak.');
        }

        $query = SuratPeringatan::with('siswa.kelas')->orderBy('created_at', 'desc');

        if ($request->filled('jenis_sp')) {
            $query->where('jenis_sp', $request->jenis_sp);
        }
        if ($request->filled('id_kelas')) {
            $query->whereHas('siswa', function ($q) use ($request) {
                $q->where('kelas_id', $request->id_kelas);
            });
        }
        if ($request->filled('search')) {
            $query->whereHas('siswa', function ($q) use ($request) {
                $q->where('nama', 'LIKE', '%' . $request->search . '%')
                  ->orWhere('nis', 'LIKE', '%' . $request->search . '%');
            });
        }

        $suratPeringatans = $query->paginate(10)->appends($request->query());
        $kelasList = Kelas::orderBy('nama_kelas')->get();

        return view('surat-peringatan.index', compact('suratPeringatans', 'kelasList'));
    }

    /**
     * Manually issue an SP to a student.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['admin', 'guru'])) {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak.');
        }

        $request->validate([
            'siswa_id' => 'required|exists:siswas,id',
            'jenis_sp' => 'required|in:SP1,SP2,SP3',
            'keterangan' => 'nullable|string|max:500',
        ]);

        // Check if this SP type already exists for the student
        $exists = SuratPeringatan::where('siswa_id', $request->siswa_id)
            ->where('jenis_sp', $request->jenis_sp)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', "Surat {$request->jenis_sp} sudah pernah diterbitkan untuk siswa ini.");
        }

        // BUG-042: check-then-create race -> unique violation 500. Wrap and
        // catch duplicate-key errors into a friendly message.
        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($request, $user) {
                SuratPeringatan::create([
                    'siswa_id' => $request->siswa_id,
                    'nomor_surat' => $request->jenis_sp . '/' . date('Ymd') . '/' . $request->siswa_id,
                    'jenis_sp' => $request->jenis_sp,
                    'tanggal_terbit' => date('Y-m-d'),
                    'keterangan' => $request->keterangan ?? "Diterbitkan manual oleh " . ucfirst($user->role),
                ]);
            });
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->back()->with('error', "Surat {$request->jenis_sp} sudah pernah diterbitkan untuk siswa ini.");
        }

        return redirect()->back()->with('success', "Surat Peringatan {$request->jenis_sp} berhasil diterbitkan.");
    }

    /**
     * Delete an SP record.
     */
    public function destroy(int $id)
    {
        $user = Auth::user();
        if ($user->role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak.');
        }

        $sp = SuratPeringatan::findOrFail($id);
        $sp->delete();

        return redirect()->back()->with('success', 'Surat Peringatan berhasil dihapus.');
    }

    /**
     * Print / Preview a single SP letter.
     */
    public function cetak(int $id)
    {
        $user = Auth::user();
        $sp = SuratPeringatan::with('siswa.kelas')->findOrFail($id);

        // Access Control
        if ($user->role == 'siswa') {
            $siswa = Siswa::where('user_id', $user->id)->first();
            if (!$siswa || $sp->siswa_id != $siswa->id) {
                return redirect()->route('dashboard')->with('error', 'Akses ditolak.');
            }
        } elseif ($user->role == 'orangtua') {
            $orangTua = \App\Models\OrangTua::where('user_id', $user->id)->first();
            if (!$orangTua || $sp->siswa_id != $orangTua->siswa_id) {
                return redirect()->route('dashboard')->with('error', 'Akses ditolak.');
            }
        } elseif (!in_array($user->role, ['admin', 'guru'])) {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak.');
        }
        $namaSekolah = SettingHelper::get('nama_sekolah', 'SMP IT Yapura');
        $kepalaSekolah = SettingHelper::get('kepala_sekolah', '.....................');
        $nipKepsek = SettingHelper::get('nip_kepsek', '');

        // Count alpha for the month this SP was issued
        $month = date('m', strtotime($sp->tanggal_terbit));
        $year = date('Y', strtotime($sp->tanggal_terbit));
        $alfaCount = $sp->siswa->getTotalAlpha($month, $year);

        return view('surat-peringatan.cetak', compact('sp', 'namaSekolah', 'kepalaSekolah', 'nipKepsek', 'alfaCount'));
    }
}
