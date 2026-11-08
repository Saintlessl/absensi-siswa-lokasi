<?php

namespace App\Services;

use App\Models\Angkatan;
use App\Models\Guru;
use App\Models\HomeroomAssignment;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\SiswaStatusHistory;
use App\Models\StudentEnrollment;
use App\Models\StudentImportBatch;
use App\Models\TahunAjaran;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class StudentImportService
{
    protected StudentAcademicHistoryService $historyService;

    public function __construct(StudentAcademicHistoryService $historyService)
    {
        $this->historyService = $historyService;
    }

    /**
     * Generate official MySkanic Student Import Template (.xlsx).
     */
    public function generateTemplate(): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Import Siswa');

        // Headers
        $headers = [
            'A1' => 'NISN',
            'B1' => 'NIS',
            'C1' => 'Nama Lengkap',
            'D1' => 'Jenis Kelamin (L/P)',
            'E1' => 'Tempat Lahir',
            'F1' => 'Tanggal Lahir (YYYY-MM-DD)',
            'G1' => 'Email Siswa (Opsional)',
            'H1' => 'Tahun Ajaran Masuk (misal: 2024/2025)',
            'I1' => 'Kode Jurusan (PPLG/ANIMASI/dll)',
            'J1' => 'Nomor Angkatan (misal: 17)',
            'K1' => 'Tingkat (10/11/12)',
            'L1' => 'Nama Rombel (misal: X PPLG 1)',
            'M1' => 'Status Siswa (aktif/lulus/pindah/keluar)',
            'N1' => 'Tahun Ajaran Enrollment (misal: 2026/2027)',
            'O1' => 'Tanggal Kelulusan (YYYY-MM-DD - khusus alumni)',
            'P1' => 'Jalur Masuk (baru/pindahan)',
            'Q1' => 'Asal Sekolah (khusus pindahan)',
            'R1' => 'Nama Orang Tua / Wali',
            'S1' => 'Telepon Orang Tua',
            'T1' => 'Alamat Rumah',
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        // Header Styling (Royal Blue MySkanic #003FA9)
        $headerStyle = [
            'font' => [
                'bold'  => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size'  => 10,
                'name'  => 'Segoe UI',
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '003FA9'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'wrapText'   => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['rgb' => '002D7A'],
                ],
            ],
        ];

        $sheet->getStyle('A1:T1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(32);

        // Sample Data Rows (Row 2: Siswa Reguler, Row 3: Siswa Lama/Kelas 12, Row 4: Alumni)
        $samples = [
            [
                '0081234567', '262710001', 'Muhammad Rizki Pratama', 'L', 'Bogor', '2008-04-12',
                'rizki.pratama@myskanic.test', '2026/2027', 'PPLG', '19', '10', 'X PPLG 1',
                'aktif', '2026/2027', '', 'baru', '', 'Supardi', '081234567890', 'Ciomas, Bogor'
            ],
            [
                '0069876543', '242512088', 'Aditya Wardhana', 'L', 'Bogor', '2006-08-20',
                'aditya.w@myskanic.test', '2024/2025', 'PPLG', '17', '12', 'XII PPLG 1',
                'aktif', '2026/2027', '', 'baru', '', 'Wardhana', '081233445566', 'Ciomas, Bogor'
            ],
            [
                '0054321098', '212412015', 'Sarah Nurhaliza', 'P', 'Jakarta', '2005-02-14',
                'sarah.n@myskanic.test', '2021/2022', 'PPLG', '14', '12', 'XII PPLG 1',
                'lulus', '2023/2024', '2024-05-06', 'baru', '', 'Halim', '081299887766', 'Ciomas, Bogor'
            ],
        ];

        $rowIdx = 2;
        foreach ($samples as $sample) {
            $colLetter = 'A';
            foreach ($sample as $val) {
                // Prepend string to prevent scientific notation in Excel
                $sheet->setCellValueExplicit($colLetter . $rowIdx, $val, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $colLetter++;
            }
            $rowIdx++;
        }

        // Auto-fit column widths
        foreach (range('A', 'T') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $tempPath = tempnam(sys_get_temp_dir(), 'myskanic_tpl_') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        return $tempPath;
    }

    /**
     * Parse and validate spreadsheet content against database constraints.
     */
    public function parseAndValidate(UploadedFile $file, ?User $actor = null): array
    {
        // 1. Load spreadsheet safely
        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        if ($highestRow < 2) {
            return [
                'total_rows'      => 0,
                'valid_count'     => 0,
                'error_count'     => 1,
                'valid_rows'      => [],
                'errors'          => [['row' => 1, 'message' => 'File Excel kosong atau tidak memiliki baris data.']],
                'file_name'       => $file->getClientOriginalName(),
            ];
        }

        // Master lookup cache for high performance
        $jurusans = Jurusan::all()->keyBy(fn($j) => strtoupper($j->kode_jurusan));
        $tahunAjarans = TahunAjaran::all()->keyBy('nama');
        $activeTa = TahunAjaran::getActive() ?? TahunAjaran::first();
        $allKelas = Kelas::with('jurusan')->get();
        $existingNis = Siswa::pluck('id', 'nis')->all();
        $existingNisn = Siswa::whereNotNull('nisn')->pluck('id', 'nisn')->all();

        $seenNis = [];
        $seenNisn = [];
        $validRows = [];
        $errors = [];

        for ($row = 2; $row <= $highestRow; $row++) {
            $nisn        = trim((string) $sheet->getCell("A{$row}")->getValue());
            $nis         = trim((string) $sheet->getCell("B{$row}")->getValue());
            $nama        = trim((string) $sheet->getCell("C{$row}")->getValue());
            $jk          = strtoupper(trim((string) $sheet->getCell("D{$row}")->getValue()));
            $tempatLahir = trim((string) $sheet->getCell("E{$row}")->getValue());
            $tglLahir    = trim((string) $sheet->getCell("F{$row}")->getValue());
            $email       = trim((string) $sheet->getCell("G{$row}")->getValue());
            $taMasukNama = trim((string) $sheet->getCell("H{$row}")->getValue());
            $kodeJurusan = strtoupper(trim((string) $sheet->getCell("I{$row}")->getValue()));
            $noAngkatan  = trim((string) $sheet->getCell("J{$row}")->getValue());
            $tingkat     = trim((string) $sheet->getCell("K{$row}")->getValue());
            $namaRombel  = strtoupper(trim((string) $sheet->getCell("L{$row}")->getValue()));
            $statusSiswa = strtolower(trim((string) $sheet->getCell("M{$row}")->getValue())) ?: 'aktif';
            $taEnrNama   = trim((string) $sheet->getCell("N{$row}")->getValue()) ?: ($activeTa?->nama ?? '2026/2027');
            $tglLulus    = trim((string) $sheet->getCell("O{$row}")->getValue());
            $jalurMasuk  = strtolower(trim((string) $sheet->getCell("P{$row}")->getValue())) ?: 'baru';
            $asalSekolah = trim((string) $sheet->getCell("Q{$row}")->getValue());
            $parentName  = trim((string) $sheet->getCell("R{$row}")->getValue());
            $parentPhone = trim((string) $sheet->getCell("S{$row}")->getValue());
            $parentAddr  = trim((string) $sheet->getCell("T{$row}")->getValue());

            // Skip entirely blank rows
            if (empty($nis) && empty($nama) && empty($nisn)) {
                continue;
            }

            $rowErrors = [];

            // Prevent formula injection
            $nama = $this->sanitizeFormula($nama);
            $parentName = $this->sanitizeFormula($parentName);
            $asalSekolah = $this->sanitizeFormula($asalSekolah);

            // Mandatory fields
            if (empty($nis)) {
                $rowErrors[] = 'NIS wajib diisi.';
            }
            if (empty($nama)) {
                $rowErrors[] = 'Nama lengkap wajib diisi.';
            }
            if (!in_array($jk, ['L', 'P'])) {
                $rowErrors[] = 'Jenis kelamin harus L atau P.';
            }

            // Duplication checks
            if ($nis) {
                if (isset($existingNis[$nis])) {
                    $rowErrors[] = "NIS '{$nis}' sudah terdaftar di database.";
                }
                if (isset($seenNis[$nis])) {
                    $rowErrors[] = "NIS '{$nis}' duplikat di baris {$seenNis[$nis]}.";
                } else {
                    $seenNis[$nis] = $row;
                }
            }

            if ($nisn) {
                if (isset($existingNisn[$nisn])) {
                    $rowErrors[] = "NISN '{$nisn}' sudah terdaftar di database.";
                }
                if (isset($seenNisn[$nisn])) {
                    $rowErrors[] = "NISN '{$nisn}' duplikat di baris {$seenNisn[$nisn]}.";
                } else {
                    $seenNisn[$nisn] = $row;
                }
            }

            // Academic Year lookup
            $taMasuk = $tahunAjarans->get($taMasukNama);
            if ($taMasukNama && !$taMasuk) {
                $rowErrors[] = "Tahun ajaran masuk '{$taMasukNama}' belum terdaftar.";
            }

            $taEnr = $tahunAjarans->get($taEnrNama);
            if (!$taEnr) {
                $rowErrors[] = "Tahun ajaran enrollment '{$taEnrNama}' belum terdaftar.";
            }

            // Jurusan lookup & scoping
            $jurusan = $jurusans->get($kodeJurusan);
            if (!$jurusan) {
                $rowErrors[] = "Kode jurusan '{$kodeJurusan}' tidak ditemukan.";
            } elseif ($actor && $actor->role === 'kaprog' && $actor->jurusan_id && $actor->jurusan_id != $jurusan->id) {
                $rowErrors[] = "Anda hanya diizinkan mengimpor siswa untuk jurusan {$actor->jurusan?->kode_jurusan}.";
            }

            // Rombel lookup & coherence (prioritizing enrollment academic year)
            $matchedClasses = $allKelas->filter(function ($k) use ($namaRombel) {
                return strtoupper(trim($k->nama_kelas)) === $namaRombel;
            });

            if ($taEnr && $matchedClasses->contains(fn($k) => (int)$k->tahun_ajaran_id === (int)$taEnr->id || $k->tahun_ajaran === $taEnr->nama)) {
                $kelas = $matchedClasses->first(fn($k) => (int)$k->tahun_ajaran_id === (int)$taEnr->id || $k->tahun_ajaran === $taEnr->nama);
            } else {
                $kelas = $matchedClasses->first();
            }

            if (!$kelas) {
                $rowErrors[] = "Rombel '{$namaRombel}' tidak ditemukan.";
            } else {
                if ($jurusan && $kelas->jurusan_id != $jurusan->id) {
                    $rowErrors[] = "Rombel '{$namaRombel}' bukan bagian dari jurusan '{$kodeJurusan}'.";
                }
                if ($tingkat && $kelas->tingkat != (int) $tingkat) {
                    $rowErrors[] = "Tingkat rombel '{$namaRombel}' (Tingkat {$kelas->tingkat}) tidak cocok dengan tingkat yang dimasukkan ({$tingkat}).";
                }
            }

            // Angkatan resolution
            $angkatan = null;
            if ($jurusan) {
                if ($noAngkatan) {
                    $angkatan = Angkatan::where('jurusan_id', $jurusan->id)
                        ->where('nomor_angkatan', (int) $noAngkatan)
                        ->first();
                } elseif ($taMasuk) {
                    $angkatan = Angkatan::where('jurusan_id', $jurusan->id)
                        ->where('tahun_masuk', $taMasuk->tahun_mulai)
                        ->first();
                }
            }

            if ($rowErrors) {
                $errors[] = [
                    'row'      => $row,
                    'nis'      => $nis,
                    'nama'     => $nama,
                    'messages' => $rowErrors,
                ];
            } else {
                $validRows[] = [
                    'row_number'        => $row,
                    'nisn'              => $nisn ?: null,
                    'nis'               => $nis,
                    'nama'              => $nama,
                    'jenis_kelamin'     => $jk,
                    'tempat_lahir'      => $tempatLahir ?: null,
                    'tanggal_lahir'     => $tglLahir ? Carbon::parse($tglLahir)->format('Y-m-d') : null,
                    'email'             => $email ?: "{$nis}@myskanic.com",
                    'tahun_ajaran_masuk_id' => $taMasuk?->id ?? $taEnr->id,
                    'tahun_ajaran_masuk'    => $taMasuk?->nama ?? $taEnr->nama,
                    'tahun_masuk'       => $taMasuk?->tahun_mulai ?? $taEnr->tahun_mulai,
                    'jurusan_id'        => $jurusan->id,
                    'angkatan_id'       => $angkatan?->id,
                    'tingkat'           => (int) ($tingkat ?: ($kelas?->tingkat ?: 10)),
                    'kelas_id'          => $kelas->id,
                    'status'            => in_array($statusSiswa, ['aktif', 'lulus', 'pindah', 'keluar']) ? $statusSiswa : 'aktif',
                    'tahun_ajaran_enr_id' => $taEnr->id,
                    'tahun_ajaran_enr'  => $taEnr->nama,
                    'tanggal_kelulusan' => $tglLulus ? Carbon::parse($tglLulus)->format('Y-m-d') : null,
                    'jalur_masuk'       => $jalurMasuk === 'pindahan' ? 'pindahan' : 'baru',
                    'asal_sekolah'      => $asalSekolah ?: null,
                    'parent_name'       => $parentName ?: null,
                    'parent_phone'      => $parentPhone ?: null,
                    'parent_address'    => $parentAddr ?: null,
                ];
            }
        }

        return [
            'total_rows'  => count($validRows) + count($errors),
            'valid_count' => count($validRows),
            'error_count' => count($errors),
            'valid_rows'  => $validRows,
            'errors'      => $errors,
            'file_name'   => $file->getClientOriginalName(),
        ];
    }

    /**
     * Execute safe database transaction batch import.
     */
    public function executeImport(
        array $validRows,
        string $importType = 'campuran',
        ?User $actor = null,
        ?string $originalFileName = 'import_siswa.xlsx'
    ): StudentImportBatch {
        return DB::transaction(function () use ($validRows, $importType, $actor, $originalFileName) {
            $actorId = $actor?->id ?? Auth::id();

            // 1. Create audit batch log
            $batch = StudentImportBatch::create([
                'file_name'       => $originalFileName,
                'import_type'     => $importType,
                'total_rows'      => count($validRows),
                'successful_rows' => 0,
                'failed_rows'     => 0,
                'skipped_rows'    => 0,
                'status'          => 'processing',
                'imported_by'     => $actorId,
            ]);

            $successCount = 0;
            $failedCount = 0;
            $errorLog = [];

            foreach ($validRows as $row) {
                try {
                    // Check duplicate again inside transaction lock
                    if (Siswa::where('nis', $row['nis'])->exists()) {
                        $failedCount++;
                        $errorLog[] = ['nis' => $row['nis'], 'nama' => $row['nama'], 'error' => "NIS {$row['nis']} sudah ada di database."];
                        continue;
                    }

                    // A. Create User Account with hashed password
                    $password = Hash::make('Siswa@' . $row['nis']);
                    $user = User::create([
                        'name'                 => $row['nama'],
                        'email'                => $row['email'],
                        'password'             => $password,
                        'must_change_password' => $row['status'] === 'aktif',
                        'role'                 => 'siswa',
                        'jurusan_id'           => $row['jurusan_id'],
                        'status'               => 'active',
                    ]);

                    // B. Create Siswa Record with permanent entry identity
                    $siswa = Siswa::create([
                        'user_id'               => $user->id,
                        'nisn'                  => $row['nisn'],
                        'nis'                   => $row['nis'],
                        'nama'                  => $row['nama'],
                        'jenis_kelamin'         => $row['jenis_kelamin'],
                        'tempat_lahir'          => $row['tempat_lahir'],
                        'tanggal_lahir'         => $row['tanggal_lahir'],
                        'kelas_id'              => $row['kelas_id'],
                        'jurusan_id'            => $row['jurusan_id'],
                        'angkatan_id'           => $row['angkatan_id'],
                        'tahun_masuk'           => $row['tahun_masuk'],
                        'tahun_ajaran_masuk_id' => $row['tahun_ajaran_masuk_id'],
                        'tahun_ajaran_masuk'    => $row['tahun_ajaran_masuk'],
                        'jalur_masuk'           => $row['jalur_masuk'],
                        'asal_sekolah'          => $row['asal_sekolah'],
                        'status'                => $row['status'],
                        'tanggal_kelulusan'     => $row['tanggal_kelulusan'],
                        'tahun_lulus'           => $row['tanggal_kelulusan'] ? Carbon::parse($row['tanggal_kelulusan'])->year : null,
                        'tahun_ajaran_lulus'    => $row['status'] === 'lulus' ? $row['tahun_ajaran_enr'] : null,
                        'catatan_status'        => "Diimpor melalui file {$originalFileName} (Batch #{$batch->id})",
                    ]);

                    // C. Create Enrollment Record
                    $isAlumni = $row['status'] === 'lulus';
                    $isActive = $row['status'] === 'aktif';
                    $kelas = Kelas::find($row['kelas_id']);
                    $homeroomSnapshot = $this->historyService->resolveHomeroomSnapshot($row['kelas_id'], $row['tahun_ajaran_enr_id']);

                    $enrollment = StudentEnrollment::create([
                        'siswa_id'                  => $siswa->id,
                        'tahun_ajaran_id'           => $row['tahun_ajaran_enr_id'],
                        'tahun_ajaran'              => $row['tahun_ajaran_enr'],
                        'kelas_id'                  => $row['kelas_id'],
                        'jurusan_id'                => $row['jurusan_id'],
                        'angkatan_id'               => $row['angkatan_id'],
                        'tingkat'                   => $row['tingkat'],
                        'started_at'                => Carbon::today(),
                        'ended_at'                  => $isAlumni ? ($row['tanggal_kelulusan'] ?: Carbon::today()) : null,
                        'is_active'                 => $isActive,
                        'status_enrollment'         => $isAlumni ? 'lulus' : ($isActive ? 'aktif' : $row['status']),
                        'homeroom_assignment_id'    => $homeroomSnapshot['homeroom_assignment_id'],
                        'wali_kelas_guru_id'        => $homeroomSnapshot['wali_kelas_guru_id'],
                        'wali_kelas_nama'           => $homeroomSnapshot['wali_kelas_nama'],
                        'status_verifikasi_histori' => 'terverifikasi',
                        'catatan'                   => "Import data siswa (Batch #{$batch->id})",
                    ]);

                    // D. Record SiswaStatusHistory
                    SiswaStatusHistory::create([
                        'siswa_id'               => $siswa->id,
                        'status_sebelumnya'      => 'belum_terdaftar',
                        'status_baru'            => $row['status'],
                        'jenis_perubahan'        => $isAlumni ? 'kelulusan' : ($row['jalur_masuk'] === 'pindahan' ? 'mutasi_masuk' : 'registrasi_baru'),
                        'tanggal_perubahan'      => Carbon::today(),
                        'tahun_ajaran'           => $row['tahun_ajaran_enr'],
                        'tahun_ajaran_tujuan_id' => $row['tahun_ajaran_enr_id'],
                        'tahun_ajaran_tujuan'    => $row['tahun_ajaran_enr'],
                        'kelas_tujuan_id'        => $row['kelas_id'],
                        'wali_kelas_tujuan'      => $homeroomSnapshot['wali_kelas_nama'],
                        'alasan'                 => "Import data siswa dari {$originalFileName}",
                        'catatan'                => "Import Batch #{$batch->id}",
                        'created_by'             => $actorId,
                    ]);

                    // E. Create Orang Tua if provided
                    if (!empty($row['parent_name'])) {
                        $parentUser = User::create([
                            'name'     => $row['parent_name'],
                            'email'    => "ortu.{$row['nis']}@myskanic.com",
                            'password' => Hash::make('Ortu@' . $row['nis']),
                            'role'     => 'orangtua',
                            'status'   => 'active',
                        ]);

                        $orangTua = \App\Models\OrangTua::create([
                            'user_id'       => $parentUser->id,
                            'siswa_id'      => $siswa->id,
                            'nama'          => $row['parent_name'],
                            'telepon'       => $row['parent_phone'] ?: '-',
                            'alamat'        => $row['parent_address'] ?: '-',
                            'jenis_kelamin' => 'L',
                        ]);

                        $siswa->orangTua()->attach($orangTua->id);
                    }

                    $successCount++;
                } catch (\Exception $e) {
                    $failedCount++;
                    $errorLog[] = [
                        'nis'   => $row['nis'] ?? 'N/A',
                        'nama'  => $row['nama'] ?? ($row['nama_lengkap'] ?? 'N/A'),
                        'error' => $e->getMessage(),
                    ];
                }
            }

            // 2. Finalize audit batch
            $batch->update([
                'successful_rows' => $successCount,
                'failed_rows'     => $failedCount,
                'status'          => $failedCount > 0 && $successCount === 0 ? 'failed' : 'completed',
                'error_details'   => $errorLog ?: null,
            ]);

            return $batch;
        });
    }

    /**
     * Prevent CSV/Excel formula injection.
     */
    protected function sanitizeFormula(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);
        if (in_array(substr($trimmed, 0, 1), ['=', '+', '-', '@', "\t", "\r"])) {
            return "'" . $trimmed;
        }

        return $value;
    }
}
