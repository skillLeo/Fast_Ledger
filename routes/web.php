<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\Employees\EmployeeController;
use App\Http\Controllers\{
    DashboardController,
    ProfileController,
    FileController,
    ClientController,
    MatterController,
    ModuleController,
    FinexerController,
    DayBookController,
    ProductController,
    InvoiceController,
    PurchaseController,
    FileUploadController,
    TransactionController,
    UserSqlExportController,
    ClientCashBookController,
    TransactionChequeController,
    FeeEarnersController,
    SupplierController,
    InvoiceReportingController,
    InvoiceTemplateController,
    BulkTransactionController,
    ChartsOfAccountController,
};
use App\Http\Controllers\Report\{
    VatReportController,
    ProfitLossController,
    BalanceSheetController,
    TrailBalanceController,
    ProfitAndLoosController,
    OfficeCashBookController,
    BillOfCostReportController,
    ClientLedgerReportController,
    FileOpeningBookReportController,
    ClientBankReconciliationController,
    OfficeBankReconciliationController,
    ClientLedgerBalanceReportController
};
use App\Http\Controllers\SubscriptionController;

// ============================================
// ✅ APPLY ONBOARDING MIDDLEWARE TO ALL ROUTES
// ============================================
Route::middleware(['auth'])->group(function () {
 

    // ✅ Subscription Management Routes
    Route::post('/subscription/toggle-renewal', [SubscriptionController::class, 'toggleRenewal'])
        ->name('subscription.toggle-renewal');
    
    Route::post('/subscription/cancel', [SubscriptionController::class, 'cancel'])
        ->name('subscription.cancel');
    
    Route::post('/subscription/reactivate', [SubscriptionController::class, 'reactivate'])
        ->name('subscription.reactivate');


    
    
    
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/user/sql-backup', [UserSqlExportController::class, 'export'])->name('user.sql.backup');
    Route::get('/admin-login-as/{id}', [ClientController::class, 'adminLoginAs'])->name('admin.login.as');

    Route::get('/admin/users/{user?}/banks', [ClientController::class, 'showBanks'])
        ->name('admin.users.banks');

    Route::get('/transactions/ledger-ref', [TransactionController::class, 'getLedgerRefsForAutocomplete'])->name('transactions.ledger-ref');
    Route::get('/transactions/ledger-refs', [DayBookController::class, 'getLedgerRefsForAutocomplete'])->name('transactions.ledger-refs');
    Route::get('/transactions/references', [TransactionController::class, 'getReferencesForAutocomplete'])->name('transactions.references');



    Route::post('/admin/bank/inactivate', [ClientController::class, 'inactivateBanks'])->name('banks.inactivate');
    // Grouped under 'admin' or 'bank' prefix if needed
    Route::prefix('admin/banks')->name('banks.')->group(function () {
        Route::get('/create/{user}', [ClientController::class, 'createBank'])->name('create'); // GET
        Route::post('/', [ClientController::class, 'storeBank'])->name('store');              // POST
    });




    Route::prefix('employees')
        ->name('employees.')
        ->controller(EmployeeController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'store')->name('store');
            Route::get('/{id}', 'show')->name('show');
            Route::get('/{id}/edit', 'edit')->name('edit');
            Route::put('/{id}', 'update')->name('update');
            Route::delete('/{id}', 'destroy')->name('destroy');
        });


    // routes/web.php
    Route::prefix('files')
        ->name('files.')
        ->controller(FileController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/soft-delete', 'softDelete')->name('softDelete');
            Route::get('/trashed', 'trashed')->name('trashed');
            Route::post('/restore', 'restore')->name('restore');
            Route::post('/permanent-delete', 'forceDelete')->name('forceDelete');
            Route::get('/create', 'create')->name('create');
            Route::get('/filter-suggestions', 'getFilterSuggestions')->name('filter.suggestions');

            Route::post('/delete_id', 'destroy')->name('destroy');
            Route::post('/get-filedata', 'getFileData')->name('get.filedata');
            Route::get('/download-pdfs', 'downloadPDF')->name('download.pdf');
            Route::post('/update-status', 'updateStatus')->name('update.status');
            Route::get('/file-opening-book/ledger-data', 'getLedgerData')->name('file-opening-book.ledger-data');
            Route::get('/file-opening-book/filter-matter', 'filterByMatter')->name('file-opening-book.filter-matter');
            Route::get('/get-supplier-data', 'getSupplierData')->name('get.supplier.data');

            // Employee data route
            Route::get('/file-opening-book/employee-data', 'getEmployeeData')->name('employee-data');
        });



    Route::post('/files', [FileController::class, 'store']);



    Route::get('/file/update/{id}', [FileController::class, 'getdata'])->name('update.file');

    // Route::post('/files', [FileController::class, 'store']) ;
    Route::middleware(['auth'])->get('/user/sql-backup', [UserSqlExportController::class, 'export'])->name('user.sql.backup');





    Route::get('/api/ledger-details/{ledgerRef}', [DayBookController::class, 'getLedgerDetails']);


    Route::get('/matters/{id}/submatters', [MatterController::class, 'getSubMatters'])->name('matters.submatters');

    Route::get('/archived', [ClientController::class, 'archivedClients'])->name('clients.archived'); // Show archived clients
    Route::get('/users/impersonate/{id}', [ClientController::class, 'impersonate'])->name('users.impersonate');
    Route::middleware(['auth'])->group(function () {
        Route::get('/admin-login-as/{id}', [ClientController::class, 'adminLoginAs'])->name('admin.login.as');
    });

    Route::post('/invoice/upload-documents', [DayBookController::class, 'uploadMultipleInvoiceDocuments'])
        ->name('invoice.upload.documents');

    Route::prefix('transactions')
        ->name('transactions.')
        ->controller(DayBookController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');

            Route::post('/store', 'store')->name('store');
            Route::get('/{transaction}/edit', 'edit')->name('edit');
            Route::post('/store-multiple', 'storeMultiple')->name('store-multiple');
            Route::get('/import/{id}', 'import')->name('import');
            Route::post('/get-payment-types', 'getPaymentTypes')->name('payment.types');
            Route::post('/get-account-ref', 'getAccountRef')->name('account.ref');
            Route::post('/get-vat-types', 'getVatTypes');

            Route::get('/get-account-details/{id}', 'getAccountDetails');
            Route::get('/download_daybook_pdf', 'downloaddaybookpdf')->name('daybook.download.pdf');
            Route::get('/daybookdeletes', 'daybookdelete')->name('daybook.bulk-delete');
        });



    Route::get('/transactions/ledger-details/{ledgerRef}', [DayBookController::class, 'getLedgerDetails'])
        ->name('transactions.ledger-details');
    Route::post('/transactions/generate-auto-code', [DayBookController::class, 'generateAutoCodeAjax'])->name('transactions.generate-auto-code');
    // Route::post('/transactions/check-code-unique', [DayBookController::class, 'checkCodeUnique'])->name('transactions.check-code-unique');
    Route::post('/transactions/check-code-unique', [DayBookController::class, 'checkTransactionCodeUnique'])->name('transactions.check-code-unique');

    Route::get('/transactions/get-ledger-refs', [DayBookController::class, 'getLedgerRefs'])->name('transactions.get-ledger-refs');
    Route::get('/payment-types', [DayBookController::class, 'getPaymentTypesbutton']);
    Route::get('/banks-by-payment-type', [DayBookController::class, 'getBanksByPaymentType']);
    Route::post('/transactions/client-bank-accounts', [DayBookController::class, 'getClientBankAccounts'])
        ->name('transactions.client-bank-accounts');
    Route::get('/bank-accounts', [DayBookController::class, 'listBankAccounts'])
        ->name('bank-accounts.list');

    Route::get('/transaction/imported', [TransactionController::class, 'index'])->name('transactions.imported');
    Route::post('/transaction/importeds', [TransactionController::class, 'importdata'])->name('transactions.importeda');
    Route::post('/bulkdelete', [TransactionController::class, 'bulkDelete'])->name('transactions.bulk-delete');

    Route::get('/download/transaction_pdf', [TransactionController::class, 'downloadtransactionpdf'])->name('transaction.download.pdf');

    Route::post('/transactions/delete', [TransactionController::class, 'bulkDelete'])->name('transactions.destroy');
    Route::get('client-cash-book', [ClientCashBookController::class, 'index'])->name('client.cashbook');
    Route::get('file-opening-book', [FileOpeningBookReportController::class, 'index'])->name('file.report');
    Route::get('file-opening-book/data', [FileOpeningBookReportController::class, 'getData'])->name('file.report.data');
    Route::get('/file/report/pdf', [FileOpeningBookReportController::class, 'downloadPDF'])->name('file.report.pdf');
    Route::get('/file/report/csv', [FileOpeningBookReportController::class, 'downloadCSV'])->name('file.report.csv');

    Route::get('/report/client-ledger-by-balance', [ClientLedgerBalanceReportController::class, 'index'])->name('client.passed.check');


    Route::get('/download-pdf', [ClientLedgerBalanceReportController::class, 'generatePDF'])->name('download.pdf');


    Route::get('/report/client-ledger', [ClientLedgerReportController::class, 'index'])->name('client.ledger');
    Route::get('/report/client-ledgers', [ClientLedgerReportController::class, 'getdata'])->name('client.ledger.data');
    Route::get('/report/client-ledger-data', [ClientLedgerReportController::class, 'index'])->name('client.ledgers');
    Route::get('/client-ledger/pdf', [ClientLedgerReportController::class, 'getdata'])->name('client.ledger.pdf');

    Route::get('/report/bill-of-cost', [BillOfCostReportController::class, 'index'])->name('bill.of.cost');

    Route::get('/profit-and-loos', [ProfitAndLoosController::class, 'index'])->name('profit.and.loos');
    Route::get('/profit-and-loss/pdf', [ProfitAndLoosController::class, 'generatePdf'])->name('profit.and.loss.pdf');


    Route::get('/search-ledger', [BillOfCostReportController::class, 'search'])->name('search.ledger');
    Route::get('/report/bill-of-cost-search', [BillOfCostReportController::class, 'get_data'])->name('bill.of.cost.data');
    Route::get('/report/vat-report', [VatReportController::class, 'index'])->name('vat.report');

    Route::get('/fee-earners', [FeeEarnersController::class, 'index'])->name('fee.earners');
    Route::get('/add-fee-earner', [FeeEarnersController::class, 'create'])->name('feeearner.create');

    Route::post('/feeearner/sotre', [FeeEarnersController::class, 'store'])->name('feeearner.store');

    Route::get('/active-fee-earners', [FeeEarnersController::class, 'checkactive'])->name('check.active');
    Route::get('/inactive-fee-earners', [FeeEarnersController::class, 'checkinactive'])->name('check.inactive');
    Route::post('/inactives-fee-earners', [FeeEarnersController::class, 'updatefeeernerstatus'])->name('update.feeerner.status');

    Route::get('/edit-Feeearner/{id}', [FeeEarnersController::class, 'edit'])->name('user.edit');

    Route::post('/feeearner/update/{id}', [FeeEarnersController::class, 'update'])->name('feeearner.update');


    Route::put('/feeearner/update/{id}', 'FeeEarnerController@update')->name('feeearner.update');

    Route::get('client-cash-book/initial-balance', [ClientCashBookController::class, 'getInitialBalance'])
        ->name('client.cashbook.get_initial_balance');
    Route::get('/export-client-cashbook-pdf', [ClientCashBookController::class, 'exportClientCashBookPDF'])
        ->name('client.cashbook.export_pdf');

    Route::get('office-cash-book', [OfficeCashBookController::class, 'index'])->name('office.cashbook');
    Route::get('office-cash-book/initial-balance', [OfficeCashBookController::class, 'getInitialBalance'])
        ->name('office.cashbook.get_initial_balance');
    Route::get('office-cash-book/pdf', [OfficeCashBookController::class, 'exportOfficeCashBookPDF'])->name('office.cashbook.export_pdf');

    Route::get('client-bank-reconciliation', [ClientBankReconciliationController::class, 'index'])
        ->name('client.bank_bank_reconciliation');
    Route::get('fetch-client-bank-reconciliation/{date}', [ClientBankReconciliationController::class, 'fetchBankReconciliation'])
        ->name('client.bank.reconciliation.fetch');
    Route::get('/client-bank-reconciliation/pdf/{date}', [ClientBankReconciliationController::class, 'exportPdf']);

    Route::match(['get', 'post'], '/download-pdf/data', [OfficeBankReconciliationController::class, 'downloadPDF'])
        ->name('generate.pdf');
    Route::get('/office-bank-reconciliation/data', [OfficeBankReconciliationController::class, 'getData'])
        ->name('Office.bank_reconciliation.data');
    Route::get('/Office/bank_reconciliation.initial_balance', [OfficeBankReconciliationController::class, 'getInitialBalance'])
        ->name('Office.bank_reconciliation.initial_balance');
    Route::get('/download-pdf/data', [OfficeBankReconciliationController::class, 'downloadPDF'])
        ->name('generate.pdf');


    Route::get('/transactions/cheque', [TransactionChequeController::class, 'index'])->name('transactions.cheque');
    Route::post('/bank-cheque/save', [TransactionChequeController::class, 'saveBankCheque'])->name('bank.cheque.save');


    Route::get('office-bank-reconciliation', [OfficeBankReconciliationController::class, 'index'])
        ->name('office.bank_reconciliation');
    Route::get('/office-bank-reconciliation/data', [OfficeBankReconciliationController::class, 'getData'])
        ->name('Office.bank_reconciliation.data');
    Route::get('/Office/bank_reconciliation.initial_balance', [OfficeBankReconciliationController::class, 'getInitialBalance'])
        ->name('Office.bank_reconciliation.initial_balance');
    Route::get('/download-pdf/data', [OfficeBankReconciliationController::class, 'downloadPDF'])
        ->name('generate.pdf');
    //Route::get('client-bank-reconciliation', [ClientBankReconciliationController::class, 'index'])->name('client.bank_bank_reconciliation');
    // Route::get('fetch-client-bank-reconciliation', [ClientBankReconciliationController::class, 'fetchBankReconciliation'])->name('client.bank_reconciliation');

    Route::prefix('clients')
        ->name('clients.')
        ->controller(ClientController::class)
        ->middleware('role:superadmin')
        ->group(function () {
            Route::get('create', 'create')->name('create');
            Route::get('/{type?}', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::get('/{client}', 'show')->name('show');
            Route::put('/{client}', 'update')->name('update');
            Route::delete('/{client}', 'destroy')->name('destroy');
            Route::put('/{client}/archive', 'archive')->name('archive');
        });

    Route::patch('/clients/{id}/archive', [ClientController::class, 'archive'])->name('clients.archive');
    Route::patch('/clients/{id}/recover', [ClientController::class, 'recover'])->name('clients.recover');

    Route::prefix('charts-of-accounts')
        ->name('charts.of.accounts.')
        ->controller(ChartsOfAccountController::class)->group(function () {
            Route::get('/index', 'index')->name('index');
            Route::get('/{id}/transactions', 'getTransactions')->name('transactions');
            Route::get('/dropdown', 'getChartOfAccounts')->name('dropdown');
        });

    Route::get('/api/chart-of-accounts-all', [ChartsOfAccountController::class, 'getAllChartOfAccounts']);

    Route::prefix('suppliers')
        ->name('suppliers.')
        ->controller(SupplierController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');                   // Changed from /store to /
            Route::get('/{supplier}', 'show')->name('show');
            Route::get('/{supplier}/edit', 'edit')->name('edit');
            Route::put('/{supplier}', 'update')->name('update');
            Route::delete('/{supplier}', 'destroy')->name('destroy');
        });

    Route::get('/suppliers-dropdown', [DayBookController::class, 'getSuppliersDropdown'])
        ->name('suppliers.dropdown');
    Route::prefix('invoices')
        ->name('invoices.')
        ->controller(InvoiceReportingController::class)->group(function () {
            Route::get('/reporting', 'index')->name('reporting');
            Route::get('/statement', 'statement')->name('statement');
            // Route::get('/dropdown', 'getChartOfAccounts')->name('dropdown');
        });

    Route::prefix('trail_balances')
        ->name('trail_balances.')
        ->controller(TrailBalanceController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            // Route::get('/create', 'create')->name('create');
            // Route::post('/store', 'store')->name('store');
        });



    // routes/api.php (Add this line)
    // Route::get('/api/chart-of-accounts', [ChartsOfAccountController::class, 'getForModal']);
    Route::get('/charts-of-accounts/modal', [ChartsOfAccountController::class, 'getForModal'])->name('charts.of.accounts.modal');
    Route::get('/api/vat-types-by-form/{formKey}', [ChartsOfAccountController::class, 'getVatTypesByForm'])->name('api.vat.types.by.form');

    Route::prefix('bulk-transactions')
        ->name('bulk-transactions.')
        ->controller(BulkTransactionController::class)
        ->group(function () {
            Route::get('/upload', 'showUploadForm')->name('upload');
            Route::post('/upload', 'uploadFile')->name('upload.post');

            Route::get('mapping/{uploadedFile}', 'showMapping')->name('mapping');
            Route::post('mapping/{uploadedFile}', 'saveMapping')->name('mapping.save');

            Route::get('/pending/{bankAccountId}', 'showPendingTransactions')->name('pending');
            Route::get('/bankpreconcile', 'bankReconcile')->name('dashboard');
            Route::get('/bank/chart/filter', 'getChartData')->name('bank.chart.filter');
            // Route::get('/map-columns', 'mapColumns')->name('map-columns');

            Route::post('/process-mapping', 'processColumnMapping')->name('process-mapping');
            Route::post('/save', 'saveTransactions')->name('save-allocation');
            Route::post('/template/{type}', 'downloadTemplate')->name('download-template')->where('type', 'csv|excel');
            Route::post('/save-row', 'saveRow')->name('save-row');
            // Add this line:
            Route::get('/account-refs-by-ledger', 'getAccountRefsByLedger')->name('account-refs-by-ledger');
            Route::get('/get-balances/{bankAccountId}', 'getBalances')->name('get-balances');
        });


    Route::prefix('invoicetemplates')
        ->name('invoicetemplates.')
        ->controller(InvoiceTemplateController::class)
        ->group(function () {
            // Route::get('/customize', 'customize')->name('customize');
            Route::post('download/pdf', 'downloadPdf')->name('preview.download.pdf');

            // Route::post('/template/upload-logo', 'uploadLogo')->name('uploadLogo');
            Route::post('/preview/ajax', 'previewAjax')->name('preview.ajax');

            Route::post('/preview/download/pdf', 'downloadPdf')->name('preview.download.pdf');

            Route::post('/preview', 'preview')->name('preview');
            Route::get('/preview/{draft}', 'showPreview')->name('preview.show');

            Route::get('/preview/{draft}/customize', 'showCustomizationMode')
                ->name('preview.customize');

            // Template CRUD operations
            Route::post('/save', 'saveTemplate')->name('save');
            Route::post('/upload-logo', 'uploadLogo')->name('uploadLogo');
            Route::get('/load/{id}', 'loadTemplate')->name('load');
            Route::delete('/delete/{id}', 'deleteTemplate')->name('delete');

            // Template listing
            Route::get('/list', 'listTemplates')->name('list');
        });



    // ============================================
    // PRODUCT MANAGEMENT ROUTES
    // ============================================
    Route::prefix('products')
        ->name('products.')
        ->controller(ProductController::class)
        ->group(function () {
            // Get products for dropdown (filtered by category)
            Route::get('/dropdown', 'getForDropdown')->name('dropdown');

            // Store new product from modal
            Route::post('/store', 'store')->name('store');

            // Get single product details
            Route::get('/{id}', 'show')->name('show');

            // Get products list (for future product management page)
            Route::get('/', 'index')->name('index');

            // Update product
            Route::put('/{id}', 'update')->name('update');

            // Delete product (soft delete)
            Route::delete('/{id}', 'destroy')->name('destroy');
        });


    Route::prefix('invoices')->name('invoices.')->group(function () {
        Route::get('/', [InvoiceController::class, 'index'])->name('index');
        Route::get('/{id}/view', [InvoiceController::class, 'view'])->name('view');
        Route::get('/{id}/edit', [InvoiceController::class, 'edit'])->name('edit');
        Route::delete('/{id}', [InvoiceController::class, 'destroy'])->name('destroy');

        // Route::post('/{id}/download-pdf', [InvoiceController::class, 'downloadPDF'])->name('download-pdf');
        Route::get('/{id}/get-invoice-data', [InvoiceController::class, 'getInvoiceData'])->name('get-invoice-data');
        Route::get('/{id}/view-pdf', [InvoiceController::class, 'viewPDF'])->name('view-pdf');

        Route::post('/{id}/issue', [InvoiceController::class, 'issue'])->name('issue');
        Route::get('/{invoice}/view', [InvoiceController::class, 'show'])
            ->name('show');

        // ✅ These methods exist in your controller
        Route::get('{id}/status-details', [InvoiceController::class, 'getStatusDetails'])->name('status-details');
        Route::post('{id}/update-status', [InvoiceController::class, 'updateStatus'])->name('update-status');
        Route::get('/{id}/documents', [InvoiceController::class, 'getDocuments'])->name('documents');


        Route::get('/activity-logs', [InvoiceController::class, 'activityLogIndex'])
            ->name('activity_logs.index');
        Route::get('/{invoice}/activity-log', [InvoiceController::class, 'getInvoiceActivityLogs'])
            ->name('activity-log');
        Route::get('/activity-logs', [InvoiceController::class, 'activityLogIndex'])
            ->name('activity_logs.index');

        // Get all activity logs (API endpoint)
        Route::get('/all-activity-logs', [InvoiceController::class, 'getAllInvoiceActivityLogs'])
            ->name('all-activity-logs');
    });



    // ==========================================
    // PURCHASE INVOICES ROUTES
    // ==========================================
    Route::prefix('purchases')->name('purchases.')->group(function () {
        Route::get('/', [PurchaseController::class, 'index'])->name('index');
        Route::get('/{id}/view', [PurchaseController::class, 'view'])->name('view');
        Route::get('/{id}/edit', [PurchaseController::class, 'edit'])->name('edit');
        Route::delete('/{id}', [PurchaseController::class, 'destroy'])->name('destroy');

        // Status management
        Route::get('/{id}/status-details', [PurchaseController::class, 'getStatusDetails'])->name('status-details');
        Route::post('/{id}/update-status', [PurchaseController::class, 'updateStatus'])->name('update-status');

        // Documents
        Route::get('/{id}/documents', [PurchaseController::class, 'getDocuments'])->name('documents');

        // PDF operations
        Route::get('/{id}/get-invoice-data', [PurchaseController::class, 'getInvoiceData'])->name('get-invoice-data');
        Route::get('/{id}/view-pdf', [PurchaseController::class, 'viewPDF'])->name('view-pdf');
        Route::get('/{id}/download-pdf', [PurchaseController::class, 'downloadPDF'])->name('download-pdf');

        // Activity logs
        Route::get('/activity-logs', [PurchaseController::class, 'activityLogIndex'])->name('activity-logs');
        Route::get('/{id}/activity-log', [PurchaseController::class, 'getInvoiceActivityLogs'])->name('activity-log');
        Route::get('/all-activity-logs', [PurchaseController::class, 'getAllInvoiceActivityLogs'])->name('all-activity-logs');
    });



    // Chart of Accounts API endpoints
    Route::get('/api/ledger-refs-dropdown', [ChartsOfAccountController::class, 'getLedgerRefsForDropdown'])->name('api.ledger-refs.dropdown');
    Route::post('/api/account-refs-by-ledger', [ChartsOfAccountController::class, 'getAccountRefsByLedger'])->name('api.account-refs.by-ledger');

    Route::get('/profit-loss', [ProfitLossController::class, 'index'])->name('profit-loss');   // ?from=YYYY-MM-DD&to=YYYY-MM-DD
    Route::get('/balance-sheet', [BalanceSheetController::class, 'index'])->name('balance-sheet'); // ?from=YYYY-MM-DD&to=YYYY-MM-DD

    Route::get('files/{folder}/{filename}', [FileUploadController::class, 'show'])
        ->where(['folder' => '[A-Za-z0-9_\-]+', 'filename' => '[A-Za-z0-9_\-\.]+'])
        ->name('uploadfiles.show');

    Route::prefix('finexer')
        ->name('finexer.')
        ->group(function () {

            Route::controller(FinexerController::class)->group(function () {

                // Settings page
                Route::get('/settings', 'settings')->name('settings');

                // Connect with bank type
                Route::get('/connect', 'connect')->name('connect');

                // Bank management
                Route::get('/banks', 'index')->name('index');
                Route::post('/sync/{bankAccountId}', 'sync')->name('sync');
                Route::post('/sync-all', 'syncAll')->name('sync-all');
                Route::post('/disconnect/{bankAccountId}', 'disconnect')->name('disconnect');

                // Transaction reconciliation
                Route::get('/pending-transactions', 'pendingTransactions')->name('pending-transactions');
                Route::post('/reconcile/{pendingTransactionId}', 'reconcile')->name('reconcile');
                Route::post('/ignore/{pendingTransactionId}', 'ignore')->name('ignore');

                // Utilities
                Route::get('/stats', 'stats')->name('stats');
                Route::get('/test-connection', 'testConnection')->name('test-connection');

                Route::post('/toggle-import-button', 'toggleImportButton')->name('toggle-import-button');
            });
        });
});


// ============================================
// MODULE SELECTION (For new users - Role 2, 4)
// ============================================
Route::middleware(['auth'])->group(function () {
    Route::get('/modules', [ModuleController::class, 'index'])
        ->name('modules.select');

    Route::post('/modules/{moduleName}/activate', [ModuleController::class, 'activate'])
        ->name('modules.activate');
});

Route::get('/finexer/callback', [FinexerController::class, 'callback'])
    ->name('finexer.callback');

Route::get('/clear-all-cache', function () {
    Artisan::call('optimize:clear');
    return "All caches cleared successfully!";
});

require __DIR__ . '/hmrc.php';


// ============================================
// COMPANY MODULE ROUTES
// ============================================
require __DIR__ . '/modules/company.php';
require __DIR__ . '/auth.php';
