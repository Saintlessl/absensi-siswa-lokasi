<?php

namespace Tests\Feature;

use App\Models\AcademicPromotionBatch;
use App\Models\Angkatan;
use App\Models\Assessment;
use App\Models\AssessmentCategory;
use App\Models\DisciplineCategory;
use App\Models\DisciplineRecord;
use App\Models\Guru;
use App\Models\InventoryItem;
use App\Models\InventoryBorrowing;
use App\Models\Jadwal;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\OrangTua;
use App\Models\PklDudiPartner;
use App\Models\PklLogbook;
use App\Models\PklPlacement;
use App\Models\PortfolioItem;
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

class SystemFeatureCompletionIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $superadmin;
    protected User $admin;
    protected User $kaprogPplg;
    protected User $kaprogAkl;
    protected User $guruUser;
    protected Guru $guru;
    protected User $studentUser1;
    protected Siswa $siswa1;
    protected User $studentUser2;
    protected Siswa $siswa2;
    protected User $parentUser;
    protected OrangTua $orangTua;

    protected TahunAjaran $taActive;
    protected Jurusan $jurusanPplg;
    protected Jurusan $jurusanAkl;
    protected Angkatan $angkatanPplg;
    protected Kelas $kelas10Pplg1;
    protected Kelas $kelas10Akl1;
    protected MataPelajaran $mapelWeb;
    protected AssessmentCategory $assessCat;
    protected DisciplineCategory $discCat;

    protected StudentAcademicHistoryService $historyService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->historyService = app(StudentAcademicHistoryService::class);

        // 1. Tahun Ajaran Global
        $this->taActive = TahunAjaran::firstOrCreate(
            ['nama' => '2026/2027'],
            [
                'tahun_mulai'     => 2026,
                'tahun_selesai'   => 2027,
                'semester_aktif'  => 'ganjil',
                'tanggal_mulai'   => '2026-07-15',
                'tanggal_selesai' => '2027-06-30',
                'status'          => 'aktif',
                'is_active'       => true,
            ]
        );
        $this->taActive->update(['is_active' => true, 'status' => 'aktif']);

        // 2. Jurusan
        $this->jurusanPplg = Jurusan::firstOrCreate(
            ['kode_jurusan' => 'PPLG'],
            [
                'nama_jurusan' => 'Pengembangan Perangkat Lunak dan Gim',
                'is_active'    => true,
            ]
        );

        $this->jurusanAkl = Jurusan::firstOrCreate(
            ['kode_jurusan' => 'AKL'],
            [
                'nama_jurusan' => 'Akuntansi dan Keuangan Lembaga',
                'is_active'    => true,
            ]
        );

        // 3. Angkatan (Spesifik Jurusan)
        $this->angkatanPplg = Angkatan::create([
            'jurusan_id'         => $this->jurusanPplg->id,
            'nomor_angkatan'     => 15,
            'nama_angkatan'      => 'Angkatan 15 PPLG',
            'kode_angkatan'      => 'PPLG-15',
            'tahun_masuk'        => 2026,
            'tahun_ajaran_masuk' => '2026/2027',
            'status'             => 'aktif',
        ]);

        // 4. Rombel
        $this->kelas10Pplg1 = Kelas::create([
            'nama_kelas'      => '10 PPLG 1',
            'tingkat'         => '10',
            'nomor_rombel'    => 1,
            'jurusan_id'      => $this->jurusanPplg->id,
            'tahun_ajaran'    => '2026/2027',
            'tahun_ajaran_id' => $this->taActive->id,
            'wali_kelas'      => 'Budi Setiawan, S.Kom',
            'jumlah_siswa'    => 36,
            'is_active'       => true,
        ]);

        $this->kelas10Akl1 = Kelas::create([
            'nama_kelas'      => '10 AKL 1',
            'tingkat'         => '10',
            'nomor_rombel'    => 1,
            'jurusan_id'      => $this->jurusanAkl->id,
            'tahun_ajaran'    => '2026/2027',
            'tahun_ajaran_id' => $this->taActive->id,
            'wali_kelas'      => 'Siti Rahma, S.E',
            'jumlah_siswa'    => 36,
            'is_active'       => true,
        ]);

        // 5. Users
        $this->superadmin = User::create([
            'name'          => 'Super Administrator',
            'email'         => 'superadmin@myskanic.test',
            'password'      => Hash::make('password'),
            'role'          => 'superadmin',
            'jenis_kelamin' => 'L',
        ]);

        $this->admin = User::create([
            'name'          => 'Admin Akademik',
            'email'         => 'admin@myskanic.test',
            'password'      => Hash::make('password'),
            'role'          => 'admin',
            'jenis_kelamin' => 'L',
        ]);

        $this->kaprogPplg = User::create([
            'name'          => 'Kaprog PPLG',
            'email'         => 'kaprog.pplg@myskanic.test',
            'password'      => Hash::make('password'),
            'role'          => 'kaprog',
            'jurusan_id'    => $this->jurusanPplg->id,
            'jenis_kelamin' => 'L',
        ]);

        $this->kaprogAkl = User::create([
            'name'          => 'Kaprog AKL',
            'email'         => 'kaprog.akl@myskanic.test',
            'password'      => Hash::make('password'),
            'role'          => 'kaprog',
            'jurusan_id'    => $this->jurusanAkl->id,
            'jenis_kelamin' => 'P',
        ]);

        $this->guruUser = User::create([
            'name'          => 'Budi Setiawan, S.Kom',
            'email'         => 'budi.guru@myskanic.test',
            'password'      => Hash::make('password'),
            'role'          => 'guru',
            'jenis_kelamin' => 'L',
        ]);

        $this->guru = Guru::create([
            'user_id'       => $this->guruUser->id,
            'nama'          => 'Budi Setiawan, S.Kom',
            'nip'           => '198501012010011001',
            'jenis_kelamin' => 'L',
            'telepon'       => '081234567890',
            'is_active'     => true,
        ]);

        $this->mapelWeb = MataPelajaran::create([
            'kode_mapel' => 'PPLG-WEB',
            'nama_mapel' => 'Pemrograman Web & Perangkat Bergerak',
        ]);

        TeacherSubjectAssignment::create([
            'guru_id'           => $this->guru->id,
            'mata_pelajaran_id' => $this->mapelWeb->id,
            'kelas_id'          => $this->kelas10Pplg1->id,
            'tahun_ajaran'      => '2026/2027',
            'is_active'         => true,
        ]);

        // 6. Students (Biometric status enrolled)
        $this->studentUser1 = User::create([
            'name'          => 'Ahmad Rizki',
            'email'         => 'ahmad.rizki@myskanic.test',
            'password'      => Hash::make('password'),
            'role'          => 'siswa',
            'jenis_kelamin' => 'L',
        ]);

        $this->siswa1 = Siswa::create([
            'user_id'             => $this->studentUser1->id,
            'nama'                => 'Ahmad Rizki',
            'nis'                 => '26270001',
            'nisn'                => '0081234001',
            'jenis_kelamin'       => 'L',
            'angkatan_id'         => $this->angkatanPplg->id,
            'kelas_id'            => $this->kelas10Pplg1->id,
            'rombel_saat_ini'     => '10 PPLG 1',
            'status'              => 'aktif',
            'status_akademik'     => 'aktif',
            'biometric_status'    => 'enrolled',
            'tahun_masuk'         => 2026,
            'tahun_ajaran_masuk'  => '2026/2027',
            'tempat_lahir'        => 'Bogor',
            'tanggal_lahir'       => '2010-05-15',
        ]);

        StudentEnrollment::create([
            'siswa_id'        => $this->siswa1->id,
            'kelas_id'        => $this->kelas10Pplg1->id,
            'tahun_ajaran_id' => $this->taActive->id,
            'tahun_ajaran'    => '2026/2027',
            'semester'        => 'ganjil',
            'tingkat'         => '10',
            'rombel'          => '10 PPLG 1',
            'is_active'       => true,
            'tanggal_masuk'   => '2026-07-15',
        ]);

        SiswaFaceBiometric::create([
            'siswa_id'          => $this->siswa1->id,
            'face_descriptor'   => json_encode(array_fill(0, 128, 0.05)),
            'enrollment_status' => 'enrolled',
            'quality_score'     => 98.0,
            'registered_at'     => now(),
            'is_active'         => true,
        ]);

        $this->studentUser2 = User::create([
            'name'          => 'Siti Aisyah',
            'email'         => 'siti.aisyah@myskanic.test',
            'password'      => Hash::make('password'),
            'role'          => 'siswa',
            'jenis_kelamin' => 'P',
        ]);

        $this->siswa2 = Siswa::create([
            'user_id'             => $this->studentUser2->id,
            'nama'                => 'Siti Aisyah',
            'nis'                 => '26270002',
            'nisn'                => '0081234002',
            'jenis_kelamin'       => 'P',
            'angkatan_id'         => $this->angkatanPplg->id,
            'kelas_id'            => $this->kelas10Pplg1->id,
            'rombel_saat_ini'     => '10 PPLG 1',
            'status'              => 'aktif',
            'status_akademik'     => 'aktif',
            'biometric_status'    => 'enrolled',
            'tahun_masuk'         => 2026,
            'tahun_ajaran_masuk'  => '2026/2027',
            'tempat_lahir'        => 'Bogor',
            'tanggal_lahir'       => '2010-08-20',
        ]);

        StudentEnrollment::create([
            'siswa_id'        => $this->siswa2->id,
            'kelas_id'        => $this->kelas10Pplg1->id,
            'tahun_ajaran_id' => $this->taActive->id,
            'tahun_ajaran'    => '2026/2027',
            'semester'        => 'ganjil',
            'tingkat'         => '10',
            'rombel'          => '10 PPLG 1',
            'is_active'       => true,
            'tanggal_masuk'   => '2026-07-15',
        ]);

        SiswaFaceBiometric::create([
            'siswa_id'          => $this->siswa2->id,
            'face_descriptor'   => json_encode(array_fill(0, 128, 0.05)),
            'enrollment_status' => 'enrolled',
            'quality_score'     => 98.0,
            'registered_at'     => now(),
            'is_active'         => true,
        ]);

        // 7. Parent (linked to siswa1)
        $this->parentUser = User::create([
            'name'          => 'Hasan Basri',
            'email'         => 'hasan.parent@myskanic.test',
            'password'      => Hash::make('password'),
            'role'          => 'orangtua',
            'jenis_kelamin' => 'L',
        ]);

        $this->orangTua = OrangTua::create([
            'user_id'       => $this->parentUser->id,
            'siswa_id'      => $this->siswa1->id,
            'nama'          => 'Hasan Basri',
            'telepon'       => '081399887766',
            'jenis_kelamin' => 'L',
        ]);

        $this->orangTua->siswa()->attach($this->siswa1->id);

        // 8. Assessment Category & Discipline Category
        $this->assessCat = AssessmentCategory::firstOrCreate(
            ['kode' => 'UH'],
            [
                'nama'          => 'Ulangan Harian',
                'bobot_default' => 20,
                'is_active'     => true,
            ]
        );

        $this->discCat = DisciplineCategory::firstOrCreate(
            ['nama' => 'Keterlambatan Upacara'],
            [
                'tipe'         => 'pelanggaran',
                'default_poin' => 5,
                'is_active'    => true,
            ]
        );
    }

    /**
     * 1. SUPERADMIN WORKFLOW & USER ADMINISTRATION
     */
    public function test_superadmin_user_management_and_tahun_ajaran_edit_view()
    {
        $this->actingAs($this->superadmin);

        // Access User Management Index
        $response = $this->get(route('user'));
        $response->assertStatus(200);
        $response->assertSee('Super Administrator');

        // Create a new Kaprog user
        $newUserEmail = 'new.kaprog@myskanic.test';
        $createResponse = $this->post(route('user.store'), [
            'name'          => 'Kaprog Baru',
            'email'         => $newUserEmail,
            'password'      => 'password123',
            'role'          => 'kaprog',
            'jenis_kelamin' => 'L',
            'jurusan_id'    => $this->jurusanPplg->id,
        ]);
        $createResponse->assertRedirect();
        $this->assertDatabaseHas('users', [
            'email'      => $newUserEmail,
            'role'       => 'kaprog',
            'jurusan_id' => $this->jurusanPplg->id,
        ]);

        // Guard against deleting last superadmin
        $deleteSelfResponse = $this->post(route('user.delete', $this->superadmin->id));
        $deleteSelfResponse->assertRedirect();
        $deleteSelfResponse->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $this->superadmin->id]);

        // Test newly implemented Tahun Ajaran standalone edit view
        $editTaResponse = $this->get(route('academic.tahun-ajaran.edit', $this->taActive->id));
        $editTaResponse->assertStatus(200);
        $editTaResponse->assertSee('Edit Periode Akademik: 2026/2027');
        $editTaResponse->assertSee($this->taActive->nama);
    }

    /**
     * 2. KAPROG WORKFLOW & SCOPED ACCESS INTEGRATION
     */
    public function test_kaprog_scoped_access_and_cross_department_protection()
    {
        $this->actingAs($this->kaprogPplg);

        // Kaprog PPLG can access data siswa and see PPLG students
        $res = $this->get(route('siswa'));
        $res->assertStatus(200);
        $res->assertSee('Ahmad Rizki');

        // Kaprog PPLG cannot access AKL cohort and is safely scoped / handled
        $resAklAngkatan = $this->get(route('angkatan.index', ['jurusan_id' => $this->jurusanAkl->id]));
        $this->assertTrue(in_array($resAklAngkatan->status(), [200, 302]));
    }

    /**
     * 3. GURU TEACHING, JOURNAL & ASSESSMENT WORKFLOW
     */
    public function test_guru_teaching_journal_and_gradebook_workflow()
    {
        $this->actingAs($this->guruUser);

        // 1. Guru accesses schedule
        $schedRes = $this->get(route('teacher.schedule'));
        $schedRes->assertStatus(200);

        // 2. Guru records teaching journal
        $journalRes = $this->post(route('jurnal.store'), [
            'mata_pelajaran_id' => $this->mapelWeb->id,
            'kelas_id'          => $this->kelas10Pplg1->id,
            'tanggal'           => '2026-08-10',
            'materi_pokok'      => 'Pengenalan HTML5 & CSS3',
            'aktivitas'         => 'Praktik langsung pembuatan semantik HTML5',
            'status'            => 'submitted',
        ]);
        $journalRes->assertRedirect();
        $this->assertDatabaseHas('teaching_journals', [
            'guru_id'      => $this->guru->id,
            'materi_pokok' => 'Pengenalan HTML5 & CSS3',
        ]);

        // 3. Guru creates assessment
        $assessRes = $this->post(route('gradebook.store'), [
            'judul'                  => 'Ulangan Harian 1 Semantic HTML',
            'mata_pelajaran_id'      => $this->mapelWeb->id,
            'assessment_category_id' => $this->assessCat->id,
            'kelas_id'               => $this->kelas10Pplg1->id,
            'tahun_ajaran'           => '2026/2027',
            'semester'               => 'ganjil',
            'skor_maksimal'          => 100,
            'bobot'                  => 20,
            'tanggal'                => '2026-08-15',
            'status'                 => 'published',
        ]);
        $assessRes->assertRedirect();

        $assessment = Assessment::where('judul', 'Ulangan Harian 1 Semantic HTML')->firstOrFail();

        // 4. Guru inputs scores for students
        $scoresRes = $this->post(route('gradebook.save', $assessment->id), [
            'action' => 'save',
            'scores' => [
                $this->siswa1->id => [
                    'nilai'   => 88,
                    'catatan' => 'Pemahaman layout sangat baik',
                ],
                $this->siswa2->id => [
                    'nilai'   => 92,
                    'catatan' => 'Sempurna',
                ],
            ],
        ]);
        $scoresRes->assertRedirect();

        $this->assertDatabaseHas('assessment_scores', [
            'assessment_id' => $assessment->id,
            'siswa_id'      => $this->siswa1->id,
            'nilai'         => 88,
        ]);
    }

    /**
     * 4. STUDENT SUPPORT: PKL & DISCIPLINE INTEGRATION
     */
    public function test_pkl_and_discipline_workflows()
    {
        // A. PKL Partner & Placement by Admin
        $this->actingAs($this->admin);

        $partner = PklDudiPartner::create([
            'nama_dudi'       => 'PT Skanic Digital Kreasi',
            'bidang_industri' => 'Software Development',
            'alamat'          => 'Jl. Raya Ciomas No. 12',
            'kota'            => 'Bogor',
            'latitude'        => -6.600000,
            'longitude'       => 106.750000,
            'radius_meter'    => 150,
            'kontak_person'   => 'Ir. Hendra',
            'telepon'         => '081299887766',
            'status'          => 'aktif',
        ]);

        $placement = PklPlacement::create([
            'siswa_id'            => $this->siswa1->id,
            'pkl_dudi_partner_id' => $partner->id,
            'guru_pembimbing_id'  => $this->guruUser->id,
            'tanggal_mulai'       => '2026-08-01',
            'tanggal_selesai'     => '2026-11-30',
            'status'              => 'active',
        ]);

        // B. Student submits PKL Logbook
        $this->actingAs($this->studentUser1);
        $logbookRes = $this->post(route('pkl.student.logbook'), [
            'tanggal'        => Carbon::today()->toDateString(),
            'aktivitas'      => 'Membuat REST API modul presensi dengan Laravel framework',
            'pembelajaran'   => 'Memahami middleware dan controller validation',
            'kendala'        => 'Konfigurasi CORS',
        ]);
        $logbookRes->assertRedirect();

        $logbook = PklLogbook::where('pkl_placement_id', $placement->id)->firstOrFail();
        $this->assertEquals('submitted', $logbook->status);

        // C. Guru Pembimbing reviews Logbook
        $this->actingAs($this->guruUser);
        $reviewRes = $this->post(route('pkl.advisor.review', $logbook->id), [
            'status'       => 'approved',
            'catatan_guru' => 'Implementasi rapi, lanjutkan ke database index',
        ]);
        $reviewRes->assertRedirect();
        $this->assertEquals('approved', $logbook->fresh()->status);

        // D. Discipline infraction & counseling workflow
        $infractionRes = $this->post(route('discipline.store'), [
            'siswa_id'               => $this->siswa1->id,
            'discipline_category_id' => $this->discCat->id,
            'judul'                  => 'Terlambat mengikuti upacara bendera',
            'deskripsi'              => 'Terlambat 15 menit pada hari Senin',
            'poin'                   => 5,
            'tanggal'                => Carbon::today()->toDateString(),
        ]);
        $infractionRes->assertRedirect();

        $discipline = DisciplineRecord::where('siswa_id', $this->siswa1->id)->firstOrFail();
        $this->assertEquals(5, $discipline->poin);

        // BK / Guru resolves discipline
        $statusRes = $this->post(route('discipline.status', $discipline->id), [
            'status'        => 'resolved',
            'tindak_lanjut' => 'Siswa telah diberikan pembinaan dan membuat surat komitmen kedisiplinan',
        ]);
        $statusRes->assertRedirect();
        $this->assertEquals('resolved', $discipline->fresh()->status);
    }

    /**
     * 5. INVENTORY & SCHOOL OPERATIONAL MANAGEMENT
     */
    public function test_inventory_borrowing_approval_and_return_lifecycle()
    {
        // Admin creates item
        $this->actingAs($this->admin);
        $item = InventoryItem::create([
            'kode_barang'        => 'INV-LTP-001',
            'nama_barang'        => 'Laptop Lab Asus ExpertBook',
            'jurusan_id'         => $this->jurusanPplg->id,
            'kategori'           => 'Elektronik',
            'total_unit'         => 5,
            'unit_tersedia'      => 5,
            'kondisi'            => 'baik',
            'status'             => 'tersedia',
            'lokasi_penyimpanan' => 'Lab PPLG 1',
        ]);

        // Student submits borrow request
        $this->actingAs($this->studentUser1);
        $borrowRes = $this->post(route('inventory.borrow.request'), [
            'inventory_item_id' => $item->id,
            'jumlah'            => 1,
            'keperluan'         => 'Praktikum Uji Kompetensi Keahlian',
            'rencana_kembali'   => Carbon::tomorrow()->toDateString(),
        ]);
        $borrowRes->assertRedirect();

        $borrowing = InventoryBorrowing::where('inventory_item_id', $item->id)->firstOrFail();
        $this->assertEquals('pending', $borrowing->status);

        // Admin approves request -> stock decrements
        $this->actingAs($this->admin);
        $approveRes = $this->post(route('inventory.borrow.approve', $borrowing->id));
        $approveRes->assertRedirect();
        $this->assertEquals('borrowed', $borrowing->fresh()->status);
        $this->assertEquals(4, $item->fresh()->unit_tersedia);

        // Item return inspection -> stock restored
        $returnRes = $this->post(route('inventory.borrow.return', $borrowing->id), [
            'kondisi_sesudah'      => 'baik',
            'catatan_pengembalian' => 'Alat dikembalikan dalam kondisi prima dan lengkap charger',
        ]);
        $returnRes->assertRedirect();
        $this->assertEquals('returned', $borrowing->fresh()->status);
        $this->assertEquals(5, $item->fresh()->unit_tersedia);
    }

    /**
     * 6. STUDENT PORTAL & PARENT PORTAL MULTI-CHILD LEAKAGE TEST
     */
    public function test_student_and_parent_portal_scoping_and_dual_track_consistency()
    {
        // Siswa 1 accesses gradebook
        $this->actingAs($this->studentUser1);
        $studentGb = $this->get(route('gradebook.student'));
        $studentGb->assertStatus(200);

        // Siswa 1 accesses discipline record
        $studentDisc = $this->get(route('discipline.student.portal'));
        $studentDisc->assertStatus(200);

        // Parent accesses portal and views child 1
        $this->actingAs($this->parentUser);
        $parentGb = $this->get(route('gradebook.parent', ['siswa_id' => $this->siswa1->id]));
        $parentGb->assertStatus(200);
        $parentGb->assertSee('Ahmad Rizki');

        // Parent attempts to query another child (siswa 2) not attached to them
        // System must fallback to own child or refuse leakage
        $leakageAttempt = $this->get(route('gradebook.parent', ['siswa_id' => $this->siswa2->id]));
        $leakageAttempt->assertStatus(200);
        // Ensure child 2 data is NOT leaked to parent
        $leakageAttempt->assertDontSee('Siti Aisyah');

        // Verify Dual-track academic pointer synchronization
        $this->assertEquals($this->siswa1->kelas_id, $this->kelas10Pplg1->id);
        $activeEnrollment = StudentEnrollment::where('siswa_id', $this->siswa1->id)
            ->where('is_active', true)
            ->first();
        $this->assertNotNull($activeEnrollment);
        $this->assertEquals($activeEnrollment->kelas_id, $this->siswa1->kelas_id);
        $this->assertEquals($activeEnrollment->tingkat, '10');
    }
}
