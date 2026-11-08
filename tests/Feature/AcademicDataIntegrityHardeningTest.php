<?php

namespace Tests\Feature;

use App\Models\Angkatan;
use App\Models\Guru;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\StudentEnrollment;
use App\Models\TahunAjaran;
use App\Models\User;
use App\Services\AcademicPromotionService;
use App\Services\StudentAcademicHistoryService;
use App\Services\TeacherAssignmentService;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AcademicDataIntegrityHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Guru $guruA;
    protected Guru $guruB;

    protected TahunAjaran $ta2026;
    protected TahunAjaran $ta2027;

    protected Jurusan $jurusan;
    protected Angkatan $angkatan18;
    protected Angkatan $angkatan17;

    protected Kelas $kelas10Pplg1_2026;
    protected Kelas $kelas10Pplg2_2026;
    protected Kelas $kelas11Pplg1_2027;
    protected Kelas $kelas10Pplg1_2027;
    protected Kelas $kelas12Pplg1_2026;

    protected StudentAcademicHistoryService $historyService;
    protected AcademicPromotionService $promotionService;
    protected TeacherAssignmentService $teacherService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->historyService = app(StudentAcademicHistoryService::class);
        $this->promotionService = app(AcademicPromotionService::class);
        $this->teacherService = app(TeacherAssignmentService::class);

        $this->admin = User::factory()->create(['name' => 'Admin Hardening', 'role' => 'admin']);

        $userA = User::factory()->create(['name' => 'Wali Kelas A', 'role' => 'guru']);
        $this->guruA = Guru::create(['user_id' => $userA->id, 'nama' => 'Wali Kelas A', 'nip' => '198101012005011001', 'is_active' => true]);

        $userB = User::factory()->create(['name' => 'Wali Kelas B', 'role' => 'guru']);
        $this->guruB = Guru::create(['user_id' => $userB->id, 'nama' => 'Wali Kelas B', 'nip' => '198602022008012002', 'is_active' => true]);

        $this->ta2026 = TahunAjaran::firstOrCreate(
            ['nama' => '2026/2027'],
            [
                'tahun_mulai'     => 2026,
                'tahun_selesai'   => 2027,
                'semester_aktif'  => 'genap',
                'tanggal_mulai'   => '2026-07-15',
                'tanggal_selesai' => '2027-06-25',
                'is_active'       => true,
                'status'          => 'aktif',
            ]
        );

        $this->ta2027 = TahunAjaran::firstOrCreate(
            ['nama' => '2027/2028'],
            [
                'tahun_mulai'     => 2027,
                'tahun_selesai'   => 2028,
                'semester_aktif'  => 'ganjil',
                'tanggal_mulai'   => '2027-07-15',
                'tanggal_selesai' => '2028-06-25',
                'is_active'       => false,
                'status'          => 'draft',
            ]
        );

        $this->jurusan = Jurusan::firstOrCreate(['kode_jurusan' => 'PPLG'], ['nama_jurusan' => 'Pengembangan Perangkat Lunak']);

        $this->angkatan18 = Angkatan::firstOrCreate(
            ['tahun_masuk' => 2026, 'jurusan_id' => $this->jurusan->id],
            ['nama_angkatan' => 'Angkatan 18', 'nomor_angkatan' => 18, 'kode_angkatan' => 'AKT-PPLG-18', 'tahun_kelulusan' => 2029, 'status' => 'aktif']
        );

        $this->angkatan17 = Angkatan::firstOrCreate(
            ['tahun_masuk' => 2024, 'jurusan_id' => $this->jurusan->id],
            ['nama_angkatan' => 'Angkatan 17', 'nomor_angkatan' => 17, 'kode_angkatan' => 'AKT-PPLG-17', 'tahun_kelulusan' => 2027, 'status' => 'aktif']
        );

        $this->kelas10Pplg1_2026 = Kelas::create([
            'nama_kelas' => 'X PPLG 1', 'tingkat' => 10, 'nomor_rombel' => 1, 'jurusan_id' => $this->jurusan->id,
            'tahun_ajaran' => $this->ta2026->nama, 'tahun_ajaran_id' => $this->ta2026->id, 'wali_kelas' => $this->guruA->nama, 'is_active' => true, 'qr_token' => 'QR-10P1-26',
        ]);

        $this->kelas10Pplg2_2026 = Kelas::create([
            'nama_kelas' => 'X PPLG 2', 'tingkat' => 10, 'nomor_rombel' => 2, 'jurusan_id' => $this->jurusan->id,
            'tahun_ajaran' => $this->ta2026->nama, 'tahun_ajaran_id' => $this->ta2026->id, 'wali_kelas' => 'Guru X', 'is_active' => true, 'qr_token' => 'QR-10P2-26',
        ]);

        $this->kelas11Pplg1_2027 = Kelas::create([
            'nama_kelas' => 'XI PPLG 1', 'tingkat' => 11, 'nomor_rombel' => 1, 'jurusan_id' => $this->jurusan->id,
            'tahun_ajaran' => $this->ta2027->nama, 'tahun_ajaran_id' => $this->ta2027->id, 'wali_kelas' => $this->guruB->nama, 'is_active' => true, 'qr_token' => 'QR-11P1-27',
        ]);

        $this->kelas10Pplg1_2027 = Kelas::create([
            'nama_kelas' => 'X PPLG 1', 'tingkat' => 10, 'nomor_rombel' => 1, 'jurusan_id' => $this->jurusan->id,
            'tahun_ajaran' => $this->ta2027->nama, 'tahun_ajaran_id' => $this->ta2027->id, 'wali_kelas' => null, 'is_active' => true, 'qr_token' => 'QR-10P1-27',
        ]);

        $this->kelas12Pplg1_2026 = Kelas::create([
            'nama_kelas' => 'XII PPLG 1', 'tingkat' => 12, 'nomor_rombel' => 1, 'jurusan_id' => $this->jurusan->id,
            'tahun_ajaran' => $this->ta2026->nama, 'tahun_ajaran_id' => $this->ta2026->id, 'wali_kelas' => 'Guru XII', 'is_active' => true, 'qr_token' => 'QR-12P1-26',
        ]);
    }

    protected function createStudentWithActiveEnrollment(string $nama, string $nis, Kelas $kelas, TahunAjaran $ta, Angkatan $angkatan): Siswa
    {
        $user = User::factory()->create(['name' => $nama, 'role' => 'siswa', 'email' => "{$nis}@student.myskanic.test"]);

        $siswa = Siswa::create([
            'user_id'               => $user->id,
            'nis'                   => $nis,
            'nisn'                  => '00' . $nis,
            'nama'                  => $nama,
            'jenis_kelamin'         => 'L',
            'kelas_id'              => $kelas->id,
            'angkatan_id'           => $angkatan->id,
            'tahun_masuk'           => 2026,
            'tahun_ajaran_masuk_id' => $ta->id,
            'tahun_ajaran_masuk'    => '2026/2027',
            'status'                => 'aktif',
            'jalur_masuk'           => 'baru',
            'tempat_lahir'          => 'Bogor',
            'tanggal_lahir'         => '2009-01-01',
        ]);

        StudentEnrollment::create([
            'siswa_id'                  => $siswa->id,
            'tahun_ajaran_id'           => $ta->id,
            'tahun_ajaran'              => $ta->nama,
            'kelas_id'                  => $kelas->id,
            'jurusan_id'                => $kelas->jurusan_id,
            'angkatan_id'               => $angkatan->id,
            'tingkat'                   => $kelas->tingkat ?: 10,
            'started_at'                => Carbon::parse('2026-07-15'),
            'is_active'                 => true,
            'status_enrollment'         => 'aktif',
            'wali_kelas_nama'           => $kelas->wali_kelas ?: 'Wali Asal',
            'status_verifikasi_histori' => 'terverifikasi',
        ]);

        return $siswa;
    }

    /**
     * Test 1: Generated column active_ta_id and unique index uq_student_active_ta exist in database.
     */
    public function test_1_generated_column_and_unique_index_exist(): void
    {
        $this->assertTrue(Schema::hasColumn('student_enrollments', 'active_ta_id'));

        $indexes = DB::select("SHOW INDEX FROM student_enrollments WHERE Key_name = 'uq_student_active_ta'");
        $this->assertNotEmpty($indexes);
        $this->assertEquals(0, $indexes[0]->Non_unique); // Unique constraint
    }

    /**
     * Test 2: Duplicate active enrollment in same TA is rejected by database engine.
     */
    public function test_2_duplicate_active_enrollment_rejected_by_db_engine(): void
    {
        $siswa = $this->createStudentWithActiveEnrollment('Siswa Dup DB', '70001', $this->kelas10Pplg1_2026, $this->ta2026, $this->angkatan18);

        // Attempt raw DB insert of a second active enrollment in the same TA
        $this->expectException(QueryException::class);
        DB::table('student_enrollments')->insert([
            'siswa_id'                  => $siswa->id,
            'tahun_ajaran_id'           => $this->ta2026->id,
            'tahun_ajaran'              => $this->ta2026->nama,
            'kelas_id'                  => $this->kelas10Pplg2_2026->id,
            'jurusan_id'                => $this->jurusan->id,
            'angkatan_id'               => $this->angkatan18->id,
            'tingkat'                   => 10,
            'is_active'                 => 1,
            'status_enrollment'         => 'aktif',
            'status_verifikasi_histori' => 'terverifikasi',
            'created_at'                => now(),
            'updated_at'                => now(),
        ]);
    }

    /**
     * Test 3: Multiple historical (inactive) enrollments in same TA are permitted.
     */
    public function test_3_multiple_historical_inactive_enrollments_permitted(): void
    {
        $siswa = $this->createStudentWithActiveEnrollment('Siswa Multi Inactive', '70002', $this->kelas10Pplg1_2026, $this->ta2026, $this->angkatan18);

        // Close first enrollment
        StudentEnrollment::where('siswa_id', $siswa->id)->update(['is_active' => false]);

        // Insert second inactive enrollment in the same TA (allowed because active_ta_id is NULL)
        $enr2 = StudentEnrollment::create([
            'siswa_id'                  => $siswa->id,
            'tahun_ajaran_id'           => $this->ta2026->id,
            'tahun_ajaran'              => $this->ta2026->nama,
            'kelas_id'                  => $this->kelas10Pplg2_2026->id,
            'jurusan_id'                => $this->jurusan->id,
            'angkatan_id'               => $this->angkatan18->id,
            'tingkat'                   => 10,
            'is_active'                 => false,
            'status_enrollment'         => 'selesai',
            'status_verifikasi_histori' => 'terverifikasi',
        ]);

        $this->assertNotNull($enr2->id);
        $this->assertEquals(2, StudentEnrollment::where('siswa_id', $siswa->id)->count());
    }

    /**
     * Test 4: Promoting student synchronizes both operational pointer and active enrollment.
     */
    public function test_4_promoting_student_synchronizes_both_class_references(): void
    {
        $siswa = $this->createStudentWithActiveEnrollment('Siswa Naik', '70003', $this->kelas10Pplg1_2026, $this->ta2026, $this->angkatan18);

        $this->historyService->promoteStudent($siswa, $this->kelas11Pplg1_2027, $this->ta2027);
        $siswa->refresh();

        // 1. Operational pointer updated
        $this->assertEquals($this->kelas11Pplg1_2027->id, $siswa->kelas_id);

        // 2. Active enrollment updated and matches operational pointer
        $activeEnr = StudentEnrollment::where('siswa_id', $siswa->id)->where('is_active', true)->first();
        $this->assertNotNull($activeEnr);
        $this->assertEquals($this->kelas11Pplg1_2027->id, $activeEnr->kelas_id);
        $this->assertEquals($siswa->kelas_id, $activeEnr->kelas_id);
    }

    /**
     * Test 5: Retaining student (tinggal kelas) synchronizes both class references.
     */
    public function test_5_retaining_student_synchronizes_both_class_references(): void
    {
        $siswa = $this->createStudentWithActiveEnrollment('Siswa Tinggal', '70004', $this->kelas10Pplg1_2026, $this->ta2026, $this->angkatan18);

        $this->historyService->retainStudent($siswa, $this->kelas10Pplg1_2027, $this->ta2027, 'Mengulang tingkat 10');
        $siswa->refresh();

        $this->assertEquals($this->kelas10Pplg1_2027->id, $siswa->kelas_id);

        $activeEnr = StudentEnrollment::where('siswa_id', $siswa->id)->where('is_active', true)->first();
        $this->assertNotNull($activeEnr);
        $this->assertEquals($this->kelas10Pplg1_2027->id, $activeEnr->kelas_id);
        $this->assertEquals($siswa->kelas_id, $activeEnr->kelas_id);
        $this->assertEquals(10, $activeEnr->tingkat);
    }

    /**
     * Test 6: Class transfer (pindah rombel) synchronizes both class references.
     */
    public function test_6_class_transfer_synchronizes_both_class_references(): void
    {
        $siswa = $this->createStudentWithActiveEnrollment('Siswa Pindah Rombel', '70005', $this->kelas10Pplg1_2026, $this->ta2026, $this->angkatan18);

        $this->historyService->transferClass($siswa, $this->kelas10Pplg2_2026, 'Penyesuaian rombel', $this->admin);
        $siswa->refresh();

        $this->assertEquals($this->kelas10Pplg2_2026->id, $siswa->kelas_id);

        $activeEnr = StudentEnrollment::where('siswa_id', $siswa->id)->where('is_active', true)->first();
        $this->assertNotNull($activeEnr);
        $this->assertEquals($this->kelas10Pplg2_2026->id, $activeEnr->kelas_id);
        $this->assertEquals($siswa->kelas_id, $activeEnr->kelas_id);
    }

    /**
     * Test 7: Graduated student has zero active enrollments.
     */
    public function test_7_graduated_student_has_zero_active_enrollments(): void
    {
        $siswa = $this->createStudentWithActiveEnrollment('Siswa Lulus', '70006', $this->kelas12Pplg1_2026, $this->ta2026, $this->angkatan17);

        $this->historyService->graduateStudent($siswa, $this->ta2026);
        $siswa->refresh();

        $this->assertEquals('lulus', $siswa->status);
        $activeEnrCount = StudentEnrollment::where('siswa_id', $siswa->id)->where('is_active', true)->count();
        $this->assertEquals(0, $activeEnrCount);
    }

    /**
     * Test 8: Transaction failure leaves zero partial data.
     */
    public function test_8_transaction_failure_leaves_zero_partial_data(): void
    {
        $siswa = $this->createStudentWithActiveEnrollment('Siswa Rollback', '70007', $this->kelas10Pplg1_2026, $this->ta2026, $this->angkatan18);

        $payload = [
            'tahun_ajaran_asal_id'   => $this->ta2026->id,
            'tahun_ajaran_tujuan_id' => $this->ta2027->id,
            'students'               => [
                [
                    'siswa_id'        => $siswa->id,
                    'action'          => 'naik_kelas',
                    'kelas_tujuan_id' => $this->kelas11Pplg1_2027->id,
                ],
                [
                    'siswa_id'        => 999999, // Triggers ValidationException / 422
                    'action'          => 'naik_kelas',
                    'kelas_tujuan_id' => $this->kelas11Pplg1_2027->id,
                ],
            ],
        ];

        $res = $this->actingAs($this->admin)->postJson(route('academic.promotion.execute'), $payload);
        $res->assertStatus(422);

        // Verify Siswa was not modified
        $siswa->refresh();
        $this->assertEquals($this->kelas10Pplg1_2026->id, $siswa->kelas_id);

        $targetEnr = StudentEnrollment::where('siswa_id', $siswa->id)->where('tahun_ajaran_id', $this->ta2027->id)->first();
        $this->assertNull($targetEnr);
    }

    /**
     * Test 9: Cohort and entry year remain permanent across promotions.
     */
    public function test_9_cohort_and_entry_year_remain_permanent(): void
    {
        $siswa = $this->createStudentWithActiveEnrollment('Siswa Permanent', '70008', $this->kelas10Pplg1_2026, $this->ta2026, $this->angkatan18);

        $origTahunMasuk = $siswa->tahun_masuk;
        $origTaMasuk = $siswa->tahun_ajaran_masuk;
        $origAngkatanId = $siswa->angkatan_id;

        $this->historyService->promoteStudent($siswa, $this->kelas11Pplg1_2027, $this->ta2027);
        $siswa->refresh();

        $this->assertEquals($origTahunMasuk, $siswa->tahun_masuk);
        $this->assertEquals($origTaMasuk, $siswa->tahun_ajaran_masuk);
        $this->assertEquals($origAngkatanId, $siswa->angkatan_id);
    }

    /**
     * Test 10: Old enrollment and homeroom snapshots remain unchanged.
     */
    public function test_10_old_enrollment_and_homeroom_snapshots_remain_unchanged(): void
    {
        $siswa = $this->createStudentWithActiveEnrollment('Siswa Snapshot', '70009', $this->kelas10Pplg1_2026, $this->ta2026, $this->angkatan18);

        $origEnrollment = StudentEnrollment::where('siswa_id', $siswa->id)->where('tahun_ajaran_id', $this->ta2026->id)->first();
        $origWaliKelas = $origEnrollment->wali_kelas_nama;

        $this->historyService->promoteStudent($siswa, $this->kelas11Pplg1_2027, $this->ta2027);

        // Re-read old enrollment
        $origEnrollment->refresh();
        $this->assertFalse($origEnrollment->is_active);
        $this->assertEquals($origWaliKelas, $origEnrollment->wali_kelas_nama);
        $this->assertNotNull($origEnrollment->ended_at);
    }
}
