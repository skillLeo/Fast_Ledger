<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Http\Requests\ProductRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * ✅ FIXED: Get context-based identifiers (handles both company users and client users)
     */
    private function getContextIdentifiers(): array
    {
        $user = auth()->user();
        $url = request()->path();

        // ✅ Priority 1: Check if we're in company module context
        // Look for session company_id OR URL pattern
        $companyId = session('current_company_id');

        if ($companyId || str_contains($url, 'company/')) {
            if (!$companyId) {
                throw new \Exception('No company selected. Please select a company first.');
            }

            // Verify user has access to this company
            if (!$user->hasAccessToCompany($companyId)) {
                throw new \Exception('You do not have access to this company.');
            }

            \Log::info('Product Context: Company Module', [
                'company_id' => $companyId,
                'user_id' => $user->User_ID,
                'url' => $url
            ]);

            return [
                'company_id' => (int) $companyId,
                'client_id' => null,
                'context' => 'company'
            ];
        }

        // ✅ Priority 2: Main App - Check if user has Client_ID
        if (!$user->Client_ID) {
            // Check if user is company-only user (has Role 4)
            $isCompanyUser = $user->hasRole(4);

            if ($isCompanyUser) {
                throw new \Exception('Company users must access products through the company module. Please select a company first.');
            }

            throw new \Exception('No client associated with user. Please contact administrator.');
        }

        \Log::info('Product Context: Main App', [
            'client_id' => $user->Client_ID,
            'user_id' => $user->User_ID,
            'url' => $url
        ]);

        return [
            'client_id' => (int) $user->Client_ID,
            'company_id' => null,
            'context' => 'client'
        ];
    }

    /**
     * Get products for dropdown (filtered by category and context)
     */
    public function getForDropdown(Request $request)
    {
        try {
            $category = $request->input('category');
            $identifiers = $this->getContextIdentifiers();

            if (!in_array($category, ['purchase', 'sales'])) {
                return $this->errorResponse('Invalid category specified', 400);
            }

            $query = Product::with(['ledger', 'vatRate'])
                ->where('category', $category)
                ->where('is_active', true);

            // ✅ Apply context-based filtering
            if ($identifiers['context'] === 'company') {
                $query->where('company_id', $identifiers['company_id']);
            } else {
                $query->where('client_id', $identifiers['client_id'])
                    ->whereNull('company_id');
            }

            $products = $query->orderBy('item_code')
                ->get()
                ->map(fn($product) => $this->formatProductData($product));

            return $this->successResponse($products, 'products');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error fetching products for dropdown');
        }
    }

    /**
     * Store new product(s) from modal
     */
    public function store(ProductRequest $request)
    {
        try {
            DB::beginTransaction();

            $createdProducts = [];
            $identifiers = $this->getContextIdentifiers();

            // ✅ Handle common image upload ONCE
            $imagePath = null;
            if ($request->hasFile('item_image')) {
                $uploadId = $identifiers['company_id'] ?? $identifiers['client_id'];
                $imagePath = $this->uploadImage($request->file('item_image'), $uploadId, $identifiers['context']);
            }

            // ✅ Common fields (shared by both)
            $commonFields = [
                'item_code' => $request->input('item_code'),
                'name' => $request->input('name'),
                'file_path' => $imagePath,
            ];

            // Create Purchase Product
            if ($request->input('create_purchase')) {
                $purchaseProduct = $this->createProduct($request, 'purchase', $identifiers, $commonFields);
                $createdProducts[] = [
                    'category' => 'purchase',
                    'product' => $this->formatProductData($purchaseProduct)
                ];
            }

            // Create Sales Product
            if ($request->input('create_sales')) {
                $salesProduct = $this->createProduct($request, 'sales', $identifiers, $commonFields);
                $createdProducts[] = [
                    'category' => 'sales',
                    'product' => $this->formatProductData($salesProduct)
                ];
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Product(s) created successfully',
                'products' => $createdProducts
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e, 'Error creating product');
        }
    }

    /**
     * Get single product details
     */
    public function show($id)
    {
        try {
            $product = $this->findProduct($id);

            if (!$product) {
                return $this->errorResponse('Product not found', 404);
            }

            if (request()->ajax() || request()->wantsJson()) {
                return $this->successResponse($this->formatProductData($product), 'product');
            }

            return view('admin.products.show', compact('product'));
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error fetching product', ['product_id' => $id]);
        }
    }

    /**
     * Get all products (index page or JSON for AJAX)
     */
    public function index(Request $request)
    {
        try {
            $identifiers = $this->getContextIdentifiers();
            $category = $request->input('category');

            $query = Product::with(['ledger', 'vatRate'])
                ->where('is_active', true);

            // ✅ Apply context-based filtering
            if ($identifiers['context'] === 'company') {
                $query->where('company_id', $identifiers['company_id']);
            } else {
                $query->where('client_id', $identifiers['client_id'])
                    ->whereNull('company_id');
            }

            if ($category && in_array($category, ['purchase', 'sales'])) {
                $query->where('category', $category);
            }

            $products = $query->orderBy('item_code')->get();

            if ($request->ajax() || $request->wantsJson()) {
                return $this->successResponse($products, 'products');
            }

            return view('admin.products.index', compact('products'));
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error fetching products list');
        }
    }

    /**
     * Update product
     */
    public function update(ProductRequest $request, $id)
    {
        try {
            DB::beginTransaction();

            $product = $this->findProduct($id);

            if (!$product) {
                return $this->errorResponse('Product not found', 404);
            }

            $category = $product->category;
            $prefix = $category;

            $updateData = [
                'item_code' => $request->input('item_code'),
                'name' => $request->input('name'),
                'description' => $request->input("{$prefix}_description"),
                'ledger_id' => $request->input("{$prefix}_ledger_id"),
                'account_ref' => $request->input("{$prefix}_account_ref"),
                'unit_amount' => $request->input("{$prefix}_unit_amount"),
                'vat_rate_id' => $request->input("{$prefix}_vat_rate_id"),
            ];

            // ✅ Handle image update
            if ($request->hasFile('item_image')) {
                $identifiers = $this->getContextIdentifiers();
                $uploadId = $identifiers['company_id'] ?? $identifiers['client_id'];

                if ($product->file_path) {
                    Storage::disk('public')->delete($product->file_path);
                }
                $updateData['file_path'] = $this->uploadImage(
                    $request->file('item_image'),
                    $uploadId,
                    $identifiers['context']
                );
            }

            $product->update($updateData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully',
                'product' => $this->formatProductData($product->fresh(['ledger', 'vatRate']))
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e, 'Error updating product', ['product_id' => $id]);
        }
    }

    /**
     * Delete product (soft delete)
     */
    public function destroy($id)
    {
        try {
            $product = $this->findProduct($id);

            if (!$product) {
                return $this->errorResponse('Product not found', 404);
            }

            $product->delete();

            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully'
            ]);
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error deleting product', ['product_id' => $id]);
        }
    }

    // ========================================
    // PRIVATE HELPER METHODS
    // ========================================

    /**
     * ✅ Create a single product with context-based identifiers
     */
    private function createProduct(ProductRequest $request, string $category, array $identifiers, array $commonFields)
    {
        $prefix = $category;

        $productData = [
            // ✅ Context-based IDs
            'client_id' => $identifiers['client_id'],
            'company_id' => $identifiers['company_id'],
            'category' => $category,

            // Common fields
            'item_code' => $commonFields['item_code'],
            'name' => $commonFields['name'],
            'file_path' => $commonFields['file_path'],

            // Category-specific fields
            'description' => $request->input("{$prefix}_description"),
            'ledger_id' => $request->input("{$prefix}_ledger_id"),
            'account_ref' => $request->input("{$prefix}_account_ref"),
            'unit_amount' => $request->input("{$prefix}_unit_amount"),
            'vat_rate_id' => $request->input("{$prefix}_vat_rate_id"),
            'is_active' => true,
        ];

        $product = Product::create($productData);

        Log::info("Product created: {$category}", [
            'product_id' => $product->id,
            'item_code' => $product->item_code,
            'name' => $product->name,
            'context' => $identifiers['context'],
            'client_id' => $identifiers['client_id'],
            'company_id' => $identifiers['company_id']
        ]);

        return $product->load(['ledger', 'vatRate']);
    }

    /**
     * ✅ Upload product image to storage (context-aware path)
     */
    private function uploadImage($file, int $id, string $context): string
    {
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $folder = $context === 'company' ? "products/company_{$id}/images" : "products/client_{$id}/images";
        return $file->storeAs($folder, $filename, 'public');
    }

    /**
     * ✅ Find product by ID for current context
     */
    private function findProduct(int $id)
    {
        $identifiers = $this->getContextIdentifiers();

        $query = Product::with(['ledger', 'vatRate'])
            ->where('id', $id)
            ->where('is_active', true);

        // Apply context-based filtering
        if ($identifiers['context'] === 'company') {
            $query->where('company_id', $identifiers['company_id']);
        } else {
            $query->where('client_id', $identifiers['client_id'])
                ->whereNull('company_id');
        }

        return $query->first();
    }

    /**
     * Format product data for response
     */
    private function formatProductData($product): array
    {
        // ✅ NEW: Include vat_type_id for cross-form matching
        $vatTypeId = null;
        $vatPercentage = 0;
        $vatName = null;

        if ($product->vatRate) {
            $vatTypeId = $product->vatRate->vat_type_id; // ✅ Get master VAT type ID
            $vatPercentage = $product->vatRate->percentage ?? 0;
            $vatName = $product->vatRate->display_name ?? null;
        }

        return [
            'id' => $product->id,
            'category' => $product->category,
            'item_code' => $product->item_code,
            'name' => $product->name,
            'description' => $product->description,
            'ledger_id' => $product->ledger_id,
            'ledger_ref' => $product->ledger->ledger_ref ?? null,
            'account_ref' => $product->account_ref,
            'unit_amount' => $product->unit_amount,
            'vat_rate_id' => $product->vat_rate_id,        // ✅ vat_form_labels.id (form-specific)
            'vat_type_id' => $vatTypeId,                   // ✅ NEW: vattype.VAT_ID (master)
            'vat_percentage' => $vatPercentage,
            'vat_name' => $vatName,
            'file_path' => $product->file_path,
            'file_url' => $product->file_url,
            'display_name' => $product->display_name,
            'client_id' => $product->client_id,
            'company_id' => $product->company_id,
        ];
    }

    /**
     * Success response helper
     */
    private function successResponse($data, string $key)
    {
        return response()->json([
            'success' => true,
            $key => $data
        ]);
    }

    /**
     * Error response helper
     */
    private function errorResponse(string $message, int $code = 500)
    {
        return response()->json([
            'success' => false,
            'message' => $message
        ], $code);
    }

    /**
     * Exception handler helper
     */
    private function handleException(\Exception $e, string $context, array $extra = [])
    {
        Log::error($context, array_merge([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], $extra));

        return response()->json([
            'success' => false,
            'message' => 'An error occurred: ' . $e->getMessage()
        ], 500);
    }
}
