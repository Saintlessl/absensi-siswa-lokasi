<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * School-wide settings, academic calendar non-effective days, and a proper
 * leave-request (izin) workflow with approval status.
 *
 * Previously:
 * - attendance deadlines were hardcoded in AbsensiController (07:15 / 14:55);
 * - the alpa generator and all recaps assumed "every weekday counts", so
 *   holidays inflated alpa;
 * - IzinController wrote an Absensi row directly with status izin/sakit and
 *   no approval step, so a parent/student claim was never verified.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Key-value school settings
        Schema::create('school_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 60)->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Defaults match the previously hardcoded values.
        DB::table('school_settings')->insert([
            ['key' => 'jam_masuk', 'value' => '07:15', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'jam_pulang', 'value' => '14:55', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'nama_sekolah', 'value' => 'SMK Negeri', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 2. Academic calendar — days that are NOT effective school days.
        Schema::create('academic_calendar_days', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal')->unique();
            $table->string('nama_kegiatan', 255);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. Leave requests with approval workflow.
        Schema::create('izins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswas')->cascadeOnDelete();
            $table->date('tanggal');
            $table->enum('jenis', ['izin', 'sakit'])->default('izin');
            $table->string('keterangan', 500);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('keputusan_catatan')->nullable();
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();

            // One leave request per student per day.
            $table->unique(['siswa_id', 'tanggal'], 'izins_siswa_tanggal_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('izins');
        Schema::dropIfExists('academic_calendar_days');
        Schema::dropIfExists('school_settings');
    }
};