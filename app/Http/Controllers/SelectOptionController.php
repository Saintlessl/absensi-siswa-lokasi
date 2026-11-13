<?php

namespace App\Http\Controllers;

use App\Models\DisciplineCategory;
use App\Models\Guru;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\PklDudiPartner;
use App\Models\PklPlacement;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SelectOptionController extends Controller
{
    /**
     * Resolve Kaprog assigned jurusan ID if user is a Kaprog
     */
    protected function getKaprogJurusanId(): ?int
    {
        $user = Auth::user();
        if (!$user) return null;

        if ($user->role === 'kaprog' && $user->jurusan_id) {
            return (int) $user->jurusan_id;
        }

        if ($user->guru) {
            $assignment = $user->guru->activeKaprogAssignment;
            if ($assignment && $assignment->jurusan_id) {
                return (int) $assignment->jurusan_id;
            }
        }

        return null;
    }

    /**
     * Search Siswa with Server-Side Pagination
     */
    public function siswa(Request $request): JsonResponse
    {
        $q = trim((string) $request->input('q'));
        $perPage = max(1, min(100, (int) $request->input('per_page', 15)));
        $kelasId = $request->input('kelas_id') ? (int) $request->input('kelas_id') : null;
        $jurusanId = $request->input('jurusan_id') ? (int) $request->input('jurusan_id') : null;
        $tingkat = $request->input('tingkat') ? (int) $request->input('tingkat') : null;
        $excludePlacedPkl = filter_var($request->input('exclude_active_pkl', false), FILTER_VALIDATE_BOOLEAN);

        // Authorization scope for Kaprog
        $kaprogJurusanId = $this->getKaprogJurusanId();
        if ($kaprogJurusanId) {
            $jurusanId = $kaprogJurusanId;
        }

        $query = Siswa::with(['kelas:id,nama_kelas,tingkat,jurusan_id', 'kelas.jurusan:id,nama_jurusan,kode_jurusan'])
            ->where('status', 'aktif');

        // Resolve pre-selected IDs if requested
        if ($request->has('selected_ids')) {
            $selectedIds = is_array($request->selected_ids)
                ? $request->selected_ids
                : array_filter(explode(',', (string) $request->selected_ids));

            if (!empty($selectedIds)) {
                $selectedSiswas = Siswa::with(['kelas:id,nama_kelas,tingkat,jurusan_id', 'kelas.jurusan:id,nama_jurusan,kode_jurusan'])
                    ->whereIn('id', $selectedIds)
                    ->get()
                    ->map(fn ($s) => [
                        'id'        => $s->id,
                        'label'     => $s->nama,
                        'subtext'   => 'NIS: ' . ($s->nis ?: '-') . ' • NISN: ' . ($s->nisn ?: '-') . ' • ' . ($s->kelas->nama_kelas ?? '-') . ($s->kelas?->jurusan ? ' (' . $s->kelas->jurusan->kode_jurusan . ')' : ''),
                        'nama'      => $s->nama,
                        'nis'       => $s->nis,
                        'nisn'      => $s->nisn,
                        'kelas'     => $s->kelas->nama_kelas ?? '-',
                        'jurusan'   => $s->kelas?->jurusan?->kode_jurusan ?? ($s->kelas?->jurusan?->nama_jurusan ?? '-'),
                        'photo_url' => $s->photo_url,
                        'initial'   => strtoupper(substr($s->nama, 0, 1)),
                    ]);

                return response()->json([
                    'data' => $selectedSiswas,
                    'pagination' => [
                        'current_page' => 1,
                        'per_page'     => count($selectedSiswas),
                        'total'        => count($selectedSiswas),
                        'last_page'    => 1,
                        'has_more'     => false,
                    ],
                ]);
            }
        }

        // Apply filters
        if ($kelasId) {
            $query->where(function ($sub) use ($kelasId) {
                $sub->where('kelas_id', $kelasId)
                    ->orWhereHas('activeEnrollment', fn ($e) => $e->where('kelas_id', $kelasId));
            });
        }

        if ($jurusanId) {
            $query->whereHas('kelas', fn ($k) => $k->where('jurusan_id', $jurusanId));
        }

        if ($tingkat) {
            $query->whereHas('kelas', fn ($k) => $k->where('tingkat', $tingkat));
        }

        if ($excludePlacedPkl) {
            $placedIds = PklPlacement::where('status', 'active')->pluck('siswa_id');
            $query->whereNotIn('id', $placedIds);
        }

        // Apply search keyword
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('nama', 'like', "%{$q}%")
                    ->orWhere('nis', 'like', "%{$q}%")
                    ->orWhere('nisn', 'like', "%{$q}%");
            });
        }

        $paginator = $query->orderBy('nama')->paginate($perPage);

        $data = $paginator->getCollection()->map(fn ($s) => [
            'id'        => $s->id,
            'label'     => $s->nama,
            'subtext'   => 'NIS: ' . ($s->nis ?: '-') . ' • NISN: ' . ($s->nisn ?: '-') . ' • ' . ($s->kelas->nama_kelas ?? '-') . ($s->kelas?->jurusan ? ' (' . $s->kelas->jurusan->kode_jurusan . ')' : ''),
            'nama'      => $s->nama,
            'nis'       => $s->nis,
            'nisn'      => $s->nisn,
            'kelas'     => $s->kelas->nama_kelas ?? '-',
            'jurusan'   => $s->kelas?->jurusan?->kode_jurusan ?? ($s->kelas?->jurusan?->nama_jurusan ?? '-'),
            'photo_url' => $s->photo_url,
            'initial'   => strtoupper(substr($s->nama, 0, 1)),
        ]);

        return response()->json([
            'data' => $data,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'last_page'    => $paginator->lastPage(),
                'has_more'     => $paginator->hasMorePages(),
            ],
            'message' => $data->isEmpty() ? 'Tidak ada data siswa yang sesuai.' : null,
        ]);
    }

    /**
     * Search Guru with Server-Side Pagination
     */
    public function guru(Request $request): JsonResponse
    {
        $q = trim((string) $request->input('q'));
        $perPage = max(1, min(100, (int) $request->input('per_page', 15)));

        // Resolve pre-selected IDs
        if ($request->has('selected_ids')) {
            $selectedIds = is_array($request->selected_ids)
                ? $request->selected_ids
                : array_filter(explode(',', (string) $request->selected_ids));

            if (!empty($selectedIds)) {
                $selectedUsers = User::whereIn('id', $selectedIds)
                    ->with('guru')
                    ->get()
                    ->map(fn ($u) => [
                        'id'      => $u->id,
                        'label'   => $u->name,
                        'subtext' => ($u->guru?->nip ? 'NIP: ' . $u->guru->nip . ' • ' : '') . ucfirst($u->role),
                    ]);

                return response()->json([
                    'data' => $selectedUsers,
                    'pagination' => [
                        'current_page' => 1,
                        'per_page'     => count($selectedUsers),
                        'total'        => count($selectedUsers),
                        'last_page'    => 1,
                        'has_more'     => false,
                    ],
                ]);
            }
        }

        $query = User::whereIn('role', ['guru', 'kaprog'])
            ->where('is_active', 1)
            ->with('guru');

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhereHas('guru', fn ($g) => $g->where('nip', 'like', "%{$q}%"));
            });
        }

        $paginator = $query->orderBy('name')->paginate($perPage);

        $data = $paginator->getCollection()->map(fn ($u) => [
            'id'      => $u->id,
            'label'   => $u->name,
            'subtext' => ($u->guru?->nip ? 'NIP: ' . $u->guru->nip . ' • ' : '') . ucfirst($u->role),
        ]);

        return response()->json([
            'data' => $data,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'last_page'    => $paginator->lastPage(),
                'has_more'     => $paginator->hasMorePages(),
            ],
            'message' => $data->isEmpty() ? 'Tidak ada data guru yang sesuai.' : null,
        ]);
    }

    /**
     * Search Rombel / Kelas with Server-Side Pagination
     */
    public function rombel(Request $request): JsonResponse
    {
        $q = trim((string) $request->input('q'));
        $perPage = max(1, min(100, (int) $request->input('per_page', 15)));
        $jurusanId = $request->input('jurusan_id') ? (int) $request->input('jurusan_id') : null;
        $tingkat = $request->input('tingkat') ? (int) $request->input('tingkat') : null;

        $kaprogJurusanId = $this->getKaprogJurusanId();
        if ($kaprogJurusanId) {
            $jurusanId = $kaprogJurusanId;
        }

        $query = Kelas::with('jurusan:id,nama_jurusan,kode_jurusan')
            ->where('is_active', true);

        if ($jurusanId) {
            $query->where('jurusan_id', $jurusanId);
        }

        if ($tingkat) {
            $query->where('tingkat', $tingkat);
        }

        if ($q !== '') {
            $query->where('nama_kelas', 'like', "%{$q}%");
        }

        $paginator = $query->orderBy('nama_kelas')->paginate($perPage);

        $data = $paginator->getCollection()->map(fn ($k) => [
            'id'      => $k->id,
            'label'   => $k->nama_kelas,
            'subtext' => ($k->jurusan->nama_jurusan ?? '') . ' • Tingkat ' . $k->tingkat . ' (' . (int) $k->jumlah_siswa . ' Siswa)',
        ]);

        return response()->json([
            'data' => $data,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'last_page'    => $paginator->lastPage(),
                'has_more'     => $paginator->hasMorePages(),
            ],
            'message' => $data->isEmpty() ? 'Tidak ada data rombel yang sesuai.' : null,
        ]);
    }

    /**
     * Search DU/DI Partners with Server-Side Pagination
     */
    public function mitraPkl(Request $request): JsonResponse
    {
        $q = trim((string) $request->input('q'));
        $perPage = max(1, min(100, (int) $request->input('per_page', 15)));
        $jurusanId = $request->input('jurusan_id') ? (int) $request->input('jurusan_id') : null;

        $kaprogJurusanId = $this->getKaprogJurusanId();
        if ($kaprogJurusanId) {
            $jurusanId = $kaprogJurusanId;
        }

        $query = PklDudiPartner::where('status', 'aktif');

        if ($jurusanId) {
            $query->where(function ($sub) use ($jurusanId) {
                $sub->whereNull('jurusan_id')->orWhere('jurusan_id', $jurusanId);
            });
        }

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('nama_dudi', 'like', "%{$q}%")
                    ->orWhere('bidang_industri', 'like', "%{$q}%")
                    ->orWhere('kota', 'like', "%{$q}%");
            });
        }

        $paginator = $query->orderBy('nama_dudi')->paginate($perPage);

        $partnerIds = $paginator->getCollection()->pluck('id');
        $activeCounts = PklPlacement::where('status', 'active')
            ->whereIn('pkl_dudi_partner_id', $partnerIds)
            ->selectRaw('pkl_dudi_partner_id, count(*) as count')
            ->groupBy('pkl_dudi_partner_id')
            ->pluck('count', 'pkl_dudi_partner_id');

        $data = $paginator->getCollection()->map(function ($p) use ($activeCounts) {
            $used = (int) ($activeCounts[$p->id] ?? 0);
            $kuota = $p->kuota_siswa ? (int) $p->kuota_siswa : null;
            $sisa = $kuota === null ? null : max(0, $kuota - $used);
            $subtext = ($p->bidang_industri ?: 'Industri') . ' • ' . ($p->kota ?: '-');
            if ($kuota !== null) {
                $subtext .= " • Sisa Kuota: {$sisa}/{$kuota}";
            }

            return [
                'id'      => $p->id,
                'label'   => $p->nama_dudi,
                'subtext' => $subtext,
                'sisa'    => $sisa,
                'kuota'   => $kuota,
                'penuh'   => $kuota !== null && $used >= $kuota,
            ];
        });

        return response()->json([
            'data' => $data,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'last_page'    => $paginator->lastPage(),
                'has_more'     => $paginator->hasMorePages(),
            ],
            'message' => $data->isEmpty() ? 'Tidak ada data mitra DU/DI yang sesuai.' : null,
        ]);
    }

    /**
     * Search Master Pelanggaran & Prestasi with Server-Side Pagination
     */
    public function disciplineCategories(Request $request): JsonResponse
    {
        $q = trim((string) $request->input('q'));
        $perPage = max(1, min(100, (int) $request->input('per_page', 15)));
        $tipe = $request->input('tipe'); // 'pelanggaran' or 'prestasi'
        $onlyActive = filter_var($request->input('only_active', true), FILTER_VALIDATE_BOOLEAN);

        $query = DisciplineCategory::query();

        if ($onlyActive) {
            $query->where('is_active', true);
        }

        if (!empty($tipe) && in_array($tipe, ['pelanggaran', 'prestasi'])) {
            $query->where('tipe', $tipe);
        }

        if ($q !== '') {
            $query->where(function ($sq) use ($q) {
                $sq->where('nama', 'like', "%{$q}%")
                   ->orWhere('deskripsi', 'like', "%{$q}%");
            });
        }

        // Selected IDs resolver
        $selectedIds = $request->input('selected_ids');
        if (is_string($selectedIds)) {
            $selectedIds = array_filter(explode(',', $selectedIds));
        }

        if (!empty($selectedIds) && is_array($selectedIds)) {
            $selectedItems = DisciplineCategory::whereIn('id', $selectedIds)->get()->map(function ($c) {
                $sign = $c->default_poin > 0 ? '+' : '';
                return [
                    'id'           => $c->id,
                    'label'        => '[' . strtoupper($c->tipe) . '] ' . $c->nama . ' (' . $sign . $c->default_poin . ' poin)',
                    'sub'          => ($c->deskripsi ?: 'Kategori Standar') . ' • Poin: ' . $sign . $c->default_poin,
                    'nama'         => $c->nama,
                    'tipe'         => $c->tipe,
                    'default_poin' => $c->default_poin,
                    'poin'         => $c->default_poin,
                    'deskripsi'    => $c->deskripsi,
                    'is_active'    => (bool) $c->is_active,
                ];
            });

            return response()->json([
                'data' => $selectedItems,
                'pagination' => [
                    'current_page' => 1,
                    'per_page'     => count($selectedItems),
                    'total'        => count($selectedItems),
                    'last_page'    => 1,
                    'has_more'     => false,
                ],
            ]);
        }

        $paginator = $query->orderBy('tipe')->orderBy('nama')->paginate($perPage);

        $data = $paginator->getCollection()->map(function ($c) {
            $sign = $c->default_poin > 0 ? '+' : '';
            return [
                'id'           => $c->id,
                'label'        => '[' . strtoupper($c->tipe) . '] ' . $c->nama . ' (' . $sign . $c->default_poin . ' poin)',
                'sub'          => ($c->deskripsi ?: 'Kategori Standar') . ' • Poin: ' . $sign . $c->default_poin,
                'nama'         => $c->nama,
                'tipe'         => $c->tipe,
                'default_poin' => $c->default_poin,
                'poin'         => $c->default_poin,
                'deskripsi'    => $c->deskripsi,
                'is_active'    => (bool) $c->is_active,
            ];
        });

        return response()->json([
            'data' => $data,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'last_page'    => $paginator->lastPage(),
                'has_more'     => $paginator->hasMorePages(),
            ],
            'message' => $data->isEmpty() ? 'Tidak ada master kategori yang sesuai.' : null,
        ]);
    }
}
