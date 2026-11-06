<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * BUG-044: DB-level integrity constraints the application previously
     * relied on "check-then-create" code for. Duplicate attendance rows,
     * duplicate user links, multiple parents per student and duplicate SP
     * tiers are now rejected by the database itself.
     *
     * Idempotent: skips indexes that already exist (nis/nip were made
     * unique in the original create migrations).
     */
    private function hasIndex(string $table, string $name): bool
    {
        foreach (DB::select("SHOW INDEX FROM `{$table}`") as $row) {
            if ($row->Key_name === $name) {
                return true;
            }
        }
        return false;
    }

    private function addUniqueIfMissing(string $table, array $columns): void
    {
        $name = $table . '_' . implode('_', $columns) . '_unique';
        if ($this->hasIndex($table, $name)) {
            return;
        }
        Schema::table($table, function (Blueprint $t) use ($columns) {
            $t->unique($columns);
        });
    }

    public function up(): void
    {
        // De-duplicate keeping the newest row, then constrain.
        if (Schema::hasTable('absensis')) {
            DB::statement('DELETE a FROM absensis a INNER JOIN absensis b
                WHERE a.id < b.id AND a.siswa_id = b.siswa_id AND a.tanggal = b.tanggal');
            $this->addUniqueIfMissing('absensis', ['siswa_id', 'tanggal']);
        }

        if (Schema::hasTable('surat_peringatans')) {
            DB::statement('DELETE o FROM surat_peringatans o INNER JOIN surat_peringatans p
                WHERE o.id < p.id AND o.siswa_id = p.siswa_id AND o.jenis_sp = p.jenis_sp');
            $this->addUniqueIfMissing('surat_peringatans', ['siswa_id', 'jenis_sp']);
        }

        if (Schema::hasTable('siswas')) {
            $this->addUniqueIfMissing('siswas', ['user_id']);
            $this->addUniqueIfMissing('siswas', ['nis']);
        }

        if (Schema::hasTable('gurus')) {
            $this->addUniqueIfMissing('gurus', ['user_id']);
            $this->addUniqueIfMissing('gurus', ['nip']);
        }

        if (Schema::hasTable('orang_tuas')) {
            $this->addUniqueIfMissing('orang_tuas', ['user_id']);
            $this->addUniqueIfMissing('orang_tuas', ['siswa_id']);
        }

        if (Schema::hasTable('kelas')) {
            $this->addUniqueIfMissing('kelas', ['nama_kelas']);
        }
    }

    public function down(): void
    {
        $drops = [
            'absensis' => ['siswa_id', 'tanggal'],
            'surat_peringatans' => ['siswa_id', 'jenis_sp'],
            'siswas' => ['user_id', 'nis'],
            'gurus' => ['user_id', 'nip'],
            'orang_tuas' => ['user_id', 'siswa_id'],
            'kelas' => ['nama_kelas'],
        ];

        foreach ($drops as $table => $columns) {
            if (!Schema::hasTable($table)) {
                continue;
            }
            $name = $table . '_' . implode('_', $columns) . '_unique';
            if ($this->hasIndex($table, $name)) {
                Schema::table($table, function (Blueprint $t) use ($columns) {
                    $t->dropUnique($columns);
                });
            }
        }
    }
};
