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
        'jenis_perubahan',
        'tanggal_perubahan',
        'tahun_ajaran',
        'tahun_ajaran_asal_id',
        'tahun_ajaran_asal',
        'tahun_ajaran_tujuan_id',
        'tahun_ajaran_tujuan',
        'kelas_asal_id',
        'kelas_tujuan_id',
        'wali_kelas_asal',
        'wali_kelas_tujuan',
        'alasan',
        'sekolah_tujuan',
        'nomor_surat',
        'catatan',
        'created_by',
    ];

    protected $casts = [
        'tanggal_perubahan' => 'date',
        'tahun_ajaran_asal_id'   => 'integer',
        'tahun_ajaran_tujuan_id' => 'integer',
        'kelas_asal_id'          => 'integer',
        'kelas_tujuan_id'        => 'integer',
    ];

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function kelasAsal(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_asal_id');
    }

    public function kelasTujuan(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_tujuan_id');
    }

    public function tahunAjaranAsal(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class, 'tahun_ajaran_asal_id');
    }

    public function tahunAjaranTujuan(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class, 'tahun_ajaran_tujuan_id');
    }

    public function getJenisPerubahanLabelAttribute(): string
    {
        return match ($this->jenis_perubahan) {
            'registrasi_baru' => 'Registrasi Siswa Baru',
            'mutasi_masuk'    => 'Mutasi Masuk (Pindahan)',
            'kenaikan_kelas'  => 'Kenaikan Kelas',
            'tinggal_kelas'   => 'Tinggal Kelas',
            'pindah_rombel'   => 'Perpindahan Rombel',
            'pindah_jurusan'  => 'Perubahan Jurusan',
            'mutasi_keluar'   => 'Mutasi Keluar',
            'kelulusan'       => 'Kelulusan Siswa',
            'reaktivasi'      => 'Pengaktifan Kembali',
            default           => ucfirst(str_replace('_', ' ', $this->jenis_perubahan ?: 'Perubahan Status')),
        };
    }

    public function getJenisPerubahanBadgeAttribute(): string
    {
        return match ($this->jenis_perubahan) {
            'registrasi_baru' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'mutasi_masuk'    => 'bg-blue-50 text-blue-700 border-blue-200',
            'kenaikan_kelas'  => 'bg-indigo-50 text-indigo-700 border-indigo-200',
            'tinggal_kelas'   => 'bg-amber-50 text-amber-700 border-amber-200',
            'pindah_rombel'   => 'bg-cyan-50 text-cyan-700 border-cyan-200',
            'pindah_jurusan'  => 'bg-violet-50 text-violet-700 border-violet-200',
            'kelulusan'       => 'bg-purple-50 text-purple-700 border-purple-200',
            'mutasi_keluar'   => 'bg-rose-50 text-rose-700 border-rose-200',
            'reaktivasi'      => 'bg-teal-50 text-teal-700 border-teal-200',
            default           => 'bg-slate-50 text-slate-700 border-slate-200',
        };
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
