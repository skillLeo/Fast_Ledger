<?php

namespace App\Services;

use App\Models\ChartOfAccount;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProfitLossReportService
{
    /**
     * Get Profit & Loss data for a specific period
     */
    public function getProfitLossData(int $userId, Carbon $fromDate, Carbon $toDate): array
    {
        // Get P&L accounts (chart of accounts is shared across all users)
        $accounts = ChartOfAccount::query()
            ->where('pl_bs', 'P&L')
            ->active()
            ->select(['id', 'ledger_ref', 'account_ref', 'description', 'normal_balance'])
            ->orderBy('ledger_ref')
            ->orderBy('account_ref')
            ->get();

        if ($accounts->isEmpty()) {
            return [
                'income' => [],
                'expenses' => [],
                'total_income' => 0,
                'total_expenses' => 0,
                'net_profit' => 0,
                'period_from' => $fromDate->format('Y-m-d'),
                'period_to' => $toDate->format('Y-m-d')
            ];
        }

        // Calculate balances for all accounts
        $balances = $this->calculateBalances(
            $accounts->pluck('id'),
            $fromDate->format('Y-m-d'),
            $toDate->format('Y-m-d')
        );

        // Attach balances to accounts
        $accounts->each(function ($account) use ($balances) {
            $account->balance = (float) ($balances[$account->id] ?? 0);
        });

        // Group accounts by ledger ref and categorize
        $groupedAccounts = $accounts->groupBy('ledger_ref');
        
        $income = [];
        $expenses = [];
        $totalIncome = 0;
        $totalExpenses = 0;

        foreach ($groupedAccounts as $ledgerRef => $accountGroup) {
            $isIncome = $this->isIncomeLedger($ledgerRef);
            $subtotal = $accountGroup->sum('balance');

            $groupData = [
                'ledger_ref' => $ledgerRef,
                'subtotal' => $subtotal,
                'accounts' => $accountGroup->map(function ($acc) {
                    return [
                        'account_ref' => $acc->account_ref,
                        'description' => $acc->description,
                        'balance' => $acc->balance
                    ];
                })->toArray()
            ];

            if ($isIncome) {
                $income[] = $groupData;
                $totalIncome += $subtotal;
            } else {
                $expenses[] = $groupData;
                $totalExpenses += $subtotal;
            }
        }

        return [
            'income' => $income,
            'expenses' => $expenses,
            'total_income' => $totalIncome,
            'total_expenses' => $totalExpenses,
            'net_profit' => $totalIncome - $totalExpenses,
            'period_from' => $fromDate->format('Y-m-d'),
            'period_to' => $toDate->format('Y-m-d')
        ];
    }

    /**
     * Calculate balances for given account IDs and date range
     */
    protected function calculateBalances($accountIds, string $fromDate, string $toDate)
    {
        return DB::table('transaction')
            ->join('chart_of_accounts as coa', 'coa.id', '=', 'transaction.chart_of_account_id')
            ->whereIn('transaction.chart_of_account_id', $accountIds)
            ->whereBetween('transaction.Transaction_Date', [$fromDate, $toDate])
            ->select(
                'transaction.chart_of_account_id',
                DB::raw("
                    SUM(
                        CASE
                            -- PAY/CHQ (Money OUT) => Debit entry
                            WHEN LEFT(transaction.Transaction_Code, 3) IN ('PAY','CHQ') THEN
                                CASE WHEN UPPER(COALESCE(coa.normal_balance,'')) = 'DR'
                                    THEN transaction.Amount
                                    ELSE -transaction.Amount
                                END

                            -- REC (Money IN) => Credit entry
                            WHEN LEFT(transaction.Transaction_Code, 3) = 'REC' THEN
                                CASE WHEN UPPER(COALESCE(coa.normal_balance,'')) = 'DR'
                                    THEN -transaction.Amount
                                    ELSE transaction.Amount
                                END

                            -- Sales/Purchase families
                            WHEN LEFT(transaction.Transaction_Code,3) = 'SIN' THEN
                                CASE WHEN UPPER(COALESCE(coa.normal_balance,'')) = 'CR'
                                    THEN transaction.Amount
                                    ELSE -transaction.Amount
                                END

                            WHEN LEFT(transaction.Transaction_Code,3) = 'SCN' THEN
                                CASE WHEN UPPER(COALESCE(coa.normal_balance,'')) = 'DR'
                                    THEN transaction.Amount
                                    ELSE -transaction.Amount
                                END

                            WHEN LEFT(transaction.Transaction_Code,3) = 'PUR' THEN
                                CASE WHEN UPPER(COALESCE(coa.normal_balance,'')) = 'DR'
                                    THEN transaction.Amount
                                    ELSE -transaction.Amount
                                END

                            WHEN LEFT(transaction.Transaction_Code,3) = 'PUC' THEN
                                CASE WHEN UPPER(COALESCE(coa.normal_balance,'')) = 'CR'
                                    THEN transaction.Amount
                                    ELSE -transaction.Amount
                                END

                            -- Fallbacks to entry_type
                            WHEN UPPER(transaction.entry_type) = 'DR' THEN
                                CASE WHEN UPPER(COALESCE(coa.normal_balance,'')) = 'DR'
                                    THEN transaction.Amount
                                    ELSE -transaction.Amount
                                END

                            WHEN UPPER(transaction.entry_type) = 'CR' THEN
                                CASE WHEN UPPER(COALESCE(coa.normal_balance,'')) = 'CR'
                                    THEN transaction.Amount
                                    ELSE -transaction.Amount
                                END

                            ELSE transaction.Amount
                        END
                    ) AS balance
                ")
            )
            ->groupBy('transaction.chart_of_account_id')
            ->pluck('balance', 'transaction.chart_of_account_id');
    }

    /**
     * Determine if a ledger reference is income
     */
    protected function isIncomeLedger(string $ledgerRef): bool
    {
        return stripos($ledgerRef, 'income') !== false ||
               stripos($ledgerRef, 'revenue') !== false ||
               stripos($ledgerRef, 'sales') !== false;
    }

    /**
     * Get suggested values for HMRC submission based on P&L data
     */
    public function getSuggestedHmrcValues(array $plData): array
    {
        // Map P&L data to HMRC expense categories
        $suggestions = [
            'income' => [
                'turnover' => $plData['total_income'],
                'other' => 0
            ],
            'expenses' => [
                'consolidated_expenses' => abs($plData['total_expenses'])
            ],
            'breakdown' => $this->mapExpensesToHmrcCategories($plData['expenses'])
        ];

        return $suggestions;
    }

    /**
     * Map expense ledgers to HMRC categories
     */
    protected function mapExpensesToHmrcCategories(array $expenses): array
    {
        $hmrcCategories = [
            'cost_of_goods' => 0,
            'staff_costs' => 0,
            'travel_costs' => 0,
            'premises_running_costs' => 0,
            'maintenance_costs' => 0,
            'admin_costs' => 0,
            'business_entertainment_costs' => 0,
            'advertising_costs' => 0,
            'interest_on_bank_other_loans' => 0,
            'financial_charges' => 0,
            'bad_debt' => 0,
            'professional_fees' => 0,
            'depreciation' => 0,
            'other_expenses' => 0
        ];

        // Map ledger references to HMRC categories
        foreach ($expenses as $expense) {
            $ledgerRef = strtolower($expense['ledger_ref']);
            $amount = abs($expense['subtotal']);

            if (stripos($ledgerRef, 'cost of goods') !== false || stripos($ledgerRef, 'cogs') !== false) {
                $hmrcCategories['cost_of_goods'] += $amount;
            } elseif (stripos($ledgerRef, 'staff') !== false || stripos($ledgerRef, 'payroll') !== false || stripos($ledgerRef, 'wages') !== false) {
                $hmrcCategories['staff_costs'] += $amount;
            } elseif (stripos($ledgerRef, 'travel') !== false || stripos($ledgerRef, 'mileage') !== false) {
                $hmrcCategories['travel_costs'] += $amount;
            } elseif (stripos($ledgerRef, 'rent') !== false || stripos($ledgerRef, 'premises') !== false || stripos($ledgerRef, 'utilities') !== false) {
                $hmrcCategories['premises_running_costs'] += $amount;
            } elseif (stripos($ledgerRef, 'maintenance') !== false || stripos($ledgerRef, 'repairs') !== false) {
                $hmrcCategories['maintenance_costs'] += $amount;
            } elseif (stripos($ledgerRef, 'admin') !== false || stripos($ledgerRef, 'office') !== false || stripos($ledgerRef, 'stationery') !== false) {
                $hmrcCategories['admin_costs'] += $amount;
            } elseif (stripos($ledgerRef, 'entertainment') !== false) {
                $hmrcCategories['business_entertainment_costs'] += $amount;
            } elseif (stripos($ledgerRef, 'advertising') !== false || stripos($ledgerRef, 'marketing') !== false) {
                $hmrcCategories['advertising_costs'] += $amount;
            } elseif (stripos($ledgerRef, 'interest') !== false || stripos($ledgerRef, 'loan') !== false) {
                $hmrcCategories['interest_on_bank_other_loans'] += $amount;
            } elseif (stripos($ledgerRef, 'bank charges') !== false || stripos($ledgerRef, 'financial charges') !== false) {
                $hmrcCategories['financial_charges'] += $amount;
            } elseif (stripos($ledgerRef, 'bad debt') !== false || stripos($ledgerRef, 'write off') !== false) {
                $hmrcCategories['bad_debt'] += $amount;
            } elseif (stripos($ledgerRef, 'professional') !== false || stripos($ledgerRef, 'accountant') !== false || stripos($ledgerRef, 'legal') !== false) {
                $hmrcCategories['professional_fees'] += $amount;
            } elseif (stripos($ledgerRef, 'depreciation') !== false || stripos($ledgerRef, 'amortisation') !== false) {
                $hmrcCategories['depreciation'] += $amount;
            } else {
                $hmrcCategories['other_expenses'] += $amount;
            }
        }

        return $hmrcCategories;
    }
}

