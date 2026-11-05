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
        Schema::create('assessment_categories', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->string('kode', 20)->unique();
            $table->integer('bobot_default')->default(20);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed standard SMK assessment categories
        DB::table('assessment_categories')->insert([
            ['nama' => 'Tugas & Latihan', 'kode' => 'TGS', 'bobot_default' => 20, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Praktik Bengkel / Lab', 'kode' => 'PRK', 'bobot_default' => 30, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Ulangan Harian / Kuis', 'kode' => 'UH', 'bobot_default' => 20, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Penilaian Sumatif Akhir', 'kode' => 'PAS', 'bobot_default' => 30, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guru_id')->constrained('gurus')->cascadeOnDelete();
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $table->foreignId('mata_pelajaran_id')->nullable()->constrained('mata_pelajarans')->nullOnDelete();
            $table->string('nama_mapel')->nullable();
            $table->foreignId('assessment_category_id')->constrained('assessment_categories')->cascadeOnDelete();
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->date('tanggal');
            $table->string('tahun_ajaran', 20)->default('2026/2027');
            $table->enum('semester', ['ganjil', 'genap'])->default('ganjil');
            $table->decimal('skor_maksimal', 6, 2)->default(100.00);
            $table->decimal('bobot', 5, 2)->default(1.00);
            $table->enum('status', ['draft', 'finalized', 'published'])->default('draft');
            $table->timestamps();

            $table->index(['guru_id', 'kelas_id']);
            $table->index(['kelas_id', 'status']);
        });

        Schema::create('assessment_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained('assessments')->cascadeOnDelete();
            $table->foreignId('siswa_id')->constrained('siswas')->cascadeOnDelete();
            $table->decimal('nilai', 6, 2)->nullable(); // Null = not yet assessed; 0.00 = scored zero
            $table->string('catatan')->nullable();
            $table->timestamps();

            $table->unique(['assessment_id', 'siswa_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessment_scores');
        Schema::dropIfExists('assessments');
        Schema::dropIfExists('assessment_categories');
    }
};
