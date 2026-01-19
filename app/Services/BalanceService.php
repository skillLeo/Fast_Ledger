<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\BankAccount;
use App\Models\PendingTransaction;
use Illuminate\Support\Facades\Log;

class BalanceService
{
    public function calculateBalances(int $bankAccountId, ?int $userId = null, bool $includeDetails = false): array
    {
        $fastLedgerBalance = $this->calculateStatementBalance($bankAccountId, $userId);
        $statementBalance = $this->calculatePendingBalance($bankAccountId);
        $pendingBalance = $statementBalance;
        $balanceToReconcile = $statementBalance;
        $pendingCount = $this->getPendingTransactionCount($bankAccountId);

        $result = [
            'statement_balance' => $statementBalance,
            'fast_ledger_balance' => $fastLedgerBalance,
            'pending_balance' => $pendingBalance,
            'balance_to_reconcile' => $balanceToReconcile,
            'pending_count' => $pendingCount,
        ];

        if ($includeDetails) {
            $result['details'] = $this->getBalanceDetails($bankAccountId, $userId);
        }

        return $result;
    }

    public function calculateStatementBalance(int $bankAccountId, ?int $userId = null): float
    {
        $query = Transaction::where('Bank_Account_ID', $bankAccountId);

        if ($userId) {
            $query->whereHas('bankAccount', function ($q) use ($userId) {
                $q->where('Client_ID', $userId);
            });
        }

        $moneyIn = (clone $query)->where('Paid_In_Out', Transaction::MONEY_IN)->sum('Amount');
        $moneyOut = (clone $query)->where('Paid_In_Out', Transaction::MONEY_OUT)->sum('Amount');

        return $moneyIn - $moneyOut;
    }

    public function calculatePendingBalance(int $bankAccountId): float
    {
        $moneyIn = PendingTransaction::where('bank_account_id', $bankAccountId)
            ->where('status', 'pending')
            ->sum('money_in');

        $moneyOut = PendingTransaction::where('bank_account_id', $bankAccountId)
            ->where('status', 'pending')
            ->sum('money_out');

        return $moneyIn - $moneyOut;
    }

    public function getPendingTransactionCount(int $bankAccountId): int
    {
        return PendingTransaction::where('bank_account_id', $bankAccountId)
            ->where('status', 'pending')
            ->count();
    }

    public function getBalanceDetails(int $bankAccountId, ?int $userId = null): array
    {
        $statementQuery = Transaction::where('Bank_Account_ID', $bankAccountId);
        if ($userId) {
            $statementQuery->whereHas('bankAccount', function ($q) use ($userId) {
                $q->where('Client_ID', $userId);
            });
        }

        $statementMoneyIn = (clone $statementQuery)->where('Paid_In_Out', Transaction::MONEY_IN)->sum('Amount');
        $statementMoneyOut = (clone $statementQuery)->where('Paid_In_Out', Transaction::MONEY_OUT)->sum('Amount');

        $pendingMoneyIn = PendingTransaction::where('bank_account_id', $bankAccountId)
            ->where('status', 'pending')
            ->sum('money_in');

        $pendingMoneyOut = PendingTransaction::where('bank_account_id', $bankAccountId)
            ->where('status', 'pending')
            ->sum('money_out');

        return [
            'statement' => [
                'money_in' => $statementMoneyIn,
                'money_out' => $statementMoneyOut,
                'balance' => $statementMoneyIn - $statementMoneyOut,
            ],
            'pending' => [
                'money_in' => $pendingMoneyIn,
                'money_out' => $pendingMoneyOut,
                'balance' => $pendingMoneyIn - $pendingMoneyOut,
            ],
        ];
    }

    public function calculateMultipleBalances(array $bankAccountIds, ?int $userId = null): array
    {
        $results = [];
        foreach ($bankAccountIds as $bankAccountId) {
            $results[$bankAccountId] = $this->calculateBalances($bankAccountId, $userId);
        }
        return $results;
    }

    public function addBalancesToBankAccount(BankAccount $bankAccount, ?int $userId = null): BankAccount
    {
        $balances = $this->calculateBalances($bankAccount->Bank_Account_ID, $userId);

        $bankAccount->statement_balance = $balances['statement_balance'];
        $bankAccount->pending_balance = $balances['pending_balance'];
        $bankAccount->fast_ledger_balance = $balances['fast_ledger_balance'];
        $bankAccount->balance_to_reconcile = $balances['balance_to_reconcile'];
        $bankAccount->pending_count = $balances['pending_count'];

        return $bankAccount;
    }

    public function formatBalance(float $amount, string $currency = '£'): string
    {
        return $currency . number_format(abs($amount), 2);
    }

    public function getBalanceColorClass(float $amount): string
    {
        return $amount < 0 ? 'text-danger' : 'text-success';
    }
}