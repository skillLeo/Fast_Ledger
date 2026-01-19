<?php
// app/Console/Commands/SetTestTrial.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class SetTestTrial extends Command
{
    protected $signature = 'trial:set-test {email} {--minutes=5}';
    protected $description = 'Set a user trial to end in X minutes for testing';

    public function handle()
    {
        $email = $this->argument('email');
        $minutes = (int) $this->option('minutes'); // ✅ CAST TO INTEGER

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("❌ User not found: {$email}");
            return 1;
        }

        if ($user->subscription_status !== 'trial') {
            $this->error("❌ User is not on trial");
            $this->line("   Current status: {$user->subscription_status}");
            return 1;
        }

        // Set trial to end in X minutes
        $newEndTime = now()->addMinutes($minutes);
        
        $user->update([
            'trial_ends_at' => $newEndTime,
        ]);

        $this->info("✅ Trial period updated!");
        $this->line("   User: {$user->Full_Name} ({$user->email})");
        $this->line("   Old trial end: {$user->getOriginal('trial_ends_at')}");
        $this->line("   New trial end: {$newEndTime->format('Y-m-d H:i:s')}");
        $this->line("   Time until end: {$minutes} minutes");
        $this->info("\n🕒 Wait {$minutes} minutes, then run:");
        $this->line("   php artisan trial:process-endings");

        return 0;
    }
}