<?php

namespace App\Models;

use App\Models\Absensi;
use App\Models\Kelas;
use App\Models\OrangTua;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    protected $fillable = [
        'user_id',
        'nis',
        'nama',
        'jenis_kelamin',
        'kelas_id'
    ];


    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function absensi()
    {
        return $this->hasMany(Absensi::class);
    }

    public function orangtua()
    {
        return $this->hasOne(OrangTua::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
