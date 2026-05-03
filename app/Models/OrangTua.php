<?php

namespace App\Models;

use App\Models\Siswa;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrangTua extends Model
{
    protected $table = 'orang_tuas';

    protected $fillable = [
        'user_id',
        'nama',
        'telepon',
        'alamat',
        'siswa_id'
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }
}
