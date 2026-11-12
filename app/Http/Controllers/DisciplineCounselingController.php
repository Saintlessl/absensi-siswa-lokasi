<?php

namespace App\Http\Controllers;

use App\Models\CounselingSession;
use App\Models\DisciplineCategory;
use App\Models\DisciplineRecord;
use App\Models\DisciplineRecordItem;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use App\Services\InAppNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DisciplineCounselingController extends Controller
{
    /**
     * Discipline & Achievement Records Dashboard
     */
    public function disciplineIndex(Request $request)
    {
        $user = Auth::user();
        $query = DisciplineRecord::with(['siswa.kelas', 'category', 'recordedBy', 'items']);

        // Scoping for Kaprog
        if ($user->role === 'kaprog' && $user->jurusan_id) {
            $query->whereHas('siswa.kelas', function ($q) use ($user) {
                $q->where('jurusan_id', $user->jurusan_id);
            });
        }

        if ($request->filled('tipe')) {
            $tipe = $request->tipe;
            $query->where(function ($q) use ($tipe) {
                $q->where('tipe', $tipe)
                  ->orWhereHas('category', fn($sq) => $sq->where('tipe', $tipe));
            });
        }

        if ($request->filled('kelas_id')) {
            $query->whereHas('siswa', function ($q) use ($request) {
                $q->where('kelas_id', $request->kelas_id);
            });
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('judul', 'like', "%{$s}%")
                  ->orWhere('deskripsi', 'like', "%{$s}%")
                  ->orWhereHas('siswa', function ($sq) use ($s) {
                      $sq->where('nama', 'like', "%{$s}%")->orWhere('nis', 'like', "%{$s}%");
                  })
                  ->orWhereHas('items', function ($iq) use ($s) {
                      $iq->where('nama_standar', 'like', "%{$s}%");
                  });
            });
        }

        $records = $query->latest('tanggal')->latest('id')->paginate(20)->withQueryString();
        $categories = DisciplineCategory::where('is_active', true)->orderBy('tipe')->orderBy('nama')->get();
        $classes = Kelas::orderBy('nama_kelas')->get();
        $siswas = Siswa::with('kelas')->orderBy('nama')->get();

        // Overall statistics
        $stats = [
            'total_pelanggaran'      => DisciplineRecord::where(fn($q) => $q->where('tipe', 'pelanggaran')->orWhereHas('category', fn($sq) => $sq->where('tipe', 'pelanggaran')))->count(),
            'total_prestasi'         => DisciplineRecord::where(fn($q) => $q->where('tipe', 'prestasi')->orWhereHas('category', fn($sq) => $sq->where('tipe', 'prestasi')))->count(),
            'total_item_pelanggaran' => DisciplineRecordItem::where('tipe', 'pelanggaran')->count(),
            'total_item_prestasi'    => DisciplineRecordItem::where('tipe', 'prestasi')->count(),
            'poin_tercatat'          => DisciplineRecord::sum('poin'),
            'kasus_aktif'            => DisciplineRecord::where('status', 'recorded')->count(),
        ];

        return view('discipline.index', compact('records', 'categories', 'classes', 'siswas', 'stats'));
    }

    /**
     * Store new discipline / achievement record (Supports Multi-Items and atomic transaction)
     */
    public function disciplineStore(Request $request, InAppNotificationService $notifService)
    {
        $request->validate([
            'siswa_id'               => 'required|exists:siswas,id',
            'tanggal'                => 'required|date|before_or_equal:today',
            'judul'                  => 'required|string|max:200',
            'deskripsi'              => 'required|string',
            'bukti'                  => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:3072',
            // Multi-item payload
            'items'                  => 'nullable|array',
            'items.*.category_id'    => 'required_with:items|exists:discipline_categories,id',
            'items.*.keterangan'     => 'nullable|string',
            'items.*.poin'           => 'nullable|integer',
            // Fallback single category
            'discipline_category_id' => 'nullable|exists:discipline_categories,id',
            'poin'                   => 'nullable|integer',
        ], [
            'siswa_id.required' => 'Siswa wajib dipilih.',
            'tanggal.required'  => 'Tanggal kejadian wajib diisi.',
            'judul.required'    => 'Judul atau perihal catatan wajib diisi.',
            'deskripsi.required'=> 'Deskripsi kejadian wajib diisi.',
        ]);

        $rawItems = $request->input('items', []);

        // Fallback for single category submission (backward compatibility)
        if (empty($rawItems) && $request->filled('discipline_category_id')) {
            $cat = DisciplineCategory::findOrFail($request->discipline_category_id);
            $rawItems = [
                [
                    'category_id' => $cat->id,
                    'keterangan'  => null,
                    'poin'        => $request->filled('poin') ? (int) $request->poin : $cat->default_poin,
                ]
            ];
        }

        if (empty($rawItems)) {
            throw ValidationException::withMessages([
                'items' => 'Minimal satu standar pelanggaran atau prestasi harus dipilih dalam catatan BK.',
            ]);
        }

        // Validate items and check consistency
        $categoryIds = collect($rawItems)->pluck('category_id')->unique()->all();
        $categories = DisciplineCategory::whereIn('id', $categoryIds)->get()->keyBy('id');

        $recordType = null;
        $totalPoin = 0;
        $preparedItems = [];

        foreach ($rawItems as $index => $itemData) {
            $catId = $itemData['category_id'] ?? null;
            if (!$catId || !isset($categories[$catId])) {
                throw ValidationException::withMessages([
                    "items.{$index}.category_id" => 'Master standar tidak ditemukan.',
                ]);
            }

            $cat = $categories[$catId];

            // Verify master is active for new records
            if (!$cat->is_active) {
                throw ValidationException::withMessages([
                    "items.{$index}.category_id" => "Master standar '{$cat->nama}' saat ini nonaktif dan tidak dapat digunakan untuk catatan baru.",
                ]);
            }

            // Determine or verify type consistency
            if ($recordType === null) {
                $recordType = $cat->tipe;
            } elseif ($recordType !== $cat->tipe) {
                throw ValidationException::withMessages([
                    'items' => 'Catatan BK tidak boleh mencampurkan item Pelanggaran dan Prestasi dalam satu catatan yang sama. Pisahkan menjadi catatan terpisah.',
                ]);
            }

            // Use point override if provided, otherwise default to master category point
            $itemPoin = isset($itemData['poin']) && $itemData['poin'] !== '' ? (int) $itemData['poin'] : $cat->default_poin;

            $totalPoin += $itemPoin;

            $preparedItems[] = [
                'discipline_category_id' => $cat->id,
                'nama_standar'           => $cat->nama,
                'tipe'                   => $cat->tipe,
                'poin'                   => $itemPoin,
                'keterangan'             => $itemData['keterangan'] ?? null,
            ];
        }

        $buktiPath = null;
        if ($request->hasFile('bukti')) {
            $buktiPath = $request->file('bukti')->store('discipline/evidence', 'public');
        }

        // Execute inside Database Transaction
        $record = DB::transaction(function () use ($request, $recordType, $totalPoin, $preparedItems, $buktiPath) {
            $firstCatId = $preparedItems[0]['discipline_category_id'] ?? null;

            $rec = DisciplineRecord::create([
                'siswa_id'               => $request->siswa_id,
                'discipline_category_id' => $firstCatId,
                'tipe'                   => $recordType,
                'tanggal'                => $request->tanggal,
                'judul'                  => $request->judul,
                'deskripsi'              => $request->deskripsi,
                'bukti_path'             => $buktiPath,
                'poin'                   => $totalPoin,
                'status'                 => 'recorded',
                'recorded_by'            => Auth::id(),
            ]);

            foreach ($preparedItems as $item) {
                $rec->items()->create($item);
            }

            return $rec;
        });

        $siswa = Siswa::with(['user', 'orangTua.user'])->find($request->siswa_id);

        // Notify student & parents
        if ($siswa && $siswa->user) {
            $notifType = $recordType === 'prestasi' ? 'academic' : 'administrative';
            $notifTitle = $recordType === 'prestasi' ? "Penghargaan Prestasi Siswa" : "Catatan Kedisiplinan Siswa";
            $sign = $totalPoin > 0 ? "+{$totalPoin}" : "{$totalPoin}";
            $notifDesc = "Catatan {$recordType} baru: '{$record->judul}' ({$sign} poin) dengan " . count($preparedItems) . " item dicatat pada {$record->tanggal->format('d/m/Y')}.";

            $notifService->sendToUser($siswa->user, $notifTitle, $notifDesc, $notifType);

            if ($siswa->orangTua) {
                foreach ($siswa->orangTua as $ot) {
                    if ($ot->user) {
                        $notifService->sendToUser($ot->user, $notifTitle, $notifDesc, $notifType);
                    }
                }
            }
        }

        $itemCount = count($preparedItems);
        $signPoin = $totalPoin > 0 ? "+{$totalPoin}" : "{$totalPoin}";

        return redirect()->route('discipline.index')
            ->with('success', "Catatan {$recordType} untuk {$siswa->nama} berhasil disimpan ({$itemCount} item, total {$signPoin} poin).");
    }

    /**
     * Get Record Detail with Child Items (for Modal Inspection / AJAX)
     */
    public function disciplineDetail($id): JsonResponse
    {
        $record = DisciplineRecord::with(['siswa.kelas', 'items.category', 'recordedBy', 'category'])
            ->findOrFail($id);

        return response()->json([
            'id'             => $record->id,
            'judul'          => $record->judul,
            'deskripsi'      => $record->deskripsi,
            'tanggal'        => $record->tanggal->format('Y-m-d'),
            'tanggal_id'     => $record->tanggal->translatedFormat('d F Y'),
            'tipe'           => $record->effective_type,
            'poin'           => $record->poin,
            'formatted_poin' => $record->formatted_poin,
            'status'         => $record->status,
            'tindak_lanjut'  => $record->tindak_lanjut,
            'bukti_path'     => $record->bukti_path ? asset('storage/' . $record->bukti_path) : null,
            'siswa'          => [
                'id'         => $record->siswa->id,
                'nama'       => $record->siswa->nama,
                'nis'        => $record->siswa->nis,
                'kelas'      => $record->siswa->kelas->nama_kelas ?? '-',
            ],
            'recorded_by'    => $record->recordedBy->name ?? 'Sistem',
            'items'          => $record->items->map(function ($it, $idx) {
                return [
                    'id'             => $it->id,
                    'no'             => $idx + 1,
                    'category_id'    => $it->discipline_category_id,
                    'nama_standar'   => $it->nama_standar,
                    'tipe'           => $it->tipe,
                    'poin'           => $it->poin,
                    'formatted_poin' => $it->formatted_poin,
                    'keterangan'     => $it->keterangan,
                ];
            }),
        ]);
    }

    /**
     * Update Record & its Child Items
     */
    public function disciplineUpdate(Request $request, $id)
    {
        $record = DisciplineRecord::with('items')->findOrFail($id);

        $request->validate([
            'tanggal'             => 'required|date|before_or_equal:today',
            'judul'               => 'required|string|max:200',
            'deskripsi'           => 'required|string',
            'status'              => 'nullable|in:recorded,verified,resolved',
            'tindak_lanjut'       => 'nullable|string',
            'items'               => 'required|array|min:1',
            'items.*.category_id' => 'required|exists:discipline_categories,id',
            'items.*.keterangan'  => 'nullable|string',
            'items.*.poin'        => 'nullable|integer',
        ]);

        $rawItems = $request->input('items', []);
        $categoryIds = collect($rawItems)->pluck('category_id')->unique()->all();
        $categories = DisciplineCategory::whereIn('id', $categoryIds)->get()->keyBy('id');

        $recordType = $record->effective_type;
        $totalPoin = 0;
        $preparedItems = [];

        foreach ($rawItems as $index => $itemData) {
            $catId = $itemData['category_id'] ?? null;
            $cat = $categories[$catId] ?? null;

            if (!$cat) {
                throw ValidationException::withMessages([
                    "items.{$index}.category_id" => 'Master standar tidak ditemukan.',
                ]);
            }

            // Verify item type matches record type
            if ($cat->tipe !== $recordType) {
                throw ValidationException::withMessages([
                    'items' => "Seluruh item harus bertipe sama ({$recordType}). Tidak dapat mencampurkan pelanggaran dan prestasi.",
                ]);
            }

            $itemPoin = isset($itemData['poin']) && $itemData['poin'] !== '' ? (int) $itemData['poin'] : $cat->default_poin;

            $totalPoin += $itemPoin;

            $preparedItems[] = [
                'discipline_category_id' => $cat->id,
                'nama_standar'           => $cat->nama,
                'tipe'                   => $cat->tipe,
                'poin'                   => $itemPoin,
                'keterangan'             => $itemData['keterangan'] ?? null,
            ];
        }

        DB::transaction(function () use ($record, $request, $totalPoin, $preparedItems) {
            $record->update([
                'tanggal'       => $request->tanggal,
                'judul'         => $request->judul,
                'deskripsi'     => $request->deskripsi,
                'poin'          => $totalPoin,
                'status'        => $request->status ?? $record->status,
                'tindak_lanjut' => $request->tindak_lanjut ?? $record->tindak_lanjut,
            ]);

            // Replace child items
            $record->items()->delete();
            foreach ($preparedItems as $item) {
                $record->items()->create($item);
            }
        });

        return redirect()->route('discipline.index')
            ->with('success', "Catatan {$record->effective_type} '{$record->judul}' berhasil diperbarui (total {$totalPoin} poin).");
    }

    /**
     * Delete discipline record safely in transaction
     */
    public function disciplineDestroy($id)
    {
        $record = DisciplineRecord::findOrFail($id);
        $judul = $record->judul;

        DB::transaction(function () use ($record) {
            $record->items()->delete();
            $record->delete();
        });

        return redirect()->route('discipline.index')
            ->with('success', "Catatan kedisiplinan '{$judul}' berhasil dihapus.");
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
            $guru = Guru::where('user_id', $user->id)->first();
            $homeroomClassId = $guru?->activeHomeroomAssignment?->kelas_id;

            $query->where(function ($q) use ($user, $homeroomClassId) {
                $q->where('guru_bk_id', $user->id);
                if ($homeroomClassId) {
                    $q->orWhereHas('siswa', function ($sq) use ($homeroomClassId) {
                        $sq->where('kelas_id', $homeroomClassId);
                    });
                }
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
     * Student & Parent Discipline Record Portal (With nested item breakdown & accurate scoring)
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

        $records = DisciplineRecord::with(['items', 'category', 'recordedBy'])
            ->where('siswa_id', $siswa->id)
            ->latest('tanggal')
            ->get();

        $pelanggaranPoin = 0;
        $prestasiPoin = 0;

        foreach ($records as $rec) {
            $type = $rec->effective_type;
            if ($type === 'pelanggaran') {
                $pelanggaranPoin += abs($rec->poin);
            } elseif ($type === 'prestasi') {
                $prestasiPoin += abs($rec->poin);
            }
        }

        $skorDisiplin = max(0, min(100, 100 - $pelanggaranPoin + ($prestasiPoin / 2)));

        return view('discipline.student', compact('siswa', 'records', 'pelanggaranPoin', 'prestasiPoin', 'skorDisiplin'));
    }
}
