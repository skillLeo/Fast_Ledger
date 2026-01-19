<?php

namespace App\Http\Controllers\Report;

use Illuminate\Http\Request;
use App\Models\ChartOfAccount;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
class BalanceSheetController extends Controller
{

    public function index(Request $request)
    {
        // Get all BS accounts (your original query; just selected columns add kiye)
        $plAccounts = ChartOfAccount::where('pl_bs', 'BS')
            ->active()
            ->select(['id', 'ledger_ref', 'account_ref', 'description', 'normal_balance', 'pl_bs'])
            ->orderBy('ledger_ref')
            ->orderBy('account_ref')
            ->get();

        // ---- NEW: same CASE-logic se balances compute ----
        $ids = $plAccounts->pluck('id');

        $balances = collect();
        if ($ids->isNotEmpty()) {
            $balances = Transaction::query()
                ->join('chart_of_accounts as coa', 'coa.id', '=', 'transaction.chart_of_account_id')
                ->whereIn('transaction.chart_of_account_id', $ids)
                ->select('transaction.chart_of_account_id', DB::raw("
                SUM(
                    CASE
                        -- PAY/CHQ (Money OUT) - Always DEBIT entry
                        WHEN LEFT(transaction.Transaction_Code, 3) IN ('PAY','CHQ') THEN
                            CASE WHEN UPPER(COALESCE(coa.normal_balance,'')) = 'DR'
                                THEN transaction.Amount      -- DR accounts: Debit increases balance
                                ELSE -transaction.Amount     -- CR accounts: Debit decreases balance
                            END

                        -- REC (Money IN) - Always CREDIT entry
                        WHEN LEFT(transaction.Transaction_Code, 3) = 'REC' THEN
                            CASE WHEN UPPER(COALESCE(coa.normal_balance,'')) = 'DR'  
                                THEN -transaction.Amount     -- DR accounts: Credit decreases balance
                                ELSE transaction.Amount      -- CR accounts: Credit increases balance
                            END

                        -- Other transaction types (same as your old index)
                        WHEN LEFT(transaction.Transaction_Code,3) = 'SIN' THEN transaction.Amount
                        WHEN LEFT(transaction.Transaction_Code,3) = 'SCN' THEN -transaction.Amount
                        WHEN LEFT(transaction.Transaction_Code,3) = 'PUR' THEN transaction.Amount
                        WHEN LEFT(transaction.Transaction_Code,3) = 'PUC' THEN -transaction.Amount

                        -- Fallback on entry_type (DR/CR)
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
            "))
                ->groupBy('transaction.chart_of_account_id')
                ->pluck('balance', 'transaction.chart_of_account_id');
        }

        // ---- NEW: attach computed balance to each account ----
        $plAccounts->transform(function ($acc) use ($balances) {
            $acc->balance = (float) ($balances[$acc->id] ?? 0);
            return $acc;
        });

        // Group accounts by ledger_ref (your original)
        $groupedAccounts = $plAccounts->groupBy('ledger_ref');

        return view('admin.reports.balance_sheet', compact('groupedAccounts'));
    }
}
