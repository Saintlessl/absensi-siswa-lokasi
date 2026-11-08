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
        if (Schema::hasTable('student_enrollments')) {
            $indexes = collect(DB::select("SHOW INDEX FROM `student_enrollments`"))->pluck('Key_name')->unique()->all();

            // 1. First add standalone index on siswa_id so the foreign key constraint is satisfied
            if (!in_array('student_enrollments_siswa_id_index', $indexes, true)) {
                DB::statement("CREATE INDEX `student_enrollments_siswa_id_index` ON `student_enrollments` (`siswa_id`)");
            }

            // 2. Drop obsolete unique constraint that prevented multiple enrollment records per academic year
            // (e.g. historical rombel changes where old enrollment is closed and new enrollment is active)
            if (in_array('student_enrollments_siswa_id_tahun_ajaran_unique', $indexes, true)) {
                DB::statement("ALTER TABLE `student_enrollments` DROP INDEX `student_enrollments_siswa_id_tahun_ajaran_unique`");
            }

            // 3. Modify status_enrollment to VARCHAR(50) to support all status variants including 'selesai'
            DB::statement("ALTER TABLE `student_enrollments` MODIFY COLUMN `status_enrollment` VARCHAR(50) NOT NULL DEFAULT 'aktif'");

            // 4. Modify started_at to be nullable so enrollment records don't fail without explicit dates
            DB::statement("ALTER TABLE `student_enrollments` MODIFY COLUMN `started_at` DATE NULL");

            // 5. Add index for fast student academic history lookup
            $refreshedIndexes = collect(DB::select("SHOW INDEX FROM `student_enrollments`"))->pluck('Key_name')->unique()->all();
            if (!in_array('student_enrollments_siswa_tahun_idx', $refreshedIndexes, true)) {
                Schema::table('student_enrollments', function (Blueprint $table) {
                    $table->index(['siswa_id', 'tahun_ajaran_id'], 'student_enrollments_siswa_tahun_idx');
                });
            }
        }

        if (Schema::hasTable('kelas')) {
            // Modify jumlah_siswa to have a default value '0'
            DB::statement("ALTER TABLE `kelas` MODIFY COLUMN `jumlah_siswa` VARCHAR(50) NOT NULL DEFAULT '0'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe to leave as VARCHAR(50) and indexes retained
    }
};
