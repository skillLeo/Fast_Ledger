<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\VatType;
use App\Models\Supplier;
use App\Models\AccountRef;
use App\Models\BankAccount;
use App\Models\PaymentType;
use App\Models\Transaction;
use Illuminate\Support\Arr;
use App\Models\DraftInvoice;
use App\Models\VatFormLabel;
use Illuminate\Http\Request;
use App\Models\ChartOfAccount;
use App\Models\InvoiceDocument;
use App\Models\InvoiceTemplate;
use App\Models\TemplateElement;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\InvoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\FacadesLog;
use Illuminate\Support\Facades\Log;
use App\DataTables\DayBookDataTable;
use App\Models\TemplateTableSetting;
use App\Services\TransactionService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\TransactionRequest;
use App\Services\Concerns\HasCustomerContext;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;

class DayBookController extends Controller
{
    use HasCustomerContext; // ✅ NEW: Add this trait

    protected InvoiceService $invoiceService;
    protected TransactionService $transactionService;

    public function __construct(
        InvoiceService $invoiceService,
        TransactionService $transactionService
    ) {
        $this->invoiceService = $invoiceService;
        $this->transactionService = $transactionService;
    }

    public function index(DayBookDataTable $dataTable)
    {
        $view = request()->get('view', 'day_book');
        $currentClientId = auth()->user()->Client_ID;

        $data = $this->getFilterData($currentClientId);

        $filterSelects = [
            'bankSelectHTML' => $this->buildBankAccountSelect($data['bankAccounts']),
            'paidInOutSelectHTML' => $this->buildPaidInOutSelect(),
            'paymentTypeSelectHTML' => $this->buildPaymentTypeSelect($data['paymentTypes']),
            'accountRefSelectHTML' => $this->buildAccountRefSelect($data['accountRefs'])
        ];

        $template = $view === 'batch_invoicing'
            ? 'admin.batch_invoicing.batch_invoicing'
            : 'admin.day_book.index';

        return app(DayBookDataTable::class, $filterSelects)
            ->render($template, ['bankAccounts' => $data['bankAccounts']]);
    }

    private function getFilterData($currentClientId)
    {
        return [
            'bankAccounts' => BankAccount::with('bankAccountType')
                ->where('Client_ID', $currentClientId)
                ->where('Is_Deleted', 0)
                ->get(),

            'paymentTypes' => PaymentType::select('Payment_Type_ID', 'Payment_Type_Name')
                ->distinct()
                ->orderBy('Payment_Type_Name')
                ->get(),

            'accountRefs' => AccountRef::select('Account_Ref_ID', 'Reference')
                ->distinct()
                ->orderBy('Reference')
                ->get()
        ];
    }

    private function buildBankAccountSelect($bankAccounts)
    {
        $options = collect($bankAccounts)->map(function ($account) {
            $label = $account->Account_Name . ' (' . ($account->bankAccountType->Bank_Type ?? 'N/A') . ')';
            return '<option value="' . $account->Bank_Account_ID . '">' . htmlentities($label) . '</option>';
        })->implode('');

        return '<option value="">All</option>' . $options;
    }

    private function buildPaidInOutSelect()
    {
        return '<option value="">All</option>' .
            '<option value="1">Paid In</option>' .
            '<option value="2">Paid Out</option>';
    }

    private function buildPaymentTypeSelect($paymentTypes)
    {
        $options = collect($paymentTypes)->map(function ($paymentType) {
            return '<option value="' . $paymentType->Payment_Type_ID . '">' .
                htmlentities($paymentType->Payment_Type_Name) . '</option>';
        })->implode('');

        return '<option value="">All</option>' . $options;
    }

    private function buildAccountRefSelect($accountRefs)
    {
        $options = collect($accountRefs)->map(function ($accountRef) {
            return '<option value="' . $accountRef->Account_Ref_ID . '">' .
                htmlentities($accountRef->Reference) . '</option>';
        })->implode('');

        return '<option value="">All</option>' . $options;
    }

    public function getLedgerRefsForAutocomplete(Request $request)
    {
        $query = $request->input('query', '');
        $clientId = auth()->user()->Client_ID;

        $files = File::where('Client_ID', $clientId)
            ->where('Ledger_Ref', 'LIKE', "%{$query}%")
            ->select('Ledger_Ref')
            ->distinct()
            ->limit(15)
            ->get();

        return response()->json($files);
    }

    public function getReferencesForAutocomplete(Request $request)
    {
        $query = $request->input('query', '');

        $references = AccountRef::where('Reference', 'LIKE', "%{$query}%")
            ->select('Reference as reference') // Make sure the field name matches what JS expects
            ->distinct()
            ->limit(15)
            ->get();

        return response()->json($references);
    }

    public function downloaddaybookpdf(Request $request)
    {
        $clientId = auth()->user()->Client_ID;
        $getclient = Client::where('Client_ID', $clientId)->first();
        $client_name = $getclient->Business_Name;

        $transactions = Transaction::with([
            'file.client',
            'bankAccount.bankAccountType',
            'paymentType',
            'accountRef',
            'vatType',
        ])
            ->whereHas('file.client', function ($query) use ($clientId) {
                $query->where('Client_ID', $clientId);
            })
            ->where('Is_Imported', 0)
            ->whereNull('Deleted_On')
            ->orderByDesc('Transaction_Date')
            ->get();

        $pdf = Pdf::loadView('admin.pdf.daybookpdf', compact('transactions', 'client_name'));
        return $pdf->download('daybook_report.pdf');
    }

    public function getLedgerRefs(Request $request)
    {
        $query = $request->input('query', '');
        $clientId = auth()->user()->Client_ID;

        $files = File::where('Client_ID', $clientId)
            ->where('Ledger_Ref', 'LIKE', "%{$query}%")
            ->select('Ledger_Ref')
            ->limit(10)
            ->get();

        return response()->json($files);
    }


    public function getLedgerDetails($ledgerRef)
    {
        $clientId = auth()->user()->Client_ID;
        $client = Client::where('Client_ID', $clientId)->first();

        $file = File::where('Client_ID', $clientId)
            ->where('Ledger_Ref', $ledgerRef)
            ->first();


        if (!$file) {
            return response()->json(['error' => 'Ledger not found'], 404);
        }

        // Get related bank accounts (assumed: Bank_Type_ID 1 = Client, 2 = Office)
        $clientBankAccount = BankAccount::where('Client_ID', $clientId)
            ->where('Bank_Type_ID', 1) // Client
            ->first();

        $officeBankAccount = BankAccount::where('Client_ID', $clientId)
            ->where('Bank_Type_ID', 2) // Office
            ->first();

        $clientBalance = 0;
        $officeBalance = 0;

        if ($clientBankAccount) {
            $clientBalance = Transaction::join('file', 'file.File_ID', '=', 'transaction.File_ID')
                ->active()
                ->where('file.Client_ID', $clientId)
                ->where('file.Ledger_Ref', $ledgerRef)
                ->where('transaction.Bank_Account_ID', $clientBankAccount->Bank_Account_ID)
                ->sum(DB::raw("CASE WHEN transaction.Paid_In_Out = 1 THEN transaction.Amount ELSE -transaction.Amount END"));
        }

        if ($officeBankAccount) {
            $officeBalance = Transaction::join('file', 'file.File_ID', '=', 'transaction.File_ID')
                ->active()
                ->where('file.Client_ID', $clientId)
                ->where('file.Ledger_Ref', $ledgerRef)
                ->where('transaction.Bank_Account_ID', $officeBankAccount->Bank_Account_ID)
                ->sum(DB::raw("CASE WHEN transaction.Paid_In_Out = 1 THEN transaction.Amount ELSE -transaction.Amount END"));
        }

        return response()->json([

            'Client_Ref' => $client->Client_Ref ?? 'N/A',
            'Full_Name' => trim("{$file->First_Name} {$file->Last_Name}"),
            'Full_Address' => trim("{$file->Address1} {$file->Address2}"),
            'Ledger_Ref' => $file->Ledger_Ref,
            'Matter' => $file->Matter,
            'Sub_Matter' => $file->Sub_Matter,
            'Client_Ledger_Balance' => number_format($clientBalance ?? 0, 2),
            'Office_Ledger_Balance' => number_format($officeBalance ?? 0, 2),
        ]);
    }



    public function create(Request $request)
    {
        // ✅ STEP 1: Load draft data FIRST (if exists)
        $editData = $this->loadDraftForEdit($request);

        // ✅ STEP 2: Determine type and payment type
        if ($editData) {
            // If editing, use the draft's detected type and payment type
            $type = $editData['type'];
            $paymentType = $editData['payment_type'];

            Log::info('📝 Editing draft - using detected values', [
                'invoice_id' => $editData['invoice']->id,
                'detected_type' => $type,
                'detected_payment_type' => $paymentType,
                'invoice_no' => $editData['invoice_no']
            ]);
        } else {
            // If creating new, use request parameters
            $type = $request->get('type', 'client');
            $paymentType = $request->get(
                'payment_type',
                $type === 'office' ? 'payment' : 'inter_bank_client'
            );

            Log::info('➕ Creating new transaction', [
                'type' => $type,
                'payment_type' => $paymentType
            ]);
        }

        // ✅ STEP 3: Validate type
        if (!in_array($type, ['client', 'office'])) {
            $type = 'client';
        }

        // ✅ STEP 4: Route to appropriate method with correct payment type
        if ($type === 'office') {
            return $this->createOffice($request, $paymentType, $editData);
        } else {
            return $this->createClient($request, $paymentType, $editData);
        }
    }

    public function loadDraftForEdit(Request $request)
    {
        $invoiceId = $request->get('edit_invoice_id');

        if (!$invoiceId) {
            return null;
        }

        // ✅ Load with product relationship
        $invoice = Invoice::with(['items.product', 'customerFile']) // Added .product
            ->where('status', 'draft')
            ->find($invoiceId);

        if (!$invoice) {
            return null;
        }



        // ✅ CRITICAL: Detect payment type from invoice number prefix
        $invoiceNo = $invoice->invoice_no;
        $prefix = strtoupper(substr($invoiceNo, 0, 3));

        $paymentTypeMap = [
            'SIN' => 'sales_invoice',
            'SCN' => 'sales_credit',
            'PUR' => 'purchase',
            'PUC' => 'purchase_credit',
            'JOU' => 'journal',
            'PAY' => 'payment',
            'REC' => 'receipt',
            'CHQ' => 'cheque',
            'IBO' => 'inter_bank_office',
            'IBC' => 'inter_bank_client',
        ];

        $detectedPaymentType = $paymentTypeMap[$prefix] ?? 'sales_invoice';

        // ✅ Get customer's File_ID (for dropdown matching)
        $customerFileId = $invoice->customer;

        // ✅ Format items with ALL fields
        $formattedItems = [];
        foreach ($invoice->items as $item) {

            // ✅ NEW: Get full product data if product_id exists
            $productVatId = $item->vat_form_label_id;
            $productImage = null;

            if ($item->product_id && $item->product) {
                $product = $item->product;

                // ✅ DEBUG: Log product VAT
                Log::info('Product loaded for draft', [
                    'product_id' => $product->id,
                    'product_vat_rate_id' => $product->vat_rate_id, // This should be 2
                    'draft_item_vat_id' => $item->vat_form_label_id, // This is 2 now
                ]);

                // Use product's VAT if not overridden
                if (!$productVatId && $product->vat_rate_id) {
                    $productVatId = $product->vat_rate_id;

                    Log::info('Using product VAT as fallback', [
                        'product_vat_rate_id' => $product->vat_rate_id
                    ]);
                }

                // Get product image
                if ($product->file_url) {
                    $productImage = $product->file_url;
                }

                Log::info('Product data loaded for draft item:', [
                    'product_id' => $product->id,
                    'vat_rate_id' => $product->vat_rate_id,
                    'file_url' => $product->file_url,
                ]);
            }

            $formattedItems[] = [
                'item_code' => $item->item_code,
                'description' => $item->description,
                'ledger_id' => $item->chart_of_account_id,  // ✅ This should be 207, not 12
                'ledger_ref' => $item->ledger_ref,
                'account_ref' => $item->account_ref,
                'unit_amount' => $item->unit_amount,
                'vat_rate' => $item->vat_rate,
                'vat_amount' => $item->vat_amount,
                'net_amount' => $item->net_amount,
                'vat_form_label_id' => $item->vat_form_label_id,
                'vat_form_label_id' => $productVatId, // ✅ Now includes product VAT
                'product_image' => $productImage,
            ];
        }

        // ✅ Determine type (office vs client) from payment type
        $officeTypes = ['payment', 'receipt', 'cheque', 'journal', 'sales_invoice', 'sales_credit', 'purchase', 'purchase_credit', 'inter_bank_office'];
        $detectedType = in_array($detectedPaymentType, $officeTypes) ? 'office' : 'client';

        // ✅ DEBUG: Log final return data
        Log::info('loadDraftForEdit return:', [
            'invoice_id' => $invoice->id,
            'customer' => $customerFileId,
            'payment_type' => $detectedPaymentType,
            'items_count' => count($formattedItems),
            'items' => $formattedItems,  // ✅ Full items for debugging
        ]);

        return [
            'invoice' => $invoice,
            'customer' => $customerFileId,
            'invoice_date' => $invoice->invoice_date->format('Y-m-d'),
            'due_date' => $invoice->due_date ? $invoice->due_date->format('Y-m-d') : null,
            'invoice_no' => $invoice->invoice_no,
            'invoice_ref' => $invoice->invoice_ref,
            'notes' => $invoice->notes,
            'items' => $formattedItems,
            'payment_type' => $detectedPaymentType,
            'type' => $detectedType,
        ];
    }

    private function createClient(Request $request, $paymentType)
    {
        // Define type variable
        $type = 'client';

        // Validate client payment types
        $validPaymentTypes = ['inter_bank_client', 'inter_ledger', 'payment', 'receipt', 'cheque'];
        if (!in_array($paymentType, $validPaymentTypes)) {
            $paymentType = 'inter_bank_client';
        }

        $currentClientId = auth()->user()->Client_ID;

        // Get bank accounts for client
        $bankAccountsQuery = BankAccount::with('bankAccountType')
            ->where('Client_ID', $currentClientId)
            ->where('Is_Deleted', 0);

        if (in_array($paymentType, ['payment', 'receipt', 'cheque'])) {
            $bankAccountsQuery->where('Bank_Type_ID', 1);
        }
        $bankAccounts = $bankAccountsQuery->get();

        // Get ledger references
        $ledgerRefs = File::where('Client_ID', $currentClientId)
            ->whereNotNull('Ledger_Ref')
            ->where('Ledger_Ref', '!=', '')
            ->select('Ledger_Ref', 'First_Name', 'Last_Name', 'Matter')
            ->orderBy('Ledger_Ref')
            ->get();


        // Receipt Payment Types - IDs for Cost on account, Disbursement, Cost & Disb, Others Receipt
        $receiptPaymentTypeIds = [42, 47, 48, 49];

        // Payment Payment Types - IDs for Bill of Cost, Disbursement, BOC & Disb  
        $paymentPaymentTypeIds = [41, 42, 43, 45];

        // Get payment types for each category
        $receiptPaymentTypes = PaymentType::whereIn('Payment_Type_ID', $receiptPaymentTypeIds)
            ->select('Payment_Type_ID', 'Payment_Type_Name')
            ->orderBy('Payment_Type_ID')
            ->get();

        $paymentPaymentTypes = PaymentType::whereIn('Payment_Type_ID', $paymentPaymentTypeIds)
            ->select('Payment_Type_ID', 'Payment_Type_Name')
            ->orderBy('Payment_Type_ID')
            ->get();

        // All payment types for other transaction types
        $allPaymentTypes = PaymentType::whereBetween('Payment_Type_ID', [41, 45])
            ->select('Payment_Type_ID', 'Payment_Type_Name')
            ->orderBy('Payment_Type_ID')
            ->get();

        // Generate auto code
        $autoCode = $this->generateAutoCode($paymentType, 'client');

        return view('admin.day_book.create_client', compact(
            'bankAccounts',
            'paymentType',
            'autoCode',
            'ledgerRefs',
            'receiptPaymentTypes',
            'paymentPaymentTypes',
            'allPaymentTypes',
            'type'
        ));
    }

    /**
     * Create form for OFFICE accounts
     */
    private function createOffice(Request $request, $paymentType, $editData = null)
    {
        $type = 'office';

        $validPaymentTypes = [
            'payment',
            'receipt',
            'sales_invoice',
            'cheque',
            'sales_credit',
            'purchase',
            'purchase_credit',
            'inter_bank_office',
            'journal'
        ];
        if (!in_array($paymentType, $validPaymentTypes)) {
            $paymentType = 'payment';
        }

        $currentClientId = auth()->user()->Client_ID;

        if (!$editData) {
            $editData = $this->loadDraftForEdit($request);
        }

        // Base query: only this client's active bank accounts
        $bankAccountsQuery = BankAccount::with('bankAccountType')
            ->where('Client_ID', $currentClientId)
            ->where('Is_Deleted', 0);

        // If inter_bank_office -> ALL banks for client (no type filter)
        if ($paymentType !== 'inter_bank_office') {
            // Otherwise, only office banks
            $bankAccountsQuery->where('Bank_Type_ID', 2);
        }

        $bankAccounts = $bankAccountsQuery
            ->orderBy('Bank_Name')
            ->get();

        $vatTypes = VatType::orderBy('VAT_Name')->get();
        $autoCode =  $editData ? $editData['invoice_no'] : $this->generateAutoCode($paymentType, 'office');

        return view('admin.day_book.create_office', compact(
            'bankAccounts',
            'vatTypes',
            'paymentType',
            'autoCode',
            'type',
            'editData'
        ));
    }

    public function listBankAccounts(Request $request)
    {
        $scope = $request->query('scope', 'office'); // 'all' | 'office'
        $clientId = auth()->user()->Client_ID;

        $q = BankAccount::with('bankAccountType')
            ->where('Client_ID', $clientId)
            ->where('Is_Deleted', 0);

        if ($scope === 'office') {
            $q->where('Bank_Type_ID', 2);
        }
        // scope === 'all' => no extra filter

        $banks = $q->orderBy('Bank_Name')
            ->get(['Bank_Account_ID', 'Bank_Name', 'Bank_Type_ID']);

        return response()->json(['success' => true, 'banks' => $banks]);
    }


    /**
     * Generate auto code based on payment type with sequential 6-digit numbers
     */
    private function generateAutoCode($paymentType, $accountType = 'client')
    {
        $prefix = '';

        if ($accountType === 'office') {
            // Office account prefixes (shorter, no 'C' suffix)
            switch ($paymentType) {
                case 'payment':
                    $prefix = 'PAY';
                    break;
                case 'receipt':
                    $prefix = 'REC';
                    break;
                case 'sales_invoice':
                    $prefix = 'SIN';
                    break;
                case 'cheque':
                    $prefix = 'CHQ';
                    break;
                case 'sales_credit':
                    $prefix = 'SCN';
                    break;
                case 'purchase':
                    $prefix = 'PUR';
                    break;
                case 'purchase_credit':
                    $prefix = 'PUC';
                    break;
                case 'inter_bank_office':
                    $prefix = 'IBO';
                    break;
                case 'journal':
                    $prefix = 'JOU';
                    break;
                default:
                    $prefix = 'PAY'; // Default to PAY for office
            }
        } else {
            // Client account prefixes (with 'C' suffix)
            switch ($paymentType) {
                case 'inter_bank_client':
                    $prefix = 'BTBC';
                    break;
                case 'inter_ledger':
                    $prefix = 'LTLC';
                    break;
                case 'payment':
                    $prefix = 'PAYC';
                    break;
                case 'receipt':
                    $prefix = 'RECC';
                    break;
                case 'cheque':
                    $prefix = 'CHQC';
                    break;
                default:
                    $prefix = 'PAYC'; // Default to PAYC for client
            }
        }

        // Get the next sequential number for this prefix
        $nextNumber = $this->getNextSequentialNumber($prefix);

        // Format as 6-digit number with leading zeros (000001, 000002, etc.)
        $sequentialNumber = str_pad($nextNumber, 6, '0', STR_PAD_LEFT);

        // Combine prefix with sequential number
        $autoCode = $prefix . $sequentialNumber;

        return $autoCode;
    }

    /**
     * Get the next sequential number for a given prefix
     */


    private function getNextSequentialNumber($prefix)
    {
        // ✅ Get current user's Client_ID and User_ID
        $currentClientId = auth()->user()->Client_ID;
        $currentUserId = auth()->id();

        // ✅ Determine transaction type based on prefix
        $isPurchase = in_array($prefix, ['PUR', 'PUC']); // Purchase & Purchase Credit

        // ✅ Build query with proper relationship filtering
        $query = Transaction::where('Transaction_Code', 'LIKE', $prefix . '%');

        if ($isPurchase) {
            // ✅ For PURCHASE transactions: Filter by supplier's user_id
            $query->where(function ($q) use ($currentUserId, $currentClientId) {
                $q->whereHas('invoice.supplier', function ($supplierQuery) use ($currentUserId) {
                    $supplierQuery->where('user_id', $currentUserId);
                })
                    // Also check bank account as fallback (office bank transactions)
                    ->orWhereHas('bankAccount', function ($bankQuery) use ($currentClientId) {
                        $bankQuery->where('Client_ID', $currentClientId);
                    });
            });

            Log::info('🔍 Purchase code query built', [
                'prefix' => $prefix,
                'user_id' => $currentUserId,
                'client_id' => $currentClientId
            ]);
        } else {
            // ✅ For SALES/OTHER transactions: Use existing logic
            $query->where(function ($q) use ($currentClientId) {
                // OPTION 1: Transaction linked to File (client transactions)
                $q->whereHas('file', function ($fileQuery) use ($currentClientId) {
                    $fileQuery->where('Client_ID', $currentClientId);
                })
                    // OPTION 2: Transaction linked to BankAccount (office transactions)
                    ->orWhereHas('bankAccount', function ($bankQuery) use ($currentClientId) {
                        $bankQuery->where('Client_ID', $currentClientId);
                    })
                    // OPTION 3: Transaction linked to Invoice → Customer → File
                    ->orWhereHas('invoice.customerFile', function ($customerQuery) use ($currentClientId) {
                        $customerQuery->where('Client_ID', $currentClientId);
                    });
            });
        }

        // ✅ Get the highest existing transaction code
        $lastTransaction = $query
            ->orderByRaw('CAST(SUBSTRING(Transaction_Code, ' . (strlen($prefix) + 1) . ') AS UNSIGNED) DESC')
            ->first();

        if (!$lastTransaction) {
            Log::info('✅ No existing transactions found, starting from 1', [
                'prefix' => $prefix,
                'is_purchase' => $isPurchase
            ]);
            return 1;
        }

        // Extract the number part from the last transaction code
        $lastCode = $lastTransaction->Transaction_Code;
        $numberPart = substr($lastCode, strlen($prefix)); // Remove prefix

        // Convert to integer and increment
        $lastNumber = intval($numberPart);
        $nextNumber = $lastNumber + 1;

        Log::info('✅ Next code calculated', [
            'prefix' => $prefix,
            'last_code' => $lastCode,
            'last_number' => $lastNumber,
            'next_number' => $nextNumber,
            'is_purchase' => $isPurchase
        ]);

        // Ensure we haven't exceeded the 6-digit limit (999999)
        if ($nextNumber > 999999) {
            throw new \Exception("Transaction code sequence has reached maximum limit for prefix: {$prefix}");
        }

        return $nextNumber;
    }

    /**
     * Generate next sequential transaction code for inter-bank transfers
     * This is used for the second transaction in inter-bank transfers
     */

    private function generateNextSequentialCode(string $baseCode): string
    {
        $prefixLength = 4; // All prefixes are 4 characters (BTBC, LTLC, etc.)
        $prefix = substr($baseCode, 0, $prefixLength);
        $currentNumber = intval(substr($baseCode, $prefixLength));

        // Increment the number
        $nextNumber = $currentNumber + 1;

        // Ensure we haven't exceeded the 6-digit limit
        if ($nextNumber > 999999) {
            throw new \Exception("Transaction code sequence has reached maximum limit for prefix: {$prefix}");
        }

        // Format as 6-digit number with leading zeros
        $sequentialNumber = str_pad($nextNumber, 6, '0', STR_PAD_LEFT);

        // Combine prefix with sequential number
        $nextCode = $prefix . $sequentialNumber;

        // ✅ FIXED: Check uniqueness FOR THIS USER ONLY
        $currentClientId = auth()->user()->Client_ID;

        $exists = Transaction::where('Transaction_Code', $nextCode)
            ->where(function ($query) use ($currentClientId) {
                $query->whereHas('file', function ($q) use ($currentClientId) {
                    $q->where('Client_ID', $currentClientId);
                })
                    ->orWhereHas('bankAccount', function ($q) use ($currentClientId) {
                        $q->where('Client_ID', $currentClientId);
                    })
                    ->orWhereHas('invoice.customerFile', function ($q) use ($currentClientId) {
                        $q->where('Client_ID', $currentClientId);
                    });
            })
            ->exists();

        if ($exists) {
            // This should rarely happen with sequential numbers, but just in case
            throw new \Exception("Sequential transaction code already exists: {$nextCode}");
        }

        return $nextCode;
    }

    /**
     * ✅ FIXED: Generate auto code via AJAX (PER USER)
     */
    public function generateAutoCodeAjax(Request $request)
    {
        try {
            // Validate the payment type
            $paymentType = $request->input('payment_type', 'payment');
            $accountType = $request->input('account_type', 'client');

            // Validate payment type parameter
            if ($accountType === 'client') {
                $validPaymentTypes = ['inter_bank_client', 'inter_ledger', 'payment', 'receipt', 'cheque'];
            } else {
                $validPaymentTypes = ['payment', 'receipt', 'sales_invoice', 'cheque', 'sales_credit', 'purchase', 'purchase_credit', 'inter_bank_office', 'journal'];
            }

            if (!in_array($paymentType, $validPaymentTypes)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid payment type for ' . $accountType . ' account: ' . $paymentType
                ], 400);
            }

            // ✅ GENERATE AUTO CODE WITH PROPER ACCOUNT TYPE (PER USER)
            $autoCode = $this->generateAutoCode($paymentType, $accountType);

            // ✅ ENSURE UNIQUENESS FOR THIS USER
            $currentClientId = auth()->user()->Client_ID;
            $attempts = 0;

            while ($attempts < 10) {
                $exists = Transaction::where('Transaction_Code', $autoCode)
                    ->where(function ($query) use ($currentClientId) {
                        $query->whereHas('file', function ($q) use ($currentClientId) {
                            $q->where('Client_ID', $currentClientId);
                        })
                            ->orWhereHas('bankAccount', function ($q) use ($currentClientId) {
                                $q->where('Client_ID', $currentClientId);
                            })
                            ->orWhereHas('invoice.customerFile', function ($q) use ($currentClientId) {
                                $q->where('Client_ID', $currentClientId);
                            });
                    })
                    ->exists();

                if (!$exists) {
                    break;
                }

                $attempts++;
                $autoCode = $this->generateAutoCode($paymentType, $accountType);
            }

            if ($attempts >= 10) {
                $timestamp = now()->format('His');
                $autoCode = substr($autoCode, 0, -2) . substr($timestamp, -2);
            }

            return response()->json([
                'success' => true,
                'auto_code' => $autoCode,
                'payment_type' => $paymentType,
                'account_type' => $accountType,
                'message' => 'Auto code generated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating auto code: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * ✅ FIXED: Check if transaction code is unique (PER USER)
     */
    public function checkTransactionCodeUnique(Request $request)
    {
        try {
            $transactionCode = trim($request->input('transaction_code'));

            if (empty($transactionCode)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction code is required'
                ], 400);
            }

            // ✅ Detect if this is a purchase transaction
            $prefix = substr($transactionCode, 0, 3);
            $isPurchase = in_array($prefix, ['PUR', 'PUC']);

            $currentClientId = auth()->user()->Client_ID;
            $currentUserId = auth()->id();

            // ✅ Build query based on transaction type
            $query = Transaction::where('Transaction_Code', $transactionCode);

            if ($isPurchase) {
                // Check against THIS USER's purchase transactions
                $query->where(function ($q) use ($currentUserId, $currentClientId) {
                    $q->whereHas('invoice.supplier', function ($supplierQuery) use ($currentUserId) {
                        $supplierQuery->where('user_id', $currentUserId);
                    })
                        ->orWhereHas('bankAccount', function ($bankQuery) use ($currentClientId) {
                            $bankQuery->where('Client_ID', $currentClientId);
                        });
                });
            } else {
                // Check against THIS USER's sales/other transactions
                $query->where(function ($q) use ($currentClientId) {
                    $q->whereHas('file', function ($fileQuery) use ($currentClientId) {
                        $fileQuery->where('Client_ID', $currentClientId);
                    })
                        ->orWhereHas('bankAccount', function ($bankQuery) use ($currentClientId) {
                            $bankQuery->where('Client_ID', $currentClientId);
                        })
                        ->orWhereHas('invoice.customerFile', function ($customerQuery) use ($currentClientId) {
                            $customerQuery->where('Client_ID', $currentClientId);
                        });
                });
            }

            $exists = $query->exists();

            return response()->json([
                'success' => true,
                'exists' => $exists,
                'message' => $exists ? 'Transaction code already exists' : 'Transaction code is available'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error checking transaction code: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ Update draft invoice status to 'issued' (from preview)
     */
    private function updateDraftToIssued(?string $draftKey, ?int $invoiceId): void
    {
        if (!$draftKey || !$invoiceId) {
            Log::warning('Cannot update draft - missing key or invoice ID', [
                'draft_key' => $draftKey,
                'invoice_id' => $invoiceId
            ]);
            return;
        }

        try {
            $draft = DraftInvoice::where('draft_key', $draftKey)->first();

            if ($draft) {
                $draft->update([
                    'invoice_id' => $invoiceId,
                    'status' => DraftInvoice::STATUS_ISSUED
                ]);

                Log::info('✅ Draft updated to issued', [
                    'draft_key' => $draftKey,
                    'invoice_id' => $invoiceId
                ]);
            } else {
                Log::warning('⚠️ Draft not found for key', ['draft_key' => $draftKey]);
            }
        } catch (\Exception $e) {
            Log::error('❌ Failed to update draft status', [
                'draft_key' => $draftKey,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * ✅ FIXED: Create issued draft record with normalized items
     */
    private function createIssuedDraft(array $validated, int $invoiceId): void
    {
        if (!$invoiceId) {
            Log::warning('Cannot create issued draft - missing invoice ID');
            return;
        }

        try {
            $clientId = auth()->user()->Client_ID;

            // ✅ FIX: Normalize items array to zero-based before storing
            $normalizedData = $validated;
            if (isset($normalizedData['items']) && is_array($normalizedData['items'])) {
                $normalizedData['items'] = array_values($normalizedData['items']);
            }

            DraftInvoice::create([
                'draft_key' => DraftInvoice::generateKey(),
                'client_id' => $clientId,
                'invoice_id' => $invoiceId,
                'status' => DraftInvoice::STATUS_ISSUED,
                'invoice_data' => $normalizedData, // ✅ Store normalized data
                'expires_at' => now()->addDays(30)
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Failed to create issued draft', [
                'invoice_id' => $invoiceId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * ✅ UPDATED: Modified store method to handle invoice-based transactions
     */
    public function store(TransactionRequest $request)
    {

        $validated = $request->validated();
        // dd('store method called', $validated);  

        DB::beginTransaction();

        try {
            $accountType = $request->input('account_type');
            $paymentType = $request->input('current_payment_type');
            $transactionCode = trim($request->input('Transaction_Code'));
            $action = $request->input('action');


            if ($action === 'save_as_draft') {
                $editInvoiceId = $request->input('edit_invoice_id'); // Get invoice ID if editing
                return $this->saveDraftInvoice($request, $transactionCode, $editInvoiceId);
            }


            // ✅ Define invoice-based payment types
            $invoiceBasedTypes = ['sales_invoice', 'sales_credit', 'purchase', 'purchase_credit', 'journal'];
            // ✅ ADD TRANSACTION CODE GENERATION FOR BOTH CLIENT AND OFFICE
            if (empty($transactionCode)) {
                $paymentType = $request->input(
                    'current_payment_type',
                    $accountType === 'office' ? 'payment' : 'inter_bank_client'
                );
                $transactionCode = $this->generateAutoCode($paymentType, $accountType);

                // Double-check uniqueness of generated code
                $attempts = 0;
                while (Transaction::where('Transaction_Code', $transactionCode)->exists() && $attempts < 10) {
                    $transactionCode = $this->generateAutoCode($paymentType, $accountType);
                    $attempts++;
                }

                if (Transaction::where('Transaction_Code', $transactionCode)->exists()) {
                    $timestamp = now()->format('His');
                    $transactionCode = substr($transactionCode, 0, -2) . substr($timestamp, -2);
                }
            }

            if ($accountType === 'office') {

                // ✅ Handle invoice-based office transactions
                if (in_array($paymentType, $invoiceBasedTypes)) {

                    $transaction = $this->handleInvoiceBasedTransaction($validated, $transactionCode, $request);

                    // ✅ NEW: Get draft_key from request (may be null if direct save)
                    $draftKey = $request->input('draft_key');

                    // ✅ NEW: Create or update draft invoice
                    if ($draftKey) {
                        // Preview → Save: Update existing draft
                        $this->updateDraftToIssued($draftKey, $transaction->invoice_id);
                    } else {
                        // Direct Save: Create new draft record
                        $this->createIssuedDraft($validated, $transaction->invoice_id);
                    }

                    $successMessage = $this->getSuccessMessage($paymentType) . " created successfully";

                    // Handle special actions for invoice buttons
                    if ($action === 'save_and_email') {
                        $this->sendInvoiceEmail($transaction, $validated);
                        $successMessage .= " and email sent successfully";
                    } elseif ($action === 'save_and_add_new') {
                        DB::commit();
                        return redirect()->route('transactions.create', [
                            'type' => 'office',
                            'payment_type' => $paymentType
                        ])->with('success', $successMessage . ". Ready to create new " . $this->getFormTitle($paymentType) . ".");
                    }

                    DB::commit();
                    return redirect()->route('transactions.index')->with('success', $successMessage);
                } else {
                    // ✅ inter-bank office => create 2 entries
                    if ($paymentType === 'inter_bank_office') {
                        $this->handleInterBankOfficeTransfer($validated, $transactionCode, $request);
                        $successMessage = "Office inter-bank transfer completed successfully";
                    } else {
                        // (as-is) single-bank office flows (payment/receipt/cheque/etc)
                        $this->handleOfficeTransaction($validated, $transactionCode, $request);
                        $successMessage = "Office transaction created successfully";
                    }
                }
            } else {

                // ✅ Handle client transactions
                if ($paymentType === 'inter_bank_client') {
                    $this->handleInterBankClientTransfer($validated, $transactionCode);
                    $successMessage = "Inter-bank transfer completed successfully";
                } elseif ($paymentType === 'inter_ledger') {
                    $this->handleInterLedgerTransfer($validated, $transactionCode);
                    $successMessage = "Inter-ledger transfer completed successfully";
                } else {

                    $this->handleSingleTransaction($validated, $transactionCode, $paymentType);
                    $direction = $this->getPaidInOutDirection($paymentType) === 1 ? 'received' : 'paid out';
                    $successMessage = ucfirst($paymentType) . " transaction {$direction} successfully";
                }
            }

            DB::commit();
            return redirect()->route('transactions.index')->with('success', $successMessage);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred while creating the transaction: ' . $e->getMessage());
        }
    }


    private function saveDraftInvoice(Request $request, string $transactionCode, ?int $editInvoiceId = null)
    {
        $isUpdate = !is_null($editInvoiceId);

        Log::info($isUpdate ? '=== DRAFT UPDATE STARTED ===' : '=== DRAFT SAVE STARTED ===', [
            'transaction_code' => $transactionCode,
            'payment_type' => $request->input('current_payment_type'),
            'user_id' => auth()->id(),
            'edit_invoice_id' => $editInvoiceId,
            'mode' => $isUpdate ? 'UPDATE' : 'CREATE'
        ]);

        try {
            $paymentType = $request->input('current_payment_type');
            Log::info('Payment type validated', ['payment_type' => $paymentType]);

            $invoiceBasedTypes = ['sales_invoice', 'sales_credit', 'purchase', 'purchase_credit', 'journal'];
            if (!in_array($paymentType, $invoiceBasedTypes)) {
                Log::warning('Invalid payment type for draft', ['payment_type' => $paymentType]);
                throw new \Exception('Drafts can only be created for invoice-based transactions.');
            }

            $accountId = $request->input('file_id');
            Log::info('Account ID extracted', ['account_id' => $accountId]);

            if (!$accountId) {
                Log::error('Missing account ID');
                throw new \Exception($this->getContextualAccountMessage($paymentType));
            }

            Log::info('Extracting items...', ['payment_type' => $paymentType]);

            if ($paymentType === 'journal') {
                $items = $this->extractAndValidateJournalItems($request);
            } else {
                $items = $this->extractAndValidateInvoiceItems($request);
            }

            Log::info('Items extracted', [
                'item_count' => count($items),
                'items_preview' => array_slice($items, 0, 2)
            ]);

            if (empty($items)) {
                Log::warning('No items found');
                throw new \Exception('Please add at least one item to save as draft.');
            }

            Log::info('Calculating totals...');

            $subtotal = collect($items)->sum('unit_amount');
            $vatTotal = collect($items)->sum('vat_amount');
            $grandTotal = collect($items)->sum('net_amount');

            Log::info('Totals calculated', [
                'subtotal' => $subtotal,
                'vat_total' => $vatTotal,
                'grand_total' => $grandTotal
            ]);

            // Prepare invoice data
            $invoiceData = [
                'invoice_date' => $request->input('Transaction_Date'),
                'due_date' => $request->input('Inv_Due_Date'),
                'invoice_no' => $request->input('invoice_no') ?: $transactionCode,
                'invoice_ref' => $request->input('invoice_ref'),
                'notes' => $request->input('notes'),
                'net_amount' => $subtotal,
                'vat_amount' => $vatTotal,
                'total_amount' => $grandTotal,
                'documents' => $request->input('invoice_documents'),
            ];

            // ✅ NEW: Get customer context
            // $customerContext = $this->getCustomerContextData($accountId);
            $customerContext = $this->getCustomerContextData($accountId, $paymentType);

            if ($isUpdate) {
                // Update existing draft
                $invoice = Invoice::where('status', 'draft')
                    ->where('id', $editInvoiceId)
                    ->firstOrFail();

                Log::info('Found existing draft for update', ['invoice_id' => $invoice->id]);

                // ✅ Use InvoiceService to update
                $invoice = $this->invoiceService->updateInvoice(
                    $invoice,
                    $invoiceData,
                    $items
                );

                Log::info('Draft invoice updated successfully', ['invoice_id' => $invoice->id]);
            } else {
                // Create new draft
                Log::info('Creating new draft invoice');

                // ✅ Use InvoiceService to create draft
                $invoice = $this->invoiceService->createInvoice(
                    $invoiceData,
                    $customerContext['customer_model'],
                    $customerContext['customer_id'],
                    $items,
                    false // isIssued = false (it's a draft!)
                );

                Log::info('Draft invoice created successfully', ['invoice_id' => $invoice->id]);
            }

            DB::commit();

            $successMessage = $isUpdate
                ? 'Draft invoice updated successfully. You can continue editing or issue it.'
                : 'Invoice saved as draft successfully. You can edit or issue it later.';

            Log::info($isUpdate ? '=== DRAFT UPDATE COMPLETED ===' : '=== DRAFT SAVE COMPLETED ===', [
                'invoice_id' => $invoice->id,
                'invoice_no' => $invoice->invoice_no
            ]);

            return redirect()
                ->route('invoices.index', ['tab' => 'drafts'])
                ->with('success', $successMessage);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('=== DRAFT SAVE/UPDATE FAILED ===', [
                'error_message' => $e->getMessage(),
                'error_line' => $e->getLine(),
                'error_file' => $e->getFile(),
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to save draft: ' . $e->getMessage());
        }
    }



    private function handleInterBankOfficeTransfer(array $validated, string $transactionCode, Request $request): void
    {
        // Validate both office banks
        $fromBank = $this->validateBankAccount($validated['Bank_Account_From_ID'], 'source office bank account');
        $toBank   = $this->validateBankAccount($validated['Bank_Account_To_ID'],   'destination office bank account');

        $coa = ChartOfAccount::where('id', $validated['chart_of_account_id'])
            ->where('is_active', 1)
            ->first();

        if (!$coa) {
            throw new \Exception('Invalid chart of account selected.');
        }

        // Resolve Account_Ref_ID
        $ledgerId = (int) $validated['chart_of_account_id'];
        $accountRefText = isset($validated['account_ref']) ? trim((string)$validated['account_ref']) : null;

        // ✅ USE TransactionService
        $accountRefIdForTxn = $this->transactionService->resolveAccountRefIdByLedgerId($ledgerId, $accountRefText);

        $invoiceItems = $this->extractAndValidateInvoiceItems($request);
        if (!empty($invoiceItems)) {
            $fromItems = $this->deriveAccountRefIdFromItems($invoiceItems);
            if ($fromItems) {
                $accountRefIdForTxn = $fromItems;
            }
        }

        // Common fields
        $common = [
            'Transaction_Date'    => $validated['Transaction_Date'],
            'chart_of_account_id' => $validated['chart_of_account_id'],
            'Payment_Type_ID'     => $validated['Payment_Type_ID'] ?? null,
            'Cheque'              => $validated['Payment_Ref'] ?? '',
            'Amount'              => $validated['Amount'],
            'Description'         => $validated['Description'] ?? '',
            'VAT_ID'              => $validated['VAT_ID'] ?? null,
            'Account_Ref_ID'      => $accountRefIdForTxn,
        ];

        // Make two unique codes
        $codeFrom = $transactionCode;
        $codeTo   = $transactionCode . 'B';
        if (Transaction::where('Transaction_Code', $codeTo)->exists()) {
            $codeTo = $this->generateAutoCode('inter_bank_office', 'office');
        }

        // ✅ FROM entry (money OUT) - USE TransactionService
        $this->transactionService->createOfficeTransaction([
            ...$common,
            'Bank_Account_ID'  => (int) $validated['Bank_Account_From_ID'],
            'Paid_In_Out'      => Transaction::MONEY_OUT,
            'Entry_Type'       => 'Dr',
            'Transaction_Code' => $codeFrom,
        ]);

        // ✅ TO entry (money IN) - USE TransactionService
        $this->transactionService->createOfficeTransaction([
            ...$common,
            'Bank_Account_ID'  => (int) $validated['Bank_Account_To_ID'],
            'Paid_In_Out'      => Transaction::MONEY_IN,
            'Entry_Type'       => 'Cr',
            'Transaction_Code' => $codeTo,
        ]);
    }



    /**
     * ✅ NEW: Get success message based on payment type
     */
    private function getSuccessMessage(string $paymentType): string
    {
        $messages = [
            'sales_invoice' => 'Sales invoice',
            'sales_credit' => 'Sales credit note',
            'purchase' => 'Purchase invoice',
            'purchase_credit' => 'Purchase credit note',
            'journal' => 'Journal entry',
        ];

        return $messages[$paymentType] ?? 'Invoice transaction';
    }

    /**
     * ✅ NEW: Get form title based on payment type
     */
    private function getFormTitle(string $paymentType): string
    {
        $titles = [
            'sales_invoice' => 'sales invoice',
            'sales_credit' => 'sales credit note',
            'purchase' => 'purchase invoice',
            'purchase_credit' => 'purchase credit note',
            'journal' => 'journal entry',
        ];

        return $titles[$paymentType] ?? 'invoice';
    }

    /**
     * ✅ UPDATED: Handle all invoice-based transactions (sales_invoice, sales_credit, purchase, purchase_credit)
     */

    private function handleInvoiceBasedTransaction(array $validated, string $transactionCode, Request $request)
    {
        // Check if updating existing draft
        $editInvoiceId = $request->input('edit_invoice_id');
        $isUpdatingDraft = !is_null($editInvoiceId);

        if ($isUpdatingDraft) {
            Log::info('Issuing edited draft invoice', ['invoice_id' => $editInvoiceId]);

            // Load existing draft
            $existingInvoice = Invoice::where('status', 'draft')
                ->where('id', $editInvoiceId)
                ->firstOrFail();

            // Delete old items and transactions
            $existingInvoice->items()->delete();
            $existingInvoice->transactions()->delete();

            Log::info('Cleared old data for draft update');
        }

        $paymentType = $request->input('current_payment_type');

        if ($paymentType === 'journal') {
            return $this->handleJournalTransaction($validated, $transactionCode, $request);
        }

        // Validate bank account (optional for invoice flows)
        $bankAccountId = $validated['Bank_Account_ID'] ?? null;
        if ($bankAccountId) {
            $bankAccount = $this->validateBankAccount($bankAccountId, 'office bank account');
            if ($bankAccount->Bank_Type_ID !== 2) {
                throw new \Exception('Please select an office bank account for invoice transactions.');
            }
        }

        // Get customer ID from form
        $accountId = $request->input('file_id');

        if (!$accountId) {
            throw new \Exception($this->getContextualAccountMessage($paymentType));
        }

        // ✅ NEW: Get customer context (File model for main app)
        // $customerContext = $this->getCustomerContextData($accountId);
        $customerContext = $this->getCustomerContextData($accountId, $paymentType);
        // Validate customer exists
        $chartOfAccount = File::where('File_ID', $accountId)->where('Status', 'L')->first();

        if (!$chartOfAccount) {
            throw new \Exception('Invalid account selected.');
        }

        // Extract and validate items
        $invoiceItems = $this->extractAndValidateInvoiceItems($request);

        if (empty($invoiceItems)) {
            throw new \Exception('Please add at least one item to the invoice.');
        }

        // Prepare invoice data
        $invoiceData = [
            'invoice_date' => $validated['Transaction_Date'],
            'due_date' => $request->input('Inv_Due_Date'),
            'invoice_no' => $request->input('invoice_no', $transactionCode),
            'invoice_ref' => $request->input('invoice_ref'),
            'net_amount' => (float)$request->input('invoice_net_amount', 0),
            'vat_amount' => (float)$request->input('invoice_vat_amount', 0),
            'total_amount' => (float)$request->input('invoice_total_amount', 0),
            'documents' => $request->input('invoice_documents'),
        ];

        if ($invoiceData['total_amount'] <= 0) {
            throw new \Exception('Invoice total amount must be greater than zero.');
        }

        // ✅ NEW: Use InvoiceService to create/update invoice
        if ($isUpdatingDraft) {
            $invoice = $this->invoiceService->updateInvoice(
                $existingInvoice,
                $invoiceData,
                $invoiceItems
            );

            // Issue the draft
            $invoice = $this->invoiceService->issueDraftInvoice($invoice);

            Log::info('Updated existing draft to issued status', ['invoice_id' => $invoice->id]);
        } else {
            $invoice = $this->invoiceService->createInvoice(
                $invoiceData,
                $customerContext['customer_model'], // ✅ Pass File::class
                $customerContext['customer_id'],    // ✅ Pass File_ID
                $invoiceItems,
                true // isIssued = true
            );
        }

        // ✅ Create MULTIPLE transaction records - one per line item
        $transactions = [];
        foreach ($invoiceItems as $index => $item) {
            // Determine entry type and paid in/out for THIS specific item
            $chartId = $item['ledger_id'];
            $effect = $this->transactionService->effectForPaymentType($paymentType);
            $entryType = $this->transactionService->entryTypeFromCoaAndEffect($chartId, $effect);
            $paidIO = $this->transactionService->paidInOutFromEntryType($entryType);

            // Resolve account_ref_id for this item
            $accountRefId = $this->transactionService->resolveAccountRefIdByLedgerId($chartId, $item['account_ref']);

            // ✅ Create transaction using TransactionService
            $transaction = $this->transactionService->createInvoiceItemTransaction([
                'entry_type' => $entryType,
                'paid_in_out' => $paidIO,
                'invoice_id' => $invoice->id,
                'Transaction_Date' => $validated['Transaction_Date'],
                'Bank_Account_ID' => $bankAccountId,
                'chart_of_account_id' => $chartId,
                'Transaction_Code' => $transactionCode,
                'payment_type' => $paymentType,
                'unit_amount' => $item['unit_amount'],
                'vat_amount' => $item['vat_amount'],
                'net_amount' => $item['net_amount'],
                'description' => $item['description'],
                'invoice_ref' => $invoiceData['invoice_ref'],
                'account_ref_id' => $accountRefId,
                'vat_form_label_id' => $item['vat_form_label_id'],
                'item_code' => $item['item_code'],
                'Payment_Type_ID' => $validated['Payment_Type_ID'] ?? null,
            ]);

            $transactions[] = $transaction;

            Log::info('Invoice item transaction created', [
                'item_index' => $index,
                'transaction_id' => $transaction->Transaction_ID,
                'ledger_id' => $chartId,
                'amount' => $item['net_amount'],
            ]);
        }

        Log::info('Invoice transactions created', [
            'invoice_id' => $invoice->id,
            'total_transactions' => count($transactions),
            'invoice_total' => $invoiceData['total_amount'],
        ]);

        // Return the first transaction as the "primary" one
        return $transactions[0];
    }

    /**
     * Upload invoice document
     */
    public function uploadInvoiceDocument(Request $request)
    {
        try {
            $request->validate([
                'document' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg|max:5120', // 5MB max
            ]);

            if (!$request->hasFile('document')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No file uploaded'
                ], 400);
            }

            $file = $request->file('document');
            $clientId = auth()->user()->Client_ID;

            // Generate unique filename
            $timestamp = time();
            $randomHash = bin2hex(random_bytes(8));
            $extension = $file->getClientOriginalExtension();
            $filename = "{$timestamp}_{$randomHash}.{$extension}";

            // Store in temporary location (will move to invoice-specific folder after invoice created)
            // For now, store in: storage/app/public/invoices/temp/{client_id}/
            $tempPath = "invoices/temp/{$clientId}";
            $filePath = $file->storeAs($tempPath, $filename, 'public');

            return response()->json([
                'success' => true,
                'message' => 'Document uploaded successfully',
                'file_path' => $filePath,
                'file_name' => $file->getClientOriginalName(),
                'stored_filename' => $filename
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Invoice document upload error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle journal entries using InvoiceService
     */
    private function handleJournalTransaction(array $validated, string $transactionCode, Request $request)
    {
        $paymentType = 'journal';
        $items = $this->extractAndValidateJournalItems($request);

        if (empty($items)) {
            throw new \Exception('Please add at least one journal entry.');
        }

        $accountId = $request->input('file_id');
        if (!$accountId) {
            throw new \Exception('Please select an account.');
        }

        // ✅ NEW: Get customer context
        // $customerContext = $this->getCustomerContextData($accountId);
        $customerContext = $this->getCustomerContextData($accountId, $paymentType);
        // Create invoice header
        $invoiceData = [
            'invoice_date' => $validated['Transaction_Date'],
            'due_date' => $request->input('Inv_Due_Date'),
            'invoice_no' => $request->input('invoice_no', $transactionCode),
            'invoice_ref' => $request->input('Payment_Ref'),
            'net_amount' => collect($items)->sum('base_amount'),
            'vat_amount' => collect($items)->sum('vat_amount'),
            'total_amount' => collect($items)->sum('net_amount'),
        ];

        // ✅ NEW: Use InvoiceService
        $invoice = $this->invoiceService->createInvoice(
            $invoiceData,
            $customerContext['customer_model'],
            $customerContext['customer_id'],
            [], // Journal items are handled separately
            true
        );

        // ✅ CREATE SEPARATE TRANSACTION FOR EACH JOURNAL ENTRY
        $transactions = [];

        foreach ($items as $item) {
            $transaction = $this->createJournalEntryTransaction([
                'invoice_id' => $invoice->id,
                'Transaction_Date' => $validated['Transaction_Date'],
                'Transaction_Code' => $transactionCode,
                'chart_of_account_id' => $item['ledger_id'],
                'account_id' => $item['account_id'],
                'description' => $item['description'],
                'base_amount' => $item['base_amount'],
                'vat_amount' => $item['vat_amount'],
                'net_amount' => $item['net_amount'],
                'entry_type' => $item['entry_type'],
                'paid_in_out' => $item['paid_in_out'],
                'vat_form_label_id' => $item['vat_form_label_id'],
            ]);

            $transactions[] = $transaction;
        }

        return $transactions[0]; // Return first transaction as primary
    }
    /**
     * ✅ NEW: Extract and validate journal items
     */
    private function extractAndValidateJournalItems(Request $request)
    {
        $itemsOut = [];
        $itemsData = $request->input('items', []);

        foreach ($itemsData as $index => $itemData) {
            if (empty($itemData['description'])) {
                continue;
            }

            $debitAmount = $this->toDecimal($itemData['debit_amount'] ?? 0) ?? 0;
            $creditAmount = $this->toDecimal($itemData['credit_amount'] ?? 0) ?? 0;
            $taxRate = $this->toDecimal($itemData['tax_rate'] ?? 0) ?? 0;

            // Determine base amount and entry type
            if ($debitAmount > 0) {
                $baseAmount = $debitAmount;
                $entryType = 'Dr';
                $paidInOut = 2; // Money Out
            } else {
                $baseAmount = $creditAmount;
                $entryType = 'Cr';
                $paidInOut = 1; // Money In
            }

            // ✅ CALCULATE VAT AND NET AMOUNT
            $vatAmount = ($baseAmount * $taxRate) / 100;
            $netAmount = $baseAmount + $vatAmount;

            $ledgerId = isset($itemData['ledger_id']) && $itemData['ledger_id'] !== ''
                ? (int) $itemData['ledger_id']
                : null;

            $accountId = isset($itemData['account_id']) && $itemData['account_id'] !== ''
                ? (int) $itemData['account_id']
                : null;

            if (!$ledgerId || !$accountId) {
                throw new \Exception("Journal entry " . ($index + 1) . ": Missing ledger or account.");
            }

            $item = [
                'ledger_id' => $ledgerId,
                'account_id' => $accountId,
                'description' => trim($itemData['description']),
                'base_amount' => $baseAmount,
                'vat_rate' => $taxRate,
                'vat_amount' => $vatAmount,
                'net_amount' => $netAmount,
                'entry_type' => $entryType,
                'paid_in_out' => $paidInOut,
                'vat_form_label_id' => isset($itemData['vat_form_label_id'])
                    ? (int)$itemData['vat_form_label_id']
                    : null,
            ];

            Log::info('Journal entry processed', [
                'index' => $index,
                'base' => $baseAmount,
                'vat' => $vatAmount,
                'net' => $netAmount,
                'entry_type' => $entryType,
            ]);

            $itemsOut[] = $item;
        }

        return $itemsOut;
    }

    /**
     * ✅ NEW: Create journal entry transaction
     */
    private function createJournalEntryTransaction(array $data)
    {
        try {
            $transaction = new Transaction();

            $transaction->Transaction_Date = $data['Transaction_Date'];
            $transaction->File_ID = null;
            $transaction->Bank_Account_ID = null; // Journal entries typically don't have bank accounts
            $transaction->chart_of_account_id = $data['chart_of_account_id'];
            $transaction->invoice_id = $data['invoice_id'];
            $transaction->entry_type = $data['entry_type']; // 'Dr' or 'Cr'
            $transaction->Paid_In_Out = $data['paid_in_out']; // 1 or 2
            $transaction->Payment_Type_ID = null;
            $transaction->Account_Ref_ID = $data['account_id'] ?? null;
            $transaction->Cheque = '';
            $transaction->Amount = $data['net_amount']; // ✅ NET AMOUNT (base + VAT)
            $transaction->Description = $data['description'];
            $transaction->VAT_ID = $data['vat_form_label_id'] ?? null;
            $transaction->Transaction_Code = $data['Transaction_Code'];
            $transaction->Is_Imported = 0;
            $transaction->Created_By = auth()->id();
            $transaction->Created_On = now();
            $transaction->Is_Bill = 0;

            $transaction->save();

            Log::info('Journal transaction created', [
                'id' => $transaction->Transaction_ID,
                'amount' => $transaction->Amount,
                'vat_id' => $transaction->VAT_ID,
            ]);

            return $transaction;
        } catch (\Exception $e) {
            Log::error('Failed to create journal transaction', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);
            throw $e;
        }
    }
    /**
     * Upload multiple invoice documents
     */
    public function uploadMultipleInvoiceDocuments(Request $request)
    {
        try {
            $request->validate([
                'documents.*' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg|max:5120', // 5MB max per file
            ]);

            if (!$request->hasFile('documents')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No files uploaded'
                ], 400);
            }

            $clientId = auth()->user()->Client_ID;
            $uploadedDocuments = [];

            foreach ($request->file('documents') as $file) {
                // Generate unique filename
                $timestamp = time();
                $randomHash = bin2hex(random_bytes(8));
                $extension = $file->getClientOriginalExtension();
                $filename = "{$timestamp}_{$randomHash}.{$extension}";

                // Store in temporary location
                $tempPath = "invoices/temp/{$clientId}";
                $filePath = $file->storeAs($tempPath, $filename, 'public');

                $uploadedDocuments[] = [
                    'file_path' => $filePath,
                    'file_name' => $file->getClientOriginalName(),
                    'stored_filename' => $filename,
                    'file_type' => $extension,
                    'file_size' => $file->getSize()
                ];
            }

            return response()->json([
                'success' => true,
                'message' => count($uploadedDocuments) . ' documents uploaded successfully',
                'documents' => $uploadedDocuments
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Multiple invoice documents upload error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ NEW: Extract and validate invoice items from request
     */

    private function extractAndValidateInvoiceItems(Request $request)
    {
        $itemsOut  = [];
        $itemsData = $request->input('items', []);

        Log::info('INV.items.raw', ['items' => $itemsData]);

        foreach ($itemsData as $index => $itemData) {
            Log::info('INV.item.row', [
                'index'       => $index,
                'description' => $itemData['description'] ?? null,
                'ledger_id'   => $itemData['ledger_id'] ?? null,
                'account_ref' => $itemData['account_ref'] ?? null,
            ]);

            if (empty($itemData['description']) || empty($itemData['unit_amount'])) {
                continue;
            }

            // 1) Validate / normalize unit amount
            $unitAmount = $this->toDecimal($itemData['unit_amount']);
            if ($unitAmount === null || $unitAmount <= 0) {
                throw new \Exception("Item " . ($index + 1) . ": Unit amount must be greater than 0");
            }

            // 2) Get ledger_id from form data
            $ledgerId = isset($itemData['ledger_id']) && $itemData['ledger_id'] !== ''
                ? (int) $itemData['ledger_id']
                : null;

            if (!$ledgerId) {
                throw new \Exception("Item " . ($index + 1) . ": Missing or invalid ledger account (ledger_id).");
            }

            // ✅ 3) CRITICAL FIX: Lookup chart_of_account to get ledger_ref text
            $chartAccount = ChartOfAccount::where('id', $ledgerId)
                ->select('id', 'ledger_ref', 'account_ref')
                ->first();

            if (!$chartAccount) {
                throw new \Exception("Item " . ($index + 1) . ": Chart of Account with ID {$ledgerId} not found.");
            }

            // ✅ NOW we have the actual ledger_ref text from database
            $ledgerRefText = $chartAccount->ledger_ref;

            Log::info('INV.item.chartAccount', [
                'index' => $index,
                'ledger_id' => $ledgerId,
                'ledger_ref_from_db' => $ledgerRefText,
            ]);

            // 4) Resolve account_ref_id using the correct method
            $accountRefText = trim($itemData['account_ref'] ?? '');
            $accountRefId = $this->resolveAccountRefIdByLedgerId($ledgerId, $accountRefText);

            Log::info('INV.item.accountRef.resolved', [
                'index'          => $index,
                'ledger_id'      => $ledgerId,
                'account_ref'    => $accountRefText,
                'account_ref_id' => $accountRefId,
            ]);

            // ✅ 5) Build item array with BOTH ledger_id AND ledger_ref
            $item = [
                'ledger_id'          => $ledgerId,              // Integer ID for FK
                'ledger_ref'         => $ledgerRefText,         // ✅ Text from database
                'item_code'          => trim($itemData['item_code'] ?? ''),
                'description'        => trim($itemData['description']),
                'account_ref'        => $accountRefText,        // Text for display
                'account_ref_id'     => $accountRefId,          // Integer ID for FK
                'unit_amount'        => $unitAmount,
                'vat_rate'           => $this->toDecimal($itemData['vat_rate'] ?? 0) ?? 0,
                'vat_form_label_id'  => isset($itemData['vat_form_label_id']) ? (int)$itemData['vat_form_label_id'] : null,
                'vat_amount'         => $this->toDecimal($itemData['vat_amount'] ?? 0) ?? 0,
                'net_amount'         => $this->toDecimal($itemData['net_amount'] ?? 0) ?? 0,
                'product_image'      => $itemData['product_image'] ?? null,
            ];

            Log::info('INV.item.final', [
                'index' => $index,
                'ledger_id' => $item['ledger_id'],
                'ledger_ref' => $item['ledger_ref'],
                'account_ref_id' => $item['account_ref_id'],
            ]);

            $itemsOut[] = $item;
        }

        Log::info('INV.items.normalized', ['itemsOut' => $itemsOut]);

        return $itemsOut;
    }

    // Same helper used earlier; safe for "17", "20.40", "1,234.56"
    private function toDecimal($v)
    {
        if ($v === '' || $v === null) return null;
        $v = str_replace(',', '', (string) $v);
        return is_numeric($v) ? (float) $v : null;
    }

    // ✅ Add this helper
    private function resolveAccountRefIdByLedgerId(?int $ledgerId, ?string $accountRef): ?int
    {

        if (!$ledgerId || !$accountRef) {
            return null;
        }

        // 1) Get the ledger_ref for the given ledger row (e.g., "Deferred tax" for id=198)
        $ledgerRef = ChartOfAccount::whereKey($ledgerId)->value('ledger_ref');
        if (!$ledgerRef) {
            return null; // invalid ledgerId
        }

        $needleAcc  = mb_strtolower(trim($accountRef));
        $needleLedg = mb_strtolower(trim($ledgerRef));

        return ChartOfAccount::query()
            ->whereRaw('LOWER(TRIM(ledger_ref)) = ?', [$needleLedg])
            ->whereRaw('LOWER(TRIM(account_ref)) = ?', [$needleAcc])
            ->value('id'); // e.g., returns 200 for "Deferred tax" + "Charged to other income"
    }

    // ✅ Add this helper
    private function deriveAccountRefIdFromItems(array $items): ?int
    {
        foreach ($items as $it) {

            if (!empty($it['account_ref_id'])) {
                return (int) $it['account_ref_id'];
            }
        }
        return null;
    }

    /**
     * ✅ NEW: Get the first vat_type_id from invoice items
     */
    private function deriveVatFormLabelIdFromItems(array $items): ?int
    {
        foreach ($items as $item) {
            if (!empty($item['vat_form_label_id'])) {
                return (int) $item['vat_form_label_id'];
            }
        }
        return null;
    }


    private function effectForPaymentType(string $paymentType): string
    {
        return match ($paymentType) {
            'sales_invoice', 'purchase'         => 'increase',
            'sales_credit', 'purchase_credit'   => 'decrease',
            default                            => 'increase', // journals handled per-line
        };
    }




    private function entryTypeFromCoaAndEffect(int $coaId, string $effect): string
    {

        $nb = strtoupper(ChartOfAccount::whereKey($coaId)->value('normal_balance') ?? 'DR'); // DR|CR
        if ($effect === 'increase') {
            return $nb === 'DR' ? 'Dr' : 'Cr';
        }


        return $nb === 'DR' ? 'Cr' : 'Dr'; // decrease → opposite side
    }

    private function paidInOutFromEntryType(string $entryType): int
    {
        // matches the Excel: Credit = Money In, Debit = Money Out
        return strtoupper($entryType) === 'CR' ? Transaction::MONEY_IN : Transaction::MONEY_OUT;
    }


    /**
     * ✅ NEW: Get contextual account selection message based on payment type
     */
    private function getContextualAccountMessage(string $paymentType): string
    {
        $messages = [
            'sales_invoice' => 'Please select a customer for the sales invoice.',
            'sales_credit' => 'Please select a customer for the sales credit note.',
            'purchase' => 'Please select a supplier for the purchase invoice.',
            'purchase_credit' => 'Please select a supplier for the purchase credit note.',
            'journal' => 'Please select an account for the journal entry.',
        ];

        return $messages[$paymentType] ?? 'Please select an account for this transaction.';
    }


    private function computeInvoicePaidIOFromItems(array $items, string $paymentType): int
    {
        $effect = $this->effectForPaymentType($paymentType);

        $totCr = 0.0;
        $totDr = 0.0;
        foreach ($items as $it) {
            $entry = $this->entryTypeFromCoaAndEffect((int)$it['ledger_id'], $effect);
            $amt   = (float)($it['net_amount'] ?? $it['unit_amount'] ?? 0);
            if (strtoupper($entry) === 'CR') {
                $totCr += $amt;
            } else {
                $totDr += $amt;
            }
        }
        return $totCr > $totDr ? Transaction::MONEY_IN : ($totDr > $totCr ? Transaction::MONEY_OUT : Transaction::MONEY_NEUTRAL);
    }



    /**
     * Handle Inter Bank Client Transfer
     */
    private function handleInterBankClientTransfer(array $validated, string $transactionCode)
    {
        // Validate file exists and belongs to current user
        $file = $this->validateFile($validated['Ledger_Ref']);

        // Validate bank accounts
        $bankAccountFrom = $this->validateBankAccount($validated['Bank_Account_From_ID'], 'source');
        $bankAccountTo = $this->validateBankAccount($validated['Bank_Account_To_ID'], 'destination');

        // Ensure source and destination are different
        if ($validated['Bank_Account_From_ID'] === $validated['Bank_Account_To_ID']) {
            throw new \Exception('Source and destination bank accounts cannot be the same.');
        }

        // Generate sequential transaction codes
        $outTransactionCode = $transactionCode;  // Uses PAY000010
        $inTransactionCode = $this->generateNextSequentialCode($transactionCode);

        // Create Transaction 1: PAID OUT from source bank account
        $this->createTransaction([
            'transaction_date' => $validated['Transaction_Date'],
            'file_id' => $file->File_ID,
            'bank_account_id' => $validated['Bank_Account_From_ID'],
            'paid_in_out' => 2, // PAID OUT
            'payment_type_id' => $validated['Payment_Type_ID'],
            'account_ref_id' => $validated['Account_Ref_ID'] ?? null,
            'cheque' => $validated['Payment_Ref'] ?? '',
            'amount' => $validated['Amount'],
            'description' => $validated['Description'] . ' (Transfer Out)',
            'vat_id' => $validated['VAT_ID'] ?? null,
            'Transaction_Code' => $outTransactionCode,
        ]);

        // Create Transaction 2: PAID IN to destination bank account
        $this->createTransaction([
            'transaction_date' => $validated['Transaction_Date'],
            'file_id' => $file->File_ID,
            'bank_account_id' => $validated['Bank_Account_To_ID'],
            'paid_in_out' => 1, // PAID IN
            'payment_type_id' => $validated['Payment_Type_ID'],
            'account_ref_id' => $validated['Account_Ref_ID'] ?? null,
            'cheque' => $validated['Payment_Ref'] ?? '',
            'amount' => $validated['Amount'],
            'description' => $validated['Description'] . ' (Transfer In)',
            'vat_id' => $validated['VAT_ID'] ?? null,
            'Transaction_Code' => $inTransactionCode,
        ]);
    }

    /**
     * Handle Inter Ledger Transfer
     */
    private function handleInterLedgerTransfer(array $validated, string $transactionCode)
    {
        // Validate files exist and belong to current user
        $fileFrom = $this->validateFile($validated['Ledger_Ref_From']);
        $fileTo = $this->validateFile($validated['Ledger_Ref_To']);

        // Ensure source and destination are different
        if ($validated['Ledger_Ref_From'] === $validated['Ledger_Ref_To']) {
            throw new \Exception('Source and destination ledgers cannot be the same.');
        }

        // Generate sequential transaction codes
        $outTransactionCode = $transactionCode;  // Uses PAY000010
        $inTransactionCode = $this->generateNextSequentialCode($transactionCode);

        // Create Transaction 1: PAID OUT from source ledger
        $this->createTransaction([
            'transaction_date' => $validated['Transaction_Date'],
            'file_id' => $fileFrom->File_ID,
            'bank_account_id' => null, // No bank account for inter ledger
            'paid_in_out' => 2, // PAID OUT
            'payment_type_id' => $validated['Payment_Type_ID'],
            'account_ref_id' => $validated['Account_Ref_ID'] ?? null,
            'cheque' => $validated['Payment_Ref'] ?? '',
            'amount' => $validated['Amount'],
            'description' => $validated['Description'] . ' (Ledger Transfer Out)',
            'vat_id' => $validated['VAT_ID'] ?? null,
            'Transaction_Code' => $outTransactionCode,
        ]);

        // Create Transaction 2: PAID IN to destination ledger
        $this->createTransaction([
            'transaction_date' => $validated['Transaction_Date'],
            'file_id' => $fileTo->File_ID,
            'bank_account_id' => null, // No bank account for inter ledger
            'paid_in_out' => 1, // PAID IN
            'payment_type_id' => $validated['Payment_Type_ID'],
            'account_ref_id' => $validated['Account_Ref_ID'] ?? null,
            'cheque' => $validated['Payment_Ref'] ?? '',
            'amount' => $validated['Amount'],
            'description' => $validated['Description'] . ' (Ledger Transfer In)',
            'vat_id' => $validated['VAT_ID'] ?? null,
            'Transaction_Code' => $inTransactionCode,
        ]);
    }

    /**
     * Handle Single Transaction
     */
    private function handleSingleTransaction(array $validated, string $transactionCode, string $paymentType)
    {

        // Validate file exists and belongs to current user
        $file = $this->validateFile($validated['Ledger_Ref']);

        // Validate bank account
        $bankAccount = $this->validateBankAccount($validated['Bank_Account_ID'], 'bank account');

        // Additional validation for payment, receipt, cheque - must be client bank
        if (in_array($paymentType, ['payment', 'receipt', 'cheque'])) {

            if ($bankAccount->Bank_Type_ID !== 1) {
                // dd('store method calledssa;');

                throw new \Exception('For ' . $paymentType . ' transactions, please select a client bank account.');
            }
        }

        // Determine paid_in_out based on payment type
        $paidInOut = $this->getPaidInOutDirection($paymentType);

        // Create single transaction
        $this->createTransaction([
            'transaction_date' => $validated['Transaction_Date'],
            'file_id' => $file->File_ID,
            'bank_account_id' => $validated['Bank_Account_ID'],
            'paid_in_out' => $paidInOut,
            'payment_type_id' => $validated['Payment_Type_ID'],
            'account_ref_id' => $validated['Account_Ref_ID'] ?? null,
            'cheque' => $validated['Payment_Ref'] ?? '',
            'amount' => $validated['Amount'],
            'description' => $validated['Description'],
            'vat_id' => $validated['VAT_ID'] ?? null,
            'Transaction_Code' => $transactionCode,
        ]);
    }

    /**
     * Get paid in/out direction based on payment type
     */
    private function getPaidInOutDirection(string $paymentType): int
    {
        return match ($paymentType) {
            'payment', 'cheque'  => Transaction::MONEY_OUT,
            'receipt'           => Transaction::MONEY_IN,
            default             => Transaction::MONEY_NEUTRAL,
        };
    }

    /**
     * Validate file exists and belongs to current user
     */
    private function validateFile(string $ledgerRef)
    {
        $file = File::where('Ledger_Ref', $ledgerRef)
            ->where('Client_ID', auth()->user()->Client_ID)
            ->first();

        if (!$file) {
            throw new \Exception('No matching file found for ledger reference: ' . $ledgerRef);
        }

        return $file;
    }

    /**
     * Validate bank account exists and belongs to current user
     */
    private function validateBankAccount(int $bankAccountId, string $type = 'bank account')
    {
        $bankAccount = BankAccount::where('Bank_Account_ID', $bankAccountId)
            ->where('Client_ID', auth()->user()->Client_ID)
            ->where('Is_Deleted', 0)
            ->first();

        if (!$bankAccount) {
            throw new \Exception('Invalid ' . $type . ' selected or it does not belong to your account.');
        }

        return $bankAccount;
    }

    /**
     * Create a transaction record
     */
    private function createTransaction(array $data)
    {
        $transaction = new Transaction();
        $transaction->transaction_date = $data['transaction_date'];
        $transaction->file_id = $data['file_id'];
        $transaction->bank_account_id = $data['bank_account_id'];
        $transaction->paid_in_out = $data['paid_in_out'];
        $transaction->payment_type_id = $data['payment_type_id'];
        $transaction->account_ref_id = $data['account_ref_id'];
        $transaction->cheque = $data['cheque'];
        $transaction->amount = $data['amount'];
        $transaction->description = $data['description'];
        $transaction->vat_id = $data['vat_id'];
        $transaction->Transaction_Code = $data['Transaction_Code'];
        $transaction->is_imported = 0;
        $transaction->created_by = auth()->id();
        $transaction->created_on = now();
        $transaction->is_bill = 0;

        if (!$transaction->save()) {
            throw new \Exception('Failed to save transaction to database.');
        }

        return $transaction;
    }



    /**
     * ✅ ADD THIS NEW METHOD: Handle office transactions
     */
    private function handleOfficeTransaction(array $validated, string $transactionCode, Request $request): void
    {
        // Validate bank account
        $bankAccount = $this->validateBankAccount($validated['Bank_Account_ID'], 'office bank account');

        if ($bankAccount->Bank_Type_ID !== 2) {
            throw new \Exception('Please select an office bank account for office transactions.');
        }

        $chartOfAccount = ChartOfAccount::where('id', $validated['chart_of_account_id'])
            ->where('is_active', 1)
            ->first();

        if (!$chartOfAccount) {
            throw new \Exception('Invalid chart of account selected.');
        }

        // Resolve ledger id
        $ledgerId = isset($validated['chart_of_account_id']) ? (int)$validated['chart_of_account_id'] : null;
        if (!$ledgerId && !empty($validated['ledger_ref'])) {
            $ledgerId = ChartOfAccount::where('ledger_ref', trim($validated['ledger_ref']))->value('id');
        }

        // Get account_ref text from header
        $accountRefText = isset($validated['account_ref']) ? trim((string)$validated['account_ref']) : null;

        // ✅ USE TransactionService
        $accountRefIdForTxn = $this->transactionService->resolveAccountRefIdByLedgerId($ledgerId, $accountRefText);

        // Optional: Override from items if present
        $invoiceItems = $this->extractAndValidateInvoiceItems($request);
        if (!empty($invoiceItems)) {
            $fromItems = $this->deriveAccountRefIdFromItems($invoiceItems);
            if ($fromItems) {
                $accountRefIdForTxn = $fromItems;
            }
        }

        $netAmount = (float)$validated['Amount'];
        $vatId = $validated['VAT_ID'] ?? null;
        $vatPercentage = 0;
        $grossAmount = $netAmount;

        // Get VAT percentage
        if ($vatId) {
            $vatLabel = \App\Models\VatFormLabel::find($vatId);
            if ($vatLabel) {
                $vatPercentage = (float)$vatLabel->percentage;
            }
        }

        // Calculate gross amount
        if ($vatPercentage > 0) {
            $vatAmount = ($netAmount * $vatPercentage) / 100;
            $grossAmount = $netAmount + $vatAmount;
        }

        $paidInOut = $this->getOfficePaidInOutDirection($validated['current_payment_type']);

        $entryType = null;
        switch (strtolower($validated['current_payment_type'] ?? '')) {
            case 'payment':
            case 'cheque':
                $entryType = 'Dr';
                $paidInOut = Transaction::MONEY_OUT;
                break;
            case 'receipt':
                $entryType = 'Cr';
                $paidInOut = Transaction::MONEY_IN;
                break;
            default:
                break;
        }

        // ✅ USE TransactionService
        $this->transactionService->createOfficeTransaction([
            'Transaction_Date'     => $validated['Transaction_Date'],
            'Bank_Account_ID'      => $validated['Bank_Account_ID'],
            'chart_of_account_id'  => $validated['chart_of_account_id'],
            'Paid_In_Out'          => $paidInOut,
            'Payment_Type_ID'      => $validated['Payment_Type_ID'] ?? null,
            'Cheque'               => $validated['Payment_Ref'] ?? '',
            'Amount'               => $grossAmount,
            'Description'          => $validated['Description'] ?? '',
            'VAT_ID'               => $validated['VAT_ID'] ?? null,
            'Transaction_Code'     => $transactionCode,
            'Account_Ref_ID'       => $accountRefIdForTxn,
            'Entry_Type'           => $entryType,
        ]);
    }

    /**
     * ✅ ADD THIS NEW METHOD: Get paid in/out direction for office transactions
     */
    private function getOfficePaidInOutDirection(string $paymentType): int
    {
        return match ($paymentType) {
            'payment', 'purchase', 'office_client', 'cheque' => Transaction::MONEY_OUT,
            'receipt', 'aggregate_client', 'free_bank'      => Transaction::MONEY_IN,
            'transfer', 'journal'                          => Transaction::MONEY_NEUTRAL,
            default                                       => Transaction::MONEY_NEUTRAL,
        };
    }

    public function edit($id)
    {
        dd('yessds');
        $transaction = Transaction::find($id);

        if (!$transaction) {
            return redirect()->route('transactions.index')->with('error', 'Transaction not found.');
        }

        return view('admin.day_book.edit', compact('transaction'));
    }

    public function update(UpdateTransactionRequest $request, $id)
    {
        $validated = $request->validated();

        DB::beginTransaction();
        try {
            $transaction = Transaction::find($id);

            if (!$transaction) {
                return redirect()->route('transactions.index')->with('error', 'Transaction not found.');
            }

            // Step 1: Validate Bank Account
            $bankAccount = BankAccount::find($validated['Bank_Account_ID']);
            if (!$bankAccount) {
                return redirect()->route('transactions.index')->with('error', 'Invalid Bank Account ID.');
            }

            // Step 2: Validate File
            $file = File::where('Ledger_Ref', $validated['Ledger_Ref'])->first();
            if (!$file) {
                return redirect()->route('transactions.index')->with('error', 'No matching file found.');
            }

            // Step 3: Update transaction details
            $transaction->transaction_date = $validated['Transaction_Date'];
            $transaction->file_id = $file->File_ID;
            $transaction->bank_account_id = $validated['Bank_Account_ID'];
            $transaction->paid_in_out = $validated['Paid_In_Out'];
            $transaction->payment_type_id = $validated['Payment_Type_ID'];
            $transaction->cheque = $validated['Cheque'] ?? null;
            $transaction->amount = $validated['Amount'];
            $transaction->description = $validated['Description'] ?? '';
            $transaction->modified_by = auth()->id();
            $transaction->modified_on = now();
            $transaction->account_ref_id = $validated['Account_Ref_ID'];

            // Step 4: Adjust VAT if necessary
            if (!in_array($transaction->account_ref_id, [2, 93]) && isset($validated['VAT_ID'])) {
                $transaction->vat_id = $validated['VAT_ID'];
            }

            // Step 5: Adjust Account_Ref_ID for specific cases
            if (in_array($transaction->account_ref_id, [2, 93])) {
                $transaction->account_ref_id = ($transaction->account_ref_id == 2) ? 101 : 99;
            }

            // Step 6: Save updated transaction
            $transaction->save();

            // Step 7: Handle second transaction for Account_Ref_ID 2 or 93
            if (in_array($validated['Account_Ref_ID'], [2, 93])) {
                $secondTransaction = Transaction::where([
                    'file_id' => $transaction->file_id,
                    'is_bill' => 1
                ])->first();

                if ($secondTransaction) {
                    $secondTransaction->paid_in_out = 2;
                    $secondTransaction->is_bill = 1;
                    $secondTransaction->payment_type_id = 28;
                    $secondTransaction->account_ref_id = ($validated['Account_Ref_ID'] == 2) ? 99 : 101;
                    $secondTransaction->vat_id = $validated['VAT_ID'] ?? null;
                    $secondTransaction->modified_on = now();
                    $secondTransaction->save();
                }
            }

            // Step 8: Handle transactions when Account_Ref_ID is 90 or 91
            if (in_array($transaction->account_ref_id, [90, 91])) {
                $transaction->bank_account_id = 23;
                $transaction->paid_in_out = 1;
                $transaction->payment_type_id = 15;
                $transaction->vat_id = null;
                $originalAccountRefId = $transaction->account_ref_id;

                if ($originalAccountRefId == 90) {
                    // First extra bill
                    $extraBill1 = Transaction::where([
                        'file_id' => $transaction->file_id,
                        'account_ref_id' => 99,
                        'is_bill' => 1
                    ])->first();

                    if ($extraBill1) {
                        $extraBill1->vat_id = $validated['VAT_ID'];
                        $extraBill1->modified_on = now();
                        $extraBill1->save();
                    }

                    // Second extra bill
                    $extraBill2 = Transaction::where([
                        'file_id' => $transaction->file_id,
                        'account_ref_id' => 86,
                        'paid_in_out' => 2
                    ])->first();

                    if ($extraBill2) {
                        $extraBill2->bank_account_id = $validated['Bank_Account_ID'];
                        $extraBill2->modified_on = now();
                        $extraBill2->save();
                    }
                }

                if ($originalAccountRefId == 90) {
                    $transaction->account_ref_id = 86;
                } elseif ($originalAccountRefId == 91) {
                    $transaction->account_ref_id = 87;
                }
            }

            // Step 9: Save the modified transaction
            $transaction->save();

            DB::commit();
            return redirect()->route('transactions.index')->with('success', 'Transaction updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('transactions.index')->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }




    public function getAccountDetails($id)
    {
        $bankAccount = BankAccount::with('bankAccountType')->find($id);

        if (!$bankAccount) {
            return response()->json(['error' => 'Bank Account not found'], 404);
        }

        return response()->json($bankAccount);
    }


    public function getPaymentTypes(Request $request)
    {

        $validated = $request->validate([
            'bankAccountTypeId' => 'required|integer|exists:bankaccounttype,Bank_Type_ID',
            'paidInOut' => 'required|in:1,2', // 1 = Paid In, 2 = Paid Out
        ]);

        // Fetch payment types for given Bank_Type_ID and Paid_In_Out
        $paymentTypes = PaymentType::where('Bank_Type_ID', $validated['bankAccountTypeId'])
            ->where('Paid_In_Out', $validated['paidInOut'])
            ->get(['Payment_Type_ID', 'Payment_Type_Name']); // Return only required fields

        if ($paymentTypes->isEmpty()) {
            return response()->json(['message' => 'No payment types available'], 404);
        }

        return response()->json($paymentTypes);
    }


    public function getAccountRef(Request $request)
    {

        $bankAccountId = $request->input('bankTypeId');

        $pinout = $request->input('pinout');

        // $pinoutValue = ($pinout == 1) ? 'Paid In' : 'Paid Out';

        $accountRefs = AccountRef::where('Bank_Type_ID', $bankAccountId)
            ->where('Paid_In_Out', $pinout)
            ->get();
        // dd($accountRefs);

        return response()->json($accountRefs);
    }


    public function getVatTypes(Request $request)
    {
        $accountRefId = $request->input('Account_Ref_ID');

        // Query VAT types based on Account_Ref_ID
        $vatTypes = DB::table('vataccref')
            ->join('vattype', 'vataccref.VAT_ID', '=', 'vattype.VAT_ID')
            ->where('vataccref.Account_Ref_ID', $accountRefId)
            ->select('vattype.VAT_ID', 'vattype.VAT_Name')
            ->get();

        return response()->json($vatTypes);
    }

    public function import($id)
    {
        try {

            $userRole = auth()->user()->User_Role;

            if (!in_array($userRole, [1, 2])) {
                return redirect()->route('transactions.index')->with('error', 'You are not authorized to import transactions.');
            }

            // Find the transaction by ID
            $transaction = Transaction::find($id);
            if (!$transaction) {
                return redirect()->route('transactions.index')->with('error', 'Transaction not found.');
            }

            // Check if the transaction is already imported
            if ($transaction->Is_Imported == 1) {
                return redirect()->route('transactions.index')->with('error', 'Transaction is already imported.');
            }

            // Update the transaction's imported status to 1
            $transaction->update(['Is_Imported' => 1]);

            return redirect()->route('transactions.index')->with('success', 'Transaction imported successfully.');
        } catch (\Exception $e) {
            return redirect()->route('transactions.index')->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }


    public function storeMultiple(StoreTransactionRequest $request)
    {
        $validated = $request->validated();

        // Begin a transaction to ensure all database operations are rolled back in case of failure
        DB::beginTransaction();
        try {
            $errors = [];

            // Loop through each transaction data
            foreach ($validated['transactions'] as $transactionData) {
                // Step 1: Validate Bank Account
                $bankAccount = BankAccount::find($transactionData['Bank_Account_ID']);
                if (!$bankAccount) {
                    $errors[] = 'Invalid Bank Account ID for Ledger Ref: ' . $transactionData['Ledger_Ref'];
                    continue; // Skip to the next transaction
                }

                // Step 2: Validate File
                $file = File::where('Ledger_Ref', $transactionData['Ledger_Ref'])->first();
                if (!$file) {
                    $errors[] = 'No matching file found for Ledger Ref: ' . $transactionData['Ledger_Ref'];
                    continue; // Skip to the next transaction
                }

                // Step 3: Create the initial transaction
                $transaction = new Transaction();
                $transaction->transaction_date = $transactionData['Transaction_Date'];
                $transaction->file_id = $file->File_ID;
                $transaction->bank_account_id = $transactionData['Bank_Account_ID'];
                $transaction->paid_in_out = $transactionData['Paid_In_Out'];
                $transaction->payment_type_id = $transactionData['Payment_Type_ID'];
                $transaction->cheque = $transactionData['Cheque'] ?? null;
                $transaction->amount = $transactionData['Amount'];
                $transaction->description = $transactionData['Description'] ?? '';
                $transaction->is_imported = 0;
                $transaction->created_by = auth()->id();
                $transaction->created_on = now();
                $transaction->account_ref_id = $transactionData['Account_Ref_ID'];
                $transaction->is_bill = 0;

                // Step 4: Adjust VAT if necessary
                if (!in_array($transaction->account_ref_id, [2, 93]) && isset($transactionData['VAT_ID'])) {
                    $transaction->vat_id = $transactionData['VAT_ID'];
                }

                // Step 5: Adjust Account_Ref_ID for specific cases
                if (in_array($transaction->account_ref_id, [2, 93])) {
                    $transaction->account_ref_id = ($transaction->account_ref_id == 2) ? 101 : 99;
                }

                // Step 6: Save the initial transaction
                $transaction->save();

                // Step 7: Handle second transaction for Account_Ref_ID 2 or 93
                if (in_array($transactionData['Account_Ref_ID'], [2, 93])) {
                    $secondTransaction = $transaction->replicate();
                    $secondTransaction->paid_in_out = 2;
                    $secondTransaction->is_bill = 1;
                    $secondTransaction->payment_type_id = 28;
                    $secondTransaction->account_ref_id = ($transactionData['Account_Ref_ID'] == 2) ? 99 : 101;
                    $secondTransaction->created_on = now();
                    $secondTransaction->vat_id = $transactionData['VAT_ID'] ?? null;
                    $secondTransaction->save();
                }

                // Step 8: Handle transactions when Account_Ref_ID is 90 or 91
                if (in_array($transaction->account_ref_id, [90, 91])) {
                    $transaction->bank_account_id = 23;
                    $transaction->paid_in_out = 1;
                    $transaction->payment_type_id = 15;
                    $transaction->vat_id = null;
                    $originalAccountRefId = $transaction->account_ref_id;

                    if ($originalAccountRefId == 90) {
                        $extraBill1 = $transaction->replicate();
                        $extraBill1->account_ref_id = 99;
                        $extraBill1->paid_in_out = 2;
                        $extraBill1->is_bill = 1;
                        $extraBill1->payment_type_id = 28;
                        $extraBill1->vat_id = $transactionData['VAT_ID'];
                        $extraBill1->created_on = now();
                        $extraBill1->save();

                        $extraBill2 = $transaction->replicate();
                        $extraBill2->bank_account_id = $transactionData['Bank_Account_ID'];
                        $extraBill2->account_ref_id = 86;
                        $extraBill2->paid_in_out = 2;
                        $extraBill2->payment_type_id = 19;
                        $extraBill2->vat_id = null;
                        $extraBill2->created_on = now();
                        $extraBill2->save();
                    }

                    if ($originalAccountRefId == 90) {
                        $transaction->account_ref_id = 86;
                    } elseif ($originalAccountRefId == 91) {
                        $transaction->account_ref_id = 87;
                    }
                }

                // Step 9: Save the modified transaction
                $transaction->save();
            }

            // If there were errors, rollback and return error messages
            if (!empty($errors)) {
                DB::rollBack();
                return redirect()->route('transactions.index')->with('error', implode(' | ', $errors));
            }

            // Commit the transaction after all records are inserted
            DB::commit();
            return redirect()->route('transactions.index')->with('success', 'Transactions added successfully.');
        } catch (\Exception $e) {
            // Rollback in case of error
            DB::rollBack();
            return redirect()->route('transactions.index')->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }


    /**
     * Get banks based on payment type selection
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getBanksByPaymentType(Request $request): JsonResponse
    {
        try {
            // Get the payment type code from request
            $paymentTypeCode = $request->input('payment_type_code');

            // First, get the payment type to find the bank_type_id
            $paymentType = PaymentType::where('Payment_Type_Code', $paymentTypeCode)->first();

            if (!$paymentType) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment type not found'
                ], 404);
            }

            // Get all banks with the matching bank_type_id
            $banks = BankAccount::where('Bank_Type_ID', $paymentType->Bank_Type_ID)
                ->select('Bank_Account_ID', 'Bank_Name', 'Account_Name', 'Account_No', 'Sort_Code')
                ->get();

            return response()->json([
                'success' => true,
                'banks' => $banks,
                'payment_type' => $paymentType->Payment_Type_Name
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching banks: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all payment types for frontend
     *
     * @return JsonResponse
     */
    public function getPaymentTypesbutton(): JsonResponse
    {
        try {
            $paymentTypes = PaymentType::select('Payment_Type_ID', 'Payment_Type_Code', 'Payment_Type_Name', 'Bank_Type_ID')
                ->get();

            return response()->json([
                'success' => true,
                'payment_types' => $paymentTypes
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching payment types: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Apply template to invoice data and generate PDF
     */
    public function downloadCustomizedPdf(Request $request)
    {
        $validated = $request->all();
        $clientId = auth()->user()->Client_ID;
        $client = Client::where('Client_ID', $clientId)->first();

        // Get template if specified
        $templateId = $request->get('template_id');
        $template = null;

        if ($templateId) {
            $template = InvoiceTemplate::with(['elements', 'tableSettings'])
                ->where('id', $templateId)
                ->where('client_id', $clientId)
                ->first();
        } else {
            // Get default template
            $template = InvoiceTemplate::getDefault($clientId);
        }

        // Choose the appropriate view based on whether template exists
        $viewName = $template ? 'admin.pdf.customized_tax_invoice_pdf' : 'admin.pdf.tax_invoice_pdf';

        $pdf = Pdf::loadView($viewName, compact('validated', 'client', 'template'))
            ->setPaper('A4', 'portrait')
            ->setOptions([
                'defaultFont' => 'Arial',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'dpi' => 150,
                'defaultPaperSize' => 'A4'
            ]);

        $filename = 'invoice_' . ($validated['invoice_no'] ?? 'preview') . '_' . date('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }


    /**
     * ✅ CORRECTED: Save template configuration
     */
    public function saveTemplate(Request $request)
    {
        $request->validate([
            'template_name' => 'required|string|max:255',
            'template_description' => 'nullable|string|max:500',
            'elements' => 'required|array',
            'table_settings' => 'required|array',
            'is_default' => 'boolean'
        ]);

        try {
            DB::beginTransaction();

            $clientId = auth()->user()->Client_ID;

            // Create or update template
            $template = InvoiceTemplate::updateOrCreate(
                [
                    'client_id' => $clientId,
                    'name' => $request->template_name
                ],
                [
                    'description' => $request->template_description,
                    'template_data' => [
                        'elements' => $request->elements,
                        'table_settings' => $request->table_settings,
                        'global_styles' => $request->global_styles ?? []
                    ],
                    'created_by' => auth()->id()
                ]
            );

            // Set as default if requested
            if ($request->is_default) {
                $template->setAsDefault();
            }

            // ✅ FIXED: Save individual elements with proper data handling
            $this->saveTemplateElements($template, $request->elements);

            // ✅ FIXED: Save table settings with proper data handling
            $this->saveTableSettings($template, $request->table_settings);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Template saved successfully',
                'template' => $template
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Template save error: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to save template: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * ✅ FIXED: Save template elements with proper error handling
     */
    private function saveTemplateElements($template, $elements)
    {
        try {
            // Delete existing elements
            TemplateElement::where('template_id', $template->id)->delete();

            if (!is_array($elements)) {
                Log::warning('Elements is not an array', ['elements' => $elements]);
                return;
            }

            foreach ($elements as $elementData) {
                if (!is_array($elementData)) {
                    Log::warning('Element data is not an array', ['elementData' => $elementData]);
                    continue;
                }

                $elementRecord = [
                    'template_id' => $template->id,
                    'element_type' => $elementData['type'] ?? 'text',
                    'element_key' => $elementData['key'] ?? '',
                    'position_x' => $this->safeNumericValue($elementData['x'] ?? 0),
                    'position_y' => $this->safeNumericValue($elementData['y'] ?? 0),
                    'width' => $this->safeNumericValue($elementData['width'] ?? null),
                    'height' => $this->safeNumericValue($elementData['height'] ?? null),
                    'font_family' => $this->safeFontFamily($elementData['fontFamily'] ?? null),
                    'font_size' => $this->safeNumericValue($elementData['fontSize'] ?? null),
                    'font_weight' => $elementData['fontWeight'] ?? null,
                    'color' => $elementData['color'] ?? null,
                    'background_color' => $elementData['backgroundColor'] ?? null,
                    'is_visible' => $elementData['visible'] ?? true,
                    'custom_css' => $elementData['customCss'] ?? null,
                    'order_index' => $this->safeNumericValue($elementData['order'] ?? 0)
                ];

                TemplateElement::create($elementRecord);
            }
        } catch (\Exception $e) {
            Log::error('Error saving template elements: ' . $e->getMessage(), [
                'template_id' => $template->id,
                'elements' => $elements
            ]);
            throw $e;
        }
    }

    /**
     * ✅ FIXED: Save table settings with proper error handling
     */
    private function saveTableSettings($template, $tableSettings)
    {
        try {
            // Delete existing table settings
            TemplateTableSetting::where('template_id', $template->id)->delete();

            if (!is_array($tableSettings)) {
                Log::warning('Table settings is not an array', ['tableSettings' => $tableSettings]);
                return;
            }

            foreach ($tableSettings as $tableName => $columns) {
                if (!is_array($columns)) {
                    Log::warning('Table columns is not an array', ['tableName' => $tableName, 'columns' => $columns]);
                    continue;
                }

                foreach ($columns as $columnData) {
                    if (!is_array($columnData)) {
                        Log::warning('Column data is not an array', ['columnData' => $columnData]);
                        continue;
                    }

                    $settingRecord = [
                        'template_id' => $template->id,
                        'table_name' => $tableName,
                        'column_name' => $columnData['name'] ?? '',
                        'column_width' => $this->safeNumericValue($columnData['width'] ?? null),
                        'is_visible' => $columnData['visible'] ?? true,
                        'order_index' => $this->safeNumericValue($columnData['order'] ?? 0),
                        'header_text' => $columnData['headerText'] ?? null,
                        'alignment' => $columnData['alignment'] ?? 'left'
                    ];

                    TemplateTableSetting::create($settingRecord);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error saving table settings: ' . $e->getMessage(), [
                'template_id' => $template->id,
                'tableSettings' => $tableSettings
            ]);
            throw $e;
        }
    }



    /**
     * ✅ NEW: Safe font family cleaning
     */
    private function safeFontFamily($fontFamily)
    {
        if (!$fontFamily) {
            return null;
        }

        // Remove quotes and extra whitespace
        return trim(str_replace(['"', "'"], '', $fontFamily));
    }


    /**
     * ✅ NEW: Safe numeric value conversion
     */
    private function safeNumericValue($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        if (is_string($value)) {
            // Remove units like px, em, rem, %, etc.
            $cleaned = preg_replace('/[^\d.-]/', '', $value);
            if (is_numeric($cleaned)) {
                return (int) $cleaned;
            }
        }

        return null;
    }

    /**
     * ✅ FIXED: Load template configuration
     */
    public function loadTemplate($templateId)
    {
        try {
            $clientId = auth()->user()->Client_ID;

            $template = InvoiceTemplate::with(['elements', 'tableSettings'])
                ->where('id', $templateId)
                ->where('client_id', $clientId)
                ->first();

            if (!$template) {
                return response()->json([
                    'success' => false,
                    'message' => 'Template not found'
                ], 404);
            }

            // ✅ FIXED: Format template data for frontend consumption
            $formattedTemplate = [
                'id' => $template->id,
                'name' => $template->name,
                'description' => $template->description,
                'is_default' => $template->is_default,
                'logo_path' => $template->logo_path,
                'template_data' => [
                    'elements' => $this->formatElementsForFrontend($template->elements),
                    'table_settings' => $this->formatTableSettingsForFrontend($template->tableSettings),
                    'global_styles' => $template->template_data['global_styles'] ?? []
                ]
            ];

            return response()->json([
                'success' => true,
                'template' => $formattedTemplate
            ]);
        } catch (\Exception $e) {
            Log::error('Template load error: ' . $e->getMessage(), [
                'template_id' => $templateId
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load template: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ NEW: Format elements for frontend
     */
    private function formatElementsForFrontend($elements)
    {
        $formatted = [];

        foreach ($elements as $element) {
            $formatted[$element->element_key] = [
                'type' => $element->element_type,
                'key' => $element->element_key,
                'position' => [
                    'x' => $element->position_x,
                    'y' => $element->position_y
                ],
                'size' => [
                    'width' => $element->width,
                    'height' => $element->height
                ],
                'styles' => [
                    'fontFamily' => $element->font_family,
                    'fontSize' => $element->font_size,
                    'fontWeight' => $element->font_weight,
                    'color' => $element->color,
                    'backgroundColor' => $element->background_color
                ],
                'visible' => $element->is_visible,
                'customCss' => $element->custom_css,
                'order' => $element->order_index
            ];
        }

        return $formatted;
    }

    /**
     * ✅ NEW: Format table settings for frontend
     */
    private function formatTableSettingsForFrontend($tableSettings)
    {
        $formatted = [];

        foreach ($tableSettings as $setting) {
            if (!isset($formatted[$setting->table_name])) {
                $formatted[$setting->table_name] = [];
            }

            $formatted[$setting->table_name][] = [
                'name' => $setting->column_name,
                'width' => $setting->column_width,
                'visible' => $setting->is_visible,
                'order' => $setting->order_index,
                'headerText' => $setting->header_text,
                'alignment' => $setting->alignment
            ];
        }

        return $formatted;
    }


    /**
     * Upload logo for template
     */
    public function uploadLogo(Request $request)
    {
        $request->validate([
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'template_id' => 'nullable|exists:invoice_templates,id'
        ]);

        try {
            $clientId = auth()->user()->Client_ID;

            if ($request->hasFile('logo')) {
                $file = $request->file('logo');
                $filename = 'logo_' . $clientId . '_' . time() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('invoice_logos', $filename, 'public');

                // If template_id provided, update that template
                if ($request->template_id) {
                    $template = InvoiceTemplate::where('id', $request->template_id)
                        ->where('client_id', $clientId)
                        ->first();

                    if ($template) {
                        // Delete old logo if exists
                        if ($template->logo_path) {
                            Storage::disk('public')->delete($template->logo_path);
                        }

                        $template->update(['logo_path' => $path]);
                    }
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Logo uploaded successfully',
                    'logo_path' => $path,
                    'logo_url' => Storage::url($path)
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'No file uploaded'
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload logo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all templates for current client
     */
    public function getTemplates()
    {
        try {
            $clientId = auth()->user()->Client_ID;

            $templates = InvoiceTemplate::where('client_id', $clientId)
                ->select('id', 'name', 'description', 'is_default', 'created_at')
                ->orderBy('is_default', 'desc')
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'templates' => $templates
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch templates: ' . $e->getMessage()
            ], 500);
        }
    }



    /**
     * Handle AJAX request for template switching
     */
    public function previewAjax(Request $request)
    {
        try {
            $validated = $request->input('invoice_data');
            $templateId = $request->input('template_id');
            $clientId = auth()->user()->Client_ID;
            $client = Client::where('Client_ID', $clientId)->first();

            $template = null;
            if ($templateId) {
                $template = InvoiceTemplate::with(['elements', 'tableSettings'])
                    ->where('id', $templateId)
                    ->where('client_id', $clientId)
                    ->first();
            } else {
                // Get default template if no specific template selected
                $defaultTemplate = InvoiceTemplate::getDefault($clientId);
                $template = $defaultTemplate;
            }

            // Get all available templates for the dropdown
            $templates = InvoiceTemplate::where('client_id', $clientId)
                ->orderBy('is_default', 'desc')
                ->orderBy('name')
                ->get();

            // Render only the invoice preview content
            $html = view('admin.day_book.preview_content', compact(
                'validated',
                'client',
                'template',
                'templates'
            ))->render();

            return response()->json([
                'success' => true,
                'html' => $html
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load template: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete template
     */
    public function deleteTemplate($templateId)
    {
        try {
            $clientId = auth()->user()->Client_ID;

            $template = InvoiceTemplate::where('id', $templateId)
                ->where('client_id', $clientId)
                ->first();

            if (!$template) {
                return response()->json([
                    'success' => false,
                    'message' => 'Template not found'
                ], 404);
            }

            // Don't allow deleting default template if it's the only one
            if ($template->is_default) {
                $otherTemplates = InvoiceTemplate::where('client_id', $clientId)
                    ->where('id', '!=', $templateId)
                    ->count();

                if ($otherTemplates === 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot delete the only template'
                    ], 400);
                }
            }

            // Delete logo file if exists
            if ($template->logo_path) {
                Storage::disk('public')->delete($template->logo_path);
            }

            $template->delete();

            return response()->json([
                'success' => true,
                'message' => 'Template deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete template: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Set template as default
     */
    public function setDefaultTemplate($templateId)
    {
        try {
            $clientId = auth()->user()->Client_ID;

            $template = InvoiceTemplate::where('id', $templateId)
                ->where('client_id', $clientId)
                ->first();

            if (!$template) {
                return response()->json([
                    'success' => false,
                    'message' => 'Template not found'
                ], 404);
            }

            $template->setAsDefault();

            return response()->json([
                'success' => true,
                'message' => 'Template set as default successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to set default template: ' . $e->getMessage()
            ], 500);
        }
    }


    private function sendInvoiceEmail($transaction, $validated)
    {
        try {
            $clientId = auth()->user()->Client_ID;
            $client = Client::where('Client_ID', $clientId)->first();

            if (!$client) {
                Log::error('Client not found for email', ['client_id' => $clientId]);
                return false;
            }

            // Get invoice
            $invoice = Invoice::with(['items', 'customerFile'])->find($transaction->invoice_id);

            if (!$invoice) {
                Log::error('Invoice not found for email', ['transaction_id' => $transaction->Transaction_ID]);
                return false;
            }

            // Get customer file (account)
            $customerFile = File::where('File_ID', $invoice->customer)->first();

            // ✅ FIX: Get customer email - handle empty strings AND null
            $customerEmail = $customerFile->Email ?? $customerFile->Email_Address ?? null;

            // ✅ Check if email is empty (not just null)
            if (empty($customerEmail) || trim($customerEmail) === '') {
                $customerEmail = 'energysaviour10@gmail.com'; // Fallback
                Log::warning('Using fallback email - customer email is empty', [
                    'invoice_id' => $invoice->id,
                    'customer_id' => $invoice->customer,
                    'fallback_email' => $customerEmail
                ]);
            }

            Log::info('Sending invoice email', [
                'invoice_id' => $invoice->id,
                'customer_email' => $customerEmail,
                'using_fallback' => ($customerEmail === 'energysaviour10@gmail.com')
            ]);

            // ✅ Validate email before sending
            if (!filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
                throw new \Exception("Invalid email address: {$customerEmail}");
            }

            // Generate PDF
            $pdfPath = $this->generateInvoicePDF($invoice, $client, $customerFile, $validated);


            // Verify PDF was created
            if (!file_exists($pdfPath)) {
                throw new \Exception('PDF generation failed - file not created at: ' . $pdfPath);
            }

            Log::info('PDF generated successfully', [
                'pdf_path' => $pdfPath,
                'pdf_size' => filesize($pdfPath) . ' bytes'
            ]);

            // Send email
            \Mail::to($customerEmail)->send(new \App\Mail\InvoiceEmail(
                $invoice,
                $client,
                $customerFile,
                $pdfPath
            ));

            // Clean up temporary PDF file
            if (file_exists($pdfPath)) {
                unlink($pdfPath);
                Log::info('Temporary PDF deleted', ['path' => $pdfPath]);
            }

            Log::info('✅ Invoice email sent successfully!', [
                'invoice_id' => $invoice->id,
                'customer_email' => $customerEmail
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('❌ Failed to send invoice email', [
                'error' => $e->getMessage(),
                'transaction_id' => $transaction->Transaction_ID ?? null,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            throw $e;
        }
    }
    /**
     * ✅ NEW: Generate invoice PDF for email attachment
     */
    private function generateInvoicePDF($invoice, $client, $customerFile, $validated)
    {
        try {
            // Get template (if any)
            $template = null;
            if (isset($validated['template_id']) && $validated['template_id']) {
                $template = \App\Models\InvoiceTemplate::find($validated['template_id']);
            }

            // Get bank account
            $bankAccount = \App\Models\BankAccount::where('Client_ID', $client->Client_ID)->first();

            // ✅ Prepare data for PDF - same as preview
            $pdfData = [
                'validated' => $validated,
                'client' => $client,
                'template' => $template,
                'bankAccount' => $bankAccount,
                'invoice' => $invoice,
                'customerFile' => $customerFile,
                'invoiceNo' => $invoice->invoice_no,
                'invoiceRef' => $validated['invoice_ref'] ?? 'N/A',
                'invoiceDate' => isset($validated['Transaction_Date'])
                    ? \Carbon\Carbon::parse($validated['Transaction_Date'])->format('d/m/Y')
                    : date('d/m/Y'),
                'dueDate' => isset($validated['Inv_Due_Date'])
                    ? \Carbon\Carbon::parse($validated['Inv_Due_Date'])->format('d/m/Y')
                    : date('d/m/Y', strtotime('+30 days')),
                'items' => $validated['items'] ?? [],
                'netAmount' => number_format(floatval($validated['invoice_net_amount'] ?? 0), 2),
                'vatAmount' => number_format(floatval($validated['invoice_vat_amount'] ?? 0), 2),
                'totalAmount' => number_format(floatval($validated['invoice_total_amount'] ?? 0), 2),
            ];

            // ✅ Use the PREVIEW template for PDF (with full design)
            $viewName = 'admin.pdf.tax_invoice_pdf';

            // Create temporary directory if it doesn't exist
            $tempDir = storage_path('app/temp');
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            // Generate PDF filename
            $filename = 'invoice_' . $invoice->invoice_no . '_' . time() . '.pdf';
            $pdfPath = $tempDir . '/' . $filename;

            // ✅ Generate PDF with full design
            $pdf = Pdf::loadView($viewName, $pdfData)
                ->setPaper('A4', 'portrait')
                ->setOption('enable-local-file-access', true);

            $pdf->save($pdfPath);

            return $pdfPath;
        } catch (\Exception $e) {
            Log::error('PDF generation failed', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            throw $e;
        }
    }


    /**
     * ✅ NEW: Get suppliers dropdown data for Purchase transactions
     */
    public function getSuppliersDropdown()
    {

        try {
            $userId = auth()->id();

            // Get active suppliers for current user
            $suppliers = Supplier::where('user_id', $userId)
                ->select(
                    'id',
                    'contact_name',
                    'account_number',
                    'first_name',
                    'last_name',
                    'email',
                    'phone'
                )
                ->orderBy('contact_name')
                ->get();

            // Format for dropdown
            $formattedSuppliers = $suppliers->map(function ($supplier) {
                return [
                    'id' => $supplier->id,
                    'display_name' => $supplier->contact_name ?: trim($supplier->first_name . ' ' . $supplier->last_name),
                    'account_number' => $supplier->account_number,
                    'email' => $supplier->email,
                    'phone' => $supplier->phone,
                ];
            });

            return response()->json([
                'success' => true,
                'suppliers' => $formattedSuppliers
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to load suppliers dropdown', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load suppliers',
                'suppliers' => []
            ], 500);
        }
    }
}
