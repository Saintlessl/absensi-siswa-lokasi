<?php

namespace Tests\Feature;

use App\Models\Angkatan;
use App\Models\Guru;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\OrangTua;
use App\Models\Siswa;
use App\Models\SiswaStatusHistory;
use App\Models\StudentEnrollment;
use App\Models\TahunAjaran;
use App\Models\User;
use App\Services\AcademicPromotionService;
use App\Services\StudentAcademicHistoryService;
use App\Services\StudentImportService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class LiveAcademicWorkflowVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $superadmin;
    protected User $admin;
    protected User $kaprog;
    protected Guru $wali10;
    protected Guru $wali11;
    protected Guru $wali12;

    protected TahunAjaran $ta2024;
    protected TahunAjaran $ta2025;
    protected TahunAjaran $ta2026;
    protected TahunAjaran $ta2027;

    protected Jurusan $jurusanPplg;

    protected Angkatan $angkatan17; // 2024
    protected Angkatan $angkatan18; // 2025
    protected Angkatan $angkatan19; // 2026

    // Classes 2026/2027
    protected Kelas $kelas10Pplg1_2026;
    protected Kelas $kelas10Pplg2_2026;
    protected Kelas $kelas11Pplg1_2026;
    protected Kelas $kelas12Pplg1_2026;

    // Classes 2027/2028
    protected Kelas $kelas10Pplg1_2027;
    protected Kelas $kelas11Pplg1_2027;
    protected Kelas $kelas12Pplg1_2027;

    protected StudentAcademicHistoryService $historyService;
    protected AcademicPromotionService $promotionService;
    protected StudentImportService $importService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->historyService = app(StudentAcademicHistoryService::class);
        $this->promotionService = app(AcademicPromotionService::class);
        $this->importService = app(StudentImportService::class);

        $this->superadmin = User::factory()->create(['name' => 'Superadmin MySkanic', 'role' => 'superadmin', 'email' => 'superadmin.wf@myskanic.test']);
        $this->admin = User::factory()->create(['name' => 'Admin Akademik', 'role' => 'admin', 'email' => 'admin.wf@myskanic.test']);

        $this->jurusanPplg = Jurusan::firstOrCreate(
            ['kode_jurusan' => 'PPLG'],
            ['nama_jurusan' => 'Pengembangan Perangkat Lunak dan Gim', 'is_active' => true]
        );

        $this->kaprog = User::factory()->create([
            'name'       => 'Kaprog PPLG',
            'role'       => 'kaprog',
            'jurusan_id' => $this->jurusanPplg->id,
            'email'      => 'kaprog.wf@myskanic.test'
        ]);

        $uWali10 = User::factory()->create(['name' => 'Wali Kelas 10', 'role' => 'guru', 'email' => 'wali10@myskanic.test']);
        $this->wali10 = Guru::create(['user_id' => $uWali10->id, 'nama' => 'Wali Kelas 10', 'nip' => '198001012005011010', 'is_active' => true]);

        $uWali11 = User::factory()->create(['name' => 'Wali Kelas 11', 'role' => 'guru', 'email' => 'wali11@myskanic.test']);
        $this->wali11 = Guru::create(['user_id' => $uWali11->id, 'nama' => 'Wali Kelas 11', 'nip' => '198002022005011011', 'is_active' => true]);

        $uWali12 = User::factory()->create(['name' => 'Wali Kelas 12', 'role' => 'guru', 'email' => 'wali12@myskanic.test']);
        $this->wali12 = Guru::create(['user_id' => $uWali12->id, 'nama' => 'Wali Kelas 12', 'nip' => '198003032005011012', 'is_active' => true]);

        // Academic Years
        $this->ta2024 = TahunAjaran::firstOrCreate(['nama' => '2024/2025'], [
            'tahun_mulai' => 2024, 'tahun_selesai' => 2025, 'semester_aktif' => 'genap',
            'tanggal_mulai' => '2024-07-15', 'tanggal_selesai' => '2025-06-25', 'is_active' => false, 'status' => 'selesai'
        ]);

        $this->ta2025 = TahunAjaran::firstOrCreate(['nama' => '2025/2026'], [
            'tahun_mulai' => 2025, 'tahun_selesai' => 2026, 'semester_aktif' => 'genap',
            'tanggal_mulai' => '2025-07-15', 'tanggal_selesai' => '2026-06-25', 'is_active' => false, 'status' => 'selesai'
        ]);

        $this->ta2026 = TahunAjaran::firstOrCreate(['nama' => '2026/2027'], [
            'tahun_mulai' => 2026, 'tahun_selesai' => 2027, 'semester_aktif' => 'ganjil',
            'tanggal_mulai' => '2026-07-15', 'tanggal_selesai' => '2027-06-25', 'is_active' => true, 'status' => 'aktif'
        ]);
        $this->ta2026->update(['is_active' => true, 'status' => 'aktif']);

        $this->ta2027 = TahunAjaran::firstOrCreate(['nama' => '2027/2028'], [
            'tahun_mulai' => 2027, 'tahun_selesai' => 2028, 'semester_aktif' => 'ganjil',
            'tanggal_mulai' => '2027-07-15', 'tanggal_selesai' => '2028-06-25', 'is_active' => false, 'status' => 'draft'
        ]);

        // Cohorts
        $this->angkatan17 = Angkatan::firstOrCreate(
            ['tahun_masuk' => 2024, 'jurusan_id' => $this->jurusanPplg->id],
            ['nomor_angkatan' => 17, 'nama_angkatan' => 'Angkatan 17 PPLG', 'kode_angkatan' => 'AKT-PPLG-17', 'tahun_kelulusan' => 2027, 'status' => 'aktif', 'is_active' => true]
        );

        $this->angkatan18 = Angkatan::firstOrCreate(
            ['tahun_masuk' => 2025, 'jurusan_id' => $this->jurusanPplg->id],
            ['nomor_angkatan' => 18, 'nama_angkatan' => 'Angkatan 18 PPLG', 'kode_angkatan' => 'AKT-PPLG-18', 'tahun_kelulusan' => 2028, 'status' => 'aktif', 'is_active' => true]
        );

        $this->angkatan19 = Angkatan::firstOrCreate(
            ['tahun_masuk' => 2026, 'jurusan_id' => $this->jurusanPplg->id],
            ['nomor_angkatan' => 19, 'nama_angkatan' => 'Angkatan 19 PPLG', 'kode_angkatan' => 'AKT-PPLG-19', 'tahun_kelulusan' => 2029, 'status' => 'aktif', 'is_active' => true]
        );

        // Classes 2026/2027
        $this->kelas10Pplg1_2026 = Kelas::firstOrCreate(['nama_kelas' => 'X PPLG 1', 'tahun_ajaran_id' => $this->ta2026->id], [
            'jurusan_id' => $this->jurusanPplg->id, 'tingkat' => 10, 'nomor_rombel' => 1, 'tahun_ajaran' => '2026/2027', 'is_active' => true
        ]);
        $this->kelas10Pplg2_2026 = Kelas::firstOrCreate(['nama_kelas' => 'X PPLG 2', 'tahun_ajaran_id' => $this->ta2026->id], [
            'jurusan_id' => $this->jurusanPplg->id, 'tingkat' => 10, 'nomor_rombel' => 2, 'tahun_ajaran' => '2026/2027', 'is_active' => true
        ]);
        $this->kelas11Pplg1_2026 = Kelas::firstOrCreate(['nama_kelas' => 'XI PPLG 1', 'tahun_ajaran_id' => $this->ta2026->id], [
            'jurusan_id' => $this->jurusanPplg->id, 'tingkat' => 11, 'nomor_rombel' => 1, 'tahun_ajaran' => '2026/2027', 'is_active' => true
        ]);
        $this->kelas12Pplg1_2026 = Kelas::firstOrCreate(['nama_kelas' => 'XII PPLG 1', 'tahun_ajaran_id' => $this->ta2026->id], [
            'jurusan_id' => $this->jurusanPplg->id, 'tingkat' => 12, 'nomor_rombel' => 1, 'tahun_ajaran' => '2026/2027', 'is_active' => true
        ]);

        // Classes 2027/2028
        $this->kelas10Pplg1_2027 = Kelas::firstOrCreate(['nama_kelas' => 'X PPLG 1', 'tahun_ajaran_id' => $this->ta2027->id], [
            'jurusan_id' => $this->jurusanPplg->id, 'tingkat' => 10, 'nomor_rombel' => 1, 'tahun_ajaran' => '2027/2028', 'is_active' => true
        ]);
        $this->kelas11Pplg1_2027 = Kelas::firstOrCreate(['nama_kelas' => 'XI PPLG 1', 'tahun_ajaran_id' => $this->ta2027->id], [
            'jurusan_id' => $this->jurusanPplg->id, 'tingkat' => 11, 'nomor_rombel' => 1, 'tahun_ajaran' => '2027/2028', 'is_active' => true
        ]);
        $this->kelas12Pplg1_2027 = Kelas::firstOrCreate(['nama_kelas' => 'XII PPLG 1', 'tahun_ajaran_id' => $this->ta2027->id], [
            'jurusan_id' => $this->jurusanPplg->id, 'tingkat' => 12, 'nomor_rombel' => 1, 'tahun_ajaran' => '2027/2028', 'is_active' => true
        ]);
    }

    protected function createSpreadsheetFile(array $rows): UploadedFile
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = [
            'NISN', 'NIS', 'Nama Lengkap', 'Jenis Kelamin (L/P)', 'Tempat Lahir',
            'Tanggal Lahir (YYYY-MM-DD)', 'Email Siswa (Opsional)', 'Tahun Ajaran Masuk (misal: 2024/2025)',
            'Kode Jurusan (PPLG/ANIMASI/dll)', 'Nomor Angkatan (misal: 17)', 'Tingkat (10/11/12)',
            'Nama Rombel (misal: X PPLG 1)', 'Status Siswa (aktif/lulus/pindah/keluar)',
            'Tahun Ajaran Enrollment (misal: 2026/2027)', 'Tanggal Kelulusan (YYYY-MM-DD - khusus alumni)',
            'Jalur Masuk (baru/pindahan)', 'Asal Sekolah (khusus pindahan)', 'Nama Orang Tua / Wali',
            'Telepon Orang Tua', 'Alamat Rumah'
        ];

        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $col++;
        }

        $rowIdx = 2;
        foreach ($rows as $data) {
            $col = 'A';
            foreach ($data as $val) {
                $sheet->setCellValueExplicit($col . $rowIdx, $val, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $col++;
            }
            $rowIdx++;
        }

        $tmpFile = tempnam(sys_get_temp_dir(), 'test_live_') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tmpFile);

        return new UploadedFile($tmpFile, 'live_import.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
    }

    /**
     * Skenario 1: Registrasi Siswa Baru (Manual via Controller)
     */
    public function test_scenario_1_registrasi_siswa_baru(): void
    {
        $response = $this->from(route('siswa'))->actingAs($this->admin)->post(route('siswa.store'), [
            'nisn'                  => '0081112233',
            'nis'                   => '262710001',
            'nama'                  => 'Ahmad Fajar Siswa Baru',
            'username'              => 'ahmad.fajar',
            'password'              => 'SiswaPass123!',
            'jenis_kelamin'         => 'L',
            'tempat_lahir'          => 'Bogor',
            'tanggal_lahir'         => '2010-05-15',
            'kelas_id'              => $this->kelas10Pplg1_2026->id,
            'jurusan_id'            => $this->jurusanPplg->id,
            'tingkat'               => 10,
            'angkatan_id'           => $this->angkatan19->id,
            'tahun_ajaran_id'       => $this->ta2026->id,
            'jalur_masuk'           => 'baru',
            'parent_name'           => 'H. Ridwan',
            'parent_jenis_kelamin'  => 'L',
            'parent_phone'          => '081298765432',
            'parent_address'        => 'Jl. Raya Ciomas No. 10',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('siswa'));

        $siswa = Siswa::where('nisn', '0081112233')->firstOrFail();

        // 1. siswas.kelas_id & status
        $this->assertEquals($this->kelas10Pplg1_2026->id, $siswa->kelas_id);
        $this->assertEquals('aktif', $siswa->status);

        // 2. student_enrollments
        $activeEnrollment = $siswa->activeEnrollment;
        $this->assertNotNull($activeEnrollment);
        $this->assertEquals($this->ta2026->id, $activeEnrollment->tahun_ajaran_id);
        $this->assertEquals($this->kelas10Pplg1_2026->id, $activeEnrollment->kelas_id);
        $this->assertTrue((bool) $activeEnrollment->is_active);
        $this->assertEquals(1, $siswa->enrollments()->count());

        // 3. siswa_status_histories
        $histories = $siswa->statusHistories;
        $this->assertNotEmpty($histories);
        $this->assertEquals('aktif', $histories->first()->status_baru);

        // 4. Identitas angkatan dan tahun masuk permanen
        $this->assertEquals($this->angkatan19->id, $siswa->angkatan_id);
        $this->assertEquals($this->ta2026->id, $siswa->tahun_ajaran_masuk_id);
        $this->assertEquals(2026, $siswa->tahun_masuk);
    }

    /**
     * Skenario 2: Import Siswa Melalui Excel
     */
    public function test_scenario_2_import_siswa_melalui_excel(): void
    {
        $file = $this->createSpreadsheetFile([
            [
                '0082223344', '262710002', 'Budi Santoso Import', 'L', 'Bogor',
                '2010-08-20', 'budi.import@myskanic.test', '2026/2027',
                'PPLG', '19', '10', 'X PPLG 1', 'aktif',
                '2026/2027', '', 'baru', '', 'Pak Santoso',
                '081312345678', 'Ciomas Permai Blok A'
            ]
        ]);

        $parsed = $this->importService->parseAndValidate($file, $this->admin);
        $this->assertEquals(1, $parsed['valid_count']);
        $this->assertEquals(0, $parsed['error_count']);

        $batch = $this->importService->executeImport($parsed['valid_rows'], 'aktif', $this->admin);

        $this->assertEquals('completed', $batch->status);
        $this->assertEquals(1, $batch->successful_rows);

        $siswa = Siswa::where('nisn', '0082223344')->firstOrFail();

        // 1. siswas.kelas_id & status
        $this->assertEquals($this->kelas10Pplg1_2026->id, $siswa->kelas_id);
        $this->assertEquals('aktif', $siswa->status);

        // 2. student_enrollments
        $activeEnrollment = $siswa->activeEnrollment;
        $this->assertNotNull($activeEnrollment);
        $this->assertEquals($this->ta2026->id, $activeEnrollment->tahun_ajaran_id);
        $this->assertEquals($this->kelas10Pplg1_2026->id, $activeEnrollment->kelas_id);
        $this->assertTrue((bool) $activeEnrollment->is_active);

        // 3. siswa_status_histories
        $this->assertTrue($siswa->statusHistories()->exists());

        // 4. Identitas permanen
        $this->assertEquals($this->angkatan19->id, $siswa->angkatan_id);
        $this->assertEquals($this->ta2026->id, $siswa->tahun_ajaran_masuk_id);
        $this->assertEquals(2026, $siswa->tahun_masuk);
    }

    /**
     * Skenario 3: Mutasi Masuk dari Sekolah Lain
     */
    public function test_scenario_3_mutasi_masuk_dari_sekolah_lain(): void
    {
        $file = $this->createSpreadsheetFile([
            [
                '0073334455', '262711001', 'Citra Pindahan Lestari', 'P', 'Bandung',
                '2009-02-14', 'citra.pindahan@myskanic.test', '2025/2026',
                'PPLG', '18', '11', 'XI PPLG 1', 'aktif',
                '2026/2027', '', 'pindahan', 'SMK Negeri 2 Bandung', 'Ibu Lestari',
                '081398765432', 'Jl. Laladon No. 44'
            ]
        ]);

        $parsed = $this->importService->parseAndValidate($file, $this->admin);
        $this->assertEquals(1, $parsed['valid_count']);

        $batch = $this->importService->executeImport($parsed['valid_rows'], 'aktif', $this->admin);
        $this->assertEquals('completed', $batch->status);

        $siswa = Siswa::where('nisn', '0073334455')->firstOrFail();

        // 1. siswas.kelas_id & status
        $this->assertEquals($this->kelas11Pplg1_2026->id, $siswa->kelas_id);
        $this->assertEquals('aktif', $siswa->status);
        $this->assertEquals('pindahan', $siswa->jalur_masuk);
        $this->assertEquals('SMK Negeri 2 Bandung', $siswa->asal_sekolah);

        // 2. student_enrollments
        $activeEnrollment = $siswa->activeEnrollment;
        $this->assertNotNull($activeEnrollment);
        $this->assertEquals($this->ta2026->id, $activeEnrollment->tahun_ajaran_id);
        $this->assertEquals($this->kelas11Pplg1_2026->id, $activeEnrollment->kelas_id);
        $this->assertTrue((bool) $activeEnrollment->is_active);

        // 3. siswa_status_histories
        $this->assertTrue($siswa->statusHistories()->exists());

        // 4. Identitas permanen
        $this->assertEquals($this->angkatan18->id, $siswa->angkatan_id);
        $this->assertEquals($this->ta2025->id, $siswa->tahun_ajaran_masuk_id);
        $this->assertEquals(2025, $siswa->tahun_masuk);
    }

    /**
     * Skenario 4: Promosi Kelas X ke XI (Wizard)
     */
    public function test_scenario_4_promosi_kelas_x_ke_xi(): void
    {
        // Setup a student in Grade 10 on TA 2026/2027
        $user = User::factory()->create(['role' => 'siswa']);
        $siswa = Siswa::create([
            'user_id' => $user->id, 'nisn' => '0084445566', 'nis' => '262710003', 'nama' => 'Doni Promosi X-XI',
            'jenis_kelamin' => 'L', 'kelas_id' => $this->kelas10Pplg1_2026->id, 'angkatan_id' => $this->angkatan19->id,
            'tahun_ajaran_masuk_id' => $this->ta2026->id, 'tahun_masuk' => 2026, 'status' => 'aktif',
        ]);
        $this->historyService->recordInitialEnrollment($siswa, $this->kelas10Pplg1_2026, $this->ta2026, $this->ta2026, $this->admin);

        // Promote to XI PPLG 1 on TA 2027/2028 via AcademicPromotionService
        $batch = $this->promotionService->executePromotionBatch([
            'tahun_ajaran_asal_id'   => $this->ta2026->id,
            'tahun_ajaran_tujuan_id' => $this->ta2027->id,
            'catatan'                => 'Promosi Massal X ke XI',
            'students' => [
                [
                    'siswa_id'        => $siswa->id,
                    'kelas_tujuan_id' => $this->kelas11Pplg1_2027->id,
                    'action'          => 'naik_kelas',
                    'catatan'         => 'Naik kelas ke XI',
                ]
            ]
        ], $this->admin);

        $this->assertEquals('completed', $batch->status);
        $this->assertEquals(1, $batch->total_naik);
        $siswa->refresh();

        // 1. siswas.kelas_id & status
        $this->assertEquals($this->kelas11Pplg1_2027->id, $siswa->kelas_id);
        $this->assertEquals('aktif', $siswa->status);

        // 2. student_enrollments: old inactive, new active
        $this->assertEquals(2, $siswa->enrollments()->count());
        $oldEnrollment = $siswa->enrollments()->where('tahun_ajaran_id', $this->ta2026->id)->firstOrFail();
        $this->assertFalse((bool) $oldEnrollment->is_active);
        $this->assertEquals($this->kelas10Pplg1_2026->id, $oldEnrollment->kelas_id);

        $newEnrollment = $siswa->activeEnrollment;
        $this->assertNotNull($newEnrollment);
        $this->assertEquals($this->ta2027->id, $newEnrollment->tahun_ajaran_id);
        $this->assertEquals($this->kelas11Pplg1_2027->id, $newEnrollment->kelas_id);
        $this->assertTrue((bool) $newEnrollment->is_active);

        // 3. siswa_status_histories
        $latestHistory = $siswa->statusHistories()->latest('id')->firstOrFail();
        $this->assertEquals('kenaikan_kelas', $latestHistory->jenis_perubahan);
        $this->assertEquals($this->kelas11Pplg1_2027->id, $latestHistory->kelas_tujuan_id);

        // 4. Identitas permanen
        $this->assertEquals($this->angkatan19->id, $siswa->angkatan_id);
        $this->assertEquals($this->ta2026->id, $siswa->tahun_ajaran_masuk_id);
        $this->assertEquals(2026, $siswa->tahun_masuk);
    }

    /**
     * Skenario 5: Promosi Kelas XI ke XII
     */
    public function test_scenario_5_promosi_kelas_xi_ke_xii(): void
    {
        // Setup a student in Grade 11 on TA 2026/2027
        $user = User::factory()->create(['role' => 'siswa']);
        $siswa = Siswa::create([
            'user_id' => $user->id, 'nisn' => '0075556677', 'nis' => '252610005', 'nama' => 'Eka Promosi XI-XII',
            'jenis_kelamin' => 'P', 'kelas_id' => $this->kelas11Pplg1_2026->id, 'angkatan_id' => $this->angkatan18->id,
            'tahun_ajaran_masuk_id' => $this->ta2025->id, 'tahun_masuk' => 2025, 'status' => 'aktif',
        ]);
        $this->historyService->recordInitialEnrollment($siswa, $this->kelas11Pplg1_2026, $this->ta2026, $this->ta2025, $this->admin);

        // Promote to XII PPLG 1 on TA 2027/2028
        $batch = $this->promotionService->executePromotionBatch([
            'tahun_ajaran_asal_id'   => $this->ta2026->id,
            'tahun_ajaran_tujuan_id' => $this->ta2027->id,
            'catatan'                => 'Promosi Massal XI ke XII',
            'students' => [
                [
                    'siswa_id'        => $siswa->id,
                    'kelas_tujuan_id' => $this->kelas12Pplg1_2027->id,
                    'action'          => 'naik_kelas',
                    'catatan'         => 'Naik kelas ke XII',
                ]
            ]
        ], $this->admin);

        $this->assertEquals('completed', $batch->status);
        $this->assertEquals(1, $batch->total_naik);
        $siswa->refresh();

        // 1. siswas.kelas_id & status
        $this->assertEquals($this->kelas12Pplg1_2027->id, $siswa->kelas_id);
        $this->assertEquals('aktif', $siswa->status);

        // 2. student_enrollments
        $newEnrollment = $siswa->activeEnrollment;
        $this->assertNotNull($newEnrollment);
        $this->assertEquals($this->ta2027->id, $newEnrollment->tahun_ajaran_id);
        $this->assertEquals($this->kelas12Pplg1_2027->id, $newEnrollment->kelas_id);
        $this->assertTrue((bool) $newEnrollment->is_active);

        // 3. Permanent identity preserved
        $this->assertEquals($this->angkatan18->id, $siswa->angkatan_id);
        $this->assertEquals($this->ta2025->id, $siswa->tahun_ajaran_masuk_id);
        $this->assertEquals(2025, $siswa->tahun_masuk);
    }

    /**
     * Skenario 6: Siswa Tinggal Kelas (Retention)
     */
    public function test_scenario_6_siswa_tinggal_kelas(): void
    {
        $user = User::factory()->create(['role' => 'siswa']);
        $siswa = Siswa::create([
            'user_id' => $user->id, 'nisn' => '0086667788', 'nis' => '262710006', 'nama' => 'Fajar Tinggal Kelas',
            'jenis_kelamin' => 'L', 'kelas_id' => $this->kelas10Pplg1_2026->id, 'angkatan_id' => $this->angkatan19->id,
            'tahun_ajaran_masuk_id' => $this->ta2026->id, 'tahun_masuk' => 2026, 'status' => 'aktif',
        ]);
        $this->historyService->recordInitialEnrollment($siswa, $this->kelas10Pplg1_2026, $this->ta2026, $this->ta2026, $this->admin);

        // Retain in Grade 10 on TA 2027/2028
        $batch = $this->promotionService->executePromotionBatch([
            'tahun_ajaran_asal_id'   => $this->ta2026->id,
            'tahun_ajaran_tujuan_id' => $this->ta2027->id,
            'catatan'                => 'Siswa Tinggal Kelas X',
            'students' => [
                [
                    'siswa_id'        => $siswa->id,
                    'kelas_tujuan_id' => $this->kelas10Pplg1_2027->id,
                    'action'          => 'tinggal_kelas',
                    'catatan'         => 'Tinggal kelas mengulang tingkat X',
                ]
            ]
        ], $this->admin);

        $this->assertEquals('completed', $batch->status);
        $this->assertEquals(1, $batch->total_tinggal);
        $siswa->refresh();

        // 1. siswas.kelas_id & status
        $this->assertEquals($this->kelas10Pplg1_2027->id, $siswa->kelas_id);
        $this->assertEquals('aktif', $siswa->status);

        // 2. student_enrollments
        $newEnrollment = $siswa->activeEnrollment;
        $this->assertNotNull($newEnrollment);
        $this->assertEquals($this->ta2027->id, $newEnrollment->tahun_ajaran_id);
        $this->assertEquals($this->kelas10Pplg1_2027->id, $newEnrollment->kelas_id);
        $this->assertEquals(10, $newEnrollment->tingkat);

        // 3. siswa_status_histories
        $latestHistory = $siswa->statusHistories()->latest('id')->firstOrFail();
        $this->assertEquals('tinggal_kelas', $latestHistory->jenis_perubahan);

        // 4. Permanent cohort remains original
        $this->assertEquals($this->angkatan19->id, $siswa->angkatan_id);
        $this->assertEquals($this->ta2026->id, $siswa->tahun_ajaran_masuk_id);
        $this->assertEquals(2026, $siswa->tahun_masuk);
    }

    /**
     * Skenario 7: Pindah Rombel Paralel
     */
    public function test_scenario_7_pindah_rombel_paralel(): void
    {
        $user = User::factory()->create(['role' => 'siswa']);
        $siswa = Siswa::create([
            'user_id' => $user->id, 'nisn' => '0087778899', 'nis' => '262710007', 'nama' => 'Gilang Pindah Paralel',
            'jenis_kelamin' => 'L', 'kelas_id' => $this->kelas10Pplg1_2026->id, 'angkatan_id' => $this->angkatan19->id,
            'tahun_ajaran_masuk_id' => $this->ta2026->id, 'tahun_masuk' => 2026, 'status' => 'aktif',
        ]);
        $this->historyService->recordInitialEnrollment($siswa, $this->kelas10Pplg1_2026, $this->ta2026, $this->ta2026, $this->admin);

        // Transfer from X PPLG 1 to X PPLG 2 in same academic year
        $newEnrollment = $this->historyService->transferClass(
            $siswa,
            $this->kelas10Pplg2_2026,
            'Penyesuaian kuota rombel X PPLG',
            $this->admin
        );

        $siswa->refresh();

        // 1. siswas.kelas_id & status
        $this->assertEquals($this->kelas10Pplg2_2026->id, $siswa->kelas_id);
        $this->assertEquals('aktif', $siswa->status);

        // 2. student_enrollments
        $this->assertEquals($this->kelas10Pplg2_2026->id, $newEnrollment->kelas_id);
        $this->assertTrue((bool) $newEnrollment->is_active);
        $this->assertEquals($this->ta2026->id, $newEnrollment->tahun_ajaran_id);

        $oldEnrollment = $siswa->enrollments()->where('kelas_id', $this->kelas10Pplg1_2026->id)->firstOrFail();
        $this->assertFalse((bool) $oldEnrollment->is_active);

        // 3. siswa_status_histories
        $latestHistory = $siswa->statusHistories()->latest('id')->firstOrFail();
        $this->assertEquals('pindah_rombel', $latestHistory->jenis_perubahan);
        $this->assertEquals($this->kelas10Pplg1_2026->id, $latestHistory->kelas_asal_id);
        $this->assertEquals($this->kelas10Pplg2_2026->id, $latestHistory->kelas_tujuan_id);

        // 4. Identitas permanen
        $this->assertEquals($this->angkatan19->id, $siswa->angkatan_id);
        $this->assertEquals($this->ta2026->id, $siswa->tahun_ajaran_masuk_id);
    }

    /**
     * Skenario 8: Kelulusan Kelas XII
     */
    public function test_scenario_8_kelulusan_kelas_xii(): void
    {
        $user = User::factory()->create(['role' => 'siswa']);
        $siswa = Siswa::create([
            'user_id' => $user->id, 'nisn' => '0068889900', 'nis' => '242510008', 'nama' => 'Hani Alumni Lulus',
            'jenis_kelamin' => 'P', 'kelas_id' => $this->kelas12Pplg1_2026->id, 'angkatan_id' => $this->angkatan17->id,
            'tahun_ajaran_masuk_id' => $this->ta2024->id, 'tahun_masuk' => 2024, 'status' => 'aktif',
        ]);
        $this->historyService->recordInitialEnrollment($siswa, $this->kelas12Pplg1_2026, $this->ta2026, $this->ta2024, $this->admin);

        // Process graduation via Promotion Service
        $batch = $this->promotionService->executePromotionBatch([
            'tahun_ajaran_asal_id'   => $this->ta2026->id,
            'tahun_ajaran_tujuan_id' => $this->ta2027->id,
            'catatan'                => 'Kelulusan Angkatan 17',
            'students' => [
                [
                    'siswa_id'        => $siswa->id,
                    'kelas_tujuan_id' => null,
                    'action'          => 'lulus',
                    'catatan'         => 'Lulus SMK Negeri 1 Ciomas',
                ]
            ]
        ], $this->admin);

        $this->assertEquals('completed', $batch->status);
        $this->assertEquals(1, $batch->total_lulus);
        $siswa->refresh();

        // 1. siswas.status & class pointer preservation
        $this->assertEquals('lulus', $siswa->status);
        $this->assertNotNull($siswa->tanggal_kelulusan);

        // 2. student_enrollments: zero active
        $this->assertNull($siswa->activeEnrollment);
        $this->assertEquals(0, $siswa->enrollments()->where('is_active', true)->count());
        $this->assertEquals(1, $siswa->enrollments()->count()); // Historical remains preserved

        // 3. siswa_status_histories
        $latestHistory = $siswa->statusHistories()->latest('id')->firstOrFail();
        $this->assertEquals('kelulusan', $latestHistory->jenis_perubahan);
        $this->assertEquals('lulus', $latestHistory->status_baru);

        // 4. Identitas permanen
        $this->assertEquals($this->angkatan17->id, $siswa->angkatan_id);
        $this->assertEquals($this->ta2024->id, $siswa->tahun_ajaran_masuk_id);
        $this->assertEquals(2024, $siswa->tahun_masuk);
    }

    /**
     * Skenario 9: Mutasi Keluar
     */
    public function test_scenario_9_mutasi_keluar(): void
    {
        $user = User::factory()->create(['role' => 'siswa']);
        $siswa = Siswa::create([
            'user_id' => $user->id, 'nisn' => '0089990011', 'nis' => '262710009', 'nama' => 'Indra Mutasi Keluar',
            'jenis_kelamin' => 'L', 'kelas_id' => $this->kelas10Pplg1_2026->id, 'angkatan_id' => $this->angkatan19->id,
            'tahun_ajaran_masuk_id' => $this->ta2026->id, 'tahun_masuk' => 2026, 'status' => 'aktif',
        ]);
        $this->historyService->recordInitialEnrollment($siswa, $this->kelas10Pplg1_2026, $this->ta2026, $this->ta2026, $this->admin);

        // Record status change via transferOutStudent
        $this->historyService->transferOutStudent(
            $siswa,
            'SMK Negeri 1 Surabaya',
            '421.5/088/SMK-2026',
            'Pindah mengikuti dinas orang tua ke Surabaya',
            $this->ta2026,
            $this->admin
        );

        $siswa->refresh();

        // 1. siswas.status
        $this->assertEquals('pindah', $siswa->status);

        // 2. student_enrollments: zero active
        $this->assertNull($siswa->activeEnrollment);
        $this->assertEquals(0, $siswa->enrollments()->where('is_active', true)->count());

        // 3. siswa_status_histories
        $latestHistory = $siswa->statusHistories()->latest('id')->firstOrFail();
        $this->assertEquals('mutasi_keluar', $latestHistory->jenis_perubahan);
        $this->assertEquals('pindah', $latestHistory->status_baru);

        // 4. Identitas permanen
        $this->assertEquals($this->angkatan19->id, $siswa->angkatan_id);
        $this->assertEquals($this->ta2026->id, $siswa->tahun_ajaran_masuk_id);
    }

    /**
     * Skenario 10: Drop Out (Keluar)
     */
    public function test_scenario_10_drop_out(): void
    {
        $user = User::factory()->create(['role' => 'siswa']);
        $siswa = Siswa::create([
            'user_id' => $user->id, 'nisn' => '0089990022', 'nis' => '262710010', 'nama' => 'Joko Drop Out',
            'jenis_kelamin' => 'L', 'kelas_id' => $this->kelas10Pplg1_2026->id, 'angkatan_id' => $this->angkatan19->id,
            'tahun_ajaran_masuk_id' => $this->ta2026->id, 'tahun_masuk' => 2026, 'status' => 'aktif',
        ]);
        $this->historyService->recordInitialEnrollment($siswa, $this->kelas10Pplg1_2026, $this->ta2026, $this->ta2026, $this->admin);

        // Record status change via dropOutStudent
        $this->historyService->dropOutStudent(
            $siswa,
            'Mengundurkan diri bekerja membantu keluarga',
            $this->ta2026,
            $this->admin
        );

        $siswa->refresh();

        // 1. siswas.status
        $this->assertEquals('keluar', $siswa->status);

        // 2. student_enrollments: zero active
        $this->assertNull($siswa->activeEnrollment);
        $this->assertEquals(0, $siswa->enrollments()->where('is_active', true)->count());

        // 3. siswa_status_histories
        $latestHistory = $siswa->statusHistories()->latest('id')->firstOrFail();
        $this->assertEquals('keluar', $latestHistory->jenis_perubahan);
        $this->assertEquals('keluar', $latestHistory->status_baru);

        // 4. Identitas permanen
        $this->assertEquals($this->angkatan19->id, $siswa->angkatan_id);
        $this->assertEquals($this->ta2026->id, $siswa->tahun_ajaran_masuk_id);
    }

    /**
     * Skenario 11: Perubahan Rombel Melalui Edit Profil Admin
     */
    public function test_scenario_11_perubahan_rombel_melalui_edit_profil_admin(): void
    {
        $user = User::factory()->create(['name' => 'Kurnia Siswa Edit', 'email' => 'kurnia@myskanic.com', 'role' => 'siswa']);
        $siswa = Siswa::create([
            'user_id' => $user->id, 'nisn' => '0089990033', 'nis' => '262710011', 'nama' => 'Kurnia Siswa Edit',
            'jenis_kelamin' => 'P', 'kelas_id' => $this->kelas10Pplg1_2026->id, 'angkatan_id' => $this->angkatan19->id,
            'tahun_ajaran_masuk_id' => $this->ta2026->id, 'tahun_masuk' => 2026, 'status' => 'aktif',
        ]);
        $this->historyService->recordInitialEnrollment($siswa, $this->kelas10Pplg1_2026, $this->ta2026, $this->ta2026, $this->admin);

        $parentUser = User::factory()->create(['role' => 'orangtua', 'email' => 'parent.kurnia@myskanic.test']);
        $parent = OrangTua::create([
            'user_id' => $parentUser->id, 'nama' => 'Ibu Kurnia', 'telepon' => '081234567890',
            'alamat' => 'Ciomas Pagelaran', 'siswa_id' => $siswa->id
        ]);
        $parent->siswa()->attach($siswa->id);

        // Admin updates student's class to X PPLG 2 via SiswaController::update
        $response = $this->from(route('siswa'))->actingAs($this->admin)->put(route('siswa.update', $siswa->id), [
            'nisn'                  => '0089990033',
            'nis'                   => '262710011',
            'nama'                  => 'Kurnia Siswa Edit Updated',
            'username'              => 'kurnia',
            'jenis_kelamin'         => 'P',
            'tempat_lahir'          => 'Bogor',
            'tanggal_lahir'         => '2010-09-19',
            'kelas_id'              => $this->kelas10Pplg2_2026->id, // Changed class
            'angkatan_id'           => $this->angkatan19->id,
            'parent_name'           => 'Ibu Kurnia',
            'parent_jenis_kelamin'  => 'P',
            'parent_phone'          => '081234567890',
            'parent_address'        => 'Ciomas Pagelaran Baru',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('siswa'));

        $siswa->refresh();

        // 1. siswas.kelas_id & status
        $this->assertEquals($this->kelas10Pplg2_2026->id, $siswa->kelas_id);
        $this->assertEquals('aktif', $siswa->status);

        // 2. student_enrollments: synchronized to new class
        $activeEnrollment = $siswa->activeEnrollment;
        $this->assertNotNull($activeEnrollment);
        $this->assertEquals($this->kelas10Pplg2_2026->id, $activeEnrollment->kelas_id);
        $this->assertTrue((bool) $activeEnrollment->is_active);

        $oldEnrollment = $siswa->enrollments()->where('kelas_id', $this->kelas10Pplg1_2026->id)->firstOrFail();
        $this->assertFalse((bool) $oldEnrollment->is_active);

        // 3. siswa_status_histories
        $latestHistory = $siswa->statusHistories()->latest('id')->firstOrFail();
        $this->assertEquals('pindah_rombel', $latestHistory->jenis_perubahan);

        // 4. Identitas permanen
        $this->assertEquals($this->angkatan19->id, $siswa->angkatan_id);
        $this->assertEquals($this->ta2026->id, $siswa->tahun_ajaran_masuk_id);
    }
}
