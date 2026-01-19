<?php
// app/Http/Middleware/EnsureCurrentCompany.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCurrentCompany
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // ============================================
        // ✅ ALLOW ONBOARDING ROUTES (NO RESTRICTIONS)
        // ============================================
        if ($request->routeIs('company.setup.*') || 
            $request->routeIs('company.payment.*') ||
            $request->routeIs('company.select') ||
            $request->routeIs('company.set-current') ||
            $request->routeIs('company.switch')) {
            return $next($request);
        }

        // ============================================
        // ✅ CHECK ONBOARDING COMPLETION
        // ============================================

        // STEP 1: Check if company created
        $hasCompany = $user->companies()->exists();
        if (!$hasCompany) {
            return redirect()->route('company.setup.create')
                ->with('info', 'Please create your company.');
        }

        // STEP 2: Check if payment completed
        if (!$user->subscription_status || $user->subscription_status === 'pending_payment') {
            return redirect()->route('company.payment.create')
                ->with('info', 'Please complete your subscription.');
        }

        // STEP 3: Check active subscription
        if (!$user->hasActiveSubscription()) {
            return redirect()->route('company.index')
                ->with('error', 'Subscription expired.');
        }

        // ============================================
        // ✅ CHECK COMPANY SELECTION (CRITICAL FIX)
        // ============================================
        
        // Only enforce company selection for routes that NEED a specific company
        $routesNeedingCompanyContext = [
            'company.invoices.*',
            'company.customers.*',
            'company.products.*',
            'company.suppliers.*',
            'company.verifactu.*',
        ];

        $needsCompanyContext = false;
        foreach ($routesNeedingCompanyContext as $pattern) {
            if ($request->routeIs($pattern)) {
                $needsCompanyContext = true;
                break;
            }
        }

        // ✅ If route needs company context, enforce selection
        if ($needsCompanyContext) {
            if (!session()->has('current_company_id')) {
                return redirect()->route('company.select')
                    ->with('info', 'Please select a company to continue.');
            }

            $companyId = session('current_company_id');
            if (!$user->hasAccessToCompany($companyId)) {
                session()->forget(['current_company_id', 'current_company_name']);
                return redirect()->route('company.select')
                    ->with('error', 'Access denied to selected company.');
            }

            $company = \App\Models\CompanyModule\Company::find($companyId);
            view()->share('currentCompany', $company);
        }

        return $next($request);
    }
}