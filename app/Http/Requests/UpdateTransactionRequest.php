<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Adjust based on your authorization logic
    }

    public function rules(): array
    {
        return [
            'Transaction_ID' => 'required|integer|exists:transaction,Transaction_ID',
            'Transaction_Date' => 'required|date',
            'Ledger_Ref' => 'required|string|max:255',
            'Bank_Account_ID' => 'required|integer|exists:bankaccount,Bank_Account_ID',
            'Paid_In_Out' => 'required|integer|in:1,2',
            'Payment_Type_ID' => 'nullable|integer|exists:paymenttype,Payment_Type_ID',
            'Account_Ref_ID' => 'required|integer|exists:accountref,Account_Ref_ID',
            'VAT_ID' => 'nullable|integer|exists:vattype,VAT_ID',
            'Cheque' => 'nullable|string|max:255',
            'Amount' => 'required|numeric|min:0.01',
            'Description' => 'required|string|max:1000',
        ];
    }
}
