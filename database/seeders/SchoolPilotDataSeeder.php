<?php

namespace Database\Seeders;

use App\Models\Angkatan;
use App\Models\Guru;
use App\Models\HomeroomAssignment;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\LokasiSekolah;
use App\Models\MataPelajaran;
use App\Models\OrangTua;
use App\Models\Room;
use App\Models\RoomAssignment;
use App\Models\Siswa;
use App\Models\SiswaFaceBiometric;
use App\Models\StudentEnrollment;
use App\Models\TahunAjaran;
use App\Models\TeacherSubjectAssignment;
use App\Models\User;
use App\Services\StudentAcademicHistoryService;
use App\Services\TeacherAssignmentService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SchoolPilotDataSeeder extends Seeder
{
    /**
     * Run the database seeds for Phase 5.9 / 5.10 School Pilot.
     * Wrapped in DB::transaction to ensure atomicity and full idempotency.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $historyService = app(StudentAcademicHistoryService::class);
            $teacherService = app(TeacherAssignmentService::class);

            // 1. Geofence Sekolah (SMK Negeri 1 Ciomas)
            LokasiSekolah::updateOrCreate(
                ['id' => 1],
                [
                    'latitude'  => -6.602115,
                    'longitude' => 106.760124,
                    'radius'    => 100, // 100 meter radius
                ]
            );

            // 2. Tahun Ajaran Aktif 2026/2027 Ganjil
            $ta = TahunAjaran::firstOrCreate(
                ['nama' => '2026/2027'],
                [
                    'tahun_mulai'     => 2026,
                    'tahun_selesai'   => 2027,
                    'semester_aktif'  => 'ganjil',
                    'tanggal_mulai'   => '2026-07-15',
                    'tanggal_selesai' => '2027-06-25',
                    'is_active'       => true,
                    'status'          => 'aktif',
                    'catatan'         => '[PILOT-CIOMAS] Tahun Ajaran Aktif Pilot Operasional',
                ]
            );
            $ta->update(['is_active' => true, 'status' => 'aktif', 'semester_aktif' => 'ganjil']);

            // 3. Jurusan PPLG
            $jurusan = Jurusan::firstOrCreate(
                ['kode_jurusan' => 'PPLG'],
                [
                    'nama_jurusan' => 'Pengembangan Perangkat Lunak dan Gim',
                    'bidang'       => 'Teknologi Informasi',
                    'deskripsi'    => '[PILOT-CIOMAS] Program Keahlian Pengembangan Perangkat Lunak dan Gim',
                    'is_active'    => true,
                ]
            );

            // 4. Angkatan 19 PPLG
            $angkatan = Angkatan::firstOrCreate(
                ['tahun_masuk' => 2026, 'jurusan_id' => $jurusan->id],
                [
                    'nomor_angkatan'  => 19,
                    'nama_angkatan'   => 'Angkatan 19 PPLG',
                    'kode_angkatan'   => 'AKT-PPLG-19',
                    'tahun_ajaran'    => '2026/2027',
                    'tahun_kelulusan' => 2029,
                    'status'          => 'aktif',
                    'is_active'       => true,
                    'is_alumni'       => false,
                ]
            );

            // 5. Rombel X PPLG 1
            $kelas = Kelas::firstOrCreate(
                ['nama_kelas' => 'X PPLG 1', 'tahun_ajaran_id' => $ta->id],
                [
                    'tingkat'         => 10,
                    'nomor_rombel'    => 1,
                    'jurusan_id'      => $jurusan->id,
                    'angkatan_id'     => $angkatan->id,
                    'tahun_ajaran'    => '2026/2027',
                    'jumlah_siswa'    => 0,
                    'is_active'       => true,
                ]
            );

            // 6. Ruangan Fisik untuk Presensi QR
            $room = Room::firstOrCreate(
                ['code' => 'LAB-PPLG-01'],
                [
                    'name'         => 'Laboratorium Komputer PPLG 1',
                    'building'     => 'Gedung B',
                    'capacity'     => 36,
                    'type'         => 'lab',
                    'description'  => '[PILOT-CIOMAS] Lab Praktik PPLG Gedung B Lantai 2',
                    'qr_token'     => 'QR-LAB-PPLG-1-PERM-2026',
                    'qr_is_active' => true,
                    'is_active'    => true,
                ]
            );

            RoomAssignment::firstOrCreate(
                [
                    'room_id'      => $room->id,
                    'kelas_id'     => $kelas->id,
                    'tahun_ajaran' => '2026/2027',
                ],
                [
                    'day_of_week'  => null,
                    'start_time'   => null,
                    'end_time'     => null,
                    'starts_on'    => '2026-07-15',
                    'ends_on'      => '2027-06-25',
                    'status'       => 'active',
                ]
            );

            // 7. Akun Admin Pilot
            $adminUser = User::updateOrCreate(
                ['email' => 'admin.pilot@myskanic.test'],
                [
                    'name'                 => 'Administrator Pilot SMKN 1 Ciomas',
                    'role'                 => 'admin',
                    'password'             => Hash::make('PilotAdmin2026!'),
                    'must_change_password' => false,
                    'status'               => 'active',
                ]
            );

            // 8. Akun Kaprog PPLG
            $kaprogUser = User::updateOrCreate(
                ['email' => 'kaprog.pplg@pilot.myskanic.test'],
                [
                    'name'                 => 'Ahmad Fauzi, M.Kom.',
                    'role'                 => 'kaprog',
                    'jurusan_id'           => $jurusan->id,
                    'password'             => Hash::make('Kaprog2026!'),
                    'must_change_password' => false,
                    'status'               => 'active',
                ]
            );

            // 9. Guru 1 (Wali Kelas) & Guru 2 (Guru Mapel)
            $guru1User = User::updateOrCreate(
                ['email' => 'guru.budi@pilot.myskanic.test'],
                [
                    'name'                 => 'Budi Hermawan, S.Kom.',
                    'role'                 => 'guru',
                    'password'             => Hash::make('GuruBudi2026!'),
                    'must_change_password' => false,
                    'status'               => 'active',
                ]
            );
            $guru1 = Guru::updateOrCreate(
                ['user_id' => $guru1User->id],
                [
                    'nama'          => 'Budi Hermawan, S.Kom.',
                    'nip'           => '198501102010011011',
                    'jenis_kelamin' => 'L',
                    'telepon'       => '081288880001',
                    'alamat'        => 'Ciomas, Bogor',
                    'catatan'       => '[PILOT-CIOMAS]',
                    'is_active'     => true,
                ]
            );

            $guru2User = User::updateOrCreate(
                ['email' => 'guru.siti@pilot.myskanic.test'],
                [
                    'name'                 => 'Siti Rahmah, M.Kom.',
                    'role'                 => 'guru',
                    'password'             => Hash::make('GuruSiti2026!'),
                    'must_change_password' => false,
                    'status'               => 'active',
                ]
            );
            $guru2 = Guru::updateOrCreate(
                ['user_id' => $guru2User->id],
                [
                    'nama'          => 'Siti Rahmah, M.Kom.',
                    'nip'           => '198805202012022022',
                    'jenis_kelamin' => 'P',
                    'telepon'       => '081288880002',
                    'alamat'        => 'Kota Bogor',
                    'catatan'       => '[PILOT-CIOMAS]',
                    'is_active'     => true,
                ]
            );

            // 10. Mata Pelajaran & Penugasan Mengajar
            $mapelWeb = MataPelajaran::firstOrCreate(
                ['kode_mapel' => 'PPLG-WEB10'],
                [
                    'nama_mapel' => 'Pemrograman Web Dasar',
                    'kelompok'   => 'kejuruan',
                    'deskripsi'  => '[PILOT-CIOMAS] Kejuruan PPLG Tingkat 10',
                    'is_active'  => true,
                ]
            );
            $mapelDb = MataPelajaran::firstOrCreate(
                ['kode_mapel' => 'PPLG-BD10'],
                [
                    'nama_mapel' => 'Basis Data Relasional',
                    'kelompok'   => 'kejuruan',
                    'deskripsi'  => '[PILOT-CIOMAS] Basis Data PPLG Tingkat 10',
                    'is_active'  => true,
                ]
            );

            TeacherSubjectAssignment::updateOrCreate(
                [
                    'guru_id'           => $guru1->id,
                    'mata_pelajaran_id' => $mapelWeb->id,
                    'kelas_id'          => $kelas->id,
                    'tahun_ajaran'      => '2026/2027',
                ],
                [
                    'is_active' => true,
                    'starts_on' => '2026-07-15',
                ]
            );

            TeacherSubjectAssignment::updateOrCreate(
                [
                    'guru_id'           => $guru2->id,
                    'mata_pelajaran_id' => $mapelDb->id,
                    'kelas_id'          => $kelas->id,
                    'tahun_ajaran'      => '2026/2027',
                ],
                [
                    'is_active' => true,
                    'starts_on' => '2026-07-15',
                ]
            );

            // 11. Penugasan Wali Kelas (Guru Budi pada X PPLG 1) Idempotent
            $homeroom = HomeroomAssignment::updateOrCreate(
                [
                    'guru_id'         => $guru1->id,
                    'kelas_id'        => $kelas->id,
                    'tahun_ajaran_id' => $ta->id,
                ],
                [
                    'tahun_ajaran' => '2026/2027',
                    'starts_on'    => '2026-07-15',
                    'is_active'    => true,
                    'catatan'      => '[PILOT-CIOMAS] Wali kelas pilot X PPLG 1 2026/2027',
                ]
            );

            // 12. Daftar 12 Siswa Pilot
            $studentList = [
                // [NIS, NISN, Nama, Gender, MustChangePassword, HasBiometric]
                ['262710001', '0089100001', 'Aditya Pratama', 'L', true, false],
                ['262710002', '0089100002', 'Bayu Saputra', 'L', true, false],
                ['262710003', '0089100003', 'Citra Melani', 'P', true, false],
                ['262710004', '0089100004', 'Dewi Anggraini', 'P', true, false],
                ['262710005', '0089100005', 'Eko Prasetyo', 'L', false, true],
                ['262710006', '0089100006', 'Farhan Maulana', 'L', false, true],
                ['262710007', '0089100007', 'Gita Gutawa Putri', 'P', false, true],
                ['262710008', '0089100008', 'Hafiz Muhammad', 'L', false, true],
                ['262710009', '0089100009', 'Intan Permatasari', 'P', false, true],
                ['262710010', '0089100010', 'Joko Susilo', 'L', false, true],
                ['262710011', '0089100011', 'Kurnia Ramadhani', 'P', true, false],
                ['262710012', '0089100012', 'Lestari Wulandari', 'P', true, false],
            ];

            $createdSiswaModels = [];

            foreach ($studentList as $s) {
                [$nis, $nisn, $nama, $gender, $mustChangePass, $hasBiometric] = $s;

                $email = "{$nis}@myskanic.com";
                $u = User::updateOrCreate(
                    ['email' => $email],
                    [
                        'name'                 => $nama,
                        'role'                 => 'siswa',
                        'jurusan_id'           => $jurusan->id,
                        'password'             => Hash::make('Siswa@' . $nis),
                        'must_change_password' => $mustChangePass,
                        'status'               => 'active',
                    ]
                );

                $siswa = Siswa::updateOrCreate(
                    ['nis' => $nis],
                    [
                        'user_id'               => $u->id,
                        'nisn'                  => $nisn,
                        'nama'                  => $nama,
                        'jenis_kelamin'         => $gender,
                        'tempat_lahir'          => 'Bogor',
                        'tanggal_lahir'         => '2010-04-15',
                        'kelas_id'              => $kelas->id,
                        'angkatan_id'           => $angkatan->id,
                        'tahun_masuk'           => 2026,
                        'tahun_ajaran_masuk_id' => $ta->id,
                        'tahun_ajaran_masuk'    => '2026/2027',
                        'jalur_masuk'           => 'baru',
                        'asal_sekolah'          => 'SMP Negeri 1 Ciomas',
                        'status'                => 'aktif',
                        'catatan_status'        => '[PILOT-CIOMAS]',
                        'biometric_status'      => $hasBiometric ? 'enrolled' : 'pending',
                    ]
                );

                $createdSiswaModels[$nis] = $siswa;

                // Student Active Enrollment (Idempotent by siswa_id and tahun_ajaran_id)
                StudentEnrollment::updateOrCreate(
                    [
                        'siswa_id'        => $siswa->id,
                        'tahun_ajaran_id' => $ta->id,
                    ],
                    [
                        'tahun_ajaran'              => '2026/2027',
                        'kelas_id'                  => $kelas->id,
                        'jurusan_id'                => $jurusan->id,
                        'angkatan_id'               => $angkatan->id,
                        'tingkat'                   => 10,
                        'started_at'                => '2026-07-15',
                        'is_active'                 => true,
                        'status_enrollment'         => 'aktif',
                        'homeroom_assignment_id'    => $homeroom->id,
                        'wali_kelas_guru_id'        => $guru1->id,
                        'wali_kelas_nama'           => $guru1->nama,
                        'status_verifikasi_histori' => 'terverifikasi',
                        'catatan'                   => '[PILOT-CIOMAS] Enrollment aktif data pilot SMKN 1 Ciomas',
                    ]
                );

                // Biometric Descriptor jika sudah enrolled
                if ($hasBiometric) {
                    SiswaFaceBiometric::updateOrCreate(
                        ['siswa_id' => $siswa->id],
                        [
                            'face_descriptor'   => json_encode(array_fill(0, 128, 0.05)),
                            'sample_photo'      => 'biometrics/pilot_' . $nis . '.jpg',
                            'enrollment_status' => 'enrolled',
                            'registered_at'     => now(),
                            'enrolled_at'       => now(),
                            'is_active'         => true,
                            'quality_score'     => 98.5,
                        ]
                    );
                }
            }

            // 13. Akun Orang Tua (3 Keluarga) Idempotent
            // Ortu 1: Anak Aditya Pratama (Siswa 1)
            $ortu1User = User::updateOrCreate(
                ['email' => 'ortu.hendra@pilot.myskanic.test'],
                [
                    'name'                 => 'Hendra Supriyadi',
                    'role'                 => 'orangtua',
                    'password'             => Hash::make('OrtuHendra2026!'),
                    'must_change_password' => false,
                    'status'               => 'active',
                ]
            );
            $ortu1 = OrangTua::updateOrCreate(
                ['user_id' => $ortu1User->id],
                [
                    'nama'          => 'Hendra Supriyadi',
                    'telepon'       => '081211112222',
                    'alamat'        => 'Jl. Raya Ciomas No. 45',
                    'jenis_kelamin' => 'L',
                    'siswa_id'      => $createdSiswaModels['262710001']->id,
                ]
            );
            $ortu1->siswa()->sync([$createdSiswaModels['262710001']->id]);

            // Ortu 2: Multi-anak (Bayu Saputra & Citra Melani)
            $ortu2User = User::updateOrCreate(
                ['email' => 'ortu.maryani@pilot.myskanic.test'],
                [
                    'name'                 => 'Maryani Susanti',
                    'role'                 => 'orangtua',
                    'password'             => Hash::make('OrtuMaryani2026!'),
                    'must_change_password' => false,
                    'status'               => 'active',
                ]
            );
            $ortu2 = OrangTua::updateOrCreate(
                ['user_id' => $ortu2User->id],
                [
                    'nama'          => 'Maryani Susanti',
                    'telepon'       => '081233334444',
                    'alamat'        => 'Perum Bukit Asri Ciomas Blok C3',
                    'jenis_kelamin' => 'P',
                    'siswa_id'      => $createdSiswaModels['262710002']->id,
                ]
            );
            $ortu2->siswa()->sync([
                $createdSiswaModels['262710002']->id,
                $createdSiswaModels['262710003']->id,
            ]);

            // Ortu 3: Anak Dewi Anggraini (Siswa 4)
            $ortu3User = User::updateOrCreate(
                ['email' => 'ortu.agus@pilot.myskanic.test'],
                [
                    'name'                 => 'Agus Salim',
                    'role'                 => 'orangtua',
                    'password'             => Hash::make('OrtuAgus2026!'),
                    'must_change_password' => false,
                    'status'               => 'active',
                ]
            );
            $ortu3 = OrangTua::updateOrCreate(
                ['user_id' => $ortu3User->id],
                [
                    'nama'          => 'Agus Salim',
                    'telepon'       => '081255556666',
                    'alamat'        => 'Kp. Sukamakmur RT 02 RW 05 Ciomas',
                    'jenis_kelamin' => 'L',
                    'siswa_id'      => $createdSiswaModels['262710004']->id,
                ]
            );
            $ortu3->siswa()->sync([$createdSiswaModels['262710004']->id]);

            // 14. Update jumlah siswa di kelas
            $kelas->update(['jumlah_siswa' => count($studentList)]);
        });
    }
}
