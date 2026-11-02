<?php

namespace App\Http\Controllers;

use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AcademicStructureController extends Controller
{
    /**
     * Level 1: Katalog Jurusan
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Jurusan::where('is_active', true);

        // Scope KAPROG to their assigned major
        if ($user->role === 'kaprog') {
            $query->where('id', $user->jurusan_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('kode_jurusan', 'LIKE', '%' . $search . '%')
                  ->orWhere('nama_jurusan', 'LIKE', '%' . $search . '%');
            });
        }

        $jurusans = $query->withCount(['kelas', 'siswa'])->orderBy('kode_jurusan')->get();

        // Calculate count of distinct tingkat for each major
        foreach ($jurusans as $j) {
            $j->tingkat_count = $j->kelas()->distinct('tingkat')->whereNotNull('tingkat')->count('tingkat');
        }

        return view('academic.index', compact('jurusans') + ['search' => $request->search ?? '']);
    }

    /**
     * Level 2: Tingkat Kelas per Jurusan (10, 11, 12)
     */
    public function major(Request $request, Jurusan $jurusan)
    {
        $this->authorizeMajor($jurusan);

        $levelsData = [];
        foreach ([10, 11, 12] as $t) {
            $rombelQuery = $jurusan->kelas()->where('tingkat', $t)->where('is_active', true);
            $rombelCount = $rombelQuery->count();
            $rombelIds = $rombelQuery->pluck('id');
            $studentCount = Siswa::whereIn('kelas_id', $rombelIds)->count();

            $levelsData[] = (object) [
                'tingkat'       => $t,
                'nama_tingkat'  => "Kelas {$t}",
                'rombel_count'  => $rombelCount,
                'student_count' => $studentCount,
            ];
        }

        return view('academic.major', compact('jurusan', 'levelsData'));
    }

    /**
     * Level 3: Rombongan Belajar per Tingkat
     */
    public function level(Request $request, Jurusan $jurusan, int $tingkat)
    {
        $this->authorizeMajor($jurusan);
        abort_unless(in_array($tingkat, [10, 11, 12]), 404);

        $query = $jurusan->kelas()->with('jurusan')->withCount('siswa')->where('tingkat', $tingkat)->where('is_active', true);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_kelas', 'LIKE', '%' . $search . '%')
                  ->orWhere('wali_kelas', 'LIKE', '%' . $search . '%');
            });
        }

        $kelas = $query->orderBy('nomor_rombel')->get();

        return view('academic.level', compact('jurusan', 'tingkat', 'kelas') + ['search' => $request->search ?? '']);
    }

    /**
     * Level 4: Siswa per Rombongan Belajar
     */
    public function roomClass(Request $request, Kelas $kelas)
    {
        $kelas->load(['jurusan', 'roomAssignments.room']);
        $this->authorizeMajor($kelas->jurusan);

        $query = $kelas->siswa()->with(['user', 'orangtua', 'faceBiometric']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'LIKE', '%' . $search . '%')
                  ->orWhere('nis', 'LIKE', '%' . $search . '%')
                  ->orWhere('nisn', 'LIKE', '%' . $search . '%');
            });
        }

        $siswa = $query->orderBy('nama')->paginate(15)->appends($request->query());

        return view('academic.class', compact('kelas', 'siswa') + ['search' => $request->search ?? '']);
    }

    private function authorizeMajor(?Jurusan $jurusan)
    {
        $user = Auth::user();
        if ($user && $user->role === 'kaprog') {
            abort_unless($jurusan && $user->jurusan_id === $jurusan->id, 403, 'Akses ditolak: Anda hanya berwenang mengelola Program Keahlian Anda sendiri.');
        }
    }
}
