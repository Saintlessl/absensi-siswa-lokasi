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
        // 1. Create table angkatans
        Schema::create('angkatans', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('nomor_angkatan')->nullable()->comment('Contoh: 16 (Angkatan 16)');
            $table->string('nama_angkatan', 100)->comment('Contoh: Angkatan 16 / Angkatan 2024');
            $table->string('kode_angkatan', 50)->unique()->comment('Contoh: AKT-16 / AKT-2024');
            $table->unsignedSmallInteger('tahun_masuk')->index()->comment('Tahun kalender masuk, misal 2024');
            $table->string('tahun_ajaran_masuk', 20)->comment('Contoh: 2024/2025');
            $table->unsignedSmallInteger('tahun_lulus_rencana')->comment('Tahun kelulusan normal (tahun_masuk + 3), misal 2027');
            $table->string('tahun_ajaran_kelulusan', 20)->index()->comment('Contoh: 2026/2027');
            $table->enum('status', ['aktif', 'proses_kelulusan', 'selesai'])->default('aktif')->index();
            $table->date('tanggal_kelulusan')->nullable()->comment('Tanggal resmi yudisium/kelulusan jika sudah selesai');
            $table->text('keterangan')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 2. Add angkatan and student status columns to siswas table
        Schema::table('siswas', function (Blueprint $table) {
            $table->foreignId('angkatan_id')
                ->nullable()
                ->after('kelas_id')
                ->constrained('angkatans')
                ->nullOnDelete();

            $table->unsignedSmallInteger('tahun_masuk')
                ->nullable()
                ->after('angkatan_id')
                ->index();

            $table->enum('status', [
                'aktif',
                'dalam_proses_kelulusan',
                'lulus',
                'tidak_lulus',
                'pindah',
                'keluar'
            ])->default('aktif')->after('biometric_status')->index();

            $table->date('tanggal_kelulusan')->nullable()->after('status');
            $table->unsignedSmallInteger('tahun_lulus')->nullable()->after('tanggal_kelulusan')->index();
            $table->string('tahun_ajaran_lulus', 20)->nullable()->after('tahun_lulus')->index();
            $table->text('catatan_status')->nullable()->after('tahun_ajaran_lulus');
        });

        // 3. Create siswa_status_histories for complete audit trail
        Schema::create('siswa_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswas')->cascadeOnDelete();
            $table->string('status_sebelumnya', 50)->nullable();
            $table->string('status_baru', 50)->index();
            $table->date('tanggal_perubahan')->index();
            $table->string('tahun_ajaran', 20)->nullable();
            $table->string('alasan', 255);
            $table->string('sekolah_tujuan', 255)->nullable()->comment('Khusus status pindah');
            $table->string('nomor_surat', 100)->nullable()->comment('Nomor surat keterangan/mutasi/ijazah');
            $table->text('catatan')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['siswa_id', 'tanggal_perubahan']);
        });

        // 4. Populate default cohorts & backfill existing students gracefully
        $this->seedInitialCohortsAndBackfillStudents();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('siswa_status_histories');

        Schema::table('siswas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('angkatan_id');
            $table->dropColumn([
                'tahun_masuk',
                'status',
                'tanggal_kelulusan',
                'tahun_lulus',
                'tahun_ajaran_lulus',
                'catatan_status'
            ]);
        });

        Schema::dropIfExists('angkatans');
    }

    /**
     * Seed baseline cohorts (past alumni & current active) and associate existing students.
     */
    protected function seedInitialCohortsAndBackfillStudents(): void
    {
        // Skip seeding in empty databases (e.g. testing environments without seeders)
        if (DB::table('jurusans')->count() === 0) {
            return;
        }

        $defaultCohorts = [
            [
                'nomor_angkatan'         => 14,
                'nama_angkatan'          => 'Angkatan 14',
                'kode_angkatan'          => 'AKT-14',
                'tahun_masuk'            => 2021,
                'tahun_ajaran_masuk'     => '2021/2022',
                'tahun_lulus_rencana'    => 2024,
                'tahun_ajaran_kelulusan' => '2023/2024',
                'status'                 => 'selesai',
                'tanggal_kelulusan'      => '2024-05-06',
                'keterangan'             => 'Alumni Angkatan 14 Tahun Ajaran 2023/2024',
            ],
            [
                'nomor_angkatan'         => 15,
                'nama_angkatan'          => 'Angkatan 15',
                'kode_angkatan'          => 'AKT-15',
                'tahun_masuk'            => 2022,
                'tahun_ajaran_masuk'     => '2022/2023',
                'tahun_lulus_rencana'    => 2025,
                'tahun_ajaran_kelulusan' => '2024/2025',
                'status'                 => 'selesai',
                'tanggal_kelulusan'      => '2025-05-05',
                'keterangan'             => 'Alumni Angkatan 15 Tahun Ajaran 2024/2025',
            ],
            [
                'nomor_angkatan'         => 16,
                'nama_angkatan'          => 'Angkatan 16',
                'kode_angkatan'          => 'AKT-16',
                'tahun_masuk'            => 2023,
                'tahun_ajaran_masuk'     => '2023/2024',
                'tahun_lulus_rencana'    => 2026,
                'tahun_ajaran_kelulusan' => '2025/2026',
                'status'                 => 'selesai',
                'tanggal_kelulusan'      => '2026-05-08',
                'keterangan'             => 'Alumni Angkatan 16 Tahun Ajaran 2025/2026',
            ],
            [
                'nomor_angkatan'         => 17,
                'nama_angkatan'          => 'Angkatan 17',
                'kode_angkatan'          => 'AKT-17',
                'tahun_masuk'            => 2024,
                'tahun_ajaran_masuk'     => '2024/2025',
                'tahun_lulus_rencana'    => 2027,
                'tahun_ajaran_kelulusan' => '2026/2027',
                'status'                 => 'aktif',
                'tanggal_kelulusan'      => null,
                'keterangan'             => 'Angkatan Aktif Kelas XII Tahun Ajaran 2026/2027',
            ],
            [
                'nomor_angkatan'         => 18,
                'nama_angkatan'          => 'Angkatan 18',
                'kode_angkatan'          => 'AKT-18',
                'tahun_masuk'            => 2025,
                'tahun_ajaran_masuk'     => '2025/2026',
                'tahun_lulus_rencana'    => 2028,
                'tahun_ajaran_kelulusan' => '2027/2028',
                'status'                 => 'aktif',
                'tanggal_kelulusan'      => null,
                'keterangan'             => 'Angkatan Aktif Kelas XI Tahun Ajaran 2026/2027',
            ],
            [
                'nomor_angkatan'         => 19,
                'nama_angkatan'          => 'Angkatan 19',
                'kode_angkatan'          => 'AKT-19',
                'tahun_masuk'            => 2026,
                'tahun_ajaran_masuk'     => '2026/2027',
                'tahun_lulus_rencana'    => 2029,
                'tahun_ajaran_kelulusan' => '2028/2029',
                'status'                 => 'aktif',
                'tanggal_kelulusan'      => null,
                'keterangan'             => 'Angkatan Aktif Kelas X Tahun Ajaran 2026/2027',
            ],
        ];

        $cohortMap = [];
        foreach ($defaultCohorts as $c) {
            $id = DB::table('angkatans')->insertGetId(array_merge($c, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
            $cohortMap[$c['tahun_masuk']] = $id;
        }

        // Backfill existing students: deduce cohort from student's class tingkat
        $existingStudents = DB::table('siswas')
            ->join('kelas', 'siswas.kelas_id', '=', 'kelas.id')
            ->select('siswas.id', 'kelas.tingkat')
            ->get();

        foreach ($existingStudents as $s) {
            $entryYear = match ((int) $s->tingkat) {
                12 => 2024,
                11 => 2025,
                default => 2026,
            };

            $angkatanId = $cohortMap[$entryYear] ?? $cohortMap[2026] ?? null;

            DB::table('siswas')->where('id', $s->id)->update([
                'angkatan_id' => $angkatanId,
                'tahun_masuk' => $entryYear,
                'status'      => 'aktif',
            ]);
        }
    }
};
