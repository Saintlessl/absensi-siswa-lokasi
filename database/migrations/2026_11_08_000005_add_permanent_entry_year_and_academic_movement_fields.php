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
        // 1. Add permanent entry academic year to siswas table
        Schema::table('siswas', function (Blueprint $table) {
            if (!Schema::hasColumn('siswas', 'tahun_ajaran_masuk_id')) {
                $table->foreignId('tahun_ajaran_masuk_id')->nullable()->after('angkatan_id')->constrained('tahun_ajarans')->nullOnDelete();
            }
            if (!Schema::hasColumn('siswas', 'tahun_ajaran_masuk')) {
                $table->string('tahun_ajaran_masuk', 20)->nullable()->after('tahun_ajaran_masuk_id');
            }
        });

        // 2. Add academic movement and homeroom tracking fields to siswa_status_histories
        Schema::table('siswa_status_histories', function (Blueprint $table) {
            if (!Schema::hasColumn('siswa_status_histories', 'jenis_perubahan')) {
                $table->string('jenis_perubahan', 50)->nullable()->after('status_baru');
            }
            if (!Schema::hasColumn('siswa_status_histories', 'tahun_ajaran_asal_id')) {
                $table->foreignId('tahun_ajaran_asal_id')->nullable()->after('tahun_ajaran')->constrained('tahun_ajarans')->nullOnDelete();
            }
            if (!Schema::hasColumn('siswa_status_histories', 'tahun_ajaran_asal')) {
                $table->string('tahun_ajaran_asal', 20)->nullable()->after('tahun_ajaran_asal_id');
            }
            if (!Schema::hasColumn('siswa_status_histories', 'tahun_ajaran_tujuan_id')) {
                $table->foreignId('tahun_ajaran_tujuan_id')->nullable()->after('tahun_ajaran_asal');
            }
            if (!Schema::hasColumn('siswa_status_histories', 'tahun_ajaran_tujuan')) {
                $table->string('tahun_ajaran_tujuan', 20)->nullable()->after('tahun_ajaran_tujuan_id');
            }
            if (!Schema::hasColumn('siswa_status_histories', 'kelas_asal_id')) {
                $table->foreignId('kelas_asal_id')->nullable()->after('tahun_ajaran_tujuan')->constrained('kelas')->nullOnDelete();
            }
            if (!Schema::hasColumn('siswa_status_histories', 'kelas_tujuan_id')) {
                $table->foreignId('kelas_tujuan_id')->nullable()->after('kelas_asal_id')->constrained('kelas')->nullOnDelete();
            }
            if (!Schema::hasColumn('siswa_status_histories', 'wali_kelas_asal')) {
                $table->string('wali_kelas_asal', 255)->nullable()->after('kelas_tujuan_id');
            }
            if (!Schema::hasColumn('siswa_status_histories', 'wali_kelas_tujuan')) {
                $table->string('wali_kelas_tujuan', 255)->nullable()->after('wali_kelas_asal');
            }
        });

        // 3. Backfill permanent entry academic year for existing students (Non-destructive)
        $siswas = DB::table('siswas')->get();
        foreach ($siswas as $s) {
            $entryYear = $s->tahun_masuk;
            if (!$entryYear && $s->angkatan_id) {
                $akt = DB::table('angkatans')->where('id', $s->angkatan_id)->first();
                $entryYear = $akt?->tahun_masuk;
            }

            if ($entryYear) {
                $taNama = "{$entryYear}/" . ($entryYear + 1);
                $ta = DB::table('tahun_ajarans')->where('nama', $taNama)->first();
                if (!$ta) {
                    // Try to match by tahun_mulai
                    $ta = DB::table('tahun_ajarans')->where('tahun_mulai', $entryYear)->first();
                }

                DB::table('siswas')->where('id', $s->id)->update([
                    'tahun_ajaran_masuk_id' => $ta?->id,
                    'tahun_ajaran_masuk'    => $ta ? $ta->nama : $taNama,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('siswa_status_histories', function (Blueprint $table) {
            $table->dropForeign(['tahun_ajaran_asal_id']);
            $table->dropForeign(['kelas_asal_id']);
            $table->dropForeign(['kelas_tujuan_id']);
            $table->dropColumn([
                'jenis_perubahan',
                'tahun_ajaran_asal_id',
                'tahun_ajaran_asal',
                'tahun_ajaran_tujuan_id',
                'tahun_ajaran_tujuan',
                'kelas_asal_id',
                'kelas_tujuan_id',
                'wali_kelas_asal',
                'wali_kelas_tujuan',
            ]);
        });

        Schema::table('siswas', function (Blueprint $table) {
            $table->dropForeign(['tahun_ajaran_masuk_id']);
            $table->dropColumn(['tahun_ajaran_masuk_id', 'tahun_ajaran_masuk']);
        });
    }
};
