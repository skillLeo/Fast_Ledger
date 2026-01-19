<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class HmrcPeriodicSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'business_id',
        'obligation_id',
        'period_id',
        'nino',
        'tax_year',
        'period_start_date',
        'period_end_date',
        'submission_date',
        'income_json',
        'expenses_json',
        'response_json',
        'status',
        'notes'
    ];

    protected $casts = [
        'period_start_date' => 'date',
        'period_end_date' => 'date',
        'submission_date' => 'datetime',
        'income_json' => 'array',
        'expenses_json' => 'array',
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
        return $this->belongsTo(HmrcObligation::class);
    }

    /**
     * Accessors
     */
    public function getPeriodLabelAttribute(): string
    {
        return $this->period_start_date->format('d M Y') . ' - ' . 
               $this->period_end_date->format('d M Y');
    }

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

    public function getTotalIncomeAttribute(): float
    {
        if (!$this->income_json) {
            return 0.0;
        }

        $income = $this->income_json;
        $total = 0.0;

        if (isset($income['turnover'])) {
            $total += (float) $income['turnover'];
        }

        if (isset($income['other'])) {
            $total += (float) $income['other'];
        }

        return $total;
    }

    public function getTotalExpensesAttribute(): float
    {
        if (!$this->expenses_json) {
            return 0.0;
        }

        $expenses = $this->expenses_json;

        // Check for consolidated expenses
        if (isset($expenses['consolidated_expenses'])) {
            return (float) $expenses['consolidated_expenses'];
        }

        // Calculate from breakdown
        if (isset($expenses['breakdown'])) {
            $total = 0.0;
            foreach ($expenses['breakdown'] as $value) {
                $total += (float) $value;
            }
            return $total;
        }

        return 0.0;
    }

    public function getNetProfitAttribute(): float
    {
        return $this->total_income - $this->total_expenses;
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
}

