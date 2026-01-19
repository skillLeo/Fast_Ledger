<?php
// app/Console/Commands/ProcessTrialEndings.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ProcessTrialEndings extends Command
{
    protected $signature = 'trial:process-endings {--force} {--dry-run}';
    protected $description = 'Process trial endings and charge users automatically';

    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        
        if ($isDryRun) {
            $this->warn('🧪 DRY RUN MODE - No actual charges will be made');
        }

        $this->info('🔍 Checking for trials ending today...');

        // Get users whose trial ends today or has already ended
        $usersToCharge = User::where('subscription_status', 'trial')
            ->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '<=', now())
            ->get();

        if ($usersToCharge->isEmpty()) {
            $this->warn('⚠️  No trials found that need processing.');
            $this->showUpcomingTrials();
            return 0;
        }

        $this->info("✅ Found {$usersToCharge->count()} trial(s) to process\n");

        $successCount = 0;
        $failCount = 0;
        $skippedCount = 0;

        foreach ($usersToCharge as $user) {
            $result = $this->processUser($user, $isDryRun);
            
            if ($result === 'success') $successCount++;
            elseif ($result === 'failed') $failCount++;
            else $skippedCount++;
        }

        $this->displaySummary($successCount, $failCount, $skippedCount, $isDryRun);
        return 0;
    }

    protected function processUser(User $user, bool $isDryRun): string
    {
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->info("👤 Processing: {$user->Full_Name} ({$user->email})");
        
        // ✅ Calculate charge amount with CORRECT discount
        $monthlyAmount = $user->allowed_companies * 10;
        $isYearly = $user->payment_frequency === 'yearly';
        
        // ✅ CRITICAL FIX: Use 20% discount (0.8 multiplier), not 10%
        $chargeAmount = $isYearly 
            ? round($monthlyAmount * 12 * 0.8, 2) // 20% discount
            : $monthlyAmount;

        $this->line("   📦 Companies: {$user->allowed_companies}");
        $this->line("   📅 Frequency: {$user->payment_frequency}");
        $this->line("   💰 Amount to charge: £{$chargeAmount}");
        $this->line("   📧 Email: {$user->email}");

        // ✅ Check if we have payment method
        if (!$user->stripe_payment_method_id) {
            $this->error("   ❌ No payment method saved - CRITICAL ERROR");
            $this->updateUserStatus($user, 'payment_failed', 'No payment method on file');
            $this->sendNoPaymentMethodEmail($user);
            return 'failed';
        }

        $this->line("   💳 Payment Method: {$user->stripe_payment_method_id}");

        if ($isDryRun) {
            $this->warn("   🧪 DRY RUN - Would charge £{$chargeAmount}");
            return 'success';
        }

        try {
            // ✅ Initialize Stripe with correct key
            $mode = config('services.stripe.mode', 'test');
            $secretKey = $mode === 'live' 
                ? config('services.stripe.live.secret')
                : config('services.stripe.test.secret');

            if (empty($secretKey)) {
                throw new \Exception('Stripe secret key not configured');
            }

            \Stripe\Stripe::setApiKey($secretKey);

            $this->line("   🔧 Using Stripe mode: {$mode}");

            // ✅ STEP 1: Verify customer exists
            if (!$user->stripe_customer_id) {
                $this->error("   ❌ No Stripe customer ID - creating one...");
                
                $customer = \Stripe\Customer::create([
                    'email' => $user->email,
                    'name' => $user->Full_Name,
                    'metadata' => ['user_id' => $user->User_ID],
                ]);

                $user->update(['stripe_customer_id' => $customer->id]);
                $this->info("   ✅ Customer created: {$customer->id}");
            }

            // ✅ STEP 2: Verify payment method is attached
            try {
                $paymentMethod = \Stripe\PaymentMethod::retrieve($user->stripe_payment_method_id);
                
                if ($paymentMethod->customer !== $user->stripe_customer_id) {
                    $this->warn("   ⚠️  Payment method not attached, attaching now...");
                    $paymentMethod->attach(['customer' => $user->stripe_customer_id]);
                }
            } catch (\Exception $e) {
                throw new \Exception("Invalid payment method: {$e->getMessage()}");
            }

            // ✅ STEP 3: Create and confirm payment intent
            $this->line("   💳 Creating payment intent...");

            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => $chargeAmount * 100, // Convert to pence
                'currency' => 'gbp',
                'customer' => $user->stripe_customer_id,
                'payment_method' => $user->stripe_payment_method_id,
                'off_session' => true, // ✅ CRITICAL: Allow charging without user present
                'confirm' => true, // ✅ CRITICAL: Automatically confirm
                'description' => "Post-trial subscription charge for {$user->allowed_companies} companies ({$user->payment_frequency})",
                'metadata' => [
                    'user_id' => $user->User_ID,
                    'user_email' => $user->email,
                    'subscription_type' => 'post_trial',
                    'companies' => $user->allowed_companies,
                    'frequency' => $user->payment_frequency,
                ],
            ]);

            if ($paymentIntent->status === 'succeeded') {
                // ✅ Update user to active subscription
                $user->update([
                    'subscription_status' => 'active',
                    'subscription_starts_at' => now(),
                    'trial_ends_at' => null, // Clear trial end date
                    'stripe_payment_intent_id' => $paymentIntent->id,
                ]);

                $this->info("   ✅ Successfully charged £{$chargeAmount}");
                $this->info("   📧 Payment Intent: {$paymentIntent->id}");

                // ✅ Send success email
                $this->sendSuccessEmail($user, $chargeAmount, $paymentIntent->id);

                Log::info('Trial ended - Payment successful', [
                    'user_id' => $user->User_ID,
                    'amount' => $chargeAmount,
                    'payment_intent' => $paymentIntent->id,
                ]);

                return 'success';

            } else {
                throw new \Exception("Payment failed with status: {$paymentIntent->status}");
            }

        } catch (\Stripe\Exception\CardException $e) {
            // ✅ Card was declined
            $this->error("   ❌ Card declined: {$e->getMessage()}");
            
            $this->updateUserStatus($user, 'payment_failed', $e->getMessage());
            $this->sendCardDeclinedEmail($user, $e->getMessage());

            return 'failed';

        } catch (\Stripe\Exception\InvalidRequestException $e) {
            $this->error("   ❌ Invalid request: {$e->getMessage()}");
            
            $this->updateUserStatus($user, 'payment_failed', $e->getMessage());
            
            return 'failed';

        } catch (\Exception $e) {
            $this->error("   ❌ Error: {$e->getMessage()}");
            
            Log::error('Trial charge failed', [
                'user_id' => $user->User_ID,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->updateUserStatus($user, 'payment_failed', $e->getMessage());
            
            return 'failed';
        }
    }

    protected function updateUserStatus(User $user, string $status, string $reason): void
    {
        $user->update([
            'subscription_status' => $status,
            'payment_failure_reason' => $reason,
            'payment_failed_at' => now(),
        ]);
    }

    protected function sendSuccessEmail(User $user, float $amount, string $paymentIntentId): void
    {
        // TODO: Implement email notification
        Log::info('Should send success email', [
            'user_id' => $user->User_ID,
            'email' => $user->email,
            'amount' => $amount,
        ]);
    }

    protected function sendCardDeclinedEmail(User $user, string $reason): void
    {
        // TODO: Implement email notification
        Log::info('Should send card declined email', [
            'user_id' => $user->User_ID,
            'email' => $user->email,
            'reason' => $reason,
        ]);
    }

    protected function sendNoPaymentMethodEmail(User $user): void
    {
        // TODO: Implement email notification
        Log::error('No payment method on file', [
            'user_id' => $user->User_ID,
            'email' => $user->email,
        ]);
    }

    protected function showUpcomingTrials(): void
    {
        $upcomingTrials = User::where('subscription_status', 'trial')
            ->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '>', now())
            ->orderBy('trial_ends_at')
            ->get();
        
        if ($upcomingTrials->isNotEmpty()) {
            $this->info("\n📅 Upcoming trial endings:");
            $this->table(
                ['User', 'Email', 'Companies', 'Ends At', 'Days Left'],
                $upcomingTrials->map(function ($user) {
                    return [
                        $user->Full_Name,
                        $user->email,
                        $user->allowed_companies,
                        $user->trial_ends_at->format('Y-m-d H:i'),
                        $user->trial_ends_at->diffInDays(now(), false) . ' days',
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
            $this->warn("   🧪 DRY RUN - No actual charges made");
        }
        
        $this->info("   ✅ Successful: {$success}");
        $this->error("   ❌ Failed: {$fail}");
        
        if ($skipped > 0) {
            $this->warn("   ⏭️  Skipped: {$skipped}");
        }
        
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
    }
}