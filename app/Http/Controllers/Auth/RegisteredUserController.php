<?php
// app/Http/Controllers/Auth/RegisteredUserController.php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Auth\Events\Registered;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'account_type' => ['required', 'in:agent_admin,entity_admin,invoicing_app'],
            'Full_Name' => ['required', 'string', 'max:255'],
            'User_Name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:user,email'],
            'language' => ['required', 'string', 'in:en,es'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Determine User_Role and Role_ID based on account_type
        $roleMapping = [
            'agent_admin' => ['User_Role' => 3, 'Role_ID' => 3],
            'entity_admin' => ['User_Role' => 2, 'Role_ID' => 2],
            'invoicing_app' => ['User_Role' => 4, 'Role_ID' => 4],
        ];

        $selectedRole = $roleMapping[$request->account_type];

        // ✅ Create user with NULL subscription fields
        $user = User::create([
            'Full_Name' => $request->Full_Name,
            'User_Name' => $request->User_Name,
            'email' => $request->email,
            'language' => $request->language,
            'password' => Hash::make($request->password),
            'User_Role' => $selectedRole['User_Role'],
            'Is_Active' => true,
            'email_verified_at' => null,
            // ✅ Initialize subscription fields as NULL
            'subscription_status' => null,
            'allowed_companies' => null,
            'subscription_price' => null,
            'payment_frequency' => null,
            'trial_starts_at' => null,
            'trial_ends_at' => null,
        ]);

        // Assign role in userrole table
        DB::table('userrole')->insert([
            'User_ID' => $user->User_ID,
            'Role_ID' => $selectedRole['Role_ID'],
        ]);
        
        // Fire the Registered event (sends verification email)
        event(new Registered($user));

        // Auto-login user
        auth()->login($user);

        // Redirect to verification notice
        return redirect()->route('verification.notice')
            ->with('info', 'Please check your email to verify your account.');
    }
}