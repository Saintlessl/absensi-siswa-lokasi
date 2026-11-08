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
        'logo_path',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function kelas()
    {
        return $this->hasMany(Kelas::class);
    }

    public function activeKelas()
    {
        return $this->hasMany(Kelas::class)->where('is_active', true);
    }

    public function siswa()
    {
        return $this->hasManyThrough(Siswa::class, Kelas::class);
    }

    public function kaprogs()
    {
        return $this->hasMany(User::class);
    }

    public function angkatans()
    {
        return $this->hasMany(Angkatan::class)->orderByDesc('nomor_angkatan');
    }

    public function activeAngkatans()
    {
        return $this->hasMany(Angkatan::class)->where('status', 'aktif')->orderByDesc('nomor_angkatan');
    }

    public function alumniAngkatans()
    {
        return $this->hasMany(Angkatan::class)->where('status', 'selesai')->orderByDesc('nomor_angkatan');
    }

    public function getLogoUrlAttribute()
    {
        if ($this->logo_path) {
            return asset('storage/' . $this->logo_path);
        }
        return null;
    }
}
