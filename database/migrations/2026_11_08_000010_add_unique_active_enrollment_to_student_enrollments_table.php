<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('student_enrollments', function (Blueprint $table) {
            $table->unsignedBigInteger('active_ta_id')
                ->nullable()
                ->virtualAs('IF(is_active = 1, tahun_ajaran_id, NULL)')
                ->after('is_active');

            $table->unique(['siswa_id', 'active_ta_id'], 'uq_student_active_ta');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_enrollments', function (Blueprint $table) {
            $table->dropUnique('uq_student_active_ta');
            $table->dropColumn('active_ta_id');
        });
    }
};
