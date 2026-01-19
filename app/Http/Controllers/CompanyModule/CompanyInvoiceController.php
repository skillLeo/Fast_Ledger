<?php

namespace App\Http\Controllers\CompanyModule;

use App\Models\Invoice;
use App\Models\BankAccount;
use App\Models\Transaction;
use App\Models\DraftInvoice;
use App\Models\VatFormLabel;
use Illuminate\Http\Request;
use App\Models\ChartOfAccount;
use App\Services\InvoiceService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\TransactionService;
use App\Models\CompanyModule\Customer;
use App\Services\Concerns\HasCustomerContext;
use App\Http\Controllers\BaseInvoiceController;

class CompanyInvoiceController extends BaseInvoiceController
{
    use HasCustomerContext;

    protected InvoiceService $invoiceService;
    protected TransactionService $transactionService;

    public function __construct(
        InvoiceService $invoiceService,
        TransactionService $transactionService
    ) {
        $this->invoiceService = $invoiceService;
        $this->transactionService = $transactionService;
    }

    protected function getCurrentCompany()
    {
        $companyId = session('current_company_id');

        if (!$companyId) {
            throw new \Exception('No company selected. Please select a company first.');
        }

        return \App\Models\CompanyModule\Company::findOrFail($companyId);
    }

    /**
     * ✅ FIXED: Return correct model based on payment type
     * - Purchase → Supplier::class
     * - Sales → Customer::class (Company Module)
     */
    protected function getCustomerModelClass(?string $paymentType = null): string
    {
        // ✅ For Purchase transactions → Always use Supplier
        if ($this->isPurchaseType($paymentType)) {
            return \App\Models\Supplier::class;
        }

        // ✅ For Sales transactions in Company Module → Use Customer
        return Customer::class;
    }

    /**
     * ✅ FIXED: Return correct ID field
     */
    protected function getCustomerIdField(?string $paymentType = null): string
    {
        // ✅ Supplier and Customer both use 'id'
        return 'id';
    }

    /**
     * ✅ FIXED: Validate customer OR supplier based on payment type
     */
    protected function validateCustomer(int $customerId, ?string $paymentType = null)
    {
        // ✅ Use parameter if provided, fallback to request
        if (!$paymentType) {
            $paymentType = request()->input('current_payment_type');
        }

        $isPurchaseType = in_array($paymentType, ['purchase', 'purchase_credit']);

        if ($isPurchaseType) {
            // ✅ Purchase types use Supplier model
            $supplier = \App\Models\Supplier::where('id', $customerId)->first();

            if (!$supplier) {
                throw new \Exception('Supplier not found');
            }

            Log::info('✅ Supplier validated', [
                'supplier_id' => $customerId,
                'supplier_name' => $supplier->contact_name,
                'payment_type' => $paymentType
            ]);

            return $supplier;
        } else {
            // ✅ Sales types use Customer model
            $customer = Customer::where('id', $customerId)->first();

            if (!$customer) {
                throw new \Exception('Customer not found');
            }

            Log::info('✅ Customer validated', [
                'customer_id' => $customerId,
                'customer_name' => $customer->Legal_Name_Company_Name,
                'payment_type' => $paymentType
            ]);

            return $customer;
        }
    }

    // ✅ CREATE FORM
    public function create(Request $request)
    {
        $type = 'office';
        $paymentType = $request->get('payment_type', 'sales_invoice');
        $allowedTypes = ['sales_invoice', 'sales_credit', 'purchase', 'purchase_credit'];

        if (!in_array($paymentType, $allowedTypes)) {
            $paymentType = 'sales_invoice';
        }

        $companyId = session('current_company_id');

        if (!$companyId) {
            return redirect()->route('company.select')
                ->with('error', 'Please select a company first.');
        }

        $customers = Customer::where('Company_ID', $companyId)
            ->select('id', 'Legal_Name_Company_Name', 'Tax_ID_Number', 'Email')
            ->orderBy('Legal_Name_Company_Name')
            ->get();

        $bankAccounts = BankAccount::with('bankAccountType')
            ->where('Client_ID', $companyId)
            ->where('Bank_Type_ID', 2)
            ->where('Is_Deleted', 0)
            ->get();

        $vatTypes = VatFormLabel::orderBy('display_name')->get();

        // ✅ NEW: Load draft data if editing
        $editData = $this->loadDraftForEdit($request);

        if ($editData && isset($editData['invoice_no'])) {
            $autoCode = $editData['invoice_no'];
            Log::info('📝 Company Module: Using existing invoice number for edit', [
                'auto_code' => $autoCode,
                'invoice_id' => $editData['invoice']->id
            ]);
        } else {
            $autoCode = $this->generateAutoCode($paymentType, 'office');
            Log::info('🆕 Company Module: Generated new invoice number', [
                'auto_code' => $autoCode
            ]);
        }

        return view('company-module.invoices.create', compact(
            'customers',
            'bankAccounts',
            'vatTypes',
            'paymentType',
            'autoCode',
            'type',
            'editData'
        ));
    }

    /**
     * Load draft invoice for editing
     */
    public function loadDraftForEdit(Request $request)
    {
        $invoiceId = $request->get('edit_invoice_id');

        if (!$invoiceId) {
            return null;
        }

        $companyId = session('current_company_id');

        if (!$companyId) {
            Log::error('❌ Company Module: No company selected in session');
            return null;
        }

        // ✅ Load invoice with items from draft_invoice_items table
        $invoice = Invoice::with(['items.product', 'items.chartOfAccount'])
            ->where('status', 'draft')
            ->where('id', $invoiceId)
            ->first();

        if (!$invoice) {
            Log::warning('⚠️ Company Module: Draft invoice not found', [
                'invoice_id' => $invoiceId,
                'company_id' => $companyId
            ]);
            return null;
        }


        // ✅ Verify company access for BOTH Customer and Supplier invoices
        if ($invoice->customer_type === Customer::class) {
            // Sales invoice - check customer belongs to company
            $customer = Customer::where('id', $invoice->customer)
                ->where('Company_ID', $companyId)
                ->first();

            if (!$customer) {
                Log::error('❌ Company Module: Customer does not belong to this company', [
                    'invoice_id' => $invoice->id,
                    'customer_id' => $invoice->customer,
                    'company_id' => $companyId
                ]);
                return null;
            }
        } elseif ($invoice->customer_type === \App\Models\Supplier::class) {
            // Purchase invoice - check supplier belongs to company
            $supplier = \App\Models\Supplier::where('id', $invoice->customer)
                ->where('company_id', $companyId)
                ->first();

            if (!$supplier) {
                Log::error('❌ Company Module: Supplier does not belong to this company', [
                    'invoice_id' => $invoice->id,
                    'supplier_id' => $invoice->customer,
                    'company_id' => $companyId
                ]);
                return null;
            }
        } else {
            // Unknown customer type
            Log::error('❌ Company Module: Unknown customer type', [
                'invoice_id' => $invoice->id,
                'customer_type' => $invoice->customer_type
            ]);
            return null;
        }



        // ✅ Detect payment type from invoice number prefix
        $invoiceNo = $invoice->invoice_no;
        $prefix = strtoupper(substr($invoiceNo, 0, 3));

        $paymentTypeMap = [
            'SIN' => 'sales_invoice',
            'SCN' => 'sales_credit',
            'PUR' => 'purchase',
            'PUC' => 'purchase_credit',
        ];

        $detectedPaymentType = $paymentTypeMap[$prefix] ?? 'sales_invoice';

        // ✅ Generate FRESH invoice number based on latest issued invoices
        $freshInvoiceNumber = $this->generateAutoCode($detectedPaymentType, 'office');

        Log::info('🔄 Draft loaded for edit - FRESH invoice number generated', [
            'draft_id' => $invoice->id,
            'old_invoice_no' => $invoice->invoice_no,
            'fresh_invoice_no' => $freshInvoiceNumber,
            'payment_type' => $detectedPaymentType
        ]);

        // ✅ Format items with ALL required fields
        $formattedItems = [];
        foreach ($invoice->items as $item) {
            $productVatId = $item->vat_form_label_id;
            $productImage = null;

            // ✅ Get product data if exists
            if ($item->product_id && $item->product) {
                $product = $item->product;

                // Fallback VAT from product
                if (!$productVatId && $product->vat_rate_id) {
                    $productVatId = $product->vat_rate_id;
                }

                // Get product image
                if ($product->file_url) {
                    $productImage = $product->file_url;
                }
            }

            $formattedItems[] = [
                'item_code' => $item->item_code ?? '',
                'description' => $item->description,
                'ledger_id' => $item->chart_of_account_id,
                'ledger_ref' => $item->ledger_ref ?? '',
                'account_ref' => $item->account_ref ?? '',
                'qty' => $item->qty ?? '1',
                'unit_amount' => number_format((float)$item->unit_amount, 2, '.', ''),
                'vat_rate' => number_format((float)$item->vat_rate, 2, '.', ''),
                'vat_amount' => number_format((float)$item->vat_amount, 2, '.', ''),
                'net_amount' => number_format((float)$item->net_amount, 2, '.', ''),
                'vat_form_label_id' => $productVatId,
                'product_image' => $productImage,
            ];
        }

        Log::info('✅ Company Module: Draft loaded for edit with FRESH number', [
            'invoice_id' => $invoice->id,
            'stored_number' => $invoice->invoice_no,
            'displayed_number' => $freshInvoiceNumber,
            'customer' => $invoice->customer,
            'items_count' => count($formattedItems),
            'payment_type' => $detectedPaymentType
        ]);

        // ✅ Return data with FRESH invoice number
        return [
            'invoice' => $invoice,
            'customer' => $invoice->customer,
            'invoice_date' => $invoice->invoice_date->format('Y-m-d'),
            'due_date' => $invoice->due_date ? $invoice->due_date->format('Y-m-d') : null,
            'invoice_no' => $freshInvoiceNumber,
            'invoice_ref' => $invoice->invoice_ref,
            'notes' => $invoice->notes,
            'items' => $formattedItems,
            'payment_type' => $detectedPaymentType,
            'type' => 'office',
        ];
    }

    /**
     * Generate auto code via AJAX
     */
    public function generateAutoCodeAjax(Request $request)
    {
        try {
            $paymentType = $request->input('payment_type', 'sales_invoice');
            $accountType = $request->input('account_type', 'office');

            Log::info('📞 AJAX: Generate auto code request', [
                'payment_type' => $paymentType,
                'account_type' => $accountType,
                'company_id' => session('current_company_id'),
                'user_id' => auth()->id()
            ]);

            $validPaymentTypes = ['sales_invoice', 'sales_credit', 'purchase', 'purchase_credit'];
            if (!in_array($paymentType, $validPaymentTypes)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid payment type for company invoices'
                ], 400);
            }

            // ✅ Generate code using the updated method
            $autoCode = $this->generateAutoCode($paymentType, $accountType);

            Log::info('✅ AJAX: Auto code generated successfully', [
                'auto_code' => $autoCode
            ]);

            return response()->json([
                'success' => true,
                'auto_code' => $autoCode,
                'payment_type' => $paymentType,
                'company_id' => session('current_company_id'),
                'user_id' => auth()->id(),
                'message' => 'Auto code generated successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('❌ AJAX: Company auto code generation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'payment_type' => $request->input('payment_type'),
                'company_id' => session('current_company_id'),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error generating auto code: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if transaction code is unique
     */
    public function checkCodeUnique(Request $request)
    {
        try {
            $transactionCode = $request->input('transaction_code');

            if (!$transactionCode) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction code is required'
                ], 400);
            }

            $userId = auth()->id();
            $company = $this->getCurrentCompany();
            $companyId = $company->id;

            Log::info('🔍 Checking code uniqueness', [
                'transaction_code' => $transactionCode,
                'user_id' => $userId,
                'company_id' => $companyId
            ]);

            $exists = Transaction::where('Transaction_Code', $transactionCode)
                ->whereHas('invoice', function ($query) use ($userId, $companyId) {
                    $query->where('created_by', $userId)
                        ->where('customer_type', Customer::class)
                        ->whereIn('customer', function ($subquery) use ($companyId) {
                            $subquery->select('id')
                                ->from('customers')
                                ->where('Company_ID', $companyId);
                        });
                })
                ->exists();

            return response()->json([
                'success' => true,
                'exists' => $exists,
                'message' => $exists ? 'Code already exists for this company' : 'Code is available'
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Code uniqueness check failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error checking code: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ Store invoice (with draft and issued support)
     */
    public function store(Request $request)
    {
        $items = $request->input('items', []);
        if (!empty($items) && isset($items[0])) {
            Log::info('📦 Sample Item (first):', [
                'item_keys' => array_keys($items[0]),
                'has_item_code' => isset($items[0]['item_code']) ? 'YES: ' . $items[0]['item_code'] : 'NO',
                'has_ledger_id' => isset($items[0]['ledger_id']) ? 'YES: ' . $items[0]['ledger_id'] : 'NO',
                'COMPLETE_ITEM_DATA' => $items[0]
            ]);
        } else {
            Log::warning('❌ NO ITEMS FOUND in request!');
        }

        $validated = $this->validateInvoiceRequest($request);

        DB::beginTransaction();

        try {
            $paymentType = $request->input('current_payment_type');
            $transactionCode = trim($request->input('Transaction_Code'));
            $action = $request->input('action');

            if ($action === 'preview') {
                return $this->handlePreview($request);
            }

            if ($action === 'save_as_draft') {
                $editInvoiceId = $request->input('edit_invoice_id');
                return $this->saveDraftInvoice($request, $transactionCode, $editInvoiceId);
            }

            if (empty($transactionCode)) {
                $transactionCode = $this->generateAutoCode($paymentType, 'office');
            }

            $transaction = $this->handleInvoiceBasedTransaction($validated, $transactionCode, $request);

            $draftKey = $request->input('draft_key');

            if ($draftKey) {
                $this->updateDraftToIssued($draftKey, $transaction->invoice_id);
            } else {
                $this->createIssuedDraft($validated, $transaction->invoice_id);
            }

            $successMessage = $this->getSuccessMessage($paymentType) . " created successfully";

            if ($action === 'save_and_email') {
                $successMessage .= " and email sent successfully";
            } elseif ($action === 'save_and_add_new') {
                DB::commit();
                return redirect()->route('company.invoices.create', [
                    'payment_type' => $paymentType
                ])->with('success', $successMessage . ". Ready to create new invoice.");
            }

            DB::commit();
            $redirectParams = $this->getRedirectParams($paymentType, 'issued');

            return redirect()
                ->route('company.invoices.index', $redirectParams)
                ->with([
                    'success' => $successMessage,
                    'payment_type' => $paymentType,
                    'highlight_invoice' => $transaction->invoice_id
                ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Company invoice creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    private function getRedirectParams(string $paymentType, string $tab = 'issued'): array
    {
        $type = match ($paymentType) {
            'sales_invoice', 'sales_credit' => 'sales',
            'purchase', 'purchase_credit' => 'purchase',
            default => 'sales'
        };

        return [
            'tab' => $tab,
            'type' => $type
        ];
    }

    private function handlePreview(Request $request)
    {
        $validated = $request->all();
        $companyId = session('current_company_id');

        if (!$companyId) {
            throw new \Exception('No company selected. Please select a company.');
        }

        Log::info('Creating draft invoice for preview (Company Module)', [
            'company_id' => $companyId,
            'has_items' => isset($validated['items'])
        ]);

        $draft = DraftInvoice::createDraft($validated, $companyId);

        return redirect()->route('company.invoices.templates.preview.show', [
            'draft' => $draft->draft_key
        ]);
    }

    /**
     * ✅ Save draft invoice
     */
    private function saveDraftInvoice(Request $request, string $transactionCode, ?int $editInvoiceId = null)
    {
        $isUpdate = !is_null($editInvoiceId);

        Log::info($isUpdate ? '=== COMPANY DRAFT UPDATE STARTED ===' : '=== COMPANY DRAFT SAVE STARTED ===', [
            'transaction_code' => $transactionCode,
            'payment_type' => $request->input('current_payment_type'),
            'edit_invoice_id' => $editInvoiceId,
        ]);

        try {
            $paymentType = $request->input('current_payment_type');

            $customerId = $request->input('customer_id');
            if (!$customerId) {
                throw new \Exception('Please select a customer.');
            }

            $items = $this->extractAndValidateInvoiceItems($request);

            if (empty($items)) {
                throw new \Exception('Please add at least one item to save as draft.');
            }

            $subtotal = collect($items)->sum('unit_amount');
            $vatTotal = collect($items)->sum('vat_amount');
            $grandTotal = collect($items)->sum('net_amount');

            $freshInvoiceNumber = $this->generateAutoCode($paymentType, 'office');

            Log::info('✅ Generated fresh invoice number for draft', [
                'old_number' => $transactionCode,
                'new_number' => $freshInvoiceNumber,
                'is_update' => $isUpdate
            ]);

            $invoiceData = [
                'invoice_date' => $request->input('Transaction_Date'),
                'due_date' => $request->input('Inv_Due_Date'),
                'invoice_no' => $freshInvoiceNumber,
                'invoice_ref' => $request->input('invoice_ref'),
                'notes' => $request->input('notes'),
                'net_amount' => $subtotal,
                'vat_amount' => $vatTotal,
                'total_amount' => $grandTotal,
                'company_id' => session('current_company_id'),
                'documents' => $request->input('invoice_documents'),
            ];

            // ✅ FIXED: Pass payment type to get correct context
            $customerContext = $this->getCustomerContextData($customerId, $paymentType);

            if ($isUpdate) {
                $invoice = Invoice::where('status', 'draft')
                    ->where('customer_type', Customer::class)
                    ->where('id', $editInvoiceId)
                    ->firstOrFail();

                Log::info('📝 Updating existing draft - RENUMBERING', [
                    'invoice_id' => $invoice->id,
                    'old_invoice_no' => $invoice->invoice_no,
                    'new_invoice_no' => $freshInvoiceNumber
                ]);

                $invoice = $this->invoiceService->updateInvoice(
                    $invoice,
                    $invoiceData,
                    $items
                );

                Log::info('✅ Company draft invoice updated with NEW number', [
                    'invoice_id' => $invoice->id,
                    'invoice_no' => $invoice->invoice_no
                ]);
            } else {
                Log::info('📝 Creating new company draft invoice', [
                    'invoice_no' => $freshInvoiceNumber
                ]);

                $invoice = $this->invoiceService->createInvoice(
                    $invoiceData,
                    $customerContext['customer_model'],
                    $customerContext['customer_id'],
                    $items,
                    false
                );

                Log::info('✅ Company draft invoice created successfully', [
                    'invoice_id' => $invoice->id,
                    'invoice_no' => $invoice->invoice_no
                ]);
            }

            DB::commit();

            $successMessage = $isUpdate
                ? "Draft invoice updated and renumbered to {$invoice->invoice_no}. You can continue editing or issue it."
                : "Invoice saved as draft with number {$invoice->invoice_no}. You can edit or issue it later.";

            Log::info($isUpdate ? '=== COMPANY DRAFT UPDATE COMPLETED ===' : '=== COMPANY DRAFT SAVE COMPLETED ===', [
                'invoice_id' => $invoice->id,
                'invoice_no' => $invoice->invoice_no
            ]);

            $redirectParams = $this->getRedirectParams($paymentType, 'drafts');

            return redirect()
                ->route('company.invoices.index', $redirectParams)
                ->with([
                    'success' => $successMessage,
                    'payment_type' => $paymentType,
                    'highlight_invoice' => $invoice->id
                ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('=== COMPANY DRAFT SAVE/UPDATE FAILED ===', [
                'error_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to save draft: ' . $e->getMessage());
        }
    }

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

                Log::info('✅ Company draft updated to issued', [
                    'draft_key' => $draftKey,
                    'invoice_id' => $invoiceId
                ]);
            } else {
                Log::warning('⚠️ Company draft not found for key', ['draft_key' => $draftKey]);
            }
        } catch (\Exception $e) {
            Log::error('❌ Failed to update company draft status', [
                'draft_key' => $draftKey,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function createIssuedDraft(array $validated, int $invoiceId): void
    {
        if (!$invoiceId) {
            Log::warning('Cannot create issued draft - missing invoice ID');
            return;
        }

        try {
            $companyId = session('current_company_id');

            if (!$companyId) {
                throw new \Exception('No company selected');
            }

            $request = request();

            $completeData = [
                'Transaction_Date' => $request->input('Transaction_Date'),
                'customer_id' => $request->input('customer_id'),
                'Transaction_Code' => $request->input('Transaction_Code'),
                'invoice_no' => $request->input('invoice_no'),
                'invoice_ref' => $request->input('invoice_ref'),
                'Inv_Due_Date' => $request->input('Inv_Due_Date'),
                'invoice_net_amount' => $request->input('invoice_net_amount'),
                'invoice_vat_amount' => $request->input('invoice_vat_amount'),
                'invoice_total_amount' => $request->input('invoice_total_amount'),
                'current_payment_type' => $request->input('current_payment_type'),
                'account_type' => $request->input('account_type'),
                'invoice_documents' => $request->input('invoice_documents'),
                'notes' => $request->input('invoice_notes'),
            ];

            $rawItems = $request->input('items', []);
            $completeItems = [];

            foreach ($rawItems as $index => $item) {
                if (empty($item['description']) || empty($item['unit_amount'])) {
                    continue;
                }

                $completeItems[] = [
                    'item_code' => $item['item_code'] ?? '',
                    'description' => $item['description'],
                    'ledger_id' => $item['ledger_id'],
                    'account_ref' => $item['account_ref'] ?? '',
                    'qty' => $item['qty'] ?? '1',
                    'unit_amount' => $item['unit_amount'],
                    'vat_rate' => $item['vat_rate'] ?? '0',
                    'vat_form_label_id' => $item['vat_form_label_id'] ?? null,
                    'vat_amount' => $item['vat_amount'] ?? '0',
                    'net_amount' => $item['net_amount'] ?? '0',
                    'product_image' => $item['product_image'] ?? '',
                ];
            }

            $completeData['items'] = $completeItems;

            Log::info('✅ Building complete draft data', [
                'invoice_id' => $invoiceId,
                'company_id' => $companyId,
                'items_count' => count($completeItems)
            ]);

            DraftInvoice::create([
                'draft_key' => DraftInvoice::generateKey(),
                'client_id' => $companyId,
                'invoice_id' => $invoiceId,
                'status' => DraftInvoice::STATUS_ISSUED,
                'invoice_data' => $completeData,
                'expires_at' => now()->addDays(30)
            ]);

            Log::info('✅ Company draft_invoices record created with COMPLETE data', [
                'invoice_id' => $invoiceId,
                'company_id' => $companyId,
                'total_fields' => count(array_keys($completeData)),
                'items_with_complete_fields' => count($completeItems)
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Failed to create company issued draft', [
                'invoice_id' => $invoiceId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * ✅ Handle invoice-based transaction
     */
    private function handleInvoiceBasedTransaction(array $validated, string $transactionCode, Request $request)
    {
        $paymentType = $request->input('current_payment_type');

        $editInvoiceId = $request->input('edit_invoice_id');
        $isUpdatingDraft = !is_null($editInvoiceId);

        if ($isUpdatingDraft) {
            Log::info('Issuing edited company draft invoice', ['invoice_id' => $editInvoiceId]);

            $existingInvoice = Invoice::where('status', 'draft')
                ->where('customer_type', Customer::class)
                ->where('id', $editInvoiceId)
                ->firstOrFail();

            $existingInvoice->items()->delete();
            $existingInvoice->transactions()->delete();

            Log::info('Cleared old data for company draft update');
        }

        $bankAccountId = $validated['Bank_Account_ID'] ?? null;
        if ($bankAccountId) {
            $bankAccount = BankAccount::find($bankAccountId);
            if (!$bankAccount || $bankAccount->Bank_Type_ID !== 2) {
                throw new \Exception('Please select an office bank account.');
            }
        }

        $customerId = $request->input('customer_id');
        if (!$customerId) {
            throw new \Exception('Please select a customer.');
        }

        // ✅ FIXED: Pass payment type to get correct context
        $customerContext = $this->getCustomerContextData($customerId, $paymentType);
        $customer = $this->validateCustomer($customerId, $paymentType);

        $invoiceItems = $this->extractAndValidateInvoiceItems($request);

        if (empty($invoiceItems)) {
            throw new \Exception('Please add at least one item to the invoice.');
        }

        $documentsJson = $request->input('invoice_documents');
        $documents = null;

        if (!empty($documentsJson)) {
            $documents = is_string($documentsJson) ? json_decode($documentsJson, true) : $documentsJson;
            Log::info('📎 Documents found for issued invoice', [
                'count' => is_array($documents) ? count($documents) : 0
            ]);
        }

        $invoiceData = [
            'invoice_date' => $validated['Transaction_Date'],
            'due_date' => $request->input('Inv_Due_Date'),
            'invoice_no' => $request->input('invoice_no', $transactionCode),
            'invoice_ref' => $request->input('invoice_ref'),
            'net_amount' => (float)$request->input('invoice_net_amount', 0),
            'vat_amount' => (float)$request->input('invoice_vat_amount', 0),
            'total_amount' => (float)$request->input('invoice_total_amount', 0),
            'documents' => $documents,
            'company_id' => session('current_company_id'),
        ];

        if ($invoiceData['total_amount'] <= 0) {
            throw new \Exception('Invoice total amount must be greater than zero.');
        }

        if ($isUpdatingDraft) {
            $invoice = $this->invoiceService->updateInvoice(
                $existingInvoice,
                $invoiceData,
                $invoiceItems
            );

            $invoice = $this->invoiceService->issueDraftInvoice($invoice);

            Log::info('✅ Updated existing company draft to issued status', [
                'invoice_id' => $invoice->id,
                'has_documents' => !empty($documents)
            ]);
        } else {
            $invoice = $this->invoiceService->createInvoice(
                $invoiceData,
                $customerContext['customer_model'],
                $customerContext['customer_id'],
                $invoiceItems,
                true
            );

            Log::info('✅ Created new issued invoice', [
                'invoice_id' => $invoice->id,
                'has_documents' => !empty($documents),
                'customer_type' => $customerContext['customer_model'],
                'customer_id' => $customerContext['customer_id']
            ]);
        }

        $transactions = [];
        foreach ($invoiceItems as $index => $item) {
            $chartId = $item['ledger_id'];
            $effect = $this->transactionService->effectForPaymentType($paymentType);
            $entryType = $this->transactionService->entryTypeFromCoaAndEffect($chartId, $effect);
            $paidIO = $this->transactionService->paidInOutFromEntryType($entryType);

            $accountRefId = $this->transactionService->resolveAccountRefIdByLedgerId($chartId, $item['account_ref']);

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
        }

        Log::info('✅ Company invoice transactions created', [
            'invoice_id' => $invoice->id,
            'transaction_count' => count($transactions),
            'customer_type' => $customerContext['customer_model'],
            'customer_id' => $customerId,
            'documents_attached' => !empty($documents) ? count($documents) : 0
        ]);

        return $transactions[0];
    }

    private function extractAndValidateInvoiceItems(Request $request)
    {
        $itemsOut = [];
        $itemsData = $request->input('items', []);

        foreach ($itemsData as $index => $itemData) {
            if (empty($itemData['description']) || empty($itemData['unit_amount'])) {
                continue;
            }

            $unitAmount = $this->toDecimal($itemData['unit_amount']);
            if ($unitAmount === null || $unitAmount <= 0) {
                throw new \Exception("Item " . ($index + 1) . ": Unit amount must be greater than 0");
            }

            $ledgerId = isset($itemData['ledger_id']) && $itemData['ledger_id'] !== ''
                ? (int) $itemData['ledger_id']
                : null;

            if (!$ledgerId) {
                throw new \Exception("Item " . ($index + 1) . ": Missing ledger account.");
            }

            $chartAccount = ChartOfAccount::where('id', $ledgerId)
                ->select('id', 'ledger_ref', 'account_ref')
                ->first();

            if (!$chartAccount) {
                throw new \Exception("Item " . ($index + 1) . ": Chart of Account not found.");
            }

            $ledgerRefText = $chartAccount->ledger_ref;
            $accountRefText = trim($itemData['account_ref'] ?? '');
            $accountRefId = $this->transactionService->resolveAccountRefIdByLedgerId($ledgerId, $accountRefText);

            $item = [
                'ledger_id' => $ledgerId,
                'ledger_ref' => $ledgerRefText,
                'item_code' => trim($itemData['item_code'] ?? ''),
                'description' => trim($itemData['description']),
                'account_ref' => $accountRefText,
                'account_ref_id' => $accountRefId,
                'unit_amount' => $unitAmount,
                'vat_rate' => $this->toDecimal($itemData['vat_rate'] ?? 0) ?? 0,
                'vat_form_label_id' => isset($itemData['vat_form_label_id']) ? (int)$itemData['vat_form_label_id'] : null,
                'vat_amount' => $this->toDecimal($itemData['vat_amount'] ?? 0) ?? 0,
                'net_amount' => $this->toDecimal($itemData['net_amount'] ?? 0) ?? 0,
            ];

            $itemsOut[] = $item;
        }

        return $itemsOut;
    }

    private function toDecimal($v)
    {
        if ($v === '' || $v === null) return null;
        $v = str_replace(',', '', (string) $v);
        return is_numeric($v) ? (float) $v : null;
    }

    /**
     * ✅ Dynamic validation based on payment type
     */
    private function validateInvoiceRequest(Request $request)
    {
        $paymentType = $request->input('current_payment_type');

        $isPurchaseType = in_array($paymentType, ['purchase', 'purchase_credit']);
        $customerValidation = $isPurchaseType
            ? 'required|integer|exists:suppliers,id'
            : 'required|integer|exists:customers,id';

        Log::info('🔍 Validating invoice request', [
            'payment_type' => $paymentType,
            'is_purchase' => $isPurchaseType,
            'customer_id' => $request->input('customer_id'),
            'validation_rule' => $customerValidation
        ]);

        return $request->validate([
            'Transaction_Date' => 'required|date',
            'customer_id' => $customerValidation,
            'Bank_Account_ID' => 'nullable|integer|exists:bankaccount,Bank_Account_ID',
            'Payment_Type_ID' => 'nullable|integer',
            'Transaction_Code' => 'nullable|string|max:50',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.unit_amount' => 'required|numeric|min:0',
            'items.*.ledger_id' => 'required|integer',
        ]);
    }

    private function generateAutoCode($paymentType, $accountType = 'office')
    {
        $prefix = match ($paymentType) {
            'sales_invoice' => 'SIN',
            'sales_credit' => 'SCN',
            'purchase' => 'PUR',
            'purchase_credit' => 'PUC',
            default => 'INV',
        };

        $userId = auth()->id();
        if (!$userId) {
            throw new \Exception('User not authenticated');
        }

        $companyId = session('current_company_id');
        if (!$companyId) {
            throw new \Exception('No company selected');
        }

        Log::info('🔍 Generating auto code (based on ISSUED invoices only)', [
            'user_id' => $userId,
            'company_id' => $companyId,
            'prefix' => $prefix
        ]);

        $lastTransaction = Transaction::where('Transaction_Code', 'LIKE', $prefix . '%')
            ->whereHas('invoice', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })
            ->orderByRaw('CAST(SUBSTRING(Transaction_Code, ' . (strlen($prefix) + 1) . ') AS UNSIGNED) DESC')
            ->first();

        $nextNumber = 1;

        if ($lastTransaction) {
            Log::info('✅ Found last ISSUED transaction', [
                'transaction_code' => $lastTransaction->Transaction_Code,
                'transaction_id' => $lastTransaction->Transaction_ID,
                'invoice_id' => $lastTransaction->invoice_id
            ]);

            $numberPart = substr($lastTransaction->Transaction_Code, strlen($prefix));
            $nextNumber = intval($numberPart) + 1;
        } else {
            Log::info('ℹ️ First invoice for this company', [
                'user_id' => $userId,
                'company_id' => $companyId,
                'prefix' => $prefix
            ]);
        }

        $autoCode = $prefix . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);

        Log::info('✅ Generated auto code (next available after ISSUED invoices)', [
            'auto_code' => $autoCode,
            'next_number' => $nextNumber,
            'user_id' => $userId,
            'company_id' => $companyId
        ]);

        return $autoCode;
    }

    private function getSuccessMessage(string $paymentType): string
    {
        return match ($paymentType) {
            'sales_invoice' => 'Sales invoice',
            'sales_credit' => 'Sales credit note',
            'purchase' => 'Purchase invoice',
            'purchase_credit' => 'Purchase credit note',
            default => 'Invoice',
        };
    }

    public function getCustomersDropdown(Request $request)
    {
        try {
            $companyId = session('current_company_id');

            if (!$companyId) {
                return response()->json([
                    'success' => false,
                    'message' => 'No company selected'
                ], 400);
            }

            $customers = Customer::where('Company_ID', $companyId)
                ->select('id', 'Legal_Name_Company_Name', 'Email', 'Tax_ID_Number')
                ->orderBy('Legal_Name_Company_Name')
                ->get();

            return response()->json([
                'success' => true,
                'customers' => $customers
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to load customers dropdown', [
                'error' => $e->getMessage(),
                'company_id' => session('current_company_id')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load customers: ' . $e->getMessage()
            ], 500);
        }
    }

    // ========================================
    // ✅ BASEINVOICECONTROLLER OVERRIDES
    // ========================================

    protected function getInvoiceTypeFilter($query)
    {
        $type = request()->get('type', 'sales');

        if ($type === 'purchase') {
            return $query->where(function ($q) {
                $q->where('invoice_no', 'LIKE', 'PUR%')
                    ->orWhere('invoice_no', 'LIKE', 'PUC%');
            });
        }

        return $query->where(function ($q) {
            $q->where('invoice_no', 'LIKE', 'SIN%')
                ->orWhere('invoice_no', 'LIKE', 'SCN%');
        });
    }

    protected function getTypeName(): string
    {
        return 'Invoice';
    }

    protected function getRoutePrefix(): string
    {
        return 'company.invoices';
    }

    protected function getViewPath(): string
    {
        return 'admin.invoices';
    }

    /**
     * ✅ FIXED: Apply context filter for BOTH sales and purchases
     */
    protected function applyContextFilter($query)
    {
        $companyId = session('current_company_id');

        if (!$companyId) {
            throw new \Exception('No company selected');
        }

        $type = request()->get('type', 'sales');
        $isPurchaseType = ($type === 'purchase');

        Log::info('🏢 CompanyInvoiceController: Applying context filter', [
            'company_id' => $companyId,
            'type' => $type,
            'is_purchase' => $isPurchaseType
        ]);

        if ($isPurchaseType) {
            Log::info('📦 Company Module: Filtering PURCHASES', [
                'company_id' => $companyId
            ]);

            return $query->where('customer_type', \App\Models\Supplier::class)
                ->whereIn('customer', function ($subquery) use ($companyId) {
                    $subquery->select('id')
                        ->from('suppliers')
                        ->where('company_id', $companyId);
                });
        } else {
            Log::info('💰 Company Module: Filtering SALES', [
                'company_id' => $companyId
            ]);

            return $query->where('customer_type', Customer::class)
                ->whereIn('customer', function ($subquery) use ($companyId) {
                    $subquery->select('id')
                        ->from('customers')
                        ->where('Company_ID', $companyId);
                });
        }
    }
}
