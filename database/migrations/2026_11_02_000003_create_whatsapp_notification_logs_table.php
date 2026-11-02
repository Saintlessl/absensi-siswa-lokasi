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
        Schema::create('whatsapp_notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('absensi_id')->nullable()->constrained('absensis')->onDelete('cascade');
            $table->foreignId('siswa_id')->constrained('siswas')->onDelete('cascade');
            $table->string('tipe', 20); // masuk, pulang
            $table->string('nomor_tujuan', 25);
            $table->text('pesan');
            $table->string('status', 20)->default('pending'); // pending, sent, failed, skipped
            $table->string('provider', 50)->default('fonnte');
            $table->text('response_payload')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['siswa_id', 'created_at']);
            $table->index(['absensi_id', 'tipe']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_notification_logs');
    }
};
