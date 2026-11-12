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
        Schema::table('inventory_items', function (Blueprint $table) {
            if (!Schema::hasColumn('inventory_items', 'borrowing_scope')) {
                $table->enum('borrowing_scope', ['jurusan', 'umum'])
                    ->default('jurusan')
                    ->after('jurusan_id');
            }
        });

        // Items with null jurusan_id default to 'umum'
        DB::table('inventory_items')
            ->whereNull('jurusan_id')
            ->update(['borrowing_scope' => 'umum']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            if (Schema::hasColumn('inventory_items', 'borrowing_scope')) {
                $table->dropColumn('borrowing_scope');
            }
        });
    }
};
