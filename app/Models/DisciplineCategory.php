<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DisciplineCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'tipe',
        'default_poin',
        'deskripsi',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'default_poin' => 'integer',
        'is_active'    => 'boolean',
    ];

    public function records()
    {
        return $this->hasMany(DisciplineRecord::class);
    }

    public function items()
    {
        return $this->hasMany(DisciplineRecordItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if master item has been referenced in any records or items
     */
    public function isUsedInRecords(): bool
    {
        return $this->items()->exists() || $this->records()->exists();
    }

    /**
     * Formatted default points for display
     */
    public function getFormattedDefaultPoinAttribute(): string
    {
        if ($this->default_poin > 0) {
            return '+' . $this->default_poin;
        }
        return (string) $this->default_poin;
    }

    /**
     * Badge CSS class for the type
     */
    public function getBadgeClassAttribute(): string
    {
        return $this->tipe === 'prestasi'
            ? 'bg-emerald-600 text-white'
            : 'bg-rose-600 text-white';
    }
}
