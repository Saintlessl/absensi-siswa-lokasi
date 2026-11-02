<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class RoomAssignment extends Model {
    protected $fillable = ['room_id','kelas_id','day_of_week','start_time','end_time','starts_on','ends_on','tahun_ajaran','status','created_by'];
    protected $casts = ['starts_on'=>'date','ends_on'=>'date'];
    public function room() { return $this->belongsTo(Room::class); }
    public function kelas() { return $this->belongsTo(Kelas::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
}
