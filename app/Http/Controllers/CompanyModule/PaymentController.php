<?php
// app/Http/Controllers/CompanyModule/PaymentController.php

namespace App\Http\Controllers\CompanyModule;

use App\Http\Controllers\Controller;
use App\Services\StripePaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected $stripeService;

    public function __construct(StripePaymentService $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    public function store(Request $request)
    {
        $request->validate([
            'payment_intent_id' => 'nullable|string',
            'payment_method_id' => 'required|string',
            'number_of_companies' => 'required|integer|min:1|max:50',
            'payment_frequency' => 'required|in:monthly,yearly',
            'is_trial' => 'required|boolean',
        ]);

        $user = auth()->user();
        $isTrial = $request->is_trial;
        $isUpgrade = $user->subscription_status && $user->allowed_companies;

        // ✅ Get user's role
        $roleIds = DB::table('userrole')
            ->where('User_ID', $user->User_ID)
            ->pluck('Role_ID')
            ->toArray();
        
        $isAgentAdmin = in_array(3, $roleIds);

        if (!$isTrial && $request->payment_intent_id) {
            $paymentResult = $this->stripeService->getPaymentIntent($request->payment_intent_id);

            if (!$paymentResult['success'] || $paymentResult['status'] !== 'succeeded') {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment verification failed',
                ], 400);
            }
        }

        if ($isTrial && $user->has_used_free_trial) {
            return response()->json([
                'success' => false,
                'message' => 'You have already used your free trial',
            ], 400);
        }

        try {
            DB::beginTransaction();

            $numberOfCompanies = $request->number_of_companies;
            $pricePerCompany = 10;
            $totalPrice = $numberOfCompanies * $pricePerCompany;
            $isYearly = $request->payment_frequency === 'yearly';

            // ✅ UPGRADE PATH
            if ($isUpgrade) {
                if ($numberOfCompanies <= $user->allowed_companies) {
                    return response()->json([
                        'success' => false,
                        'message' => "You must select more than your current {$user->allowed_companies} companies",
                    ], 400);
                }

                $nextBilling = $isYearly ? now()->addYear() : now()->addMonth();

                DB::table('user')
                    ->where('User_ID', $user->User_ID)
                    ->update([
                        'allowed_companies' => $numberOfCompanies,
                        'subscription_price' => $totalPrice,
                        'payment_frequency' => $request->payment_frequency,
                        'next_billing_date' => $nextBilling,
                        'stripe_payment_intent_id' => $request->payment_intent_id,
                        'stripe_payment_method_id' => $request->payment_method_id,
                        'Modified_On' => now(),
                    ]);

                DB::commit();

                // ✅ Redirect based on role
                $redirectUrl = $isAgentAdmin 
                    ? url('/clients') 
                    : route('company.select');

                return response()->json([
                    'success' => true,
                    'message' => "Subscription upgraded to {$numberOfCompanies} companies!",
                    'redirect_url' => $redirectUrl,
                ]);
            }

            // ✅ NEW SUBSCRIPTION - FREE TRIAL
            if ($isTrial) {
                $trialStartsAt = now();
                $trialEndsAt = now()->addDays(14);

                DB::table('user')
                    ->where('User_ID', $user->User_ID)
                    ->update([
                        'allowed_companies' => $numberOfCompanies,
                        'subscription_price' => $totalPrice,
                        'subscription_status' => 'trial',
                        'trial_starts_at' => $trialStartsAt,
                        'trial_ends_at' => $trialEndsAt,
                        'payment_frequency' => $request->payment_frequency,
                        'has_used_free_trial' => 1,
                        'auto_renewal' => true,
                        'stripe_payment_intent_id' => null,
                        'stripe_payment_method_id' => $request->payment_method_id,
                        'Modified_On' => now(),
                    ]);

                $this->grantModuleAccess($user->User_ID);

                DB::commit();

                Log::info('✅ Free trial started', [
                    'user_id' => $user->User_ID,
                    'companies' => $numberOfCompanies,
                    'trial_ends' => $trialEndsAt,
                    'is_agent_admin' => $isAgentAdmin,
                ]);

                // ✅ Redirect based on role
                $redirectUrl = $isAgentAdmin 
                    ? url('/clients') 
                    : route('company.select');

                return response()->json([
                    'success' => true,
                    'message' => '14-day free trial started! Enjoy full access.',
                    'redirect_url' => $redirectUrl,
                ]);
            }

            // ✅ NEW SUBSCRIPTION - PAID
            $periodStart = now();
            $periodEnd = $isYearly ? now()->addYear() : now()->addMonth();

            DB::table('user')
                ->where('User_ID', $user->User_ID)
                ->update([
                    'allowed_companies' => $numberOfCompanies,
                    'subscription_price' => $totalPrice,
                    'subscription_status' => 'active',
                    'subscription_starts_at' => $periodStart,
                    'last_payment_date' => now(),
                    'next_billing_date' => $periodEnd,
                    'current_period_start' => $periodStart,
                    'current_period_end' => $periodEnd,
                    'trial_starts_at' => null,
                    'trial_ends_at' => null,
                    'payment_frequency' => $request->payment_frequency,
                    'has_used_free_trial' => 1,
                    'auto_renewal' => true,
                    'stripe_payment_intent_id' => $request->payment_intent_id,
                    'stripe_payment_method_id' => $request->payment_method_id,
                    'Modified_On' => now(),
                ]);

            DB::table('subscription_payments')->insert([
                'user_id' => $user->User_ID,
                'stripe_payment_intent_id' => $request->payment_intent_id,
                'amount' => $totalPrice,
                'currency' => 'gbp',
                'status' => 'succeeded',
                'payment_type' => 'initial',
                'payment_frequency' => $request->payment_frequency,
                'companies_count' => $numberOfCompanies,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'paid_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->grantModuleAccess($user->User_ID);

            DB::commit();

            Log::info('✅ Paid subscription activated', [
                'user_id' => $user->User_ID,
                'companies' => $numberOfCompanies,
                'amount' => $totalPrice,
                'next_billing' => $periodEnd,
                'is_agent_admin' => $isAgentAdmin,
            ]);

            // ✅ Redirect based on role
            $redirectUrl = $isAgentAdmin 
                ? url('/clients') 
                : route('company.select');

            return response()->json([
                'success' => true,
                'message' => 'Subscription activated! Welcome to FastLedger.',
                'redirect_url' => $redirectUrl,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('❌ Payment processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment processing failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    protected function grantModuleAccess($userId)
    {
        $companyModule = DB::table('modules')
            ->where('Module_Name', 'company_module')
            ->first();

        if (!$companyModule) {
            throw new \Exception('Company module not found');
        }

        $existingAccess = DB::table('user_module_access')
            ->where('User_ID', $userId)
            ->where('Module_ID', $companyModule->Module_ID)
            ->first();

        if ($existingAccess) {
            DB::table('user_module_access')
                ->where('User_ID', $userId)
                ->where('Module_ID', $companyModule->Module_ID)
                ->update([
                    'Has_Access' => true,
                    'Is_Active' => true,
                    'updated_at' => now(),
                ]);
        } else {
            DB::table('user_module_access')->insert([
                'User_ID' => $userId,
                'Module_ID' => $companyModule->Module_ID,
                'Has_Access' => true,
                'Is_Active' => true,
                'Granted_By' => $userId,
                'Granted_At' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function create()
    {
        $user = auth()->user();
        
        // ✅ Check if Agent Admin
        $roleIds = DB::table('userrole')
            ->where('User_ID', $user->User_ID)
            ->pluck('Role_ID')
            ->toArray();
        
        $isAgentAdmin = in_array(3, $roleIds);
        $isUpgrade = $user->subscription_status && $user->allowed_companies;

        if ($user->hasCompletedSubscriptionSetup() && !$isUpgrade) {
            // Redirect based on role
            $redirectRoute = $isAgentAdmin ? '/clients' : 'company.select';
            return redirect($redirectRoute)
                ->with('info', 'You have already completed your subscription.');
        }

        // ✅ Agent Admin does NOT need company setup
        if (!$isAgentAdmin) {
            $hasCompany = $user->companies()->exists();
            if (!$hasCompany && !$isUpgrade) {
                return redirect()->route('company.setup.create')
                    ->with('error', 'Please create your company first.');
            }
        }

        $pricingConfig = \App\Http\Controllers\CompanyModule\CompanySetupController::getPricingConfig();
        $canUseTrial = !$user->has_used_free_trial && !$isUpgrade;

        $pricing = [
            'default_companies' => 1,
            'current_companies' => $user->allowed_companies ?? 0,
            'is_upgrade' => $isUpgrade,
            'is_agent_admin' => $isAgentAdmin, // ✅ Pass to view
            'price_per_company' => $pricingConfig['price_per_company'],
            'currency' => $pricingConfig['currency'],
            'can_use_trial' => $canUseTrial,
        ];

        $stripeKey = config('services.stripe.test.key', 'pk_test_51SrFGgRtqr6GBNluGE7EWMY9LQWjdFdz3BgXB2QcLmv3oTrcohCqg2XA29dsjrSpX5qodH06sr2Yzvo2MoABlqcf00T8Bnhl7A');
        $isTestMode = config('services.stripe.mode', 'test') === 'test';

        return view('company-module.payment.create', compact('pricing', 'stripeKey', 'isTestMode'));
    }

    public function createPaymentIntent(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'number_of_companies' => 'required|integer|min:1',
            'payment_method_id' => 'required|string',
        ]);

        $user = auth()->user();
        $amount = $request->amount;

        try {
            $mode = config('services.stripe.mode', 'test');
            $secretKey = $mode === 'live' 
                ? config('services.stripe.live.secret')
                : config('services.stripe.test.secret');

            if (empty($secretKey)) {
                throw new \Exception('Stripe secret key not configured');
            }

            \Stripe\Stripe::setApiKey($secretKey);

            if (!$user->stripe_customer_id) {
                $customer = \Stripe\Customer::create([
                    'email' => $user->email,
                    'name' => $user->Full_Name,
                    'metadata' => ['user_id' => $user->User_ID],
                ]);

                DB::table('user')
                    ->where('User_ID', $user->User_ID)
                    ->update([
                        'stripe_customer_id' => $customer->id,
                        'Modified_On' => now(),
                    ]);

                Log::info('✅ Customer created', [
                    'user_id' => $user->User_ID,
                    'customer_id' => $customer->id,
                ]);

                $user = DB::table('user')->where('User_ID', $user->User_ID)->first();
            }

            $paymentMethod = \Stripe\PaymentMethod::retrieve($request->payment_method_id);
            $paymentMethod->attach(['customer' => $user->stripe_customer_id]);

            \Stripe\Customer::update(
                $user->stripe_customer_id,
                ['invoice_settings' => ['default_payment_method' => $request->payment_method_id]]
            );

            $updated = DB::table('user')
                ->where('User_ID', $user->User_ID)
                ->update([
                    'stripe_payment_method_id' => $request->payment_method_id,
                    'Modified_On' => now(),
                ]);

            Log::info('✅ Payment method saved', [
                'user_id' => $user->User_ID,
                'payment_method' => $request->payment_method_id,
                'rows_updated' => $updated,
            ]);

            $verify = DB::table('user')
                ->where('User_ID', $user->User_ID)
                ->value('stripe_payment_method_id');

            if ($verify !== $request->payment_method_id) {
                throw new \Exception('Failed to save payment method to database');
            }

            if ($amount == 0) {
                return response()->json([
                    'success' => true,
                    'is_trial' => true,
                    'clientSecret' => null,
                    'paymentIntentId' => null,
                    'message' => 'Payment method saved successfully',
                ]);
            }

            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => $amount * 100,
                'currency' => 'gbp',
                'customer' => $user->stripe_customer_id,
                'payment_method' => $request->payment_method_id,
                'description' => "Subscription for {$request->number_of_companies} companies",
                'metadata' => [
                    'user_id' => $user->User_ID,
                    'user_email' => $user->email ?? '',
                    'number_of_companies' => $request->number_of_companies,
                ],
            ]);

            return response()->json([
                'success' => true,
                'is_trial' => false,
                'clientSecret' => $paymentIntent->client_secret,
                'paymentIntentId' => $paymentIntent->id,
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Payment intent failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->User_ID,
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}