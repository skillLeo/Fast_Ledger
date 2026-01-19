<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [];
        
        // Get current product ID for update (if exists)
        $productId = $this->route('id');
        
        // Check which categories are being created/updated
        $createPurchase = $this->has('create_purchase') && $this->input('create_purchase');
        $createSales = $this->has('create_sales') && $this->input('create_sales');

        // At least one category must be selected
        if (!$createPurchase && !$createSales) {
            $rules['create_purchase'] = 'required_without:create_sales|boolean';
            $rules['create_sales'] = 'required_without:create_purchase|boolean';
        }

        // ========================================
        // COMMON FIELDS (ALWAYS REQUIRED)
        // ========================================
        $rules['item_code'] = [
            'required',
            'string',
            'max:50',
            // Unique within client (excluding current product if updating)
            Rule::unique('products', 'item_code')
                ->where('client_id', auth()->user()->Client_ID)
                ->ignore($productId)
        ];

        $rules['name'] = 'required|string|max:255';
        
        $rules['item_image'] = $this->isMethod('POST') 
            ? 'nullable|file|mimes:jpg,jpeg,png,gif,webp|max:5120'
            : 'nullable|file|mimes:jpg,jpeg,png,gif,webp|max:5120';

        // ========================================
        // PURCHASE PRODUCT RULES
        // ========================================
        if ($createPurchase) {
            $rules['purchase_description'] = 'required|string|max:1000';
            $rules['purchase_ledger_id'] = 'required|integer|exists:chart_of_accounts,id';
            $rules['purchase_account_ref'] = 'nullable|string|max:100';
            $rules['purchase_unit_amount'] = 'required|numeric|min:0';
            $rules['purchase_vat_rate_id'] = 'nullable|integer|exists:vat_form_labels,id';
        }

        // ========================================
        // SALES PRODUCT RULES
        // ========================================
        if ($createSales) {
            $rules['sales_description'] = 'required|string|max:1000';
            $rules['sales_ledger_id'] = 'required|integer|exists:chart_of_accounts,id';
            $rules['sales_account_ref'] = 'nullable|string|max:100';
            $rules['sales_unit_amount'] = 'required|numeric|min:0';
            $rules['sales_vat_rate_id'] = 'nullable|integer|exists:vat_form_labels,id';
        }

        return $rules;
    }

    /**
     * Get custom validation messages
     */
    public function messages(): array
    {
        return [
            // Common field messages
            'item_code.required' => 'Item code is required.',
            'item_code.unique' => 'This item code already exists for your account.',
            'name.required' => 'Item name is required.',
            'name.max' => 'Item name must not exceed 255 characters.',
            'item_image.mimes' => 'Item image must be a JPG, PNG, GIF, or WEBP file.',
            'item_image.max' => 'Item image must not exceed 5MB.',
            
            // Purchase messages
            'purchase_description.required' => 'Purchase description is required.',
            'purchase_ledger_id.required' => 'Purchase ledger is required.',
            'purchase_ledger_id.exists' => 'Selected purchase ledger does not exist.',
            'purchase_unit_amount.required' => 'Purchase unit amount is required.',
            'purchase_unit_amount.min' => 'Purchase unit amount must be at least 0.',
            'purchase_vat_rate_id.exists' => 'Selected purchase VAT rate does not exist.',
            
            // Sales messages
            'sales_description.required' => 'Sales description is required.',
            'sales_ledger_id.required' => 'Sales ledger is required.',
            'sales_ledger_id.exists' => 'Selected sales ledger does not exist.',
            'sales_unit_amount.required' => 'Sales unit amount is required.',
            'sales_unit_amount.min' => 'Sales unit amount must be at least 0.',
            'sales_vat_rate_id.exists' => 'Selected sales VAT rate does not exist.',
            
            // Category selection
            'create_purchase.required_without' => 'Please select at least one category (Purchase or Sales).',
            'create_sales.required_without' => 'Please select at least one category (Purchase or Sales).',
        ];
    }

    /**
     * Get custom attribute names for validation errors
     */
    public function attributes(): array
    {
        return [
            'item_code' => 'item code',
            'name' => 'item name',
            'item_image' => 'item image',
            
            'purchase_description' => 'purchase description',
            'purchase_ledger_id' => 'purchase ledger',
            'purchase_account_ref' => 'purchase account reference',
            'purchase_unit_amount' => 'purchase unit amount',
            'purchase_vat_rate_id' => 'purchase VAT rate',
            
            'sales_description' => 'sales description',
            'sales_ledger_id' => 'sales ledger',
            'sales_account_ref' => 'sales account reference',
            'sales_unit_amount' => 'sales unit amount',
            'sales_vat_rate_id' => 'sales VAT rate',
        ];
    }
}