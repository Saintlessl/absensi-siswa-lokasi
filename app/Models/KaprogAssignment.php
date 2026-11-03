<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KaprogAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'guru_id',
        'jurusan_id',
        'tahun_ajaran',
        'starts_on',
        'ends_on',
        'is_active',
        'catatan',
    ];

    protected $casts = [
        'starts_on' => 'date',
        'ends_on'   => 'date',
        'is_active' => 'boolean',
    ];

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class);
    }

    public function jurusan(): BelongsTo
    {
        return $this->belongsTo(Jurusan::class);
    }
}
