<?php

namespace App\Models;

use App\Models\Jadwal;
use App\Models\Jurusan;
use App\Models\Siswa;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    protected $table = 'kelas';

    protected $fillable = [
        'jurusan_id',
        'qr_token',
        'nama_kelas',
        'jumlah_siswa',
        'wali_kelas'
        ,'tingkat','nomor_rombel','tahun_ajaran','is_active'
    ];

    public function jurusan()
    {
        return $this->belongsTo(Jurusan::class);
    }

    public function siswa()
    {
        return $this->hasMany(Siswa::class);
    }

    public function jadwal()
    {
        return $this->hasMany(Jadwal::class);
    }
    public function enrollments() { return $this->hasMany(StudentEnrollment::class); }
    public function roomAssignments() { return $this->hasMany(RoomAssignment::class); }
    public function homeroomAssignments() { return $this->hasMany(HomeroomAssignment::class); }
    public function activeHomeroom() { return $this->hasOne(HomeroomAssignment::class)->where('is_active', true)->with('guru'); }
    public function subjectAssignments() { return $this->hasMany(TeacherSubjectAssignment::class); }
}
