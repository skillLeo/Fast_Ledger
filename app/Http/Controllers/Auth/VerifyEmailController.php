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

        // Super Admin
        if (in_array(1, $roleIds)) {
            return redirect()->route('dashboard')
                ->with('verified', true)
                ->with('success', 'Email verified!');
        }

        // Company module users
        if (in_array(3, $roleIds) || in_array(2, $roleIds) || in_array(4, $roleIds)) {
            return $this->handleCompanyModuleUser($user);
        }

        return redirect()->route('dashboard')
            ->with('verified', true);
    }

    protected function handleCompanyModuleUser($user): RedirectResponse
    {
        // ✅ STEP 1: Check if company created
        $hasCompanies = DB::table('company_module_users')
            ->where('User_ID', $user->User_ID)
            ->where('Is_Active', true)
            ->exists();

        if (!$hasCompanies) {
            // ✅ Go directly to company setup (subscription removed)
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

        // Everything complete
        return redirect()->route('company.select')
            ->with('verified', true)
            ->with('success', 'Welcome back!');
    }
}