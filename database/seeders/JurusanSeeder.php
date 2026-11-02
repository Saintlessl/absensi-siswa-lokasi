<?php

namespace Database\Seeders;

use App\Models\Jurusan;
use App\Models\Kelas;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class JurusanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jurusans = [
            [
                'kode_jurusan' => 'PPLG',
                'nama_jurusan' => 'Pengembangan Perangkat Lunak dan Gim',
                'deskripsi'    => 'Mempelajari pemrograman web, mobile, desktop, basis data, dan pengembangan gim interaktif.',
                'kelas'        => [
                    ['nama_kelas' => 'X PPLG 1', 'jumlah_siswa' => 36, 'wali_kelas' => 'Budi Santoso, S.Kom.'],
                    ['nama_kelas' => 'X PPLG 2', 'jumlah_siswa' => 36, 'wali_kelas' => 'Rina Wijaya, M.Kom.'],
                    ['nama_kelas' => 'X PPLG 3', 'jumlah_siswa' => 35, 'wali_kelas' => 'Gilang Pratama, S.Kom.'],
                    ['nama_kelas' => 'XI PPLG 1', 'jumlah_siswa' => 35, 'wali_kelas' => 'Hendra Setiawan, S.Kom.'],
                    ['nama_kelas' => 'XI PPLG 2', 'jumlah_siswa' => 35, 'wali_kelas' => 'Yuni Astuti, S.Pd.'],
                    ['nama_kelas' => 'XI PPLG 3', 'jumlah_siswa' => 34, 'wali_kelas' => 'Rizki Maulidin, M.Kom.'],
                    ['nama_kelas' => 'XII PPLG 1', 'jumlah_siswa' => 34, 'wali_kelas' => 'Ahmad Fauzi, S.Pd.'],
                    ['nama_kelas' => 'XII PPLG 2', 'jumlah_siswa' => 34, 'wali_kelas' => 'Siti Aminah, M.Kom.'],
                    ['nama_kelas' => 'XII PPLG 3', 'jumlah_siswa' => 33, 'wali_kelas' => 'Faisal Reza, S.Kom.'],
                ],
            ],
            [
                'kode_jurusan' => 'ANIMASI',
                'nama_jurusan' => 'Animasi',
                'deskripsi'    => 'Mempelajari teknik animasi 2D, 3D modelling, visual effects, character design, dan motion graphics.',
                'kelas'        => [
                    ['nama_kelas' => 'X Animasi 1', 'jumlah_siswa' => 34, 'wali_kelas' => 'Dewi Lestari, S.Sn.'],
                    ['nama_kelas' => 'X Animasi 2', 'jumlah_siswa' => 34, 'wali_kelas' => 'Bagus Saputra, M.Sn.'],
                    ['nama_kelas' => 'XI Animasi 1', 'jumlah_siswa' => 33, 'wali_kelas' => 'Fajar Pratama, S.Sn.'],
                    ['nama_kelas' => 'XI Animasi 2', 'jumlah_siswa' => 33, 'wali_kelas' => 'Nadia Putri, S.Ds.'],
                    ['nama_kelas' => 'XII Animasi 1', 'jumlah_siswa' => 32, 'wali_kelas' => 'Tri Wahyuni, M.Ds.'],
                    ['nama_kelas' => 'XII Animasi 2', 'jumlah_siswa' => 32, 'wali_kelas' => 'Surya Gemilang, S.Sn.'],
                ],
            ],
            [
                'kode_jurusan' => 'TPFL',
                'nama_jurusan' => 'Teknik Pengelasan dan Fabrikasi Logam',
                'deskripsi'    => 'Mempelajari teknik las SMAW, GMAW, GTAW, pemotongan termal, perakitan pelat, dan fabrikasi struktur baja/logam.',
                'kelas'        => [
                    ['nama_kelas' => 'X TPFL 1', 'jumlah_siswa' => 36, 'wali_kelas' => 'Agus Priyono, S.T.'],
                    ['nama_kelas' => 'X TPFL 2', 'jumlah_siswa' => 35, 'wali_kelas' => 'Darmawan Santoso, S.T.'],
                    ['nama_kelas' => 'XI TPFL 1', 'jumlah_siswa' => 35, 'wali_kelas' => 'Bambang Supriyanto, S.T.'],
                    ['nama_kelas' => 'XI TPFL 2', 'jumlah_siswa' => 34, 'wali_kelas' => 'Hardianto, S.Pd.T.'],
                    ['nama_kelas' => 'XII TPFL 1', 'jumlah_siswa' => 35, 'wali_kelas' => 'Sutrisno, S.Pd.'],
                    ['nama_kelas' => 'XII TPFL 2', 'jumlah_siswa' => 33, 'wali_kelas' => 'Sugeng Wibowo, S.T.'],
                ],
            ],
            [
                'kode_jurusan' => 'TO',
                'nama_jurusan' => 'Teknik Otomotif',
                'deskripsi'    => 'Mempelajari sistem chasis, pemindah tenaga, engine management, kelistrikan bodi, dan servis kendaraan bermotor.',
                'kelas'        => [
                    ['nama_kelas' => 'X TO 1', 'jumlah_siswa' => 36, 'wali_kelas' => 'Eko Prasetyo, S.Pd.T.'],
                    ['nama_kelas' => 'X TO 2', 'jumlah_siswa' => 36, 'wali_kelas' => 'Nanang Kurniawan, S.T.'],
                    ['nama_kelas' => 'XI TO 1', 'jumlah_siswa' => 35, 'wali_kelas' => 'Rahmat Hidayat, S.T.'],
                    ['nama_kelas' => 'XI TO 2', 'jumlah_siswa' => 35, 'wali_kelas' => 'Wahyu Hidayatullah, S.T.'],
                    ['nama_kelas' => 'XII TO 1', 'jumlah_siswa' => 34, 'wali_kelas' => 'Dwi Cahyono, S.Pd.'],
                    ['nama_kelas' => 'XII TO 2', 'jumlah_siswa' => 34, 'wali_kelas' => 'Arif Budiman, S.Pd.T.'],
                ],
            ],
            [
                'kode_jurusan' => 'BCF',
                'nama_jurusan' => 'Broadcasting dan Perfilman',
                'deskripsi'    => 'Mempelajari tata kamera, tata suara, tata cahaya, penulisan naskah, editing video, dan produksi program TV & film.',
                'kelas'        => [
                    ['nama_kelas' => 'X BCF 1', 'jumlah_siswa' => 35, 'wali_kelas' => 'Maya Anggraini, S.I.Kom.'],
                    ['nama_kelas' => 'X BCF 2', 'jumlah_siswa' => 35, 'wali_kelas' => 'Dimas Anggara, M.I.Kom.'],
                    ['nama_kelas' => 'XI BCF 1', 'jumlah_siswa' => 34, 'wali_kelas' => 'Rizky Ramadhan, M.I.Kom.'],
                    ['nama_kelas' => 'XI BCF 2', 'jumlah_siswa' => 34, 'wali_kelas' => 'Clarissa Putri, S.Sos.'],
                    ['nama_kelas' => 'XII BCF 1', 'jumlah_siswa' => 33, 'wali_kelas' => 'Siti Nurhaliza, S.Sos.'],
                    ['nama_kelas' => 'XII BCF 2', 'jumlah_siswa' => 33, 'wali_kelas' => 'Bayu Wicaksono, M.Sn.'],
                ],
            ],
        ];

        foreach ($jurusans as $data) {
            $jurusan = Jurusan::updateOrCreate(
                ['kode_jurusan' => $data['kode_jurusan']],
                [
                    'nama_jurusan' => $data['nama_jurusan'],
                    'deskripsi'    => $data['deskripsi'],
                ]
            );

            foreach ($data['kelas'] as $kelasData) {
                $existingKelas = Kelas::where('nama_kelas', $kelasData['nama_kelas'])->first();
                $qrToken = $existingKelas && $existingKelas->qr_token
                    ? $existingKelas->qr_token
                    : 'QR-' . strtoupper(Str::slug($kelasData['nama_kelas'])) . '-' . Str::random(12);

                Kelas::updateOrCreate(
                    ['nama_kelas' => $kelasData['nama_kelas']],
                    [
                        'jurusan_id'   => $jurusan->id,
                        'qr_token'     => $qrToken,
                        'jumlah_siswa' => $kelasData['jumlah_siswa'],
                        'wali_kelas'   => $kelasData['wali_kelas'],
                    ]
                );
            }
        }
    }
}
