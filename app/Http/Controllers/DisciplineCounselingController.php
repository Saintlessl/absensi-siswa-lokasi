<?php

namespace App\Http\Controllers;

use App\Models\CounselingSession;
use App\Models\DisciplineCategory;
use App\Models\DisciplineRecord;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use App\Services\InAppNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DisciplineCounselingController extends Controller
{
    /**
     * Discipline & Achievement Records Dashboard
     */
    public function disciplineIndex(Request $request)
    {
        $user = Auth::user();
        $query = DisciplineRecord::with(['siswa.kelas', 'category', 'recordedBy']);

        // Scoping for Homeroom / Kaprog
        if ($user->role === 'kaprog' && $user->jurusan_id) {
            $query->whereHas('siswa', function ($q) use ($user) {
                $q->where('jurusan_id', $user->jurusan_id);
            });
        }

        if ($request->filled('tipe')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('tipe', $request->tipe);
            });
        }

        if ($request->filled('kelas_id')) {
            $query->whereHas('siswa', function ($q) use ($request) {
                $q->where('kelas_id', $request->kelas_id);
            });
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('judul', 'like', "%{$s}%")
                  ->orWhereHas('siswa', function ($sq) use ($s) {
                      $sq->where('nama', 'like', "%{$s}%")->orWhere('nis', 'like', "%{$s}%");
                  });
            });
        }

        $records = $query->latest('tanggal')->latest('id')->paginate(20)->withQueryString();
        $categories = DisciplineCategory::where('is_active', true)->orderBy('tipe')->orderBy('nama')->get();
        $classes = Kelas::orderBy('nama_kelas')->get();
        $siswas = Siswa::with('kelas')->orderBy('nama')->get();

        // Overall statistics
        $stats = [
            'total_pelanggaran' => DisciplineRecord::whereHas('category', fn($q) => $q->where('tipe', 'pelanggaran'))->count(),
            'total_prestasi'    => DisciplineRecord::whereHas('category', fn($q) => $q->where('tipe', 'prestasi'))->count(),
            'poin_tercatat'     => DisciplineRecord::sum('poin'),
            'kasus_aktif'       => DisciplineRecord::where('status', 'recorded')->count(),
        ];

        return view('discipline.index', compact('records', 'categories', 'classes', 'siswas', 'stats'));
    }

    /**
     * Store new discipline / achievement record
     */
    public function disciplineStore(Request $request, InAppNotificationService $notifService)
    {
        $request->validate([
            'siswa_id'               => 'required|exists:siswas,id',
            'discipline_category_id' => 'required|exists:discipline_categories,id',
            'tanggal'                => 'required|date|before_or_equal:today',
            'judul'                  => 'required|string|max:200',
            'deskripsi'              => 'required|string',
            'poin'                   => 'nullable|integer',
            'bukti'                  => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:3072',
        ]);

        $category = DisciplineCategory::findOrFail($request->discipline_category_id);
        $poin = $request->poin ?? $category->default_poin;

        $buktiPath = null;
        if ($request->hasFile('bukti')) {
            $buktiPath = $request->file('bukti')->store('discipline/evidence', 'public');
        }

        $record = DisciplineRecord::create([
            'siswa_id'               => $request->siswa_id,
            'discipline_category_id' => $category->id,
            'tanggal'                => $request->tanggal,
            'judul'                  => $request->judul,
            'deskripsi'              => $request->deskripsi,
            'bukti_path'             => $buktiPath,
            'poin'                   => $poin,
            'status'                 => 'recorded',
            'recorded_by'            => Auth::id(),
        ]);

        $siswa = Siswa::with(['user', 'orangTua.user'])->find($request->siswa_id);

        // Notify student & parents
        if ($siswa && $siswa->user) {
            $notifType = $category->tipe === 'prestasi' ? 'academic' : 'administrative';
            $notifTitle = $category->tipe === 'prestasi' ? "Penghargaan Prestasi Siswa" : "Catatan Kedisiplinan Siswa";
            $notifDesc = "Catatan {$category->tipe} baru: '{$record->judul}' ({$poin} poin) dicatat pada {$record->tanggal->format('d/m/Y')}.";

            $notifService->sendToUser($siswa->user, $notifTitle, $notifDesc, $notifType);

            if ($siswa->orangTua) {
                foreach ($siswa->orangTua as $ot) {
                    if ($ot->user) {
                        $notifService->sendToUser($ot->user, $notifTitle, $notifDesc, $notifType);
                    }
                }
            }
        }

        return redirect()->route('discipline.index')
            ->with('success', "Catatan {$category->tipe} untuk {$siswa->nama} berhasil disimpan.");
    }

    /**
     * Verify or update action follow-up
     */
    public function disciplineUpdateStatus(Request $request, $id)
    {
        $request->validate([
            'status'        => 'required|in:verified,resolved',
            'tindak_lanjut' => 'nullable|string|max:500',
        ]);

        $record = DisciplineRecord::findOrFail($id);
        $record->update([
            'status'        => $request->status,
            'tindak_lanjut' => $request->tindak_lanjut,
        ]);

        return back()->with('success', "Status catatan disiplin berhasil diperbarui.");
    }

    /**
     * BK Counseling Sessions List
     */
    public function counselingIndex(Request $request)
    {
        $user = Auth::user();
        $query = CounselingSession::with(['siswa.kelas', 'guruBk']);

        if ($user->role === 'guru') {
            // Check if user is BK or Homeroom
            $query->where(function ($q) use ($user) {
                $q->where('guru_bk_id', $user->id)
                  ->orWhereHas('siswa.kelas', function ($kq) use ($user) {
                      $kq->where('wali_kelas_id', $user->id);
                  });
            });
        }

        $sessions = $query->latest('tanggal')->latest('id')->paginate(15)->withQueryString();
        $siswas = Siswa::with('kelas')->orderBy('nama')->get();
        $bkTeachers = User::whereIn('role', ['guru', 'admin'])->orderBy('name')->get();

        return view('counseling.index', compact('sessions', 'siswas', 'bkTeachers'));
    }

    /**
     * Store Counseling Session (Strict BK/Admin)
     */
    public function counselingStore(Request $request)
    {
        $request->validate([
            'siswa_id'             => 'required|exists:siswas,id',
            'tanggal'              => 'required|date',
            'topik_umum'           => 'required|string|max:255',
            'permasalahan_rahasia' => 'required|string',
            'tindak_lanjut_umum'   => 'nullable|string',
            'catatan_rahasia'      => 'nullable|string',
            'status'               => 'required|in:scheduled,in_progress,completed',
        ]);

        $session = CounselingSession::create([
            'siswa_id'             => $request->siswa_id,
            'guru_bk_id'           => Auth::id(),
            'tanggal'              => $request->tanggal,
            'topik_umum'           => $request->topik_umum,
            'permasalahan_rahasia' => $request->permasalahan_rahasia,
            'tindak_lanjut_umum'   => $request->tindak_lanjut_umum,
            'catatan_rahasia'      => $request->catatan_rahasia,
            'status'               => $request->status,
        ]);

        return redirect()->route('counseling.index')
            ->with('success', "Sesi konseling BK untuk siswa berhasil dijadwalkan & dicatat.");
    }

    /**
     * Student & Parent Discipline Record Portal
     */
    public function studentPortal()
    {
        $user = Auth::user();
        $siswa = null;

        if ($user->role === 'siswa') {
            $siswa = Siswa::where('user_id', $user->id)->firstOrFail();
        } elseif ($user->role === 'orangtua') {
            $parent = DB::table('orang_tuas')->where('user_id', $user->id)->first();
            if ($parent && $parent->siswa_id) {
                $siswa = Siswa::find($parent->siswa_id);
            }
        }

        if (!$siswa) {
            abort(403, 'Akses tidak sah.');
        }

        $records = DisciplineRecord::with(['category', 'recordedBy'])
            ->where('siswa_id', $siswa->id)
            ->latest('tanggal')
            ->get();

        $pelanggaranPoin = $records->where('category.tipe', 'pelanggaran')->sum('poin');
        $prestasiPoin = $records->where('category.tipe', 'prestasi')->sum('poin');
        $skorDisiplin = max(0, 100 - $pelanggaranPoin + ($prestasiPoin / 2));

        return view('discipline.student', compact('siswa', 'records', 'pelanggaranPoin', 'prestasiPoin', 'skorDisiplin'));
    }
}
