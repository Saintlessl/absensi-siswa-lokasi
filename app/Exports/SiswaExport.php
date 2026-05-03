<?php

namespace App\Exports;

use App\Models\Siswa;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SiswaExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $search;
    protected $kelasId;
    protected $rowNumber = 0;

    public function __construct($search = '', $kelasId = '')
    {
        $this->search = $search;
        $this->kelasId = $kelasId;
    }

    public function collection()
    {
        $query = Siswa::with(['kelas', 'user']);

        if ($this->search) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'LIKE', '%' . $search . '%')
                  ->orWhere('nis', 'LIKE', '%' . $search . '%');
            });
        }

        if ($this->kelasId) {
            $query->where('kelas_id', $this->kelasId);
        }

        return $query->orderBy('nama')->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'NIS',
            'Nama Siswa',
            'Jenis Kelamin',
            'Kelas',
            'Email',
        ];
    }

    public function map($siswa): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            $siswa->nis,
            $siswa->nama,
            $siswa->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan',
            $siswa->kelas->nama_kelas ?? '-',
            $siswa->user->email ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF4472C4'],
                ],
            ],
        ];
    }

    public function title(): string
    {
        return 'Data Siswa';
    }
}
