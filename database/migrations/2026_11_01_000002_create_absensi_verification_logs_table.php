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
        Schema::create('absensi_verification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')
                ->constrained('siswas')
                ->cascadeOnDelete();
            
            $table->enum('session_type', ['masuk', 'pulang', 'registrasi'])->default('masuk');
            $table->enum('status', [
                'success',
                'failed_liveness',
                'failed_match',
                'failed_quality',
                'failed_location',
                'failed_qr',
                'manual_override'
            ]);

            $table->float('confidence_score')->nullable();
            $table->float('euclidean_distance')->nullable();
            $table->float('liveness_score')->nullable();
            $table->string('failure_reason')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('snapshot_path')->nullable();
            $table->timestamps();

            $table->index(['siswa_id', 'created_at']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absensi_verification_logs');
    }
};
