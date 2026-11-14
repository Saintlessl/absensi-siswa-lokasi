<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Restrict cascade deletion on student assessments, scores, and teaching journals
     * when a teacher record is targeted for deletion.
     */
    public function up(): void
    {
        // 1. Restrict cascade delete on assessments table
        if (Schema::hasTable('assessments') && Schema::hasColumn('assessments', 'guru_id')) {
            try {
                Schema::table('assessments', function (Blueprint $table) {
                    $table->dropForeign('assessments_guru_id_foreign');
                });
            } catch (\Throwable $e) {
                // If named differently or already dropped
            }

            Schema::table('assessments', function (Blueprint $table) {
                $table->foreign('guru_id')
                    ->references('id')
                    ->on('gurus')
                    ->restrictOnDelete();
            });
        }

        // 2. Restrict cascade delete on teaching_journals table
        if (Schema::hasTable('teaching_journals') && Schema::hasColumn('teaching_journals', 'guru_id')) {
            try {
                Schema::table('teaching_journals', function (Blueprint $table) {
                    $table->dropForeign('teaching_journals_guru_id_foreign');
                });
            } catch (\Throwable $e) {
                // If named differently or already dropped
            }

            Schema::table('teaching_journals', function (Blueprint $table) {
                $table->foreign('guru_id')
                    ->references('id')
                    ->on('gurus')
                    ->restrictOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('assessments') && Schema::hasColumn('assessments', 'guru_id')) {
            try {
                Schema::table('assessments', function (Blueprint $table) {
                    $table->dropForeign(['guru_id']);
                });
            } catch (\Throwable $e) {}

            Schema::table('assessments', function (Blueprint $table) {
                $table->foreign('guru_id')
                    ->references('id')
                    ->on('gurus')
                    ->cascadeOnDelete();
            });
        }

        if (Schema::hasTable('teaching_journals') && Schema::hasColumn('teaching_journals', 'guru_id')) {
            try {
                Schema::table('teaching_journals', function (Blueprint $table) {
                    $table->dropForeign(['guru_id']);
                });
            } catch (\Throwable $e) {}

            Schema::table('teaching_journals', function (Blueprint $table) {
                $table->foreign('guru_id')
                    ->references('id')
                    ->on('gurus')
                    ->cascadeOnDelete();
            });
        }
    }
};
