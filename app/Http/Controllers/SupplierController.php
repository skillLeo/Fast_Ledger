<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\SupplierRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SupplierController extends Controller
{
    /**
     * ✅ Determine context: Main App (client) or Company Module
     */
    protected function getContextIdentifiers(): array
    {
        $user = auth()->user();

        // Check if it's a company route
        $isCompanyRoute = request()->routeIs('company.*');

        if ($isCompanyRoute) {
            $companyId = session('current_company_id');

            if (!$companyId) {
                abort(redirect()->route('company.select')->with('error', 'Please select a company first.'));
            }

            // Check if user has access to this company
            $hasAccess = DB::table('company_module_users')
                ->where('Company_ID', $companyId)
                ->where('User_ID', $user->User_ID)
                ->where('Is_Active', true)
                ->exists();

            if (!$hasAccess) {
                abort(403, 'You do not have access to this company.');
            }

            return [
                'user_id' => (int) $user->User_ID,
                'context_id' => (int) $companyId,
                'context' => 'company'
            ];
        }

        // Main app context - client based
        if (!$user->Client_ID) {
            throw new \Exception('No client associated with user.');
        }

        return [
            'user_id' => (int) $user->User_ID,
            'context_id' => (int) $user->Client_ID,
            'context' => 'client'
        ];
    }

    /**
     * ✅ Apply context filter to query
     */
    protected function applyContextFilter($query)
    {
        $identifiers = $this->getContextIdentifiers();

        // Filter by user_id
        $query->where('user_id', $identifiers['user_id']);

        // ✅ CRITICAL: Filter by context
        if ($identifiers['context'] === 'company') {
            // Company module: Only show suppliers for THIS company
            $query->where('company_id', $identifiers['context_id']);
        } else {
            // Main app: Only show suppliers WITHOUT company_id
            $query->whereNull('company_id');
        }

        return $query;
    }

    /**
     * Display a listing of suppliers
     */
    public function index()
    {
        try {
            $identifiers = $this->getContextIdentifiers();

            $suppliers = Supplier::where('user_id', $identifiers['user_id'])
                ->when($identifiers['context'] === 'company', function ($query) use ($identifiers) {
                    // Company module: Filter by company_id
                    $query->where('company_id', $identifiers['context_id']);
                }, function ($query) {
                    // Main app: Only suppliers without company_id
                    $query->whereNull('company_id');
                })
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            $isCompanyModule = ($identifiers['context'] === 'company');

            Log::info('✅ Suppliers index loaded', [
                'context' => $identifiers['context'],
                'user_id' => $identifiers['user_id'],
                'context_id' => $identifiers['context_id'],
                'suppliers_count' => $suppliers->total()
            ]);

            return view('admin.suppliers.index', [
                'suppliers' => $suppliers,
                'isCompanyModule' => $isCompanyModule,
            ]);
        } catch (\Exception $e) {
            Log::error('Supplier index error', [
                'error' => $e->getMessage(),
                'user' => auth()->id()
            ]);

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new supplier
     */
    public function create()
    {
        try {
            $identifiers = $this->getContextIdentifiers();
            $isCompanyModule = ($identifiers['context'] === 'company');

            return view('admin.suppliers.create', [
                'isCompanyModule' => $isCompanyModule,
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * ✅ Store a newly created supplier
     */
    public function store(SupplierRequest $request)
    {

        try {
            DB::beginTransaction();
            $identifiers = $this->getContextIdentifiers();

            $data = $request->validated();
            $data['user_id'] = $identifiers['user_id'];

            // ✅ CRITICAL: Set company_id for company module
            if ($identifiers['context'] === 'company') {
                $data['company_id'] = $identifiers['context_id'];
            } else {
                // Main app: Explicitly set to NULL
                $data['company_id'] = null;
            }

            $supplier = Supplier::create($data);

            DB::commit();

            Log::info('✅ Supplier created successfully', [
                'supplier_id' => $supplier->id,
                'context' => $identifiers['context'],
                'user_id' => $identifiers['user_id'],
                'company_id' => $supplier->company_id
            ]);

            $route = $identifiers['context'] === 'company'
                ? 'company.suppliers.index'
                : 'suppliers.index';

            return redirect()
                ->route($route)
                ->with('success', 'Supplier created successfully');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Supplier creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->all()
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create supplier: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified supplier
     */
    public function show($id)
    {
        try {
            $supplier = Supplier::findOrFail($id);

            // Check access
            $this->checkSupplierAccess($supplier);

            $identifiers = $this->getContextIdentifiers();
            $isCompanyModule = ($identifiers['context'] === 'company');

            return view('admin.suppliers.show', [
                'supplier' => $supplier,
                'isCompanyModule' => $isCompanyModule,
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Supplier not found');
        }
    }

    /**
     * Show the form for editing the specified supplier
     */
    public function edit($id)
    {
        try {
            $supplier = Supplier::findOrFail($id);

            // Check access
            $this->checkSupplierAccess($supplier);

            $identifiers = $this->getContextIdentifiers();
            $isCompanyModule = ($identifiers['context'] === 'company');

            return view('admin.suppliers.create', [
                'supplier' => $supplier,
                'isCompanyModule' => $isCompanyModule,
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Supplier not found');
        }
    }

    /**
     * Update the specified supplier
     */
    public function update(SupplierRequest $request, $id)
    {
        try {
            DB::beginTransaction();

            $supplier = Supplier::findOrFail($id);

            // Check access
            $this->checkSupplierAccess($supplier);

            $data = $request->validated();

            // ✅ IMPORTANT: Don't allow changing company_id or user_id during update
            unset($data['company_id'], $data['user_id']);

            $supplier->update($data);

            DB::commit();

            Log::info('✅ Supplier updated successfully', [
                'supplier_id' => $supplier->id,
                'user_id' => $supplier->user_id,
                'company_id' => $supplier->company_id
            ]);

            $identifiers = $this->getContextIdentifiers();
            $route = $identifiers['context'] === 'company'
                ? 'company.suppliers.index'
                : 'suppliers.index';

            return redirect()
                ->route($route)
                ->with('success', 'Supplier updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Supplier update failed', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update supplier: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified supplier
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $supplier = Supplier::findOrFail($id);

            // Check access
            $this->checkSupplierAccess($supplier);

            $supplier->delete();

            DB::commit();

            Log::info('✅ Supplier deleted successfully', [
                'supplier_id' => $id,
                'user_id' => $supplier->user_id,
                'company_id' => $supplier->company_id
            ]);

            return redirect()
                ->back()
                ->with('success', 'Supplier deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Supplier deletion failed', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return redirect()
                ->back()
                ->with('error', 'Failed to delete supplier: ' . $e->getMessage());
        }
    }

    /**
     * ✅ Check if user has access to this supplier
     */
    protected function checkSupplierAccess($supplier)
    {
        $identifiers = $this->getContextIdentifiers();

        // ✅ CRITICAL: Check BOTH user_id AND context
        if ($supplier->user_id != $identifiers['user_id']) {
            Log::warning('❌ Unauthorized supplier access attempt - user mismatch', [
                'supplier_user_id' => $supplier->user_id,
                'current_user_id' => $identifiers['user_id']
            ]);
            abort(403, 'Unauthorized access to this supplier');
        }

        // ✅ CRITICAL: Verify context matches
        if ($identifiers['context'] === 'company') {
            // Company module: Supplier MUST belong to current company
            if ($supplier->company_id != $identifiers['context_id']) {
                Log::warning('❌ Unauthorized supplier access attempt - company mismatch', [
                    'supplier_company_id' => $supplier->company_id,
                    'current_company_id' => $identifiers['context_id']
                ]);
                abort(403, 'This supplier does not belong to the current company');
            }
        } else {
            // Main app: Supplier MUST NOT have a company_id
            if ($supplier->company_id !== null) {
                Log::warning('❌ Unauthorized supplier access attempt - main app accessing company supplier', [
                    'supplier_company_id' => $supplier->company_id
                ]);
                abort(403, 'Cannot access company suppliers from main app');
            }
        }

        return true;
    }


    /**
     * ✅ Get suppliers dropdown for invoices (AJAX endpoint)
     * Works for both Main App and Company Module
     */
    /**
     * ✅ Get suppliers dropdown for invoices (AJAX endpoint)
     * Works for both Main App and Company Module
     */
    public function getSuppliersDropdown(Request $request)
    {
        try {
            $userId = auth()->id();

            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            // ✅ Check if in company module context
            $companyId = session('current_company_id');
            $isCompanyModule = !is_null($companyId);

            Log::info('📡 Suppliers dropdown request', [
                'user_id' => $userId,
                'company_id' => $companyId,
                'context' => $isCompanyModule ? 'company_module' : 'main_app',
                'request_path' => $request->path()
            ]);

            // ✅ Build query based on context
            $query = Supplier::where('user_id', $userId);

            if ($isCompanyModule) {
                // Company Module: Filter by company_id
                $query->where('company_id', $companyId);
                Log::info('🏢 Loading suppliers for company', ['company_id' => $companyId]);
            } else {
                // Main App: Only suppliers with no company_id
                $query->whereNull('company_id');
                Log::info('👤 Loading suppliers for main app (user-level)');
            }

            $suppliers = $query->select(
                'id',
                'contact_name',
                'first_name',
                'last_name',
                'email',
                'phone',
                'account_number',
                'company_id'
            )
                ->orderBy('contact_name')
                ->get();

            // ✅ Format response
            $formattedSuppliers = $suppliers->map(function ($supplier) {
                return [
                    'id' => $supplier->id,
                    'contact_name' => $supplier->contact_name,
                    'first_name' => $supplier->first_name,
                    'last_name' => $supplier->last_name,
                    'display_name' => $supplier->contact_name ?: trim($supplier->first_name . ' ' . $supplier->last_name),
                    'account_number' => $supplier->account_number,
                    'email' => $supplier->email,
                    'phone' => $supplier->phone,
                    'company_id' => $supplier->company_id
                ];
            });

            Log::info('✅ Suppliers loaded', [
                'count' => $suppliers->count(),
                'context' => $isCompanyModule ? 'company_module' : 'main_app',
                'first_supplier' => $formattedSuppliers->first()
            ]);

            return response()->json([
                'success' => true,
                'suppliers' => $formattedSuppliers,
                'count' => $suppliers->count(),
                'context' => $isCompanyModule ? 'company_module' : 'main_app',
                'company_id' => $companyId
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Failed to load suppliers dropdown', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'company_id' => session('current_company_id')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load suppliers: ' . $e->getMessage()
            ], 500);
        }
    }
}
