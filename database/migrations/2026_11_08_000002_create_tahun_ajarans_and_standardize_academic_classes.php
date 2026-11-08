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
        // 1. Create tahun_ajarans master table
        if (!Schema::hasTable('tahun_ajarans')) {
            Schema::create('tahun_ajarans', function (Blueprint $table) {
                $table->id();
                $table->unsignedSmallInteger('tahun_mulai');
                $table->unsignedSmallInteger('tahun_selesai');
                $table->string('nama', 20)->unique();
                $table->enum('semester_aktif', ['ganjil', 'genap'])->default('ganjil');
                $table->date('tanggal_mulai');
                $table->date('tanggal_selesai');
                $table->boolean('is_active')->default(false)->index();
                $table->enum('status', ['draft', 'aktif', 'selesai', 'diarsipkan'])->default('draft');
                $table->text('catatan')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        // 2. Add foreign key tahun_ajaran_id to kelas
        if (Schema::hasTable('kelas') && !Schema::hasColumn('kelas', 'tahun_ajaran_id')) {
            Schema::table('kelas', function (Blueprint $table) {
                $table->foreignId('tahun_ajaran_id')->nullable()->after('tahun_ajaran')->constrained('tahun_ajarans')->nullOnDelete();
            });
        }

        // 3. Add foreign key tahun_ajaran_id to homeroom_assignments
        if (Schema::hasTable('homeroom_assignments') && !Schema::hasColumn('homeroom_assignments', 'tahun_ajaran_id')) {
            Schema::table('homeroom_assignments', function (Blueprint $table) {
                $table->foreignId('tahun_ajaran_id')->nullable()->after('tahun_ajaran')->constrained('tahun_ajarans')->nullOnDelete();
            });
        }

        // 4. Seed baseline academic years
        $baseline = [
            [
                'tahun_mulai'     => 2024,
                'tahun_selesai'   => 2025,
                'nama'            => '2024/2025',
                'semester_aktif'  => 'genap',
                'tanggal_mulai'   => '2024-07-15',
                'tanggal_selesai' => '2025-06-30',
                'is_active'       => false,
                'status'          => 'selesai',
                'catatan'         => 'Tahun Ajaran 2024/2025 (Arsip Historis)',
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
            [
                'tahun_mulai'     => 2025,
                'tahun_selesai'   => 2026,
                'nama'            => '2025/2026',
                'semester_aktif'  => 'genap',
                'tanggal_mulai'   => '2025-07-15',
                'tanggal_selesai' => '2026-06-30',
                'is_active'       => false,
                'status'          => 'selesai',
                'catatan'         => 'Tahun Ajaran 2025/2026 (Arsip Historis)',
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
            [
                'tahun_mulai'     => 2026,
                'tahun_selesai'   => 2027,
                'nama'            => '2026/2027',
                'semester_aktif'  => 'ganjil',
                'tanggal_mulai'   => '2026-07-15',
                'tanggal_selesai' => '2027-06-30',
                'is_active'       => true,
                'status'          => 'aktif',
                'catatan'         => 'Tahun Ajaran Aktif Berjalan SMK Negeri 1 Ciomas',
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
            [
                'tahun_mulai'     => 2027,
                'tahun_selesai'   => 2028,
                'nama'            => '2027/2028',
                'semester_aktif'  => 'ganjil',
                'tanggal_mulai'   => '2027-07-15',
                'tanggal_selesai' => '2028-06-30',
                'is_active'       => false,
                'status'          => 'draft',
                'catatan'         => 'Tahun Ajaran Perencanaan Berikutnya',
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
        ];

        foreach ($baseline as $ta) {
            $existing = DB::table('tahun_ajarans')->where('nama', $ta['nama'])->first();
            if (!$existing) {
                DB::table('tahun_ajarans')->insert($ta);
            }
        }

        $activeTa = DB::table('tahun_ajarans')->where('is_active', true)->first();
        $activeTaId = $activeTa?->id;

        // 5. Data Hygiene: Standardize existing classes
        if (Schema::hasTable('kelas')) {
            $classes = DB::table('kelas')->get();
            foreach ($classes as $k) {
                $name = $k->nama_kelas;
                $tingkat = $k->tingkat;
                $rombel = $k->nomor_rombel;
                $tahunAjaran = $k->tahun_ajaran ?? '2026/2027';
                $tahunAjaranId = $k->tahun_ajaran_id ?? $activeTaId;

                // Parse tingkat if null
                if (empty($tingkat)) {
                    if (preg_match('/^(?:XII|12)\b/i', $name)) {
                        $tingkat = 12;
                    } elseif (preg_match('/^(?:XI|11)\b/i', $name)) {
                        $tingkat = 11;
                    } elseif (preg_match('/^(?:X|10)\b/i', $name)) {
                        $tingkat = 10;
                    }
                }

                // Parse nomor_rombel if null
                if (empty($rombel)) {
                    if (preg_match('/(\d+)$/', trim($name), $m)) {
                        $rombel = (int) $m[1];
                    }
                }

                DB::table('kelas')->where('id', $k->id)->update([
                    'tingkat'         => $tingkat,
                    'nomor_rombel'    => $rombel,
                    'tahun_ajaran'    => $tahunAjaran,
                    'tahun_ajaran_id' => $tahunAjaranId,
                    'is_active'       => true,
                ]);
            }
        }

        // 6. Sync existing homeroom_assignments tahun_ajaran_id
        if (Schema::hasTable('homeroom_assignments') && $activeTaId) {
            DB::table('homeroom_assignments')
                ->whereNull('tahun_ajaran_id')
                ->where(function ($q) {
                    $q->where('tahun_ajaran', '2026/2027')
                      ->orWhereNull('tahun_ajaran');
                })
                ->update(['tahun_ajaran_id' => $activeTaId, 'tahun_ajaran' => '2026/2027']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('homeroom_assignments') && Schema::hasColumn('homeroom_assignments', 'tahun_ajaran_id')) {
            Schema::table('homeroom_assignments', function (Blueprint $table) {
                $table->dropForeign(['tahun_ajaran_id']);
                $table->dropColumn('tahun_ajaran_id');
            });
        }

        if (Schema::hasTable('kelas') && Schema::hasColumn('kelas', 'tahun_ajaran_id')) {
            Schema::table('kelas', function (Blueprint $table) {
                $table->dropForeign(['tahun_ajaran_id']);
                $table->dropColumn('tahun_ajaran_id');
            });
        }

        Schema::dropIfExists('tahun_ajarans');
    }
};
