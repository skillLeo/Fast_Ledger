<?php
// app/Http/Controllers/Auth/AuthenticatedSessionController.php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

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
            // ✅ No companies - redirect to company setup
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