<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'is_system',
        'data_scope',
        'status',
    ];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permissions')
            ->withPivot('data_scope')
            ->withTimestamps();
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Clear cached permission array for this role
     */
    public function flushPermissionCache(): void
    {
        Cache::forget("role_permissions_{$this->id}");
        Cache::forget("role_permissions_{$this->name}");
    }

    /**
     * Get cached array of permission names for this role
     */
    public function getCachedPermissions(): array
    {
        return Cache::rememberForever("role_permissions_{$this->id}", function () {
            return $this->permissions()->pluck('name')->toArray();
        });
    }

    /**
     * Check if role has permission
     */
    public function hasPermission(string $permissionName): bool
    {
        if ($this->name === 'superadmin') {
            return true;
        }

        return in_array($permissionName, $this->getCachedPermissions(), true);
    }

    protected static function booted()
    {
        static::saved(function ($role) {
            $role->flushPermissionCache();
        });

        static::deleted(function ($role) {
            $role->flushPermissionCache();
        });
    }
}
