<?php
// app/Http/Controllers/Auth/EmailVerificationPromptController.php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

class EmailVerificationPromptController extends Controller
{
    /**
     * Display the email verification prompt.
     */
    public function __invoke(Request $request): RedirectResponse|View
    {
        // If email is already verified, redirect based on role
        if ($request->user()->hasVerifiedEmail()) {
            return $this->redirectBasedOnRole($request->user());
        }

        // Show verification notice page
        return view('auth.verify-email');
    }

    /**
     * Redirect user based on their role after email verification
     */
    protected function redirectBasedOnRole($user): RedirectResponse
    {
        // Get user's roles from userrole table
        $roleIds = DB::table('userrole')
            ->where('User_ID', $user->User_ID)
            ->pluck('Role_ID')
            ->toArray();

        // ============================================
        // SUPER ADMIN (Role 1)
        // ============================================
        if (in_array(1, $roleIds)) {
            return redirect()->route('dashboard');
        }

        // ============================================
        // AGENT ADMIN (Role 3) ⭐ CRITICAL FIX
        // ============================================
        if (in_array(3, $roleIds)) {
            // ✅ Check if has active subscription
            $hasActiveSubscription = $this->hasActiveSubscription($user);
            
            // ❌ No subscription → Go to PAYMENT page directly
            if (!$hasActiveSubscription) {
                return redirect()->route('company.payment.create')
                    ->with('info', 'Please complete your subscription to continue.');
            }

            // ✅ Has subscription → Go to CLIENTS page
            return redirect('/clients')
                ->with('success', 'Welcome back!');
        }

        // ============================================
        // ENTITY ADMIN (Role 2)
        // ============================================
        if (in_array(2, $roleIds)) {
            // Check if has active subscription
            $hasActiveSubscription = $this->hasActiveSubscription($user);
            
            if (!$hasActiveSubscription) {
                // Check if has company already
                $hasCompany = DB::table('company_module_users')
                    ->where('User_ID', $user->User_ID)
                    ->exists();
                
                // No company → Company setup first
                if (!$hasCompany) {
                    return redirect()->route('company.setup.create');
                }
                
                // Has company but no subscription → Payment
                return redirect()->route('company.payment.create');
            }

            // Has subscription → Company selection
            return redirect()->route('company.select');
        }

        // ============================================
        // INVOICING APP USER (Role 4)
        // ============================================
        if (in_array(4, $roleIds)) {
            // Check if has active subscription
            $hasActiveSubscription = $this->hasActiveSubscription($user);
            
            if (!$hasActiveSubscription) {
                // Check if has company already
                $hasCompany = DB::table('company_module_users')
                    ->where('User_ID', $user->User_ID)
                    ->exists();
                
                // No company → Company setup first
                if (!$hasCompany) {
                    return redirect()->route('company.setup.create');
                }
                
                // Has company but no subscription → Payment
                return redirect()->route('company.payment.create');
            }

            // Has subscription → Company selection
            return redirect()->route('company.select');
        }

        // ============================================
        // FALLBACK
        // ============================================
        return redirect()->route('dashboard');
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