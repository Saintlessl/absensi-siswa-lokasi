<?php

namespace Tests\Feature;

use App\Models\AcademicPromotionBatch;
use App\Models\Angkatan;
use App\Models\Assessment;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\OrangTua;
use App\Models\Siswa;
use App\Models\SiswaFaceBiometric;
use App\Models\StudentEnrollment;
use App\Models\TahunAjaran;
use App\Models\TeacherSubjectAssignment;
use App\Models\TeachingJournal;
use App\Models\User;
use App\Services\StudentAcademicHistoryService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserAcceptanceTestingSuiteTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $kaprogPplgUser;
    protected User $kaprogAnimasiUser;
    protected User $guruUser;
    protected Guru $guru;
    protected User $studentUser1;
    protected Siswa $siswa1;
    protected User $studentUser2;
    protected Siswa $siswa2;
    protected User $parentUser;
    protected OrangTua $orangTua;

    protected TahunAjaran $ta2026;
    protected Jurusan $jurusanPplg;
    protected Jurusan $jurusanAnimasi;
    protected Angkatan $angkatanPplg;
    protected Kelas $kelas10Pplg1;
    protected Kelas $kelas10Animasi1;
    protected MataPelajaran $mapelWeb;

    protected StudentAcademicHistoryService $historyService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->historyService = app(StudentAcademicHistoryService::class);

        // 1. Academic Structure
        $this->ta2026 = TahunAjaran::firstOrCreate(
            ['nama' => '2026/2027'],
            [
                'tahun_mulai' => 2026, 'tahun_selesai' => 2027, 'semester_aktif' => 'ganjil',
                'tanggal_mulai' => '2026-07-15', 'tanggal_selesai' => '2027-06-25', 'is_active' => true, 'status' => 'aktif'
            ]
        );
        $this->ta2026->update(['is_active' => true, 'status' => 'aktif']);

        $this->jurusanPplg = Jurusan::firstOrCreate(['kode_jurusan' => 'PPLG'], ['nama_jurusan' => 'PPLG', 'is_active' => true]);
        $this->jurusanAnimasi = Jurusan::firstOrCreate(['kode_jurusan' => 'ANIMASI'], ['nama_jurusan' => 'Animasi', 'is_active' => true]);

        $this->angkatanPplg = Angkatan::firstOrCreate(
            ['tahun_masuk' => 2026, 'jurusan_id' => $this->jurusanPplg->id],
            ['nomor_angkatan' => 19, 'nama_angkatan' => 'Angkatan 19 PPLG', 'kode_angkatan' => 'AKT-PPLG-19', 'tahun_kelulusan' => 2029, 'status' => 'aktif', 'is_active' => true]
        );

        $this->kelas10Pplg1 = Kelas::firstOrCreate(['nama_kelas' => 'X PPLG 1', 'tahun_ajaran_id' => $this->ta2026->id], [
            'jurusan_id' => $this->jurusanPplg->id, 'tingkat' => 10, 'nomor_rombel' => 1, 'tahun_ajaran' => '2026/2027', 'is_active' => true
        ]);

        $this->kelas10Animasi1 = Kelas::firstOrCreate(['nama_kelas' => 'X ANIMASI 1', 'tahun_ajaran_id' => $this->ta2026->id], [
            'jurusan_id' => $this->jurusanAnimasi->id, 'tingkat' => 10, 'nomor_rombel' => 1, 'tahun_ajaran' => '2026/2027', 'is_active' => true
        ]);

        // 2. Users & Roles
        $this->adminUser = User::factory()->create(['name' => 'Admin UAT', 'role' => 'admin', 'email' => 'admin.uat@myskanic.test']);

        $this->kaprogPplgUser = User::factory()->create([
            'name'       => 'Kaprog PPLG UAT',
            'role'       => 'kaprog',
            'jurusan_id' => $this->jurusanPplg->id,
            'email'      => 'kaprog.pplg@myskanic.test',
        ]);

        $this->kaprogAnimasiUser = User::factory()->create([
            'name'       => 'Kaprog Animasi UAT',
            'role'       => 'kaprog',
            'jurusan_id' => $this->jurusanAnimasi->id,
            'email'      => 'kaprog.animasi@myskanic.test',
        ]);

        $this->guruUser = User::factory()->create(['name' => 'Guru Pengajar UAT', 'role' => 'guru', 'email' => 'guru.uat@myskanic.test']);
        $this->guru = Guru::create(['user_id' => $this->guruUser->id, 'nama' => 'Guru Pengajar UAT', 'nip' => '198501012010011005', 'is_active' => true]);

        // Mapel & Teaching Assignment
        $this->mapelWeb = MataPelajaran::firstOrCreate(['kode_mapel' => 'WEB01'], ['nama_mapel' => 'Pemrograman Web', 'beban_jam' => 4, 'is_active' => true]);
        TeacherSubjectAssignment::create([
            'guru_id'           => $this->guru->id,
            'mata_pelajaran_id' => $this->mapelWeb->id,
            'kelas_id'          => $this->kelas10Pplg1->id,
            'tahun_ajaran_id'   => $this->ta2026->id,
            'is_active'         => true,
            'assigned_at'       => now(),
        ]);

        // 3. Students (2 children for same parent)
        $this->studentUser1 = User::factory()->create(['name' => 'Siswa Anak Pertama', 'role' => 'siswa', 'email' => 'anak1@myskanic.com']);
        $this->siswa1 = Siswa::create([
            'user_id'               => $this->studentUser1->id,
            'nisn'                  => '0091112221',
            'nis'                   => '262710801',
            'nama'                  => 'Siswa Anak Pertama',
            'jenis_kelamin'         => 'L',
            'kelas_id'              => $this->kelas10Pplg1->id,
            'angkatan_id'           => $this->angkatanPplg->id,
            'tahun_ajaran_masuk_id' => $this->ta2026->id,
            'tahun_masuk'           => 2026,
            'status'                => 'aktif',
            'biometric_status'      => 'enrolled',
        ]);
        $this->historyService->recordInitialEnrollment($this->siswa1, $this->kelas10Pplg1, $this->ta2026, $this->ta2026, $this->adminUser);

        // Biometric enrollment record
        $descriptor = array_fill(0, 128, 0.05);
        SiswaFaceBiometric::create([
            'siswa_id'          => $this->siswa1->id,
            'face_descriptor'   => $descriptor,
            'quality_score'     => 98.0,
            'registered_at'     => now(),
            'is_active'         => true,
            'enrollment_status' => 'enrolled',
        ]);

        $this->studentUser2 = User::factory()->create(['name' => 'Siswa Anak Kedua', 'role' => 'siswa', 'email' => 'anak2@myskanic.com']);
        $this->siswa2 = Siswa::create([
            'user_id'               => $this->studentUser2->id,
            'nisn'                  => '0091112222',
            'nis'                   => '262710802',
            'nama'                  => 'Siswa Anak Kedua',
            'jenis_kelamin'         => 'P',
            'kelas_id'              => $this->kelas10Pplg1->id,
            'angkatan_id'           => $this->angkatanPplg->id,
            'tahun_ajaran_masuk_id' => $this->ta2026->id,
            'tahun_masuk'           => 2026,
            'status'                => 'aktif',
            'biometric_status'      => 'enrolled',
        ]);
        $this->historyService->recordInitialEnrollment($this->siswa2, $this->kelas10Pplg1, $this->ta2026, $this->ta2026, $this->adminUser);

        SiswaFaceBiometric::create([
            'siswa_id'          => $this->siswa2->id,
            'face_descriptor'   => $descriptor,
            'quality_score'     => 98.0,
            'registered_at'     => now(),
            'is_active'         => true,
            'enrollment_status' => 'enrolled',
        ]);

        // 4. Parent with both children
        $this->parentUser = User::factory()->create(['name' => 'Bapak Budi Wali', 'role' => 'orangtua', 'email' => 'ortu.budi@myskanic.test']);
        $this->orangTua = OrangTua::create([
            'user_id' => $this->parentUser->id, 'nama' => 'Bapak Budi Wali', 'telepon' => '081234567899',
            'alamat' => 'Perumahan Ciomas No 12', 'siswa_id' => $this->siswa1->id,
        ]);
        $this->orangTua->siswa()->attach([$this->siswa1->id, $this->siswa2->id]);
    }

    /**
     * UAT 1: Admin Operational Journey
     */
    public function test_uat_1_admin_operational_journey(): void
    {
        // 1. Admin login & dashboard
        $response = $this->actingAs($this->adminUser)->get(route('dashboard'));
        $response->assertOk();
        $response->assertSee('Data Siswa');

        // 2. Admin creates academic year
        $createTaRes = $this->actingAs($this->adminUser)->post(route('academic.tahun-ajaran.store'), [
            'nama'            => '2028/2029',
            'tahun_mulai'     => 2028,
            'tahun_selesai'   => 2029,
            'semester_aktif'  => 'ganjil',
            'tanggal_mulai'   => '2028-07-15',
            'tanggal_selesai' => '2029-06-25',
        ]);
        $createTaRes->assertSessionHasNoErrors();
        $this->assertDatabaseHas('tahun_ajarans', ['nama' => '2028/2029']);

        // 3. Admin switches semester
        $switchRes = $this->actingAs($this->adminUser)->post(route('academic.tahun-ajaran.switch-semester', $this->ta2026->id));
        $switchRes->assertSessionHasNoErrors();
        $this->ta2026->refresh();
        $this->assertEquals('genap', $this->ta2026->semester_aktif);

        // 4. Admin views student list & search filter
        $studentListRes = $this->actingAs($this->adminUser)->get(route('siswa', ['search' => 'Pertama']));
        $studentListRes->assertOk();
        $studentListRes->assertSee('Siswa Anak Pertama');

        // 5. Admin accesses promotion wizard & history
        $wizardRes = $this->actingAs($this->adminUser)->get(route('academic.promotion.index'));
        $wizardRes->assertOk();
        $wizardRes->assertSee('Kenaikan Kelas');

        $historyRes = $this->actingAs($this->adminUser)->get(route('academic.promotion.history'));
        $historyRes->assertOk();
    }

    /**
     * UAT 2: Kaprog Scoped Access & Restriction
     */
    public function test_uat_2_kaprog_scoped_access_and_restriction(): void
    {
        // Student in Animasi
        $userAnimasi = User::factory()->create(['role' => 'siswa']);
        $siswaAnimasi = Siswa::create([
            'user_id' => $userAnimasi->id, 'nisn' => '0092223331', 'nis' => '262710899', 'nama' => 'Siswa Animasi Target',
            'jenis_kelamin' => 'P', 'kelas_id' => $this->kelas10Animasi1->id, 'angkatan_id' => $this->angkatanPplg->id,
            'tahun_ajaran_masuk_id' => $this->ta2026->id, 'tahun_masuk' => 2026, 'status' => 'aktif',
        ]);

        // 1. Kaprog PPLG can see PPLG students
        $resPplg = $this->actingAs($this->kaprogPplgUser)->get(route('siswa'));
        $resPplg->assertOk();
        $resPplg->assertSee('Siswa Anak Pertama');

        // 2. Kaprog PPLG cannot see Animasi student
        $resPplg->assertDontSee('Siswa Animasi Target');

        // 3. Kaprog PPLG cannot edit or manage Animasi student (403 Forbidden)
        $forbiddenEdit = $this->actingAs($this->kaprogPplgUser)->post(route('siswa.edit'), ['id' => $siswaAnimasi->id]);
        $forbiddenEdit->assertForbidden();

        // 4. Kaprog PPLG cannot delete Animasi student
        $forbiddenDelete = $this->actingAs($this->kaprogPplgUser)->post(route('siswa.delete', $siswaAnimasi->id));
        $forbiddenDelete->assertForbidden();
    }

    /**
     * UAT 3: Guru Teaching & Grading Workflow
     */
    public function test_uat_3_guru_teaching_and_grading_workflow(): void
    {
        // 1. Guru accesses journal dashboard
        $resJournal = $this->actingAs($this->guruUser)->get(route('jurnal.index'));
        $resJournal->assertOk();

        // 2. Guru creates an assessment for assigned class X PPLG 1
        $category = \App\Models\AssessmentCategory::firstOrCreate(
            ['kode' => 'UH'],
            ['nama' => 'Ulangan Harian', 'bobot_default' => 1.0, 'is_active' => true]
        );

        $assessmentRes = $this->actingAs($this->guruUser)->post(route('gradebook.store'), [
            'kelas_id'               => $this->kelas10Pplg1->id,
            'assessment_category_id' => $category->id,
            'mata_pelajaran_id'      => $this->mapelWeb->id,
            'judul'                  => 'Ulangan Harian HTML & CSS',
            'deskripsi'              => 'Ujian praktik dasar web',
            'tanggal'                => now()->toDateString(),
            'tahun_ajaran'           => '2026/2027',
            'semester'               => 'ganjil',
            'skor_maksimal'          => 100,
            'bobot'                  => 1.0,
            'status'                 => 'draft',
        ]);
        $assessmentRes->assertSessionHasNoErrors();
        $assessment = Assessment::where('judul', 'Ulangan Harian HTML & CSS')->firstOrFail();

        // 3. Guru inputs score for Siswa 1
        $scoreRes = $this->actingAs($this->guruUser)->post(route('gradebook.save', $assessment->id), [
            'action' => 'save',
            'scores' => [
                $this->siswa1->id => [
                    'nilai'   => 88,
                    'catatan' => 'Sangat bagus menguasai CSS grid',
                ]
            ]
        ]);
        $scoreRes->assertSessionHasNoErrors();
        $this->assertDatabaseHas('assessment_scores', [
            'assessment_id' => $assessment->id,
            'siswa_id'      => $this->siswa1->id,
            'nilai'         => 88,
        ]);
    }

    /**
     * UAT 4: Siswa Self-Service & Password Change
     */
    public function test_uat_4_siswa_self_service_and_password_change(): void
    {
        // 1. Siswa logs in & views dashboard
        $resDashboard = $this->actingAs($this->studentUser1)->get(route('dashboard'));
        $resDashboard->assertOk();
        $resDashboard->assertSee('Siswa Anak Pertama');
        $resDashboard->assertSee('X PPLG 1');

        // 2. Siswa views discipline score portal & gradebook
        $resDiscipline = $this->actingAs($this->studentUser1)->get(route('discipline.student.portal'));
        $resDiscipline->assertOk();

        $resGrades = $this->actingAs($this->studentUser1)->get(route('gradebook.student'));
        $resGrades->assertOk();

        // 3. Siswa changes password
        $resPassword = $this->actingAs($this->studentUser1)->post(route('profile.password'), [
            'current_password'      => 'password',
            'password'              => 'NewSiswaPass2026!',
            'password_confirmation' => 'NewSiswaPass2026!',
        ]);
        $resPassword->assertSessionHasNoErrors();

        // Verify password was changed
        $this->studentUser1->refresh();
        $this->assertTrue(Hash::check('NewSiswaPass2026!', $this->studentUser1->password));
    }

    /**
     * UAT 5: Orang Tua Multi-Child Portal & Data Scoping
     */
    public function test_uat_5_orang_tua_multi_child_portal_and_scoping(): void
    {
        // 1. Orang Tua logs in, defaults to first child
        $resParent = $this->actingAs($this->parentUser)->get(route('dashboard'));
        $resParent->assertOk();
        $resParent->assertSee('Siswa Anak Pertama');

        // 2. Orang Tua switches to second child via query parameter
        $resChild2 = $this->actingAs($this->parentUser)->get(route('dashboard', ['siswa_id' => $this->siswa2->id]));
        $resChild2->assertOk();
        $resChild2->assertSee('Siswa Anak Kedua');

        // 3. Orang Tua views gradebook of their child
        $resGrades = $this->actingAs($this->parentUser)->get(route('gradebook.parent', ['siswa_id' => $this->siswa1->id]));
        $resGrades->assertOk();

        // 4. Orang Tua attempts to access another parent's child: safely falls back to own child, zero leakage
        $otherUser = User::factory()->create(['role' => 'siswa']);
        $otherSiswa = Siswa::create([
            'user_id' => $otherUser->id, 'nisn' => '0098887776', 'nis' => '262710991', 'nama' => 'Anak Orang Lain',
            'jenis_kelamin' => 'L', 'kelas_id' => $this->kelas10Pplg1->id, 'angkatan_id' => $this->angkatanPplg->id,
            'tahun_ajaran_masuk_id' => $this->ta2026->id, 'tahun_masuk' => 2026, 'status' => 'aktif',
        ]);

        $resFallback = $this->actingAs($this->parentUser)->get(route('gradebook.parent', ['siswa_id' => $otherSiswa->id]));
        $resFallback->assertOk();
        // Verifies fallback to own child
        $this->assertEquals($this->siswa1->id, $resFallback->viewData('siswa')->id);
        $resFallback->assertDontSee('Anak Orang Lain');
    }

    /**
     * UAT 6: Academic Data Consistency Across Lifecycle States
     */
    public function test_uat_6_academic_data_states_distinction(): void
    {
        // Create an alumnus student (status = lulus)
        $alumniUser = User::factory()->create(['role' => 'siswa']);
        $alumniSiswa = Siswa::create([
            'user_id' => $alumniUser->id, 'nisn' => '0069998881', 'nis' => '232410999', 'nama' => 'Alumni SMK Ciomas',
            'jenis_kelamin' => 'L', 'kelas_id' => $this->kelas10Pplg1->id, 'angkatan_id' => $this->angkatanPplg->id,
            'tahun_ajaran_masuk_id' => $this->ta2026->id, 'tahun_masuk' => 2023, 'status' => 'lulus',
            'tanggal_kelulusan' => '2026-05-10', 'tahun_lulus' => 2026,
        ]);

        // Historical enrollment (closed)
        StudentEnrollment::create([
            'siswa_id' => $alumniSiswa->id, 'tahun_ajaran_id' => $this->ta2026->id, 'tahun_ajaran' => '2026/2027',
            'kelas_id' => $this->kelas10Pplg1->id, 'jurusan_id' => $this->jurusanPplg->id, 'angkatan_id' => $this->angkatanPplg->id,
            'tingkat' => 12, 'is_active' => false, 'status_enrollment' => 'lulus', 'started_at' => '2025-07-15', 'ended_at' => '2026-05-10'
        ]);

        // 1. Alumni has exactly zero active enrollments
        $this->assertEquals(0, $alumniSiswa->enrollments()->where('is_active', true)->count());
        $this->assertNull($alumniSiswa->activeEnrollment);

        // 2. Active student count in dashboard excludes alumni
        $activeCount = Siswa::where('status', 'aktif')->where('kelas_id', $this->kelas10Pplg1->id)->count();
        $this->assertEquals(2, $activeCount); // Only siswa1 & siswa2

        // 3. Alumni profile can be viewed by Admin without errors
        $alumniShowRes = $this->actingAs($this->adminUser)->get(route('siswa.show', $alumniSiswa->id));
        $alumniShowRes->assertOk();
        $alumniShowRes->assertSee('Alumni SMK Ciomas');
        $alumniShowRes->assertSee('lulus');
    }
}
