<?php

namespace App\Http\Controllers;

use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\PklAttendance;
use App\Models\PklDudiPartner;
use App\Models\PklLogbook;
use App\Models\PklPlacement;
use App\Models\Siswa;
use App\Models\User;
use App\Services\InAppNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PklController extends Controller
{
    /**
     * Resolve the department id a KAPROG actually manages.
     * Server-side authority: active assignment table first, users.jurusan_id as fallback.
     * Returns null for non-kaprog users.
     */
    protected function managedJurusanId(?User $user): ?int
    {
        if (!$user || $user->role !== 'kaprog') {
            return null;
        }

        $assignment = $user->guru?->activeKaprogAssignment;

        return $assignment?->jurusan_id ?? ($user->jurusan_id ? (int) $user->jurusan_id : null);
    }

    /**
     * DUDI Partners List (Admin / Kaprog)
     */
    public function partnersIndex(Request $request)
    {
        $user = Auth::user();
        $query = PklDudiPartner::with(['jurusan', 'placements']);

        $kaprogJurusan = $this->managedJurusanId($user);
        if ($kaprogJurusan) {
            $query->where(function ($q) use ($kaprogJurusan) {
                $q->where('jurusan_id', $kaprogJurusan)
                  ->orWhereNull('jurusan_id');
            });
        }

        if ($request->filled('jurusan_id')) {
            $jurusanFilter = (int) $request->jurusan_id;
            // Kaprog may never browse another department's partners, even shared "all jurusan" rows.
            if ($kaprogJurusan && $jurusanFilter !== $kaprogJurusan) {
                $jurusanFilter = $kaprogJurusan;
            }
            $query->where(function ($q) use ($jurusanFilter) {
                $q->where('jurusan_id', $jurusanFilter)->orWhereNull('jurusan_id');
            });
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('nama_dudi', 'like', "%{$s}%")
                  ->orWhere('kota', 'like', "%{$s}%")
                  ->orWhere('bidang_industri', 'like', "%{$s}%");
            });
        }

        $partners = $query->orderBy('nama_dudi')->paginate(15)->withQueryString();
        $jurusans = Jurusan::orderBy('nama_jurusan')->get();
        $kaprogManagedJurusanId = $kaprogJurusan;

        return view('pkl.partners', compact('partners', 'jurusans', 'kaprogManagedJurusanId'));
    }

    /**
     * Store DUDI Partner
     */
    public function partnerStore(Request $request)
    {
        $request->validate([
            'nama_dudi'       => 'required|string|max:200',
            'bidang_industri' => 'required|string|max:150',
            'alamat'          => 'required|string',
            'kota'            => 'required|string|max:100',
            'kontak_person'   => 'required|string|max:100',
            'email'           => 'nullable|email|max:150',
            'telepon'         => 'nullable|string|max:30',
            'pembimbing_dudi' => 'nullable|string|max:150',
            'latitude'        => 'nullable|numeric|between:-90,90',
            'longitude'       => 'nullable|numeric|between:-180,180',
            'radius_meter'    => 'nullable|integer|min:20|max:5000',
            'kuota_siswa'     => 'nullable|integer|min:1|max:1000',
            'jurusan_id'      => 'nullable|exists:jurusans,id',
            'status'          => 'nullable|in:aktif,nonaktif',
        ]);

        // Kaprog-created partners are always bound to their own department.
        $kaprogJurusan = $this->managedJurusanId(Auth::user());
        if ($kaprogJurusan) {
            if ($request->filled('jurusan_id') && (int) $request->jurusan_id !== $kaprogJurusan) {
                return back()->withInput()->with('error', 'Akses ditolak: Anda hanya dapat mendaftarkan mitra untuk jurusan yang Anda kepalai.');
            }
        }

        $partner = PklDudiPartner::create([
            'nama_dudi'       => $request->nama_dudi,
            'bidang_industri' => $request->bidang_industri,
            'alamat'          => $request->alamat,
            'kota'            => $request->kota,
            'kontak_person'   => $request->kontak_person,
            'email'           => $request->email,
            'telepon'         => $request->telepon,
            'pembimbing_dudi' => $request->pembimbing_dudi,
            'latitude'        => $request->latitude,
            'longitude'       => $request->longitude,
            'radius_meter'    => $request->radius_meter ?? 150,
            'kuota_siswa'     => $request->kuota_siswa,
            'jurusan_id'      => $kaprogJurusan ?? $request->jurusan_id,
            'status'          => $request->status === 'nonaktif' ? 'nonaktif' : 'aktif',
        ]);

        return redirect()->route('pkl.partners.index')
            ->with('success', "Mitra DU/DI {$partner->nama_dudi} berhasil didaftarkan.");
    }

    /**
     * Update DUDI Partner
     */
    public function partnerUpdate(Request $request, $id)
    {
        $partner = PklDudiPartner::findOrFail($id);

        $request->validate([
            'nama_dudi'       => 'required|string|max:200',
            'bidang_industri' => 'required|string|max:150',
            'alamat'          => 'required|string',
            'kota'            => 'required|string|max:100',
            'kontak_person'   => 'required|string|max:100',
            'email'           => 'nullable|email|max:150',
            'telepon'         => 'nullable|string|max:30',
            'pembimbing_dudi' => 'nullable|string|max:150',
            'latitude'        => 'nullable|numeric|between:-90,90',
            'longitude'       => 'nullable|numeric|between:-180,180',
            'radius_meter'    => 'nullable|integer|min:20|max:5000',
            'kuota_siswa'     => 'nullable|integer|min:1|max:1000',
            'jurusan_id'      => 'nullable|exists:jurusans,id',
            'status'          => 'required|in:aktif,nonaktif',
        ]);

        // Kaprog may only touch partners of their own department (or shared rows),
        // and may never move a partner into/out of another department.
        $kaprogJurusan = $this->managedJurusanId(Auth::user());
        if ($kaprogJurusan) {
            if ($partner->jurusan_id && (int) $partner->jurusan_id !== $kaprogJurusan) {
                abort(403, 'Akses ditolak: mitra ini bukan bagian dari jurusan yang Anda kepalai.');
            }
            if ($request->filled('jurusan_id') && (int) $request->jurusan_id !== $kaprogJurusan) {
                return back()->withInput()->with('error', 'Akses ditolak: Anda hanya dapat menempatkan mitra pada jurusan yang Anda kepalai.');
            }
        }

        // Never allow a quota below the students already placed there.
        if ($request->filled('kuota_siswa')) {
            $activeNow = $partner->placements()->where('status', 'active')->count();
            if ((int) $request->kuota_siswa < $activeNow) {
                return back()->withInput()->with('error', "Kuota tidak boleh kurang dari siswa yang sedang PKL di mitra ini ({$activeNow} aktif).");
            }
        }

        $partner->update([
            'nama_dudi'       => $request->nama_dudi,
            'bidang_industri' => $request->bidang_industri,
            'alamat'          => $request->alamat,
            'kota'            => $request->kota,
            'kontak_person'   => $request->kontak_person,
            'email'           => $request->email,
            'telepon'         => $request->telepon,
            'pembimbing_dudi' => $request->pembimbing_dudi,
            'latitude'        => $request->latitude,
            'longitude'       => $request->longitude,
            'radius_meter'    => $request->radius_meter ?? 150,
            'kuota_siswa'     => $request->kuota_siswa,
            'jurusan_id'      => $kaprogJurusan ?? ($request->jurusan_id ?: null),
            'status'          => $request->status,
        ]);

        return redirect()->route('pkl.partners.index')
            ->with('success', "Mitra DU/DI {$partner->nama_dudi} berhasil diperbarui.");
    }

    /**
     * Delete DUDI Partner (blocked while active placements exist)
     */
    public function partnerDestroy($id)
    {
        $partner = PklDudiPartner::findOrFail($id);

        $kaprogJurusan = $this->managedJurusanId(Auth::user());
        if ($kaprogJurusan && $partner->jurusan_id && (int) $partner->jurusan_id !== $kaprogJurusan) {
            abort(403, 'Akses ditolak: mitra ini bukan bagian dari jurusan yang Anda kepalai.');
        }

        $activeCount = $partner->placements()->where('status', 'active')->count();
        $totalCount  = $partner->placements()->count();

        if ($activeCount > 0) {
            return back()->with('error', "Mitra tidak dapat dihapus: masih digunakan oleh {$activeCount} penempatan PKL aktif. Alihkan penempatannya terlebih dahulu atau nonaktifkan mitra.");
        }

        // History-safe guard: placements/logbooks keep an FK to the partner; deleting the
        // row would erase PKL history via cascade. Suggest deactivation instead.
        if ($totalCount > 0) {
            return back()->with('error', "Mitra masih terikat {$totalCount} riwayat penempatan PKL. Gunakan status NONAKTIF agar riwayat siswa tetap tersimpan.");
        }

        $partner->delete();

        return redirect()->route('pkl.partners.index')
            ->with('success', "Mitra DU/DI {$partner->nama_dudi} berhasil dihapus.");
    }

    /**
     * AJAX: kelas-12 rombel options for a department (jurusan).
     * Kaprog is clamped server-side to their own department.
     */
    public function optionRombel(Request $request)
    {
        $kaprogJurusan = $this->managedJurusanId(Auth::user());
        $jurusanId = $kaprogJurusan ?? (int) $request->jurusan_id;
        if (!$jurusanId) {
            return response()->json(['data' => [], 'message' => 'Pilih jurusan terlebih dahulu.']);
        }

        $rombels = Kelas::query()
            ->where('jurusan_id', $jurusanId)
            ->where('tingkat', 12)
            ->where('is_active', true)
            ->orderBy('nama_kelas')
            ->get(['id', 'nama_kelas', 'tingkat', 'jumlah_siswa'])
            ->map(fn ($k) => [
                'id'          => $k->id,
                'nama'        => $k->nama_kelas,
                'jumlah'      => (int) $k->jumlah_siswa,
            ]);

        return response()->json([
            'data'    => $rombels,
            'message' => $rombels->isEmpty() ? 'Tidak ada rombel kelas 12 aktif untuk jurusan ini.' : null,
        ]);
    }

    /**
     * AJAX: PKL-eligible siswa of a rombel.
     * Eligible = status aktif + enrolled in that kelas-12 rombel + no active placement.
     * Supports search ($request->q) and server-side pagination ($request->page).
     */
    public function optionSiswa(Request $request)
    {
        $kaprogJurusan = $this->managedJurusanId(Auth::user());
        $kelasId = (int) $request->kelas_id;

        $kelas = Kelas::find($kelasId);
        if (!$kelas || (int) $kelas->tingkat !== 12) {
            return response()->json(['data' => [], 'message' => 'Rombel tidak valid atau bukan kelas 12.']);
        }
        if ($kaprogJurusan && (int) $kelas->jurusan_id !== $kaprogJurusan) {
            abort(403, 'Akses ditolak: rombel ini bukan dari jurusan yang Anda kepalai.');
        }

        // Resolve selected IDs if requested (e.g. for preloading selected chips)
        if ($request->has('selected_ids')) {
            $selectedIds = is_array($request->selected_ids)
                ? $request->selected_ids
                : array_filter(explode(',', (string) $request->selected_ids));

            if (!empty($selectedIds)) {
                $selected = Siswa::whereIn('id', $selectedIds)
                    ->get(['id', 'nama', 'nis', 'nisn'])
                    ->map(fn ($s) => [
                        'id'      => $s->id,
                        'nama'    => $s->nama,
                        'nis'     => $s->nis,
                        'nisn'    => $s->nisn,
                        'label'   => $s->nama . ' (NIS: ' . ($s->nis ?: '-') . ')',
                        'subtext' => 'NIS: ' . ($s->nis ?: '-') . ($s->nisn ? ' • NISN: ' . $s->nisn : ''),
                    ]);

                return response()->json([
                    'data' => $selected,
                    'pagination' => [
                        'current_page' => 1,
                        'per_page'     => count($selected),
                        'total'        => count($selected),
                        'last_page'    => 1,
                        'has_more'     => false,
                    ],
                ]);
            }
        }

        $placedIds = PklPlacement::where('status', 'active')->pluck('siswa_id');

        $query = Siswa::query()
            ->where('status', 'aktif')
            ->where(function ($q) use ($kelasId) {
                $q->where('kelas_id', $kelasId)
                  ->orWhereHas('activeEnrollment', fn ($e) => $e->where('kelas_id', $kelasId));
            })
            ->whereNotIn('id', $placedIds);

        $q = trim((string) $request->input('q'));
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('nama', 'like', "%{$q}%")
                    ->orWhere('nis', 'like', "%{$q}%")
                    ->orWhere('nisn', 'like', "%{$q}%");
            });
        }

        $query->orderBy('nama');

        // Check if server-side pagination requested (page or per_page)
        if ($request->has('page') || $request->has('per_page')) {
            $perPage = max(1, min(100, (int) $request->input('per_page', 15)));
            $paginator = $query->paginate($perPage);

            $data = $paginator->getCollection()->map(fn ($s) => [
                'id'      => $s->id,
                'nama'    => $s->nama,
                'nis'     => $s->nis,
                'nisn'    => $s->nisn,
                'label'   => $s->nama . ' (NIS: ' . ($s->nis ?: '-') . ')',
                'subtext' => 'NIS: ' . ($s->nis ?: '-') . ($s->nisn ? ' • NISN: ' . $s->nisn : ''),
            ]);

            return response()->json([
                'data'       => $data,
                'pagination' => [
                    'current_page' => $paginator->currentPage(),
                    'per_page'     => $paginator->perPage(),
                    'total'        => $paginator->total(),
                    'last_page'    => $paginator->lastPage(),
                    'has_more'     => $paginator->hasMorePages(),
                ],
                'message'    => $data->isEmpty() ? 'Semua siswa di rombel ini sudah memiliki penempatan aktif atau tidak ada siswa yang cocok.' : null,
            ]);
        }

        // Backward-compatible unpaginated response
        $siswas = $query->get(['id', 'nama', 'nis', 'nisn'])
            ->map(fn ($s) => [
                'id'      => $s->id,
                'nama'    => $s->nama,
                'nis'     => $s->nis,
                'nisn'    => $s->nisn,
                'label'   => $s->nama . ' (NIS: ' . ($s->nis ?: '-') . ')',
                'subtext' => 'NIS: ' . ($s->nis ?: '-') . ($s->nisn ? ' • NISN: ' . $s->nisn : ''),
            ]);

        return response()->json([
            'data'       => $siswas,
            'pagination' => [
                'current_page' => 1,
                'per_page'     => $siswas->count(),
                'total'        => $siswas->count(),
                'last_page'    => 1,
                'has_more'     => false,
            ],
            'message'    => $siswas->isEmpty() ? 'Semua siswa di rombel ini sudah memiliki penempatan aktif atau data belum tersedia.' : null,
        ]);
    }

    /**
     * AJAX: partners eligible for a department (active, matching jurusan, quota available).
     */
    public function optionMitra(Request $request)
    {
        $kaprogJurusan = $this->managedJurusanId(Auth::user());
        if ($request->filled('jurusan_id') && !$kaprogJurusan) {
            $jurusanId = (int) $request->jurusan_id;
        } else {
            $jurusanId = $kaprogJurusan;
        }

        $query = PklDudiPartner::query()->where('status', 'aktif')
            ->where(function ($q) use ($jurusanId, $kaprogJurusan) {
                if ($jurusanId) {
                    $q->whereNull('jurusan_id')->orWhere('jurusan_id', $jurusanId);
                }
            });

        $partners = $query->orderBy('nama_dudi')->get();

        $activeCounts = PklPlacement::where('status', 'active')
            ->whereIn('pkl_dudi_partner_id', $partners->pluck('id'))
            ->selectRaw('pkl_dudi_partner_id, count(*) c')
            ->groupBy('pkl_dudi_partner_id')
            ->pluck('c', 'pkl_dudi_partner_id');

        $data = $partners->map(function ($p) use ($activeCounts) {
            $used = (int) ($activeCounts[$p->id] ?? 0);
            $kuota = $p->kuota_siswa ? (int) $p->kuota_siswa : null;
            return [
                'id'         => $p->id,
                'nama'       => $p->nama_dudi,
                'kota'       => $p->kota,
                'kuota'      => $kuota,
                'used'       => $used,
                'sisa'       => $kuota === null ? null : max(0, $kuota - $used),
                'penuh'      => $kuota !== null && $used >= $kuota,
            ];
        })->reject(fn ($p) => $p['penuh'])->values();

        return response()->json([
            'data'    => $data,
            'message' => $data->isEmpty() ? 'Tidak ada mitra aktif dengan kuota tersisa untuk jurusan ini.' : null,
        ]);
    }

    /**
     * Placements List (Admin / Kaprog / Pembimbing)
     */
    public function placementsIndex(Request $request)
    {
        $user = Auth::user();
        $query = PklPlacement::with(['siswa.kelas', 'partner', 'guruPembimbing']);

        $kaprogJurusan = $this->managedJurusanId($user);
        if ($kaprogJurusan) {
            $query->whereHas('siswa.kelas', function ($q) use ($kaprogJurusan) {
                $q->where('jurusan_id', $kaprogJurusan);
            });
        } elseif ($user->role === 'guru') {
            $query->where('guru_pembimbing_id', $user->id);
        }

        if ($request->filled('partner_id')) {
            $query->where('pkl_dudi_partner_id', $request->partner_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $placements = $query->latest('id')->paginate(20)->withQueryString();
        $partners = PklDudiPartner::where('status', 'aktif')->orderBy('nama_dudi')->get();
        $gurus = User::whereIn('role', ['guru', 'kaprog'])->orderBy('name')->get();
        $jurusans = Jurusan::orderBy('nama_jurusan')->get();
        $kaprogManagedJurusanId = $this->managedJurusanId($user);
        if ($kaprogManagedJurusanId) {
            $jurusans = $jurusans->where('id', $kaprogManagedJurusanId)->values();
        }

        return view('pkl.placements', compact('placements', 'partners', 'gurus', 'jurusans', 'kaprogManagedJurusanId'));
    }

    /**
     * Resolve & validate the Jurusan -> Rombel -> Siswa chain server-side.
     * Returns ['siswa' => Siswa, 'kelas' => Kelas] or throws InvalidArgumentException
     * with an end-user message. Kaprog scope is enforced here, never trusted from input.
     */
    protected function validatePlacementChain(int $siswaId, ?int $jurusanId, ?int $kelasId): array
    {
        $kaprogJurusan = $this->managedJurusanId(Auth::user());

        $siswa = Siswa::with('kelas')->find($siswaId);
        if (!$siswa) {
            throw new \InvalidArgumentException('Siswa tidak ditemukan.');
        }
        if ($siswa->status !== 'aktif') {
            throw new \InvalidArgumentException('Siswa tidak memenuhi syarat PKL (status tidak aktif).');
        }

        // Authoritative rombel: active enrollment first, siswa.kelas_id fallback.
        $enrollment = $siswa->activeEnrollment ?: $siswa->latestEnrollment;
        $kelasIdEffective = $enrollment?->kelas_id ?: $siswa->kelas_id;
        $kelas = $kelasIdEffective ? Kelas::find($kelasIdEffective) : $siswa->kelas;

        if (!$kelas) {
            throw new \InvalidArgumentException('Siswa belum memiliki rombel aktif, tidak dapat ditempatkan PKL.');
        }
        if ((int) $kelas->tingkat !== 12) {
            throw new \InvalidArgumentException('PKL hanya untuk siswa kelas 12. Rombel siswa ini tingkat ' . $kelas->tingkat . '.');
        }
        if ($kelasId && (int) $kelasId !== (int) $kelas->id) {
            throw new \InvalidArgumentException('Siswa tidak sesuai rombel yang dipilih.');
        }
        if ($jurusanId && (int) $jurusanId !== (int) $kelas->jurusan_id) {
            throw new \InvalidArgumentException('Jurusan tidak sesuai dengan rombel siswa.');
        }
        if ($kaprogJurusan && (int) $kelas->jurusan_id !== $kaprogJurusan) {
            throw new \RuntimeException('ACCESS_DENIED');
        }

        return ['siswa' => $siswa, 'kelas' => $kelas];
    }

    /**
     * Enforce partner eligibility: active, jurusan-compatible, quota free.
     */
    protected function validatePartnerForPlacement(PklDudiPartner $partner, $kelas, int $excludePlacementId = 0): ?string
    {
        if ($partner->status !== 'aktif') {
            return 'Mitra industri tidak aktif. Pilih mitra lain.';
        }
        if ($partner->jurusan_id && (int) $partner->jurusan_id !== (int) $kelas->jurusan_id) {
            return 'Mitra tidak sesuai dengan jurusan siswa.';
        }
        if ($partner->kuota_siswa) {
            $used = PklPlacement::where('pkl_dudi_partner_id', $partner->id)
                ->where('status', 'active')
                ->where('id', '!=', $excludePlacementId)
                ->count();
            if ($used >= (int) $partner->kuota_siswa) {
                return 'Kapasitas mitra penuh (' . $partner->kuota_siswa . ' siswa). Penempatan baru akan melampaui kuota.';
            }
        }
        return null;
    }

    /**
     * Store Placement (Supports Single & Multi-Select Bulk Plotting)
     */
    public function placementStore(Request $request)
    {
        $request->validate([
            'jurusan_id'          => 'nullable|exists:jurusans,id',
            'kelas_id'            => 'nullable|exists:kelas,id',
            'siswa_id'            => 'nullable|exists:siswas,id',
            'siswa_ids'           => 'nullable|array|min:1',
            'siswa_ids.*'         => 'required|exists:siswas,id',
            'pkl_dudi_partner_id' => 'required|exists:pkl_dudi_partners,id',
            'guru_pembimbing_id'  => 'nullable|exists:users,id',
            'tanggal_mulai'       => 'required|date',
            'tanggal_selesai'     => 'required|date|after_or_equal:tanggal_mulai',
            'catatan'             => 'nullable|string',
        ]);

        $siswaIds = $request->input('siswa_ids');
        if (empty($siswaIds)) {
            if ($request->filled('siswa_id')) {
                $siswaIds = [(int) $request->siswa_id];
            } else {
                return back()->withInput()->with('error', 'Pilih minimal satu siswa untuk penempatan PKL.');
            }
        }

        // Deduplicate and sanitize
        $siswaIds = array_values(array_unique(array_map('intval', $siswaIds)));
        if (empty($siswaIds)) {
            return back()->withInput()->with('error', 'Pilih minimal satu siswa untuk penempatan PKL.');
        }

        $partner = PklDudiPartner::findOrFail($request->pkl_dudi_partner_id);
        if ($partner->status !== 'aktif') {
            return back()->withInput()->with('error', 'Mitra industri tidak aktif. Pilih mitra lain.');
        }

        // Batch partner quota validation
        if ($partner->kuota_siswa) {
            $used = PklPlacement::where('pkl_dudi_partner_id', $partner->id)
                ->where('status', 'active')
                ->count();
            $needed = count($siswaIds);
            if ($used + $needed > (int) $partner->kuota_siswa) {
                $sisa = max(0, (int) $partner->kuota_siswa - $used);
                return back()->withInput()->with(
                    'error',
                    "Kapasitas mitra penuh. Sisa kuota untuk mitra {$partner->nama_dudi} adalah {$sisa} siswa, sementara Anda memilih {$needed} siswa."
                );
            }
        }

        // Process placement within a database transaction
        $createdPlacements = [];
        try {
            DB::beginTransaction();

            foreach ($siswaIds as $sid) {
                $chain = $this->validatePlacementChain(
                    $sid,
                    $request->jurusan_id ? (int) $request->jurusan_id : null,
                    $request->kelas_id ? (int) $request->kelas_id : null
                );

                $siswa = $chain['siswa'];
                $kelas = $chain['kelas'];

                // Partner department compatibility check
                if ($partner->jurusan_id && (int) $partner->jurusan_id !== (int) $kelas->jurusan_id) {
                    throw new \InvalidArgumentException("Mitra tidak sesuai dengan jurusan siswa {$siswa->nama}.");
                }

                // Active placement conflict check
                $activeExists = PklPlacement::where('siswa_id', $siswa->id)->where('status', 'active')->exists();
                if ($activeExists) {
                    throw new \InvalidArgumentException("Siswa {$siswa->nama} sudah memiliki penempatan PKL aktif.");
                }

                $createdPlacements[] = PklPlacement::create([
                    'siswa_id'            => $siswa->id,
                    'pkl_dudi_partner_id' => $partner->id,
                    'guru_pembimbing_id'  => $request->guru_pembimbing_id,
                    'tanggal_mulai'       => $request->tanggal_mulai,
                    'tanggal_selesai'     => $request->tanggal_selesai,
                    'status'              => 'active',
                    'catatan'             => $request->catatan,
                ]);
            }

            DB::commit();
        } catch (\InvalidArgumentException $e) {
            DB::rollBack();
            return back()->withInput()->with('error', $e->getMessage());
        } catch (\RuntimeException $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Akses ditolak: Anda hanya dapat menempatkan siswa dari jurusan yang Anda kepalai.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Terjadi kesalahan sistem saat menyimpan penempatan: ' . $e->getMessage());
        }

        $count = count($createdPlacements);
        $msg = $count === 1
            ? "Penempatan PKL untuk {$createdPlacements[0]->siswa->nama} di {$partner->nama_dudi} berhasil disimpan."
            : "Penempatan PKL untuk {$count} siswa di {$partner->nama_dudi} berhasil disimpan.";

        return redirect()->route('pkl.placements.index')->with('success', $msg);
    }

    /**
     * Update Placement
     */
    public function placementUpdate(Request $request, $id)
    {
        $placement = PklPlacement::with('siswa')->findOrFail($id);

        // Kaprog may only edit placements inside their department.
        $kaprogJurusan = $this->managedJurusanId(Auth::user());
        if ($kaprogJurusan) {
            $jurusanSiswa = $placement->siswa?->kelas?->jurusan_id
                ?: $placement->siswa?->activeEnrollment?->kelas?->jurusan_id;
            if ((int) $jurusanSiswa !== $kaprogJurusan) {
                abort(403, 'Akses ditolak: penempatan ini bukan dari jurusan yang Anda kepalai.');
            }
        }

        $request->validate([
            'pkl_dudi_partner_id' => 'required|exists:pkl_dudi_partners,id',
            'guru_pembimbing_id'  => 'nullable|exists:users,id',
            'tanggal_mulai'       => 'required|date',
            'tanggal_selesai'     => 'required|date|after_or_equal:tanggal_mulai',
            'catatan'             => 'nullable|string',
            'status'              => 'required|in:active,completed,withdrawn',
        ]);

        $siswa = Siswa::with('kelas')->find($placement->siswa_id);
        if (!$siswa) {
            return back()->with('error', 'Data siswa pada penempatan ini tidak ditemukan.');
        }

        $partner = PklDudiPartner::findOrFail($request->pkl_dudi_partner_id);
        $kelas = $siswa->kelas ?: $siswa->activeEnrollment?->kelas ?: $siswa->rombel_terakhir;
        if (!$kelas) {
            return back()->with('error', 'Siswa tidak memiliki rombel untuk validasi mitra.');
        }
        $quotaError = $this->validatePartnerForPlacement($partner, $kelas, (int) $placement->id);
        if ($quotaError) {
            return back()->withInput()->with('error', $quotaError);
        }

        if ($request->status === 'active') {
            $otherActive = PklPlacement::where('siswa_id', $siswa->id)
                ->where('status', 'active')
                ->where('id', '!=', $placement->id)
                ->exists();
            if ($otherActive) {
                return back()->withInput()->with('error', 'Siswa sudah memiliki penempatan aktif lain.');
            }
        }

        $placement->update([
            'pkl_dudi_partner_id' => $partner->id,
            'guru_pembimbing_id'  => $request->guru_pembimbing_id,
            'tanggal_mulai'       => $request->tanggal_mulai,
            'tanggal_selesai'     => $request->tanggal_selesai,
            'status'              => $request->status,
            'catatan'             => $request->catatan,
        ]);

        return redirect()->route('pkl.placements.index')
            ->with('success', "Penempatan PKL {$siswa->nama} berhasil diperbarui.");
    }

    /**
     * Destroy / cancel Placement. History (logbook/attendance) is preserved:
     * hard delete only allowed when no PKL activity exists yet, otherwise it
     * becomes a 'withdrawn' cancellation.
     */
    public function placementDestroy($id)
    {
        $placement = PklPlacement::with('siswa')->findOrFail($id);

        $kaprogJurusan = $this->managedJurusanId(Auth::user());
        if ($kaprogJurusan) {
            $siswa = $placement->siswa;
            $jurusanSiswa = $siswa?->kelas?->jurusan_id ?: $siswa?->activeEnrollment?->kelas?->jurusan_id;
            if ((int) $jurusanSiswa !== $kaprogJurusan) {
                abort(403, 'Akses ditolak: penempatan ini bukan dari jurusan yang Anda kepalai.');
            }
        }

        $hasHistory = $placement->logbooks()->exists() || $placement->attendances()->exists();

        if ($hasHistory) {
            $placement->update(['status' => 'withdrawn']);
            return redirect()->route('pkl.placements.index')->with('success',
                "Penempatan PKL {$placement->siswa?->nama} dibatalkan (status WITHDRAWN). Riwayat logbook & presensi tetap tersimpan.");
        }

        $placement->delete();

        return redirect()->route('pkl.placements.index')
            ->with('success', 'Penempatan PKL tanpa riwayat berhasil dihapus permanen.');
    }

    /**
     * Student PKL Portal
     */
    public function studentPortal()
    {
        $user = Auth::user();
        $siswa = Siswa::where('user_id', $user->id)->firstOrFail();

        $activePlacement = PklPlacement::with(['partner', 'guruPembimbing'])
            ->where('siswa_id', $siswa->id)
            ->where('status', 'active')
            ->first();

        $todayAttendance = null;
        $recentLogbooks = collect();

        if ($activePlacement) {
            $todayAttendance = PklAttendance::where('pkl_placement_id', $activePlacement->id)
                ->where('tanggal', now()->toDateString())
                ->first();

            $recentLogbooks = PklLogbook::where('pkl_placement_id', $activePlacement->id)
                ->latest('tanggal')
                ->limit(10)
                ->get();
        }

        return view('pkl.student_portal', compact('siswa', 'activePlacement', 'todayAttendance', 'recentLogbooks'));
    }

    /**
     * Student PKL Check-In (Geofenced)
     */
    public function studentCheckIn(Request $request)
    {
        $user = Auth::user();
        $siswa = Siswa::where('user_id', $user->id)->firstOrFail();

        $placement = PklPlacement::with('partner')
            ->where('siswa_id', $siswa->id)
            ->where('status', 'active')
            ->first();

        if (!$placement) {
            return back()->with('error', 'Anda tidak memiliki penempatan PKL aktif saat ini.');
        }

        $request->validate([
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'foto'      => 'nullable|image|max:3072',
            'catatan'   => 'nullable|string|max:255',
        ]);

        $today = now()->toDateString();
        $attendance = PklAttendance::where('pkl_placement_id', $placement->id)
            ->where('tanggal', $today)
            ->first();

        $partner = $placement->partner;
        $distance = $partner->getDistanceFrom($request->latitude, $request->longitude);
        $isValidLocation = $partner->isWithinRadius($request->latitude, $request->longitude);

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('pkl/selfie', 'public');
        }

        if (!$attendance) {
            // Check-in masuk
            $jamMasuk = now()->format('H:i:s');
            $status = now()->format('H:i:s') > '08:00:00' ? 'terlambat' : 'hadir';

            PklAttendance::create([
                'pkl_placement_id'  => $placement->id,
                'tanggal'           => $today,
                'jam_masuk'         => $jamMasuk,
                'latitude'          => $request->latitude,
                'longitude'         => $request->longitude,
                'distance_meters'   => $distance,
                'is_location_valid' => $isValidLocation,
                'status'            => $status,
                'foto_selfie_path'  => $fotoPath,
                'catatan'           => $request->catatan,
            ]);

            $message = $isValidLocation
                ? "Presensi masuk PKL berhasil dicatat pada {$jamMasuk} (Jarak: {$distance}m)."
                : "Presensi masuk PKL dicatat di luar radius industri ({$distance}m). Ditandai untuk evaluasi pembimbing.";

            return back()->with($isValidLocation ? 'success' : 'warning', $message);
        } else {
            // Check-out pulang
            $jamPulang = now()->format('H:i:s');
            $attendance->update([
                'jam_pulang' => $jamPulang,
            ]);

            return back()->with('success', "Presensi pulang PKL berhasil dicatat pada {$jamPulang}.");
        }
    }

    /**
     * Submit Student Logbook
     */
    public function studentLogbookStore(Request $request, InAppNotificationService $notifService)
    {
        $user = Auth::user();
        $siswa = Siswa::where('user_id', $user->id)->firstOrFail();

        $placement = PklPlacement::with(['guruPembimbing'])
            ->where('siswa_id', $siswa->id)
            ->where('status', 'active')
            ->first();

        if (!$placement) {
            return back()->with('error', 'Penempatan PKL aktif tidak ditemukan.');
        }

        $request->validate([
            'tanggal'               => 'required|date|before_or_equal:today',
            'aktivitas'             => 'required|string|min:10',
            'kompetensi_dipelajari' => 'nullable|string',
            'foto_dokumentasi'      => 'nullable|image|max:4096',
        ]);

        $fotoPath = null;
        if ($request->hasFile('foto_dokumentasi')) {
            $fotoPath = $request->file('foto_dokumentasi')->store('pkl/logbooks', 'public');
        }

        $logbook = PklLogbook::updateOrCreate(
            [
                'pkl_placement_id' => $placement->id,
                'tanggal'          => $request->tanggal,
            ],
            [
                'aktivitas'             => $request->aktivitas,
                'kompetensi_dipelajari' => $request->kompetensi_dipelajari,
                'foto_dokumentasi_path' => $fotoPath ?? DB::raw('foto_dokumentasi_path'),
                'status'                => 'submitted',
                'catatan_guru'          => null,
            ]
        );

        if ($placement->guruPembimbing) {
            $notifService->sendToUser(
                $placement->guruPembimbing,
                "Jurnal Harian PKL Diserahkan",
                "Siswa {$siswa->nama} telah menyerahkan logbook PKL untuk tanggal {$request->tanggal}.",
                'academic'
            );
        }

        return back()->with('success', "Jurnal kegiatan harian PKL berhasil disimpan dan dikirim ke guru pembimbing.");
    }

    /**
     * Advisor Logbook Verification List
     */
    public function advisorLogbookIndex(Request $request)
    {
        $user = Auth::user();
        $query = PklLogbook::with(['placement.siswa.kelas', 'placement.partner', 'verifier']);

        if ($user->role === 'guru') {
            $query->whereHas('placement', function ($q) use ($user) {
                $q->where('guru_pembimbing_id', $user->id);
            });
        } else {
            $kaprogJurusan = $this->managedJurusanId($user);
            if ($kaprogJurusan) {
                $query->whereHas('placement.siswa.kelas', function ($q) use ($kaprogJurusan) {
                    $q->where('jurusan_id', $kaprogJurusan);
                });
            }
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $logbooks = $query->latest('tanggal')->latest('id')->paginate(20)->withQueryString();

        return view('pkl.advisor_logbooks', compact('logbooks'));
    }

    /**
     * Review Logbook (Approve or Request Revision)
     */
    public function advisorLogbookReview(Request $request, $id, InAppNotificationService $notifService)
    {
        $request->validate([
            'status'       => 'required|in:approved,revision_requested',
            'catatan_guru' => 'nullable|string|max:500',
        ]);

        $logbook = PklLogbook::with(['placement.siswa.kelas', 'placement.siswa.user'])->findOrFail($id);

        // Security Authorization Check:
        // Guru may only review logbooks for students they are assigned to advise.
        // Kaprog may only review logbooks for students in their department.
        $user = Auth::user();
        if ($user->role === 'guru') {
            abort_unless(
                $logbook->placement && (int) $logbook->placement->guru_pembimbing_id === (int) $user->id,
                403,
                'Akses ditolak: Anda bukan guru pembimbing yang ditugaskan untuk siswa ini.'
            );
        } else {
            $kaprogJurusan = $this->managedJurusanId($user);
            if ($kaprogJurusan) {
                abort_unless(
                    $logbook->placement?->siswa?->kelas && (int) $logbook->placement->siswa->kelas->jurusan_id === $kaprogJurusan,
                    403,
                    'Akses ditolak: Siswa ini bukan dari jurusan yang Anda kepalai.'
                );
            }
        }

        $logbook->update([
            'status'       => $request->status,
            'catatan_guru' => $request->catatan_guru,
            'verified_by'  => Auth::id(),
            'verified_at'  => now(),
        ]);

        $statusText = $request->status === 'approved' ? 'disetujui' : 'memerlukan perbaikan';
        $studentUser = $logbook->placement->siswa->user ?? null;

        if ($studentUser) {
            $notifService->sendToUser(
                $studentUser,
                "Status Jurnal PKL Diperbarui",
                "Logbook PKL tanggal {$logbook->tanggal->format('d/m/Y')} {$statusText} oleh pembimbing. " . ($request->catatan_guru ? "Catatan: {$request->catatan_guru}" : ""),
                'academic'
            );
        }

        return back()->with('success', "Verifikasi logbook PKL berhasil disimpan.");
    }

    /**
     * Overall PKL Monitoring Dashboard (Admin / Kaprog)
     */
    public function monitoringDashboard()
    {
        $stats = [
            'total_partners'    => PklDudiPartner::where('status', 'aktif')->count(),
            'total_active_pk'   => PklPlacement::where('status', 'active')->count(),
            'today_attendances' => PklAttendance::where('tanggal', now()->toDateString())->count(),
            'pending_logbooks'  => PklLogbook::where('status', 'submitted')->count(),
        ];

        $todayAttendances = PklAttendance::with(['placement.siswa.kelas', 'placement.partner'])
            ->where('tanggal', now()->toDateString())
            ->latest('id')
            ->limit(30)
            ->get();

        $activePlacements = PklPlacement::with(['siswa.kelas', 'partner', 'guruPembimbing'])
            ->where('status', 'active')
            ->latest('id')
            ->limit(15)
            ->get();

        return view('pkl.monitoring', compact('stats', 'todayAttendances', 'activePlacements'));
    }
}
