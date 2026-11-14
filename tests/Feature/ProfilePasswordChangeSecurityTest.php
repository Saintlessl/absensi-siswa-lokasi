<?php

namespace Tests\Feature;

use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfilePasswordChangeSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected User $studentUser;
    protected Siswa $siswa;

    protected function setUp(): void
    {
        parent::setUp();

        $jurusan = Jurusan::create([
            'kode_jurusan' => 'PPLG',
            'nama_jurusan' => 'Pengembangan Perangkat Lunak dan Gim',
            'is_active' => true,
        ]);

        $ta = TahunAjaran::where('nama', '2026/2027')->first() ?? TahunAjaran::firstOrCreate(
            ['nama' => '2026/2027'],
            [
                'tahun_mulai' => 2026,
                'tahun_selesai' => 2027,
                'tanggal_mulai' => '2026-07-15',
                'tanggal_selesai' => '2027-06-30',
                'semester' => 'ganjil',
                'status' => 'aktif',
                'is_active' => true,
            ]
        );

        $kelas = Kelas::create([
            'jurusan_id' => $jurusan->id,
            'tahun_ajaran_id' => $ta->id,
            'nama_kelas' => '10 PPLG 1',
            'tingkat' => 10,
            'is_active' => true,
        ]);

        $this->studentUser = User::factory()->create([
            'role' => 'siswa',
            'name' => 'Azura Zura',
            'email' => '452542895@myskanic.com',
            'password' => Hash::make('defaultPass123'),
            'must_change_password' => true,
        ]);

        $this->siswa = Siswa::create([
            'user_id' => $this->studentUser->id,
            'nis' => '452542895',
            'nama' => 'Azura Zura',
            'jenis_kelamin' => 'P',
            'kelas_id' => $kelas->id,
            'status' => 'aktif',
            'is_biometric_exempt' => true,
        ]);
    }

    /**
     * Test accessing settings.profile is permitted even when must_change_password = true
     */
    public function test_student_with_must_change_password_can_access_profile_page(): void
    {
        $response = $this->actingAs($this->studentUser)->get(route('settings.profile'));
        $response->assertOk();
        $response->assertSee('Wajib Ganti Password');
        $response->assertSee('Azura Zura');
    }

    /**
     * Test updating profile info via settings.profile.info succeeds and syncs to Siswa model
     */
    public function test_student_can_update_profile_info_without_being_blocked(): void
    {
        $response = $this->actingAs($this->studentUser)->post(route('settings.profile.info'), [
            'name' => 'Azura Zura Updated',
            'email' => '452542895@myskanic.com',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->studentUser->refresh();
        $this->siswa->refresh();

        $this->assertEquals('Azura Zura Updated', $this->studentUser->name);
        $this->assertEquals('Azura Zura Updated', $this->siswa->nama);
    }

    /**
     * Test updating password via settings.profile.password succeeds, sets must_change_password to false, and unlocks dashboard
     */
    public function test_student_can_update_password_and_clears_must_change_password(): void
    {
        // Initially trying to access dashboard is blocked and redirected to profile
        $dashBlocked = $this->actingAs($this->studentUser)->get(route('dashboard'));
        $dashBlocked->assertRedirect(route('profile'));
        $dashBlocked->assertSessionHas('warning', 'Demi keamanan akun Anda, silakan ubah kata sandi default terlebih dahulu.');

        // Now student changes their password
        $response = $this->actingAs($this->studentUser)->post(route('settings.profile.password'), [
            'current_password' => 'defaultPass123',
            'password' => 'newSecretPass456',
            'password_confirmation' => 'newSecretPass456',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->studentUser->refresh();
        $this->assertFalse($this->studentUser->must_change_password);
        $this->assertTrue(Hash::check('newSecretPass456', $this->studentUser->password));

        // Now dashboard access is fully permitted!
        $dashAllowed = $this->actingAs($this->studentUser)->get(route('dashboard'));
        $dashAllowed->assertOk();
    }
}
