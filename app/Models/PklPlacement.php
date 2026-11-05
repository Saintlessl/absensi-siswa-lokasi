<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PklPlacement extends Model
{
    use HasFactory;

    protected $fillable = [
        'siswa_id',
        'pkl_dudi_partner_id',
        'guru_pembimbing_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'status',
        'catatan',
    ];

    protected $casts = [
        'tanggal_mulai'   => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function partner()
    {
        return $this->belongsTo(PklDudiPartner::class, 'pkl_dudi_partner_id');
    }

    public function guruPembimbing()
    {
        return $this->belongsTo(User::class, 'guru_pembimbing_id');
    }

    public function attendances()
    {
        return $this->hasMany(PklAttendance::class);
    }

    public function logbooks()
    {
        return $this->hasMany(PklLogbook::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active' &&
            now()->toDateString() >= $this->tanggal_mulai->toDateString() &&
            now()->toDateString() <= $this->tanggal_selesai->toDateString();
    }
}
