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
        // 1. Tambah status biometrik pada tabel siswas
        Schema::table('siswas', function (Blueprint $table) {
            $table->enum('biometric_status', [
                'not_enrolled',
                'pending',
                'enrolled',
                'failed',
                'suspended',
                'needs_re_enrollment'
            ])->default('not_enrolled')->after('kelas_id');
        });

        // 2. Tambah kolom metadata & status pada tabel siswa_face_biometrics
        Schema::table('siswa_face_biometrics', function (Blueprint $table) {
            $table->enum('enrollment_status', [
                'pending',
                'enrolled',
                'failed',
                'suspended',
                'needs_re_enrollment'
            ])->default('enrolled')->after('is_active');
            $table->string('model_version')->default('face-api-v1')->after('enrollment_status');
            $table->text('status_reason')->nullable()->after('model_version');
            $table->timestamp('enrolled_at')->nullable()->after('registered_at');
            $table->timestamp('last_verified_at')->nullable()->after('enrolled_at');
        });

        // 3. Buat tabel audit log riwayat percobaan enrollment biometrik
        Schema::create('biometric_enrollment_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswas')->cascadeOnDelete();
            $table->string('attempt_type')->default('first_login_enrollment'); // first_login_enrollment, re_enrollment, admin_reset, admin_suspend, admin_request_re_enroll
            $table->string('status'); // success, failed_quality, failed_liveness, failed_validation, reset, suspended, re_enroll_requested
            $table->float('quality_score')->nullable();
            $table->float('liveness_score')->nullable();
            $table->string('model_version')->default('face-api-v1');
            $table->text('failure_reason')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('biometric_enrollment_logs');

        Schema::table('siswa_face_biometrics', function (Blueprint $table) {
            $table->dropColumn([
                'enrollment_status',
                'model_version',
                'status_reason',
                'enrolled_at',
                'last_verified_at'
            ]);
        });

        Schema::table('siswas', function (Blueprint $table) {
            $table->dropColumn('biometric_status');
        });
    }
};
