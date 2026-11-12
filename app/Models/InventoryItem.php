<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class InventoryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_barang',
        'nama_barang',
        'jurusan_id',
        'borrowing_scope',
        'kategori',
        'merk',
        'tipe_model',
        'lokasi_penyimpanan',
        'deskripsi',
        'foto_path',
        'total_unit',
        'unit_tersedia',
        'kondisi',
        'status',
        'is_active',
        'qr_token',
    ];

    protected $casts = [
        'is_active'     => 'boolean',
        'total_unit'    => 'integer',
        'unit_tersedia' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            if (empty($item->qr_token)) {
                $item->qr_token = Str::random(40);
            }
            if (!isset($item->is_active)) {
                $item->is_active = true;
            }
        });
    }

    public function jurusan(): BelongsTo
    {
        return $this->belongsTo(Jurusan::class);
    }

    public function units(): HasMany
    {
        return $this->hasMany(InventoryUnit::class, 'inventory_item_id');
    }

    public function activeUnits(): HasMany
    {
        return $this->hasMany(InventoryUnit::class, 'inventory_item_id')->where('is_active', true);
    }

    public function borrowings(): HasMany
    {
        return $this->hasMany(InventoryBorrowing::class);
    }

    /**
     * Count physical units eligible for borrowing:
     * active, physical availability = 'tersedia', condition = 'baik',
     * and NOT reserved in active borrowings.
     */
    public function countAvailableUnits(): int
    {
        $hasUnits = $this->units()->exists();
        if (!$hasUnits) {
            return (int) $this->unit_tersedia;
        }

        return $this->units()
            ->where('is_active', true)
            ->where('status_ketersediaan', 'tersedia')
            ->where('kondisi', 'baik')
            ->whereDoesntHave('borrowingUnits', function ($q) {
                $q->whereIn('status', ['reserved', 'borrowed']);
            })
            ->count();
    }

    /**
     * Check if user is eligible to borrow this item based on borrowing_scope.
     */
    public function canBeBorrowedBy(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        if (in_array($user->role, ['superadmin', 'admin'], true)) {
            return true;
        }

        $scope = $this->borrowing_scope ?: 'jurusan';

        if ($scope === 'umum') {
            return true;
        }

        // Scope is 'jurusan': only students from this item's jurusan can borrow
        if ($user->role === 'siswa') {
            $siswa = $user->siswa ?: Siswa::with('kelas')->where('user_id', $user->id)->first();
            $siswaJurusanId = $siswa?->kelas?->jurusan_id ?? $siswa?->jurusan_id;

            if (!$this->jurusan_id) {
                return true;
            }

            return (int) $siswaJurusanId === (int) $this->jurusan_id;
        }

        return false;
    }

    /**
     * Query scope: filter items eligible for borrowing by a given user.
     */
    public function scopeBorrowableBy($query, User $user)
    {
        if (in_array($user->role, ['superadmin', 'admin'], true)) {
            return $query;
        }

        if ($user->role === 'siswa') {
            $siswa = $user->siswa ?: Siswa::with('kelas')->where('user_id', $user->id)->first();
            $siswaJurusanId = $siswa?->kelas?->jurusan_id ?? $siswa?->jurusan_id;

            return $query->where(function ($q) use ($siswaJurusanId) {
                $q->where('borrowing_scope', 'umum');
                if ($siswaJurusanId) {
                    $q->orWhere(function ($sub) use ($siswaJurusanId) {
                        $sub->where(function ($s) {
                            $s->where('borrowing_scope', 'jurusan')->orWhereNull('borrowing_scope');
                        })->where('jurusan_id', $siswaJurusanId);
                    });
                }
            });
        }

        if ($user->role === 'guru') {
            return $query->where('borrowing_scope', 'umum');
        }

        return $query;
    }

    /**
     * Scope badge attribute for UI
     */
    public function getBorrowingScopeBadgeAttribute(): array
    {
        $scope = $this->borrowing_scope ?: 'jurusan';
        if ($scope === 'umum') {
            return [
                'label' => 'Umum (Semua Jurusan & Guru)',
                'short' => 'Umum',
                'bg'    => 'bg-blue-50 text-[#003FA9] border border-blue-200',
                'icon'  => 'bx bx-globe',
            ];
        }

        $jurusanName = $this->jurusan ? $this->jurusan->nama_jurusan : 'Jurusan';
        return [
            'label' => "Khusus Jurusan ({$jurusanName})",
            'short' => 'Khusus Jurusan',
            'bg'    => 'bg-amber-50 text-amber-800 border border-amber-200',
            'icon'  => 'bx bx-lock-alt',
        ];
    }

    /**
     * Check if group has at least one unit eligible for lending.
     */
    public function isAvailable(): bool
    {
        if (!$this->units()->exists()) {
            return $this->status === 'tersedia' && $this->unit_tersedia > 0 && !in_array($this->kondisi, ['rusak_berat', 'maintenance']);
        }

        return $this->countAvailableUnits() > 0;
    }

    /**
     * Breakdown of conditions across active physical units.
     */
    public function getConditionBreakdownAttribute(): array
    {
        $breakdown = [
            'baik'              => 0,
            'rusak_ringan'      => 0,
            'rusak_berat'       => 0,
            'perlu_pemeriksaan' => 0,
        ];

        if (!$this->units()->exists()) {
            $k = in_array($this->kondisi, ['baik', 'rusak_ringan', 'rusak_berat', 'perlu_pemeriksaan']) ? $this->kondisi : 'baik';
            $breakdown[$k] = (int) $this->total_unit;
            return $breakdown;
        }

        $counts = $this->units()
            ->where('is_active', true)
            ->selectRaw('kondisi, count(*) as count')
            ->groupBy('kondisi')
            ->pluck('count', 'kondisi');

        foreach ($counts as $kondisi => $count) {
            if (isset($breakdown[$kondisi])) {
                $breakdown[$kondisi] = (int) $count;
            }
        }

        return $breakdown;
    }

    /**
     * Count units currently borrowed.
     */
    public function countBorrowedUnits(): int
    {
        if (!$this->units()->exists()) {
            return max(0, (int) $this->total_unit - (int) $this->unit_tersedia);
        }

        return $this->units()
            ->where('is_active', true)
            ->where('status_ketersediaan', 'dipinjam')
            ->count();
    }

    /**
     * Recalculate and synchronize total_unit and unit_tersedia based on physical units.
     */
    public function syncStockCounts(): self
    {
        if (!$this->units()->exists()) {
            return $this;
        }

        $total = $this->units()->where('is_active', true)->count();
        $tersedia = $this->countAvailableUnits();
        $borrowed = $this->countBorrowedUnits();

        $status = 'tersedia';
        if ($total === 0) {
            $status = 'afkir';
        } elseif ($tersedia === 0 && $borrowed > 0) {
            $status = 'dipinjam';
        } elseif ($tersedia === 0 && $total > 0) {
            $status = 'maintenance';
        }

        $this->update([
            'total_unit'    => $total,
            'unit_tersedia' => $tersedia,
            'status'        => $status,
        ]);

        return $this;
    }

    /**
     * Auto-create physical units for this item if needed.
     */
    public function createUnits(int $count, string $initialCondition = 'baik', ?string $lokasi = null, ?int $userId = null): array
    {
        $created = [];
        $cleanKode = preg_replace('/[^A-Za-z0-9\-]/', '', strtoupper($this->kode_barang));
        
        // Find current maximum unit index
        $existingCount = $this->units()->count();

        for ($i = 1; $i <= $count; $i++) {
            $serialNumber = $existingCount + $i;
            $serial = str_pad($serialNumber, 4, '0', STR_PAD_LEFT);
            $kodeUnit = "{$cleanKode}-{$serial}";

            while (InventoryUnit::where('kode_unit', $kodeUnit)->exists()) {
                $serialNumber++;
                $serial = str_pad($serialNumber, 4, '0', STR_PAD_LEFT);
                $kodeUnit = "{$cleanKode}-{$serial}";
            }

            $unit = InventoryUnit::create([
                'inventory_item_id'   => $this->id,
                'kode_unit'           => $kodeUnit,
                'barcode_eksternal'   => null,
                'qr_token'            => Str::random(40),
                'kondisi'             => in_array($initialCondition, ['baik', 'rusak_ringan', 'rusak_berat', 'perlu_pemeriksaan']) ? $initialCondition : 'baik',
                'status_ketersediaan' => 'tersedia',
                'lokasi_penyimpanan'  => $lokasi ?: $this->lokasi_penyimpanan,
                'tanggal_pengadaan'   => now()->toDateString(),
                'is_active'           => true,
                'status_alasan'       => 'aktif',
                'catatan'             => 'Pendaftaran unit baru',
            ]);

            $unit->logActivity(
                action: 'unit_didaftarkan',
                keterangan: "Pendaftaran unit fisik {$kodeUnit} (Kondisi awal: {$unit->kondisi})",
                kondisiBaru: $unit->kondisi,
                lokasiBaru: $unit->lokasi_penyimpanan,
                userId: $userId
            );

            $created[] = $unit;
        }

        $this->syncStockCounts();

        return $created;
    }
}
