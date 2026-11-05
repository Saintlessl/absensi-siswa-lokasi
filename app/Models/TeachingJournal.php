<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TeachingJournal extends Model
{
    use HasFactory;

    protected $fillable = [
        'guru_id',
        'jadwal_id',
        'kelas_id',
        'mata_pelajaran_id',
        'nama_mapel',
        'tanggal',
        'jam_ke',
        'materi_pokok',
        'tujuan_pembelajaran',
        'elemen_capaian',
        'aktivitas',
        'metode',
        'catatan',
        'kendala',
        'status',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class);
    }

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(Jadwal::class);
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function mataPelajaran(): BelongsTo
    {
        return $this->belongsTo(MataPelajaran::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(TeachingJournalAttendance::class);
    }
}
