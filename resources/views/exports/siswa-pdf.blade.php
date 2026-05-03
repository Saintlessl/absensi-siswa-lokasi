<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Data Siswa</title>
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

        .badge-l {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .badge-p {
            background-color: #fce7f3;
            color: #9d174d;
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
        <h1>DATA SISWA</h1>
        <h2>Daftar Lengkap Siswa Terdaftar</h2>
        @if ($search)
            <p>Filter Pencarian: "{{ $search }}"</p>
        @endif
        @if ($filterKelas)
            <p>Kelas: {{ $filterKelas }}</p>
        @endif
    </div>

    <div class="meta">
        <p>Tanggal Cetak: <span>{{ \Carbon\Carbon::now()->isoFormat('dddd, D MMMM Y - HH:mm') }}</span></p>
        <p>Total Data: <span>{{ $siswa->count() }}</span> siswa</p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="center" style="width: 35px;">No</th>
                <th style="width: 80px;">NIS</th>
                <th>Nama Siswa</th>
                <th class="center" style="width: 55px;">L/P</th>
                <th class="center" style="width: 80px;">Kelas</th>
                <th>Email</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($siswa as $i => $s)
                <tr>
                    <td class="center">{{ $i + 1 }}</td>
                    <td>{{ $s->nis }}</td>
                    <td>{{ $s->nama }}</td>
                    <td class="center">
                        <span class="badge badge-{{ strtolower($s->jenis_kelamin) }}">
                            {{ $s->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}
                        </span>
                    </td>
                    <td class="center">{{ $s->kelas->nama_kelas ?? '-' }}</td>
                    <td>{{ $s->user->email ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 20px; color: #94a3b8;">
                        Tidak ada data siswa.
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
