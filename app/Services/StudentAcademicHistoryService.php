<?php

namespace App\Services;

use App\Models\HomeroomAssignment;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\SiswaStatusHistory;
use App\Models\StudentEnrollment;
use App\Models\TahunAjaran;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StudentAcademicHistoryService
{
    /**
     * Create initial student enrollment and establish permanent entry academic year.
     * Captures homeroom snapshot and avoids fabricating assumption-based promotion loops.
     */
    public function recordInitialEnrollment(
        Siswa $siswa,
        Kelas $targetKelas,
        TahunAjaran $activeTa,
        ?TahunAjaran $entryTa = null,
        ?User $actor = null
    ): StudentEnrollment {
        return DB::transaction(function () use ($siswa, $targetKelas, $activeTa, $entryTa, $actor) {
            $actorId = $actor?->id ?? Auth::id();

            // 1. Resolve & persist permanent entry academic year
            $resolvedEntryTa = $entryTa;
            if (!$resolvedEntryTa && $siswa->tahun_ajaran_masuk_id) {
                $resolvedEntryTa = TahunAjaran::find($siswa->tahun_ajaran_masuk_id);
            }
            if (!$resolvedEntryTa && $siswa->tahun_masuk) {
                $resolvedEntryTa = TahunAjaran::where('tahun_mulai', $siswa->tahun_masuk)->first();
            }
            if (!$resolvedEntryTa) {
                $resolvedEntryTa = $activeTa;
            }

            $siswa->update([
                'tahun_ajaran_masuk_id' => $resolvedEntryTa->id,
                'tahun_ajaran_masuk'    => $resolvedEntryTa->nama,
            ]);

            // 2. Prevent duplicate active enrollment in active academic year
            $this->preventDuplicateActiveEnrollment($siswa->id, $activeTa->id);

            // 3. Resolve Homeroom Snapshot
            $homeroomSnapshot = $this->resolveHomeroomSnapshot($targetKelas->id, $activeTa->id);
            $isTransfer = $siswa->jalur_masuk === 'pindahan';

            // 4. Create active enrollment for the current operational year
            $activeEnrollment = StudentEnrollment::create([
                'siswa_id'                  => $siswa->id,
                'tahun_ajaran_id'           => $activeTa->id,
                'tahun_ajaran'              => $activeTa->nama,
                'kelas_id'                  => $targetKelas->id,
                'jurusan_id'                => $targetKelas->jurusan_id,
                'angkatan_id'               => $siswa->angkatan_id,
                'tingkat'                   => $targetKelas->tingkat ?: 10,
                'started_at'                => Carbon::today(),
                'ended_at'                  => null,
                'is_active'                 => true,
                'status_enrollment'         => 'aktif',
                'homeroom_assignment_id'    => $homeroomSnapshot['homeroom_assignment_id'],
                'wali_kelas_guru_id'        => $homeroomSnapshot['wali_kelas_guru_id'],
                'wali_kelas_nama'           => $homeroomSnapshot['wali_kelas_nama'],
                'status_verifikasi_histori' => 'terverifikasi',
                'catatan'                   => $isTransfer
                    ? 'Siswa pindahan masuk dari ' . ($siswa->asal_sekolah ?: 'sekolah sebelumnya')
                    : 'Pendaftaran siswa baru',
            ]);

            // 5. Record movement audit log in SiswaStatusHistory
            $homeroomName = $homeroomSnapshot['wali_kelas_nama'];

            SiswaStatusHistory::create([
                'siswa_id'               => $siswa->id,
                'status_sebelumnya'      => 'belum_terdaftar',
                'status_baru'            => 'aktif',
                'jenis_perubahan'        => $isTransfer ? 'mutasi_masuk' : 'registrasi_baru',
                'tanggal_perubahan'      => Carbon::today(),
                'tahun_ajaran'           => $activeTa->nama,
                'tahun_ajaran_tujuan_id' => $activeTa->id,
                'tahun_ajaran_tujuan'    => $activeTa->nama,
                'kelas_tujuan_id'        => $targetKelas->id,
                'wali_kelas_tujuan'      => $homeroomName,
                'alasan'                 => $isTransfer ? 'Mutasi masuk dari ' . $siswa->asal_sekolah : 'Pendaftaran siswa reguler baru',
                'catatan'                => 'Penempatan awal rombel ' . $targetKelas->nama_kelas,
                'created_by'             => $actorId,
            ]);

            return $activeEnrollment;
        });
    }

    /**
     * Transfer student to a different class / rombel while preserving full historical paper trail.
     */
    public function transferClass(
        Siswa $siswa,
        Kelas $newKelas,
        ?string $alasan = null,
        ?User $actor = null
    ): StudentEnrollment {
        return DB::transaction(function () use ($siswa, $newKelas, $alasan, $actor) {
            $actorId = $actor?->id ?? Auth::id();
            $oldKelas = $siswa->kelas;
            $activeTa = $newKelas->tahunAjaran ?? TahunAjaran::getActive() ?? TahunAjaran::first();

            // 1. Finalize current active enrollment (do not delete or overwrite)
            $currentActive = StudentEnrollment::where('siswa_id', $siswa->id)
                ->where('is_active', true)
                ->first();

            $oldHomeroom = '-';
            $oldTaNama = $activeTa?->nama;
            $oldTaId = $activeTa?->id;

            if ($currentActive) {
                $oldHomeroom = $this->resolveHomeroomTeacherName($currentActive->kelas_id, $currentActive->tahun_ajaran_id);
                $oldTaNama = $currentActive->tahun_ajaran;
                $oldTaId = $currentActive->tahun_ajaran_id;

                $currentActive->update([
                    'is_active' => false,
                    'ended_at'  => Carbon::today(),
                    'catatan'   => ($currentActive->catatan ? $currentActive->catatan . ' | ' : '') . 'Pindah rombel ke ' . $newKelas->nama_kelas,
                ]);
            }

            // 2. Prevent duplicate active enrollment in target academic year
            $this->preventDuplicateActiveEnrollment($siswa->id, $activeTa->id);

            // 3. Create new active enrollment for the new class with snapshot
            $homeroomSnapshot = $this->resolveHomeroomSnapshot($newKelas->id, $activeTa->id);

            $newEnrollment = StudentEnrollment::create([
                'siswa_id'                  => $siswa->id,
                'tahun_ajaran_id'           => $activeTa->id,
                'tahun_ajaran'              => $activeTa->nama,
                'kelas_id'                  => $newKelas->id,
                'jurusan_id'                => $newKelas->jurusan_id,
                'angkatan_id'               => $siswa->angkatan_id,
                'tingkat'                   => $newKelas->tingkat ?: 10,
                'started_at'                => Carbon::today(),
                'ended_at'                  => null,
                'is_active'                 => true,
                'status_enrollment'         => 'aktif',
                'homeroom_assignment_id'    => $homeroomSnapshot['homeroom_assignment_id'],
                'wali_kelas_guru_id'        => $homeroomSnapshot['wali_kelas_guru_id'],
                'wali_kelas_nama'           => $homeroomSnapshot['wali_kelas_nama'],
                'status_verifikasi_histori' => 'terverifikasi',
                'catatan'                   => $alasan ?: 'Perpindahan rombel kelas dari ' . ($oldKelas?->nama_kelas ?? '-'),
            ]);

            // 4. Update student's current active class pointer
            $siswa->update(['kelas_id' => $newKelas->id]);

            // 5. Record audit trail in SiswaStatusHistory
            $newHomeroom = $homeroomSnapshot['wali_kelas_nama'];

            SiswaStatusHistory::create([
                'siswa_id'               => $siswa->id,
                'status_sebelumnya'      => 'aktif',
                'status_baru'            => 'aktif',
                'jenis_perubahan'        => 'pindah_rombel',
                'tanggal_perubahan'      => Carbon::today(),
                'tahun_ajaran'           => $activeTa->nama,
                'tahun_ajaran_asal_id'   => $oldTaId,
                'tahun_ajaran_asal'      => $oldTaNama,
                'tahun_ajaran_tujuan_id' => $activeTa->id,
                'tahun_ajaran_tujuan'    => $activeTa->nama,
                'kelas_asal_id'          => $oldKelas?->id,
                'kelas_tujuan_id'        => $newKelas->id,
                'wali_kelas_asal'        => $oldHomeroom,
                'wali_kelas_tujuan'      => $newHomeroom,
                'alasan'                 => $alasan ?: 'Perpindahan rombel kelas',
                'catatan'                => 'Siswa dipindahkan dari ' . ($oldKelas?->nama_kelas ?? '-') . ' ke ' . $newKelas->nama_kelas,
                'created_by'             => $actorId,
            ]);

            return $newEnrollment;
        });
    }

    /**
     * Promote student to the next grade level in a new academic year.
     * Permanent entry academic year & cohort remain untouched.
     */
    public function promoteStudent(
        Siswa $siswa,
        Kelas $targetKelas,
        TahunAjaran $targetTa,
        ?string $catatan = null,
        ?User $actor = null
    ): StudentEnrollment {
        return DB::transaction(function () use ($siswa, $targetKelas, $targetTa, $catatan, $actor) {
            $actorId = $actor?->id ?? Auth::id();

            // Guard: Only active students can be promoted
            if ($siswa->status !== 'aktif') {
                throw ValidationException::withMessages([
                    'siswa' => ["Siswa {$siswa->nama} ({$siswa->nis}) berstatus '{$siswa->status}'. Hanya siswa berstatus aktif yang dapat dipromosikan."],
                ]);
            }

            $oldKelas = $siswa->kelas;

            // 1. Finalize previous year's enrollment
            $currentActive = StudentEnrollment::where('siswa_id', $siswa->id)
                ->where('is_active', true)
                ->first();

            $oldTaId = null;
            $oldTaNama = null;
            $oldHomeroom = '-';

            if ($currentActive) {
                $oldTaId = $currentActive->tahun_ajaran_id;
                $oldTaNama = $currentActive->tahun_ajaran;
                $oldHomeroom = $this->resolveHomeroomTeacherName($currentActive->kelas_id, $currentActive->tahun_ajaran_id);

                $currentActive->update([
                    'is_active'         => false,
                    'status_enrollment' => 'selesai',
                    'ended_at'          => Carbon::today(),
                    'catatan'           => ($currentActive->catatan ? $currentActive->catatan . ' | ' : '') . 'Naik kelas ke ' . $targetKelas->nama_kelas,
                ]);
            }

            // 2. Prevent duplicate active enrollment in target academic year
            $this->preventDuplicateActiveEnrollment($siswa->id, $targetTa->id);

            // 3. Create new active enrollment with snapshot
            $homeroomSnapshot = $this->resolveHomeroomSnapshot($targetKelas->id, $targetTa->id);

            $newEnrollment = StudentEnrollment::create([
                'siswa_id'                  => $siswa->id,
                'tahun_ajaran_id'           => $targetTa->id,
                'tahun_ajaran'              => $targetTa->nama,
                'kelas_id'                  => $targetKelas->id,
                'jurusan_id'                => $targetKelas->jurusan_id,
                'angkatan_id'               => $siswa->angkatan_id, // cohort stays permanent
                'tingkat'                   => $targetKelas->tingkat ?: 10,
                'started_at'                => Carbon::today(),
                'ended_at'                  => null,
                'is_active'                 => true,
                'status_enrollment'         => 'aktif',
                'homeroom_assignment_id'    => $homeroomSnapshot['homeroom_assignment_id'],
                'wali_kelas_guru_id'        => $homeroomSnapshot['wali_kelas_guru_id'],
                'wali_kelas_nama'           => $homeroomSnapshot['wali_kelas_nama'],
                'status_verifikasi_histori' => 'terverifikasi',
                'catatan'                   => $catatan ?: 'Naik kelas ke tingkat ' . ($targetKelas->tingkat ?: 10),
            ]);

            // 4. Update student's active class pointer without touching permanent entry year
            $siswa->update(['kelas_id' => $targetKelas->id]);

            // 5. Record movement audit log
            $newHomeroom = $homeroomSnapshot['wali_kelas_nama'];

            SiswaStatusHistory::create([
                'siswa_id'               => $siswa->id,
                'status_sebelumnya'      => 'aktif',
                'status_baru'            => 'aktif',
                'jenis_perubahan'        => 'kenaikan_kelas',
                'tanggal_perubahan'      => Carbon::today(),
                'tahun_ajaran'           => $targetTa->nama,
                'tahun_ajaran_asal_id'   => $oldTaId,
                'tahun_ajaran_asal'      => $oldTaNama,
                'tahun_ajaran_tujuan_id' => $targetTa->id,
                'tahun_ajaran_tujuan'    => $targetTa->nama,
                'kelas_asal_id'          => $oldKelas?->id,
                'kelas_tujuan_id'        => $targetKelas->id,
                'wali_kelas_asal'        => $oldHomeroom,
                'wali_kelas_tujuan'      => $newHomeroom,
                'alasan'                 => 'Kenaikan kelas tahunan',
                'catatan'                => $catatan ?: 'Naik kelas dari ' . ($oldKelas?->nama_kelas ?? '-') . ' ke ' . $targetKelas->nama_kelas,
                'created_by'             => $actorId,
            ]);

            return $newEnrollment;
        });
    }

    /**
     * Retain student in the same grade level (Tinggal Kelas).
     * Permanent entry year and original cohort MUST NOT CHANGE.
     */
    public function retainStudent(
        Siswa $siswa,
        Kelas $targetKelas,
        TahunAjaran $targetTa,
        ?string $alasan = null,
        ?User $actor = null
    ): StudentEnrollment {
        return DB::transaction(function () use ($siswa, $targetKelas, $targetTa, $alasan, $actor) {
            $actorId = $actor?->id ?? Auth::id();

            // Guard: Only active students can be retained
            if ($siswa->status !== 'aktif') {
                throw ValidationException::withMessages([
                    'siswa' => ["Siswa {$siswa->nama} ({$siswa->nis}) berstatus '{$siswa->status}'. Hanya siswa berstatus aktif yang dapat diproses tinggal kelas."],
                ]);
            }

            $oldKelas = $siswa->kelas;

            // 1. Mark previous enrollment as 'tinggal_kelas'
            $currentActive = StudentEnrollment::where('siswa_id', $siswa->id)
                ->where('is_active', true)
                ->first();

            $oldTaId = null;
            $oldTaNama = null;
            $oldHomeroom = '-';

            if ($currentActive) {
                $oldTaId = $currentActive->tahun_ajaran_id;
                $oldTaNama = $currentActive->tahun_ajaran;
                $oldHomeroom = $this->resolveHomeroomTeacherName($currentActive->kelas_id, $currentActive->tahun_ajaran_id);

                $currentActive->update([
                    'is_active'         => false,
                    'status_enrollment' => 'tinggal_kelas',
                    'ended_at'          => Carbon::today(),
                    'catatan'           => ($currentActive->catatan ? $currentActive->catatan . ' | ' : '') . 'Tinggal kelas di tingkat ' . $currentActive->tingkat,
                ]);
            }

            // 2. Prevent duplicate active enrollment in target academic year
            $this->preventDuplicateActiveEnrollment($siswa->id, $targetTa->id);

            // 3. Create new active enrollment for repeated grade level (preserving original cohort & entry year!)
            $homeroomSnapshot = $this->resolveHomeroomSnapshot($targetKelas->id, $targetTa->id);

            $newEnrollment = StudentEnrollment::create([
                'siswa_id'                  => $siswa->id,
                'tahun_ajaran_id'           => $targetTa->id,
                'tahun_ajaran'              => $targetTa->nama,
                'kelas_id'                  => $targetKelas->id,
                'jurusan_id'                => $targetKelas->jurusan_id,
                'angkatan_id'               => $siswa->angkatan_id, // cohort stays permanent!
                'tingkat'                   => $targetKelas->tingkat ?: 10,
                'started_at'                => Carbon::today(),
                'ended_at'                  => null,
                'is_active'                 => true,
                'status_enrollment'         => 'aktif',
                'homeroom_assignment_id'    => $homeroomSnapshot['homeroom_assignment_id'],
                'wali_kelas_guru_id'        => $homeroomSnapshot['wali_kelas_guru_id'],
                'wali_kelas_nama'           => $homeroomSnapshot['wali_kelas_nama'],
                'status_verifikasi_histori' => 'terverifikasi',
                'catatan'                   => $alasan ?: 'Tinggal kelas mengulang jenjang tingkat ' . ($targetKelas->tingkat ?: 10),
            ]);

            // 4. Update class pointer
            $siswa->update(['kelas_id' => $targetKelas->id]);

            // 5. Record audit trail
            $newHomeroom = $homeroomSnapshot['wali_kelas_nama'];

            SiswaStatusHistory::create([
                'siswa_id'               => $siswa->id,
                'status_sebelumnya'      => 'aktif',
                'status_baru'            => 'aktif',
                'jenis_perubahan'        => 'tinggal_kelas',
                'tanggal_perubahan'      => Carbon::today(),
                'tahun_ajaran'           => $targetTa->nama,
                'tahun_ajaran_asal_id'   => $oldTaId,
                'tahun_ajaran_asal'      => $oldTaNama,
                'tahun_ajaran_tujuan_id' => $targetTa->id,
                'tahun_ajaran_tujuan'    => $targetTa->nama,
                'kelas_asal_id'          => $oldKelas?->id,
                'kelas_tujuan_id'        => $targetKelas->id,
                'wali_kelas_asal'        => $oldHomeroom,
                'wali_kelas_tujuan'      => $newHomeroom,
                'alasan'                 => $alasan ?: 'Tinggal kelas berdasarkan evaluasi akademik',
                'catatan'                => 'Mengulang jenjang tingkat ' . ($targetKelas->tingkat ?: 10) . ' di kelas ' . $targetKelas->nama_kelas,
                'created_by'             => $actorId,
            ]);

            return $newEnrollment;
        });
    }

    /**
     * Graduate student (Kelulusan). Full history is preserved.
     */
    public function graduateStudent(
        Siswa $siswa,
        ?TahunAjaran $taLulus = null,
        ?string $catatan = null,
        ?User $actor = null
    ): void {
        DB::transaction(function () use ($siswa, $taLulus, $catatan, $actor) {
            $actorId = $actor?->id ?? Auth::id();

            // Guard: Graduated student cannot be graduated again
            if ($siswa->status === 'lulus') {
                throw ValidationException::withMessages([
                    'siswa' => ["Siswa {$siswa->nama} ({$siswa->nis}) sudah berstatus lulus."],
                ]);
            }

            $resolvedTa = $taLulus ?? TahunAjaran::getActive() ?? TahunAjaran::first();

            // 1. Finalize active enrollment
            $currentActive = StudentEnrollment::where('siswa_id', $siswa->id)
                ->where('is_active', true)
                ->first();

            $oldHomeroom = '-';
            $oldKelasId = $siswa->kelas_id;

            if ($currentActive) {
                $oldHomeroom = $this->resolveHomeroomTeacherName($currentActive->kelas_id, $currentActive->tahun_ajaran_id);
                $oldKelasId = $currentActive->kelas_id;

                $currentActive->update([
                    'is_active'         => false,
                    'status_enrollment' => 'lulus',
                    'ended_at'          => Carbon::today(),
                    'catatan'           => ($currentActive->catatan ? $currentActive->catatan . ' | ' : '') . 'Siswa dinyatakan lulus SMK Negeri 1 Ciomas',
                ]);
            }

            // 2. Update Siswa status to alumnus
            $siswa->update([
                'status'             => 'lulus',
                'tanggal_kelulusan'  => Carbon::today(),
                'tahun_lulus'        => (int) ($resolvedTa ? $resolvedTa->tahun_selesai : now()->year),
                'tahun_ajaran_lulus' => $resolvedTa?->nama,
                'catatan_status'     => $catatan ?: 'Telah menyelesaikan seluruh program pendidikan dan dinyatakan lulus',
            ]);

            // 3. Record audit trail
            SiswaStatusHistory::create([
                'siswa_id'               => $siswa->id,
                'status_sebelumnya'      => 'aktif',
                'status_baru'            => 'lulus',
                'jenis_perubahan'        => 'kelulusan',
                'tanggal_perubahan'      => Carbon::today(),
                'tahun_ajaran'           => $resolvedTa?->nama,
                'tahun_ajaran_asal_id'   => $resolvedTa?->id,
                'tahun_ajaran_asal'      => $resolvedTa?->nama,
                'kelas_asal_id'          => $oldKelasId,
                'wali_kelas_asal'        => $oldHomeroom,
                'alasan'                 => 'Kelulusan dan penuntasan kurikulum SMK',
                'catatan'                => $catatan ?: 'Siswa resmi menjadi alumni SMK Negeri 1 Ciomas',
                'created_by'             => $actorId,
            ]);
        });
    }

    /**
     * Transfer out student to another school (Mutasi Keluar).
     */
    public function transferOutStudent(
        Siswa $siswa,
        string $targetSchool,
        string $nomorSurat,
        ?string $alasan = null,
        ?TahunAjaran $ta = null,
        ?User $actor = null
    ): void {
        DB::transaction(function () use ($siswa, $targetSchool, $nomorSurat, $alasan, $ta, $actor) {
            $actorId = $actor?->id ?? Auth::id();

            // Guard: Inactive student cannot be transferred out
            if (in_array($siswa->status, ['lulus', 'mutasi', 'pindah', 'keluar'])) {
                throw ValidationException::withMessages([
                    'siswa' => ["Siswa {$siswa->nama} ({$siswa->nis}) sudah tidak berstatus aktif (status: {$siswa->status})."],
                ]);
            }

            $activeTa = $ta ?? TahunAjaran::getActive() ?? TahunAjaran::first();

            // 1. Finalize active enrollment
            $currentActive = StudentEnrollment::where('siswa_id', $siswa->id)
                ->where('is_active', true)
                ->first();

            $oldHomeroom = '-';
            $oldKelasId = $siswa->kelas_id;

            if ($currentActive) {
                $oldHomeroom = $this->resolveHomeroomTeacherName($currentActive->kelas_id, $currentActive->tahun_ajaran_id);
                $oldKelasId = $currentActive->kelas_id;

                $currentActive->update([
                    'is_active'         => false,
                    'status_enrollment' => 'mutasi',
                    'ended_at'          => Carbon::today(),
                    'catatan'           => ($currentActive->catatan ? $currentActive->catatan . ' | ' : '') . "Mutasi keluar ke {$targetSchool} (Surat: {$nomorSurat})",
                ]);
            }

            // 2. Update status
            $siswa->update([
                'status'         => 'pindah',
                'catatan_status' => "Pindah sekolah ke {$targetSchool}",
            ]);

            // 3. Record audit trail
            SiswaStatusHistory::create([
                'siswa_id'             => $siswa->id,
                'status_sebelumnya'    => 'aktif',
                'status_baru'          => 'pindah',
                'jenis_perubahan'      => 'mutasi_keluar',
                'tanggal_perubahan'    => Carbon::today(),
                'tahun_ajaran'         => $activeTa?->nama,
                'tahun_ajaran_asal_id' => $activeTa?->id,
                'tahun_ajaran_asal'    => $activeTa?->nama,
                'kelas_asal_id'        => $oldKelasId,
                'wali_kelas_asal'      => $oldHomeroom,
                'sekolah_tujuan'       => $targetSchool,
                'nomor_surat'          => $nomorSurat,
                'alasan'               => $alasan ?: 'Mutasi atas permohonan orang tua/wali',
                'catatan'              => 'Pindah ke ' . $targetSchool,
                'created_by'           => $actorId,
            ]);
        });
    }

    /**
     * Mark student as dropped out or withdrawn (Keluar / DO).
     */
    public function dropOutStudent(
        Siswa $siswa,
        ?string $alasan = null,
        ?TahunAjaran $ta = null,
        ?User $actor = null
    ): void {
        DB::transaction(function () use ($siswa, $alasan, $ta, $actor) {
            $actorId = $actor?->id ?? Auth::id();

            // Guard: Inactive student cannot be dropped out
            if (in_array($siswa->status, ['lulus', 'mutasi', 'pindah', 'keluar'])) {
                throw ValidationException::withMessages([
                    'siswa' => ["Siswa {$siswa->nama} ({$siswa->nis}) sudah tidak berstatus aktif (status: {$siswa->status})."],
                ]);
            }

            $activeTa = $ta ?? TahunAjaran::getActive() ?? TahunAjaran::first();

            // 1. Finalize active enrollment
            $currentActive = StudentEnrollment::where('siswa_id', $siswa->id)
                ->where('is_active', true)
                ->first();

            $oldHomeroom = '-';
            $oldKelasId = $siswa->kelas_id;

            if ($currentActive) {
                $oldHomeroom = $this->resolveHomeroomTeacherName($currentActive->kelas_id, $currentActive->tahun_ajaran_id);
                $oldKelasId = $currentActive->kelas_id;

                $currentActive->update([
                    'is_active'         => false,
                    'status_enrollment' => 'keluar',
                    'ended_at'          => Carbon::today(),
                    'catatan'           => ($currentActive->catatan ? $currentActive->catatan . ' | ' : '') . 'Status siswa keluar / drop out',
                ]);
            }

            // 2. Update status on Siswa
            $siswa->update([
                'status'         => 'keluar',
                'catatan_status' => $alasan ?: 'Siswa keluar / drop out dari sekolah',
            ]);

            // 3. Record audit trail
            SiswaStatusHistory::create([
                'siswa_id'             => $siswa->id,
                'status_sebelumnya'    => 'aktif',
                'status_baru'          => 'keluar',
                'jenis_perubahan'      => 'keluar',
                'tanggal_perubahan'    => Carbon::today(),
                'tahun_ajaran'         => $activeTa?->nama,
                'tahun_ajaran_asal_id' => $activeTa?->id,
                'tahun_ajaran_asal'    => $activeTa?->nama,
                'kelas_asal_id'        => $oldKelasId,
                'wali_kelas_asal'      => $oldHomeroom,
                'alasan'               => $alasan ?: 'Keluar / Mengundurkan Diri',
                'catatan'              => 'Siswa resmi keluar dari sekolah',
                'created_by'           => $actorId,
            ]);
        });
    }

    /**
     * Prevent duplicate active enrollment in the same academic year.
     */
    public function preventDuplicateActiveEnrollment(int $siswaId, int $tahunAjaranId, ?int $ignoreEnrollmentId = null): void
    {
        $query = StudentEnrollment::where('siswa_id', $siswaId)
            ->where('tahun_ajaran_id', $tahunAjaranId)
            ->where('is_active', true);

        if ($ignoreEnrollmentId) {
            $query->where('id', '!=', $ignoreEnrollmentId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'enrollment' => 'Siswa sudah memiliki enrollment aktif pada tahun ajaran ini. Tidak diizinkan memiliki enrollment ganda.',
            ]);
        }
    }

    /**
     * Resolve the assigned homeroom teacher name from HomeroomAssignment.
     */
    public function resolveHomeroomTeacherName(int $kelasId, ?int $tahunAjaranId = null): string
    {
        $assignment = HomeroomAssignment::with('guru')
            ->where('kelas_id', $kelasId)
            ->when($tahunAjaranId, fn($q) => $q->where('tahun_ajaran_id', $tahunAjaranId))
            ->orderByDesc('is_active')
            ->first();

        if ($assignment && $assignment->guru) {
            return $assignment->guru->nama;
        }

        $kelas = Kelas::find($kelasId);
        return $kelas?->wali_kelas ?: '-';
    }

    /**
     * Resolve the complete homeroom snapshot (assignment ID, teacher ID, and teacher name).
     * Used to freeze historical records so future teacher edits do not mutate past snapshots.
     */
    public function resolveHomeroomSnapshot(int $kelasId, ?int $tahunAjaranId = null): array
    {
        $assignment = HomeroomAssignment::with('guru')
            ->where('kelas_id', $kelasId)
            ->when($tahunAjaranId, fn($q) => $q->where('tahun_ajaran_id', $tahunAjaranId))
            ->orderByDesc('is_active')
            ->first();

        if ($assignment && $assignment->guru) {
            return [
                'homeroom_assignment_id' => $assignment->id,
                'wali_kelas_guru_id'     => $assignment->guru_id,
                'wali_kelas_nama'        => $assignment->guru->nama,
            ];
        }

        $kelas = Kelas::find($kelasId);
        return [
            'homeroom_assignment_id' => null,
            'wali_kelas_guru_id'     => null,
            'wali_kelas_nama'        => $kelas?->wali_kelas ?: '-',
        ];
    }

    /**
     * Record an explicit past or historical academic enrollment without fabricating data.
     * Supports tagging as verified or unverified.
     */
    public function recordHistoricalEnrollment(
        Siswa $siswa,
        Kelas $kelas,
        TahunAjaran $tahunAjaran,
        int $tingkat,
        string $statusEnrollment = 'selesai',
        bool $isVerified = true,
        ?string $waliKelasNama = null,
        ?string $catatan = null
    ): StudentEnrollment {
        $homeroomSnapshot = $this->resolveHomeroomSnapshot($kelas->id, $tahunAjaran->id);
        $finalWaliKelas = $waliKelasNama ?: $homeroomSnapshot['wali_kelas_nama'];

        return StudentEnrollment::create([
            'siswa_id'                  => $siswa->id,
            'tahun_ajaran_id'           => $tahunAjaran->id,
            'tahun_ajaran'              => $tahunAjaran->nama,
            'kelas_id'                  => $kelas->id,
            'jurusan_id'                => $kelas->jurusan_id ?: $siswa->jurusan_id,
            'angkatan_id'               => $siswa->angkatan_id,
            'tingkat'                   => $tingkat,
            'started_at'                => $tahunAjaran->tanggal_mulai,
            'ended_at'                  => $tahunAjaran->tanggal_selesai,
            'is_active'                 => false,
            'status_enrollment'         => $statusEnrollment,
            'homeroom_assignment_id'    => $homeroomSnapshot['homeroom_assignment_id'],
            'wali_kelas_guru_id'        => $homeroomSnapshot['wali_kelas_guru_id'],
            'wali_kelas_nama'           => $finalWaliKelas,
            'status_verifikasi_histori' => $isVerified ? 'terverifikasi' : 'belum_terverifikasi',
            'catatan'                   => $catatan ?: "Histori penempatan akademik jenjang Kelas {$tingkat}",
        ]);
    }
}
