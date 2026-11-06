<?php

namespace App\Helpers;

use App\Models\Setting;

class SettingHelper
{
    /**
     * Get a setting value by key, with a default fallback.
     */
    public static function get(string $key, $default = null)
    {
        $setting = Setting::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Set a setting value by key (create or update).
     */
    public static function set(string $key, $value): void
    {
        Setting::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    /**
     * Get alpha quota for SP thresholds.
     */
    public static function getAlphaQuota(string $sp = 'SP1'): int
    {
        return (int) self::get("kuota_alpha_{$sp}", match ($sp) {
            'SP1' => 5,
            'SP2' => 10,
            'SP3' => 15,
            default => 5,
        });
    }
    /**
     * Get late threshold time (default 07:30).
     */
    public static function getLateThreshold(): string
    {
        return self::get('late_threshold', '07:30:00');
    }
}
