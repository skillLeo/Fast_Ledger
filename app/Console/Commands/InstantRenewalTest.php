<?php
// app/Console/Commands/InstantRenewalTest.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class InstantRenewalTest extends Command
{
    protected $signature = 'test:instant-renewal {email} {type}';
    protected $description = 'Run instant auto-renewal tests (no waiting)';

    public function handle()
    {
        $email = $this->argument('email');
        $type = $this->argument('type'); // 'trial-on', 'trial-off', 'active-on', 'active-off'

        $user = DB::table('user')->where('email', $email)->first();

        if (!$user) {
            $this->error("❌ User not found: {$email}");
            return 1;
        }

        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->info("⚡ INSTANT TEST: {$type}");
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");

        // ✅ Set dates to 1 SECOND AGO (already expired)
        $expiredTime = now()->subSeconds(1);

        switch ($type) {
            case 'trial-on':
                // TEST 1: Trial expired 1 second ago, auto-renewal ON
                DB::table('user')->where('User_ID', $user->User_ID)->update([
                    'subscription_status' => 'trial',
                    'trial_ends_at' => $expiredTime,
                    'auto_renewal' => 1,
                    'allowed_companies' => 2,
                    'payment_frequency' => 'monthly',
                    'stripe_payment_method_id' => $user->stripe_payment_method_id,
                    'stripe_customer_id' => $user->stripe_customer_id,
                ]);
                
                $this->info("✅ SETUP:");
                $this->line("   Trial ended: {$expiredTime}");
                $this->line("   Auto-Renewal: ON");
                $this->line("   Expected: CHARGE £20");
                
                sleep(2);
                $this->info("\n⚡ PROCESSING...");
                $this->call('trial:process-endings');
                break;

            case 'trial-off':
                // TEST 2: Trial expired, auto-renewal OFF
                DB::table('user')->where('User_ID', $user->User_ID)->update([
                    'subscription_status' => 'trial',
                    'trial_ends_at' => $expiredTime,
                    'auto_renewal' => 0,
                    'allowed_companies' => 2,
                    'payment_frequency' => 'monthly',
                    'stripe_payment_method_id' => $user->stripe_payment_method_id,
                    'stripe_customer_id' => $user->stripe_customer_id,
                ]);
                
                $this->warn("✅ SETUP:");
                $this->line("   Trial ended: {$expiredTime}");
                $this->line("   Auto-Renewal: OFF");
                $this->line("   Expected: EXPIRE (no charge)");
                
                sleep(2);
                $this->info("\n⚡ PROCESSING...");
                $this->call('trial:process-endings');
                break;

            case 'active-on':
                // TEST 3: Subscription renewal due, auto-renewal ON
                DB::table('user')->where('User_ID', $user->User_ID)->update([
                    'subscription_status' => 'active',
                    'trial_ends_at' => null,
                    'next_billing_date' => $expiredTime,
                    'auto_renewal' => 1,
                    'allowed_companies' => 3,
                    'payment_frequency' => 'monthly',
                    'stripe_payment_method_id' => $user->stripe_payment_method_id,
                    'stripe_customer_id' => $user->stripe_customer_id,
                ]);
                
                $this->info("✅ SETUP:");
                $this->line("   Billing due: {$expiredTime}");
                $this->line("   Auto-Renewal: ON");
                $this->line("   Expected: RENEW £30");
                
                sleep(2);
                $this->info("\n⚡ PROCESSING...");
                $this->call('subscription:process-renewals');
                break;

            case 'active-off':
                // TEST 4: Subscription renewal due, auto-renewal OFF
                DB::table('user')->where('User_ID', $user->User_ID)->update([
                    'subscription_status' => 'active',
                    'trial_ends_at' => null,
                    'next_billing_date' => $expiredTime,
                    'auto_renewal' => 0,
                    'allowed_companies' => 4,
                    'payment_frequency' => 'monthly',
                    'stripe_payment_method_id' => $user->stripe_payment_method_id,
                    'stripe_customer_id' => $user->stripe_customer_id,
                ]);
                
                $this->warn("✅ SETUP:");
                $this->line("   Billing due: {$expiredTime}");
                $this->line("   Auto-Renewal: OFF");
                $this->line("   Expected: EXPIRE (no charge)");
                
                sleep(2);
                $this->info("\n⚡ PROCESSING...");
                $this->call('subscription:process-renewals');
                break;

            default:
                $this->error("❌ Invalid type");
                return 1;
        }

        // ✅ Show final result
        sleep(1);
        $this->showResult($user->User_ID, $type);

        return 0;
    }

    protected function showResult($userId, $type)
    {
        $user = DB::table('user')->where('User_ID', $userId)->first();

        $this->info("\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->info("📊 FINAL RESULT:");
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");

        $this->table(
            ['Field', 'Value'],
            [
                ['Status', $user->subscription_status],
                ['Auto-Renewal', $user->auto_renewal . ' (' . ($user->auto_renewal == 1 ? 'ON' : 'OFF') . ')'],
                ['Next Billing', $user->next_billing_date ?? 'N/A'],
            ]
        );

        // Check if charge was made
        $lastPayment = DB::table('subscription_payments')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($lastPayment && $lastPayment->created_at > now()->subSeconds(10)) {
            $this->info("✅ PAYMENT RECORDED:");
            $this->line("   Amount: £{$lastPayment->amount}");
            $this->line("   Status: {$lastPayment->status}");
            $this->line("   Type: {$lastPayment->payment_type}");
        }

        // Expected results
        if (str_contains($type, '-on')) {
            if ($user->subscription_status === 'active') {
                $this->info("\n✅ TEST PASSED - Auto-renewal worked!");
            } else {
                $this->error("\n❌ TEST FAILED - Should be active");
            }
        } else {
            if ($user->subscription_status === 'expired') {
                $this->info("\n✅ TEST PASSED - Correctly expired!");
            } else {
                $this->error("\n❌ TEST FAILED - Should be expired");
            }
        }
    }
}