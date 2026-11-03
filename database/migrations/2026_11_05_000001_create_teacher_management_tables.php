<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah status pada tabel users
        if (!Schema::hasColumn('users', 'status')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('status', 30)->default('active')->after('role');
            });
        }

        // 2. Modifikasi tabel gurus
        Schema::table('gurus', function (Blueprint $table) {
            if (!Schema::hasColumn('gurus', 'nuptk')) {
                $table->string('nuptk', 30)->nullable()->after('nip');
            }
            if (!Schema::hasColumn('gurus', 'telepon')) {
                $table->string('telepon', 30)->nullable()->after('jenis_kelamin');
            }
            if (!Schema::hasColumn('gurus', 'alamat')) {
                $table->text('alamat')->nullable()->after('telepon');
            }
            if (!Schema::hasColumn('gurus', 'photo_path')) {
                $table->string('photo_path', 255)->nullable()->after('alamat');
            }
            if (!Schema::hasColumn('gurus', 'status_kepegawaian')) {
                $table->string('status_kepegawaian', 50)->nullable()->after('photo_path');
            }
            if (!Schema::hasColumn('gurus', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('status_kepegawaian');
            }
            if (!Schema::hasColumn('gurus', 'catatan')) {
                $table->text('catatan')->nullable()->after('is_active');
            }
        });

        // Ubah nip dan mapel agar nullable pada gurus
        DB::statement("ALTER TABLE gurus MODIFY COLUMN nip VARCHAR(255) NULL");
        DB::statement("ALTER TABLE gurus MODIFY COLUMN mapel VARCHAR(255) NULL");

        // 3. Buat tabel mata_pelajarans
        if (!Schema::hasTable('mata_pelajarans')) {
            Schema::create('mata_pelajarans', function (Blueprint $table) {
                $table->id();
                $table->string('kode_mapel', 30)->unique();
                $table->string('nama_mapel', 255);
                $table->string('kelompok', 50)->default('Umum'); // Umum, Kejuruan, Muatan Lokal
                $table->text('deskripsi')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 4. Buat tabel teacher_subject_assignments
        if (!Schema::hasTable('teacher_subject_assignments')) {
            Schema::create('teacher_subject_assignments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('guru_id')->constrained('gurus')->cascadeOnDelete();
                $table->foreignId('mata_pelajaran_id')->constrained('mata_pelajarans')->cascadeOnDelete();
                $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
                $table->string('tahun_ajaran', 20)->default('2026/2027');
                $table->date('starts_on')->nullable();
                $table->date('ends_on')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['guru_id', 'mata_pelajaran_id', 'kelas_id', 'tahun_ajaran'], 'uniq_guru_mapel_kelas_ta');
            });
        }

        // 5. Buat tabel homeroom_assignments (Penugasan Wali Kelas dengan Histori)
        if (!Schema::hasTable('homeroom_assignments')) {
            Schema::create('homeroom_assignments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('guru_id')->constrained('gurus')->cascadeOnDelete();
                $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
                $table->string('tahun_ajaran', 20)->default('2026/2027');
                $table->date('starts_on')->nullable();
                $table->date('ends_on')->nullable();
                $table->boolean('is_active')->default(true);
                $table->text('catatan')->nullable();
                $table->timestamps();

                $table->index(['kelas_id', 'tahun_ajaran', 'is_active']);
                $table->index(['guru_id', 'is_active']);
            });
        }

        // 6. Modifikasi tabel jadwals
        Schema::table('jadwals', function (Blueprint $table) {
            if (!Schema::hasColumn('jadwals', 'mata_pelajaran_id')) {
                $table->foreignId('mata_pelajaran_id')->nullable()->after('kelas_id')->constrained('mata_pelajarans')->nullOnDelete();
            }
            if (!Schema::hasColumn('jadwals', 'tahun_ajaran')) {
                $table->string('tahun_ajaran', 20)->default('2026/2027')->after('jam_selesai');
            }
            if (!Schema::hasColumn('jadwals', 'room_id')) {
                $table->foreignId('room_id')->nullable()->after('tahun_ajaran')->constrained('rooms')->nullOnDelete();
            }
            if (!Schema::hasColumn('jadwals', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('room_id');
            }
        });

        // 7. Buat tabel teacher_account_activations
        if (!Schema::hasTable('teacher_account_activations')) {
            Schema::create('teacher_account_activations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
                $table->string('token_hash', 64)->unique();
                $table->timestamp('expires_at');
                $table->timestamp('activated_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_account_activations');

        Schema::table('jadwals', function (Blueprint $table) {
            if (Schema::hasColumn('jadwals', 'room_id')) {
                $table->dropForeign(['room_id']);
                $table->dropColumn('room_id');
            }
            if (Schema::hasColumn('jadwals', 'mata_pelajaran_id')) {
                $table->dropForeign(['mata_pelajaran_id']);
                $table->dropColumn('mata_pelajaran_id');
            }
            if (Schema::hasColumn('jadwals', 'tahun_ajaran')) {
                $table->dropColumn('tahun_ajaran');
            }
            if (Schema::hasColumn('jadwals', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });

        Schema::dropIfExists('homeroom_assignments');
        Schema::dropIfExists('teacher_subject_assignments');
        Schema::dropIfExists('mata_pelajarans');

        Schema::table('gurus', function (Blueprint $table) {
            $table->dropColumn(['nuptk', 'telepon', 'alamat', 'photo_path', 'status_kepegawaian', 'is_active', 'catatan']);
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
