<?php

namespace Tests\Feature;

use App\Models\Angkatan;
use App\Models\Assessment;
use App\Models\AssessmentCategory;
use App\Models\Guru;
use App\Models\HomeroomAssignment;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\LokasiSekolah;
use App\Models\MataPelajaran;
use App\Models\OrangTua;
use App\Models\Room;
use App\Models\RoomAssignment;
use App\Models\Siswa;
use App\Models\SiswaFaceBiometric;
use App\Models\StudentEnrollment;
use App\Models\TahunAjaran;
use App\Models\TeacherSubjectAssignment;
use App\Models\TeachingJournal;
use App\Models\User;
use App\Models\WhatsAppNotificationLog;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SchoolPilotOperationalJourneyTest extends TestCase
{
    use RefreshDatabase;

    protected TahunAjaran $ta;
    protected Jurusan $jurusanPplg;
    protected Jurusan $jurusanAnimasi;
    protected Angkatan $angkatanPplg;
    protected Kelas $kelasPplg;
    protected Kelas $kelasAnimasi;
    protected Room $roomLab;
    protected User $adminUser;
    protected User $kaprogUser;
    protected User $guruBudiUser;
    protected Guru $guruBudi;
    protected User $guruSitiUser;
    protected Guru $guruSiti;
    protected MataPelajaran $mapelWeb;
    protected MataPelajaran $mapelDb;
    protected User $siswa1User;
    protected Siswa $siswa1;
    protected User $siswa5User;
    protected Siswa $siswa5;
    protected User $ortu2User;
    protected OrangTua $ortu2;
    protected Siswa $siswa2;
    protected Siswa $siswa3;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Geofence
        LokasiSekolah::create([
            'latitude'  => -6.602115,
            'longitude' => 106.760124,
            'radius'    => 100,
        ]);

        // 2. Academic Year
        $this->ta = TahunAjaran::firstOrCreate(['nama' => '2026/2027'], [
            'tahun_mulai'     => 2026,
            'tahun_selesai'   => 2027,
            'semester_aktif'  => 'ganjil',
            'tanggal_mulai'   => '2026-07-15',
            'tanggal_selesai' => '2027-06-25',
            'is_active'       => true,
            'status'          => 'aktif',
        ]);
        $this->ta->update(['is_active' => true, 'status' => 'aktif', 'semester_aktif' => 'ganjil']);

        // 3. Departments
        $this->jurusanPplg = Jurusan::firstOrCreate(['kode_jurusan' => 'PPLG'], [
            'nama_jurusan' => 'Pengembangan Perangkat Lunak dan Gim',
            'is_active'    => true,
        ]);

        $this->jurusanAnimasi = Jurusan::firstOrCreate(['kode_jurusan' => 'ANIMASI'], [
            'nama_jurusan' => 'Animasi 3D',
            'is_active'    => true,
        ]);

        // 4. Cohorts & Classes
        $this->angkatanPplg = Angkatan::firstOrCreate(['tahun_masuk' => 2026, 'jurusan_id' => $this->jurusanPplg->id], [
            'nomor_angkatan'  => 19,
            'nama_angkatan'   => 'Angkatan 19 PPLG',
            'kode_angkatan'   => 'AKT-PPLG-19',
            'tahun_ajaran'    => '2026/2027',
            'tahun_kelulusan' => 2029,
            'status'          => 'aktif',
            'is_active'       => true,
        ]);

        $this->kelasPplg = Kelas::firstOrCreate(['nama_kelas' => 'X PPLG 1', 'tahun_ajaran_id' => $this->ta->id], [
            'tingkat'         => 10,
            'nomor_rombel'    => 1,
            'jurusan_id'      => $this->jurusanPplg->id,
            'angkatan_id'     => $this->angkatanPplg->id,
            'tahun_ajaran'    => '2026/2027',
            'jumlah_siswa'    => 12,
            'is_active'       => true,
        ]);

        $this->kelasAnimasi = Kelas::firstOrCreate(['nama_kelas' => 'X ANIMASI 1', 'tahun_ajaran_id' => $this->ta->id], [
            'tingkat'         => 10,
            'nomor_rombel'    => 1,
            'jurusan_id'      => $this->jurusanAnimasi->id,
            'angkatan_id'     => $this->angkatanPplg->id,
            'tahun_ajaran'    => '2026/2027',
            'jumlah_siswa'    => 10,
            'is_active'       => true,
        ]);

        // 5. Room
        $this->roomLab = Room::firstOrCreate(['code' => 'LAB-PPLG-01'], [
            'name'         => 'Laboratorium Komputer PPLG 1',
            'building'     => 'Gedung B',
            'capacity'     => 36,
            'type'         => 'lab',
            'qr_token'     => 'QR-LAB-PPLG-1-PERM-2026',
            'qr_is_active' => true,
            'is_active'    => true,
        ]);

        RoomAssignment::firstOrCreate([
            'room_id'      => $this->roomLab->id,
            'kelas_id'     => $this->kelasPplg->id,
            'tahun_ajaran' => '2026/2027',
        ], [
            'day_of_week' => 'all',
            'start_time'  => '07:00:00',
            'end_time'    => '16:00:00',
            'starts_on'   => '2026-07-15',
            'ends_on'     => '2027-06-25',
            'status'      => 'active',
        ]);

        // 6. Users: Admin & Kaprog
        $this->adminUser = User::create([
            'name'                 => 'Administrator Pilot',
            'email'                => 'admin.pilot@myskanic.test',
            'role'                 => 'admin',
            'password'             => Hash::make('PilotAdmin2026!'),
            'must_change_password' => false,
        ]);

        $this->kaprogUser = User::create([
            'name'                 => 'Ahmad Fauzi, M.Kom.',
            'email'                => 'kaprog.pplg@pilot.myskanic.test',
            'role'                 => 'kaprog',
            'jurusan_id'           => $this->jurusanPplg->id,
            'password'             => Hash::make('Kaprog2026!'),
            'must_change_password' => false,
        ]);

        // 7. Teachers
        $this->guruBudiUser = User::create([
            'name'                 => 'Budi Hermawan, S.Kom.',
            'email'                => 'guru.budi@pilot.myskanic.test',
            'role'                 => 'guru',
            'password'             => Hash::make('GuruBudi2026!'),
            'must_change_password' => false,
        ]);
        $this->guruBudi = Guru::create([
            'user_id'       => $this->guruBudiUser->id,
            'nama'          => 'Budi Hermawan, S.Kom.',
            'nip'           => '198501102010011011',
            'jenis_kelamin' => 'L',
            'telepon'       => '081288880001',
            'is_active'     => true,
        ]);

        $this->guruSitiUser = User::create([
            'name'                 => 'Siti Rahmah, M.Kom.',
            'email'                => 'guru.siti@pilot.myskanic.test',
            'role'                 => 'guru',
            'password'             => Hash::make('GuruSiti2026!'),
            'must_change_password' => false,
        ]);
        $this->guruSiti = Guru::create([
            'user_id'       => $this->guruSitiUser->id,
            'nama'          => 'Siti Rahmah, M.Kom.',
            'nip'           => '198805202012022022',
            'jenis_kelamin' => 'P',
            'telepon'       => '081288880002',
            'is_active'     => true,
        ]);

        // 8. Homeroom Assignment
        $homeroom = HomeroomAssignment::create([
            'guru_id'         => $this->guruBudi->id,
            'kelas_id'        => $this->kelasPplg->id,
            'tahun_ajaran_id' => $this->ta->id,
            'tahun_ajaran'    => '2026/2027',
            'starts_on'       => '2026-07-15',
            'is_active'       => true,
        ]);

        // 9. Subject Assignments
        $this->mapelWeb = MataPelajaran::firstOrCreate(['kode_mapel' => 'PPLG-WEB10'], [
            'nama_mapel' => 'Pemrograman Web Dasar',
            'kelompok'   => 'kejuruan',
            'is_active'  => true,
        ]);
        TeacherSubjectAssignment::create([
            'guru_id'           => $this->guruBudi->id,
            'mata_pelajaran_id' => $this->mapelWeb->id,
            'kelas_id'          => $this->kelasPplg->id,
            'tahun_ajaran'      => '2026/2027',
            'starts_on'         => '2026-07-15',
            'is_active'         => true,
        ]);

        // 10. Student 1 (New Student, Must Change Password)
        $this->siswa1User = User::create([
            'name'                 => 'Aditya Pratama',
            'email'                => '262710001@myskanic.com',
            'role'                 => 'siswa',
            'jurusan_id'           => $this->jurusanPplg->id,
            'password'             => Hash::make('Siswa@262710001'),
            'must_change_password' => true,
        ]);
        $this->siswa1 = Siswa::create([
            'user_id'               => $this->siswa1User->id,
            'nis'                   => '262710001',
            'nisn'                  => '0089100001',
            'nama'                  => 'Aditya Pratama',
            'jenis_kelamin'         => 'L',
            'kelas_id'              => $this->kelasPplg->id,
            'angkatan_id'           => $this->angkatanPplg->id,
            'tahun_masuk'           => 2026,
            'tahun_ajaran_masuk_id' => $this->ta->id,
            'tahun_ajaran_masuk'    => '2026/2027',
            'status'                => 'aktif',
            'biometric_status'      => 'pending',
        ]);
        StudentEnrollment::create([
            'siswa_id'                  => $this->siswa1->id,
            'tahun_ajaran_id'           => $this->ta->id,
            'tahun_ajaran'              => '2026/2027',
            'kelas_id'                  => $this->kelasPplg->id,
            'jurusan_id'                => $this->jurusanPplg->id,
            'angkatan_id'               => $this->angkatanPplg->id,
            'tingkat'                   => 10,
            'is_active'                 => true,
            'status_enrollment'         => 'aktif',
            'homeroom_assignment_id'    => $homeroom->id,
            'wali_kelas_guru_id'        => $this->guruBudi->id,
            'wali_kelas_nama'           => $this->guruBudi->nama,
            'status_verifikasi_histori' => 'terverifikasi',
        ]);

        // 11. Student 5 (Enrolled Student, Can take attendance immediately)
        $this->siswa5User = User::create([
            'name'                 => 'Eko Prasetyo',
            'email'                => '262710005@myskanic.com',
            'role'                 => 'siswa',
            'jurusan_id'           => $this->jurusanPplg->id,
            'password'             => Hash::make('Siswa@262710005'),
            'must_change_password' => false,
        ]);
        $this->siswa5 = Siswa::create([
            'user_id'               => $this->siswa5User->id,
            'nis'                   => '262710005',
            'nisn'                  => '0089100005',
            'nama'                  => 'Eko Prasetyo',
            'jenis_kelamin'         => 'L',
            'kelas_id'              => $this->kelasPplg->id,
            'angkatan_id'           => $this->angkatanPplg->id,
            'tahun_masuk'           => 2026,
            'tahun_ajaran_masuk_id' => $this->ta->id,
            'tahun_ajaran_masuk'    => '2026/2027',
            'status'                => 'aktif',
            'biometric_status'      => 'enrolled',
        ]);
        StudentEnrollment::create([
            'siswa_id'                  => $this->siswa5->id,
            'tahun_ajaran_id'           => $this->ta->id,
            'tahun_ajaran'              => '2026/2027',
            'kelas_id'                  => $this->kelasPplg->id,
            'jurusan_id'                => $this->jurusanPplg->id,
            'angkatan_id'               => $this->angkatanPplg->id,
            'tingkat'                   => 10,
            'is_active'                 => true,
            'status_enrollment'         => 'aktif',
            'homeroom_assignment_id'    => $homeroom->id,
            'wali_kelas_guru_id'        => $this->guruBudi->id,
            'wali_kelas_nama'           => $this->guruBudi->nama,
            'status_verifikasi_histori' => 'terverifikasi',
        ]);
        SiswaFaceBiometric::create([
            'siswa_id'          => $this->siswa5->id,
            'face_descriptor'   => json_encode(array_fill(0, 128, 0.05)),
            'sample_photo'      => 'biometrics/pilot_262710005.jpg',
            'enrollment_status' => 'enrolled',
            'registered_at'     => now(),
            'enrolled_at'       => now(),
            'is_active'         => true,
            'quality_score'     => 98.5,
        ]);

        // 12. Multi-child Parent (Bayu Saputra & Citra Melani)
        $userSiswa2 = User::create(['name' => 'Bayu Saputra', 'email' => '262710002@myskanic.com', 'role' => 'siswa', 'password' => Hash::make('password123')]);
        $this->siswa2 = Siswa::create(['user_id' => $userSiswa2->id, 'nis' => '262710002', 'nisn' => '0089100002', 'nama' => 'Bayu Saputra', 'jenis_kelamin' => 'L', 'kelas_id' => $this->kelasPplg->id, 'angkatan_id' => $this->angkatanPplg->id, 'tahun_masuk' => 2026, 'tahun_ajaran_masuk_id' => $this->ta->id, 'status' => 'aktif']);

        $userSiswa3 = User::create(['name' => 'Citra Melani', 'email' => '262710003@myskanic.com', 'role' => 'siswa', 'password' => Hash::make('password123')]);
        $this->siswa3 = Siswa::create(['user_id' => $userSiswa3->id, 'nis' => '262710003', 'nisn' => '0089100003', 'nama' => 'Citra Melani', 'jenis_kelamin' => 'P', 'kelas_id' => $this->kelasPplg->id, 'angkatan_id' => $this->angkatanPplg->id, 'tahun_masuk' => 2026, 'tahun_ajaran_masuk_id' => $this->ta->id, 'status' => 'aktif']);

        $this->ortu2User = User::create([
            'name'                 => 'Maryani Susanti',
            'email'                => 'ortu.maryani@pilot.myskanic.test',
            'role'                 => 'orangtua',
            'password'             => Hash::make('OrtuMaryani2026!'),
            'must_change_password' => false,
        ]);
        $this->ortu2 = OrangTua::create([
            'user_id'       => $this->ortu2User->id,
            'nama'          => 'Maryani Susanti',
            'telepon'       => '081233334444',
            'alamat'        => 'Perum Bukit Asri Ciomas Blok C3',
            'jenis_kelamin' => 'P',
            'siswa_id'      => $this->siswa2->id,
        ]);
        $this->ortu2->siswa()->sync([$this->siswa2->id, $this->siswa3->id]);
    }

    public function test_admin_pilot_can_monitor_and_manage_academics(): void
    {
        $res = $this->actingAs($this->adminUser)->get(route('dashboard'));
        $res->assertOk();
        $res->assertSee('Data Siswa');

        $kelasRes = $this->actingAs($this->adminUser)->get(route('kelas'));
        $kelasRes->assertOk();
        $kelasRes->assertSee('X PPLG 1');

        $waliRes = $this->actingAs($this->adminUser)->get(route('academic.wali-kelas.index'));
        $waliRes->assertOk();
        $waliRes->assertSee('Budi Hermawan, S.Kom.');
    }

    public function test_kaprog_scoped_to_pplg_and_blocked_from_other_departments(): void
    {
        $res = $this->actingAs($this->kaprogUser)->get(route('siswa'));
        $res->assertOk();
        $res->assertSee('Aditya Pratama');
        $res->assertSee('Eko Prasetyo');

        // Student in Animasi
        $animasiStudentUser = User::create(['name' => 'Siswa Animasi Target', 'email' => 'animasi@myskanic.com', 'role' => 'siswa', 'password' => Hash::make('password123')]);
        $animasiStudent = Siswa::create(['user_id' => $animasiStudentUser->id, 'nis' => '262710099', 'nisn' => '0089100099', 'nama' => 'Siswa Animasi Target', 'jenis_kelamin' => 'L', 'kelas_id' => $this->kelasAnimasi->id, 'angkatan_id' => $this->angkatanPplg->id, 'tahun_masuk' => 2026, 'tahun_ajaran_masuk_id' => $this->ta->id, 'status' => 'aktif']);

        $res->assertDontSee('Siswa Animasi Target');

        // Kaprog cannot delete or edit Animasi student
        $forbiddenEdit = $this->actingAs($this->kaprogUser)->post(route('siswa.edit'), ['id' => $animasiStudent->id]);
        $forbiddenEdit->assertForbidden();
    }

    public function test_guru_can_record_journal_and_input_gradebook_scores(): void
    {
        // 1. Guru accesses gradebook
        $category = AssessmentCategory::firstOrCreate(['kode' => 'UH'], ['nama' => 'Ulangan Harian', 'bobot_default' => 1.0, 'is_active' => true]);

        $resAssessment = $this->actingAs($this->guruBudiUser)->post(route('gradebook.store'), [
            'kelas_id'               => $this->kelasPplg->id,
            'assessment_category_id' => $category->id,
            'mata_pelajaran_id'      => $this->mapelWeb->id,
            'judul'                  => 'Penilaian Praktik HTML & Tailwind',
            'deskripsi'              => 'Ujian praktik mandiri membuat layout dashboard',
            'tanggal'                => now()->toDateString(),
            'tahun_ajaran'           => '2026/2027',
            'semester'               => 'ganjil',
            'skor_maksimal'          => 100,
            'bobot'                  => 1.0,
            'status'                 => 'draft',
        ]);
        $resAssessment->assertSessionHasNoErrors();

        $assessment = Assessment::where('judul', 'Penilaian Praktik HTML & Tailwind')->firstOrFail();

        // 2. Guru saves score for Siswa 5
        $scoreRes = $this->actingAs($this->guruBudiUser)->post(route('gradebook.save', $assessment->id), [
            'action' => 'save',
            'scores' => [
                $this->siswa5->id => [
                    'nilai'   => 92,
                    'catatan' => 'Sangat rapi dan responsif',
                ]
            ]
        ]);
        $scoreRes->assertSessionHasNoErrors();

        $this->assertDatabaseHas('assessment_scores', [
            'assessment_id' => $assessment->id,
            'siswa_id'      => $this->siswa5->id,
            'nilai'         => 92,
        ]);
    }

    public function test_siswa_must_change_password_on_first_login_before_accessing_dashboard(): void
    {
        // 1. Initial attempt to visit dashboard gets intercepted
        $blocked = $this->actingAs($this->siswa1User)->get(route('dashboard'));
        $blocked->assertRedirect(route('profile'));
        $blocked->assertSessionHas('warning');

        // 2. User changes password
        $res = $this->actingAs($this->siswa1User)->post(route('profile.password'), [
            'current_password'      => 'Siswa@262710001',
            'password'              => 'AdityaBaru2026!',
            'password_confirmation' => 'AdityaBaru2026!',
        ]);
        $res->assertSessionHas('success');

        $this->siswa1User->refresh();
        $this->assertFalse((bool) $this->siswa1User->must_change_password);

        // 3. User is now redirected to biometric enrollment because face is not yet registered
        $next = $this->actingAs($this->siswa1User)->get(route('dashboard'));
        $next->assertRedirect(route('siswa.enrollment'));
    }

    public function test_enrolled_siswa_can_view_grades_and_dashboard(): void
    {
        $res = $this->actingAs($this->siswa5User)->get(route('dashboard'));
        $res->assertOk();
        $res->assertSee('Eko Prasetyo');

        $gradesRes = $this->actingAs($this->siswa5User)->get(route('gradebook.student'));
        $gradesRes->assertOk();
    }

    public function test_parent_can_switch_between_children_and_zero_data_leakage(): void
    {
        // 1. Parent views dashboard for child 1 (Bayu)
        $res = $this->actingAs($this->ortu2User)->get(route('dashboard'));
        $res->assertOk();
        $res->assertSee('Bayu Saputra');

        // 2. Parent switches to child 2 (Citra)
        $resChild2 = $this->actingAs($this->ortu2User)->get(route('dashboard', ['siswa_id' => $this->siswa3->id]));
        $resChild2->assertOk();
        $resChild2->assertSee('Citra Melani');

        // 3. Parent queries child from another family (Siswa 5) -> falls back to own children, zero leakage
        $resFallback = $this->actingAs($this->ortu2User)->get(route('gradebook.parent', ['siswa_id' => $this->siswa5->id]));
        $resFallback->assertOk();
        $this->assertEquals($this->siswa2->id, $resFallback->viewData('siswa')->id);
        $resFallback->assertDontSee('Eko Prasetyo');
    }
}
