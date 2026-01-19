<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use App\Models\CompanyModule\Company;

class CheckCompanyRole
{
    public function handle(Request $request, Closure $next, string ...$allowedRoles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // ✅ FIX: Properly extract company ID from route parameter
        $company = $request->route('company');
        
        // If it's a Company model instance, get its ID
        if ($company instanceof Company) {
            $companyId = $company->id;
        } else {
            // Fallback to other methods
            $companyId = $company 
                ?? $request->route('id') 
                ?? session('current_company_id') 
                ?? $request->input('company_id');
        }

        if (!$companyId) {
            return redirect()->route('company.select')
                ->with('error', 'Please select a company first.');
        }

        $userId = auth()->id();

        // Get user's role in this company
        $companyUser = DB::table('company_module_users')
            ->where('Company_ID', $companyId)
            ->where('User_ID', $userId)
            ->where('Is_Active', true)
            ->first();

        // Check 1: Does user have ANY access to this company?
        if (!$companyUser) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'You do not have access to this company',
                    'redirect' => route('company.select'),
                ], 403);
            }

            return redirect()->route('company.select')
                ->with('error', 'You do not have access to this company.');
        }

        // Check 2: Does user have the REQUIRED role?
        if (!in_array($companyUser->Role, $allowedRoles)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'You do not have permission to perform this action',
                    'required_role' => implode(' or ', $allowedRoles),
                    'your_role' => $companyUser->Role,
                ], 403);
            }

            return redirect()->route('company.dashboard', $companyId)
                ->with('error', 'You do not have permission to perform this action.');
        }

        // Store company info in request for easy access
        $request->merge([
            '_company_id' => $companyId,
            '_company_role' => $companyUser->Role,
            '_is_owner' => $companyUser->Role === 'owner',
            '_is_admin' => in_array($companyUser->Role, ['owner', 'admin']),
        ]);

        return $next($request);
    }
}