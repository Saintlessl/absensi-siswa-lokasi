<?php

namespace App\Services;

use App\Models\Guru;
use App\Models\Jadwal;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class TeacherWorkloadService
{
    /**
     * Standard duration of one Jam Pelajaran (JP) in minutes.
     * SMK standard is typically 45 minutes per JP.
     */
    public const MINUTES_PER_JP = 45;

    /**
     * Standard minimum JP per week (Kemendikbud reference: 24 JP).
     */
    public const STANDARD_MIN_JP = 24;

    /**
     * Standard maximum optimal JP per week.
     */
    public const STANDARD_MAX_JP = 36;

    /**
     * Calculate individual teacher workload for an academic year.
     */
    public function calculateWorkload(Guru $guru, string $tahunAjaran = '2026/2027'): array
    {
        $schedules = Jadwal::with(['kelas.jurusan', 'mataPelajaran', 'room'])
            ->where('guru_id', $guru->id)
            ->where('tahun_ajaran', $tahunAjaran)
            ->where('is_active', true)
            ->orderBy('hari')
            ->orderBy('jam_mulai')
            ->get();

        $totalMinutes = 0;
        $totalSessions = $schedules->count();
        $classIds = [];
        $subjectNames = [];

        foreach ($schedules as $s) {
            $classIds[$s->kelas_id] = true;
            $subjectName = $s->mataPelajaran->nama_mapel ?? $s->mata_pelajaran;
            $subjectNames[$subjectName] = true;

            try {
                $start = Carbon::createFromFormat('H:i:s', strlen($s->jam_mulai) === 5 ? $s->jam_mulai . ':00' : $s->jam_mulai);
                $end   = Carbon::createFromFormat('H:i:s', strlen($s->jam_selesai) === 5 ? $s->jam_selesai . ':00' : $s->jam_selesai);
                $diffMinutes = max(0, $start->diffInMinutes($end));
                $totalMinutes += $diffMinutes;
            } catch (\Exception $e) {
                // Fallback standard 90 mins if parsing edge-case
                $totalMinutes += 90;
            }
        }

        $totalJp = (int) round($totalMinutes / self::MINUTES_PER_JP);
        $totalClockHours = round($totalMinutes / 60, 1);
        $totalClasses = count($classIds);
        $totalSubjects = count($subjectNames);

        // Homeroom and KAPROG Status
        $activeHomeroom = $guru->activeHomeroomAssignment;
        $isKaprog = $guru->is_kaprog;
        $kaprogJurusan = $guru->kaprog_jurusan;

        // Workload status classification
        $statusCategory = 'normal';
        $statusLabel = 'Optimal';
        $statusColor = 'emerald';

        if ($totalJp === 0) {
            $statusCategory = 'none';
            $statusLabel = 'Belum Ada Jadwal';
            $statusColor = 'dark';
        } elseif ($totalJp < self::STANDARD_MIN_JP) {
            $statusCategory = 'underload';
            $statusLabel = "Di Bawah Target ({$totalJp}/" . self::STANDARD_MIN_JP . " JP)";
            $statusColor = 'amber';
        } elseif ($totalJp > self::STANDARD_MAX_JP) {
            $statusCategory = 'heavy';
            $statusLabel = "Jadwal Padat ({$totalJp} JP)";
            $statusColor = 'rose';
        } else {
            $statusCategory = 'optimal';
            $statusLabel = "Memenuhi Target ({$totalJp} JP)";
            $statusColor = 'emerald';
        }

        return [
            'guru'               => $guru,
            'tahun_ajaran'       => $tahunAjaran,
            'total_jp'           => $totalJp,
            'total_minutes'      => $totalMinutes,
            'total_clock_hours'  => $totalClockHours,
            'total_sessions'     => $totalSessions,
            'total_classes'      => $totalClasses,
            'total_subjects'     => $totalSubjects,
            'subject_list'       => array_keys($subjectNames),
            'schedules'          => $schedules,
            'is_homeroom'        => (bool) $activeHomeroom,
            'homeroom_kelas'     => $activeHomeroom?->kelas,
            'is_kaprog'          => $isKaprog,
            'kaprog_jurusan'     => $kaprogJurusan,
            'status'             => $statusCategory,
            'status_category'    => $statusCategory,
            'status_label'       => $statusLabel,
            'status_color'       => $statusColor,
            'clock_hours'        => $totalClockHours,
            'rombels_taught'     => $totalClasses,
            'subjects_taught'    => $totalSubjects,
            'target_min_jp'      => self::STANDARD_MIN_JP,
            'target_max_jp'      => self::STANDARD_MAX_JP,
        ];
    }

    /**
     * Get workload analysis for all teachers.
     */
    public function getAllTeachersWorkload(
        string $tahunAjaran = '2026/2027',
        ?string $search = null,
        ?int $jurusanId = null
    ): array {
        $query = Guru::with(['user.jurusan', 'activeHomeroomAssignment.kelas', 'activeSubjectAssignments.mataPelajaran'])
            ->where('is_active', true);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'LIKE', "%{$search}%")
                  ->orWhere('nip', 'LIKE', "%{$search}%")
                  ->orWhere('nuptk', 'LIKE', "%{$search}%");
            });
        }

        if ($jurusanId) {
            $query->whereHas('subjectAssignments.kelas', function ($q) use ($jurusanId) {
                $q->where('jurusan_id', $jurusanId);
            });
        }

        $teachers = $query->orderBy('nama')->get();

        $workloads = $teachers->map(function ($guru) use ($tahunAjaran) {
            return $this->calculateWorkload($guru, $tahunAjaran);
        });

        // Compute Schoolwide Aggregates
        $totalTeachers = $workloads->count();
        $totalJpSchool = $workloads->sum('total_jp');
        $averageJp = $totalTeachers > 0 ? round($totalJpSchool / $totalTeachers, 1) : 0;
        $underloadCount = $workloads->where('status_category', 'underload')->count();
        $optimalCount = $workloads->where('status_category', 'optimal')->count();
        $heavyCount = $workloads->where('status_category', 'heavy')->count();
        $homeroomCount = $workloads->where('is_homeroom', true)->count();
        $kaprogCount = $workloads->where('is_kaprog', true)->count();

        return [
            'workloads'       => $workloads,
            'tahun_ajaran'    => $tahunAjaran,
            'total_teachers'  => $totalTeachers,
            'total_jp_school' => $totalJpSchool,
            'average_jp'      => $averageJp,
            'underload_count' => $underloadCount,
            'optimal_count'   => $optimalCount,
            'heavy_count'     => $heavyCount,
            'homeroom_count'  => $homeroomCount,
            'kaprog_count'    => $kaprogCount,
        ];
    }
}
