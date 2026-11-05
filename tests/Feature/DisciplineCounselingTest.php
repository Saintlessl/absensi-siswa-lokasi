<?php

namespace Tests\Feature;

use App\Models\CounselingSession;
use App\Models\DisciplineCategory;
use App\Models\DisciplineRecord;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\SiswaFaceBiometric;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DisciplineCounselingTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $guruBk;
    private $waliKelas;
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

        $this->waliKelas = User::create([
            'name'     => 'Pak Guru Wali',
            'email'    => 'wali@smk.test',
            'password' => bcrypt('password'),
            'role'     => 'guru',
        ]);

        $this->kelas = Kelas::create([
            'nama_kelas'    => '11 PPLG 2',
            'tingkat'       => '11',
            'jurusan_id'    => $this->jurusan->id,
            'tahun_ajaran'  => '2026/2027',
            'jumlah_siswa'  => 30,
            'wali_kelas_id' => $this->waliKelas->id,
        ]);

        $this->admin = User::create([
            'name'     => 'Admin Kesiswaan',
            'email'    => 'kesiswaan@smk.test',
            'password' => bcrypt('password'),
            'role'     => 'admin',
        ]);

        $this->guruBk = User::create([
            'name'     => 'Ibu Konselor BK',
            'email'    => 'bk@smk.test',
            'password' => bcrypt('password'),
            'role'     => 'guru',
        ]);

        $this->siswaUser = User::create([
            'name'     => 'Bagas Siswa',
            'email'    => 'bagas@smk.test',
            'password' => bcrypt('password'),
            'role'     => 'siswa',
        ]);

        $this->siswa = Siswa::create([
            'user_id'          => $this->siswaUser->id,
            'nama'             => 'Bagas Siswa',
            'nis'              => '10293849',
            'nisn'             => '0081234569',
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

    public function test_teacher_can_record_discipline_infraction()
    {
        $category = DisciplineCategory::where('tipe', 'pelanggaran')->first();

        $response = $this->actingAs($this->waliKelas)->post(route('discipline.store'), [
            'siswa_id'               => $this->siswa->id,
            'discipline_category_id' => $category->id,
            'tanggal'                => now()->toDateString(),
            'judul'                  => 'Terlambat Masuk Jam Pertama',
            'deskripsi'              => 'Siswa terlambat 25 menit karena kendala transportasi.',
            'poin'                   => 5,
        ]);

        $response->assertRedirect(route('discipline.index'));
        $this->assertDatabaseHas('discipline_records', [
            'siswa_id'               => $this->siswa->id,
            'discipline_category_id' => $category->id,
            'poin'                   => 5,
            'status'                 => 'recorded',
        ]);
    }

    public function test_recording_achievement_adds_merit_points()
    {
        $category = DisciplineCategory::where('tipe', 'prestasi')->first();

        $response = $this->actingAs($this->admin)->post(route('discipline.store'), [
            'siswa_id'               => $this->siswa->id,
            'discipline_category_id' => $category->id,
            'tanggal'                => now()->toDateString(),
            'judul'                  => 'Juara 1 LKS Web Technologies Kota',
            'deskripsi'              => 'Meraih medali emas seleksi LKS SMK tingkat kota.',
            'poin'                   => 50,
        ]);

        $response->assertRedirect(route('discipline.index'));
        $this->assertDatabaseHas('discipline_records', [
            'siswa_id' => $this->siswa->id,
            'poin'     => 50,
        ]);
    }

    public function test_admin_can_resolve_discipline_record()
    {
        $category = DisciplineCategory::where('tipe', 'pelanggaran')->first();

        $record = DisciplineRecord::create([
            'siswa_id'               => $this->siswa->id,
            'discipline_category_id' => $category->id,
            'tanggal'                => now()->subDays(2)->toDateString(),
            'judul'                  => 'Seragam tidak rapi',
            'deskripsi'              => 'Tidak mengenakan dasi saat upacara.',
            'poin'                   => 5,
            'status'                 => 'recorded',
            'recorded_by'            => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->post(route('discipline.status', $record->id), [
            'status'        => 'resolved',
            'tindak_lanjut' => 'Telah diberikan pengarahan oleh wali kelas dan berjanji tertib.',
        ]);

        $response->assertSessionHas('success');
        $record->refresh();
        $this->assertEquals('resolved', $record->status);
        $this->assertStringContainsString('pengarahan', $record->tindak_lanjut);
    }

    public function test_bk_counseling_confidentiality_scope()
    {
        $session = CounselingSession::create([
            'siswa_id'             => $this->siswa->id,
            'guru_bk_id'           => $this->guruBk->id,
            'tanggal'              => now()->toDateString(),
            'topik_umum'           => 'Konsultasi Perubahan Sikap di Kelas',
            'permasalahan_rahasia' => 'Catatan sangat rahasia: Siswa mengalami konflik keluarga pribadi.',
            'tindak_lanjut_umum'   => 'Wali kelas diminta memantau kehadiran dengan pendekatan suportif.',
            'status'               => 'in_progress',
        ]);

        // BK counselor can view confidential notes
        $this->assertTrue($session->canViewConfidentialNotes($this->guruBk));

        // Superadmin/Admin can view confidential notes
        $this->assertTrue($session->canViewConfidentialNotes($this->admin));

        // Wali Kelas / regular teacher CANNOT view confidential notes
        $this->assertFalse($session->canViewConfidentialNotes($this->waliKelas));

        // Guest / unauthenticated CANNOT view
        $this->assertFalse($session->canViewConfidentialNotes(null));
    }

    public function test_student_can_access_discipline_score_portal()
    {
        $category = DisciplineCategory::where('tipe', 'prestasi')->first();

        DisciplineRecord::create([
            'siswa_id'               => $this->siswa->id,
            'discipline_category_id' => $category->id,
            'tanggal'                => now()->toDateString(),
            'judul'                  => 'LKS Juara 1',
            'deskripsi'              => 'Juara 1 LKS Kota',
            'poin'                   => 50,
            'status'                 => 'verified',
        ]);

        $response = $this->actingAs($this->siswaUser)->get(route('discipline.student.portal'));
        $response->assertOk();
        $response->assertSee('Bagas Siswa');
        $response->assertSee('LKS Juara 1');
    }
}
