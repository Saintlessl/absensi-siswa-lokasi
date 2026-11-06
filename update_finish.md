# 📋 Update Finish — Ringkasan Revisi Sistem Absensi Siswa

> **Proyek:** Absensi Siswa SMP IT Yapura  
> **Tanggal Revisi:** 14 Mei 2026  
> **Tujuan Utama:** Migrasi dari sistem hardcoded ke sistem dinamis berbasis konfigurasi Admin, disertai penguatan logika disiplin, keamanan, dan akurasi data absensi.

---

## Daftar Isi

1. [Sentralisasi Perhitungan Alpha](#1-sentralisasi-perhitungan-alpha)
2. [Dinamisasi Parameter Keterlambatan](#2-dinamisasi-parameter-keterlambatan)
3. [Sistem SP Dinamis (SP1, SP2, SP3)](#3-sistem-sp-dinamis-sp1-sp2-sp3)
4. [Perhitungan Alpha Per Bulan](#4-perhitungan-alpha-per-bulan)
5. [Bypass Alpha untuk Siswa Baru / Pindahan](#5-bypass-alpha-untuk-siswa-baru--pindahan)
6. [Izin Hanya oleh Orang Tua](#6-izin-hanya-oleh-orang-tua)
7. [Wajib Lampiran Setelah Ambang SP](#7-wajib-lampiran-setelah-ambang-sp)
8. [Fitur Selfie Saat Absensi](#8-fitur-selfie-saat-absensi)
9. [Pencegahan Fake GPS](#9-pencegahan-fake-gps)
10. [Transparansi SP di Dashboard Siswa & Orang Tua](#10-transparansi-sp-di-dashboard-siswa--orang-tua)
11. [Akses Cetak SP untuk Siswa & Orang Tua](#11-akses-cetak-sp-untuk-siswa--orang-tua)
12. [Akurasi Rekap Per Kelas untuk Siswa Baru](#12-akurasi-rekap-per-kelas-untuk-siswa-baru)

---

## 1. Sentralisasi Perhitungan Alpha

### File Terdampak
- `app/Models/Siswa.php` — **Ditambahkan** `getAlphaCount()` dan `getTotalAlpha()`
- `app/Http/Controllers/AbsensiController.php`, `IzinController.php`, `SuratPeringatanController.php`, `DashboardController.php` — Update pemanggilan

### Sebelum (Before)

```php
// Tersebar di banyak controller, masing-masing hitung sendiri:
$alfaDb = Absensi::where('siswa_id', $siswa->id)
    ->whereMonth('tanggal', $bulan)
    ->whereRaw('LOWER(status) = ?', ['alfa'])
    ->count();
// Tidak memperhitungkan hari yang tidak diisi (missing days)
```

### Sesudah (After)

```php
// Di Model Siswa — satu sumber kebenaran:
public function getAlphaCount($month = null, $year = null)
{
    $alfaDb = $this->absensi()->whereMonth(...)->whereRaw('LOWER(status) = ?', ['alfa'])->count();
    $missingDays = max(0, $hariEfektif - $totalTercatat);
    return $alfaDb + $missingDays;
}

// Di controller, cukup panggil:
$alfaCount = $siswa->getTotalAlpha($bulan, $tahun);
```

### Argumentasi
Sebelumnya setiap controller menghitung alpha secara mandiri dengan logika berbeda. Akibatnya angka yang tampil di rekap, surat peringatan, dan izin bisa saling bertentangan. Dengan **DRY Principle**, satu method terpusat memastikan konsistensi data di seluruh sistem. **Missing days** (hari efektif tanpa catatan absensi apapun) juga sebelumnya diabaikan, membuat angka Alpha terlihat lebih kecil dari kenyataan.

---

## 2. Dinamisasi Parameter Keterlambatan

### File Terdampak
- `app/Helpers/SettingHelper.php` — `getLateThreshold()`
- `app/Http/Controllers/AbsensiController.php`, `SettingController.php`
- `resources/views/setting/index.blade.php`

### Sebelum (Before)

```php
// Hardcoded, bergantung pada jadwal per kelas:
if ($jamSekarang > $jadwalHariIni->jam_mulai) {
    $isLate = true;
}
```

### Sesudah (After)

```php
// SettingHelper:
public static function getLateThreshold(): string
{
    return self::get('late_threshold', '07:30:00');
}

// AbsensiController:
$lateThreshold = \App\Helpers\SettingHelper::getLateThreshold();
if ($jamSekarang > $lateThreshold) { $isLate = true; }
```

### Argumentasi
Waktu batas masuk adalah kebijakan sekolah yang bisa berubah (Ramadan, libur khusus, dll). Dengan parameter dinamis, Admin cukup mengubah nilai dari UI Pengaturan Sistem tanpa perlu menyentuh kode. Default fallback `07:30:00` tetap tersedia jika setting belum diisi.

---

## 3. Sistem SP Dinamis (SP1, SP2, SP3)

### File Terdampak
- `app/Helpers/SettingHelper.php` — `getAlphaQuota()`
- `app/Http/Controllers/AbsensiController.php` — Otomatisasi SP saat guru input Alfa
- `resources/views/setting/index.blade.php` — Input kuota SP1, SP2, SP3

### Sebelum (Before)

```php
// Kuota hardcoded angka 5, hanya SP1:
if ($alfaCount >= 5) {
    // Buat SP1
}
// SP2 dan SP3 tidak ada
```

### Sesudah (After)

```php
// SettingHelper:
public static function getAlphaQuota(string $sp = 'SP1'): int
{
    return (int) self::get("kuota_alpha_{$sp}", match($sp) {
        'SP1' => 5, 'SP2' => 10, 'SP3' => 15, default => 5,
    });
}

// AbsensiController::updateHarian() — otomatis saat guru input Alfa:
$spLevels = [
    'SP3' => SettingHelper::getAlphaQuota('SP3'),
    'SP2' => SettingHelper::getAlphaQuota('SP2'),
    'SP1' => SettingHelper::getAlphaQuota('SP1'),
];
foreach ($spLevels as $jenisSP => $kuota) {
    if ($alfaCount >= $kuota) {
        SuratPeringatan::firstOrCreate(['siswa_id' => ..., 'jenis_sp' => $jenisSP], [...]);
        break;
    }
}
```

### Argumentasi
Kuota 5 alpha adalah kebijakan, bukan konstanta teknis. SP2 dan SP3 adalah prosedur standar kesiswaan yang sebelumnya tidak terimplementasi. Dengan sistem bertingkat dan dinamis, Admin dapat menyesuaikan ambang batas tanpa developer, dan setiap level SP diterbitkan secara otomatis tanpa intervensi manual.

---

## 4. Perhitungan Alpha Per Bulan

### File Terdampak
- `app/Models/Siswa.php` — Logika `getTotalAlpha()`

### Sebelum (Before)

```php
public function getTotalAlpha()
{
    // Menghitung alfa dari AWAL TAHUN hingga hari ini — akumulasi sepanjang tahun
    $startOfYear = \Carbon\Carbon::now()->startOfYear();
    // Loop dari Januari sampai sekarang
    return $alfaDb + $missingDays;
}
```

### Sesudah (After)

```php
public function getTotalAlpha($month = null, $year = null)
{
    $month = $month ?? date('m');
    $year  = $year  ?? date('Y');
    return $this->getAlphaCount($month, $year); // Hanya bulan ini
}
```

### Argumentasi
Perhitungan tahunan tidak adil: siswa yang pernah alfa banyak di bulan lalu terus terbebani angka yang sama selamanya. Dengan **reset per bulan**, siswa mendapat kesempatan "lembaran baru" setiap bulan, mendorong perbaikan berkelanjutan. SP yang sudah terbit tetap ada secara historis, namun threshold tidak dihitung kumulatif dari awal tahun. Namun tindakan pemberian SP kembali lagi kepada tenaga kerja/profesional yang memutuskan.

---

## 5. Bypass Alpha untuk Siswa Baru / Pindahan

### File Terdampak
- `app/Models/Siswa.php` — `getAlphaCount()`
- `app/Http/Controllers/AbsensiController.php` — `rekapPerKelas()`

### Sebelum (Before)

```php
// Hari efektif selalu dihitung dari tanggal 1 bulan
$startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();

// Semua siswa di kelas dihitung (termasuk yang baru bergabung)
$siswaIds = Siswa::where('kelas_id', $kelas->id)->pluck('id');
```

### Sesudah (After)

```php
// Di Siswa::getAlphaCount() — mulai hitung sejak siswa terdaftar:
$registrationDate = $this->created_at
    ? \Carbon\Carbon::parse($this->created_at)->startOfDay() : null;
$startDate = \Carbon\Carbon::createFromDate($year, $month, 1)->startOfMonth();

if ($registrationDate && $registrationDate->gt($startDate)) {
    $startDate = $registrationDate->copy(); // Override ke tanggal daftar
}

// Di rekapPerKelas() — hanya siswa yang sudah ada pada tanggal tsb:
$siswaIds = Siswa::where('kelas_id', $kelas->id)
    ->whereDate('created_at', '<=', $tgl)
    ->pluck('id');
```

### Argumentasi
Ini adalah **anomali data paling kritis**: siswa pindahan yang didaftarkan 20 Mei akan dianggap alfa di hari 1-19 Mei (19 hari kerja di kurangi hari libur!), langsung memicu SP tanpa pernah absen sekalipun. Dengan bypass berbasis `created_at`, hari efektif hanya dihitung sejak siswa resmi terdaftar — fair, akurat, dan tidak membutuhkan field database tambahan.

---

## 6. Izin Hanya oleh Orang Tua

### File Terdampak
- `app/Http/Controllers/IzinController.php`

### Sebelum (Before)

```php
// Tidak ada pembatasan role — siapa saja bisa akses form izin
```

### Sesudah (After)

```php
public function index()
{
    $user = Auth::user();
    if ($user->role == 'orangtua') {
        // Proses ambil data siswa dari relasi orangtua
    } else {
        return redirect()->route('dashboard')
            ->with('error', 'Pengajuan izin hanya dapat dilakukan oleh Orang Tua.');
    }
}
```

### Argumentasi
Jika siswa bisa mengajukan izin sendiri, celah penyalahgunaan sangat besar: siswa bolos → izin sendiri → tidak ketahuan. Membatasi fitur ke role `orangtua` memastikan setiap pengajuan izin melalui filter orang tua. Ini sesuai standar prosedur kesiswaan formal dan memperkuat akuntabilitas.

---

## 7. Wajib Lampiran Setelah Ambang SP

### File Terdampak
- `app/Http/Controllers/IzinController.php`

### Sebelum (Before)

```php
$needsAttachment = $alfaCount >= $kuotaSP1;
// Hanya cek alfa bulan ini, tidak ada memori historis
```

### Sesudah (After)

```php
$alfaCount = $siswa->getTotalAlpha();
$kuotaSP1  = SettingHelper::getAlphaQuota('SP1');
$hasSP     = \App\Models\SuratPeringatan::where('siswa_id', $siswa->id)->exists();

// Wajib lampiran jika: bulan ini melewati kuota SP1 ATAU pernah dapat SP apapun
$needsAttachment = ($alfaCount >= $kuotaSP1) || $hasSP;
```

### Argumentasi
Tanpa `$hasSP`, siswa yang sudah dapat SP bisa bulan berikutnya izin bebas lampiran lagi. Pengecekan eksistensi SP memastikan aturan ketat berlaku **permanen** setelah ada rekam pelanggaran — tidak peduli berapa angka alfa di bulan berjalan. Ini mencerminkan prinsip "sekali terperingati, selalu diawasi."

---

## 8. Fitur Selfie Saat Absensi

### File Terdampak
- `app/Http/Controllers/AbsensiController.php`
- `resources/views/absensi/index.blade.php`
- Database: kolom `foto` di tabel `absensis`

### Sebelum (Before)

```php
// Hanya validasi koordinat GPS
Absensi::create(['siswa_id' => ..., 'latitude' => $lat, 'longitude' => $lng]);
// Tidak ada bukti visual
```

### Sesudah (After)

```php
$img = $request->image;
if (!$img) {
    return response()->json(['success' => false, 'message' => 'Foto selfie diperlukan.'], 400);
}
$image_base64 = base64_decode(explode(";base64,", $img)[1]);
$fileName     = $siswa->nis . "_" . date('Ymd_His') . ".png";
Storage::put("public/absensi/selfie/{$fileName}", $image_base64);

Absensi::create([...
    'foto' => $fileName,
]);
```

### Argumentasi
GPS bisa dimanipulasi, tapi foto fisik jauh lebih sulit dipalsukan. Selfie berfungsi sebagai **bukti kehadiran visual** yang dapat diaudit. Juga mencegah "titip absen" di mana HP siswa A diabsenkan oleh siswa B dari lokasi yang sama.

---

## 9. Pencegahan Fake GPS

### File Terdampak
- `app/Http/Controllers/AbsensiController.php`
- `resources/views/absensi/index.blade.php` (JS deteksi `MockLocation`)

### Sebelum (Before)

```php
// Tidak ada deteksi fake GPS — hanya validasi jarak koordinat
```

### Sesudah (After)

```php
// Frontend mengirim flag is_mocked dari Android MockLocation API
$isMocked = $request->is_mocked;

if ($isMocked) {
    return response()->json([
        'success' => false,
        'message' => 'Peringatan: Fake GPS terdeteksi! Silakan gunakan lokasi asli.'
    ], 400);
}
```

### Argumentasi
Aplikasi fake GPS mudah diunduh. Validasi jarak di backend tidak cukup jika koordinat yang dikirim sudah palsu. Flag `is_mocked` dari Android API menambah lapisan pertahanan. Meskipun tidak 100% (bisa di-bypass dengan root), ini secara signifikan **meningkatkan effort untuk curang** dan memberikan pesan yang jelas kepada siswa.

---

## 10. Transparansi SP di Dashboard Siswa & Orang Tua

### File Terdampak
- `app/Http/Controllers/DashboardController.php`
- `resources/views/dashboard/index.blade.php`

### Sebelum (Before)

```
Dashboard siswa dan orang tua tidak menampilkan informasi SP.
Orang tua hanya bisa tahu jika diberitahu guru secara manual/lisan.
```

### Sesudah (After)

```php
// DashboardController — fetch SP:
$data['suratPeringatan'] = \App\Models\SuratPeringatan::where('siswa_id', $siswa->id)
    ->orderBy('created_at', 'desc')->get();
```

```blade
{{-- Card SP muncul otomatis di dashboard jika ada SP --}}
@if (isset($suratPeringatan) && $suratPeringatan->count() > 0)
    @foreach ($suratPeringatan as $sp)
        <div class="glass-card border border-rose-200 ...">
            <span>{{ $sp->jenis_sp }}</span>
            <h4>{{ $sp->nomor_surat }}</h4>
            <a href="{{ route('surat-peringatan.cetak', $sp->id) }}">Lihat & Cetak</a>
        </div>
    @endforeach
@endif
```

### Argumentasi
Tanpa visibilitas real-time, orang tua tidak bisa proaktif merespons. SP yang hanya ada di sistem admin tidak punya nilai komunikasi. Dengan menampilkan SP di dashboard, informasi disiplin langsung tersedia bagi stakeholder yang paling relevan, mengurangi beban komunikasi manual guru.

---

## 11. Akses Cetak SP untuk Siswa & Orang Tua

### File Terdampak
- `app/Http/Controllers/SuratPeringatanController.php` — Method `cetak()`

### Sebelum (Before)

```php
// Hanya admin dan guru yang bisa cetak SP
if (!in_array($user->role, ['admin', 'guru'])) {
    return redirect()->route('dashboard')->with('error', 'Akses ditolak.');
}
```

### Sesudah (After)

```php
// Siswa hanya bisa cetak SP miliknya sendiri
if ($user->role == 'siswa') {
    $siswa = Siswa::where('user_id', $user->id)->first();
    if (!$siswa || $sp->siswa_id != $siswa->id) abort_to_dashboard();
}
// Orang tua hanya bisa cetak SP anak terhubung
elseif ($user->role == 'orangtua') {
    $orangTua = OrangTua::where('user_id', $user->id)->first();
    if (!$orangTua || $sp->siswa_id != $orangTua->siswa_id) abort_to_dashboard();
}

// Alfa yang tercantum di surat = alfa pada bulan surat diterbitkan
$month     = date('m', strtotime($sp->tanggal_terbit));
$year      = date('Y', strtotime($sp->tanggal_terbit));
$alfaCount = $sp->siswa->getTotalAlpha($month, $year);
```

### Argumentasi
Dokumen resmi yang menyangkut siswa seharusnya dapat diakses oleh yang bersangkutan. Pembatasan akses berbasis kepemilikan (`siswa_id == siswa->id`) menjaga privasi antar-siswa. Angka alfa di surat juga sekarang disesuaikan dengan bulan penerbitan (bukan bulan ini), sehingga isi surat konsisten dan dapat dipertanggungjawabkan.

---

## 12. Akurasi Rekap Per Kelas untuk Siswa Baru

### File Terdampak
- `app/Http/Controllers/AbsensiController.php` — `rekapPerKelas()`

### Sebelum (Before)

```php
// Semua siswa di kelas dihitung tanpa memperhitungkan kapan bergabung
$siswaIds   = Siswa::where('kelas_id', $kelas->id)->pluck('id');
$totalSiswa = $siswaIds->count();
// Data historis tanggal kemarin juga "30 siswa" meski siswa ke-30 baru bergabung hari ini
```

### Sesudah (After)

```php
// Hanya siswa yang sudah terdaftar pada tanggal rekap:
$siswaIds = Siswa::where('kelas_id', $kelas->id)
    ->whereDate('created_at', '<=', $tgl)
    ->pluck('id');
$totalSiswa = $siswaIds->count();
```

### Argumentasi
Laporan rekap kelas adalah fondasi laporan kepala sekolah dan Dinas Pendidikan. Data "Total Siswa" yang tidak akurat akan merusak angka kehadiran secara sistemik. Dengan filter `created_at <= $tgl`, rekap historis mencerminkan kondisi nyata pada tanggal tersebut, bukan kondisi saat ini.

---

## Ringkasan File Terdampak

| File | Tipe | Keterangan |
|------|------|------------|
| `app/Models/Siswa.php` | ✅ Baru | Sumber kebenaran tunggal alpha + bypass baru |
| `app/Helpers/SettingHelper.php` | ✅ Baru | Helper kuota SP & jam terlambat dinamis |
| `app/Http/Controllers/AbsensiController.php` | 🔄 Update | Selfie, fake GPS, SP otomatis, bypass siswa baru |
| `app/Http/Controllers/IzinController.php` | 🔄 Update | Role guard ortu, wajib lampiran permanen |
| `app/Http/Controllers/SuratPeringatanController.php` | 🔄 Update | Akses siswa/ortu, alfa per bulan di cetak |
| `app/Http/Controllers/DashboardController.php` | 🔄 Update | Tampilkan SP, pakai `getTotalAlpha()` |
| `app/Http/Controllers/SettingController.php` | 🔄 Update | Handle `late_threshold`, kuota SP1/SP2/SP3 |
| `resources/views/setting/index.blade.php` | 🔄 Update | Input kuota SP & jam masuk |
| `resources/views/dashboard/index.blade.php` | ✅ Baru | Card Surat Peringatan untuk siswa & orang tua |
| `resources/views/layouts/sidebar.blade.php` | ✅ Baru | Menu SP untuk guru & admin |

---

## Filosofi Pendekatan

Seluruh revisi dibangun di atas tiga prinsip:

1. **Dinamis > Hardcoded** — Setiap parameter operasional harus bisa dikonfigurasi Admin tanpa developer.
2. **Akurasi > Simplisitas** — Perhitungan alpha yang benar lebih penting daripada kode yang sederhana. Missing days, bypass siswa baru, dan histori registrasi semuanya dipertimbangkan.
3. **Transparansi untuk Semua Stakeholder** — Informasi disiplin harus dapat diakses oleh semua pihak yang relevan sesuai hak akses masing-masing.

---

*Dokumen ini dibuat sebagai catatan teknis revisi sistem absensi siswa SMP IT Yapura.*
