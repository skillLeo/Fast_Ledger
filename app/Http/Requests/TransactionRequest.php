<?php

namespace App\Http\Requests;

use App\Models\Transaction;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class TransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        Log::info('[VAT][1] Raw input snapshot', [
            'VAT_ID' => $this->input('VAT_ID'),
            'items'  => $this->input('items'),
            'paymentType' => $this->input('current_payment_type'),
        ]);

        if ((string) $this->input('current_payment_type') === 'inter_bank_office') {
            $this->merge(['Bank_Account_ID' => null]);

            Log::info('[TRX][IBO][S0] Snapshot', [
                'account_type' => $this->input('account_type'),
                'from'         => $this->input('Bank_Account_From_ID'),
                'to'           => $this->input('Bank_Account_To_ID'),
                'coa_id'       => $this->input('chart_of_account_id'),
                'ledger_ref'   => $this->input('ledger_ref'),
            ]);
        }
    }

    public function rules(): array
    {
        $accountType = $this->input('account_type');
        $paymentType = $this->input('current_payment_type');

        Log::info('[TRX][1] rules() start', compact('accountType', 'paymentType'));

        $invoiceBasedTypes = ['sales_invoice', 'sales_credit', 'purchase', 'purchase_credit', 'journal'];

        $rules = [
            'Transaction_Date' => 'required|date',
            'VAT_ID' => 'nullable|integer|exists:vat_form_labels,id',
            'Payment_Ref' => 'nullable|string|max:255',
            'Account_Ref_ID' => 'nullable|integer|exists:accountref,Account_Ref_ID',
            'Amount' => 'required|numeric|min:0.01',
            'Description' => 'nullable|string|max:1000',
            'Transaction_Code' => 'required|string|max:50',
            'current_payment_type' => 'required|string',
            'account_type' => 'required|in:client,office',
        ];

        $rules['account_ref']     = 'sometimes|nullable|string|max:255';
        $rules['ledger_ref']      = 'sometimes|nullable|string|max:255';
        $rules['coa_description'] = 'sometimes|nullable|string|max:1000';

        if ($accountType === 'office') {
            $rules['Payment_Type_ID'] = 'nullable|integer|exists:paymenttype,Payment_Type_ID';
            $rules['Account_Ref_ID']  = 'nullable|integer|exists:accountref,Account_Ref_ID';
            $rules['current_payment_type'] = 'required|string|in:payment,receipt,transfer,cheque,office_client,purchase,aggregate_client,free_bank,journal,sales_invoice,sales_credit,purchase_credit,inter_bank_office';

            // 🔹 INTER BANK OFFICE (two bank accounts)
            if ($paymentType === 'inter_bank_office') {
                $rules['Bank_Account_ID'] = 'nullable';

                $rules['Bank_Account_From_ID'] = [
                    'required',
                    'integer',
                    Rule::exists('bankaccount', 'Bank_Account_ID')->where(function ($q) {
                        $q->where('Client_ID', $this->user()->Client_ID)
                            ->where('Is_Deleted', 0);
                    }),
                ];

                $rules['Bank_Account_To_ID'] = [
                    'required',
                    'integer',
                    'different:Bank_Account_From_ID',
                    Rule::exists('bankaccount', 'Bank_Account_ID')->where(function ($q) {
                        $q->where('Client_ID', $this->user()->Client_ID)
                            ->where('Is_Deleted', 0);
                    }),
                ];

                $rules['chart_of_account_id'] = 'required|integer|exists:chart_of_accounts,id';
                $rules['ledger_ref'] = 'sometimes|nullable|string|max:255';
            }
            // 🔹 INVOICE-BASED FORMS (including journal)
            elseif (in_array($paymentType, $invoiceBasedTypes)) {
                $rules += [
                    'file_id'               => 'bail|required|exists:file,File_ID',
                    'invoice_no'            => 'nullable|string|max:50',
                    'invoice_ref'           => 'nullable|string|max:100',
                    'Inv_Due_Date'          => 'required|date|after_or_equal:Transaction_Date',
                    'invoice_notes'         => 'nullable|string|max:65535',
                    'items'                 => 'bail|required|array|min:1',
                    'items.*.ledger_id'     => 'bail|required|integer|exists:chart_of_accounts,id',
                    'items.*.description'   => 'bail|required|string|max:255',
                    'items.*.tax_rate'      => 'nullable|numeric|min:0|max:100',
                    'items.*.vat_form_label_id' => 'nullable|integer|exists:vat_form_labels,id',
                    'invoice_document_path' => 'nullable|string|max:500',
                    'invoice_document_name' => 'nullable|string|max:255',
                ];

                // ✅ DIFFERENT RULES FOR JOURNAL vs REGULAR INVOICES
                if ($paymentType === 'journal') {
                    // Journal entries use debit_amount OR credit_amount
                    $rules += [
                        'items.*.account_id'    => 'bail|required|integer|exists:chart_of_accounts,id',
                        'items.*.debit_amount'  => 'nullable|numeric|min:0',
                        'items.*.credit_amount' => 'nullable|numeric|min:0',
                        'items.*.region'        => 'nullable|string|max:100',
                        'journal_notes'         => 'nullable|string|max:1000',
                    ];
                } else {
                    // Regular invoice items use unit_amount
                    $rules += [
                        'items.*.item_code'     => 'nullable|string|max:50',
                        'items.*.account_ref'   => 'nullable|string|max:50',
                        'items.*.unit_amount'   => 'bail|required|numeric|min:0.01',
                        'items.*.vat_amount'    => 'nullable|numeric|min:0',
                        'items.*.net_amount'    => 'nullable|numeric|min:0',
                        'items.*.product_image' => 'nullable|string|max:500',
                        'invoice_net_amount'    => 'nullable|numeric|min:0',
                        'invoice_vat_amount'    => 'nullable|numeric|min:0',
                        'invoice_total_amount'  => 'bail|required|numeric|min:0.01',
                    ];
                }

                // Credit notes specific rules
                if (in_array($paymentType, ['sales_credit', 'purchase_credit'])) {
                    $rules['credit_reason']        = 'nullable|string|max:500';
                    $rules['original_invoice_ref'] = 'nullable|string|max:100';
                }
            }
            // 🔹 REGULAR OFFICE FORMS (single bank)
            else {
                $rules['Bank_Account_ID']     = 'required|integer|exists:bankaccount,Bank_Account_ID';
                $rules['chart_of_account_id'] = 'required|integer|exists:chart_of_accounts,id';
            }
        }
        // 🔹 CLIENT ACCOUNT
        else {
            $rules += [
                'Payment_Type_ID' => 'required|integer|exists:paymenttype,Payment_Type_ID',
                'Account_Ref_ID' => 'nullable|integer|exists:accountref,Account_Ref_ID',
                'current_payment_type' => 'required|string|in:inter_bank_client,inter_ledger,payment,receipt,cheque',
            ];

            if ($paymentType === 'inter_bank_client') {
                $rules += [
                    'Bank_Account_From_ID' => 'required|integer|exists:bankaccount,Bank_Account_ID',
                    'Bank_Account_To_ID' => 'required|integer|exists:bankaccount,Bank_Account_ID|different:Bank_Account_From_ID',
                    'Ledger_Ref' => 'required|string|max:255|exists:file,Ledger_Ref',
                ];
            } elseif ($paymentType === 'inter_ledger') {
                $rules += [
                    'Ledger_Ref_From' => 'required|string|max:255|exists:file,Ledger_Ref',
                    'Ledger_Ref_To' => 'required|string|max:255|exists:file,Ledger_Ref|different:Ledger_Ref_From',
                ];
            } else {
                $rules += [
                    'Bank_Account_ID' => 'required|integer|exists:bankaccount,Bank_Account_ID',
                    'Ledger_Ref' => 'required|string|max:255|exists:file,Ledger_Ref',
                ];
            }
        }

        Log::info('[TRX][2] rules() end', [
            'inter_bank_office?' => $accountType === 'office' && $paymentType === 'inter_bank_office'
        ]);

        return $rules;
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $accountType = $this->input('account_type');
            $paymentType = $this->input('current_payment_type');
            $invoiceBasedTypes = ['sales_invoice', 'sales_credit', 'purchase', 'purchase_credit', 'journal'];

            // Check transaction code uniqueness per user
            $transactionCode = trim($this->input('Transaction_Code', ''));
            if ($transactionCode) {
                $currentClientId = auth()->user()->Client_ID;

                $exists = Transaction::where('Transaction_Code', $transactionCode)
                    ->where(function ($query) use ($currentClientId) {
                        $query->whereHas('file', function ($q) use ($currentClientId) {
                            $q->where('Client_ID', $currentClientId);
                        })
                            ->orWhereHas('bankAccount', function ($q) use ($currentClientId) {
                                $q->where('Client_ID', $currentClientId);
                            })
                            ->orWhereHas('invoice.customerFile', function ($q) use ($currentClientId) {
                                $q->where('Client_ID', $currentClientId);
                            });
                    })
                    ->exists();

                if ($exists) {
                    $validator->errors()->add('Transaction_Code', 'This transaction code already exists.');
                }
            }

            // ✅ CLIENT ACCOUNT VALIDATIONS
            if ($accountType === 'client') {
                if ($paymentType === 'inter_bank_client') {
                    if ($this->input('Bank_Account_From_ID') === $this->input('Bank_Account_To_ID')) {
                        $validator->errors()->add('Bank_Account_To_ID', 'Source and destination bank accounts cannot be the same.');
                    }
                }
                if ($paymentType === 'inter_ledger') {
                    if ($this->input('Ledger_Ref_From') === $this->input('Ledger_Ref_To')) {
                        $validator->errors()->add('Ledger_Ref_To', 'Source and destination ledgers cannot be the same.');
                    }
                }
            }

            // ✅ INVOICE-BASED FORMS VALIDATIONS
            if ($accountType === 'office' && in_array($paymentType, $invoiceBasedTypes)) {
                $items = $this->input('items', []);

                // Different validation for journal vs invoice
                if ($paymentType === 'journal') {
                    $this->validateJournalEntries($validator, $items);
                } else {
                    $this->validateInvoiceItems($validator, $items);
                }
            }

            // ✅ INTER BANK OFFICE VALIDATIONS
            if ($accountType === 'office' && $paymentType === 'inter_bank_office') {
                $from = $this->input('Bank_Account_From_ID');
                $to   = $this->input('Bank_Account_To_ID');
                $coaId = $this->input('chart_of_account_id');

                Log::info('[TRX][IBO][after]', compact('from', 'to', 'coaId'));

                // Presence checks (defensive; rules() already handle)
                if (empty($from)) {
                    $validator->errors()->add('Bank_Account_From_ID', 'Please select the source bank account.');
                }
                if (empty($to)) {
                    $validator->errors()->add('Bank_Account_To_ID', 'Please select the destination bank account.');
                }
                if (empty($coaId)) {
                    $validator->errors()->add('chart_of_account_id', 'Please select an Analysis Account (COA).');
                }

                // Early stop on missing
                if ($validator->errors()->any()) {
                    Log::warning('[TRX][IBO] missing from/to', $validator->errors()->toArray());
                    return;
                }

                // Same/different
                if ($from === $to) {
                    $validator->errors()->add('Bank_Account_To_ID', 'Source and destination bank accounts cannot be the same.');
                    Log::warning('[TRX][IBO] same banks', compact('from', 'to'));
                } else {
                    Log::info('[TRX][IBO] banks OK', compact('from', 'to'));
                }
            }

            if ($validator->errors()->any()) {
                Log::warning('[TRX] validator->after() errors', $validator->errors()->toArray());
            } else {
                Log::debug('[TRX] validator->after() no extra errors');
            }
        });
    }

    /**
     * ✅ VALIDATE JOURNAL ENTRIES
     * Each entry must have either debit OR credit (not both, not neither)
     * Total debits (with VAT) must equal total credits (with VAT)
     */
    private function validateJournalEntries($validator, array $items)
    {
        if (empty($items)) {
            $validator->errors()->add('items', 'Please add at least one journal entry.');
            return;
        }

        $totalDebit = 0;
        $totalCredit = 0;
        $totalDebitWithVAT = 0;
        $totalCreditWithVAT = 0;

        foreach ($items as $index => $item) {
            // Validate description
            if (empty($item['description']) || trim($item['description']) === '') {
                $validator->errors()->add("items.{$index}.description", 'Entry description is required.');
            }

            // Validate ledger and account
            if (empty($item['ledger_id'])) {
                $validator->errors()->add("items.{$index}.ledger_id", 'Ledger is required.');
            }
            if (empty($item['account_id'])) {
                $validator->errors()->add("items.{$index}.account_id", 'Account is required.');
            }

            // Get amounts
            $debitAmount = floatval($item['debit_amount'] ?? 0);
            $creditAmount = floatval($item['credit_amount'] ?? 0);
            $taxRate = floatval($item['tax_rate'] ?? 0);

            // Validate that entry has either debit OR credit (not both, not neither)
            if ($debitAmount > 0 && $creditAmount > 0) {
                $validator->errors()->add("items.{$index}", 'Entry cannot have both debit and credit amounts.');
            }

            if ($debitAmount <= 0 && $creditAmount <= 0) {
                $validator->errors()->add("items.{$index}", 'Entry must have either a debit or credit amount greater than 0.');
            }

            // Calculate VAT for this entry
            $baseAmount = max($debitAmount, $creditAmount);
            $vatAmount = ($baseAmount * $taxRate) / 100;
            $netAmount = $baseAmount + $vatAmount;

            // Add to totals
            if ($debitAmount > 0) {
                $totalDebit += $baseAmount;
                $totalDebitWithVAT += $netAmount;
            } else {
                $totalCredit += $baseAmount;
                $totalCreditWithVAT += $netAmount;
            }
        }

        Log::info('[JOURNAL][VALIDATION]', [
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'total_debit_with_vat' => $totalDebitWithVAT,
            'total_credit_with_vat' => $totalCreditWithVAT,
            'difference' => abs($totalDebitWithVAT - $totalCreditWithVAT),
        ]);

        // ✅ VALIDATE JOURNAL BALANCE (debits must equal credits, including VAT)
        if (abs($totalDebitWithVAT - $totalCreditWithVAT) > 0.01) {
            $validator->errors()->add(
                'journal_balance',
                sprintf(
                    'Journal entries must balance. Total Debits (inc VAT): £%.2f, Total Credits (inc VAT): £%.2f, Difference: £%.2f',
                    $totalDebitWithVAT,
                    $totalCreditWithVAT,
                    abs($totalDebitWithVAT - $totalCreditWithVAT)
                )
            );
        }

        // ✅ VALIDATE AMOUNT FIELD matches total (with VAT)
        $submittedAmount = floatval($this->input('Amount', 0));
        $expectedAmount = $totalDebitWithVAT; // or $totalCreditWithVAT (they should be equal)

        if (abs($submittedAmount - $expectedAmount) > 0.01) {
            $validator->errors()->add(
                'Amount',
                sprintf('Amount (£%.2f) must match journal total with VAT (£%.2f).', $submittedAmount, $expectedAmount)
            );
        }
    }

    /**
     * ✅ VALIDATE INVOICE ITEMS
     * Each item must have description and unit_amount
     * Total amounts must match submitted totals
     */
    private function validateInvoiceItems($validator, array $items)
    {
        foreach ($items as $index => $item) {
            if (empty($item['description']) || trim($item['description']) === '') {
                $validator->errors()->add("items.{$index}.description", 'Item description is required.');
            }
            if (empty($item['unit_amount']) || !is_numeric($item['unit_amount']) || $item['unit_amount'] <= 0) {
                $validator->errors()->add("items.{$index}.unit_amount", 'Valid unit amount is required for each item.');
            }
        }

        // Validate invoice totals match items
        $this->validateInvoiceTotals($validator, $items);
    }

    /**
     * ✅ VALIDATE INVOICE TOTALS (with Qty support)
     * Calculate totals from items and compare with submitted totals
     */
    private function validateInvoiceTotals($validator, array $items)
    {
        $net = $vat = $total = 0;

        foreach ($items as $item) {
            // ✅ GET QTY (default to 1 if not present for backward compatibility)
            $qty = floatval($item['qty'] ?? 1);

            // ✅ GET BASE VALUES
            $unitAmount = floatval($item['unit_amount'] ?? 0);
            $vatAmount = floatval($item['vat_amount'] ?? 0);
            $netAmount = floatval($item['net_amount'] ?? 0);

            // ✅ CALCULATE LINE TOTAL (Unit Amount × Qty)
            $lineTotal = $unitAmount * $qty;

            // ✅ SUM EVERYTHING
            $net += $lineTotal;       // Sum of all line totals (before VAT)
            $vat += $vatAmount;       // Sum of all VAT amounts (already calculated by frontend)
            $total += $netAmount;     // Sum of all net amounts (line total + VAT)
        }

        $submittedNet = floatval($this->input('invoice_net_amount'));
        $submittedVat = floatval($this->input('invoice_vat_amount'));
        $submittedTotal = floatval($this->input('invoice_total_amount'));

        Log::info('[INVOICE][VALIDATION]', [
            'calculated_net' => $net,
            'submitted_net' => $submittedNet,
            'calculated_vat' => $vat,
            'submitted_vat' => $submittedVat,
            'calculated_total' => $total,
            'submitted_total' => $submittedTotal,
        ]);

        // ✅ Allow small floating-point differences (0.01)
        if (abs($net - $submittedNet) > 0.01) {
            $validator->errors()->add(
                'invoice_net_amount',
                sprintf('Net amount (£%.2f) does not match item total (£%.2f).', $submittedNet, $net)
            );
        }
        if (abs($vat - $submittedVat) > 0.01) {
            $validator->errors()->add(
                'invoice_vat_amount',
                sprintf('VAT amount (£%.2f) does not match item total (£%.2f).', $submittedVat, $vat)
            );
        }
        if (abs($total - $submittedTotal) > 0.01) {
            $validator->errors()->add(
                'invoice_total_amount',
                sprintf('Total amount (£%.2f) does not match sum of items (£%.2f).', $submittedTotal, $total)
            );
        }

        // ✅ Amount field should match net amount (sum of line totals)
        $amount = floatval($this->input('Amount', 0));
        if (abs($net - $amount) > 0.01) {
            $validator->errors()->add(
                'Amount',
                sprintf('Amount (£%.2f) must match the invoice net amount (£%.2f).', $amount, $net)
            );
        }
    }

    /**
     * Custom error messages for better UX
     */
    public function messages()
    {
        return [
            // General
            'Transaction_Date.required' => 'Transaction date is required.',
            'Transaction_Date.date' => 'Transaction date must be a valid date.',
            'Amount.required' => 'The amount field is required.',
            'Amount.numeric' => 'The amount must be a valid number.',
            'Amount.min' => 'The amount must be at least £0.01.',
            'Transaction_Code.required' => 'Transaction code is required.',
            'Transaction_Code.unique' => 'This transaction code already exists.',

            // Chart of Accounts
            'chart_of_account_id.required' => 'Please select an account.',
            'chart_of_account_id.exists' => 'Selected account does not exist.',
            'file_id.required' => 'Please select a customer/account.',
            'file_id.exists' => 'Selected customer/account does not exist.',

            // Bank Accounts
            'Bank_Account_ID.required' => 'Bank account is required.',
            'Bank_Account_ID.exists' => 'Selected bank account does not exist.',
            'Bank_Account_From_ID.required' => 'Source bank account is required.',
            'Bank_Account_From_ID.exists' => 'Selected source bank account does not exist.',
            'Bank_Account_To_ID.required' => 'Destination bank account is required.',
            'Bank_Account_To_ID.exists' => 'Selected destination bank account does not exist.',
            'Bank_Account_To_ID.different' => 'Destination bank account must be different from source.',

            // Items
            'items.required' => 'At least one item/entry is required.',
            'items.min' => 'At least one item/entry is required.',
            'items.*.description.required' => 'Item description is required.',
            'items.*.ledger_id.required' => 'Ledger is required for each item.',
            'items.*.ledger_id.exists' => 'Selected ledger does not exist.',
            'items.*.account_id.required' => 'Account is required for each journal entry.',
            'items.*.account_id.exists' => 'Selected account does not exist.',
            'items.*.unit_amount.required' => 'Unit amount is required for each item.',
            'items.*.unit_amount.min' => 'Unit amount must be greater than 0.',
            'items.*.debit_amount.numeric' => 'Debit amount must be a number.',
            'items.*.credit_amount.numeric' => 'Credit amount must be a number.',

            // Invoice Totals
            'invoice_total_amount.required' => 'Invoice total amount is required.',
            'invoice_total_amount.min' => 'Invoice total amount must be greater than 0.',
            'invoice_net_amount.numeric' => 'Invoice net amount must be a number.',
            'invoice_vat_amount.numeric' => 'Invoice VAT amount must be a number.',

            // VAT
            'VAT_ID.exists' => 'Selected VAT type does not exist.',
            'items.*.vat_form_label_id.exists' => 'Selected VAT type does not exist.',

            'invoice_document_path.string' => 'Invalid document path.',
            'invoice_document_path.max' => 'Document path is too long.',
            'invoice_document_name.string' => 'Invalid document filename.',
            'invoice_document_name.max' => 'Document filename is too long.',
        ];
    }
}
