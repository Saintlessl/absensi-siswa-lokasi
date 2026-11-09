<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Manual inventory transactions recorded by a petugas on behalf of a
     * siswa/guru, plus explicit borrower identity for report filtering.
     */
    public function up(): void
    {
        Schema::table('inventory_borrowings', function (Blueprint $table) {
            if (!Schema::hasColumn('inventory_borrowings', 'sumber_transaksi')) {
                $table->enum('sumber_transaksi', ['mandiri', 'manual_petugas'])->default('mandiri')->after('status');
            }
            if (!Schema::hasColumn('inventory_borrowings', 'dicatat_oleh')) {
                $table->foreignId('dicatat_oleh')->nullable()->after('sumber_transaksi')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('inventory_borrowings', 'peminjam_guru_id')) {
                $table->foreignId('peminjam_guru_id')->nullable()->after('siswa_id')->constrained('gurus')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('inventory_borrowings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('dicatat_oleh');
            $table->dropConstrainedForeignId('peminjam_guru_id');
            $table->dropColumn('sumber_transaksi');
        });
    }
};
