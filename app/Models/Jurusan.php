<?php

namespace App\Models;

use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jurusan extends Model
{
    use HasFactory;

    protected $table = 'jurusans';

    protected $fillable = [
        'kode_jurusan',
        'nama_jurusan',
        'deskripsi',
    ];

    public function kelas()
    {
        return $this->hasMany(Kelas::class);
    }

    public function siswa()
    {
        return $this->hasManyThrough(Siswa::class, Kelas::class);
    }
}
