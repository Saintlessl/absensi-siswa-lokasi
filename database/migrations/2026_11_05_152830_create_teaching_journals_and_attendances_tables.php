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
        Schema::create('teaching_journals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guru_id')->constrained('gurus')->cascadeOnDelete();
            $table->foreignId('jadwal_id')->nullable()->constrained('jadwals')->nullOnDelete();
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $table->foreignId('mata_pelajaran_id')->nullable()->constrained('mata_pelajarans')->nullOnDelete();
            $table->string('nama_mapel')->nullable();
            $table->date('tanggal');
            $table->string('jam_ke', 50)->nullable();
            $table->string('materi_pokok');
            $table->text('tujuan_pembelajaran')->nullable();
            $table->string('elemen_capaian')->nullable();
            $table->text('aktivitas');
            $table->string('metode')->nullable();
            $table->text('catatan')->nullable();
            $table->text('kendala')->nullable();
            $table->enum('status', ['draft', 'submitted'])->default('submitted');
            $table->timestamps();

            $table->index(['guru_id', 'tanggal']);
            $table->index(['kelas_id', 'tanggal']);
        });

        Schema::create('teaching_journal_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teaching_journal_id')->constrained('teaching_journals')->cascadeOnDelete();
            $table->foreignId('siswa_id')->constrained('siswas')->cascadeOnDelete();
            $table->enum('status', ['hadir', 'terlambat', 'izin', 'sakit', 'alpa'])->default('hadir');
            $table->string('catatan')->nullable();
            $table->timestamps();

            $table->unique(['teaching_journal_id', 'siswa_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teaching_journal_attendances');
        Schema::dropIfExists('teaching_journals');
    }
};
