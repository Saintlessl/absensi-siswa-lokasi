<?php

namespace App\Models;

use App\Models\Siswa;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BiometricEnrollmentLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'siswa_id',
        'attempt_type',
        'status',
        'quality_score',
        'liveness_score',
        'model_version',
        'failure_reason',
        'ip_address',
        'user_agent',
        'performed_by',
    ];

    protected $casts = [
        'quality_score' => 'float',
        'liveness_score' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function performer()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
