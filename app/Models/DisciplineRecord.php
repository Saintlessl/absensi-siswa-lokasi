<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DisciplineRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'siswa_id',
        'discipline_category_id',
        'tanggal',
        'judul',
        'deskripsi',
        'bukti_path',
        'poin',
        'status',
        'tindak_lanjut',
        'recorded_by',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'poin'    => 'integer',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function category()
    {
        return $this->belongsTo(DisciplineCategory::class, 'discipline_category_id');
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
