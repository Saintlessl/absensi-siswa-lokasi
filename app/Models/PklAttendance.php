<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PklAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'pkl_placement_id',
        'tanggal',
        'jam_masuk',
        'jam_pulang',
        'latitude',
        'longitude',
        'distance_meters',
        'is_location_valid',
        'status',
        'foto_selfie_path',
        'catatan',
    ];

    protected $casts = [
        'tanggal'           => 'date',
        'latitude'          => 'float',
        'longitude'         => 'float',
        'distance_meters'   => 'integer',
        'is_location_valid' => 'boolean',
    ];

    public function placement()
    {
        return $this->belongsTo(PklPlacement::class, 'pkl_placement_id');
    }
}
