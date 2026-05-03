<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Siswa;
use App\Models\Absensi;
use Carbon\Carbon;

class GenerateAlpaDaily extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'absensi:generate-alpa';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate status Alpa otomatis untuk siswa yang tidak absen pada hari ini (Kecuali Minggu) atau dalam rentang bulan ini';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Mulai pengecekan Alpa harian...');

        // Kita bisa atur dari tanggal 1 di bulan ini, sampai ke hari ini
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now(); // Sampai batas hari ini

        $siswas = Siswa::all();
        $insertedCount = 0;

        foreach ($siswas as $siswa) {
            // Loop dari awal bulan sampai hari ini
            $currentDate = $startDate->copy();
            
            while ($currentDate->lte($endDate)) {
                // Lewati hari Minggu (dayOfWeek 0)
                if ($currentDate->dayOfWeek == Carbon::SUNDAY) {
                    $currentDate->addDay();
                    continue;
                }

                $tanggalStr = $currentDate->format('Y-m-d');

                // Cek apakah siswa ini sudah punya data (hadir/izin/sakit/alfa) di tanggal ini
                $hasRecord = Absensi::where('siswa_id', $siswa->id)
                                    ->whereDate('tanggal', $tanggalStr)
                                    ->exists();

                // Jika belum ada rekam sama sekali, kita inject 'alfa'
                if (!$hasRecord) {
                    Absensi::create([
                        'siswa_id' => $siswa->id,
                        'tanggal' => $tanggalStr,
                        'jam_masuk' => null,
                        'status' => 'alfa',
                        'keterangan' => 'Sistem Alpa Otomatis (Tidak ada rekam kehadiran)',
                        'latitude' => null,
                        'longitude' => null,
                    ]);
                    $insertedCount++;
                }

                $currentDate->addDay();
            }
        }

        $this->info("Berhasil! Menambahkan $insertedCount rekam Alpa otomatis.");
    }
}
