<?php
// app/Http/Requests/CompanyModule/StoreCompanyRequest.php

namespace App\Http\Requests\CompanyModule;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $rules = [
            // Basic Information
            'Company_Name' => 'required|string|max:255',
            'Trade_Name' => 'nullable|string|max:255',
            
            // Address
            'Street_Address' => 'required|string|max:255',
            'City' => 'required|string|max:100',
            'State' => 'nullable|string|max:100',
            'Postal_Code' => 'required|string|max:20',
            'Country' => 'required|string|size:2',
            
            // Tax & Legal
            'Tax_ID' => 'required|string|max:50|unique:company_module_companies,Tax_ID',
            'Country_Tax_Residence' => 'required|string|size:2',
            
            // Contact Information
            'Phone_Number' => 'nullable|string|max:50',
            'Email' => 'nullable|email|max:100',
            'Website' => 'nullable|url|max:255',
            
            // VERIFACTU Settings
            'Verifactu_Enabled' => 'nullable|boolean',
            'SIF_Identifier' => 'nullable|string|max:100',
            'Is_Test_Mode' => 'nullable|boolean',
            
            // Files
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'aeat_certificate' => 'nullable|file|mimes:pfx,pem|max:5120',
            
            // Invoice Settings
            'Invoice_Prefix' => 'nullable|string|max:10',// ❌ REMOVED: number_of_companies (it's in subscription form, not here)
        ];
    
        // Country-specific validation
        if ($this->input('Country') === 'ES') {
            $rules['Company_Type_ES'] = 'required|in:autonomo,sociedad_limitada,sociedad_anonima,cooperativa,sociedad_civil,comunidades_bienes,fundacion_asociacion,otra';
            $rules['Tax_Regime'] = 'nullable|in:regimen_general,regimen_simplificado,recargo_equivalencia,agricultura_ganaderia_pesca,grupo_iva,oss_ioss,estimacion_directa_objetiva,bienes_usados_arte_antiguos,otra';
        } elseif ($this->input('Country') === 'GB') {
            $rules['Company_Type_UK'] = 'required|in:sole_trader,private_limited_company,public_limited_company,limited_liability_partnership,partnership,community_interest_company,charity,overseas_company,other';
        }
    
        return $rules;
    }
    
    public function messages(): array
    {
        return [
            'Company_Name.required' => 'Company name is required',
            'Street_Address.required' => 'Street address is required',
            'City.required' => 'City is required',
            'Postal_Code.required' => 'Postal code is required',
            'Country.required' => 'Country is required',
            'Tax_ID.required' => 'Tax ID is required',
            'Tax_ID.unique' => 'This Tax ID is already registered',
            'Country_Tax_Residence.required' => 'Tax residence is required',
            'Company_Type_ES.required' => 'Company type is required for Spain',
            'Company_Type_UK.required' => 'Company type is required for UK',
        ];
    }
    
    protected function prepareForValidation(): void
    {
        $this->merge([
            'Verifactu_Enabled' => $this->has('Verifactu_Enabled') ? true : false,
            'Is_Test_Mode' => $this->has('Is_Test_Mode') ? true : false,
        ]);
    }}
