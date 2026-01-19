<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DraftInvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'product_id',
        'item_code',
        'description',
        'chart_of_account_id',
        'ledger_ref',
        'account_ref',
        'unit_amount',
        'vat_rate',
        'vat_amount',
        'net_amount',
        'vat_form_label_id',
        'order_index',
    ];

    protected $casts = [
        'unit_amount' => 'decimal:2',
        'vat_rate' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'order_index' => 'integer',
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    /**
     * Invoice this item belongs to
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    /**
     * Chart of Account (Ledger)
     */
    public function chartOfAccount()
    {
        return $this->belongsTo(\App\Models\ChartOfAccount::class, 'chart_of_account_id');
    }

    /**
     * VAT type
     */
    public function vatFormLabel()
    {
        return $this->belongsTo(\App\Models\VatFormLabel::class, 'vat_form_label_id');
    }

    /**
     * ✅ Add product relationship
     */
    public function product()
    {
        return $this->belongsTo(\App\Models\Product::class, 'product_id');
    }
}
