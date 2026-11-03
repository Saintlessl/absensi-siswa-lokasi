<?php

namespace App\Services;

use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class TeacherScheduleService
{
    public function __construct(protected RoomAssignmentService $roomAssignmentService)
    {
    }

    /**
     * Validate that schedule has no time/day/teacher/class conflicts.
     */
    public function assertNoConflict(array $data, ?int $ignoreJadwalId = null): void
    {
        $guruId      = (int) $data['guru_id'];
        $kelasId     = (int) $data['kelas_id'];
        $hari        = $data['hari'];
        $jamMulai    = $data['jam_mulai'];
        $jamSelesai  = $data['jam_selesai'];
        $tahunAjaran = $data['tahun_ajaran'] ?? '2026/2027';

        if ($jamMulai >= $jamSelesai) {
            throw ValidationException::withMessages([
                'jam_selesai' => ['Jam selesai harus lebih akhir daripada jam mulai.'],
            ]);
        }

        // 1. Conflict Check: Teacher already booked on the same day & time
        $teacherConflictQuery = Jadwal::where('guru_id', $guruId)
            ->where('hari', $hari)
            ->where('tahun_ajaran', $tahunAjaran)
            ->where('is_active', true)
            ->where(function ($q) use ($jamMulai, $jamSelesai) {
                $q->where('jam_mulai', '<', $jamSelesai)
                  ->where('jam_selesai', '>', $jamMulai);
            });

        if ($ignoreJadwalId) {
            $teacherConflictQuery->where('id', '!=', $ignoreJadwalId);
        }

        $teacherConflict = $teacherConflictQuery->with(['kelas', 'mataPelajaran'])->first();
        if ($teacherConflict) {
            $kelasName = $teacherConflict->kelas->nama_kelas ?? 'Kelas lain';
            $mapelName = $teacherConflict->mataPelajaran->nama_mapel ?? $teacherConflict->mata_pelajaran;
            throw ValidationException::withMessages([
                'jam_mulai' => ["Guru sudah memiliki jadwal mengajar {$mapelName} di {$kelasName} pada hari {$hari} pukul {$teacherConflict->jam_mulai} - {$teacherConflict->jam_selesai}."],
            ]);
        }

        // 2. Conflict Check: Class (Rombel) already has another lesson on the same day & time
        $classConflictQuery = Jadwal::where('kelas_id', $kelasId)
            ->where('hari', $hari)
            ->where('tahun_ajaran', $tahunAjaran)
            ->where('is_active', true)
            ->where(function ($q) use ($jamMulai, $jamSelesai) {
                $q->where('jam_mulai', '<', $jamSelesai)
                  ->where('jam_selesai', '>', $jamMulai);
            });

        if ($ignoreJadwalId) {
            $classConflictQuery->where('id', '!=', $ignoreJadwalId);
        }

        $classConflict = $classConflictQuery->with(['guru', 'mataPelajaran'])->first();
        if ($classConflict) {
            $guruName = $classConflict->guru->nama ?? 'Guru lain';
            $mapelName = $classConflict->mataPelajaran->nama_mapel ?? $classConflict->mata_pelajaran;
            throw ValidationException::withMessages([
                'kelas_id' => ["Rombel ini sudah memiliki jadwal pelajaran {$mapelName} bersama {$guruName} pada hari {$hari} pukul {$classConflict->jam_mulai} - {$classConflict->jam_selesai}."],
            ]);
        }

        // 3. Conflict Check: Explicit physical room already booked
        if (!empty($data['room_id'])) {
            $roomId = (int) $data['room_id'];
            $roomConflictQuery = Jadwal::where('room_id', $roomId)
                ->where('hari', $hari)
                ->where('tahun_ajaran', $tahunAjaran)
                ->where('is_active', true)
                ->where(function ($q) use ($jamMulai, $jamSelesai) {
                    $q->where('jam_mulai', '<', $jamSelesai)
                      ->where('jam_selesai', '>', $jamMulai);
                });

            if ($ignoreJadwalId) {
                $roomConflictQuery->where('id', '!=', $ignoreJadwalId);
            }

            $roomConflict = $roomConflictQuery->with(['kelas', 'room'])->first();
            if ($roomConflict) {
                $roomName = $roomConflict->room->name ?? 'Ruangan';
                $kelasName = $roomConflict->kelas->nama_kelas ?? 'kelas lain';
                throw ValidationException::withMessages([
                    'room_id' => ["{$roomName} sudah digunakan oleh rombel {$kelasName} pada hari {$hari} pukul {$roomConflict->jam_mulai} - {$roomConflict->jam_selesai}."],
                ]);
            }
        }
    }

    /**
     * Create a validated teaching schedule.
     */
    public function createSchedule(array $data): Jadwal
    {
        $this->assertNoConflict($data);

        // Resolve subject name from mata_pelajaran_id if present
        if (!empty($data['mata_pelajaran_id'])) {
            $mapel = MataPelajaran::find($data['mata_pelajaran_id']);
            if ($mapel && empty($data['mata_pelajaran'])) {
                $data['mata_pelajaran'] = $mapel->nama_mapel;
            }
        }

        return Jadwal::create([
            'guru_id'           => $data['guru_id'],
            'kelas_id'          => $data['kelas_id'],
            'mata_pelajaran_id' => $data['mata_pelajaran_id'] ?? null,
            'mata_pelajaran'    => $data['mata_pelajaran'] ?? 'Pelajaran',
            'hari'              => $data['hari'],
            'jam_mulai'         => $data['jam_mulai'],
            'jam_selesai'       => $data['jam_selesai'],
            'tahun_ajaran'      => $data['tahun_ajaran'] ?? '2026/2027',
            'room_id'           => $data['room_id'] ?? null,
            'is_active'         => $data['is_active'] ?? true,
        ]);
    }

    /**
     * Update an existing schedule with conflict validation.
     */
    public function updateSchedule(Jadwal $jadwal, array $data): Jadwal
    {
        $mergedData = array_merge($jadwal->toArray(), $data);
        $this->assertNoConflict($mergedData, $jadwal->id);

        if (!empty($data['mata_pelajaran_id'])) {
            $mapel = MataPelajaran::find($data['mata_pelajaran_id']);
            if ($mapel) {
                $data['mata_pelajaran'] = $mapel->nama_mapel;
            }
        }

        $jadwal->update($data);
        return $jadwal->fresh(['guru', 'kelas.jurusan', 'mataPelajaran', 'room']);
    }

    /**
     * Fetch teacher schedules with dynamically resolved physical classrooms from Room Rolling.
     */
    public function getTeacherSchedulesWithLiveRooms(Guru $guru, ?string $day = null, ?Carbon $date = null): Collection
    {
        $date = $date ?? Carbon::today();

        $query = Jadwal::with(['kelas.jurusan', 'mataPelajaran', 'room'])
            ->where('guru_id', $guru->id)
            ->where('is_active', true);

        if ($day) {
            $query->where('hari', $day);
        }

        $schedules = $query->orderBy('jam_mulai')->get();

        // Attach live physical room to each schedule using rolling room allocations
        return $schedules->map(function ($jadwal) use ($date) {
            $liveRoom = null;
            if ($jadwal->kelas_id) {
                // Check active room assignment for this class on this date
                $assignment = \App\Models\RoomAssignment::where('kelas_id', $jadwal->kelas_id)
                    ->where(function ($q) use ($date) {
                        $q->whereNull('starts_on')->orWhere('starts_on', '<=', $date->toDateString());
                    })
                    ->where(function ($q) use ($date) {
                        $q->whereNull('ends_on')->orWhere('ends_on', '>=', $date->toDateString());
                    })
                    ->with('room')
                    ->first();

                if ($assignment && $assignment->room) {
                    $liveRoom = $assignment->room;
                }
            }

            // Fallback to explicitly bound room if no rolling room is active
            if (!$liveRoom && $jadwal->room) {
                $liveRoom = $jadwal->room;
            }

            $jadwal->live_room = $liveRoom;
            return $jadwal;
        });
    }
}
