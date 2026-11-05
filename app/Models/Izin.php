<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Izin extends Model
{
    protected $fillable = [
        'siswa_id',
        'tanggal',
        'jenis',
        'keterangan',
        'status',
        'keputusan_catatan',
        'decided_by',
        'decided_at',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'decided_at' => 'datetime',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function decidedBy()
    {
        return $this->belongsTo(User::class, 'decided_by');
    }
}
