<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $sp->jenis_sp }} — {{ $sp->siswa->nama ?? 'Siswa' }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', 'Segoe UI', sans-serif;
            font-size: 13px;
            color: #1e293b;
            line-height: 1.7;
            padding: 40px 60px;
            background: #fff;
        }

        .kop {
            text-align: center;
            border-bottom: 3px double #1e293b;
            padding-bottom: 16px;
            margin-bottom: 24px;
        }

        .kop h1 {
            font-size: 20px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .kop p {
            font-size: 11px;
            color: #475569;
        }

        .meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 24px;
        }

        .meta-left { font-size: 12px; }
        .meta-right { text-align: right; font-size: 12px; }

        h2 {
            text-align: center;
            font-size: 16px;
            text-decoration: underline;
            text-underline-offset: 4px;
            margin-bottom: 4px;
        }

        .sp-number {
            text-align: center;
            font-size: 12px;
            color: #64748b;
            margin-bottom: 24px;
        }

        .content { margin-bottom: 24px; }
        .content p { margin-bottom: 12px; text-align: justify; }

        table.info {
            margin: 16px 0 16px 32px;
            border-collapse: collapse;
        }

        table.info td {
            padding: 3px 8px;
            vertical-align: top;
        }

        table.info td:first-child {
            font-weight: 600;
            white-space: nowrap;
        }

        .stamp-box {
            margin-top: 48px;
            display: flex;
            justify-content: flex-end;
        }

        .stamp-box .inner {
            text-align: center;
            width: 260px;
        }

        .stamp-box .inner p {
            margin-bottom: 60px;
        }

        .stamp-box .inner .name {
            font-weight: 700;
            text-decoration: underline;
            text-underline-offset: 3px;
        }

        .stamp-box .inner .nip {
            font-size: 11px;
            color: #64748b;
        }

        .footer {
            margin-top: 48px;
            border-top: 1px solid #e2e8f0;
            padding-top: 12px;
            font-size: 10px;
            color: #94a3b8;
            text-align: center;
        }

        @media print {
            body { padding: 20px 40px; }
            .no-print { display: none !important; }
        }

        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 24px;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.3);
            transition: all 0.2s;
        }

        .print-btn:hover { transform: translateY(-2px); }

        .badge {
            display: inline-block;
            padding: 2px 12px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 12px;
        }

        .badge-sp1 { background: #fef3c7; color: #92400e; }
        .badge-sp2 { background: #ffedd5; color: #9a3412; }
        .badge-sp3 { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>

<button class="print-btn no-print" onclick="window.print()">
    🖨️ Cetak Surat
</button>

<div class="kop">
    <h1>{{ $namaSekolah }}</h1>
    <p>Surat Peringatan Resmi — Bagian Kesiswaan</p>
</div>

<div class="meta">
    <div class="meta-left">
        <p>No. Surat: <strong>{{ $sp->nomor_surat }}</strong></p>
        <p>Perihal: <strong>Surat Peringatan {{ $sp->jenis_sp }}</strong></p>
    </div>
    <div class="meta-right">
        <p>{{ \Carbon\Carbon::parse($sp->tanggal_terbit)->isoFormat('D MMMM Y') }}</p>
    </div>
</div>

<h2>SURAT PERINGATAN ({{ $sp->jenis_sp }})</h2>
<p class="sp-number">{{ $sp->nomor_surat }}</p>

<div class="content">
    <p>Dengan ini, pihak sekolah <strong>{{ $namaSekolah }}</strong> menyampaikan Surat Peringatan
    <span class="badge {{ strtolower(str_replace('SP', 'badge-sp', $sp->jenis_sp)) }}">{{ $sp->jenis_sp }}</span>
    kepada:</p>

    <table class="info">
        <tr>
            <td>Nama Siswa</td>
            <td>:</td>
            <td>{{ $sp->siswa->nama ?? '-' }}</td>
        </tr>
        <tr>
            <td>NIS</td>
            <td>:</td>
            <td>{{ $sp->siswa->nis ?? '-' }}</td>
        </tr>
        <tr>
            <td>Kelas</td>
            <td>:</td>
            <td>{{ $sp->siswa->kelas->nama_kelas ?? '-' }}</td>
        </tr>
        <tr>
            <td>Total Alpha</td>
            <td>:</td>
            <td><strong>{{ $alfaCount }} hari</strong></td>
        </tr>
    </table>

    <p><strong>Keterangan:</strong> {{ $sp->keterangan }}</p>

    @if ($sp->jenis_sp == 'SP1')
        <p>Surat ini merupakan peringatan pertama atas tingginya angka ketidakhadiran tanpa keterangan (Alpha). Diharapkan siswa dan orang tua/wali yang bersangkutan untuk segera memperbaiki tingkat kehadiran. Apabila perilaku berlanjut, pihak sekolah akan mengeluarkan <strong>Surat Peringatan Kedua (SP2)</strong>.</p>
    @elseif ($sp->jenis_sp == 'SP2')
        <p>Surat ini merupakan peringatan kedua. Siswa yang bersangkutan sudah menerima SP1 sebelumnya. Jika angka Alpha terus bertambah tanpa alasan yang jelas, pihak sekolah berhak mengeluarkan <strong>Surat Peringatan Terakhir (SP3)</strong> yang berakibat pada sanksi administratif lebih lanjut.</p>
    @elseif ($sp->jenis_sp == 'SP3')
        <p>Surat ini merupakan <strong>peringatan terakhir</strong>. Siswa yang bersangkutan telah mendapatkan SP1 dan SP2 sebelumnya. Orang tua/wali <strong>WAJIB</strong> hadir ke sekolah untuk menghadap bagian kesiswaan guna membahas langkah selanjutnya.</p>
    @endif

    <p>Demikian surat ini dibuat untuk dapat diperhatikan dan ditindaklanjuti sebagaimana mestinya.</p>
</div>

<div class="stamp-box">
    <div class="inner">
        <p>Kepala Sekolah,</p>
        <p class="name">{{ $kepalaSekolah ?: '.....................' }}</p>
        @if ($nipKepsek)
            <p class="nip">NIP. {{ $nipKepsek }}</p>
        @endif
    </div>
</div>

<div class="footer">
    Dicetak otomatis oleh Sistem Absensi — {{ $namaSekolah }} &mdash; {{ now()->isoFormat('D MMMM Y, HH:mm') }}
</div>

</body>
</html>
