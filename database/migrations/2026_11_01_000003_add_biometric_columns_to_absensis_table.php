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
        Schema::table('absensis', function (Blueprint $table) {
            $table->float('confidence_masuk')->nullable()->after('foto_masuk');
            $table->float('confidence_pulang')->nullable()->after('foto_pulang');
            $table->float('liveness_score_masuk')->nullable()->after('confidence_masuk');
            $table->float('liveness_score_pulang')->nullable()->after('confidence_pulang');
            $table->boolean('is_verified_biometric')->default(false)->after('liveness_score_pulang');
            $table->boolean('is_manual_override')->default(false)->after('is_verified_biometric');
            $table->foreignId('manual_override_by')->nullable()->after('is_manual_override')->constrained('users')->nullOnDelete();
            $table->string('manual_override_reason')->nullable()->after('manual_override_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('absensis', function (Blueprint $table) {
            $table->dropForeign(['manual_override_by']);
            $table->dropColumn([
                'confidence_masuk',
                'confidence_pulang',
                'liveness_score_masuk',
                'liveness_score_pulang',
                'is_verified_biometric',
                'is_manual_override',
                'manual_override_by',
                'manual_override_reason',
            ]);
        });
    }
};
