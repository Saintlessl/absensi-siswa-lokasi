<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuratPeringatan extends Model
{
    use HasFactory;

    protected $fillable = [
        'siswa_id',
        'nomor_surat',
        'jenis_sp',
        'tanggal_terbit',
        'keterangan'
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }
}
