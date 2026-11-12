<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryUnitLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_unit_id',
        'inventory_borrowing_id',
        'user_id',
        'action',
        'kondisi_lama',
        'kondisi_baru',
        'lokasi_lama',
        'lokasi_baru',
        'keterangan',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(InventoryUnit::class, 'inventory_unit_id');
    }

    public function borrowing(): BelongsTo
    {
        return $this->belongsTo(InventoryBorrowing::class, 'inventory_borrowing_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
