<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AnnualSubmissionRequest extends FormRequest
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
        $taxYear = $this->input('tax_year');
        $taxYearNum = $taxYear ? (int) substr($taxYear, 0, 4) : null;

        // Check if trading allowance is being used (value > 0)
        $tradingAllowanceValue = $this->input('allowances.trading_income_allowance');
        $usingTradingAllowance = !empty($tradingAllowanceValue) && floatval($tradingAllowanceValue) > 0;

        $rules = [
            'business_id' => 'required|string',
            'tax_year' => 'required|string|regex:/^\d{4}-\d{2}$/',
            'nino' => 'nullable|string|regex:/^[A-Z]{2}[0-9]{6}[A-Z]$/',

            // Adjustments - Flat structure (all tax years)
            'adjustments.included_non_taxable_profits' => 'nullable|numeric|min:0|max:99999999999.99',
            'adjustments.basis_adjustment' => 'nullable|numeric|min:-99999999999.99|max:99999999999.99',
            'adjustments.overlap_relief_used' => 'nullable|numeric|min:0|max:99999999999.99',
            'adjustments.accounting_adjustment' => 'nullable|numeric|min:-99999999999.99|max:99999999999.99',
            'adjustments.averaging_adjustment' => 'nullable|numeric|min:-99999999999.99|max:99999999999.99',
            'adjustments.outstanding_business_income' => 'nullable|numeric|min:0|max:99999999999.99',
            'adjustments.balancing_charge_bpra' => 'nullable|numeric|min:0|max:99999999999.99',
            'adjustments.balancing_charge_other' => 'nullable|numeric|min:0|max:99999999999.99',
            'adjustments.goods_and_services_own_use' => 'nullable|numeric|min:0|max:99999999999.99',

            // Allowances - Trading Allowance (mutually exclusive with capital allowances)
            'allowances.trading_income_allowance' => 'nullable|numeric|min:0|max:1000',

            // Allowances - Capital Allowances (all tax years)
            'allowances.annual_investment_allowance' => $usingTradingAllowance ? 'nullable|max:0' : 'nullable|numeric|min:0|max:99999999999.99',
            'allowances.capital_allowance_main_pool' => $usingTradingAllowance ? 'nullable|max:0' : 'nullable|numeric|min:0|max:99999999999.99',
            'allowances.capital_allowance_special_rate_pool' => $usingTradingAllowance ? 'nullable|max:0' : 'nullable|numeric|min:0|max:99999999999.99',
            'allowances.business_premises_renovation_allowance' => $usingTradingAllowance ? 'nullable|max:0' : 'nullable|numeric|min:0|max:99999999999.99',
            'allowances.enhanced_capital_allowance' => $usingTradingAllowance ? 'nullable|max:0' : 'nullable|numeric|min:0|max:99999999999.99',
            'allowances.allowance_on_sales' => $usingTradingAllowance ? 'nullable|max:0' : 'nullable|numeric|min:0|max:99999999999.99',
            'allowances.capital_allowance_single_asset_pool' => $usingTradingAllowance ? 'nullable|max:0' : 'nullable|numeric|min:0|max:99999999999.99',
            'allowances.zero_emissions_car_allowance' => $usingTradingAllowance ? 'nullable|max:0' : 'nullable|numeric|min:0|max:99999999999.99',

            // Structured Building Allowances (arrays)
            'allowances.structured_building_allowance' => $usingTradingAllowance ? 'nullable|array|max:0' : 'nullable|array',
            'allowances.structured_building_allowance.*.amount' => 'sometimes|required|numeric|min:0|max:99999999999.99',
            'allowances.structured_building_allowance.*.first_year_qualifying_date' => 'nullable|date',
            'allowances.structured_building_allowance.*.first_year_qualifying_amount' => 'nullable|numeric|min:0|max:99999999999.99',
            'allowances.structured_building_allowance.*.building_name' => 'nullable|string|max:90',
            'allowances.structured_building_allowance.*.building_postcode' => 'nullable|string|max:10',

            // Enhanced Structured Building Allowances (arrays)
            'allowances.enhanced_structured_building_allowance' => $usingTradingAllowance ? 'nullable|array|max:0' : 'nullable|array',
            'allowances.enhanced_structured_building_allowance.*.amount' => 'sometimes|required|numeric|min:0|max:99999999999.99',
            'allowances.enhanced_structured_building_allowance.*.first_year_qualifying_date' => 'nullable|date',
            'allowances.enhanced_structured_building_allowance.*.first_year_qualifying_amount' => 'nullable|numeric|min:0|max:99999999999.99',
            'allowances.enhanced_structured_building_allowance.*.building_name' => 'nullable|string|max:90',
            'allowances.enhanced_structured_building_allowance.*.building_postcode' => 'nullable|string|max:10',

            // Non-financials
            'non_financials.business_address_line_1' => 'nullable|string|max:35',
            'non_financials.business_address_line_2' => 'nullable|string|max:35',
            'non_financials.business_address_line_3' => 'nullable|string|max:35',
            'non_financials.business_address_line_4' => 'nullable|string|max:35',
            'non_financials.business_address_postcode' => 'nullable|string|max:10',
            'non_financials.business_address_country_code' => 'nullable|string|size:2',
            'non_financials.class_4_nics_exemption_reason' => [
                'nullable',
                'string',
                'in:001,002,003,004,005,006',
                function ($attribute, $value, $fail) use ($usingTradingAllowance) {
                    if (!$usingTradingAllowance && $value) {
                        $fail('Class 4 NICs exemption reason can only be used with trading allowance.');
                    }
                }
            ],
            'non_financials.business_details_changed_recently' => 'nullable|boolean',

            // Notes
            'notes' => 'nullable|string|max:5000',

            // Test scenario for sandbox mode
            'test_scenario' => 'nullable|string'
        ];

        // Add TY 2024-25+ specific adjustment fields
        if ($taxYearNum && $taxYearNum >= 2024) {
            $rules['adjustments.transition_profit_amount'] = 'nullable|numeric|min:0|max:99999999999.99';
            $rules['adjustments.transition_profit_acceleration_amount'] = 'nullable|numeric|min:0|max:99999999999.99';
        }

        // Add TY ≤ 2024 specific allowance fields (removed in TY 2025-26+)
        if ($taxYearNum && $taxYearNum <= 2024) {
            $rules['allowances.zero_emissions_goods_vehicle_allowance'] = $usingTradingAllowance ? 'nullable|max:0' : 'nullable|numeric|min:0|max:99999999999.99';
            $rules['allowances.electric_charge_point_allowance'] = $usingTradingAllowance ? 'nullable|max:0' : 'nullable|numeric|min:0|max:99999999999.99';
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'business_id.required' => 'Please select a business.',
            'tax_year.required' => 'Please select a tax year.',
            'tax_year.regex' => 'Tax year must be in format YYYY-YY (e.g., 2023-24).',
            'nino.regex' => 'NINO must be in format AA123456A.',
            '*.numeric' => 'This field must be a valid number.',
            '*.min' => 'This field must be at least :min.',
            '*.max' => 'This field cannot exceed :max.',
            'non_financials.class_4_nics_exemption_reason.in' => 'Please select a valid exemption reason.',
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     */
    public function attributes(): array
    {
        return [
            // Adjustments
            'adjustments.included_non_taxable_profits' => 'included non-taxable profits',
            'adjustments.basis_adjustment' => 'basis adjustment',
            'adjustments.overlap_relief_used' => 'overlap relief used',
            'adjustments.accounting_adjustment' => 'accounting adjustment',
            'adjustments.averaging_adjustment' => 'averaging adjustment',
            'adjustments.outstanding_business_income' => 'outstanding business income',
            'adjustments.balancing_charge_bpra' => 'balancing charge BPRA',
            'adjustments.balancing_charge_other' => 'balancing charge other',
            'adjustments.goods_and_services_own_use' => 'goods and services own use',
            'adjustments.transition_profit_amount' => 'transition profit amount',
            'adjustments.transition_profit_acceleration_amount' => 'transition profit acceleration amount',

            // Allowances
            'allowances.trading_income_allowance' => 'trading income allowance',
            'allowances.annual_investment_allowance' => 'annual investment allowance',
            'allowances.capital_allowance_main_pool' => 'capital allowance main pool',
            'allowances.capital_allowance_special_rate_pool' => 'capital allowance special rate pool',
            'allowances.business_premises_renovation_allowance' => 'business premises renovation allowance',
            'allowances.enhanced_capital_allowance' => 'enhanced capital allowance',
            'allowances.allowance_on_sales' => 'allowance on sales',
            'allowances.capital_allowance_single_asset_pool' => 'capital allowance single asset pool',
            'allowances.zero_emissions_car_allowance' => 'zero emissions car allowance',
            'allowances.zero_emissions_goods_vehicle_allowance' => 'zero emissions goods vehicle allowance',
            'allowances.electric_charge_point_allowance' => 'electric charge point allowance',

            // Structured Building Allowances
            'allowances.structured_building_allowance.*.amount' => 'structured building allowance amount',
            'allowances.structured_building_allowance.*.first_year_qualifying_date' => 'qualifying date',
            'allowances.structured_building_allowance.*.first_year_qualifying_amount' => 'qualifying amount',
            'allowances.structured_building_allowance.*.building_name' => 'building name',
            'allowances.structured_building_allowance.*.building_postcode' => 'building postcode',

            // Enhanced Structured Building Allowances
            'allowances.enhanced_structured_building_allowance.*.amount' => 'enhanced structured building allowance amount',
            'allowances.enhanced_structured_building_allowance.*.first_year_qualifying_date' => 'qualifying date',
            'allowances.enhanced_structured_building_allowance.*.first_year_qualifying_amount' => 'qualifying amount',
            'allowances.enhanced_structured_building_allowance.*.building_name' => 'building name',
            'allowances.enhanced_structured_building_allowance.*.building_postcode' => 'building postcode',

            // Non-financials
            'non_financials.business_address_line_1' => 'address line 1',
            'non_financials.business_address_line_2' => 'address line 2',
            'non_financials.business_address_line_3' => 'address line 3',
            'non_financials.business_address_line_4' => 'address line 4',
            'non_financials.business_address_postcode' => 'postcode',
            'non_financials.business_address_country_code' => 'country code',
            'non_financials.class_4_nics_exemption_reason' => 'Class 4 NICs exemption reason',
            'non_financials.business_details_changed_recently' => 'business details changed recently',
        ];
    }
}
