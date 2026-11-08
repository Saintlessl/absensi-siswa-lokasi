<?php

namespace App\Services;

use App\Models\AcademicPromotionBatch;
use App\Models\AcademicPromotionItem;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\StudentEnrollment;
use App\Models\TahunAjaran;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AcademicPromotionService
{
    public function __construct(
        protected StudentAcademicHistoryService $historyService,
        protected TeacherAssignmentService $teacherService
    ) {}

    /**
     * Get academic years structured for the wizard.
     */
    public function getAcademicYearsForPromotion(): array
    {
        $all = TahunAjaran::orderBy('tahun_mulai')->get();
        $activeTa = TahunAjaran::where('is_active', true)->first();

        // Origin candidates: academic years that have active enrollments or are active
        $origins = $all->filter(function ($ta) {
            return StudentEnrollment::where('tahun_ajaran_id', $ta->id)->where('is_active', true)->exists()
                || $ta->is_active;
        })->values();

        // If no origins have active enrollments, default to all except the very last
        if ($origins->isEmpty()) {
            $origins = $all;
        }

        return [
            'all'       => $all,
            'active_ta' => $activeTa,
            'origins'   => $origins,
        ];
    }

    /**
     * Get eligible origin classes for a given academic year that have active enrollments.
     */
    public function getEligibleOriginClasses(int $originTaId): \Illuminate\Support\Collection
    {
        $originTa = TahunAjaran::findOrFail($originTaId);

        // Fetch classes that have active enrollments in this academic year
        $activeEnrollments = StudentEnrollment::where('tahun_ajaran_id', $originTaId)
            ->where('is_active', true)
            ->select('kelas_id', DB::raw('count(*) as total_siswa'))
            ->groupBy('kelas_id')
            ->pluck('total_siswa', 'kelas_id');

        $kelasIds = $activeEnrollments->keys();

        if ($kelasIds->isEmpty()) {
            // Also include classes tagged directly with this tahun_ajaran_id
            $classes = Kelas::with(['jurusan', 'activeHomeroom.guru'])
                ->where('tahun_ajaran_id', $originTaId)
                ->orderBy('tingkat')
                ->orderBy('nama_kelas')
                ->get();
        } else {
            $classes = Kelas::with(['jurusan', 'activeHomeroom.guru'])
                ->whereIn('id', $kelasIds)
                ->orderBy('tingkat')
                ->orderBy('nama_kelas')
                ->get();
        }

        return $classes->map(function ($kelas) use ($activeEnrollments, $originTaId) {
            $totalActive = $activeEnrollments[$kelas->id] ?? StudentEnrollment::where('kelas_id', $kelas->id)
                ->where('tahun_ajaran_id', $originTaId)
                ->where('is_active', true)
                ->count();

            $homeroomName = $this->historyService->resolveHomeroomTeacherName($kelas->id, $originTaId);

            return [
                'id'                 => $kelas->id,
                'nama_kelas'         => $kelas->nama_kelas,
                'tingkat'            => (int) ($kelas->tingkat ?: 10),
                'nomor_rombel'       => $kelas->nomor_rombel,
                'jurusan_id'         => $kelas->jurusan_id,
                'jurusan_kode'       => $kelas->jurusan?->kode_jurusan ?? '-',
                'jurusan_nama'       => $kelas->jurusan?->nama_jurusan ?? '-',
                'wali_kelas'         => $homeroomName,
                'total_siswa_aktif'  => $totalActive,
            ];
        });
    }

    /**
     * Get origin students with active enrollments from selected classes.
     */
    public function getOriginStudents(int $originTaId, array $kelasIds): \Illuminate\Support\Collection
    {
        $enrollments = StudentEnrollment::with(['siswa.angkatan', 'kelas.jurusan', 'jurusan', 'angkatan'])
            ->where('tahun_ajaran_id', $originTaId)
            ->where('is_active', true)
            ->whereHas('siswa', function ($q) {
                $q->where('status', 'aktif');
            })
            ->whereIn('kelas_id', $kelasIds)
            ->orderBy('kelas_id')
            ->get();

        return $enrollments->map(function ($enrollment) {
            $siswa = $enrollment->siswa;
            $currentTingkat = (int) ($enrollment->tingkat ?: $enrollment->kelas?->tingkat ?: 10);

            // Determine default smart action
            if ($currentTingkat >= 12) {
                $defaultAction = 'lulus';
                $recommendedTargetTingkat = null;
            } else {
                $defaultAction = 'naik_kelas';
                $recommendedTargetTingkat = $currentTingkat + 1;
            }

            return [
                'enrollment_id'             => $enrollment->id,
                'siswa_id'                  => $siswa->id,
                'nis'                       => $siswa->nis,
                'nisn'                      => $siswa->nisn,
                'nama'                      => $siswa->nama,
                'jenis_kelamin'             => $siswa->jenis_kelamin,
                'kelas_asal_id'             => $enrollment->kelas_id,
                'kelas_asal_nama'           => $enrollment->kelas?->nama_kelas ?? '-',
                'tingkat_asal'              => $currentTingkat,
                'jurusan_id'                => $enrollment->jurusan_id ?: $siswa->jurusan_id,
                'jurusan_kode'              => $enrollment->jurusan?->kode_jurusan ?? $enrollment->kelas?->jurusan?->kode_jurusan ?? '-',
                'angkatan_id'               => $siswa->angkatan_id,
                'angkatan_nama'             => $siswa->angkatan?->nama_angkatan ?? ($siswa->tahun_masuk ? 'Angkatan ' . $siswa->tahun_masuk : '-'),
                'tahun_masuk'               => $siswa->tahun_masuk,
                'tahun_ajaran_masuk'        => $siswa->tahun_ajaran_masuk,
                'default_action'            => $defaultAction,
                'recommended_target_tingkat'=> $recommendedTargetTingkat,
                'wali_kelas_asal'           => $enrollment->wali_kelas_nama ?: '-',
            ];
        });
    }

    /**
     * Get classes belonging to the destination academic year.
     */
    public function getDestinationClasses(int $destTaId): \Illuminate\Support\Collection
    {
        $destTa = TahunAjaran::findOrFail($destTaId);

        $classes = Kelas::with(['jurusan', 'homeroomAssignments' => function ($q) use ($destTaId) {
            $q->where('tahun_ajaran_id', $destTaId)->where('is_active', true)->with('guru');
        }])
            ->where(function ($q) use ($destTaId, $destTa) {
                $q->where('tahun_ajaran_id', $destTaId)
                  ->orWhere('tahun_ajaran', $destTa->nama);
            })
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->get();

        return $classes->map(function ($k) use ($destTaId) {
            $assignedHomeroom = $k->homeroomAssignments->first();
            $currentEnrollmentCount = StudentEnrollment::where('kelas_id', $k->id)
                ->where('tahun_ajaran_id', $destTaId)
                ->where('is_active', true)
                ->count();

            return [
                'id'                 => $k->id,
                'nama_kelas'         => $k->nama_kelas,
                'tingkat'            => (int) ($k->tingkat ?: 10),
                'nomor_rombel'       => $k->nomor_rombel,
                'jurusan_id'         => $k->jurusan_id,
                'jurusan_kode'       => $k->jurusan?->kode_jurusan ?? '-',
                'jurusan_nama'       => $k->jurusan?->nama_jurusan ?? '-',
                'kapasitas'          => 36, // Standard SMK capacity
                'total_terisi'       => $currentEnrollmentCount,
                'wali_kelas_guru_id' => $assignedHomeroom?->guru_id,
                'wali_kelas_nama'    => $assignedHomeroom?->guru?->nama ?? $k->wali_kelas ?? null,
            ];
        });
    }

    /**
     * Replicate class structure from origin academic year to destination academic year if destination has no classes.
     */
    public function replicateClassesForDestinationTa(int $originTaId, int $destTaId): int
    {
        $originTa = TahunAjaran::findOrFail($originTaId);
        $destTa = TahunAjaran::findOrFail($destTaId);

        if ($destTa->id === $originTa->id || $destTa->tahun_mulai <= $originTa->tahun_mulai) {
            throw ValidationException::withMessages([
                'dest_ta_id' => ['Tahun ajaran tujuan harus secara kronologis lebih baru daripada tahun ajaran asal.'],
            ]);
        }

        $originClasses = Kelas::where('tahun_ajaran_id', $originTaId)->get();

        if ($originClasses->isEmpty()) {
            // Fallback: take active classes from active TA or first available
            $originClasses = Kelas::where('is_active', true)->get();
        }

        $createdCount = 0;

        DB::transaction(function () use ($originClasses, $destTa, &$createdCount) {
            foreach ($originClasses as $orig) {
                $exists = Kelas::where(function ($q) use ($destTa) {
                        $q->where('tahun_ajaran_id', $destTa->id)
                          ->orWhere('tahun_ajaran', $destTa->nama);
                    })
                    ->where(function ($q) use ($orig) {
                        $q->where('nama_kelas', $orig->nama_kelas)
                          ->orWhere(function ($sub) use ($orig) {
                              $sub->where('jurusan_id', $orig->jurusan_id)
                                  ->where('tingkat', $orig->tingkat)
                                  ->where('nomor_rombel', $orig->nomor_rombel);
                          });
                    })
                    ->exists();

                if (!$exists) {
                    Kelas::create([
                        'jurusan_id'      => $orig->jurusan_id,
                        'qr_token'        => 'QR-' . Str::slug($orig->nama_kelas) . '-' . Str::random(12),
                        'nama_kelas'      => $orig->nama_kelas,
                        'jumlah_siswa'    => 0,
                        'wali_kelas'      => null,
                        'tingkat'         => $orig->tingkat,
                        'nomor_rombel'    => $orig->nomor_rombel,
                        'tahun_ajaran'    => $destTa->nama,
                        'tahun_ajaran_id' => $destTa->id,
                        'is_active'       => true,
                    ]);
                    $createdCount++;
                }
            }
        });

        return $createdCount;
    }

    /**
     * Execute the atomic 6-step Bulk Promotion and Graduation process.
     * Wrapped completely in DB::transaction() with lockForUpdate().
     */
    public function executePromotionBatch(array $payload, ?User $actor = null): AcademicPromotionBatch
    {
        return DB::transaction(function () use ($payload, $actor) {
            $actorId = $actor?->id ?? Auth::id();
            $originTaId = (int) $payload['tahun_ajaran_asal_id'];
            $destTaId = (int) $payload['tahun_ajaran_tujuan_id'];
            $studentsData = $payload['students'] ?? [];
            $homeroomData = $payload['homeroom_assignments'] ?? [];
            $batchNotes = $payload['catatan'] ?? null;

            // 1. Validate Academic Years
            $originTa = TahunAjaran::lockForUpdate()->findOrFail($originTaId);
            $destTa = TahunAjaran::lockForUpdate()->findOrFail($destTaId);

            if ($destTa->id === $originTa->id || $destTa->tahun_mulai <= $originTa->tahun_mulai) {
                throw ValidationException::withMessages([
                    'tahun_ajaran_tujuan_id' => ['Tahun ajaran tujuan harus secara kronologis lebih baru daripada tahun ajaran asal.'],
                ]);
            }

            if (empty($studentsData)) {
                throw ValidationException::withMessages([
                    'students' => ['Tidak ada siswa yang dipilih untuk diproses dalam batch ini.'],
                ]);
            }

            // Validate no duplicate student entries in payload
            $studentIds = array_column($studentsData, 'siswa_id');
            if (count($studentIds) !== count(array_unique($studentIds))) {
                throw ValidationException::withMessages([
                    'students' => ['Terdapat duplikasi data siswa di dalam payload permintaan eksekusi.'],
                ]);
            }

            // Validate no duplicate teacher assignments in target classes
            $assignedGuruIds = array_filter(array_column($homeroomData, 'guru_id'));
            if (count($assignedGuruIds) !== count(array_unique($assignedGuruIds))) {
                throw ValidationException::withMessages([
                    'homeroom_assignments' => ['Satu guru tidak boleh ditugaskan sebagai wali kelas pada lebih dari satu rombel pada tahun ajaran yang sama.'],
                ]);
            }

            // Generate unique batch code
            $batchCode = 'PROM-' . date('Ymd-His') . '-' . strtoupper(Str::random(4));

            // Create initial batch log
            $batch = AcademicPromotionBatch::create([
                'batch_code'             => $batchCode,
                'tahun_ajaran_asal_id'   => $originTa->id,
                'tahun_ajaran_tujuan_id' => $destTa->id,
                'total_siswa'            => count($studentsData),
                'total_naik'             => 0,
                'total_tinggal'          => 0,
                'total_lulus'            => 0,
                'total_pindah'           => 0,
                'total_keluar'           => 0,
                'status'                 => 'completed',
                'summary_meta'           => [
                    'executed_by'        => $actor?->name ?? 'Admin',
                    'executed_at'        => Carbon::now()->toIso8601String(),
                    'origin_ta'          => $originTa->nama,
                    'dest_ta'            => $destTa->nama,
                ],
                'catatan'                => $batchNotes,
                'created_by'             => $actorId,
            ]);

            $totalNaik = 0;
            $totalTinggal = 0;
            $totalLulus = 0;
            $totalPindah = 0;
            $totalKeluar = 0;

            // 2. First pass: Apply Homeroom Assignments if specified for destination classes
            foreach ($homeroomData as $assignmentItem) {
                $targetKelasId = (int) ($assignmentItem['kelas_id'] ?? 0);
                $guruId = (int) ($assignmentItem['guru_id'] ?? 0);

                if ($targetKelasId > 0 && $guruId > 0) {
                    $this->teacherService->assignHomeroom(
                        guruId: $guruId,
                        kelasId: $targetKelasId,
                        tahunAjaran: $destTa->id,
                        catatan: 'Penetapan wali kelas pada proses kenaikan kelas massal ' . $batchCode
                    );
                }
            }

            // 3. Second pass: Process each student atomically
            foreach ($studentsData as $studentItem) {
                $siswaId = (int) $studentItem['siswa_id'];
                $action = $studentItem['action']; // naik_kelas, tinggal_kelas, lulus, pindah, keluar
                $targetKelasId = !empty($studentItem['kelas_tujuan_id']) ? (int) $studentItem['kelas_tujuan_id'] : null;
                $alasan = $studentItem['alasan'] ?? null;
                $catatanItem = $studentItem['catatan'] ?? null;

                // Lock student record to avoid concurrency issues
                $siswa = Siswa::with('kelas')->lockForUpdate()->findOrFail($siswaId);
                $originKelasId = $siswa->kelas_id;
                $originTingkat = $siswa->kelas?->tingkat ?: 10;

                $targetKelas = null;
                $targetTingkat = null;

                if (in_array($action, ['naik_kelas', 'tinggal_kelas'])) {
                    if (!$targetKelasId) {
                        throw ValidationException::withMessages([
                            'students' => ["Siswa {$siswa->nama} ({$siswa->nis}) berstatus {$action} tetapi belum dialokasikan ke rombel tujuan."],
                        ]);
                    }

                    $targetKelas = Kelas::lockForUpdate()->findOrFail($targetKelasId);
                    $targetTingkat = (int) ($targetKelas->tingkat ?: 10);

                    // Validate class belongs to destination TA
                    if ($targetKelas->tahun_ajaran_id && $targetKelas->tahun_ajaran_id !== $destTa->id) {
                        throw ValidationException::withMessages([
                            'students' => ["Rombel tujuan {$targetKelas->nama_kelas} tidak berada pada tahun ajaran tujuan ({$destTa->nama})."],
                        ]);
                    }

                    // Check duplicate active enrollment in destination TA
                    $this->historyService->preventDuplicateActiveEnrollment($siswa->id, $destTa->id);
                }

                // Execute action using StudentAcademicHistoryService primitives
                switch ($action) {
                    case 'naik_kelas':
                        $this->historyService->promoteStudent(
                            siswa: $siswa,
                            targetKelas: $targetKelas,
                            targetTa: $destTa,
                            catatan: $catatanItem ?: 'Kenaikan kelas massal batch ' . $batchCode,
                            actor: $actor
                        );
                        $totalNaik++;
                        break;

                    case 'tinggal_kelas':
                        $this->historyService->retainStudent(
                            siswa: $siswa,
                            targetKelas: $targetKelas,
                            targetTa: $destTa,
                            alasan: $alasan ?: 'Tinggal kelas batch ' . $batchCode,
                            actor: $actor
                        );
                        $totalTinggal++;
                        break;

                    case 'lulus':
                        $this->historyService->graduateStudent(
                            siswa: $siswa,
                            taLulus: $originTa,
                            catatan: $catatanItem ?: 'Kelulusan massal batch ' . $batchCode,
                            actor: $actor
                        );
                        $totalLulus++;
                        break;

                    case 'pindah':
                        $sekolahTujuan = $studentItem['sekolah_tujuan'] ?? 'Sekolah Lain';
                        $nomorSurat = $studentItem['nomor_surat'] ?? 'SK-MUTASI/' . $batchCode;

                        $this->historyService->transferOutStudent(
                            siswa: $siswa,
                            targetSchool: $sekolahTujuan,
                            nomorSurat: $nomorSurat,
                            alasan: $alasan ?: 'Mutasi keluar pada akhir tahun ajaran',
                            ta: $originTa,
                            actor: $actor
                        );
                        $totalPindah++;
                        break;

                    case 'keluar':
                        $this->historyService->dropOutStudent(
                            siswa: $siswa,
                            alasan: $alasan ?: 'Keluar / Drop Out pada batch ' . $batchCode,
                            ta: $originTa,
                            actor: $actor
                        );
                        $totalKeluar++;
                        break;

                    default:
                        throw ValidationException::withMessages([
                            'students' => ["Aksi '{$action}' untuk siswa {$siswa->nama} tidak valid."],
                        ]);
                }

                // Log promotion item
                AcademicPromotionItem::create([
                    'batch_id'        => $batch->id,
                    'siswa_id'        => $siswa->id,
                    'action'          => $action,
                    'kelas_asal_id'   => $originKelasId,
                    'kelas_tujuan_id' => $targetKelasId,
                    'tingkat_asal'    => $originTingkat,
                    'tingkat_tujuan'  => $targetTingkat,
                    'status'          => 'success',
                    'alasan'          => $alasan,
                    'catatan'         => $catatanItem,
                ]);
            }

            // 4. Update batch counters
            $batch->update([
                'total_naik'    => $totalNaik,
                'total_tinggal' => $totalTinggal,
                'total_lulus'   => $totalLulus,
                'total_pindah'  => $totalPindah,
                'total_keluar'  => $totalKeluar,
            ]);

            return $batch;
        });
    }

    /**
     * Get paginated promotion batches for history view.
     */
    public function getPromotionBatches(int $perPage = 15): LengthAwarePaginator
    {
        return AcademicPromotionBatch::with(['tahunAjaranAsal', 'tahunAjaranTujuan', 'creator'])
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    /**
     * Get single promotion batch details.
     */
    public function getPromotionBatchDetail(int $batchId): AcademicPromotionBatch
    {
        return AcademicPromotionBatch::with([
            'tahunAjaranAsal',
            'tahunAjaranTujuan',
            'creator',
            'items.siswa.angkatan',
            'items.kelasAsal',
            'items.kelasTujuan',
        ])->findOrFail($batchId);
    }
}
