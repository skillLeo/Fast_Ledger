<?php

namespace App\DataTables;

use App\Models\Transaction;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

class DayBookDataTable extends DataTable
{

    protected $bankSelectHTML;
    protected $paidInOutSelectHTML;
    protected $paymentTypeSelectHTML;
    protected $accountRefSelectHTML;

    public function __construct($bankSelectHTML = '', $paidInOutSelectHTML = '', $paymentTypeSelectHTML = '', $accountRefSelectHTML = '')
    {
        $this->bankSelectHTML = $bankSelectHTML;
        $this->paidInOutSelectHTML = $paidInOutSelectHTML;
        $this->paymentTypeSelectHTML = $paymentTypeSelectHTML;
        $this->accountRefSelectHTML = $accountRefSelectHTML;
    }
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('Transaction_Date', function ($row) {
                return \Carbon\Carbon::parse($row->Transaction_Date)->format('d/m/Y'); // Display date only
            })
            ->addColumn('Paid_In_Out', function ($row) {
                return $row->Paid_In_Out == 1 ? 'Paid In' : ($row->Paid_In_Out == 2 ? 'Paid Out' : 'N/A'); // Map Paid In/Out
            })
            ->addColumn('checkbox', function ($row) {
                return '<input type="checkbox" class="transaction-checkbox" value="' . $row->Transaction_ID . '">';
            })

            ->addColumn('Bank_Account_Name', function ($row) {
                if ($row->Is_Bill == 1) {
                    return 'Bill of Costs';
                } elseif ($row->bankAccount) {
                    $accountName = $row->bankAccount->Account_Name ?? 'N/A';
                    $bankType = $row->bankAccount->bankAccountType->Bank_Type ?? 'N/A';
                    return e($accountName . ' (' . $bankType . ')');  // Escape output
                }
                return 'N/A';
            })

             ->addColumn('Reference', function ($row) {
                // Check for File first - use accountRef relationship
                if ($row->File_ID && $row->accountRef) {
                    return $row->accountRef->Reference;
                }
                // Then check for Chart of Account - use account_ref column
                elseif ($row->chart_of_account_id && $row->chartOfAccount) {
                    return $row->chartOfAccount->account_ref ?? 'N/A';
                }

                return 'N/A';
            })
            ->addColumn('Payment_Type_Name', function ($row) {
                return $row->paymentType ? $row->paymentType->Payment_Type_Name : 'N/A';
            })
            ->addColumn('Net_Amount', function ($row) {
                $percentage = $row->vatType ? $row->vatType->Percentage : 0;
                $netVat = $this->calculateNetAmount($row->Amount, $percentage);
                return number_format($netVat['net'], 2);  // Ensure two decimal places
            })
            ->addColumn('Vat_Amount', function ($row) {
                $percentage = $row->vatType ? $row->vatType->Percentage : 0;
                $netVat = $this->calculateNetAmount($row->Amount, $percentage);
                return number_format($netVat['vat'], 2);  // Ensure two decimal places
            })
            ->addColumn('Total_Amount', function ($row) {
                return number_format($row->Amount, 2);  // Adds a total amount column with 2 decimals
            })

            ->addColumn('Ledger_Ref', function ($row) {
                $ledgerRef = 'N/A';

                // Check for File first
                if ($row->File_ID && $row->file) {
                    $ledgerRef = $row->file->Ledger_Ref ?? 'N/A';
                }
                // Then check for Chart of Account
                elseif ($row->chart_of_account_id && $row->chartOfAccount) {
                    $ledgerRef = $row->chartOfAccount->ledger_ref ?? 'N/A';
                }

                return '<a href="' . route('transactions.edit', $row->Transaction_ID) . '" class="text-primary">' . e($ledgerRef) . '</a>';
            })
            ->setRowId('Transaction_ID')
            ->rawColumns(['Ledger_Ref', 'Is_Imported', 'checkbox']);
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
        $clientId = auth()->user()->User_ID;
        $userRole = auth()->user()->Role;
        $userRoleId = $userRole ? $userRole->Role_ID : null;
        $userId = null;

        if ($userRoleId == 3) {
            $userId = auth()->id();
        }

        $query = $model->newQuery()
            ->with([
                'file.client',
                'chartOfAccount',  // ADD THIS LINE
                'bankAccount.bankAccountType',
                'paymentType',
                'accountRef',
                'vatType',
                'vatFormLabel.vatType',  // ADD THIS LINE for VAT calculation
            ])
            ->where(function ($query) use ($clientId) {
                // Case 1: Has file_id with matching client
                $query->whereHas('file.client', function ($subQuery) use ($clientId) {
                    $subQuery->where('Client_ID', $clientId);
                })
                    // Case 2: file_id is NULL but has chart_of_account_id
                     ->orWhere(function ($subQuery) use ($clientId) {
                        $subQuery->whereNull('File_ID')
                            ->whereNotNull('chart_of_account_id')
                            ->where('Created_By', $clientId); // Filter by creator
                    });
            })
            ->where('Is_Imported', 0)
            ->whereNull('transaction.Deleted_On')
            ->orderByDesc('Transaction_Date');

        // dd('here', $query);
        // Filter: Bank Account ID
        if ($bankAccount = request('bank_account')) {
            $query->where('Bank_Account_ID', $bankAccount);
        }

        // Filter: Paid In / Paid Out
        if ($paidInOut = request('paid_in_out')) {
            $query->where('Paid_In_Out', $paidInOut);
        }

        // Filter: Payment Type
        if ($paymentType = request('payment_type')) {
            $query->where('Payment_Type_ID', $paymentType);
        }

        // Enhanced Ledger Ref Filter - supports partial matching
        if ($ledgerRef = request('ledgerRefFilter')) {
            $query->whereHas('file', function ($subQuery) use ($ledgerRef) {
                $subQuery->where('Ledger_Ref', 'LIKE', "%{$ledgerRef}%");
            });
        }

        // Enhanced Bank Account Filter - use the existing parameter
        if ($bankAccountFilter = request('bankAccountFilter')) {
            $query->where('Bank_Account_ID', $bankAccountFilter);
        }

        // Enhanced Paid In/Out Filter
        if ($paidInOutFilter = request('paidInOutFilter')) {
            $query->where('Paid_In_Out', $paidInOutFilter);
        }

        // Enhanced Payment Type Filter  
        if ($paymentTypeFilter = request('paymentTypeFilter')) {
            $query->where('Payment_Type_ID', $paymentTypeFilter);
        }

        // FIX: Account Ref Filter - Change from account_ref to accountRefFilter
        if ($accountRefFilter = request('accountRefFilter')) {
            $query->where('Account_Ref_ID', $accountRefFilter);
        }

        // Filter: Reference
        if ($reference = request('referenceFilter')) {
            $query->whereHas('accountRef', function ($subQuery) use ($reference) {
                $subQuery->where('Reference', 'LIKE', "%{$reference}%");
            });
        }

        // Filter: Transaction Date
        if ($transDate = request('transDateFilter')) {
            $query->whereDate('Transaction_Date', $transDate);
        }

        // Filter: Net Amount
        if ($amountActual = request('txtAmountActual')) {
            $query->whereRaw("FLOOR(Amount * 5 / 6) = ?", [$amountActual]);
        }

        // Filter: VAT Amount
        if ($amountNet = request('txtAmountNet')) {
            $query->whereRaw("FLOOR(Amount - (Amount * 5 / 6)) = ?", [$amountNet]);
        }

        return $query;
    }


    /**
     * Optional method if you want to use the html builder.
     */
   public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('daybook-table')
            ->columns($this->getColumns())
            ->minifiedAjax(route('transactions.index'))
            ->orderBy(1)
            ->selectStyleSingle()
            ->responsive(true)
            ->pagingType('full_numbers')
            ->pageLength(50)
            ->lengthMenu([[50, 100, 250, 500, 1000], [50, 100, 250, 500, 1000]])
            ->minifiedAjax('', null, [
                'ledgerRefFilter'   => 'function() { return $("#ledgerRefFilter").val(); }',
                'bankAccountFilter' => 'function() { return $("#bankAccountFilter").val(); }',
                'paidInOutFilter'   => 'function() { return $("#paidInOutFilter").val(); }',
                'accountRefFilter'  => 'function() { return $("#accountRefFilter").val(); }',
                'paymentTypeFilter' => 'function() { return $("#paymentTypeFilter").val(); }',
                'referenceFilter'   => 'function() { return $("#referenceFilter").val(); }',
            ])
           ->parameters([
                        'dom' => 'rt<"bottom d-flex justify-content-between align-items-center"lip><"clear">',
                        
                        'initComplete' => 'function(settings, json) {
                            const bankAccountHTML = ' . json_encode($this->bankSelectHTML) . ';
                            const paidInOutHTML = ' . json_encode($this->paidInOutSelectHTML) . ';
                            const accountRefHTML = ' . json_encode($this->accountRefSelectHTML) . ';
                            const paymentTypeHTML = ' . json_encode($this->paymentTypeSelectHTML) . ';
                    
                            // Ledger Ref (column index 2)
                            $(this.api().column(2).header()).html(`
                                <div class="filter-wrapper position-relative">
                                    <span id="ledgerRefTitle" class="d-inline" style="margin-right: 22px;">Ledger Ref</span>
                                    <input type="text" id="ledgerRefFilter" class="form-control form-control-sm d-none" placeholder="Search" />
                                    <i class="fas fa-search pointer filter-icon position-absolute top-0 end-0 me-1 mt-1" id="ledgerRefIcon"></i>
                                </div>
                            `);
                    
                            // ... baaki ka code same rahega
                            
                            if (typeof attachEventListeners === "function") {
                                setTimeout(attachEventListeners, 100);
                            }
                        }'
                    ])
            ->buttons([
                Button::make('excel')->addClass('btn btn-success'),
                Button::make('csv')->addClass('btn btn-primary'),
                Button::make('pdf')->addClass('btn btn-danger'),
                Button::make('print')->addClass('btn btn-secondary'),
                Button::make('reset')->addClass('btn btn-warning'),
                Button::make('reload')->addClass('btn btn-info')
            ]);
    }

    // Update the getColumns method to change Account Ref to dropdown
    public function getColumns(): array
    {
        return [
            Column::computed('checkbox')
                ->exportable(false)
                ->printable(false)
                ->width(30)
                ->addClass('text-center')
                ->title('<input type="checkbox" id="select-all" />')
                ->orderable(false)
                ->searchable(false)
                ->escape(false),

            Column::make('Transaction_Date')->title('Date')->orderable(false),

            Column::make('Ledger_Ref')
                ->title('Ledger Ref')
                ->escape(false)->orderable(false),

            // Simplified titles - HTML will be added via headerCallback
            Column::make('Bank_Account_Name')->title('Bank Account')->orderable(false),
            Column::make('Paid_In_Out')->title('Paid In/Out')->orderable(false),
            Column::make('Reference')->title('Account Ref')->orderable(false),
            Column::make('Payment_Type_Name')->title('Payment Type')->orderable(false),

            Column::computed('Net_Amount')->title('Net Amount')->orderable(false),
            Column::computed('Vat_Amount')->title('VAT Amount')->orderable(false),
            Column::computed('Total_Amount')->title('Total Amount')->orderable(false),
        ];
    }


    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'DayBook_' . date('YmdHis');
    }
}
