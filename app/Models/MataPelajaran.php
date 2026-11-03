<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MataPelajaran extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_mapel',
        'nama_mapel',
        'kelompok',
        'deskripsi',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function assignments()
    {
        return $this->hasMany(TeacherSubjectAssignment::class, 'mata_pelajaran_id');
    }

    public function jadwals()
    {
        return $this->hasMany(Jadwal::class, 'mata_pelajaran_id');
    }
}
