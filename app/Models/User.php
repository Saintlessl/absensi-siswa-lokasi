<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Guru;
use App\Models\OrangTua;
use App\Models\Role;
use App\Models\Siswa;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'must_change_password',
        'role',
        'role_id',
        'status',
        'jurusan_id',
        'jenis_kelamin',
    ];

    public function roleRel(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

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

    public function jurusan()
    {
        return $this->belongsTo(Jurusan::class);
    }

    /**
     * Check if user has given permission.
     * Superadmin always returns true.
     */
    public function hasPermission(string $permissionName): bool
    {
        // 1. Superadmin role always has absolute access
        if ($this->role === 'superadmin' || ($this->roleRel && $this->roleRel->name === 'superadmin')) {
            return true;
        }

        // 2. Check through linked Role model
        if ($this->role_id && $this->roleRel) {
            return $this->roleRel->hasPermission($permissionName);
        }

        // 3. Fallback: resolve role by string name
        $roleModel = Role::where('name', $this->role)->first();
        if ($roleModel) {
            return $roleModel->hasPermission($permissionName);
        }

        return false;
    }

    /**
     * Check if user has any of the given roles.
     * Accepts string or array of role names.
     */
    public function hasRole($roles): bool
    {
        if (is_string($roles)) {
            $roles = array_map('trim', explode(',', $roles));
        }

        $currentRoles = array_filter([
            $this->role,
            $this->roleRel?->name,
        ]);

        return !empty(array_intersect($currentRoles, (array) $roles));
    }

    /**
     * Determine user's effective data scope for queries.
     * Returns: 'all' | 'department' | 'assigned' | 'own'
     */
    public function getDataScope(?string $permissionName = null): string
    {
        if ($this->hasRole(['superadmin', 'admin'])) {
            return 'all';
        }

        if ($this->hasRole('kaprog')) {
            return 'department';
        }

        if ($this->hasRole('guru')) {
            return 'assigned';
        }

        return 'own';
    }

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
        'must_change_password' => 'boolean',
    ];
}
