<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Core Roles Definition
        $rolesData = [
            [
                'name'         => 'superadmin',
                'display_name' => 'Super Administrator',
                'description'  => 'Kontrol penuh sistem MySkanic, manajemen konfigurasi sekolah, role & hak akses tingkat master.',
                'is_system'    => true,
                'data_scope'   => 'all',
                'status'       => 'active',
            ],
            [
                'name'         => 'admin',
                'display_name' => 'Administrator',
                'description'  => 'Pengelolaan operasional sekolah, data master akademik, persetujuan dan monitoring harian.',
                'is_system'    => true,
                'data_scope'   => 'all',
                'status'       => 'active',
            ],
            [
                'name'         => 'kaprog',
                'display_name' => 'Kepala Program Keahlian',
                'description'  => 'Pengelolaan program keahlian/jurusan, monitoring siswa kejuruan, inventaris lab/bengkel & persetujuan izin.',
                'is_system'    => true,
                'data_scope'   => 'department',
                'status'       => 'active',
            ],
            [
                'name'         => 'guru',
                'display_name' => 'Guru / Tenaga Pendidik',
                'description'  => 'Pengelolaan pembelajaran, jurnal mengajar, presensi siswa kelas binaan & pengajuan izin mengajar.',
                'is_system'    => true,
                'data_scope'   => 'own',
                'status'       => 'active',
            ],
            [
                'name'         => 'siswa',
                'display_name' => 'Siswa',
                'description'  => 'Presensi harian, portofolio karya, peminjaman alat praktik & pengajuan izin ketidakhadiran.',
                'is_system'    => true,
                'data_scope'   => 'own',
                'status'       => 'active',
            ],
            [
                'name'         => 'orangtua',
                'display_name' => 'Orang Tua / Wali',
                'description'  => 'Monitoring kehadiran, perkembangan kedisiplinan dan rekap nilai akademik putra/putri.',
                'is_system'    => true,
                'data_scope'   => 'own',
                'status'       => 'active',
            ],
        ];

        $roles = [];
        foreach ($rolesData as $r) {
            $roles[$r['name']] = Role::updateOrCreate(
                ['name' => $r['name']],
                $r
            );
        }

        // 2. Granular Permissions Definition by Module
        $permissionsData = [
            // PENGATURAN & MASTER
            ['name' => 'pengaturan.view',        'display_name' => 'Melihat Home Pengaturan',           'module' => 'Pengaturan',        'description' => 'Akses dashboard ringkasan pengaturan dan konfigurasi sekolah'],
            ['name' => 'pengaturan.jam_sekolah', 'display_name' => 'Mengelola Jam Sekolah',            'module' => 'Pengaturan',        'description' => 'Mengubah batas jam masuk dan jam pulang absensi'],
            ['name' => 'pengaturan.kalender',    'display_name' => 'Mengelola Kalender Akademik',      'module' => 'Pengaturan',        'description' => 'Menambah dan mengubah hari libur/non-efektif akademik'],
            ['name' => 'pengaturan.lokasi',      'display_name' => 'Mengelola Lokasi Sekolah',         'module' => 'Pengaturan',        'description' => 'Mengubah koordinat GPS dan radius geofencing sekolah'],
            ['name' => 'pengaturan.ruangan',     'display_name' => 'Mengelola Data Ruangan',           'module' => 'Pengaturan',        'description' => 'Menambah, mengedit, dan mencetak QR ruangan fisik'],
            ['name' => 'pengaturan.pengguna',    'display_name' => 'Mengelola Akun Pengguna',          'module' => 'Pengaturan',        'description' => 'Membuat, mengedit, dan mengelola akun pengguna sistem'],
            ['name' => 'pengaturan.role',        'display_name' => 'Mengelola Role & Permission',      'module' => 'Pengaturan',        'description' => 'Mengonfigurasi peran pengguna dan granular hak akses'],

            // PERSETUJUAN IZIN
            ['name' => 'izin.view_siswa',        'display_name' => 'Melihat Pengajuan Izin Siswa',     'module' => 'Persetujuan Izin',  'description' => 'Melihat permohonan izin dan sakit yang diajukan siswa'],
            ['name' => 'izin.view_guru',         'display_name' => 'Melihat Pengajuan Izin Guru',      'module' => 'Persetujuan Izin',  'description' => 'Melihat permohonan izin dinas/sakit yang diajukan guru'],
            ['name' => 'izin.approve_siswa',     'display_name' => 'Menyetujui Izin Siswa',            'module' => 'Persetujuan Izin',  'description' => 'Memberikan persetujuan izin siswa dan update absensi'],
            ['name' => 'izin.reject_siswa',      'display_name' => 'Menolak Izin Siswa',               'module' => 'Persetujuan Izin',  'description' => 'Menolak permohonan izin siswa dengan catatan'],
            ['name' => 'izin.approve_guru',      'display_name' => 'Menyetujui Izin Guru',             'module' => 'Persetujuan Izin',  'description' => 'Memberikan persetujuan izin guru dan update kehadiran'],
            ['name' => 'izin.reject_guru',       'display_name' => 'Menolak Izin Guru',                'module' => 'Persetujuan Izin',  'description' => 'Menolak permohonan izin guru dengan catatan'],
            ['name' => 'izin.create_guru',       'display_name' => 'Mengajukan Izin Guru',             'module' => 'Persetujuan Izin',  'description' => 'Guru dapat mengajukan izin tidak masuk mengajar'],
            ['name' => 'izin.create_siswa',      'display_name' => 'Mengajukan Izin Siswa',            'module' => 'Persetujuan Izin',  'description' => 'Siswa dapat mengajukan izin atau sakit harian'],
            ['name' => 'izin.cancel',            'display_name' => 'Membatalkan Pengajuan Izin',       'module' => 'Persetujuan Izin',  'description' => 'Membatalkan izin pribadi yang masih berstatus pending'],

            // DATA SISWA
            ['name' => 'siswa.view',             'display_name' => 'Melihat Data Siswa',               'module' => 'Data Siswa',        'description' => 'Melihat daftar biodata siswa dan detail profil'],
            ['name' => 'siswa.create',           'display_name' => 'Menambah Data Siswa',              'module' => 'Data Siswa',        'description' => 'Mendaftarkan siswa baru ke sistem'],
            ['name' => 'siswa.update',           'display_name' => 'Mengedit Data Siswa',              'module' => 'Data Siswa',        'description' => 'Mengubah data profil dan kelas siswa'],
            ['name' => 'siswa.delete',           'display_name' => 'Menghapus Data Siswa',             'module' => 'Data Siswa',        'description' => 'Menghapus data siswa dari database'],
            ['name' => 'siswa.export',           'display_name' => 'Ekspor Data Siswa',                'module' => 'Data Siswa',        'description' => 'Mengekspor daftar siswa ke format Excel / PDF'],
            ['name' => 'siswa.import',           'display_name' => 'Impor Data Siswa',                 'module' => 'Data Siswa',        'description' => 'Impor massal siswa menggunakan template Excel'],

            // DATA GURU
            ['name' => 'guru.view',              'display_name' => 'Melihat Data Guru',                'module' => 'Data Guru',         'description' => 'Melihat daftar pengajar dan rincian beban kerja'],
            ['name' => 'guru.create',            'display_name' => 'Menambah Data Guru',               'module' => 'Data Guru',         'description' => 'Menambahkan data guru dan akun pengajar baru'],
            ['name' => 'guru.update',            'display_name' => 'Mengedit Data Guru',               'module' => 'Data Guru',         'description' => 'Mengubah data guru, penugasan mapel dan wali kelas'],
            ['name' => 'guru.delete',            'display_name' => 'Menghapus Data Guru',              'module' => 'Data Guru',         'description' => 'Menghapus data guru dari sistem'],
            ['name' => 'guru.export',            'display_name' => 'Ekspor Data Guru',                 'module' => 'Data Guru',         'description' => 'Mengekspor data guru ke format dokumen'],

            // INVENTARIS
            ['name' => 'inventaris.view',        'display_name' => 'Melihat Inventaris & Alat',        'module' => 'Inventaris',        'description' => 'Melihat katalog alat, barcode unit, dan ketersediaan'],
            ['name' => 'inventaris.create',      'display_name' => 'Menambah Barang Inventaris',       'module' => 'Inventaris',        'description' => 'Menambahkan aset alat atau instrumen lab baru'],
            ['name' => 'inventaris.update',      'display_name' => 'Mengedit Data Inventaris',         'module' => 'Inventaris',        'description' => 'Mengubah spesifikasi aset dan mengelola unit fisik'],
            ['name' => 'inventaris.delete',      'display_name' => 'Menghapus Barang Inventaris',      'module' => 'Inventaris',        'description' => 'Menghapus aset yang sudah dihapusbukukan'],
            ['name' => 'inventaris.approve',     'display_name' => 'Menyetujui Peminjaman Alat',       'module' => 'Inventaris',        'description' => 'Verifikasi dan approve peminjaman alat bengkel'],
            ['name' => 'inventaris.borrow',      'display_name' => 'Mengajukan Peminjaman Alat',       'module' => 'Inventaris',        'description' => 'Pengajuan permohonan pinjam alat oleh siswa/guru'],
            ['name' => 'inventaris.return',      'display_name' => 'Memproses Pengembalian Alat',      'module' => 'Inventaris',        'description' => 'Verifikasi pengembalian alat dan cek kondisi unit'],

            // KEDISIPLINAN & BK
            ['name' => 'kedisiplinan.view',      'display_name' => 'Melihat Catatan Kedisiplinan',     'module' => 'Kedisiplinan & BK', 'description' => 'Melihat catatan pelanggaran dan poin kedisiplinan siswa'],
            ['name' => 'kedisiplinan.create',    'display_name' => 'Mencatat Pelanggaran Siswa',       'module' => 'Kedisiplinan & BK', 'description' => 'Menambah rekor pelanggaran atau prestasi siswa'],
            ['name' => 'kedisiplinan.update',    'display_name' => 'Mengedit Data Kedisiplinan',       'module' => 'Kedisiplinan & BK', 'description' => 'Mengubah data tindak lanjut dan konseling BK'],
            ['name' => 'kedisiplinan.delete',    'display_name' => 'Menghapus Catatan Kedisiplinan',   'module' => 'Kedisiplinan & BK', 'description' => 'Menghapus catatan pelanggaran siswa'],
            ['name' => 'kedisiplinan.master',    'display_name' => 'Mengelola Master Kategori BK',     'module' => 'Kedisiplinan & BK', 'description' => 'Konfigurasi jenis pelanggaran dan bobot poin'],

            // PKL / MAGANG
            ['name' => 'pkl.view',               'display_name' => 'Melihat Data PKL',                 'module' => 'PKL / Magang',      'description' => 'Melihat penempatan siswa dan monitoring PKL'],
            ['name' => 'pkl.manage',             'display_name' => 'Mengelola Penempatan & Mitra',     'module' => 'PKL / Magang',      'description' => 'Mengatur mitra DUDI dan penempatan magang siswa'],
            ['name' => 'pkl.review',             'display_name' => 'Verifikasi Logbook PKL',           'module' => 'PKL / Magang',      'description' => 'Guru pembimbing memeriksa logbook harian siswa'],

            // AKADEMIK & KELAS
            ['name' => 'akademik.view',          'display_name' => 'Melihat Struktur Akademik',        'module' => 'Akademik',          'description' => 'Melihat struktur jurusan, rombel kelas dan alumni'],
            ['name' => 'akademik.manage',        'display_name' => 'Mengelola Kenaikan Kelas & Rombel', 'module' => 'Akademik',         'description' => 'Proses kenaikan kelas massal dan pembagian rombel'],
            ['name' => 'jadwal.manage',          'display_name' => 'Mengelola Jadwal Pelajaran',       'module' => 'Akademik',          'description' => 'Menyusun alokasi jadwal pelajaran dan jam mengajar'],
            ['name' => 'jurnal.manage',          'display_name' => 'Mengelola Jurnal Mengajar',        'module' => 'Akademik',          'description' => 'Mengisi aktivitas mengajar dan presensi sesi mapel'],
            ['name' => 'nilai.manage',           'display_name' => 'Mengelola Buku Nilai Akademik',    'module' => 'Akademik',          'description' => 'Input nilai tugas, ulangan, PTS dan PAS siswa'],
        ];

        $permissionModels = [];
        foreach ($permissionsData as $p) {
            $permissionModels[$p['name']] = Permission::updateOrCreate(
                ['name' => $p['name']],
                $p
            );
        }

        // 3. Assign Default Permissions to Roles
        // Superadmin gets ALL permissions
        $allPermissionIds = array_map(fn ($p) => $p->id, $permissionModels);
        $roles['superadmin']->permissions()->sync($allPermissionIds);

        // Admin gets full operational & administrative permissions
        $adminPermissions = [
            'pengaturan.view', 'pengaturan.jam_sekolah', 'pengaturan.kalender', 'pengaturan.lokasi', 'pengaturan.ruangan',
            'izin.view_siswa', 'izin.view_guru', 'izin.approve_siswa', 'izin.reject_siswa', 'izin.approve_guru', 'izin.reject_guru',
            'siswa.view', 'siswa.create', 'siswa.update', 'siswa.delete', 'siswa.export', 'siswa.import',
            'guru.view', 'guru.create', 'guru.update', 'guru.delete', 'guru.export',
            'inventaris.view', 'inventaris.create', 'inventaris.update', 'inventaris.delete', 'inventaris.approve', 'inventaris.return',
            'kedisiplinan.view', 'kedisiplinan.create', 'kedisiplinan.update', 'kedisiplinan.delete', 'kedisiplinan.master',
            'pkl.view', 'pkl.manage', 'pkl.review',
            'akademik.view', 'akademik.manage', 'jadwal.manage', 'jurnal.manage', 'nilai.manage',
        ];
        $adminIds = array_intersect_key($permissionModels, array_flip($adminPermissions));
        $roles['admin']->permissions()->sync(array_column($adminIds, 'id'));

        // Kaprog (Kepala Program Keahlian) - department scope
        $kaprogPermissions = [
            'pengaturan.view', 'pengaturan.ruangan',
            'izin.view_siswa', 'izin.approve_siswa', 'izin.reject_siswa', 'izin.create_guru', 'izin.cancel',
            'siswa.view', 'siswa.export',
            'guru.view',
            'inventaris.view', 'inventaris.create', 'inventaris.update', 'inventaris.approve', 'inventaris.return',
            'kedisiplinan.view', 'kedisiplinan.create',
            'pkl.view', 'pkl.manage', 'pkl.review',
            'akademik.view', 'jurnal.manage', 'nilai.manage',
        ];
        $kaprogIds = array_intersect_key($permissionModels, array_flip($kaprogPermissions));
        $roles['kaprog']->permissions()->sync(array_column($kaprogIds, 'id'));

        // Guru (Tenaga Pendidik)
        $guruPermissions = [
            'izin.create_guru', 'izin.cancel', 'izin.view_siswa', 'izin.approve_siswa', 'izin.reject_siswa',
            'inventaris.view', 'inventaris.borrow',
            'kedisiplinan.view', 'kedisiplinan.create',
            'pkl.view', 'pkl.review',
            'jurnal.manage', 'nilai.manage',
        ];
        $guruIds = array_intersect_key($permissionModels, array_flip($guruPermissions));
        $roles['guru']->permissions()->sync(array_column($guruIds, 'id'));

        // Siswa
        $siswaPermissions = [
            'izin.create_siswa', 'izin.cancel',
            'inventaris.view', 'inventaris.borrow',
            'pkl.view',
        ];
        $siswaIds = array_intersect_key($permissionModels, array_flip($siswaPermissions));
        $roles['siswa']->permissions()->sync(array_column($siswaIds, 'id'));

        // Orang Tua
        $ortuPermissions = [
            'inventaris.view',
        ];
        $ortuIds = array_intersect_key($permissionModels, array_flip($ortuPermissions));
        $roles['orangtua']->permissions()->sync(array_column($ortuIds, 'id'));

        // 4. Link Existing Users to Matching Roles
        foreach ($roles as $roleName => $roleModel) {
            User::where('role', $roleName)->update(['role_id' => $roleModel->id]);
            $roleModel->flushPermissionCache();
        }
    }
}
