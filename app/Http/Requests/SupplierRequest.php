<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SupplierRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled in controller
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $supplierId = $this->route('supplier'); // For update

        return [
            // Contact Information (Required)
            'contact_name' => 'required|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            
            // Primary Person
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'company_reg_no' => 'nullable|string|max:255',
            
            // Addresses
            'billing_address' => 'nullable|string',
            'delivery_address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            
            // Financial Details
            'bank_account_name' => 'nullable|string|max:255',
            'sort_code' => 'nullable|string|max:20',
            'bank_account_number' => 'nullable|string|max:50',
            'reference' => 'nullable|string|max:255',
            
            // VAT Details
            'vat_number' => 'nullable|string|max:255',
            'vat_status' => 'nullable|in:registered,not_registered',
            'tax_id' => 'nullable|string|max:255',
            'currency' => 'nullable|in:USD,EUR,GBP',
            
            // Business Details
            'business_type' => 'nullable|in:manufacturer,distributor,wholesaler,retailer',
            'industry' => 'nullable|string|max:255',
            'established_date' => 'nullable|date',
            'employee_count' => 'nullable|integer|min:0',
            
            // Payment Terms
            'payment_terms' => 'nullable|in:net_15,net_30,net_60,net_90',
            'credit_limit' => 'nullable|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'payment_method' => 'nullable|in:bank_transfer,check,cash,credit_card',
            
            // Status & Rating
            'status' => 'nullable|in:active,inactive,pending,suspended',
            'rating' => 'nullable|integer|min:1|max:5',
            'preferred_supplier' => 'nullable|boolean',
            'last_order_date' => 'nullable|date',
            
            // Additional Notes
            'notes' => 'nullable|string|max:4000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'contact_name.required' => 'Contact name is required',
            'email.email' => 'Please enter a valid email address',
            'website.url' => 'Please enter a valid website URL',
            'credit_limit.min' => 'Credit limit must be positive',
            'discount_percentage.max' => 'Discount cannot exceed 100%',
            'rating.min' => 'Rating must be between 1 and 5',
            'rating.max' => 'Rating must be between 1 and 5',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert preferred_supplier to boolean
        if ($this->has('preferred_supplier')) {
            $this->merge([
                'preferred_supplier' => filter_var($this->preferred_supplier, FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }
}