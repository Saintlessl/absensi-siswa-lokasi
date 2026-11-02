<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiswaDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'siswa_id',
        'device_token',
        'device_name',
        'platform',
        'browser',
        'ip_address',
        'user_agent',
        'is_primary',
        'is_active',
        'registered_at',
        'last_active_at',
        'revoked_at',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'is_active' => 'boolean',
        'registered_at' => 'datetime',
        'last_active_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function revoke(): bool
    {
        return $this->update([
            'is_active' => false,
            'revoked_at' => now(),
        ]);
    }
}
