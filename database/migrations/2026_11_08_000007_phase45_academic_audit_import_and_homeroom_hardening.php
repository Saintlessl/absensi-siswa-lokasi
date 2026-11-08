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
        // 1. Add snapshot and history verification fields to student_enrollments
        if (Schema::hasTable('student_enrollments')) {
            Schema::table('student_enrollments', function (Blueprint $table) {
                if (!Schema::hasColumn('student_enrollments', 'homeroom_assignment_id')) {
                    $table->foreignId('homeroom_assignment_id')
                        ->nullable()
                        ->after('catatan')
                        ->constrained('homeroom_assignments')
                        ->nullOnDelete();
                }
                if (!Schema::hasColumn('student_enrollments', 'wali_kelas_guru_id')) {
                    $table->foreignId('wali_kelas_guru_id')
                        ->nullable()
                        ->after('homeroom_assignment_id')
                        ->constrained('gurus')
                        ->nullOnDelete();
                }
                if (!Schema::hasColumn('student_enrollments', 'wali_kelas_nama')) {
                    $table->string('wali_kelas_nama', 255)
                        ->nullable()
                        ->after('wali_kelas_guru_id');
                }
                if (!Schema::hasColumn('student_enrollments', 'status_verifikasi_histori')) {
                    $table->string('status_verifikasi_histori', 50)
                        ->default('terverifikasi')
                        ->after('wali_kelas_nama');
                }
            });

            // Backfill snapshot wali_kelas_nama from HomeroomAssignment or kelas table
            $enrollments = DB::table('student_enrollments')->whereNull('wali_kelas_nama')->get();
            foreach ($enrollments as $enr) {
                $assignment = DB::table('homeroom_assignments')
                    ->where('kelas_id', $enr->kelas_id)
                    ->where(function ($q) use ($enr) {
                        if ($enr->tahun_ajaran_id) {
                            $q->where('tahun_ajaran_id', $enr->tahun_ajaran_id);
                        }
                        if ($enr->tahun_ajaran) {
                            $q->orWhere('tahun_ajaran', $enr->tahun_ajaran);
                        }
                    })
                    ->orderByDesc('is_active')
                    ->first();

                $teacherName = null;
                $guruId = null;
                $assignmentId = null;

                if ($assignment) {
                    $assignmentId = $assignment->id;
                    $guruId = $assignment->guru_id;
                    $teacherName = DB::table('gurus')->where('id', $guruId)->value('nama');
                }

                if (!$teacherName) {
                    $teacherName = DB::table('kelas')->where('id', $enr->kelas_id)->value('wali_kelas');
                }

                if ($teacherName) {
                    DB::table('student_enrollments')->where('id', $enr->id)->update([
                        'homeroom_assignment_id'   => $assignmentId,
                        'wali_kelas_guru_id'       => $guruId,
                        'wali_kelas_nama'          => $teacherName,
                        'status_verifikasi_histori'=> 'terverifikasi',
                    ]);
                }
            }
        }

        // 2. Add replacement tracking columns to homeroom_assignments
        if (Schema::hasTable('homeroom_assignments')) {
            Schema::table('homeroom_assignments', function (Blueprint $table) {
                if (!Schema::hasColumn('homeroom_assignments', 'alasan_pergantian')) {
                    $table->text('alasan_pergantian')->nullable()->after('catatan');
                }
                if (!Schema::hasColumn('homeroom_assignments', 'replaced_by_assignment_id')) {
                    $table->foreignId('replaced_by_assignment_id')
                        ->nullable()
                        ->after('alasan_pergantian')
                        ->constrained('homeroom_assignments')
                        ->nullOnDelete();
                }
            });
        }

        // 3. Create student_import_batches table for audit and import history
        if (!Schema::hasTable('student_import_batches')) {
            Schema::create('student_import_batches', function (Blueprint $table) {
                $table->id();
                $table->string('file_name');
                $table->string('file_path')->nullable();
                $table->string('import_type', 50)->default('campuran'); // aktif, alumni, historis, campuran
                $table->unsignedInteger('total_rows')->default(0);
                $table->unsignedInteger('successful_rows')->default(0);
                $table->unsignedInteger('failed_rows')->default(0);
                $table->unsignedInteger('skipped_rows')->default(0);
                $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
                $table->longText('error_details')->nullable(); // JSON formatted row errors
                $table->foreignId('imported_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_import_batches');

        if (Schema::hasTable('homeroom_assignments')) {
            Schema::table('homeroom_assignments', function (Blueprint $table) {
                $table->dropConstrainedForeignId('replaced_by_assignment_id');
                $table->dropColumn('alasan_pergantian');
            });
        }

        if (Schema::hasTable('student_enrollments')) {
            Schema::table('student_enrollments', function (Blueprint $table) {
                $table->dropConstrainedForeignId('homeroom_assignment_id');
                $table->dropConstrainedForeignId('wali_kelas_guru_id');
                $table->dropColumn(['wali_kelas_nama', 'status_verifikasi_histori']);
            });
        }
    }
};
