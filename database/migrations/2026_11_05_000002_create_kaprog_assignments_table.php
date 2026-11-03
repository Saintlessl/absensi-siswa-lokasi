<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('kaprog_assignments')) {
            Schema::create('kaprog_assignments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('guru_id')->constrained('gurus')->cascadeOnDelete();
                $table->foreignId('jurusan_id')->constrained('jurusans')->cascadeOnDelete();
                $table->string('tahun_ajaran', 20)->default('2026/2027');
                $table->date('starts_on')->nullable();
                $table->date('ends_on')->nullable();
                $table->boolean('is_active')->default(true);
                $table->text('catatan')->nullable();
                $table->timestamps();

                $table->index(['guru_id', 'is_active']);
                $table->index(['jurusan_id', 'tahun_ajaran', 'is_active']);
            });

            // Backfill from any existing users with jurusan_id and guru profile
            $kaprogUsers = DB::table('users')
                ->whereNotNull('jurusan_id')
                ->get();

            foreach ($kaprogUsers as $u) {
                $guru = DB::table('gurus')->where('user_id', $u->id)->first();
                if ($guru) {
                    DB::table('kaprog_assignments')->insert([
                        'guru_id'      => $guru->id,
                        'jurusan_id'   => $u->jurusan_id,
                        'tahun_ajaran' => '2026/2027',
                        'starts_on'    => now()->toDateString(),
                        'is_active'    => true,
                        'catatan'      => 'Penugasan awal KAPROG',
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kaprog_assignments');
    }
};
