<?php

namespace App\Models;

use App\Models\Jadwal;
use App\Models\Jurusan;
use App\Models\Siswa;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    protected $table = 'kelas';

    protected $fillable = [
        'jurusan_id',
        'qr_token',
        'nama_kelas',
        'jumlah_siswa',
        'wali_kelas'
    ];

    public function jurusan()
    {
        return $this->belongsTo(Jurusan::class);
    }

    public function siswa()
    {
        return $this->hasMany(Siswa::class);
    }

    public function jadwal()
    {
        return $this->hasMany(Jadwal::class);
    }
}
