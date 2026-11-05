<?php

namespace Tests\Feature;

use App\Models\Assessment;
use App\Models\AssessmentCategory;
use App\Models\AssessmentScore;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GradebookSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_create_assessment_and_scores_are_initialized()
    {
        $teacherUser = User::factory()->create(['role' => 'guru', 'name' => 'Pak Haris']);
        $guru = Guru::create(['user_id' => $teacherUser->id, 'nip' => '199001012015011001', 'nama' => 'Pak Haris']);
        $kelas = Kelas::create(['nama_kelas' => '10 PPLG 1', 'jumlah_siswa' => 30]);
        $mapel = MataPelajaran::create(['nama_mapel' => 'Dasar Pemrograman', 'kode_mapel' => 'DP10']);

        $category = AssessmentCategory::where('kode', 'PRK')->first() ?? AssessmentCategory::create([
            'nama' => 'Praktik Lab',
            'kode' => 'PRK',
            'bobot_default' => 30,
        ]);

        // Create 2 students in that class
        $siswa1 = Siswa::create(['user_id' => User::factory()->create()->id, 'kelas_id' => $kelas->id, 'nis' => '77001', 'nama' => 'Siswa Satu']);
        $siswa2 = Siswa::create(['user_id' => User::factory()->create()->id, 'kelas_id' => $kelas->id, 'nis' => '77002', 'nama' => 'Siswa Dua']);

        $this->actingAs($teacherUser);

        $response = $this->post(route('gradebook.store'), [
            'kelas_id'               => $kelas->id,
            'assessment_category_id' => $category->id,
            'mata_pelajaran_id'      => $mapel->id,
            'judul'                  => 'Praktik CRUD Database Laravel',
            'deskripsi'              => 'Membuat fitur create, read, update, delete',
            'tanggal'                => now()->toDateString(),
            'tahun_ajaran'           => '2026/2027',
            'semester'               => 'ganjil',
            'skor_maksimal'          => 100,
            'bobot'                  => 1.5,
            'status'                 => 'draft',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('assessments', [
            'guru_id'  => $guru->id,
            'kelas_id' => $kelas->id,
            'judul'    => 'Praktik CRUD Database Laravel',
        ]);

        $assessment = Assessment::where('guru_id', $guru->id)->first();
        $this->assertNotNull($assessment);

        // Scores must be initialized as null for all students
        $this->assertEquals(2, $assessment->scores()->count());
        $this->assertNull($assessment->scores()->where('siswa_id', $siswa1->id)->value('nilai'));
        $this->assertNull($assessment->scores()->where('siswa_id', $siswa2->id)->value('nilai'));
    }

    public function test_score_saving_distinguishes_null_vs_zero()
    {
        $teacherUser = User::factory()->create(['role' => 'guru', 'name' => 'Pak Haris']);
        $guru = Guru::create(['user_id' => $teacherUser->id, 'nip' => '199001012015011002', 'nama' => 'Pak Haris']);
        $kelas = Kelas::create(['nama_kelas' => '10 PPLG 2', 'jumlah_siswa' => 30]);
        $category = AssessmentCategory::first();

        $siswa1 = Siswa::create(['user_id' => User::factory()->create()->id, 'kelas_id' => $kelas->id, 'nis' => '77003', 'nama' => 'Siswa Tiga']);
        $siswa2 = Siswa::create(['user_id' => User::factory()->create()->id, 'kelas_id' => $kelas->id, 'nis' => '77004', 'nama' => 'Siswa Empat']);
        $siswa3 = Siswa::create(['user_id' => User::factory()->create()->id, 'kelas_id' => $kelas->id, 'nis' => '77005', 'nama' => 'Siswa Lima']);

        $assessment = Assessment::create([
            'guru_id'                => $guru->id,
            'kelas_id'               => $kelas->id,
            'assessment_category_id' => $category->id,
            'nama_mapel'             => 'Basis Data',
            'judul'                  => 'Kuis Query SQL',
            'tanggal'                => now()->toDateString(),
            'tahun_ajaran'           => '2026/2027',
            'semester'               => 'ganjil',
            'skor_maksimal'          => 100,
            'bobot'                  => 1.0,
            'status'                 => 'draft',
        ]);

        $this->actingAs($teacherUser);

        // Siswa 1 gets 85.50
        // Siswa 2 gets 0.00 (plagiarized / failed)
        // Siswa 3 gets empty string (not submitted / absent)
        $response = $this->post(route('gradebook.save', $assessment->id), [
            'scores' => [
                $siswa1->id => ['nilai' => '85.5', 'catatan' => 'Sangat rapi'],
                $siswa2->id => ['nilai' => '0', 'catatan' => 'Pelanggaran integritas'],
                $siswa3->id => ['nilai' => '', 'catatan' => 'Belum mengumpulkan'],
            ],
            'action' => 'save',
        ]);

        $response->assertRedirect();

        $this->assertEquals(85.50, (float) AssessmentScore::where('assessment_id', $assessment->id)->where('siswa_id', $siswa1->id)->value('nilai'));
        $this->assertEquals(0.00, (float) AssessmentScore::where('assessment_id', $assessment->id)->where('siswa_id', $siswa2->id)->value('nilai'));
        $this->assertNull(AssessmentScore::where('assessment_id', $assessment->id)->where('siswa_id', $siswa3->id)->value('nilai'));
    }

    public function test_publishing_makes_scores_visible_to_student_and_notifies()
    {
        $teacherUser = User::factory()->create(['role' => 'guru', 'name' => 'Bu Maya']);
        $guru = Guru::create(['user_id' => $teacherUser->id, 'nip' => '199201012018012001', 'nama' => 'Bu Maya']);
        $kelas = Kelas::create(['nama_kelas' => '11 BCF 1', 'jumlah_siswa' => 20]);
        $category = AssessmentCategory::first();

        $studentUser = User::factory()->create(['role' => 'siswa', 'name' => 'Dimas Siswa']);
        $siswa = Siswa::create([
            'user_id'          => $studentUser->id,
            'kelas_id'         => $kelas->id,
            'nis'              => '66001',
            'nama'             => 'Dimas Siswa',
            'biometric_status' => 'enrolled',
        ]);
        \App\Models\SiswaFaceBiometric::create([
            'siswa_id'          => $siswa->id,
            'face_descriptor'   => json_encode(array_fill(0, 128, 0.05)),
            'enrollment_status' => 'enrolled',
            'is_active'         => true,
        ]);

        $assessment = Assessment::create([
            'guru_id'                => $guru->id,
            'kelas_id'               => $kelas->id,
            'assessment_category_id' => $category->id,
            'nama_mapel'             => 'Tata Suara Film',
            'judul'                  => 'Perekaman Foley & Voice Over',
            'tanggal'                => now()->toDateString(),
            'tahun_ajaran'           => '2026/2027',
            'semester'               => 'ganjil',
            'skor_maksimal'          => 100,
            'bobot'                  => 1.0,
            'status'                 => 'draft',
        ]);

        AssessmentScore::create([
            'assessment_id' => $assessment->id,
            'siswa_id'      => $siswa->id,
            'nilai'         => 92.00,
        ]);

        // Student checks before publish: should not see any scores
        $this->actingAs($studentUser);
        $studentResponse = $this->get(route('gradebook.student'));
        $studentResponse->assertOk();
        $studentResponse->assertDontSee('Perekaman Foley & Voice Over');

        // Teacher publishes
        $this->actingAs($teacherUser);
        $this->post(route('gradebook.publish', $assessment->id));

        $this->assertEquals('published', $assessment->fresh()->status);
        $this->assertEquals(1, $studentUser->unreadNotifications()->count());

        // Student checks after publish: should see score 92.0
        $this->actingAs($studentUser);
        $studentResponse2 = $this->get(route('gradebook.student'));
        $studentResponse2->assertOk();
        $studentResponse2->assertSee('Perekaman Foley & Voice Over');
        $studentResponse2->assertSee('92.0');
    }

    public function test_teacher_cannot_tamper_another_teachers_assessment()
    {
        $teacherUserA = User::factory()->create(['role' => 'guru', 'name' => 'Guru A']);
        $guruA = Guru::create(['user_id' => $teacherUserA->id, 'nip' => '331', 'nama' => 'Guru A']);

        $teacherUserB = User::factory()->create(['role' => 'guru', 'name' => 'Guru B']);
        $guruB = Guru::create(['user_id' => $teacherUserB->id, 'nip' => '332', 'nama' => 'Guru B']);

        $kelas = Kelas::create(['nama_kelas' => '10 TO 1', 'jumlah_siswa' => 30]);
        $category = AssessmentCategory::first();

        $assessment = Assessment::create([
            'guru_id'                => $guruA->id,
            'kelas_id'               => $kelas->id,
            'assessment_category_id' => $category->id,
            'nama_mapel'             => 'Mesin Otomotif',
            'judul'                  => 'Tune Up Konvensional',
            'tanggal'                => now()->toDateString(),
            'tahun_ajaran'           => '2026/2027',
            'semester'               => 'ganjil',
            'skor_maksimal'          => 100,
            'bobot'                  => 1.0,
            'status'                 => 'draft',
        ]);

        // Guru B tries to access input scores of Guru A's assessment
        $this->actingAs($teacherUserB);
        $response = $this->get(route('gradebook.input', $assessment->id));
        $response->assertForbidden();
    }
}
