<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Room extends Model {
    protected $fillable = ['name','code','building','capacity','type','description','qr_token','qr_is_active','is_active'];
    protected $casts = ['qr_is_active'=>'boolean','is_active'=>'boolean'];
    public function assignments() { return $this->hasMany(RoomAssignment::class); }
    public function scopeActive($query) { return $query->where('is_active', true); }
}
