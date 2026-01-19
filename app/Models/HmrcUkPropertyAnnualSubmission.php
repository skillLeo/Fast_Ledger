<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HmrcUkPropertyAnnualSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'business_id',
        'obligation_id',
        'nino',
        'tax_year',
        'submission_date',
        'adjustments_json',
        'allowances_json',
        'response_json',
        'status',
        'test_scenario',
        'notes',
    ];

    protected $casts = [
        'submission_date' => 'datetime',
        'adjustments_json' => 'array',
        'allowances_json' => 'array',
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
        return match ($this->status) {
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
     * Check if submission is outside the amendment window
     * HMRC may return OUTSIDE_AMENDMENT_WINDOW error when trying to amend after the window closes
     */
    public function isOutsideAmendmentWindow(): bool
    {
        return $this->response_json &&
               isset($this->response_json['code']) &&
               $this->response_json['code'] === 'OUTSIDE_AMENDMENT_WINDOW';
    }

    /**
     * Check if submission can be amended (edit after submission)
     * Submitted submissions can be amended using the same PUT endpoint
     * However, amendments cannot be made if outside the amendment window
     */
    public function canAmend(): bool
    {
        return $this->status === 'submitted' && !$this->isOutsideAmendmentWindow();
    }

    /**
     * Check if submission can be submitted/resubmitted
     */
    public function canSubmit(): bool
    {
        return $this->status === 'draft' || $this->status === 'failed' || $this->status === 'submitted';
    }

    /**
     * Check if submission can be deleted
     */
    public function canDelete(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Check if submission has adjustments or allowances data
     * Used to determine if there's any data to submit to HMRC
     */
    public function hasData(): bool
    {
        return !empty($this->adjustments_json) || !empty($this->allowances_json);
    }
}
