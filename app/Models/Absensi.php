<?php

namespace App\Models;

use App\Models\Siswa;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    protected $fillable = [
        'siswa_id',
        'room_id',
        'tanggal',
        'jam_masuk',
        'jam_pulang',
        'status',
        'keterangan',
        'keterangan_terlambat',
        'menit_terlambat',
        'foto_masuk',
        'foto_pulang',
        'latitude',
        'longitude',
        'latitude_pulang',
        'longitude_pulang',
        'confidence_masuk',
        'confidence_pulang',
        'liveness_score_masuk',
        'liveness_score_pulang',
        'is_verified_biometric',
        'is_manual_override',
        'manual_override_by',
        'manual_override_reason'
    ];

    protected $casts = [
        'is_verified_biometric' => 'boolean',
        'is_manual_override' => 'boolean',
        'confidence_masuk' => 'float',
        'confidence_pulang' => 'float',
        'liveness_score_masuk' => 'float',
        'liveness_score_pulang' => 'float',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function manualOverrideUser()
    {
        return $this->belongsTo(User::class, 'manual_override_by');
    }
}
