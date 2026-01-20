<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;



Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();






// ✅ Laravel 11: Schedule commands here (no Kernel.php)

// Process trial endings daily at 2 AM
Schedule::command('trial:process-endings')
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->onSuccess(function () {
        \Log::info('✅ Trial endings processed successfully');
    })
    ->onFailure(function () {
        \Log::error('❌ Trial endings processing failed');
    });

// Process subscription renewals daily at 3 AM
Schedule::command('subscription:process-renewals')
    ->dailyAt('03:00')
    ->withoutOverlapping()
    ->onSuccess(function () {
        \Log::info('✅ Subscription renewals processed successfully');
    })
    ->onFailure(function () {
        \Log::error('❌ Subscription renewals processing failed');
    });