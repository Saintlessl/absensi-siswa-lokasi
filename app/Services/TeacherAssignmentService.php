<?php

namespace App\Services;

use App\Models\Guru;
use App\Models\HomeroomAssignment;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\StudentEnrollment;
use App\Models\TahunAjaran;
use App\Models\TeacherSubjectAssignment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TeacherAssignmentService
{
    /**
     * Assign a subject and class (rombel) to a teacher.
     */
    public function assignSubject(int $guruId, int $mataPelajaranId, int $kelasId, string $tahunAjaran = '2026/2027'): TeacherSubjectAssignment
    {
        $guru = Guru::findOrFail($guruId);
        $mapel = MataPelajaran::findOrFail($mataPelajaranId);
        $kelas = Kelas::findOrFail($kelasId);

        $exists = TeacherSubjectAssignment::where('guru_id', $guruId)
            ->where('mata_pelajaran_id', $mataPelajaranId)
            ->where('kelas_id', $kelasId)
            ->where('tahun_ajaran', $tahunAjaran)
            ->first();

        if ($exists) {
            if (!$exists->is_active) {
                $exists->update(['is_active' => true, 'ends_on' => null]);
            }
            return $exists;
        }

        return TeacherSubjectAssignment::create([
            'guru_id'           => $guruId,
            'mata_pelajaran_id' => $mataPelajaranId,
            'kelas_id'          => $kelasId,
            'tahun_ajaran'      => $tahunAjaran,
            'starts_on'         => Carbon::today(),
            'is_active'         => true,
        ]);
    }

    /**
     * Remove or deactivate a subject teaching assignment.
     * Enforces requirement: Every active teacher must retain at least one valid subject assignment.
     */
    public function removeSubjectAssignment(int $assignmentId): void
    {
        $assignment = TeacherSubjectAssignment::findOrFail($assignmentId);
        $guru = $assignment->guru;

        if ($guru && $guru->is_active) {
            $activeCount = TeacherSubjectAssignment::where('guru_id', $guru->id)
                ->where('is_active', true)
                ->count();

            if ($activeCount <= 1) {
                throw ValidationException::withMessages([
                    'assignment' => ['Setiap guru aktif wajib memiliki minimal satu penugasan mata pelajaran yang valid. Silakan tambahkan mata pelajaran pengganti terlebih dahulu sebelum menghapus penugasan ini.'],
                ]);
            }
        }

        $assignment->delete();
    }

    /**
     * Assign a teacher as homeroom teacher (Wali Kelas) for a class in an academic year.
     * Enforces:
     * 1. Guru must be active.
     * 2. Max 1 active homeroom per class per academic year.
     * 3. Max 1 active class per teacher per academic year.
     * 4. Deactivates previous homeroom without deleting historical records.
     * 5. Freezes immutable snapshot on active student enrollments.
     */
    public function assignHomeroom(
        int $guruId,
        int $kelasId,
        string|int $tahunAjaran = '2026/2027',
        ?string $catatan = null,
        ?string $alasanPergantian = null,
        ?Carbon $effectiveDate = null
    ): HomeroomAssignment {
        return DB::transaction(function () use ($guruId, $kelasId, $tahunAjaran, $catatan, $alasanPergantian, $effectiveDate) {
            $guru = Guru::with('user')->findOrFail($guruId);
            $kelas = Kelas::findOrFail($kelasId);

            // 1. Validation: Teacher must be active
            if (!$guru->is_active || ($guru->user && $guru->user->status === 'inactive')) {
                throw ValidationException::withMessages([
                    'guru' => ["Guru {$guru->nama} berstatus nonaktif dan tidak dapat menerima penugasan wali kelas."],
                ]);
            }

            // 2. Resolve Academic Year
            $taModel = null;
            if (is_numeric($tahunAjaran)) {
                $taModel = TahunAjaran::find($tahunAjaran);
            }
            if (!$taModel && is_string($tahunAjaran)) {
                $taModel = TahunAjaran::where('nama', $tahunAjaran)->first();
            }
            if (!$taModel) {
                $taModel = TahunAjaran::where('is_active', true)->first();
            }

            $taId = $taModel?->id;
            $taNama = $taModel ? $taModel->nama : (string) $tahunAjaran;
            $effDate = $effectiveDate ?: Carbon::today();

            // 3. Validation: Teacher cannot be homeroom for another class in the SAME academic year
            $otherActive = HomeroomAssignment::where('guru_id', $guruId)
                ->where(function ($q) use ($taId, $taNama) {
                    if ($taId) {
                        $q->where('tahun_ajaran_id', $taId);
                    } else {
                        $q->where('tahun_ajaran', $taNama);
                    }
                })
                ->where('kelas_id', '!=', $kelasId)
                ->where('is_active', true)
                ->first();

            if ($otherActive) {
                $otherKelas = Kelas::find($otherActive->kelas_id);
                throw ValidationException::withMessages([
                    'guru' => ["Guru {$guru->nama} sudah menjadi wali kelas aktif untuk rombel {$otherKelas?->nama_kelas} pada tahun ajaran {$taNama}. Satu guru maksimal membina satu rombel dalam tahun ajaran yang sama."],
                ]);
            }

            // 4. Find existing active homeroom for this class in this academic year
            $existingAssignment = HomeroomAssignment::where('kelas_id', $kelasId)
                ->where(function ($q) use ($taId, $taNama) {
                    if ($taId) {
                        $q->where('tahun_ajaran_id', $taId);
                    } else {
                        $q->where('tahun_ajaran', $taNama);
                    }
                })
                ->where('is_active', true)
                ->first();

            // If same teacher already assigned, just update note and return
            if ($existingAssignment && $existingAssignment->guru_id === $guruId) {
                if ($catatan) {
                    $existingAssignment->update(['catatan' => $catatan]);
                }
                $kelas->update(['wali_kelas' => $guru->nama]);
                return $existingAssignment;
            }

            // 5. Deactivate previous teacher assignment (DO NOT DELETE — RETAIN FULL HISTORY)
            if ($existingAssignment) {
                $existingAssignment->update([
                    'is_active'         => false,
                    'ends_on'           => $effDate,
                    'alasan_pergantian' => $alasanPergantian ?: 'Pergantian wali kelas oleh administrator',
                ]);
            }

            // 6. Create new active assignment
            $newAssignment = HomeroomAssignment::create([
                'guru_id'         => $guruId,
                'kelas_id'        => $kelasId,
                'tahun_ajaran'    => $taNama,
                'tahun_ajaran_id' => $taId,
                'starts_on'       => $effDate,
                'ends_on'         => null,
                'is_active'       => true,
                'catatan'         => $catatan,
            ]);

            // Link replaced assignment to successor
            if ($existingAssignment) {
                $existingAssignment->update(['replaced_by_assignment_id' => $newAssignment->id]);
            }

            // 7. Synchronize legacy wali_kelas string on kelas table
            $kelas->update(['wali_kelas' => $guru->nama]);

            // 8. Freeze / update snapshot on active student enrollments for this class & academic year
            StudentEnrollment::where('kelas_id', $kelasId)
                ->where('is_active', true)
                ->where(function ($q) use ($taId, $taNama) {
                    if ($taId) {
                        $q->where('tahun_ajaran_id', $taId);
                    } else {
                        $q->where('tahun_ajaran', $taNama);
                    }
                })
                ->update([
                    'homeroom_assignment_id' => $newAssignment->id,
                    'wali_kelas_guru_id'     => $guruId,
                    'wali_kelas_nama'        => $guru->nama,
                ]);

            return $newAssignment;
        });
    }

    /**
     * Explicit replacement of homeroom teacher with confirmation data.
     */
    public function replaceHomeroom(
        int $kelasId,
        int $newGuruId,
        string|int $tahunAjaran,
        string $alasanPergantian,
        ?Carbon $effectiveDate = null
    ): HomeroomAssignment {
        return $this->assignHomeroom(
            $newGuruId,
            $kelasId,
            $tahunAjaran,
            "Pergantian wali kelas: {$alasanPergantian}",
            $alasanPergantian,
            $effectiveDate
        );
    }

    /**
     * Terminate or revoke homeroom assignment without successor (e.g. teacher resigned / reassigned).
     * Preserves entire historical record.
     */
    public function terminateHomeroomAssignment(int $assignmentId, string $alasan = 'Penonaktifan penugasan wali kelas', ?Carbon $effectiveDate = null): void
    {
        DB::transaction(function () use ($assignmentId, $alasan, $effectiveDate) {
            $assignment = HomeroomAssignment::findOrFail($assignmentId);
            $kelas = $assignment->kelas;
            $effDate = $effectiveDate ?: Carbon::today();

            $assignment->update([
                'is_active'         => false,
                'ends_on'           => $effDate,
                'alasan_pergantian' => $alasan,
            ]);

            // If this was active homeroom on kelas, clear to "-"
            if ($kelas && $assignment->guru && $kelas->wali_kelas === $assignment->guru->nama) {
                $kelas->update(['wali_kelas' => '-']);
            }
        });
    }

    /**
     * Legacy wrapper for backward compatibility.
     */
    public function removeHomeroomAssignment(int $assignmentId): void
    {
        $this->terminateHomeroomAssignment($assignmentId, 'Penugasan wali kelas diakhiri melalui panel guru');
    }

    /**
     * Assign a teacher as Head of Department (KAPROG) for a major with historical tracking.
     */
    public function assignKaprog(int $guruId, int $jurusanId, string $tahunAjaran = '2026/2027', ?string $catatan = null): \App\Models\KaprogAssignment
    {
        return DB::transaction(function () use ($guruId, $jurusanId, $tahunAjaran, $catatan) {
            $guru = Guru::with('user')->findOrFail($guruId);
            $jurusan = Jurusan::findOrFail($jurusanId);

            if (!$guru->user) {
                throw ValidationException::withMessages([
                    'guru' => ['Guru ini tidak memiliki akun autentikasi yang terhubung.'],
                ]);
            }

            // 1. Deactivate existing active KAPROG assignment for this department
            \App\Models\KaprogAssignment::where('jurusan_id', $jurusanId)
                ->where('is_active', true)
                ->update([
                    'is_active' => false,
                    'ends_on'   => Carbon::today(),
                ]);

            // 2. Deactivate existing active KAPROG assignment for this teacher
            \App\Models\KaprogAssignment::where('guru_id', $guruId)
                ->where('is_active', true)
                ->update([
                    'is_active' => false,
                    'ends_on'   => Carbon::today(),
                ]);

            // 3. Remove existing KAPROG on this department in user table
            User::where('jurusan_id', $jurusanId)
                ->where('id', '!=', $guru->user->id)
                ->update(['jurusan_id' => null]);

            // 4. Assign KAPROG to this teacher's user
            $guru->user->update([
                'jurusan_id' => $jurusanId,
            ]);

            // 5. Create new historical KAPROG assignment record
            return \App\Models\KaprogAssignment::create([
                'guru_id'      => $guruId,
                'jurusan_id'   => $jurusanId,
                'tahun_ajaran' => $tahunAjaran,
                'starts_on'    => Carbon::today(),
                'is_active'    => true,
                'catatan'      => $catatan ?? "Penugasan KAPROG {$jurusan->nama_jurusan}",
            ]);
        });
    }

    /**
     * Unassign a teacher from KAPROG duty.
     */
    public function removeKaprog(int $guruId): void
    {
        DB::transaction(function () use ($guruId) {
            $guru = Guru::with('user')->findOrFail($guruId);

            \App\Models\KaprogAssignment::where('guru_id', $guruId)
                ->where('is_active', true)
                ->update([
                    'is_active' => false,
                    'ends_on'   => Carbon::today(),
                ]);

            if ($guru->user) {
                $guru->user->update(['jurusan_id' => null]);
            }
        });
    }
}
