<?php

namespace Tests\Feature;

use App\Models\Angkatan;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\SiswaStatusHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CohortAndStudentStatusArchitectureTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;
    protected Kelas $kelas;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::firstOrCreate(
            ['email' => 'admin.test.cohort@myskanic.com'],
            ['name' => 'Admin Cohort Test', 'password' => bcrypt('password123'), 'role' => 'admin', 'status' => 'active']
        );

        $jurusan = Jurusan::first() ?? Jurusan::create([
            'nama_jurusan' => 'Pengembangan Perangkat Lunak dan Gim',
            'kode_jurusan' => 'PPLG',
        ]);

        $this->kelas = Kelas::first() ?? Kelas::create([
            'nama_kelas'   => 'X PPLG 1',
            'tingkat'      => '10',
            'jurusan_id'   => $jurusan->id,
            'jumlah_siswa' => 0,
            'tahun_ajaran' => '2026/2027',
        ]);
    }

    protected function createStudentUser(string $name, string $email): User
    {
        return User::create([
            'name'     => $name,
            'email'    => $email,
            'password' => bcrypt('password123'),
            'role'     => 'siswa',
            'status'   => 'active',
        ]);
    }

    /**
     * Test Angkatan creation, validation, dynamic accessors, and scopes.
     */
    public function test_angkatan_model_crud_and_computed_properties(): void
    {
        $angkatan = Angkatan::create([
            'nomor_angkatan'         => 99,
            'nama_angkatan'          => 'Angkatan 99',
            'kode_angkatan'          => 'AKT-99',
            'tahun_masuk'            => 2026,
            'tahun_ajaran_masuk'     => '2026/2027',
            'tahun_lulus_rencana'    => 2029,
            'tahun_ajaran_kelulusan' => '2028/2029',
            'status'                 => 'aktif',
            'created_by'             => $this->admin->id,
        ]);

        $this->assertDatabaseHas('angkatans', [
            'nomor_angkatan' => 99,
            'kode_angkatan'  => 'AKT-99',
            'status'         => 'aktif',
        ]);

        $this->assertTrue($angkatan->isAktif());
        $this->assertFalse($angkatan->isSelesai());
        $this->assertEquals('Aktif', $angkatan->status_label);
        $this->assertEquals('Angkatan 99 (2026/2027)', $angkatan->display_title);
        $this->assertEquals(0, $angkatan->jumlah_siswa);
        $this->assertEquals(0, $angkatan->jumlah_siswa_aktif);
    }

    /**
     * Test Siswa relationship with Angkatan and computed counters.
     */
    public function test_siswa_angkatan_relation_and_status_counters(): void
    {
        $angkatan = Angkatan::create([
            'nomor_angkatan'         => 98,
            'nama_angkatan'          => 'Angkatan 98',
            'kode_angkatan'          => 'AKT-98',
            'tahun_masuk'            => 2025,
            'tahun_ajaran_masuk'     => '2025/2026',
            'tahun_lulus_rencana'    => 2028,
            'tahun_ajaran_kelulusan' => '2027/2028',
            'status'                 => 'aktif',
        ]);

        $u1 = $this->createStudentUser('Siswa Aktif Test', 'siswa.aktif.test@myskanic.com');
        $siswaAktif = Siswa::create([
            'user_id'      => $u1->id,
            'nis'          => '98001',
            'nisn'         => '0098000001',
            'nama'         => 'Siswa Aktif Test',
            'angkatan_id'  => $angkatan->id,
            'tahun_masuk'  => 2025,
            'status'       => 'aktif',
            'kelas_id'     => $this->kelas->id,
        ]);

        $u2 = $this->createStudentUser('Siswa Lulus Test', 'siswa.lulus.test@myskanic.com');
        $siswaLulus = Siswa::create([
            'user_id'            => $u2->id,
            'nis'                => '98002',
            'nisn'               => '0098000002',
            'nama'               => 'Siswa Lulus Test',
            'angkatan_id'        => $angkatan->id,
            'tahun_masuk'        => 2025,
            'status'             => 'lulus',
            'tanggal_kelulusan'  => now()->toDateString(),
            'tahun_lulus'        => 2028,
            'tahun_ajaran_lulus' => '2027/2028',
            'kelas_id'           => $this->kelas->id,
        ]);

        $u3 = $this->createStudentUser('Siswa Pindah Test', 'siswa.pindah.test@myskanic.com');
        $siswaPindah = Siswa::create([
            'user_id'     => $u3->id,
            'nis'         => '98003',
            'nisn'        => '0098000003',
            'nama'        => 'Siswa Pindah Test',
            'angkatan_id' => $angkatan->id,
            'tahun_masuk' => 2025,
            'status'      => 'pindah',
            'kelas_id'    => $this->kelas->id,
        ]);

        // Refresh angkatan
        $angkatan->refresh();

        $this->assertEquals(3, $angkatan->jumlah_siswa);
        $this->assertEquals(1, $angkatan->jumlah_siswa_aktif);
        $this->assertEquals(1, $angkatan->jumlah_siswa_lulus);
        $this->assertEquals(1, $angkatan->jumlah_siswa_pindah);
        $this->assertEquals(0, $angkatan->jumlah_siswa_keluar);

        // Siswa helper method tests
        $this->assertTrue($siswaAktif->isAktif());
        $this->assertTrue($siswaAktif->canAttend());
        $this->assertEquals('Aktif', $siswaAktif->status_label);

        $this->assertTrue($siswaLulus->isLulus());
        $this->assertTrue($siswaLulus->isAlumni());
        $this->assertFalse($siswaLulus->canAttend());
        $this->assertEquals('Lulus (Alumni)', $siswaLulus->status_label);

        $this->assertTrue($siswaPindah->isPindah());
        $this->assertFalse($siswaPindah->canAttend());
        $this->assertEquals('Pindah Sekolah', $siswaPindah->status_label);

        // Siswa scopes test
        $this->assertEquals(1, Siswa::byAngkatan($angkatan->id)->aktif()->count());
        $this->assertEquals(1, Siswa::byAngkatan($angkatan->id)->lulus()->count());
        $this->assertEquals(1, Siswa::byAngkatan($angkatan->id)->pindah()->count());
    }

    /**
     * Test SiswaStatusHistory audit trail logging and association.
     */
    public function test_siswa_status_history_audit_trail(): void
    {
        $u4 = $this->createStudentUser('Siswa Audit History Test', 'siswa.audit.test@myskanic.com');
        $siswa = Siswa::create([
            'user_id'     => $u4->id,
            'nis'         => '98004',
            'nisn'        => '0098000004',
            'nama'        => 'Siswa Audit History Test',
            'tahun_masuk' => 2025,
            'status'      => 'aktif',
            'kelas_id'    => $this->kelas->id,
        ]);

        // Record status change to pindah
        $history = SiswaStatusHistory::create([
            'siswa_id'          => $siswa->id,
            'status_sebelumnya' => 'aktif',
            'status_baru'       => 'pindah',
            'tanggal_perubahan' => now()->toDateString(),
            'tahun_ajaran'      => '2025/2026',
            'alasan'            => 'Pindah domisili orang tua ke luar kota',
            'sekolah_tujuan'    => 'SMK Negeri 1 Surabaya',
            'nomor_surat'       => '421/089/SMK/2025',
            'created_by'        => $this->admin->id,
        ]);

        $this->assertDatabaseHas('siswa_status_histories', [
            'siswa_id'       => $siswa->id,
            'status_baru'    => 'pindah',
            'sekolah_tujuan' => 'SMK Negeri 1 Surabaya',
        ]);

        $this->assertEquals('Pindah Sekolah', $history->status_label);
        $this->assertEquals($siswa->id, $history->siswa->id);
        $this->assertEquals($this->admin->id, $history->createdBy->id);

        // Siswa latestStatusHistory relationship
        $this->assertNotNull($siswa->latestStatusHistory);
        $this->assertEquals('pindah', $siswa->latestStatusHistory->status_baru);
        $this->assertEquals('SMK Negeri 1 Surabaya', $siswa->latestStatusHistory->sekolah_tujuan);
    }
}
