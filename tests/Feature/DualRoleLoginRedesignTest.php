<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Jurusan;
use App\Models\SiswaFaceBiometric;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DualRoleLoginRedesignTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_renders_dual_role_elements_and_branding(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Selamat Datang');
        $response->assertSee('MySKANIC');
        $response->assertSee('SMK Negeri 1 Ciomas');
        $response->assertSee('Siswa');
        $response->assertSee('Guru');
        $response->assertSee('student-characters.png');
        $response->assertSee('teacher-characters.png');
        $response->assertSee('images/jurusan/pplg.png');
        $response->assertSee('images/jurusan/animasi.png');
        $response->assertSee('images/jurusan/tpfl.png');
        $response->assertSee('images/jurusan/to.png');
        $response->assertSee('images/jurusan/bcf.png');
        $response->assertSee('proseslogin');
        $response->assertSee('name="email"', false);
        $response->assertSee('name="password"', false);
        $response->assertSee('name="remember"', false);
        $response->assertSee('device_token', false);
    }

    public function test_invalid_login_redirects_back_with_error(): void
    {
        $response = $this->from('/')->post('/proseslogin', [
            'email'    => 'unknown.user@myskanic.test',
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHas('error');
    }

    public function test_teacher_can_login_directly_to_dashboard(): void
    {
        $teacher = User::factory()->create([
            'email'    => 'guru.test@myskanic.test',
            'password' => bcrypt('password123'),
            'role'     => 'guru',
            'status'   => 'active',
        ]);

        $response = $this->post('/proseslogin', [
            'email'    => 'guru.test@myskanic.test',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($teacher);
    }

    public function test_student_with_biometric_redirects_to_verification(): void
    {
        $jurusan = Jurusan::firstOrCreate(
            ['kode_jurusan' => 'PPLG'],
            ['nama_jurusan' => 'Pengembangan Perangkat Lunak dan Gim']
        );
        $kelas = Kelas::firstOrCreate(
            ['nama_kelas' => 'XI PPLG 1'],
            ['tingkat' => '11', 'jurusan_id' => $jurusan->id, 'tahun_ajaran' => '2026/2027']
        );

        $studentUser = User::factory()->create([
            'email'    => 'siswa.test@myskanic.test',
            'password' => bcrypt('password123'),
            'role'     => 'siswa',
            'status'   => 'active',
        ]);

        $siswa = Siswa::create([
            'user_id'          => $studentUser->id,
            'nis'              => '262799999',
            'nisn'             => '0089999999',
            'nama'             => 'Siswa Test Biometrik',
            'jenis_kelamin'    => 'L',
            'kelas_id'         => $kelas->id,
            'biometric_status' => 'enrolled',
        ]);

        SiswaFaceBiometric::create([
            'siswa_id'          => $siswa->id,
            'face_descriptor'   => json_encode(array_fill(0, 128, 0.05)),
            'enrollment_status' => 'enrolled',
            'is_active'         => true,
            'enrolled_at'       => now(),
        ]);

        $response = $this->post('/proseslogin', [
            'email'    => 'siswa.test@myskanic.test',
            'password' => 'password123',
        ]);

        // Student with active biometric verification is redirected to verify_face
        $response->assertRedirect(route('login.verify_face'));
    }
}
