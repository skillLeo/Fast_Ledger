<?php

namespace App\DataTables;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class FeeEarnerDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('Last_Login_DateTime', function ($row) {
                return \Carbon\Carbon::parse($row->Last_Login_DateTime)->format('d/m/Y');
            })
            ->editColumn('Is_Archive', function ($row) {
                return $row->Is_Archive ? 'Inactive' : 'Active';
            })
        
            ->setRowId(function ($row) {
                return $row->User_ID;
            });
    }
    


    /**
     * Get the query source of dataTable.
     */
    public function query(User $model): QueryBuilder
    {
        $userClientId = auth()->user()->Client_ID;

        return $model->newQuery()
            ->select([
                'User_ID',
                'Full_Name',
                'User_Name',
                'email',
                'Is_Archive',
                'Last_Login_DateTime'
            ])
            ->where('Client_ID', $userClientId);
    }

    /**
     * Optional method if you want to use the DataTable in an HTML table.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('Fee-Earner-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(1)
            ->processing(false)
            ->pageLength(50)  
            ->lengthMenu([[50, 100, 250, 500, 1000], [50, 100, 250, 500, 1000]]) 
            ->selectStyleSingle()
            ->scrollX(true)
            ->responsive(true)
            ->dom('Bflrtip')
            ->buttons([
                Button::make('excel')->addClass('btn btn-success'),
                Button::make('csv')->addClass('btn btn-primary'),
                Button::make('pdf')->addClass('btn btn-danger'),
                Button::make('print')->addClass('btn btn-secondary'),
                Button::make('reset')->addClass('btn btn-warning'),
                Button::make('reload')->addClass('btn btn-info')
            ])
            ->parameters([
                'createdRow' => 'function(row, data, dataIndex) {
                    $(row).attr("data-userid", data.User_ID); // Set User_ID on row
                    $(row).attr("style", "cursor: pointer"); // Change cursor to pointer
                }'
            ]);
    }
    
    /**
     * Get the table columns.
     */
    public function getColumns(): array
    {
        return [
            Column::make('Full_Name')->title('Full Name'),
            Column::make('User_Name')->title('User Name'),
            Column::make('email')->title('Email'),
            Column::computed('Is_Archive')->title('Status'),
            Column::make('Last_Login_DateTime')->title('Last Login'),
      
        ];
    }
    
}
