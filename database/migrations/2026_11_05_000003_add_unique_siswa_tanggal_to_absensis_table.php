<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Enforce one attendance row per student per day.
 *
 * BUG-014 — absensis had no UNIQUE(siswa_id, tanggal). Two parallel requests
 * (double-tap, network retry) could both pass the exists() check in
 * AbsensiController::store() and create duplicate "Hadir" rows, permanently
 * over-counting the recap. GenerateAlpaDaily had the same race.
 *
 * BUG-017 — there was no index on tanggal, so every monthly recap
 * (whereMonth/whereDate) performed a full scan.
 *
 * Deduplication: keep the earliest row (lowest id) per (siswa_id, tanggal)
 * and delete later duplicates before installing the constraint.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Deduplicate any existing rows (keep the earliest occurrence).
        DB::statement('
            DELETE a FROM absensis a
            INNER JOIN absensis b
                ON a.siswa_id = b.siswa_id
               AND a.tanggal  = b.tanggal
               AND a.id > b.id
        ');

        // 2. Unique constraint — the actual anti-duplicate guarantee.
        Schema::table('absensis', function (Blueprint $table) {
            $table->unique(['siswa_id', 'tanggal'], 'absensis_siswa_tanggal_unique');
        });

        // 3. Composite index so per-student date lookups (store(), recaps,
        //    homeroom monitoring) can use a range scan instead of a full scan.
        Schema::table('absensis', function (Blueprint $table) {
            $table->index(['tanggal'], 'absensis_tanggal_index');
        });
    }

    public function down(): void
    {
        Schema::table('absensis', function (Blueprint $table) {
            $table->dropUnique('absensis_siswa_tanggal_unique');
            $table->dropIndex('absensis_tanggal_index');
        });
    }
};