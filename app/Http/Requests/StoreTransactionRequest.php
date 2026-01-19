<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // return [
        //     'Transaction_Date' => 'required|date',
        //     'Ledger_Ref' => 'required|string|max:255',
        //     'Bank_Account_ID' => 'required|integer|exists:bankaccount,Bank_Account_ID', // Update `bank_accounts` and `id` to match your database
        //     'Paid_In_Out' => 'required|integer|in:1,2', // Assuming 1 and 2 are valid values
        //     'Payment_Type_ID' => 'nullable|integer|exists:paymenttype,Payment_Type_ID', // Update `payment_types` and `id` to match your database
        //     'Account_Ref_ID' => 'required|integer|exists:accountref,Account_Ref_ID', // Update `accounts` and `id` to match your database
        //     'VAT_ID' => 'nullable|integer|exists:vattype,VAT_ID', // Update `vats` and `id` to match your database
        //     'Cheque' => 'nullable|string|max:255',
        //     'Amount' => 'required|numeric|min:0.01',
        //     'Description' => 'required|string|max:1000',
        // ];

        // if ($this->route()->getName() === 'transactions.store-multiple') {
            return [
                'transactions' => 'required|array',
                'transactions.*.Transaction_Date' => 'required|date',
                'transactions.*.Ledger_Ref' => 'required|string|max:255',
                'transactions.*.Bank_Account_ID' => 'required|integer|exists:bankaccount,Bank_Account_ID',
                'transactions.*.Paid_In_Out' => 'required|integer|in:1,2',
                'transactions.*.Payment_Type_ID' => 'nullable|integer|exists:paymenttype,Payment_Type_ID',
                'transactions.*.Account_Ref_ID' => 'required|integer|exists:accountref,Account_Ref_ID',
                'transactions.*.VAT_ID' => 'nullable|integer|exists:vattype,VAT_ID',
                'transactions.*.Cheque' => 'required|string|max:255',
                'transactions.*.Amount' => 'required|numeric|min:0.01',
                'transactions.*.Description' => 'required|string|max:1000',
            ];
        // }
    }

    public function messages()
    {
        return [
            'Transaction_Date.required' => 'The transaction date is required.',
            'Transaction_Date.date' => 'The transaction date must be a valid date.',
            'Ledger_Ref.required' => 'The ledger reference is required.',
            'Amount.required' => 'The amount is required.',
            'Amount.numeric' => 'The amount must be a numeric value.',
            'Amount.min' => 'The amount must be at least 0.01.',
            // Add more custom messages as needed
        ];
    }
}
