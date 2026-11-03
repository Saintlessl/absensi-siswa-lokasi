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
        if (Schema::hasTable('orang_tuas') && !Schema::hasColumn('orang_tuas', 'jenis_kelamin')) {
            Schema::table('orang_tuas', function (Blueprint $table) {
                $table->enum('jenis_kelamin', ['L', 'P'])->nullable()->after('nama');
            });
        }

        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'jenis_kelamin')) {
            Schema::table('users', function (Blueprint $table) {
                $table->enum('jenis_kelamin', ['L', 'P'])->nullable()->after('name');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('orang_tuas') && Schema::hasColumn('orang_tuas', 'jenis_kelamin')) {
            Schema::table('orang_tuas', function (Blueprint $table) {
                $table->dropColumn('jenis_kelamin');
            });
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'jenis_kelamin')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('jenis_kelamin');
            });
        }
    }
};
