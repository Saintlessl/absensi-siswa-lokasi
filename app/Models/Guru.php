<?php

namespace App\Models;

use App\Models\Jadwal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guru extends Model
{
    protected $fillable = [
        'user_id',
        'nip',
        'nama',
        'mapel',
        'jenis_kelamin'
    ];

    public function jadwal()
    {
        return $this->hasMany(Jadwal::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
