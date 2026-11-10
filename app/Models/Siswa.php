<?php

namespace App\Models;

use App\Models\Absensi;
use App\Models\AbsensiVerificationLog;
use App\Models\Angkatan;
use App\Models\Kelas;
use App\Models\OrangTua;
use App\Models\SiswaFaceBiometric;
use App\Models\SiswaStatusHistory;
use App\Models\StudentEnrollment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Siswa extends Model
{
    protected $fillable = [
        'user_id',
        'nis',
        'nisn',
        'nama',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'kelas_id',
        'angkatan_id',
        'tahun_ajaran_masuk_id',
        'tahun_ajaran_masuk',
        'tahun_masuk',
        'biometric_status',
        'is_biometric_exempt',
        'status',
        'jalur_masuk',
        'asal_sekolah',
        'tanggal_kelulusan',
        'tahun_lulus',
        'tahun_ajaran_lulus',
        'catatan_status',
        'photo_path',
        'no_telp_siswa',
        'no_telp_orangtua',
    ];

    protected $casts = [
        'tanggal_lahir'        => 'date',
        'tahun_masuk'          => 'integer',
        'tahun_lulus'          => 'integer',
        'tanggal_kelulusan'    => 'date',
        'is_biometric_exempt'  => 'boolean',
    ];

    /**
     * Get formatted Tempat, Tanggal Lahir (TTL).
     */
    public function getTtlAttribute(): ?string
    {
        if ($this->tempat_lahir && $this->tanggal_lahir) {
            return "{$this->tempat_lahir}, " . $this->tanggal_lahir->translatedFormat('d F Y');
        } elseif ($this->tempat_lahir) {
            return $this->tempat_lahir;
        } elseif ($this->tanggal_lahir) {
            return $this->tanggal_lahir->translatedFormat('d F Y');
        }

        return null;
    }

    /**
     * Public URL for the student's photo, mirroring Guru::getPhotoUrlAttribute.
     */
    public function getPhotoUrlAttribute(): ?string
    {
        if ($this->photo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($this->photo_path)) {
            return asset('storage/' . $this->photo_path);
        }

        return null;
    }

    protected static function booted(): void
    {
        static::creating(function (Siswa $siswa) {
            if (empty($siswa->status)) {
                $siswa->status = 'aktif';
            }

            if (empty($siswa->angkatan_id) && !empty($siswa->kelas_id)) {
                $kelas = $siswa->kelas ?? Kelas::find($siswa->kelas_id);
                if ($kelas) {
                    $ta = $kelas->tahun_ajaran ?: '2026/2027';
                    $baseYear = (int) explode('/', $ta)[0];
                    $tingkat = (int) $kelas->tingkat ?: 10;
                    $entryYear = $baseYear - ($tingkat - 10);

                    if (empty($siswa->tahun_masuk)) {
                        $siswa->tahun_masuk = $entryYear;
                    }

                    if (class_exists(\App\Services\CohortManagementService::class)) {
                        $cohort = app(\App\Services\CohortManagementService::class)
                            ->determineCohortByYear($siswa->tahun_masuk ?: $entryYear, $kelas->jurusan_id);

                        if ($cohort) {
                            $siswa->angkatan_id = $cohort->id;
                        }
                    }
                }
            }
        });
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function jurusan(): HasOneThrough
    {
        return $this->hasOneThrough(
            Jurusan::class,
            Kelas::class,
            'id',          // Foreign key on Kelas table (matches siswas.kelas_id)
            'id',          // Foreign key on Jurusan table (matches kelas.jurusan_id)
            'kelas_id',    // Local key on Siswa table
            'jurusan_id'   // Local key on Kelas table
        );
    }

    public function getJurusanIdAttribute(): ?int
    {
        return $this->kelas?->jurusan_id ?? $this->angkatan?->jurusan_id;
    }

    public function angkatan(): BelongsTo
    {
        return $this->belongsTo(Angkatan::class, 'angkatan_id');
    }

    public function tahunAjaranMasuk(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class, 'tahun_ajaran_masuk_id');
    }

    public function getTahunAjaranMasukNamaAttribute(): string
    {
        if ($this->tahun_ajaran_masuk) {
            return $this->tahun_ajaran_masuk;
        }

        if ($this->relationLoaded('tahunAjaranMasuk') && $this->tahunAjaranMasuk) {
            return $this->tahunAjaranMasuk->nama;
        }

        if ($this->tahun_masuk) {
            return "{$this->tahun_masuk}/" . ($this->tahun_masuk + 1);
        }

        return '-';
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(SiswaStatusHistory::class, 'siswa_id')
            ->orderByDesc('tanggal_perubahan')
            ->orderByDesc('id');
    }

    public function latestStatusHistory(): HasOne
    {
        return $this->hasOne(SiswaStatusHistory::class, 'siswa_id')->latestOfMany('tanggal_perubahan');
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

    public function enrollments()
    {
        return $this->hasMany(StudentEnrollment::class);
    }

    public function activeEnrollment()
    {
        return $this->hasOne(StudentEnrollment::class)->where('is_active', true);
    }

    public function latestEnrollment(): HasOne
    {
        return $this->hasOne(StudentEnrollment::class, 'siswa_id')
            ->latestOfMany('id');
    }

    public function promotionItems(): HasMany
    {
        return $this->hasMany(AcademicPromotionItem::class, 'siswa_id');
    }

    /**
     * Rombel terakhir siswa berdasarkan histori penempatan kelas (StudentEnrollment),
     * atau fallback ke relasi kelas aktif jika histori belum tercatat.
     */
    public function getRombelTerakhirAttribute(): ?Kelas
    {
        if ($this->relationLoaded('latestEnrollment') && $this->latestEnrollment?->kelas) {
            return $this->latestEnrollment->kelas;
        }

        if ($this->latestEnrollment?->kelas) {
            return $this->latestEnrollment->kelas;
        }

        return $this->kelas;
    }

    // =========================================================================
    // STATUS SCOPES & HELPERS
    // =========================================================================

    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    public function scopeLulus($query)
    {
        return $query->where('status', 'lulus');
    }

    public function scopeAlumni($query)
    {
        return $query->where('status', 'lulus');
    }

    public function scopeDalamProses($query)
    {
        return $query->where('status', 'dalam_proses_kelulusan');
    }

    public function scopeTidakLulus($query)
    {
        return $query->where('status', 'tidak_lulus');
    }

    public function scopePindah($query)
    {
        return $query->where('status', 'pindah');
    }

    public function scopeKeluar($query)
    {
        return $query->where('status', 'keluar');
    }

    public function scopeByAngkatan($query, $angkatanId)
    {
        return $query->where('angkatan_id', $angkatanId);
    }

    public function isAktif(): bool
    {
        return $this->status === 'aktif';
    }

    public function isLulus(): bool
    {
        return $this->status === 'lulus';
    }

    public function isAlumni(): bool
    {
        return $this->status === 'lulus';
    }

    public function isDalamProses(): bool
    {
        return $this->status === 'dalam_proses_kelulusan';
    }

    public function isTidakLulus(): bool
    {
        return $this->status === 'tidak_lulus';
    }

    public function isPindah(): bool
    {
        return $this->status === 'pindah';
    }

    public function isKeluar(): bool
    {
        return $this->status === 'keluar';
    }

    /**
     * Siswa yang diizinkan melakukan presensi aktif.
     */
    public function canAttend(): bool
    {
        return in_array($this->status, ['aktif', 'dalam_proses_kelulusan']);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'aktif'                  => 'Aktif',
            'dalam_proses_kelulusan' => 'Proses Kelulusan',
            'lulus'                  => 'Lulus (Alumni)',
            'tidak_lulus'            => 'Tidak Lulus',
            'pindah'                 => 'Pindah Sekolah',
            'keluar'                 => 'Keluar',
            default                  => ucfirst(str_replace('_', ' ', $this->status ?? 'aktif')),
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'aktif'                  => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'dalam_proses_kelulusan' => 'bg-amber-50 text-amber-700 border-amber-200',
            'lulus'                  => 'bg-blue-50 text-[#003EA7] border-blue-200 font-bold',
            'tidak_lulus'            => 'bg-rose-50 text-rose-700 border-rose-200',
            'pindah'                 => 'bg-purple-50 text-purple-700 border-purple-200',
            'keluar'                 => 'bg-slate-100 text-slate-700 border-slate-300',
            default                  => 'bg-slate-100 text-slate-700 border-slate-200',
        };
    }

    /**
     * Check if student is fully enrolled with a valid biometric template
     */
    public function isBiometricEnrolled(): bool
    {
        if ($this->is_biometric_exempt) {
            return true;
        }

        return $this->biometric_status === 'enrolled'
            && $this->faceBiometric()
                ->where('is_active', true)
                ->where('enrollment_status', 'enrolled')
                ->exists();
    }

    public function isFaceRegistered(): bool
    {
        if ($this->is_biometric_exempt) {
            return true;
        }

        return $this->isBiometricEnrolled();
    }

    public function getGenderLabelAttribute(): string
    {
        return app(\App\Services\SalutationService::class)->getGenderLabel($this->jenis_kelamin);
    }

    public function getSalutationAttribute(): string
    {
        return '';
    }

    public function getSalutationNameAttribute(): string
    {
        return $this->nama;
    }
}
