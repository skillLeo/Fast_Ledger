<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\BankAccount;
use App\Models\Transaction;
use App\Models\DraftInvoice;
use Illuminate\Http\Request;
use App\Models\InvoiceTemplate;
use App\Models\InvoiceActivityLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

abstract class BaseInvoiceController extends Controller
{
    /**
     * ✅ ABSTRACT METHOD - Child controllers MUST implement this
     */
    abstract protected function getInvoiceTypeFilter($query);
    abstract protected function getTypeName(): string;
    abstract protected function getRoutePrefix(): string;
    abstract protected function getViewPath(): string;

    /**
     * ✅ FIXED: Better context detection
     */
    protected function getContextIdentifiers(): array
    {
        $user = auth()->user();

        // ✅ FIX 1: Check route name first (more reliable than path)
        $isCompanyRoute = request()->routeIs('company.*');

        // ✅ FIX 2: If it's a company route, REQUIRE company session
        if ($isCompanyRoute) {
            $companyId = session('current_company_id');

            if (!$companyId) {
                // Redirect to company selection instead of throwing exception
                abort(redirect()->route('company.select')->with('error', 'Please select a company first.'));
            }

            // Check if user has access to this company
            $hasAccess = DB::table('company_module_users')
                ->where('company_id', $companyId)
                ->where('user_id', $user->User_ID)
                ->exists();

            if (!$hasAccess) {
                abort(403, 'You do not have access to this company.');
            }

            return [
                'company_id' => (int) $companyId,
                'client_id' => null,
                'context' => 'company'
            ];
        }

        if (!$user->Client_ID) {
            $isCompanyUser = $user->hasRole(4);
            if ($isCompanyUser) {
                throw new \Exception('Company users must access invoices through the company module.');
            }
            throw new \Exception('No client associated with user.');
        }

        return [
            'client_id' => (int) $user->Client_ID,  // ✅ FIX: Use Client_ID not User_ID
            'company_id' => null,
            'context' => 'client'
        ];
    }

    /**
     * ✅ Apply context-based filtering to query
     */
    protected function applyContextFilter($query)
    {
        $identifiers = $this->getContextIdentifiers();

        if ($identifiers['context'] === 'company') {
            // Company context: Filter by company through customers table
            return $query->where('customer_type', \App\Models\CompanyModule\Customer::class)
                ->whereIn('customer', function ($subquery) use ($identifiers) {
                    $subquery->select('id')
                        ->from('customers')
                        ->where('Company_ID', $identifiers['company_id']);
                });
        } else {
            // ✅ CLIENT CONTEXT FIX: Filter by who created the invoice
            return $query->whereHas('customerFile', function ($q) use ($identifiers) {
                $q->where('Client_ID', $identifiers['client_id']);
            });
        }
    }

    public function index(Request $request)
    {
        
        try {
            $activeTab = $request->get('tab', 'issued');

            // ✅ Smart type detection:
            // 1. Check query param first (for company module: ?type=purchase)
            // 2. Fall back to controller type (for main app separate controllers)
            $queryType = $request->get('type');
            $controllerType = $this->getTypeName() === 'Purchase' ? 'purchase' : 'sales';
            $type = $queryType ?: $controllerType;
            $query = Invoice::with([
                'customerFile:File_ID,First_Name,Last_Name,Ledger_Ref',
                'documents',
            ])->whereNotNull('customer');

            // ✅ ONLY apply child controller's filter - no duplicate filtering!
            $query = $this->getInvoiceTypeFilter($query);

            // ✅ Apply context filter (company or client)
            $query = $this->applyContextFilter($query);

            if ($activeTab === 'drafts') {
                $query->where('status', Invoice::STATUS_DRAFT);
            } else {
                $query->where('status', '!=', Invoice::STATUS_DRAFT);
            }

            $invoices = $query->orderByDesc('invoice_date')
                ->orderByDesc('id')
                ->paginate(20);

            // ✅ Determine if we're in company module
            $identifiers = $this->getContextIdentifiers();
            $isCompanyModule = ($identifiers['context'] === 'company');

            return view($this->getViewPath() . '.index', [
                'invoices' => $invoices,
                'activeTab' => $activeTab,
                'typeName' => $type === 'purchase' ? 'Purchase' : 'Invoice',
                'routePrefix' => $this->getRoutePrefix(),
                'isCompanyModule' => $isCompanyModule,
                'type' => $type,
            ]);
        } catch (\Exception $e) {
            Log::error('Invoice index error', [
                'error' => $e->getMessage(),
                'route' => request()->route()->getName()
            ]);

            return redirect()->route('company.select')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Edit draft
     */
    public function edit($id)
    {
        $invoice = Invoice::where('status', Invoice::STATUS_DRAFT)->findOrFail($id);

        // ✅ Check access
        $this->checkInvoiceAccess($invoice);

        $paymentType = $this->getPaymentTypeFromInvoiceNo($invoice->invoice_no);

        // ✅ Route based on context
        $identifiers = $this->getContextIdentifiers();

        if ($identifiers['context'] === 'company') {
            return redirect()->route('company.invoices.create', [
                'payment_type' => $paymentType,
                'edit_invoice_id' => $invoice->id
            ]);
        }

        return redirect()->route('transactions.create', [
            'type' => 'office',
            'payment_type' => $paymentType,
            'edit_invoice_id' => $invoice->id
        ]);
    }

    /**
     * View details
     * ✅ FIXED: Use client_id for both contexts
     */
    public function view($id)
    {

        try {
            $identifiers = $this->getContextIdentifiers();

            // ✅ Use client_id for BOTH company and client context
            $contextId = $identifiers['context'] === 'company'
                ? $identifiers['company_id']
                : $identifiers['client_id'];

            $draftInvoice = DraftInvoice::where('invoice_id', $id)
                ->where('client_id', $contextId)
                ->first();

            if (!$draftInvoice) {
                Log::warning('Draft invoice not found', [
                    'invoice_id' => $id,
                    'context' => $identifiers['context'],
                    'context_id' => $contextId
                ]);
                return redirect()->back()->with('error', $this->getTypeName() . ' not found');
            }

            // ✅ Check access
            $this->checkDraftInvoiceAccess($draftInvoice);

            $invoiceData = $draftInvoice->formatted_data;

            if (empty($invoiceData)) {
                return redirect()->back()->with('error', 'Invalid ' . strtolower($this->getTypeName()) . ' data');
            }


            // dd($invoiceData);
            // ✅ Pass context info to view
            $isCompanyModule = ($identifiers['context'] === 'company');
            return view($this->getViewPath() . '.view', [
                'invoice' => $draftInvoice,
                'invoiceData' => $invoiceData,
                'typeName' => $this->getTypeName(),
                'routePrefix' => $this->getRoutePrefix(),
                'isCompanyModule' => $isCompanyModule,
            ]);
        } catch (\Exception $e) {
            Log::error('View invoice error', [
                'invoice_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->with('error', $this->getTypeName() . ' not found: ' . $e->getMessage());
        }
    }

    /**
     * Download as PDF
     * ✅ FIXED: Use client_id for both contexts
     */
    public function downloadPDF($id)
    {
        try {
            $identifiers = $this->getContextIdentifiers();

            // ✅ Use client_id for BOTH contexts
            $contextId = $identifiers['context'] === 'company'
                ? $identifiers['company_id']
                : $identifiers['client_id'];

            $draftInvoice = DraftInvoice::where('invoice_id', $id)
                ->where('client_id', $contextId)
                ->firstOrFail();

            // ✅ Check access
            $this->checkDraftInvoiceAccess($draftInvoice);

            $invoiceData = $draftInvoice->invoice_data;
            if (is_string($invoiceData)) {
                $invoiceData = json_decode($invoiceData, true);
            }

            if (!$invoiceData) {
                return redirect()->back()->with('error', 'Invalid ' . strtolower($this->getTypeName()) . ' data');
            }

            $data = [
                'invoice' => $draftInvoice,
                'invoiceData' => $invoiceData,
                'typeName' => $this->getTypeName(),
            ];

            $pdf = \PDF::loadView('admin.invoices.pdf', $data);
            $pdf->setPaper('A4', 'portrait');

            $filename = strtolower($this->getTypeName()) . '-' . ($invoiceData['invoice_no'] ?? $id) . '-' . date('Y-m-d') . '.pdf';

            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error($this->getTypeName() . ' PDF Generation Failed', [
                'invoice_id' => $id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    /**
     * View PDF in browser
     * ✅ FIXED: Use client_id for both contexts
     */
    public function viewPDF($id)
    {
        try {
            $identifiers = $this->getContextIdentifiers();

            // ✅ Use client_id for BOTH contexts
            $contextId = $identifiers['context'] === 'company'
                ? $identifiers['company_id']
                : $identifiers['client_id'];

            $draftInvoice = DraftInvoice::where('invoice_id', $id)
                ->where('client_id', $contextId)
                ->firstOrFail();

            // ✅ Check access
            $this->checkDraftInvoiceAccess($draftInvoice);

            $invoiceData = $draftInvoice->invoice_data;
            if (is_string($invoiceData)) {
                $invoiceData = json_decode($invoiceData, true);
            }

            $data = [
                'invoice' => $draftInvoice,
                'invoiceData' => $invoiceData,
                'typeName' => $this->getTypeName(),
            ];

            $pdf = \PDF::loadView('admin.invoices.pdf', $data);
            $pdf->setPaper('A4', 'portrait');

            return $pdf->stream(strtolower($this->getTypeName()) . '-' . ($invoiceData['invoice_no'] ?? $id) . '.pdf');
        } catch (\Exception $e) {
            Log::error('PDF view error', [
                'invoice_id' => $id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Failed to generate PDF');
        }
    }



    /**
     * Delete draft
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $invoice = Invoice::where('status', Invoice::STATUS_DRAFT)->findOrFail($id);

            // ✅ Check access
            $this->checkInvoiceAccess($invoice);

            $invoice->delete();

            DB::commit();

            return redirect()
                ->route($this->getRoutePrefix() . '.index', ['tab' => 'drafts'])
                ->with('success', 'Draft ' . strtolower($this->getTypeName()) . ' deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->with('error', 'Failed to delete: ' . $e->getMessage());
        }
    }

    /**
     * Get status details for modal
     */
    public function getStatusDetails($id)
    {
        try {
            $invoice = Invoice::with(['customerFile'])->findOrFail($id);

            // ✅ Check access
            $this->checkInvoiceAccess($invoice);

            return response()->json([
                'success' => true,
                'data' => [
                    'invoice_id' => $invoice->id,
                    'invoice_no' => $invoice->invoice_no,
                    'customer_name' => $invoice->customer_name,
                    'invoice_date' => $invoice->invoice_date->format('d/m/Y'),
                    'due_date' => $invoice->due_date ? $invoice->due_date->format('d/m/Y') : '-',
                    'is_overdue' => $invoice->isOverdue(),
                    'current_status' => $invoice->status,
                    'current_status_label' => $invoice->status_label,
                    'total_amount' => number_format($invoice->total_amount, 2),
                    'paid' => number_format($invoice->paid, 2),
                    'balance' => number_format($invoice->balance, 2),
                    'balance_raw' => $invoice->balance,
                    'status_options' => Invoice::getStatusOptions(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load ' . strtolower($this->getTypeName()) . ' details: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get activity log
     * ✅ FIXED: Use client_id for both contexts
     */
    public function getInvoiceActivityLogs($invoiceId)
    {
        try {
            $identifiers = $this->getContextIdentifiers();

            // ✅ Use client_id for BOTH contexts
            $contextId = $identifiers['context'] === 'company'
                ? $identifiers['company_id']
                : $identifiers['client_id'];

            $draftInvoice = DraftInvoice::where('invoice_id', $invoiceId)
                ->where('client_id', $contextId)  // ✅ FIXED: Use client_id for both
                ->first();

            if (!$draftInvoice) {
                return response()->json([
                    'success' => false,
                    'message' => $this->getTypeName() . ' not found'
                ], 404);
            }

            $activities = InvoiceActivityLog::where('invoice_id', $invoiceId)
                ->with(['user' => function ($query) {
                    $query->select('User_ID', 'Full_Name', 'email');
                }])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($activity) {
                    return [
                        'id' => $activity->id,
                        'action' => $activity->action,
                        'user_name' => $activity->user?->Full_Name ?? 'System',
                        'user_email' => $activity->user?->email ?? null,
                        'user_id' => $activity->user_id,
                        'old_values' => $activity->old_values,
                        'new_values' => $activity->new_values,
                        'notes' => $activity->notes,
                        'ip_address' => $activity->ip_address,
                        'user_agent' => $activity->user_agent,
                        'created_at' => $activity->created_at->toIso8601String(),
                        'formatted_time' => $activity->created_at->diffForHumans(),
                        'action_label' => $activity->action_label ?? ucfirst($activity->action),
                        'action_icon' => $activity->action_icon ?? 'fa-circle',
                        'action_color' => $activity->action_color ?? 'secondary',
                    ];
                });

            $invoiceData = $draftInvoice->formatted_data;

            return response()->json([
                'success' => true,
                'activities' => $activities,
                'invoice' => [
                    'id' => $invoiceId,
                    'invoice_no' => $invoiceData['invoice_no'] ?? 'N/A',
                    'status' => $draftInvoice->status ?? 'N/A',
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch ' . strtolower($this->getTypeName()) . ' activity log', [
                'invoice_id' => $invoiceId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load activity log: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update status and payment
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $invoice = Invoice::findOrFail($id);

            // ✅ Check access
            $this->checkInvoiceAccess($invoice);

            $rules = [
                'status' => 'required|in:draft,sent,paid,partially_paid,overdue',
            ];

            if ($request->status === Invoice::STATUS_PARTIALLY_PAID) {
                $rules['payment_amount'] = [
                    'required',
                    'numeric',
                    'min:0.01',
                    'max:' . $invoice->balance
                ];
            }

            $request->validate($rules, [
                'payment_amount.required' => 'Payment amount is required for partial payments',
                'payment_amount.max' => 'Payment amount cannot exceed remaining balance of £' . number_format($invoice->balance, 2)
            ]);

            DB::beginTransaction();

            $oldStatus = $invoice->status;
            $paymentAmount = $request->payment_amount;

            switch ($request->status) {
                case Invoice::STATUS_PAID:
                    $invoice->status = Invoice::STATUS_PAID;
                    $invoice->paid = $invoice->total_amount;
                    $invoice->balance = 0;
                    break;

                case Invoice::STATUS_PARTIALLY_PAID:
                    if ($paymentAmount) {
                        $invoice->paid += $paymentAmount;
                        $invoice->balance = $invoice->total_amount - $invoice->paid;

                        if ($invoice->balance <= 0.01) {
                            $invoice->status = Invoice::STATUS_PAID;
                            $invoice->balance = 0;
                        } else {
                            $invoice->status = Invoice::STATUS_PARTIALLY_PAID;
                        }
                    }
                    break;

                case Invoice::STATUS_SENT:
                    $invoice->status = Invoice::STATUS_SENT;
                    break;

                case Invoice::STATUS_OVERDUE:
                    $invoice->status = Invoice::STATUS_OVERDUE;
                    break;

                case Invoice::STATUS_DRAFT:
                    $invoice->status = Invoice::STATUS_DRAFT;
                    break;
            }

            $invoice->save();

            $description = "Status changed from {$oldStatus} to {$request->status}";
            if ($paymentAmount) {
                $description .= " - Payment of £{$paymentAmount} recorded";
            }

            $invoice->activityLogs()->create([
                'invoice_id' => $invoice->id,
                'user_id' => auth()->id(),
                'action' => 'status_updated',
                'notes' => $description,
                'old_values' => json_encode(['status' => $oldStatus, 'paid' => $invoice->getOriginal('paid')]),
                'new_values' => json_encode(['status' => $invoice->status, 'paid' => $invoice->paid]),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully',
                'data' => [
                    'status' => $invoice->status,
                    'status_label' => $invoice->status_label,
                    'status_badge' => $invoice->status_badge,
                    'paid' => number_format($invoice->paid, 2),
                    'balance' => number_format($invoice->balance, 2),
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to update status: ' . $e->getMessage()
            ], 500);
        }
    }



    /**
     * Get documents for modal
     */
    public function getDocuments($id)
    {
        try {
            $invoice = Invoice::with(['documents', 'customerFile'])->findOrFail($id);

            // ✅ Check access
            $this->checkInvoiceAccess($invoice);

            $documentsData = $invoice->documents->map(function ($doc) {
                return [
                    'id' => $doc->id,
                    'document_name' => $doc->document_name,
                    'file_type' => $doc->file_type,
                    'file_size' => $doc->file_size,
                    'formatted_size' => $doc->formatted_size,
                    'document_url' => Storage::url($doc->document_path),
                    'created_at' => $doc->created_at->format('d/m/Y H:i'),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'invoice_no' => $invoice->invoice_no,
                    'customer_name' => $invoice->customer_name,
                    'documents' => $documentsData,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load documents: ' . $e->getMessage()
            ], 500);
        }
    }

    // ========================================
    // HELPER METHODS
    // ========================================

    /**
     * ✅ FIXED: Check if user has access to this invoice
     */
    protected function checkInvoiceAccess($invoice)
    {
        $identifiers = $this->getContextIdentifiers();

        Log::info('🔍 Checking invoice access', [
            'invoice_id' => $invoice->id,
            'context' => $identifiers['context'],
            'company_id' => $identifiers['company_id'] ?? null,
            'client_id' => $identifiers['client_id'] ?? null,
            'invoice_customer_type' => $invoice->customer_type,
            'invoice_customer' => $invoice->customer,
            'created_by' => $invoice->created_by
        ]);

        if ($identifiers['context'] === 'company') {
            // ========================================
            // COMPANY MODULE ACCESS CHECK
            // ========================================

            // ✅ Check 1: Invoice creator must have access to this company
            $creatorHasAccess = DB::table('company_module_users')
                ->where('company_id', $identifiers['company_id'])
                ->where('user_id', $invoice->created_by)
                ->exists();

            if (!$creatorHasAccess) {
                Log::error('❌ Invoice creator does not have access to this company', [
                    'invoice_id' => $invoice->id,
                    'created_by' => $invoice->created_by,
                    'company_id' => $identifiers['company_id']
                ]);
                abort(403, 'Unauthorized access to this invoice');
            }

            // ✅ Check 2: Verify customer/supplier belongs to company
            if ($invoice->customer_type === \App\Models\CompanyModule\Customer::class) {
                // Sales Invoice - check Customer
                $customer = \App\Models\CompanyModule\Customer::find($invoice->customer);

                if (!$customer || $customer->Company_ID != $identifiers['company_id']) {
                    Log::error('❌ Customer does not belong to this company', [
                        'customer_id' => $invoice->customer,
                        'customer_company_id' => $customer->Company_ID ?? 'NULL',
                        'session_company_id' => $identifiers['company_id']
                    ]);
                    abort(403, 'Unauthorized access to this invoice');
                }
            } elseif ($invoice->customer_type === \App\Models\Supplier::class) {
                // Purchase Invoice - check Supplier
                $supplier = \App\Models\Supplier::find($invoice->customer);

                if (!$supplier) {
                    Log::error('❌ Supplier not found', ['supplier_id' => $invoice->customer]);
                    abort(403, 'Supplier not found');
                }

                // Check if supplier creator has access to this company
                $supplierCreatorHasAccess = DB::table('company_module_users')
                    ->where('company_id', $identifiers['company_id'])
                    ->where('user_id', $supplier->user_id)
                    ->exists();

                if (!$supplierCreatorHasAccess) {
                    Log::error('❌ Supplier creator does not have access to this company', [
                        'supplier_id' => $supplier->id,
                        'supplier_user_id' => $supplier->user_id,
                        'company_id' => $identifiers['company_id']
                    ]);
                    abort(403, 'Unauthorized access to this purchase');
                }
            } else {
                Log::error('❌ Invalid customer type for company invoice', [
                    'customer_type' => $invoice->customer_type
                ]);
                abort(403, 'Invalid invoice type');
            }

            return true;
        }

        // ========================================
        // CLIENT CONTEXT ACCESS CHECK
        // ========================================

        if ($invoice->customer_type === \App\Models\Supplier::class) {
            // ✅ PURCHASE: Check via creator's Client_ID
            $creator = \App\Models\User::find($invoice->created_by);

            if (!$creator || $creator->Client_ID != $identifiers['client_id']) {
                Log::error('❌ Purchase invoice creator does not belong to this client', [
                    'invoice_id' => $invoice->id,
                    'created_by' => $invoice->created_by,
                    'creator_client_id' => $creator->Client_ID ?? 'NULL',
                    'expected_client_id' => $identifiers['client_id']
                ]);
                abort(403, 'Unauthorized access to this purchase');
            }

            // ✅ Also verify supplier belongs to same client
            $supplier = \App\Models\Supplier::find($invoice->customer);

            if ($supplier) {
                $supplierUser = \App\Models\User::find($supplier->user_id);

                if (!$supplierUser || $supplierUser->Client_ID != $identifiers['client_id']) {
                    Log::error('❌ Supplier does not belong to this client', [
                        'supplier_id' => $supplier->id,
                        'supplier_user_id' => $supplier->user_id,
                        'supplier_client_id' => $supplierUser->Client_ID ?? 'NULL',
                        'expected_client_id' => $identifiers['client_id']
                    ]);
                    abort(403, 'Unauthorized access to this purchase');
                }
            }

            return true;
        }

        // ✅ SALES: Check via customerFile's Client_ID
        if (!$invoice->customerFile || $invoice->customerFile->Client_ID != $identifiers['client_id']) {
            Log::error('❌ Sales invoice does not belong to this client', [
                'invoice_id' => $invoice->id,
                'has_customer_file' => (bool)$invoice->customerFile,
                'customer_file_client_id' => $invoice->customerFile->Client_ID ?? 'NULL',
                'expected_client_id' => $identifiers['client_id']
            ]);
            abort(403, 'Unauthorized access to this invoice');
        }

        return true;
    }

    /**
     * ✅ FIXED: Check if user has access to this draft invoice
     */
    protected function checkDraftInvoiceAccess($draftInvoice)
    {
        $identifiers = $this->getContextIdentifiers();

        if ($identifiers['context'] === 'company') {
            // ✅ Company context: client_id field contains company_id
            if ($draftInvoice->client_id != $identifiers['company_id']) {
                abort(403, 'Unauthorized access to this invoice');
            }
            return true;
        }

        // Client context: client_id field contains actual client_id
        if ($draftInvoice->client_id != $identifiers['client_id']) {
            abort(403, 'Unauthorized access to this invoice');
        }

        return true;
    }

    /**
     * Helper: Detect payment type from invoice number
     */
    protected function getPaymentTypeFromInvoiceNo($invoiceNo): string
    {
        $prefix = strtoupper(substr($invoiceNo, 0, 3));

        $map = [
            'SIN' => 'sales_invoice',
            'SCN' => 'sales_credit',
            'PUR' => 'purchase',
            'PUC' => 'purchase_credit',
        ];

        return $map[$prefix] ?? 'sales_invoice';
    }

    /**
     * Activity log index page
     */
    public function activityLogIndex()
    {
        // Determine context
        $identifiers = $this->getContextIdentifiers();
        $isCompanyModule = ($identifiers['context'] === 'company');

        return view($this->getViewPath() . '.activity-list', [
            'typeName' => $this->getTypeName(),
            'routePrefix' => $this->getRoutePrefix(),
            'isCompanyModule' => $isCompanyModule,
        ]);
    }

    /**
     * Get ALL activity logs for current client OR company
     * ✅ UPDATED to support both contexts
     */
    public function getAllInvoiceActivityLogs()
    {
        try {
            $identifiers = $this->getContextIdentifiers();

            // Build query based on context
            $query = Invoice::query();

            if ($identifiers['context'] === 'company') {
                // ✅ Company context: Filter by company_id through customers
                $query->where('customer_type', \App\Models\CompanyModule\Customer::class)
                    ->whereIn('customer', function ($subquery) use ($identifiers) {
                        $subquery->select('id')
                            ->from('customers')
                            ->where('Company_ID', $identifiers['company_id']);
                    });
            } else {
                // Client context: Filter by client_id
                $query->whereHas('customerFile', function ($q) use ($identifiers) {
                    $q->where('Client_ID', $identifiers['client_id']);
                });
            }

            // Apply type filter (SIN/SCN or PUR/PUC)
            $query = $this->getInvoiceTypeFilter($query);

            $invoiceIds = $query->pluck('id');

            // Get activity logs
            $activities = InvoiceActivityLog::whereIn('invoice_id', $invoiceIds)
                ->with(['user', 'invoice'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($activity) {
                    return [
                        'id' => $activity->id,
                        'invoice_id' => $activity->invoice_id,
                        'invoice_no' => $activity->invoice->invoice_no ?? 'N/A',
                        'invoice_status' => $activity->invoice->status ?? null,
                        'action' => $activity->action,
                        'user_id' => $activity->user_id,
                        'user_name' => $activity->user ? $activity->user->Full_Name : 'System',
                        'user_email' => $activity->user ? $activity->user->email : null,
                        'old_values' => $activity->old_values,
                        'new_values' => $activity->new_values,
                        'notes' => $activity->notes,
                        'ip_address' => $activity->ip_address,
                        'user_agent' => $activity->user_agent,
                        'created_at' => $activity->created_at->toIso8601String(),
                    ];
                });

            return response()->json([
                'success' => true,
                'activities' => $activities,
                'total_count' => $activities->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch all activity logs', [
                'context' => $identifiers['context'] ?? 'unknown',
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load activity logs: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Get invoice data for PDF generation (reuses preview route)
     */
    public function getInvoiceData($id)
    {
        try {
            $identifiers = $this->getContextIdentifiers();

            $contextId = $identifiers['context'] === 'company'
                ? $identifiers['company_id']
                : $identifiers['client_id'];

            $draftInvoice = DraftInvoice::where('invoice_id', $id)
                ->where('client_id', $contextId)
                ->firstOrFail();

            // ✅ Check access
            $this->checkDraftInvoiceAccess($draftInvoice);

            $invoiceData = $draftInvoice->invoice_data;
            if (is_string($invoiceData)) {
                $invoiceData = json_decode($invoiceData, true);
            }

            return response()->json([
                'success' => true,
                'invoice_data' => $invoiceData,
                'template_id' => $draftInvoice->template_id ?? null, // If you store template_id
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get invoice data', [
                'invoice_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load invoice data: ' . $e->getMessage()
            ], 500);
        }
    }
}
