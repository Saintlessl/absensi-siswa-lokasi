<?php

namespace Tests\Feature;

use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\OrangTua;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SiswaWizardCreationTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $jurusan;
    protected $kelas;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin.test@myskanic.com',
            'password' => bcrypt('password'),
        ]);

        $this->jurusan = Jurusan::create([
            'kode_jurusan' => 'PPLG',
            'nama_jurusan' => 'Pengembangan Perangkat Lunak dan Gim',
            'is_active' => true,
        ]);

        $this->kelas = Kelas::create([
            'jurusan_id' => $this->jurusan->id,
            'tingkat' => '11',
            'nomor_rombel' => '2',
            'nama_kelas' => '11 PPLG 2',
            'tahun_ajaran' => '2026/2027',
            'jumlah_siswa' => 0,
            'is_active' => true,
        ]);
    }

    public function test_student_wizard_modal_creates_student_and_parent_atomically(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $photo = UploadedFile::fake()->image('student_photo.jpg', 300, 300);

        $response = $this->actingAs($this->admin)->post(route('siswa.store'), [
            'nisn' => '0063677346',
            'nis' => '212127327',
            'nama' => 'Arya Saloka',
            'no_telp_siswa' => '081299998888',
            'jenis_kelamin' => 'L',
            'kelas_id' => $this->kelas->id,
            'photo' => $photo,
            'parent_name' => 'Supriono',
            'parent_jenis_kelamin' => 'L',
            'parent_phone' => '08123456789',
            'parent_address' => 'Kp. Sawah Baru RT100/008',
            'confirm_parent_verified' => 'on',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Check student created
        $siswa = Siswa::where('nis', '212127327')->first();
        $this->assertNotNull($siswa);
        $this->assertEquals('0063677346', $siswa->nisn);
        $this->assertEquals('Arya Saloka', $siswa->nama);
        $this->assertEquals('L', $siswa->jenis_kelamin);
        $this->assertEquals($this->kelas->id, $siswa->kelas_id);
        $this->assertNotNull($siswa->photo_path);

        // Check student account with fixed domain @myskanic.com
        $studentUser = User::find($siswa->user_id);
        $this->assertNotNull($studentUser);
        $this->assertEquals('aryasaloka@myskanic.com', $studentUser->email);
        $this->assertEquals('siswa', $studentUser->role);

        // Check parent created and linked
        $parent = OrangTua::where('nama', 'Supriono')->first();
        $this->assertNotNull($parent);
        $this->assertEquals('L', $parent->jenis_kelamin);
        $this->assertStringContainsString('8123456789', $parent->telepon);
        $this->assertEquals('Kp. Sawah Baru RT100/008', $parent->alamat);
        $this->assertEquals($siswa->id, $parent->siswa_id);
        $this->assertTrue($siswa->fresh()->orangtua->contains($parent->id));
    }

    public function test_student_wizard_validates_required_fields(): void
    {
        $response = $this->actingAs($this->admin)->post(route('siswa.store'), [
            // Missing nis, nama, photo, etc.
        ]);

        $response->assertSessionHasErrors(['nis', 'photo']);
    }

    public function test_student_wizard_prevents_duplicate_nis(): void
    {
        $existingUser = User::factory()->create(['role' => 'siswa']);
        Siswa::create([
            'user_id' => $existingUser->id,
            'nis' => '212127327',
            'nama' => 'Existing Student',
            'jenis_kelamin' => 'L',
            'kelas_id' => $this->kelas->id,
            'biometric_status' => 'not_enrolled',
        ]);

        $response = $this->actingAs($this->admin)->post(route('siswa.store'), [
            'nis' => '212127327',
            'nama' => 'New Student with Same NIS',
            'jenis_kelamin' => 'L',
            'kelas_id' => $this->kelas->id,
            'parent_name' => 'Wali',
            'parent_jenis_kelamin' => 'L',
            'parent_phone' => '08123456788',
            'parent_address' => 'Alamat',
        ]);

        $response->assertSessionHasErrors('nis');
    }
}
