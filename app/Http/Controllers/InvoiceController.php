<?php

namespace App\Http\Controllers;

class InvoiceController extends BaseInvoiceController
{
    /**
     * ✅ Filter for SALES invoices only (SIN, SCN)
     */
    protected function getInvoiceTypeFilter($query)
    {
        return $query->where(function ($q) {
            $q->where('invoice_no', 'LIKE', 'SIN%')
              ->orWhere('invoice_no', 'LIKE', 'SCN%');
        });
    }

    /**
     * ✅ Type name for UI
     */
    protected function getTypeName(): string
    {
        return 'Invoice';
    }

    /**
     * ✅ Route prefix
     */
    protected function getRoutePrefix(): string
    {
        return 'invoices';
    }

    /**
     * ✅ View path
     */
    protected function getViewPath(): string
    {
        return 'admin.invoices';
    }
}