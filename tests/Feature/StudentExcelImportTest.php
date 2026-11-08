<?php

namespace Tests\Feature;

use App\Models\Angkatan;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\StudentEnrollment;
use App\Models\StudentImportBatch;
use App\Models\TahunAjaran;
use App\Models\User;
use App\Services\StudentImportService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class StudentExcelImportTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $kaprog;
    protected TahunAjaran $ta2024;
    protected TahunAjaran $ta2026;
    protected Jurusan $jurusanPplg;
    protected Jurusan $jurusanAnimasi;
    protected Angkatan $angkatan17Pplg;
    protected Angkatan $angkatan19Pplg;
    protected Kelas $kelas10Pplg1;
    protected Kelas $kelas12Pplg1;
    protected Kelas $kelas10Animasi1;
    protected StudentImportService $importService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->importService = app(StudentImportService::class);

        $this->admin = User::factory()->create(['role' => 'admin', 'email' => 'admin.import@myskanic.test']);

        $this->ta2024 = TahunAjaran::firstOrCreate(['nama' => '2024/2025'], [
            'tahun_mulai'     => 2024,
            'tahun_selesai'   => 2025,
            'semester_aktif'  => 'genap',
            'tanggal_mulai'   => '2024-07-15',
            'tanggal_selesai' => '2025-06-25',
            'is_active'       => false,
            'status'          => 'selesai',
        ]);

        $this->ta2026 = TahunAjaran::firstOrCreate(['nama' => '2026/2027'], [
            'tahun_mulai'     => 2026,
            'tahun_selesai'   => 2027,
            'semester_aktif'  => 'ganjil',
            'tanggal_mulai'   => '2026-07-15',
            'tanggal_selesai' => '2027-06-25',
            'is_active'       => true,
            'status'          => 'aktif',
        ]);
        $this->ta2026->update(['is_active' => true, 'status' => 'aktif']);

        $this->jurusanPplg = Jurusan::create([
            'nama_jurusan' => 'Pengembangan Perangkat Lunak dan Gim',
            'kode_jurusan' => 'PPLG',
        ]);

        $this->jurusanAnimasi = Jurusan::create([
            'nama_jurusan' => 'Animasi',
            'kode_jurusan' => 'ANIMASI',
        ]);

        $this->kaprog = User::factory()->create([
            'role'       => 'kaprog',
            'jurusan_id' => $this->jurusanPplg->id,
            'email'      => 'kaprog.import@myskanic.test',
        ]);

        $this->angkatan17Pplg = Angkatan::create([
            'jurusan_id'     => $this->jurusanPplg->id,
            'nomor_angkatan' => 17,
            'nama_angkatan'  => 'Angkatan 17 PPLG',
            'tahun_masuk'    => 2024,
            'is_active'      => true,
        ]);

        $this->angkatan19Pplg = Angkatan::create([
            'jurusan_id'     => $this->jurusanPplg->id,
            'nomor_angkatan' => 19,
            'nama_angkatan'  => 'Angkatan 19 PPLG',
            'tahun_masuk'    => 2026,
            'is_active'      => true,
        ]);

        $this->kelas10Pplg1 = Kelas::create([
            'nama_kelas'      => 'X PPLG 1',
            'jurusan_id'      => $this->jurusanPplg->id,
            'tingkat'         => 10,
            'nomor_rombel'    => 1,
            'tahun_ajaran'    => '2026/2027',
            'tahun_ajaran_id' => $this->ta2026->id,
            'wali_kelas'      => 'Dra. Siti Aminah',
            'is_active'       => true,
        ]);

        $this->kelas12Pplg1 = Kelas::create([
            'nama_kelas'      => 'XII PPLG 1',
            'jurusan_id'      => $this->jurusanPplg->id,
            'tingkat'         => 12,
            'nomor_rombel'    => 1,
            'tahun_ajaran'    => '2026/2027',
            'tahun_ajaran_id' => $this->ta2026->id,
            'wali_kelas'      => 'Bambang Sudarsono, S.T.',
            'is_active'       => true,
        ]);

        $this->kelas10Animasi1 = Kelas::create([
            'nama_kelas'      => 'X ANIMASI 1',
            'jurusan_id'      => $this->jurusanAnimasi->id,
            'tingkat'         => 10,
            'nomor_rombel'    => 1,
            'tahun_ajaran'    => '2026/2027',
            'tahun_ajaran_id' => $this->ta2026->id,
            'is_active'       => true,
        ]);
    }

    /**
     * Helper to create a temporary test Excel file.
     */
    protected function createTestExcel(array $rows): UploadedFile
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header Row
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

        $tmpFile = tempnam(sys_get_temp_dir(), 'test_import_') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tmpFile);

        return new UploadedFile($tmpFile, 'test_students.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
    }

    /**
     * Skenario 1: Download template import mengembalikan berkas excel yang valid.
     */
    public function test_download_import_template_returns_valid_excel_stream(): void
    {
        $response = $this->actingAs($this->admin)->get(route('siswa.import.template'));
        $response->assertOk();
        $response->assertHeader('content-disposition', 'attachment; filename=Template_Import_Siswa_MySkanic.xlsx');
    }

    /**
     * Skenario 2: Preview import memvalidasi data dan menghitung ringkasan baris valid serta error.
     */
    public function test_import_preview_validates_rows_and_returns_summary(): void
    {
        $testRows = [
            [
                '0081112233', '262710101', 'Fajar Pratama', 'L', 'Bogor', '2008-05-10',
                'fajar@myskanic.test', '2026/2027', 'PPLG', '19', '10', 'X PPLG 1',
                'aktif', '2026/2027', '', 'baru', '', 'Hendrawan', '081211112222', 'Ciomas'
            ],
            [
                '0081112234', '262710102', 'Dewi Lestari', 'P', 'Bogor', '2008-06-15',
                'dewi@myskanic.test', '2026/2027', 'PPLG', '19', '10', 'X PPLG 1',
                'aktif', '2026/2027', '', 'baru', '', 'Lestari', '081233334444', 'Bogor'
            ],
        ];

        $file = $this->createTestExcel($testRows);

        $response = $this->actingAs($this->admin)->post(route('siswa.import.preview'), [
            'excel_file'  => $file,
            'import_type' => 'aktif',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success'     => true,
            'total_rows'  => 2,
            'valid_count' => 2,
            'error_count' => 0,
        ]);
        $this->assertNotEmpty($response->json('import_token'));
    }

    /**
     * Skenario 3: Eksekusi import siswa aktif membuat siswa, enrollment aktif, snapshot wali kelas, dan akun ber-password hash.
     */
    public function test_import_execution_creates_active_student_with_enrollment_and_account(): void
    {
        $testRows = [
            [
                '0081112255', '262710155', 'Bagus Ramadhan', 'L', 'Bogor', '2008-01-01',
                'bagus.ramadhan@myskanic.test', '2026/2027', 'PPLG', '19', '10', 'X PPLG 1',
                'aktif', '2026/2027', '', 'baru', '', 'Subagyo', '081255556666', 'Ciomas'
            ],
        ];

        $file = $this->createTestExcel($testRows);

        // Preview
        $previewRes = $this->actingAs($this->admin)->post(route('siswa.import.preview'), [
            'excel_file'  => $file,
            'import_type' => 'aktif',
        ]);
        $token = $previewRes->json('import_token');

        // Confirm
        $confirmRes = $this->actingAs($this->admin)->post(route('siswa.import.confirm'), [
            'import_token' => $token,
        ]);

        $confirmRes->assertOk();
        $confirmRes->assertJson([
            'success'         => true,
            'successful_rows' => 1,
            'failed_rows'     => 0,
        ]);

        // Verify Siswa
        $siswa = Siswa::where('nis', '262710155')->first();
        $this->assertNotNull($siswa);
        $this->assertEquals('Bagus Ramadhan', $siswa->nama);
        $this->assertEquals($this->ta2026->id, $siswa->tahun_ajaran_masuk_id);
        $this->assertEquals(2026, $siswa->tahun_masuk);
        $this->assertEquals('aktif', $siswa->status);

        // Verify User Account and hashed password
        $user = $siswa->user;
        $this->assertNotNull($user);
        $this->assertTrue(Hash::check('Siswa@262710155', $user->password));

        // Verify Active Enrollment and Homeroom Snapshot
        $enrollment = $siswa->activeEnrollment;
        $this->assertNotNull($enrollment);
        $this->assertEquals('2026/2027', $enrollment->tahun_ajaran);
        $this->assertEquals('Dra. Siti Aminah', $enrollment->wali_kelas_nama);
        $this->assertEquals('terverifikasi', $enrollment->status_verifikasi_histori);
    }

    /**
     * Skenario 4: Import siswa alumni/lulus menandai status lulus dan menyimpan tanggal kelulusan tanpa enrollment aktif.
     */
    public function test_import_alumni_records_graduation_and_historical_enrollment(): void
    {
        TahunAjaran::firstOrCreate(['nama' => '2021/2022'], [
            'tahun_mulai' => 2021, 'tahun_selesai' => 2022, 'tanggal_mulai' => '2021-07-15', 'tanggal_selesai' => '2022-06-25', 'status' => 'selesai'
        ]);
        TahunAjaran::firstOrCreate(['nama' => '2023/2024'], [
            'tahun_mulai' => 2023, 'tahun_selesai' => 2024, 'tanggal_mulai' => '2023-07-15', 'tanggal_selesai' => '2024-06-25', 'status' => 'selesai'
        ]);

        $testRows = [
            [
                '0059998877', '212412099', 'Alumni Hebat', 'L', 'Bogor', '2005-03-20',
                'alumni.hebat@myskanic.test', '2021/2022', 'PPLG', '17', '12', 'XII PPLG 1',
                'lulus', '2023/2024', '2024-05-06', 'baru', '', 'Bapak Alumni', '081277778888', 'Bogor'
            ],
        ];

        $file = $this->createTestExcel($testRows);

        $previewRes = $this->actingAs($this->admin)->post(route('siswa.import.preview'), [
            'excel_file'  => $file,
            'import_type' => 'alumni',
        ]);
        $token = $previewRes->json('import_token');

        $confirmRes = $this->actingAs($this->admin)->post(route('siswa.import.confirm'), [
            'import_token' => $token,
        ]);
        $confirmRes->assertOk();

        $siswa = Siswa::where('nis', '212412099')->first();
        $this->assertNotNull($siswa);
        $this->assertEquals('lulus', $siswa->status);
        $this->assertEquals('2024-05-06', $siswa->tanggal_kelulusan->format('Y-m-d'));
        $this->assertNull($siswa->activeEnrollment); // Alumni must not have active enrollment!

        $lastEnrollment = $siswa->enrollments()->first();
        $this->assertNotNull($lastEnrollment);
        $this->assertEquals('lulus', $lastEnrollment->status_enrollment);
        $this->assertFalse($lastEnrollment->is_active);
    }

    /**
     * Skenario 5: Import menolak duplikasi NIS atau NISN yang sudah terdaftar.
     */
    public function test_import_rejects_duplicate_nisn_and_duplicate_nis(): void
    {
        // Pre-create student
        $existingUser = User::factory()->create(['role' => 'siswa']);
        Siswa::create([
            'user_id'               => $existingUser->id,
            'nisn'                  => '0089999999',
            'nis'                   => '262710999',
            'nama'                  => 'Siswa Lama Sudah Ada',
            'jenis_kelamin'         => 'L',
            'kelas_id'              => $this->kelas10Pplg1->id,
            'tahun_masuk'           => 2026,
            'tahun_ajaran_masuk_id' => $this->ta2026->id,
            'status'                => 'aktif',
        ]);

        $testRows = [
            [
                '0089999999', '262710999', 'Siswa Duplikat File', 'L', 'Bogor', '2008-01-01',
                '', '2026/2027', 'PPLG', '19', '10', 'X PPLG 1', 'aktif', '2026/2027', '', 'baru', '', '', '', ''
            ],
        ];

        $file = $this->createTestExcel($testRows);

        $response = $this->actingAs($this->admin)->post(route('siswa.import.preview'), [
            'excel_file' => $file,
        ]);

        $response->assertOk();
        $this->assertEquals(0, $response->json('valid_count'));
        $this->assertEquals(1, $response->json('error_count'));
        $this->assertStringContainsString('sudah terdaftar', $response->json('errors.0.messages.0'));
    }

    /**
     * Skenario 6: Import menolak rombel yang tidak cocok dengan jurusan.
     */
    public function test_import_rejects_mismatched_rombel_and_major(): void
    {
        $testRows = [
            [
                '0081239876', '262710777', 'Siswa Mismatch', 'L', 'Bogor', '2008-01-01',
                '', '2026/2027', 'PPLG', '19', '10', 'X ANIMASI 1', // ANIMASI rombel for PPLG major!
                'aktif', '2026/2027', '', 'baru', '', '', '', ''
            ],
        ];

        $file = $this->createTestExcel($testRows);

        $response = $this->actingAs($this->admin)->post(route('siswa.import.preview'), [
            'excel_file' => $file,
        ]);

        $response->assertOk();
        $this->assertEquals(0, $response->json('valid_count'));
        $this->assertEquals(1, $response->json('error_count'));
        $this->assertStringContainsString('bukan bagian dari jurusan', $response->json('errors.0.messages.0'));
    }

    /**
     * Skenario 7: Baris yang bermasalah tidak merusak pemrosesan baris yang valid.
     */
    public function test_invalid_rows_do_not_block_valid_rows(): void
    {
        $testRows = [
            // Row 1: Valid
            [
                '0083334441', '262710331', 'Siswa Valid Pertama', 'L', 'Bogor', '2008-01-01',
                '', '2026/2027', 'PPLG', '19', '10', 'X PPLG 1', 'aktif', '2026/2027', '', 'baru', '', '', '', ''
            ],
            // Row 2: Invalid (No NIS)
            [
                '', '', 'Siswa Tanpa NIS', 'L', 'Bogor', '2008-01-01',
                '', '2026/2027', 'PPLG', '19', '10', 'X PPLG 1', 'aktif', '2026/2027', '', 'baru', '', '', '', ''
            ],
        ];

        $file = $this->createTestExcel($testRows);

        $previewRes = $this->actingAs($this->admin)->post(route('siswa.import.preview'), [
            'excel_file' => $file,
        ]);
        $this->assertEquals(1, $previewRes->json('valid_count'));
        $this->assertEquals(1, $previewRes->json('error_count'));

        $token = $previewRes->json('import_token');
        $confirmRes = $this->actingAs($this->admin)->post(route('siswa.import.confirm'), [
            'import_token' => $token,
        ]);

        $confirmRes->assertOk();
        $this->assertEquals(1, $confirmRes->json('successful_rows'));
        $this->assertDatabaseHas('siswas', ['nis' => '262710331']);
    }

    /**
     * Skenario 8: Berkas non-excel ditolak oleh validasi.
     */
    public function test_non_excel_file_is_rejected(): void
    {
        $fakeFile = UploadedFile::fake()->create('document.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->admin)->post(route('siswa.import.preview'), [
            'excel_file' => $fakeFile,
        ]);

        $response->assertSessionHasErrors('excel_file');
    }

    /**
     * Skenario 9: Unduhan laporan hasil batch import mengembalikan file excel.
     */
    public function test_download_import_report_returns_detailed_xlsx(): void
    {
        $batch = StudentImportBatch::create([
            'file_name'       => 'test_sample.xlsx',
            'import_type'     => 'aktif',
            'total_rows'      => 5,
            'successful_rows' => 4,
            'failed_rows'     => 1,
            'status'          => 'completed',
            'error_details'   => [['nis' => '262710999', 'nama' => 'Gagal Test', 'error' => 'NIS duplikat']],
            'imported_by'     => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('siswa.import.report', $batch->id));
        $response->assertOk();
        $response->assertHeader('content-disposition', "attachment; filename=Laporan_Import_Batch_{$batch->id}.xlsx");
    }
}
