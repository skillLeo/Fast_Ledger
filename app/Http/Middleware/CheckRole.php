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
  public function handle(Request $request, Closure $next, string ...$roles): Response
{
    if (!Auth::check()) {
        return redirect()->route('sign-in');
    }

    // ✅ Role name => Role_ID mapping (your DB Role_ID values)
    $roleMap = [
            'superadmin' => [1],
            'admin' => [3], // Agent Admin
            'client' => [2], // Entity Admin
            'companyuser' => [4], // Invoicing App
    ];

    // ✅ Get user role IDs from userrole table
    $userRoleIds = DB::table('userrole')
        ->where('User_ID', auth()->user()->User_ID)
        ->pluck('Role_ID')
        ->toArray();

    // ✅ Build allowed Role_IDs from all passed role names
    $allowedRoleIds = [];
    foreach ($roles as $r) {
        $r = trim($r);
        if (isset($roleMap[$r])) {
            $allowedRoleIds = array_merge($allowedRoleIds, $roleMap[$r]);
        }
    }
    $allowedRoleIds = array_unique($allowedRoleIds);

    // ✅ Check access
    if (empty(array_intersect($userRoleIds, $allowedRoleIds))) {
        abort(403, 'Unauthorized access');
    }

    return $next($request);
}
}