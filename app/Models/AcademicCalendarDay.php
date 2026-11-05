<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicCalendarDay extends Model
{
    protected $fillable = ['tanggal', 'nama_kegiatan', 'is_active'];

    protected $casts = [
        'tanggal' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Whether a given day is an active non-effective school day.
     */
    public static function isNonEffective(string $tanggal): bool
    {
        return static::query()
            ->whereDate('tanggal', $tanggal)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * All active non-effective days within a date range, keyed by Y-m-d.
     */
    public static function nonEffectiveWithin(string $from, string $to): array
    {
        return static::query()
            ->whereBetween('tanggal', [$from, $to])
            ->where('is_active', true)
            ->pluck('nama_kegiatan', 'tanggal')
            ->mapWithKeys(fn ($v, $k) => [substr((string) $k, 0, 10) => $v])
            ->all();
    }
}
