<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('siswa_orang_tua', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswas')->cascadeOnDelete();
            $table->foreignId('orang_tua_id')->constrained('orang_tuas')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['siswa_id', 'orang_tua_id']);
        });

        // Preserve every existing relationship.  Legacy orang_tuas.siswa_id remains
        // untouched so this migration is safe to deploy to an occupied database.
        DB::table('orang_tuas')->whereNotNull('siswa_id')->orderBy('id')->each(function ($parent) {
            DB::table('siswa_orang_tua')->insertOrIgnore([
                'siswa_id' => $parent->siswa_id,
                'orang_tua_id' => $parent->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('siswa_orang_tua');
    }
};
