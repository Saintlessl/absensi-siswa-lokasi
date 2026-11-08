<?php

namespace App\Models;

use App\Models\Angkatan;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentEnrollment extends Model
{
    use HasFactory;

    protected $table = 'student_enrollments';

    protected $fillable = [
        'siswa_id',
        'tahun_ajaran_id',
        'kelas_id',
        'jurusan_id',
        'angkatan_id',
        'tingkat',
        'tahun_ajaran',
        'started_at',
        'ended_at',
        'is_active',
        'status_enrollment',
        'catatan',
        'homeroom_assignment_id',
        'wali_kelas_guru_id',
        'wali_kelas_nama',
        'status_verifikasi_histori',
    ];

    protected $casts = [
        'tahun_ajaran_id' => 'integer',
        'kelas_id'        => 'integer',
        'jurusan_id'      => 'integer',
        'angkatan_id'     => 'integer',
        'tingkat'         => 'integer',
        'started_at'      => 'date',
        'ended_at'        => 'date',
        'is_active'       => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (StudentEnrollment $enrollment) {
            if (empty($enrollment->started_at)) {
                $enrollment->started_at = now();
            }
        });
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function jurusan(): BelongsTo
    {
        return $this->belongsTo(Jurusan::class, 'jurusan_id');
    }

    public function angkatan(): BelongsTo
    {
        return $this->belongsTo(Angkatan::class, 'angkatan_id');
    }

    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class, 'tahun_ajaran_id');
    }

    public function homeroomAssignment()
    {
        return $this->hasOne(HomeroomAssignment::class, 'kelas_id', 'kelas_id')
            ->where(function ($q) {
                if ($this->tahun_ajaran_id) {
                    $q->where('tahun_ajaran_id', $this->tahun_ajaran_id);
                } else {
                    $q->where('tahun_ajaran', $this->tahun_ajaran);
                }
            })
            ->orderByDesc('is_active');
    }

    public function waliKelasGuru(): BelongsTo
    {
        return $this->belongsTo(Guru::class, 'wali_kelas_guru_id');
    }

    /**
     * Resolve the assigned homeroom teacher for this class in this specific academic year.
     * Prefers immutable historical snapshot when available, preventing past enrollments
     * from changing if homeroom assignment or teacher name is modified in the future.
     */
    public function getWaliKelasNamaAttribute(): string
    {
        // 1. Immutable Historical Snapshot check
        if (!empty($this->attributes['wali_kelas_nama'])) {
            return $this->attributes['wali_kelas_nama'];
        }

        // 2. Check HomeroomAssignment for this class & academic year
        $assignment = HomeroomAssignment::with('guru')
            ->where('kelas_id', $this->kelas_id)
            ->where(function ($q) {
                if ($this->tahun_ajaran_id) {
                    $q->where('tahun_ajaran_id', $this->tahun_ajaran_id);
                }
                if ($this->tahun_ajaran) {
                    $q->orWhere('tahun_ajaran', $this->tahun_ajaran);
                }
            })
            ->orderByDesc('is_active')
            ->first();

        if ($assignment && $assignment->guru) {
            return $assignment->guru->nama;
        }

        // 3. Fallback to Kelas string wali_kelas if any
        return $this->kelas?->wali_kelas ?: '-';
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status_enrollment) {
            'aktif'         => 'Aktif Berjalan',
            'selesai'       => 'Selesai (Naik Kelas)',
            'tinggal_kelas' => 'Tinggal Kelas',
            'lulus'         => 'Lulus',
            'mutasi'        => 'Mutasi Keluar',
            'pindah'        => 'Pindah Sekolah',
            'keluar'        => 'Keluar',
            default         => ucfirst(str_replace('_', ' ', $this->status_enrollment ?? 'aktif')),
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status_enrollment) {
            'aktif'         => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'selesai'       => 'bg-blue-50 text-blue-700 border-blue-200',
            'tinggal_kelas' => 'bg-amber-50 text-amber-700 border-amber-200',
            'lulus'         => 'bg-purple-50 text-purple-700 border-purple-200',
            'mutasi', 'pindah', 'keluar' => 'bg-rose-50 text-rose-700 border-rose-200',
            default         => 'bg-slate-50 text-slate-700 border-slate-200',
        };
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
