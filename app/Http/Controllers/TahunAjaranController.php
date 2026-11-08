<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TahunAjaranController extends Controller
{
    /**
     * Authorize user access for academic year management.
     */
    protected function authorizeAdmin(): void
    {
        $user = Auth::user();
        if (!$user || !in_array($user->role, ['superadmin', 'admin'])) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengelola Tahun Ajaran.');
        }
    }

    /**
     * Display a listing of academic years.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $search = trim($request->get('search', ''));
        $statusFilter = $request->get('status');

        $query = TahunAjaran::query()
            ->withCount(['kelas', 'enrollments'])
            ->with('creator')
            ->orderByDesc('tahun_mulai');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('tahun_mulai', 'like', "%{$search}%")
                  ->orWhere('catatan', 'like', "%{$search}%");
            });
        }

        if (!empty($statusFilter) && in_array($statusFilter, ['draft', 'aktif', 'selesai', 'diarsipkan'])) {
            $query->where('status', $statusFilter);
        }

        $tahunAjarans = $query->paginate(10)->withQueryString();

        // Active Academic Year with statistics
        $activeTahunAjaran = TahunAjaran::withCount(['kelas', 'enrollments'])->where('is_active', true)->first();
        $totalClasses = Kelas::where('is_active', true)->count();
        $totalActiveStudents = Siswa::where('status', 'aktif')->count();

        $stats = [
            'total_periods'   => TahunAjaran::count(),
            'active_period'   => $activeTahunAjaran,
            'total_classes'   => $totalClasses,
            'active_students' => $totalActiveStudents,
        ];

        return view('academic.tahun-ajaran.index', compact('tahunAjarans', 'activeTahunAjaran', 'stats', 'search', 'statusFilter', 'user'));
    }

    /**
     * Store a newly created academic year.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'tahun_mulai'     => 'required|integer|min:2000|max:2099',
            'tahun_selesai'   => 'required|integer|gt:tahun_mulai',
            'nama'            => 'required|string|max:20|unique:tahun_ajarans,nama',
            'semester_aktif'  => 'required|in:ganjil,genap',
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai',
            'status'          => 'nullable|in:draft,aktif,selesai,diarsipkan',
            'is_active'       => 'nullable|boolean',
            'catatan'         => 'nullable|string|max:500',
        ], [
            'nama.unique'           => 'Tahun Ajaran dengan nama tersebut sudah ada di sistem.',
            'tahun_selesai.gt'      => 'Tahun selesai harus lebih besar dari tahun mulai.',
            'tanggal_selesai.after' => 'Tanggal selesai harus setelah tanggal mulai.',
        ]);

        $validated['created_by'] = Auth::id();
        $validated['status'] = $validated['status'] ?? 'draft';
        $validated['is_active'] = $request->boolean('is_active', false);

        if ($validated['is_active']) {
            $validated['status'] = 'aktif';
        }

        $ta = TahunAjaran::create($validated);

        return redirect()->route('academic.tahun-ajaran.index')
            ->with('success', "Tahun Ajaran {$ta->nama} berhasil dibuat.");
    }

    /**
     * Return edit form via AJAX / modal.
     */
    public function edit(int $id): JsonResponse|View
    {
        $this->authorizeAdmin();

        $tahunAjaran = TahunAjaran::findOrFail($id);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'data'    => $tahunAjaran,
            ]);
        }

        return view('academic.tahun-ajaran.edit', compact('tahunAjaran'));
    }

    /**
     * Update the specified academic year.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $this->authorizeAdmin();

        $tahunAjaran = TahunAjaran::findOrFail($id);

        $validated = $request->validate([
            'tahun_mulai'     => 'required|integer|min:2000|max:2099',
            'tahun_selesai'   => 'required|integer|gt:tahun_mulai',
            'nama'            => ['required', 'string', 'max:20', Rule::unique('tahun_ajarans', 'nama')->ignore($tahunAjaran->id)],
            'semester_aktif'  => 'required|in:ganjil,genap',
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai',
            'status'          => 'nullable|in:draft,aktif,selesai,diarsipkan',
            'is_active'       => 'nullable|boolean',
            'catatan'         => 'nullable|string|max:500',
        ]);

        $validated['is_active'] = $request->boolean('is_active', $tahunAjaran->is_active);

        // If activating, sync status
        if ($validated['is_active']) {
            $validated['status'] = 'aktif';
        }

        $tahunAjaran->update($validated);

        return redirect()->route('academic.tahun-ajaran.index')
            ->with('success', "Tahun Ajaran {$tahunAjaran->nama} berhasil diperbarui.");
    }

    /**
     * Set a specific academic year as active (enforces single active period).
     */
    public function activate(int $id): RedirectResponse
    {
        $this->authorizeAdmin();

        $tahunAjaran = TahunAjaran::findOrFail($id);
        $tahunAjaran->activate();

        return redirect()->route('academic.tahun-ajaran.index')
            ->with('success', "Tahun Ajaran {$tahunAjaran->nama} berhasil diaktifkan sebagai periode operasional sekolah.");
    }

    /**
     * Toggle the active semester of an academic year.
     */
    public function switchSemester(int $id): RedirectResponse
    {
        $this->authorizeAdmin();

        $tahunAjaran = TahunAjaran::findOrFail($id);
        $newSemester = $tahunAjaran->toggleSemester();
        $label = ucfirst($newSemester);

        return redirect()->route('academic.tahun-ajaran.index')
            ->with('success', "Semester aktif untuk {$tahunAjaran->nama} berhasil diubah menjadi Semester {$label}.");
    }

    /**
     * Remove the specified academic year.
     */
    public function destroy(int $id): RedirectResponse
    {
        $this->authorizeAdmin();

        $tahunAjaran = TahunAjaran::withCount(['kelas', 'enrollments'])->findOrFail($id);

        if ($tahunAjaran->is_active) {
            return redirect()->route('academic.tahun-ajaran.index')
                ->with('error', "Tahun Ajaran aktif ({$tahunAjaran->nama}) tidak dapat dihapus. Silakan aktifkan periode lain terlebih dahulu.");
        }

        if ($tahunAjaran->kelas_count > 0) {
            return redirect()->route('academic.tahun-ajaran.index')
                ->with('error', "Tahun Ajaran {$tahunAjaran->nama} masih memiliki {$tahunAjaran->kelas_count} rombel/kelas terkait dan tidak dapat dihapus.");
        }

        if ($tahunAjaran->enrollments_count > 0) {
            return redirect()->route('academic.tahun-ajaran.index')
                ->with('error', "Tahun Ajaran {$tahunAjaran->nama} memiliki riwayat enrollment siswa dan tidak dapat dihapus.");
        }

        if ($tahunAjaran->promotionBatchesAsOrigin()->exists() || $tahunAjaran->promotionBatchesAsDestination()->exists()) {
            return redirect()->route('academic.tahun-ajaran.index')
                ->with('error', "Tahun Ajaran {$tahunAjaran->nama} memiliki catatan riwayat audit kenaikan kelas / kelulusan (Promotion Batch) dan tidak dapat dihapus.");
        }

        $nama = $tahunAjaran->nama;
        $tahunAjaran->delete();

        return redirect()->route('academic.tahun-ajaran.index')
            ->with('success', "Tahun Ajaran {$nama} berhasil dihapus.");
    }
}
