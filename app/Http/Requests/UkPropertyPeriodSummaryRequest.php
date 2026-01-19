<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UkPropertyPeriodSummaryRequest extends FormRequest
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
        $rules = [
            'business_id' => 'required|string|regex:/^X[A-Z0-9]{1}IS[0-9]{11}$/',
            'tax_year' => 'required|string|regex:/^\d{4}-\d{2}$/',
            'nino' => 'nullable|string|regex:/^[A-Z]{2}[0-9]{6}[A-Z]$/',

            // Period dates
            'from_date' => 'required|date|date_format:Y-m-d|after_or_equal:1900-01-01|before_or_equal:2100-01-01',
            'to_date' => 'required|date|date_format:Y-m-d|after:from_date|before_or_equal:2100-01-01',

            // FHL Income
            'fhl_income.period_amount' => 'nullable|numeric|min:0|max:99999999999.99',
            'fhl_income.tax_deducted' => 'nullable|numeric|min:0|max:99999999999.99',
            'fhl_income.rent_a_room.rents_received' => 'nullable|numeric|min:0|max:99999999999.99',

            // FHL Expenses
            'fhl_expenses.premises_running_costs' => $this->getExpenseRule(),
            'fhl_expenses.repairs_and_maintenance' => $this->getExpenseRule(),
            'fhl_expenses.financial_costs' => $this->getExpenseRule(),
            'fhl_expenses.professional_fees' => $this->getExpenseRule(),
            'fhl_expenses.cost_of_services' => $this->getExpenseRule(),
            'fhl_expenses.travel_costs' => $this->getExpenseRule(),
            'fhl_expenses.other' => $this->getExpenseRule(),
            'fhl_expenses.rent_a_room.amount_claimed' => 'nullable|numeric|min:0|max:99999999999.99',
            'fhl_expenses.consolidated_expenses' => $this->getExpenseRule(),

            // Non-FHL Income
            'non_fhl_income.premiums_of_lease_grant' => 'nullable|numeric|min:0|max:99999999999.99',
            'non_fhl_income.reverse_premiums' => 'nullable|numeric|min:0|max:99999999999.99',
            'non_fhl_income.period_amount' => 'nullable|numeric|min:0|max:99999999999.99',
            'non_fhl_income.tax_deducted' => 'nullable|numeric|min:0|max:99999999999.99',
            'non_fhl_income.other_income' => 'nullable|numeric|min:0|max:99999999999.99',
            'non_fhl_income.rent_a_room.rents_received' => 'nullable|numeric|min:0|max:99999999999.99',

            // Non-FHL Expenses
            'non_fhl_expenses.premises_running_costs' => $this->getExpenseRule(),
            'non_fhl_expenses.repairs_and_maintenance' => $this->getExpenseRule(),
            'non_fhl_expenses.financial_costs' => $this->getExpenseRule(),
            'non_fhl_expenses.professional_fees' => $this->getExpenseRule(),
            'non_fhl_expenses.cost_of_services' => $this->getExpenseRule(),
            'non_fhl_expenses.other' => $this->getExpenseRule(),
            'non_fhl_expenses.residential_financial_cost' => 'nullable|numeric|min:0|max:99999999999.99',
            'non_fhl_expenses.travel_costs' => $this->getExpenseRule(),
            'non_fhl_expenses.residential_financial_costs_carried_forward' => 'nullable|numeric|min:0|max:99999999999.99',
            'non_fhl_expenses.rent_a_room.amount_claimed' => 'nullable|numeric|min:0|max:99999999999.99',
            'non_fhl_expenses.consolidated_expenses' => $this->getExpenseRule(),

            // Unified UK Property Income (for 2025-26+)
            'uk_property_income.premiums_of_lease_grant' => 'nullable|numeric|min:0|max:99999999999.99',
            'uk_property_income.reverse_premiums' => 'nullable|numeric|min:0|max:99999999999.99',
            'uk_property_income.period_amount' => 'nullable|numeric|min:0|max:99999999999.99',
            'uk_property_income.tax_deducted' => 'nullable|numeric|min:0|max:99999999999.99',
            'uk_property_income.other_income' => 'nullable|numeric|min:0|max:99999999999.99',
            'uk_property_income.rent_a_room.rents_received' => 'nullable|numeric|min:0|max:99999999999.99',

            // Unified UK Property Expenses (for 2025-26+)
            'uk_property_expenses.premises_running_costs' => $this->getExpenseRule(),
            'uk_property_expenses.repairs_and_maintenance' => $this->getExpenseRule(),
            'uk_property_expenses.financial_costs' => $this->getExpenseRule(),
            'uk_property_expenses.professional_fees' => $this->getExpenseRule(),
            'uk_property_expenses.cost_of_services' => $this->getExpenseRule(),
            'uk_property_expenses.other' => $this->getExpenseRule(),
            'uk_property_expenses.residential_financial_cost' => 'nullable|numeric|min:0|max:99999999999.99',
            'uk_property_expenses.travel_costs' => $this->getExpenseRule(),
            'uk_property_expenses.residential_financial_costs_carried_forward' => 'nullable|numeric|min:0|max:99999999999.99',
            'uk_property_expenses.rent_a_room.amount_claimed' => 'nullable|numeric|min:0|max:99999999999.99',
            'uk_property_expenses.consolidated_expenses' => $this->getExpenseRule(),
        ];

        // Test scenario validation (only in sandbox)
        if (config('hmrc.environment') === 'sandbox') {
            $rules['test_scenario'] = [
                'nullable',
                'string',
                Rule::in([
                    'NOT_FOUND',
                    'OVERLAPPING',
                    'MISALIGNED',
                    'NOT_CONTIGUOUS',
                    'DUPLICATE_SUBMISSION',
                    'TYPE_OF_BUSINESS_INCORRECT',
                    'STATEFUL',
                ]),
            ];
        }

        return $rules;
    }

    /**
     * Get expense validation rule based on tax year
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
            'from_date.required' => 'Period start date is required.',
            'from_date.date_format' => 'Period start date must be in format YYYY-MM-DD.',
            'to_date.required' => 'Period end date is required.',
            'to_date.date_format' => 'Period end date must be in format YYYY-MM-DD.',
            'to_date.after' => 'Period end date must be after the start date.',
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
            'from_date' => 'period start date',
            'to_date' => 'period end date',

            // FHL Income
            'fhl_income.period_amount' => 'FHL rental income',
            'fhl_income.tax_deducted' => 'FHL tax deducted',
            'fhl_income.rent_a_room.rents_received' => 'FHL rent a room income',

            // FHL Expenses
            'fhl_expenses.premises_running_costs' => 'FHL premises running costs',
            'fhl_expenses.repairs_and_maintenance' => 'FHL repairs and maintenance',
            'fhl_expenses.financial_costs' => 'FHL financial costs',
            'fhl_expenses.professional_fees' => 'FHL professional fees',
            'fhl_expenses.cost_of_services' => 'FHL cost of services',
            'fhl_expenses.travel_costs' => 'FHL travel costs',
            'fhl_expenses.other' => 'FHL other expenses',
            'fhl_expenses.rent_a_room.amount_claimed' => 'FHL rent a room amount claimed',
            'fhl_expenses.consolidated_expenses' => 'FHL consolidated expenses',

            // Non-FHL Income
            'non_fhl_income.premiums_of_lease_grant' => 'premiums of lease grant',
            'non_fhl_income.reverse_premiums' => 'reverse premiums',
            'non_fhl_income.period_amount' => 'rental income',
            'non_fhl_income.tax_deducted' => 'tax deducted',
            'non_fhl_income.other_income' => 'other income',
            'non_fhl_income.rent_a_room.rents_received' => 'rent a room income',

            // Non-FHL Expenses
            'non_fhl_expenses.premises_running_costs' => 'premises running costs',
            'non_fhl_expenses.repairs_and_maintenance' => 'repairs and maintenance',
            'non_fhl_expenses.financial_costs' => 'financial costs',
            'non_fhl_expenses.professional_fees' => 'professional fees',
            'non_fhl_expenses.cost_of_services' => 'cost of services',
            'non_fhl_expenses.other' => 'other expenses',
            'non_fhl_expenses.residential_financial_cost' => 'residential financial cost',
            'non_fhl_expenses.travel_costs' => 'travel costs',
            'non_fhl_expenses.residential_financial_costs_carried_forward' => 'residential financial costs carried forward',
            'non_fhl_expenses.rent_a_room.amount_claimed' => 'rent a room amount claimed',
            'non_fhl_expenses.consolidated_expenses' => 'consolidated expenses',

            // Unified UK Property Income
            'uk_property_income.premiums_of_lease_grant' => 'premiums of lease grant',
            'uk_property_income.reverse_premiums' => 'reverse premiums',
            'uk_property_income.period_amount' => 'rental income',
            'uk_property_income.tax_deducted' => 'tax deducted',
            'uk_property_income.other_income' => 'other income',
            'uk_property_income.rent_a_room.rents_received' => 'rent a room income',

            // Unified UK Property Expenses
            'uk_property_expenses.premises_running_costs' => 'premises running costs',
            'uk_property_expenses.repairs_and_maintenance' => 'repairs and maintenance',
            'uk_property_expenses.financial_costs' => 'financial costs',
            'uk_property_expenses.professional_fees' => 'professional fees',
            'uk_property_expenses.cost_of_services' => 'cost of services',
            'uk_property_expenses.other' => 'other expenses',
            'uk_property_expenses.residential_financial_cost' => 'residential financial cost',
            'uk_property_expenses.travel_costs' => 'travel costs',
            'uk_property_expenses.residential_financial_costs_carried_forward' => 'residential financial costs carried forward',
            'uk_property_expenses.rent_a_room.amount_claimed' => 'rent a room amount claimed',
            'uk_property_expenses.consolidated_expenses' => 'consolidated expenses',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $taxYear = $this->input('tax_year');
            $isUnified = $taxYear && $taxYear >= '2025-26';

            if ($isUnified) {
                // For 2025-26+, validate unified property structure
                $this->validateConsolidatedExpenses($validator, 'uk_property_expenses');

                // Check that unified property data is present
                $hasUnifiedData = !empty($this->input('uk_property_income')) || !empty($this->input('uk_property_expenses'));

                if (!$hasUnifiedData) {
                    $validator->errors()->add('submission', 'You must provide UK Property income or expense data.');
                }
            } else {
                // For <=2024-25, validate legacy FHL/Non-FHL structure
                $this->validateConsolidatedExpenses($validator, 'fhl_expenses');
                $this->validateConsolidatedExpenses($validator, 'non_fhl_expenses');

                // Check that at least one of FHL or Non-FHL is present
                $hasFhl = !empty($this->input('fhl_income')) || !empty($this->input('fhl_expenses'));
                $hasNonFhl = !empty($this->input('non_fhl_income')) || !empty($this->input('non_fhl_expenses'));

                if (!$hasFhl && !$hasNonFhl) {
                    $validator->errors()->add('submission', 'You must provide either FHL or Non-FHL property data (or both).');
                }
            }
        });
    }

    /**
     * Validate that consolidated expenses is not used with individual expenses
     */
    protected function validateConsolidatedExpenses($validator, string $prefix)
    {
        $consolidated = $this->input("{$prefix}.consolidated_expenses");

        if ($consolidated !== null && $consolidated !== '') {
            $individualExpenses = [
                "{$prefix}.premises_running_costs",
                "{$prefix}.repairs_and_maintenance",
                "{$prefix}.financial_costs",
                "{$prefix}.professional_fees",
                "{$prefix}.cost_of_services",
                "{$prefix}.travel_costs",
                "{$prefix}.other",
            ];

            foreach ($individualExpenses as $field) {
                $value = $this->input($field);
                if ($value !== null && $value !== '') {
                    $validator->errors()->add(
                        $prefix,
                        'You cannot use consolidated expenses together with individual expense items. Please use one or the other.'
                    );
                    break;
                }
            }
        }
    }
}
