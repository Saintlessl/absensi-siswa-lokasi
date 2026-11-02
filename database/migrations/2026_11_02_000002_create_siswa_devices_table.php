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
        Schema::create('siswa_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswas')->onDelete('cascade');
            $table->string('device_token', 64)->unique()->index();
            $table->string('device_name')->default('Perangkat Siswa');
            $table->string('platform', 50)->default('Unknown');
            $table->string('browser', 50)->default('Unknown');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamp('registered_at')->useCurrent();
            $table->timestamp('last_active_at')->useCurrent();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->index(['siswa_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('siswa_devices');
    }
};
