<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Siswa;
use App\Models\TeachingJournal;
use App\Models\TeachingJournalAttendance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TeachingJournalController extends Controller
{
    /**
     * Map English day name to Indonesian schedule day
     */
    private function getIndonesianDay(?Carbon $date = null): string
    {
        $dayNum = ($date ?? Carbon::now())->dayOfWeekIso; // 1 = Senin, 7 = Minggu
        $days = [
            1 => 'senin',
            2 => 'selasa',
            3 => 'rabu',
            4 => 'kamis',
            5 => 'jumat',
            6 => 'sabtu',
            7 => 'minggu',
        ];
        return $days[$dayNum] ?? 'senin';
    }

    /**
     * Teacher Journal Index
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $guru = Guru::where('user_id', $user->id)->first();

        if (!$guru) {
            return redirect()->route('dashboard')->with('error', 'Akun Anda tidak terhubung dengan profil guru.');
        }

        $today = Carbon::today();
        $todayDay = $this->getIndonesianDay($today);

        // Today's Schedules for this teacher
        $todaySchedules = Jadwal::with(['kelas.jurusan', 'mataPelajaran', 'room'])
            ->where('guru_id', $guru->id)
            ->where('hari', $todayDay)
            ->orderBy('jam_mulai')
            ->get();

        // Get schedule IDs that already have a journal today
        $filledScheduleIds = TeachingJournal::where('guru_id', $guru->id)
            ->whereDate('tanggal', $today->toDateString())
            ->pluck('jadwal_id')
            ->filter()
            ->toArray();

        // Historical Journals Query with Filter
        $query = TeachingJournal::with(['kelas', 'mataPelajaran', 'attendances'])
            ->where('guru_id', $guru->id);

        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal', $request->tanggal);
        }

        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }

        $journals = $query->latest('tanggal')->latest('id')->paginate(15)->withQueryString();

        // Distinct classes taught by this guru for filter dropdown
        $classes = Kelas::whereHas('jadwal', function ($q) use ($guru) {
            $q->where('guru_id', $guru->id);
        })->orderBy('nama_kelas')->get();

        return view('jurnal.index', compact('guru', 'todaySchedules', 'filledScheduleIds', 'journals', 'classes', 'today'));
    }

    /**
     * Form to create a new teaching journal
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        $guru = Guru::where('user_id', $user->id)->firstOrFail();

        $jadwalId = $request->query('jadwal_id');
        $kelasId = $request->query('kelas_id');
        $jadwal = null;
        $selectedKelas = null;
        $mapelName = '';
        $mapelId = null;

        if ($jadwalId) {
            $jadwal = Jadwal::with(['kelas', 'mataPelajaran', 'room'])
                ->where('guru_id', $guru->id)
                ->findOrFail($jadwalId);

            $selectedKelas = $jadwal->kelas;
            $mapelName = $jadwal->mataPelajaran ? $jadwal->mataPelajaran->nama_mapel : $jadwal->mapel;
            $mapelId = $jadwal->mata_pelajaran_id;
        } elseif ($kelasId) {
            $selectedKelas = Kelas::findOrFail($kelasId);
        }

        // Available classes taught by this teacher
        $classes = Kelas::whereHas('jadwal', function ($q) use ($guru) {
            $q->where('guru_id', $guru->id);
        })->orWhereHas('subjectAssignments', function ($q) use ($guru) {
            $q->where('guru_id', $guru->id)->where('is_active', true);
        })->distinct()->get();

        // Student Roster for session attendance checklist
        $students = collect();
        if ($selectedKelas) {
            $students = Siswa::where('kelas_id', $selectedKelas->id)
                ->orderBy('nama')
                ->get();
        }

        return view('jurnal.create', compact('guru', 'jadwal', 'selectedKelas', 'classes', 'students', 'mapelName', 'mapelId'));
    }

    /**
     * Store new teaching journal
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $guru = Guru::where('user_id', $user->id)->firstOrFail();

        $request->validate([
            'kelas_id'            => 'required|exists:kelas,id',
            'tanggal'             => 'required|date',
            'materi_pokok'        => 'required|string|max:255',
            'aktivitas'           => 'required|string',
            'tujuan_pembelajaran' => 'nullable|string',
            'elemen_capaian'      => 'nullable|string|max:255',
            'metode'              => 'nullable|string|max:255',
            'catatan'             => 'nullable|string',
            'kendala'             => 'nullable|string',
            'status'              => 'required|in:draft,submitted',
            'attendances'         => 'nullable|array',
            'attendances.*.status'=> 'required|in:hadir,terlambat,izin,sakit,alpa',
            'attendances.*.catatan'=> 'nullable|string|max:255',
        ]);

        // Guard against duplicate journal for the exact same teacher + schedule + date
        if ($request->filled('jadwal_id')) {
            $existing = TeachingJournal::where('guru_id', $guru->id)
                ->where('jadwal_id', $request->jadwal_id)
                ->whereDate('tanggal', $request->tanggal)
                ->first();

            if ($existing) {
                return redirect()->route('jurnal.show', $existing->id)
                    ->with('info', 'Jurnal untuk sesi jadwal dan tanggal ini sudah pernah dibuat.');
            }
        }

        DB::transaction(function () use ($request, $guru, &$journal) {
            $mapelId = $request->mata_pelajaran_id;
            $namaMapel = $request->nama_mapel;

            if ($request->filled('jadwal_id')) {
                $j = Jadwal::find($request->jadwal_id);
                if ($j) {
                    $mapelId = $j->mata_pelajaran_id ?? $mapelId;
                    $namaMapel = $j->mataPelajaran ? $j->mataPelajaran->nama_mapel : ($j->mapel ?? $namaMapel);
                }
            }

            $journal = TeachingJournal::create([
                'guru_id'            => $guru->id,
                'jadwal_id'          => $request->jadwal_id,
                'kelas_id'           => $request->kelas_id,
                'mata_pelajaran_id'  => $mapelId,
                'nama_mapel'         => $namaMapel ?? 'Mata Pelajaran',
                'tanggal'            => $request->tanggal,
                'jam_ke'             => $request->jam_ke ?? ($j->jam_mulai ?? '-') . ' - ' . ($j->jam_selesai ?? '-'),
                'materi_pokok'       => $request->materi_pokok,
                'tujuan_pembelajaran'=> $request->tujuan_pembelajaran,
                'elemen_capaian'     => $request->elemen_capaian,
                'aktivitas'          => $request->aktivitas,
                'metode'             => $request->metode,
                'catatan'            => $request->catatan,
                'kendala'            => $request->kendala,
                'status'             => $request->status,
            ]);

            // Save session attendance per student
            if ($request->has('attendances') && is_array($request->attendances)) {
                foreach ($request->attendances as $siswaId => $attData) {
                    TeachingJournalAttendance::create([
                        'teaching_journal_id' => $journal->id,
                        'siswa_id'            => $siswaId,
                        'status'              => $attData['status'] ?? 'hadir',
                        'catatan'             => $attData['catatan'] ?? null,
                    ]);
                }
            }
        });

        return redirect()->route('jurnal.show', $journal->id)
            ->with('success', 'Jurnal pembelajaran harian berhasil disimpan.');
    }

    /**
     * Show teaching journal detail
     */
    public function show($id)
    {
        $journal = TeachingJournal::with(['guru.user', 'kelas.jurusan', 'mataPelajaran', 'jadwal.room', 'attendances.siswa'])
            ->findOrFail($id);

        $user = Auth::user();

        // Authorization check: Guru owner, Admin/Superadmin, or KAPROG in same department
        if ($user->role === 'guru') {
            $guru = Guru::where('user_id', $user->id)->first();
            if (!$guru || $guru->id !== $journal->guru_id) {
                abort(403, 'Anda tidak memiliki hak akses melihat jurnal ini.');
            }
        } elseif ($user->role === 'kaprog') {
            if ($user->jurusan_id && $journal->kelas && $journal->kelas->jurusan_id !== $user->jurusan_id) {
                abort(403, 'Akses terbatas untuk jurusan Anda.');
            }
        }

        // Attendance Stats summary
        $stats = [
            'total'     => $journal->attendances->count(),
            'hadir'     => $journal->attendances->where('status', 'hadir')->count(),
            'terlambat' => $journal->attendances->where('status', 'terlambat')->count(),
            'izin'      => $journal->attendances->where('status', 'izin')->count(),
            'sakit'     => $journal->attendances->where('status', 'sakit')->count(),
            'alpa'      => $journal->attendances->where('status', 'alpa')->count(),
        ];

        return view('jurnal.show', compact('journal', 'stats'));
    }

    /**
     * Delete teaching journal
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $journal = TeachingJournal::findOrFail($id);

        if ($user->role === 'guru') {
            $guru = Guru::where('user_id', $user->id)->first();
            if (!$guru || $guru->id !== $journal->guru_id) {
                abort(403, 'Hanya pembuat jurnal atau admin yang dapat menghapus jurnal.');
            }
        }

        $journal->delete();

        return redirect()->route('jurnal.index')->with('success', 'Jurnal berhasil dihapus.');
    }

    /**
     * Admin & KAPROG Monitoring View
     */
    public function adminMonitoring(Request $request)
    {
        $user = Auth::user();

        $query = TeachingJournal::with(['guru.user', 'kelas.jurusan', 'mataPelajaran', 'attendances']);

        // KAPROG Scoping
        if ($user->role === 'kaprog' && $user->jurusan_id) {
            $query->whereHas('kelas', function ($q) use ($user) {
                $q->where('jurusan_id', $user->jurusan_id);
            });
        }

        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal', $request->tanggal);
        }

        if ($request->filled('guru_id')) {
            $query->where('guru_id', $request->guru_id);
        }

        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }

        $journals = $query->latest('tanggal')->latest('id')->paginate(20)->withQueryString();

        $gurus = Guru::where('is_active', true)->orderBy('nama')->get();
        $classes = Kelas::orderBy('nama_kelas')->get();

        return view('jurnal.monitoring', compact('journals', 'gurus', 'classes'));
    }
}
