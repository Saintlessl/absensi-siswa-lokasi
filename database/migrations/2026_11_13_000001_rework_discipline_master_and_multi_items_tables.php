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
        // 1. Enhance discipline_categories table
        Schema::table('discipline_categories', function (Blueprint $table) {
            if (!Schema::hasColumn('discipline_categories', 'deskripsi')) {
                $table->text('deskripsi')->nullable()->after('default_poin');
            }
            if (!Schema::hasColumn('discipline_categories', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('is_active')->constrained('users')->nullOnDelete();
            }
        });

        // 2. Enhance discipline_records table (allow nullable discipline_category_id and add explicit tipe)
        Schema::table('discipline_records', function (Blueprint $table) {
            if (!Schema::hasColumn('discipline_records', 'tipe')) {
                $table->enum('tipe', ['pelanggaran', 'prestasi'])->default('pelanggaran')->after('discipline_category_id');
            }
        });

        // Modify discipline_category_id column to nullable
        try {
            Schema::table('discipline_records', function (Blueprint $table) {
                $table->foreignId('discipline_category_id')->nullable()->change();
            });
        } catch (\Throwable $e) {
            // Some drivers need raw statement
            DB::statement('ALTER TABLE discipline_records MODIFY discipline_category_id BIGINT UNSIGNED NULL');
        }

        // 3. Create discipline_record_items table for parent-child multi-item records
        Schema::create('discipline_record_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discipline_record_id')->constrained('discipline_records')->cascadeOnDelete();
            $table->foreignId('discipline_category_id')->nullable()->constrained('discipline_categories')->nullOnDelete();
            $table->string('nama_standar', 150);
            $table->enum('tipe', ['pelanggaran', 'prestasi']);
            $table->integer('poin');
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index(['discipline_record_id', 'tipe']);
        });

        // 4. Standardize default categories points to signed integers (negative for violations, positive for achievements)
        DB::table('discipline_categories')
            ->where('tipe', 'pelanggaran')
            ->where('default_poin', '>', 0)
            ->update([
                'default_poin' => DB::raw('-ABS(default_poin)'),
            ]);

        // Ensure descriptions for the standard initial categories
        DB::table('discipline_categories')->where('nama', 'like', '%Terlambat%')->update([
            'deskripsi' => 'Terlambat masuk gerbang sekolah, apel pagi, atau upacara bendera hari Senin.',
            'default_poin' => -5,
        ]);
        DB::table('discipline_categories')->where('nama', 'like', '%Atribut%')->update([
            'deskripsi' => 'Kelengkapan seragam tidak sesuai ketentuan, sepatu tidak hitam, atau tidak memakai atribut resmi.',
            'default_poin' => -5,
        ]);
        DB::table('discipline_categories')->where('nama', 'like', '%Meninggalkan%')->update([
            'deskripsi' => 'Meninggalkan kelas / bolos pada jam pelajaran tanpa izin guru piket atau wali kelas.',
            'default_poin' => -15,
        ]);
        DB::table('discipline_categories')->where('nama', 'like', '%Merokok%')->update([
            'deskripsi' => 'Menghisap atau membawa rokok/vape di lingkungan sekolah atau dalam radius sekolah.',
            'default_poin' => -30,
        ]);
        DB::table('discipline_categories')->where('nama', 'like', '%Perundungan%')->update([
            'deskripsi' => 'Tindakan kekerasan verbal, fisik, siber, atau perkelahian di dalam maupun luar sekolah.',
            'default_poin' => -75,
        ]);

        DB::table('discipline_categories')->where('nama', 'like', '%Juara LKS%Provinsi%')->update([
            'deskripsi' => 'Meraih juara 1, 2, atau 3 pada ajang Lomba Kompetensi Siswa (LKS) tingkat Kota / Provinsi.',
            'default_poin' => 50,
        ]);
        DB::table('discipline_categories')->where('nama', 'like', '%Juara LKS%Nasional%')->update([
            'deskripsi' => 'Meraih medali emas, perak, perunggu, atau Medallion of Excellence pada LKS Nasional.',
            'default_poin' => 100,
        ]);
        DB::table('discipline_categories')->where('nama', 'like', '%Kompetisi Olahraga%')->update([
            'deskripsi' => 'Juara kejuaraan minat bakat, keolahragaan, seni, sains, atau teknologi tingkat resmi.',
            'default_poin' => 35,
        ]);
        DB::table('discipline_categories')->where('nama', 'like', '%OSIS%')->update([
            'deskripsi' => 'Menjadi bagian dari kepengurusan aktif OSIS, MPK, atau ketua ekstrakurikuler sekolah.',
            'default_poin' => 20,
        ]);

        // 5. Migrate existing discipline records to have items snapshot and correct signed point
        $existingRecords = DB::table('discipline_records')->get();
        foreach ($existingRecords as $rec) {
            $cat = null;
            if ($rec->discipline_category_id) {
                $cat = DB::table('discipline_categories')->where('id', $rec->discipline_category_id)->first();
            }

            $tipe = $cat ? $cat->tipe : 'pelanggaran';
            $nama = $cat ? $cat->nama : ($rec->judul ?: 'Catatan Kedisiplinan');
            
            // Signed point calculation: if violation and poin > 0, make negative; if achievement, ensure positive
            $signedPoin = $rec->poin;
            if ($tipe === 'pelanggaran' && $signedPoin > 0) {
                $signedPoin = -$signedPoin;
            } elseif ($tipe === 'prestasi' && $signedPoin < 0) {
                $signedPoin = abs($signedPoin);
            }

            // Update parent record
            DB::table('discipline_records')->where('id', $rec->id)->update([
                'tipe' => $tipe,
                'poin' => $signedPoin,
            ]);

            // Create corresponding snapshot item
            DB::table('discipline_record_items')->insert([
                'discipline_record_id'   => $rec->id,
                'discipline_category_id' => $rec->discipline_category_id,
                'nama_standar'           => $nama,
                'tipe'                   => $tipe,
                'poin'                   => $signedPoin,
                'keterangan'             => $rec->deskripsi,
                'created_at'             => $rec->created_at ?? now(),
                'updated_at'             => $rec->updated_at ?? now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discipline_record_items');

        Schema::table('discipline_records', function (Blueprint $table) {
            if (Schema::hasColumn('discipline_records', 'tipe')) {
                $table->dropColumn('tipe');
            }
        });

        Schema::table('discipline_categories', function (Blueprint $table) {
            if (Schema::hasColumn('discipline_categories', 'created_by')) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            }
            if (Schema::hasColumn('discipline_categories', 'deskripsi')) {
                $table->dropColumn('deskripsi');
            }
        });
    }
};
