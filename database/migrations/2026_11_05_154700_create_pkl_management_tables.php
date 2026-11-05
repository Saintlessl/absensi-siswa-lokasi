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
        Schema::create('pkl_dudi_partners', function (Blueprint $table) {
            $table->id();
            $table->string('nama_dudi', 200);
            $table->string('bidang_industri', 150);
            $table->text('alamat');
            $table->string('kota', 100)->default('Cimahi');
            $table->string('kontak_person', 100);
            $table->string('email', 150)->nullable();
            $table->string('telepon', 30)->nullable();
            $table->string('pembimbing_dudi', 150)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->unsignedInteger('radius_meter')->default(150);
            $table->foreignId('jurusan_id')->nullable()->constrained('jurusans')->nullOnDelete();
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->timestamps();
        });

        Schema::create('pkl_placements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswas')->cascadeOnDelete();
            $table->foreignId('pkl_dudi_partner_id')->constrained('pkl_dudi_partners')->cascadeOnDelete();
            $table->foreignId('guru_pembimbing_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->enum('status', ['active', 'completed', 'withdrawn'])->default('active');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        Schema::create('pkl_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pkl_placement_id')->constrained('pkl_placements')->cascadeOnDelete();
            $table->date('tanggal');
            $table->time('jam_masuk')->nullable();
            $table->time('jam_pulang')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->unsignedInteger('distance_meters')->nullable();
            $table->boolean('is_location_valid')->default(true);
            $table->enum('status', ['hadir', 'terlambat', 'izin', 'sakit', 'alpa'])->default('hadir');
            $table->string('foto_selfie_path', 255)->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->unique(['pkl_placement_id', 'tanggal']);
        });

        Schema::create('pkl_logbooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pkl_placement_id')->constrained('pkl_placements')->cascadeOnDelete();
            $table->date('tanggal');
            $table->text('aktivitas');
            $table->text('kompetensi_dipelajari')->nullable();
            $table->string('foto_dokumentasi_path', 255)->nullable();
            $table->enum('status', ['submitted', 'approved', 'revision_requested'])->default('submitted');
            $table->text('catatan_guru')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pkl_logbooks');
        Schema::dropIfExists('pkl_attendances');
        Schema::dropIfExists('pkl_placements');
        Schema::dropIfExists('pkl_dudi_partners');
    }
};
