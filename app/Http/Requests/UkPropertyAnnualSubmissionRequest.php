<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UkPropertyAnnualSubmissionRequest extends FormRequest
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
        $adjustmentRules = [
            'private_use_adjustment' => 'nullable|numeric|min:0|max:99999999999.99',
            'balancing_charge' => 'nullable|numeric|min:0|max:99999999999.99',
            'business_premises_renovation_allowance_balancing_charges' => 'nullable|numeric|min:0|max:99999999999.99',
            'period_of_grace_adjustment' => 'nullable|boolean',
            'non_resident_landlord' => 'nullable|boolean',
            'rent_a_room_jointly_let' => 'nullable|boolean',
        ];

        $allowanceRules = [
            'annual_investment_allowance' => 'nullable|numeric|min:0|max:99999999999.99',
            'business_premises_renovation_allowance' => 'nullable|numeric|min:0|max:99999999999.99',
            'other_capital_allowance' => 'nullable|numeric|min:0|max:99999999999.99',
            'cost_of_replacing_domestic_goods' => 'nullable|numeric|min:0|max:99999999999.99',
            'zero_emissions_car_allowance' => 'nullable|numeric|min:0|max:99999999999.99',
            'zero_emissions_goods_vehicle_allowance' => 'nullable|numeric|min:0|max:99999999999.99',
            'electric_charge_point_allowance' => 'nullable|numeric|min:0|max:99999999999.99',
            'property_income_allowance' => 'nullable|numeric|min:0|max:99999999999.99',
            'structured_building_allowance' => 'nullable|array',
            'structured_building_allowance.*.amount' => 'sometimes|required|numeric|min:0|max:99999999999.99',
            'structured_building_allowance.*.first_year_qualifying_date' => 'nullable|date',
            'structured_building_allowance.*.first_year_qualifying_amount' => 'nullable|numeric|min:0|max:99999999999.99',
            'structured_building_allowance.*.building_name' => 'nullable|string|max:255',
            'structured_building_allowance.*.building_number' => 'nullable|string|max:255',
            'structured_building_allowance.*.building_postcode' => 'nullable|string|max:10|regex:/^[A-Z]{1,2}[0-9]{1,2}[A-Z]?\s?[0-9][A-Z]{2}$/i',
            'enhanced_structured_building_allowance' => 'nullable|array',
            'enhanced_structured_building_allowance.*.amount' => 'sometimes|required|numeric|min:0|max:99999999999.99',
            'enhanced_structured_building_allowance.*.first_year_qualifying_date' => 'nullable|date',
            'enhanced_structured_building_allowance.*.first_year_qualifying_amount' => 'nullable|numeric|min:0|max:99999999999.99',
            'enhanced_structured_building_allowance.*.building_name' => 'nullable|string|max:255',
            'enhanced_structured_building_allowance.*.building_number' => 'nullable|string|max:255',
            'enhanced_structured_building_allowance.*.building_postcode' => 'nullable|string|max:10|regex:/^[A-Z]{1,2}[0-9]{1,2}[A-Z]?\s?[0-9][A-Z]{2}$/i',
        ];

        $rules = [
            'business_id' => 'required|string|regex:/^X[A-Z0-9]{1}IS[0-9]{11}$/',
            'tax_year' => 'required|string|regex:/^\d{4}-\d{2}$/',
            'nino' => 'nullable|string|regex:/^[A-Z]{2}[0-9]{6}[A-Z]$/',

            // Notes
            'notes' => 'nullable|string|max:5000'
        ];

        // Add rules for FHL adjustments/allowances (TY 2024-25 and before)
        foreach ($adjustmentRules as $key => $rule) {
            $rules["fhl_adjustments.{$key}"] = $rule;
        }
        foreach ($allowanceRules as $key => $rule) {
            $rules["fhl_allowances.{$key}"] = $rule;
        }

        // Add rules for Non-FHL adjustments/allowances (all tax years)
        foreach ($adjustmentRules as $key => $rule) {
            $rules["non_fhl_adjustments.{$key}"] = $rule;
        }
        foreach ($allowanceRules as $key => $rule) {
            $rules["non_fhl_allowances.{$key}"] = $rule;
        }

        // Add rules for unified adjustments/allowances (TY 2025-26+)
        foreach ($adjustmentRules as $key => $rule) {
            $rules["adjustments.{$key}"] = $rule;
        }
        foreach ($allowanceRules as $key => $rule) {
            $rules["allowances.{$key}"] = $rule;
        }

        // Test scenario validation (only in sandbox)
        if (config('hmrc.environment') === 'sandbox') {
            $rules['test_scenario'] = [
                'nullable',
                'string',
                Rule::in([
                    'NOT_FOUND',
                    'STATEFUL',
                    'OUTSIDE_AMENDMENT_WINDOW',
                ]),
            ];
        }

        return $rules;
    }

    /**
     * Get expense validation rule based on tax year
     * For TY 2024-25, expenses can be negative
     */
    protected function getExpenseRule(): string
    {
        $taxYear = $this->input('tax_year');

        // For TY 2024-25 onwards, allow negative values
        if ($taxYear && $taxYear >= '2024-25') {
            return 'nullable|numeric|min:-99999999999.99|max:99999999999.99';
        }

        // For TY 2023-24 and before, only positive values
        return 'nullable|numeric|min:0|max:99999999999.99';
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'business_id.required' => 'Please select a property business.',
            'business_id.regex' => 'Invalid business ID format.',
            'tax_year.required' => 'Please select a tax year.',
            'tax_year.regex' => 'Tax year must be in format YYYY-YY (e.g., 2023-24).',
            'nino.regex' => 'NINO must be in format AA123456A.',
            '*.numeric' => 'This field must be a valid number.',
            '*.min' => 'This field must be at least :min.',
            '*.max' => 'This field cannot exceed :max.',
            'test_scenario.in' => 'Please select a valid test scenario.',
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     */
    public function attributes(): array
    {
        return [
            // Adjustments
            'adjustments.private_use_adjustment' => 'private use adjustment',
            'adjustments.balancing_charge' => 'balancing charge',
            'adjustments.business_premises_renovation_allowance_balancing_charges' => 'BPRA balancing charges',
            'adjustments.period_of_grace_adjustment' => 'period of grace adjustment',
            'adjustments.non_resident_landlord' => 'non-resident landlord',
            'adjustments.rent_a_room_jointly_let' => 'rent a room jointly let',

            // Allowances
            'allowances.annual_investment_allowance' => 'annual investment allowance',
            'allowances.business_premises_renovation_allowance' => 'business premises renovation allowance',
            'allowances.other_capital_allowance' => 'other capital allowance',
            'allowances.cost_of_replacing_domestic_goods' => 'cost of replacing domestic goods',
            'allowances.zero_emissions_car_allowance' => 'zero emissions car allowance',
            'allowances.zero_emissions_goods_vehicle_allowance' => 'zero emissions goods vehicle allowance',
            'allowances.property_allowance' => 'property allowance',
            'allowances.electric_charge_point_allowance' => 'electric charge point allowance',
            'allowances.structure_and_buildings_allowance' => 'structure and buildings allowance',
            'allowances.enhanced_structure_and_buildings_allowance' => 'enhanced structure and buildings allowance',
            'allowances.property_income_allowance' => 'property income allowance',
            'allowances.structured_building_allowance.*.amount' => 'structured building allowance amount',
            'allowances.structured_building_allowance.*.first_year_qualifying_date' => 'first year qualifying date',
            'allowances.structured_building_allowance.*.first_year_qualifying_amount' => 'first year qualifying amount',
            'allowances.structured_building_allowance.*.building_name' => 'building name',
            'allowances.structured_building_allowance.*.building_number' => 'building number',
            'allowances.structured_building_allowance.*.building_postcode' => 'building postcode',
            'allowances.enhanced_structured_building_allowance.*.amount' => 'enhanced structured building allowance amount',
            'allowances.enhanced_structured_building_allowance.*.first_year_qualifying_date' => 'first year qualifying date',
            'allowances.enhanced_structured_building_allowance.*.first_year_qualifying_amount' => 'first year qualifying amount',
            'allowances.enhanced_structured_building_allowance.*.building_name' => 'building name',
            'allowances.enhanced_structured_building_allowance.*.building_number' => 'building number',
            'allowances.enhanced_structured_building_allowance.*.building_postcode' => 'building postcode',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validate structured building allowances - building object is REQUIRED
            $this->validateStructuredBuildingAllowances($validator, 'allowances.structured_building_allowance');
            $this->validateStructuredBuildingAllowances($validator, 'allowances.enhanced_structured_building_allowance');
            $this->validateStructuredBuildingAllowances($validator, 'fhl_allowances.structured_building_allowance');
            $this->validateStructuredBuildingAllowances($validator, 'fhl_allowances.enhanced_structured_building_allowance');
            $this->validateStructuredBuildingAllowances($validator, 'non_fhl_allowances.structured_building_allowance');
            $this->validateStructuredBuildingAllowances($validator, 'non_fhl_allowances.enhanced_structured_building_allowance');

            // Validate property income allowance - mutually exclusive with other allowances
            $this->validatePropertyIncomeAllowance($validator, 'allowances');
            $this->validatePropertyIncomeAllowance($validator, 'fhl_allowances');
            $this->validatePropertyIncomeAllowance($validator, 'non_fhl_allowances');
        });
    }

    /**
     * Validate that propertyIncomeAllowance is not used with other allowances (mutually exclusive)
     */
    protected function validatePropertyIncomeAllowance($validator, string $prefix)
    {
        $allowances = $this->input($prefix, []);
        $propertyIncomeAllowance = $allowances['property_income_allowance'] ?? null;

        // If propertyIncomeAllowance is set, check that no other allowances are set
        if (!empty($propertyIncomeAllowance)) {
            $otherAllowances = [
                'annual_investment_allowance',
                'business_premises_renovation_allowance',
                'other_capital_allowance',
                'cost_of_replacing_domestic_goods',
                'zero_emissions_car_allowance',
                'zero_emissions_goods_vehicle_allowance',
                'electric_charge_point_allowance',
                'structured_building_allowance',
                'enhanced_structured_building_allowance',
            ];

            foreach ($otherAllowances as $otherAllowance) {
                $value = $allowances[$otherAllowance] ?? null;

                // Check for non-empty values (including arrays)
                if (!empty($value)) {
                    $validator->errors()->add(
                        "{$prefix}.property_income_allowance",
                        'Property Income Allowance cannot be used together with other allowances. You must choose either Property Income Allowance OR other capital allowances, not both.'
                    );
                    break;
                }
            }
        }
    }

    /**
     * Validate that structured building allowances have at least one building field
     */
    protected function validateStructuredBuildingAllowances($validator, string $field)
    {
        $allowances = $this->input($field, []);

        if (!is_array($allowances)) {
            return;
        }

        foreach ($allowances as $index => $building) {
            $hasName = !empty($building['building_name']);
            $hasNumber = !empty($building['building_number']);
            $hasPostcode = !empty($building['building_postcode']);

            if (!$hasName && !$hasNumber && !$hasPostcode) {
                $displayName = ucwords(str_replace('_', ' ', basename($field)));
                $buildingNumber = $index + 1;
                $validator->errors()->add(
                    "{$field}.{$index}.building_name",
                    "{$displayName} #{$buildingNumber} requires at least one building field (name, number, or postcode)."
                );
            }
        }
    }
}
