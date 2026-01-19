<?php
// app/Console/Kernel.php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // Check for trial endings every day at midnight
        $schedule->command('trial:process-endings')
            ->daily()
            ->at('00:00')
            ->timezone('Europe/London');

        // Also check every hour during business hours (optional)
        $schedule->command('trial:process-endings')
            ->hourly()
            ->between('9:00', '18:00')
            ->timezone('Europe/London');
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}