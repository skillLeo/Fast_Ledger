<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VatSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'vrn',
        'period_key',
        'vat_due_sales',
        'vat_due_acquisitions',
        'total_vat_due',
        'vat_reclaimed_curr_period',
        'net_vat_due',
        'total_value_sales_ex_vat',
        'total_value_purchases_ex_vat',
        'total_value_goods_supplied_ex_vat',
        'total_acquisitions_ex_vat',
        'submitted_by_user_id',
        'submitted_at',
        'hmrc_response',
        'successful',
        'processing_date',
        'error_message',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'hmrc_response' => 'array',
        'successful' => 'boolean',
        'vat_due_sales' => 'decimal:2',
        'vat_due_acquisitions' => 'decimal:2',
        'total_vat_due' => 'decimal:2',
        'vat_reclaimed_curr_period' => 'decimal:2',
        'net_vat_due' => 'decimal:2',
        'total_value_sales_ex_vat' => 'decimal:2',
        'total_value_purchases_ex_vat' => 'decimal:2',
        'total_value_goods_supplied_ex_vat' => 'decimal:2',
        'total_acquisitions_ex_vat' => 'decimal:2',
    ];

    // Relationships
    public function submittedBy()
    {
        return $this->belongsTo(User::class, 'submitted_by_user_id', 'User_ID');
    }

    // Scopes
    public function scopeSuccessful($query)
    {
        return $query->where('successful', true);
    }

    public function scopeFailed($query)
    {
        return $query->where('successful', false);
    }

    public function scopeForPeriod($query, string $periodKey)
    {
        return $query->where('period_key', $periodKey);
    }
}