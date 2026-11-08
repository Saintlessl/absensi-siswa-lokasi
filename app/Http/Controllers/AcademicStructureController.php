<?php

namespace App\Http\Controllers;

use App\Models\Angkatan;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\StudentEnrollment;
use App\Models\TahunAjaran;
use App\Services\CohortManagementService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AcademicStructureController extends Controller
{
    protected CohortManagementService $cohortService;

    public function __construct(CohortManagementService $cohortService)
    {
        $this->cohortService = $cohortService;
    }

    /**
     * Level 1: Katalog Jurusan & Overview Global Struktur Akademik
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Jurusan::where('is_active', true);

        // Scope KAPROG to their assigned major
        if ($user && $user->role === 'kaprog') {
            $query->where('id', $user->jurusan_id);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
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

        // Section A: Summary Angkatan Aktif (Tingkat 10, 11, 12 at school level)
        $activeTahunAjaran = TahunAjaran::getActiveName();
        $baseYear = (int) explode('/', $activeTahunAjaran)[0];

        $activeCohortsSummary = [];
        foreach ([10, 11, 12] as $t) {
            $expectedEntryYear = $baseYear - ($t - 10);
            $matchedCohort = Angkatan::where('tahun_masuk', $expectedEntryYear)->first();

            $rombelCount = Kelas::where('tingkat', $t)->where('is_active', true)->count();
            $rombelIds = Kelas::where('tingkat', $t)->where('is_active', true)->pluck('id');
            $activeStudents = Siswa::whereIn('kelas_id', $rombelIds)->where('status', 'aktif')->count();
            $totalStudents = Siswa::whereIn('kelas_id', $rombelIds)->count();

            $activeCohortsSummary[] = (object) [
                'tingkat'         => $t,
                'nama_tingkat'    => "Kelas {$t}",
                'tahun_ajaran'    => $activeTahunAjaran,
                'angkatan'        => $matchedCohort,
                'rombel_count'    => $rombelCount,
                'siswa_aktif'     => $activeStudents,
                'total_terdaftar' => $totalStudents,
            ];
        }

        // Section B: Angkatan Alumni / Kelulusan (Global School Level)
        $alumniCohorts = Angkatan::where('status', 'selesai')
            ->orderByDesc('nomor_angkatan')
            ->get()
            ->map(function ($cohort) {
                $cohort->computed_stats = $this->cohortService->calculateStats($cohort);
                return $cohort;
            });

        return view('academic.index', [
            'jurusans'             => $jurusans,
            'search'               => $request->search ?? '',
            'activeCohortsSummary' => $activeCohortsSummary,
            'alumniCohorts'        => $alumniCohorts,
        ]);
    }

    /**
     * Level 2: Tingkat Kelas per Jurusan (10, 11, 12) + Section Angkatan Alumni
     */
    public function major(Request $request, Jurusan $jurusan)
    {
        $this->authorizeMajor($jurusan);

        // Determine base academic year
        $activeTahunAjaran = TahunAjaran::getActiveName();
        $baseYear = (int) explode('/', $activeTahunAjaran)[0];

        // Section A: ANGKATAN AKTIF (Major-Specific Cohort)
        $levelsData = [];
        foreach ([10, 11, 12] as $t) {
            $expectedEntryYear = $baseYear - ($t - 10);
            $matchedCohort = $jurusan->angkatans()
                ->where('tahun_masuk', $expectedEntryYear)
                ->first();

            if (!$matchedCohort) {
                $matchedCohort = Angkatan::where('jurusan_id', $jurusan->id)
                    ->where('status', 'aktif')
                    ->where('tahun_masuk', $expectedEntryYear)
                    ->first();
            }

            $rombelQuery = $jurusan->kelas()->where('tingkat', $t)->where('is_active', true);
            $rombelCount = $rombelQuery->count();
            $rombelIds = $rombelQuery->pluck('id');

            // Strictly count active students with status 'aktif'
            $activeStudents = Siswa::whereIn('kelas_id', $rombelIds)->where('status', 'aktif')->count();
            $totalStudents = Siswa::whereIn('kelas_id', $rombelIds)->count();

            $levelsData[] = (object) [
                'tingkat'         => $t,
                'nama_tingkat'    => "Kelas {$t}",
                'tahun_ajaran'    => $activeTahunAjaran,
                'angkatan'        => $matchedCohort,
                'rombel_count'    => $rombelCount,
                'siswa_aktif'     => $activeStudents,
                'student_count'   => $totalStudents,
            ];
        }

        // Section B: ANGKATAN ALUMNI / LULUS (Strictly Scoped to this Jurusan)
        $alumniQuery = $jurusan->angkatans()->where('status', 'selesai')->orderByDesc('nomor_angkatan');
        $alumniCohorts = $alumniQuery->get();

        if ($alumniCohorts->isEmpty()) {
            $alumniCohorts = Angkatan::where('status', 'selesai')
                ->where(function ($q) use ($jurusan) {
                    $q->where('jurusan_id', $jurusan->id)
                      ->orWhereNull('jurusan_id');
                })
                ->orderByDesc('nomor_angkatan')
                ->get();
        }

        $alumniCohorts = $alumniCohorts->map(function ($cohort) use ($jurusan) {
            $cohort->major_stats = $this->cohortService->calculateStatsForMajor($cohort, $jurusan->id);
            return $cohort;
        });

        return view('academic.major', compact('jurusan', 'levelsData', 'alumniCohorts', 'activeTahunAjaran'));
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
            $search = trim($request->search);
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

        $query = $kelas->siswa()->with(['user', 'orangtua', 'faceBiometric', 'angkatan']);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'LIKE', '%' . $search . '%')
                  ->orWhere('nis', 'LIKE', '%' . $search . '%')
                  ->orWhere('nisn', 'LIKE', '%' . $search . '%');
            });
        }

        $siswa = $query->orderBy('nama')->paginate(15)->appends($request->query());

        return view('academic.class', compact('kelas', 'siswa') + ['search' => $request->search ?? '']);
    }

    /**
     * Halaman Detail Angkatan Alumni (Global di tingkat sekolah)
     */
    public function alumniCohort(Request $request, Angkatan $angkatan)
    {
        return $this->renderAlumniView($request, $angkatan, null);
    }

    /**
     * Halaman Detail Angkatan Alumni (Scoped per Program Keahlian / Jurusan)
     */
    public function majorAlumni(Request $request, Jurusan $jurusan, Angkatan $angkatan)
    {
        $this->authorizeMajor($jurusan);
        return $this->renderAlumniView($request, $angkatan, $jurusan);
    }

    /**
     * Shared render logic for Alumni Detail View
     */
    protected function renderAlumniView(Request $request, Angkatan $angkatan, ?Jurusan $jurusan = null)
    {
        $targetJurusan = $jurusan;
        if (!$targetJurusan && $request->filled('jurusan_id')) {
            $targetJurusan = Jurusan::find($request->jurusan_id);
            if ($targetJurusan) {
                $this->authorizeMajor($targetJurusan);
            }
        }

        if ($targetJurusan) {
            $stats = $this->cohortService->calculateStatsForMajor($angkatan, $targetJurusan->id);
        } else {
            $stats = $this->cohortService->calculateStats($angkatan);
        }

        // Query students of this cohort
        $studentsQuery = Siswa::where('angkatan_id', $angkatan->id)
            ->with(['kelas.jurusan', 'latestEnrollment.kelas.jurusan', 'user', 'angkatan']);

        if ($targetJurusan) {
            $jId = $targetJurusan->id;
            $studentsQuery->where(function ($q) use ($jId) {
                $q->whereHas('kelas', fn($k) => $k->where('jurusan_id', $jId))
                  ->orWhereHas('latestEnrollment.kelas', fn($k) => $k->where('jurusan_id', $jId));
            });
        }

        // Filter status kelulusan
        if ($request->filled('status') && $request->status !== 'semua') {
            $studentsQuery->where('status', $request->status);
        }

        // Search name/nis/nisn
        if ($request->filled('search')) {
            $search = trim($request->search);
            $studentsQuery->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        // Filter rombel terakhir
        if ($request->filled('rombel_id')) {
            $rId = (int) $request->rombel_id;
            $studentsQuery->where(function ($q) use ($rId) {
                $q->where('kelas_id', $rId)
                  ->orWhereHas('latestEnrollment', fn($e) => $e->where('kelas_id', $rId));
            });
        }

        // Filter tahun kelulusan
        if ($request->filled('tahun_lulus')) {
            $studentsQuery->where('tahun_lulus', (int) $request->tahun_lulus);
        }

        $siswas = $studentsQuery->orderBy('nama')->paginate(20)->appends($request->query());

        // Get filter options
        $jurusans = Jurusan::where('is_active', true)->orderBy('nama_jurusan')->get();
        $rombels = Kelas::when($targetJurusan, fn($q) => $q->where('jurusan_id', $targetJurusan->id))
            ->orderBy('nama_kelas')
            ->get();

        return view('academic.alumni-detail', [
            'angkatan'      => $angkatan,
            'jurusan'       => $targetJurusan,
            'stats'         => $stats,
            'siswas'        => $siswas,
            'jurusans'      => $jurusans,
            'rombels'       => $rombels,
            'currentSearch' => $request->search ?? '',
            'currentStatus' => $request->status ?? 'semua',
            'currentRombel' => $request->rombel_id ?? '',
            'currentYear'   => $request->tahun_lulus ?? '',
        ]);
    }

    /**
     * Export Alumni to PDF
     */
    public function exportAlumniPdf(Request $request, Angkatan $angkatan)
    {
        $targetJurusan = $request->filled('jurusan_id') ? Jurusan::find($request->jurusan_id) : null;
        if ($targetJurusan) {
            $this->authorizeMajor($targetJurusan);
        }

        $query = Siswa::where('angkatan_id', $angkatan->id)
            ->with(['kelas.jurusan', 'latestEnrollment.kelas.jurusan']);

        if ($targetJurusan) {
            $jId = $targetJurusan->id;
            $query->where(function ($q) use ($jId) {
                $q->whereHas('kelas', fn($k) => $k->where('jurusan_id', $jId))
                  ->orWhereHas('latestEnrollment.kelas', fn($k) => $k->where('jurusan_id', $jId));
            });
        }

        if ($request->filled('status') && $request->status !== 'semua') {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        $students = $query->orderBy('nama')->get();

        $pdf = Pdf::loadView('exports.alumni-pdf', [
            'angkatan' => $angkatan,
            'jurusan'  => $targetJurusan,
            'students' => $students,
            'filter'   => [
                'status' => $request->status ?? 'semua',
                'search' => $request->search ?? '',
            ],
        ])->setPaper('a4', 'landscape');

        $filename = "Alumni_{$angkatan->kode_angkatan}" . ($targetJurusan ? "_{$targetJurusan->kode_jurusan}" : "") . ".pdf";
        return $pdf->download($filename);
    }

    private function authorizeMajor(?Jurusan $jurusan)
    {
        $user = Auth::user();
        if ($user && $user->role === 'kaprog') {
            abort_unless($jurusan && $user->jurusan_id === $jurusan->id, 403, 'Akses ditolak: Anda hanya berwenang mengelola Program Keahlian Anda sendiri.');
        }
    }
}
