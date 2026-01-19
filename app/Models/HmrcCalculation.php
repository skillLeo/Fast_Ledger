<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HmrcCalculation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nino',
        'tax_year',
        'calculation_id',
        'calculation_timestamp',
        'type',
        'request_intent',
        'total_income_received',
        'total_taxable_income',
        'income_tax_and_nics_due',
        'income_tax_nics_charged',
        'total_allowances_and_deductions',
        'total_student_loans_repayment_amount',
        'calculation_json',
        'status',
        'error_message',
        'messages',
        'is_crystallised',
        'crystallised_at',
        'crystallisation_response',
    ];

    protected $casts = [
        'calculation_timestamp' => 'datetime',
        'total_income_received' => 'decimal:2',
        'total_taxable_income' => 'decimal:2',
        'income_tax_and_nics_due' => 'decimal:2',
        'income_tax_nics_charged' => 'decimal:2',
        'total_allowances_and_deductions' => 'decimal:2',
        'total_student_loans_repayment_amount' => 'decimal:2',
        'calculation_json' => 'array',
        'messages' => 'array',
        'is_crystallised' => 'boolean',
        'crystallised_at' => 'datetime',
        'crystallisation_response' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'User_ID');
    }

    /**
     * Scopes
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeForTaxYear($query, string $taxYear)
    {
        return $query->where('tax_year', $taxYear);
    }

    /**
     * Accessors & Mutators
     */
    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'completed' => [
                'class' => 'success',
                'icon' => 'fa-check-circle',
                'text' => 'Completed',
            ],
            'processing' => [
                'class' => 'warning',
                'icon' => 'fa-spinner fa-spin',
                'text' => 'Processing',
            ],
            'failed' => [
                'class' => 'danger',
                'icon' => 'fa-times-circle',
                'text' => 'Failed',
            ],
            default => [
                'class' => 'secondary',
                'icon' => 'fa-question-circle',
                'text' => 'Unknown',
            ],
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'inYear' => 'In-Year Estimate',
            'crystallisation' => 'Final Declaration',
            default => ucfirst($this->type ?? 'Unknown'),
        };
    }

    /**
     * Get income breakdown from calculation JSON
     */
    public function getIncomeBreakdown(): array
    {
        if (!$this->calculation_json) {
            return [];
        }

        $breakdown = [];
        $calc = $this->calculation_json;

        // Self-employment income
        if (isset($calc['calculation']['taxCalculation']['incomeTax']['totalIncomeReceivedFromAllSources'])) {
            $breakdown['total_received'] = $calc['calculation']['taxCalculation']['incomeTax']['totalIncomeReceivedFromAllSources'];
        }

        // Business profit
        if (isset($calc['inputs']['businessProfitAndLoss'])) {
            $breakdown['business'] = [];
            foreach ($calc['inputs']['businessProfitAndLoss'] as $business) {
                $breakdown['business'][] = [
                    'id' => $business['businessId'] ?? null,
                    'income' => $business['income'] ?? null,
                    'expenses' => $business['expenses'] ?? null,
                    'net_profit' => $business['netProfit'] ?? null,
                ];
            }
        }

        return $breakdown;
    }

    /**
     * Get allowances and deductions breakdown
     */
    public function getAllowancesBreakdown(): array
    {
        if (!$this->calculation_json) {
            return [];
        }

        $breakdown = [];
        $calc = $this->calculation_json;

        if (isset($calc['calculation']['allowancesAndDeductions'])) {
            $ad = $calc['calculation']['allowancesAndDeductions'];
            
            if (isset($ad['personalAllowance'])) {
                $breakdown['personal_allowance'] = $ad['personalAllowance'];
            }
            
            if (isset($ad['marriageAllowanceTransferOut'])) {
                $breakdown['marriage_allowance'] = $ad['marriageAllowanceTransferOut'];
            }
            
            if (isset($ad['pensionContributions'])) {
                $breakdown['pension_contributions'] = $ad['pensionContributions'];
            }
        }

        return $breakdown;
    }

    /**
     * Get tax calculation breakdown
     */
    public function getTaxCalculationBreakdown(): array
    {
        if (!$this->calculation_json) {
            return [];
        }

        $breakdown = [];
        $calc = $this->calculation_json;

        if (isset($calc['calculation']['taxCalculation']['incomeTax'])) {
            $incomeTax = $calc['calculation']['taxCalculation']['incomeTax'];
            
            $breakdown['income_tax'] = [
                'total_income_received' => $incomeTax['totalIncomeReceivedFromAllSources'] ?? 0,
                'total_taxable_income' => $incomeTax['totalTaxableIncome'] ?? 0,
                'income_tax_due' => $incomeTax['incomeTaxCharged'] ?? 0,
                'payable_income_tax' => $incomeTax['totalIncomeTaxAndNicsDue'] ?? 0,
            ];

            // Tax bands
            if (isset($incomeTax['incomeTaxBands'])) {
                $breakdown['tax_bands'] = $incomeTax['incomeTaxBands'];
            }
        }

        if (isset($calc['calculation']['taxCalculation']['nics'])) {
            $nics = $calc['calculation']['taxCalculation']['nics'];
            
            $breakdown['national_insurance'] = [
                'class2' => $nics['class2Nics']['amount'] ?? 0,
                'class4' => $nics['class4Nics']['totalAmount'] ?? 0,
                'total' => ($nics['class2Nics']['amount'] ?? 0) + ($nics['class4Nics']['totalAmount'] ?? 0),
            ];
        }

        return $breakdown;
    }

    /**
     * Get messages from HMRC
     */
    public function getHmrcMessages(): array
    {
        if (!$this->messages) {
            return [];
        }

        return $this->messages;
    }

    /**
     * Check if calculation has errors
     */
    public function hasErrors(): bool
    {
        if ($this->status === 'failed') {
            return true;
        }

        if ($this->messages) {
            foreach ($this->messages as $message) {
                if (isset($message['type']) && $message['type'] === 'error') {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if calculation has warnings
     */
    public function hasWarnings(): bool
    {
        if (!$this->messages) {
            return false;
        }

        foreach ($this->messages as $message) {
            if (isset($message['type']) && $message['type'] === 'warning') {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if this is a crystallisation (final declaration)
     */
    public function isCrystallisation(): bool
    {
        return $this->type === 'crystallisation' || $this->request_intent === 'crystallisation';
    }

    /**
     * Check if calculation can be retriggered
     */
    public function canRetrigger(): bool
    {
        return $this->status === 'failed' || $this->type === 'inYear';
    }
}

