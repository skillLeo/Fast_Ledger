<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {   
        if (!Auth::check()) {
            return redirect()->route('sign-in'); 
        }
        
        $roles = [
            'superadmin' => [1],
            'admin' => [3],
            'client' => [2],
            'companyuser' => [4],
            // 'company_user' => [5,6],
        ];

        $roleIDs = $roles[$role] ?? [];

        if(!in_array(auth()->user()->User_Role, $roleIDs)){
            abort(code:403);
        }
        return $next($request);
    }
    
}
