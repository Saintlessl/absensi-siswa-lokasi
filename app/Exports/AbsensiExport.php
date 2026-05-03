<?php

namespace App\Exports;

use App\Models\Absensi;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AbsensiExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $bulan;
    protected $tahun;
    protected $search;
    protected $rowNumber = 0;

    public function __construct($bulan, $tahun, $search = '')
    {
        $this->bulan = $bulan;
        $this->tahun = $tahun;
        $this->search = $search;
    }

    public function collection()
    {
        $query = Absensi::with('siswa.kelas')
            ->whereMonth('tanggal', $this->bulan)
            ->whereYear('tanggal', $this->tahun)
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc');

        if ($this->search) {
            $search = $this->search;
            $query->whereHas('siswa', function ($q) use ($search) {
                $q->where('nama', 'LIKE', '%' . $search . '%')
                    ->orWhere('nis', 'LIKE', '%' . $search . '%');
            });
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal',
            'NIS',
            'Nama Siswa',
            'Kelas',
            'Status',
            'Keterangan',
        ];
    }

    public function map($absensi): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            \Carbon\Carbon::parse($absensi->tanggal)->format('d-m-Y'),
            $absensi->siswa->nis ?? '-',
            $absensi->siswa->nama ?? '-',
            $absensi->siswa->kelas->nama_kelas ?? '-',
            ucfirst($absensi->status),
            $absensi->keterangan ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 11],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF4472C4'],
                ],
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            ],
        ];
    }

    public function title(): string
    {
        $namaBulan = \Carbon\Carbon::createFromDate(null, $this->bulan, 1)->format('F');
        return "Rekap Absensi {$namaBulan} {$this->tahun}";
    }
}
