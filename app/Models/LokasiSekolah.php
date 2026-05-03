<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LokasiSekolah extends Model
{
    protected $fillable = [
        'latitude',
        'longitude',
        'radius'
    ];
}
