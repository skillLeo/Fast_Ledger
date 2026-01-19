<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\ChartOfAccount;
use Illuminate\Support\Facades\Log;

class TransactionService
{
    /**
     * Create invoice item transaction
     * Used for sales_invoice, purchase, credit notes, etc.
     */
    public function createInvoiceItemTransaction(array $data): Transaction
    {
        try {
            $transaction = new Transaction();

            $transaction->Transaction_Date = $data['Transaction_Date'];
            $transaction->File_ID = $data['File_ID'] ?? null;
            $transaction->Bank_Account_ID = $data['Bank_Account_ID'] ?? null;
            $transaction->chart_of_account_id = $data['chart_of_account_id'];
            $transaction->invoice_id = $data['invoice_id'];
            $transaction->entry_type = $data['entry_type'];
            $transaction->Paid_In_Out = $data['paid_in_out'];
            $transaction->Payment_Type_ID = $data['Payment_Type_ID'] ?? null;
            $transaction->Account_Ref_ID = $data['account_ref_id'] ?? null;
            $transaction->Cheque = $data['invoice_ref'] ?? '';
            $transaction->Amount = $data['net_amount'];
            $transaction->Description = $data['description'];
            $transaction->VAT_ID = $data['vat_form_label_id'] ?? null;
            $transaction->Transaction_Code = $data['Transaction_Code'];
            $transaction->Is_Imported = 0;
            $transaction->Created_By = auth()->id();
            $transaction->Created_On = now();
            $transaction->Is_Bill = 0;

            if (!$transaction->save()) {
                throw new \Exception('Failed to save transaction');
            }

            Log::info('Invoice item transaction created', [
                'transaction_id' => $transaction->Transaction_ID,
                'amount' => $transaction->Amount,
            ]);

            return $transaction;

        } catch (\Exception $e) {
            Log::error('Failed to create invoice item transaction', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);
            throw $e;
        }
    }

    /**
     * Create office transaction
     */
    public function createOfficeTransaction(array $data): Transaction
    {
        try {
            $transaction = new Transaction();

            $transaction->Transaction_Date = $data['Transaction_Date'];
            $transaction->File_ID = null;
            $transaction->Bank_Account_ID = $data['Bank_Account_ID'];
            $transaction->chart_of_account_id = $data['chart_of_account_id'];
            $transaction->Paid_In_Out = $data['Paid_In_Out'];
            $transaction->entry_type = $data['Entry_Type'] ?? null;
            $transaction->Payment_Type_ID = $data['Payment_Type_ID'];
            $transaction->Account_Ref_ID = $data['Account_Ref_ID'];
            $transaction->Cheque = $data['Cheque'];
            $transaction->Amount = $data['Amount'];
            $transaction->Description = $data['Description'];
            $transaction->VAT_ID = $data['VAT_ID'];
            $transaction->Transaction_Code = $data['Transaction_Code'];
            $transaction->Is_Imported = 0;
            $transaction->Created_By = auth()->id();
            $transaction->Created_On = now();
            $transaction->Is_Bill = 0;

            $saved = $transaction->save();

            if (!$saved) {
                throw new \Exception('Failed to save office transaction');
            }

            return $transaction;

        } catch (\Exception $e) {
            Log::error('Failed to create office transaction', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);
            throw $e;
        }
    }

    /**
     * Create client transaction
     */
    public function createClientTransaction(array $data): Transaction
    {
        try {
            $transaction = new Transaction();

            $transaction->transaction_date = $data['transaction_date'];
            $transaction->file_id = $data['file_id'];
            $transaction->bank_account_id = $data['bank_account_id'];
            $transaction->paid_in_out = $data['paid_in_out'];
            $transaction->payment_type_id = $data['payment_type_id'];
            $transaction->account_ref_id = $data['account_ref_id'];
            $transaction->cheque = $data['cheque'];
            $transaction->amount = $data['amount'];
            $transaction->description = $data['description'];
            $transaction->vat_id = $data['vat_id'];
            $transaction->Transaction_Code = $data['Transaction_Code'];
            $transaction->is_imported = 0;
            $transaction->created_by = auth()->id();
            $transaction->created_on = now();
            $transaction->is_bill = 0;

            if (!$transaction->save()) {
                throw new \Exception('Failed to save client transaction');
            }

            return $transaction;

        } catch (\Exception $e) {
            Log::error('Failed to create client transaction', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);
            throw $e;
        }
    }

    /**
     * Resolve Account_Ref_ID by ledger ID and account ref text
     */
    public function resolveAccountRefIdByLedgerId(?int $ledgerId, ?string $accountRef): ?int
    {
        if (!$ledgerId || !$accountRef) {
            return null;
        }

        $ledgerRef = ChartOfAccount::whereKey($ledgerId)->value('ledger_ref');
        if (!$ledgerRef) {
            return null;
        }

        $needleAcc = mb_strtolower(trim($accountRef));
        $needleLedg = mb_strtolower(trim($ledgerRef));

        return ChartOfAccount::query()
            ->whereRaw('LOWER(TRIM(ledger_ref)) = ?', [$needleLedg])
            ->whereRaw('LOWER(TRIM(account_ref)) = ?', [$needleAcc])
            ->value('id');
    }

    /**
     * Calculate entry type based on COA and effect
     */
    public function entryTypeFromCoaAndEffect(int $coaId, string $effect): string
    {
        $normalBalance = strtoupper(ChartOfAccount::whereKey($coaId)->value('normal_balance') ?? 'DR');
        
        if ($effect === 'increase') {
            return $normalBalance === 'DR' ? 'Dr' : 'Cr';
        }

        return $normalBalance === 'DR' ? 'Cr' : 'Dr';
    }

    /**
     * Calculate Paid_In_Out from entry type
     */
    public function paidInOutFromEntryType(string $entryType): int
    {
        return strtoupper($entryType) === 'CR' ? Transaction::MONEY_IN : Transaction::MONEY_OUT;
    }

    /**
     * Get effect for payment type
     */
    public function effectForPaymentType(string $paymentType): string
    {
        return match ($paymentType) {
            'sales_invoice', 'purchase' => 'increase',
            'sales_credit', 'purchase_credit' => 'decrease',
            default => 'increase',
        };
    }
}