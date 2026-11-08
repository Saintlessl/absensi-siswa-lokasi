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
        Schema::create('academic_promotion_batches', function (Blueprint $table) {
            $table->id();
            $table->string('batch_code', 64)->unique();
            $table->foreignId('tahun_ajaran_asal_id')->constrained('tahun_ajarans')->cascadeOnDelete();
            $table->foreignId('tahun_ajaran_tujuan_id')->constrained('tahun_ajarans')->cascadeOnDelete();
            $table->unsignedInteger('total_siswa')->default(0);
            $table->unsignedInteger('total_naik')->default(0);
            $table->unsignedInteger('total_tinggal')->default(0);
            $table->unsignedInteger('total_lulus')->default(0);
            $table->unsignedInteger('total_pindah')->default(0);
            $table->unsignedInteger('total_keluar')->default(0);
            $table->string('status', 32)->default('completed'); // completed, partial, failed
            $table->json('summary_meta')->nullable();
            $table->text('catatan')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tahun_ajaran_asal_id', 'tahun_ajaran_tujuan_id'], 'prom_batches_ta_index');
            $table->index('status', 'prom_batches_status_index');
            $table->index('created_at', 'prom_batches_created_at_index');
        });

        Schema::create('academic_promotion_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('academic_promotion_batches')->cascadeOnDelete();
            $table->foreignId('siswa_id')->constrained('siswas')->cascadeOnDelete();
            $table->string('action', 32); // naik_kelas, tinggal_kelas, lulus, pindah, keluar
            $table->foreignId('kelas_asal_id')->nullable()->constrained('kelas')->nullOnDelete();
            $table->foreignId('kelas_tujuan_id')->nullable()->constrained('kelas')->nullOnDelete();
            $table->unsignedTinyInteger('tingkat_asal')->nullable();
            $table->unsignedTinyInteger('tingkat_tujuan')->nullable();
            $table->string('status', 32)->default('success'); // success, failed
            $table->text('error_message')->nullable();
            $table->string('alasan', 255)->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['batch_id', 'siswa_id'], 'prom_items_batch_siswa_index');
            $table->index('action', 'prom_items_action_index');
            $table->index('status', 'prom_items_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_promotion_items');
        Schema::dropIfExists('academic_promotion_batches');
    }
};
