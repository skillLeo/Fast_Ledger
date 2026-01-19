<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LanguageController;

/*
|--------------------------------------------------------------------------
| Shared Routes
|--------------------------------------------------------------------------
|
| Routes that are shared across the entire application (main app, company module, etc.)
|
*/

Route::middleware(['auth'])->group(function () {
    
    // ============================================
    // LANGUAGE SWITCHING
    // ============================================
    Route::post('/language/switch', [LanguageController::class, 'switch'])
        ->name('language.switch');
    
    // Add other shared functionality here in the future
    // Examples: notifications, file uploads, user profile, etc.
});