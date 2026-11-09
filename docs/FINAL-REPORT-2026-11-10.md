# LAPORAN AKHIR — PKL, INVENTARIS, ROLE ACCESS, IMAGE HANDLING
Tanggal: 2026-11-10 · Branch: `master` · Remote: https://github.com/Saintlessl/absensi-siswa-lokasi

Semua klaim di bawah berbasis hasil uji nyata (PHPUnit penuh + probe HTTP). Tidak ada fitur yang diklaim PASS tanpa diuji.

## A. Fitur yang Ditambahkan

| # | Fitur | Status |
|---|-------|--------|
| 1 | CRUD Mitra DUDI (tambah/edit/hapus + konfirmasi + guard relasi + kuota) | PASS |
| 2 | CRUD Penempatan PKL (daftar/filter/tambah/edit/hapus/detail) | PASS |
| 3 | Dependent dropdown Jurusan → Rombel Kelas 12 → Siswa (AJAX + validasi backend ulang) | PASS |
| 4 | Pembatasan Kaprog per jurusan (server-side, semua endpoint + AJAX) | PASS |
| 5 | Transaksi manual inventaris oleh petugas (siswa/guru) + pengembalian manual anti double-credit + filter laporan | PASS |
| 6 | Image preview client-side + rendering anti broken-image + perbaikan storage symlink | PASS |

## B. File yang Diubah

### Mitra DUDI & Penempatan PKL (commit `ec51c06`)
- `app/Http/Controllers/PklController.php` — helper `managedJurusanId()`, scope list/store/update/destroy mitra, `validatePlacementChain()` (jurusan/rombel 12/siswa/mitra/kuota/duplikat), endpoint AJAX `optionRombel/optionSiswa/optionMitra`, scope logbook + abort 403 lintas jurusan. Alasan: backend wajib memverifikasi seluruh relasi, bukan hanya sembunyikan dropdown.
- `app/Models/PklDudiPartner.php` — fillable/casts `kuota_siswa`.
- `routes/web.php` — route PUT/DELETE mitra & penempatan + 3 route options.
- `resources/views/pkl/partners.blade.php` — modal edit (semua field incl. kuota), hapus dengan konfirmasi, tombol hanya admin/superadmin/kaprog.
- `resources/views/pkl/placements.blade.php` — alur bertingkat Jurusan→Rombel→Siswa→Mitra dengan loading/empty/error state, modal edit, hidden `capJ` untuk Kaprog.
- `tests/Feature/PklPlacementSystemTest.php` — 20 test.

### Inventaris Manual (commit `a1f747c`)
- `database/migrations/2026_11_09_163000_add_manual_transaction_fields_to_inventory_borrowings.php` — `sumber_transaksi` enum('mandiri','manual_petugas'), `dicatat_oleh` FK users, `peminjam_guru_id` FK gurus.
- `app/Models/InventoryBorrowing.php` — fillable + relasi `peminjamGuru()`, `pencatat()`.
- `app/Http/Controllers/InventoryController.php` — `manualBorrow()` (validasi jenis peminjam siswa/guru, stok atomik `lockForUpdate`, kondisi rusak tidak bisa dipinjam), `manualReturn()` (anti stok ganda, rusak_berat → quarantine maintenance), `manualOptions()`, filter laporan status/sumber/tanggal/peminjam/barang, scope Kaprog per aset jurusan.
- `routes/web.php` — `POST /peminjaman-alat/manual`, `POST /peminjaman-alat/{id}/return-manual`, `GET /peminjaman-alat/options`.
- `resources/views/inventory/borrowings.blade.php` — tombol "Transaksi Manual", modal form, badge MANUAL PETUGAS/pencatat, dropdown pengembalian manual, filter bar.
- `tests/Feature/InventoryManualTransactionTest.php` — 11 test.

### Image Handling (commit `0cd9d2f`)
- `app/helpers.php` (baru, autoload `files` di `composer.json`) — `image_url()` (URL hanya jika file nyata ada di disk public; null → caller tampilkan placeholder), `image_tag()` (fallback inisial).
- `resources/views/layouts/image-preview.blade.php` (baru) + `@include` di `layouts/app.blade.php` — preview generik `data-preview`: validasi format + ukuran, refresh saat ganti file, tidak bergantung keberhasilan DB.
- Guard `image_url()` dipasang di: `absensi/index`, `absensi/admin-biometrik`, `dashboard/index`, `portfolio/index|show|student_index`, `pkl/advisor_logbooks`, `pkl/student_portal` (foto dokumentasi PKL sebelumnya diupload tapi tidak pernah ditampilkan — kini tampil di tabel guru pembimbing dan riwayat portal siswa).
- `pkl/student_portal`, `inventory/index`, `portfolio/student_index`, `guru/show` — input file + elemen preview + pesan error.
- `public/storage` direktori real usang (hasil salinan manual, isi tidak lengkap) → dihapus, isinya dimerge ke `storage/app/public`, symlink diregenerasi `php artisan storage:link`.

## C. Perubahan Database
- `pkl_dudi_partners.kuota_siswa` (unsigned int nullable; NULL = tanpa batas).
- `inventory_borrowings`: `sumber_transaksi` enum default `mandiri`, `dicatat_oleh` FK users nullOnDelete, `peminjam_guru_id` FK gurus nullOnDelete. Semua additive, tanpa drop kolom/tabel, data lama tetap (`mandiri` via default).

## D. Permission Setelah Perubahan
- Superadmin/Admin: penuh (mitra, penempatan, transaksi manual, semua jurusan).
- Kaprog: hanya jurusan yang dipimpin — sumber otoritas `guru->activeKaprogAssignment->jurusan_id` (fallback `users.jurusan_id`), **tidak pernah** membaca `jurusan_id` dari request; list, form, AJAX options, store/update/destroy, review logbook, dan aset inventaris jurusan lain → 403.
- Guru pembimbing: hanya siswa bimbingannya (logbook/monitoring).
- Siswa: portal pribadi; tidak bisa akses transaksi manual.
- Orang tua: tidak berubah.

## E. Hasil Testing
Perintah: `php artisan test` (PHP 8.3.16, DB MySQL terisolasi via `.env.testing`).

**Seluruh suite: OK (323 tests, 1508 assertions)** — tanpa regresi.

| Area | Status |
|---|---|
| Mitra: tambah/edit/hapus bebas, hapus ditolak jika ada penempatan, duplikat, validasi kosong | PASS (PklPlacementSystemTest) |
| Rombel hanya kelas 12 jurusan terpilih; kelas 10/11 & lintas jurusan tidak muncul (server-side) | PASS |
| Siswa mismatch rombel ditolak backend; siswa sudah penempatan aktif ditolak | PASS |
| Kuota mitra penuh ditolak; edit kuota < aktif ditolak | PASS |
| Kaprog manipulasi ID jurusan/rombel/siswa/penempatan → 403/salah jurusan ditolak | PASS |
| Inventaris: pinjam manual siswa & guru, stok berkurang, riwayat tervalidasi, return menambah stok tepat sekali, return ganda ditolak, melebihi stok ditolak, petugas tercatat | PASS (InventoryManualTransactionTest) |
| Gambar: URL hanya untuk file nyata, placeholder anti broken-image, preview client terpasang, `GET /storage/...png` → **HTTP 200** | PASS (ImageHandlingTest + probe HTTP) |
| Login 2FA biometrik siswa (BUG-001) | **FAIL — belum diperbaiki** (di luar scope prompt ini, butuh approval) |

## F. Bug Ditemukan & Diselesaikan pada Tahap Ini
1. **`InvalidArgumentException` resolve ke namespace controller** — `PklController` melempar exception tanpa backslash → 500. Fix: `\InvalidArgumentException`/`\RuntimeException` + urutan catch subclass dulu. (Ditemukan via test rombel mismatch.)
2. **Foto dokumentasi PKL tidak pernah ditampilkan** — upload tersimpan tapi tidak ada view yang merender. Fix: render dengan guard `image_url()` di advisor logbook + riwayat portal siswa.
3. **`public/storage` direktori real tidak lengkap** — symlink asli hilang, diganti salinan manual; gambar baru tidak tampil (404). Fix: merge file lama, hapus direktori, `storage:link`.
4. **Race double-lending inventaris** — cek stok lama tidak atomik. Fix: `SELECT ... FOR UPDATE` di `manualBorrow()` + penolakan saat stok kurang.
5. **Potensi stok ganda saat pengembalian** — Fix: hanya `approved/borrowed` yang bisa ditutup; transaksi kedua ditolak.

Bug lama masih terbuka (butuh persetujuan, lihat `docs/AUDIT-REPORT-2026-11-09.md`): **BUG-001** (500 login biometrik, enum `session_type`), **BUG-002** (`MataPelajaran::subjectAssignments()` tidak ada), **BUG-004** (pesan validasi Inggris mentah, `lang/id` tidak ada), **BUG-005** (403 telanjang di beberapa edge case).

## G. Kesimpulan
Seluruh 7 fokus master prompt terimplementasi, teruji nyata, dan sudah ter-push ke `master`:
`ec51c06` (Mitra + Penempatan + Kaprog) → `a1f747c` (Inventaris manual) → `0cd9d2f` (Image handling).

Alur `JURUSAN → ROMBEL KELAS 12 → SISWA → MITRA → PENEMPATAN` bekerja penuh sampai database dengan validasi ganda (frontend AJAX + backend re-validation). Kaprog dibatasi server-side di semua jalur termasuk endpoint AJAX dan transaksi inventaris. Transaksi manual petugas memakai tabel `inventory_borrowings` yang sama dengan penanda sumber, pencatat, dan penanganan stok yang benar. Setiap gambar yang diinput kini dapat dilihat kembali; gambar yang file-nya tidak ada menampilkan placeholder, bukan broken image.

Pekerjaan belum selesai: BUG-001/002/004/005 menunggu persetujuan perbaikan, dan catatan: server dev di port 8000 masih instance lama bersistem PHP 8.2 (butuh restart dengan PHP 8.3 Laragon).
