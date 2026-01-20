<?php
// app/Console/Kernel.php

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
        // ✅ Process trial endings daily at 2 AM
        $schedule->command('trial:process-endings')
            ->dailyAt('02:00')
            ->withoutOverlapping()
            ->onSuccess(function () {
                \Log::info('Trial endings processed successfully');
            })
            ->onFailure(function () {
                \Log::error('Trial endings processing failed');
            });

        // ✅ Process subscription renewals daily at 3 AM
        $schedule->command('subscription:process-renewals')
            ->dailyAt('03:00')
            ->withoutOverlapping()
            ->onSuccess(function () {
                \Log::info('Subscription renewals processed successfully');
            })
            ->onFailure(function () {
                \Log::error('Subscription renewals processing failed');
            });
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