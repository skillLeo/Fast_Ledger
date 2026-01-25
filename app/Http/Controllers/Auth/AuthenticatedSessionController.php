<?php
// app/Http/Controllers/Auth/AuthenticatedSessionController.php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Carbon\Carbon;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // ✅ Get authenticated user
        $user = Auth::user();

        // ✅ Check if user has companies
        $companies = $user->companies()->get();

        if ($companies->isEmpty()) {
    // If the user's role is 3 (agent_admin), redirect to the payment route
  if ($user->User_Role == 3) {

            // Get the current date
            $currentDate = Carbon::now();

            // Check if the trial or subscription has ended
            if ($user->trial_ent_at && Carbon::parse($user->trial_ent_at)->lt($currentDate) || 
                $user->subscription_end_at && Carbon::parse($user->subscription_end_at)->lt($currentDate)) {
                
                // Redirect to the payment page if trial or subscription has ended
                return redirect()->route('company.payment.create');
            }

            // If trial or subscription is still valid, redirect to the clients page
            return redirect()->route('clients.index');
        }
    
    // If the role is not 3, redirect to company setup
    return redirect()->route('company.setup.create');
}

        // ✅ Check if user has selected a company in session
        if (!session()->has('current_company_id')) {
            // ✅ Multiple companies or no selection - go to company select
            if ($companies->count() > 1) {
                return redirect()->route('company.select');
            }

            // ✅ Only one company - auto-select it
            $company = $companies->first();
            session([
                'current_company_id' => $company->id,
                'current_company_name' => $company->Company_Name,
            ]);
        }

        // ✅ Get current company from session
        $companyId = session('current_company_id');

        // ✅ Verify user still has access to this company
        if (!$user->hasAccessToCompany($companyId)) {
            session()->forget(['current_company_id', 'current_company_name']);
            return redirect()->route('company.select');
        }

        // ✅ Redirect to company dashboard with company ID
        return redirect()->route('company.select');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}