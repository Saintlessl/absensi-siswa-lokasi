<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryBorrowing extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_item_id',
        'user_id',
        'siswa_id',
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
    ];

    protected $casts = [
        'tanggal_pinjam'  => 'date',
        'rencana_kembali' => 'date',
        'tanggal_kembali' => 'date',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
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
}
