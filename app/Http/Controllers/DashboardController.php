<?php
// app/Http/Controllers/DashboardController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Get user roles
        $roleIds = DB::table('userrole')
            ->where('User_ID', $user->User_ID)
            ->pluck('Role_ID')
            ->toArray();

        // ✅ For company module users, check onboarding
        if (in_array(2, $roleIds) || in_array(3, $roleIds) || in_array(4, $roleIds)) {
            
            // STEP 1: Check if subscription plan selected
            if (!$user->allowed_companies || !$user->subscription_price || !$user->subscription_status) {
                return redirect()->route('company.subscription.setup')
                    ->with('info', 'Please select your subscription plan to continue.');
            }

            // STEP 2: Check if company created
            $hasCompanies = DB::table('company_module_users')
                ->where('User_ID', $user->User_ID)
                ->where('Is_Active', true)
                ->exists();

            if (!$hasCompanies) {
                return redirect()->route('company.setup.create')
                    ->with('info', 'Please create your company to continue.');
            }

            // STEP 3: Check if payment completed
            if ($user->subscription_status === 'pending_payment') {
                return redirect()->route('company.payment.create')
                    ->with('info', 'Please complete payment to continue.');
            }

            // STEP 4: Redirect to company selection
            if (!session()->has('current_company_id')) {
                return redirect()->route('company.select')
                    ->with('info', 'Please select a company to continue.');
            }
        }

        // Show dashboard
        return view('dashboard');
    }


    /**
     * Check if user needs onboarding
     */
    protected function needsOnboarding($user): bool
    {
        // User needs onboarding if:
        // 1. They don't have subscription status set, OR
        // 2. Their subscription status is 'pending_payment'
        return !$user->subscription_status || $user->subscription_status === 'pending_payment';
    }

    /**
     * Redirect to appropriate onboarding step
     */
    protected function redirectToOnboardingStep($user)
    {
        // Step 1: No subscription selected yet
        if (!$user->allowed_companies || !$user->subscription_price) {
            return redirect()->route('company.subscription.setup')
                ->with('info', 'Please select your subscription plan to get started.');
        }

        // Step 2: Subscription selected, but no company created
        $hasCompany = $user->companies()->exists();
        if (!$hasCompany) {
            return redirect()->route('company.setup.create')
                ->with('info', 'Please create your company to continue.');
        }

        // Step 3: Company created, but payment not completed
        if ($user->subscription_status === 'pending_payment') {
            return redirect()->route('company.payment.create')
                ->with('info', 'Please complete payment to start your free trial.');
        }

        // If we get here, user should be able to use the dashboard
        return null;
    }
}