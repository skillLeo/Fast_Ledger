<?php

namespace App\Http\Requests\Hmrc;

use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class SubmitVatReturnRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        // Convert string "1" to boolean true
        if ($this->has('finalised')) {
            $this->merge([
                'finalised' => filter_var($this->finalised, FILTER_VALIDATE_BOOLEAN),
            ]);
        }

        // Ensure numeric values are properly cast
        $numericFields = [
            'vatDueSales',
            'vatDueAcquisitions',
            'totalVatDue',
            'vatReclaimedCurrPeriod',
            'netVatDue',
        ];

        foreach ($numericFields as $field) {
            if ($this->has($field)) {
                $this->merge([
                    $field => (float) $this->input($field),
                ]);
            }
        }

        // Ensure integer values are properly cast
        $integerFields = [
            'totalValueSalesExVAT',
            'totalValuePurchasesExVAT',
            'totalValueGoodsSuppliedExVAT',
            'totalAcquisitionsExVAT',
        ];

        foreach ($integerFields as $field) {
            if ($this->has($field)) {
                $this->merge([
                    $field => (int) $this->input($field),
                ]);
            }
        }

        Log::debug('SubmitVatReturnRequest prepared for validation', [
            'original' => $this->except($numericFields + $integerFields + ['finalised']),
            'prepared' => $this->only($numericFields + $integerFields + ['finalised']),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'periodKey' => 'required|string|max:20',
            'vatDueSales' => 'required|numeric|min:0',
            'vatDueAcquisitions' => 'required|numeric|min:0',
            'totalVatDue' => 'required|numeric|min:0',
            'vatReclaimedCurrPeriod' => 'required|numeric|min:0',
            'netVatDue' => 'required|numeric',
            'totalValueSalesExVAT' => 'required|integer|min:0',
            'totalValuePurchasesExVAT' => 'required|integer|min:0',
            'totalValueGoodsSuppliedExVAT' => 'required|integer|min:0',
            'totalAcquisitionsExVAT' => 'required|integer|min:0',
            'finalised' => 'required|boolean|accepted', // Must be true
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        Log::error('SubmitVatReturnRequest validation failed', [
            'errors' => $validator->errors()->toArray(),
            'input' => $this->all(),
            'failed_rules' => $validator->failed(),
        ]);

        parent::failedValidation($validator);
    }

    /**
     * Log successful validation
     */
    protected function passedValidation()
    {
        Log::info('SubmitVatReturnRequest validation passed', [
            'validated_data' => $this->validated(),
        ]);
    }

    public function messages(): array
    {
        return [
            'periodKey.required' => 'Period key is required',
            'vatDueSales.required' => 'VAT due on sales is required',
            'vatDueSales.numeric' => 'VAT due on sales must be a number',
            'finalised.required' => 'Return must be marked as finalised',
            'finalised.accepted' => 'Return must be finalised (checked) before submission',
        ];
    }

    /**
     * Additional validation after basic rules
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Get values (already cast by prepareForValidation)
            $vatDueSales = $this->input('vatDueSales', 0);
            $vatDueAcquisitions = $this->input('vatDueAcquisitions', 0);
            $totalVatDue = $this->input('totalVatDue', 0);
            $vatReclaimed = $this->input('vatReclaimedCurrPeriod', 0);
            $netVatDue = $this->input('netVatDue', 0);

            // Calculate expected values (using same precision as HMRC)
            $calculatedTotal = round($vatDueSales + $vatDueAcquisitions, 2);
            $calculatedNet = round($totalVatDue - $vatReclaimed, 2);
            
            // Calculate differences (allow 1 penny tolerance for floating point)
            $totalDifference = abs($calculatedTotal - $totalVatDue);
            $netDifference = abs($calculatedNet - $netVatDue);

            Log::debug('VAT calculation validation', [
                'calculations' => [
                    'vatDueSales' => $vatDueSales,
                    'vatDueAcquisitions' => $vatDueAcquisitions,
                    'sum' => $vatDueSales + $vatDueAcquisitions,
                    'calculatedTotal' => $calculatedTotal,
                    'providedTotal' => $totalVatDue,
                    'difference' => $totalDifference,
                ],
                'net_calculations' => [
                    'totalVatDue' => $totalVatDue,
                    'vatReclaimed' => $vatReclaimed,
                    'difference_raw' => $totalVatDue - $vatReclaimed,
                    'calculatedNet' => $calculatedNet,
                    'providedNet' => $netVatDue,
                    'difference' => $netDifference,
                ],
            ]);

            // Validate totalVatDue calculation (allow 1p tolerance)
            if ($totalDifference > 0.01) {
                $validator->errors()->add(
                    'totalVatDue',
                    sprintf(
                        'Total VAT due calculation error. Expected: £%s, Got: £%s (Difference: £%s)',
                        number_format($calculatedTotal, 2),
                        number_format($totalVatDue, 2),
                        number_format($totalDifference, 2)
                    )
                );
            }

            // Validate netVatDue calculation (allow 1p tolerance)
            if ($netDifference > 0.01) {
                $validator->errors()->add(
                    'netVatDue',
                    sprintf(
                        'Net VAT due calculation error. Expected: £%s, Got: £%s (Difference: £%s)',
                        number_format($calculatedNet, 2),
                        number_format($netVatDue, 2),
                        number_format($netDifference, 2)
                    )
                );
            }
        });
    }
}