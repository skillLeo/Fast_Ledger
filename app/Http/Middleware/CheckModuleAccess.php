<?php
// app/Http/Middleware/CheckModuleAccess.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\CompanyModule\ModuleService;
use Symfony\Component\HttpFoundation\Response;

class CheckModuleAccess
{
    protected ModuleService $moduleService;

    public function __construct(ModuleService $moduleService)
    {
        $this->moduleService = $moduleService;
    }

    public function handle(Request $request, Closure $next, string $moduleName): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // ============================================
        // ✅ ALLOW ONBOARDING ROUTES
        // ============================================
        
        if ($request->routeIs('company.subscription.*')) {
            return $next($request);
        }

        if ($request->routeIs('company.setup.*')) {
            return $next($request);
        }

        if ($request->routeIs('company.payment.*')) {
            return $next($request);
        }

        if ($request->routeIs('company.select', 'company.set-current', 'company.switch')) {
            return $next($request);
        }

        // ============================================
        // ✅ SUBSCRIPTION CHECKS
        // ============================================

        if (!$user->allowed_companies || !$user->subscription_price || !$user->subscription_status) {
            return redirect()->route('company.subscription.setup')
                ->with('info', 'Please complete subscription setup.');
        }

        $hasCompany = $user->companies()->exists();
        if (!$hasCompany) {
            return redirect()->route('company.setup.create')
                ->with('info', 'Please create your company.');
        }

        if ($user->subscription_status === 'pending_payment') {
            return redirect()->route('company.payment.create')
                ->with('info', 'Please complete payment.');
        }

        if (!$user->hasActiveSubscription()) {
            return redirect()->route('dashboard')
                ->with('error', 'Subscription expired.');
        }

        // ============================================
        // ✅ MODULE ACCESS
        // ============================================

        if (!$this->moduleService->hasAccess($user->User_ID, $moduleName)) {
            return redirect()->route('dashboard')
                ->with('error', 'No module access.');
        }

        return $next($request);
    }
}