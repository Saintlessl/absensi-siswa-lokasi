<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParentAccountActivation extends Model
{
    protected $fillable = ['user_id', 'token_hash', 'expires_at', 'activated_at'];
    protected $casts = ['expires_at' => 'datetime', 'activated_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
