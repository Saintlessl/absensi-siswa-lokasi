<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Hash;

class StudentPasswordResetOtp extends Model
{
    use HasFactory;

    protected $table = 'student_password_reset_otps';

    protected $fillable = [
        'user_id',
        'siswa_id',
        'no_telp',
        'otp_hash',
        'reset_token',
        'attempts',
        'expires_at',
        'resend_available_at',
        'verified_at',
        'used_at',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'expires_at'          => 'datetime',
        'resend_available_at' => 'datetime',
        'verified_at'         => 'datetime',
        'used_at'             => 'datetime',
        'attempts'            => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    public function isExpired(): bool
    {
        return now()->isAfter($this->expires_at);
    }

    public function isUsed(): bool
    {
        return !is_null($this->used_at);
    }

    public function isMaxAttemptsExceeded(): bool
    {
        return $this->attempts >= 5;
    }

    public function canResend(): bool
    {
        return now()->isAfter($this->resend_available_at);
    }

    public function verifyOtp(string $otp): bool
    {
        return Hash::check($otp, $this->otp_hash);
    }

    /**
     * Mask phone number for public preview, e.g. 0812******45
     */
    public static function maskPhone(?string $phone): string
    {
        if (empty($phone)) {
            return '-';
        }

        // Standardize leading 62 to 0 for friendly Indonesian display
        $display = preg_replace('/^62/', '0', $phone);
        $len = strlen($display);

        if ($len <= 6) {
            return $display;
        }

        $prefix = substr($display, 0, 4);
        $suffix = substr($display, -2);
        $maskLength = max(4, $len - 6);

        return $prefix . str_repeat('*', $maskLength) . $suffix;
    }
}
