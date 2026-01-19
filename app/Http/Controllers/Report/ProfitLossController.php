<?php

namespace App\Http\Controllers\Report;

use Illuminate\Http\Request;
use App\Models\ChartOfAccount;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class ProfitLossController extends Controller
{
    public function index(Request $request)
    {
        // 1) Financial year window (Apr–Mar)
        $currentDate = Carbon::now();
        $currentFinancialYear = $currentDate->month >= 4 ? $currentDate->year + 1 : $currentDate->year;

        $defaultFromDate = Carbon::createFromDate($currentFinancialYear - 1, 4, 1)->format('Y-m-d');
        $defaultToDate   = Carbon::createFromDate($currentFinancialYear, 3, 31)->format('Y-m-d');

        $fromDate = $request->get('from_date', $defaultFromDate);
        $toDate   = $request->get('to_date', $defaultToDate);

        // 2) Export shortcut
        if ($request->has('export')) {
            return $this->handleExport($request, $fromDate, $toDate);
        }

        // 3) P&L accounts (PAGINATED: 250 per page)
        $accountsPaginator = ChartOfAccount::query()
            ->where('pl_bs', 'P&L')
            ->active()
            ->select(['id', 'ledger_ref', 'account_ref', 'vat_id', 'description', 'pl_bs', 'normal_balance'])
            ->orderBy('ledger_ref')
            ->orderBy('account_ref')
            ->paginate(100)                 // <<-- pagination added
            ->appends($request->query());   // <<-- preserve filters on next/prev

        // 4) Calculate balances ONLY for current page accounts
        $ids = $accountsPaginator->getCollection()->pluck('id');

        if ($ids->isNotEmpty()) {
            $balances = DB::table('transaction') // <-- use your real table name
                ->join('chart_of_accounts as coa', 'coa.id', '=', 'transaction.chart_of_account_id')
                ->whereIn('transaction.chart_of_account_id', $ids)
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

            // 5) Attach balances to current page models
            $accountsPaginator->getCollection()->transform(function ($acc) use ($balances) {
                $acc->balance = (float) ($balances[$acc->id] ?? 0);
                return $acc;
            });
        }

        // 6) Group current page collection for UI
        $groupedAccounts = $accountsPaginator->getCollection()->groupBy('ledger_ref');

        return view('admin.reports.profit_loss', compact(
            'accountsPaginator',            // <<-- pass paginator to view
            'groupedAccounts',
            'fromDate',
            'toDate',
            'currentFinancialYear'
        ));
    }

    private function handleExport(Request $request, $fromDate, $toDate)
    {
        $exportType = $request->get('export');
        
        if ($exportType === 'pdf') {
            return $this->exportToPDF($fromDate, $toDate);
        } elseif ($exportType === 'excel') {
            return $this->exportToExcel($fromDate, $toDate);
        }
        
        return back();
    }

    private function exportToPDF($fromDate, $toDate)
    {
        // TODO: Implement PDF export logic
        // You can use libraries like DomPDF or similar
        return response()->json(['message' => 'PDF export functionality to be implemented']);
    }

    private function exportToExcel($fromDate, $toDate)
    {
        // TODO: Implement Excel export logic  
        // You can use libraries like Laravel Excel
        return response()->json(['message' => 'Excel export functionality to be implemented']);
    }
}