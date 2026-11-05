<?php

namespace Tests\Feature;

use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\SiswaFaceBiometric;
use App\Models\StudentPortfolio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentPortfolioTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $kaprog;
    private $jurusan;
    private $kelas;
    private $siswaUser;
    private $siswa;

    protected function setUp(): void
    {
        parent::setUp();

        $this->jurusan = Jurusan::create([
            'kode_jurusan' => 'PPLG',
            'nama_jurusan' => 'Pengembangan Perangkat Lunak dan Gim',
        ]);

        $this->kelas = Kelas::create([
            'nama_kelas'   => '12 PPLG 2',
            'tingkat'      => '12',
            'jurusan_id'   => $this->jurusan->id,
            'tahun_ajaran' => '2026/2027',
            'jumlah_siswa' => 30,
        ]);

        $this->admin = User::create([
            'name'     => 'Admin TeFa',
            'email'    => 'tefa@smk.test',
            'password' => bcrypt('password'),
            'role'     => 'admin',
        ]);

        $this->kaprog = User::create([
            'name'       => 'Pak Kaprog PPLG',
            'email'      => 'kaprog.pplg@smk.test',
            'password'   => bcrypt('password'),
            'role'       => 'kaprog',
            'jurusan_id' => $this->jurusan->id,
        ]);

        $this->siswaUser = User::create([
            'name'     => 'Kreator Siswa',
            'email'    => 'kreator@smk.test',
            'password' => bcrypt('password'),
            'role'     => 'siswa',
        ]);

        $this->siswa = Siswa::create([
            'user_id'          => $this->siswaUser->id,
            'nama'             => 'Kreator Siswa',
            'nis'              => '10293850',
            'nisn'             => '0081234570',
            'kelas_id'         => $this->kelas->id,
            'jurusan_id'       => $this->jurusan->id,
            'biometric_status' => 'enrolled',
        ]);

        SiswaFaceBiometric::create([
            'siswa_id'          => $this->siswa->id,
            'face_descriptor'   => json_encode(array_fill(0, 128, 0.05)),
            'enrollment_status' => 'enrolled',
            'is_active'         => true,
            'enrolled_at'       => now(),
        ]);
    }

    public function test_student_can_submit_portfolio_project()
    {
        $response = $this->actingAs($this->siswaUser)->post(route('portfolio.store'), [
            'judul'          => 'Smart School IoT Weather Station',
            'kategori'       => 'iot_hardware',
            'deskripsi'      => 'Stasiun cuaca pintar berbasis ESP32 dan dashboard web real-time untuk pemantauan lingkungan sekolah.',
            'demo_url'       => 'https://weather.smkn1.test',
            'repository_url' => 'https://github.com/smkn/iot-weather',
        ]);

        $response->assertRedirect(route('portfolio.student.index'));
        $this->assertDatabaseHas('student_portfolios', [
            'siswa_id' => $this->siswa->id,
            'judul'    => 'Smart School IoT Weather Station',
            'slug'     => 'smart-school-iot-weather-station',
            'status'   => 'submitted',
        ]);
    }

    public function test_kaprog_can_moderate_and_approve_tefa_product()
    {
        $portfolio = StudentPortfolio::create([
            'siswa_id'   => $this->siswa->id,
            'jurusan_id' => $this->jurusan->id,
            'judul'      => 'Antigravity 2D Platformer Game',
            'slug'       => 'antigravity-2d-platformer-game',
            'kategori'   => 'game',
            'deskripsi'  => 'Game platformer petualangan luar angkasa dengan engine Godot 4.2.',
            'status'     => 'submitted',
        ]);

        $response = $this->actingAs($this->kaprog)->post(route('portfolio.moderate', $portfolio->id), [
            'status'           => 'approved',
            'is_tefa_product'  => 1,
            'moderation_notes' => 'Karya game sangat baik dan siap dipasarkan sebagai produk komersial Teaching Factory.',
        ]);

        $response->assertSessionHas('success');
        $portfolio->refresh();

        $this->assertEquals('approved', $portfolio->status);
        $this->assertTrue($portfolio->is_tefa_product);
        $this->assertEquals($this->kaprog->id, $portfolio->approved_by);
        $this->assertNotNull($portfolio->approved_at);
    }

    public function test_gallery_only_shows_approved_portfolios()
    {
        StudentPortfolio::create([
            'siswa_id'   => $this->siswa->id,
            'jurusan_id' => $this->jurusan->id,
            'judul'      => 'Karya Terverifikasi Lulus',
            'slug'       => 'karya-terverifikasi-lulus',
            'kategori'   => 'web_app',
            'deskripsi'  => 'Aplikasi web sekolah yang sudah disetujui kurator.',
            'status'     => 'approved',
        ]);

        StudentPortfolio::create([
            'siswa_id'   => $this->siswa->id,
            'jurusan_id' => $this->jurusan->id,
            'judul'      => 'Karya Masih Draft Rahasia',
            'slug'       => 'karya-masih-draft-rahasia',
            'kategori'   => 'web_app',
            'deskripsi'  => 'Proyek masih dalam tahap rancangan awal.',
            'status'     => 'submitted',
        ]);

        $response = $this->actingAs($this->admin)->get(route('portfolio.index'));
        $response->assertOk();
        $response->assertSee('Karya Terverifikasi Lulus');
        $response->assertDontSee('Karya Masih Draft Rahasia');
    }

    public function test_viewing_portfolio_increments_views_count()
    {
        $portfolio = StudentPortfolio::create([
            'siswa_id'    => $this->siswa->id,
            'jurusan_id'  => $this->jurusan->id,
            'judul'       => 'Sistem Parkir RFID Otomatis',
            'slug'        => 'sistem-parkir-rfid-otomatis',
            'kategori'    => 'iot_hardware',
            'deskripsi'   => 'Alat gerbang palang parkir dengan scan kartu siswa RFID.',
            'status'      => 'approved',
            'views_count' => 0,
        ]);

        $response = $this->actingAs($this->admin)->get(route('portfolio.show', $portfolio->slug));
        $response->assertOk();

        $portfolio->refresh();
        $this->assertEquals(1, $portfolio->views_count);
    }
}
