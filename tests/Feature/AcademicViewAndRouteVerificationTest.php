<?php

namespace Tests\Feature;

use App\Models\AcademicPromotionBatch;
use App\Models\Angkatan;
use App\Models\Guru;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\StudentEnrollment;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicViewAndRouteVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $superadmin;
    protected User $admin;
    protected User $kaprog;
    protected User $guruUser;
    protected User $studentUser;

    protected TahunAjaran $ta2026;
    protected Jurusan $jurusan;
    protected Angkatan $angkatan;
    protected Kelas $kelas;
    protected Siswa $siswa;
    protected AcademicPromotionBatch $batch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superadmin = User::factory()->create(['name' => 'Superadmin View', 'role' => 'superadmin']);
        $this->admin = User::factory()->create(['name' => 'Admin View', 'role' => 'admin']);

        $this->jurusan = Jurusan::firstOrCreate(['kode_jurusan' => 'PPLG'], ['nama_jurusan' => 'Pengembangan Perangkat Lunak', 'is_active' => true]);

        $this->kaprog = User::factory()->create([
            'name'       => 'Kaprog View',
            'role'       => 'kaprog',
            'jurusan_id' => $this->jurusan->id,
        ]);

        $this->guruUser = User::factory()->create(['name' => 'Guru View', 'role' => 'guru']);
        $guru = Guru::create(['user_id' => $this->guruUser->id, 'nama' => 'Guru View', 'nip' => '198001012005011099', 'is_active' => true]);

        $this->ta2026 = TahunAjaran::firstOrCreate(['nama' => '2026/2027'], [
            'tahun_mulai' => 2026, 'tahun_selesai' => 2027, 'semester_aktif' => 'ganjil',
            'tanggal_mulai' => '2026-07-15', 'tanggal_selesai' => '2027-06-25', 'is_active' => true, 'status' => 'aktif'
        ]);

        $this->angkatan = Angkatan::firstOrCreate(
            ['tahun_masuk' => 2026, 'jurusan_id' => $this->jurusan->id],
            ['nomor_angkatan' => 19, 'nama_angkatan' => 'Angkatan 19 PPLG', 'kode_angkatan' => 'AKT-PPLG-19', 'tahun_kelulusan' => 2029, 'status' => 'aktif', 'is_active' => true]
        );

        $this->kelas = Kelas::firstOrCreate(['nama_kelas' => 'X PPLG 1', 'tahun_ajaran_id' => $this->ta2026->id], [
            'jurusan_id' => $this->jurusan->id, 'tingkat' => 10, 'nomor_rombel' => 1, 'tahun_ajaran' => '2026/2027', 'is_active' => true
        ]);

        $this->studentUser = User::factory()->create(['name' => 'Siswa View', 'role' => 'siswa']);
        $this->siswa = Siswa::create([
            'user_id' => $this->studentUser->id, 'nisn' => '0099998888', 'nis' => '262710999', 'nama' => 'Siswa View Test',
            'jenis_kelamin' => 'L', 'kelas_id' => $this->kelas->id, 'angkatan_id' => $this->angkatan->id,
            'tahun_ajaran_masuk_id' => $this->ta2026->id, 'tahun_masuk' => 2026, 'status' => 'aktif',
        ]);

        StudentEnrollment::create([
            'siswa_id' => $this->siswa->id, 'tahun_ajaran_id' => $this->ta2026->id, 'tahun_ajaran' => $this->ta2026->nama,
            'kelas_id' => $this->kelas->id, 'jurusan_id' => $this->jurusan->id, 'angkatan_id' => $this->angkatan->id,
            'tingkat' => 10, 'is_active' => true, 'status_enrollment' => 'aktif', 'started_at' => now(),
        ]);

        $this->batch = AcademicPromotionBatch::create([
            'batch_code' => 'BATCH-TEST-VIEW',
            'tahun_ajaran_asal_id' => $this->ta2026->id,
            'tahun_ajaran_tujuan_id' => $this->ta2026->id,
            'total_siswa' => 1,
            'total_naik' => 1,
            'status' => 'completed',
            'created_by' => $this->admin->id,
        ]);
    }

    public function test_manajemen_tahun_ajaran_page_loads_cleanly(): void
    {
        $response = $this->actingAs($this->superadmin)->get(route('academic.tahun-ajaran.index'));
        $response->assertOk();
        $response->assertSee('2026/2027');
    }

    public function test_manajemen_struktur_akademik_page_loads_cleanly(): void
    {
        $response = $this->actingAs($this->admin)->get(route('academic.index'));
        $response->assertOk();
        $response->assertSee('PPLG');
    }

    public function test_manajemen_rombel_page_loads_cleanly(): void
    {
        $response = $this->actingAs($this->admin)->get(route('kelas'));
        $response->assertOk();
        $response->assertSee('X PPLG 1');
    }

    public function test_manajemen_siswa_page_loads_cleanly(): void
    {
        $response = $this->actingAs($this->admin)->get(route('siswa'));
        $response->assertOk();
        $response->assertSee('Siswa View Test');
    }

    public function test_siswa_import_page_loads_cleanly(): void
    {
        $response = $this->actingAs($this->admin)->get(route('siswa.import'));
        $response->assertOk();
        $response->assertSee('Import Data Siswa');
    }

    public function test_manajemen_wali_kelas_page_loads_cleanly(): void
    {
        $response = $this->actingAs($this->admin)->get(route('academic.wali-kelas.index'));
        $response->assertOk();
    }

    public function test_wizard_kenaikan_kelas_page_loads_cleanly(): void
    {
        $response = $this->actingAs($this->admin)->get(route('academic.promotion.index'));
        $response->assertOk();
        $response->assertSee('Kenaikan Kelas');
    }

    public function test_riwayat_promosi_page_loads_cleanly(): void
    {
        $response = $this->actingAs($this->admin)->get(route('academic.promotion.history'));
        $response->assertOk();
        $response->assertSee('BATCH-TEST-VIEW');
    }

    public function test_detail_siswa_and_alumni_profile_page_loads_cleanly(): void
    {
        $response = $this->actingAs($this->admin)->get(route('siswa.show', $this->siswa->id));
        $response->assertOk();
        $response->assertSee('Siswa View Test');
        $response->assertSee('262710999');
    }
}
