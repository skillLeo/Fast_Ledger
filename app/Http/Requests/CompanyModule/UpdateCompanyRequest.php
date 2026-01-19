<?php

namespace App\Http\Requests\CompanyModule;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCompanyRequest extends FormRequest
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
        $companyId = $this->route('company');

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
            'Tax_ID' => [
                'required',
                'string',
                'max:50',
                Rule::unique('company_module_companies', 'Tax_ID')->ignore($companyId)
            ],
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
            'Invoice_Prefix' => 'nullable|string|max:10',
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

    /**
     * Custom validation messages
     */
    public function messages(): array
    {
        return [
            'Company_Name.required' => 'Company name is required',
            'Tax_ID.unique' => 'This Tax ID is already registered',
        ];
    }

    /**
     * Prepare data for validation
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'Verifactu_Enabled' => $this->has('Verifactu_Enabled') ? true : false,
            'Is_Test_Mode' => $this->has('Is_Test_Mode') ? true : false,
        ]);
    }
}