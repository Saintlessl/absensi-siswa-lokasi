<?php

namespace App\Services;

use App\Models\Guru;
use App\Models\HomeroomAssignment;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\MataPelajaran;
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
     * Preserves assignment history by deactivating previous homeroom assignments for this class.
     */
    public function assignHomeroom(int $guruId, int $kelasId, string $tahunAjaran = '2026/2027', ?string $catatan = null): HomeroomAssignment
    {
        return DB::transaction(function () use ($guruId, $kelasId, $tahunAjaran, $catatan) {
            $guru = Guru::findOrFail($guruId);
            $kelas = Kelas::findOrFail($kelasId);

            // 1. Deactivate previous active homeroom assignments for this class in this academic year
            HomeroomAssignment::where('kelas_id', $kelasId)
                ->where('tahun_ajaran', $tahunAjaran)
                ->where('is_active', true)
                ->update([
                    'is_active' => false,
                    'ends_on'   => Carbon::today(),
                ]);

            // 2. Also deactivate any other active homeroom assignment for this teacher in the same academic year
            HomeroomAssignment::where('guru_id', $guruId)
                ->where('tahun_ajaran', $tahunAjaran)
                ->where('is_active', true)
                ->update([
                    'is_active' => false,
                    'ends_on'   => Carbon::today(),
                ]);

            // 3. Create new active assignment
            $assignment = HomeroomAssignment::create([
                'guru_id'      => $guruId,
                'kelas_id'     => $kelasId,
                'tahun_ajaran' => $tahunAjaran,
                'starts_on'    => Carbon::today(),
                'is_active'    => true,
                'catatan'      => $catatan,
            ]);

            // 4. Synchronize legacy wali_kelas string on kelas table
            $kelas->update(['wali_kelas' => $guru->nama]);

            return $assignment;
        });
    }

    /**
     * Remove or deactivate a homeroom assignment.
     */
    public function removeHomeroomAssignment(int $assignmentId): void
    {
        DB::transaction(function () use ($assignmentId) {
            $assignment = HomeroomAssignment::findOrFail($assignmentId);
            $kelas = $assignment->kelas;

            $assignment->update([
                'is_active' => false,
                'ends_on'   => Carbon::today(),
            ]);

            // If this was the active homeroom teacher on kelas, clear or set to "-"
            if ($kelas && $assignment->guru && $kelas->wali_kelas === $assignment->guru->nama) {
                $kelas->update(['wali_kelas' => '-']);
            }
        });
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
