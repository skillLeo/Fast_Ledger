<?php
// app/Http/Controllers/SubscriptionController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{
    /**
     * Toggle auto-renewal on/off
     */
    public function toggleRenewal(Request $request)
    {
        $request->validate([
            'enabled' => 'required|boolean',
        ]);

        $user = auth()->user();
        $enabled = $request->enabled;

        try {
            // ✅ STEP 1: Check current value BEFORE update
            $beforeValue = DB::table('user')
                ->where('User_ID', $user->User_ID)
                ->value('auto_renewal');

            Log::info('🔍 Toggle Request', [
                'user_id' => $user->User_ID,
                'email' => $user->email,
                'requested_value' => $enabled,
                'current_db_value' => $beforeValue,
            ]);

            // ✅ STEP 2: Update database
            DB::beginTransaction();

            $updated = DB::table('user')
                ->where('User_ID', $user->User_ID)
                ->update([
                    'auto_renewal' => $enabled ? 1 : 0,
                    'Modified_On' => now(),
                ]);

            // ✅ STEP 3: Verify the update worked
            $afterValue = DB::table('user')
                ->where('User_ID', $user->User_ID)
                ->value('auto_renewal');

            Log::info('✅ Update Result', [
                'rows_updated' => $updated,
                'before_value' => $beforeValue,
                'after_value' => $afterValue,
                'expected_value' => $enabled ? 1 : 0,
            ]);

            // ✅ STEP 4: Check if update actually changed the value
            $expectedValue = $enabled ? 1 : 0;
            if ($afterValue != $expectedValue) {
                DB::rollBack();
                
                Log::error('❌ Update verification failed', [
                    'expected' => $expectedValue,
                    'actual' => $afterValue,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update auto-renewal setting. Database value did not change.',
                    'debug' => [
                        'before' => $beforeValue,
                        'after' => $afterValue,
                        'expected' => $expectedValue,
                    ],
                ], 500);
            }

            DB::commit();

            // ✅ Get next billing date
            $nextBilling = DB::table('user')
                ->where('User_ID', $user->User_ID)
                ->value('next_billing_date');

            $message = $enabled 
                ? 'Auto-renewal enabled. Your subscription will renew automatically.'
                : 'Auto-renewal disabled. Your subscription will expire on ' . 
                  ($nextBilling ? date('M d, Y', strtotime($nextBilling)) : 'the end of your billing period');

            return response()->json([
                'success' => true,
                'message' => $message,
                'auto_renewal' => $enabled,
                'verified_value' => $afterValue, // ✅ Send back the actual DB value
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('❌ Toggle failed', [
                'user_id' => $user->User_ID,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update auto-renewal: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cancel subscription (disable auto-renewal)
     */
    public function cancel(Request $request)
    {
        $user = auth()->user();

        if ($user->subscription_status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Subscription is already cancelled.',
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Disable auto-renewal
            $updated = DB::table('user')
                ->where('User_ID', $user->User_ID)
                ->update([
                    'auto_renewal' => 0,
                    'Modified_On' => now(),
                ]);

            // Verify
            $afterValue = DB::table('user')
                ->where('User_ID', $user->User_ID)
                ->value('auto_renewal');

            if ($afterValue != 0) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to cancel subscription.',
                ], 500);
            }

            DB::commit();

            $nextBilling = DB::table('user')
                ->where('User_ID', $user->User_ID)
                ->value('next_billing_date');

            Log::info('Subscription cancelled', [
                'user_id' => $user->User_ID,
                'next_billing' => $nextBilling,
            ]);

            $message = $nextBilling 
                ? "Subscription cancelled. You can continue using the service until " . date('M d, Y', strtotime($nextBilling))
                : 'Subscription cancelled. You can continue using the service until the end of your billing period.';

            return response()->json([
                'success' => true,
                'message' => $message,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to cancel subscription', [
                'user_id' => $user->User_ID,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel subscription.',
            ], 500);
        }
    }

    /**
     * Reactivate subscription (enable auto-renewal)
     */
    public function reactivate(Request $request)
    {
        $user = auth()->user();

        $currentAutoRenewal = DB::table('user')
            ->where('User_ID', $user->User_ID)
            ->value('auto_renewal');

        if ($currentAutoRenewal == 1) {
            return response()->json([
                'success' => false,
                'message' => 'Auto-renewal is already enabled.',
            ], 400);
        }

        $nextBilling = DB::table('user')
            ->where('User_ID', $user->User_ID)
            ->value('next_billing_date');

        if ($nextBilling && strtotime($nextBilling) < time()) {
            return response()->json([
                'success' => false,
                'message' => 'Your subscription has expired. Please renew to continue.',
            ], 400);
        }

        try {
            DB::beginTransaction();

            DB::table('user')
                ->where('User_ID', $user->User_ID)
                ->update([
                    'auto_renewal' => 1,
                    'Modified_On' => now(),
                ]);

            // Verify
            $afterValue = DB::table('user')
                ->where('User_ID', $user->User_ID)
                ->value('auto_renewal');

            if ($afterValue != 1) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to reactivate subscription.',
                ], 500);
            }

            DB::commit();

            Log::info('Subscription reactivated', [
                'user_id' => $user->User_ID,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Auto-renewal reactivated. Your subscription will continue automatically.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to reactivate subscription', [
                'user_id' => $user->User_ID,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to reactivate subscription.',
            ], 500);
        }
    }
}