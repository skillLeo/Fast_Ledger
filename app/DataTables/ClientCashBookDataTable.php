<?php

namespace App\DataTables;

use App\Models\Transaction;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

class ClientCashBookDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', 'clientcashbook.action')
            ->setRowId('id')
            ->addColumn('Transaction_Date', function ($row) {
                return \Carbon\Carbon::parse($row->Transaction_Date)->format('d/m/Y');
            });
    }

     public function query(Transaction $model): QueryBuilder
    {
        // Check if date filters are applied
        $hasDateFilter = request()->filled('from_date') && request()->filled('to_date');

        // If no date filters, return a query that doesn't match any records
        if (!$hasDateFilter) {
            return $model->newQuery()->whereRaw('1=0');
        }

        // Get the logged-in user's client ID
        $clientId = auth()->user()->Client_ID;
        $bankAccountId = request()->get('bank_account_id');

        // Calculate the initial balance (before the selected date range)
        $initialBalanceQuery = $model->newQuery()
            ->join('file', 'file.File_ID', '=', 'transaction.File_ID')
            ->leftJoin('bankaccount', 'bankaccount.Bank_Account_ID', '=', 'transaction.Bank_Account_ID')
            ->whereNull('transaction.Deleted_On')
            ->where('transaction.Is_Imported', 1)
            ->where('transaction.Is_Bill', 0)
            ->where('file.Client_ID', $clientId);

        // For initial balance calculation, apply filters based on bank selection
        if ($bankAccountId === 'ledger_to_ledger') {
            // For ledger_to_ledger mode, calculate initial balance from LTLC transactions only
            $initialBalanceQuery->where('transaction.Transaction_Code', 'LIKE', 'LTLC%');
        } elseif ($bankAccountId === 'all_banks') {
            // For "All Banks" option, include all bank transactions (no additional filtering)
        } elseif (!empty($bankAccountId) && $bankAccountId !== '') {
            // For specific bank selection
            $initialBalanceQuery->where('transaction.Bank_Account_ID', $bankAccountId);
        }
        // If no bank selected (empty), include all transactions (default behavior)

        $initialBalanceQuery->when(request()->filled('from_date'), function ($q) {
            $q->where('transaction.Transaction_Date', '<', request('from_date'));
        });

        // Sum the initial balance (considering both Payments and Receipts)
        $initialBalance = $initialBalanceQuery->sum(DB::raw("CASE WHEN transaction.Paid_In_Out = 1 THEN transaction.Amount ELSE -transaction.Amount END"));
        $initialBalance = $initialBalance === null ? 0 : $initialBalance;

        // Base query for transactions - Always include basic filters
        $query = $model->newQuery()
            ->join('file', 'file.File_ID', '=', 'transaction.File_ID')
            ->leftJoin('bankaccount', 'bankaccount.Bank_Account_ID', '=', 'transaction.Bank_Account_ID')
            ->join('paymenttype', 'paymenttype.Payment_Type_ID', '=', 'transaction.Payment_Type_ID')
            ->leftJoin('accountref', 'accountref.Account_Ref_ID', '=', 'transaction.Account_Ref_ID')
            ->whereNull('transaction.Deleted_On')
            ->where('transaction.Is_Imported', 1)
            ->where('transaction.Is_Bill', 0)
            ->where('file.Client_ID', $clientId);

        // Apply filters based on bank selection
        if ($bankAccountId === 'ledger_to_ledger') {
            $query->where('transaction.Transaction_Code', 'LIKE', 'LTLC%');
        } elseif ($bankAccountId === 'all_banks') {
            // For "All Banks" option, don't add any bank filtering (show all bank transactions)
        } elseif (!empty($bankAccountId) && $bankAccountId !== '') {
            // Specific bank selection
            $query->where('transaction.Bank_Account_ID', $bankAccountId);
        }
        // If no bank selected or empty, include all transactions (default behavior)
        if ($val = request('transTypeFilter')) {
            $query->where('paymenttype.Payment_Type_Name', 'LIKE', "%{$val}%");
        }
        if ($val = request('chequeFilter')) {
            $query->where('transaction.Cheque', 'LIKE', "%{$val}%");
        }
        if ($val = request('descriptionFilter')) {
            $query->where('transaction.Description', 'LIKE', "%{$val}%");
        }
        if ($val = request('accountRefFilter')) {
            $query->where('accountref.Reference', 'LIKE', "%{$val}%");
        }
        if ($val = request('ledgerRefFilter')) {
            $query->where('file.Ledger_Ref', 'LIKE', "%{$val}%");
        }
        if ($val = request('transactionCodeFilter')) {
            $query->where('transaction.Transaction_Code', 'LIKE', "%{$val}%");
        }
        if ($val = request('paymentsFilter')) {
            $query->where('transaction.Paid_In_Out', 2) // payments
                ->where('transaction.Amount', 'LIKE', "%{$val}%");
        }
        if ($val = request('receiptsFilter')) {
            $query->where('transaction.Paid_In_Out', 1) // receipts
                ->where('transaction.Amount', 'LIKE', "%{$val}%");
        }
        // Apply date range filter
        $query->whereBetween('transaction.Transaction_Date', [request('from_date'), request('to_date')])
            ->select([
                'transaction.Transaction_ID',
                'transaction.Transaction_Date',
                'file.Ledger_Ref',
                'transaction.Amount',
                DB::raw("COALESCE(bankaccount.Bank_Name, 'N/A') as Bank_Account_Name"),
                DB::raw("COALESCE(bankaccount.Account_No, 'N/A') as Account_No"),
                DB::raw("COALESCE(bankaccount.Sort_Code, 'N/A') as Sort_Code"),
                'paymenttype.Payment_Type_Name',
                'accountref.Reference as Account_Ref',
                'transaction.Description',
                'transaction.Cheque',
                'transaction.Transaction_Code',
                DB::raw("CASE WHEN transaction.Paid_In_Out = 2 THEN transaction.Amount ELSE 0 END AS Payments"),
                DB::raw("CASE WHEN transaction.Paid_In_Out = 1 THEN transaction.Amount ELSE 0 END AS Receipts"),
                DB::raw("SUM(CASE
                    WHEN transaction.Paid_In_Out = 1 THEN transaction.Amount
                    WHEN transaction.Paid_In_Out = 2 THEN -transaction.Amount
                    ELSE 0
                END) OVER (ORDER BY transaction.Transaction_Date ASC ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW) + $initialBalance AS Balance"),
                DB::raw("IF(transaction.Cheque IS NOT NULL AND transaction.Cheque != '', 'CHQ', '') AS Transaction_Type"),
                DB::raw("GREATEST(0, $initialBalance) AS initial_Balance"),
            ]);

        // Return the ordered query
        return $query->orderBy('transaction.Transaction_Date', 'asc');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('clientcashbook-table')
            ->columns($this->getColumns())
            ->ajax([
                'url' => route('client.cashbook'),
                'data' => 'function(d) {
        d.from_date = $("#from_date").val();
        d.to_date = $("#to_date").val();
        d.bank_account_id = $("#bank_account_id").val();

        // custom header filters
        d.transTypeFilter = $("#transTypeFilter").val();
        d.chequeFilter = $("#chequeFilter").val();
        d.descriptionFilter = $("#descriptionFilter").val();
        d.accountRefFilter = $("#accountRefFilter").val();
        d.ledgerRefFilter = $("#ledgerRefFilter").val();
        d.transactionCodeFilter = $("#transactionCodeFilter").val();
        d.paymentsFilter = $("#paymentsFilter").val();
        d.receiptsFilter = $("#receiptsFilter").val();
    }'
            ])

            ->pageLength(50)
            ->lengthMenu([[50, 100, 250, 500, 1000], [50, 100, 250, 500, 1000]])
            ->orderBy(0)
            ->parameters([
                'autoWidth'  => false,
                'responsive' => true,
                'dom'        => 'rt<"bottom d-flex justify-content-between align-items-center"lip><"clear">',
                'initComplete' => 'function(settings, json) {
                const api = this.api();

                $(api.column(0).header()).html(`
                        <div class="d-flex">
                            <div class="filter-wrapper position-relative">
                                <span id="dateTitle" class="d-inline">DATE</span>
                                <input type="date" id="dateFilter" 
                                    class="form-control form-control-sm d-none" />
                            </div>
                            <div>
                                <i class="fas fa-calendar-alt pointer table-header-icon filter-icon position-absolute top-0 end-0 d-flex" id="dateIcon"></i>
                            </div>
                        </div>
                    `);

                const headers = [
                     { index: 1, label: "TRANS TYPE", field: "transType" },
                     { index: 2, label: "CHQ NO PAY IN", field: "cheque" },
                     { index: 3, label: "DESCRIPTION", field: "description" },
                     { index: 4, label: "ACCOUNT REF", field: "accountRef" },
                     { index: 5, label: "LEDGER REF", field: "ledgerRef" },
                     { index: 6, label: "TRANSACTION CODE", field: "transactionCode" },
                     { index: 7, label: "PAYMENTS (DR)", field: "payments" },
                     { index: 8, label: "RECEIPTS (CR)", field: "receipts" },
                     { index: 9, label: "BALANCE", field: "balance" },
                ];

                headers.forEach(h => {
                    $(api.column(h.index).header()).html(`
                      <div class="d-flex">
                        <div class="filter-wrapper position-relative">
                            <span id="${h.field}Title" class="d-inline">${h.label}</span>
                            <input type="text" id="${h.field}Filter" 
                                   class="form-control form-control-sm d-none" 
                                   placeholder="Search ${h.label}" />
                        </div>
                        <div>
                             <i class="fas fa-search pointer table-header-icon filter-icon position-absolute top-0 end-0 d-flex" id="${h.field}Icon"></i>
                        </div>
                      </div>
                    `);
                });

                if (typeof attachEventListeners === "function") {
                    setTimeout(attachEventListeners, 100);
                }
            }'
            ])
            ->selectStyleSingle()
            ->buttons([
                Button::make('excel'),
                Button::make('csv'),
                Button::make('pdf'),
                Button::make('print'),
                Button::make('reset'),
                Button::make('reload')
            ]);
    }
    public function getColumns(): array
    {
        return [
            'Transaction_Date' => ['title' => 'DATE'],
            'Transaction_Type' => ['title' => 'TRANS TYPE', 'orderable' => false],
            'Cheque' => ['title' => 'CHQ NO PAY IN'],
            'Description' => ['title' => 'DESCRIPTION'],
            'Account_Ref' => ['title' => 'Account Ref'],
            'Ledger_Ref' => ['title' => 'LEDGER REF'],
            'Transaction_Code' => ['title' => 'TRANSACTION CODE'],
            'Payments' => ['title' => 'PAYMENTS (DR)'],
            'Receipts' => ['title' => 'RECEIPTS (CR)'],
            'Balance' => ['title' => 'BALANCE'],
        ];
    }

    protected function filename(): string
    {
        return 'ClientCashBook_' . date('YmdHis');
    }
}
