<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Rekap Absensi - {{ \Carbon\Carbon::createFromDate(null, $bulan, 1)->isoFormat('MMMM') }} {{ $tahun }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 11px;
            color: #1e293b;
            padding: 30px;
        }

        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 15px;
        }

        .header h1 {
            font-size: 20px;
            font-weight: bold;
            color: #1e3a5f;
            margin-bottom: 4px;
        }

        .header h2 {
            font-size: 14px;
            font-weight: 600;
            color: #475569;
            margin-bottom: 2px;
        }

        .header p {
            font-size: 11px;
            color: #64748b;
        }

        .meta {
            margin-bottom: 15px;
            font-size: 11px;
            color: #475569;
        }

        .meta span {
            font-weight: 600;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        thead th {
            background-color: #2563eb;
            color: #ffffff;
            padding: 8px 10px;
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }

        thead th.center {
            text-align: center;
        }

        tbody td {
            padding: 7px 10px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 11px;
        }

        tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        tbody td.center {
            text-align: center;
        }

        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 600;
        }

        .badge-hadir {
            background-color: #dcfce7;
            color: #166534;
        }

        .badge-izin {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .badge-sakit {
            background-color: #fef3c7;
            color: #92400e;
        }

        .badge-alfa {
            background-color: #fecdd3;
            color: #9f1239;
        }

        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 10px;
            color: #94a3b8;
        }

        .footer .date {
            margin-bottom: 50px;
        }

        .footer .signature {
            font-weight: 600;
            color: #1e293b;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>REKAP ABSENSI SISWA</h1>
        <h2>Periode: {{ \Carbon\Carbon::createFromDate(null, $bulan, 1)->isoFormat('MMMM') }} {{ $tahun }}</h2>
        @if ($search)
            <p>Filter: "{{ $search }}"</p>
        @endif
    </div>

    <div class="meta">
        <p>Tanggal Cetak: <span>{{ \Carbon\Carbon::now()->isoFormat('dddd, D MMMM Y - HH:mm') }}</span></p>
        <p>Total Data: <span>{{ $absensi->count() }}</span> catatan</p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="center" style="width: 35px;">No</th>
                <th style="width: 80px;">Tanggal</th>
                <th style="width: 70px;">NIS</th>
                <th>Nama Siswa</th>
                <th class="center" style="width: 70px;">Kelas</th>
                <th class="center" style="width: 65px;">Status</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($absensi as $i => $a)
                <tr>
                    <td class="center">{{ $i + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($a->tanggal)->format('d-m-Y') }}</td>
                    <td>{{ $a->siswa->nis ?? '-' }}</td>
                    <td>{{ $a->siswa->nama ?? '-' }}</td>
                    <td class="center">{{ $a->siswa->kelas->nama_kelas ?? '-' }}</td>
                    <td class="center">
                        @php $st = strtolower($a->status); @endphp
                        <span class="badge badge-{{ $st }}">{{ ucfirst($a->status) }}</span>
                    </td>
                    <td>{{ $a->keterangan ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 20px; color: #94a3b8;">
                        Tidak ada data absensi pada periode ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p class="date">{{ \Carbon\Carbon::now()->isoFormat('D MMMM Y') }}</p>
        <p class="signature">Administrator</p>
    </div>
</body>

</html>
