<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Expand inventory_items (Master Asset Group)
        Schema::table('inventory_items', function (Blueprint $table) {
            if (!Schema::hasColumn('inventory_items', 'merk')) {
                $table->string('merk', 100)->nullable()->after('kategori');
            }
            if (!Schema::hasColumn('inventory_items', 'tipe_model')) {
                $table->string('tipe_model', 100)->nullable()->after('merk');
            }
            if (!Schema::hasColumn('inventory_items', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('status');
            }
        });

        // 2. Create inventory_units (Individual Physical Assets)
        if (!Schema::hasTable('inventory_units')) {
            Schema::create('inventory_units', function (Blueprint $table) {
                $table->id();
                $table->foreignId('inventory_item_id')->constrained('inventory_items')->cascadeOnDelete();
                $table->string('kode_unit', 100)->unique();
                $table->string('barcode_eksternal', 100)->nullable()->unique();
                $table->string('qr_token', 64)->unique();
                $table->enum('kondisi', ['baik', 'rusak_ringan', 'rusak_berat', 'perlu_pemeriksaan'])->default('baik');
                $table->enum('status_ketersediaan', ['tersedia', 'dipinjam'])->default('tersedia');
                $table->string('lokasi_penyimpanan', 150)->nullable();
                $table->date('tanggal_pengadaan')->nullable();
                $table->text('catatan')->nullable();
                $table->boolean('is_active')->default(true);
                $table->string('status_alasan', 50)->nullable(); // e.g. aktif, rusak_berat, hilang, dihapuskan, diarsipkan
                $table->timestamps();

                $table->index(['inventory_item_id', 'status_ketersediaan', 'kondisi'], 'idx_inv_unit_avail');
                $table->index(['inventory_item_id', 'is_active'], 'idx_inv_unit_active');
            });
        }

        // 3. Modify inventory_borrowings to include approval, handover & cancellation
        Schema::table('inventory_borrowings', function (Blueprint $table) {
            if (!Schema::hasColumn('inventory_borrowings', 'approved_by')) {
                $table->foreignId('approved_by')->nullable()->after('decided_by')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('inventory_borrowings', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }
            if (!Schema::hasColumn('inventory_borrowings', 'diserahkan_oleh')) {
                $table->foreignId('diserahkan_oleh')->nullable()->after('approved_at')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('inventory_borrowings', 'diserahkan_at')) {
                $table->timestamp('diserahkan_at')->nullable()->after('diserahkan_oleh');
            }
        });

        // Update status enum in inventory_borrowings to include 'cancelled' if MySQL
        try {
            DB::statement("ALTER TABLE inventory_borrowings MODIFY COLUMN status ENUM('pending', 'approved', 'borrowed', 'returned', 'rejected', 'cancelled') DEFAULT 'pending'");
        } catch (\Throwable $e) {
            // In case of drivers that do not use raw MySQL ALTER enum
        }

        // 4. Create inventory_borrowing_units (Physical unit allocation pivot)
        if (!Schema::hasTable('inventory_borrowing_units')) {
            Schema::create('inventory_borrowing_units', function (Blueprint $table) {
                $table->id();
                $table->foreignId('inventory_borrowing_id')->constrained('inventory_borrowings')->cascadeOnDelete();
                $table->foreignId('inventory_unit_id')->constrained('inventory_units')->cascadeOnDelete();
                $table->enum('status', ['reserved', 'borrowed', 'returned'])->default('reserved');
                $table->enum('kondisi_sebelum', ['baik', 'rusak_ringan', 'rusak_berat', 'perlu_pemeriksaan'])->default('baik');
                $table->enum('kondisi_sesudah', ['baik', 'rusak_ringan', 'rusak_berat', 'perlu_pemeriksaan'])->nullable();
                $table->text('catatan_pengembalian')->nullable();
                $table->timestamp('diserahkan_at')->nullable();
                $table->timestamp('dikembalikan_at')->nullable();
                $table->timestamps();

                $table->unique(['inventory_borrowing_id', 'inventory_unit_id'], 'uq_inv_borrow_unit');
                $table->index(['inventory_unit_id', 'status'], 'idx_inv_unit_status');
            });
        }

        // 5. Create inventory_unit_logs (Audit trail for individual physical units)
        if (!Schema::hasTable('inventory_unit_logs')) {
            Schema::create('inventory_unit_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('inventory_unit_id')->constrained('inventory_units')->cascadeOnDelete();
                $table->foreignId('inventory_borrowing_id')->nullable()->constrained('inventory_borrowings')->nullOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('action', 50); // unit_created, kondisi_diubah, lokasi_diubah, reservasi, penyerahan, pengembalian, diarsipkan, diaktifkan
                $table->string('kondisi_lama', 50)->nullable();
                $table->string('kondisi_baru', 50)->nullable();
                $table->string('lokasi_lama', 150)->nullable();
                $table->string('lokasi_baru', 150)->nullable();
                $table->text('keterangan')->nullable();
                $table->timestamps();

                $table->index(['inventory_unit_id', 'created_at'], 'idx_unit_logs_created');
            });
        }

        // 6. Data Migration for existing items
        // Convert existing quantity in inventory_items to physical individual units
        $existingItems = DB::table('inventory_items')->get();
        foreach ($existingItems as $item) {
            $unitCount = DB::table('inventory_units')->where('inventory_item_id', $item->id)->count();
            if ($unitCount === 0) {
                $targetTotal = max(1, (int) $item->total_unit);
                $cleanKode = preg_replace('/[^A-Za-z0-9\-]/', '', strtoupper($item->kode_barang));
                
                for ($i = 1; $i <= $targetTotal; $i++) {
                    $serial = str_pad($i, 4, '0', STR_PAD_LEFT);
                    $kodeUnit = "{$cleanKode}-{$serial}";

                    // Ensure uniqueness
                    if (DB::table('inventory_units')->where('kode_unit', $kodeUnit)->exists()) {
                        $kodeUnit = "{$cleanKode}-" . strtoupper(Str::random(4)) . "-{$serial}";
                    }

                    $unitId = DB::table('inventory_units')->insertGetId([
                        'inventory_item_id'   => $item->id,
                        'kode_unit'           => $kodeUnit,
                        'barcode_eksternal'   => null,
                        'qr_token'            => Str::random(40),
                        // Historical condition must NOT be assumed as newly verified physical check.
                        // Mark migrated units as 'perlu_pemeriksaan' as required by prompt.
                        'kondisi'             => 'perlu_pemeriksaan',
                        'status_ketersediaan' => 'tersedia',
                        'lokasi_penyimpanan'  => $item->lokasi_penyimpanan,
                        'is_active'           => true,
                        'status_alasan'       => 'aktif',
                        'catatan'             => 'Unit fisik hasil migrasi dari stok legacy',
                        'created_at'          => now(),
                        'updated_at'          => now(),
                    ]);

                    DB::table('inventory_unit_logs')->insert([
                        'inventory_unit_id' => $unitId,
                        'user_id'           => null,
                        'action'            => 'migrasi_legacy',
                        'kondisi_lama'      => null,
                        'kondisi_baru'      => 'perlu_pemeriksaan',
                        'lokasi_lama'       => null,
                        'lokasi_baru'       => $item->lokasi_penyimpanan,
                        'keterangan'        => "Unit fisik dibuat otomatis dari migrasi data inventaris legacy ({$item->kode_barang})",
                        'created_at'        => now(),
                        'updated_at'        => now(),
                    ]);
                }

                // Sync counts on the item
                DB::table('inventory_items')->where('id', $item->id)->update([
                    'total_unit'    => $targetTotal,
                    'unit_tersedia' => 0, // Since condition is 'perlu_pemeriksaan', available loan units is 0 until verified
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_unit_logs');
        Schema::dropIfExists('inventory_borrowing_units');
        Schema::dropIfExists('inventory_units');

        Schema::table('inventory_borrowings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('diserahkan_oleh');
            $table->dropConstrainedForeignId('approved_by');
            $table->dropColumn(['approved_at', 'diserahkan_at']);
        });

        Schema::table('inventory_items', function (Blueprint $table) {
            $table->dropColumn(['merk', 'tipe_model', 'is_active']);
        });
    }
};
