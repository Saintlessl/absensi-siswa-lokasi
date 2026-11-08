<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomeroomAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'guru_id',
        'kelas_id',
        'tahun_ajaran',
        'tahun_ajaran_id',
        'starts_on',
        'ends_on',
        'is_active',
        'catatan',
        'alasan_pergantian',
        'replaced_by_assignment_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'starts_on' => 'date',
        'ends_on'   => 'date',
        'tahun_ajaran_id' => 'integer',
        'kelas_id'  => 'integer',
        'guru_id'   => 'integer',
    ];

    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class, 'tahun_ajaran_id');
    }

    public function replacedBy()
    {
        return $this->belongsTo(HomeroomAssignment::class, 'replaced_by_assignment_id');
    }

    public function replaces()
    {
        return $this->hasOne(HomeroomAssignment::class, 'replaced_by_assignment_id');
    }
}
