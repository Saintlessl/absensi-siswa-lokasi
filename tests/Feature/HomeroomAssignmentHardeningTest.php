<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\HomeroomAssignment;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\StudentEnrollment;
use App\Models\TahunAjaran;
use App\Models\User;
use App\Services\StudentAcademicHistoryService;
use App\Services\TeacherAssignmentService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class HomeroomAssignmentHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $kaprog;
    protected TahunAjaran $ta2025;
    protected TahunAjaran $ta2026;
    protected Jurusan $jurusan;
    protected Kelas $kelasA;
    protected Kelas $kelasB;
    protected Guru $guru1;
    protected Guru $guru2;
    protected Guru $guru3;
    protected TeacherAssignmentService $assignmentService;
    protected StudentAcademicHistoryService $historyService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->assignmentService = app(TeacherAssignmentService::class);
        $this->historyService = app(StudentAcademicHistoryService::class);

        $this->admin = User::factory()->create(['role' => 'admin', 'email' => 'admin.homeroom@myskanic.test']);

        $this->ta2025 = TahunAjaran::firstOrCreate(['nama' => '2025/2026'], [
            'tahun_mulai'     => 2025,
            'tahun_selesai'   => 2026,
            'semester_aktif'  => 'genap',
            'tanggal_mulai'   => '2025-07-15',
            'tanggal_selesai' => '2026-06-25',
            'is_active'       => false,
            'status'          => 'selesai',
        ]);

        $this->ta2026 = TahunAjaran::firstOrCreate(['nama' => '2026/2027'], [
            'tahun_mulai'     => 2026,
            'tahun_selesai'   => 2027,
            'semester_aktif'  => 'ganjil',
            'tanggal_mulai'   => '2026-07-15',
            'tanggal_selesai' => '2027-06-25',
            'is_active'       => true,
            'status'          => 'aktif',
        ]);
        $this->ta2026->update(['is_active' => true, 'status' => 'aktif']);

        $this->jurusan = Jurusan::create([
            'nama_jurusan' => 'Pengembangan Perangkat Lunak dan Gim',
            'kode_jurusan' => 'PPLG',
        ]);

        $this->kelasA = Kelas::create([
            'nama_kelas'      => 'X PPLG 1',
            'jurusan_id'      => $this->jurusan->id,
            'tingkat'         => 10,
            'nomor_rombel'    => 1,
            'tahun_ajaran'    => '2026/2027',
            'tahun_ajaran_id' => $this->ta2026->id,
            'is_active'       => true,
        ]);

        $this->kelasB = Kelas::create([
            'nama_kelas'      => 'X PPLG 2',
            'jurusan_id'      => $this->jurusan->id,
            'tingkat'         => 10,
            'nomor_rombel'    => 2,
            'tahun_ajaran'    => '2026/2027',
            'tahun_ajaran_id' => $this->ta2026->id,
            'is_active'       => true,
        ]);

        $u1 = User::factory()->create(['role' => 'guru', 'status' => 'active', 'email' => 'guru1@myskanic.test']);
        $this->guru1 = Guru::create(['user_id' => $u1->id, 'nama' => 'Dra. Siti Aminah', 'is_active' => true]);

        $u2 = User::factory()->create(['role' => 'guru', 'status' => 'active', 'email' => 'guru2@myskanic.test']);
        $this->guru2 = Guru::create(['user_id' => $u2->id, 'nama' => 'Bambang Sudarsono, S.T.', 'is_active' => true]);

        $u3 = User::factory()->create(['role' => 'guru', 'status' => 'inactive', 'email' => 'guru3@myskanic.test']);
        $this->guru3 = Guru::create(['user_id' => $u3->id, 'nama' => 'Guru Nonaktif, S.Pd.', 'is_active' => false]);
    }

    /**
     * Skenario 1: Satu rombel tidak bisa memiliki dua wali kelas aktif pada tahun ajaran yang sama.
     */
    public function test_rombel_cannot_have_two_active_homeroom_teachers_simultaneously(): void
    {
        // 1. Assign Guru 1
        $assignment1 = $this->assignmentService->assignHomeroom($this->guru1->id, $this->kelasA->id, $this->ta2026->id);
        $this->assertTrue($assignment1->is_active);
        $this->assertEquals(1, HomeroomAssignment::where('kelas_id', $this->kelasA->id)->where('is_active', true)->count());

        // 2. Assign Guru 2 to the same class in the same academic year
        $assignment2 = $this->assignmentService->assignHomeroom($this->guru2->id, $this->kelasA->id, $this->ta2026->id);

        // Verification: Exactly ONE active assignment remains
        $activeAssignments = HomeroomAssignment::where('kelas_id', $this->kelasA->id)->where('is_active', true)->get();
        $this->assertCount(1, $activeAssignments);
        $this->assertEquals($this->guru2->id, $activeAssignments->first()->guru_id);

        // Guru 1 assignment was deactivated, NOT deleted
        $assignment1->refresh();
        $this->assertFalse($assignment1->is_active);
        $this->assertNotNull($assignment1->ends_on);
        $this->assertEquals($assignment2->id, $assignment1->replaced_by_assignment_id);
    }

    /**
     * Skenario 2: Satu guru maksimal membina satu rombel dalam tahun ajaran yang sama.
     */
    public function test_one_teacher_cannot_be_active_homeroom_for_two_rombels_in_same_academic_year(): void
    {
        // Guru 1 assigned to Kelas A
        $this->assignmentService->assignHomeroom($this->guru1->id, $this->kelasA->id, $this->ta2026->id);

        // Attempting to assign Guru 1 to Kelas B in the same academic year must throw ValidationException
        $this->expectException(ValidationException::class);
        $this->assignmentService->assignHomeroom($this->guru1->id, $this->kelasB->id, $this->ta2026->id);
    }

    /**
     * Skenario 3: Pergantian wali kelas otomatis mencabut penugasan aktif guru sebelumnya tanpa menghapus histori.
     */
    public function test_replacing_homeroom_teacher_deactivates_old_teacher_and_preserves_history(): void
    {
        // Initial assignment: Guru 1
        $oldAssign = $this->assignmentService->assignHomeroom($this->guru1->id, $this->kelasA->id, $this->ta2026->id);
        $this->assertEquals('Dra. Siti Aminah', $this->kelasA->fresh()->wali_kelas);

        // Replace with Guru 2 with reason
        $newAssign = $this->assignmentService->replaceHomeroom(
            $this->kelasA->id,
            $this->guru2->id,
            $this->ta2026->id,
            'Guru 1 cuti melahirkan 3 bulan',
            Carbon::parse('2026-10-01')
        );

        // Old assignment is deactivated with metadata
        $oldAssign->refresh();
        $this->assertFalse($oldAssign->is_active);
        $this->assertEquals('2026-10-01', $oldAssign->ends_on->format('Y-m-d'));
        $this->assertStringContainsString('cuti melahirkan', $oldAssign->alasan_pergantian);
        $this->assertEquals($newAssign->id, $oldAssign->replaced_by_assignment_id);

        // New assignment is active
        $this->assertTrue($newAssign->is_active);
        $this->assertEquals($this->guru2->id, $newAssign->guru_id);
        $this->assertEquals('Bambang Sudarsono, S.T.', $this->kelasA->fresh()->wali_kelas);

        // History count: Both records must remain in database!
        $this->assertEquals(2, HomeroomAssignment::where('kelas_id', $this->kelasA->id)->count());
    }

    /**
     * Skenario 4: Pergantian wali kelas tahun ajaran baru tidak mengubah histori tahun ajaran lama.
     */
    public function test_new_academic_year_homeroom_assignment_does_not_mutate_old_year_history(): void
    {
        // Year 1 (2025/2026): Guru 1 is homeroom for Kelas A
        $assign2025 = $this->assignmentService->assignHomeroom($this->guru1->id, $this->kelasA->id, $this->ta2025->id);

        // Year 2 (2026/2027): Guru 2 is assigned homeroom for Kelas A
        $assign2026 = $this->assignmentService->assignHomeroom($this->guru2->id, $this->kelasA->id, $this->ta2026->id);

        // Verify Year 1 assignment was NOT modified by Year 2 assignment
        $assign2025->refresh();
        $this->assertEquals($this->guru1->id, $assign2025->guru_id);
        $this->assertEquals('2025/2026', $assign2025->tahun_ajaran);

        // Verify Year 2 assignment is active for 2026/2027
        $this->assertEquals($this->guru2->id, $assign2026->guru_id);
        $this->assertEquals('2026/2027', $assign2026->tahun_ajaran);
        $this->assertTrue($assign2026->is_active);
    }

    /**
     * Skenario 5: Guru yang dinonaktifkan tidak dapat menerima penugasan baru.
     */
    public function test_inactive_teacher_cannot_receive_homeroom_assignment(): void
    {
        $this->expectException(ValidationException::class);
        $this->assignmentService->assignHomeroom($this->guru3->id, $this->kelasA->id, $this->ta2026->id);
    }

    /**
     * Skenario 6: Snapshot histori wali kelas pada enrollment tetap membekukan nama guru saat penugasan.
     */
    public function test_enrollment_snapshot_preserves_historical_teacher_name_even_if_homeroom_changes_later(): void
    {
        // 1. Assign Guru 1 to Kelas A for 2025/2026
        $this->assignmentService->assignHomeroom($this->guru1->id, $this->kelasA->id, $this->ta2025->id);

        // 2. Student enrolled in 2025/2026 under Guru 1
        $userSiswa = User::factory()->create(['role' => 'siswa']);
        $siswa = Siswa::create([
            'user_id'               => $userSiswa->id,
            'nis'                   => '252610011',
            'nama'                  => 'Siswa Snapshot Test',
            'jenis_kelamin'         => 'L',
            'kelas_id'              => $this->kelasA->id,
            'jurusan_id'            => $this->jurusan->id,
            'tahun_masuk'           => 2025,
            'tahun_ajaran_masuk_id' => $this->ta2025->id,
            'tahun_ajaran_masuk'    => '2025/2026',
            'status'                => 'aktif',
        ]);

        $enr2025 = StudentEnrollment::create([
            'siswa_id'                  => $siswa->id,
            'tahun_ajaran_id'           => $this->ta2025->id,
            'tahun_ajaran'              => '2025/2026',
            'kelas_id'                  => $this->kelasA->id,
            'jurusan_id'                => $this->jurusan->id,
            'tingkat'                   => 10,
            'started_at'                => '2025-07-15',
            'ended_at'                  => '2026-06-25',
            'is_active'                 => false,
            'status_enrollment'         => 'selesai',
            'wali_kelas_guru_id'        => $this->guru1->id,
            'wali_kelas_nama'           => 'Dra. Siti Aminah', // FROZEN SNAPSHOT
            'status_verifikasi_histori' => 'terverifikasi',
        ]);

        // 3. In 2026/2027, the class homeroom is replaced by Guru 2 (Bambang Sudarsono)
        $this->assignmentService->assignHomeroom($this->guru2->id, $this->kelasA->id, $this->ta2026->id);

        // 4. Verification: Past 2025 enrollment MUST still return Dra. Siti Aminah
        $this->assertEquals('Dra. Siti Aminah', $enr2025->fresh()->wali_kelas_nama);
    }

    /**
     * Skenario 7: Pengakhiran penugasan wali kelas tanpa pengganti (terminate) mempertahankan histori dan membersihkan pointer kelas.
     */
    public function test_terminating_homeroom_without_successor_retains_history_and_clears_class_pointer(): void
    {
        $assignment = $this->assignmentService->assignHomeroom($this->guru1->id, $this->kelasA->id, $this->ta2026->id);
        $this->assertEquals('Dra. Siti Aminah', $this->kelasA->fresh()->wali_kelas);

        // Terminate
        $this->assignmentService->terminateHomeroomAssignment($assignment->id, 'Guru pensiun terhitung 1 Oktober 2026');

        $assignment->refresh();
        $this->assertFalse($assignment->is_active);
        $this->assertNotNull($assignment->ends_on);
        $this->assertStringContainsString('pensiun', $assignment->alasan_pergantian);

        // Kelas pointer is cleared
        $this->assertEquals('-', $this->kelasA->fresh()->wali_kelas);
    }

    /**
     * Skenario 8: Halaman UI manajemen wali kelas dapat diakses dan memfilter data dengan benar.
     */
    public function test_homeroom_management_ui_renders_and_filters_properly(): void
    {
        $this->assignmentService->assignHomeroom($this->guru1->id, $this->kelasA->id, $this->ta2026->id);

        $response = $this->actingAs($this->admin)->get(route('academic.wali-kelas.index', [
            'tahun_ajaran_id' => $this->ta2026->id,
            'status_filter'   => 'assigned',
        ]));

        $response->assertOk();
        $response->assertSee('Manajemen Wali Kelas');
        $response->assertSee('X PPLG 1');
        $response->assertSee('Dra. Siti Aminah');
    }

    /**
     * Skenario 9: Endpoint AJAX riwayat penugasan mengembalikan alur mutasi yang lengkap.
     */
    public function test_homeroom_history_endpoint_returns_accurate_audit_logs(): void
    {
        $this->assignmentService->assignHomeroom($this->guru1->id, $this->kelasA->id, $this->ta2026->id);
        $this->assignmentService->replaceHomeroom($this->kelasA->id, $this->guru2->id, $this->ta2026->id, 'Rotasi tugas');

        $response = $this->actingAs($this->admin)->get(route('academic.wali-kelas.history', [
            'kelas_id' => $this->kelasA->id,
        ]));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'type'    => 'kelas',
        ]);
        $this->assertCount(2, $response->json('data'));
    }
}
