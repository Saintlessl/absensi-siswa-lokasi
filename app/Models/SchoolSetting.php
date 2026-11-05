<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Key-value store for school-wide settings (e.g. jam_masuk, jam_pulang).
 * Replaces hardcoded attendance deadlines in controllers.
 */
class SchoolSetting extends Model
{
    protected $fillable = ['key', 'value'];

    /**
     * Read a setting value with a fallback default.
     */
    public static function value(string $key, ?string $default = null): ?string
    {
        return static::query()->where('key', $key)->value('value') ?? $default;
    }
}