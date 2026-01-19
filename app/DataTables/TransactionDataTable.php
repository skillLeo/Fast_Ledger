<?php

namespace App\DataTables;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class TransactionDataTable extends DataTable
{

    public $bankSelectHTML = '';
    public $paidInOutSelectHTML = '';
    public $paymentTypeSelectHTML = '';
    public $accountRefSelectHTML = '';
    
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('Transaction_Date', function ($row) {
                return \Carbon\Carbon::parse($row->Transaction_Date)->format('d/m/Y');
            })
            ->addColumn('checkbox', function ($row) {
                return '<input type="checkbox" class="transaction-checkbox" value="' . $row->Transaction_ID . '">';
            })
            ->addColumn('Paid_In_Out', function ($row) {
                return $row->Paid_In_Out == 1 ? 'Paid In' : ($row->Paid_In_Out == 2 ? 'Paid Out' : 'N/A');
            })
            ->addColumn('Bank_Account_Name', function ($row) {
                if ($row->bankAccount) {
                    $accountName = $row->bankAccount->Account_Name ?? 'N/A';
                    $bankType = $row->bankAccount->bankAccountType->Bank_Type ?? 'N/A';
                    return $accountName . ' (' . $bankType . ')';
                }
                return 'N/A';
            })
            ->addColumn('Reference', function ($row) {
                return $row->accountRef ? $row->accountRef->Reference : 'N/A';
            })
            ->addColumn('Payment_Type_Name', function ($row) {
                return $row->paymentType ? $row->paymentType->Payment_Type_Name : 'N/A';
            })
            ->addColumn('Net_Amount', function ($row) {
                $percentage = $row->vatType ? $row->vatType->Percentage : 0;
                $netVat = $this->calculateNetAmount($row->Amount, $percentage);
                return number_format($netVat['net'], 2);
            })
            ->addColumn('Vat_Amount', function ($row) {
                $percentage = $row->vatType ? $row->vatType->Percentage : 0;
                $netVat = $this->calculateNetAmount($row->Amount, $percentage);
                return number_format($netVat['vat'], 2);
            })
            ->addColumn('Total_Amount', function ($row) {
                return number_format($row->Amount, 2);
            })
            ->addColumn('action', function ($row) {
                if (auth()->user()->User_Role == 1) {
                    return '
                <form action="' . route('transactions.destroy', $row->Transaction_ID) . '" method="POST" style="display:inline;">
                    ' . csrf_field() . '
                    ' . method_field('DELETE') . '
                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm(\'Are you sure you want to delete this transaction?\')">
                        Delete
                    </button>
                </form>
            ';
                }
                return '';
            })
            ->setRowId('Transaction_ID')
            ->rawColumns(['checkbox', 'action']);
    }

    private function calculateNetAmount($amount, $percentage)
    {
        $netAmount = $amount;
        if ($percentage == 20) {
            $netAmount = ($amount * 5) / 6;
        } elseif ($percentage == 5) {
            $netAmount = ($amount * 20) / 21;
        }
        $vatAmount = $amount - $netAmount;

        return [
            'net' => $netAmount,
            'vat' => $vatAmount,
        ];
    }

    public function query(Transaction $model): QueryBuilder
    {
        $clientId = auth()->user()->Client_ID;
        $userRole = auth()->user()->Role;
        $userRoleId = $userRole ? $userRole->Role_ID : null;
        $userId = null;

        if ($userRoleId == 3) {
            $userId = auth()->id();
        }

        $query = $model->newQuery()
            ->with([
                'file.client',
                'bankAccount.bankAccountType',
                'paymentType',
                'accountRef',
                'vatType',
            ])
            ->whereHas('file.client', function ($query) use ($clientId) {
                $query->where('Client_ID', $clientId);
            })
            ->where('Is_Imported', 1)
            ->whereNull('transaction.Deleted_On')
            ->orderByDesc('Transaction_Date');

        if ($amountActual = request('txtAmountActual')) {
            $query->whereRaw("FLOOR(Amount * 5 / 6) = ?", [$amountActual]);
        }

        if ($amountNet = request('txtAmountNet')) {
            $query->whereRaw("FLOOR(Amount - (Amount * 5 / 6)) = ?", [$amountNet]);
        }

        if ($ledgerRef = request('ledgerRefFilter')) {
            $query->whereHas('file', function ($subQuery) use ($ledgerRef) {
                $subQuery->where('Ledger_Ref', 'LIKE', "%{$ledgerRef}%");
            });
        }

        if ($bankAccountFilter = request('bankAccountFilter')) {
            $query->where('Bank_Account_ID', $bankAccountFilter);
        }

        if ($paidInOutFilter = request('paidInOutFilter')) {
            $query->where('Paid_In_Out', $paidInOutFilter);
        }

        if ($paymentTypeFilter = request('paymentTypeFilter')) {
            $query->where('Payment_Type_ID', $paymentTypeFilter);
        }

        if ($referenceFilter = request('referenceFilter')) {
            $query->whereHas('accountRef', function ($subQuery) use ($referenceFilter) {
                $subQuery->where('Reference', 'LIKE', "%{$referenceFilter}%");
            });
        }

        return $query;
    }

     public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('transaction-table')
            ->columns($this->getColumns())
            ->pageLength(50)
            ->pagingType('full_numbers')
            ->lengthMenu([[50, 100, 250, 500, 1000], [50, 100, 250, 500, 1000]])
            ->minifiedAjax(route('transactions.imported'), null, [
                'ledgerRefFilter'   => 'function() { return $("#ledgerRefFilter").val(); }',
                'bankAccountFilter' => 'function() { return $("#bankAccountFilter").val(); }',
                'paidInOutFilter'   => 'function() { return $("#paidInOutFilter").val(); }',
                'paymentTypeFilter' => 'function() { return $("#paymentTypeFilter").val(); }',
                'referenceFilter'   => 'function() { return $("#referenceFilter").val(); }',

                'referenceFilter'   => 'function() { return $("#referenceFilter").val(); }',
                'netAmountFilter'   => 'function() { return $("#netAmountFilter").val(); }',
                'vatAmountFilter'   => 'function() { return $("#vatAmountFilter").val(); }',
                'totalAmountFilter' => 'function() { return $("#totalAmountFilter").val(); }',
            ])
            ->parameters([
                "autoWidth"    => false,
                "responsive"   => false,
                "ordering"     => false,
                "order"        => [],
                
                // Enable ColReorder with fixed columns
                "colReorder"   => [
                    "fixedColumnsLeft" => 2,  // Fix first 2 columns
                    "realtime" => false       // Disable realtime for better performance
                ],
                
                // Enable Column Resizing with exclusions
                "colResize" => [
                    "exclude" => [0, 1],      // Exclude first 2 columns from resizing
                    "tableWidthFixed" => false,
                ],
                
                "columnDefs" => [
                    [
                        "targets" => [0, 1],     // First 2 columns
                        "width" => "30px",       // Checkbox width
                        "className" => "no-resize no-reorder",
                        "orderable" => false,
                        "searchable" => false,
                        "resizable" => false     // Explicitly disable resizing
                    ],
                    [
                        "targets" => [1],        // Date column
                        "width" => "80px",
                        "className" => "no-resize no-reorder",
                        "resizable" => false     // Explicitly disable resizing
                    ],
                    [
                        "targets" => "_all",     // All other columns
                        "className" => "resizable-column reorderable-column"
                    ]
                ],
                
                "dom" => 'rt<"bottom d-flex justify-content-between align-items-center"lip><"clear">',

                'initComplete' => 'function(settings, json) {
                    const bankAccountHTML = `' . $this->bankSelectHTML . '`;
                    const paidInOutHTML = `' . $this->paidInOutSelectHTML . '`;
                    const paymentTypeHTML = `' . $this->paymentTypeSelectHTML . '`;

                    // Get the API instance
                        const api = this.api();
                        
                        // Check how many columns exist
                        const columnCount = api.columns().count();
                        
                     if (api.column(1).header()) {
                        $(api.column(1).header()).html(`
                            <div class="d-flex">
                                <div class="filter-wrapper position-relative">
                                    <span id="dateTitle" class="d-inline" style="margin-right: 22px;">Date</span>
                                    <input type="date" id="dateFilter" class="form-control form-control-sm d-none" />
                                </div>
                                <div>
                                    <i class="fas fa-calendar-alt pointer table-header-icon filter-icon position-absolute top-0 end-0 d-flex" id="dateIcon"></i>
                                </div>
                            </div>
                        `);
                    }
                    
                    // Setup filter headers
                    $(this.api().column(2).header()).html(`
                      <div class="d-flex" >
                        <div class="filter-wrapper position-relative">
                            <span id="ledgerRefTitle" class="d-inline" style="margin-right: 22px;">Ledger Ref</span>
                            <input type="text" id="ledgerRefFilter" class="form-control form-control-sm d-none" placeholder="Search Ledger Ref" />
                        </div>
                        <div>
                            <i class="fas fa-search pointer table-header-icon filter-icon table-header-icon position-absolute top-0 end-0 d-flex" id="ledgerRefIcon"></i>
                        </div>
                      </div>
                    `);

                    $(this.api().column(3).header()).html(`
                      <div class="d-flex" >
                        <div class="filter-wrapper position-relative">
                            <span id="bankAccountTitle" class="d-inline">Bank Account</span>
                            <select id="bankAccountFilter" class="form-control form-control-sm d-none">
                                ${bankAccountHTML}
                            </select>
                        </div>
                        <div>
                            <i class="fas fa-chevron-down pointer table-header-icon filter-icon position-absolute top-0 end-0 d-flex" id="bankAccountIcon"></i>
                        </div>
                      </div>
                    `);

                    $(this.api().column(4).header()).html(`
                      <div class="d-flex" >
                        <div class="filter-wrapper position-relative">
                            <span id="paidInOutTitle" class="d-inline">Paid In/Out</span>
                            <select id="paidInOutFilter" class="form-control form-control-sm d-none">
                                ${paidInOutHTML}
                            </select>
                        </div>
                        <div>
                            <i class="fas fa-chevron-down pointer table-header-icon filter-icon position-absolute top-0 end-0 d-flex" id="paidInOutIcon"></i>
                        </div>
                      </div>
                    `);

                    $(this.api().column(5).header()).html(`
                      <div class="d-flex" >
                        <div class="filter-wrapper position-relative">
                            <span id="referenceTitle" class="d-inline" style="margin-right: 22px;">Reference</span>
                            <input type="text" id="referenceFilter" class="form-control form-control-sm d-none" placeholder="Search Reference" />
                        </div>
                        <div>
                            <i class="fas fa-search pointer table-header-icon filter-icon position-absolute top-0 end-0 d-flex" id="referenceIcon"></i>
                        </div>
                      </div>
                    `);

                    $(this.api().column(6).header()).html(`
                      <div class="d-flex" >
                        <div class="filter-wrapper position-relative">
                            <span id="paymentTypeTitle" class="d-inline">Payment Type</span>
                            <select id="paymentTypeFilter" class="form-control form-control-sm d-none">
                                ${paymentTypeHTML}
                            </select>
                        </div>
                        <div>
                            <i class="fas fa-chevron-down pointer table-header-icon filter-icon position-absolute top-0 end-0 d-flex" id="paymentTypeIcon"></i>
                        </div>
                      </div>
                    `);
                    

                        $(this.api().column(7).header()).html(`
                        <div class="d-flex" >
                            <div class="filter-wrapper position-relative">
                                <span id="amountTitle" class="d-inline" style="margin-right: 22px;">Amount</span>
                                <input type="text" id="amountFilter" class="form-control form-control-sm d-none" placeholder="Search Amount" />
                            </div>
                            <div>
                                <i class="fas fa-search pointer table-header-icon filter-icon position-absolute top-0 end-0 d-flex" id="amountIcon"></i>
                            </div>
                        </div>
                        `);

                        $(this.api().column(8).header()).html(`
                        <div class="d-flex" >
                            <div class="filter-wrapper position-relative">
                                <span id="balanceTitle" class="d-inline" style="margin-right: 22px;">Balance</span>
                                <input type="text" id="balanceFilter" class="form-control form-control-sm d-none" placeholder="Search Balance" />
                            </div>
                            <div>
                                <i class="fas fa-search pointer table-header-icon filter-icon position-absolute top-0 end-0 d-flex" id="balanceIcon"></i>
                            </div>
                        </div>
                        `);

                        $(this.api().column(9).header()).html(`
                        <div class="d-flex" >
                            <div class="filter-wrapper position-relative">
                                <span id="descriptionTitle" class="d-inline" style="margin-right: 22px;">Description</span>
                                <input type="text" id="descriptionFilter" class="form-control form-control-sm d-none" placeholder="Search Description" />
                            </div>
                            <div>
                                <i class="fas fa-search pointer table-header-icon filter-icon position-absolute top-0 end-0 d-flex" id="descriptionIcon"></i>
                            </div>
                        </div>
                        `);

                    // Initialize features after DataTable is ready
                    if (typeof initializeTableFeatures === "function") {
                        setTimeout(initializeTableFeatures, 200);
                    }
                }'
            ])
            ->orderBy(1)
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
        $columns = [
            Column::computed('checkbox')
                ->exportable(false)
                ->printable(false)
                ->width(30)
                ->addClass('text-center no-resize no-reorder')
                ->title('<input type="checkbox" id="select-all" />')
                ->orderable(false)
                ->searchable(false)
                ->escape(false),

            Column::make('Transaction_Date')
                ->title('Date')
                ->sortable(false)
                ->width(80)
                ->addClass('no-resize no-reorder'),
            
            Column::make('file.Ledger_Ref')
                ->title('Ledger Ref')
                ->sortable(false)
                ->addClass('resizable-column reorderable-column'),
            
            Column::make('Bank_Account_Name')
                ->title('Bank Account (Type)')
                ->sortable(false)
                ->addClass('resizable-column reorderable-column'),
            
            Column::make('Paid_In_Out')
                ->title('Paid In/Out')
                ->sortable(false)
                ->addClass('resizable-column reorderable-column'),
            
            Column::make('Reference')
                ->title('Reference')
                ->sortable(false)
                ->addClass('resizable-column reorderable-column'),
            
            Column::make('Payment_Type_Name')
                ->title('Payment Type')
                ->sortable(false)
                ->addClass('resizable-column reorderable-column'),
            
            Column::computed('Net_Amount')
                ->title('Net Amount')
                ->sortable(false)
                ->className('text-end resizable-column reorderable-column'),
            
            Column::computed('Vat_Amount')
                ->title('VAT Amount')
                ->sortable(false)
                ->className('text-end resizable-column reorderable-column'),
            
            Column::computed('Total_Amount')
                ->title('Total Amount')
                ->sortable(false)
                ->className('text-end resizable-column reorderable-column'),
        ];

        if (auth()->user()->User_Role == 1) {
            $columns[] = Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(60)
                ->addClass('text-center resizable-column reorderable-column')
                ->title('Actions');
        }

        return $columns;
    }

    protected function filename(): string
    {
        return 'Transaction_' . date('YmdHis');
    }
}