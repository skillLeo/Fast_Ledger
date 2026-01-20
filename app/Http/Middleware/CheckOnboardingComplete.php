<?php
// app/Http/Middleware/CheckOnboardingComplete.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckOnboardingComplete
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user) {
            return $next($request);
        }

        // ✅ CRITICAL: These routes are ALWAYS allowed
        $alwaysAllowedRoutes = [
            'company.setup.*',
            'company.payment.*',
            'verification.*',
            'logout',
            'login',
            'register',
        ];

        foreach ($alwaysAllowedRoutes as $pattern) {
            if ($request->routeIs($pattern)) {
                return $next($request);
            }
        }

        // ============================================
        // STEP 1: Check if user has ANY companies
        // ============================================
        $hasCompanies = $user->companies()->exists();

        if (!$hasCompanies) {
            // ❌ User has NO companies → FORCE to company setup
            return redirect()->route('company.setup.create')
                ->with('warning', 'Please create your company to continue.');
        }

        // ============================================
        // STEP 2: Check if user has ACTIVE subscription
        // ============================================
        $hasActiveSubscription = false;
        $status = $user->subscription_status;

        // Check trial
        if ($status === 'trial' && $user->trial_ends_at && now()->lt($user->trial_ends_at)) {
            $hasActiveSubscription = true;
        }

        // Check paid subscription
        if ($status === 'active' && $user->next_billing_date && now()->lt($user->next_billing_date)) {
            $hasActiveSubscription = true;
        }

        if (!$hasActiveSubscription) {
            // ❌ User has NO active subscription → FORCE to payment
            return redirect()->route('company.payment.create')
                ->with('warning', 'Please complete your subscription to access the platform.');
        }

        // ✅ User has BOTH company AND active subscription → Allow access
        return $next($request);
    }
}