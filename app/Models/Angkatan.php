<?php

namespace App\Models;

use App\Models\Siswa;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Angkatan extends Model
{
    use HasFactory;

    protected $table = 'angkatans';

    protected $fillable = [
        'jurusan_id',
        'nomor_angkatan',
        'nama_angkatan',
        'kode_angkatan',
        'tahun_masuk',
        'tahun_ajaran_masuk',
        'tahun_lulus_rencana',
        'tahun_ajaran_kelulusan',
        'status',
        'tanggal_kelulusan',
        'keterangan',
        'created_by',
    ];

    protected $casts = [
        'jurusan_id'          => 'integer',
        'nomor_angkatan'      => 'integer',
        'tahun_masuk'         => 'integer',
        'tahun_lulus_rencana' => 'integer',
        'tanggal_kelulusan'   => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (Angkatan $model) {
            if (empty($model->nama_angkatan) && !empty($model->nomor_angkatan)) {
                $model->nama_angkatan = "Angkatan {$model->nomor_angkatan}";
            }

            if (empty($model->kode_angkatan) && !empty($model->nomor_angkatan)) {
                $jurusan = $model->jurusan ?? ($model->jurusan_id ? Jurusan::find($model->jurusan_id) : null);
                $prefix = $jurusan?->kode_jurusan ? "{$jurusan->kode_jurusan}-" : '';
                $model->kode_angkatan = "AKT-{$prefix}{$model->nomor_angkatan}";
            }

            if (empty($model->tahun_ajaran_masuk) && !empty($model->tahun_masuk)) {
                $model->tahun_ajaran_masuk = sprintf('%d/%d', $model->tahun_masuk, $model->tahun_masuk + 1);
            }

            if (empty($model->tahun_lulus_rencana) && !empty($model->tahun_masuk)) {
                $model->tahun_lulus_rencana = $model->tahun_masuk + 3;
            }

            if (empty($model->tahun_ajaran_kelulusan) && !empty($model->tahun_lulus_rencana)) {
                $model->tahun_ajaran_kelulusan = sprintf('%d/%d', $model->tahun_lulus_rencana - 1, $model->tahun_lulus_rencana);
            }

            if (empty($model->status)) {
                $model->status = 'aktif';
            }
        });
    }

    // =========================================================================
    // RELATIONS
    // =========================================================================

    public function jurusan(): BelongsTo
    {
        return $this->belongsTo(Jurusan::class, 'jurusan_id');
    }

    public function siswas(): HasMany
    {
        return $this->hasMany(Siswa::class, 'angkatan_id');
    }

    public function siswaAktif(): HasMany
    {
        return $this->hasMany(Siswa::class, 'angkatan_id')->where('status', 'aktif');
    }

    public function siswaLulus(): HasMany
    {
        return $this->hasMany(Siswa::class, 'angkatan_id')->where('status', 'lulus');
    }

    public function siswaProses(): HasMany
    {
        return $this->hasMany(Siswa::class, 'angkatan_id')->where('status', 'dalam_proses_kelulusan');
    }

    public function siswaTidakLulus(): HasMany
    {
        return $this->hasMany(Siswa::class, 'angkatan_id')->where('status', 'tidak_lulus');
    }

    public function siswaPindah(): HasMany
    {
        return $this->hasMany(Siswa::class, 'angkatan_id')->where('status', 'pindah');
    }

    public function siswaKeluar(): HasMany
    {
        return $this->hasMany(Siswa::class, 'angkatan_id')->where('status', 'keluar');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    public function scopeAlumni($query)
    {
        return $query->where('status', 'selesai');
    }

    public function scopeProsesKelulusan($query)
    {
        return $query->where('status', 'proses_kelulusan');
    }

    // =========================================================================
    // ACCESSORS & HELPERS
    // =========================================================================

    public function isSelesai(): bool
    {
        return $this->status === 'selesai';
    }

    public function isAktif(): bool
    {
        return $this->status === 'aktif';
    }

    public function isProsesKelulusan(): bool
    {
        return $this->status === 'proses_kelulusan';
    }

    public function getJumlahSiswaAttribute(): int
    {
        return $this->siswas()->count();
    }

    public function getJumlahSiswaAktifAttribute(): int
    {
        return $this->siswaAktif()->count();
    }

    public function getJumlahSiswaLulusAttribute(): int
    {
        return $this->siswaLulus()->count();
    }

    public function getJumlahSiswaTidakLulusAttribute(): int
    {
        return $this->siswaTidakLulus()->count();
    }

    public function getJumlahSiswaPindahAttribute(): int
    {
        return $this->siswaPindah()->count();
    }

    public function getJumlahSiswaKeluarAttribute(): int
    {
        return $this->siswaKeluar()->count();
    }

    /**
     * Hitung jumlah rombel unik yang terasosiasi dengan siswa di angkatan ini.
     */
    public function getRombelCountAttribute(): int
    {
        // Hitung dari kelas_id unik siswa yang terdaftar
        $count = $this->siswas()->whereNotNull('kelas_id')->distinct('kelas_id')->count('kelas_id');
        return $count > 0 ? $count : 1;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'selesai'          => 'Alumni / Lulus',
            'proses_kelulusan' => 'Proses Kelulusan',
            default            => 'Aktif',
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'selesai'          => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'proses_kelulusan' => 'bg-amber-50 text-amber-700 border-amber-200',
            default            => 'bg-blue-50 text-blue-700 border-blue-200',
        };
    }

    /**
     * Format display label lengkap (e.g. "Angkatan 16 (2023/2024)")
     */
    public function getDisplayTitleAttribute(): string
    {
        return "{$this->nama_angkatan} ({$this->tahun_ajaran_masuk})";
    }
}
