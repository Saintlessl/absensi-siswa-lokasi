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
        'nisn',
        'nama',
        'jenis_kelamin',
        'kelas_id',
        'biometric_status'
        ,'photo_path'
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
        return $this->belongsToMany(OrangTua::class, 'siswa_orang_tua')->withTimestamps();
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

    public function devices()
    {
        return $this->hasMany(SiswaDevice::class);
    }

    public function activeDevices()
    {
        return $this->hasMany(SiswaDevice::class)->where('is_active', true);
    }

    public function whatsappLogs()
    {
        return $this->hasMany(WhatsappNotificationLog::class);
    }
    public function enrollments() { return $this->hasMany(StudentEnrollment::class); }
    public function activeEnrollment() { return $this->hasOne(StudentEnrollment::class)->where('is_active', true); }

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
