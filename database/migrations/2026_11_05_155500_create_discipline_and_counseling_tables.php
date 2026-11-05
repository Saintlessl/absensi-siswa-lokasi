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
        Schema::create('discipline_categories', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 150);
            $table->enum('tipe', ['pelanggaran', 'prestasi']);
            $table->integer('default_poin')->default(10);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('discipline_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswas')->cascadeOnDelete();
            $table->foreignId('discipline_category_id')->constrained('discipline_categories')->cascadeOnDelete();
            $table->date('tanggal');
            $table->string('judul', 200);
            $table->text('deskripsi');
            $table->string('bukti_path', 255)->nullable();
            $table->integer('poin');
            $table->enum('status', ['recorded', 'verified', 'resolved'])->default('recorded');
            $table->text('tindak_lanjut')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('counseling_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswas')->cascadeOnDelete();
            $table->foreignId('guru_bk_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('tanggal');
            $table->string('topik_umum', 255);
            $table->longText('permasalahan_rahasia');
            $table->text('tindak_lanjut_umum')->nullable();
            $table->text('catatan_rahasia')->nullable();
            $table->enum('status', ['scheduled', 'in_progress', 'completed'])->default('scheduled');
            $table->timestamps();
        });

        // Seed default vocational school standard categories
        DB::table('discipline_categories')->insert([
            // Pelanggaran (Poin negatif/pelanggaran)
            ['nama' => 'Terlambat Masuk Sekolah / Upacara', 'tipe' => 'pelanggaran', 'default_poin' => 5, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Atribut Seragam atau Sepatu Tidak Lengkap', 'tipe' => 'pelanggaran', 'default_poin' => 5, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Meninggalkan KBM / Bolos Tanpa Keterangan', 'tipe' => 'pelanggaran', 'default_poin' => 15, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Merokok atau Membawa Vape di Lingkungan Sekolah', 'tipe' => 'pelanggaran', 'default_poin' => 30, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Perundungan (Bullying) / Berkelahi', 'tipe' => 'pelanggaran', 'default_poin' => 75, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            
            // Prestasi (Poin merit/penghargaan)
            ['nama' => 'Juara LKS SMK Tingkat Kota / Provinsi', 'tipe' => 'prestasi', 'default_poin' => 50, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Juara LKS SMK Tingkat Nasional', 'tipe' => 'prestasi', 'default_poin' => 100, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Juara Kompetisi Olahraga / Seni / Teknologi', 'tipe' => 'prestasi', 'default_poin' => 35, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Pengurus Aktif OSIS / MPK / Ekstrakurikuler', 'tipe' => 'prestasi', 'default_poin' => 20, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('counseling_sessions');
        Schema::dropIfExists('discipline_records');
        Schema::dropIfExists('discipline_categories');
    }
};
