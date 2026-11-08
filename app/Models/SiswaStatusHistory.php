<?php

namespace App\Models;

use App\Models\Siswa;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiswaStatusHistory extends Model
{
    use HasFactory;

    protected $table = 'siswa_status_histories';

    protected $fillable = [
        'siswa_id',
        'status_sebelumnya',
        'status_baru',
        'tanggal_perubahan',
        'tahun_ajaran',
        'alasan',
        'sekolah_tujuan',
        'nomor_surat',
        'catatan',
        'created_by',
    ];

    protected $casts = [
        'tanggal_perubahan' => 'date',
    ];

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status_baru) {
            'aktif'                  => 'Siswa Aktif',
            'dalam_proses_kelulusan' => 'Dalam Proses Kelulusan',
            'lulus'                  => 'Lulus (Alumni)',
            'tidak_lulus'            => 'Tidak Lulus',
            'pindah'                 => 'Pindah Sekolah',
            'keluar'                 => 'Keluar / Mengundurkan Diri',
            default                  => ucfirst(str_replace('_', ' ', $this->status_baru)),
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status_baru) {
            'aktif'                  => 'bg-blue-50 text-blue-700 border-blue-200',
            'dalam_proses_kelulusan' => 'bg-amber-50 text-amber-700 border-amber-200',
            'lulus'                  => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'tidak_lulus'            => 'bg-rose-50 text-rose-700 border-rose-200',
            'pindah'                 => 'bg-purple-50 text-purple-700 border-purple-200',
            'keluar'                 => 'bg-slate-100 text-slate-700 border-slate-300',
            default                  => 'bg-slate-100 text-slate-700 border-slate-200',
        };
    }
}
