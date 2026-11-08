<?php

namespace App\Http\Controllers;

use App\Models\StudentImportBatch;
use App\Services\StudentImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class StudentImportController extends Controller
{
    protected StudentImportService $importService;

    public function __construct(StudentImportService $importService)
    {
        $this->importService = $importService;
    }

    /**
     * Download official Excel import template.
     */
    public function downloadTemplate(): BinaryFileResponse
    {
        $path = $this->importService->generateTemplate();
        return response()->download($path, 'Template_Import_Siswa_MySkanic.xlsx')->deleteFileAfterSend(true);
    }

    /**
     * Show Student Import Wizard.
     */
    public function index()
    {
        $recentBatches = StudentImportBatch::with('user')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        return view('siswa.import', [
            'recentBatches' => $recentBatches,
        ]);
    }

    /**
     * Upload & Validate Spreadsheet (Steps 1, 2, 3).
     */
    public function preview(Request $request)
    {
        $request->validate([
            'excel_file'  => 'required|file|mimes:xlsx,xls|max:10240', // Max 10MB
            'import_type' => 'nullable|in:aktif,alumni,historis,campuran',
        ], [
            'excel_file.required' => 'Pilih file Excel yang akan diunggah.',
            'excel_file.mimes'    => 'Format file harus berupa XLSX atau XLS.',
            'excel_file.max'      => 'Ukuran file maksimal adalah 10MB.',
        ]);

        $file = $request->file('excel_file');
        $importType = $request->input('import_type', 'campuran');

        $result = $this->importService->parseAndValidate($file, Auth::user());

        // Cache the parsed valid rows using a secure token for 30 minutes
        $importToken = Str::uuid()->toString();
        Cache::put("student_import_{$importToken}", [
            'valid_rows'  => $result['valid_rows'],
            'import_type' => $importType,
            'file_name'   => $file->getClientOriginalName(),
            'user_id'     => Auth::id(),
        ], now()->addMinutes(30));

        return response()->json([
            'success'      => true,
            'import_token' => $importToken,
            'file_name'    => $result['file_name'],
            'total_rows'   => $result['total_rows'],
            'valid_count'  => $result['valid_count'],
            'error_count'  => $result['error_count'],
            'valid_rows'   => array_slice($result['valid_rows'], 0, 50), // Send first 50 rows for preview table
            'errors'       => $result['errors'],
        ]);
    }

    /**
     * Confirm & Execute Import (Steps 4 & 5).
     */
    public function confirm(Request $request)
    {
        $request->validate([
            'import_token' => 'required|string',
        ]);

        $cached = Cache::get("student_import_{$request->import_token}");
        if (!$cached) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi import telah kedaluwarsa atau tidak valid. Silakan unggah kembali file Excel Anda.',
            ], 422);
        }

        if (empty($cached['valid_rows'])) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada baris data valid yang dapat diproses.',
            ], 422);
        }

        $batch = $this->importService->executeImport(
            $cached['valid_rows'],
            $cached['import_type'] ?? 'campuran',
            Auth::user(),
            $cached['file_name'] ?? 'import_siswa.xlsx'
        );

        // Clear cache
        Cache::forget("student_import_{$request->import_token}");

        return response()->json([
            'success'          => true,
            'batch_id'         => $batch->id,
            'successful_rows'  => $batch->successful_rows,
            'failed_rows'      => $batch->failed_rows,
            'total_rows'       => $batch->total_rows,
            'status'           => $batch->status,
            'error_details'    => $batch->error_details,
            'report_url'       => route('siswa.import.report', $batch->id),
            'redirect_url'     => route('siswa'),
        ]);
    }

    /**
     * Download Report for an Import Batch (Step 6).
     */
    public function downloadReport($batchId): BinaryFileResponse
    {
        $batch = StudentImportBatch::with('user')->findOrFail($batchId);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Hasil Import');

        // Header info
        $sheet->setCellValue('A1', 'LAPORAN HASIL IMPORT DATA SISWA — MYSKANIC');
        $sheet->setCellValue('A2', "File Asal: {$batch->file_name} | Tanggal: " . $batch->created_at->format('d/m/Y H:i'));
        $sheet->setCellValue('A3', "Total: {$batch->total_rows} | Berhasil: {$batch->successful_rows} | Gagal: {$batch->failed_rows}");

        // Column Headings
        $sheet->setCellValue('A5', 'No');
        $sheet->setCellValue('B5', 'NIS');
        $sheet->setCellValue('C5', 'Nama Siswa');
        $sheet->setCellValue('D5', 'Status');
        $sheet->setCellValue('E5', 'Keterangan / Detail Error');

        $rowIdx = 6;
        $no = 1;

        if ($batch->error_details && is_array($batch->error_details)) {
            foreach ($batch->error_details as $err) {
                $sheet->setCellValue("A{$rowIdx}", $no++);
                $sheet->setCellValue("B{$rowIdx}", $err['nis'] ?? '-');
                $sheet->setCellValue("C{$rowIdx}", $err['nama'] ?? '-');
                $sheet->setCellValue("D{$rowIdx}", 'Gagal');
                $sheet->setCellValue("E{$rowIdx}", $err['error'] ?? 'Terjadi kesalahan sistem');
                $rowIdx++;
            }
        }

        if ($batch->successful_rows > 0) {
            $sheet->setCellValue("A{$rowIdx}", $no++);
            $sheet->setCellValue("B{$rowIdx}", '-');
            $sheet->setCellValue("C{$rowIdx}", 'Semua data valid lainnya');
            $sheet->setCellValue("D{$rowIdx}", 'Berhasil');
            $sheet->setCellValue("E{$rowIdx}", "{$batch->successful_rows} data siswa berhasil dibuat dan di-enroll ke sistem.");
        }

        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $tempPath = tempnam(sys_get_temp_dir(), 'myskanic_report_') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        return response()->download($tempPath, "Laporan_Import_Batch_{$batch->id}.xlsx")->deleteFileAfterSend(true);
    }
}
