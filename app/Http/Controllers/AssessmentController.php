<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\AssessmentCategory;
use App\Models\AssessmentScore;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Siswa;
use App\Services\InAppNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AssessmentController extends Controller
{
    /**
     * Teacher Assessment List
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $guru = Guru::where('user_id', $user->id)->first();

        if (!$guru) {
            return redirect()->route('dashboard')->with('error', 'Akun Anda tidak terhubung dengan profil guru.');
        }

        $query = Assessment::with(['kelas', 'category', 'scores'])
            ->where('guru_id', $guru->id);

        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }

        if ($request->filled('category_id')) {
            $query->where('assessment_category_id', $request->category_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $assessments = $query->latest('tanggal')->latest('id')->paginate(15)->withQueryString();

        $classes = Kelas::whereHas('jadwal', function ($q) use ($guru) {
            $q->where('guru_id', $guru->id);
        })->orWhereHas('subjectAssignments', function ($q) use ($guru) {
            $q->where('guru_id', $guru->id)->where('is_active', true);
        })->distinct()->orderBy('nama_kelas')->get();

        $categories = AssessmentCategory::where('is_active', true)->get();

        return view('gradebook.index', compact('guru', 'assessments', 'classes', 'categories'));
    }

    /**
     * Create Assessment Form
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        $guru = Guru::where('user_id', $user->id)->firstOrFail();

        $classes = Kelas::whereHas('jadwal', function ($q) use ($guru) {
            $q->where('guru_id', $guru->id);
        })->orWhereHas('subjectAssignments', function ($q) use ($guru) {
            $q->where('guru_id', $guru->id)->where('is_active', true);
        })->distinct()->orderBy('nama_kelas')->get();

        $categories = AssessmentCategory::where('is_active', true)->get();

        // Get subjects taught by this teacher
        $subjects = MataPelajaran::whereHas('subjectAssignments', function ($q) use ($guru) {
            $q->where('guru_id', $guru->id)->where('is_active', true);
        })->get();

        return view('gradebook.create', compact('guru', 'classes', 'categories', 'subjects'));
    }

    /**
     * Store Assessment
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $guru = Guru::where('user_id', $user->id)->firstOrFail();

        $request->validate([
            'kelas_id'               => 'required|exists:kelas,id',
            'assessment_category_id' => 'required|exists:assessment_categories,id',
            'judul'                  => 'required|string|max:255',
            'deskripsi'              => 'nullable|string',
            'tanggal'                => 'required|date',
            'tahun_ajaran'           => 'required|string|max:20',
            'semester'               => 'required|in:ganjil,genap',
            'skor_maksimal'          => 'required|numeric|min:1|max:1000',
            'bobot'                  => 'required|numeric|min:0.1|max:100',
            'status'                 => 'required|in:draft,finalized,published',
        ]);

        $kelas = Kelas::findOrFail($request->kelas_id);
        $mapel = MataPelajaran::find($request->mata_pelajaran_id);
        $namaMapel = $mapel ? $mapel->nama_mapel : ($request->nama_mapel ?? 'Mata Pelajaran');

        DB::transaction(function () use ($request, $guru, $kelas, $namaMapel, &$assessment) {
            $assessment = Assessment::create([
                'guru_id'                => $guru->id,
                'kelas_id'               => $kelas->id,
                'mata_pelajaran_id'      => $request->mata_pelajaran_id,
                'nama_mapel'             => $namaMapel,
                'assessment_category_id' => $request->assessment_category_id,
                'judul'                  => $request->judul,
                'deskripsi'              => $request->deskripsi,
                'tanggal'                => $request->tanggal,
                'tahun_ajaran'           => $request->tahun_ajaran,
                'semester'               => $request->semester,
                'skor_maksimal'          => $request->skor_maksimal,
                'bobot'                  => $request->bobot,
                'status'                 => $request->status,
            ]);

            // Initialize score rows for all students in the class
            $students = Siswa::where('kelas_id', $kelas->id)->get();
            foreach ($students as $st) {
                AssessmentScore::create([
                    'assessment_id' => $assessment->id,
                    'siswa_id'      => $st->id,
                    'nilai'         => null, // Null indicates not yet entered
                ]);
            }
        });

        return redirect()->route('gradebook.input', $assessment->id)
            ->with('success', 'Komponen penilaian berhasil dibuat. Silakan masukkan nilai siswa.');
    }

    /**
     * Input scores spreadsheet view
     */
    public function inputScores($id)
    {
        $user = Auth::user();
        $assessment = Assessment::with(['guru', 'kelas', 'category', 'scores.siswa'])
            ->findOrFail($id);

        if ($user->role === 'guru') {
            $guru = Guru::where('user_id', $user->id)->first();
            if (!$guru || $guru->id !== $assessment->guru_id) {
                abort(403, 'Anda tidak memiliki hak akses menginput nilai untuk asesmen ini.');
            }
        }

        $scores = $assessment->scores()->with('siswa')->get()->sortBy('siswa.nama');

        return view('gradebook.input', compact('assessment', 'scores'));
    }

    /**
     * Save student scores with distinction between null (unsubmitted) and 0.00
     */
    public function saveScores(Request $request, $id, InAppNotificationService $notifService)
    {
        $user = Auth::user();
        $assessment = Assessment::findOrFail($id);

        if ($user->role === 'guru') {
            $guru = Guru::where('user_id', $user->id)->first();
            if (!$guru || $guru->id !== $assessment->guru_id) {
                abort(403, 'Akses ditolak.');
            }
        }

        $request->validate([
            'scores'             => 'required|array',
            'scores.*.nilai'     => 'nullable|numeric|min:0|max:' . $assessment->skor_maksimal,
            'scores.*.catatan'   => 'nullable|string|max:255',
            'action'             => 'required|in:save,publish',
        ]);

        DB::transaction(function () use ($request, $assessment) {
            foreach ($request->scores as $siswaId => $data) {
                $rawVal = $data['nilai'] ?? null;
                $val = ($rawVal !== '' && $rawVal !== null) ? (float) $rawVal : null;

                AssessmentScore::updateOrCreate(
                    ['assessment_id' => $assessment->id, 'siswa_id' => $siswaId],
                    [
                        'nilai'   => $val,
                        'catatan' => $data['catatan'] ?? null,
                    ]
                );
            }

            if ($request->action === 'publish') {
                $assessment->update(['status' => 'published']);
            } elseif ($assessment->status === 'draft') {
                $assessment->update(['status' => 'finalized']);
            }
        });

        // If published, notify students
        if ($request->action === 'publish') {
            $students = Siswa::where('kelas_id', $assessment->kelas_id)->with('user')->get();
            foreach ($students as $st) {
                if ($st->user) {
                    $notifService->sendToUser(
                        $st->user,
                        "Nilai Baru: {$assessment->judul}",
                        "Nilai untuk {$assessment->nama_mapel} ({$assessment->judul}) telah dipublikasikan.",
                        'academic',
                        route('gradebook.student')
                    );
                }
            }
        }

        return redirect()->route('gradebook.input', $assessment->id)
            ->with('success', $request->action === 'publish'
                ? 'Nilai siswa berhasil disimpan dan dipublikasikan.'
                : 'Nilai siswa berhasil disimpan sebagai draf/final.');
    }

    /**
     * Publish Assessment
     */
    public function publish($id, InAppNotificationService $notifService)
    {
        $user = Auth::user();
        $assessment = Assessment::findOrFail($id);

        if ($user->role === 'guru') {
            $guru = Guru::where('user_id', $user->id)->first();
            if (!$guru || $guru->id !== $assessment->guru_id) {
                abort(403, 'Akses ditolak.');
            }
        }

        $assessment->update(['status' => 'published']);

        // Notify students
        $students = Siswa::where('kelas_id', $assessment->kelas_id)->with('user')->get();
        foreach ($students as $st) {
            if ($st->user) {
                $notifService->sendToUser(
                    $st->user,
                    "Nilai Baru: {$assessment->judul}",
                    "Nilai untuk {$assessment->nama_mapel} ({$assessment->judul}) telah dipublikasikan.",
                    'academic',
                    route('gradebook.student')
                );
            }
        }

        return back()->with('success', 'Nilai asesmen berhasil dipublikasikan kepada siswa & orang tua.');
    }

    /**
     * Student Gradebook Portal View
     */
    public function studentGradebook(Request $request)
    {
        $user = Auth::user();
        $siswa = Siswa::where('user_id', $user->id)->firstOrFail();

        // Only published assessments
        $scores = AssessmentScore::with(['assessment.category', 'assessment.guru'])
            ->where('siswa_id', $siswa->id)
            ->whereHas('assessment', function ($q) {
                $q->where('status', 'published');
            })
            ->latest()
            ->paginate(15);

        // Calculate average of graded items
        $gradedScores = $scores->whereNotNull('nilai');
        $average = $gradedScores->isNotEmpty() ? $gradedScores->avg('nilai') : null;

        return view('gradebook.student', compact('siswa', 'scores', 'average'));
    }

    /**
     * Parent Gradebook Portal View
     */
    public function parentGradebook(Request $request)
    {
        $user = Auth::user();
        $parent = $user->orangtua ?? \App\Models\OrangTua::where('user_id', $user->id)->firstOrFail();
        $siswa = $parent->siswa()->first();

        if (!$siswa) {
            return redirect()->route('dashboard')->with('error', 'Data siswa binaan belum terhubung.');
        }

        $scores = AssessmentScore::with(['assessment.category', 'assessment.guru'])
            ->where('siswa_id', $siswa->id)
            ->whereHas('assessment', function ($q) {
                $q->where('status', 'published');
            })
            ->latest()
            ->paginate(15);

        $gradedScores = $scores->whereNotNull('nilai');
        $average = $gradedScores->isNotEmpty() ? $gradedScores->avg('nilai') : null;

        return view('gradebook.student', compact('siswa', 'scores', 'average'));
    }

    /**
     * Delete Assessment
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $assessment = Assessment::findOrFail($id);

        if ($user->role === 'guru') {
            $guru = Guru::where('user_id', $user->id)->first();
            if (!$guru || $guru->id !== $assessment->guru_id) {
                abort(403, 'Akses ditolak.');
            }
        }

        $assessment->delete();

        return redirect()->route('gradebook.index')->with('success', 'Komponen penilaian berhasil dihapus.');
    }
}
