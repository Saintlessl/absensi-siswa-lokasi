<?php

namespace App\Models;

use App\Models\Siswa;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    protected $fillable = [
        'siswa_id',
        'tanggal',
        'jam_masuk',
        'status',
        'keterangan',
        'latitude',
        'longitude'
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }
}
