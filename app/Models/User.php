<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Guru;
use App\Models\OrangTua;
use App\Models\Siswa;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'jurusan_id',
        'jenis_kelamin',
    ];
    public function guru()
    {
        return $this->hasOne(Guru::class);
    }

    public function teacherActivation()
    {
        return $this->hasOne(TeacherAccountActivation::class);
    }

    public function siswa()
    {
        return $this->hasOne(Siswa::class);
    }

    public function orangTua()
    {
        return $this->hasOne(OrangTua::class);
    }
    public function parentActivation()
    {
        return $this->hasOne(ParentAccountActivation::class);
    }
    public function jurusan() { return $this->belongsTo(Jurusan::class); }

    /**
     * Salutation and Gender Helpers via SalutationService
     */
    public function getEffectiveGenderAttribute(): ?string
    {
        return app(\App\Services\SalutationService::class)->resolveGender($this);
    }

    public function getEffectiveGenderLabelAttribute(): string
    {
        return app(\App\Services\SalutationService::class)->getGenderLabel($this->effective_gender);
    }

    public function getSalutationAttribute(): string
    {
        return app(\App\Services\SalutationService::class)->getHonorific($this);
    }

    public function getSalutationNameAttribute(): string
    {
        return app(\App\Services\SalutationService::class)->getSalutationName($this);
    }

    public function getWelcomeGreetingAttribute(): string
    {
        return app(\App\Services\SalutationService::class)->getWelcomeGreeting($this, true);
    }
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
}
