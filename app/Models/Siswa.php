<?php

namespace App\Models;

use App\Models\Absensi;
use App\Models\Kelas;
use App\Models\OrangTua;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    protected $fillable = [
        'user_id',
        'nis',
        'nama',
        'jenis_kelamin',
        'kelas_id'
    ];


    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function absensi()
    {
        return $this->hasMany(Absensi::class);
    }

    public function suratPeringatan()
    {
        return $this->hasMany(SuratPeringatan::class);
    }

    public function getLatestSP()
    {
        $latest = $this->suratPeringatan()->orderBy('tanggal_terbit', 'desc')->first();
        return $latest ? $latest->jenis_sp : '-';
    }

    public function orangtua()
    {
        return $this->hasOne(OrangTua::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getAlphaCount($month = null, $year = null)
    {
        $month = $month ?? date('m');
        $year = $year ?? date('Y');

        // Count explicit Alpa records
        $alfaDb = $this->absensi()
            ->whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year)
            ->whereRaw('LOWER(status) = ?', ['alfa'])
            ->count();

        // Count missing days (days with no record)
        $registrationDate = $this->created_at ? \Carbon\Carbon::parse($this->created_at)->startOfDay() : null;
        $startDate = \Carbon\Carbon::createFromDate($year, $month, 1)->startOfMonth();
        
        // If the student was registered after the start of the month, 
        // we only start counting from their registration date.
        if ($registrationDate && $registrationDate->gt($startDate)) {
            $startDate = $registrationDate->copy();
        }

        $endDate = ($month == date('m') && $year == date('Y'))
            ? \Carbon\Carbon::now()->startOfDay()
            : \Carbon\Carbon::createFromDate($year, $month, 1)->endOfMonth()->startOfDay();

        $hariEfektif = 0;
        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            if ($currentDate->dayOfWeek !== \Carbon\CarbonInterface::SUNDAY) {
                $hariEfektif++;
            }
            $currentDate->addDay();
        }

        $totalTercatat = $this->absensi()
            ->whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year)
            ->count();

        $missingDays = max(0, $hariEfektif - $totalTercatat);

        return $alfaDb + $missingDays;
    }

    /**
     * Get total alpha for a specific month (defaults to current month)
     * as per user request to calculate SP based on monthly alpha.
     */
    public function getTotalAlpha($month = null, $year = null)
    {
        $month = $month ?? date('m');
        $year = $year ?? date('Y');

        return $this->getAlphaCount($month, $year);
    }
}
