<?php

namespace App\Http\Controllers;

use App\Models\Angkatan;
use App\Models\Jurusan;
use App\Models\Siswa;
use App\Services\CohortManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AngkatanController extends Controller
{
    protected CohortManagementService $cohortService;

    public function __construct(CohortManagementService $cohortService)
    {
        $this->cohortService = $cohortService;
    }

    /**
     * Display listing of Angkatans with filter and statistics.
     */
    public function index(Request $request)
    {
        $query = Angkatan::query();

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('nama_angkatan', 'like', "%{$search}%")
                  ->orWhere('kode_angkatan', 'like', "%{$search}%")
                  ->orWhere('nomor_angkatan', 'like', "%{$search}%")
                  ->orWhere('tahun_masuk', 'like', "%{$search}%")
                  ->orWhere('tahun_ajaran_masuk', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status') && $request->status !== 'semua') {
            $query->where('status', $request->status);
        }

        if ($request->filled('jurusan_id') && $request->jurusan_id !== 'semua') {
            $query->where('jurusan_id', $request->jurusan_id);
        }

        $angkatans = $query->with('jurusan')->orderByDesc('nomor_angkatan')->get();

        // Attach dynamic calculated stats to each cohort
        $angkatansWithStats = $angkatans->map(function ($angkatan) {
            $stats = $this->cohortService->calculateStats($angkatan);
            $angkatan->computed_stats = $stats;
            return $angkatan;
        });

        $jurusans = Jurusan::where('is_active', true)->orderBy('nama_jurusan')->get();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'data'    => $angkatansWithStats,
            ]);
        }

        return view('angkatan.index', [
            'angkatans'  => $angkatansWithStats,
            'jurusans'   => $jurusans,
            'jurusan_id' => $request->jurusan_id ?? 'semua',
            'search'     => $request->search ?? '',
            'status'     => $request->status ?? 'semua',
        ]);
    }

    /**
     * Store a newly created Angkatan.
     */
    public function store(Request $request)
    {
        $jurusanId = $request->jurusan_id ?: DB::table('jurusans')->value('id');

        $validated = $request->validate([
            'jurusan_id'             => 'nullable|exists:jurusans,id',
            'nomor_angkatan'         => [
                'required',
                'integer',
                'min:1',
                Rule::unique('angkatans', 'nomor_angkatan')->where(function ($q) use ($jurusanId) {
                    return $q->where('jurusan_id', $jurusanId);
                }),
            ],
            'nama_angkatan'          => 'nullable|string|max:100',
            'kode_angkatan'          => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('angkatans', 'kode_angkatan')->where(function ($q) use ($jurusanId) {
                    return $q->where('jurusan_id', $jurusanId);
                }),
            ],
            'tahun_masuk'            => [
                'required',
                'integer',
                'min:2000',
                'max:2099',
                Rule::unique('angkatans', 'tahun_masuk')->where(function ($q) use ($jurusanId) {
                    return $q->where('jurusan_id', $jurusanId);
                }),
            ],
            'tahun_ajaran_masuk'     => 'nullable|string|max:20',
            'tahun_lulus_rencana'    => 'nullable|integer|gte:tahun_masuk',
            'tahun_ajaran_kelulusan' => 'nullable|string|max:20',
            'status'                 => 'nullable|in:aktif,proses_kelulusan,selesai',
            'tanggal_kelulusan'      => 'nullable|date',
            'keterangan'             => 'nullable|string|max:500',
        ]);

        if ($jurusanId) {
            $validated['jurusan_id'] = $jurusanId;
        }

        $currentUserId = Auth::id();
        $angkatan = $this->cohortService->createCohort($validated, $currentUserId);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Data {$angkatan->nama_angkatan} berhasil ditambahkan.",
                'data'    => $angkatan,
            ], 201);
        }

        return redirect()->back()->with('success', "Data {$angkatan->nama_angkatan} berhasil ditambahkan.");
    }

    /**
     * Display the specified Angkatan with its students.
     */
    public function show(Request $request, $id)
    {
        $angkatan = Angkatan::findOrFail($id);
        $stats = $this->cohortService->calculateStats($angkatan);

        $studentsQuery = Siswa::where('angkatan_id', $angkatan->id)
            ->with(['kelas.jurusan', 'user', 'latestStatusHistory']);

        if ($request->filled('status') && $request->status !== 'semua') {
            $studentsQuery->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $studentsQuery->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        $siswas = $studentsQuery->orderBy('nama')->paginate(20)->appends($request->query());

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'  => true,
                'angkatan' => $angkatan,
                'stats'    => $stats,
                'siswas'   => $siswas,
            ]);
        }

        return view('angkatan.show', compact('angkatan', 'stats', 'siswas'));
    }

    /**
     * Show edit form HTML modal for Angkatan.
     */
    public function edit(Request $request, $id = null)
    {
        $cohortId = $id ?? $request->id;
        $angkatan = Angkatan::findOrFail($cohortId);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'  => true,
                'angkatan' => $angkatan,
            ]);
        }

        return view('angkatan.edit', compact('angkatan'));
    }

    /**
     * Update the specified Angkatan.
     */
    public function update(Request $request, $id)
    {
        $angkatan = Angkatan::findOrFail($id);
        $jurusanId = $request->jurusan_id ?: $angkatan->jurusan_id;

        $validated = $request->validate([
            'jurusan_id'             => 'nullable|exists:jurusans,id',
            'nomor_angkatan'         => [
                'required',
                'integer',
                'min:1',
                Rule::unique('angkatans', 'nomor_angkatan')
                    ->where(fn($q) => $q->where('jurusan_id', $jurusanId))
                    ->ignore($angkatan->id),
            ],
            'nama_angkatan'          => 'nullable|string|max:100',
            'kode_angkatan'          => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('angkatans', 'kode_angkatan')
                    ->where(fn($q) => $q->where('jurusan_id', $jurusanId))
                    ->ignore($angkatan->id),
            ],
            'tahun_masuk'            => [
                'required',
                'integer',
                'min:2000',
                'max:2099',
                Rule::unique('angkatans', 'tahun_masuk')
                    ->where(fn($q) => $q->where('jurusan_id', $jurusanId))
                    ->ignore($angkatan->id),
            ],
            'tahun_ajaran_masuk'     => 'nullable|string|max:20',
            'tahun_lulus_rencana'    => 'nullable|integer|gte:tahun_masuk',
            'tahun_ajaran_kelulusan' => 'nullable|string|max:20',
            'status'                 => 'nullable|in:aktif,proses_kelulusan,selesai',
            'tanggal_kelulusan'      => 'nullable|date',
            'keterangan'             => 'nullable|string|max:500',
        ]);

        if ($jurusanId) {
            $validated['jurusan_id'] = $jurusanId;
        }

        $updated = $this->cohortService->updateCohort($angkatan, $validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Data {$updated->nama_angkatan} berhasil diperbarui.",
                'data'    => $updated,
            ]);
        }

        return redirect()->back()->with('success', "Data {$updated->nama_angkatan} berhasil diperbarui.");
    }

    /**
     * Remove the specified Angkatan with relational integrity guard.
     */
    public function destroy(Request $request, $id)
    {
        $angkatan = Angkatan::findOrFail($id);
        $nama = $angkatan->nama_angkatan;

        try {
            $this->cohortService->deleteCohort($angkatan);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Data {$nama} berhasil dihapus.",
                ]);
            }

            return redirect()->back()->with('success', "Data {$nama} berhasil dihapus.");
        } catch (ValidationException $e) {
            $message = $e->validator->errors()->first('angkatan');

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 422);
            }

            return redirect()->back()->with('error', $message);
        }
    }

    /**
     * Real-time statistics endpoint for an Angkatan.
     */
    public function stats($id): JsonResponse
    {
        $angkatan = Angkatan::findOrFail($id);
        $stats = $this->cohortService->calculateStats($angkatan);

        return response()->json([
            'success' => true,
            'data'    => $stats,
        ]);
    }
}
