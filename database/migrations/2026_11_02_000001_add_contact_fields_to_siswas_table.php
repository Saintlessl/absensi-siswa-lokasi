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
        Schema::table('siswas', function (Blueprint $table) {
            $table->string('nisn', 20)->nullable()->unique()->after('nis');
            $table->string('no_telp_siswa', 25)->nullable()->after('kelas_id');
            $table->string('no_telp_orangtua', 25)->nullable()->after('no_telp_siswa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            $table->dropColumn(['nisn', 'no_telp_siswa', 'no_telp_orangtua']);
        });
    }
};
