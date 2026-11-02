<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class StudentEnrollment extends Model {
    protected $fillable = ['siswa_id','kelas_id','tahun_ajaran','started_at','ended_at','is_active'];
    protected $casts = ['started_at'=>'date','ended_at'=>'date','is_active'=>'boolean'];
    public function siswa() { return $this->belongsTo(Siswa::class); }
    public function kelas() { return $this->belongsTo(Kelas::class); }
}
