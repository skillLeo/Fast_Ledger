<?php
// app/Console/Commands/TestExpiredSubscription.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestExpiredSubscription extends Command
{
    protected $signature = 'test:expire-subscription {email}';
    protected $description = 'Expire a user subscription to test sidebar visibility';

    public function handle()
    {
        $email = $this->argument('email');

        $user = DB::table('user')->where('email', $email)->first();

        if (!$user) {
            $this->error("❌ User not found: {$email}");
            return 1;
        }

        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->info("🧪 EXPIRING SUBSCRIPTION");
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");

        // Show current status
        $this->line("\n📊 BEFORE:");
        $this->table(
            ['Field', 'Value'],
            [
                ['Email', $user->email],
                ['Status', $user->subscription_status],
                ['Auto-Renewal', $user->auto_renewal == 1 ? 'ON' : 'OFF'],
                ['Next Billing', $user->next_billing_date ?? 'N/A'],
                ['Trial Ends', $user->trial_ends_at ?? 'N/A'],
            ]
        );

        // Update to expired state
        DB::table('user')
            ->where('User_ID', $user->User_ID)
            ->update([
                'subscription_status' => 'active', // Keep active
                'auto_renewal' => 0, // Turn OFF auto-renewal
                'next_billing_date' => now()->subDays(1), // Set to YESTERDAY
                'trial_ends_at' => null,
                'Modified_On' => now(),
            ]);

        // Get updated data
        $updated = DB::table('user')->where('User_ID', $user->User_ID)->first();

        $this->line("\n📊 AFTER:");
        $this->table(
            ['Field', 'Value'],
            [
                ['Email', $updated->email],
                ['Status', $updated->subscription_status],
                ['Auto-Renewal', $updated->auto_renewal == 1 ? 'ON ✅' : 'OFF ❌'],
                ['Next Billing', $updated->next_billing_date . ' (PAST)'],
                ['Trial Ends', $updated->trial_ends_at ?? 'N/A'],
            ]
        );

        $this->info("\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->warn("⚠️  SUBSCRIPTION EXPIRED!");
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        
        $this->line("\n🧪 TEST:");
        $this->line("1. Go to: http://127.0.0.1:8000");
        $this->line("2. Login as: {$email}");
        $this->line("3. Expected: NO SIDEBAR (subscription expired)");
        
        $this->info("\n✅ To RESTORE subscription, run:");
        $this->line("   php artisan test:restore-subscription {$email}");

        return 0;
    }
}