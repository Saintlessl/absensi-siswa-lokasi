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
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AcademicPromotionWizardTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $guruUser;
    protected User $studentUser;

    protected TahunAjaran $ta2025;
    protected TahunAjaran $ta2026;
    protected TahunAjaran $ta2027;

    protected Jurusan $jurusanPplg;
    protected Angkatan $angkatan18;
    protected Angkatan $angkatan17;

    protected Kelas $kelas10Pplg1_2025;
    protected Kelas $kelas11Pplg1_2025;
    protected Kelas $kelas12Pplg1_2025;

    protected Kelas $kelas10Pplg1_2026;
    protected Kelas $kelas11Pplg1_2026;
    protected Kelas $kelas12Pplg1_2026;

    protected Guru $guruA;
    protected Guru $guruB;
    protected Guru $guruC;

    protected AcademicPromotionService $promotionService;
    protected StudentAcademicHistoryService $historyService;
    protected TeacherAssignmentService $teacherService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->promotionService = app(AcademicPromotionService::class);
        $this->historyService = app(StudentAcademicHistoryService::class);
        $this->teacherService = app(TeacherAssignmentService::class);

        // Users
        $this->admin = User::factory()->create([
            'name'  => 'Administrator Sekolah',
            'role'  => 'admin',
            'email' => 'admin.promo@myskanic.test',
        ]);

        $this->guruUser = User::factory()->create([
            'name'  => 'Guru Pengajar',
            'role'  => 'guru',
            'email' => 'guru.pengajar@myskanic.test',
        ]);

        $this->studentUser = User::factory()->create([
            'name'  => 'User Siswa',
            'role'  => 'siswa',
            'email' => 'siswa@myskanic.test',
        ]);

        // Academic Years (using firstOrCreate to avoid baseline seeder conflicts)
        $this->ta2025 = TahunAjaran::firstOrCreate(
            ['nama' => '2025/2026'],
            [
                'tahun_mulai'     => 2025,
                'tahun_selesai'   => 2026,
                'semester_aktif'  => 'genap',
                'tanggal_mulai'   => '2025-07-15',
                'tanggal_selesai' => '2026-06-25',
                'is_active'       => false,
                'status'          => 'selesai',
            ]
        );

        $this->ta2026 = TahunAjaran::firstOrCreate(
            ['nama' => '2026/2027'],
            [
                'tahun_mulai'     => 2026,
                'tahun_selesai'   => 2027,
                'semester_aktif'  => 'ganjil',
                'tanggal_mulai'   => '2026-07-15',
                'tanggal_selesai' => '2027-06-25',
                'is_active'       => true,
                'status'          => 'aktif',
            ]
        );
        $this->ta2026->update(['is_active' => true, 'status' => 'aktif']);

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

        // Major
        $this->jurusanPplg = Jurusan::create([
            'nama_jurusan' => 'Pengembangan Perangkat Lunak dan Gim',
            'kode_jurusan' => 'PPLG',
        ]);

        // Cohorts
        $this->angkatan18 = Angkatan::create([
            'nama_angkatan' => 'Angkatan 18 PPLG (Masuk 2025)',
            'nomor_angkatan'=> 18,
            'kode_angkatan' => 'AKT-PPLG-18',
            'tahun_masuk'   => 2025,
            'jurusan_id'    => $this->jurusanPplg->id,
            'status'        => 'aktif',
            'is_active'     => true,
        ]);

        $this->angkatan17 = Angkatan::create([
            'nama_angkatan' => 'Angkatan 17 PPLG (Masuk 2024)',
            'nomor_angkatan'=> 17,
            'kode_angkatan' => 'AKT-PPLG-17',
            'tahun_masuk'   => 2024,
            'jurusan_id'    => $this->jurusanPplg->id,
            'status'        => 'aktif',
            'is_active'     => true,
        ]);

        // Classes for 2025/2026 (Origin TA)
        $this->kelas10Pplg1_2025 = Kelas::create([
            'nama_kelas'      => 'X PPLG 1',
            'tingkat'         => 10,
            'nomor_rombel'    => 1,
            'jurusan_id'      => $this->jurusanPplg->id,
            'tahun_ajaran'    => '2025/2026',
            'tahun_ajaran_id' => $this->ta2025->id,
            'jumlah_siswa'    => 0,
            'is_active'       => true,
        ]);

        $this->kelas11Pplg1_2025 = Kelas::create([
            'nama_kelas'      => 'XI PPLG 1',
            'tingkat'         => 11,
            'nomor_rombel'    => 1,
            'jurusan_id'      => $this->jurusanPplg->id,
            'tahun_ajaran'    => '2025/2026',
            'tahun_ajaran_id' => $this->ta2025->id,
            'jumlah_siswa'    => 0,
            'is_active'       => true,
        ]);

        $this->kelas12Pplg1_2025 = Kelas::create([
            'nama_kelas'      => 'XII PPLG 1',
            'tingkat'         => 12,
            'nomor_rombel'    => 1,
            'jurusan_id'      => $this->jurusanPplg->id,
            'tahun_ajaran'    => '2025/2026',
            'tahun_ajaran_id' => $this->ta2025->id,
            'jumlah_siswa'    => 0,
            'is_active'       => true,
        ]);

        // Classes for 2026/2027 (Destination TA)
        $this->kelas10Pplg1_2026 = Kelas::create([
            'nama_kelas'      => 'X PPLG 1',
            'tingkat'         => 10,
            'nomor_rombel'    => 1,
            'jurusan_id'      => $this->jurusanPplg->id,
            'tahun_ajaran'    => '2026/2027',
            'tahun_ajaran_id' => $this->ta2026->id,
            'jumlah_siswa'    => 0,
            'is_active'       => true,
        ]);

        $this->kelas11Pplg1_2026 = Kelas::create([
            'nama_kelas'      => 'XI PPLG 1',
            'tingkat'         => 11,
            'nomor_rombel'    => 1,
            'jurusan_id'      => $this->jurusanPplg->id,
            'tahun_ajaran'    => '2026/2027',
            'tahun_ajaran_id' => $this->ta2026->id,
            'jumlah_siswa'    => 0,
            'is_active'       => true,
        ]);

        $this->kelas12Pplg1_2026 = Kelas::create([
            'nama_kelas'      => 'XII PPLG 1',
            'tingkat'         => 12,
            'nomor_rombel'    => 1,
            'jurusan_id'      => $this->jurusanPplg->id,
            'tahun_ajaran'    => '2026/2027',
            'tahun_ajaran_id' => $this->ta2026->id,
            'jumlah_siswa'    => 0,
            'is_active'       => true,
        ]);

        // Teachers
        $userGuruA = User::factory()->create(['role' => 'guru', 'status' => 'active']);
        $this->guruA = Guru::create([
            'user_id'   => $userGuruA->id,
            'nip'       => '198101012005011001',
            'nama'      => 'Budi Santoso, S.Kom.',
            'is_active' => true,
        ]);

        $userGuruB = User::factory()->create(['role' => 'guru', 'status' => 'active']);
        $this->guruB = Guru::create([
            'user_id'   => $userGuruB->id,
            'nip'       => '198202022005021002',
            'nama'      => 'Siti Rahma, M.Pd.',
            'is_active' => true,
        ]);

        $userGuruC = User::factory()->create(['role' => 'guru', 'status' => 'active']);
        $this->guruC = Guru::create([
            'user_id'   => $userGuruC->id,
            'nip'       => '198303032005031003',
            'nama'      => 'Ahmad Fauzi, S.T.',
            'is_active' => true,
        ]);

        // Assign Homeroom in origin TA 2025/2026
        $this->teacherService->assignHomeroom(
            guruId: $this->guruA->id,
            kelasId: $this->kelas10Pplg1_2025->id,
            tahunAjaran: $this->ta2025->id,
            catatan: 'Wali Kelas X PPLG 1 T.A. 2025/2026'
        );
    }

    /**
     * Helper to create a student with an active enrollment in origin TA.
     */
    protected function createEnrolledStudent(
        string $nama,
        string $nis,
        Kelas $kelas,
        TahunAjaran $ta,
        Angkatan $angkatan,
        int $tahunMasuk = 2025,
        string $tahunAjaranMasuk = '2025/2026'
    ): Siswa {
        $user = User::factory()->create([
            'name'     => $nama,
            'email'    => "siswa.{$nis}@myskanic.test",
            'role'     => 'siswa',
        ]);

        $siswa = Siswa::create([
            'user_id'               => $user->id,
            'nis'                   => $nis,
            'nisn'                  => '00' . $nis,
            'nama'                  => $nama,
            'jenis_kelamin'         => 'L',
            'kelas_id'              => $kelas->id,
            'angkatan_id'           => $angkatan->id,
            'tahun_ajaran_masuk_id' => $ta->id,
            'tahun_ajaran_masuk'    => $tahunAjaranMasuk,
            'tahun_masuk'           => $tahunMasuk,
            'status'                => 'aktif',
            'jalur_masuk'           => 'baru',
        ]);

        $snapshot = $this->historyService->resolveHomeroomSnapshot($kelas->id, $ta->id);

        StudentEnrollment::create([
            'siswa_id'                  => $siswa->id,
            'tahun_ajaran_id'           => $ta->id,
            'tahun_ajaran'              => $ta->nama,
            'kelas_id'                  => $kelas->id,
            'jurusan_id'                => $kelas->jurusan_id,
            'angkatan_id'               => $angkatan->id,
            'tingkat'                   => $kelas->tingkat,
            'started_at'                => Carbon::parse('2025-07-15'),
            'is_active'                 => true,
            'status_enrollment'         => 'aktif',
            'homeroom_assignment_id'    => $snapshot['homeroom_assignment_id'],
            'wali_kelas_guru_id'        => $snapshot['wali_kelas_guru_id'],
            'wali_kelas_nama'           => $snapshot['wali_kelas_nama'],
            'status_verifikasi_histori' => 'terverifikasi',
            'catatan'                   => 'Enrollment origin setup',
        ]);

        return $siswa;
    }

    /**
     * Test 1: Access control - admin can access, non-admin blocked (403).
     */
    public function test_access_authorization_for_promotion_wizard(): void
    {
        // Admin can access
        $resAdmin = $this->actingAs($this->admin)->get(route('academic.promotion.index'));
        $resAdmin->assertOk();
        $resAdmin->assertViewIs('academic.promotion.wizard');

        // Guru is blocked (redirected or forbidden JSON)
        $resGuru = $this->actingAs($this->guruUser)->get(route('academic.promotion.index'));
        $resGuru->assertRedirect(route('dashboard'));
        $resGuruJson = $this->actingAs($this->guruUser)->getJson(route('academic.promotion.index'));
        $resGuruJson->assertForbidden();

        // Student is blocked (redirected or forbidden JSON)
        $resStudent = $this->actingAs($this->studentUser)->get(route('academic.promotion.index'));
        $resStudent->assertRedirect(route('dashboard'));
        $resStudentJson = $this->actingAs($this->studentUser)->getJson(route('academic.promotion.index'));
        $resStudentJson->assertForbidden();
    }

    /**
     * Test 2: Origin classes & Load students AJAX endpoints.
     */
    public function test_origin_classes_and_load_students_ajax(): void
    {
        $siswa = $this->createEnrolledStudent('Ahmad Dani', '10101', $this->kelas10Pplg1_2025, $this->ta2025, $this->angkatan18);

        // Fetch origin classes
        $resClasses = $this->actingAs($this->admin)->getJson(
            route('academic.promotion.origin_classes', ['origin_ta_id' => $this->ta2025->id])
        );
        $resClasses->assertOk();
        $resClasses->assertJson(['success' => true]);
        $this->assertNotEmpty($resClasses->json('classes'));

        // Load students
        $resStudents = $this->actingAs($this->admin)->postJson(route('academic.promotion.load_students'), [
            'origin_ta_id' => $this->ta2025->id,
            'kelas_ids'    => [$this->kelas10Pplg1_2025->id],
        ]);
        $resStudents->assertOk();
        $resStudents->assertJson(['success' => true, 'total' => 1]);
        $this->assertEquals('naik_kelas', $resStudents->json('students.0.default_action'));
        $this->assertEquals(11, $resStudents->json('students.0.recommended_target_tingkat'));
    }

    /**
     * Test 3: Regular promotion (Grade 10 -> Grade 11) preserves permanent identity and creates new enrollment.
     */
    public function test_regular_promotion_grade_10_to_11_preserves_permanent_identity(): void
    {
        $siswa = $this->createEnrolledStudent(
            'Budi Setiawan',
            '10102',
            $this->kelas10Pplg1_2025,
            $this->ta2025,
            $this->angkatan18,
            2025,
            '2025/2026'
        );

        $payload = [
            'tahun_ajaran_asal_id'   => $this->ta2025->id,
            'tahun_ajaran_tujuan_id' => $this->ta2026->id,
            'catatan'                => 'Kenaikan kelas reguler X ke XI',
            'students'               => [
                [
                    'siswa_id'        => $siswa->id,
                    'action'          => 'naik_kelas',
                    'kelas_tujuan_id' => $this->kelas11Pplg1_2026->id,
                    'catatan'         => 'Nilai akademik memenuhi syarat naik kelas',
                ],
            ],
            'homeroom_assignments' => [
                [
                    'kelas_id' => $this->kelas11Pplg1_2026->id,
                    'guru_id'  => $this->guruB->id,
                ],
            ],
        ];

        $res = $this->actingAs($this->admin)->postJson(route('academic.promotion.execute'), $payload);
        $res->assertOk();
        $res->assertJson(['success' => true]);

        // 1. Verify old enrollment is closed
        $oldEnrollment = StudentEnrollment::where('siswa_id', $siswa->id)
            ->where('tahun_ajaran_id', $this->ta2025->id)
            ->first();
        $this->assertNotNull($oldEnrollment);
        $this->assertFalse($oldEnrollment->is_active);
        $this->assertEquals('selesai', $oldEnrollment->status_enrollment);
        $this->assertEquals($this->kelas10Pplg1_2025->id, $oldEnrollment->kelas_id);
        $this->assertEquals('Budi Santoso, S.Kom.', $oldEnrollment->wali_kelas_nama); // preserved snapshot

        // 2. Verify new enrollment is active in target TA
        $newEnrollment = StudentEnrollment::where('siswa_id', $siswa->id)
            ->where('tahun_ajaran_id', $this->ta2026->id)
            ->first();
        $this->assertNotNull($newEnrollment);
        $this->assertTrue($newEnrollment->is_active);
        $this->assertEquals('aktif', $newEnrollment->status_enrollment);
        $this->assertEquals($this->kelas11Pplg1_2026->id, $newEnrollment->kelas_id);
        $this->assertEquals(11, $newEnrollment->tingkat);
        $this->assertEquals('Siti Rahma, M.Pd.', $newEnrollment->wali_kelas_nama); // new snapshot

        // 3. Exactly ONE active enrollment exists for this student
        $activeCount = StudentEnrollment::where('siswa_id', $siswa->id)->where('is_active', true)->count();
        $this->assertEquals(1, $activeCount);

        // 4. CRITICAL: Permanent entry year & cohort NEVER CHANGED!
        $siswa->refresh();
        $this->assertEquals(2025, $siswa->tahun_masuk);
        $this->assertEquals('2025/2026', $siswa->tahun_ajaran_masuk);
        $this->assertEquals($this->angkatan18->id, $siswa->angkatan_id);
        $this->assertEquals($this->kelas11Pplg1_2026->id, $siswa->kelas_id); // pointer updated

        // 5. Audit trail in SiswaStatusHistory
        $this->assertDatabaseHas('siswa_status_histories', [
            'siswa_id'               => $siswa->id,
            'jenis_perubahan'        => 'kenaikan_kelas',
            'tahun_ajaran_asal_id'   => $this->ta2025->id,
            'tahun_ajaran_tujuan_id' => $this->ta2026->id,
            'kelas_asal_id'          => $this->kelas10Pplg1_2025->id,
            'kelas_tujuan_id'        => $this->kelas11Pplg1_2026->id,
        ]);
    }

    /**
     * Test 4: Grade 12 student graduation marks alumnus without active destination enrollment.
     */
    public function test_grade_12_student_graduation(): void
    {
        $siswa12 = $this->createEnrolledStudent(
            'Calon Alumni',
            '10103',
            $this->kelas12Pplg1_2025,
            $this->ta2025,
            $this->angkatan17,
            2024,
            '2024/2025'
        );

        $payload = [
            'tahun_ajaran_asal_id'   => $this->ta2025->id,
            'tahun_ajaran_tujuan_id' => $this->ta2026->id,
            'catatan'                => 'Kelulusan massal Angkatan 17',
            'students'               => [
                [
                    'siswa_id' => $siswa12->id,
                    'action'   => 'lulus',
                    'catatan'  => 'Lulus dengan predikat sangat memuaskan',
                ],
            ],
        ];

        $res = $this->actingAs($this->admin)->postJson(route('academic.promotion.execute'), $payload);
        $res->assertOk();

        // 1. Origin enrollment closed with status 'lulus'
        $enrollment = StudentEnrollment::where('siswa_id', $siswa12->id)
            ->where('tahun_ajaran_id', $this->ta2025->id)
            ->first();
        $this->assertNotNull($enrollment);
        $this->assertFalse($enrollment->is_active);
        $this->assertEquals('lulus', $enrollment->status_enrollment);

        // 2. NO active enrollment in destination TA 2026/2027
        $destEnrollment = StudentEnrollment::where('siswa_id', $siswa12->id)
            ->where('tahun_ajaran_id', $this->ta2026->id)
            ->first();
        $this->assertNull($destEnrollment);

        // 3. Siswa status is 'lulus'
        $siswa12->refresh();
        $this->assertEquals('lulus', $siswa12->status);
        $this->assertNotNull($siswa12->tanggal_kelulusan);
        $this->assertEquals(2026, $siswa12->tahun_lulus);
        $this->assertEquals('2025/2026', $siswa12->tahun_ajaran_lulus);

        // 4. Permanent cohort remains untouched
        $this->assertEquals($this->angkatan17->id, $siswa12->angkatan_id);
        $this->assertEquals(2024, $siswa12->tahun_masuk);
    }

    /**
     * Test 5: Retained student repeats grade with unchanged cohort & permanent entry year.
     */
    public function test_student_retention_tinggal_kelas(): void
    {
        $siswaRetained = $this->createEnrolledStudent(
            'Siswa Mengulang',
            '10104',
            $this->kelas10Pplg1_2025,
            $this->ta2025,
            $this->angkatan18,
            2025,
            '2025/2026'
        );

        $payload = [
            'tahun_ajaran_asal_id'   => $this->ta2025->id,
            'tahun_ajaran_tujuan_id' => $this->ta2026->id,
            'catatan'                => 'Evaluasi kenaikan kelas',
            'students'               => [
                [
                    'siswa_id'        => $siswaRetained->id,
                    'action'          => 'tinggal_kelas',
                    'kelas_tujuan_id' => $this->kelas10Pplg1_2026->id, // repeats grade 10 in destination TA
                    'alasan'          => 'Kehadiran di bawah 75% dan kompetensi dasar belum tuntas',
                ],
            ],
        ];

        $res = $this->actingAs($this->admin)->postJson(route('academic.promotion.execute'), $payload);
        $res->assertOk();

        // 1. Old enrollment marked 'tinggal_kelas'
        $oldEnrollment = StudentEnrollment::where('siswa_id', $siswaRetained->id)
            ->where('tahun_ajaran_id', $this->ta2025->id)
            ->first();
        $this->assertFalse($oldEnrollment->is_active);
        $this->assertEquals('tinggal_kelas', $oldEnrollment->status_enrollment);

        // 2. New enrollment is active in grade 10
        $newEnrollment = StudentEnrollment::where('siswa_id', $siswaRetained->id)
            ->where('tahun_ajaran_id', $this->ta2026->id)
            ->first();
        $this->assertTrue($newEnrollment->is_active);
        $this->assertEquals(10, $newEnrollment->tingkat);
        $this->assertEquals($this->kelas10Pplg1_2026->id, $newEnrollment->kelas_id);

        // 3. CRITICAL: Cohort and permanent entry year are NOT CHANGED
        $siswaRetained->refresh();
        $this->assertEquals(2025, $siswaRetained->tahun_masuk);
        $this->assertEquals('2025/2026', $siswaRetained->tahun_ajaran_masuk);
        $this->assertEquals($this->angkatan18->id, $siswaRetained->angkatan_id);
    }

    /**
     * Test 6: School transfer out (pindah) & drop out (keluar).
     */
    public function test_transfer_out_and_dropout(): void
    {
        $siswaPindah = $this->createEnrolledStudent('Siswa Pindah', '10105', $this->kelas10Pplg1_2025, $this->ta2025, $this->angkatan18);
        $siswaKeluar = $this->createEnrolledStudent('Siswa Keluar', '10106', $this->kelas10Pplg1_2025, $this->ta2025, $this->angkatan18);

        $payload = [
            'tahun_ajaran_asal_id'   => $this->ta2025->id,
            'tahun_ajaran_tujuan_id' => $this->ta2026->id,
            'students'               => [
                [
                    'siswa_id'       => $siswaPindah->id,
                    'action'         => 'pindah',
                    'sekolah_tujuan' => 'SMK Negeri 2 Bogor',
                    'nomor_surat'    => '421/100/SMK-1/2026',
                    'alasan'         => 'Mengikuti perpindahan tugas orang tua',
                ],
                [
                    'siswa_id' => $siswaKeluar->id,
                    'action'   => 'keluar',
                    'alasan'   => 'Mengundurkan diri bekerja',
                ],
            ],
        ];

        $res = $this->actingAs($this->admin)->postJson(route('academic.promotion.execute'), $payload);
        $res->assertOk();

        // Check siswa pindah
        $siswaPindah->refresh();
        $this->assertEquals('pindah', $siswaPindah->status);
        $this->assertFalse(StudentEnrollment::where('siswa_id', $siswaPindah->id)->where('is_active', true)->exists());

        // Check siswa keluar
        $siswaKeluar->refresh();
        $this->assertEquals('keluar', $siswaKeluar->status);
        $this->assertFalse(StudentEnrollment::where('siswa_id', $siswaKeluar->id)->where('is_active', true)->exists());
    }

    /**
     * Test 7: Homeroom assignment in destination TA preserves origin TA history.
     */
    public function test_homeroom_assignment_preserves_past_history(): void
    {
        // Guru A was homeroom of X PPLG 1 in 2025/2026.
        // Guru A is now assigned to XI PPLG 1 in 2026/2027.
        $this->teacherService->assignHomeroom(
            guruId: $this->guruA->id,
            kelasId: $this->kelas11Pplg1_2026->id,
            tahunAjaran: $this->ta2026->id,
            catatan: 'Penugasan T.A. Baru'
        );

        // Verify assignment in 2026/2027 exists
        $newAssignment = HomeroomAssignment::where('guru_id', $this->guruA->id)
            ->where('tahun_ajaran_id', $this->ta2026->id)
            ->where('is_active', true)
            ->first();
        $this->assertNotNull($newAssignment);
        $this->assertEquals($this->kelas11Pplg1_2026->id, $newAssignment->kelas_id);

        // Verify past assignment in 2025/2026 is still preserved!
        $pastAssignment = HomeroomAssignment::where('guru_id', $this->guruA->id)
            ->where('tahun_ajaran_id', $this->ta2025->id)
            ->first();
        $this->assertNotNull($pastAssignment);
        $this->assertEquals($this->kelas10Pplg1_2025->id, $pastAssignment->kelas_id);
    }

    /**
     * Test 8: One teacher cannot have two active homerooms in the same academic year.
     */
    public function test_one_teacher_cannot_have_two_active_homeroom_classes_in_same_ta(): void
    {
        $this->teacherService->assignHomeroom(
            guruId: $this->guruB->id,
            kelasId: $this->kelas10Pplg1_2026->id,
            tahunAjaran: $this->ta2026->id
        );

        $this->expectException(ValidationException::class);

        // Try to assign same teacher to another class in same academic year
        $this->teacherService->assignHomeroom(
            guruId: $this->guruB->id,
            kelasId: $this->kelas11Pplg1_2026->id,
            tahunAjaran: $this->ta2026->id
        );
    }

    /**
     * Test 9: Batch tracking table and audit logging.
     */
    public function test_batch_tracking_table_and_item_logging(): void
    {
        $siswa1 = $this->createEnrolledStudent('Siswa 1', '10107', $this->kelas10Pplg1_2025, $this->ta2025, $this->angkatan18);
        $siswa2 = $this->createEnrolledStudent('Siswa 2', '10108', $this->kelas12Pplg1_2025, $this->ta2025, $this->angkatan17);

        $payload = [
            'tahun_ajaran_asal_id'   => $this->ta2025->id,
            'tahun_ajaran_tujuan_id' => $this->ta2026->id,
            'catatan'                => 'Batch Kenaikan Pleno 2026',
            'students'               => [
                [
                    'siswa_id'        => $siswa1->id,
                    'action'          => 'naik_kelas',
                    'kelas_tujuan_id' => $this->kelas11Pplg1_2026->id,
                ],
                [
                    'siswa_id' => $siswa2->id,
                    'action'   => 'lulus',
                ],
            ],
        ];

        $res = $this->actingAs($this->admin)->postJson(route('academic.promotion.execute'), $payload);
        $res->assertOk();

        $batchId = $res->json('batch_id');
        $batch = AcademicPromotionBatch::with('items')->find($batchId);

        $this->assertNotNull($batch);
        $this->assertEquals(2, $batch->total_siswa);
        $this->assertEquals(1, $batch->total_naik);
        $this->assertEquals(1, $batch->total_lulus);
        $this->assertEquals('completed', $batch->status);
        $this->assertCount(2, $batch->items);

        // Verify history endpoint
        $resHistory = $this->actingAs($this->admin)->get(route('academic.promotion.history'));
        $resHistory->assertOk();
        $resHistory->assertSee($batch->batch_code);

        // Verify show detail endpoint
        $resShow = $this->actingAs($this->admin)->get(route('academic.promotion.show', $batch->id));
        $resShow->assertOk();
        $resShow->assertSee($siswa1->nama);
        $resShow->assertSee($siswa2->nama);
    }

    /**
     * Test 10: Validation rejects missing target class for promoting students.
     */
    public function test_unallocated_promoting_student_is_rejected(): void
    {
        $siswa = $this->createEnrolledStudent('Siswa Tanpa Kelas', '10109', $this->kelas10Pplg1_2025, $this->ta2025, $this->angkatan18);

        $payload = [
            'tahun_ajaran_asal_id'   => $this->ta2025->id,
            'tahun_ajaran_tujuan_id' => $this->ta2026->id,
            'students'               => [
                [
                    'siswa_id'        => $siswa->id,
                    'action'          => 'naik_kelas',
                    'kelas_tujuan_id' => null, // Missing destination class!
                ],
            ],
        ];

        $res = $this->actingAs($this->admin)->postJson(route('academic.promotion.execute'), $payload);
        $res->assertStatus(422);
    }

    /**
     * Test 11: Replicate classes endpoint creates destination classes cleanly.
     */
    public function test_replicate_destination_classes_endpoint(): void
    {
        $res = $this->actingAs($this->admin)->postJson(route('academic.promotion.replicate_classes'), [
            'origin_ta_id' => $this->ta2026->id,
            'dest_ta_id'   => $this->ta2027->id,
        ]);

        $res->assertOk();
        $res->assertJson(['success' => true]);

        // Verify classes were created in ta2027
        $count = Kelas::where('tahun_ajaran_id', $this->ta2027->id)->count();
        $this->assertGreaterThan(0, $count);
    }

    /**
     * Test 12: Grade 11 to 12 promotion.
     */
    public function test_grade_11_to_12_promotion(): void
    {
        $siswa11 = $this->createEnrolledStudent(
            'Fajar Ramadhan',
            '10110',
            $this->kelas11Pplg1_2025,
            $this->ta2025,
            $this->angkatan17,
            2024,
            '2024/2025'
        );

        $payload = [
            'tahun_ajaran_asal_id'   => $this->ta2025->id,
            'tahun_ajaran_tujuan_id' => $this->ta2026->id,
            'students'               => [
                [
                    'siswa_id'        => $siswa11->id,
                    'action'          => 'naik_kelas',
                    'kelas_tujuan_id' => $this->kelas12Pplg1_2026->id,
                    'catatan'         => 'Naik ke tingkat akhir XII',
                ],
            ],
        ];

        $res = $this->actingAs($this->admin)->postJson(route('academic.promotion.execute'), $payload);
        $res->assertOk();

        // Old enrollment closed
        $old = StudentEnrollment::where('siswa_id', $siswa11->id)->where('tahun_ajaran_id', $this->ta2025->id)->first();
        $this->assertFalse($old->is_active);
        $this->assertEquals('selesai', $old->status_enrollment);

        // New enrollment active in Grade 12
        $new = StudentEnrollment::where('siswa_id', $siswa11->id)->where('tahun_ajaran_id', $this->ta2026->id)->first();
        $this->assertTrue($new->is_active);
        $this->assertEquals(12, $new->tingkat);
        $this->assertEquals($this->kelas12Pplg1_2026->id, $new->kelas_id);

        // Permanent cohort untouched
        $siswa11->refresh();
        $this->assertEquals($this->angkatan17->id, $siswa11->angkatan_id);
        $this->assertEquals(2024, $siswa11->tahun_masuk);
    }

    /**
     * Test 13: Prevent duplicate active enrollment in destination TA.
     */
    public function test_prevent_duplicate_active_enrollment_in_destination_ta(): void
    {
        $siswa = $this->createEnrolledStudent('Siswa Duplikat', '10111', $this->kelas10Pplg1_2025, $this->ta2025, $this->angkatan18);

        // Manually pre-create an active enrollment in destination TA (simulate prior enrollment)
        StudentEnrollment::create([
            'siswa_id'                  => $siswa->id,
            'tahun_ajaran_id'           => $this->ta2026->id,
            'tahun_ajaran'              => $this->ta2026->nama,
            'kelas_id'                  => $this->kelas11Pplg1_2026->id,
            'jurusan_id'                => $this->jurusanPplg->id,
            'angkatan_id'               => $this->angkatan18->id,
            'tingkat'                   => 11,
            'is_active'                 => true,
            'status_enrollment'         => 'aktif',
            'status_verifikasi_histori' => 'terverifikasi',
        ]);

        $payload = [
            'tahun_ajaran_asal_id'   => $this->ta2025->id,
            'tahun_ajaran_tujuan_id' => $this->ta2026->id,
            'students'               => [
                [
                    'siswa_id'        => $siswa->id,
                    'action'          => 'naik_kelas',
                    'kelas_tujuan_id' => $this->kelas11Pplg1_2026->id,
                ],
            ],
        ];

        // Should fail validation because student already has active enrollment in target TA
        $res = $this->actingAs($this->admin)->postJson(route('academic.promotion.execute'), $payload);
        $res->assertStatus(422);
    }

    /**
     * Test 14: Atomic rollback on failure ensures zero partial data changes.
     */
    public function test_atomic_rollback_on_failure(): void
    {
        $siswaGood = $this->createEnrolledStudent('Siswa Sukses', '10112', $this->kelas10Pplg1_2025, $this->ta2025, $this->angkatan18);

        $payload = [
            'tahun_ajaran_asal_id'   => $this->ta2025->id,
            'tahun_ajaran_tujuan_id' => $this->ta2026->id,
            'students'               => [
                [
                    'siswa_id'        => $siswaGood->id,
                    'action'          => 'naik_kelas',
                    'kelas_tujuan_id' => $this->kelas11Pplg1_2026->id,
                ],
                [
                    'siswa_id'        => 999999, // Non-existent student triggers failure
                    'action'          => 'naik_kelas',
                    'kelas_tujuan_id' => $this->kelas11Pplg1_2026->id,
                ],
            ],
        ];

        $res = $this->actingAs($this->admin)->postJson(route('academic.promotion.execute'), $payload);
        $res->assertStatus(422);

        // Verify SiswaGood was NOT promoted (transaction rolled back completely!)
        $destEnrollment = StudentEnrollment::where('siswa_id', $siswaGood->id)
            ->where('tahun_ajaran_id', $this->ta2026->id)
            ->first();
        $this->assertNull($destEnrollment);

        // Origin enrollment remains active
        $origEnrollment = StudentEnrollment::where('siswa_id', $siswaGood->id)
            ->where('tahun_ajaran_id', $this->ta2025->id)
            ->first();
        $this->assertTrue($origEnrollment->is_active);
    }

    /**
     * Test 15: Same origin and destination academic year is strictly rejected.
     */
    public function test_same_origin_and_destination_ta_rejected(): void
    {
        $siswa = $this->createEnrolledStudent('Siswa Same TA', '10113', $this->kelas10Pplg1_2025, $this->ta2025, $this->angkatan18);

        $payload = [
            'tahun_ajaran_asal_id'   => $this->ta2025->id,
            'tahun_ajaran_tujuan_id' => $this->ta2025->id,
            'students'               => [
                [
                    'siswa_id'        => $siswa->id,
                    'action'          => 'naik_kelas',
                    'kelas_tujuan_id' => $this->kelas10Pplg1_2025->id,
                ],
            ],
        ];

        $res = $this->actingAs($this->admin)->postJson(route('academic.promotion.execute'), $payload);
        $res->assertStatus(422);

        // Also verify service layer directly rejects same TA
        $this->expectException(ValidationException::class);
        $this->promotionService->executePromotionBatch($payload, $this->admin);
    }

    /**
     * Test 16: Chronologically older destination academic year is strictly rejected.
     */
    public function test_older_destination_ta_rejected(): void
    {
        $siswa = $this->createEnrolledStudent('Siswa Older TA', '10114', $this->kelas11Pplg1_2026, $this->ta2026, $this->angkatan18);

        $payload = [
            'tahun_ajaran_asal_id'   => $this->ta2026->id,
            'tahun_ajaran_tujuan_id' => $this->ta2025->id, // 2025 is older than 2026
            'students'               => [
                [
                    'siswa_id'        => $siswa->id,
                    'action'          => 'naik_kelas',
                    'kelas_tujuan_id' => $this->kelas12Pplg1_2025->id,
                ],
            ],
        ];

        $res = $this->actingAs($this->admin)->postJson(route('academic.promotion.execute'), $payload);
        $res->assertStatus(422);

        // Service layer rejection
        $this->expectException(ValidationException::class);
        $this->promotionService->executePromotionBatch($payload, $this->admin);
    }

    /**
     * Test 17: Duplicate student in the same batch payload is rejected upfront.
     */
    public function test_duplicate_student_in_same_payload_rejected(): void
    {
        $siswa = $this->createEnrolledStudent('Siswa Duplikat', '10115', $this->kelas10Pplg1_2025, $this->ta2025, $this->angkatan18);

        $payload = [
            'tahun_ajaran_asal_id'   => $this->ta2025->id,
            'tahun_ajaran_tujuan_id' => $this->ta2026->id,
            'students'               => [
                [
                    'siswa_id'        => $siswa->id,
                    'action'          => 'naik_kelas',
                    'kelas_tujuan_id' => $this->kelas11Pplg1_2026->id,
                ],
                [
                    'siswa_id'        => $siswa->id, // Duplicate entry
                    'action'          => 'naik_kelas',
                    'kelas_tujuan_id' => $this->kelas11Pplg1_2026->id,
                ],
            ],
        ];

        $res = $this->actingAs($this->admin)->postJson(route('academic.promotion.execute'), $payload);
        $res->assertStatus(422);
    }

    /**
     * Test 18: Double batch execution for same student is prevented by active enrollment check.
     */
    public function test_double_batch_execution_prevented(): void
    {
        $siswa = $this->createEnrolledStudent('Siswa Double Exec', '10116', $this->kelas10Pplg1_2025, $this->ta2025, $this->angkatan18);

        $payload = [
            'tahun_ajaran_asal_id'   => $this->ta2025->id,
            'tahun_ajaran_tujuan_id' => $this->ta2026->id,
            'students'               => [
                [
                    'siswa_id'        => $siswa->id,
                    'action'          => 'naik_kelas',
                    'kelas_tujuan_id' => $this->kelas11Pplg1_2026->id,
                ],
            ],
        ];

        // First execution succeeds
        $res1 = $this->actingAs($this->admin)->postJson(route('academic.promotion.execute'), $payload);
        $res1->assertStatus(200);

        // Second execution (e.g. accidental double click) must fail with 422
        $res2 = $this->actingAs($this->admin)->postJson(route('academic.promotion.execute'), $payload);
        $res2->assertStatus(422);
    }

    /**
     * Test 19: Replicate classes endpoint is idempotent and rejects invalid years.
     */
    public function test_replicate_classes_idempotency_and_chronology_validation(): void
    {
        // Destination year: ta2027
        $initialCount = Kelas::where('tahun_ajaran_id', $this->ta2027->id)->count();

        // 1. Replicating to older year must fail
        $resOlder = $this->actingAs($this->admin)->postJson(route('academic.promotion.replicate_classes'), [
            'origin_ta_id' => $this->ta2026->id,
            'dest_ta_id'   => $this->ta2025->id,
        ]);
        $resOlder->assertStatus(422);

        // 2. Replicating to same year must fail
        $resSame = $this->actingAs($this->admin)->postJson(route('academic.promotion.replicate_classes'), [
            'origin_ta_id' => $this->ta2026->id,
            'dest_ta_id'   => $this->ta2026->id,
        ]);
        $resSame->assertStatus(422);

        // 3. First replication to ta2027 succeeds
        $resFirst = $this->actingAs($this->admin)->postJson(route('academic.promotion.replicate_classes'), [
            'origin_ta_id' => $this->ta2026->id,
            'dest_ta_id'   => $this->ta2027->id,
        ]);
        $resFirst->assertStatus(200);
        $firstCreated = $resFirst->json('created_count');
        $this->assertGreaterThan(0, $firstCreated);

        // 4. Second replication to ta2027 creates 0 duplicates
        $resSecond = $this->actingAs($this->admin)->postJson(route('academic.promotion.replicate_classes'), [
            'origin_ta_id' => $this->ta2026->id,
            'dest_ta_id'   => $this->ta2027->id,
        ]);
        $resSecond->assertStatus(200);
        $this->assertEquals(0, $resSecond->json('created_count'));

        // Verify total classes in ta2027 did not increase
        $finalCount = Kelas::where('tahun_ajaran_id', $this->ta2027->id)->count();
        $this->assertEquals($initialCount + $firstCreated, $finalCount);
    }

    /**
     * Test 20: Graduated student cannot be promoted or re-graduated.
     */
    public function test_graduated_student_cannot_be_promoted_or_re_graduated(): void
    {
        $siswa = $this->createEnrolledStudent('Siswa Lulus Test', '10117', $this->kelas12Pplg1_2025, $this->ta2025, $this->angkatan17);

        // Graduate student
        $this->historyService->graduateStudent($siswa, $this->ta2025);
        $siswa->refresh();
        $this->assertEquals('lulus', $siswa->status);

        // Attempting to graduate again throws ValidationException
        $this->expectException(ValidationException::class);
        $this->historyService->graduateStudent($siswa, $this->ta2025);
    }

    /**
     * Test 21: Graduated student cannot be promoted.
     */
    public function test_graduated_student_cannot_be_promoted(): void
    {
        $siswa = $this->createEnrolledStudent('Siswa Lulus No Promo', '10118', $this->kelas12Pplg1_2025, $this->ta2025, $this->angkatan17);

        $this->historyService->graduateStudent($siswa, $this->ta2025);
        $siswa->refresh();

        $this->expectException(ValidationException::class);
        $this->historyService->promoteStudent($siswa, $this->kelas12Pplg1_2026, $this->ta2026);
    }

    /**
     * Test 22: Homeroom teacher conflict within same promotion payload is rejected.
     */
    public function test_guru_homeroom_conflict_in_promotion_payload(): void
    {
        $siswa1 = $this->createEnrolledStudent('Siswa Guru A', '10119', $this->kelas10Pplg1_2025, $this->ta2025, $this->angkatan18);
        $siswa2 = $this->createEnrolledStudent('Siswa Guru B', '10120', $this->kelas10Pplg1_2025, $this->ta2025, $this->angkatan18);

        $payload = [
            'tahun_ajaran_asal_id'   => $this->ta2025->id,
            'tahun_ajaran_tujuan_id' => $this->ta2026->id,
            'students'               => [
                [
                    'siswa_id'        => $siswa1->id,
                    'action'          => 'naik_kelas',
                    'kelas_tujuan_id' => $this->kelas11Pplg1_2026->id,
                ],
                [
                    'siswa_id'        => $siswa2->id,
                    'action'          => 'naik_kelas',
                    'kelas_tujuan_id' => $this->kelas12Pplg1_2026->id,
                ],
            ],
            // Same guru assigned to two classes in the target TA
            'homeroom_assignments'   => [
                ['kelas_id' => $this->kelas11Pplg1_2026->id, 'guru_id' => $this->guruA->id],
                ['kelas_id' => $this->kelas12Pplg1_2026->id, 'guru_id' => $this->guruA->id],
            ],
        ];

        $res = $this->actingAs($this->admin)->postJson(route('academic.promotion.execute'), $payload);
        $res->assertStatus(422);
    }

    /**
     * Test 23: Deletion of academic year with promotion batch history is blocked.
     */
    public function test_deletion_of_tahun_ajaran_with_promotion_batch_blocked(): void
    {
        $siswa = $this->createEnrolledStudent('Siswa Ta Hist', '10121', $this->kelas10Pplg1_2025, $this->ta2025, $this->angkatan18);

        $payload = [
            'tahun_ajaran_asal_id'   => $this->ta2025->id,
            'tahun_ajaran_tujuan_id' => $this->ta2026->id,
            'students'               => [
                [
                    'siswa_id'        => $siswa->id,
                    'action'          => 'naik_kelas',
                    'kelas_tujuan_id' => $this->kelas11Pplg1_2026->id,
                ],
            ],
        ];

        $this->actingAs($this->admin)->postJson(route('academic.promotion.execute'), $payload)->assertStatus(200);

        // Attempting to delete ta2025 must be redirected with error and record preserved
        $res = $this->actingAs($this->admin)->post(route('academic.tahun-ajaran.destroy', $this->ta2025->id));
        $res->assertSessionHas('error');
        $this->assertDatabaseHas('tahun_ajarans', ['id' => $this->ta2025->id]);
    }

    /**
     * Test 24: Deletion of student with promotion history is blocked.
     */
    public function test_deletion_of_student_with_promotion_history_blocked(): void
    {
        $siswa = $this->createEnrolledStudent('Siswa Siswa Hist', '10122', $this->kelas10Pplg1_2025, $this->ta2025, $this->angkatan18);

        $payload = [
            'tahun_ajaran_asal_id'   => $this->ta2025->id,
            'tahun_ajaran_tujuan_id' => $this->ta2026->id,
            'students'               => [
                [
                    'siswa_id'        => $siswa->id,
                    'action'          => 'naik_kelas',
                    'kelas_tujuan_id' => $this->kelas11Pplg1_2026->id,
                ],
            ],
        ];

        $this->actingAs($this->admin)->postJson(route('academic.promotion.execute'), $payload)->assertStatus(200);

        // Attempting to delete student must be blocked
        $res = $this->actingAs($this->admin)->post(route('siswa.delete', $siswa->id));
        $res->assertSessionHas('error');
        $this->assertDatabaseHas('siswas', ['id' => $siswa->id]);
    }

    /**
     * Test 25: Graduated and inactive students are excluded from active daily attendance roster.
     */
    public function test_graduated_students_excluded_from_daily_attendance_roster(): void
    {
        $activeStudent = $this->createEnrolledStudent('Siswa Masih Aktif', '10123', $this->kelas10Pplg1_2025, $this->ta2025, $this->angkatan18);
        $graduatedStudent = $this->createEnrolledStudent('Siswa Sudah Lulus', '10124', $this->kelas10Pplg1_2025, $this->ta2025, $this->angkatan18);

        $this->historyService->graduateStudent($graduatedStudent, $this->ta2025);
        $graduatedStudent->refresh();
        $this->assertEquals('lulus', $graduatedStudent->status);

        // Daily attendance roster only includes active students
        $roster = Siswa::where('status', 'aktif')->where('kelas_id', $this->kelas10Pplg1_2025->id)->pluck('id');
        $this->assertTrue($roster->contains($activeStudent->id));
        $this->assertFalse($roster->contains($graduatedStudent->id));
    }

    /**
     * Test 26: Promotion views render successfully with consistent font styling, clean typography, and SweetAlert.
     */
    public function test_promotion_views_render_successfully_with_consistent_font_styling_and_clean_syntax(): void
    {
        // 1. Wizard index page
        $resIndex = $this->actingAs($this->admin)->get(route('academic.promotion.index'));
        $resIndex->assertStatus(200);
        $resIndex->assertDontSee('$\rightarrow$');
        $resIndex->assertSee('font-normal text-slate-900 tracking-tight', false);
        $resIndex->assertSee('Swal.fire', false);

        // 2. History page
        $resHistory = $this->actingAs($this->admin)->get(route('academic.promotion.history'));
        $resHistory->assertStatus(200);
        $resHistory->assertSee('font-normal text-slate-900 tracking-tight', false);

        // 3. Batch show page
        $batch = AcademicPromotionBatch::create([
            'batch_code'             => 'PROM-TEST-001',
            'tahun_ajaran_asal_id'   => $this->ta2025->id,
            'tahun_ajaran_tujuan_id' => $this->ta2026->id,
            'total_siswa'            => 1,
            'total_naik'             => 1,
            'status'                 => 'completed',
            'created_by'             => $this->admin->id,
        ]);

        $resShow = $this->actingAs($this->admin)->get(route('academic.promotion.show', $batch->id));
        $resShow->assertStatus(200);
        $resShow->assertSee('font-normal text-slate-900 tracking-tight', false);
        $resShow->assertSee('PROM-TEST-001');
    }
}
