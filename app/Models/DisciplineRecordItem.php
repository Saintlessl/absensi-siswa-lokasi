<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DisciplineRecordItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'discipline_record_id',
        'discipline_category_id',
        'nama_standar',
        'tipe',
        'poin',
        'keterangan',
    ];

    protected $casts = [
        'poin' => 'integer',
    ];

    public function record()
    {
        return $this->belongsTo(DisciplineRecord::class, 'discipline_record_id');
    }

    public function category()
    {
        return $this->belongsTo(DisciplineCategory::class, 'discipline_category_id');
    }

    /**
     * Get display badge class based on item type
     */
    public function getBadgeClassAttribute(): string
    {
        return $this->tipe === 'prestasi'
            ? 'bg-emerald-600 text-white'
            : 'bg-rose-600 text-white';
    }

    /**
     * Formatted point with explicit sign (+ / -)
     */
    public function getFormattedPoinAttribute(): string
    {
        if ($this->poin > 0) {
            return '+' . $this->poin;
        }
        return (string) $this->poin;
    }
}
