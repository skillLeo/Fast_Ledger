<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Hmrc\OAuthController;
use App\Http\Controllers\Hmrc\VatReturnController;
use App\Http\Controllers\Hmrc\VatDashboardController;
use App\Http\Controllers\Hmrc\HmrcAnnualSubmissionController;
use App\Http\Controllers\Hmrc\HmrcAuthController;
use App\Http\Controllers\Hmrc\HmrcBusinessController;
use App\Http\Controllers\Hmrc\HmrcCalculationController;
use App\Http\Controllers\Hmrc\HmrcFinalDeclarationController;
use App\Http\Controllers\Hmrc\HmrcObligationController;
use App\Http\Controllers\Hmrc\HmrcPeriodicSubmissionController;
use App\Http\Controllers\Hmrc\HmrcUkPropertyAnnualSubmissionController;
use App\Http\Controllers\Hmrc\HmrcUkPropertyPeriodSummaryController;

/*
|--------------------------------------------------------------------------
| HMRC Routes (Laravel 11)
|--------------------------------------------------------------------------
*/


Route::prefix('hmrc')->name('hmrc.')->group(function () {

    // OAuth Routes (Public - No Authentication Required)
    Route::controller(OAuthController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/connect', 'connect')->name('connect');
        Route::get('/callback', 'callback')->name('callback');
    });

    Route::get('/auth/callback', [HmrcAuthController::class, 'callback'])->name('hmrc.auth.callback');
    // Protected Routes (Require Laravel Authentication)
    Route::middleware(['auth'])->group(function () {

        // OAuth Management (Auth required but no HMRC token needed)
        Route::controller(OAuthController::class)->group(function () {
            Route::post('/disconnect', 'disconnect')->name('disconnect');
            Route::get('/status', 'status')->name('status');
        });

        // Dashboard Routes (Auth required, but HMRC token optional)
        Route::prefix('vat')->name('vat.')->group(function () {
            Route::controller(VatDashboardController::class)->group(function () {
                Route::get('/dashboard', 'index')->name('dashboard');
                Route::get('/obligations', 'obligations')->name('obligations');
                Route::get('/payments', 'payments')->name('payments');
                Route::get('/liabilities', 'liabilities')->name('liabilities');
            });
        });

        // VAT Routes (Require both Auth + Valid HMRC Token)
        Route::middleware(['hmrc.token'])->prefix('vat')->name('vat.')->group(function () {

            // VAT Review route (requires HMRC connection)
            Route::controller(VatDashboardController::class)->group(function () {
                Route::get('/review/{periodKey}', 'review')->name('review');
            });

            // VAT Returns Routes
            Route::controller(VatReturnController::class)->prefix('returns')->name('returns.')->group(function () {
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{periodKey}', 'show')->name('show');
            });
        });

        // OAuth routes
        Route::prefix('auth')->name('auth.')->group(function () {
            Route::get('/', [HmrcAuthController::class, 'index'])->name('index');
            Route::get('/connect', [HmrcAuthController::class, 'connect'])->name('connect');
            Route::get('/redirect', [HmrcAuthController::class, 'redirect'])->name('redirect');
            Route::post('/disconnect', [HmrcAuthController::class, 'disconnect'])->name('disconnect');
            Route::get('/test', [HmrcAuthController::class, 'test'])->name('test');
        });

        // Business routes
        Route::get('/businesses', [HmrcBusinessController::class, 'index'])->name('businesses.index');
        Route::post('/businesses/sync', [HmrcBusinessController::class, 'sync'])->name('businesses.sync');
        Route::get('/businesses/{business}', [HmrcBusinessController::class, 'show'])->name('businesses.show');

        // Obligations routes
        Route::prefix('obligations')->name('obligations.')->group(function () {
            Route::get('/', [HmrcObligationController::class, 'index'])->name('index');
            Route::get('/list', [HmrcObligationController::class, 'list'])->name('list');
            Route::get('/calendar', [HmrcObligationController::class, 'calendar'])->name('calendar');
            Route::get('/export', [HmrcObligationController::class, 'export'])->name('export');
            Route::post('/sync', [HmrcObligationController::class, 'sync'])->name('sync');
            Route::get('/{obligation}', [HmrcObligationController::class, 'show'])->name('show');
        });

        // Periodic Submissions routes
        Route::prefix('submissions')->name('submissions.')->group(function () {
            Route::get('/', [HmrcPeriodicSubmissionController::class, 'index'])->name('index');
            Route::get('/create', [HmrcPeriodicSubmissionController::class, 'create'])->name('create');
            Route::post('/', [HmrcPeriodicSubmissionController::class, 'store'])->name('store');
            Route::get('/export', [HmrcPeriodicSubmissionController::class, 'export'])->name('export');
            Route::post('/profit-loss-data', [HmrcPeriodicSubmissionController::class, 'getProfitLossData'])->name('profit-loss-data');
            Route::get('/{submission}', [HmrcPeriodicSubmissionController::class, 'show'])->name('show');
            Route::get('/{submission}/edit', [HmrcPeriodicSubmissionController::class, 'edit'])->name('edit');
            Route::put('/{submission}', [HmrcPeriodicSubmissionController::class, 'update'])->name('update');
            Route::post('/{submission}/submit', [HmrcPeriodicSubmissionController::class, 'submit'])->name('submit');
            Route::delete('/{submission}', [HmrcPeriodicSubmissionController::class, 'destroy'])->name('destroy');
        });

        // Annual Submissions routes
        Route::prefix('annual-submissions')->name('annual-submissions.')->group(function () {
            Route::get('/', [HmrcAnnualSubmissionController::class, 'index'])->name('index');
            Route::get('/create', [HmrcAnnualSubmissionController::class, 'create'])->name('create');
            Route::post('/', [HmrcAnnualSubmissionController::class, 'store'])->name('store');
            Route::get('/export', [HmrcAnnualSubmissionController::class, 'export'])->name('export');
            Route::post('/quarterly-summary', [HmrcAnnualSubmissionController::class, 'getQuarterlySummary'])->name('quarterly-summary');
            Route::get('/{annualSubmission}', [HmrcAnnualSubmissionController::class, 'show'])->name('show');
            Route::get('/{annualSubmission}/edit', [HmrcAnnualSubmissionController::class, 'edit'])->name('edit');
            Route::put('/{annualSubmission}', [HmrcAnnualSubmissionController::class, 'update'])->name('update');
            Route::post('/{annualSubmission}/submit', [HmrcAnnualSubmissionController::class, 'submit'])->name('submit');
            Route::delete('/{annualSubmission}', [HmrcAnnualSubmissionController::class, 'destroy'])->name('destroy');
        });

        // Tax Calculations routes
        Route::prefix('calculations')->name('calculations.')->group(function () {
            Route::get('/', [HmrcCalculationController::class, 'index'])->name('index');
            Route::get('/create', [HmrcCalculationController::class, 'create'])->name('create');
            Route::post('/', [HmrcCalculationController::class, 'store'])->name('store');
            Route::get('/export', [HmrcCalculationController::class, 'export'])->name('export');
            Route::post('/sync', [HmrcCalculationController::class, 'sync'])->name('sync');
            Route::get('/{calculation}', [HmrcCalculationController::class, 'show'])->name('show');
            Route::post('/{calculation}/refresh', [HmrcCalculationController::class, 'refresh'])->name('refresh');
            Route::delete('/{calculation}', [HmrcCalculationController::class, 'destroy'])->name('destroy');
        });

        // HMRC Final Declaration (Crystallisation) routes
        Route::prefix('final-declaration/{taxYear}')->name('final-declaration.')->group(function () {
            // Main wizard entry
            Route::get('/', [HmrcFinalDeclarationController::class, 'index'])
                ->name('index');

            // Step 1: Prerequisites
            Route::get('/prerequisites', [HmrcFinalDeclarationController::class, 'checkPrerequisites'])
                ->name('prerequisites-check');

            // Step 2: Review submissions
            Route::get('/review-submissions', [HmrcFinalDeclarationController::class, 'reviewSubmissions'])
                ->name('review-submissions');

            // Step 3: Review calculation
            Route::get('/review-calculation', [HmrcFinalDeclarationController::class, 'reviewCalculation'])
                ->name('review-calculation');

            // Step 4: Review income
            Route::get('/review-income', [HmrcFinalDeclarationController::class, 'reviewIncome'])
                ->name('review-income');

            // Step 5: Declaration
            Route::get('/declaration', [HmrcFinalDeclarationController::class, 'declaration'])
                ->name('declaration');

            // Complete step (AJAX)
            Route::post('/complete-step/{step}', [HmrcFinalDeclarationController::class, 'completeStep'])
                ->name('complete-step');

            // Confirm declaration (AJAX)
            Route::post('/confirm', [HmrcFinalDeclarationController::class, 'confirmDeclaration'])
                ->name('confirm');

            // Submit to HMRC
            Route::post('/submit', [HmrcFinalDeclarationController::class, 'submit'])
                ->name('submit');

            // Confirmation page
            Route::get('/confirmation/{declaration}', [HmrcFinalDeclarationController::class, 'confirmation'])
                ->name('confirmation');
        });

        Route::prefix('uk-property-annual-submissions')->name('uk-property-annual-submissions.')->group(function () {

            // List all annual submissions
            Route::get('/', [HmrcUkPropertyAnnualSubmissionController::class, 'index'])
                ->name('index');

            // Create new annual submission
            Route::get('/create', [HmrcUkPropertyAnnualSubmissionController::class, 'create'])
                ->name('create');

            // Store new annual submission
            Route::post('/', [HmrcUkPropertyAnnualSubmissionController::class, 'store'])
                ->name('store');

            // Show specific annual submission
            Route::get('/{submission}', [HmrcUkPropertyAnnualSubmissionController::class, 'show'])
                ->name('show');

            // Edit annual submission
            Route::get('/{submission}/edit', [HmrcUkPropertyAnnualSubmissionController::class, 'edit'])
                ->name('edit');

            // Update annual submission
            Route::put('/{submission}', [HmrcUkPropertyAnnualSubmissionController::class, 'update'])
                ->name('update');

            // Preview HMRC API payload
            Route::get('/{submission}/preview-payload', [HmrcUkPropertyAnnualSubmissionController::class, 'previewPayload'])
                ->name('preview-payload');

            // Submit annual submission to HMRC
            Route::post('/{submission}/submit', [HmrcUkPropertyAnnualSubmissionController::class, 'submit'])
                ->name('submit');

            // Delete annual submission
            Route::delete('/{submission}', [HmrcUkPropertyAnnualSubmissionController::class, 'destroy'])
                ->name('destroy');
        });

        /*
        |--------------------------------------------------------------------------
        | UK Property Period Summaries Routes
        |--------------------------------------------------------------------------
        |
        | Routes for managing UK Property Period Summaries (quarterly submissions).
        | These include CRUD operations and submission to HMRC API.
        |
        */

        Route::prefix('uk-property-period-summaries')->name('uk-property-period-summaries.')->group(function () {
            // List all period summaries
            Route::get('/', [HmrcUkPropertyPeriodSummaryController::class, 'index'])
                ->name('index');

            // Create new period summary
            Route::get('/create', [HmrcUkPropertyPeriodSummaryController::class, 'create'])
                ->name('create');

            // Store new period summary
            Route::post('/', [HmrcUkPropertyPeriodSummaryController::class, 'store'])
                ->name('store');

            // Show specific period summary
            Route::get('/{summary}', [HmrcUkPropertyPeriodSummaryController::class, 'show'])
                ->name('show');

            // Edit period summary
            Route::get('/{summary}/edit', [HmrcUkPropertyPeriodSummaryController::class, 'edit'])
                ->name('edit');

            // Update period summary
            Route::put('/{summary}', [HmrcUkPropertyPeriodSummaryController::class, 'update'])
                ->name('update');

            // Submit period summary to HMRC
            Route::post('/{summary}/submit', [HmrcUkPropertyPeriodSummaryController::class, 'submit'])
                ->name('submit');

            // Amend submitted period summary
            Route::get('/{summary}/amend', [HmrcUkPropertyPeriodSummaryController::class, 'amend'])
                ->name('amend');

            // Submit amendment to HMRC
            Route::put('/{summary}/amend', [HmrcUkPropertyPeriodSummaryController::class, 'amendSubmit'])
                ->name('amend-submit');

            // Delete period summary
            Route::delete('/{summary}', [HmrcUkPropertyPeriodSummaryController::class, 'destroy'])
                ->name('destroy');
        });
    });
});
