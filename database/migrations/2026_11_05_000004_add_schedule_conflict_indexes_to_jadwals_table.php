<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Indexes for schedule conflict detection.
 *
 * TeacherScheduleService::assertNoConflict() filters on
 * (guru_id, hari, tahun_ajaran) plus a jam range, then on
 * (kelas_id, hari, tahun_ajaran), then on (room_id, hari, tahun_ajaran).
 * Only the single-column FK indexes existed, so every conflict check scanned
 * the whole teacher/class/room slice of jadwals.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jadwals', function (Blueprint $table) {
            $table->index(['guru_id', 'hari', 'tahun_ajaran'], 'jadwals_guru_hari_ta_index');
            $table->index(['kelas_id', 'hari', 'tahun_ajaran'], 'jadwals_kelas_hari_ta_index');
            $table->index(['room_id', 'hari', 'tahun_ajaran'], 'jadwals_room_hari_ta_index');
        });
    }

    public function down(): void
    {
        Schema::table('jadwals', function (Blueprint $table) {
            $table->dropIndex('jadwals_guru_hari_ta_index');
            $table->dropIndex('jadwals_kelas_hari_ta_index');
            $table->dropIndex('jadwals_room_hari_ta_index');
        });
    }
};