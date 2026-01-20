<?php
// app/Http/Middleware/CheckSubscriptionStatus.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CheckSubscriptionStatus
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        if (!$user) {
            return $next($request);
        }

        // ✅ Check if subscription has expired
        if ($user->subscription_status === 'active' && 
            $user->next_billing_date && 
            Carbon::parse($user->next_billing_date)->isPast() && 
            $user->auto_renewal != 1) { // ✅ Use != 1 instead of !
            
            // Subscription expired because auto-renewal was off
            DB::beginTransaction();

            DB::table('user')
                ->where('User_ID', $user->User_ID)
                ->update([
                    'subscription_status' => 'expired',
                    'Modified_On' => now(),
                ]);

            // Deactivate all companies
            DB::table('company_module_users')
                ->where('User_ID', $user->User_ID)
                ->update([
                    'Is_Active' => 0, // ✅ Use 0 instead of false
                    'Modified_On' => now(),
                ]);

            DB::commit();

            return redirect()->route('company.payment.create')
                ->with('error', 'Your subscription has expired. Please renew to continue.');
        }

        // Check if payment failed status
        if (in_array($user->subscription_status, ['payment_failed', 'expired'])) {
            return redirect()->route('company.payment.create')
                ->with('error', 'Please update your subscription to continue.');
        }

        return $next($request);
    }
}