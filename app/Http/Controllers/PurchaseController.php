<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PurchaseController extends BaseInvoiceController
{
    /**
     * ✅ Override index to load supplier instead of customerFile
     */
    public function index(Request $request)
    {
        try {
            $activeTab = $request->get('tab', 'issued');
            $queryType = $request->get('type');
            $controllerType = $this->getTypeName() === 'Purchase' ? 'purchase' : 'sales';
            $type = $queryType ?: $controllerType;

            // ✅ Load 'supplier' relationship instead of 'customerFile'
            $query = Invoice::with([
                'supplier:id,contact_name,first_name,last_name,account_number,reference,company_id',
                'documents',
            ])->whereNotNull('customer');

            $query = $this->getInvoiceTypeFilter($query);
            $query = $this->applyContextFilter($query);

            if ($activeTab === 'drafts') {
                $query->where('status', Invoice::STATUS_DRAFT);
            } else {
                $query->where('status', '!=', Invoice::STATUS_DRAFT);
            }

            $invoices = $query->orderByDesc('invoice_date')
                ->orderByDesc('id')
                ->paginate(20);

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
     * ✅ Filter for PURCHASE invoices only (PUR, PUC)
     */
    protected function getInvoiceTypeFilter($query)
    {
        return $query->where(function ($q) {
            $q->where('invoice_no', 'LIKE', 'PUR%')
                ->orWhere('invoice_no', 'LIKE', 'PUC%');
        });
    }

    /**
     * ✅ COMPLETELY FIXED: Context filter for PURCHASES
     * 
     * COMPANY MODULE: suppliers with company_id = session_company_id
     * MAIN APP: suppliers with company_id = NULL and user_id from same Client_ID
     */
    protected function applyContextFilter($query)
    {
        $identifiers = $this->getContextIdentifiers();

        Log::info('🔍 PurchaseController: Applying context filter', [
            'context' => $identifiers['context'],
            'company_id' => $identifiers['company_id'] ?? null,
            'client_id' => $identifiers['client_id'] ?? null
        ]);

        if ($identifiers['context'] === 'company') {
            // ========================================
            // ✅ COMPANY MODULE PURCHASES
            // ========================================
            // Filter: customer_type = Supplier AND suppliers.company_id = current_company_id
            
            Log::info('🏢 Company Module: Filtering purchases by company suppliers', [
                'company_id' => $identifiers['company_id']
            ]);

            return $query->where('customer_type', Supplier::class)
                ->whereIn('customer', function ($subquery) use ($identifiers) {
                    $subquery->select('id')
                        ->from('suppliers')
                        ->where('company_id', $identifiers['company_id']);
                });
        } else {
            // ========================================
            // ✅ MAIN APP PURCHASES
            // ========================================
            // Filter: customer_type = Supplier 
            //     AND suppliers.company_id IS NULL 
            //     AND suppliers.user_id belongs to users with Client_ID = current_user_Client_ID
            
            Log::info('👤 Main App: Filtering purchases by client suppliers', [
                'client_id' => $identifiers['client_id']
            ]);

            return $query->where('customer_type', Supplier::class)
                ->whereIn('customer', function ($subquery) use ($identifiers) {
                    $subquery->select('id')
                        ->from('suppliers')
                        ->whereNull('company_id')  // ✅ Main app suppliers only
                        ->whereIn('user_id', function ($userQuery) use ($identifiers) {
                            $userQuery->select('User_ID')
                                ->from('user')
                                ->where('Client_ID', $identifiers['client_id']);
                        });
                });
        }
    }

    /**
     * ✅ COMPLETELY FIXED: Check invoice access for purchases
     */
    protected function checkInvoiceAccess($invoice)
    {
        $identifiers = $this->getContextIdentifiers();

        Log::info('🔍 Purchase: Checking invoice access', [
            'invoice_id' => $invoice->id,
            'context' => $identifiers['context'],
            'company_id' => $identifiers['company_id'] ?? null,
            'client_id' => $identifiers['client_id'] ?? null,
            'invoice_customer_type' => $invoice->customer_type,
            'invoice_customer' => $invoice->customer,
        ]);

        // ✅ ALL purchase invoices must use Supplier model
        if ($invoice->customer_type !== Supplier::class) {
            Log::error('❌ Purchase invoice must use Supplier model', [
                'invoice_customer_type' => $invoice->customer_type
            ]);
            abort(403, 'Invalid purchase invoice type');
        }

        $supplier = Supplier::find($invoice->customer);

        if (!$supplier) {
            Log::error('❌ Supplier not found', ['supplier_id' => $invoice->customer]);
            abort(403, 'Supplier not found');
        }

        if ($identifiers['context'] === 'company') {
            // ========================================
            // ✅ COMPANY MODULE: Check supplier belongs to company
            // ========================================
            if ($supplier->company_id != $identifiers['company_id']) {
                Log::error('❌ Supplier belongs to different company', [
                    'supplier_company_id' => $supplier->company_id,
                    'session_company_id' => $identifiers['company_id']
                ]);
                abort(403, 'Unauthorized access to this purchase');
            }

            Log::info('✅ Company purchase access granted', [
                'invoice_id' => $invoice->id,
                'supplier_id' => $supplier->id,
                'supplier_name' => $supplier->display_name,
                'supplier_company_id' => $supplier->company_id
            ]);

            return true;
        } else {
            // ========================================
            // ✅ MAIN APP: Check supplier is main app (company_id = NULL) 
            //              AND belongs to same Client_ID
            // ========================================
            
            // Check 1: Supplier must be main app (no company_id)
            if (!is_null($supplier->company_id)) {
                Log::error('❌ Main app cannot access company suppliers', [
                    'supplier_id' => $supplier->id,
                    'supplier_company_id' => $supplier->company_id
                ]);
                abort(403, 'Unauthorized access to company supplier');
            }

            // Check 2: Supplier user's Client_ID must match
            $supplierUser = \App\Models\User::find($supplier->user_id);

            if (!$supplierUser || $supplierUser->Client_ID != $identifiers['client_id']) {
                Log::error('❌ Supplier does not belong to this client', [
                    'supplier_id' => $supplier->id,
                    'supplier_user_id' => $supplier->user_id,
                    'supplier_client_id' => $supplierUser->Client_ID ?? 'NULL',
                    'expected_client_id' => $identifiers['client_id']
                ]);
                abort(403, 'Unauthorized access to this purchase invoice');
            }

            Log::info('✅ Main app purchase access granted', [
                'invoice_id' => $invoice->id,
                'supplier_id' => $supplier->id,
                'supplier_company_id' => $supplier->company_id,
                'supplier_user_client_id' => $supplierUser->Client_ID
            ]);

            return true;
        }
    }

    protected function getTypeName(): string
    {
        return 'Purchase';
    }

    protected function getRoutePrefix(): string
    {
        return 'purchases';
    }

    protected function getViewPath(): string
    {
        return 'admin.purchases';
    }
}