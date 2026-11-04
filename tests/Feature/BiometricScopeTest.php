<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Siswa;
use App\Models\TeacherSubjectAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Regression test for BUG-007 / BUG-008.
 *
 * A guru could previously call the assisted-enrollment and manual-override
 * endpoints for ANY student id. Assisted enrollment accepts a client-supplied
 * descriptor, so a teacher could replace a student's biometric template with
 * their own face and then pass that student's login 2FA — account takeover.
 */
class BiometricScopeTest extends TestCase
{
    use DatabaseTransactions;

    protected User $teacher;
    protected User $otherTeacher;
    protected Kelas $taughtClass;
    protected Kelas $foreignClass;
    protected Siswa $taughtStudent;
    protected Siswa $foreignStudent;

    protected function setUp(): void
    {
        parent::setUp();

        $jurusan = Jurusan::firstOrCreate(
            ['kode_jurusan' => 'BIOS'],
            ['nama_jurusan' => 'Biometric Scope Major', 'is_active' => true]
        );

        $this->taughtClass = Kelas::firstOrCreate(
            ['nama_kelas' => '10 BIOS 1'],
            ['jurusan_id' => $jurusan->id, 'tingkat' => '10', 'nomor_rombel' => '1', 'tahun_ajaran' => '2026/2027', 'qr_token' => 'qr-bios-1', 'jumlah_siswa' => 30]
        );
        $this->foreignClass = Kelas::firstOrCreate(
            ['nama_kelas' => '10 BIOS 2'],
            ['jurusan_id' => $jurusan->id, 'tingkat' => '10', 'nomor_rombel' => '2', 'tahun_ajaran' => '2026/2027', 'qr_token' => 'qr-bios-2', 'jumlah_siswa' => 30]
        );

        $mapel = MataPelajaran::firstOrCreate(
            ['kode_mapel' => 'BIOS-01'],
            ['nama_mapel' => 'Biometric Test Subject', 'kelompok' => 'Umum', 'is_active' => true]
        );

        $this->teacher = User::firstOrCreate(
            ['email' => 'guru.bioscope@skanic.com'],
            ['name' => 'Guru Bio Scope', 'password' => bcrypt('password123'), 'role' => 'guru', 'status' => 'active']
        );

        $guru = Guru::firstOrCreate(
            ['user_id' => $this->teacher->id],
            ['nama' => 'Guru Bio Scope', 'jenis_kelamin' => 'L', 'is_active' => true, 'mapel' => 'Test']
        );

        // Teacher only teaches taughtClass.
        TeacherSubjectAssignment::firstOrCreate(
            ['guru_id' => $guru->id, 'mata_pelajaran_id' => $mapel->id, 'kelas_id' => $this->taughtClass->id, 'tahun_ajaran' => '2026/2027'],
            ['starts_on' => now()->toDateString(), 'is_active' => true]
        );

        $this->otherTeacher = User::firstOrCreate(
            ['email' => 'guru.bioscope2@skanic.com'],
            ['name' => 'Guru Bio Scope 2', 'password' => bcrypt('password123'), 'role' => 'guru', 'status' => 'active']
        );

        $sUserA = User::firstOrCreate(
            ['email' => 'siswa.bioscope1@skanic.com'],
            ['name' => 'Siswa Bio 1', 'password' => bcrypt('password123'), 'role' => 'siswa', 'status' => 'active']
        );
        $sUserB = User::firstOrCreate(
            ['email' => 'siswa.bioscope2@skanic.com'],
            ['name' => 'Siswa Bio 2', 'password' => bcrypt('password123'), 'role' => 'siswa', 'status' => 'active']
        );

        $this->taughtStudent = Siswa::firstOrCreate(
            ['user_id' => $sUserA->id],
            ['nis' => 'BIOS-001', 'nisn' => 'BIOS-N1', 'nama' => 'Siswa Bio 1', 'jenis_kelamin' => 'L', 'kelas_id' => $this->taughtClass->id, 'biometric_status' => 'enrolled']
        );
        $this->foreignStudent = Siswa::firstOrCreate(
            ['user_id' => $sUserB->id],
            ['nis' => 'BIOS-002', 'nisn' => 'BIOS-N2', 'nama' => 'Siswa Bio 2', 'jenis_kelamin' => 'P', 'kelas_id' => $this->foreignClass->id, 'biometric_status' => 'enrolled']
        );
    }

    public function test_guru_cannot_open_assisted_enroll_for_student_outside_their_classes()
    {
        $response = $this->actingAs($this->teacher)
            ->get(route('admin.biometrik.assisted_enroll', $this->foreignStudent->id));

        $response->assertStatus(403);
    }

    public function test_guru_cannot_request_challenge_for_student_outside_their_classes()
    {
        $response = $this->actingAs($this->teacher)
            ->post(route('admin.biometrik.assisted_challenge', $this->foreignStudent->id));

        $response->assertStatus(403);
    }

    public function test_guru_cannot_write_manual_override_for_student_outside_their_classes()
    {
        $response = $this->actingAs($this->teacher)
            ->post(route('admin.biometrik.override'), [
                'siswa_id' => $this->foreignStudent->id,
                'status'   => 'Hadir',
                'tipe'     => 'masuk',
                'reason'   => 'Percobaan manipulasi lintas kelas',
            ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('absensis', ['siswa_id' => $this->foreignStudent->id]);
    }

    public function test_guru_can_still_open_assisted_enroll_for_their_own_class()
    {
        $response = $this->actingAs($this->teacher)
            ->get(route('admin.biometrik.assisted_enroll', $this->taughtStudent->id));

        $response->assertStatus(200);
    }

    public function test_guru_can_open_biometric_panel_without_sql_error()
    {
        // Regression: the panel queried Kelas::where('guru_id', ...) but kelas
        // has no guru_id column, so every guru opening it received HTTP 500.
        $response = $this->actingAs($this->teacher)->get(route('admin.biometrik'));

        $response->assertStatus(200);
    }
}