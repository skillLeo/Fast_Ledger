<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class SetLocale
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Priority 1: Check if user is authenticated and has language preference
        if (Auth::check() && Auth::user()->language) {
            $locale = Auth::user()->language;
            Session::put('locale', $locale); // Sync with session
        } 
        // Priority 2: Check session
        elseif (Session::has('locale')) {
            $locale = Session::get('locale');
        } 
        // Priority 3: Default to English
        else {
            $locale = 'en';
        }
        
        // Set application locale
        App::setLocale($locale);
        
        return $next($request);
    }
}