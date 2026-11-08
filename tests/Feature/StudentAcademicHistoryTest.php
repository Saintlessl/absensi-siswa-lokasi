<?php

namespace Tests\Feature;

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
use App\Services\StudentAcademicHistoryService;
use App\Services\TeacherAssignmentService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class StudentAcademicHistoryTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $kaprogPplg;
    protected User $kaprogAnimasi;
    protected TahunAjaran $ta2024;
    protected TahunAjaran $ta2025;
    protected TahunAjaran $ta2026;
    protected Jurusan $jurusanPplg;
    protected Jurusan $jurusanAnimasi;
    protected Angkatan $angkatan17Pplg;
    protected Angkatan $angkatan18Pplg;
    protected Angkatan $angkatan19Pplg;
    protected Kelas $kelas10Pplg1;
    protected Kelas $kelas10Pplg2;
    protected Kelas $kelas11Pplg1;
    protected Kelas $kelas12Pplg1;
    protected Guru $guruA;
    protected Guru $guruB;
    protected StudentAcademicHistoryService $historyService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->historyService = app(StudentAcademicHistoryService::class);

        $this->admin = User::factory()->create([
            'role'  => 'admin',
            'email' => 'admin.academic@myskanic.test',
        ]);

        // Academic Years (use firstOrCreate since baseline migration seeds standard years)
        $this->ta2024 = TahunAjaran::firstOrCreate(
            ['nama' => '2024/2025'],
            [
                'tahun_mulai'     => 2024,
                'tahun_selesai'   => 2025,
                'semester_aktif'  => 'genap',
                'tanggal_mulai'   => '2024-07-15',
                'tanggal_selesai' => '2025-06-25',
                'is_active'       => false,
                'status'          => 'selesai',
            ]
        );

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

        // Majors
        $this->jurusanPplg = Jurusan::create([
            'nama_jurusan' => 'Pengembangan Perangkat Lunak dan Gim',
            'kode_jurusan' => 'PPLG',
        ]);

        $this->jurusanAnimasi = Jurusan::create([
            'nama_jurusan' => 'Animasi',
            'kode_jurusan' => 'ANIMASI',
        ]);

        // Kaprogs
        $this->kaprogPplg = User::factory()->create([
            'role'       => 'kaprog',
            'jurusan_id' => $this->jurusanPplg->id,
            'email'      => 'kaprog.pplg@myskanic.test',
        ]);

        $this->kaprogAnimasi = User::factory()->create([
            'role'       => 'kaprog',
            'jurusan_id' => $this->jurusanAnimasi->id,
            'email'      => 'kaprog.animasi@myskanic.test',
        ]);

        // Major-Specific Cohorts
        $this->angkatan17Pplg = Angkatan::create([
            'jurusan_id'    => $this->jurusanPplg->id,
            'tahun_masuk'   => 2024,
            'nama_angkatan' => 'Angkatan 17 PPLG',
            'nomor_angkatan'=> 17,
            'kode_angkatan' => 'AKT-PPLG-17',
            'status'        => 'aktif',
        ]);

        $this->angkatan18Pplg = Angkatan::create([
            'jurusan_id'    => $this->jurusanPplg->id,
            'tahun_masuk'   => 2025,
            'nama_angkatan' => 'Angkatan 18 PPLG',
            'nomor_angkatan'=> 18,
            'kode_angkatan' => 'AKT-PPLG-18',
            'status'        => 'aktif',
        ]);

        $this->angkatan19Pplg = Angkatan::create([
            'jurusan_id'    => $this->jurusanPplg->id,
            'tahun_masuk'   => 2026,
            'nama_angkatan' => 'Angkatan 19 PPLG',
            'nomor_angkatan'=> 19,
            'kode_angkatan' => 'AKT-PPLG-19',
            'status'        => 'aktif',
        ]);

        // Classes
        $this->kelas10Pplg1 = Kelas::create([
            'nama_kelas'      => 'X PPLG 1',
            'jurusan_id'      => $this->jurusanPplg->id,
            'tingkat'         => 10,
            'nomor_rombel'    => 1,
            'tahun_ajaran'    => '2026/2027',
            'tahun_ajaran_id' => $this->ta2026->id,
            'is_active'       => true,
            'wali_kelas'      => 'Guru Awal',
        ]);

        $this->kelas10Pplg2 = Kelas::create([
            'nama_kelas'      => 'X PPLG 2',
            'jurusan_id'      => $this->jurusanPplg->id,
            'tingkat'         => 10,
            'nomor_rombel'    => 2,
            'tahun_ajaran'    => '2026/2027',
            'tahun_ajaran_id' => $this->ta2026->id,
            'is_active'       => true,
            'wali_kelas'      => 'Guru Dua',
        ]);

        $this->kelas11Pplg1 = Kelas::create([
            'nama_kelas'      => 'XI PPLG 1',
            'jurusan_id'      => $this->jurusanPplg->id,
            'tingkat'         => 11,
            'nomor_rombel'    => 1,
            'tahun_ajaran'    => '2026/2027',
            'tahun_ajaran_id' => $this->ta2026->id,
            'is_active'       => true,
        ]);

        $this->kelas12Pplg1 = Kelas::create([
            'nama_kelas'      => 'XII PPLG 1',
            'jurusan_id'      => $this->jurusanPplg->id,
            'tingkat'         => 12,
            'nomor_rombel'    => 1,
            'tahun_ajaran'    => '2026/2027',
            'tahun_ajaran_id' => $this->ta2026->id,
            'is_active'       => true,
        ]);

        // Teachers
        $userGuruA = User::factory()->create(['role' => 'guru', 'email' => 'guru.a@myskanic.test']);
        $this->guruA = Guru::create([
            'user_id'       => $userGuruA->id,
            'nama'          => 'Dra. Siti Aminah, M.Pd.',
            'nip'           => '197501012000032001',
            'mapel'         => 'Dasar Kejuruan PPLG',
            'jenis_kelamin' => 'P',
        ]);

        $userGuruB = User::factory()->create(['role' => 'guru', 'email' => 'guru.b@myskanic.test']);
        $this->guruB = Guru::create([
            'user_id'       => $userGuruB->id,
            'nama'          => 'Bambang Sudarsono, S.T.',
            'nip'           => '198202022005011002',
            'mapel'         => 'Pemrograman Web',
            'jenis_kelamin' => 'L',
        ]);
    }

    /**
     * Skenario 1: Siswa memiliki tahun ajaran masuk permanen yang tersimpan secara eksplisit.
     */
    public function test_student_has_explicit_permanent_entry_academic_year(): void
    {
        $payload = [
            'nisn'                 => '0081234567',
            'nis'                  => '262710001',
            'nama'                 => 'Rian Firmansyah',
            'jenis_kelamin'        => 'L',
            'tahun_ajaran_id'      => $this->ta2026->id,
            'jalur_masuk'          => 'baru',
            'jurusan_id'           => $this->jurusanPplg->id,
            'angkatan_id'          => $this->angkatan19Pplg->id,
            'tingkat'              => '10',
            'kelas_id'             => $this->kelas10Pplg1->id,
            'email'                => '262710001@myskanic.com',
            'password'             => 'password123',
            'parent_name'          => 'Supardi',
            'parent_jenis_kelamin' => 'L',
            'parent_phone'         => '081234567890',
            'parent_address'       => 'Ciomas, Bogor',
        ];

        $response = $this->actingAs($this->admin)->post(route('siswa.store'), $payload);
        $response->assertRedirect();

        $siswa = Siswa::where('nis', '262710001')->first();
        $this->assertNotNull($siswa);
        $this->assertEquals($this->ta2026->id, $siswa->tahun_ajaran_masuk_id);
        $this->assertEquals('2026/2027', $siswa->tahun_ajaran_masuk);
        $this->assertEquals(2026, $siswa->tahun_masuk);
        $this->assertEquals('2026/2027', $siswa->tahun_ajaran_masuk_nama);
    }

    /**
     * Skenario 2: Tahun ajaran masuk permanen berbeda dengan tahun ajaran enrollment aktif.
     */
    public function test_permanent_entry_year_differs_from_active_enrollment_year(): void
    {
        // Student entered in 2024/2025, currently enrolling in Grade 12 in active year 2026/2027
        $payload = [
            'nisn'                 => '0069876543',
            'nis'                  => '242512088',
            'nama'                 => 'Aditya Wardhana',
            'jenis_kelamin'        => 'L',
            'tahun_ajaran_id'      => $this->ta2024->id, // Permanent entry year
            'jalur_masuk'          => 'baru',
            'jurusan_id'           => $this->jurusanPplg->id,
            'angkatan_id'          => $this->angkatan17Pplg->id,
            'tingkat'              => '12',
            'kelas_id'             => $this->kelas12Pplg1->id,
            'email'                => '242512088@myskanic.com',
            'password'             => 'password123',
            'parent_name'          => 'Wardhana',
            'parent_jenis_kelamin' => 'L',
            'parent_phone'         => '081233445566',
            'parent_address'       => 'Ciomas, Bogor',
        ];

        $response = $this->actingAs($this->admin)->post(route('siswa.store'), $payload);
        $response->assertRedirect();

        $siswa = Siswa::where('nis', '242512088')->first();
        $this->assertNotNull($siswa);

        // Permanent identity must be 2024/2025
        $this->assertEquals($this->ta2024->id, $siswa->tahun_ajaran_masuk_id);
        $this->assertEquals('2024/2025', $siswa->tahun_ajaran_masuk);
        $this->assertEquals(2024, $siswa->tahun_masuk);

        // Active enrollment must be 2026/2027 (operational current year)
        $activeEnrollment = $siswa->activeEnrollment;
        $this->assertNotNull($activeEnrollment);
        $this->assertEquals($this->ta2026->id, $activeEnrollment->tahun_ajaran_id);
        $this->assertEquals('2026/2027', $activeEnrollment->tahun_ajaran);
        $this->assertEquals(12, $activeEnrollment->tingkat);
        $this->assertEquals($this->kelas12Pplg1->id, $activeEnrollment->kelas_id);
    }

    /**
     * Skenario 3: Histori enrollment beberapa tahun ajaran dapat ditampilkan pada timeline profil siswa.
     */
    public function test_multi_year_enrollment_history_renders_chronologically(): void
    {
        $user = User::factory()->create(['role' => 'siswa', 'email' => 'timeline.student@myskanic.com']);
        $siswa = Siswa::create([
            'user_id'               => $user->id,
            'nis'                   => '242512099',
            'nama'                  => 'Budi Timeline',
            'jenis_kelamin'         => 'L',
            'kelas_id'              => $this->kelas12Pplg1->id,
            'angkatan_id'           => $this->angkatan17Pplg->id,
            'tahun_ajaran_masuk_id' => $this->ta2024->id,
            'tahun_ajaran_masuk'    => '2024/2025',
            'tahun_masuk'           => 2024,
            'status'                => 'aktif',
        ]);

        // Historical Year 1: Grade 10 in 2024/2025
        StudentEnrollment::create([
            'siswa_id'          => $siswa->id,
            'tahun_ajaran_id'   => $this->ta2024->id,
            'tahun_ajaran'      => '2024/2025',
            'kelas_id'          => $this->kelas10Pplg1->id,
            'jurusan_id'        => $this->jurusanPplg->id,
            'angkatan_id'       => $this->angkatan17Pplg->id,
            'tingkat'           => 10,
            'started_at'        => '2024-07-15',
            'ended_at'          => '2025-06-25',
            'is_active'         => false,
            'status_enrollment' => 'selesai',
        ]);

        // Historical Year 2: Grade 11 in 2025/2026
        StudentEnrollment::create([
            'siswa_id'          => $siswa->id,
            'tahun_ajaran_id'   => $this->ta2025->id,
            'tahun_ajaran'      => '2025/2026',
            'kelas_id'          => $this->kelas11Pplg1->id,
            'jurusan_id'        => $this->jurusanPplg->id,
            'angkatan_id'       => $this->angkatan17Pplg->id,
            'tingkat'           => 11,
            'started_at'        => '2025-07-15',
            'ended_at'          => '2026-06-25',
            'is_active'         => false,
            'status_enrollment' => 'selesai',
        ]);

        // Current Year 3: Grade 12 in 2026/2027
        StudentEnrollment::create([
            'siswa_id'          => $siswa->id,
            'tahun_ajaran_id'   => $this->ta2026->id,
            'tahun_ajaran'      => '2026/2027',
            'kelas_id'          => $this->kelas12Pplg1->id,
            'jurusan_id'        => $this->jurusanPplg->id,
            'angkatan_id'       => $this->angkatan17Pplg->id,
            'tingkat'           => 12,
            'started_at'        => '2026-07-15',
            'ended_at'          => null,
            'is_active'         => true,
            'status_enrollment' => 'aktif',
        ]);

        $response = $this->actingAs($this->admin)->get(route('siswa.show', $siswa->id));
        $response->assertOk();
        $response->assertSee('Riwayat Akademik (Timeline)');
        $response->assertSee('Tahun Ajaran 2024/2025');
        $response->assertSee('Tahun Ajaran 2025/2026');
        $response->assertSee('Tahun Ajaran 2026/2027');
        $response->assertSee('Masuk TA: 2024/2025');
    }

    /**
     * Skenario 4: Kenaikan kelas tidak mengubah tahun masuk permanen siswa.
     */
    public function test_class_promotion_preserves_permanent_entry_year(): void
    {
        $user = User::factory()->create(['role' => 'siswa']);
        $siswa = Siswa::create([
            'user_id'               => $user->id,
            'nis'                   => '252610011',
            'nama'                  => 'Dimas Saputra',
            'jenis_kelamin'         => 'L',
            'kelas_id'              => $this->kelas10Pplg1->id,
            'angkatan_id'           => $this->angkatan18Pplg->id,
            'tahun_ajaran_masuk_id' => $this->ta2025->id,
            'tahun_ajaran_masuk'    => '2025/2026',
            'tahun_masuk'           => 2025,
            'status'                => 'aktif',
        ]);

        // Active in Grade 10 (2025/2026)
        StudentEnrollment::create([
            'siswa_id'          => $siswa->id,
            'tahun_ajaran_id'   => $this->ta2025->id,
            'tahun_ajaran'      => '2025/2026',
            'kelas_id'          => $this->kelas10Pplg1->id,
            'jurusan_id'        => $this->jurusanPplg->id,
            'angkatan_id'       => $this->angkatan18Pplg->id,
            'tingkat'           => 10,
            'is_active'         => true,
            'status_enrollment' => 'aktif',
        ]);

        // Promote to Grade 11 in 2026/2027
        $newEnrollment = $this->historyService->promoteStudent(
            $siswa,
            $this->kelas11Pplg1,
            $this->ta2026,
            'Naik kelas XI PPLG 1',
            $this->admin
        );

        $siswa->refresh();

        // Permanent identity must remain 2025/2026 and Angkatan 18!
        $this->assertEquals($this->ta2025->id, $siswa->tahun_ajaran_masuk_id);
        $this->assertEquals('2025/2026', $siswa->tahun_ajaran_masuk);
        $this->assertEquals(2025, $siswa->tahun_masuk);
        $this->assertEquals($this->angkatan18Pplg->id, $siswa->angkatan_id);

        // New active enrollment must be Grade 11 in 2026/2027
        $this->assertEquals($this->ta2026->id, $newEnrollment->tahun_ajaran_id);
        $this->assertEquals(11, $newEnrollment->tingkat);
        $this->assertTrue((bool) $newEnrollment->is_active);

        // Old enrollment must be finalized as selesai
        $oldEnrollment = StudentEnrollment::where('siswa_id', $siswa->id)->where('tahun_ajaran_id', $this->ta2025->id)->first();
        $this->assertFalse((bool) $oldEnrollment->is_active);
        $this->assertEquals('selesai', $oldEnrollment->status_enrollment);

        // Audit log must exist
        $audit = SiswaStatusHistory::where('siswa_id', $siswa->id)->where('jenis_perubahan', 'kenaikan_kelas')->first();
        $this->assertNotNull($audit);
    }

    /**
     * Skenario 5: Tinggal kelas mempertahankan identitas angkatan dan tahun masuk permanen.
     */
    public function test_retaining_student_preserves_original_cohort_and_entry_year(): void
    {
        $user = User::factory()->create(['role' => 'siswa']);
        $siswa = Siswa::create([
            'user_id'               => $user->id,
            'nis'                   => '252610022',
            'nama'                  => 'Reno Tinggal Kelas',
            'jenis_kelamin'         => 'L',
            'kelas_id'              => $this->kelas10Pplg1->id,
            'angkatan_id'           => $this->angkatan18Pplg->id,
            'tahun_ajaran_masuk_id' => $this->ta2025->id,
            'tahun_ajaran_masuk'    => '2025/2026',
            'tahun_masuk'           => 2025,
            'status'                => 'aktif',
        ]);

        StudentEnrollment::create([
            'siswa_id'          => $siswa->id,
            'tahun_ajaran_id'   => $this->ta2025->id,
            'tahun_ajaran'      => '2025/2026',
            'kelas_id'          => $this->kelas10Pplg1->id,
            'jurusan_id'        => $this->jurusanPplg->id,
            'angkatan_id'       => $this->angkatan18Pplg->id,
            'tingkat'           => 10,
            'is_active'         => true,
            'status_enrollment' => 'aktif',
        ]);

        // Retain in Grade 10 in new academic year 2026/2027
        $newEnrollment = $this->historyService->retainStudent(
            $siswa,
            $this->kelas10Pplg2,
            $this->ta2026,
            'Tinggal kelas mengulang kelas 10',
            $this->admin
        );

        $siswa->refresh();

        // Permanent identity and original cohort MUST NOT CHANGE!
        $this->assertEquals('2025/2026', $siswa->tahun_ajaran_masuk);
        $this->assertEquals(2025, $siswa->tahun_masuk);
        $this->assertEquals($this->angkatan18Pplg->id, $siswa->angkatan_id);

        // Previous enrollment marked as tinggal_kelas
        $oldEnrollment = StudentEnrollment::where('siswa_id', $siswa->id)->where('tahun_ajaran_id', $this->ta2025->id)->first();
        $this->assertEquals('tinggal_kelas', $oldEnrollment->status_enrollment);
        $this->assertFalse((bool) $oldEnrollment->is_active);

        // New enrollment is active in Grade 10 with same cohort
        $this->assertTrue((bool) $newEnrollment->is_active);
        $this->assertEquals(10, $newEnrollment->tingkat);
        $this->assertEquals($this->angkatan18Pplg->id, $newEnrollment->angkatan_id);

        // Audit log exists
        $audit = SiswaStatusHistory::where('siswa_id', $siswa->id)->where('jenis_perubahan', 'tinggal_kelas')->first();
        $this->assertNotNull($audit);
    }

    /**
     * Skenario 6: Mutasi masuk mencatat histori yang benar dan asal sekolah sebelumnya.
     */
    public function test_transfer_in_student_records_origin_school_and_audit_history(): void
    {
        $payload = [
            'nisn'                 => '0075554443',
            'nis'                  => '262711099',
            'nama'                 => 'Rani Siswa Pindahan',
            'jenis_kelamin'        => 'P',
            'tahun_ajaran_id'      => $this->ta2026->id,
            'jalur_masuk'          => 'pindahan',
            'asal_sekolah'         => 'SMK Negeri 2 Bogor',
            'jurusan_id'           => $this->jurusanPplg->id,
            'angkatan_id'          => $this->angkatan18Pplg->id, // Kohort kelas 11
            'tingkat'              => '11',
            'kelas_id'             => $this->kelas11Pplg1->id,
            'email'                => '262711099@myskanic.com',
            'password'             => 'password123',
            'parent_name'          => 'Rahmat',
            'parent_jenis_kelamin' => 'L',
            'parent_phone'         => '081299887766',
            'parent_address'       => 'Bogor Barat',
        ];

        $response = $this->actingAs($this->admin)->post(route('siswa.store'), $payload);
        $response->assertRedirect();

        $siswa = Siswa::where('nis', '262711099')->first();
        $this->assertNotNull($siswa);
        $this->assertEquals('pindahan', $siswa->jalur_masuk);
        $this->assertEquals('SMK Negeri 2 Bogor', $siswa->asal_sekolah);
        $this->assertEquals($this->ta2026->id, $siswa->tahun_ajaran_masuk_id);

        $audit = SiswaStatusHistory::where('siswa_id', $siswa->id)->where('jenis_perubahan', 'mutasi_masuk')->first();
        $this->assertNotNull($audit);
        $this->assertStringContainsString('SMK Negeri 2 Bogor', $audit->alasan);
    }

    /**
     * Skenario 7: Perubahan rombel mempertahankan histori rombel sebelumnya (tidak saling menimpa).
     */
    public function test_class_transfer_preserves_previous_rombel_history(): void
    {
        $user = User::factory()->create(['role' => 'siswa']);
        $siswa = Siswa::create([
            'user_id'               => $user->id,
            'nis'                   => '262710077',
            'nama'                  => 'Rafi Pindah Rombel',
            'jenis_kelamin'         => 'L',
            'kelas_id'              => $this->kelas10Pplg1->id,
            'angkatan_id'           => $this->angkatan19Pplg->id,
            'tahun_ajaran_masuk_id' => $this->ta2026->id,
            'tahun_ajaran_masuk'    => '2026/2027',
            'tahun_masuk'           => 2026,
            'status'                => 'aktif',
        ]);

        $enr1 = StudentEnrollment::create([
            'siswa_id'          => $siswa->id,
            'tahun_ajaran_id'   => $this->ta2026->id,
            'tahun_ajaran'      => '2026/2027',
            'kelas_id'          => $this->kelas10Pplg1->id,
            'jurusan_id'        => $this->jurusanPplg->id,
            'angkatan_id'       => $this->angkatan19Pplg->id,
            'tingkat'           => 10,
            'is_active'         => true,
            'status_enrollment' => 'aktif',
        ]);

        // Transfer to X PPLG 2
        $enr2 = $this->historyService->transferClass($siswa, $this->kelas10Pplg2, 'Penyeimbangan jumlah rombel', $this->admin);

        // Both records must exist!
        $this->assertDatabaseHas('student_enrollments', [
            'id'        => $enr1->id,
            'kelas_id'  => $this->kelas10Pplg1->id,
            'is_active' => false,
        ]);

        $this->assertDatabaseHas('student_enrollments', [
            'id'        => $enr2->id,
            'kelas_id'  => $this->kelas10Pplg2->id,
            'is_active' => true,
        ]);

        $this->assertEquals(2, StudentEnrollment::where('siswa_id', $siswa->id)->count());

        // Audit log exists
        $audit = SiswaStatusHistory::where('siswa_id', $siswa->id)->where('jenis_perubahan', 'pindah_rombel')->first();
        $this->assertNotNull($audit);
        $this->assertEquals($this->kelas10Pplg1->id, $audit->kelas_asal_id);
        $this->assertEquals($this->kelas10Pplg2->id, $audit->kelas_tujuan_id);
    }

    /**
     * Skenario 8: Pergantian wali kelas mempertahankan histori penugasan lama per tahun ajaran.
     */
    public function test_homeroom_assignment_history_is_preserved_per_academic_year(): void
    {
        $teacherService = app(TeacherAssignmentService::class);

        // Year 1 (2025/2026): Guru A is homeroom for X PPLG 1
        $assignment1 = $teacherService->assignHomeroom($this->guruA->id, $this->kelas10Pplg1->id, '2025/2026');
        $assignment1->update(['tahun_ajaran_id' => $this->ta2025->id]);

        // Year 2 (2026/2027): Guru B is assigned homeroom for X PPLG 1
        $assignment2 = $teacherService->assignHomeroom($this->guruB->id, $this->kelas10Pplg1->id, '2026/2027');
        $assignment2->update(['tahun_ajaran_id' => $this->ta2026->id]);

        // Enrollment in Year 1 should resolve Guru A
        $user = User::factory()->create(['role' => 'siswa']);
        $siswa = Siswa::create([
            'user_id'               => $user->id,
            'nis'                   => '252610055',
            'nama'                  => 'Siswa Homeroom Test',
            'jenis_kelamin'         => 'L',
            'kelas_id'              => $this->kelas10Pplg1->id,
            'angkatan_id'           => $this->angkatan18Pplg->id,
            'tahun_ajaran_masuk_id' => $this->ta2025->id,
            'tahun_ajaran_masuk'    => '2025/2026',
            'tahun_masuk'           => 2025,
            'status'                => 'aktif',
        ]);

        $enrYear1 = StudentEnrollment::create([
            'siswa_id'          => $siswa->id,
            'tahun_ajaran_id'   => $this->ta2025->id,
            'tahun_ajaran'      => '2025/2026',
            'kelas_id'          => $this->kelas10Pplg1->id,
            'jurusan_id'        => $this->jurusanPplg->id,
            'angkatan_id'       => $this->angkatan18Pplg->id,
            'tingkat'           => 10,
            'is_active'         => false,
            'status_enrollment' => 'selesai',
        ]);

        $enrYear2 = StudentEnrollment::create([
            'siswa_id'          => $siswa->id,
            'tahun_ajaran_id'   => $this->ta2026->id,
            'tahun_ajaran'      => '2026/2027',
            'kelas_id'          => $this->kelas10Pplg1->id,
            'jurusan_id'        => $this->jurusanPplg->id,
            'angkatan_id'       => $this->angkatan18Pplg->id,
            'tingkat'           => 10,
            'is_active'         => true,
            'status_enrollment' => 'aktif',
        ]);

        // Verify that year 1 returns Guru A and year 2 returns Guru B
        $this->assertEquals($this->guruA->nama, $enrYear1->wali_kelas_nama);
        $this->assertEquals($this->guruB->nama, $enrYear2->wali_kelas_nama);
    }

    /**
     * Skenario 9: Siswa yang lulus tetap memiliki histori akademik lengkap.
     */
    public function test_graduated_student_preserves_entire_enrollment_history(): void
    {
        $user = User::factory()->create(['role' => 'siswa']);
        $siswa = Siswa::create([
            'user_id'               => $user->id,
            'nis'                   => '242512111',
            'nama'                  => 'Alumni Sukses',
            'jenis_kelamin'         => 'L',
            'kelas_id'              => $this->kelas12Pplg1->id,
            'angkatan_id'           => $this->angkatan17Pplg->id,
            'tahun_ajaran_masuk_id' => $this->ta2024->id,
            'tahun_ajaran_masuk'    => '2024/2025',
            'tahun_masuk'           => 2024,
            'status'                => 'aktif',
        ]);

        StudentEnrollment::create([
            'siswa_id'          => $siswa->id,
            'tahun_ajaran_id'   => $this->ta2024->id,
            'tahun_ajaran'      => '2024/2025',
            'kelas_id'          => $this->kelas10Pplg1->id,
            'jurusan_id'        => $this->jurusanPplg->id,
            'angkatan_id'       => $this->angkatan17Pplg->id,
            'tingkat'           => 10,
            'is_active'         => false,
            'status_enrollment' => 'selesai',
        ]);

        StudentEnrollment::create([
            'siswa_id'          => $siswa->id,
            'tahun_ajaran_id'   => $this->ta2025->id,
            'tahun_ajaran'      => '2025/2026',
            'kelas_id'          => $this->kelas11Pplg1->id,
            'jurusan_id'        => $this->jurusanPplg->id,
            'angkatan_id'       => $this->angkatan17Pplg->id,
            'tingkat'           => 11,
            'is_active'         => false,
            'status_enrollment' => 'selesai',
        ]);

        StudentEnrollment::create([
            'siswa_id'          => $siswa->id,
            'tahun_ajaran_id'   => $this->ta2026->id,
            'tahun_ajaran'      => '2026/2027',
            'kelas_id'          => $this->kelas12Pplg1->id,
            'jurusan_id'        => $this->jurusanPplg->id,
            'angkatan_id'       => $this->angkatan17Pplg->id,
            'tingkat'           => 12,
            'is_active'         => true,
            'status_enrollment' => 'aktif',
        ]);

        // Graduate student
        $this->historyService->graduateStudent($siswa, $this->ta2026, 'Lulus dengan nilai sangat memuaskan', $this->admin);

        $siswa->refresh();
        $this->assertEquals('lulus', $siswa->status);
        $this->assertEquals(2027, $siswa->tahun_lulus);
        $this->assertNotNull($siswa->tanggal_kelulusan);

        // Entire history must still exist (3 enrollments)
        $this->assertEquals(3, $siswa->enrollments()->count());
        $this->assertEquals('2024/2025', $siswa->tahun_ajaran_masuk);

        // Audit log exists
        $audit = SiswaStatusHistory::where('siswa_id', $siswa->id)->where('jenis_perubahan', 'kelulusan')->first();
        $this->assertNotNull($audit);
    }

    /**
     * Skenario 10: Enrollment aktif ganda pada tahun ajaran yang sama ditolak.
     */
    public function test_duplicate_active_enrollment_in_same_academic_year_is_rejected(): void
    {
        $this->expectException(ValidationException::class);

        $user = User::factory()->create(['role' => 'siswa']);
        $siswa = Siswa::create([
            'user_id'               => $user->id,
            'nis'                   => '262710088',
            'nama'                  => 'Siswa Duplikat Test',
            'jenis_kelamin'         => 'L',
            'kelas_id'              => $this->kelas10Pplg1->id,
            'angkatan_id'           => $this->angkatan19Pplg->id,
            'tahun_ajaran_masuk_id' => $this->ta2026->id,
            'tahun_ajaran_masuk'    => '2026/2027',
            'tahun_masuk'           => 2026,
            'status'                => 'aktif',
        ]);

        // First active enrollment in 2026/2027
        StudentEnrollment::create([
            'siswa_id'          => $siswa->id,
            'tahun_ajaran_id'   => $this->ta2026->id,
            'tahun_ajaran'      => '2026/2027',
            'kelas_id'          => $this->kelas10Pplg1->id,
            'jurusan_id'        => $this->jurusanPplg->id,
            'angkatan_id'       => $this->angkatan19Pplg->id,
            'tingkat'           => 10,
            'is_active'         => true,
            'status_enrollment' => 'aktif',
        ]);

        // Attempting to create second active enrollment in 2026/2027 must throw ValidationException
        $this->historyService->preventDuplicateActiveEnrollment($siswa->id, $this->ta2026->id);
    }

    /**
     * Skenario 11: Pengguna tanpa otorisasi tidak dapat melihat profil siswa jurusan lain.
     */
    public function test_unauthorized_user_cannot_view_student_from_other_major(): void
    {
        $user = User::factory()->create(['role' => 'siswa']);
        $siswaPplg = Siswa::create([
            'user_id'               => $user->id,
            'nis'                   => '262710033',
            'nama'                  => 'Siswa Khusus PPLG',
            'jenis_kelamin'         => 'L',
            'kelas_id'              => $this->kelas10Pplg1->id,
            'angkatan_id'           => $this->angkatan19Pplg->id,
            'tahun_ajaran_masuk_id' => $this->ta2026->id,
            'tahun_ajaran_masuk'    => '2026/2027',
            'tahun_masuk'           => 2026,
            'status'                => 'aktif',
        ]);

        // Kaprog Animasi cannot view student of PPLG
        $response = $this->actingAs($this->kaprogAnimasi)->get(route('siswa.show', $siswaPplg->id));
        $response->assertStatus(403);

        // Kaprog PPLG CAN view student of PPLG
        $validResponse = $this->actingAs($this->kaprogPplg)->get(route('siswa.show', $siswaPplg->id));
        $validResponse->assertOk();
    }

    /**
     * Skenario 12: Database transaction melakukan rollback ketika terjadi kegagalan.
     */
    public function test_database_transaction_rolls_back_on_failure(): void
    {
        $user = User::factory()->create(['role' => 'siswa']);
        $siswa = Siswa::create([
            'user_id'               => $user->id,
            'nis'                   => '262710044',
            'nama'                  => 'Rollback Test Student',
            'jenis_kelamin'         => 'L',
            'kelas_id'              => $this->kelas10Pplg1->id,
            'angkatan_id'           => $this->angkatan19Pplg->id,
            'tahun_ajaran_masuk_id' => $this->ta2026->id,
            'tahun_ajaran_masuk'    => '2026/2027',
            'tahun_masuk'           => 2026,
            'status'                => 'aktif',
        ]);

        $initialEnrollmentCount = StudentEnrollment::count();
        $initialHistoryCount = SiswaStatusHistory::count();

        try {
            \DB::transaction(function () use ($siswa) {
                // Step 1: create enrollment
                StudentEnrollment::create([
                    'siswa_id'          => $siswa->id,
                    'tahun_ajaran_id'   => $this->ta2026->id,
                    'tahun_ajaran'      => '2026/2027',
                    'kelas_id'          => $this->kelas10Pplg1->id,
                    'jurusan_id'        => $this->jurusanPplg->id,
                    'angkatan_id'       => $this->angkatan19Pplg->id,
                    'tingkat'           => 10,
                    'is_active'         => true,
                    'status_enrollment' => 'aktif',
                ]);

                // Step 2: Simulate unhandled error
                throw new \RuntimeException('Simulated failure during transaction');
            });
        } catch (\RuntimeException $e) {
            // Expected
        }

        // Verify complete rollback: count must not increase
        $this->assertEquals($initialEnrollmentCount, StudentEnrollment::count());
        $this->assertEquals($initialHistoryCount, SiswaStatusHistory::count());
    }
}
