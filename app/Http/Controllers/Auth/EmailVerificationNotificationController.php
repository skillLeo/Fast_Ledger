<?php
// app/Http/Controllers/Auth/EmailVerificationNotificationController.php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): RedirectResponse
    {
        // If email is already verified, redirect based on role
        if ($request->user()->hasVerifiedEmail()) {
            return $this->redirectBasedOnRole($request->user());
        }

        // Send verification email
        $request->user()->sendEmailVerificationNotification();

        // Return to verification page with success message
        return back()->with('status', 'verification-link-sent');
    }

    /**
     * Redirect user based on their role
     */
    protected function redirectBasedOnRole($user): RedirectResponse
    {
        // Get user's roles from userrole table
        $roleIds = DB::table('userrole')
            ->where('User_ID', $user->User_ID)
            ->pluck('Role_ID')
            ->toArray();

        // SUPER ADMIN (Role 1)
        if (in_array(1, $roleIds)) {
            return redirect()->route('dashboard');
        }

        // AGENT ADMIN (Role 3)
        if (in_array(3, $roleIds)) {
            $hasCompanies = DB::table('company_module_users')
                ->where('User_ID', $user->User_ID)
                ->where('Is_Active', true)
                ->exists();

            return $hasCompanies 
                ? redirect()->route('company.select')
                : redirect()->route('company.setup.create');
        }

        // ENTITY ADMIN (Role 2)
        if (in_array(2, $roleIds)) {
            $hasCompanies = DB::table('company_module_users')
                ->where('User_ID', $user->User_ID)
                ->where('Is_Active', true)
                ->exists();

            return $hasCompanies 
                ? redirect()->route('company.select')
                : redirect()->route('company.setup.create');
        }

        // INVOICING APP USER (Role 4)
        if (in_array(4, $roleIds)) {
            $hasCompanies = DB::table('company_module_users')
                ->where('User_ID', $user->User_ID)
                ->where('Is_Active', true)
                ->exists();

            return $hasCompanies 
                ? redirect()->route('company.select')
                : redirect()->route('company.setup.create');
        }

        // FALLBACK
        return redirect()->route('dashboard');
    }
}