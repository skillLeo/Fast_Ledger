<?php

namespace App\Http\Requests\CompanyModule;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // Identity
            'Customer_Type' => 'required|in:Individual,Company',
            'Legal_Name_Company_Name' => 'required|string|max:255',
            
            // Tax Identification
            'Tax_ID_Type' => 'required|in:NIF,CIF,NIE,EU_VAT',
            'Tax_ID_Number' => 'required|string|max:50|unique:customers,Tax_ID_Number',
            
            // Address (Mandatory)
            'Street_Address' => 'required|string|max:255',
            'City' => 'required|string|max:100',
            'Postal_Code' => 'required|string|max:20',
            'Province' => 'required|string|max:100',
            'Country' => 'required|string|max:100',
            
            // Contact Information
            'Email' => 'required|email|max:255',
            'Phone' => 'nullable|string|max:30',
            'Contact_Person_Name' => 'nullable|required_if:Customer_Type,Company|string|max:255',
            
            // Tax Configuration
            'Has_VAT' => 'required|boolean',
            'VAT_Rate' => 'nullable|required_if:Has_VAT,1|in:Standard_21,Reduced_10,Super_Reduced_4,Exempt_0,Intra_EU,Export',
            
            'Has_IRPF' => 'required|boolean',
            'IRPF_Rate' => 'nullable|required_if:Has_IRPF,1|in:7,15',
            
            // Payment Settings
            'Payment_Method' => 'required|in:Bank_Transfer,Cash',
            'IBAN' => 'nullable|required_if:Payment_Method,Bank_Transfer|string|max:50',
            'Bank_Name' => 'nullable|string|max:255',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'Customer_Type' => 'customer type',
            'Legal_Name_Company_Name' => 'legal name/company name',
            'Tax_ID_Type' => 'tax identification type',
            'Tax_ID_Number' => 'tax ID number',
            'Street_Address' => 'street address',
            'City' => 'city',
            'Postal_Code' => 'postal code',
            'Province' => 'province',
            'Country' => 'country',
            'Email' => 'email',
            'Phone' => 'phone',
            'Contact_Person_Name' => 'contact person name',
            'Has_VAT' => 'VAT status',
            'VAT_Rate' => 'VAT rate',
            'Has_IRPF' => 'IRPF status',
            'IRPF_Rate' => 'IRPF rate',
            'Payment_Method' => 'payment method',
            'IBAN' => 'IBAN',
            'Bank_Name' => 'bank name',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'Tax_ID_Number.unique' => 'This tax ID number is already registered.',
            'Contact_Person_Name.required_if' => 'Contact person name is required for companies.',
            'VAT_Rate.required_if' => 'Please select a VAT rate when VAT is enabled.',
            'IRPF_Rate.required_if' => 'Please select an IRPF rate when IRPF is enabled.',
            'IBAN.required_if' => 'IBAN is required when payment method is Bank Transfer.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'Has_VAT' => $this->boolean('Has_VAT'),
            'Has_IRPF' => $this->boolean('Has_IRPF'),
        ]);
    }
}