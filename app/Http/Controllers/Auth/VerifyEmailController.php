<?php
// app/Http/Controllers/Auth/VerifyEmailController.php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

class VerifyEmailController extends Controller
{
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return $this->redirectBasedOnRole($request->user());
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return $this->redirectBasedOnRole($request->user());
    }

    protected function redirectBasedOnRole($user): RedirectResponse
    {
        $roleIds = DB::table('userrole')
            ->where('User_ID', $user->User_ID)
            ->pluck('Role_ID')
            ->toArray();

        // ============================================
        // SUPER ADMIN (Role 1)
        // ============================================
        if (in_array(1, $roleIds)) {
            return redirect()->route('dashboard')
                ->with('verified', true)
                ->with('success', 'Email verified!');
        }

        // ============================================
        // AGENT ADMIN (Role 3) ⭐ CRITICAL FIX
        // ============================================
        if (in_array(3, $roleIds)) {
            // ✅ Check if has active subscription
            $hasActiveSubscription = $this->hasActiveSubscription($user);
            
            // ❌ No subscription → Go to PAYMENT page directly (NO company check)
            if (!$hasActiveSubscription) {
                return redirect()->route('company.payment.create')
                    ->with('verified', true)
                    ->with('success', 'Email verified! Please complete your subscription.');
            }

            // ✅ Has subscription → Go to CLIENTS page
            return redirect('/clients')
                ->with('verified', true)
                ->with('success', 'Welcome back!');
        }

        // ============================================
        // ENTITY ADMIN (Role 2) & INVOICING APP (Role 4)
        // ============================================
        if (in_array(2, $roleIds) || in_array(4, $roleIds)) {
            return $this->handleCompanyModuleUser($user);
        }

        // ============================================
        // FALLBACK
        // ============================================
        return redirect()->route('dashboard')
            ->with('verified', true);
    }

    /**
     * ✅ Handle Entity Admin & Invoicing App users (NOT Agent Admin)
     */
    protected function handleCompanyModuleUser($user): RedirectResponse
    {
        // ✅ STEP 1: Check if company created
        $hasCompanies = DB::table('company_module_users')
            ->where('User_ID', $user->User_ID)
            ->where('Is_Active', true)
            ->exists();

        if (!$hasCompanies) {
            return redirect()->route('company.setup.create')
                ->with('verified', true)
                ->with('success', 'Email verified! Please create your company.');
        }

        // ✅ STEP 2: Check if payment completed
        if (!$user->subscription_status || $user->subscription_status === 'pending_payment') {
            return redirect()->route('company.payment.create')
                ->with('verified', true)
                ->with('success', 'Please complete your subscription.');
        }

        // ✅ STEP 3: Check if subscription is active
        if (!$this->hasActiveSubscription($user)) {
            return redirect()->route('company.payment.create')
                ->with('verified', true)
                ->with('warning', 'Your subscription has expired. Please renew.');
        }

        // Everything complete
        return redirect()->route('company.select')
            ->with('verified', true)
            ->with('success', 'Welcome back!');
    }

    /**
     * ✅ Check if user has active subscription (trial OR paid)
     */
    protected function hasActiveSubscription($user): bool
    {
        $status = $user->subscription_status;
        
        // Check trial
        if ($status === 'trial' && $user->trial_ends_at && now()->lt($user->trial_ends_at)) {
            return true;
        }
        
        // Check paid subscription
        if ($status === 'active' && $user->next_billing_date && now()->lt($user->next_billing_date)) {
            return true;
        }
        
        return false;
    }
}