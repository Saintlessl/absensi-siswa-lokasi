<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\TahunAjaran;
use App\Services\AcademicPromotionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AcademicPromotionController extends Controller
{
    public function __construct(
        protected AcademicPromotionService $promotionService
    ) {}

    /**
     * Authorize admin only access.
     */
    protected function authorizeAdmin(): void
    {
        $user = Auth::user();
        if (!$user || !in_array($user->role, ['superadmin', 'admin'])) {
            abort(403, 'Akses ditolak. Fitur kenaikan kelas dan kelulusan hanya dapat diakses oleh Administrator.');
        }
    }

    /**
     * Step-by-Step Bulk Promotion and Graduation Wizard page.
     */
    public function index(): View
    {
        $this->authorizeAdmin();

        $taData = $this->promotionService->getAcademicYearsForPromotion();
        $gurus = Guru::where('is_active', true)->orderBy('nama')->get();

        return view('academic.promotion.wizard', [
            'academicYears' => $taData['all'],
            'activeTa'      => $taData['active_ta'],
            'originOptions' => $taData['origins'],
            'gurus'         => $gurus,
        ]);
    }

    /**
     * AJAX: Get eligible origin classes that have active student enrollments.
     */
    public function getOriginClasses(Request $request): JsonResponse
    {
        $this->authorizeAdmin();

        $request->validate([
            'origin_ta_id' => 'required|integer|exists:tahun_ajarans,id',
        ]);

        $classes = $this->promotionService->getEligibleOriginClasses((int) $request->origin_ta_id);

        return response()->json([
            'success' => true,
            'classes' => $classes,
        ]);
    }

    /**
     * AJAX: Fetch active students from selected origin classes.
     */
    public function loadStudents(Request $request): JsonResponse
    {
        $this->authorizeAdmin();

        $request->validate([
            'origin_ta_id' => 'required|integer|exists:tahun_ajarans,id',
            'kelas_ids'    => 'required|array|min:1',
            'kelas_ids.*'  => 'integer|exists:kelas,id',
        ]);

        $students = $this->promotionService->getOriginStudents(
            originTaId: (int) $request->origin_ta_id,
            kelasIds: $request->kelas_ids
        );

        return response()->json([
            'success'  => true,
            'total'    => $students->count(),
            'students' => $students,
        ]);
    }

    /**
     * AJAX: Get destination classes for target academic year.
     */
    public function getDestinationClasses(Request $request): JsonResponse
    {
        $this->authorizeAdmin();

        $request->validate([
            'dest_ta_id' => 'required|integer|exists:tahun_ajarans,id',
        ]);

        $classes = $this->promotionService->getDestinationClasses((int) $request->dest_ta_id);

        return response()->json([
            'success' => true,
            'classes' => $classes,
        ]);
    }

    /**
     * AJAX: Replicate class structure from origin TA to destination TA if destination has 0 classes.
     */
    public function replicateClasses(Request $request): JsonResponse
    {
        $this->authorizeAdmin();

        $request->validate([
            'origin_ta_id' => 'required|integer|exists:tahun_ajarans,id',
            'dest_ta_id'   => 'required|integer|exists:tahun_ajarans,id|different:origin_ta_id',
        ]);

        $count = $this->promotionService->replicateClassesForDestinationTa(
            originTaId: (int) $request->origin_ta_id,
            destTaId: (int) $request->dest_ta_id
        );

        $classes = $this->promotionService->getDestinationClasses((int) $request->dest_ta_id);

        return response()->json([
            'success'       => true,
            'message'       => "Berhasil menyiapkan {$count} rombel baru untuk tahun ajaran tujuan.",
            'created_count' => $count,
            'classes'       => $classes,
        ]);
    }

    /**
     * AJAX: Execute promotion batch atomically.
     */
    public function execute(Request $request): JsonResponse
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'tahun_ajaran_asal_id'                => 'required|integer|exists:tahun_ajarans,id',
            'tahun_ajaran_tujuan_id'              => 'required|integer|exists:tahun_ajarans,id|different:tahun_ajaran_asal_id',
            'students'                            => 'required|array|min:1',
            'students.*.siswa_id'                 => 'required|integer|exists:siswas,id',
            'students.*.action'                   => 'required|string|in:naik_kelas,tinggal_kelas,lulus,pindah,keluar',
            'students.*.kelas_tujuan_id'          => 'nullable|integer|exists:kelas,id',
            'students.*.alasan'                   => 'nullable|string|max:255',
            'students.*.catatan'                  => 'nullable|string|max:500',
            'students.*.sekolah_tujuan'           => 'nullable|string|max:255',
            'students.*.nomor_surat'              => 'nullable|string|max:100',
            'homeroom_assignments'                => 'nullable|array',
            'homeroom_assignments.*.kelas_id'     => 'required_with:homeroom_assignments|integer|exists:kelas,id',
            'homeroom_assignments.*.guru_id'      => 'required_with:homeroom_assignments|integer|exists:gurus,id',
            'catatan'                             => 'nullable|string|max:1000',
        ], [
            'tahun_ajaran_tujuan_id.different' => 'Tahun ajaran tujuan tidak boleh sama dengan tahun ajaran asal.',
            'students.required'                => 'Daftar siswa wajib dipilih untuk diproses.',
            'students.min'                     => 'Minimal satu siswa wajib dipilih untuk diproses.',
        ]);

        $batch = $this->promotionService->executePromotionBatch($validated, Auth::user());

        return response()->json([
            'success'      => true,
            'message'      => "Proses kenaikan kelas dan kelulusan berhasil dieksekusi ({$batch->batch_code}).",
            'batch_id'     => $batch->id,
            'batch_code'   => $batch->batch_code,
            'redirect_url' => route('academic.promotion.show', $batch->id),
            'summary'      => [
                'total_siswa'   => $batch->total_siswa,
                'total_naik'    => $batch->total_naik,
                'total_tinggal' => $batch->total_tinggal,
                'total_lulus'   => $batch->total_lulus,
                'total_pindah'  => $batch->total_pindah,
                'total_keluar'  => $batch->total_keluar,
            ],
        ]);
    }

    /**
     * History list of previous promotion batches.
     */
    public function history(): View
    {
        $this->authorizeAdmin();

        $batches = $this->promotionService->getPromotionBatches(15);

        return view('academic.promotion.history', [
            'batches' => $batches,
        ]);
    }

    /**
     * Detail report of a specific promotion batch.
     */
    public function show(int $id): View
    {
        $this->authorizeAdmin();

        $batch = $this->promotionService->getPromotionBatchDetail($id);

        return view('academic.promotion.show', [
            'batch' => $batch,
        ]);
    }
}
