<?php

namespace App\Models;

use App\Models\Absensi;
use App\Models\AbsensiVerificationLog;
use App\Models\Kelas;
use App\Models\OrangTua;
use App\Models\SiswaFaceBiometric;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    protected $fillable = [
        'user_id',
        'nis',
        'nama',
        'jenis_kelamin',
        'kelas_id',
        'biometric_status'
    ];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function absensi()
    {
        return $this->hasMany(Absensi::class);
    }

    public function orangtua()
    {
        return $this->hasOne(OrangTua::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function faceBiometric()
    {
        return $this->hasOne(SiswaFaceBiometric::class);
    }

    public function verificationLogs()
    {
        return $this->hasMany(AbsensiVerificationLog::class);
    }

    public function enrollmentLogs()
    {
        return $this->hasMany(BiometricEnrollmentLog::class);
    }

    /**
     * Check if student is fully enrolled with a valid biometric template
     */
    public function isBiometricEnrolled(): bool
    {
        return $this->biometric_status === 'enrolled'
            && $this->faceBiometric()
                ->where('is_active', true)
                ->where('enrollment_status', 'enrolled')
                ->exists();
    }

    public function isFaceRegistered(): bool
    {
        return $this->isBiometricEnrolled();
    }
}
