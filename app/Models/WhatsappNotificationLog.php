<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappNotificationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'absensi_id',
        'siswa_id',
        'tipe',
        'nomor_tujuan',
        'pesan',
        'status',
        'provider',
        'response_payload',
        'error_message',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function absensi()
    {
        return $this->belongsTo(Absensi::class);
    }
}
