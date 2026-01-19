<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class HmrcAnnualSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'business_id',
        'nino',
        'tax_year',
        'submission_date',
        'adjustments_json',
        'allowances_json',
        'non_financials_json',
        'response_json',
        'status',
        'notes'
    ];

    protected $casts = [
        'submission_date' => 'datetime',
        'adjustments_json' => 'array',
        'allowances_json' => 'array',
        'non_financials_json' => 'array',
        'response_json' => 'array',
    ];

    /**
     * Relationships
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'User_ID');
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(HmrcBusiness::class, 'business_id', 'business_id');
    }

    /**
     * Accessors
     */
    public function getStatusBadgeAttribute(): array
    {
        return match($this->status) {
            'submitted' => [
                'class' => 'success',
                'icon' => 'fa-check-circle',
                'text' => 'Submitted'
            ],
            'failed' => [
                'class' => 'danger',
                'icon' => 'fa-times-circle',
                'text' => 'Failed'
            ],
            'draft' => [
                'class' => 'secondary',
                'icon' => 'fa-file',
                'text' => 'Draft'
            ],
            default => [
                'class' => 'secondary',
                'icon' => 'fa-question',
                'text' => 'Unknown'
            ]
        };
    }

    public function getTaxYearLabelAttribute(): string
    {
        return 'Tax Year ' . $this->tax_year;
    }

    /**
     * Get total allowances amount
     */
    public function getTotalAllowancesAttribute(): float
    {
        if (!$this->allowances_json) {
            return 0.0;
        }

        $total = 0.0;
        foreach ($this->allowances_json as $key => $value) {
            if (is_numeric($value)) {
                $total += (float) $value;
            }
        }

        return $total;
    }

    /**
     * Get net income adjustment
     */
    public function getNetIncomeAdjustmentAttribute(): float
    {
        if (!$this->adjustments_json || !isset($this->adjustments_json['income_adjustment'])) {
            return 0.0;
        }

        $total = 0.0;
        foreach ($this->adjustments_json['income_adjustment'] as $value) {
            if (is_numeric($value)) {
                $total += (float) $value;
            }
        }

        return $total;
    }

    /**
     * Get net expense adjustment
     */
    public function getNetExpenseAdjustmentAttribute(): float
    {
        if (!$this->adjustments_json || !isset($this->adjustments_json['expense_adjustment'])) {
            return 0.0;
        }

        $total = 0.0;
        foreach ($this->adjustments_json['expense_adjustment'] as $value) {
            if (is_numeric($value)) {
                $total += (float) $value;
            }
        }

        return $total;
    }

    /**
     * Scopes
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeSubmitted($query)
    {
        return $query->where('status', 'submitted');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForBusiness($query, string $businessId)
    {
        return $query->where('business_id', $businessId);
    }

    public function scopeForTaxYear($query, string $taxYear)
    {
        return $query->where('tax_year', $taxYear);
    }

    /**
     * Check if submission can be edited
     */
    public function canEdit(): bool
    {
        return $this->status === 'draft' || $this->status === 'failed';
    }

    /**
     * Check if submission can be submitted
     */
    public function canSubmit(): bool
    {
        return $this->status === 'draft' || $this->status === 'failed';
    }

    /**
     * Check if submission can be deleted
     */
    public function canDelete(): bool
    {
        return $this->status === 'draft';
    }
}



