<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class HmrcFinalDeclaration extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'nino',
        'tax_year',
        'wizard_step',
        'prerequisites_check',
        'prerequisites_passed',
        'submissions_reviewed',
        'submissions_reviewed_at',
        'calculation_reviewed',
        'calculation_reviewed_at',
        'income_reviewed',
        'income_reviewed_at',
        'declaration_confirmed',
        'declaration_confirmed_at',
        'declaration_ip_address',
        'declaration_user_agent',
        'calculation_id',
        'status',
        'submitted_at',
        'submission_response',
        'submission_errors',
    ];

    protected $casts = [
        'prerequisites_check' => 'array',
        'prerequisites_passed' => 'boolean',
        'submissions_reviewed' => 'boolean',
        'submissions_reviewed_at' => 'datetime',
        'calculation_reviewed' => 'boolean',
        'calculation_reviewed_at' => 'datetime',
        'income_reviewed' => 'boolean',
        'income_reviewed_at' => 'datetime',
        'declaration_confirmed' => 'boolean',
        'declaration_confirmed_at' => 'datetime',
        'submitted_at' => 'datetime',
        'submission_response' => 'array',
        'submission_errors' => 'array',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function calculation(): BelongsTo
    {
        return $this->belongsTo(HmrcCalculation::class, 'calculation_id');
    }

    // Scopes
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeSubmitted($query)
    {
        return $query->where('status', 'submitted');
    }

    public function scopeForTaxYear($query, string $taxYear)
    {
        return $query->where('tax_year', $taxYear);
    }

    // Accessor & Mutators
    public function getIsReadyForSubmissionAttribute(): bool
    {
        return $this->prerequisites_passed
            && $this->submissions_reviewed
            && $this->calculation_reviewed
            && $this->income_reviewed
            && $this->declaration_confirmed;
    }

    public function getProgressPercentageAttribute(): int
    {
        $steps = [
            'prerequisites_passed',
            'submissions_reviewed',
            'calculation_reviewed',
            'income_reviewed',
            'declaration_confirmed',
        ];

        $completed = 0;
        foreach ($steps as $step) {
            if ($this->$step) {
                $completed++;
            }
        }

        return (int) (($completed / count($steps)) * 100);
    }

    // Helper Methods
    public function canProceedToNextStep(): bool
    {
        return match($this->wizard_step) {
            'prerequisites_check' => $this->prerequisites_passed,
            'review_submissions' => $this->submissions_reviewed,
            'review_calculation' => $this->calculation_reviewed,
            'review_income' => $this->income_reviewed,
            'declaration' => $this->declaration_confirmed,
            default => false,
        };
    }

    public function getNextWizardStep(): ?string
    {
        $steps = [
            'prerequisites_check',
            'review_submissions',
            'review_calculation',
            'review_income',
            'declaration',
            'completed',
        ];

        $currentIndex = array_search($this->wizard_step, $steps);
        return $steps[$currentIndex + 1] ?? null;
    }

    public function markStepComplete(string $step): void
    {
        $fieldMap = [
            'prerequisites_check' => 'prerequisites_passed',
            'review_submissions' => 'submissions_reviewed',
            'review_calculation' => 'calculation_reviewed',
            'review_income' => 'income_reviewed',
            'declaration' => 'declaration_confirmed',
        ];

        if (isset($fieldMap[$step])) {
            $field = $fieldMap[$step];
            $this->update([
                $field => true,
                $field . '_at' => now(),
            ]);
        }
    }
}

