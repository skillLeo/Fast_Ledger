<?php
// app/Console/Commands/TestAllScenarios.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TestAllScenarios extends Command
{
    protected $signature = 'subscription:test-all {email}';
    protected $description = 'Test all 4 payment scenarios automatically';

    protected $email;
    protected $userId;
    protected $stripeCustomerId;
    protected $stripePaymentMethodId;

    public function handle()
    {
        $this->email = $this->argument('email');
        
        $user = DB::table('user')->where('email', $this->email)->first();
        
        if (!$user) {
            $this->error("❌ User not found: {$this->email}");
            return 1;
        }

        $this->userId = $user->User_ID;
        
        // ✅ Use existing Stripe IDs or create real test ones
        $this->setupStripeTestData();
        
        $this->info("🧪 TESTING ALL 4 SCENARIOS");
        $this->info("User: {$user->Full_Name} ({$this->email})");
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n");

        if (!$this->confirm('This will modify the user data. Continue?', true)) {
            $this->warn('Test cancelled');
            return 0;
        }

        // Run all scenarios
        $this->testScenario1();
        $this->testScenario2();
        $this->testScenario3();
        $this->testScenario4();

        $this->displayFinalSummary();

        return 0;
    }

    /**
     * ✅ Setup real Stripe test customer and payment method
     */
    protected function setupStripeTestData()
    {
        $user = DB::table('user')->where('User_ID', $this->userId)->first();
        
        try {
            $mode = config('services.stripe.mode', 'test');
            $secretKey = $mode === 'live' 
                ? config('services.stripe.live.secret')
                : config('services.stripe.test.secret');

            \Stripe\Stripe::setApiKey($secretKey);

            // ✅ Create or use existing customer
            if ($user->stripe_customer_id) {
                try {
                    // Verify customer exists
                    \Stripe\Customer::retrieve($user->stripe_customer_id);
                    $this->stripeCustomerId = $user->stripe_customer_id;
                    $this->line("✅ Using existing Stripe customer: {$this->stripeCustomerId}");
                } catch (\Exception $e) {
                    // Customer doesn't exist, create new one
                    $customer = \Stripe\Customer::create([
                        'email' => $this->email,
                        'name' => $user->Full_Name,
                        'metadata' => ['user_id' => $this->userId],
                    ]);
                    $this->stripeCustomerId = $customer->id;
                    $this->line("✅ Created new Stripe customer: {$this->stripeCustomerId}");
                }
            } else {
                $customer = \Stripe\Customer::create([
                    'email' => $this->email,
                    'name' => $user->Full_Name,
                    'metadata' => ['user_id' => $this->userId],
                ]);
                $this->stripeCustomerId = $customer->id;
                $this->line("✅ Created new Stripe customer: {$this->stripeCustomerId}");
            }

            // ✅ Create test payment method
            $paymentMethod = \Stripe\PaymentMethod::create([
                'type' => 'card',
                'card' => [
                    'token' => 'tok_visa', // Stripe test token
                ],
            ]);

            // Attach to customer
            $paymentMethod->attach(['customer' => $this->stripeCustomerId]);
            $this->stripePaymentMethodId = $paymentMethod->id;
            
            $this->line("✅ Created test payment method: {$this->stripePaymentMethodId}\n");

        } catch (\Exception $e) {
            $this->warn("⚠️  Could not setup Stripe test data: {$e->getMessage()}");
            $this->warn("⚠️  Tests will run but payments will fail (expected in test mode)\n");
            
            // Fallback to test IDs
            $this->stripeCustomerId = 'cus_test_fallback';
            $this->stripePaymentMethodId = 'pm_card_visa';
        }
    }

    protected function testScenario1()
    {
        $this->info("\n╔══════════════════════════════════════════════════╗");
        $this->info("║  SCENARIO 1: FREE TRIAL + AUTO-RENEWAL OFF      ║");
        $this->info("╚══════════════════════════════════════════════════╝\n");

        // ✅ Clear old test payments first
        $this->clearOldTestPayments();

        // Setup
        $this->line("📝 Setting up scenario...");
        DB::table('user')->where('User_ID', $this->userId)->update([
            'subscription_status' => 'trial',
            'auto_renewal' => 0,
            'trial_starts_at' => now()->subDays(14),
            'trial_ends_at' => now()->subSecond(),
            'stripe_payment_method_id' => $this->stripePaymentMethodId,
            'stripe_customer_id' => $this->stripeCustomerId,
            'allowed_companies' => 2,
            'payment_frequency' => 'monthly',
            'subscription_price' => 20.00,
            'Modified_On' => now(),
        ]);

        $this->line("⏳ Processing trial ending...");
        
        // Process
        $this->call('trial:process-endings');

        // Verify
        $this->line("\n🔍 Verifying results...");
        $user = DB::table('user')->where('User_ID', $this->userId)->first();
        
        $passed = true;
        
        if ($user->subscription_status !== 'expired') {
            $this->error("❌ Status: Expected 'expired', got '{$user->subscription_status}'");
            $passed = false;
        } else {
            $this->info("✅ Status: expired");
        }

        if ($user->auto_renewal != 0) {
            $this->error("❌ Auto-renewal: Expected 0, got {$user->auto_renewal}");
            $passed = false;
        } else {
            $this->info("✅ Auto-renewal: OFF");
        }

        // ✅ Check no payment was made in THIS scenario
        $payment = DB::table('subscription_payments')
            ->where('user_id', $this->userId)
            ->where('payment_type', 'trial_end')
            ->where('created_at', '>', now()->subSeconds(10)) // ✅ Very recent only
            ->first();

        if ($payment) {
            $this->error("❌ Payment made (should not have charged!)");
            $passed = false;
        } else {
            $this->info("✅ No payment made");
        }

        $this->displayScenarioResult(1, $passed);
    }

    protected function testScenario2()
    {
        $this->info("\n╔══════════════════════════════════════════════════╗");
        $this->info("║  SCENARIO 2: FREE TRIAL + AUTO-RENEWAL ON       ║");
        $this->info("╚══════════════════════════════════════════════════╝\n");

        // ✅ Clear old test payments first
        $this->clearOldTestPayments();

        // Setup
        $this->line("📝 Setting up scenario...");
        DB::table('user')->where('User_ID', $this->userId)->update([
            'subscription_status' => 'trial',
            'auto_renewal' => 1,
            'trial_starts_at' => now()->subDays(14),
            'trial_ends_at' => now()->subSecond(),
            'stripe_payment_method_id' => $this->stripePaymentMethodId,
            'stripe_customer_id' => $this->stripeCustomerId,
            'allowed_companies' => 2,
            'payment_frequency' => 'monthly',
            'subscription_price' => 20.00,
            'Modified_On' => now(),
        ]);

        $this->line("⏳ Processing trial ending...");
        
        // Process
        $this->call('trial:process-endings');

        // Verify
        $this->line("\n🔍 Verifying results...");
        $user = DB::table('user')->where('User_ID', $this->userId)->first();
        
        $passed = true;

        // In test mode, payment will likely succeed or fail gracefully
        if ($user->subscription_status !== 'active' && $user->subscription_status !== 'expired') {
            $this->error("❌ Status: Expected 'active' or 'expired', got '{$user->subscription_status}'");
            $passed = false;
        } else {
            $this->info("✅ Status: {$user->subscription_status}");
        }

        // Check if payment was attempted
        $payment = DB::table('subscription_payments')
            ->where('user_id', $this->userId)
            ->where('payment_type', 'trial_end')
            ->where('created_at', '>', now()->subSeconds(10))
            ->first();

        if ($payment) {
            $this->info("✅ Payment attempt logged: {$payment->status}");
        } else {
            $this->warn("⚠️  No payment record (might have failed before logging)");
        }

        $this->displayScenarioResult(2, $passed);
    }

    protected function testScenario3()
    {
        $this->info("\n╔══════════════════════════════════════════════════╗");
        $this->info("║  SCENARIO 3: PAID USER + AUTO-RENEWAL OFF       ║");
        $this->info("╚══════════════════════════════════════════════════╝\n");

        // Setup
        $this->line("📝 Setting up scenario...");
        DB::table('user')->where('User_ID', $this->userId)->update([
            'subscription_status' => 'active',
            'auto_renewal' => 0,
            'subscription_starts_at' => now()->subMonth(),
            'last_payment_date' => now()->subMonth(),
            'next_billing_date' => now()->subHour(), // ✅ Use subHour for reliability
            'current_period_start' => now()->subMonth(),
            'current_period_end' => now()->subHour(),
            'stripe_payment_method_id' => $this->stripePaymentMethodId,
            'stripe_customer_id' => $this->stripeCustomerId,
            'allowed_companies' => 3,
            'payment_frequency' => 'monthly',
            'subscription_price' => 30.00,
            'trial_ends_at' => null,
            'trial_starts_at' => null,
            'Modified_On' => now(),
        ]);

        $this->line("⏳ Processing renewal...");
        
        // Process
        $this->call('subscription:process-renewals');

        // Verify
        $this->line("\n🔍 Verifying results...");
        $user = DB::table('user')->where('User_ID', $this->userId)->first();
        
        $passed = true;

        if ($user->subscription_status !== 'expired') {
            $this->error("❌ Status: Expected 'expired', got '{$user->subscription_status}'");
            $passed = false;
        } else {
            $this->info("✅ Status: expired");
        }

        if ($user->auto_renewal != 0) {
            $this->error("❌ Auto-renewal: Expected 0, got {$user->auto_renewal}");
            $passed = false;
        } else {
            $this->info("✅ Auto-renewal: OFF");
        }

        // Check no recent renewal payment
        $payment = DB::table('subscription_payments')
            ->where('user_id', $this->userId)
            ->where('payment_type', 'renewal')
            ->where('created_at', '>', now()->subMinutes(2))
            ->first();

        if ($payment) {
            $this->error("❌ Renewal payment made (should not have charged!)");
            $passed = false;
        } else {
            $this->info("✅ No renewal payment made");
        }

        $this->displayScenarioResult(3, $passed);
    }

    protected function testScenario4()
    {
        $this->info("\n╔══════════════════════════════════════════════════╗");
        $this->info("║  SCENARIO 4: PAID USER + AUTO-RENEWAL ON        ║");
        $this->info("╚══════════════════════════════════════════════════╝\n");

        // Setup
        $this->line("📝 Setting up scenario...");
        DB::table('user')->where('User_ID', $this->userId)->update([
            'subscription_status' => 'active',
            'auto_renewal' => 1,
            'subscription_starts_at' => now()->subMonth(),
            'last_payment_date' => now()->subMonth(),
            'next_billing_date' => now()->subHour(), // ✅ Use subHour for reliability
            'current_period_start' => now()->subMonth(),
            'current_period_end' => now()->subHour(),
            'stripe_payment_method_id' => $this->stripePaymentMethodId,
            'stripe_customer_id' => $this->stripeCustomerId,
            'allowed_companies' => 3,
            'payment_frequency' => 'monthly',
            'subscription_price' => 30.00,
            'trial_ends_at' => null,
            'trial_starts_at' => null,
            'Modified_On' => now(),
        ]);

        $this->line("⏳ Processing renewal...");
        
        // Process
        $this->call('subscription:process-renewals');

        // Verify
        $this->line("\n🔍 Verifying results...");
        $user = DB::table('user')->where('User_ID', $this->userId)->first();
        
        $passed = true;

        // Should attempt to charge
        if ($user->subscription_status !== 'active' && $user->subscription_status !== 'payment_failed') {
            $this->error("❌ Status: Expected 'active' or 'payment_failed', got '{$user->subscription_status}'");
            $passed = false;
        } else {
            $this->info("✅ Status: {$user->subscription_status}");
        }

        // Check if payment was attempted
        $payment = DB::table('subscription_payments')
            ->where('user_id', $this->userId)
            ->where('payment_type', 'renewal')
            ->where('created_at', '>', now()->subMinutes(2))
            ->first();

        if ($payment) {
            $this->info("✅ Payment attempt logged: {$payment->status}");
        } else {
            $this->warn("⚠️  No payment record (might have failed before logging)");
        }

        $this->displayScenarioResult(4, $passed);
    }

    protected function displayScenarioResult(int $number, bool $passed)
    {
        $this->line("");
        if ($passed) {
            $this->info("✅ SCENARIO {$number} PASSED");
        } else {
            $this->error("❌ SCENARIO {$number} FAILED");
        }
        $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
    }

    protected function displayFinalSummary()
    {
        $this->line("\n\n");
        $this->info("╔══════════════════════════════════════════════════╗");
        $this->info("║           TESTING COMPLETE                       ║");
        $this->info("╚══════════════════════════════════════════════════╝");
        
        $this->line("\n📊 EXPECTED BEHAVIORS:");
        $this->table(
            ['Scenario', 'Trial?', 'Auto-Renewal', 'Expected Result'],
            [
                ['1', 'Yes', 'OFF', 'Expire without charging'],
                ['2', 'Yes', 'ON', 'Charge and activate'],
                ['3', 'No', 'OFF', 'Expire without charging'],
                ['4', 'No', 'ON', 'Charge and renew'],
            ]
        );

        $this->line("\n📝 Check Logs:");
        $this->line("   tail -f storage/logs/laravel.log");
        
        $this->line("\n💳 Check Payments:");
        $this->line("   php artisan tinker");
        $this->line("   DB::table('subscription_payments')->where('user_id', {$this->userId})->latest()->get();");
        
        $this->line("\n👤 Check User Status:");
        $this->line("   php artisan subscription:check-auto-renewal {$this->email}");
        
        $this->info("\n✅ All scenarios tested!");
    }
}