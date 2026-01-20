<?php
// routes/modules/company.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InvoiceTemplateController;
use App\Http\Controllers\CompanyModule\CompanyController;
use App\Http\Controllers\CompanyModule\CustomerController;
use App\Http\Controllers\CompanyModule\CompanyUserController;
use App\Http\Controllers\CompanyModule\CompanySetupController;
use App\Http\Controllers\CompanyModule\CompanyInvoiceController;
use App\Http\Controllers\CompanyModule\CompanyDashboardController;
use App\Http\Controllers\CompanyModule\CompanySelectionController;
use App\Http\Controllers\CompanyModule\Verifactu\ConnectionController;
use App\Http\Controllers\CompanyModule\PaymentController;

Route::middleware(['auth'])->prefix('company')->name('company.')->group(function () {

    // ============================================
    // ONBOARDING ROUTES (NO ONBOARDING MIDDLEWARE)
    // These are ALWAYS accessible
    // ============================================
    
    // Company Setup Routes
    Route::prefix('setup')->name('setup.')->group(function () {
        Route::get('/choice', [CompanySetupController::class, 'showChoice'])
            ->name('choice');
        
        Route::get('/create', [CompanySetupController::class, 'create'])
            ->name('create');
        
        Route::post('/store', [CompanySetupController::class, 'store'])
            ->name('store');
        
        Route::get('/success/{company}', [CompanySetupController::class, 'success'])
            ->name('success');
        
        Route::post('/skip', [CompanySetupController::class, 'skip'])
            ->name('skip');
    });

    // Payment Routes
    Route::prefix('payment')->name('payment.')->group(function () {
        Route::get('/create', [PaymentController::class, 'create'])
            ->name('create');
        
        Route::post('/create-intent', [PaymentController::class, 'createPaymentIntent'])
            ->name('create-intent');
        
        Route::post('/store', [PaymentController::class, 'store'])
            ->name('store');
    });

    // Subscription Routes (legacy)
    Route::prefix('subscription')->name('subscription.')->group(function () {
        Route::get('/setup', [CompanySetupController::class, 'showSubscription'])
            ->name('setup');
        
        Route::post('/store', [CompanySetupController::class, 'storeSubscription'])
            ->name('store');
    });

    // ============================================
    // ✅ ALL ROUTES BELOW REQUIRE ONBOARDING COMPLETE
    // User must have company + active subscription
    // ============================================
    Route::middleware(['onboarding'])->group(function () {

        // ============================================
        // COMPANY SELECTION
        // ============================================
        Route::get('/select', [CompanySelectionController::class, 'select'])
            ->name('select');

        Route::post('/set-current/{company}', [CompanySelectionController::class, 'setCurrentCompany'])
            ->name('set-current');

        Route::post('/switch', [CompanySelectionController::class, 'switchCompany'])
            ->name('switch');

        // ============================================
        // MODULE ROUTES (Requires module + current company)
        // ============================================
        Route::middleware(['module:company_module', 'current.company'])->group(function () {

            // Company List
            Route::get('/', [CompanyController::class, 'index'])
                ->name('index');

            // Create New Company
            Route::get('/create', [CompanyController::class, 'create'])
                ->name('create');
            Route::post('/', [CompanyController::class, 'store'])
                ->name('store');

            // API ENDPOINTS
            Route::get('/customers-dropdown', [CompanyInvoiceController::class, 'getCustomersDropdown'])
                ->name('api.customers.dropdown');

            Route::post('/generate-auto-code', [CompanyInvoiceController::class, 'generateAutoCodeAjax'])
                ->middleware('company.role:owner,admin,accountant')
                ->name('generate-auto-code');

            Route::post('/check-code-unique', [CompanyInvoiceController::class, 'checkCodeUnique'])
                ->middleware('company.role:owner,admin,accountant')
                ->name('check-code-unique');

            // ============================================
            // SUPPLIER ROUTES
            // ============================================
            Route::prefix('suppliers')->name('suppliers.')->group(function () {
                Route::middleware(['company.role:owner,admin,accountant'])->group(function () {
                    Route::get('/', [\App\Http\Controllers\SupplierController::class, 'index'])
                        ->name('index');
                    Route::get('/create', [\App\Http\Controllers\SupplierController::class, 'create'])
                        ->name('create');
                    Route::post('/', [\App\Http\Controllers\SupplierController::class, 'store'])
                        ->name('store');
                });

                Route::prefix('{supplier}')->group(function () {
                    Route::get('/', [\App\Http\Controllers\SupplierController::class, 'show'])
                        ->middleware('company.role:owner,admin,accountant,viewer')
                        ->name('show');

                    Route::middleware(['company.role:owner,admin,accountant'])->group(function () {
                        Route::get('/edit', [\App\Http\Controllers\SupplierController::class, 'edit'])
                            ->name('edit');
                        Route::put('/', [\App\Http\Controllers\SupplierController::class, 'update'])
                            ->name('update');
                    });

                    Route::delete('/', [\App\Http\Controllers\SupplierController::class, 'destroy'])
                        ->middleware('company.role:owner,admin')
                        ->name('destroy');
                });
            });

            Route::get('/suppliers-dropdown', [\App\Http\Controllers\SupplierController::class, 'getSuppliersDropdown'])
                ->middleware('company.role:owner,admin,accountant')
                ->name('api.suppliers.dropdown');

            // ============================================
            // INVOICE ROUTES
            // ============================================
            Route::prefix('invoices')->name('invoices.')->group(function () {
                Route::post('/generate-auto-code', [CompanyInvoiceController::class, 'generateAutoCodeAjax'])
                    ->name('generate-auto-code');

                Route::middleware(['company.role:owner,admin,accountant'])->group(function () {
                    Route::get('/', [CompanyInvoiceController::class, 'index'])
                        ->name('index');
                    Route::get('/create', [CompanyInvoiceController::class, 'create'])
                        ->name('create');
                    Route::post('/', [CompanyInvoiceController::class, 'store'])
                        ->name('store');
                });

                Route::middleware(['company.role:owner,admin,accountant,viewer'])->group(function () {
                    Route::get('/activity-logs', [CompanyInvoiceController::class, 'activityLogIndex'])
                        ->name('activity-logs.index');
                    Route::get('/all-activity-logs', [CompanyInvoiceController::class, 'getAllInvoiceActivityLogs'])
                        ->name('all-activity-logs');
                });

                Route::prefix('{invoice}')->group(function () {
                    Route::get('/view', [CompanyInvoiceController::class, 'view'])
                        ->middleware('company.role:owner,admin,accountant,viewer')
                        ->name('view');
                    Route::get('/get-invoice-data', [CompanyInvoiceController::class, 'getInvoiceData'])
                        ->middleware('company.role:owner,admin,accountant,viewer')
                        ->name('get-invoice-data');
                    Route::get('/download-pdf', [CompanyInvoiceController::class, 'downloadPDF'])
                        ->middleware('company.role:owner,admin,accountant,viewer')
                        ->name('download-pdf');
                    Route::get('/view-pdf', [CompanyInvoiceController::class, 'viewPDF'])
                        ->middleware('company.role:owner,admin,accountant,viewer')
                        ->name('view-pdf');
                    Route::get('/edit', [CompanyInvoiceController::class, 'edit'])
                        ->middleware('company.role:owner,admin,accountant')
                        ->name('edit');
                    Route::delete('/', [CompanyInvoiceController::class, 'destroy'])
                        ->middleware('company.role:owner,admin')
                        ->name('destroy');
                    Route::get('/status-details', [CompanyInvoiceController::class, 'getStatusDetails'])
                        ->middleware('company.role:owner,admin,accountant')
                        ->name('status-details');
                    Route::post('/update-status', [CompanyInvoiceController::class, 'updateStatus'])
                        ->middleware('company.role:owner,admin,accountant')
                        ->name('update-status');
                    Route::get('/documents', [CompanyInvoiceController::class, 'getDocuments'])
                        ->middleware('company.role:owner,admin,accountant,viewer')
                        ->name('documents');
                    Route::get('/activity-log', [CompanyInvoiceController::class, 'getInvoiceActivityLog'])
                        ->middleware('company.role:owner,admin,accountant,viewer')
                        ->name('activity-log');
                });
            });

            // ============================================
            // INVOICE TEMPLATE ROUTES
            // ============================================
            Route::prefix('invoices/templates')->name('invoices.templates.')->group(function () {
                Route::get('/gallery', [InvoiceTemplateController::class, 'gallery'])
                    ->name('gallery');
                Route::get('/create', [InvoiceTemplateController::class, 'createTemplate'])
                    ->name('create');
                Route::post('preview', [InvoiceTemplateController::class, 'preview'])
                    ->name('preview');
                Route::get('preview/{draft}', [InvoiceTemplateController::class, 'showPreview'])
                    ->name('preview.show');
                Route::get('preview/{draft}/customize', [InvoiceTemplateController::class, 'showCustomizationMode'])
                    ->name('preview.customize');
                Route::post('preview/ajax', [InvoiceTemplateController::class, 'previewAjax'])
                    ->name('preview.ajax');
                Route::post('preview/download/pdf', [InvoiceTemplateController::class, 'downloadPdf'])
                    ->name('preview.download.pdf');
                Route::post('save', [InvoiceTemplateController::class, 'saveTemplate'])
                    ->name('save');
                Route::post('upload-logo', [InvoiceTemplateController::class, 'uploadLogo'])
                    ->name('uploadLogo');
                Route::get('load/{id}', [InvoiceTemplateController::class, 'loadTemplate'])
                    ->name('load');
                Route::delete('delete/{id}', [InvoiceTemplateController::class, 'deleteTemplate'])
                    ->name('delete');
                Route::get('list', [InvoiceTemplateController::class, 'listTemplates'])
                    ->name('list');
            });

            // ============================================
            // PRODUCTS ROUTES
            // ============================================
            Route::prefix('products')->name('products.')->group(function () {
                Route::get('/dropdown', [\App\Http\Controllers\ProductController::class, 'getForDropdown'])
                    ->middleware('company.role:owner,admin,accountant')
                    ->name('dropdown');

                Route::get('/', [\App\Http\Controllers\ProductController::class, 'index'])
                    ->middleware('company.role:owner,admin,accountant,viewer')
                    ->name('index');

                Route::middleware(['company.role:owner,admin,accountant'])->group(function () {
                    Route::get('/create', [\App\Http\Controllers\ProductController::class, 'create'])
                        ->name('create');
                    Route::post('/', [\App\Http\Controllers\ProductController::class, 'store'])
                        ->name('store');
                });

                Route::prefix('{product}')->group(function () {
                    Route::get('/', [\App\Http\Controllers\ProductController::class, 'show'])
                        ->middleware('company.role:owner,admin,accountant,viewer')
                        ->name('show');

                    Route::middleware(['company.role:owner,admin,accountant'])->group(function () {
                        Route::get('/edit', [\App\Http\Controllers\ProductController::class, 'edit'])
                            ->name('edit');
                        Route::put('/', [\App\Http\Controllers\ProductController::class, 'update'])
                            ->name('update');
                    });

                    Route::delete('/', [\App\Http\Controllers\ProductController::class, 'destroy'])
                        ->middleware('company.role:owner,admin')
                        ->name('destroy');
                });
            });

            // ============================================
            // VERIFACTU ROUTES
            // ============================================
            Route::prefix('verifactu')->name('verifactu.')->group(function () {
                Route::middleware(['company.role:owner,admin,accountant'])->group(function () {
                    Route::get('/connections', [ConnectionController::class, 'index'])
                        ->name('connections.index');
                    Route::post('/connections', [ConnectionController::class, 'store'])
                        ->name('connections.store');
                    Route::post('/connections/{connection}/test', [ConnectionController::class, 'testConnection'])
                        ->name('connections.test');
                    Route::delete('/connections/{connection}', [ConnectionController::class, 'destroy'])
                        ->middleware('company.role:owner,admin')
                        ->name('connections.destroy');
                });
            });

            // ============================================
            // SPECIFIC COMPANY ROUTES (with {company} in URL)
            // ============================================
            Route::prefix('{company}')->group(function () {
                
                // Company Dashboard
                Route::get('/dashboard', [CompanyDashboardController::class, 'index'])
                    ->middleware('company.role:owner,admin,accountant,viewer')
                    ->name('dashboard');

                // View Company
                Route::get('/', [CompanyController::class, 'show'])
                    ->middleware('company.role:owner,admin,accountant,viewer')
                    ->name('show');

                // Owner + Admin Routes
                Route::middleware(['company.role:owner,admin'])->group(function () {
                    Route::get('/edit', [CompanyController::class, 'edit'])
                        ->name('edit');
                    Route::put('/', [CompanyController::class, 'update'])
                        ->name('update');
                    Route::get('/users', [CompanyUserController::class, 'index'])
                        ->name('users.index');
                    Route::post('/users/invite', [CompanyUserController::class, 'invite'])
                        ->name('users.invite');
                    Route::delete('/users/{user}', [CompanyUserController::class, 'remove'])
                        ->name('users.remove');
                    Route::put('/users/{user}/role', [CompanyUserController::class, 'updateRole'])
                        ->name('users.updateRole');
                });

                // Owner Only
                Route::middleware(['company.role:owner'])->group(function () {
                    Route::delete('/', [CompanyController::class, 'destroy'])
                        ->name('destroy');
                });

                // Customer Routes
                Route::prefix('customers')->name('customers.')->group(function () {
                    Route::middleware(['company.role:owner,admin,accountant'])->group(function () {
                        Route::get('/', [CustomerController::class, 'index'])->name('index');
                        Route::get('/create', [CustomerController::class, 'create'])->name('create');
                        Route::post('/', [CustomerController::class, 'store'])->name('store');
                    });

                    Route::prefix('{customer}')->group(function () {
                        Route::get('/', [CustomerController::class, 'show'])
                            ->middleware('company.role:owner,admin,accountant,viewer')
                            ->name('show');

                        Route::middleware(['company.role:owner,admin,accountant'])->group(function () {
                            Route::get('/edit', [CustomerController::class, 'edit'])->name('edit');
                            Route::put('/', [CustomerController::class, 'update'])->name('update');
                        });

                        Route::delete('/', [CustomerController::class, 'destroy'])
                            ->middleware('company.role:owner,admin')
                            ->name('destroy');
                    });
                });
            });
        });
    });
});