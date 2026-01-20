<?php
// app/Console/Commands/TestRestoreSubscription.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestRestoreSubscription extends Command
{
    protected $signature = 'test:restore-subscription {email}';
    protected $description = 'Restore subscription to active state';

    public function handle()
    {
        $email = $this->argument('email');

        $user = DB::table('user')->where('email', $email)->first();

        if (!$user) {
            $this->error("❌ User not found: {$email}");
            return 1;
        }

        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->info("✅ RESTORING SUBSCRIPTION");
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");

        // Restore to active state
        DB::table('user')
            ->where('User_ID', $user->User_ID)
            ->update([
                'subscription_status' => 'active',
                'auto_renewal' => 1, // Turn ON auto-renewal
                'next_billing_date' => now()->addMonth(), // Set to next month
                'trial_ends_at' => null,
                'Modified_On' => now(),
            ]);

        // Get updated data
        $updated = DB::table('user')->where('User_ID', $user->User_ID)->first();

        $this->line("\n📊 RESTORED:");
        $this->table(
            ['Field', 'Value'],
            [
                ['Email', $updated->email],
                ['Status', $updated->subscription_status],
                ['Auto-Renewal', $updated->auto_renewal == 1 ? 'ON ✅' : 'OFF ❌'],
                ['Next Billing', $updated->next_billing_date],
                ['Trial Ends', $updated->trial_ends_at ?? 'N/A'],
            ]
        );

        $this->info("\n✅ Subscription restored!");
        $this->line("\n🧪 TEST:");
        $this->line("1. Go to: http://127.0.0.1:8000");
        $this->line("2. Login as: {$email}");
        $this->line("3. Expected: SIDEBAR VISIBLE (active subscription)");

        return 0;
    }
}