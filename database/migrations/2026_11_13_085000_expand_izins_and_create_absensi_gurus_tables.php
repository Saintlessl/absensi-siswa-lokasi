<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Check if index exists before trying to drop
        $indexExists = collect(DB::select("SHOW INDEX FROM izins WHERE Key_name = 'izins_siswa_tanggal_unique'"))->isNotEmpty();
        if ($indexExists) {
            // In MySQL InnoDB, an index supporting a foreign key cannot be dropped unless an alternate index exists.
            // Create dedicated index on siswa_id first.
            $hasSiswaIndex = collect(DB::select("SHOW INDEX FROM izins WHERE Key_name = 'izins_siswa_id_index'"))->isNotEmpty();
            if (!$hasSiswaIndex) {
                Schema::table('izins', function (Blueprint $table) {
                    $table->index('siswa_id', 'izins_siswa_id_index');
                });
            }

            Schema::table('izins', function (Blueprint $table) {
                $table->dropUnique('izins_siswa_tanggal_unique');
            });
        }

        Schema::table('izins', function (Blueprint $table) {
            if (!Schema::hasColumn('izins', 'applicant_type')) {
                $table->enum('applicant_type', ['siswa', 'guru'])->default('siswa')->after('id');
            }
            if (!Schema::hasColumn('izins', 'guru_id')) {
                $table->foreignId('guru_id')->nullable()->after('siswa_id')->constrained('gurus')->cascadeOnDelete();
            }
            if (!Schema::hasColumn('izins', 'tanggal_mulai')) {
                $table->date('tanggal_mulai')->nullable()->after('tanggal');
            }
            if (!Schema::hasColumn('izins', 'tanggal_selesai')) {
                $table->date('tanggal_selesai')->nullable()->after('tanggal_mulai');
            }
            if (!Schema::hasColumn('izins', 'durasi_tipe')) {
                $table->enum('durasi_tipe', ['sehari_penuh', 'rentang_hari', 'sebagian_hari'])->default('sehari_penuh')->after('tanggal_selesai');
            }
            if (!Schema::hasColumn('izins', 'jam_mulai')) {
                $table->time('jam_mulai')->nullable()->after('durasi_tipe');
            }
            if (!Schema::hasColumn('izins', 'jam_selesai')) {
                $table->time('jam_selesai')->nullable()->after('jam_mulai');
            }
            if (!Schema::hasColumn('izins', 'lampiran_path')) {
                $table->string('lampiran_path', 255)->nullable()->after('keterangan');
            }
        });

        // Ensure foreign key on guru_id exists if guru_id exists
        $hasGuruFk = collect(DB::select("SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'izins' AND CONSTRAINT_NAME = 'izins_guru_id_foreign'"))->isNotEmpty();
        if (!$hasGuruFk && Schema::hasColumn('izins', 'guru_id')) {
            try {
                Schema::table('izins', function (Blueprint $table) {
                    $table->foreign('guru_id')->references('id')->on('gurus')->cascadeOnDelete();
                });
            } catch (\Throwable $e) {}
        }

        // Make siswa_id nullable
        try {
            DB::statement("ALTER TABLE izins MODIFY COLUMN siswa_id BIGINT UNSIGNED NULL");
        } catch (\Throwable $e) {}

        // Ensure foreign key on siswa_id exists
        $hasSiswaFk = collect(DB::select("SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'izins' AND CONSTRAINT_NAME = 'izins_siswa_id_foreign'"))->isNotEmpty();
        if (!$hasSiswaFk) {
            try {
                Schema::table('izins', function (Blueprint $table) {
                    $table->foreign('siswa_id')->references('id')->on('siswas')->cascadeOnDelete();
                });
            } catch (\Throwable $e) {}
        }

        // Convert jenis to varchar(50) and status to enum with cancelled
        try {
            DB::statement("ALTER TABLE izins MODIFY COLUMN jenis VARCHAR(50) NOT NULL DEFAULT 'izin'");
        } catch (\Throwable $e) {}
        try {
            DB::statement("ALTER TABLE izins MODIFY COLUMN status ENUM('pending', 'approved', 'rejected', 'cancelled') NOT NULL DEFAULT 'pending'");
        } catch (\Throwable $e) {}

        // Backfill existing rows
        DB::statement("UPDATE izins SET applicant_type = 'siswa', tanggal_mulai = tanggal, tanggal_selesai = tanggal WHERE tanggal_mulai IS NULL");

        // 2. Create absensi_gurus table if not exists
        if (!Schema::hasTable('absensi_gurus')) {
            Schema::create('absensi_gurus', function (Blueprint $table) {
                $table->id();
                $table->foreignId('guru_id')->constrained('gurus')->cascadeOnDelete();
                $table->date('tanggal');
                $table->enum('status', ['hadir', 'izin', 'sakit', 'dinas_luar', 'alfa'])->default('hadir');
                $table->time('jam_masuk')->nullable();
                $table->time('jam_pulang')->nullable();
                $table->text('keterangan')->nullable();
                $table->foreignId('izin_id')->nullable()->constrained('izins')->nullOnDelete();
                $table->timestamps();

                $table->unique(['guru_id', 'tanggal'], 'absensi_gurus_guru_tanggal_unique');
                $table->index(['guru_id', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('absensi_gurus');
    }
};
