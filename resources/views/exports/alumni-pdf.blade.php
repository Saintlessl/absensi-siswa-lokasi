<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Daftar Alumni {{ $angkatan->nama_angkatan }}</title>
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
            margin-bottom: 20px;
            border-bottom: 3px solid #003EA7;
            padding-bottom: 12px;
        }

        .header h1 {
            font-size: 18px;
            font-weight: bold;
            color: #003EA7;
            margin-bottom: 3px;
        }

        .header h2 {
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 2px;
        }

        .header p {
            font-size: 10px;
            color: #64748b;
        }

        .meta {
            margin-bottom: 15px;
            font-size: 10px;
            color: #475569;
        }

        .meta table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
        }

        .meta td {
            padding: 3px 0;
            border: none;
        }

        table.data {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table.data thead th {
            background-color: #003EA7;
            color: #ffffff;
            padding: 7px 8px;
            text-align: left;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        table.data tbody td {
            padding: 6px 8px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 10px;
        }

        table.data tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
        }

        .badge-lulus {
            background-color: #ecfdf5;
            color: #047857;
        }

        .badge-tidak-lulus {
            background-color: #fff1f2;
            color: #be123c;
        }

        .badge-pindah {
            background-color: #faf5ff;
            color: #7e22ce;
        }

        .badge-keluar {
            background-color: #f1f5f9;
            color: #334155;
        }

        .footer {
            margin-top: 25px;
            text-align: right;
            font-size: 10px;
            color: #64748b;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>SMK NEGERI — BUKU INDUK DIREKTORI ALUMNI</h1>
        <h2>{{ $angkatan->nama_angkatan }} (T.A. Kelulusan {{ $angkatan->tahun_ajaran_kelulusan }})</h2>
        @if ($jurusan)
            <p>Program Keahlian: {{ $jurusan->nama_jurusan }} ({{ $jurusan->kode_jurusan }})</p>
        @else
            <p>Seluruh Program Keahlian SMK</p>
        @endif
    </div>

    <div class="meta">
        <table>
            <tr>
                <td width="15%"><strong>Kode Angkatan</strong></td>
                <td width="35%">: {{ $angkatan->kode_angkatan }}</td>
                <td width="20%"><strong>Total Dicetak</strong></td>
                <td width="30%">: {{ $students->count() }} Siswa</td>
            </tr>
            <tr>
                <td><strong>Tahun Masuk</strong></td>
                <td>: {{ $angkatan->tahun_masuk }} ({{ $angkatan->tahun_ajaran_masuk }})</td>
                <td><strong>Tanggal Cetak</strong></td>
                <td>: {{ now()->translatedFormat('d F Y, H:i') }} WIB</td>
            </tr>
        </table>
    </div>

    <table class="data">
        <thead>
            <tr>
                <th width="4%" style="text-align: center;">No</th>
                <th width="12%">NIS / NISN</th>
                <th width="24%">Nama Siswa</th>
                <th width="5%" style="text-align: center;">JK</th>
                <th width="15%">Program Keahlian</th>
                <th width="16%">Rombel Terakhir</th>
                <th width="12%">Tahun Masuk / Lulus</th>
                <th width="12%" style="text-align: center;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($students as $idx => $s)
                @php
                    $rombel = $s->rombel_terakhir;
                @endphp
                <tr>
                    <td style="text-align: center;">{{ $idx + 1 }}</td>
                    <td>
                        <strong>{{ $s->nis }}</strong><br>
                        <span style="color: #64748b; font-size: 9px;">{{ $s->nisn ?: '-' }}</span>
                    </td>
                    <td><strong>{{ $s->nama }}</strong></td>
                    <td style="text-align: center;">{{ $s->jenis_kelamin }}</td>
                    <td>{{ $rombel?->jurusan?->nama_jurusan ?? $s->kelas?->jurusan?->nama_jurusan ?? '-' }}</td>
                    <td>{{ $rombel?->nama_kelas ?? 'Belum tercatat' }}</td>
                    <td>
                        M: {{ $s->tahun_masuk ?? $angkatan->tahun_masuk }}<br>
                        L: {{ $s->tahun_lulus ?? $angkatan->tahun_lulus_rencana }}
                    </td>
                    <td style="text-align: center;">
                        <span class="badge badge-{{ str_replace('_', '-', $s->status) }}">
                            {{ $s->status_label }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 20px; color: #64748b;">
                        Tidak ada data siswa alumni yang sesuai filter.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Dicetak otomatis oleh Sistem MySkanic — {{ now()->translatedFormat('d F Y') }}</p>
    </div>
</body>

</html>
