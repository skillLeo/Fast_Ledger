<?php

namespace App\DataTables;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class BillofcostDataTable extends DataTable
{
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
                if ($row->accountRef && $row->file) {
                    $reference = $row->accountRef->Reference ?? 'N/A';
                    $fileId = $row->file->File_ID;
                    $ledgerRef = $row->file->Ledger_Ref;

                    return '<a href="javascript:void(0);" class="ref-link text-primary" data-file-id="' . $fileId . '" data-ledger-ref="' . htmlspecialchars($ledgerRef) . '">' . e($reference) . '</a>';
                }
                return 'N/A';
            })
            ->rawColumns(['Reference'])



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
            ->rawColumns(['Reference', 'action']);
    }


    /**
     * Calculate Net Amount and VAT.
     */
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

    /**
     * Get the query source of dataTable.
     */
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
            ->where('Paid_In_Out', 2) // Filter for Paid Out
            ->where('Account_Ref_ID', 99) // Filter for Account Ref ID 99
            ->orderByDesc('Transaction_Date');

        // Optional additional filters
        if ($amountActual = request('txtAmountActual')) {
            $query->whereRaw("FLOOR(Amount * 5 / 6) = ?", [$amountActual]);
        }

        if ($amountNet = request('txtAmountNet')) {
            $query->whereRaw("FLOOR(Amount - (Amount * 5 / 6)) = ?", [$amountNet]);
        }


        // ============ ADD THESE COLUMN FILTERS BELOW ============

        // Date filter
        if ($val = request('dateFilter')) {
            $query->whereDate('transaction.Transaction_Date', $val);
        }

        // Ledger Ref filter
        if ($val = request('ledgerRefFilter')) {
            $query->whereHas('file', function ($q) use ($val) {
                $q->where('Ledger_Ref', 'LIKE', "%{$val}%");
            });
        }

        // Bank Account filter
        if ($val = request('bankAccountFilter')) {
            $query->whereHas('bankAccount', function ($q) use ($val) {
                $q->where('Account_Name', 'LIKE', "%{$val}%");
            });
        }

        // Reference filter
        if ($val = request('referenceFilter')) {
            $query->whereHas('accountRef', function ($q) use ($val) {
                $q->where('Reference', 'LIKE', "%{$val}%");
            });
        }

        // Payment Type filter
        if ($val = request('paymentTypeFilter')) {
            $query->whereHas('paymentType', function ($q) use ($val) {
                $q->where('Payment_Type_Name', 'LIKE', "%{$val}%");
            });
        }

        // Net Amount filter
        if ($val = request('netAmountFilter')) {
            $query->havingRaw("FLOOR(Amount * 5 / 6) LIKE ?", ["%{$val}%"]);
        }

        // VAT Amount filter
        if ($val = request('vatAmountFilter')) {
            $query->havingRaw("FLOOR(Amount - (Amount * 5 / 6)) LIKE ?", ["%{$val}%"]);
        }

        // Total Amount filter
        if ($val = request('totalAmountFilter')) {
            $query->where('Amount', 'LIKE', "%{$val}%");
        }

        // ============ END OF COLUMN FILTERS ============

        return $query;
    }


    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('transaction-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            // ============ ADD THIS AJAX CONFIG BELOW ============
            ->ajax([
                'data' => 'function(d) {
                d.dateFilter = $("#dateFilter").val();
                d.ledgerRefFilter = $("#ledgerRefFilter").val();
                d.bankAccountFilter = $("#bankAccountFilter").val();
                d.paidInOutFilter = $("#paidInOutFilter").val();
                d.referenceFilter = $("#referenceFilter").val();
                d.paymentTypeFilter = $("#paymentTypeFilter").val();
                d.netAmountFilter = $("#netAmountFilter").val();
                d.vatAmountFilter = $("#vatAmountFilter").val();
                d.totalAmountFilter = $("#totalAmountFilter").val();
            }'
            ])
            // ============ END OF AJAX CONFIG ============
            ->pageLength(50)
            ->pageLength(50)
            ->parameters([
                "autoWidth"    => false,
                "responsive"   => false,
                "ordering"     => false,
                "order"        => [],
                "dom" => 'rt<"bottom d-flex justify-content-between align-items-center"lip><"clear">',
                "drawCallback" => "function() { 
                $('#transaction-table td').css('text-align', 'center'); 
            }",
                // ============ ADD initComplete BELOW ============
                'initComplete' => 'function(settings, json) {
                const api = this.api();
                
                // Column 0: Date with calendar icon
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
                
                // Other columns with search icon
                const headers = [
                    { index: 1, label: "LEDGER REF", field: "ledgerRef" },
                    { index: 2, label: "BANK ACCOUNT (TYPE)", field: "bankAccount" },
                    { index: 3, label: "PAID IN/OUT", field: "paidInOut" },
                    { index: 4, label: "REFERENCE", field: "reference" },
                    { index: 5, label: "PAYMENT TYPE", field: "paymentType" },
                    { index: 6, label: "NET AMOUNT", field: "netAmount" },
                    { index: 7, label: "VAT AMOUNT", field: "vatAmount" },
                    { index: 8, label: "TOTAL AMOUNT", field: "totalAmount" },
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
                // ============ END OF initComplete ============
            ])
            ->lengthMenu([[50, 100, 250, 500, 1000], [50, 100, 250, 500, 1000]])
            ->selectStyleSingle()
            ->buttons([
                Button::make('excel'),
                Button::make('csv'),
                Button::make('pdf'),
                Button::make('print'),
                Button::make('reset'),
                Button::make('reload')
            ])->fixedColumns([
                'fixedColumns' => [
                    'leftColumns' => 1, // Fix the first column (Ledger Ref)
                ],
                'fixedHeader' => false,
            ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        $columns = [
            Column::make('Transaction_Date')->title('Date')
                ->sortable(false)
                ->width(80)
                ->addClass('no-resize no-reorder'),
            Column::make('file.Ledger_Ref')->title('Ledger Ref')->sortable(false),
            Column::make('Bank_Account_Name')->title('Bank Account (Type)')->sortable(false),
            Column::make('Paid_In_Out')->title('Paid In/Out')->sortable(false),
            Column::make('Reference')->title('Reference')->sortable(false),
            Column::make('Payment_Type_Name')->title('Payment Type')->sortable(false),
            Column::computed('Net_Amount')->title('Net Amount')->sortable(false),
            Column::computed('Vat_Amount')->title('VAT Amount')->sortable(false),
            Column::computed('Total_Amount')->title('Total Amount')->sortable(false),
        ];

        // Only add the action column if the user is admin (role 1)
        if (auth()->user()->User_Role == 1) {
            $columns[] = Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(60)
                ->addClass('text-center')
                ->title('Actions');
        }

        return $columns;
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Transaction_' . date('YmdHis');
    }
}
