<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class InventoryUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_item_id',
        'kode_unit',
        'barcode_eksternal',
        'qr_token',
        'kondisi',
        'status_ketersediaan',
        'lokasi_penyimpanan',
        'tanggal_pengadaan',
        'catatan',
        'is_active',
        'status_alasan',
    ];

    protected $casts = [
        'tanggal_pengadaan' => 'date',
        'is_active'         => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($unit) {
            if (empty($unit->qr_token)) {
                $unit->qr_token = Str::random(40);
            }
            if (empty($unit->status_ketersediaan)) {
                $unit->status_ketersediaan = 'tersedia';
            }
            if (empty($unit->kondisi)) {
                $unit->kondisi = 'baik';
            }
        });
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    public function borrowingUnits(): HasMany
    {
        return $this->hasMany(InventoryBorrowingUnit::class, 'inventory_unit_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(InventoryUnitLog::class, 'inventory_unit_id')->latest();
    }

    /**
     * Scope only active units (not archived/deleted).
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope units that are physically available and in condition 'baik' and not reserved.
     */
    public function scopeAvailableForLoan($query)
    {
        return $query->where('is_active', true)
            ->where('status_ketersediaan', 'tersedia')
            ->where('kondisi', 'baik')
            ->whereDoesntHave('borrowingUnits', function ($q) {
                $q->whereIn('status', ['reserved', 'borrowed']);
            });
    }

    /**
     * Check if this unit is eligible for a new loan request/allocation.
     */
    public function isEligibleForLoan(): bool
    {
        return $this->is_active
            && $this->kondisi === 'baik'
            && $this->status_ketersediaan === 'tersedia'
            && !$this->isReserved();
    }

    /**
     * Check if unit is currently reserved or borrowed in an active transaction.
     */
    public function isReserved(): bool
    {
        return $this->borrowingUnits()
            ->whereIn('status', ['reserved', 'borrowed'])
            ->exists();
    }

    /**
     * Get active borrowing if any.
     */
    public function activeBorrowing(): ?InventoryBorrowing
    {
        return $this->borrowingUnits()
            ->whereIn('status', ['reserved', 'borrowed'])
            ->with(['borrowing.user', 'borrowing.siswa', 'borrowing.peminjamGuru'])
            ->latest('id')
            ->first()?->borrowing;
    }

    /**
     * Effective storage location.
     */
    public function getEffectiveLokasiAttribute(): string
    {
        return $this->lokasi_penyimpanan ?: ($this->item?->lokasi_penyimpanan ?: '-');
    }

    /**
     * Helper to write an audit log entry.
     */
    public function logActivity(
        string $action,
        ?string $keterangan = null,
        ?string $kondisiLama = null,
        ?string $kondisiBaru = null,
        ?string $lokasiLama = null,
        ?string $lokasiBaru = null,
        ?int $userId = null,
        ?int $borrowingId = null
    ): InventoryUnitLog {
        return InventoryUnitLog::create([
            'inventory_unit_id'      => $this->id,
            'inventory_borrowing_id' => $borrowingId,
            'user_id'                => $userId ?? auth()->id(),
            'action'                 => $action,
            'kondisi_lama'           => $kondisiLama,
            'kondisi_baru'           => $kondisiBaru,
            'lokasi_lama'            => $lokasiLama,
            'lokasi_baru'            => $lokasiBaru,
            'keterangan'             => $keterangan,
        ]);
    }
}
