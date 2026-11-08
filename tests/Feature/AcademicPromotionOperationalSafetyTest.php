<?php

namespace Tests\Feature;

use App\Models\AcademicPromotionBatch;
use App\Models\AcademicPromotionItem;
use App\Models\Angkatan;
use App\Models\Guru;
use App\Models\HomeroomAssignment;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\SiswaStatusHistory;
use App\Models\StudentEnrollment;
use App\Models\TahunAjaran;
use App\Models\User;
use App\Services\AcademicPromotionService;
use App\Services\StudentAcademicHistoryService;
use App\Services\TeacherAssignmentService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AcademicPromotionOperationalSafetyTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Guru $guruA;
    protected Guru $guruB;
    protected Guru $guruC;

    protected TahunAjaran $ta2026;
    protected TahunAjaran $ta2027;

    protected Jurusan $jurusan;
    protected Angkatan $angkatan18;
    protected Angkatan $angkatan17;

    protected Kelas $kelas10Pplg1_2026;
    protected Kelas $kelas12Pplg1_2026;
    protected Kelas $kelas10Pplg1_2027;
    protected Kelas $kelas11Pplg1_2027;
    protected Kelas $kelas10Pplg2_2027;

    protected AcademicPromotionService $promotionService;
    protected StudentAcademicHistoryService $historyService;
    protected TeacherAssignmentService $teacherService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->promotionService = app(AcademicPromotionService::class);
        $this->historyService = app(StudentAcademicHistoryService::class);
        $this->teacherService = app(TeacherAssignmentService::class);

        $this->admin = User::factory()->create([
            'name'  => 'Admin Operational',
            'role'  => 'admin',
            'email' => 'admin.ops@myskanic.test',
        ]);

        // Academic Years
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

        // Department & Cohorts
        $this->jurusan = Jurusan::firstOrCreate(
            ['kode_jurusan' => 'PPLG'],
            ['nama_jurusan' => 'Pengembangan Perangkat Lunak dan Gim']
        );

        $this->angkatan18 = Angkatan::firstOrCreate(
            ['tahun_masuk' => 2025, 'jurusan_id' => $this->jurusan->id],
            [
                'nama_angkatan'  => 'Angkatan 18 PPLG',
                'nomor_angkatan' => 18,
                'kode_angkatan'  => 'AKT-PPLG-18',
                'tahun_kelulusan'=> 2028,
                'status'         => 'aktif',
            ]
        );

        $this->angkatan17 = Angkatan::firstOrCreate(
            ['tahun_masuk' => 2024, 'jurusan_id' => $this->jurusan->id],
            [
                'nama_angkatan'  => 'Angkatan 17 PPLG',
                'nomor_angkatan' => 17,
                'kode_angkatan'  => 'AKT-PPLG-17',
                'tahun_kelulusan'=> 2027,
                'status'         => 'aktif',
            ]
        );

        // Teachers
        $userA = User::factory()->create(['name' => 'Budi Santoso, M.Kom', 'role' => 'guru']);
        $this->guruA = Guru::create(['user_id' => $userA->id, 'nama' => 'Budi Santoso, M.Kom', 'nip' => '198001012005011001', 'is_active' => true]);

        $userB = User::factory()->create(['name' => 'Siti Rahmawati, S.Pd', 'role' => 'guru']);
        $this->guruB = Guru::create(['user_id' => $userB->id, 'nama' => 'Siti Rahmawati, S.Pd', 'nip' => '198502022008012002', 'is_active' => true]);

        $userC = User::factory()->create(['name' => 'Ahmad Dahlan, S.Kom', 'role' => 'guru']);
        $this->guruC = Guru::create(['user_id' => $userC->id, 'nama' => 'Ahmad Dahlan, S.Kom', 'nip' => '198703032010011003', 'is_active' => true]);

        // Origin Classes 2026/2027
        $this->kelas10Pplg1_2026 = Kelas::create([
            'nama_kelas'      => 'X PPLG 1',
            'tingkat'         => 10,
            'nomor_rombel'    => 1,
            'jurusan_id'      => $this->jurusan->id,
            'tahun_ajaran'    => $this->ta2026->nama,
            'tahun_ajaran_id' => $this->ta2026->id,
            'wali_kelas'      => $this->guruA->nama,
            'jumlah_siswa'    => 0,
            'is_active'       => true,
            'qr_token'        => 'QR-X-PPLG-1-2026',
        ]);

        $this->kelas12Pplg1_2026 = Kelas::create([
            'nama_kelas'      => 'XII PPLG 1',
            'tingkat'         => 12,
            'nomor_rombel'    => 1,
            'jurusan_id'      => $this->jurusan->id,
            'tahun_ajaran'    => $this->ta2026->nama,
            'tahun_ajaran_id' => $this->ta2026->id,
            'wali_kelas'      => $this->guruC->nama,
            'jumlah_siswa'    => 0,
            'is_active'       => true,
            'qr_token'        => 'QR-XII-PPLG-1-2026',
        ]);

        // Target Classes 2027/2028
        $this->kelas10Pplg1_2027 = Kelas::create([
            'nama_kelas'      => 'X PPLG 1',
            'tingkat'         => 10,
            'nomor_rombel'    => 1,
            'jurusan_id'      => $this->jurusan->id,
            'tahun_ajaran'    => $this->ta2027->nama,
            'tahun_ajaran_id' => $this->ta2027->id,
            'wali_kelas'      => null,
            'jumlah_siswa'    => 0,
            'is_active'       => true,
            'qr_token'        => 'QR-X-PPLG-1-2027',
        ]);

        $this->kelas11Pplg1_2027 = Kelas::create([
            'nama_kelas'      => 'XI PPLG 1',
            'tingkat'         => 11,
            'nomor_rombel'    => 1,
            'jurusan_id'      => $this->jurusan->id,
            'tahun_ajaran'    => $this->ta2027->nama,
            'tahun_ajaran_id' => $this->ta2027->id,
            'wali_kelas'      => null,
            'jumlah_siswa'    => 0,
            'is_active'       => true,
            'qr_token'        => 'QR-XI-PPLG-1-2027',
        ]);

        $this->kelas10Pplg2_2027 = Kelas::create([
            'nama_kelas'      => 'X PPLG 2',
            'tingkat'         => 10,
            'nomor_rombel'    => 2,
            'jurusan_id'      => $this->jurusan->id,
            'tahun_ajaran'    => $this->ta2027->nama,
            'tahun_ajaran_id' => $this->ta2027->id,
            'wali_kelas'      => null,
            'jumlah_siswa'    => 0,
            'is_active'       => true,
            'qr_token'        => 'QR-X-PPLG-2-2027',
        ]);

        // Assign Guru A as Homeroom of X PPLG 1 in 2026/2027
        $this->teacherService->assignHomeroom(
            guruId: $this->guruA->id,
            kelasId: $this->kelas10Pplg1_2026->id,
            tahunAjaran: $this->ta2026->id,
            catatan: 'Penetapan awal wali kelas 2026/2027'
        );
    }

    protected function createStudentWithEnrollment(
        string $nama,
        string $nis,
        Kelas $kelas,
        TahunAjaran $ta,
        Angkatan $angkatan,
        int $entryYear = 2026
    ): Siswa {
        $user = User::factory()->create([
            'name'  => $nama,
            'role'  => 'siswa',
            'email' => "{$nis}@student.myskanic.test",
        ]);

        $siswa = Siswa::create([
            'user_id'               => $user->id,
            'nis'                   => $nis,
            'nisn'                  => '00' . $nis,
            'nama'                  => $nama,
            'jenis_kelamin'         => 'L',
            'kelas_id'              => $kelas->id,
            'angkatan_id'           => $angkatan->id,
            'tahun_masuk'           => $entryYear,
            'tahun_ajaran_masuk_id' => $ta->id,
            'tahun_ajaran_masuk'    => "{$entryYear}/" . ($entryYear + 1),
            'status'                => 'aktif',
            'jalur_masuk'           => 'baru',
            'tempat_lahir'          => 'Bogor',
            'tanggal_lahir'         => '2009-05-15',
        ]);

        $homeroomSnapshot = $this->historyService->resolveHomeroomSnapshot($kelas->id, $ta->id);

        StudentEnrollment::create([
            'siswa_id'                  => $siswa->id,
            'tahun_ajaran_id'           => $ta->id,
            'tahun_ajaran'              => $ta->nama,
            'kelas_id'                  => $kelas->id,
            'jurusan_id'                => $kelas->jurusan_id,
            'angkatan_id'               => $angkatan->id,
            'tingkat'                   => $kelas->tingkat ?: 10,
            'started_at'                => Carbon::parse('2026-07-15'),
            'ended_at'                  => null,
            'is_active'                 => true,
            'status_enrollment'         => 'aktif',
            'homeroom_assignment_id'    => $homeroomSnapshot['homeroom_assignment_id'],
            'wali_kelas_guru_id'        => $homeroomSnapshot['wali_kelas_guru_id'],
            'wali_kelas_nama'           => $homeroomSnapshot['wali_kelas_nama'],
            'status_verifikasi_histori' => 'terverifikasi',
        ]);

        return $siswa;
    }

    /**
     * Requirement 2 & 5: Full Realistic Simulation of Class Promotion, Retention, Graduation, and Homeroom Transfer.
     */
    public function test_realistic_operational_simulation_full_lifecycle(): void
    {
        // 1. Setup Student 1 (Promoting from X to XI)
        $siswaPromote = $this->createStudentWithEnrollment('Rizki Pratama', '26001', $this->kelas10Pplg1_2026, $this->ta2026, $this->angkatan18, 2026);

        // 2. Setup Student 2 (Retaining / Tinggal Kelas in Grade 10)
        $siswaRetain = $this->createStudentWithEnrollment('Dimas Setiawan', '26002', $this->kelas10Pplg1_2026, $this->ta2026, $this->angkatan18, 2026);

        // 3. Setup Student 3 (Graduating from Grade 12)
        $siswaGraduate = $this->createStudentWithEnrollment('Alamsyah Alumni', '24001', $this->kelas12Pplg1_2026, $this->ta2026, $this->angkatan17, 2024);

        // Verify initial state
        $this->assertEquals(1, StudentEnrollment::where('siswa_id', $siswaPromote->id)->where('is_active', true)->count());
        $this->assertEquals(1, StudentEnrollment::where('siswa_id', $siswaRetain->id)->where('is_active', true)->count());
        $this->assertEquals(1, StudentEnrollment::where('siswa_id', $siswaGraduate->id)->where('is_active', true)->count());

        // Initial Homeroom in 2026/2027 is Guru A
        $initialEnrollmentPromote = StudentEnrollment::where('siswa_id', $siswaPromote->id)->first();
        $this->assertEquals($this->guruA->nama, $initialEnrollmentPromote->wali_kelas_nama);

        // Prepare Wizard Promotion Payload for transition 2026/2027 -> 2027/2028:
        // - XI PPLG 1 homeroom is assigned to Guru B
        // - X PPLG 2 homeroom is assigned to Guru A (proving Guru A can be assigned to another class in new TA)
        $payload = [
            'tahun_ajaran_asal_id'   => $this->ta2026->id,
            'tahun_ajaran_tujuan_id' => $this->ta2027->id,
            'catatan'                => 'Simulasi operasional kenaikan kelas SMKN 1 Ciomas',
            'homeroom_assignments'   => [
                ['kelas_id' => $this->kelas11Pplg1_2027->id, 'guru_id' => $this->guruB->id],
                ['kelas_id' => $this->kelas10Pplg2_2027->id, 'guru_id' => $this->guruA->id], // Guru A takes X PPLG 2 in 2027/2028
            ],
            'students'               => [
                [
                    'siswa_id'        => $siswaPromote->id,
                    'action'          => 'naik_kelas',
                    'kelas_tujuan_id' => $this->kelas11Pplg1_2027->id,
                ],
                [
                    'siswa_id'        => $siswaRetain->id,
                    'action'          => 'tinggal_kelas',
                    'kelas_tujuan_id' => $this->kelas10Pplg1_2027->id,
                    'alasan'          => 'Tinggal kelas evaluasi capaian kompetensi',
                ],
                [
                    'siswa_id'        => $siswaGraduate->id,
                    'action'          => 'lulus',
                    'catatan'         => 'Lulus resmi penuntasan kurikulum SMK',
                ],
            ],
        ];

        // Execute batch through controller endpoint
        $res = $this->actingAs($this->admin)->postJson(route('academic.promotion.execute'), $payload);
        $res->assertStatus(200);
        $res->assertJsonPath('success', true);
        $res->assertJsonPath('summary.total_siswa', 3);
        $res->assertJsonPath('summary.total_naik', 1);
        $res->assertJsonPath('summary.total_tinggal', 1);
        $res->assertJsonPath('summary.total_lulus', 1);

        // -------------------------------------------------------------
        // POST-EXECUTION VERIFICATION: STUDENT 1 (PROMOTED)
        // -------------------------------------------------------------
        $siswaPromote->refresh();
        $this->assertEquals('aktif', $siswaPromote->status);
        $this->assertEquals($this->kelas11Pplg1_2027->id, $siswaPromote->kelas_id);
        $this->assertEquals(2026, $siswaPromote->tahun_masuk); // Immutable entry year
        $this->assertEquals('2026/2027', $siswaPromote->tahun_ajaran_masuk); // Immutable entry TA
        $this->assertEquals($this->angkatan18->id, $siswaPromote->angkatan_id); // Immutable cohort

        // Check enrollments for Student 1: exactly 1 active, 1 ended
        $activeEnrollmentsP = StudentEnrollment::where('siswa_id', $siswaPromote->id)->where('is_active', true)->get();
        $this->assertCount(1, $activeEnrollmentsP);
        $newEnrP = $activeEnrollmentsP->first();
        $this->assertEquals($this->ta2027->id, $newEnrP->tahun_ajaran_id);
        $this->assertEquals($this->kelas11Pplg1_2027->id, $newEnrP->kelas_id);
        $this->assertEquals($this->guruB->nama, $newEnrP->wali_kelas_nama); // Snapshot updated to Guru B

        // Historical enrollment preserved with Guru A snapshot
        $oldEnrP = StudentEnrollment::where('siswa_id', $siswaPromote->id)->where('is_active', false)->first();
        $this->assertNotNull($oldEnrP);
        $this->assertEquals($this->ta2026->id, $oldEnrP->tahun_ajaran_id);
        $this->assertEquals($this->kelas10Pplg1_2026->id, $oldEnrP->kelas_id);
        $this->assertEquals($this->guruA->nama, $oldEnrP->wali_kelas_nama); // Guru A permanently frozen!
        $this->assertNotNull($oldEnrP->ended_at);

        // -------------------------------------------------------------
        // POST-EXECUTION VERIFICATION: STUDENT 2 (RETAINED)
        // -------------------------------------------------------------
        $siswaRetain->refresh();
        $this->assertEquals('aktif', $siswaRetain->status);
        $this->assertEquals($this->kelas10Pplg1_2027->id, $siswaRetain->kelas_id);
        $this->assertEquals(2026, $siswaRetain->tahun_masuk); // Cohort & entry year remain original
        $this->assertEquals($this->angkatan18->id, $siswaRetain->angkatan_id);

        $activeEnrR = StudentEnrollment::where('siswa_id', $siswaRetain->id)->where('is_active', true)->first();
        $this->assertNotNull($activeEnrR);
        $this->assertEquals($this->ta2027->id, $activeEnrR->tahun_ajaran_id);
        $this->assertEquals(10, $activeEnrR->tingkat); // Repeated grade 10

        $oldEnrR = StudentEnrollment::where('siswa_id', $siswaRetain->id)->where('is_active', false)->first();
        $this->assertEquals('tinggal_kelas', $oldEnrR->status_enrollment);

        // -------------------------------------------------------------
        // POST-EXECUTION VERIFICATION: STUDENT 3 (GRADUATED)
        // -------------------------------------------------------------
        $siswaGraduate->refresh();
        $this->assertEquals('lulus', $siswaGraduate->status);
        $this->assertEquals(2027, $siswaGraduate->tahun_lulus);
        $this->assertEquals('2026/2027', $siswaGraduate->tahun_ajaran_lulus);
        $this->assertNotNull($siswaGraduate->tanggal_kelulusan);

        // Zero active enrollments for graduated student
        $activeEnrG = StudentEnrollment::where('siswa_id', $siswaGraduate->id)->where('is_active', true)->count();
        $this->assertEquals(0, $activeEnrG);

        $oldEnrG = StudentEnrollment::where('siswa_id', $siswaGraduate->id)->where('is_active', false)->first();
        $this->assertEquals('lulus', $oldEnrG->status_enrollment);

        // -------------------------------------------------------------
        // REQUIREMENT 5: HOMEROOM INTEGRITY & MULTI-PERIOD VERIFICATION
        // -------------------------------------------------------------
        // In 2026/2027: Guru A was homeroom of X PPLG 1
        $ha2026 = HomeroomAssignment::where('guru_id', $this->guruA->id)->where('tahun_ajaran_id', $this->ta2026->id)->first();
        $this->assertNotNull($ha2026);
        $this->assertEquals($this->kelas10Pplg1_2026->id, $ha2026->kelas_id);

        // In 2027/2028: Guru A is homeroom of X PPLG 2
        $ha2027A = HomeroomAssignment::where('guru_id', $this->guruA->id)->where('tahun_ajaran_id', $this->ta2027->id)->first();
        $this->assertNotNull($ha2027A);
        $this->assertEquals($this->kelas10Pplg2_2027->id, $ha2027A->kelas_id);

        // In 2027/2028: Guru B is homeroom of XI PPLG 1
        $ha2027B = HomeroomAssignment::where('guru_id', $this->guruB->id)->where('tahun_ajaran_id', $this->ta2027->id)->first();
        $this->assertNotNull($ha2027B);
        $this->assertEquals($this->kelas11Pplg1_2027->id, $ha2027B->kelas_id);

        // Historical query resolution: Old enrollment displays Guru A, new displays Guru B
        $this->assertEquals($this->guruA->nama, $this->historyService->resolveHomeroomTeacherName($this->kelas10Pplg1_2026->id, $this->ta2026->id));
        $this->assertEquals($this->guruB->nama, $this->historyService->resolveHomeroomTeacherName($this->kelas11Pplg1_2027->id, $this->ta2027->id));
    }

    /**
     * Requirement 3: Simulated Concurrent Requests for the Same Student / Same Batch.
     */
    public function test_simulated_concurrent_requests_handled_safely(): void
    {
        $siswa = $this->createStudentWithEnrollment('Siswa Concurrency', '26099', $this->kelas10Pplg1_2026, $this->ta2026, $this->angkatan18);

        $payload = [
            'tahun_ajaran_asal_id'   => $this->ta2026->id,
            'tahun_ajaran_tujuan_id' => $this->ta2027->id,
            'students'               => [
                [
                    'siswa_id'        => $siswa->id,
                    'action'          => 'naik_kelas',
                    'kelas_tujuan_id' => $this->kelas11Pplg1_2027->id,
                ],
            ],
        ];

        // First request succeeds
        $res1 = $this->actingAs($this->admin)->postJson(route('academic.promotion.execute'), $payload);
        $res1->assertStatus(200);

        // Concurrent / duplicate request arriving immediately after must fail cleanly
        $res2 = $this->actingAs($this->admin)->postJson(route('academic.promotion.execute'), $payload);
        $res2->assertStatus(422);

        // Verify zero duplicate active enrollments in target TA
        $activeCount = StudentEnrollment::where('siswa_id', $siswa->id)
            ->where('tahun_ajaran_id', $this->ta2027->id)
            ->where('is_active', true)
            ->count();
        $this->assertEquals(1, $activeCount);

        // Verify total promotion items logged for this student is exactly 1
        $this->assertEquals(1, AcademicPromotionItem::where('siswa_id', $siswa->id)->count());
    }

    /**
     * Requirement 4: Academic Year Query Isolation Across Modules.
     */
    public function test_academic_year_isolation_across_modules(): void
    {
        $activeStudent = $this->createStudentWithEnrollment('Siswa Masih Aktif', '26081', $this->kelas10Pplg1_2026, $this->ta2026, $this->angkatan18);
        $graduatedStudent = $this->createStudentWithEnrollment('Siswa Alumni Kelulusan', '24082', $this->kelas12Pplg1_2026, $this->ta2026, $this->angkatan17);

        // Graduate second student
        $this->historyService->graduateStudent($graduatedStudent, $this->ta2026);
        $graduatedStudent->refresh();

        // 1. Absensi query test: only active students in class appear
        $activeRoster = Siswa::where('status', 'aktif')
            ->where('kelas_id', $this->kelas12Pplg1_2026->id)
            ->pluck('id');
        $this->assertFalse($activeRoster->contains($graduatedStudent->id));

        // 2. Promotion wizard student fetch: only active students in selected origin classes
        $originStudents = $this->promotionService->getOriginStudents($this->ta2026->id, [$this->kelas12Pplg1_2026->id]);
        $this->assertEquals(0, $originStudents->count()); // Graduated student is not offered for promotion!

        // 3. Historical enrollment lookup remains intact
        $gradHistory = StudentEnrollment::where('siswa_id', $graduatedStudent->id)->first();
        $this->assertNotNull($gradHistory);
        $this->assertEquals('lulus', $gradHistory->status_enrollment);
        $this->assertFalse($gradHistory->is_active);
    }
}
