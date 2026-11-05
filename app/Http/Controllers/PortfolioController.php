<?php

namespace App\Http\Controllers;

use App\Models\Jurusan;
use App\Models\Siswa;
use App\Models\StudentPortfolio;
use App\Services\InAppNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PortfolioController extends Controller
{
    /**
     * Public / Internal Showcase Exhibition Gallery
     */
    public function index(Request $request)
    {
        $query = StudentPortfolio::with(['siswa.kelas', 'jurusan'])
            ->where('status', 'approved');

        if ($request->filled('jurusan_id')) {
            $query->where('jurusan_id', $request->jurusan_id);
        }

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        if ($request->boolean('tefa_only')) {
            $query->where('is_tefa_product', true);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('judul', 'like', "%{$s}%")
                  ->orWhere('deskripsi', 'like', "%{$s}%")
                  ->orWhereHas('siswa', function ($sq) use ($s) {
                      $sq->where('nama', 'like', "%{$s}%");
                  });
            });
        }

        $portfolios = $query->latest('id')->paginate(12)->withQueryString();
        $jurusans = Jurusan::orderBy('nama_jurusan')->get();

        return view('portfolio.index', compact('portfolios', 'jurusans'));
    }

    /**
     * Single Portfolio Showcase Detail
     */
    public function show($slug)
    {
        $portfolio = StudentPortfolio::with(['siswa.kelas', 'jurusan', 'approver'])
            ->where('slug', $slug)
            ->firstOrFail();

        // Increment view counter
        $portfolio->increment('views_count');

        $relatedPortfolios = StudentPortfolio::where('jurusan_id', $portfolio->jurusan_id)
            ->where('status', 'approved')
            ->where('id', '!=', $portfolio->id)
            ->limit(3)
            ->get();

        return view('portfolio.show', compact('portfolio', 'relatedPortfolios'));
    }

    /**
     * Student My Portfolio Portal
     */
    public function studentIndex()
    {
        $user = Auth::user();
        $siswa = Siswa::where('user_id', $user->id)->firstOrFail();

        $portfolios = StudentPortfolio::with('jurusan')
            ->where('siswa_id', $siswa->id)
            ->latest('id')
            ->get();

        return view('portfolio.student_index', compact('siswa', 'portfolios'));
    }

    /**
     * Store new student project portfolio
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $siswa = Siswa::with('kelas')->where('user_id', $user->id)->firstOrFail();

        $request->validate([
            'judul'          => 'required|string|max:200',
            'kategori'       => 'required|string|max:50',
            'deskripsi'      => 'required|string|min:20',
            'cover_image'    => 'nullable|image|max:3072',
            'demo_url'       => 'nullable|url|max:500',
            'repository_url' => 'nullable|url|max:500',
        ]);

        $coverPath = null;
        if ($request->hasFile('cover_image')) {
            $coverPath = $request->file('cover_image')->store('portfolios/covers', 'public');
        }

        $slug = StudentPortfolio::generateUniqueSlug($request->judul);
        $jurusanId = $siswa->jurusan_id ?? ($siswa->kelas ? $siswa->kelas->jurusan_id : Jurusan::value('id'));

        $portfolio = StudentPortfolio::create([
            'siswa_id'         => $siswa->id,
            'jurusan_id'       => $jurusanId,
            'judul'            => $request->judul,
            'slug'             => $slug,
            'kategori'         => $request->kategori,
            'deskripsi'        => $request->deskripsi,
            'cover_image_path' => $coverPath,
            'demo_url'         => $request->demo_url,
            'repository_url'   => $request->repository_url,
            'status'           => 'submitted', // Auto-submit to moderation queue
        ]);

        return redirect()->route('portfolio.student.index')
            ->with('success', "Karya inovasi '{$portfolio->judul}' berhasil diajukan untuk kurasi etalase karya sekolah.");
    }

    /**
     * Moderation queue (Kaprog / Admin / Guru)
     */
    public function moderationIndex(Request $request)
    {
        $user = Auth::user();
        $query = StudentPortfolio::with(['siswa.kelas', 'jurusan']);

        if ($user->role === 'kaprog' && $user->jurusan_id) {
            $query->where('jurusan_id', $user->jurusan_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->where('status', 'submitted');
        }

        $portfolios = $query->latest('id')->paginate(15)->withQueryString();

        return view('portfolio.moderation', compact('portfolios'));
    }

    /**
     * Moderate portfolio (Approve / Reject / TeFa Tag)
     */
    public function moderate(Request $request, $id, InAppNotificationService $notifService)
    {
        $request->validate([
            'status'           => 'required|in:approved,rejected',
            'moderation_notes' => 'nullable|string|max:500',
            'is_tefa_product'  => 'nullable|boolean',
        ]);

        $portfolio = StudentPortfolio::with('siswa.user')->findOrFail($id);

        $portfolio->update([
            'status'           => $request->status,
            'moderation_notes' => $request->moderation_notes,
            'is_tefa_product'  => $request->boolean('is_tefa_product'),
            'approved_by'      => Auth::id(),
            'approved_at'      => $request->status === 'approved' ? now() : null,
        ]);

        if ($portfolio->siswa && $portfolio->siswa->user) {
            $statusLabel = $request->status === 'approved' ? 'Disetujui & Dipublikasikan' : 'Memerlukan Revisi';
            $notifService->sendToUser(
                $portfolio->siswa->user,
                "Kurasi Portofolio: {$statusLabel}",
                "Karya portofolio '{$portfolio->judul}' telah dikurasi oleh tim Teaching Factory / Kaprog. Status: {$statusLabel}." . ($request->moderation_notes ? " Catatan: {$request->moderation_notes}" : ""),
                'academic'
            );
        }

        return back()->with('success', "Keputusan kurasi untuk '{$portfolio->judul}' berhasil disimpan.");
    }
}
