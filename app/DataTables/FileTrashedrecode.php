<?php

namespace App\DataTables;

use App\Models\File;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Services\DataTable;

class FileTrashedrecode extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('Full_Name', function ($row) {
                return $row->First_Name . ' ' . $row->Last_Name;
            })
            ->addColumn('action', function ($row) {
                return '<input type="checkbox" 
                            class="row-checkbox" 
                            name="selected_files[]" 
                            value="' . $row->File_ID . '" 
                            onclick="event.stopPropagation();">';
            })
            ->rawColumns(['action']);
    }

    public function query(File $model): QueryBuilder
    {
        return $model->onlyTrashed()
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
            ])
            ->where('Client_ID', auth()->user()->Client_ID)
            ->orderByDesc('File_ID');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('trashed-file-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(1)
            ->selectStyleSingle()
            ->responsive(true)
            ->pageLength(50)
            ->lengthMenu([[50, 100, 250, 500], [50, 100, 250, 500]])
            ->buttons([
                Button::make('excel')->addClass('btn btn-success'),
                Button::make('csv')->addClass('btn btn-primary'),
                Button::make('print')->addClass('btn btn-secondary'),
                Button::make('reset')->addClass('btn btn-warning'),
                Button::make('reload')->addClass('btn btn-info'),
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

            Column::make('File_Date')->title('Date'),
            Column::make('Ledger_Ref')->title('Ledger Ref'),
            Column::make('Matter')->title('Matter'),
            Column::computed('Full_Name')->title('Name'),
            Column::make('Address1')->title('Address'),
            Column::make('Post_Code')->title('Post Code'),
            Column::make('Fee_Earner')->title('Fee Earner'),
        ];
    }

    protected function filename(): string
    {
        return 'Trashed_Files_' . date('YmdHis');
    }
}
