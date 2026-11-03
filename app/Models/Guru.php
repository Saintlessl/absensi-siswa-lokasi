<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
     */
    public function getIsKaprogAttribute(): bool
    {
        return $this->user && ($this->user->role === 'kaprog' || !is_null($this->user->jurusan_id));
    }

    /**
     * Get department managed if KAPROG
     */
    public function getKaprogJurusanAttribute()
    {
        return $this->user?->jurusan;
    }
}
