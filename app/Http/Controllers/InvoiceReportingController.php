<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InvoiceReportingController extends Controller
{
    // Single source of truth for prefixes
    public const PREFIX_MAP = [
        'sales_invoice'   => 'SIN',
        'sales_credit'    => 'SCN',
        'purchase'        => 'PUR',
        'purchase_credit' => 'PUC',
    ];

    // Labels for dropdown
    private const PAYMENT_TYPE_OPTIONS = [
        'all'             => 'All Payment Types',
        'sales_invoice'   => 'Sales Invoice (SIN)',
        'sales_credit'    => 'Sales Credit (SCN)',
        'purchase'        => 'Purchase (PUR)',
        'purchase_credit' => 'Purchase Credit (PUC)',
    ];

    public function index(Request $request)
    {
        // Dates (Y-m-d strings)
        $from = $request->input('from_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $to   = $request->input('to_date', Carbon::now()->format('Y-m-d'));

        $selectedPaymentType = $request->get('payment_type', 'all');
        $prefixes = array_values(self::PREFIX_MAP);

        // Optional chart_of_account_id filter (single or array)
        $chartIdsInput = $request->input('chart_of_account_id');
        $chartIds = is_array($chartIdsInput) ? $chartIdsInput : array_filter([(int) $chartIdsInput]);

        $q = DB::table('transaction as t')
            ->leftJoin('invoices as i', 'i.id', '=', 't.invoice_id')
            ->leftJoin('chart_of_accounts as coa', 'coa.id', '=', 't.chart_of_account_id')
            ->select([
                'i.invoice_no',
                'i.invoice_ref',
                'i.invoice_date',
                DB::raw('COALESCE(i.due_date) as due_date'),
                DB::raw('0 as net_amount'),
                DB::raw('0 as vat_amount'),
                DB::raw('t.Amount as total_amount'),
                DB::raw('0 as paid_amount'),
                DB::raw('COALESCE(coa.ledger_ref, "") as ledger_ref'),
                DB::raw('COALESCE(coa.account_ref, "") as account_ref'),
                DB::raw('NULL as doc_url'),
            ])
            ->whereNotNull('t.invoice_id')
            ->whereBetween(DB::raw('COALESCE(i.invoice_date, DATE(t.Transaction_Date))'), [$from, $to])
            ->when(
                isset(self::PREFIX_MAP[$selectedPaymentType]),
                fn($qq) => $qq->where('t.Transaction_Code', 'like', self::PREFIX_MAP[$selectedPaymentType] . '%'),
                fn($qq) => $qq->whereIn(DB::raw('LEFT(t.Transaction_Code, 3)'), $prefixes)
            )
            ->when(!empty($chartIds), fn($qq) => $qq->whereIn('t.chart_of_account_id', $chartIds));

        $rows = $q->orderByRaw('COALESCE(i.invoice_date, t.Transaction_Date) DESC')->paginate(25);

        return view('admin.invoice_reporting.index', [
            'rows'                => $rows,
            'from'                => $from,
            'to'                  => $to,
            'paymentTypeOptions'  => self::PAYMENT_TYPE_OPTIONS,
            'selectedPaymentType' => $selectedPaymentType,
            'prefixMap'           => self::PREFIX_MAP,
        ]);
    }

    public function statement(Request $request)
    {
        $from = $request->filled('from_date')
            ? Carbon::parse($request->input('from_date'))->startOfDay()
            : now()->startOfMonth()->startOfDay();

        $to = $request->filled('to_date')
            ? Carbon::parse($request->input('to_date'))->endOfDay()
            : now()->endOfDay();

        $selectedPaymentType = $request->input('payment_type', 'all');

        // Common filter base
        $base = DB::table('transaction as t')
            ->leftJoin('invoices as i', 'i.id', '=', 't.invoice_id')
            ->leftJoin('chart_of_accounts as coa', 'coa.id', '=', 't.chart_of_account_id')
            ->whereNotNull('t.invoice_id')
            ->whereBetween(DB::raw('COALESCE(i.invoice_date, DATE(t.Transaction_Date))'), [
                $from->toDateString(),
                $to->toDateString(),
            ])
            ->when(
                isset(self::PREFIX_MAP[$selectedPaymentType]),
                fn($q) => $q->where('t.Transaction_Code', 'like', self::PREFIX_MAP[$selectedPaymentType] . '%'),
                fn($q) => $q->whereIn(DB::raw('LEFT(t.Transaction_Code, 3)'), array_values(self::PREFIX_MAP))
            );

        // Main rows: per-ledger
        $rows = (clone $base)
            ->selectRaw("
            t.chart_of_account_id,
            COALESCE(coa.ledger_ref, '') AS ledger_ref,
            COUNT(*)                      AS entry_count,
            SUM(t.Amount)                 AS amount
        ")
            ->groupBy('t.chart_of_account_id', 'coa.ledger_ref')
            ->orderBy('coa.ledger_ref')
            ->get();

        // Type totals: one row per prefix present (e.g., SIN/SCN/…)
        $typeTotals = (clone $base)
            ->selectRaw("
            LEFT(t.Transaction_Code, 3) AS prefix,
            COUNT(*)                    AS entry_count,
            SUM(t.Amount)               AS amount
        ")
            ->groupBy('prefix')
            ->orderBy('prefix')
            ->get();

        // Grand total (all types)
        $grandAmount = (float) $rows->sum('amount');

        return view('admin.invoice_reporting.statement', [
            'rows'                => $rows,
            'typeTotals'          => $typeTotals,       // <-- pass to view
            'from'                => $from->toDateString(),
            'to'                  => $to->toDateString(),
            'selectedPaymentType' => $selectedPaymentType,
            'grandAmount'         => $grandAmount,
            'prefixMap'           => self::PREFIX_MAP,
        ]);
    }
}
