# Laporan Audit & Multi-Role Testing — 2026-11-09

**Lingkup:** seluruh sistem, 6 role, 220 rute (98 halaman GET di-sweep per role).
**Lingkungan uji:** instance terisolasi (`APP_ENV=testing`, DB `laravel_testing`, port 8200). Database dev tidak dimutasi untuk pengujian destruktif; tidak ada data produksi yang dihapus/diubah.
**Metode:** HTTP functional testing nyata (login, CRUD, probe akses silang, export, 2FA) + PHPUnit + verifikasi silang ke skema database dan log aplikasi.

---

## 1. Role terdeteksi di kode

superadmin, admin, kaprog, guru, siswa, orangtua — seluruhnya dari `RoleMiddleware` di `routes/web.php` dan `User::role`.

## 2. Bug terkonfirmasi

### BUG-001 — Login biometrik siswa 2FA selalu HTTP 500 · CRITICAL · BELUM DIPERBAIKI
- **Endpoint:** `POST /login/verify-face/process`
- **File:** `app/Http/Controllers/LoginBiometricController.php` (baris 124, 161, 268 — argumen `'login'`), `app/Services/FaceBiometricService.php::logVerification()`; enum `session_type` di `database/migrations/2026_11_01_000002_create_absensi_verification_logs_table.php:20` = `enum('masuk','pulang','registrasi')`.
- **Repro:** login akun siswa yang sudah enrolled wajah → `/login/verify-face` → `POST /challenge` (200) → `POST /process` dengan descriptor + liveness sah → **500** `SQLSTATE[01000] Data truncated for column 'session_type'`.
- **Bukti:** error live (testing DB) + `storage/logs/laravel.log` dev (2026-11-09 22:20:43, nilai `login` ditolak enum).
- **Dampak:** siswa enrolled tidak dapat login sama sekali; jalur sukses dan gagal sama-sama crash sebelum `Auth::login`. Seluruh modul siswa (absensi QR/GPS, izin, riwayat) ikut terkunci.
- **Rekomendasi:** migration yang menambah `'login'` ke enum `session_type` (mempertahankan akurasi log), atau memetakan `'login'` → `'masuk'` di `logVerification`.

### BUG-002 — Halaman Buku Nilai 500 untuk guru & kaprog · HIGH · BELUM DIPERBAIKI
- **URL:** `GET /buku-nilai`, `GET /buku-nilai/buat`
- **File:** `app/Http/Controllers/AssessmentController.php` baris 56 & 80 — `MataPelajaran::whereHas('subjectAssignments', …)`; relasi itu milik `Guru` (`app/Models/Guru.php:50`), tidak ada di `app/Models/MataPelajaran.php` (model punya `assignments()`).
- **Bukti:** halaman error: `Call to undefined method App\Models\MataPelajaran::subjectAssignments()`. Sweep: 500 utk guru/kaprog, 200 utk superadmin.
- **Rekomendasi:** tambahkan `subjectAssignments()` di `MataPelajaran` (`hasMany(TeacherSubjectAssignment::class, 'mata_pelajaran_id')`) atau pakai relasi `assignments()` yang sudah ada.

### BUG-003 — SchoolPilotDataSeeder gagal di database fresh · HIGH (deployment) · ✅ DIPERBAIKI (commit ini)
- **File:** `database/seeders/SchoolPilotDataSeeder.php`
- **Menyebabkan:** seed kolom yang tidak ada di skema: `jurusans.bidang`, `angkatans.tahun_ajaran`, `angkatans.tahun_kelulusan`, `angkatans.is_active`, `angkatans.is_alumni`, `kelas.angkatan_id`.
- **Bukti:** `migrate:fresh` + seed → `Unknown column 'bidang'` lalu `Unknown column 'tahun_ajaran'`; di DB dev gejala tersembunyi karena `firstOrCreate` selalu match baris lama.
- **Fix:** kolom dihapus / diganti nama sebenarnya (`tahun_ajaran_masuk`, `tahun_lulus_rencana`).
- **Verifikasi:** `migrate:fresh` (DB throwaway) + seeder → **DONE**: 19 users, 12 siswa, 1 angkatan, 12 enrollment.

### BUG-004 — Pesan validasi tampil sebagai key mentah · MEDIUM · BELUM DIPERBAIKI
- **Bukti:** `POST /kelas` tanpa `wali_kelas` → toast penguna menampilkan `validation.required`.
- **Root cause:** `config/app.php:86` `locale='id'` tetapi direktori `lang/` tidak ada di repo → terjemahan fallback mengembalikan key mentah.
- **Dampak:** semua rule tanpa pesan kustom (mayoritas controller) membocorkan key internal.
- **Rekomendasi:** tambahkan `lang/id/validation.php` standar Laravel.

### BUG-005 — Inkonsistensi respons akses · LOW · BELUM DIPERBAIKI
`GET /guru-kelas` sbg superadmin/admin → 403 telanjang (`TeacherViewController:31`), padahal pola sistem = redirect + flash "Akses ditolak". Fungsi benar, UX tidak seragam.

## 3. Hasil keamanan (semua dieksekusi via HTTP nyata)

| Percobaan | Hasil |
|---|---|
| siswa → POST /siswa | 302, 0 baris tersimpan — DIBLOKIR ✓ |
| orangtua → POST /siswa, POST /user (role superadmin) | 302, 0 tersimpan ✓ |
| guru → POST /dashboard/absensi/1/delete | 302 + flash "Akses ditolak" ✓ |
| siswa → approve izin, reset biometrik admin | 302, tanpa dampak ✓ |
| anonim → halaman admin / POST /user | 302 login / 419 ✓ |
| logout POST (token segar) | sesi mati, /dashboard redirect ✓ |
| CSRF basi | halaman "Sesi berakhir" (fix 8d3f960) berfungsi ✓ |

IDOR scope data siswa/orangtua: **NOT TESTED** — terblokir BUG-001.

## 4. Fitur yang PASS terverifikasi nyata
Login + dashboard 6 role · CRUD kelas (tolak duplikat, create OK, toast error OK) · export PDF/Excel (dashboard & siswa, 4/4 file valid) · halaman import Excel / jadwal / inventaris / etalase karya 200 · proteksi role matrix.

## 5. Automated testing
`vendor/bin/phpunit`: 285 tests; 8 gagal **pre-existing** (dibuktikan `git stash`; keluarga akar BUG-003 di jalur import Excel). 11 test baru `SiswaAddNotificationTest` (8d3f960): PASS.

## 6. Belum diuji (transparansi)
Alur absensi siswa end-to-end & IDOR (BUG-001 memblokir) · jurnal mengajar · monitoring wali kelas · wizard kenaikan kelas end-to-end · notifikasi WhatsApp · UI revocation perangkat · responsivitas tablet/mobile.
