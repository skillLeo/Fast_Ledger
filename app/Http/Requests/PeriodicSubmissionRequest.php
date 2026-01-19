<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PeriodicSubmissionRequest extends FormRequest
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
        return [
            // Basic fields
            'business_id' => 'required|string',
            'obligation_id' => 'nullable|integer|exists:hmrc_obligations,id',
            'nino' => 'nullable|string|regex:/^[A-Z]{2}[0-9]{6}[A-Z]$/',
            'period_start_date' => 'nullable|date',
            'period_end_date' => 'nullable|date|after_or_equal:period_start_date',
            'notes' => 'nullable|string|max:5000',

            // Income fields
            'income.turnover' => 'nullable|numeric|min:0|max:99999999.99',
            'income.other' => 'nullable|numeric|min:-99999999.99|max:99999999.99',

            // Expense mode
            'expense_mode' => 'nullable|in:consolidated,breakdown',

            // Consolidated expenses
            'expenses.consolidated_expenses' => 'nullable|numeric|min:0|max:99999999.99',

            // Breakdown expenses
            'expenses.breakdown.cost_of_goods' => 'nullable|numeric|min:-99999999.99|max:99999999.99',
            'expenses.breakdown.staff_costs' => 'nullable|numeric|min:-99999999.99|max:99999999.99',
            'expenses.breakdown.travel_costs' => 'nullable|numeric|min:-99999999.99|max:99999999.99',
            'expenses.breakdown.premises_running_costs' => 'nullable|numeric|min:-99999999.99|max:99999999.99',
            'expenses.breakdown.maintenance_costs' => 'nullable|numeric|min:-99999999.99|max:99999999.99',
            'expenses.breakdown.admin_costs' => 'nullable|numeric|min:-99999999.99|max:99999999.99',
            'expenses.breakdown.business_entertainment_costs' => 'nullable|numeric|min:-99999999.99|max:99999999.99',
            'expenses.breakdown.advertising_costs' => 'nullable|numeric|min:-99999999.99|max:99999999.99',
            'expenses.breakdown.interest_on_bank_other_loans' => 'nullable|numeric|min:-99999999.99|max:99999999.99',
            'expenses.breakdown.financial_charges' => 'nullable|numeric|min:-99999999.99|max:99999999.99',
            'expenses.breakdown.bad_debt' => 'nullable|numeric|min:-99999999.99|max:99999999.99',
            'expenses.breakdown.professional_fees' => 'nullable|numeric|min:-99999999.99|max:99999999.99',
            'expenses.breakdown.depreciation' => 'nullable|numeric|min:-99999999.99|max:99999999.99',
            'expenses.breakdown.other_expenses' => 'nullable|numeric|min:-99999999.99|max:99999999.99',
        ];
    }

    /**
     * Get custom attribute names
     */
    public function attributes(): array
    {
        return [
            'income.turnover' => 'turnover',
            'income.other' => 'other income',
            'expenses.consolidated_expenses' => 'consolidated expenses',
            'expenses.breakdown.cost_of_goods' => 'cost of goods',
            'expenses.breakdown.staff_costs' => 'staff costs',
            'expenses.breakdown.travel_costs' => 'travel costs',
            'expenses.breakdown.premises_running_costs' => 'premises running costs',
            'expenses.breakdown.maintenance_costs' => 'maintenance costs',
            'expenses.breakdown.admin_costs' => 'admin costs',
            'expenses.breakdown.business_entertainment_costs' => 'business entertainment costs',
            'expenses.breakdown.advertising_costs' => 'advertising costs',
            'expenses.breakdown.interest_on_bank_other_loans' => 'interest on bank/other loans',
            'expenses.breakdown.financial_charges' => 'financial charges',
            'expenses.breakdown.bad_debt' => 'bad debt',
            'expenses.breakdown.professional_fees' => 'professional fees',
            'expenses.breakdown.depreciation' => 'depreciation',
            'expenses.breakdown.other_expenses' => 'other expenses',
        ];
    }

    /**
     * Get custom messages
     */
    public function messages(): array
    {
        return [
            'business_id.required' => 'Please select a business.',
            'nino.regex' => 'Invalid NINO format. Should be like AB123456C.',
            'period_end_date.after_or_equal' => 'Period end date must be after or equal to the start date.',
            '*.numeric' => 'The :attribute must be a valid number.',
            '*.min' => 'The :attribute must be at least :min.',
            '*.max' => 'The :attribute must not exceed :max.',
        ];
    }

    /**
     * Prepare data for validation
     */
    protected function prepareForValidation(): void
    {
        // Clean up expense mode - remove consolidated if breakdown is selected
        if ($this->input('expense_mode') === 'breakdown' && $this->has('expenses.consolidated_expenses')) {
            $expenses = $this->input('expenses', []);
            unset($expenses['consolidated_expenses']);
            $this->merge(['expenses' => $expenses]);
        }

        // Clean up breakdown if consolidated is selected
        if ($this->input('expense_mode') === 'consolidated' && $this->has('expenses.breakdown')) {
            $expenses = $this->input('expenses', []);
            unset($expenses['breakdown']);
            $this->merge(['expenses' => $expenses]);
        }

        // Convert empty strings to null for numeric fields
        $this->cleanNumericFields('income');
        $this->cleanNumericFields('expenses');
    }

    /**
     * Clean numeric fields by converting empty strings to null
     */
    protected function cleanNumericFields(string $section): void
    {
        if (!$this->has($section)) {
            return;
        }

        $data = $this->input($section, []);
        
        array_walk_recursive($data, function (&$value) {
            if ($value === '' || $value === null) {
                $value = null;
            }
        });

        $this->merge([$section => $data]);
    }
}

