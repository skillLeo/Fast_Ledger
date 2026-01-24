<?php
// app/Http/Middleware/CheckRole.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {   
        if (!Auth::check()) {
            return redirect()->route('sign-in'); 
        }
        
        $roles = [
            'superadmin' => [1],
            'admin' => [3], // Agent Admin
            'client' => [2], // Entity Admin
            'companyuser' => [4], // Invoicing App
        ];

        $allowedRoleIds = $roles[$role] ?? [];

        // ✅ Get user's roles from userrole table
        $userRoleIds = DB::table('userrole')
            ->where('User_ID', auth()->user()->User_ID)
            ->pluck('Role_ID')
            ->toArray();

        // ✅ Check if user has any of the allowed roles
        $hasAccess = false;
        foreach ($allowedRoleIds as $roleId) {
            if (in_array($roleId, $userRoleIds)) {
                $hasAccess = true;
                break;
            }
        }

        if (!$hasAccess) {
            abort(403, 'Unauthorized access');
        }

        return $next($request);
    }
}