<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class HmrcUkPropertyPeriodSummary extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'business_id',
        'obligation_id',
        'nino',
        'tax_year',
        'submission_id',
        'from_date',
        'to_date',
        'fhl_income_json',
        'fhl_expenses_json',
        'non_fhl_income_json',
        'non_fhl_expenses_json',
        'uk_property_income_json',
        'uk_property_expenses_json',
        'response_json',
        'status',
        'test_scenario',
        'submission_date',
    ];

    protected $casts = [
        'from_date' => 'date',
        'to_date' => 'date',
        'submission_date' => 'datetime',
        'fhl_income_json' => 'array',
        'fhl_expenses_json' => 'array',
        'non_fhl_income_json' => 'array',
        'non_fhl_expenses_json' => 'array',
        'uk_property_income_json' => 'array',
        'uk_property_expenses_json' => 'array',
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

    public function obligation(): BelongsTo
    {
        return $this->belongsTo(HmrcObligation::class, 'obligation_id');
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

    public function getPeriodLabelAttribute(): string
    {
        return $this->from_date->format('d M Y') . ' - ' . $this->to_date->format('d M Y');
    }

    public function getTaxYearLabelAttribute(): string
    {
        return 'Tax Year ' . $this->tax_year;
    }

    /**
     * Get total FHL income
     */
    public function getTotalFhlIncomeAttribute(): float
    {
        if (!$this->fhl_income_json) {
            return 0.0;
        }

        $total = 0.0;
        foreach ($this->fhl_income_json as $key => $value) {
            if (is_numeric($value)) {
                $total += (float) $value;
            } elseif (is_array($value)) {
                foreach ($value as $subValue) {
                    if (is_numeric($subValue)) {
                        $total += (float) $subValue;
                    }
                }
            }
        }

        return $total;
    }

    /**
     * Get total FHL expenses
     */
    public function getTotalFhlExpensesAttribute(): float
    {
        if (!$this->fhl_expenses_json) {
            return 0.0;
        }

        $total = 0.0;
        foreach ($this->fhl_expenses_json as $key => $value) {
            if (is_numeric($value)) {
                $total += (float) $value;
            }
        }

        return $total;
    }

    /**
     * Get total Non-FHL income
     */
    public function getTotalNonFhlIncomeAttribute(): float
    {
        if (!$this->non_fhl_income_json) {
            return 0.0;
        }

        $total = 0.0;
        foreach ($this->non_fhl_income_json as $key => $value) {
            if (is_numeric($value)) {
                $total += (float) $value;
            } elseif (is_array($value)) {
                foreach ($value as $subValue) {
                    if (is_numeric($subValue)) {
                        $total += (float) $subValue;
                    }
                }
            }
        }

        return $total;
    }

    /**
     * Get total Non-FHL expenses
     */
    public function getTotalNonFhlExpensesAttribute(): float
    {
        if (!$this->non_fhl_expenses_json) {
            return 0.0;
        }

        $total = 0.0;
        foreach ($this->non_fhl_expenses_json as $key => $value) {
            if (is_numeric($value)) {
                $total += (float) $value;
            }
        }

        return $total;
    }

    /**
     * Get combined total income
     * For TY 2025-26+: Uses unified UK property income
     * For TY < 2025-26: Uses FHL + Non-FHL income
     */
    public function getTotalIncomeAttribute(): float
    {
        // For unified property structure (2025-26+)
        if ($this->isUnifiedProperty()) {
            return $this->total_uk_property_income;
        }

        // For legacy structure (separate FHL and Non-FHL)
        return $this->total_fhl_income + $this->total_non_fhl_income;
    }

    /**
     * Get combined total expenses
     * For TY 2025-26+: Uses unified UK property expenses
     * For TY < 2025-26: Uses FHL + Non-FHL expenses
     */
    public function getTotalExpensesAttribute(): float
    {
        // For unified property structure (2025-26+)
        if ($this->isUnifiedProperty()) {
            return $this->total_uk_property_expenses;
        }

        // For legacy structure (separate FHL and Non-FHL)
        return $this->total_fhl_expenses + $this->total_non_fhl_expenses;
    }

    /**
     * Get period duration in days
     */
    public function getPeriodDaysAttribute(): int
    {
        return $this->from_date->diffInDays($this->to_date) + 1;
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

    public function scopeForPeriod($query, string $fromDate, string $toDate)
    {
        return $query->where('from_date', $fromDate)
                     ->where('to_date', $toDate);
    }

    /**
     * Check if this period overlaps with another period
     */
    public function scopeOverlapping($query, string $fromDate, string $toDate, ?int $excludeId = null)
    {
        $query->where(function ($q) use ($fromDate, $toDate) {
            $q->whereBetween('from_date', [$fromDate, $toDate])
              ->orWhereBetween('to_date', [$fromDate, $toDate])
              ->orWhere(function ($q2) use ($fromDate, $toDate) {
                  $q2->where('from_date', '<=', $fromDate)
                     ->where('to_date', '>=', $toDate);
              });
        });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query;
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

    /**
     * Check if submission can be amended (edit after submission to HMRC)
     * Submitted period summaries can be amended using PUT endpoint
     */
    public function canAmend(): bool
    {
        return $this->status === 'submitted';
    }

    /**
     * Check if period has FHL data
     */
    public function hasFhlData(): bool
    {
        return !empty($this->fhl_income_json) || !empty($this->fhl_expenses_json);
    }

    /**
     * Check if period has Non-FHL data
     */
    public function hasNonFhlData(): bool
    {
        return !empty($this->non_fhl_income_json) || !empty($this->non_fhl_expenses_json);
    }

    /**
     * Check if this is using the unified property structure (2025-26+)
     */
    public function isUnifiedProperty(): bool
    {
        return $this->tax_year >= '2025-26';
    }

    /**
     * Check if period has unified UK Property data
     */
    public function hasUnifiedPropertyData(): bool
    {
        return !empty($this->uk_property_income_json) || !empty($this->uk_property_expenses_json);
    }

    /**
     * Get total unified UK Property income (for 2025-26+)
     */
    public function getTotalUkPropertyIncomeAttribute(): float
    {
        if (!$this->uk_property_income_json) {
            return 0.0;
        }

        $total = 0.0;
        foreach ($this->uk_property_income_json as $key => $value) {
            if (is_numeric($value)) {
                $total += (float) $value;
            } elseif (is_array($value)) {
                foreach ($value as $subValue) {
                    if (is_numeric($subValue)) {
                        $total += (float) $subValue;
                    }
                }
            }
        }

        return $total;
    }

    /**
     * Get total unified UK Property expenses (for 2025-26+)
     */
    public function getTotalUkPropertyExpensesAttribute(): float
    {
        if (!$this->uk_property_expenses_json) {
            return 0.0;
        }

        $total = 0.0;
        foreach ($this->uk_property_expenses_json as $key => $value) {
            if (is_numeric($value)) {
                $total += abs((float) $value);
            }
        }

        return $total;
    }
}
