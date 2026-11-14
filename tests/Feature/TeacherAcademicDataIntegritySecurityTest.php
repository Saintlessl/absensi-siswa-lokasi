<?php

namespace Tests\Feature;

use App\Models\Assessment;
use App\Models\AssessmentCategory;
use App\Models\AssessmentScore;
use App\Models\Guru;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Siswa;
use App\Models\TeachingJournal;
use App\Models\TeachingJournalAttendance;
use App\Models\User;
use App\Services\TeacherAssignmentService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class TeacherAcademicDataIntegritySecurityTest extends TestCase
{
    private User $admin;
    private User $teacherUser;
    private Guru $teacher;
    private Kelas $kelas;
    private MataPelajaran $mapel;
    private Siswa $siswa1;
    private Siswa $siswa2;
    private AssessmentCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Setup Admin
        $this->admin = User::firstOrCreate(
            ['email' => 'admin.test@myskanic.com'],
            ['name' => 'Admin Test', 'password' => Hash::make('password123'), 'role' => 'admin', 'status' => 'active', 'must_change_password' => false]
        );

        // 2. Setup Academic Structure
        $jurusan = Jurusan::firstOrCreate(
            ['nama_jurusan' => 'Teknik Komputer dan Jaringan'],
            ['kode_jurusan' => 'TKJ', 'singkatan' => 'TKJ', 'is_active' => true]
        );

        $this->kelas = Kelas::firstOrCreate(
            ['nama_kelas' => 'XII TKJ 1'],
            ['tingkat' => '12', 'jurusan_id' => $jurusan->id, 'tahun_ajaran' => '2026/2027', 'is_active' => true]
        );

        $this->mapel = MataPelajaran::firstOrCreate(
            ['nama_mapel' => 'Administrasi Infrastruktur Jaringan'],
            ['kode_mapel' => 'AIJ-12', 'is_active' => true]
        );

        $this->category = AssessmentCategory::firstOrCreate(
            ['kode' => 'UH'],
            ['nama' => 'Ulangan Harian', 'bobot_default' => 20, 'is_active' => true]
        );

        // 3. Setup Teacher Account & Profile
        $rawEmail = 'guru.akademik.' . Str::random(6) . '@myskanic.com';
        $this->teacherUser = User::create([
            'name'                 => 'Bambang Sudarsono, S.Pd',
            'email'                => $rawEmail,
            'password'             => Hash::make('GuruPassword123!'),
            'role'                 => 'guru',
            'status'               => 'active',
            'must_change_password' => false,
            'jenis_kelamin'        => 'L',
        ]);

        $this->teacher = Guru::create([
            'user_id'            => $this->teacherUser->id,
            'nip'                => '19850101' . rand(1000, 9999),
            'nama'               => 'Bambang Sudarsono, S.Pd',
            'mapel'              => $this->mapel->nama_mapel,
            'jenis_kelamin'      => 'L',
            'status_kepegawaian' => 'PNS',
            'is_active'          => true,
        ]);

        // 4. Setup Students
        $siswaUser1 = User::create([
            'name'                 => 'Ahmad Fadhil',
            'email'                => 'ahmad.' . Str::random(6) . '@myskanic.com',
            'password'             => Hash::make('password123'),
            'role'                 => 'siswa',
            'status'               => 'active',
            'must_change_password' => false,
        ]);
        $this->siswa1 = Siswa::create([
            'user_id'       => $siswaUser1->id,
            'nis'           => '2627' . rand(10000, 99999),
            'nama'          => 'Ahmad Fadhil',
            'kelas_id'      => $this->kelas->id,
            'jenis_kelamin' => 'L',
        ]);

        $siswaUser2 = User::create([
            'name'                 => 'Budi Pratama',
            'email'                => 'budi.' . Str::random(6) . '@myskanic.com',
            'password'             => Hash::make('password123'),
            'role'                 => 'siswa',
            'status'               => 'active',
            'must_change_password' => false,
        ]);
        $this->siswa2 = Siswa::create([
            'user_id'       => $siswaUser2->id,
            'nis'           => '2627' . rand(10000, 99999),
            'nama'          => 'Budi Pratama',
            'kelas_id'      => $this->kelas->id,
            'jenis_kelamin' => 'L',
        ]);
    }

    /**
     * Test full cycle:
     * - Guru has assessments, student scores, and teaching journals.
     * - Hard delete is rejected by backend and database engine.
     * - Deactivation successfully sets user deactivated & guru inactive.
     * - Teacher cannot login anymore.
     * - All academic records (assessments, student scores, journals, attendances) remain 100% intact.
     * - Historical relation displays teacher identity correctly.
     * - Deactivated teacher is blocked from receiving new assignments.
     */
    public function test_teacher_deactivation_preserves_all_student_academic_records()
    {
        // 1. Create Assessment & Scores for Students
        $assessment = Assessment::create([
            'guru_id'                => $this->teacher->id,
            'kelas_id'               => $this->kelas->id,
            'mata_pelajaran_id'      => $this->mapel->id,
            'nama_mapel'             => $this->mapel->nama_mapel,
            'assessment_category_id' => $this->category->id,
            'judul'                  => 'Ulangan Harian Routing Dinamis',
            'tanggal'                => now()->toDateString(),
            'tahun_ajaran'           => '2026/2027',
            'semester'               => 'ganjil',
            'skor_maksimal'          => 100,
            'bobot'                  => 1.0,
            'status'                 => 'published',
        ]);

        $score1 = AssessmentScore::create([
            'assessment_id' => $assessment->id,
            'siswa_id'      => $this->siswa1->id,
            'nilai'         => 88.50,
            'catatan'       => 'Sangat baik dalam konfigurasi BGP.',
        ]);

        $score2 = AssessmentScore::create([
            'assessment_id' => $assessment->id,
            'siswa_id'      => $this->siswa2->id,
            'nilai'         => 92.00,
            'catatan'       => 'Paham konsep routing OSPF.',
        ]);

        // 2. Create Teaching Journal & Attendance
        $journal = TeachingJournal::create([
            'guru_id'           => $this->teacher->id,
            'kelas_id'          => $this->kelas->id,
            'mata_pelajaran_id' => $this->mapel->id,
            'nama_mapel'        => $this->mapel->nama_mapel,
            'tanggal'           => now()->toDateString(),
            'jam_ke'            => '1-4',
            'materi_pokok'      => 'Konfigurasi MikroTik OSPF Multi-Area',
            'aktivitas'         => 'Praktik langsung di Lab Jaringan',
            'status'            => 'submitted',
        ]);

        $journalAtt1 = TeachingJournalAttendance::create([
            'teaching_journal_id' => $journal->id,
            'siswa_id'            => $this->siswa1->id,
            'status'              => 'hadir',
        ]);

        $journalAtt2 = TeachingJournalAttendance::create([
            'teaching_journal_id' => $journal->id,
            'siswa_id'            => $this->siswa2->id,
            'status'              => 'hadir',
        ]);

        // Verify model detects academic records
        $this->assertTrue($this->teacher->hasAcademicRecords());
        $summary = $this->teacher->getAcademicRecordsSummary();
        $this->assertEquals(1, $summary['assessments']);
        $this->assertEquals(2, $summary['scores']);
        $this->assertEquals(1, $summary['journals']);

        // 3. Attempting Hard Delete via Controller MUST BE BLOCKED
        $deleteResponse = $this->actingAs($this->admin)->post(route('guru.delete', $this->teacher->id));
        $deleteResponse->assertRedirect();
        $deleteResponse->assertSessionHas('error');

        // Teacher and all records must STILL exist
        $this->assertDatabaseHas('gurus', ['id' => $this->teacher->id]);
        $this->assertDatabaseHas('assessments', ['id' => $assessment->id]);
        $this->assertDatabaseHas('assessment_scores', ['id' => $score1->id]);
        $this->assertDatabaseHas('assessment_scores', ['id' => $score2->id]);
        $this->assertDatabaseHas('teaching_journals', ['id' => $journal->id]);
        $this->assertDatabaseHas('teaching_journal_attendances', ['id' => $journalAtt1->id]);

        // Also attempting hard delete via UserController MUST BE BLOCKED
        $userDeleteResponse = $this->actingAs($this->admin)->post(route('user.delete', $this->teacherUser->id));
        $userDeleteResponse->assertRedirect();
        $userDeleteResponse->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $this->teacherUser->id]);

        // 4. Execute Deactivation Action (The correct, sanctioned path)
        $deactivateResponse = $this->actingAs($this->admin)->post(route('guru.deactivate', $this->teacher->id));
        $deactivateResponse->assertRedirect();
        $deactivateResponse->assertSessionHas('success');

        // 5. Verify Teacher State After Deactivation
        $this->teacher->refresh();
        $this->teacherUser->refresh();

        $this->assertFalse($this->teacher->is_active, 'Guru is_active must be false.');
        $this->assertEquals('deactivated', $this->teacherUser->status, 'User status must be deactivated.');

        // 6. Verify Teacher CANNOT LOGIN
        Auth::logout();
        session()->flush();

        $loginAttempt = $this->post(route('proseslogin'), [
            'email'       => $this->teacherUser->email,
            'password'    => 'GuruPassword123!',
            'portal_role' => 'guru',
        ]);
        $loginAttempt->assertSessionHas('error');
        $this->assertFalse(Auth::check(), 'Deactivated teacher must not be able to log in.');

        // 7. Verify Academic Integrity: All scores and journals remain 100% intact
        $this->assertDatabaseHas('assessments', [
            'id'      => $assessment->id,
            'guru_id' => $this->teacher->id,
            'judul'   => 'Ulangan Harian Routing Dinamis',
        ]);

        $this->assertDatabaseHas('assessment_scores', [
            'id'            => $score1->id,
            'assessment_id' => $assessment->id,
            'siswa_id'      => $this->siswa1->id,
            'nilai'         => 88.50,
        ]);

        $this->assertDatabaseHas('assessment_scores', [
            'id'            => $score2->id,
            'assessment_id' => $assessment->id,
            'siswa_id'      => $this->siswa2->id,
            'nilai'         => 92.00,
        ]);

        $this->assertDatabaseHas('teaching_journals', [
            'id'      => $journal->id,
            'guru_id' => $this->teacher->id,
        ]);

        $this->assertDatabaseHas('teaching_journal_attendances', [
            'id'                  => $journalAtt1->id,
            'teaching_journal_id' => $journal->id,
            'siswa_id'            => $this->siswa1->id,
        ]);

        // 8. Historical queries and report relations resolve correctly
        $loadedAssessment = Assessment::with('guru')->find($assessment->id);
        $this->assertNotNull($loadedAssessment);
        $this->assertEquals('Bambang Sudarsono, S.Pd', $loadedAssessment->guru->nama);

        $loadedScore = AssessmentScore::with('assessment.guru')->find($score1->id);
        $this->assertEquals('Bambang Sudarsono, S.Pd', $loadedScore->assessment->guru->nama);

        $loadedJournal = TeachingJournal::with('guru')->find($journal->id);
        $this->assertEquals('Bambang Sudarsono, S.Pd', $loadedJournal->guru->nama);

        // 9. Deactivated teacher is rejected from receiving new subject or homeroom assignments
        $assignmentService = app(TeacherAssignmentService::class);

        $this->expectException(ValidationException::class);
        $assignmentService->assignSubject($this->teacher->id, $this->mapel->id, $this->kelas->id);
    }

    /**
     * Isolated teacher without any academic history can be deleted safely if needed.
     */
    public function test_teacher_without_academic_history_can_be_deleted()
    {
        $rawEmail = 'guru.tanpahistori.' . Str::random(6) . '@myskanic.com';
        $user = User::create([
            'name'                 => 'Guru Baru Tanpa Rekam Jejak',
            'email'                => $rawEmail,
            'password'             => Hash::make('password123'),
            'role'                 => 'guru',
            'status'               => 'active',
            'must_change_password' => false,
            'jenis_kelamin'        => 'L',
        ]);

        $guru = Guru::create([
            'user_id'            => $user->id,
            'nip'                => '19950101' . rand(1000, 9999),
            'nama'               => 'Guru Baru Tanpa Rekam Jejak',
            'mapel'              => $this->mapel->nama_mapel,
            'jenis_kelamin'      => 'L',
            'status_kepegawaian' => 'Honorer',
            'is_active'          => true,
        ]);

        $this->assertFalse($guru->hasAcademicRecords());

        $deleteResponse = $this->actingAs($this->admin)->post(route('guru.delete', $guru->id));
        $deleteResponse->assertRedirect(route('guru'));
        $deleteResponse->assertSessionHas('success');

        $this->assertDatabaseMissing('gurus', ['id' => $guru->id]);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}
