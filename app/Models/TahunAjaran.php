<?php

namespace App\Models;

use App\Models\Kelas;
use App\Models\StudentEnrollment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TahunAjaran extends Model
{
    use HasFactory;

    protected $table = 'tahun_ajarans';

    protected $fillable = [
        'tahun_mulai',
        'tahun_selesai',
        'nama',
        'semester_aktif',
        'tanggal_mulai',
        'tanggal_selesai',
        'is_active',
        'status',
        'catatan',
        'created_by',
    ];

    protected $casts = [
        'tahun_mulai'     => 'integer',
        'tahun_selesai'   => 'integer',
        'tanggal_mulai'   => 'date',
        'tanggal_selesai' => 'date',
        'is_active'       => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (TahunAjaran $model) {
            if (empty($model->tahun_selesai) && !empty($model->tahun_mulai)) {
                $model->tahun_selesai = $model->tahun_mulai + 1;
            }

            if (empty($model->nama) && !empty($model->tahun_mulai)) {
                $model->nama = sprintf('%d/%d', $model->tahun_mulai, $model->tahun_selesai);
            }
        });

        static::saving(function (TahunAjaran $model) {
            // Enforce single active academic year across the entire school
            if ($model->is_active) {
                static::where('id', '!=', $model->id ?? 0)
                    ->where('is_active', true)
                    ->update([
                        'is_active' => false,
                        'status'    => 'selesai',
                    ]);
                $model->status = 'aktif';
            }
        });
    }

    /**
     * Get the single active academic year, with fallback to latest active class or default string.
     */
    public static function getActive(): ?self
    {
        return static::where('is_active', true)->first();
    }

    /**
     * Get the name of the active academic year (e.g. '2026/2027').
     */
    public static function getActiveName(): string
    {
        return static::getActive()?->nama ?? '2026/2027';
    }

    /**
     * Get the active semester ('ganjil' or 'genap').
     */
    public static function getActiveSemester(): string
    {
        return static::getActive()?->semester_aktif ?? 'ganjil';
    }

    /**
     * Activate this academic year, deactivating all others.
     */
    public function activate(): void
    {
        $this->is_active = true;
        $this->status = 'aktif';
        $this->save();
    }

    /**
     * Toggle the active semester between 'ganjil' and 'genap'.
     */
    public function toggleSemester(): string
    {
        $this->semester_aktif = ($this->semester_aktif === 'ganjil') ? 'genap' : 'ganjil';
        $this->save();

        return $this->semester_aktif;
    }

    /**
     * Scope query to only active academic year.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope query ordered chronologically descending.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderByDesc('tahun_mulai');
    }

    /**
     * Classes assigned to this academic year.
     */
    public function kelas(): HasMany
    {
        return $this->hasMany(Kelas::class, 'tahun_ajaran_id');
    }

    /**
     * Enrollments linked to this academic year (matched via nama e.g. '2026/2027').
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(StudentEnrollment::class, 'tahun_ajaran', 'nama');
    }

    /**
     * User who created this academic year record.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Promotion batches originated from this academic year.
     */
    public function promotionBatchesAsOrigin(): HasMany
    {
        return $this->hasMany(AcademicPromotionBatch::class, 'tahun_ajaran_asal_id');
    }

    /**
     * Promotion batches targeted into this academic year.
     */
    public function promotionBatchesAsDestination(): HasMany
    {
        return $this->hasMany(AcademicPromotionBatch::class, 'tahun_ajaran_tujuan_id');
    }
}
