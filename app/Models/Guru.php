<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;

class Guru extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nip',
        'nuptk',
        'nama',
        'mapel',
        'jenis_kelamin',
        'telepon',
        'alamat',
        'photo_path',
        'status_kepegawaian',
        'is_active',
        'catatan',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function absensiGurus(): HasMany
    {
        return $this->hasMany(AbsensiGuru::class, 'guru_id');
    }

    public function izins(): HasMany
    {
        return $this->hasMany(Izin::class, 'guru_id');
    }

    public function jadwal()
    {
        return $this->hasMany(Jadwal::class);
    }

    public function activeJadwal()
    {
        return $this->hasMany(Jadwal::class)->where('is_active', true);
    }

    public function subjectAssignments()
    {
        return $this->hasMany(TeacherSubjectAssignment::class, 'guru_id');
    }

    public function activeSubjectAssignments()
    {
        return $this->hasMany(TeacherSubjectAssignment::class, 'guru_id')
            ->where('is_active', true)
            ->with(['mataPelajaran', 'kelas.jurusan']);
    }

    public function homeroomAssignments()
    {
        return $this->hasMany(HomeroomAssignment::class, 'guru_id')->latest();
    }

    public function activeHomeroomAssignment()
    {
        return $this->hasOne(HomeroomAssignment::class, 'guru_id')
            ->where('is_active', true)
            ->with(['kelas.jurusan']);
    }

    public function kaprogAssignments(): HasMany
    {
        return $this->hasMany(KaprogAssignment::class, 'guru_id')->latest();
    }

    public function activeKaprogAssignment()
    {
        return $this->hasOne(KaprogAssignment::class, 'guru_id')
            ->where('is_active', true)
            ->with('jurusan');
    }

    public function getPhotoUrlAttribute(): ?string
    {
        if ($this->photo_path && Storage::disk('public')->exists($this->photo_path)) {
            return asset('storage/' . $this->photo_path);
        }
        return null;
    }

    /**
     * Check if teacher is currently an active homeroom teacher (Wali Kelas)
     */
    public function getIsHomeroomAttribute(): bool
    {
        return $this->homeroomAssignments()->where('is_active', true)->exists();
    }

    /**
     * Check if teacher is currently a KAPROG
     *
     * Relies on the historical assignment table only. Previously this also
     * treated any users.jurusan_id as a KAPROG marker, which over-counted
     * kaprog teachers in the workload report.
     */
    public function getIsKaprogAttribute(): bool
    {
        return $this->kaprogAssignments()->where('is_active', true)->exists();
    }

    /**
     * Get department managed if KAPROG
     */
    public function getKaprogJurusanAttribute()
    {
        return $this->activeKaprogAssignment?->jurusan ?? $this->user?->jurusan;
    }

    public function getGenderLabelAttribute(): string
    {
        return app(\App\Services\SalutationService::class)->getGenderLabel($this->jenis_kelamin);
    }

    public function getSalutationAttribute(): string
    {
        if ($this->jenis_kelamin === 'L') {
            return 'Pak';
        }
        if ($this->jenis_kelamin === 'P') {
            return 'Bu';
        }
        return '';
    }

    public function getSalutationNameAttribute(): string
    {
        return app(\App\Services\SalutationService::class)->forGuru($this);
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class, 'guru_id');
    }

    public function teachingJournals(): HasMany
    {
        return $this->hasMany(TeachingJournal::class, 'guru_id');
    }

    public function studentEnrollments(): HasMany
    {
        return $this->hasMany(StudentEnrollment::class, 'wali_kelas_guru_id');
    }

    /**
     * Check whether teacher has any historical academic records that must be protected.
     */
    public function hasAcademicRecords(): bool
    {
        return $this->assessments()->exists()
            || $this->teachingJournals()->exists()
            || $this->homeroomAssignments()->exists()
            || $this->subjectAssignments()->exists()
            || $this->studentEnrollments()->exists();
    }

    /**
     * Detailed breakdown of historical academic records.
     */
    public function getAcademicRecordsSummary(): array
    {
        $assessmentsCount = $this->assessments()->count();
        $assessmentIds = $this->assessments()->pluck('id');
        $scoresCount = AssessmentScore::whereIn('assessment_id', $assessmentIds)->count();
        $journalsCount = $this->teachingJournals()->count();
        $homeroomCount = $this->homeroomAssignments()->count();
        $subjectCount = $this->subjectAssignments()->count();

        return [
            'assessments' => $assessmentsCount,
            'scores'      => $scoresCount,
            'journals'    => $journalsCount,
            'homeroom'    => $homeroomCount,
            'subject'     => $subjectCount,
            'has_records' => ($assessmentsCount > 0 || $journalsCount > 0 || $homeroomCount > 0 || $subjectCount > 0),
        ];
    }
}
