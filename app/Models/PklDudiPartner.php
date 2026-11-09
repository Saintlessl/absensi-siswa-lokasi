<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PklDudiPartner extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_dudi',
        'bidang_industri',
        'alamat',
        'kota',
        'kontak_person',
        'email',
        'telepon',
        'pembimbing_dudi',
        'latitude',
        'longitude',
        'radius_meter',
        'kuota_siswa',
        'jurusan_id',
        'status',
    ];

    protected $casts = [
        'latitude'     => 'float',
        'longitude'    => 'float',
        'radius_meter' => 'integer',
        'kuota_siswa'  => 'integer',
    ];

    public function jurusan()
    {
        return $this->belongsTo(Jurusan::class);
    }

    public function placements()
    {
        return $this->hasMany(PklPlacement::class);
    }

    /**
     * Calculate distance in meters using Haversine formula
     */
    public function getDistanceFrom($userLat, $userLng): float
    {
        if (is_null($this->latitude) || is_null($this->longitude)) {
            return 0.0;
        }

        $earthRadius = 6371000; // in meters
        $latFrom = deg2rad((float)$userLat);
        $lonFrom = deg2rad((float)$userLng);
        $latTo = deg2rad((float)$this->latitude);
        $lonTo = deg2rad((float)$this->longitude);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return round($angle * $earthRadius);
    }

    /**
     * Check if given coordinates are within radius
     */
    public function isWithinRadius($userLat, $userLng): bool
    {
        if (is_null($this->latitude) || is_null($this->longitude)) {
            return true; // No coordinates set means geofence not enforced
        }

        return $this->getDistanceFrom($userLat, $userLng) <= ($this->radius_meter ?? 150);
    }
}
