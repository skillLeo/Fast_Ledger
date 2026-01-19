<?php

namespace App\DataTables;

use App\Models\Client;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class ClientDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        // dd($query);
        return (new EloquentDataTable($query))
            // ->addColumn('checkbox', fn($row) => $this->checkboxColumn($row))
            ->addColumn('status', function ($row) {
                // Check the is_archive value to determine the status
                $status = $row->is_archive ? 'Archived' : 'Active';
                $statusClass = $row->is_archive ? 'badge bg-secondary' : 'badge bg-primary';
                return "<span class='{$statusClass}'>{$status}</span>";
            })
            ->editColumn('created_on', fn($row) => $row->created_on ? $row->created_on->format('d/M/Y') : '')
            ->addColumn('action', fn($row) => $this->actionColumn())
            ->rawColumns(['checkbox', 'status', 'action']);
    }

    protected function actionColumn(): string
    {
        return '<div class="hstack gap-2 fs-15 text-center">
                    <a href="javascript:void(0);" class="btn btn-icon btn-sm btn-light"><i class="ri-download-2-line"></i></a>
                    <a href="javascript:void(0);" class="btn btn-icon btn-sm btn-light"><i class="ri-edit-line"></i></a>
                </div>';
    }

    public function query(Client $model): QueryBuilder
    {
        $type = $this->type ?? 'active';

        $query = $model->newQuery()->whereNull('deleted_on');

        if ($type === 'archived') {
            $query->where('is_archive', 1);  // Only archived clients
        } else {
            $query->where('is_archive', 0);  // Default to active clients
        }

        return $query->orderBy('client_ref');
    }


    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('client-table')
            ->columns($this->getColumns())
            ->pageLength(50) 
            ->lengthMenu([[50, 100, 250, 500, 1000], [50, 100, 250, 500, 1000]])
            // ->minifiedAjax()
            ->orderBy(1)
            ->selectStyleSingle()
            ->buttons([
                Button::make('add'),
                Button::make('excel'),
                Button::make('csv'),
                Button::make('pdf'),
                Button::make('print'),
                Button::make('reset'),
                Button::make('reload'),
            ]);
    }


    public function getColumns(): array
    {
        return [
            Column::make('Client_Ref')->title('Client Reference')->orderable(false),
            Column::make('Contact_Name')->title('Contact Name')->orderable(false),
            Column::make('Business_Name')->title('Business Name')->orderable(false),
            Column::make('Address1')->title('Address')->orderable(false),
            Column::computed('status')->title('Status')->orderable(false), 
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(60)
                ->addClass('text-center'),
        ];
    }


    protected function filename(): string
    {
        return 'Client_' . date('YmdHis');
    }
}
