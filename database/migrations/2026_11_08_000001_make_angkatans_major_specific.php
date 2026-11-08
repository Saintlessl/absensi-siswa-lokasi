<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add jurusan_id to angkatans table
        Schema::table('angkatans', function (Blueprint $table) {
            $table->foreignId('jurusan_id')
                ->nullable()
                ->after('id')
                ->constrained('jurusans')
                ->cascadeOnDelete();
        });

        // 2. Identify Jurusan IDs and backfill/seed if Jurusans exist
        if (DB::table('jurusans')->count() === 0) {
            DB::table('angkatans')->whereNull('jurusan_id')->delete();
        } else {
            $pplgId    = DB::table('jurusans')->where('kode_jurusan', 'PPLG')->value('id') ?? DB::table('jurusans')->value('id');
            $animasiId = DB::table('jurusans')->where('kode_jurusan', 'ANIMASI')->value('id');
            $tpflId    = DB::table('jurusans')->where('kode_jurusan', 'TPFL')->value('id');
            $toId      = DB::table('jurusans')->where('kode_jurusan', 'TO')->value('id');
            $bcfId     = DB::table('jurusans')->where('kode_jurusan', 'BCF')->value('id');

            // 3. Backfill existing baseline cohorts 1-6 to PPLG
            $existingPplgCohorts = DB::table('angkatans')->whereNull('jurusan_id')->get();
            foreach ($existingPplgCohorts as $c) {
                DB::table('angkatans')->where('id', $c->id)->update([
                    'jurusan_id'    => $pplgId,
                    'nama_angkatan' => "Angkatan {$c->nomor_angkatan} PPLG",
                    'kode_angkatan' => "AKT-PPLG-{$c->nomor_angkatan}",
                ]);
            }

            // 4. Seed baseline cohorts for ANIMASI (as explicitly specified in prompt: Kelas 10 on 2026/2027 is Angkatan 17)
            if ($animasiId) {
                $animasiCohorts = [
                    // Alumni
                    [
                        'jurusan_id'             => $animasiId,
                        'nomor_angkatan'         => 12,
                        'nama_angkatan'          => 'Angkatan 12 Animasi',
                        'kode_angkatan'          => 'AKT-ANIMASI-12',
                        'tahun_masuk'            => 2021,
                        'tahun_ajaran_masuk'     => '2021/2022',
                        'tahun_lulus_rencana'    => 2024,
                        'tahun_ajaran_kelulusan' => '2023/2024',
                        'status'                 => 'selesai',
                        'tanggal_kelulusan'      => '2024-05-06',
                        'keterangan'             => 'Alumni Angkatan 12 Animasi T.A. 2023/2024',
                        'created_at'             => now(),
                        'updated_at'             => now(),
                    ],
                    [
                        'jurusan_id'             => $animasiId,
                        'nomor_angkatan'         => 13,
                        'nama_angkatan'          => 'Angkatan 13 Animasi',
                        'kode_angkatan'          => 'AKT-ANIMASI-13',
                        'tahun_masuk'            => 2022,
                        'tahun_ajaran_masuk'     => '2022/2023',
                        'tahun_lulus_rencana'    => 2025,
                        'tahun_ajaran_kelulusan' => '2024/2025',
                        'status'                 => 'selesai',
                        'tanggal_kelulusan'      => '2025-05-05',
                        'keterangan'             => 'Alumni Angkatan 13 Animasi T.A. 2024/2025',
                        'created_at'             => now(),
                        'updated_at'             => now(),
                    ],
                    [
                        'jurusan_id'             => $animasiId,
                        'nomor_angkatan'         => 14,
                        'nama_angkatan'          => 'Angkatan 14 Animasi',
                        'kode_angkatan'          => 'AKT-ANIMASI-14',
                        'tahun_masuk'            => 2023,
                        'tahun_ajaran_masuk'     => '2023/2024',
                        'tahun_lulus_rencana'    => 2026,
                        'tahun_ajaran_kelulusan' => '2025/2026',
                        'status'                 => 'selesai',
                        'tanggal_kelulusan'      => '2026-05-08',
                        'keterangan'             => 'Alumni Angkatan 14 Animasi T.A. 2025/2026',
                        'created_at'             => now(),
                        'updated_at'             => now(),
                    ],
                    // Aktif
                    [
                        'jurusan_id'             => $animasiId,
                        'nomor_angkatan'         => 15,
                        'nama_angkatan'          => 'Angkatan 15 Animasi',
                        'kode_angkatan'          => 'AKT-ANIMASI-15',
                        'tahun_masuk'            => 2024,
                        'tahun_ajaran_masuk'     => '2024/2025',
                        'tahun_lulus_rencana'    => 2027,
                        'tahun_ajaran_kelulusan' => '2026/2027',
                        'status'                 => 'aktif',
                        'tanggal_kelulusan'      => null,
                        'keterangan'             => 'Angkatan Aktif Kelas XII Animasi T.A. 2026/2027',
                        'created_at'             => now(),
                        'updated_at'             => now(),
                    ],
                    [
                        'jurusan_id'             => $animasiId,
                        'nomor_angkatan'         => 16,
                        'nama_angkatan'          => 'Angkatan 16 Animasi',
                        'kode_angkatan'          => 'AKT-ANIMASI-16',
                        'tahun_masuk'            => 2025,
                        'tahun_ajaran_masuk'     => '2025/2026',
                        'tahun_lulus_rencana'    => 2028,
                        'tahun_ajaran_kelulusan' => '2027/2028',
                        'status'                 => 'aktif',
                        'tanggal_kelulusan'      => null,
                        'keterangan'             => 'Angkatan Aktif Kelas XI Animasi T.A. 2026/2027',
                        'created_at'             => now(),
                        'updated_at'             => now(),
                    ],
                    [
                        'jurusan_id'             => $animasiId,
                        'nomor_angkatan'         => 17,
                        'nama_angkatan'          => 'Angkatan 17 Animasi',
                        'kode_angkatan'          => 'AKT-ANIMASI-17',
                        'tahun_masuk'            => 2026,
                        'tahun_ajaran_masuk'     => '2026/2027',
                        'tahun_lulus_rencana'    => 2029,
                        'tahun_ajaran_kelulusan' => '2028/2029',
                        'status'                 => 'aktif',
                        'tanggal_kelulusan'      => null,
                        'keterangan'             => 'Angkatan Aktif Kelas X Animasi T.A. 2026/2027',
                        'created_at'             => now(),
                        'updated_at'             => now(),
                    ],
                ];

                DB::table('angkatans')->insert($animasiCohorts);
            }

            // 5. Seed baseline cohorts for other majors if they exist
            $otherMajors = [
                ['id' => $tpflId, 'kode' => 'TPFL', 'nama' => 'TPFL', 'offset' => 10],
                ['id' => $toId,   'kode' => 'TO',   'nama' => 'TO',   'offset' => 22],
                ['id' => $bcfId,  'kode' => 'BCF',  'nama' => 'BCF',  'offset' => 6],
            ];

            foreach ($otherMajors as $m) {
                if (!$m['id']) continue;
                $offset = $m['offset'];
                $cohorts = [
                    // Alumni (Masuk 2023 -> Lulus 2026)
                    [
                        'jurusan_id'             => $m['id'],
                        'nomor_angkatan'         => $offset - 3,
                        'nama_angkatan'          => "Angkatan " . ($offset - 3) . " {$m['kode']}",
                        'kode_angkatan'          => "AKT-{$m['kode']}-" . ($offset - 3),
                        'tahun_masuk'            => 2023,
                        'tahun_ajaran_masuk'     => '2023/2024',
                        'tahun_lulus_rencana'    => 2026,
                        'tahun_ajaran_kelulusan' => '2025/2026',
                        'status'                 => 'selesai',
                        'tanggal_kelulusan'      => '2026-05-08',
                        'keterangan'             => "Alumni Angkatan " . ($offset - 3) . " {$m['nama']}",
                        'created_at'             => now(),
                        'updated_at'             => now(),
                    ],
                    // Kelas XII (Masuk 2024)
                    [
                        'jurusan_id'             => $m['id'],
                        'nomor_angkatan'         => $offset - 2,
                        'nama_angkatan'          => "Angkatan " . ($offset - 2) . " {$m['kode']}",
                        'kode_angkatan'          => "AKT-{$m['kode']}-" . ($offset - 2),
                        'tahun_masuk'            => 2024,
                        'tahun_ajaran_masuk'     => '2024/2025',
                        'tahun_lulus_rencana'    => 2027,
                        'tahun_ajaran_kelulusan' => '2026/2027',
                        'status'                 => 'aktif',
                        'tanggal_kelulusan'      => null,
                        'keterangan'             => "Angkatan Aktif Kelas XII {$m['nama']}",
                        'created_at'             => now(),
                        'updated_at'             => now(),
                    ],
                    // Kelas XI (Masuk 2025)
                    [
                        'jurusan_id'             => $m['id'],
                        'nomor_angkatan'         => $offset - 1,
                        'nama_angkatan'          => "Angkatan " . ($offset - 1) . " {$m['kode']}",
                        'kode_angkatan'          => "AKT-{$m['kode']}-" . ($offset - 1),
                        'tahun_masuk'            => 2025,
                        'tahun_ajaran_masuk'     => '2025/2026',
                        'tahun_lulus_rencana'    => 2028,
                        'tahun_ajaran_kelulusan' => '2027/2028',
                        'status'                 => 'aktif',
                        'tanggal_kelulusan'      => null,
                        'keterangan'             => "Angkatan Aktif Kelas XI {$m['nama']}",
                        'created_at'             => now(),
                        'updated_at'             => now(),
                    ],
                    // Kelas X (Masuk 2026)
                    [
                        'jurusan_id'             => $m['id'],
                        'nomor_angkatan'         => $offset,
                        'nama_angkatan'          => "Angkatan {$offset} {$m['kode']}",
                        'kode_angkatan'          => "AKT-{$m['kode']}-{$offset}",
                        'tahun_masuk'            => 2026,
                        'tahun_ajaran_masuk'     => '2026/2027',
                        'tahun_lulus_rencana'    => 2029,
                        'tahun_ajaran_kelulusan' => '2028/2029',
                        'status'                 => 'aktif',
                        'tanggal_kelulusan'      => null,
                        'keterangan'             => "Angkatan Aktif Kelas X {$m['nama']}",
                        'created_at'             => now(),
                        'updated_at'             => now(),
                    ],
                ];

                DB::table('angkatans')->insert($cohorts);
            }
        }

        // 6. Add composite unique constraints
        Schema::table('angkatans', function (Blueprint $table) {
            $table->unique(['jurusan_id', 'nomor_angkatan'], 'angkatans_jurusan_nomor_unique');
            $table->unique(['jurusan_id', 'tahun_masuk'], 'angkatans_jurusan_tahun_masuk_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('angkatans', function (Blueprint $table) {
            $table->dropUnique('angkatans_jurusan_nomor_unique');
            $table->dropUnique('angkatans_jurusan_tahun_masuk_unique');
            $table->dropForeign(['jurusan_id']);
            $table->dropColumn('jurusan_id');
        });
    }
};
