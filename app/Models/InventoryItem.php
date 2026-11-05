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
        'kategori',
        'lokasi_penyimpanan',
        'deskripsi',
        'foto_path',
        'total_unit',
        'unit_tersedia',
        'kondisi',
        'status',
        'qr_token',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            if (empty($item->qr_token)) {
                $item->qr_token = Str::random(40);
            }
        });
    }

    public function jurusan(): BelongsTo
    {
        return $this->belongsTo(Jurusan::class);
    }

    public function borrowings(): HasMany
    {
        return $this->hasMany(InventoryBorrowing::class);
    }

    public function isAvailable(): bool
    {
        return $this->status === 'tersedia' && $this->unit_tersedia > 0 && !in_array($this->kondisi, ['rusak_berat', 'maintenance']);
    }
}
