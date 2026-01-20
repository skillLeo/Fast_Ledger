<?php
// app/Console/Commands/ProcessSubscriptionRenewals.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessSubscriptionRenewals extends Command
{
    protected $signature = 'subscription:process-renewals {--dry-run} {--force}';
    protected $description = 'Process subscription renewals for users whose billing date is today';

    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        
        if ($isDryRun) {
            $this->warn('🧪 DRY RUN MODE - No actual charges will be made');
        }

        $this->info('🔍 Checking for subscriptions due for renewal...');

        // ✅ Check auto_renewal as integer (1 = enabled)
        $usersDue = DB::table('user')
            ->where('subscription_status', 'active')
            ->where('auto_renewal', 1) // ✅ Use integer check
            ->whereNotNull('next_billing_date')
            ->where('next_billing_date', '<=', now())
            ->whereNotNull('stripe_payment_method_id')
            ->get();

        if ($usersDue->isEmpty()) {
            $this->warn('⚠️  No subscriptions found that need renewal.');
            $this->showUpcomingRenewals();
            return 0;
        }

        $this->info("✅ Found {$usersDue->count()} subscription(s) to renew\n");

        $successCount = 0;
        $failCount = 0;
        $skippedCount = 0;

        foreach ($usersDue as $user) {
            $result = $this->processRenewal($user, $isDryRun);
            
            if ($result === 'success') $successCount++;
            elseif ($result === 'failed') $failCount++;
            else $skippedCount++;
        }

        $this->displaySummary($successCount, $failCount, $skippedCount, $isDryRun);
        return 0;
    }

    protected function processRenewal($user, bool $isDryRun): string
    {
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->info("👤 Processing: {$user->Full_Name} ({$user->email})");

        // ✅ Check if auto-renewal is still enabled (as integer)
        if ($user->auto_renewal != 1) {
            $this->warn("   ⏭️  Auto-renewal disabled - skipping");
            $this->disableSubscription($user, 'auto_renewal_disabled');
            return 'skipped';
        }

        // Calculate charge amount
        $monthlyAmount = $user->allowed_companies * 10;
        $isYearly = $user->payment_frequency === 'yearly';
        $chargeAmount = $isYearly 
            ? round($monthlyAmount * 12 * 0.8, 2)
            : $monthlyAmount;

        $this->line("   📦 Companies: {$user->allowed_companies}");
        $this->line("   📅 Frequency: {$user->payment_frequency}");
        $this->line("   💰 Amount to charge: £{$chargeAmount}");

        if (!$user->stripe_payment_method_id) {
            $this->error("   ❌ No payment method - DISABLING SUBSCRIPTION");
            $this->disableSubscription($user, 'no_payment_method');
            return 'failed';
        }

        $this->line("   💳 Payment Method: {$user->stripe_payment_method_id}");

        if ($isDryRun) {
            $this->warn("   🧪 DRY RUN - Would charge £{$chargeAmount}");
            return 'success';
        }

        try {
            $mode = config('services.stripe.mode', 'test');
            $secretKey = $mode === 'live' 
                ? config('services.stripe.live.secret')
                : config('services.stripe.test.secret');

            if (empty($secretKey)) {
                throw new \Exception('Stripe secret key not configured');
            }

            \Stripe\Stripe::setApiKey($secretKey);

            $periodStart = now();
            $periodEnd = $isYearly ? now()->addYear() : now()->addMonth();

            $this->line("   💳 Creating payment intent...");

            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => $chargeAmount * 100,
                'currency' => 'gbp',
                'customer' => $user->stripe_customer_id,
                'payment_method' => $user->stripe_payment_method_id,
                'off_session' => true,
                'confirm' => true,
                'description' => "Subscription renewal for {$user->allowed_companies} companies ({$user->payment_frequency})",
                'metadata' => [
                    'user_id' => $user->User_ID,
                    'user_email' => $user->email,
                    'payment_type' => 'renewal',
                    'companies' => $user->allowed_companies,
                    'frequency' => $user->payment_frequency,
                ],
            ]);

            if ($paymentIntent->status === 'succeeded') {
                DB::beginTransaction();

                DB::table('user')
                    ->where('User_ID', $user->User_ID)
                    ->update([
                        'subscription_status' => 'active',
                        'last_payment_date' => now(),
                        'next_billing_date' => $periodEnd,
                        'current_period_start' => $periodStart,
                        'current_period_end' => $periodEnd,
                        'stripe_payment_intent_id' => $paymentIntent->id,
                        'Modified_On' => now(),
                    ]);

                DB::table('subscription_payments')->insert([
                    'user_id' => $user->User_ID,
                    'stripe_payment_intent_id' => $paymentIntent->id,
                    'amount' => $chargeAmount,
                    'currency' => 'gbp',
                    'status' => 'succeeded',
                    'payment_type' => 'renewal',
                    'payment_frequency' => $user->payment_frequency,
                    'companies_count' => $user->allowed_companies,
                    'period_start' => $periodStart,
                    'period_end' => $periodEnd,
                    'paid_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::commit();

                $this->info("   ✅ Successfully charged £{$chargeAmount}");
                $this->info("   📧 Payment Intent: {$paymentIntent->id}");
                $this->info("   📅 Next billing: {$periodEnd->format('Y-m-d')}");

                Log::info('Subscription renewed', [
                    'user_id' => $user->User_ID,
                    'amount' => $chargeAmount,
                ]);

                return 'success';

            } else {
                throw new \Exception("Payment failed: {$paymentIntent->status}");
            }

        } catch (\Stripe\Exception\CardException $e) {
            $this->error("   ❌ Card declined: {$e->getMessage()}");
            $this->handlePaymentFailure($user, $chargeAmount, $periodStart, $periodEnd, $e->getMessage());
            return 'failed';

        } catch (\Exception $e) {
            $this->error("   ❌ Error: {$e->getMessage()}");
            $this->handlePaymentFailure($user, $chargeAmount, $periodStart ?? now(), $periodEnd ?? now(), $e->getMessage());
            return 'failed';
        }
    }

    protected function handlePaymentFailure($user, $amount, $periodStart, $periodEnd, $reason): void
    {
        DB::beginTransaction();

        DB::table('subscription_payments')->insert([
            'user_id' => $user->User_ID,
            'amount' => $amount,
            'currency' => 'gbp',
            'status' => 'failed',
            'payment_type' => 'renewal',
            'payment_frequency' => $user->payment_frequency,
            'companies_count' => $user->allowed_companies,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'failure_reason' => $reason,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('user')
            ->where('User_ID', $user->User_ID)
            ->update([
                'subscription_status' => 'payment_failed',
                'auto_renewal' => 0, // ✅ Use integer
                'payment_failure_reason' => $reason,
                'payment_failed_at' => now(),
                'Modified_On' => now(),
            ]);

            DB::table('company_module_users')
            ->where('User_ID', $user->User_ID)
            ->update([
                'Is_Active' => 0,
            ]);

        DB::commit();

        Log::error('Renewal failed', ['user_id' => $user->User_ID]);
    }

    protected function disableSubscription($user, $reason): void
    {
        DB::beginTransaction();

        DB::table('user')
            ->where('User_ID', $user->User_ID)
            ->update([
                'subscription_status' => 'expired',
                'auto_renewal' => 0, // ✅ Use integer
                'Modified_On' => now(),
            ]);

            DB::table('company_module_users')
            ->where('User_ID', $user->User_ID)
            ->update([
                'Is_Active' => 0,
            ]);

        DB::commit();

        Log::info('Subscription disabled', ['user_id' => $user->User_ID, 'reason' => $reason]);
    }

    protected function showUpcomingRenewals(): void
    {
        $upcoming = DB::table('user')
            ->where('subscription_status', 'active')
            ->where('auto_renewal', 1) // ✅ Use integer
            ->whereNotNull('next_billing_date')
            ->where('next_billing_date', '>', now())
            ->orderBy('next_billing_date')
            ->limit(10)
            ->get(['Full_Name', 'email', 'allowed_companies', 'next_billing_date']);

        if ($upcoming->isNotEmpty()) {
            $this->info("\n📅 Upcoming renewals:");
            $this->table(
                ['User', 'Email', 'Companies', 'Next Billing'],
                $upcoming->map(function ($user) {
                    return [
                        $user->Full_Name,
                        $user->email,
                        $user->allowed_companies,
                        $user->next_billing_date,
                    ];
                })
            );
        }
    }

    protected function displaySummary(int $success, int $fail, int $skipped, bool $isDryRun): void
    {
        $this->info("\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->info("📊 SUMMARY:");
        
        if ($isDryRun) {
            $this->warn("   🧪 DRY RUN");
        }
        
        $this->info("   ✅ Successful: {$success}");
        $this->error("   ❌ Failed: {$fail}");
        
        if ($skipped > 0) {
            $this->warn("   ⏭️  Skipped: {$skipped}");
        }
        
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
    }
}