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
        Schema::table('absensis', function (Blueprint $table) {
            $table->time('jam_pulang')->nullable()->after('jam_masuk');
            $table->string('foto_masuk')->nullable()->after('keterangan');
            $table->string('foto_pulang')->nullable()->after('foto_masuk');
            $table->string('keterangan_terlambat')->nullable()->after('foto_pulang');
            $table->integer('menit_terlambat')->default(0)->after('keterangan_terlambat');
            $table->double('latitude_pulang')->nullable()->after('longitude');
            $table->double('longitude_pulang')->nullable()->after('latitude_pulang');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('absensis', function (Blueprint $table) {
            $table->dropColumn([
                'jam_pulang',
                'foto_masuk',
                'foto_pulang',
                'keterangan_terlambat',
                'menit_terlambat',
                'latitude_pulang',
                'longitude_pulang',
            ]);
        });
    }
};
