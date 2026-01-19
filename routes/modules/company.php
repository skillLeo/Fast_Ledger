<?php

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
use App\Http\Controllers\CompanyModule\PaymentController; // ✅ Add this import

Route::middleware(['auth'])->prefix('company')->name('company.')->group(function () {





    Route::prefix('payment')->name('payment.')->group(function () {
        Route::get('/create', [PaymentController::class, 'create'])
            ->name('create');
        
        // ✅ NEW: Create Payment Intent (AJAX)
        Route::post('/create-intent', [PaymentController::class, 'createPaymentIntent'])
            ->name('create-intent');
        
        Route::post('/store', [PaymentController::class, 'store'])
            ->name('store');
    });






// routes/modules/company.php


Route::prefix('subscription')->name('subscription.')->group(function () {
    Route::get('/setup', [CompanySetupController::class, 'showSubscription'])
        ->name('setup');
    
    Route::post('/store', [CompanySetupController::class, 'storeSubscription'])
        ->name('store');
});

// ============================================
// COMPANY SETUP (STEP 2 - First Company Only)
// ============================================
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

Route::prefix('payment')->name('payment.')->group(function () {
    Route::get('/create', [PaymentController::class, 'create'])
        ->name('create');
    
    Route::post('/store', [PaymentController::class, 'store'])
        ->name('store');
});

// ============================================
// COMPANY SELECTION (STEP 4)
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

        // Company List (available from any company context)
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
        // SUPPLIER ROUTES - Uses session('current_company_id')
        // ============================================
        Route::prefix('suppliers')->name('suppliers.')->group(function () {

            // List & Create Suppliers (Owner, Admin, Accountant)
            Route::middleware(['company.role:owner,admin,accountant'])->group(function () {
                Route::get('/', [\App\Http\Controllers\SupplierController::class, 'index'])
                    ->name('index');
                Route::get('/create', [\App\Http\Controllers\SupplierController::class, 'create'])
                    ->name('create');
                Route::post('/', [\App\Http\Controllers\SupplierController::class, 'store'])
                    ->name('store');
            });

            // Specific Supplier Routes
            Route::prefix('{supplier}')->group(function () {

                // View Supplier (All roles can view)
                Route::get('/', [\App\Http\Controllers\SupplierController::class, 'show'])
                    ->middleware('company.role:owner,admin,accountant,viewer')
                    ->name('show');

                // Edit Supplier (Owner, Admin, Accountant)
                Route::middleware(['company.role:owner,admin,accountant'])->group(function () {
                    Route::get('/edit', [\App\Http\Controllers\SupplierController::class, 'edit'])
                        ->name('edit');
                    Route::put('/', [\App\Http\Controllers\SupplierController::class, 'update'])
                        ->name('update');
                });

                // Delete Supplier (Owner, Admin only)
                Route::delete('/', [\App\Http\Controllers\SupplierController::class, 'destroy'])
                    ->middleware('company.role:owner,admin')
                    ->name('destroy');
            });
        });

        // ============================================
        // SUPPLIER DROPDOWN API (For Purchase Invoices)
        // ============================================
        Route::get('/suppliers-dropdown', [\App\Http\Controllers\SupplierController::class, 'getSuppliersDropdown'])
            ->middleware('company.role:owner,admin,accountant')
            ->name('api.suppliers.dropdown');
        // ============================================
        // ✅ INVOICE ROUTES - OUTSIDE {company} GROUP
        // Uses session('current_company_id') instead of route parameter
        // ============================================
        Route::prefix('invoices')->name('invoices.')->group(function () {

            Route::post('/generate-auto-code', [CompanyInvoiceController::class, 'generateAutoCodeAjax'])
                ->name('generate-auto-code');
            // List & Create Invoices
            Route::middleware(['company.role:owner,admin,accountant'])->group(function () {
                Route::get('/', [CompanyInvoiceController::class, 'index'])
                    ->name('index');
                Route::get('/create', [CompanyInvoiceController::class, 'create'])
                    ->name('create');
                Route::post('/', [CompanyInvoiceController::class, 'store'])
                    ->name('store');
            });

            // ============================================
            // ✅ ACTIVITY LOG ROUTES - NEW
            // ============================================
            Route::middleware(['company.role:owner,admin,accountant,viewer'])->group(function () {

                // Activity Log Index Page
                Route::get('/activity-logs', [CompanyInvoiceController::class, 'activityLogIndex'])
                    ->name('activity-logs.index');

                // Get All Activity Logs (AJAX endpoint)
                Route::get('/all-activity-logs', [CompanyInvoiceController::class, 'getAllInvoiceActivityLogs'])
                    ->name('all-activity-logs');
            });

            // Specific Invoice Routes
            Route::prefix('{invoice}')->group(function () {

                // View (All roles)
                Route::get('/view', [CompanyInvoiceController::class, 'view'])
                    ->middleware('company.role:owner,admin,accountant,viewer')
                    ->name('view');

                Route::get('/get-invoice-data', [CompanyInvoiceController::class, 'getInvoiceData'])
                    ->middleware('company.role:owner,admin,accountant,viewer')
                    ->name('get-invoice-data');

                // Download PDF (All roles)
                Route::get('/download-pdf', [CompanyInvoiceController::class, 'downloadPDF'])
                    ->middleware('company.role:owner,admin,accountant,viewer')
                    ->name('download-pdf');

                // View PDF in Browser (All roles)
                Route::get('/view-pdf', [CompanyInvoiceController::class, 'viewPDF'])
                    ->middleware('company.role:owner,admin,accountant,viewer')
                    ->name('view-pdf');

                // Edit Draft (Owner, Admin, Accountant)
                Route::get('/edit', [CompanyInvoiceController::class, 'edit'])
                    ->middleware('company.role:owner,admin,accountant')
                    ->name('edit');

                // Delete Draft (Owner, Admin only)
                Route::delete('/', [CompanyInvoiceController::class, 'destroy'])
                    ->middleware('company.role:owner,admin')
                    ->name('destroy');

                // AJAX/API Routes
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
        // INVOICE TEMPLATE/PREVIEW ROUTES
        // ============================================
        Route::prefix('invoices/templates')->name('invoices.templates.')->group(function () {

            // Template Gallery (list all templates)
            Route::get('/gallery', [InvoiceTemplateController::class, 'gallery'])
                ->name('gallery');

            // Create New Template (with dummy data)
            Route::get('/create', [InvoiceTemplateController::class, 'createTemplate'])
                ->name('create');

            // Preview routes   
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

            // Template CRUD operations
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
        // PRODUCTS ROUTES - Uses session('current_company_id')
        // ============================================
        Route::prefix('products')->name('products.')->group(function () {

            // API endpoint for dropdown (used in invoice forms)
            Route::get('/dropdown', [\App\Http\Controllers\ProductController::class, 'getForDropdown'])
                ->middleware('company.role:owner,admin,accountant')
                ->name('dropdown');

            // List Products (All roles can view)
            Route::get('/', [\App\Http\Controllers\ProductController::class, 'index'])
                ->middleware('company.role:owner,admin,accountant,viewer')
                ->name('index');

            // Create Product (Owner, Admin, Accountant)
            Route::middleware(['company.role:owner,admin,accountant'])->group(function () {
                Route::get('/create', [\App\Http\Controllers\ProductController::class, 'create'])
                    ->name('create');
                Route::post('/', [\App\Http\Controllers\ProductController::class, 'store'])
                    ->name('store');
            });

            // Specific Product Routes
            Route::prefix('{product}')->group(function () {

                // View Product (All roles)
                Route::get('/', [\App\Http\Controllers\ProductController::class, 'show'])
                    ->middleware('company.role:owner,admin,accountant,viewer')
                    ->name('show');

                // Edit Product (Owner, Admin, Accountant)
                Route::middleware(['company.role:owner,admin,accountant'])->group(function () {
                    Route::get('/edit', [\App\Http\Controllers\ProductController::class, 'edit'])
                        ->name('edit');
                    Route::put('/', [\App\Http\Controllers\ProductController::class, 'update'])
                        ->name('update');
                });

                // Delete Product (Owner, Admin only)
                Route::delete('/', [\App\Http\Controllers\ProductController::class, 'destroy'])
                    ->middleware('company.role:owner,admin')
                    ->name('destroy');
            });
        });


        // ============================================
        // ✅ VERIFACTU ROUTES - NEW (Add this here)
        // ============================================
        Route::prefix('verifactu')->name('verifactu.')->group(function () {

            // Connection Management (Owner, Admin, Accountant)
            Route::middleware(['company.role:owner,admin,accountant'])->group(function () {

                // Main Connections Page
                Route::get('/connections', [ConnectionController::class, 'index'])
                    ->name('connections.index');

                // Store Connection
                Route::post('/connections', [ConnectionController::class, 'store'])
                    ->name('connections.store');

                // Test Connection
                Route::post('/connections/{connection}/test', [ConnectionController::class, 'testConnection'])
                    ->name('connections.test');

                // Delete Connection (Owner, Admin only)
                Route::delete('/connections/{connection}', [ConnectionController::class, 'destroy'])
                    ->middleware('company.role:owner,admin')
                    ->name('connections.destroy');
            });
        });


        // ============================================
        // SPECIFIC COMPANY ROUTES - ✅ {company} IN URL
        // ============================================
        Route::prefix('{company}')->group(function () {

            // Company Dashboard - All roles can access
            Route::get('/dashboard', [CompanyDashboardController::class, 'index'])
                ->middleware('company.role:owner,admin,accountant,viewer')
                ->name('dashboard');

            // View Company Details - All roles can access
            Route::get('/', [CompanyController::class, 'show'])
                ->middleware('company.role:owner,admin,accountant,viewer')
                ->name('show');

            // ============================================
            // OWNER + ADMIN ONLY ROUTES
            // ============================================
            Route::middleware(['company.role:owner,admin'])->group(function () {

                // Edit Company
                Route::get('/edit', [CompanyController::class, 'edit'])
                    ->name('edit');
                Route::put('/', [CompanyController::class, 'update'])
                    ->name('update');

                // User Management
                Route::get('/users', [CompanyUserController::class, 'index'])
                    ->name('users.index');
                Route::post('/users/invite', [CompanyUserController::class, 'invite'])
                    ->name('users.invite');
                Route::delete('/users/{user}', [CompanyUserController::class, 'remove'])
                    ->name('users.remove');
                Route::put('/users/{user}/role', [CompanyUserController::class, 'updateRole'])
                    ->name('users.updateRole');
            });

            // ============================================
            // OWNER ONLY ROUTES
            // ============================================
            Route::middleware(['company.role:owner'])->group(function () {
                Route::delete('/', [CompanyController::class, 'destroy'])
                    ->name('destroy');
            });

            // ============================================
            // CUSTOMER ROUTES - EXISTING PATTERN ✅
            // ============================================
            Route::prefix('customers')->name('customers.')->group(function () {

                // List & Create (Owner, Admin, Accountant)
                Route::middleware(['company.role:owner,admin,accountant'])->group(function () {
                    Route::get('/', [CustomerController::class, 'index'])->name('index');
                    Route::get('/create', [CustomerController::class, 'create'])->name('create');
                    Route::post('/', [CustomerController::class, 'store'])->name('store');
                });

                // Specific Customer
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

    // ============================================
    // API ENDPOINTS (outside {company} scope)
    // ============================================
    // Route::get('/customers-dropdown', [CompanyInvoiceController::class, 'getCustomersDropdown'])
    //     ->name('api.customers.dropdown');
});
