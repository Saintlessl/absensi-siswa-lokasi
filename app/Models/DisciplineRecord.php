<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DisciplineRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'siswa_id',
        'discipline_category_id',
        'tipe',
        'tanggal',
        'judul',
        'deskripsi',
        'bukti_path',
        'poin',
        'status',
        'tindak_lanjut',
        'recorded_by',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'poin'    => 'integer',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function category()
    {
        return $this->belongsTo(DisciplineCategory::class, 'discipline_category_id');
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function items()
    {
        return $this->hasMany(DisciplineRecordItem::class, 'discipline_record_id');
    }

    /**
     * Effective type (pelanggaran / prestasi)
     */
    public function getEffectiveTypeAttribute(): string
    {
        return $this->tipe ?: ($this->category?->tipe ?: 'pelanggaran');
    }

    /**
     * Formatted points with explicit sign (+ / -)
     */
    public function getFormattedPoinAttribute(): string
    {
        if ($this->poin > 0) {
            return '+' . $this->poin;
        }
        return (string) $this->poin;
    }

    /**
     * Badge CSS class
     */
    public function getBadgeClassAttribute(): string
    {
        return $this->effective_type === 'prestasi'
            ? 'bg-emerald-600 text-white'
            : 'bg-rose-600 text-white';
    }
}
