<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PklLogbook extends Model
{
    use HasFactory;

    protected $fillable = [
        'pkl_placement_id',
        'tanggal',
        'aktivitas',
        'kompetensi_dipelajari',
        'foto_dokumentasi_path',
        'status',
        'catatan_guru',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'tanggal'     => 'date',
        'verified_at' => 'datetime',
    ];

    public function placement()
    {
        return $this->belongsTo(PklPlacement::class, 'pkl_placement_id');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
