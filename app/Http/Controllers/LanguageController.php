<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{
    /**
     * Switch application language
     */
    public function switch(Request $request)
    {
        $locale = $request->input('locale');
        
        // Validate locale
        $availableLocales = ['en', 'es'];
        
        if (!in_array($locale, $availableLocales)) {
            return redirect()->back()->with('error', 'Invalid language selected');
        }
        
        // Store in session
        Session::put('locale', $locale);
        
        // Update user's language preference if authenticated
        if (Auth::check()) {
            Auth::user()->update(['language' => $locale]);
        }
        
        // Set application locale
        App::setLocale($locale);
        
        return redirect()->back()->with('success', 'Language changed successfully');
    }
}