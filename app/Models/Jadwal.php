<?php

namespace App\Models;

use App\Models\Guru;
use App\Models\Kelas;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jadwal extends Model
{
    protected $fillable = [
        'guru_id',
        'kelas_id',
        'mata_pelajaran',
        'hari',
        'jam_mulai',
        'jam_selesai'
    ];

    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }
}
