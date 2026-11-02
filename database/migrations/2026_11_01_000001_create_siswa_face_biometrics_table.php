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
        Schema::create('siswa_face_biometrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')
                ->constrained('siswas')
                ->cascadeOnDelete();
            
            // Encrypted/Serialized 128-d descriptor vector
            $table->mediumText('face_descriptor');
            $table->float('quality_score')->default(0);
            $table->string('sample_photo')->nullable();
            $table->timestamp('registered_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('registered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique('siswa_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('siswa_face_biometrics');
    }
};
