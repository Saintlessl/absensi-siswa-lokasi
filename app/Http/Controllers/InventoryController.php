<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\InventoryBorrowing;
use App\Models\InventoryBorrowingUnit;
use App\Models\InventoryItem;
use App\Models\InventoryUnit;
use App\Models\InventoryUnitLog;
use App\Models\Jurusan;
use App\Models\Siswa;
use App\Services\InAppNotificationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InventoryController extends Controller
{
    /**
     * Asset inventory gallery & master asset list
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // =============================================================
        // 1. GLOBAL BORROWING MONITORING (Requested Section 2, 3, 4)
        // =============================================================
        $borrowingBaseQuery = InventoryBorrowing::query()
            ->with([
                'item.jurusan',
                'user',
                'siswa.kelas.jurusan',
                'peminjamGuru',
                'borrowingUnits.unit',
            ]);

        // Strict role-based backend scoping for Kaprog
        if ($user->role === 'kaprog' && $user->jurusan_id) {
            $borrowingBaseQuery->whereHas('item', fn ($i) => $i->where('jurusan_id', $user->jurusan_id));
        }

        // Search within Global Monitoring
        $monitoringSearch = trim((string) $request->input('monitoring_search'));
        if ($monitoringSearch !== '') {
            $borrowingBaseQuery->where(function ($q) use ($monitoringSearch) {
                $q->whereHas('user', fn ($u) => $u->where('name', 'like', "%{$monitoringSearch}%"))
                  ->orWhereHas('siswa', fn ($s) => $s->where('nama', 'like', "%{$monitoringSearch}%")->orWhere('nis', 'like', "%{$monitoringSearch}%")->orWhere('nisn', 'like', "%{$monitoringSearch}%"))
                  ->orWhereHas('peminjamGuru', fn ($g) => $g->where('nama', 'like', "%{$monitoringSearch}%")->orWhere('nip', 'like', "%{$monitoringSearch}%"))
                  ->orWhereHas('item', fn ($i) => $i->where('nama_barang', 'like', "%{$monitoringSearch}%")->orWhere('kode_barang', 'like', "%{$monitoringSearch}%"))
                  ->orWhereHas('borrowingUnits.unit', fn ($u) => $u->where('kode_unit', 'like', "%{$monitoringSearch}%"));
            });
        }

        // Super Admin Jurusan filter for global monitoring
        $monitoringJurusanId = $request->input('monitoring_jurusan_id');
        if ($user->role !== 'kaprog' && !empty($monitoringJurusanId)) {
            $borrowingBaseQuery->whereHas('item', fn ($i) => $i->where('jurusan_id', $monitoringJurusanId));
        }

        // Active status tab: 'dipinjam' (Sedang Dipinjam), 'dikembalikan' (Dikembalikan), 'belum_dikembalikan' (Belum Dikembalikan)
        $monitoringTab = $request->input('monitoring_tab', 'dipinjam');
        if (!in_array($monitoringTab, ['dipinjam', 'dikembalikan', 'belum_dikembalikan'], true)) {
            $monitoringTab = 'dipinjam';
        }

        // Calculate counts for tab badges
        $countDipinjam = (clone $borrowingBaseQuery)->sedangDipinjam()->count();
        $countDikembalikan = (clone $borrowingBaseQuery)->dikembalikan()->count();
        $countBelumDikembalikan = (clone $borrowingBaseQuery)->belumDikembalikan()->count();

        // Paginated transactions for the active tab
        $monitoringBorrowings = (clone $borrowingBaseQuery)
            ->filterMonitoringTab($monitoringTab)
            ->orderByDesc('id')
            ->paginate(10, ['*'], 'monitoring_page')
            ->withQueryString();

        // =============================================================
        // 2. KELOMPOK BARANG / ASSET CARDS
        // =============================================================
        $query = InventoryItem::with(['jurusan', 'units'])
            ->where('is_active', true);

        // Scoping for KAPROG: Kaprog only manages their own department
        if ($user->role === 'kaprog' && $user->jurusan_id) {
            $query->where('jurusan_id', $user->jurusan_id);
        }

        if ($request->filled('jurusan_id')) {
            $query->where('jurusan_id', $request->jurusan_id);
        }

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        if ($request->filled('kondisi')) {
            $k = $request->kondisi;
            $query->where(function ($q) use ($k) {
                $q->whereHas('units', fn ($u) => $u->where('is_active', true)->where('kondisi', $k))
                  ->orWhere('kondisi', $k);
            });
        }

        if ($request->filled('ketersediaan')) {
            if ($request->ketersediaan === 'tersedia') {
                $query->where('unit_tersedia', '>', 0);
            } elseif ($request->ketersediaan === 'habis') {
                $query->where('unit_tersedia', '<=', 0);
            }
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('nama_barang', 'like', "%{$search}%")
                  ->orWhere('kode_barang', 'like', "%{$search}%")
                  ->orWhere('kategori', 'like', "%{$search}%")
                  ->orWhere('merk', 'like', "%{$search}%")
                  ->orWhere('tipe_model', 'like', "%{$search}%")
                  ->orWhere('lokasi_penyimpanan', 'like', "%{$search}%")
                  ->orWhereHas('units', function ($u) use ($search) {
                      $u->where('kode_unit', 'like', "%{$search}%")
                        ->orWhere('barcode_eksternal', 'like', "%{$search}%");
                  });
            });
        }

        $items = $query->orderBy('nama_barang')->paginate(12, ['*'], 'page')->withQueryString();
        
        // Synchronize display stock for viewed items
        foreach ($items as $item) {
            if ($item->units->isNotEmpty()) {
                $item->total_unit = $item->units->where('is_active', true)->count();
                $item->unit_tersedia = $item->countAvailableUnits();
            }
        }

        $jurusansQuery = Jurusan::orderBy('nama_jurusan');
        if ($user->role === 'kaprog' && $user->jurusan_id) {
            $jurusansQuery->where('id', $user->jurusan_id);
        }
        $jurusans = $jurusansQuery->get();

        $kategoriList = InventoryItem::distinct()->whereNotNull('kategori')->pluck('kategori');

        // Scoped stats calculation
        $baseItemQuery = InventoryItem::query();
        $baseUnitQuery = InventoryUnit::query()->where('is_active', true);
        $baseBorrowingStatsQuery = InventoryBorrowing::query();

        if ($user->role === 'kaprog' && $user->jurusan_id) {
            $baseItemQuery->where('jurusan_id', $user->jurusan_id);
            $baseUnitQuery->whereHas('item', fn ($i) => $i->where('jurusan_id', $user->jurusan_id));
            $baseBorrowingStatsQuery->whereHas('item', fn ($i) => $i->where('jurusan_id', $user->jurusan_id));
        }

        $stats = [
            'total_kelompok' => (clone $baseItemQuery)->count(),
            'total_unit'     => (clone $baseUnitQuery)->count(),
            'tersedia'       => (clone $baseUnitQuery)->where('status_ketersediaan', 'tersedia')
                                    ->where('kondisi', 'baik')
                                    ->whereDoesntHave('borrowingUnits', fn ($q) => $q->whereIn('status', ['reserved', 'borrowed']))
                                    ->count(),
            'dipinjam'       => (clone $baseUnitQuery)->where('status_ketersediaan', 'dipinjam')->count(),
            'rusak'          => (clone $baseUnitQuery)->whereIn('kondisi', ['rusak_ringan', 'rusak_berat', 'perlu_pemeriksaan'])->count(),
        ];

        // Fallback for empty units (legacy dataset)
        if ($stats['total_unit'] === 0 && $stats['total_kelompok'] > 0) {
            $stats['total_unit'] = (clone $baseItemQuery)->sum('total_unit');
            $stats['tersedia']   = (clone $baseItemQuery)->where('status', 'tersedia')->sum('unit_tersedia');
            $stats['dipinjam']   = (clone $baseBorrowingStatsQuery)->whereIn('status', ['borrowed', 'approved'])->count();
            $stats['rusak']      = (clone $baseItemQuery)->whereIn('kondisi', ['rusak_berat', 'maintenance'])->count();
        }

        return view('inventory.index', compact(
            'items',
            'jurusans',
            'kategoriList',
            'stats',
            'monitoringBorrowings',
            'monitoringTab',
            'monitoringSearch',
            'monitoringJurusanId',
            'countDipinjam',
            'countDikembalikan',
            'countBelumDikembalikan'
        ));
    }

    /**
     * Form to create asset group
     */
    public function create()
    {
        $user = Auth::user();
        $jurusansQuery = Jurusan::orderBy('nama_jurusan');
        if ($user->role === 'kaprog' && $user->jurusan_id) {
            $jurusansQuery->where('id', $user->jurusan_id);
        }
        $jurusans = $jurusansQuery->get();

        return view('inventory.create', compact('jurusans'));
    }

    /**
     * Store new asset group and auto-generate physical units
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // Enforce Kaprog department authorization
        if ($user->role === 'kaprog' && $user->jurusan_id) {
            if ((int) $request->jurusan_id !== (int) $user->jurusan_id) {
                abort(403, 'Akses ditolak: Anda hanya berwenang menambahkan peralatan pada jurusan Anda.');
            }
        }

        $request->validate([
            'kode_barang'        => 'required|string|max:50|unique:inventory_items,kode_barang',
            'nama_barang'        => 'required|string|max:255',
            'jurusan_id'         => 'nullable|exists:jurusans,id',
            'borrowing_scope'    => 'nullable|in:jurusan,umum',
            'kategori'           => 'required|string|max:100',
            'merk'               => 'nullable|string|max:100',
            'tipe_model'         => 'nullable|string|max:100',
            'lokasi_penyimpanan' => 'required|string|max:150',
            'deskripsi'          => 'nullable|string',
            'total_unit'         => 'required|integer|min:1|max:1000',
            'kondisi'            => 'required|in:baik,rusak_ringan,rusak_berat,perlu_pemeriksaan,maintenance',
        ]);

        $initialKondisi = $request->kondisi === 'maintenance' ? 'rusak_ringan' : $request->kondisi;

        $item = DB::transaction(function () use ($request, $user, $initialKondisi) {
            $item = InventoryItem::create([
                'kode_barang'        => strtoupper(trim($request->kode_barang)),
                'nama_barang'        => trim($request->nama_barang),
                'jurusan_id'         => $request->jurusan_id,
                'borrowing_scope'    => $request->input('borrowing_scope', ($request->jurusan_id ? 'jurusan' : 'umum')),
                'kategori'           => trim($request->kategori),
                'merk'               => $request->merk ? trim($request->merk) : null,
                'tipe_model'         => $request->tipe_model ? trim($request->tipe_model) : null,
                'lokasi_penyimpanan' => trim($request->lokasi_penyimpanan),
                'deskripsi'          => $request->deskripsi,
                'total_unit'         => (int) $request->total_unit,
                'unit_tersedia'      => $initialKondisi === 'baik' ? (int) $request->total_unit : 0,
                'kondisi'            => $initialKondisi,
                'status'             => $initialKondisi === 'baik' ? 'tersedia' : 'maintenance',
                'is_active'          => true,
                'qr_token'           => Str::random(40),
            ]);

            // Auto-create individual physical units
            $item->createUnits(
                count: (int) $request->total_unit,
                initialCondition: $initialKondisi,
                lokasi: $item->lokasi_penyimpanan,
                userId: $user->id
            );

            return $item;
        });

        return redirect()->route('inventory.index')
            ->with('success', "Kelompok aset {$item->nama_barang} ({$item->kode_barang}) berhasil dibuat bersama {$item->total_unit} unit fisik.");
    }

    /**
     * Asset Detail View (Group information & individual physical units list)
     */
    public function show($id)
    {
        $user = Auth::user();
        $item = InventoryItem::with([
            'jurusan',
            'units' => fn ($q) => $q->orderBy('kode_unit'),
            'units.logs.user',
            'units.borrowingUnits.borrowing.user',
            'borrowings.user',
            'borrowings.siswa.kelas',
            'borrowings.peminjamGuru',
            'borrowings.borrowingUnits.unit',
        ])->findOrFail($id);

        // Security check for Kaprog:
        if ($user->role === 'kaprog' && $user->jurusan_id && $item->jurusan_id) {
            abort_unless(
                (int) $item->jurusan_id === (int) $user->jurusan_id,
                403,
                'Akses ditolak: Anda hanya berwenang melihat detail peralatan dari program keahlian Anda.'
            );
        }

        $item->syncStockCounts();

        $activeUnits = $item->units->where('is_active', true);
        $archivedUnits = $item->units->where('is_active', false);

        // Item-level Borrowing Monitoring tabs (Sedang Dipinjam, Dikembalikan, Belum Dikembalikan)
        $itemBorrowingBase = InventoryBorrowing::where('inventory_item_id', $item->id)
            ->with(['user', 'siswa.kelas.jurusan', 'peminjamGuru', 'borrowingUnits.unit']);

        $itemCountDipinjam = (clone $itemBorrowingBase)->sedangDipinjam()->count();
        $itemCountDikembalikan = (clone $itemBorrowingBase)->dikembalikan()->count();
        $itemCountBelumDikembalikan = (clone $itemBorrowingBase)->belumDikembalikan()->count();

        $borrowingsDipinjam = (clone $itemBorrowingBase)->sedangDipinjam()->orderByDesc('id')->get();
        $borrowingsDikembalikan = (clone $itemBorrowingBase)->dikembalikan()->orderByDesc('id')->get();
        $borrowingsBelumDikembalikan = (clone $itemBorrowingBase)->belumDikembalikan()->orderByDesc('id')->get();

        $activeBorrowings = $item->borrowings
            ->whereIn('status', ['pending', 'approved', 'borrowed'])
            ->sortByDesc('id');

        $historyBorrowings = $item->borrowings
            ->whereIn('status', ['returned', 'rejected', 'cancelled'])
            ->sortByDesc('id')
            ->take(20);

        $jurusansQuery = Jurusan::orderBy('nama_jurusan');
        if ($user->role === 'kaprog' && $user->jurusan_id) {
            $jurusansQuery->where('id', $user->jurusan_id);
        }
        $jurusans = $jurusansQuery->get();

        return view('inventory.show', compact(
            'item',
            'activeUnits',
            'archivedUnits',
            'activeBorrowings',
            'historyBorrowings',
            'jurusans',
            'itemCountDipinjam',
            'itemCountDikembalikan',
            'itemCountBelumDikembalikan',
            'borrowingsDipinjam',
            'borrowingsDikembalikan',
            'borrowingsBelumDikembalikan'
        ));
    }

    /**
     * Update asset group details (Master Asset level)
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();

        // Enforce role authorization: only Admin and Kaprog
        abort_unless(
            in_array($user->role, ['superadmin', 'admin', 'kaprog'], true),
            403,
            'Akses ditolak: Anda tidak memiliki wewenang untuk mengubah data kelompok aset.'
        );

        $item = InventoryItem::findOrFail($id);

        // Kaprog may only edit assets of their own department
        if ($user->role === 'kaprog' && $user->jurusan_id && $item->jurusan_id) {
            abort_unless(
                (int) $item->jurusan_id === (int) $user->jurusan_id,
                403,
                'Akses ditolak: Anda hanya berwenang mengubah informasi peralatan dari program keahlian Anda.'
            );
        }

        $request->validate([
            'nama_barang'        => 'required|string|max:255',
            'kategori'           => 'required|string|max:100',
            'merk'               => 'nullable|string|max:100',
            'tipe_model'         => 'nullable|string|max:100',
            'lokasi_penyimpanan' => 'required|string|max:150',
            'deskripsi'          => 'nullable|string',
            'jurusan_id'         => 'nullable|exists:jurusans,id',
            'borrowing_scope'    => 'nullable|in:jurusan,umum',
        ]);

        $item->update([
            'nama_barang'        => trim($request->nama_barang),
            'kategori'           => trim($request->kategori),
            'merk'               => $request->merk ? trim($request->merk) : null,
            'tipe_model'         => $request->tipe_model ? trim($request->tipe_model) : null,
            'lokasi_penyimpanan' => trim($request->lokasi_penyimpanan),
            'deskripsi'          => $request->deskripsi,
            'jurusan_id'         => ($user->role === 'kaprog') ? $user->jurusan_id : $request->jurusan_id,
            'borrowing_scope'    => $request->input('borrowing_scope', $item->borrowing_scope ?: 'jurusan'),
        ]);

        return back()->with('success', "Informasi kelompok aset {$item->nama_barang} berhasil diperbarui.");
    }

    /**
     * Add physical unit(s) to existing group
     */
    public function storeUnit(Request $request, $itemId)
    {
        $user = Auth::user();
        $item = InventoryItem::findOrFail($itemId);

        if ($user->role === 'kaprog' && $user->jurusan_id && $item->jurusan_id) {
            abort_unless(
                (int) $item->jurusan_id === (int) $user->jurusan_id,
                403,
                'Akses ditolak: Anda hanya berwenang mengelola peralatan dari program keahlian Anda.'
            );
        }

        $request->validate([
            'jumlah_unit'        => 'required|integer|min:1|max:100',
            'kondisi'            => 'required|in:baik,rusak_ringan,rusak_berat,perlu_pemeriksaan',
            'kode_unit_manual'   => 'nullable|string|max:100|unique:inventory_units,kode_unit',
            'barcode_eksternal'  => 'nullable|string|max:100|unique:inventory_units,barcode_eksternal',
            'lokasi_penyimpanan' => 'nullable|string|max:150',
            'tanggal_pengadaan'  => 'nullable|date',
            'catatan'            => 'nullable|string|max:500',
        ]);

        $count = (int) $request->jumlah_unit;

        DB::transaction(function () use ($request, $item, $user, $count) {
            // Single unit with manual code / barcode
            if ($count === 1 && $request->filled('kode_unit_manual')) {
                $unit = InventoryUnit::create([
                    'inventory_item_id'   => $item->id,
                    'kode_unit'           => strtoupper(trim($request->kode_unit_manual)),
                    'barcode_eksternal'   => $request->filled('barcode_eksternal') ? trim($request->barcode_eksternal) : null,
                    'qr_token'            => Str::random(40),
                    'kondisi'             => $request->kondisi,
                    'status_ketersediaan' => 'tersedia',
                    'lokasi_penyimpanan'  => $request->filled('lokasi_penyimpanan') ? trim($request->lokasi_penyimpanan) : $item->lokasi_penyimpanan,
                    'tanggal_pengadaan'   => $request->tanggal_pengadaan ?: now()->toDateString(),
                    'is_active'           => true,
                    'status_alasan'       => 'aktif',
                    'catatan'             => $request->catatan,
                ]);

                $unit->logActivity(
                    action: 'unit_didaftarkan',
                    keterangan: "Pendaftaran unit individual {$unit->kode_unit} (Kondisi: {$unit->kondisi})",
                    kondisiBaru: $unit->kondisi,
                    lokasiBaru: $unit->lokasi_penyimpanan,
                    userId: $user->id
                );
            } else {
                // Batch auto-generate
                $item->createUnits(
                    count: $count,
                    initialCondition: $request->kondisi,
                    lokasi: $request->filled('lokasi_penyimpanan') ? trim($request->lokasi_penyimpanan) : $item->lokasi_penyimpanan,
                    userId: $user->id
                );
            }

            $item->syncStockCounts();
        });

        return back()->with('success', "Berhasil menambahkan {$count} unit fisik ke kelompok {$item->nama_barang}.");
    }

    /**
     * Update individual unit details & physical condition
     */
    public function updateUnit(Request $request, $unitId)
    {
        $user = Auth::user();
        $unit = InventoryUnit::with('item')->findOrFail($unitId);
        $item = $unit->item;

        if ($user->role === 'kaprog' && $user->jurusan_id && $item->jurusan_id) {
            abort_unless(
                (int) $item->jurusan_id === (int) $user->jurusan_id,
                403,
                'Akses ditolak: Anda hanya berwenang mengubah unit peralatan dari program keahlian Anda.'
            );
        }

        $request->validate([
            'kondisi'              => 'required|in:baik,rusak_ringan,rusak_berat,perlu_pemeriksaan',
            'lokasi_penyimpanan'   => 'nullable|string|max:150',
            'barcode_eksternal'    => 'nullable|string|max:100|unique:inventory_units,barcode_eksternal,' . $unit->id,
            'tanggal_pengadaan'    => 'nullable|date',
            'catatan'              => 'nullable|string|max:500',
            'keterangan_perubahan' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($request, $unit, $item, $user) {
            $kondisiLama = $unit->kondisi;
            $lokasiLama = $unit->lokasi_penyimpanan;

            $unit->update([
                'kondisi'            => $request->kondisi,
                'lokasi_penyimpanan' => $request->filled('lokasi_penyimpanan') ? trim($request->lokasi_penyimpanan) : $item->lokasi_penyimpanan,
                'barcode_eksternal'  => $request->filled('barcode_eksternal') ? trim($request->barcode_eksternal) : null,
                'tanggal_pengadaan'  => $request->tanggal_pengadaan,
                'catatan'            => $request->catatan,
            ]);

            // If condition or location changed, write detailed audit log
            if ($kondisiLama !== $request->kondisi || $lokasiLama !== $unit->lokasi_penyimpanan) {
                $desc = $request->keterangan_perubahan ?: "Pembaruan unit: Kondisi [{$kondisiLama} -> {$request->kondisi}]";
                $unit->logActivity(
                    action: $kondisiLama !== $request->kondisi ? 'kondisi_diubah' : 'lokasi_diubah',
                    keterangan: $desc,
                    kondisiLama: $kondisiLama,
                    kondisiBaru: $request->kondisi,
                    lokasiLama: $lokasiLama,
                    lokasiBaru: $unit->lokasi_penyimpanan,
                    userId: $user->id
                );
            }

            $item->syncStockCounts();
        });

        return back()->with('success', "Data unit fisik {$unit->kode_unit} berhasil diperbarui.");
    }

    /**
     * Archive or retire unit from inventory (safe removal)
     */
    public function archiveUnit(Request $request, $unitId)
    {
        $user = Auth::user();
        $unit = InventoryUnit::with('item')->findOrFail($unitId);
        $item = $unit->item;

        if ($user->role === 'kaprog' && $user->jurusan_id && $item->jurusan_id) {
            abort_unless(
                (int) $item->jurusan_id === (int) $user->jurusan_id,
                403,
                'Akses ditolak: Anda hanya berwenang mengarsipkan unit dari program keahlian Anda.'
            );
        }

        // Check if unit is in an active loan transaction
        if ($unit->status_ketersediaan === 'dipinjam' || $unit->isReserved()) {
            return back()->with('error', "Unit {$unit->kode_unit} sedang dipinjam atau direservasi; selesaikan transaksi terlebih dahulu.");
        }

        $request->validate([
            'status_alasan' => 'required|in:rusak_berat,hilang,dihapuskan,diarsipkan',
            'catatan'       => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($request, $unit, $item, $user) {
            $unit->update([
                'is_active'     => false,
                'status_alasan' => $request->status_alasan,
                'catatan'       => $request->catatan ?: $unit->catatan,
            ]);

            $unit->logActivity(
                action: 'diarsipkan',
                keterangan: "Unit diarsipkan/dikeluarkan dari inventaris aktif dengan alasan: {$request->status_alasan}. Catatan: {$request->catatan}",
                userId: $user->id
            );

            $item->syncStockCounts();
        });

        return back()->with('success', "Unit fisik {$unit->kode_unit} berhasil diarsipkan.");
    }

    /**
     * Restore archived unit back to active pool
     */
    public function restoreUnit($unitId)
    {
        $user = Auth::user();
        $unit = InventoryUnit::with('item')->findOrFail($unitId);
        $item = $unit->item;

        if ($user->role === 'kaprog' && $user->jurusan_id && $item->jurusan_id) {
            abort_unless((int) $item->jurusan_id === (int) $user->jurusan_id, 403);
        }

        DB::transaction(function () use ($unit, $item, $user) {
            $unit->update([
                'is_active'     => true,
                'status_alasan' => 'aktif',
            ]);

            $unit->logActivity(
                action: 'diaktifkan',
                keterangan: "Unit diaktifkan kembali ke daftar inventaris aktif.",
                userId: $user->id
            );

            $item->syncStockCounts();
        });

        return back()->with('success', "Unit fisik {$unit->kode_unit} berhasil diaktifkan kembali.");
    }

    /**
     * AJAX: Get chronological audit log for a single physical unit
     */
    public function unitLogs($unitId): JsonResponse
    {
        $unit = InventoryUnit::with(['logs.user', 'logs.borrowing.user', 'item'])->findOrFail($unitId);

        $logs = $unit->logs->map(fn ($log) => [
            'id'           => $log->id,
            'action'       => $log->action,
            'action_label' => match ($log->action) {
                'unit_didaftarkan' => 'Pendaftaran Unit',
                'kondisi_diubah'   => 'Perubahan Kondisi',
                'lokasi_diubah'    => 'Pindah Lokasi',
                'reservasi'        => 'Direservasi Peminjaman',
                'penyerahan'       => 'Diserahkan ke Peminjam',
                'pengembalian'     => 'Dikembalikan ke Gudang',
                'diarsipkan'       => 'Diarsipkan / Dinonaktifkan',
                'diaktifkan'       => 'Diaktifkan Kembali',
                'migrasi_legacy'   => 'Migrasi Sistem',
                default            => ucfirst(str_replace('_', ' ', $log->action)),
            },
            'keterangan'   => $log->keterangan,
            'kondisi_lama' => $log->kondisi_lama,
            'kondisi_baru' => $log->kondisi_baru,
            'lokasi_lama'  => $log->lokasi_lama,
            'lokasi_baru'  => $log->lokasi_baru,
            'user'         => $log->user?->name ?? 'Sistem',
            'waktu'        => $log->created_at->translatedFormat('d M Y, H:i'),
        ]);

        return response()->json([
            'unit' => [
                'id'                  => $unit->id,
                'kode_unit'           => $unit->kode_unit,
                'nama_barang'         => $unit->item->nama_barang,
                'kondisi'             => $unit->kondisi,
                'status_ketersediaan' => $unit->status_ketersediaan,
                'lokasi'              => $unit->effective_lokasi,
            ],
            'logs' => $logs,
        ]);
    }

    /**
     * Borrowings management & transactions view
     */
    public function borrowingsIndex(Request $request)
    {
        $user = Auth::user();
        $query = InventoryBorrowing::with([
            'item.jurusan',
            'user',
            'siswa.kelas',
            'peminjamGuru',
            'pencatat',
            'decidedBy',
            'diserahkanOleh',
            'borrowingUnits.unit',
        ]);

        if ($user->role === 'siswa') {
            $query->where('user_id', $user->id);
        } elseif ($user->role === 'guru') {
            $guru = Guru::where('user_id', $user->id)->first();
            $query->where(function ($q) use ($user, $guru) {
                $q->where('user_id', $user->id);
                if ($guru) $q->orWhere('peminjam_guru_id', $guru->id);
            });
        } elseif ($user->role === 'kaprog' && $user->jurusan_id) {
            $query->whereHas('item', function ($q) use ($user) {
                $q->where('jurusan_id', $user->jurusan_id);
            });
        }

        // Filter status tab
        if ($request->filled('tab')) {
            $tab = $request->tab;
            if ($tab === 'pending') {
                $query->where('status', 'pending');
            } elseif ($tab === 'approved') {
                $query->where('status', 'approved');
            } elseif ($tab === 'borrowed') {
                $query->where('status', 'borrowed')
                      ->whereDate('rencana_kembali', '>=', now()->toDateString());
            } elseif ($tab === 'overdue') {
                $query->where('status', 'borrowed')
                      ->whereDate('rencana_kembali', '<', now()->toDateString());
            } elseif ($tab === 'returned') {
                $query->where('status', 'returned');
            }
        } elseif ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('sumber')) {
            $query->where('sumber_transaksi', $request->sumber === 'manual' ? 'manual_petugas' : 'mandiri');
        }

        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal_pinjam', $request->tanggal);
        }

        if ($request->filled('peminjam')) {
            $s = trim($request->peminjam);
            $query->where(function ($q) use ($s) {
                $q->whereHas('user', fn ($u) => $u->where('name', 'like', "%{$s}%"))
                  ->orWhereHas('siswa', fn ($x) => $x->where('nama', 'like', "%{$s}%"))
                  ->orWhereHas('peminjamGuru', fn ($x) => $x->where('nama', 'like', "%{$s}%"));
            });
        }

        if ($request->filled('barang')) {
            $b = trim($request->barang);
            $query->whereHas('item', fn ($x) => $x->where('nama_barang', 'like', "%{$b}%")->orWhere('kode_barang', 'like', "%{$b}%"));
        }

        $borrowings = $query->latest('id')->paginate(20)->withQueryString();

        // Calculate counts for tab navigation
        $countBase = InventoryBorrowing::query();
        if ($user->role === 'siswa') {
            $countBase->where('user_id', $user->id);
        } elseif ($user->role === 'guru') {
            $guru = Guru::where('user_id', $user->id)->first();
            $countBase->where(function ($q) use ($user, $guru) {
                $q->where('user_id', $user->id);
                if ($guru) $q->orWhere('peminjam_guru_id', $guru->id);
            });
        } elseif ($user->role === 'kaprog' && $user->jurusan_id) {
            $countBase->whereHas('item', fn ($q) => $q->where('jurusan_id', $user->jurusan_id));
        }

        $counts = [
            'all'      => (clone $countBase)->count(),
            'pending'  => (clone $countBase)->where('status', 'pending')->count(),
            'approved' => (clone $countBase)->where('status', 'approved')->count(),
            'borrowed' => (clone $countBase)->where('status', 'borrowed')->whereDate('rencana_kembali', '>=', now()->toDateString())->count(),
            'overdue'  => (clone $countBase)->where('status', 'borrowed')->whereDate('rencana_kembali', '<', now()->toDateString())->count(),
            'returned' => (clone $countBase)->where('status', 'returned')->count(),
        ];

        return view('inventory.borrowings', compact('borrowings', 'counts'));
    }

    /**
     * Submit borrow request (Guru & Siswa level)
     * Borrowers only select group & quantity; specific physical units are allocated upon approval.
     */
    public function requestBorrow(Request $request, InAppNotificationService $notifService)
    {
        $user = Auth::user();

        $request->validate([
            'inventory_item_id' => 'required|exists:inventory_items,id',
            'jumlah'            => 'required|integer|min:1',
            'rencana_kembali'   => 'required|date|after_or_equal:today',
            'keperluan'         => 'required|string|max:500',
        ]);

        $item = InventoryItem::findOrFail($request->inventory_item_id);

        // Enforce borrowing_scope authorization (Khusus Jurusan vs Umum)
        if (!$item->canBeBorrowedBy($user)) {
            $namaJurusan = $item->jurusan ? $item->jurusan->nama_jurusan : 'jurusan terkait';
            return back()->with('error', "Akses ditolak: Peralatan {$item->nama_barang} diatur khusus untuk siswa {$namaJurusan}. Anda tidak memiliki izin mengajukan peminjaman.");
        }

        if (!$item->isAvailable() || $item->countAvailableUnits() < $request->jumlah) {
            $tersedia = $item->countAvailableUnits();
            return back()->with('error', "Peralatan {$item->nama_barang} saat ini tidak mencukupi untuk dipinjam (unit layak tersedia: {$tersedia}).");
        }

        $siswa = Siswa::where('user_id', $user->id)->first();
        $guru = Guru::where('user_id', $user->id)->first();

        $borrowing = InventoryBorrowing::create([
            'inventory_item_id' => $item->id,
            'user_id'           => $user->id,
            'siswa_id'          => $siswa ? $siswa->id : null,
            'peminjam_guru_id'  => $guru ? $guru->id : null,
            'jumlah'            => (int) $request->jumlah,
            'tanggal_pinjam'    => now()->toDateString(),
            'rencana_kembali'   => $request->rencana_kembali,
            'keperluan'         => trim($request->keperluan),
            'status'            => 'pending',
            'kondisi_sebelum'   => 'baik',
        ]);

        return back()->with('success', "Permohonan peminjaman {$item->nama_barang} (x{$request->jumlah}) berhasil dikirim dan menunggu persetujuan Kaprog / Petugas.");
    }

    /**
     * Approve borrowing & reserve physical units atomically
     */
    public function approveBorrow($id, InAppNotificationService $notifService)
    {
        $user = Auth::user();

        // Enforce role: Only Superadmin, Admin, and Kaprog of the item's department
        if (!in_array($user->role, ['superadmin', 'admin', 'kaprog'], true)) {
            abort(403, 'Akses ditolak: Hanya Admin dan Kaprog yang berwenang menyetujui peminjaman.');
        }

        $borrowing = InventoryBorrowing::with(['item', 'user'])->findOrFail($id);
        $item = $borrowing->item;

        if ($user->role === 'kaprog' && $user->jurusan_id && $item->jurusan_id) {
            abort_unless(
                (int) $item->jurusan_id === (int) $user->jurusan_id,
                403,
                'Akses ditolak: Anda hanya berwenang memproses peralatan dari program keahlian Anda.'
            );
        }

        if ($borrowing->status !== 'pending') {
            return back()->with('error', 'Permohonan peminjaman ini sudah diproses sebelumnya.');
        }

        $failed = false;
        $allocatedUnits = [];

        DB::transaction(function () use ($borrowing, $item, $user, &$failed, &$allocatedUnits) {
            // Re-read item under lock
            $lockedItem = InventoryItem::whereKey($item->id)->lockForUpdate()->first();

            // Find physical units eligible for allocation
            $units = InventoryUnit::where('inventory_item_id', $lockedItem->id)
                ->where('is_active', true)
                ->where('status_ketersediaan', 'tersedia')
                ->where('kondisi', 'baik')
                ->whereDoesntHave('borrowingUnits', function ($q) {
                    $q->whereIn('status', ['reserved', 'borrowed']);
                })
                ->lockForUpdate()
                ->limit($borrowing->jumlah)
                ->get();

            // Fallback for legacy items without units
            if ($units->isEmpty() && !$lockedItem->units()->exists()) {
                if ($lockedItem->unit_tersedia < $borrowing->jumlah) {
                    $failed = true;
                    return;
                }
                $newTersedia = $lockedItem->unit_tersedia - $borrowing->jumlah;
                $lockedItem->update([
                    'unit_tersedia' => $newTersedia,
                    'status'        => $newTersedia <= 0 ? 'dipinjam' : 'tersedia',
                ]);

                $borrowing->update([
                    'status'      => 'borrowed', // legacy fallback
                    'decided_by'  => $user->id,
                    'approved_by' => $user->id,
                    'approved_at' => now(),
                ]);
                return;
            }

            if ($units->count() < $borrowing->jumlah) {
                $failed = true;
                return;
            }

            // Reserve the physical units
            foreach ($units as $unit) {
                InventoryBorrowingUnit::create([
                    'inventory_borrowing_id' => $borrowing->id,
                    'inventory_unit_id'      => $unit->id,
                    'status'                 => 'reserved',
                    'kondisi_sebelum'        => $unit->kondisi,
                ]);

                $unit->logActivity(
                    action: 'reservasi',
                    keterangan: "Unit dialokasikan & direservasi untuk peminjaman #{$borrowing->id} oleh {$borrowing->user?->name}",
                    userId: $user->id,
                    borrowingId: $borrowing->id
                );

                $allocatedUnits[] = $unit->kode_unit;
            }

            $borrowing->update([
                'status'      => 'approved',
                'decided_by'  => $user->id,
                'approved_by' => $user->id,
                'approved_at' => now(),
            ]);

            $lockedItem->syncStockCounts();
        });

        if ($failed) {
            return back()->with('error', "Stok unit fisik kondisi Baik untuk {$item->nama_barang} tidak mencukupi saat persetujuan.");
        }

        // Notify user
        if ($borrowing->user) {
            $unitCodes = implode(', ', $allocatedUnits);
            $notifService->sendToUser(
                $borrowing->user,
                "Permohonan Pinjam Alat Disetujui",
                "Permohonan peminjaman {$item->nama_barang} telah disetujui (Unit dialokasikan: {$unitCodes}). Silakan ambil di {$item->lokasi_penyimpanan}.",
                'administrative'
            );
        }

        return back()->with('success', "Peminjaman alat {$item->nama_barang} berhasil disetujui. Unit telah direservasi dan siap diserahkan.");
    }

    /**
     * Handover / dispatch tool to borrower
     * Updates physical unit status to 'dipinjam'
     */
    public function handoverBorrow(Request $request, $id, InAppNotificationService $notifService)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['superadmin', 'admin', 'kaprog'], true)) {
            abort(403, 'Akses ditolak: Hanya Admin dan Kaprog yang berwenang menyerahkan peralatan.');
        }

        $borrowing = InventoryBorrowing::with(['item', 'user', 'borrowingUnits.unit'])->findOrFail($id);
        $item = $borrowing->item;

        if ($user->role === 'kaprog' && $user->jurusan_id && $item->jurusan_id) {
            abort_unless(
                (int) $item->jurusan_id === (int) $user->jurusan_id,
                403,
                'Akses ditolak: Anda hanya berwenang menyerahkan peralatan dari program keahlian Anda.'
            );
        }

        if ($borrowing->status !== 'approved') {
            return back()->with('error', 'Hanya permohonan yang berstatus Disetujui yang dapat diproses penyerahan.');
        }

        DB::transaction(function () use ($borrowing, $item, $user) {
            foreach ($borrowing->borrowingUnits as $bu) {
                $bu->update([
                    'status'        => 'borrowed',
                    'diserahkan_at' => now(),
                ]);

                if ($bu->unit) {
                    $bu->unit->update([
                        'status_ketersediaan' => 'dipinjam',
                    ]);

                    $bu->unit->logActivity(
                        action: 'penyerahan',
                        keterangan: "Barang fisik diserahkan kepada {$borrowing->user?->name}. Batas pengembalian: " . $borrowing->rencana_kembali->format('d/m/Y'),
                        userId: $user->id,
                        borrowingId: $borrowing->id
                    );
                }
            }

            $borrowing->update([
                'status'          => 'borrowed',
                'diserahkan_oleh' => $user->id,
                'diserahkan_at'   => now(),
                'tanggal_pinjam'  => now()->toDateString(),
            ]);

            $item->syncStockCounts();
        });

        if ($borrowing->user) {
            $notifService->sendToUser(
                $borrowing->user,
                "Peralatan Telah Diserahkan",
                "Peralatan {$item->nama_barang} telah diserahkan oleh petugas. Mohon jaga kondisi fisik dan kembalikan sebelum {$borrowing->rencana_kembali->format('d/m/Y')}.",
                'administrative'
            );
        }

        return back()->with('success', "Penyerahan alat {$item->nama_barang} berhasil dikonfirmasi. Status unit kini DIPINJAM.");
    }

    /**
     * Process tool return and inspect physical condition per unit
     */
    public function returnItem(Request $request, $id, InAppNotificationService $notifService)
    {
        $request->validate([
            'kondisi_sesudah'      => 'required|in:baik,rusak_ringan,rusak_berat,perlu_pemeriksaan',
            'catatan_pengembalian' => 'nullable|string|max:500',
        ]);

        $borrowing = InventoryBorrowing::with(['item', 'user', 'borrowingUnits.unit'])->findOrFail($id);
        $item = $borrowing->item;

        $user = Auth::user();
        if (!in_array($user->role, ['superadmin', 'admin', 'kaprog'], true)) {
            abort(403, 'Akses ditolak: Hanya Admin dan Kaprog yang berwenang memproses pengembalian.');
        }

        if ($user->role === 'kaprog' && $user->jurusan_id && $item->jurusan_id) {
            abort_unless(
                (int) $item->jurusan_id === (int) $user->jurusan_id,
                403,
                'Akses ditolak: Anda hanya berwenang memproses peralatan dari program keahlian Anda.'
            );
        }

        if (!in_array($borrowing->status, ['borrowed', 'approved'], true)) {
            return back()->with('error', 'Status peminjaman ini tidak valid untuk diproses pengembalian.');
        }

        $kondisi = $request->kondisi_sesudah;

        DB::transaction(function () use ($request, $borrowing, $item, $kondisi, $user) {
            $hasUnits = $borrowing->borrowingUnits->isNotEmpty();

            if ($hasUnits) {
                foreach ($borrowing->borrowingUnits as $bu) {
                    $bu->update([
                        'status'               => 'returned',
                        'kondisi_sesudah'      => $kondisi,
                        'catatan_pengembalian' => $request->catatan_pengembalian,
                        'dikembalikan_at'      => now(),
                    ]);

                    if ($bu->unit) {
                        $bu->unit->update([
                            'kondisi'             => $kondisi,
                            'status_ketersediaan' => 'tersedia', // strictly 2 statuses: tersedia / dipinjam
                        ]);

                        $bu->unit->logActivity(
                            action: 'pengembalian',
                            keterangan: "Pengembalian dari {$borrowing->user?->name}. Kondisi fisik: {$kondisi}. Catatan: {$request->catatan_pengembalian}",
                            kondisiLama: $bu->kondisi_sebelum,
                            kondisiBaru: $kondisi,
                            userId: $user->id,
                            borrowingId: $borrowing->id
                        );
                    }
                }
            }

            $borrowing->update([
                'status'               => 'returned',
                'tanggal_kembali'      => now()->toDateString(),
                'kondisi_sesudah'      => $kondisi,
                'catatan_pengembalian' => $request->catatan_pengembalian,
            ]);

            // If legacy without physical units
            if (!$hasUnits) {
                $newTersedia = $item->unit_tersedia + $borrowing->jumlah;
                if (in_array($kondisi, ['rusak_berat', 'maintenance'])) {
                    $item->update([
                        'kondisi' => 'rusak_berat',
                        'status'  => 'maintenance',
                    ]);
                } else {
                    $item->update([
                        'unit_tersedia' => min($newTersedia, $item->total_unit),
                        'kondisi'       => $kondisi,
                        'status'        => 'tersedia',
                    ]);
                }
            } else {
                $item->syncStockCounts();
            }
        });

        if ($borrowing->user) {
            $notifService->sendToUser(
                $borrowing->user,
                "Pengembalian Alat Selesai",
                "Pengembalian alat {$item->nama_barang} telah diverifikasi oleh petugas (Kondisi: {$kondisi}).",
                'administrative'
            );
        }

        return back()->with('success', "Pengembalian alat {$item->nama_barang} berhasil diverifikasi dan dicatat.");
    }

    /**
     * Reject borrow request
     */
    public function rejectBorrow(Request $request, $id)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['superadmin', 'admin', 'kaprog'], true)) {
            abort(403, 'Akses ditolak: Hanya Admin dan Kaprog yang berwenang menolak peminjaman.');
        }

        $borrowing = InventoryBorrowing::with(['item', 'borrowingUnits.unit'])->findOrFail($id);
        $item = $borrowing->item;

        if ($user->role === 'kaprog' && $user->jurusan_id && $item->jurusan_id) {
            abort_unless((int) $item->jurusan_id === (int) $user->jurusan_id, 403);
        }

        if (!in_array($borrowing->status, ['pending', 'approved'], true)) {
            return back()->with('error', 'Peminjaman ini sudah tidak dapat ditolak.');
        }

        DB::transaction(function () use ($borrowing, $item, $user) {
            // Release reserved units if any
            foreach ($borrowing->borrowingUnits as $bu) {
                if ($bu->unit) {
                    $bu->unit->logActivity(
                        action: 'reservasi_dibatalkan',
                        keterangan: "Peminjaman ditolak oleh petugas. Alokasi unit dibatalkan.",
                        userId: $user->id,
                        borrowingId: $borrowing->id
                    );
                }
            }
            $borrowing->borrowingUnits()->delete();

            $borrowing->update([
                'status'     => 'rejected',
                'decided_by' => $user->id,
            ]);

            $item->syncStockCounts();
        });

        return back()->with('success', "Permohonan peminjaman berhasil ditolak.");
    }

    /**
     * Cancel borrow request (by borrower before handover)
     */
    public function cancelBorrow($id)
    {
        $user = Auth::user();
        $borrowing = InventoryBorrowing::with(['item', 'borrowingUnits.unit'])->findOrFail($id);

        if ($borrowing->user_id !== $user->id && !in_array($user->role, ['superadmin', 'admin', 'kaprog'])) {
            abort(403, 'Akses ditolak: Anda hanya dapat membatalkan permohonan milik Anda sendiri.');
        }

        if (!in_array($borrowing->status, ['pending', 'approved'], true)) {
            return back()->with('error', 'Peminjaman ini sudah berjalan atau selesai sehingga tidak dapat dibatalkan.');
        }

        DB::transaction(function () use ($borrowing, $user) {
            foreach ($borrowing->borrowingUnits as $bu) {
                if ($bu->unit) {
                    $bu->unit->logActivity(
                        action: 'reservasi_dibatalkan',
                        keterangan: "Permohonan dibatalkan oleh peminjam.",
                        userId: $user->id,
                        borrowingId: $borrowing->id
                    );
                }
            }
            $borrowing->borrowingUnits()->delete();

            $borrowing->update([
                'status' => 'cancelled',
            ]);

            $borrowing->item?->syncStockCounts();
        });

        return back()->with('success', "Permohonan peminjaman berhasil dibatalkan.");
    }

    /**
     * Manual transaction recorded by a petugas on behalf of a siswa or guru.
     */
    public function manualBorrow(Request $request, InAppNotificationService $notifService)
    {
        $request->validate([
            'jenis_peminjam'    => 'required|in:siswa,guru',
            'siswa_id'          => 'required_if:jenis_peminjam,siswa|nullable|exists:siswas,id',
            'guru_id'           => 'required_if:jenis_peminjam,guru|nullable|exists:gurus,id',
            'inventory_item_id' => 'required|exists:inventory_items,id',
            'jumlah'            => 'required|integer|min:1',
            'tanggal_pinjam'    => 'required|date',
            'rencana_kembali'   => 'required|date|after_or_equal:tanggal_pinjam',
            'keperluan'         => 'required|string|max:500',
        ]);

        $user = Auth::user();
        $item = InventoryItem::findOrFail($request->inventory_item_id);

        if ($user->role === 'kaprog' && $user->jurusan_id && $item->jurusan_id && (int) $item->jurusan_id !== (int) $user->jurusan_id) {
            abort(403, 'Akses ditolak: Anda hanya berwenang mencatat peminjaman peralatan dari program keahlian Anda.');
        }

        if (in_array($item->kondisi, ['rusak_berat', 'maintenance']) || $item->status === 'afkir') {
            return back()->withInput()->with('error', "Peralatan {$item->nama_barang} sedang {$item->kondisi} dan tidak dapat dipinjam.");
        }

        $peminjamUser = null;
        $siswaId = null;
        $guruId = null;

        if ($request->jenis_peminjam === 'siswa') {
            $siswa = Siswa::with('user')->findOrFail($request->siswa_id);
            if ($siswa->status !== 'aktif') {
                return back()->withInput()->with('error', 'Siswa tersebut tidak berstatus aktif.');
            }
            $siswaId = $siswa->id;
            $peminjamUser = $siswa->user;
        } else {
            $guru = Guru::with('user')->findOrFail($request->guru_id);
            $guruId = $guru->id;
            $peminjamUser = $guru->user;
        }

        $borrowing = null;
        $failed = false;

        DB::transaction(function () use ($request, $item, $siswaId, $guruId, $peminjamUser, $user, &$borrowing, &$failed) {
            $locked = InventoryItem::whereKey($item->id)->lockForUpdate()->first();

            // Check if units exist
            $units = InventoryUnit::where('inventory_item_id', $locked->id)
                ->where('is_active', true)
                ->where('status_ketersediaan', 'tersedia')
                ->where('kondisi', 'baik')
                ->whereDoesntHave('borrowingUnits', fn ($q) => $q->whereIn('status', ['reserved', 'borrowed']))
                ->lockForUpdate()
                ->limit($request->jumlah)
                ->get();

            // Legacy fallback if no units table rows exist yet
            if ($units->isEmpty() && !$locked->units()->exists()) {
                if ($locked->unit_tersedia < $request->jumlah) {
                    $failed = true;
                    return;
                }

                $newTersedia = $locked->unit_tersedia - $request->jumlah;
                $borrowing = InventoryBorrowing::create([
                    'inventory_item_id' => $locked->id,
                    'user_id'           => $peminjamUser->id,
                    'siswa_id'          => $siswaId,
                    'peminjam_guru_id'  => $guruId,
                    'sumber_transaksi'  => 'manual_petugas',
                    'dicatat_oleh'      => $user->id,
                    'jumlah'            => $request->jumlah,
                    'tanggal_pinjam'    => $request->tanggal_pinjam,
                    'rencana_kembali'   => $request->rencana_kembali,
                    'keperluan'         => $request->keperluan,
                    'status'            => 'borrowed',
                    'decided_by'        => $user->id,
                    'kondisi_sebelum'   => $locked->kondisi === 'baik' ? 'baik' : 'rusak_ringan',
                ]);

                $locked->update([
                    'unit_tersedia' => $newTersedia,
                    'status'        => $newTersedia <= 0 ? 'dipinjam' : 'tersedia',
                ]);
                return;
            }

            if ($units->count() < $request->jumlah) {
                $failed = true;
                return;
            }

            $borrowing = InventoryBorrowing::create([
                'inventory_item_id' => $locked->id,
                'user_id'           => $peminjamUser->id,
                'siswa_id'          => $siswaId,
                'peminjam_guru_id'  => $guruId,
                'sumber_transaksi'  => 'manual_petugas',
                'dicatat_oleh'      => $user->id,
                'jumlah'            => $request->jumlah,
                'tanggal_pinjam'    => $request->tanggal_pinjam,
                'rencana_kembali'   => $request->rencana_kembali,
                'keperluan'         => $request->keperluan,
                'status'            => 'borrowed',
                'decided_by'        => $user->id,
                'diserahkan_oleh'   => $user->id,
                'diserahkan_at'     => now(),
                'kondisi_sebelum'   => 'baik',
            ]);

            foreach ($units as $unit) {
                InventoryBorrowingUnit::create([
                    'inventory_borrowing_id' => $borrowing->id,
                    'inventory_unit_id'      => $unit->id,
                    'status'                 => 'borrowed',
                    'kondisi_sebelum'        => $unit->kondisi,
                    'diserahkan_at'          => now(),
                ]);

                $unit->update([
                    'status_ketersediaan' => 'dipinjam',
                ]);

                $unit->logActivity(
                    action: 'penyerahan',
                    keterangan: "Peminjaman manual oleh petugas {$user->name} atas nama {$peminjamUser->name}",
                    userId: $user->id,
                    borrowingId: $borrowing->id
                );
            }

            $locked->syncStockCounts();
        });

        if ($failed || !$borrowing) {
            return back()->withInput()->with('error', "Stok unit layak untuk {$item->nama_barang} tidak mencukupi (tersedia: {$item->unit_tersedia}).");
        }

        if ($peminjamUser) {
            $notifService->sendToUser(
                $peminjamUser,
                'Peminjaman Alat Dicatat Petugas',
                "Petugas mencatat peminjaman {$item->nama_barang} x{$borrowing->jumlah} atas nama Anda (rencana kembali: {$borrowing->rencana_kembali->format('d/m/Y')}).",
                'administrative'
            );
        }

        return back()->with('success', "Transaksi manual tercatat: {$item->nama_barang} x{$borrowing->jumlah} diserahkan dan unit tercatat dipinjam.");
    }

    /**
     * Manual return recorded by a petugas
     */
    public function manualReturn(Request $request, $id, InAppNotificationService $notifService)
    {
        $request->validate([
            'kondisi_sesudah'      => 'required|in:baik,rusak_ringan,rusak_berat,perlu_pemeriksaan',
            'tanggal_kembali'      => 'required|date',
            'catatan_pengembalian' => 'nullable|string|max:500',
        ]);

        $borrowing = InventoryBorrowing::with(['item', 'user', 'borrowingUnits.unit'])->findOrFail($id);
        $item = $borrowing->item;

        $user = Auth::user();
        if (!in_array($user->role, ['superadmin', 'admin', 'kaprog'], true)) {
            abort(403, 'Akses ditolak: Hanya Admin dan Kaprog yang berwenang memproses pengembalian.');
        }

        if ($user->role === 'kaprog' && $user->jurusan_id && $item->jurusan_id) {
            abort_unless(
                (int) $item->jurusan_id === (int) $user->jurusan_id,
                403,
                'Akses ditolak: Anda hanya berwenang memproses peralatan dari program keahlian Anda.'
            );
        }

        if (!in_array($borrowing->status, ['approved', 'borrowed'], true)) {
            return back()->with('error', "Transaksi ini sudah berstatus {$borrowing->status}; pengembalian tidak dapat dicatat dua kali.");
        }

        DB::transaction(function () use ($request, $borrowing, $item, $user) {
            $locked = InventoryItem::whereKey($item->id)->lockForUpdate()->first();
            $kondisi = $request->kondisi_sesudah;

            if ($borrowing->borrowingUnits->isNotEmpty()) {
                foreach ($borrowing->borrowingUnits as $bu) {
                    $bu->update([
                        'status'               => 'returned',
                        'kondisi_sesudah'      => $kondisi,
                        'catatan_pengembalian' => $request->catatan_pengembalian,
                        'dikembalikan_at'      => now(),
                    ]);

                    if ($bu->unit) {
                        $bu->unit->update([
                            'kondisi'             => $kondisi,
                            'status_ketersediaan' => 'tersedia',
                        ]);

                        $bu->unit->logActivity(
                            action: 'pengembalian',
                            keterangan: "Pengembalian walk-in dicatat petugas {$user->name}. Kondisi fisik: {$kondisi}.",
                            kondisiLama: $bu->kondisi_sebelum,
                            kondisiBaru: $kondisi,
                            userId: $user->id,
                            borrowingId: $borrowing->id
                        );
                    }
                }
            }

            $borrowing->update([
                'status'               => 'returned',
                'tanggal_kembali'      => $request->tanggal_kembali,
                'kondisi_sesudah'      => $kondisi,
                'catatan_pengembalian' => $request->catatan_pengembalian,
            ]);

            if ($borrowing->borrowingUnits->isEmpty()) {
                if (in_array($kondisi, ['rusak_berat', 'maintenance'])) {
                    $locked->update([
                        'kondisi' => 'rusak_berat',
                        'status'  => 'maintenance',
                    ]);
                } else {
                    $locked->update([
                        'unit_tersedia' => min($locked->unit_tersedia + $borrowing->jumlah, $locked->total_unit),
                        'kondisi'       => $kondisi,
                        'status'        => 'tersedia',
                    ]);
                }
            } else {
                $locked->syncStockCounts();
            }
        });

        if ($borrowing->user) {
            $notifService->sendToUser(
                $borrowing->user,
                'Pengembalian Alat Dicatat Petugas',
                "Pengembalian {$item->nama_barang} telah dicatat petugas (Kondisi: {$request->kondisi_sesudah}).",
                'administrative'
            );
        }

        return back()->with('success', "Pengembalian manual {$item->nama_barang} berhasil dicatat dan fisik unit telah diverifikasi.");
    }

    /**
     * AJAX options for manual modal
     */
    public function manualOptions(Request $request)
    {
        $user = Auth::user();
        $kaprogJurusan = ($user->role === 'kaprog' && $user->jurusan_id) ? (int) $user->jurusan_id : null;
        $q = trim((string) $request->input('q'));

        $siswaQuery = Siswa::with('kelas:id,nama_kelas')->where('status', 'aktif');
        if ($kaprogJurusan) {
            $siswaQuery->whereHas('kelas', fn ($x) => $x->where('jurusan_id', $kaprogJurusan));
        }
        if ($q !== '') {
            $siswaQuery->where(fn ($x) => $x->where('nama', 'like', "%{$q}%")->orWhere('nis', 'like', "%{$q}%"));
        }
        $siswas = $siswaQuery->orderBy('nama')->limit(30)->get(['id', 'nama', 'nis'])
            ->map(fn ($s) => ['id' => $s->id, 'label' => $s->nama . ' — ' . ($s->kelas->nama_kelas ?? '-') . ($s->nis ? " (NIS {$s->nis})" : '')]);

        $guruQuery = Guru::query()->whereHas('user', fn ($u) => $u->whereIn('role', ['guru', 'kaprog']));
        if ($q !== '') {
            $guruQuery->where(fn ($x) => $x->where('nama', 'like', "%{$q}%")->orWhere('nip', 'like', "%{$q}%"));
        }
        $gurus = $guruQuery->orderBy('nama')->limit(30)->get(['id', 'nama', 'nip'])
            ->map(fn ($g) => ['id' => $g->id, 'label' => $g->nama . ($g->nip ? " (NIP {$g->nip})" : '')]);

        $itemQuery = InventoryItem::query()->whereIn('status', ['tersedia', 'dipinjam']);
        if ($kaprogJurusan) {
            $itemQuery->where(fn ($x) => $x->where('jurusan_id', $kaprogJurusan)->orWhereNull('jurusan_id'));
        }
        $items = $itemQuery->orderBy('nama_barang')->get(['id', 'nama_barang', 'kode_barang', 'unit_tersedia', 'kondisi'])
            ->map(fn ($i) => [
                'id'      => $i->id,
                'label'   => $i->nama_barang . ' (' . $i->kode_barang . ')',
                'stok'    => (int) $i->countAvailableUnits(),
                'kondisi' => $i->kondisi,
            ]);

        return response()->json(['siswa' => $siswas, 'guru' => $gurus, 'items' => $items]);
    }

    /**
     * QR Code / Barcode Scanner Interface
     */
    public function scanIndex()
    {
        return view('inventory.scan');
    }

    /**
     * AJAX: Lookup scanned barcode / QR token
     * Identifies unit, its group, active borrowing, and recommended action.
     */
    public function scanLookup(Request $request): JsonResponse
    {
        $code = trim((string) $request->input('code'));
        if ($code === '') {
            return response()->json(['success' => false, 'message' => 'Kode scan tidak boleh kosong.'], 422);
        }

        // Search unit by kode_unit, barcode_eksternal, or qr_token
        $unit = InventoryUnit::with([
            'item.jurusan',
            'borrowingUnits' => fn ($q) => $q->whereIn('status', ['reserved', 'borrowed'])->latest('id'),
            'borrowingUnits.borrowing.user',
            'borrowingUnits.borrowing.siswa.kelas',
            'borrowingUnits.borrowing.peminjamGuru',
        ])
        ->where('kode_unit', $code)
        ->orWhere('barcode_eksternal', $code)
        ->orWhere('qr_token', $code)
        ->first();

        // If not found as unit, check if scanned code is item group's kode_barang
        if (!$unit) {
            $itemGroup = InventoryItem::with('jurusan')
                ->where('kode_barang', $code)
                ->orWhere('qr_token', $code)
                ->first();

            if ($itemGroup) {
                return response()->json([
                    'success'    => true,
                    'type'       => 'group',
                    'item'       => [
                        'id'            => $itemGroup->id,
                        'nama_barang'   => $itemGroup->nama_barang,
                        'kode_barang'   => $itemGroup->kode_barang,
                        'jurusan'       => $itemGroup->jurusan?->nama_jurusan ?? 'Umum',
                        'total_unit'    => $itemGroup->total_unit,
                        'unit_tersedia' => $itemGroup->countAvailableUnits(),
                        'lokasi'        => $itemGroup->lokasi_penyimpanan,
                        'url'           => route('inventory.show', $itemGroup->id),
                    ],
                    'message'    => "Kode kelompok terdeteksi: {$itemGroup->nama_barang}. Buka detail kelompok untuk melihat unit fisik individual.",
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => "Unit fisik dengan kode atau barcode '{$code}' tidak ditemukan di sistem inventaris.",
            ], 404);
        }

        $user = Auth::user();
        if ($user->role === 'kaprog' && $user->jurusan_id && $unit->item->jurusan_id) {
            if ((int) $unit->item->jurusan_id !== (int) $user->jurusan_id) {
                return response()->json([
                    'success' => false,
                    'message' => "Unit ini milik program keahlian {$unit->item->jurusan->nama_jurusan}. Anda tidak berwenang memproses unit ini.",
                ], 403);
            }
        }

        $activeBu = $unit->borrowingUnits->first();
        $borrowing = $activeBu?->borrowing;

        $recommendedAction = null;
        if ($activeBu && $activeBu->status === 'reserved' && $borrowing && $borrowing->status === 'approved') {
            $recommendedAction = 'handover';
        } elseif ($activeBu && $activeBu->status === 'borrowed' && $borrowing) {
            $recommendedAction = 'return';
        } elseif ($unit->isEligibleForLoan()) {
            $recommendedAction = 'available';
        }

        return response()->json([
            'success'            => true,
            'type'               => 'unit',
            'unit'               => [
                'id'                  => $unit->id,
                'kode_unit'           => $unit->kode_unit,
                'barcode_eksternal'   => $unit->barcode_eksternal,
                'kondisi'             => $unit->kondisi,
                'status_ketersediaan' => $unit->status_ketersediaan,
                'lokasi'              => $unit->effective_lokasi,
                'is_active'           => $unit->is_active,
            ],
            'item'               => [
                'id'          => $unit->item->id,
                'nama_barang' => $unit->item->nama_barang,
                'kode_barang' => $unit->item->kode_barang,
                'jurusan'     => $unit->item->jurusan?->nama_jurusan ?? 'Umum',
                'kategori'    => $unit->item->kategori,
                'url'         => route('inventory.show', $unit->item->id),
            ],
            'borrowing'          => $borrowing ? [
                'id'              => $borrowing->id,
                'status'          => $borrowing->status,
                'peminjam'        => $borrowing->user?->name,
                'role_peminjam'   => $borrowing->siswa ? 'Siswa (' . ($borrowing->siswa->kelas->nama_kelas ?? '-') . ')' : ($borrowing->peminjamGuru ? 'Guru' : 'Staf'),
                'tanggal_pinjam'  => $borrowing->tanggal_pinjam->format('d/m/Y'),
                'rencana_kembali' => $borrowing->rencana_kembali->format('d/m/Y'),
                'is_overdue'      => $borrowing->is_overdue,
                'keperluan'       => $borrowing->keperluan,
                'handover_url'    => route('inventory.borrow.handover', $borrowing->id),
                'return_url'      => route('inventory.borrow.return', $borrowing->id),
            ] : null,
            'recommended_action' => $recommendedAction,
        ]);
    }

    /**
     * Printable label sheets for physical units
     */
    public function printLabels(Request $request, $id)
    {
        $item = InventoryItem::with(['jurusan', 'units' => fn ($q) => $q->where('is_active', true)->orderBy('kode_unit')])->findOrFail($id);

        $unitId = $request->input('unit_id');
        if ($unitId) {
            $units = $item->units->where('id', $unitId);
        } else {
            $units = $item->units;
        }

        return view('inventory.print-labels', compact('item', 'units'));
    }

    /**
     * Delete asset group
     */
    public function destroy($id)
    {
        $item = InventoryItem::findOrFail($id);
        $user = Auth::user();

        if ($user->role === 'kaprog' && $user->jurusan_id && $item->jurusan_id) {
            abort_unless(
                (int) $item->jurusan_id === (int) $user->jurusan_id,
                403,
                'Akses ditolak: Anda hanya berwenang menghapus peralatan dari program keahlian Anda.'
            );
        }

        if ($item->borrowings()->whereIn('status', ['pending', 'borrowed', 'approved'])->exists()) {
            return back()->with('error', 'Tidak dapat menghapus alat yang masih dalam status peminjaman aktif.');
        }

        // If units have borrowing history, archive group rather than destructive hard delete
        if ($item->borrowings()->exists()) {
            $item->update(['is_active' => false]);
            $item->units()->update(['is_active' => false, 'status_alasan' => 'dihapuskan']);
            return redirect()->route('inventory.index')->with('success', 'Kelompok aset berhasil diarsipkan karena memiliki riwayat peminjaman.');
        }

        $item->delete();

        return redirect()->route('inventory.index')->with('success', 'Aset peralatan berhasil dihapus.');
    }
}
