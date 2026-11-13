<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * Key-value store for school-wide settings (e.g. jam_masuk, jam_pulang, app_logo, app_favicon, nama_sekolah).
 * Replaces hardcoded attendance deadlines and branding paths in views and controllers.
 */
class SchoolSetting extends Model
{
    protected $fillable = ['key', 'value'];

    /**
     * Cache in-memory settings within a single request to avoid duplicate SQL queries.
     */
    private static ?array $cachedSettings = null;

    /**
     * Clear cached settings in current request lifecycle.
     */
    public static function clearSettingCache(): void
    {
        static::$cachedSettings = null;
    }

    /**
     * Read a setting value with a fallback default.
     */
    public static function value(string $key, ?string $default = null): ?string
    {
        if (static::$cachedSettings === null) {
            try {
                static::$cachedSettings = static::query()->pluck('value', 'key')->toArray();
            } catch (\Throwable $e) {
                static::$cachedSettings = [];
            }
        }

        return static::$cachedSettings[$key] ?? $default;
    }

    /**
     * Get the resolved URL for the main application / sidebar logo.
     * Falls back to default asset ('img/school-crest.png') if not set or missing.
     */
    public static function getLogoUrl(): string
    {
        $path = static::value('app_logo');
        if ($path && Storage::disk('public')->exists($path)) {
            return asset('storage/' . $path);
        }

        return asset('img/school-crest.png');
    }

    /**
     * Get the resolved URL for the application favicon.
     * Falls back to default asset ('img/logo.png') if not set or missing.
     */
    public static function getFaviconUrl(): string
    {
        $path = static::value('app_favicon');
        if ($path && Storage::disk('public')->exists($path)) {
            return asset('storage/' . $path);
        }

        return asset('img/logo.png');
    }

    /**
     * Check if a custom logo is currently set and exists on disk.
     */
    public static function hasCustomLogo(): bool
    {
        $path = static::value('app_logo');
        return !empty($path) && Storage::disk('public')->exists($path);
    }

    /**
     * Check if a custom favicon is currently set and exists on disk.
     */
    public static function hasCustomFavicon(): bool
    {
        $path = static::value('app_favicon');
        return !empty($path) && Storage::disk('public')->exists($path);
    }
}
