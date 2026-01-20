<?php
// app/Console/Commands/SetupAutoRenewalTest.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SetupAutoRenewalTest extends Command
{
    protected $signature = 'test:setup-renewal {email} {type} {minutes=2}';
    protected $description = 'Setup auto-renewal test scenarios';

    public function handle()
    {
        $email = $this->argument('email');
        $type = $this->argument('type'); // 'trial-on', 'trial-off', 'active-on', 'active-off'
        $minutes = (int) $this->argument('minutes');

        $user = DB::table('user')->where('email', $email)->first();

        if (!$user) {
            $this->error("❌ User not found");
            return 1;
        }

        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->info("🧪 TEST SETUP: {$type}");
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");

        switch ($type) {
            case 'trial-on':
                // TEST 1: Free trial ending with auto-renewal ON
                DB::table('user')->where('User_ID', $user->User_ID)->update([
                    'subscription_status' => 'trial',
                    'trial_ends_at' => now()->addMinutes($minutes),
                    'auto_renewal' => 1, // ✅ ON
                    'allowed_companies' => 2,
                    'payment_frequency' => 'monthly',
                ]);
                
                $this->info("✅ TEST 1 SETUP:");
                $this->line("   Status: trial");
                $this->line("   Auto-Renewal: ON (1)");
                $this->line("   Trial ends: " . now()->addMinutes($minutes));
                $this->line("   Expected: CHARGE £20 after {$minutes} min");
                break;

            case 'trial-off':
                // TEST 2: Free trial ending with auto-renewal OFF
                DB::table('user')->where('User_ID', $user->User_ID)->update([
                    'subscription_status' => 'trial',
                    'trial_ends_at' => now()->addMinutes($minutes),
                    'auto_renewal' => 0, // ❌ OFF
                    'allowed_companies' => 2,
                    'payment_frequency' => 'monthly',
                ]);
                
                $this->warn("✅ TEST 2 SETUP:");
                $this->line("   Status: trial");
                $this->line("   Auto-Renewal: OFF (0)");
                $this->line("   Trial ends: " . now()->addMinutes($minutes));
                $this->line("   Expected: EXPIRE (no charge) after {$minutes} min");
                break;

            case 'active-on':
                // TEST 3: Active subscription renewal with auto-renewal ON
                $nextBilling = now()->addMinutes($minutes);
                
                DB::table('user')->where('User_ID', $user->User_ID)->update([
                    'subscription_status' => 'active',
                    'trial_ends_at' => null,
                    'next_billing_date' => $nextBilling,
                    'auto_renewal' => 1, // ✅ ON
                    'allowed_companies' => 3,
                    'payment_frequency' => 'monthly',
                ]);
                
                $this->info("✅ TEST 3 SETUP:");
                $this->line("   Status: active");
                $this->line("   Auto-Renewal: ON (1)");
                $this->line("   Next billing: {$nextBilling}");
                $this->line("   Expected: RENEW £30 after {$minutes} min");
                break;

            case 'active-off':
                // TEST 4: Active subscription renewal with auto-renewal OFF
                $nextBilling = now()->addMinutes($minutes);
                
                DB::table('user')->where('User_ID', $user->User_ID)->update([
                    'subscription_status' => 'active',
                    'trial_ends_at' => null,
                    'next_billing_date' => $nextBilling,
                    'auto_renewal' => 0, // ❌ OFF
                    'allowed_companies' => 4,
                    'payment_frequency' => 'monthly',
                ]);
                
                $this->warn("✅ TEST 4 SETUP:");
                $this->line("   Status: active");
                $this->line("   Auto-Renewal: OFF (0)");
                $this->line("   Next billing: {$nextBilling}");
                $this->line("   Expected: EXPIRE (no charge) after {$minutes} min");
                break;

            default:
                $this->error("❌ Invalid type. Use: trial-on, trial-off, active-on, active-off");
                return 1;
        }

        // Verify
        $updated = DB::table('user')->where('User_ID', $user->User_ID)->first([
            'subscription_status',
            'auto_renewal',
            'trial_ends_at',
            'next_billing_date',
        ]);

        $this->line("\n📊 VERIFICATION:");
        $this->table(
            ['Field', 'Value'],
            [
                ['Status', $updated->subscription_status],
                ['Auto-Renewal', $updated->auto_renewal . ' (' . ($updated->auto_renewal == 1 ? 'ON' : 'OFF') . ')'],
                ['Trial Ends', $updated->trial_ends_at ?? 'N/A'],
                ['Next Billing', $updated->next_billing_date ?? 'N/A'],
            ]
        );

        $this->info("\n⏰ WAIT {$minutes} MINUTES, THEN RUN:");
        
        if (str_contains($type, 'trial')) {
            $this->line("   php artisan trial:process-endings");
        } else {
            $this->line("   php artisan subscription:process-renewals");
        }

        return 0;
    }
}