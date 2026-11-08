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
        Schema::table('academic_promotion_batches', function (Blueprint $table) {
            $table->dropForeign(['tahun_ajaran_asal_id']);
            $table->dropForeign(['tahun_ajaran_tujuan_id']);

            $table->foreign('tahun_ajaran_asal_id')
                ->references('id')
                ->on('tahun_ajarans')
                ->restrictOnDelete();

            $table->foreign('tahun_ajaran_tujuan_id')
                ->references('id')
                ->on('tahun_ajarans')
                ->restrictOnDelete();
        });

        Schema::table('academic_promotion_items', function (Blueprint $table) {
            $table->dropForeign(['siswa_id']);

            $table->foreign('siswa_id')
                ->references('id')
                ->on('siswas')
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('academic_promotion_items', function (Blueprint $table) {
            $table->dropForeign(['siswa_id']);

            $table->foreign('siswa_id')
                ->references('id')
                ->on('siswas')
                ->cascadeOnDelete();
        });

        Schema::table('academic_promotion_batches', function (Blueprint $table) {
            $table->dropForeign(['tahun_ajaran_asal_id']);
            $table->dropForeign(['tahun_ajaran_tujuan_id']);

            $table->foreign('tahun_ajaran_asal_id')
                ->references('id')
                ->on('tahun_ajarans')
                ->cascadeOnDelete();

            $table->foreign('tahun_ajaran_tujuan_id')
                ->references('id')
                ->on('tahun_ajarans')
                ->cascadeOnDelete();
        });
    }
};
