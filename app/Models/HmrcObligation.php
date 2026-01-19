<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class HmrcObligation extends Model
{
    protected $fillable = [
        'user_id',
        'business_id',
        'type_of_business',
        'obligation_type',
        'period_start_date',
        'period_end_date',
        'due_date',
        'status',
        'received_date',
        'period_key',
        'quarter',
        'tax_year',
        'is_overdue',
        'days_until_due',
        'last_synced_at',
        'reminder_sent_at',
        'submission_id',
        'metadata'
    ];

    protected $casts = [
        'period_start_date' => 'date',
        'period_end_date' => 'date',
        'due_date' => 'date',
        'received_date' => 'date',
        'last_synced_at' => 'datetime',
        'reminder_sent_at' => 'datetime',
        'is_overdue' => 'boolean',
        'metadata' => 'array'
    ];

    protected $appends = [
        'period_label',
        'status_badge',
        'urgency_level',
        'can_submit'
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

    public function submissions()
    {
        return $this->hasMany(HmrcPeriodicSubmission::class, 'obligation_id');
    }

    /**
     * Scopes
     */
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeFulfilled($query)
    {
        return $query->where('status', 'fulfilled');
    }

    public function scopeOverdue($query)
    {
        return $query->where('is_overdue', true)->where('status', 'open');
    }

    public function scopeDueWithin($query, int $days)
    {
        return $query->where('due_date', '<=', now()->addDays($days))
                    ->where('due_date', '>=', now())
                    ->where('status', 'open');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('due_date', '>', now())
                    ->where('status', 'open')
                    ->orderBy('due_date', 'asc');
    }

    public function scopePeriodic($query)
    {
        return $query->where('obligation_type', 'periodic');
    }

    public function scopeCrystallisation($query)
    {
        return $query->where('obligation_type', 'crystallisation');
    }

    public function scopeForBusiness($query, string $businessId)
    {
        return $query->where('business_id', $businessId);
    }

    public function scopeForTaxYear($query, string $taxYear)
    {
        return $query->where('tax_year', $taxYear);
    }

    public function scopeForQuarter($query, string $quarter)
    {
        return $query->where('quarter', $quarter);
    }

    /**
     * Accessors
     */
    public function getPeriodLabelAttribute(): string
    {
        $start = $this->period_start_date->format('d M Y');
        $end = $this->period_end_date->format('d M Y');
        
        if ($this->quarter) {
            return "{$this->quarter} ({$start} - {$end})";
        }
        
        return "{$start} - {$end}";
    }

    public function getStatusBadgeAttribute(): array
    {
        return match($this->status) {
            'open' => [
                'class' => $this->is_overdue ? 'danger' : ($this->daysUntilDue() <= 7 ? 'warning' : 'info'),
                'text' => $this->is_overdue ? 'Overdue' : 'Open',
                'icon' => $this->is_overdue ? 'fa-exclamation-circle' : 'fa-clock'
            ],
            'fulfilled' => [
                'class' => 'success',
                'text' => 'Fulfilled',
                'icon' => 'fa-check-circle'
            ],
            default => [
                'class' => 'secondary',
                'text' => 'Unknown',
                'icon' => 'fa-question-circle'
            ]
        };
    }

    public function getUrgencyLevelAttribute(): string
    {
        if ($this->status === 'fulfilled') {
            return 'completed';
        }
        
        if ($this->is_overdue) {
            return 'critical';
        }
        
        $daysUntil = $this->daysUntilDue();
        
        if ($daysUntil <= 3) return 'urgent';
        if ($daysUntil <= 7) return 'warning';
        if ($daysUntil <= 14) return 'attention';
        
        return 'normal';
    }

    public function getCanSubmitAttribute(): bool
    {
        return $this->status === 'open' && 
               $this->obligation_type === 'periodic' &&
               !$this->submission_id;
    }

    /**
     * Methods
     */
    public function daysUntilDue(): int
    {
        if ($this->status === 'fulfilled') {
            return 0;
        }
        
        return (int) $this->due_date->diffInDays(now(), false);
    }

    public function isOverdue(): bool
    {
        return $this->status === 'open' && $this->due_date->isPast();
    }

    public function updateOverdueStatus(): void
    {
        $this->update([
            'is_overdue' => $this->isOverdue(),
            'days_until_due' => $this->daysUntilDue()
        ]);
    }

    public function markAsFulfilled(?string $submissionId = null): void
    {
        $this->update([
            'status' => 'fulfilled',
            'received_date' => now(),
            'is_overdue' => false,
            'submission_id' => $submissionId
        ]);
    }

    public function getObligationTypeLabel(): string
    {
        return match($this->obligation_type) {
            'periodic' => 'Quarterly Update',
            'crystallisation' => 'Final Declaration',
            default => 'Unknown'
        };
    }

    public function getBusinessTypeLabel(): string
    {
        return match($this->type_of_business) {
            'self-employment' => 'Self Employment',
            'uk-property' => 'UK Property',
            'foreign-property' => 'Foreign Property',
            'property-unspecified' => 'Property',
            default => 'Unknown'
        };
    }

    /**
     * Get the submission creation route for this obligation
     */
    public function getSubmissionRoute(): ?string
    {
        // Only periodic obligations can create submissions
        if ($this->obligation_type !== 'periodic') {
            return null;
        }

        $routeName = match($this->type_of_business) {
            'self-employment' => 'hmrc.submissions.create',
            'uk-property' => 'hmrc.uk-property-period-summaries.create',
            default => null
        };

        return $routeName;
    }

    /**
     * Get the annual submission creation route for this obligation
     */
    public function getAnnualSubmissionRoute(): ?string
    {
        // Only crystallisation obligations can create annual submissions
        if ($this->obligation_type !== 'crystallisation') {
            return null;
        }

        $routeName = match($this->type_of_business) {
            'self-employment' => 'hmrc.annual-submissions.create',
            'uk-property' => 'hmrc.uk-property-annual-submissions.create',
            default => null
        };

        return $routeName;
    }

    /**
     * Get the appropriate submission route based on obligation type
     */
    public function getDynamicSubmissionRoute(): ?string
    {
        return match($this->obligation_type) {
            'periodic' => $this->getSubmissionRoute(),
            'crystallisation' => $this->getAnnualSubmissionRoute(),
            default => null
        };
    }

    /**
     * Boot method
     */
    protected static function booted()
    {
        static::creating(function ($obligation) {
            $obligation->period_key = "{$obligation->business_id}-" .
                                     "{$obligation->period_start_date->format('Y-m-d')}-" .
                                     "{$obligation->period_end_date->format('Y-m-d')}";
            
            // Calculate quarter
            $month = $obligation->period_start_date->month;
            $obligation->quarter = match(true) {
                $month >= 4 && $month <= 6 => 'Q1',
                $month >= 7 && $month <= 9 => 'Q2',
                $month >= 10 && $month <= 12 => 'Q3',
                $month >= 1 && $month <= 3 => 'Q4',
                default => null
            };
            
            // Calculate tax year
            $year = $obligation->period_start_date->year;
            $nextYear = $year + 1;
            $obligation->tax_year = $obligation->period_start_date->month >= 4 
                ? "{$year}-" . substr((string)$nextYear, 2, 2)
                : ($year - 1) . "-" . substr((string)$year, 2, 2);
            
            $obligation->is_overdue = $obligation->isOverdue();
            $obligation->days_until_due = $obligation->daysUntilDue();
        });
    }
}

