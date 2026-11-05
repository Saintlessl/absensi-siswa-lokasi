<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CounselingSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'siswa_id',
        'guru_bk_id',
        'tanggal',
        'topik_umum',
        'permasalahan_rahasia',
        'tindak_lanjut_umum',
        'catatan_rahasia',
        'status',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function guruBk()
    {
        return $this->belongsTo(User::class, 'guru_bk_id');
    }

    /**
     * Strict confidentiality authorization check
     */
    public function canViewConfidentialNotes(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        if (in_array($user->role, ['superadmin', 'admin'])) {
            return true;
        }

        return $user->id === $this->guru_bk_id;
    }
}
