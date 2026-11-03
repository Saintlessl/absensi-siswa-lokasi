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
     */
    public function removeSubjectAssignment(int $assignmentId): void
    {
        $assignment = TeacherSubjectAssignment::findOrFail($assignmentId);
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
     * Assign a teacher as Head of Department (KAPROG) for a major.
     */
    public function assignKaprog(int $guruId, int $jurusanId): void
    {
        DB::transaction(function () use ($guruId, $jurusanId) {
            $guru = Guru::with('user')->findOrFail($guruId);
            $jurusan = Jurusan::findOrFail($jurusanId);

            if (!$guru->user) {
                throw ValidationException::withMessages([
                    'guru' => ['Guru ini tidak memiliki akun autentikasi yang terhubung.'],
                ]);
            }

            // Remove existing KAPROG on this department
            User::where('jurusan_id', $jurusanId)
                ->where('id', '!=', $guru->user->id)
                ->update(['jurusan_id' => null]);

            // Assign KAPROG to this teacher's user
            $guru->user->update([
                'jurusan_id' => $jurusanId,
            ]);
        });
    }

    /**
     * Unassign a teacher from KAPROG duty.
     */
    public function removeKaprog(int $guruId): void
    {
        $guru = Guru::with('user')->findOrFail($guruId);
        if ($guru->user) {
            $guru->user->update(['jurusan_id' => null]);
        }
    }
}
