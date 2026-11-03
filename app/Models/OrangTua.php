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
        'jenis_kelamin',
        'telepon',
        'alamat',
        'siswa_id'
    ];

    public function getGenderLabelAttribute(): string
    {
        return app(\App\Services\SalutationService::class)->getGenderLabel($this->jenis_kelamin);
    }

    public function getSalutationAttribute(): string
    {
        if ($this->jenis_kelamin === 'L') {
            return 'Bapak';
        }
        if ($this->jenis_kelamin === 'P') {
            return 'Ibu';
        }
        return '';
    }

    public function getSalutationNameAttribute(): string
    {
        return app(\App\Services\SalutationService::class)->forOrangTua($this);
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function siswa()
    {
        return $this->belongsToMany(Siswa::class, 'siswa_orang_tua')->withTimestamps();
    }
}
