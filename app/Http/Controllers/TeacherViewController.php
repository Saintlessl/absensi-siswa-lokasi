<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Services\TeacherScheduleService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeacherViewController extends Controller
{
    public function __construct(protected TeacherScheduleService $scheduleService)
    {
    }

    /**
     * Get the authenticated teacher profile.
     */
    protected function getAuthGuru(): Guru
    {
        $user = Auth::user();
        $guru = Guru::with(['activeHomeroomAssignment.kelas.jurusan', 'activeSubjectAssignments.kelas.jurusan'])
            ->where('user_id', $user->id)
            ->first();

        if (!$guru) {
            abort(403, 'Akses ditolak: Profil guru tidak ditemukan untuk akun ini.');
        }

        return $guru;
    }

    /**
     * View classes and subjects taught by this teacher.
     */
    public function myClasses(Request $request)
    {
        $guru = $this->getAuthGuru();

        // Distinct classes taught via subject assignments or schedules
        $assignedClassIds = $guru->activeSubjectAssignments()->pluck('kelas_id')
            ->merge($guru->activeJadwal()->pluck('kelas_id'))
            ->unique();

        $classes = Kelas::with(['jurusan', 'roomAssignments.room'])
            ->withCount('siswa')
            ->whereIn('id', $assignedClassIds)
            ->orderBy('nama_kelas')
            ->get();

        // Resolve active subjects for each class
        foreach ($classes as $c) {
            $c->my_subjects = $guru->activeSubjectAssignments()
                ->where('kelas_id', $c->id)
                ->with('mataPelajaran')
                ->get()
                ->pluck('mataPelajaran.nama_mapel')
                ->unique();
        }

        return view('teacher.classes', [
            'guru'    => $guru,
            'classes' => $classes,
        ]);
    }

    /**
     * View students in a specific class taught by this teacher (scoped).
     * Protected from accessing classes not assigned to this teacher.
     */
    public function showClassStudents(Request $request, Kelas $kelas)
    {
        $guru = $this->getAuthGuru();

        // Backend security authorization check
        $teachesThisClass = $guru->activeSubjectAssignments()->where('kelas_id', $kelas->id)->exists()
            || $guru->activeJadwal()->where('kelas_id', $kelas->id)->exists()
            || ($guru->activeHomeroomAssignment && $guru->activeHomeroomAssignment->kelas_id === $kelas->id)
            || ($guru->is_kaprog && $guru->user->jurusan_id === $kelas->jurusan_id);

        if (!$teachesThisClass) {
            abort(403, 'Akses ditolak: Anda tidak memiliki penugasan mengajar pada rombel ini.');
        }

        $query = Siswa::with('user')
            ->where('kelas_id', $kelas->id);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'LIKE', '%' . $search . '%')
                  ->orWhere('nis', 'LIKE', '%' . $search . '%')
                  ->orWhere('nisn', 'LIKE', '%' . $search . '%');
            });
        }

        $students = $query->orderBy('nama')->paginate(20)->withQueryString();

        return view('teacher.class-students', [
            'guru'     => $guru,
            'kelas'    => $kelas,
            'students' => $students,
            'search'   => $request->search ?? '',
        ]);
    }

    /**
     * Personal Schedule page for teacher.
     */
    public function mySchedule(Request $request)
    {
        $guru = $this->getAuthGuru();
        $schedules = $this->scheduleService->getTeacherSchedulesWithLiveRooms($guru);

        return view('teacher.schedule', [
            'guru'      => $guru,
            'schedules' => $schedules,
        ]);
    }

    /**
     * Homeroom Class Monitoring (Wali Kelas).
     */
    public function homeroomMonitoring(Request $request)
    {
        $guru = $this->getAuthGuru();
        $activeHomeroom = $guru->activeHomeroomAssignment;

        if (!$activeHomeroom) {
            return redirect()->route('dashboard')->with('error', 'Anda saat ini tidak memiliki penugasan aktif sebagai Wali Kelas.');
        }

        $kelas = $activeHomeroom->kelas;
        $tanggal = $request->tanggal ? Carbon::parse($request->tanggal) : Carbon::today();
        $tanggalStr = $tanggal->toDateString();

        // Query students in this homeroom
        $students = Siswa::with(['user', 'orangtua'])
            ->where('kelas_id', $kelas->id)
            ->orderBy('nama')
            ->get();

        // Query attendance records on that date
        $attendanceRecords = Absensi::whereIn('siswa_id', $students->pluck('id'))
            ->whereDate('tanggal', $tanggalStr)
            ->get()
            ->keyBy('siswa_id');

        // Query WhatsApp notification logs for these students on that date
        $waLogs = \App\Models\WhatsappNotificationLog::whereIn('siswa_id', $students->pluck('id'))
            ->whereDate('created_at', $tanggalStr)
            ->get()
            ->keyBy('siswa_id');

        // Aggregate statistics
        $totalStudents = $students->count();
        $hadirCount = 0;
        $terlambatCount = 0;
        $izinCount = 0;
        $sakitCount = 0;
        $alpaCount = 0;

        foreach ($students as $s) {
            $record = $attendanceRecords->get($s->id);
            if ($record) {
                // Status may be persisted as 'hadir' or 'Hadir' depending on the
                // writer; compare case-insensitively so izin/sakit are not
                // silently counted as alpa.
                $status = strtolower((string) $record->status);

                if ($status === 'hadir') {
                    $hadirCount++;
                    if ($record->menit_terlambat > 0) {
                        $terlambatCount++;
                    }
                } elseif ($status === 'izin') {
                    $izinCount++;
                } elseif ($status === 'sakit') {
                    $sakitCount++;
                } elseif ($status === 'alfa') {
                    $alpaCount++;
                }
            } else {
                $alpaCount++;
            }
        }

        return view('teacher.homeroom-monitoring', [
            'guru'              => $guru,
            'kelas'             => $kelas,
            'students'          => $students,
            'attendanceRecords' => $attendanceRecords,
            'waLogs'            => $waLogs,
            'tanggal'           => $tanggalStr,
            'totalStudents'     => $totalStudents,
            'hadirCount'        => $hadirCount,
            'terlambatCount'    => $terlambatCount,
            'izinCount'         => $izinCount,
            'sakitCount'        => $sakitCount,
            'alpaCount'         => $alpaCount,
        ]);
    }
}
