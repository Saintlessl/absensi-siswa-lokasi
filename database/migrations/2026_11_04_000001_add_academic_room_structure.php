<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('jurusans', function (Blueprint $table) {
            $table->string('logo_path')->nullable()->after('deskripsi');
            $table->boolean('is_active')->default(true)->after('logo_path');
        });
        Schema::table('kelas', function (Blueprint $table) {
            $table->unsignedTinyInteger('tingkat')->nullable()->after('jurusan_id');
            $table->unsignedSmallInteger('nomor_rombel')->nullable()->after('tingkat');
            $table->string('tahun_ajaran', 20)->nullable()->after('nomor_rombel');
            $table->boolean('is_active')->default(true)->after('tahun_ajaran');
            $table->unique(['jurusan_id', 'tingkat', 'nomor_rombel', 'tahun_ajaran'], 'kelas_academic_unique');
        });
        Schema::table('siswas', function (Blueprint $table) {
            $table->string('photo_path')->nullable()->after('jenis_kelamin');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('jurusan_id')->nullable()->after('role')->constrained('jurusans')->nullOnDelete();
        });
        Schema::create('student_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswas')->cascadeOnDelete();
            $table->foreignId('kelas_id')->constrained('kelas')->restrictOnDelete();
            $table->string('tahun_ajaran', 20);
            $table->date('started_at');
            $table->date('ended_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['siswa_id', 'tahun_ajaran']);
        });
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 50)->unique();
            $table->string('building')->nullable();
            $table->unsignedInteger('capacity')->nullable();
            $table->string('type')->nullable();
            $table->text('description')->nullable();
            $table->string('qr_token', 64)->unique();
            $table->boolean('qr_is_active')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('room_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('rooms')->restrictOnDelete();
            $table->foreignId('kelas_id')->constrained('kelas')->restrictOnDelete();
            $table->string('day_of_week', 20)->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->date('starts_on');
            $table->date('ends_on')->nullable();
            $table->string('tahun_ajaran', 20);
            $table->string('status', 20)->default('active');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['room_id', 'starts_on', 'ends_on']);
            $table->index(['kelas_id', 'starts_on', 'ends_on']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('room_assignments'); Schema::dropIfExists('rooms'); Schema::dropIfExists('student_enrollments');
        Schema::table('users', fn (Blueprint $t) => $t->dropConstrainedForeignId('jurusan_id'));
        Schema::table('siswas', fn (Blueprint $t) => $t->dropColumn('photo_path'));
        Schema::table('kelas', function (Blueprint $t) { $t->dropUnique('kelas_academic_unique'); $t->dropColumn(['tingkat','nomor_rombel','tahun_ajaran','is_active']); });
        Schema::table('jurusans', fn (Blueprint $t) => $t->dropColumn(['logo_path','is_active']));
    }
};
