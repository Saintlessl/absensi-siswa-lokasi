<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Angkatan;
use App\Models\TahunAjaran;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ExemptStudentBiometricTest extends TestCase
{
    use RefreshDatabase;

    public function test_exempt_student_can_login_without_face_verification(): void
    {
        $ta = TahunAjaran::firstOrCreate(
            ['nama' => '2026/2027'],
            [
                'tahun_mulai' => 2026,
                'tahun_selesai' => 2027,
                'semester' => 'ganjil',
                'tanggal_mulai' => '2026-07-15',
                'tanggal_selesai' => '2026-12-20',
                'is_active' => true,
            ]
        );

        $jurusan = \App\Models\Jurusan::firstOrCreate(
            ['kode_jurusan' => 'PPLG'],
            ['nama_jurusan' => 'Pengembangan Perangkat Lunak dan Gim']
        );

        $angkatan = Angkatan::firstOrCreate(
            ['kode_angkatan' => 'AKT-TEST-26'],
            [
                'nama_angkatan' => 'Angkatan Test 2026',
                'nomor_angkatan' => 26,
                'tahun_masuk' => 2026,
                'jurusan_id' => $jurusan->id,
            ]
        );

        $kelas = Kelas::firstOrCreate(
            [
                'jurusan_id' => $jurusan->id,
                'tingkat' => 10,
                'nomor_rombel' => 1,
                'tahun_ajaran' => '2026/2027',
            ],
            [
                'nama_kelas' => 'X PPLG 1',
            ]
        );

        $user = User::factory()->create([
            'email' => 'test.exempt@myskanic.sch.id',
            'password' => Hash::make('password123'),
            'role' => 'siswa',
            'status' => 'active',
            'must_change_password' => false,
        ]);

        $siswa = Siswa::create([
            'user_id' => $user->id,
            'nis' => '99988811',
            'nisn' => '0099988811',
            'nama' => 'Student Exempt Test',
            'jenis_kelamin' => 'L',
            'kelas_id' => $kelas->id,
            'angkatan_id' => $angkatan->id,
            'biometric_status' => 'not_enrolled',
            'is_biometric_exempt' => true,
            'status' => 'aktif',
        ]);

        // Login via POST /proseslogin
        $response = $this->post('/proseslogin', [
            'email' => 'test.exempt@myskanic.sch.id',
            'password' => 'password123',
        ]);

        // Should NOT redirect to login.verify_face or siswa.enrollment
        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);

        // Access dashboard directly
        $dashResponse = $this->actingAs($user)->get('/dashboard');
        $dashResponse->assertStatus(200);

        // Access device page directly
        $devResponse = $this->actingAs($user)->get('/perangkat-saya');
        $devResponse->assertStatus(200);
    }
}
