<?php

namespace Database\Seeders;

use App\Models\MataPelajaran;
use Illuminate\Database\Seeder;

class MataPelajaranSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            // Muatan Umum
            ['kode_mapel' => 'PAIBP', 'nama_mapel' => 'Pendidikan Agama dan Budi Pekerti', 'kelompok' => 'Umum', 'deskripsi' => 'Pendidikan agama dan penanaman budi pekerti.'],
            ['kode_mapel' => 'PPKN', 'nama_mapel' => 'Pendidikan Pancasila dan Kewarganegaraan', 'kelompok' => 'Umum', 'deskripsi' => 'Penguatan wawasan kebangsaan dan kewarganegaraan.'],
            ['kode_mapel' => 'BINDO', 'nama_mapel' => 'Bahasa Indonesia', 'kelompok' => 'Umum', 'deskripsi' => 'Kecakapan literasi dan komunikasi bahasa nasional.'],
            ['kode_mapel' => 'BING', 'nama_mapel' => 'Bahasa Inggris', 'kelompok' => 'Umum', 'deskripsi' => 'Komunikasi dan literasi bahasa internasional.'],
            ['kode_mapel' => 'MTK', 'nama_mapel' => 'Matematika', 'kelompok' => 'Umum', 'deskripsi' => 'Logika analitis, aljabar, dan komputasi dasar.'],
            ['kode_mapel' => 'SEJ', 'nama_mapel' => 'Sejarah Indonesia', 'kelompok' => 'Umum', 'deskripsi' => 'Pemahaman kronologi sejarah nasional.'],
            ['kode_mapel' => 'PJOK', 'nama_mapel' => 'Pendidikan Jasmani, Olahraga dan Kesehatan', 'kelompok' => 'Umum', 'deskripsi' => 'Kebugaran fisik dan kesehatan tubuh.'],
            ['kode_mapel' => 'IPAS', 'nama_mapel' => 'Projek Ilmu Pengetahuan Alam dan Sosial', 'kelompok' => 'Umum', 'deskripsi' => 'Pendekatan saintifik kontekstual kejuruan.'],
            ['kode_mapel' => 'INF', 'nama_mapel' => 'Informatika', 'kelompok' => 'Umum', 'deskripsi' => 'Fondasi computational thinking dan teknologi digital.'],

            // Kejuruan PPLG
            ['kode_mapel' => 'PPLG-DASAR', 'nama_mapel' => 'Dasar-dasar Pengembangan Perangkat Lunak & Gim', 'kelompok' => 'Kejuruan', 'deskripsi' => 'Logika pemrograman dasar dan pengenalan industri gim.'],
            ['kode_mapel' => 'PPLG-WEB', 'nama_mapel' => 'Pemrograman Web dan Perangkat Bergerak', 'kelompok' => 'Kejuruan', 'deskripsi' => 'Fullstack web development dan aplikasi mobile.'],
            ['kode_mapel' => 'PPLG-PBO', 'nama_mapel' => 'Pemrograman Berorientasi Objek', 'kelompok' => 'Kejuruan', 'deskripsi' => 'Konsep OOP, arsitektur software, dan framework backend.'],
            ['kode_mapel' => 'PPLG-DB', 'nama_mapel' => 'Basis Data & Administrasi Server', 'kelompok' => 'Kejuruan', 'deskripsi' => 'Relational database, SQL, dan manajemen data presisi.'],

            // Kejuruan Animasi
            ['kode_mapel' => 'ANI-2D', 'nama_mapel' => 'Animasi 2 Dimensi & Motion Graphic', 'kelompok' => 'Kejuruan', 'deskripsi' => 'Prinsip animasi 2D, puppet rigging, dan visual effects.'],
            ['kode_mapel' => 'ANI-3D', 'nama_mapel' => 'Animasi 3 Dimensi & Modeling', 'kelompok' => 'Kejuruan', 'deskripsi' => '3D modeling, texturing, lighting, and rendering.'],

            // Kejuruan Teknik Otomotif & Pengelasan
            ['kode_mapel' => 'TO-MESIN', 'nama_mapel' => 'Pemeliharaan Mesin Kendaraan Ringan', 'kelompok' => 'Kejuruan', 'deskripsi' => 'Engine management system, tune up, and overhauling.'],
            ['kode_mapel' => 'TPFL-LAS', 'nama_mapel' => 'Teknik Pengelasan Busur Manual (SMAW/GMAW)', 'kelompok' => 'Kejuruan', 'deskripsi' => 'Pengelasan pelat dan pipa berbagai posisi standar industri.'],

            // Muatan Lokal
            ['kode_mapel' => 'MULOK-SUNDA', 'nama_mapel' => 'Bahasa & Sastra Sunda', 'kelompok' => 'Muatan Lokal', 'deskripsi' => 'Preservasi bahasa dan kebudayaan daerah Jawa Barat.'],
            ['kode_mapel' => 'PKWU', 'nama_mapel' => 'Produk Kreatif dan Kewirausahaan (PKWU)', 'kelompok' => 'Muatan Lokal', 'deskripsi' => 'Inkubasi produk inovatif dan rintisan usaha siswa.'],
        ];

        foreach ($subjects as $s) {
            MataPelajaran::firstOrCreate(
                ['kode_mapel' => $s['kode_mapel']],
                [
                    'nama_mapel' => $s['nama_mapel'],
                    'kelompok' => $s['kelompok'],
                    'deskripsi' => $s['deskripsi'],
                    'is_active' => true,
                ]
            );
        }
    }
}
