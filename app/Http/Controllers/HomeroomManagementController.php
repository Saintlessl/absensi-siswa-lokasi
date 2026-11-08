<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\HomeroomAssignment;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\TahunAjaran;
use App\Services\TeacherAssignmentService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class HomeroomManagementController extends Controller
{
    protected TeacherAssignmentService $assignmentService;

    public function __construct(TeacherAssignmentService $assignmentService)
    {
        $this->assignmentService = $assignmentService;
    }

    /**
     * Central Homeroom Management Dashboard & Matrix.
     */
    public function index(Request $request)
    {
        // 1. Resolve Academic Year filter (default to active)
        $tahunAjarans = TahunAjaran::orderByDesc('tahun_mulai')->get();
        $activeTa = TahunAjaran::getActive() ?? $tahunAjarans->first();

        $selectedTaId = $request->filled('tahun_ajaran_id')
            ? (int) $request->tahun_ajaran_id
            : ($activeTa?->id);

        $selectedTa = $tahunAjarans->firstWhere('id', $selectedTaId) ?? $activeTa;
        $taNama = $selectedTa ? $selectedTa->nama : '2026/2027';

        // 2. Query Classes for the selected academic year
        $kelasQuery = Kelas::with([
            'jurusan',
            'homeroomAssignments' => function ($q) use ($selectedTaId, $taNama) {
                $q->where(function ($sub) use ($selectedTaId, $taNama) {
                    if ($selectedTaId) {
                        $sub->where('tahun_ajaran_id', $selectedTaId);
                    } else {
                        $sub->where('tahun_ajaran', $taNama);
                    }
                })->with('guru.user')->orderByDesc('is_active')->orderByDesc('starts_on');
            }
        ]);

        // Filter by Jurusan
        if ($request->filled('jurusan_id')) {
            $kelasQuery->where('jurusan_id', $request->jurusan_id);
        }

        // Kaprog scoping
        if (Auth::user()->role === 'kaprog' && Auth::user()->jurusan_id) {
            $kelasQuery->where('jurusan_id', Auth::user()->jurusan_id);
        }

        // Filter by Tingkat
        if ($request->filled('tingkat')) {
            $kelasQuery->where('tingkat', $request->tingkat);
        }

        // Filter by Search (Rombel name or teacher name)
        if ($request->filled('search')) {
            $search = trim($request->search);
            $kelasQuery->where(function ($q) use ($search) {
                $q->where('nama_kelas', 'like', "%{$search}%")
                  ->orWhere('wali_kelas', 'like', "%{$search}%")
                  ->orWhereHas('jurusan', fn($qJ) => $qJ->where('kode_jurusan', 'like', "%{$search}%")->orWhere('nama_jurusan', 'like', "%{$search}%"));
            });
        }

        $allKelas = $kelasQuery->orderBy('tingkat')->orderBy('nama_kelas')->get();

        // 3. Attach active assignment for the selected academic year to each class
        $kelasList = $allKelas->map(function ($kelas) use ($selectedTaId, $taNama) {
            $activeAssignment = $kelas->homeroomAssignments->firstWhere('is_active', true);
            $kelas->current_homeroom_assignment = $activeAssignment;
            $kelas->has_homeroom = !empty($activeAssignment);
            return $kelas;
        });

        // Filter by Assignment Status (assigned vs unassigned)
        if ($request->filled('status_filter')) {
            if ($request->status_filter === 'assigned') {
                $kelasList = $kelasList->where('has_homeroom', true);
            } elseif ($request->status_filter === 'unassigned') {
                $kelasList = $kelasList->where('has_homeroom', false);
            }
        }

        // 4. Metrics calculation
        $totalRombel = $allKelas->count();
        $rombelAssigned = $allKelas->where('has_homeroom', true)->count();
        $rombelUnassigned = $totalRombel - $rombelAssigned;

        // 5. Active Teachers for selection modal (with their current homeroom status in this TA)
        $assignedTeacherIds = HomeroomAssignment::where('is_active', true)
            ->where(function ($q) use ($selectedTaId, $taNama) {
                if ($selectedTaId) {
                    $q->where('tahun_ajaran_id', $selectedTaId);
                } else {
                    $q->where('tahun_ajaran', $taNama);
                }
            })
            ->pluck('guru_id')
            ->all();

        $activeGurus = Guru::where('is_active', true)
            ->with(['user', 'homeroomAssignments' => function ($q) use ($selectedTaId, $taNama) {
                $q->where('is_active', true)->where(function ($sub) use ($selectedTaId, $taNama) {
                    if ($selectedTaId) {
                        $sub->where('tahun_ajaran_id', $selectedTaId);
                    } else {
                        $sub->where('tahun_ajaran', $taNama);
                    }
                })->with('kelas');
            }])
            ->orderBy('nama')
            ->get();

        $jurusans = Jurusan::where('is_active', true)->orderBy('nama_jurusan')->get();

        return view('academic.wali-kelas.index', [
            'kelasList'          => $kelasList,
            'tahunAjarans'       => $tahunAjarans,
            'selectedTa'         => $selectedTa,
            'jurusans'           => $jurusans,
            'activeGurus'        => $activeGurus,
            'assignedTeacherIds' => $assignedTeacherIds,
            'totalRombel'        => $totalRombel,
            'rombelAssigned'     => $rombelAssigned,
            'rombelUnassigned'   => $rombelUnassigned,
            'filters'            => [
                'tahun_ajaran_id' => $selectedTaId,
                'jurusan_id'      => $request->jurusan_id ?? '',
                'tingkat'         => $request->tingkat ?? '',
                'search'          => $request->search ?? '',
                'status_filter'   => $request->status_filter ?? '',
            ],
        ]);
    }

    /**
     * Store new homeroom assignment.
     */
    public function assign(Request $request)
    {
        $request->validate([
            'guru_id'         => 'required|exists:gurus,id',
            'kelas_id'        => 'required|exists:kelas,id',
            'tahun_ajaran_id' => 'required|exists:tahun_ajarans,id',
            'catatan'         => 'nullable|string|max:500',
        ]);

        $ta = TahunAjaran::findOrFail($request->tahun_ajaran_id);

        try {
            $this->assignmentService->assignHomeroom(
                (int) $request->guru_id,
                (int) $request->kelas_id,
                $ta->id,
                $request->catatan
            );

            return redirect()->back()->with('success', 'Penugasan wali kelas berhasil disimpan. Snapshot dan status aktif telah diperbarui.');
        } catch (ValidationException $e) {
            return redirect()->back()->with('error', $e->validator->errors()->first());
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menetapkan wali kelas: ' . $e->getMessage());
        }
    }

    /**
     * Replace existing homeroom teacher with atomic deactivation of previous teacher and history preservation.
     */
    public function replace(Request $request)
    {
        $request->validate([
            'kelas_id'          => 'required|exists:kelas,id',
            'new_guru_id'       => 'required|exists:gurus,id',
            'tahun_ajaran_id'   => 'required|exists:tahun_ajarans,id',
            'alasan_pergantian' => 'required|string|max:500',
            'effective_date'    => 'nullable|date',
        ], [
            'alasan_pergantian.required' => 'Alasan pergantian wali kelas wajib diisi.',
            'new_guru_id.required'       => 'Pilih guru pengganti.',
        ]);

        $ta = TahunAjaran::findOrFail($request->tahun_ajaran_id);
        $effDate = $request->effective_date ? Carbon::parse($request->effective_date) : Carbon::today();

        try {
            $this->assignmentService->replaceHomeroom(
                (int) $request->kelas_id,
                (int) $request->new_guru_id,
                $ta->id,
                $request->alasan_pergantian,
                $effDate
            );

            return redirect()->back()->with('success', 'Pergantian wali kelas berhasil dilakukan. Penugasan guru lama resmi dinonaktifkan dengan histori tersimpan utuh.');
        } catch (ValidationException $e) {
            return redirect()->back()->with('error', $e->validator->errors()->first());
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengganti wali kelas: ' . $e->getMessage());
        }
    }

    /**
     * Terminate / Revoke homeroom assignment without successor (e.g. resigned, retired).
     */
    public function terminate(Request $request, $assignmentId)
    {
        $request->validate([
            'alasan' => 'required|string|max:500',
        ], [
            'alasan.required' => 'Alasan penonaktifan penugasan wajib diisi.',
        ]);

        try {
            $this->assignmentService->terminateHomeroomAssignment((int) $assignmentId, $request->alasan);

            return redirect()->back()->with('success', 'Penugasan wali kelas telah diakhiri. Histori penugasan tetap tersimpan.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengakhiri penugasan: ' . $e->getMessage());
        }
    }

    /**
     * Fetch complete homeroom history for a class or teacher (JSON for Modal inspection).
     */
    public function history(Request $request)
    {
        if ($request->filled('kelas_id')) {
            $kelas = Kelas::with('jurusan')->findOrFail($request->kelas_id);
            $history = HomeroomAssignment::with(['guru', 'tahunAjaran', 'replacedBy.guru'])
                ->where('kelas_id', $kelas->id)
                ->orderByDesc('id')
                ->get()
                ->map(fn($h) => [
                    'id'                => $h->id,
                    'guru_nama'         => $h->guru?->nama ?? '-',
                    'guru_nip'          => $h->guru?->nip ?? '-',
                    'tahun_ajaran'      => $h->tahunAjaran?->nama ?? $h->tahun_ajaran,
                    'starts_on'         => $h->starts_on ? $h->starts_on->format('d/m/Y') : '-',
                    'ends_on'           => $h->ends_on ? $h->ends_on->format('d/m/Y') : '-',
                    'is_active'         => $h->is_active,
                    'alasan_pergantian' => $h->alasan_pergantian,
                    'catatan'           => $h->catatan,
                    'replaced_by_nama'  => $h->replacedBy?->guru?->nama,
                ]);

            return response()->json([
                'success' => true,
                'title'   => "Riwayat Wali Kelas: {$kelas->nama_kelas}",
                'type'    => 'kelas',
                'data'    => $history,
            ]);
        }

        if ($request->filled('guru_id')) {
            $guru = Guru::findOrFail($request->guru_id);
            $history = HomeroomAssignment::with(['kelas.jurusan', 'tahunAjaran'])
                ->where('guru_id', $guru->id)
                ->orderByDesc('id')
                ->get()
                ->map(fn($h) => [
                    'id'                => $h->id,
                    'nama_kelas'        => $h->kelas?->nama_kelas ?? '-',
                    'jurusan'           => $h->kelas?->jurusan?->kode_jurusan ?? '-',
                    'tahun_ajaran'      => $h->tahunAjaran?->nama ?? $h->tahun_ajaran,
                    'starts_on'         => $h->starts_on ? $h->starts_on->format('d/m/Y') : '-',
                    'ends_on'           => $h->ends_on ? $h->ends_on->format('d/m/Y') : '-',
                    'is_active'         => $h->is_active,
                    'alasan_pergantian' => $h->alasan_pergantian,
                    'catatan'           => $h->catatan,
                ]);

            return response()->json([
                'success' => true,
                'title'   => "Riwayat Penugasan Wali Kelas: {$guru->nama}",
                'type'    => 'guru',
                'data'    => $history,
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Parameter tidak lengkap.'], 400);
    }
}
