<?php

namespace App\Http\Controllers;

use App\Models\InventoryBorrowing;
use App\Models\InventoryItem;
use App\Models\Jurusan;
use App\Models\Siswa;
use App\Services\InAppNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InventoryController extends Controller
{
    /**
     * Asset inventory gallery & list
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = InventoryItem::with(['jurusan']);

        // Scoping for KAPROG
        if ($user->role === 'kaprog' && $user->jurusan_id) {
            $query->where('jurusan_id', $user->jurusan_id);
        }

        if ($request->filled('jurusan_id')) {
            $query->where('jurusan_id', $request->jurusan_id);
        }

        if ($request->filled('kondisi')) {
            $query->where('kondisi', $request->kondisi);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_barang', 'like', "%{$search}%")
                  ->orWhere('kode_barang', 'like', "%{$search}%")
                  ->orWhere('kategori', 'like', "%{$search}%")
                  ->orWhere('lokasi_penyimpanan', 'like', "%{$search}%");
            });
        }

        $items = $query->orderBy('nama_barang')->paginate(16)->withQueryString();
        $jurusans = Jurusan::orderBy('nama_jurusan')->get();

        // Stats summary
        $stats = [
            'total'     => InventoryItem::count(),
            'tersedia'  => InventoryItem::where('status', 'tersedia')->where('unit_tersedia', '>', 0)->count(),
            'dipinjam'  => InventoryBorrowing::whereIn('status', ['borrowed', 'approved'])->count(),
            'rusak'     => InventoryItem::whereIn('kondisi', ['rusak_berat', 'maintenance'])->count(),
        ];

        return view('inventory.index', compact('items', 'jurusans', 'stats'));
    }

    /**
     * Form to create asset
     */
    public function create()
    {
        $jurusans = Jurusan::orderBy('nama_jurusan')->get();
        return view('inventory.create', compact('jurusans'));
    }

    /**
     * Store new asset
     */
    public function store(Request $request)
    {
        $request->validate([
            'kode_barang'        => 'required|string|max:50|unique:inventory_items,kode_barang',
            'nama_barang'        => 'required|string|max:255',
            'jurusan_id'         => 'nullable|exists:jurusans,id',
            'kategori'           => 'required|string|max:100',
            'lokasi_penyimpanan' => 'required|string|max:150',
            'deskripsi'          => 'nullable|string',
            'total_unit'         => 'required|integer|min:1|max:1000',
            'kondisi'            => 'required|in:baik,rusak_ringan,rusak_berat,maintenance',
        ]);

        $status = in_array($request->kondisi, ['rusak_berat', 'maintenance']) ? 'maintenance' : 'tersedia';

        $item = InventoryItem::create([
            'kode_barang'        => strtoupper($request->kode_barang),
            'nama_barang'        => $request->nama_barang,
            'jurusan_id'         => $request->jurusan_id,
            'kategori'           => $request->kategori,
            'lokasi_penyimpanan' => $request->lokasi_penyimpanan,
            'deskripsi'          => $request->deskripsi,
            'total_unit'         => $request->total_unit,
            'unit_tersedia'      => $request->total_unit,
            'kondisi'            => $request->kondisi,
            'status'             => $status,
            'qr_token'           => Str::random(40),
        ]);

        return redirect()->route('inventory.index')
            ->with('success', "Aset peralatan {$item->nama_barang} ({$item->kode_barang}) berhasil didaftarkan.");
    }

    /**
     * Asset Detail View
     */
    public function show($id)
    {
        $item = InventoryItem::with(['jurusan', 'borrowings.user', 'borrowings.siswa'])->findOrFail($id);
        $activeBorrowings = $item->borrowings()->whereIn('status', ['pending', 'approved', 'borrowed'])->latest()->get();
        $historyBorrowings = $item->borrowings()->whereIn('status', ['returned', 'rejected'])->latest()->limit(20)->get();

        return view('inventory.show', compact('item', 'activeBorrowings', 'historyBorrowings'));
    }

    /**
     * Borrowings management & transactions view
     */
    public function borrowingsIndex(Request $request)
    {
        $user = Auth::user();
        $query = InventoryBorrowing::with(['item.jurusan', 'user', 'siswa.kelas']);

        if ($user->role === 'siswa') {
            $query->where('user_id', $user->id);
        } elseif ($user->role === 'kaprog' && $user->jurusan_id) {
            $query->whereHas('item', function ($q) use ($user) {
                $q->where('jurusan_id', $user->jurusan_id);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $borrowings = $query->latest('tanggal_pinjam')->latest('id')->paginate(20)->withQueryString();

        return view('inventory.borrowings', compact('borrowings'));
    }

    /**
     * Submit borrow request
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

        if (!$item->isAvailable() || $item->unit_tersedia < $request->jumlah) {
            return back()->with('error', "Peralatan {$item->nama_barang} saat ini tidak tersedia untuk dipinjam (stok tersedia: {$item->unit_tersedia}).");
        }

        $siswa = Siswa::where('user_id', $user->id)->first();

        $borrowing = InventoryBorrowing::create([
            'inventory_item_id' => $item->id,
            'user_id'           => $user->id,
            'siswa_id'          => $siswa ? $siswa->id : null,
            'jumlah'            => $request->jumlah,
            'tanggal_pinjam'    => now()->toDateString(),
            'rencana_kembali'   => $request->rencana_kembali,
            'keperluan'         => $request->keperluan,
            'status'            => 'pending',
            'kondisi_sebelum'   => $item->kondisi === 'baik' ? 'baik' : 'rusak_ringan',
        ]);

        return back()->with('success', "Pengajuan peminjaman {$item->nama_barang} berhasil dikirim dan menunggu persetujuan toolman/admin.");
    }

    /**
     * Approve borrowing & dispatch tool
     */
    public function approveBorrow($id, InAppNotificationService $notifService)
    {
        $borrowing = InventoryBorrowing::with(['item', 'user'])->findOrFail($id);
        $item = $borrowing->item;

        $user = Auth::user();
        if ($user->role === 'kaprog' && $user->jurusan_id && $item->jurusan_id) {
            abort_unless(
                (int) $item->jurusan_id === (int) $user->jurusan_id,
                403,
                'Akses ditolak: Anda hanya berwenang memproses peralatan dari program keahlian Anda.'
            );
        }

        if ($borrowing->status !== 'pending') {
            return back()->with('error', 'Peminjaman ini sudah diproses sebelumnya.');
        }

        if ($item->unit_tersedia < $borrowing->jumlah) {
            return back()->with('error', "Stok unit {$item->nama_barang} tidak mencukupi untuk diserahkan.");
        }

        DB::transaction(function () use ($borrowing, $item) {
            $newTersedia = $item->unit_tersedia - $borrowing->jumlah;
            $newStatus = $newTersedia <= 0 ? 'dipinjam' : 'tersedia';

            $item->update([
                'unit_tersedia' => $newTersedia,
                'status'        => $newStatus,
            ]);

            $borrowing->update([
                'status'     => 'borrowed',
                'decided_by' => Auth::id(),
            ]);
        });

        // Notify user
        if ($borrowing->user) {
            $notifService->sendToUser(
                $borrowing->user,
                "Peminjaman Alat Disetujui",
                "Peminjaman alat {$item->nama_barang} telah disetujui. Silakan ambil alat di ruang {$item->lokasi_penyimpanan}.",
                'administrative'
            );
        }

        return back()->with('success', "Peminjaman alat {$item->nama_barang} berhasil disetujui dan diserahkan.");
    }

    /**
     * Process tool return and inspect physical condition
     */
    public function returnItem(Request $request, $id, InAppNotificationService $notifService)
    {
        $request->validate([
            'kondisi_sesudah'      => 'required|in:baik,rusak_ringan,rusak_berat',
            'catatan_pengembalian' => 'nullable|string|max:500',
        ]);

        $borrowing = InventoryBorrowing::with(['item', 'user'])->findOrFail($id);
        $item = $borrowing->item;

        $user = Auth::user();
        if ($user->role === 'kaprog' && $user->jurusan_id && $item->jurusan_id) {
            abort_unless(
                (int) $item->jurusan_id === (int) $user->jurusan_id,
                403,
                'Akses ditolak: Anda hanya berwenang memproses peralatan dari program keahlian Anda.'
            );
        }

        if ($borrowing->status !== 'borrowed' && $borrowing->status !== 'approved') {
            return back()->with('error', 'Status peminjaman ini tidak valid untuk diproses pengembalian.');
        }

        DB::transaction(function () use ($request, $borrowing, $item) {
            $kondisi = $request->kondisi_sesudah;

            $borrowing->update([
                'status'               => 'returned',
                'tanggal_kembali'      => now()->toDateString(),
                'kondisi_sesudah'      => $kondisi,
                'catatan_pengembalian' => $request->catatan_pengembalian,
            ]);

            // Restore units if item is still operational
            $newTersedia = $item->unit_tersedia + $borrowing->jumlah;
            if ($kondisi === 'rusak_berat') {
                $item->update([
                    'kondisi'       => 'rusak_berat',
                    'status'        => 'maintenance', // Quarantined from lending
                ]);
            } else {
                $item->update([
                    'unit_tersedia' => min($newTersedia, $item->total_unit),
                    'kondisi'       => $kondisi,
                    'status'        => 'tersedia',
                ]);
            }
        });

        if ($borrowing->user) {
            $notifService->sendToUser(
                $borrowing->user,
                "Pengembalian Alat Selesai",
                "Pengembalian alat {$item->nama_barang} telah diverifikasi oleh petugas (Kondisi: {$request->kondisi_sesudah}).",
                'administrative'
            );
        }

        return back()->with('success', "Pengembalian alat {$item->nama_barang} berhasil dicatat.");
    }

    /**
     * Delete asset
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

        $item->delete();

        return redirect()->route('inventory.index')->with('success', 'Aset peralatan berhasil dihapus.');
    }
}
