<?php

namespace Database\Seeders;

use App\Models\Guru;
use App\Models\HomeroomAssignment;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\OrangTua;
use App\Models\Siswa;
use App\Models\SiswaFaceBiometric;
use App\Models\TeacherSubjectAssignment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Superadmin
        User::updateOrCreate(
            ['email' => 'superadmin@gmail.com'],
            [
                'name'     => 'Super Administrator',
                'role'     => 'superadmin',
                'password' => Hash::make('password'),
            ]
        );

        // 2. Admin Kesiswaan
        User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name'     => 'Admin Kesiswaan',
                'role'     => 'admin',
                'password' => Hash::make('password'),
            ]
        );

        // Ensure Jurusan PPLG exists
        $jurusanPplg = Jurusan::firstOrCreate(
            ['kode_jurusan' => 'PPLG'],
            [
                'nama_jurusan' => 'Pengembangan Perangkat Lunak dan Gim',
                'deskripsi'    => 'Program keahlian rekayasa perangkat lunak dan gim interaktif.',
            ]
        );

        // Ensure Kelas XI PPLG 1 exists
        $kelas = Kelas::firstOrCreate(
            ['nama_kelas' => 'XI PPLG 1'],
            [
                'tingkat'      => '11',
                'jurusan_id'   => $jurusanPplg->id,
                'tahun_ajaran' => '2026/2027',
                'jumlah_siswa' => 36,
            ]
        );

        // 3. KAPROG (Kepala Program Keahlian PPLG)
        $kaprogUser = User::updateOrCreate(
            ['email' => 'kaprog.pplg@gmail.com'],
            [
                'name'       => 'Sudjatnico S.Pd',
                'role'       => 'kaprog',
                'jurusan_id' => $jurusanPplg->id,
                'password'   => Hash::make('password'),
            ]
        );

        Guru::updateOrCreate(
            ['user_id' => $kaprogUser->id],
            [
                'nip'                => '198005122005011002',
                'nama'               => 'Sudjatnico S.Pd',
                'mapel'              => 'Pemrograman Web & Mobile',
                'jenis_kelamin'      => 'L',
                'telepon'            => '081234567890',
                'status_kepegawaian' => 'PNS',
                'is_active'          => true,
            ]
        );

        // 4. GURU (Guru Mata Pelajaran & Wali Kelas)
        $guruUser = User::updateOrCreate(
            ['email' => 'guru.budi@gmail.com'],
            [
                'name'     => 'Budi Santoso, S.Kom.',
                'role'     => 'guru',
                'password' => Hash::make('password'),
            ]
        );

        $guru = Guru::updateOrCreate(
            ['user_id' => $guruUser->id],
            [
                'nip'                => '198503152010011015',
                'nama'               => 'Budi Santoso, S.Kom.',
                'mapel'              => 'Pemrograman Berorientasi Objek',
                'jenis_kelamin'      => 'L',
                'telepon'            => '081298765432',
                'status_kepegawaian' => 'PNS',
                'is_active'          => true,
            ]
        );

        // Assign Wali Kelas
        $kelas->update(['wali_kelas' => 'Budi Santoso, S.Kom.']);

        HomeroomAssignment::updateOrCreate(
            [
                'guru_id'  => $guru->id,
                'kelas_id' => $kelas->id,
            ],
            [
                'tahun_ajaran' => '2026/2027',
                'is_active'    => true,
                'starts_on'    => now()->startOfYear(),
            ]
        );

        $mapel = MataPelajaran::firstOrCreate(
            ['nama_mapel' => 'Pemrograman Berorientasi Objek'],
            [
                'kode_mapel' => 'PBO',
                'kelompok'   => 'kejuruan',
                'is_active'  => true,
            ]
        );

        TeacherSubjectAssignment::updateOrCreate(
            [
                'guru_id'           => $guru->id,
                'kelas_id'          => $kelas->id,
                'mata_pelajaran_id' => $mapel->id,
            ],
            [
                'tahun_ajaran' => '2026/2027',
                'is_active'    => true,
                'starts_on'    => now()->startOfYear(),
            ]
        );

        // 5. SISWA
        $siswaUser = User::updateOrCreate(
            ['email' => 'siswa.rizki@gmail.com'],
            [
                'name'     => 'Rizki Pratama',
                'role'     => 'siswa',
                'password' => Hash::make('password'),
            ]
        );

        $siswa = Siswa::updateOrCreate(
            ['user_id' => $siswaUser->id],
            [
                'nis'              => '242510001',
                'nisn'             => '0087654321',
                'nama'             => 'Rizki Pratama',
                'jenis_kelamin'    => 'L',
                'kelas_id'         => $kelas->id,
                'no_telp_siswa'    => '085712345678',
                'no_telp_orangtua' => '081299887766',
                'biometric_status' => 'enrolled',
            ]
        );

        SiswaFaceBiometric::updateOrCreate(
            ['siswa_id' => $siswa->id],
            [
                'face_descriptor'   => json_encode(array_fill(0, 128, 0.05)),
                'enrollment_status' => 'enrolled',
                'is_active'         => true,
                'enrolled_at'       => now(),
            ]
        );

        // 6. ORANG TUA
        $ortuUser = User::updateOrCreate(
            ['email' => 'ortu.rizki@gmail.com'],
            [
                'name'     => 'Herman Pratama',
                'role'     => 'orangtua',
                'password' => Hash::make('password'),
            ]
        );

        OrangTua::updateOrCreate(
            ['user_id' => $ortuUser->id],
            [
                'nama'          => 'Herman Pratama',
                'jenis_kelamin' => 'L',
                'telepon'       => '081299887766',
                'alamat'        => 'Jl. Pesantren No. 45, Cimahi',
                'siswa_id'      => $siswa->id,
            ]
        );
    }
}
