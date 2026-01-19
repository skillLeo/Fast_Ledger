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
     * Redirect user based on their role
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
        // AGENT ADMIN (Role 3)
        // ============================================
        if (in_array(3, $roleIds)) {
            $hasCompanies = DB::table('company_module_users')
                ->where('User_ID', $user->User_ID)
                ->where('Is_Active', true)
                ->exists();

            if (!$hasCompanies) {
                return redirect()->route('company.setup.create');
            }

            return redirect()->route('company.select');
        }

        // ============================================
        // ENTITY ADMIN (Role 2)
        // ============================================
        if (in_array(2, $roleIds)) {
            $hasCompanies = DB::table('company_module_users')
                ->where('User_ID', $user->User_ID)
                ->where('Is_Active', true)
                ->exists();

            if (!$hasCompanies) {
                return redirect()->route('company.setup.create');
            }

            return redirect()->route('company.select');
        }

        // ============================================
        // INVOICING APP USER (Role 4)
        // ============================================
        if (in_array(4, $roleIds)) {
            $hasCompanies = DB::table('company_module_users')
                ->where('User_ID', $user->User_ID)
                ->where('Is_Active', true)
                ->exists();

            if (!$hasCompanies) {
                return redirect()->route('company.setup.create');
            }

            return redirect()->route('company.select');
        }

        // ============================================
        // FALLBACK
        // ============================================
        return redirect()->route('dashboard');
    }
}