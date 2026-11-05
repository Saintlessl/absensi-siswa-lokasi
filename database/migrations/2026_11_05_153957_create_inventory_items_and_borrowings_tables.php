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
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->string('kode_barang', 50)->unique();
            $table->string('nama_barang');
            $table->foreignId('jurusan_id')->nullable()->constrained('jurusans')->nullOnDelete();
            $table->string('kategori', 100);
            $table->string('lokasi_penyimpanan', 150);
            $table->text('deskripsi')->nullable();
            $table->string('foto_path')->nullable();
            $table->integer('total_unit')->default(1);
            $table->integer('unit_tersedia')->default(1);
            $table->enum('kondisi', ['baik', 'rusak_ringan', 'rusak_berat', 'maintenance'])->default('baik');
            $table->enum('status', ['tersedia', 'dipinjam', 'maintenance', 'afkir'])->default('tersedia');
            $table->string('qr_token', 64)->unique();
            $table->timestamps();

            $table->index(['jurusan_id', 'status']);
        });

        Schema::create('inventory_borrowings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_item_id')->constrained('inventory_items')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('siswa_id')->nullable()->constrained('siswas')->nullOnDelete();
            $table->integer('jumlah')->default(1);
            $table->date('tanggal_pinjam');
            $table->date('rencana_kembali');
            $table->date('tanggal_kembali')->nullable();
            $table->text('keperluan');
            $table->enum('status', ['pending', 'approved', 'borrowed', 'returned', 'rejected'])->default('pending');
            $table->enum('kondisi_sebelum', ['baik', 'rusak_ringan'])->default('baik');
            $table->enum('kondisi_sesudah', ['baik', 'rusak_ringan', 'rusak_berat'])->nullable();
            $table->text('catatan_pengembalian')->nullable();
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['inventory_item_id', 'status']);
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_borrowings');
        Schema::dropIfExists('inventory_items');
    }
};
