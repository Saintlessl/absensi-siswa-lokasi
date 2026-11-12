<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class InventoryBorrowing extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_item_id',
        'user_id',
        'siswa_id',
        'peminjam_guru_id',
        'sumber_transaksi',
        'dicatat_oleh',
        'jumlah',
        'tanggal_pinjam',
        'rencana_kembali',
        'tanggal_kembali',
        'keperluan',
        'status',
        'kondisi_sebelum',
        'kondisi_sesudah',
        'catatan_pengembalian',
        'decided_by',
        'approved_by',
        'approved_at',
        'diserahkan_oleh',
        'diserahkan_at',
    ];

    protected $casts = [
        'tanggal_pinjam'  => 'date',
        'rencana_kembali' => 'date',
        'tanggal_kembali' => 'date',
        'approved_at'     => 'datetime',
        'diserahkan_at'   => 'datetime',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    public function borrowingUnits(): HasMany
    {
        return $this->hasMany(InventoryBorrowingUnit::class, 'inventory_borrowing_id');
    }

    public function units(): HasManyThrough
    {
        return $this->hasManyThrough(
            InventoryUnit::class,
            InventoryBorrowingUnit::class,
            'inventory_borrowing_id',
            'id',
            'id',
            'inventory_unit_id'
        );
    }

    public function peminjamGuru(): BelongsTo
    {
        return $this->belongsTo(Guru::class, 'peminjam_guru_id');
    }

    public function pencatat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    public function decidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function diserahkanOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diserahkan_oleh');
    }

    /**
     * Scope for "Sedang Dipinjam" tab:
     * Handed over, not yet returned, within due date.
     */
    public function scopeSedangDipinjam($query)
    {
        $today = Carbon::now('Asia/Jakarta')->startOfDay()->toDateString();
        return $query->where('status', 'borrowed')
            ->whereNull('tanggal_kembali')
            ->whereDate('rencana_kembali', '>=', $today);
    }

    /**
     * Scope for "Dikembalikan" tab:
     * Returned & confirmed by petugas.
     */
    public function scopeDikembalikan($query)
    {
        return $query->where(function ($q) {
            $q->where('status', 'returned')
              ->orWhereNotNull('tanggal_kembali');
        });
    }

    /**
     * Scope for "Belum Dikembalikan" tab:
     * Handed over, not yet returned, past due date.
     */
    public function scopeBelumDikembalikan($query)
    {
        $today = Carbon::now('Asia/Jakarta')->startOfDay()->toDateString();
        return $query->where('status', 'borrowed')
            ->whereNull('tanggal_kembali')
            ->whereDate('rencana_kembali', '<', $today);
    }

    /**
     * Scope by monitoring tab key ('dipinjam', 'dikembalikan', 'belum_dikembalikan')
     */
    public function scopeFilterMonitoringTab($query, string $tab)
    {
        return match ($tab) {
            'dikembalikan', 'returned'       => $query->dikembalikan(),
            'belum_dikembalikan', 'overdue'  => $query->belumDikembalikan(),
            default                          => $query->sedangDipinjam(),
        };
    }

    /**
     * Determine derived transaction status based on Section 12 rules:
     * 1. If returned / tanggal_kembali exists => Sudah Dikembalikan ('returned')
     * 2. If borrowed & current time in Asia/Jakarta is past rencana_kembali => Belum Dikembalikan ('overdue')
     * 3. If borrowed & not overdue => Dipinjam ('borrowed')
     * 4. Others: 'pending', 'approved', 'rejected', 'cancelled'
     */
    public function getEffectiveStatusAttribute(): string
    {
        if ($this->status === 'returned' || !empty($this->tanggal_kembali)) {
            return 'returned';
        }

        if ($this->status === 'borrowed') {
            $now = Carbon::now('Asia/Jakarta')->startOfDay();
            $rencana = Carbon::parse($this->rencana_kembali)->startOfDay();

            if ($now->gt($rencana)) {
                return 'overdue';
            }

            return 'borrowed';
        }

        return $this->status;
    }

    /**
     * Check if currently overdue.
     */
    public function getIsOverdueAttribute(): bool
    {
        return $this->effective_status === 'overdue';
    }

    /**
     * Badge configuration for UI based on design system.
     */
    public function getStatusBadgeAttribute(): array
    {
        return match ($this->effective_status) {
            'returned'  => ['label' => 'Sudah Dikembalikan', 'bg' => 'bg-emerald-50 text-emerald-700 border border-emerald-200', 'color' => '#059669'],
            'borrowed'  => ['label' => 'Dipinjam', 'bg' => 'bg-blue-50 text-[#003FA9] border border-blue-200', 'color' => '#003FA9'],
            'overdue'   => ['label' => 'Belum Dikembalikan', 'bg' => 'bg-rose-50 text-rose-700 border border-rose-200', 'color' => '#E11D48'],
            'approved'  => ['label' => 'Disetujui / Siap Diambil', 'bg' => 'bg-indigo-50 text-indigo-700 border border-indigo-200', 'color' => '#4F46E5'],
            'pending'   => ['label' => 'Menunggu Persetujuan', 'bg' => 'bg-amber-50 text-amber-700 border border-amber-200', 'color' => '#D97706'],
            'rejected'  => ['label' => 'Ditolak', 'bg' => 'bg-slate-100 text-slate-700 border border-slate-200', 'color' => '#475569'],
            'cancelled' => ['label' => 'Dibatalkan', 'bg' => 'bg-slate-100 text-slate-600 border border-slate-200', 'color' => '#64748B'],
            default     => ['label' => ucfirst($this->status), 'bg' => 'bg-slate-100 text-slate-700 border border-slate-200', 'color' => '#475569'],
        };
    }
}
