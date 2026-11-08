<?php

namespace Tests\Feature;

use App\Models\Angkatan;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\OrangTua;
use App\Models\Siswa;
use App\Models\SiswaStatusHistory;
use App\Models\StudentEnrollment;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiswaRegistrationHierarchyTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected TahunAjaran $activeTa;
    protected Jurusan $jurusanPplg;
    protected Jurusan $jurusanAnimasi;
    protected Angkatan $angkatan19Pplg;
    protected Angkatan $angkatan18Pplg;
    protected Angkatan $angkatan17Pplg;
    protected Angkatan $angkatan17Animasi;
    protected Kelas $kelas10Pplg1;
    protected Kelas $kelas10Pplg2;
    protected Kelas $kelas11Pplg1;
    protected Kelas $kelas12Pplg1;
    protected Kelas $kelas10Animasi1;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role'  => 'admin',
            'email' => 'admin.academic@myskanic.test',
        ]);

        $this->activeTa = TahunAjaran::where('nama', '2026/2027')->first() ?? TahunAjaran::create([
            'tahun_mulai'       => 2026,
            'tahun_selesai'     => 2027,
            'nama'              => '2026/2027',
            'semester_aktif'    => 'ganjil',
            'tanggal_mulai'     => '2026-07-15',
            'tanggal_selesai'   => '2027-06-25',
            'is_active'         => true,
            'status'            => 'aktif',
            'catatan'           => 'Tahun Ajaran Aktif 2026/2027',
        ]);

        $this->jurusanPplg = Jurusan::create([
            'kode_jurusan' => 'PPLG',
            'nama_jurusan' => 'Pengembangan Perangkat Lunak dan Gim',
            'bidang'       => 'Teknologi Informasi',
            'is_active'    => true,
        ]);

        $this->jurusanAnimasi = Jurusan::create([
            'kode_jurusan' => 'ANIMASI',
            'nama_jurusan' => 'Animasi 3D & 2D',
            'bidang'       => 'Seni dan Ekonomi Kreatif',
            'is_active'    => true,
        ]);

        $this->angkatan19Pplg = Angkatan::create([
            'jurusan_id'       => $this->jurusanPplg->id,
            'nomor_angkatan'   => 19,
            'nama_angkatan'    => 'Angkatan 19 PPLG',
            'tahun_masuk'      => 2026,
            'tahun_ajaran'     => '2026/2027',
            'tahun_kelulusan'  => 2029,
            'is_active'        => true,
            'is_alumni'        => false,
        ]);

        $this->angkatan18Pplg = Angkatan::create([
            'jurusan_id'       => $this->jurusanPplg->id,
            'nomor_angkatan'   => 18,
            'nama_angkatan'    => 'Angkatan 18 PPLG',
            'tahun_masuk'      => 2025,
            'tahun_ajaran'     => '2025/2026',
            'tahun_kelulusan'  => 2028,
            'is_active'        => true,
            'is_alumni'        => false,
        ]);

        $this->angkatan17Pplg = Angkatan::create([
            'jurusan_id'       => $this->jurusanPplg->id,
            'nomor_angkatan'   => 17,
            'nama_angkatan'    => 'Angkatan 17 PPLG',
            'tahun_masuk'      => 2024,
            'tahun_ajaran'     => '2024/2025',
            'tahun_kelulusan'  => 2027,
            'is_active'        => true,
            'is_alumni'        => false,
        ]);

        $this->angkatan17Animasi = Angkatan::create([
            'jurusan_id'       => $this->jurusanAnimasi->id,
            'nomor_angkatan'   => 17,
            'nama_angkatan'    => 'Angkatan 17 Animasi',
            'tahun_masuk'      => 2026,
            'tahun_ajaran'     => '2026/2027',
            'tahun_kelulusan'  => 2029,
            'is_active'        => true,
            'is_alumni'        => false,
        ]);

        $this->kelas10Pplg1 = Kelas::create([
            'jurusan_id'      => $this->jurusanPplg->id,
            'tahun_ajaran_id' => $this->activeTa->id,
            'tingkat'         => '10',
            'nomor_rombel'    => '1',
            'nama_kelas'      => 'X PPLG 1',
            'tahun_ajaran'    => '2026/2027',
            'jumlah_siswa'    => 0,
            'is_active'       => true,
        ]);

        $this->kelas10Pplg2 = Kelas::create([
            'jurusan_id'      => $this->jurusanPplg->id,
            'tahun_ajaran_id' => $this->activeTa->id,
            'tingkat'         => '10',
            'nomor_rombel'    => '2',
            'nama_kelas'      => 'X PPLG 2',
            'tahun_ajaran'    => '2026/2027',
            'jumlah_siswa'    => 0,
            'is_active'       => true,
        ]);

        $this->kelas11Pplg1 = Kelas::create([
            'jurusan_id'      => $this->jurusanPplg->id,
            'tahun_ajaran_id' => $this->activeTa->id,
            'tingkat'         => '11',
            'nomor_rombel'    => '1',
            'nama_kelas'      => 'XI PPLG 1',
            'tahun_ajaran'    => '2026/2027',
            'jumlah_siswa'    => 0,
            'is_active'       => true,
        ]);

        $this->kelas12Pplg1 = Kelas::create([
            'jurusan_id'      => $this->jurusanPplg->id,
            'tahun_ajaran_id' => $this->activeTa->id,
            'tingkat'         => '12',
            'nomor_rombel'    => '1',
            'nama_kelas'      => 'XII PPLG 1',
            'tahun_ajaran'    => '2026/2027',
            'jumlah_siswa'    => 0,
            'is_active'       => true,
        ]);

        $this->kelas10Animasi1 = Kelas::create([
            'jurusan_id'      => $this->jurusanAnimasi->id,
            'tahun_ajaran_id' => $this->activeTa->id,
            'tingkat'         => '10',
            'nomor_rombel'    => '1',
            'nama_kelas'      => 'X Animasi 1',
            'tahun_ajaran'    => '2026/2027',
            'jumlah_siswa'    => 0,
            'is_active'       => true,
        ]);
    }

    public function test_new_student_registration_assigns_correct_hierarchy_and_active_enrollment(): void
    {
        $payload = [
            'nisn'                    => '0098765432',
            'nis'                     => '262710001',
            'nama'                    => 'Budi Santoso',
            'jenis_kelamin'           => 'L',
            'tahun_ajaran_id'         => $this->activeTa->id,
            'jalur_masuk'             => 'baru',
            'jurusan_id'              => $this->jurusanPplg->id,
            'angkatan_id'             => $this->angkatan19Pplg->id,
            'tingkat'                 => '10',
            'kelas_id'                => $this->kelas10Pplg1->id,
            'email'                   => '262710001@myskanic.com',
            'password'                => 'password123',
            'parent_name'             => 'Santoso Senior',
            'parent_jenis_kelamin'    => 'L',
            'parent_phone'            => '081234567890',
            'parent_address'          => 'Jl. Raya Ciomas No. 10',
            'confirm_parent_verified' => 'on',
        ];

        $response = $this->actingAs($this->admin)->post(route('siswa.store'), $payload);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify Siswa model fields
        $siswa = Siswa::where('nis', '262710001')->first();
        $this->assertNotNull($siswa);
        $this->assertEquals('Budi Santoso', $siswa->nama);
        $this->assertEquals('0098765432', $siswa->nisn);
        $this->assertEquals(2026, $siswa->tahun_masuk);
        $this->assertEquals('baru', $siswa->jalur_masuk);
        $this->assertEquals($this->angkatan19Pplg->id, $siswa->angkatan_id);
        $this->assertEquals($this->kelas10Pplg1->id, $siswa->kelas_id);
        $this->assertEquals('aktif', $siswa->status);

        // Verify StudentEnrollment
        $enrollment = StudentEnrollment::where('siswa_id', $siswa->id)->first();
        $this->assertNotNull($enrollment);
        $this->assertEquals($this->activeTa->id, $enrollment->tahun_ajaran_id);
        $this->assertEquals('2026/2027', $enrollment->tahun_ajaran);
        $this->assertEquals($this->jurusanPplg->id, $enrollment->jurusan_id);
        $this->assertEquals($this->angkatan19Pplg->id, $enrollment->angkatan_id);
        $this->assertEquals('10', (string) $enrollment->tingkat);
        $this->assertEquals($this->kelas10Pplg1->id, $enrollment->kelas_id);
        $this->assertEquals('aktif', $enrollment->status_enrollment);
        $this->assertTrue((bool) $enrollment->is_active);

        // Verify Eloquent relationships work seamlessly
        $this->assertEquals('PPLG', $siswa->angkatan->jurusan->kode_jurusan);
        $this->assertEquals('Angkatan 19 PPLG', $enrollment->angkatan->nama_angkatan);
        $this->assertEquals('2026/2027', $enrollment->tahunAjaran->nama);
        $this->assertEquals('X PPLG 1', $enrollment->kelas->nama_kelas);
    }

    public function test_transfer_student_registration_preserves_cohort_entry_year_and_logs_audit_trail(): void
    {
        // Transfer student entering Grade 11 during 2026/2027 belongs to Cohort 2025 (Angkatan 18)
        $payload = [
            'nisn'                    => '0087654321',
            'nis'                     => '252611002',
            'nama'                    => 'Siti Pindahan',
            'jenis_kelamin'           => 'P',
            'tahun_ajaran_id'         => $this->activeTa->id,
            'jalur_masuk'             => 'pindahan',
            'asal_sekolah'            => 'SMK Negeri 2 Bogor',
            'jurusan_id'              => $this->jurusanPplg->id,
            'angkatan_id'             => $this->angkatan18Pplg->id,
            'tingkat'                 => '11',
            'kelas_id'                => $this->kelas11Pplg1->id,
            'email'                   => '252611002@myskanic.com',
            'password'                => 'password123',
            'parent_name'             => 'Ahmad Suryo',
            'parent_jenis_kelamin'    => 'L',
            'parent_phone'            => '081298765432',
            'parent_address'          => 'Jl. Pajajaran No. 45 Bogor',
            'confirm_parent_verified' => 'on',
        ];

        $response = $this->actingAs($this->admin)->post(route('siswa.store'), $payload);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $siswa = Siswa::where('nis', '252611002')->first();
        $this->assertNotNull($siswa);
        $this->assertEquals('pindahan', $siswa->jalur_masuk);
        $this->assertEquals('SMK Negeri 2 Bogor', $siswa->asal_sekolah);
        // Cohort entry year must be 2025 (original entry year), NOT current operational academic year 2026
        $this->assertEquals(2025, $siswa->tahun_masuk);
        $this->assertEquals($this->angkatan18Pplg->id, $siswa->angkatan_id);

        // Verify Enrollment is for Grade 11 in operational year 2026/2027
        $enrollment = StudentEnrollment::where('siswa_id', $siswa->id)->first();
        $this->assertNotNull($enrollment);
        $this->assertEquals($this->activeTa->id, $enrollment->tahun_ajaran_id);
        $this->assertEquals('11', (string) $enrollment->tingkat);
        $this->assertEquals($this->kelas11Pplg1->id, $enrollment->kelas_id);
        $this->assertEquals($this->angkatan18Pplg->id, $enrollment->angkatan_id);

        // Verify SiswaStatusHistory audit trail was recorded
        $history = SiswaStatusHistory::where('siswa_id', $siswa->id)->first();
        $this->assertNotNull($history);
        $this->assertEquals('belum_terdaftar', $history->status_sebelumnya);
        $this->assertEquals('aktif', $history->status_baru);
        $this->assertStringContainsString('SMK Negeri 2 Bogor', $history->alasan);
    }

    public function test_backend_validation_rejects_incoherent_hierarchy_relations(): void
    {
        // 1. Mismatch: Rombel Animasi chosen while Jurusan is PPLG
        $responseMismatchMajor = $this->actingAs($this->admin)->post(route('siswa.store'), [
            'nis'                     => '262799001',
            'nama'                    => 'Test Mismatch Major',
            'jenis_kelamin'           => 'L',
            'jurusan_id'              => $this->jurusanPplg->id,
            'tingkat'                 => '10',
            'kelas_id'                => $this->kelas10Animasi1->id, // Belongs to ANIMASI
            'parent_name'             => 'Parent Test',
            'parent_jenis_kelamin'    => 'L',
            'parent_phone'            => '081234567891',
            'parent_address'          => 'Ciomas',
            'confirm_parent_verified' => 'on',
        ]);
        $responseMismatchMajor->assertSessionHasErrors(['kelas_id']);

        // 2. Mismatch: Rombel Grade 10 chosen while Tingkat is 11
        $responseMismatchGrade = $this->actingAs($this->admin)->post(route('siswa.store'), [
            'nis'                     => '262799002',
            'nama'                    => 'Test Mismatch Grade',
            'jenis_kelamin'           => 'L',
            'jurusan_id'              => $this->jurusanPplg->id,
            'tingkat'                 => '11',
            'kelas_id'                => $this->kelas10Pplg1->id, // Tingkat 10
            'parent_name'             => 'Parent Test',
            'parent_jenis_kelamin'    => 'L',
            'parent_phone'            => '081234567892',
            'parent_address'          => 'Ciomas',
            'confirm_parent_verified' => 'on',
        ]);
        $responseMismatchGrade->assertSessionHasErrors(['kelas_id']);

        // 3. Mismatch: Angkatan Animasi chosen while Jurusan/Kelas is PPLG
        $responseMismatchCohort = $this->actingAs($this->admin)->post(route('siswa.store'), [
            'nis'                     => '262799003',
            'nama'                    => 'Test Mismatch Cohort',
            'jenis_kelamin'           => 'L',
            'jurusan_id'              => $this->jurusanPplg->id,
            'angkatan_id'             => $this->angkatan17Animasi->id, // Belongs to ANIMASI
            'tingkat'                 => '10',
            'kelas_id'                => $this->kelas10Pplg1->id,
            'parent_name'             => 'Parent Test',
            'parent_jenis_kelamin'    => 'L',
            'parent_phone'            => '081234567893',
            'parent_address'          => 'Ciomas',
            'confirm_parent_verified' => 'on',
        ]);
        $responseMismatchCohort->assertSessionHasErrors(['angkatan_id']);
    }

    public function test_updating_student_rombel_updates_enrollment_history(): void
    {
        // Create initial student in X PPLG 1
        $user = User::factory()->create(['role' => 'siswa', 'email' => 'student.move@myskanic.com']);
        $siswa = Siswa::create([
            'user_id'       => $user->id,
            'nis'           => '262788001',
            'nama'          => 'Student Move Test',
            'jenis_kelamin' => 'L',
            'kelas_id'      => $this->kelas10Pplg1->id,
            'angkatan_id'   => $this->angkatan19Pplg->id,
            'tahun_masuk'   => 2026,
            'jalur_masuk'   => 'baru',
            'status'        => 'aktif',
        ]);
        $parentUser = User::factory()->create(['role' => 'orangtua', 'email' => 'parent.move@myskanic.com']);
        $parent = OrangTua::create([
            'user_id'  => $parentUser->id,
            'nama'     => 'Parent Move',
            'telepon'  => '081233344455',
            'alamat'   => 'Ciomas',
            'siswa_id' => $siswa->id,
        ]);
        $siswa->orangtua()->attach($parent->id);

        $initialEnrollment = StudentEnrollment::create([
            'siswa_id'          => $siswa->id,
            'tahun_ajaran_id'   => $this->activeTa->id,
            'tahun_ajaran'      => '2026/2027',
            'kelas_id'          => $this->kelas10Pplg1->id,
            'jurusan_id'        => $this->jurusanPplg->id,
            'angkatan_id'       => $this->angkatan19Pplg->id,
            'tingkat'           => 10,
            'status_enrollment' => 'aktif',
            'started_at'        => now()->toDateString(),
            'is_active'         => true,
        ]);

        // Admin moves student from X PPLG 1 to X PPLG 2
        $response = $this->actingAs($this->admin)->post(route('siswa.update', ['id' => $siswa->id]), [
            'nis'                  => '262788001',
            'nama'                 => 'Student Move Test',
            'jenis_kelamin'        => 'L',
            'kelas_id'             => $this->kelas10Pplg2->id,
            'angkatan_id'          => $this->angkatan19Pplg->id,
            'jalur_masuk'          => 'baru',
            'parent_name'          => 'Parent Move',
            'parent_jenis_kelamin' => 'L',
            'parent_phone'         => '081233344455',
            'parent_address'       => 'Ciomas RT 01',
            'username'             => 'student.move',
        ]);

        $response->assertRedirect();

        // Verify class changed on Siswa
        $siswa->refresh();
        $this->assertEquals($this->kelas10Pplg2->id, $siswa->kelas_id);

        // Verify StudentEnrollment reflects new class and active status
        $activeEnrollments = StudentEnrollment::where('siswa_id', $siswa->id)
            ->where('is_active', true)
            ->get();

        $this->assertCount(1, $activeEnrollments);
        $this->assertEquals($this->kelas10Pplg2->id, $activeEnrollments->first()->kelas_id);
        $this->assertEquals($this->activeTa->id, $activeEnrollments->first()->tahun_ajaran_id);
    }

    public function test_student_edit_endpoint_loads_with_hierarchical_data(): void
    {
        $user = User::factory()->create(['role' => 'siswa', 'email' => 'student.view@myskanic.com']);
        $siswa = Siswa::create([
            'user_id'       => $user->id,
            'nis'           => '262777001',
            'nama'          => 'Student View Test',
            'jenis_kelamin' => 'P',
            'kelas_id'      => $this->kelas11Pplg1->id,
            'angkatan_id'   => $this->angkatan18Pplg->id,
            'tahun_masuk'   => 2025,
            'jalur_masuk'   => 'pindahan',
            'asal_sekolah'  => 'SMK Asal',
            'status'        => 'aktif',
        ]);
        $parentUser = User::factory()->create(['role' => 'orangtua', 'email' => 'parent.view@myskanic.com']);
        $parent = OrangTua::create([
            'user_id'  => $parentUser->id,
            'nama'     => 'Parent View',
            'telepon'  => '081255566677',
            'alamat'   => 'Bogor',
            'siswa_id' => $siswa->id,
        ]);
        $siswa->orangtua()->attach($parent->id);

        $response = $this->actingAs($this->admin)->post(route('siswa.edit'), ['id' => $siswa->id]);

        $response->assertOk();
        $response->assertSee('Student View Test');
        $response->assertSee('Angkatan 18 PPLG');
        $response->assertSee('SMK Asal');
        $response->assertSee('XI PPLG 1');
    }

    public function test_regular_student_registration_for_grade_12_assigns_2024_cohort_without_being_transfer(): void
    {
        $payload = [
            'nisn'                    => '0076543210',
            'nis'                     => '242512003',
            'nama'                    => 'Ahmad Kelas Dua Belas',
            'jenis_kelamin'           => 'L',
            'tahun_ajaran_id'         => $this->activeTa->id,
            'jalur_masuk'             => 'baru', // Regular student, NOT transfer
            'jurusan_id'              => $this->jurusanPplg->id,
            'angkatan_id'             => $this->angkatan17Pplg->id,
            'tingkat'                 => '12',
            'kelas_id'                => $this->kelas12Pplg1->id,
            'email'                   => '242512003@myskanic.com',
            'password'                => 'password123',
            'parent_name'             => 'Hasan Basri',
            'parent_jenis_kelamin'    => 'L',
            'parent_phone'            => '081234445566',
            'parent_address'          => 'Jl. Ciomas No. 88',
            'confirm_parent_verified' => 'on',
        ];

        $response = $this->actingAs($this->admin)->post(route('siswa.store'), $payload);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $siswa = Siswa::where('nis', '242512003')->first();
        $this->assertNotNull($siswa);
        $this->assertEquals(2024, $siswa->tahun_masuk);
        $this->assertEquals('baru', $siswa->jalur_masuk);
        $this->assertNull($siswa->asal_sekolah);
        $this->assertEquals($this->angkatan17Pplg->id, $siswa->angkatan_id);
        $this->assertEquals($this->kelas12Pplg1->id, $siswa->kelas_id);

        $enrollment = StudentEnrollment::where('siswa_id', $siswa->id)->first();
        $this->assertNotNull($enrollment);
        $this->assertEquals('12', (string) $enrollment->tingkat);
        $this->assertEquals($this->activeTa->id, $enrollment->tahun_ajaran_id);
        $this->assertEquals('aktif', $enrollment->status_enrollment);
        $this->assertTrue((bool) $enrollment->is_active);
    }

    public function test_student_registration_and_update_persists_ttl_in_database(): void
    {
        $payload = [
            'nisn'                    => '0089998881',
            'nis'                     => '262710999',
            'nama'                    => 'Budi TTL Test',
            'jenis_kelamin'           => 'L',
            'tempat_lahir'            => 'Bogor',
            'tanggal_lahir'           => '2009-08-17',
            'tahun_ajaran_id'         => $this->activeTa->id,
            'jalur_masuk'             => 'baru',
            'jurusan_id'              => $this->jurusanPplg->id,
            'angkatan_id'             => $this->angkatan19Pplg->id,
            'tingkat'                 => '10',
            'kelas_id'                => $this->kelas10Pplg1->id,
            'email'                   => '262710999@myskanic.com',
            'password'                => 'password123',
            'parent_name'             => 'Ayah Budi',
            'parent_jenis_kelamin'    => 'L',
            'parent_phone'            => '081299998888',
            'parent_address'          => 'Ciomas Bogor',
            'confirm_parent_verified' => 'on',
        ];

        $response = $this->actingAs($this->admin)->post(route('siswa.store'), $payload);
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $siswa = Siswa::where('nis', '262710999')->first();
        $this->assertNotNull($siswa);
        $this->assertEquals('Bogor', $siswa->tempat_lahir);
        $this->assertEquals('2009-08-17', $siswa->tanggal_lahir?->format('Y-m-d'));
        $this->assertStringContainsString('Bogor', $siswa->ttl);
        $this->assertStringContainsString('2009', $siswa->ttl);

        // Test update TTL
        $updatePayload = [
            'id'                   => $siswa->id,
            'nisn'                 => '0089998881',
            'nis'                  => '262710999',
            'nama'                 => 'Budi TTL Updated',
            'jenis_kelamin'        => 'L',
            'tempat_lahir'         => 'Jakarta Selatan',
            'tanggal_lahir'        => '2009-09-20',
            'kelas_id'             => $this->kelas10Pplg1->id,
            'angkatan_id'          => $this->angkatan19Pplg->id,
            'jalur_masuk'          => 'baru',
            'email'                => '262710999@myskanic.com',
            'parent_name'          => 'Ayah Budi',
            'parent_jenis_kelamin' => 'L',
            'parent_phone'         => '081299998888',
            'parent_address'       => 'Ciomas Bogor',
        ];

        $updateResponse = $this->actingAs($this->admin)->put(route('siswa.update', $siswa->id), $updatePayload);
        $updateResponse->assertRedirect();

        $siswa->refresh();
        $this->assertEquals('Jakarta Selatan', $siswa->tempat_lahir);
        $this->assertEquals('2009-09-20', $siswa->tanggal_lahir?->format('Y-m-d'));
        $this->assertStringContainsString('Jakarta Selatan', $siswa->ttl);
    }
}
