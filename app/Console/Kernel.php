<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // BUG: the alpa generator existed but was never registered here, so no
        // automatic alfa was ever created. Runs after the school day closes and
        // only covers the previous working day (see GenerateAlpaDaily).
        $schedule->command('absensi:generate-alpa')
            ->dailyAt('16:00')
            ->timezone('Asia/Jakarta')
            ->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
