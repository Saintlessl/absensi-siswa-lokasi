<?php

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\Guru;
use App\Models\HomeroomAssignment;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Regression tests for authorization scoping and status aggregation.
 *
 * BUG-009: a kaprog could edit/move/delete a student from another major.
 * BUG-010: dashboard aggregates counted izin/sakit as alpa because the
 *          comparisons were case-sensitive ('Hadir' vs stored 'hadir').
 */
class ScopingAndStatusTest extends TestCase
{
    use DatabaseTransactions;

    protected User $kaprogA;
    protected Jurusan $jurusanA;
    protected Jurusan $jurusanB;
    protected Kelas $kelasA;
    protected Kelas $kelasB;
    protected Siswa $siswaA;
    protected Siswa $siswaB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->jurusanA = Jurusan::firstOrCreate(
            ['kode_jurusan' => 'SCA'],
            ['nama_jurusan' => 'Scope Major A', 'is_active' => true]
        );
        $this->jurusanB = Jurusan::firstOrCreate(
            ['kode_jurusan' => 'SCB'],
            ['nama_jurusan' => 'Scope Major B', 'is_active' => true]
        );

        $this->kelasA = Kelas::firstOrCreate(
            ['nama_kelas' => '10 SCA 1'],
            ['jurusan_id' => $this->jurusanA->id, 'tingkat' => '10', 'nomor_rombel' => '1', 'tahun_ajaran' => '2026/2027', 'qr_token' => 'qr-sca-1', 'jumlah_siswa' => 30]
        );
        $this->kelasB = Kelas::firstOrCreate(
            ['nama_kelas' => '10 SCB 1'],
            ['jurusan_id' => $this->jurusanB->id, 'tingkat' => '10', 'nomor_rombel' => '1', 'tahun_ajaran' => '2026/2027', 'qr_token' => 'qr-scb-1', 'jumlah_siswa' => 30]
        );

        $this->kaprogA = User::firstOrCreate(
            ['email' => 'kaprog.scoppa@skanic.com'],
            ['name' => 'Kaprog A', 'password' => bcrypt('password123'), 'role' => 'kaprog', 'status' => 'active', 'jurusan_id' => $this->jurusanA->id]
        );

        $userA = User::firstOrCreate(
            ['email' => 'siswa.scoppa@skanic.com'],
            ['name' => 'Siswa A', 'password' => bcrypt('password123'), 'role' => 'siswa', 'status' => 'active']
        );
        $userB = User::firstOrCreate(
            ['email' => 'siswa.scoppb@skanic.com'],
            ['name' => 'Siswa B', 'password' => bcrypt('password123'), 'role' => 'siswa', 'status' => 'active']
        );

        $this->siswaA = Siswa::firstOrCreate(
            ['user_id' => $userA->id],
            ['nis' => 'SCA-001', 'nisn' => 'SCA-NISN-001', 'nama' => 'Siswa A', 'jenis_kelamin' => 'L', 'kelas_id' => $this->kelasA->id, 'biometric_status' => 'enrolled']
        );
        $this->siswaB = Siswa::firstOrCreate(
            ['user_id' => $userB->id],
            ['nis' => 'SCB-001', 'nisn' => 'SCB-NISN-001', 'nama' => 'Siswa B', 'jenis_kelamin' => 'P', 'kelas_id' => $this->kelasB->id, 'biometric_status' => 'enrolled']
        );
    }

    public function test_kaprog_cannot_delete_student_from_another_major()
    {
        $response = $this->actingAs($this->kaprogA)
            ->post(route('siswa.delete', $this->siswaB->id));

        $response->assertStatus(403);
        $this->assertDatabaseHas('siswas', ['id' => $this->siswaB->id]);
    }

    public function test_kaprog_can_delete_student_in_own_major()
    {
        $response = $this->actingAs($this->kaprogA)
            ->post(route('siswa.delete', $this->siswaA->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('siswas', ['id' => $this->siswaA->id]);
    }

    public function test_kaprog_cannot_open_edit_form_for_student_from_another_major()
    {
        $response = $this->actingAs($this->kaprogA)
            ->post(route('siswa.edit'), ['id' => $this->siswaB->id]);

        $response->assertStatus(403);
    }

    public function test_kaprog_cannot_move_student_from_another_major_into_own_major()
    {
        $payload = [
            'username'       => 'siswa.scoppb',
            'nis'            => $this->siswaB->nis,
            'nisn'           => $this->siswaB->nisn,
            'nama'           => $this->siswaB->nama,
            'jenis_kelamin'  => 'P',
            'kelas_id'       => $this->kelasA->id,
            'parent_name'    => 'Orang Tua B',
            'parent_phone'   => '081234567890',
            'parent_address' => 'Alamat B',
        ];

        $response = $this->actingAs($this->kaprogA)
            ->post(route('siswa.update', $this->siswaB->id), $payload);

        $response->assertStatus(403);
        $this->assertDatabaseHas('siswas', ['id' => $this->siswaB->id, 'kelas_id' => $this->kelasB->id]);
    }

    public function test_homeroom_dashboard_counts_lowercase_izin_as_izin_not_alpa()
    {
        $teacherUser = User::firstOrCreate(
            ['email' => 'wali.status.test@skanic.com'],
            ['name' => 'Wali Status Test', 'password' => bcrypt('password123'), 'role' => 'guru', 'status' => 'active']
        );

        $guru = Guru::firstOrCreate(
            ['user_id' => $teacherUser->id],
            ['nama' => 'Wali Status Test', 'jenis_kelamin' => 'L', 'is_active' => true, 'mapel' => 'Test']
        );

        HomeroomAssignment::create([
            'guru_id'      => $guru->id,
            'kelas_id'     => $this->kelasA->id,
            'tahun_ajaran' => '2026/2027',
            'starts_on'    => now()->toDateString(),
            'is_active'    => true,
        ]);

        // IzinController writes lowercase 'izin'; the aggregate used to
        // compare against 'Izin' and therefore counted this student as alpa.
        Absensi::create([
            'siswa_id'   => $this->siswaA->id,
            'tanggal'    => now()->toDateString(),
            'status'     => 'izin',
            'keterangan' => 'sakit gigi',
        ]);

        $response = $this->actingAs($teacherUser)->get(route('homeroom.monitoring'));
        $response->assertStatus(200);
        $response->assertViewHas('izinCount', 1);
        $response->assertViewHas('alpaCount', 0);
    }
}