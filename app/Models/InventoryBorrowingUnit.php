<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryBorrowingUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_borrowing_id',
        'inventory_unit_id',
        'status',
        'kondisi_sebelum',
        'kondisi_sesudah',
        'catatan_pengembalian',
        'diserahkan_at',
        'dikembalikan_at',
    ];

    protected $casts = [
        'diserahkan_at'   => 'datetime',
        'dikembalikan_at' => 'datetime',
    ];

    public function borrowing(): BelongsTo
    {
        return $this->belongsTo(InventoryBorrowing::class, 'inventory_borrowing_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(InventoryUnit::class, 'inventory_unit_id');
    }
}
