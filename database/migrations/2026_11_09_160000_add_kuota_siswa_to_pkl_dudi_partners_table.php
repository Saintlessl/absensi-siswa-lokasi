<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * PKL placement hardening:
     * - kuota_siswa on partners (nullable = unlimited) so capacity can be enforced server-side
     * - source flag on placements kept out of scope; placements stay in their own table
     */
    public function up(): void
    {
        Schema::table('pkl_dudi_partners', function (Blueprint $table) {
            if (!Schema::hasColumn('pkl_dudi_partners', 'kuota_siswa')) {
                $table->unsignedInteger('kuota_siswa')->nullable()->after('radius_meter');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pkl_dudi_partners', function (Blueprint $table) {
            if (Schema::hasColumn('pkl_dudi_partners', 'kuota_siswa')) {
                $table->dropColumn('kuota_siswa');
            }
        });
    }
};
