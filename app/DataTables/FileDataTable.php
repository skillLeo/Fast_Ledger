<?php

namespace App\DataTables;

use App\Models\File;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class FileDataTable extends DataTable
{
    public string $feeEarnerSelectHTML;

    public function __construct()
    {
        $feeEarnerIDs = \App\Models\File::where('Client_ID', auth()->user()->Client_ID)
            ->whereNotNull('Fee_Earner')
            ->distinct()
            ->pluck('Fee_Earner')
            ->toArray();

        // Get actual User Names from Users table
        $users = \App\Models\User::whereIn('User_ID', $feeEarnerIDs)->pluck('User_Name', 'User_ID');

        $options = '<option value="">All</option>';
        foreach ($users as $id => $name) {
            $options .= '<option value="' . e($id) . '">' . e($name) . '</option>';
        }

        $this->feeEarnerSelectHTML = $options;
    }
    
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('Full_Name', function ($row) {
                return $row->First_Name . ' ' . $row->Last_Name;
            })
            ->editColumn('File_Date', function ($row) {
                return \Carbon\Carbon::parse($row->File_Date)->format('d/m/Y');
            })
            ->editColumn('Status', function ($row) {
                $statusMap = [
                    'L' => ['name' => 'Live', 'class' => 'success'],
                    'C' => ['name' => 'Close', 'class' => 'secondary'],
                    'A' => ['name' => 'Abortive', 'class' => 'danger'],
                    'I' => ['name' => 'Close Abortive', 'class' => 'warning'],
                ];

                $status = $statusMap[$row->Status] ?? ['name' => $row->Status, 'class' => 'dark'];

                return '<span class="badge bg-' . $status['class'] . '" style="padding:6px;font-size:14px;font-weight:bold;color:white;">
                            <a href="javascript:void(0);" 
                               data-id="' . $row->File_ID . '" 
                               data-status="' . $row->Status . '" 
                               data-bs-toggle="modal" 
                               data-bs-target="#statusModal" 
                               class="status-modal-trigger"
                               style="color:white;text-decoration:none;font-weight:bold;font-size:14px;">
                                ' . $status['name'] . '
                            </a>
                        </span>';
            })
            ->editColumn('Fee_Earner', function ($row) {
                return optional(\App\Models\User::find($row->Fee_Earner))->User_Name ?? $row->Fee_Earner;
            })
            ->addColumn('action', function ($row) {
                return $this->actionColumn($row);
            })
            ->rawColumns(['Status', 'action'])
            ->setRowId('File_ID');
    }

    public function query(File $model): QueryBuilder
    {
        $userClientId = auth()->user()->Client_ID;

        $query = $model->newQuery()
            ->select([
                'File_ID',
                'File_Date',
                'Ledger_Ref',
                'Matter',
                'First_Name',
                'Last_Name',
                'Address1',
                'Post_Code',
                'Fee_Earner',
                'Status'
            ])
            ->where('Client_ID', $userClientId)
            ->orderByDesc('File_ID');

        if (request()->filled('from_date') && request()->filled('to_date')) {
            $query->whereBetween('File_Date', [
                request('from_date'),
                request('to_date'),
            ]);
        }
        
          // Dynamic filters
        if ($ledger = request('ledgerRefFilter')) {
            $query->where('Ledger_Ref', 'like', "%$ledger%");
        }
        if ($matter = request('matterFilter')) {
            $query->where('Matter', 'like', "%$matter%");
        }
        if ($name = request('nameFilter')) {
            $query->where(function ($q) use ($name) {
                $q->where('First_Name', 'like', "%$name%")
                    ->orWhere('Last_Name', 'like', "%$name%")
                    ->orWhereRaw("CONCAT(First_Name, ' ', Last_Name) LIKE ?", ["%$name%"]);
            });
        }
        if ($address = request('addressFilter')) {  // ✅ Fixed: correct key
            $query->where('Address1', 'like', "%$address%");
        }
        if ($postCode = request('postCodeFilter')) {
            $query->where('Post_Code', 'like', "%$postCode%");
        }
        if ($feeEarner = request('feeEarnerFilter')) {
            $query->where('Fee_Earner', $feeEarner);
        }

        if ($status = request('statusFilter')) {
            $query->where('Status', $status);
        }
        
        
        // Reference Filter
        if ($referenceFilter = request('referenceFilter')) {
            $query->whereHas('accountRef', function ($subQuery) use ($referenceFilter) {
                $subQuery->where('Reference', 'LIKE', "%{$referenceFilter}%");
            });
        }


        return $query;
    }

    protected function actionColumn($row): string
    {
        return '<input type="checkbox" 
                       class="row-checkbox" 
                       name="selected_files[]" 
                       value="' . $row->File_ID . '" 
                       data-file-id="' . $row->File_ID . '" 
                       onclick="event.stopPropagation();">';
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('file-table')
            ->columns($this->getColumns())
            ->minifiedAjax(route('files.index'))
            ->orderBy(1)
            ->selectStyleSingle()
            ->responsive(true)
            ->pagingType('full_numbers')
            ->processing(false)
            ->pageLength(50)
            ->lengthMenu([[50, 100, 250, 500, 1000], [50, 100, 250, 500, 1000]])
             ->minifiedAjax('', null, [
                'ledgerRefFilter' => 'function() { return $("#ledgerRefFilter").val(); }',
                'matterFilter' => 'function() { return $("#matterFilter").val(); }',
                'nameFilter' => 'function() { return $("#nameFilter").val(); }',
                'addressFilter' => 'function() { return $("#addressFilter").val(); }',
                'postCodeFilter' => 'function() { return $("#postCodeFilter").val(); }',
                'feeEarnerFilter' => 'function() { return $("#feeEarnerDropdown").val(); }',
                'statusFilter' => 'function() { return $("#statusDropdown").val(); }',

            ])
            ->parameters([
                'initComplete' => 'function(settings, json) {

                // Ledger Ref (column index 1)
                $(this.api().column(2).header()).html(`
                    <div class="filter-wrapper position-relative">
                        <span id="ledgerRefTitle" class="d-inline" style="margin-right: 22px;">Ledger Ref</span>
                        <input type="text" id="ledgerRefFilter" class="form-control form-control-sm d-none" placeholder="Search" />
                        <i class="fas fa-search pointer filter-icon position-absolute top-0 end-0 me-1 mt-1" id="ledgerRefIcon"></i>
                    </div>
                `);

                // Matter (column index 2)
                $(this.api().column(3).header()).html(`
                    <div class="filter-wrapper position-relative">
                        <span id="matterTitle" class="d-inline" style="margin-right: 22px;">Matter</span>
                        <input type="text" id="matterFilter" class="form-control form-control-sm d-none" placeholder="Search" />
                        <i class="fas fa-search pointer filter-icon position-absolute top-0 end-0 me-1 mt-1" id="matterIcon"></i>
                    </div>
                `);

                // Name (column index 3)
                $(this.api().column(4).header()).html(`
                    <div class="filter-wrapper position-relative">
                        <span id="nameTitle" class="d-inline">Name</span>
                        <input type="text" id="nameFilter" class="form-control form-control-sm d-none" placeholder="Search" />
                        <i class="fas fa-search pointer filter-icon position-absolute top-0 end-0 mt-1" id="nameIcon"></i>
                    </div>
                `);

                // Address (column index 4)
                $(this.api().column(5).header()).html(`
                    <div class="filter-wrapper position-relative">
                        <span id="addressTitle" class="d-inline" style="margin-right: 22px;">Address</span>
                        <input type="text" id="addressFilter" class="form-control form-control-sm d-none" placeholder="Search" />
                        <i class="fas fa-search pointer filter-icon position-absolute top-0 end-0 me-1 mt-1" id="addressIcon"></i>
                    </div>
                `);


                // Post Code (column index 5)
                $(this.api().column(6).header()).html(`
                    <div class="filter-wrapper position-relative">
                        <span id="postCodeTitle" class="d-inline" style="margin-right: 22px;">Post Code</span>
                        <input type="text" id="postCodeFilter" class="form-control form-control-sm d-none" placeholder="Search" />
                        <i class="fas fa-search pointer filter-icon position-absolute top-0 end-0 me-1 mt-1" id="postCodeIcon"></i>
                    </div>
                `);

                const feeEarnerHTML = ' . json_encode($this->feeEarnerSelectHTML) . ';

               $(this.api().column(7).header()).html(`
                    <div class="filter-wrapper position-relative">
                        <span id="feeEarnerTitle" class="d-inline" style="margin-right: 30px;">Fee Earner</span>
                        <select id="feeEarnerDropdown" class="form-control form-control-sm d-none">
                            ${feeEarnerHTML}
                        </select>
                        <i class="fas fa-chevron-down pointer filter-icon position-absolute top-0 end-0 me-1 mt-1" id="feeEarnerIcon"></i>
                    </div>
                `);


               $(this.api().column(8).header()).html(`
                    <div class="filter-wrapper position-relative">
                        <span id="statusTitle" class="d-inline" style="margin-right: 35px;">Status</span>
                        <select id="statusDropdown" class="form-control form-control-sm d-none">
                            <option value="">All</option>
                            <option value="L">Live</option>
                            <option value="C">Close</option>
                            <option value="A">Abortive</option>
                            <option value="I">Close Abortive</option>
                        </select>
                        <i class="fas fa-chevron-down pointer filter-icon position-absolute top-0 end-0 me-0 mt-1" id="statusIcon"></i>
                    </div>
                 `);


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

    public function getColumns(): array
    {
        return [
            Column::computed('action')
                ->title('<input type="checkbox" id="select-all">')
                ->exportable(false)
                ->printable(false)
                ->width(30)
                ->addClass('text-center'),

            Column::make('File_Date')->title('Date')->orderable(false)->width(0),
            Column::make('Ledger_Ref')->title('Ledger Ref')->orderable(false),
            Column::make('Matter')->title('Matter')->orderable(false),
            Column::computed('Full_Name')->title('Name')->orderable(false), // ← Changed to computed
            Column::make('Address1')->title('Address')->orderable(false)->width(150),
            Column::make('Post_Code')->title('Post Code')->orderable(false),
            Column::make('Fee_Earner')->title('Fee Earner')->orderable(false),
            Column::make('Status')->title('Status')->orderable(false),
        ];
    }

    protected function filename(): string
    {
        return 'File_' . date('YmdHis');
    }
}
