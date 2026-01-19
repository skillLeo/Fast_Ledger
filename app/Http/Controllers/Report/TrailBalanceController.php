<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\ChartOfAccount;
use App\Models\Transaction;

class TrailBalanceController extends Controller
{
    public function index(Request $request)
    {
        [$defaultFrom, $defaultTo] = $this->getFinancialYearDates();

        $fromDate = $request->input('from_date', $defaultFrom);
        $toDate   = $request->input('to_date', $defaultTo);

        // Dynamic available years (last 6 years)
        $availableYears = $this->getAvailableYears();

        // All active Chart of Accounts
        $accounts = ChartOfAccount::query()
            ->active()
            ->select(['id', 'ledger_ref', 'code', 'account_ref', 'pl_bs', 'normal_balance', 'description'])
            ->orderBy('ledger_ref')
            ->orderBy('account_ref')
            ->get();

        if ($accounts->isEmpty()) {
            return view('admin.reports.trail_balance', [
                'groupedAccounts' => collect(),
                'fromDate' => $fromDate,
                'toDate' => $toDate,
                'availableYears' => $availableYears,
                'asOfDate' => Carbon::parse($toDate),
            ]);
        }

        $ids = $accounts->pluck('id');

        // Ledger balances as of toDate
        $balances = $this->ledgerBalanceQuery($ids, $toDate);

        // Year-to-date balances
        $ytd = $this->ytdQuery($ids, $fromDate, $toDate);

        // Comparatives for previous years
        $comparativesByYear = [];
        foreach ($availableYears as $yr) {
            $asOf = Carbon::create($yr, 3, 31)->format('Y-m-d');
            $comparativesByYear[$yr] = $this->ledgerBalanceQuery($ids, $asOf);
        }

        // Attach to models
        $accounts->transform(function ($acc) use ($balances, $ytd, $comparativesByYear, $availableYears) {
            $acc->ledger_balance = (float)($balances[$acc->id] ?? 0.0);
            $acc->debit_ytd      = (float)($ytd[$acc->id]->debit_ytd  ?? 0.0);
            $acc->credit_ytd     = (float)($ytd[$acc->id]->credit_ytd ?? 0.0);

            $acc->setAttribute(
                'comparatives',
                collect($availableYears)
                    ->mapWithKeys(fn($yr) => [$yr => (float) ($comparativesByYear[$yr][$acc->id] ?? 0.0)])
            );

            return $acc;
        });

        $groupedAccounts = $accounts->groupBy('ledger_ref');
        $groupTotals = $groupedAccounts->map(fn($grp) => $grp->sum('ledger_balance'));
        $asOfDate = Carbon::parse($toDate);

        return view('admin.reports.trail_balance', compact(
            'groupedAccounts',
            'fromDate',
            'toDate',
            'availableYears',
            'asOfDate',
            'groupTotals'
        ));
    }

    protected function getFinancialYearDates(): array
    {
        $today = Carbon::today();

        if ($today->month >= 4) {
            return [
                Carbon::create($today->year, 4, 1)->toDateString(),
                Carbon::create($today->year + 1, 3, 31)->toDateString(),
            ];
        }

        return [
            Carbon::create($today->year - 1, 4, 1)->toDateString(),
            Carbon::create($today->year, 3, 31)->toDateString(),
        ];
    }

    protected function getAvailableYears(): array
    {
        $today = Carbon::today();
        $fyEnd = $today->month >= 4 ? $today->year + 1 : $today->year;

        return collect(range($fyEnd - 6, $fyEnd - 1))->reverse()->values()->toArray();
    }

    protected function ledgerBalanceQuery($ids, $asOfDate)
    {
        return Transaction::query()
            ->join('chart_of_accounts as coa', 'coa.id', '=', 'transaction.chart_of_account_id')
            ->whereIn('transaction.chart_of_account_id', $ids)
            ->whereDate('transaction.Transaction_Date', '<=', $asOfDate)
            ->select('transaction.chart_of_account_id', DB::raw($this->balanceCaseSql()))
            ->groupBy('transaction.chart_of_account_id')
            ->pluck('balance', 'transaction.chart_of_account_id');
    }

    protected function ytdQuery($ids, $fromDate, $toDate)
    {
        return Transaction::query()
            ->whereIn('transaction.chart_of_account_id', $ids)
            ->whereBetween('transaction.Transaction_Date', [$fromDate, $toDate])
            ->select('transaction.chart_of_account_id', DB::raw($this->ytdCaseSql()))
            ->groupBy('transaction.chart_of_account_id')
            ->get()
            ->keyBy('chart_of_account_id');
    }

    protected function balanceCaseSql(): string
    {
        return "
        SUM(
            CASE
                WHEN LEFT(transaction.Transaction_Code,3) IN ('PAY','CHQ') THEN
                    CASE WHEN UPPER(COALESCE(coa.normal_balance,''))='DR'
                         THEN transaction.Amount ELSE -transaction.Amount END
                WHEN LEFT(transaction.Transaction_Code,3)='REC' THEN
                    CASE WHEN UPPER(COALESCE(coa.normal_balance,''))='DR'
                         THEN -transaction.Amount ELSE transaction.Amount END
                WHEN LEFT(transaction.Transaction_Code,3)='SIN' THEN transaction.Amount
                WHEN LEFT(transaction.Transaction_Code,3)='SCN' THEN -transaction.Amount
                WHEN LEFT(transaction.Transaction_Code,3)='PUR' THEN transaction.Amount
                WHEN LEFT(transaction.Transaction_Code,3)='PUC' THEN -transaction.Amount
                WHEN UPPER(transaction.entry_type)='DR' THEN
                    CASE WHEN UPPER(COALESCE(coa.normal_balance,''))='DR'
                         THEN transaction.Amount ELSE -transaction.Amount END
                WHEN UPPER(transaction.entry_type)='CR' THEN
                    CASE WHEN UPPER(COALESCE(coa.normal_balance,''))='CR'
                         THEN transaction.Amount ELSE -transaction.Amount END
                ELSE transaction.Amount
            END
        ) AS balance
    ";
    }

    protected function ytdCaseSql(): string
    {
        return "
        SUM(
            CASE
                WHEN LEFT(transaction.Transaction_Code,3) IN ('PAY','CHQ','SCN','PUR') THEN ABS(transaction.Amount)
                WHEN UPPER(transaction.entry_type)='DR' THEN ABS(transaction.Amount)
                WHEN UPPER(transaction.entry_type)='CR' THEN 0
                WHEN transaction.Amount < 0 THEN ABS(transaction.Amount)
                ELSE 0
            END
        ) AS debit_ytd,
        SUM(
            CASE
                WHEN LEFT(transaction.Transaction_Code,3) IN ('REC','SIN','PUC') THEN ABS(transaction.Amount)
                WHEN UPPER(transaction.entry_type)='CR' THEN ABS(transaction.Amount)
                WHEN UPPER(transaction.entry_type)='DR' THEN 0
                WHEN transaction.Amount >= 0 THEN ABS(transaction.Amount)
                ELSE 0
            END
        ) AS credit_ytd
    ";
    }
}
