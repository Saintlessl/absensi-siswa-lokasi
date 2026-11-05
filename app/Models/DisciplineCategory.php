<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DisciplineCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'tipe',
        'default_poin',
        'is_active',
    ];

    protected $casts = [
        'default_poin' => 'integer',
        'is_active'    => 'boolean',
    ];

    public function records()
    {
        return $this->hasMany(DisciplineRecord::class);
    }
}
