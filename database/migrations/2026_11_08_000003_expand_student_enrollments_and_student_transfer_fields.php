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
        // 1. Expand student_enrollments table with additive tracking columns
        if (Schema::hasTable('student_enrollments')) {
            Schema::table('student_enrollments', function (Blueprint $table) {
                if (!Schema::hasColumn('student_enrollments', 'tahun_ajaran_id')) {
                    $table->foreignId('tahun_ajaran_id')->nullable()->after('siswa_id')->constrained('tahun_ajarans')->nullOnDelete();
                }
                if (!Schema::hasColumn('student_enrollments', 'jurusan_id')) {
                    $table->foreignId('jurusan_id')->nullable()->after('kelas_id')->constrained('jurusans')->nullOnDelete();
                }
                if (!Schema::hasColumn('student_enrollments', 'angkatan_id')) {
                    $table->foreignId('angkatan_id')->nullable()->after('jurusan_id')->constrained('angkatans')->nullOnDelete();
                }
                if (!Schema::hasColumn('student_enrollments', 'tingkat')) {
                    $table->unsignedTinyInteger('tingkat')->nullable()->after('angkatan_id');
                }
                if (!Schema::hasColumn('student_enrollments', 'status_enrollment')) {
                    $table->enum('status_enrollment', ['aktif', 'naik_kelas', 'tinggal_kelas', 'lulus', 'tidak_lulus', 'mutasi', 'keluar'])
                        ->default('aktif')
                        ->after('is_active');
                }
                if (!Schema::hasColumn('student_enrollments', 'catatan')) {
                    $table->text('catatan')->nullable()->after('status_enrollment');
                }
            });

            // Backfill existing enrollments from their related kelas and siswa
            $enrollments = DB::table('student_enrollments')->get();
            foreach ($enrollments as $enr) {
                $kelas = DB::table('kelas')->where('id', $enr->kelas_id)->first();
                $siswa = DB::table('siswas')->where('id', $enr->siswa_id)->first();

                $taId = $kelas?->tahun_ajaran_id;
                if (!$taId && $enr->tahun_ajaran) {
                    $ta = DB::table('tahun_ajarans')->where('nama', $enr->tahun_ajaran)->first();
                    $taId = $ta?->id;
                }

                DB::table('student_enrollments')->where('id', $enr->id)->update([
                    'tahun_ajaran_id'   => $taId,
                    'jurusan_id'        => $kelas?->jurusan_id,
                    'angkatan_id'       => $siswa?->angkatan_id,
                    'tingkat'           => $kelas?->tingkat,
                    'status_enrollment' => 'aktif',
                    'catatan'           => $enr->catatan ?? 'Enrollment baseline terdaftar',
                ]);
            }
        }

        // 2. Add transfer student support columns to siswas
        if (Schema::hasTable('siswas')) {
            Schema::table('siswas', function (Blueprint $table) {
                if (!Schema::hasColumn('siswas', 'jalur_masuk')) {
                    $table->enum('jalur_masuk', ['baru', 'pindahan'])->default('baru')->after('status');
                }
                if (!Schema::hasColumn('siswas', 'asal_sekolah')) {
                    $table->string('asal_sekolah', 150)->nullable()->after('catatan_status');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('siswas')) {
            Schema::table('siswas', function (Blueprint $table) {
                if (Schema::hasColumn('siswas', 'asal_sekolah')) {
                    $table->dropColumn('asal_sekolah');
                }
                if (Schema::hasColumn('siswas', 'jalur_masuk')) {
                    $table->dropColumn('jalur_masuk');
                }
            });
        }

        if (Schema::hasTable('student_enrollments')) {
            Schema::table('student_enrollments', function (Blueprint $table) {
                if (Schema::hasColumn('student_enrollments', 'angkatan_id')) {
                    $table->dropForeign(['angkatan_id']);
                    $table->dropColumn('angkatan_id');
                }
                if (Schema::hasColumn('student_enrollments', 'jurusan_id')) {
                    $table->dropForeign(['jurusan_id']);
                    $table->dropColumn('jurusan_id');
                }
                if (Schema::hasColumn('student_enrollments', 'tahun_ajaran_id')) {
                    $table->dropForeign(['tahun_ajaran_id']);
                    $table->dropColumn('tahun_ajaran_id');
                }
                if (Schema::hasColumn('student_enrollments', 'catatan')) {
                    $table->dropColumn('catatan');
                }
                if (Schema::hasColumn('student_enrollments', 'status_enrollment')) {
                    $table->dropColumn('status_enrollment');
                }
                if (Schema::hasColumn('student_enrollments', 'tingkat')) {
                    $table->dropColumn('tingkat');
                }
            });
        }
    }
};
