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
    protected $signature = 'absensi:generate-alpa {--date= : Tanggal tunggal (Y-m-d) yang akan diproses}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate status Alpa otomatis untuk siswa tanpa rekam kehadiran pada hari kerja yang sudah selesai';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Only fully-elapsed working days are eligible. The previous version
        // looped from the 1st of the month through *today*, so a morning run
        // marked every student who had not checked in yet as alpa, and granted
        // alfa to every earlier weekday including holidays.
        $target = $this->option('date')
            ? Carbon::parse($this->option('date'))->startOfDay()
            : Carbon::yesterday()->startOfDay();

        if ($target->isSunday()) {
            $this->info("Tanggal {$target->toDateString()} adalah hari Minggu, tidak ada alpa yang dibuat.");
            return self::SUCCESS;
        }

        if ($target->isFuture()) {
            $this->warn('Tidak dapat membuat alpa untuk tanggal yang belum berjalan.');
            return self::FAILURE;
        }

        $tanggalStr = $target->toDateString();
        $this->info("Memproses alpa untuk tanggal {$tanggalStr}...");

        $insertedCount = 0;
        $skipped = 0;

        // Skip students who already have a record that day. Filtering in SQL
        // replaces the previous day-by-day loop (one query per student per day).
        $siswas = Siswa::query()
            ->whereDoesntHave('absensi', fn ($q) => $q->whereDate('tanggal', $tanggalStr))
            ->get();

        foreach ($siswas as $siswa) {
            try {
                Absensi::create([
                    'siswa_id'   => $siswa->id,
                    'tanggal'    => $tanggalStr,
                    'jam_masuk'  => null,
                    'status'     => 'alfa',
                    'keterangan' => 'Sistem Alpa Otomatis (Tidak ada rekam kehadiran)',
                    'latitude'   => null,
                    'longitude'  => null,
                ]);
                $insertedCount++;
            } catch (UniqueConstraintViolationException $e) {
                // UNIQUE(siswa_id, tanggal) — another process got there first.
                $skipped++;
            }
        }

        $this->info("Selesai. {$insertedCount} rekam alpa dibuat, {$skipped} dilewati (sudah ada).");

        return self::SUCCESS;
    }
}
