<?php

namespace App\Models;

use App\Models\Siswa;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AbsensiVerificationLog extends Model
{
    use HasFactory;

    protected $table = 'absensi_verification_logs';

    protected $fillable = [
        'siswa_id',
        'session_type',
        'status',
        'confidence_score',
        'euclidean_distance',
        'liveness_score',
        'failure_reason',
        'ip_address',
        'user_agent',
        'snapshot_path',
    ];

    protected $casts = [
        'confidence_score' => 'float',
        'euclidean_distance' => 'float',
        'liveness_score' => 'float',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }
}
