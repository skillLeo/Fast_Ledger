<?php

namespace App\Services\Hmrc;

use App\Models\HmrcCalculation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class HmrcCalculationService
{
    public function __construct(protected HmrcApiClient $apiClient)
    {
    }

    /**
     * Trigger a new tax calculation
     */
    public function triggerCalculation(
        int $userId,
        string $nino,
        string $taxYear,
        bool $crystallise = false,
        ?string $finalDeclaration = null
    ): HmrcCalculation {
        Log::info('Triggering HMRC calculation', [
            'user_id' => $userId,
            'nino' => $nino,
            'tax_year' => $taxYear,
            'crystallise' => $crystallise,
        ]);

        $endpoint = "/individuals/calculations/{$nino}/self-assessment/{$taxYear}";


        $isCrystallisation = $crystallise || $finalDeclaration === 'true' || $finalDeclaration === '1';

        if($isCrystallisation) {
            $endpoint = "{$endpoint}/trigger/intent-to-finalize";
        }else {

            $endpoint = "{$endpoint}/trigger/in-year";
        }

        try {
            $response = $this->apiClient->post($endpoint, [], [
                'Accept' => 'application/vnd.hmrc.8.0+json',
            ]);

            $calculationId = (string) $response['calculationId'];

            // Create calculation record with processing status
            $calculation = HmrcCalculation::create([
                'user_id' => $userId,
                'nino' => $nino,
                'tax_year' => $taxYear,
                'calculation_id' => $calculationId,
                'request_intent' => $crystallise ? 'crystallisation' : 'forecast',
                'status' => 'processing',
            ]);

            Log::info('Calculation triggered successfully', [
                'calculation_id' => $calculationId,
                'record_id' => $calculation->id,
            ]);

            // Fetch full calculation details after a short delay
            // Note: In production, this might be better handled by a queue job
            try {
                sleep(2); // Give HMRC time to process
                $this->getCalculation($userId, $nino, $taxYear, $calculationId);
            } catch (\Exception $e) {
                Log::warning('Failed to immediately fetch calculation details', [
                    'calculation_id' => $calculationId,
                    'error' => $e->getMessage(),
                ]);
            }

            return $calculation->fresh();
        } catch (\Exception $e) {
            Log::error('Failed to trigger calculation', [
                'user_id' => $userId,
                'nino' => $nino,
                'tax_year' => $taxYear,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * List all calculations for a specific tax year
     */
    public function listCalculations(string $nino, string $taxYear): array
    {
        $endpoint = "/individuals/calculations/{$nino}/self-assessment/{$taxYear}";

        $response = $this->apiClient->get($endpoint, [
            'Accept' => 'application/vnd.hmrc.8.0+json',
        ]);

        Log::info('Listed calculations from HMRC', [
            'nino' => $nino,
            'tax_year' => $taxYear,
            'count' => count($response['calculations'] ?? []),
        ]);

        return $response;
    }

    /**
     * Get full calculation details
     */
    public function getCalculation(
        int $userId,
        string $nino,
        string $taxYear,
        string $calculationId
    ): array {
        $endpoint = "/individuals/calculations/{$nino}/self-assessment/{$taxYear}/{$calculationId}";

        try {
            $response = $this->apiClient->get($endpoint, [
                'Accept' => 'application/vnd.hmrc.8.0+json',
            ]);

            // Update or create calculation record with full details
            $calculation = HmrcCalculation::updateOrCreate(
                [
                    'calculation_id' => $calculationId,
                ],
                [
                    'user_id' => $userId,
                    'nino' => $nino,
                    'tax_year' => $taxYear,
                    'calculation_timestamp' => $response['metadata']['calculationTimestamp'] ?? null,
                    'type' => $response['metadata']['type'] ?? null,
                    'request_intent' => $response['metadata']['requestedBy'] ?? null,
                    'total_income_received' => $this->extractValue($response, 'calculation.taxCalculation.incomeTax.totalIncomeReceivedFromAllSources'),
                    'total_taxable_income' => $this->extractValue($response, 'calculation.taxCalculation.incomeTax.totalTaxableIncome'),
                    'income_tax_and_nics_due' => $this->extractValue($response, 'calculation.taxCalculation.incomeTax.totalIncomeTaxAndNicsDue'),
                    'income_tax_nics_charged' => $this->extractValue($response, 'calculation.taxCalculation.incomeTax.incomeTaxCharged'),
                    'total_allowances_and_deductions' => $this->extractValue($response, 'calculation.allowancesAndDeductions.totalAllowancesAndDeductions'),
                    'total_student_loans_repayment_amount' => $this->extractValue($response, 'calculation.studentLoans.totalStudentLoansRepaymentAmount'),
                    'calculation_json' => $response,
                    'messages' => $response['messages'] ?? null,
                    'status' => 'completed',
                ]
            );

            Log::info('Calculation details retrieved and stored', [
                'calculation_id' => $calculationId,
                'record_id' => $calculation->id,
            ]);

            return $response;
        } catch (\Exception $e) {
            // Mark as failed if exists
            HmrcCalculation::where('calculation_id', $calculationId)->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            Log::error('Failed to retrieve calculation', [
                'calculation_id' => $calculationId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Get calculations for a user
     */
    public function getCalculationsForUser(int $userId, ?string $taxYear = null): Collection
    {
        $query = HmrcCalculation::where('user_id', $userId)
            ->orderBy('calculation_timestamp', 'desc')
            ->orderBy('created_at', 'desc');

        if ($taxYear) {
            $query->where('tax_year', $taxYear);
        }

        return $query->get();
    }

    /**
     * Get calculation statistics for user
     */
    public function getCalculationStats(int $userId): array
    {
        $baseQuery = HmrcCalculation::where('user_id', $userId);

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'completed' => (clone $baseQuery)->completed()->count(),
            'processing' => (clone $baseQuery)->processing()->count(),
            'failed' => (clone $baseQuery)->failed()->count(),
        ];

        // Get latest completed calculation
        $latestCalculation = (clone $baseQuery)
            ->completed()
            ->orderBy('calculation_timestamp', 'desc')
            ->first();

        if ($latestCalculation) {
            $stats['latest_tax_due'] = $latestCalculation->income_tax_and_nics_due;
            $stats['latest_taxable_income'] = $latestCalculation->total_taxable_income;
            $stats['latest_tax_year'] = $latestCalculation->tax_year;
        }

        return $stats;
    }

    /**
     * Parse calculation breakdown for display
     */
    public function getCalculationBreakdown(int $calculationId): array
    {
        $calculation = HmrcCalculation::findOrFail($calculationId);

        if (!$calculation->calculation_json) {
            return [];
        }

        $calc = $calculation->calculation_json;
        $breakdown = [
            'summary' => [],
            'income' => [],
            'allowances' => [],
            'tax' => [],
            'nics' => [],
            'messages' => [],
        ];

        // Summary
        if (isset($calc['calculation']['taxCalculation']['incomeTax'])) {
            $incomeTax = $calc['calculation']['taxCalculation']['incomeTax'];
            $breakdown['summary'] = [
                'total_income_received' => $incomeTax['totalIncomeReceivedFromAllSources'] ?? 0,
                'total_allowances_deducted' => $incomeTax['totalAllowancesAndDeductions'] ?? 0,
                'total_taxable_income' => $incomeTax['totalTaxableIncome'] ?? 0,
                'income_tax_charged' => $incomeTax['incomeTaxCharged'] ?? 0,
                'total_income_tax_and_nics_due' => $incomeTax['totalIncomeTaxAndNicsDue'] ?? 0,
            ];
        }

        // Income breakdown
        if (isset($calc['inputs']['businessProfitAndLoss'])) {
            foreach ($calc['inputs']['businessProfitAndLoss'] as $business) {
                $breakdown['income']['businesses'][] = [
                    'business_id' => $business['businessId'] ?? 'Unknown',
                    'income' => $business['income'] ?? [],
                    'expenses' => $business['expenses'] ?? [],
                    'net_profit' => $business['netProfit'] ?? 0,
                ];
            }
        }

        // Allowances and deductions
        if (isset($calc['calculation']['allowancesAndDeductions'])) {
            $allowances = $calc['calculation']['allowancesAndDeductions'];
            
            // Normalize complex fields that may be arrays with 'amount' key
            $fieldsToNormalize = [
                'marriageAllowanceTransferOut', 
                'pensionContributions', 
                'giftAidTax',
                'personalAllowance',
                'totalAllowancesAndDeductions'
            ];
            
            foreach ($fieldsToNormalize as $field) {
                if (isset($allowances[$field]) && is_array($allowances[$field])) {
                    // Log the structure for debugging
                    Log::debug("Normalizing allowances field: {$field}", [
                        'structure' => $allowances[$field]
                    ]);
                    
                    // If it's an array, try to extract the amount
                    if (isset($allowances[$field]['amount'])) {
                        $allowances[$field] = $allowances[$field]['amount'];
                    } elseif (isset($allowances[$field]['totalAmount'])) {
                        $allowances[$field] = $allowances[$field]['totalAmount'];
                    } else {
                        // If it's an array but no recognizable structure, sum numeric values or use first value
                        $numericValues = array_filter($allowances[$field], 'is_numeric');
                        $allowances[$field] = !empty($numericValues) ? array_sum($numericValues) : 0;
                        
                        Log::warning("Allowances field has unexpected structure: {$field}", [
                            'original_value' => $allowances[$field],
                            'normalized_to' => $allowances[$field]
                        ]);
                    }
                }
            }
            
            $breakdown['allowances'] = $allowances;
        }

        // Tax calculation
        if (isset($calc['calculation']['taxCalculation']['incomeTax'])) {
            $incomeTax = $calc['calculation']['taxCalculation']['incomeTax'];
            
            if (isset($incomeTax['incomeTaxBands'])) {
                $breakdown['tax']['bands'] = $incomeTax['incomeTaxBands'];
            }

            if (isset($incomeTax['payPensionsProfit'])) {
                $breakdown['tax']['pay_pensions_profit'] = $incomeTax['payPensionsProfit'];
            }

            if (isset($incomeTax['savingsAndGains'])) {
                $breakdown['tax']['savings_and_gains'] = $incomeTax['savingsAndGains'];
            }

            if (isset($incomeTax['dividends'])) {
                $breakdown['tax']['dividends'] = $incomeTax['dividends'];
            }
        }

        // National Insurance
        if (isset($calc['calculation']['taxCalculation']['nics'])) {
            $nics = $calc['calculation']['taxCalculation']['nics'];
            
            if (isset($nics['class2Nics'])) {
                $breakdown['nics']['class2'] = $nics['class2Nics'];
            }

            if (isset($nics['class4Nics'])) {
                $breakdown['nics']['class4'] = $nics['class4Nics'];
            }
        }

        // Messages
        if (isset($calc['messages'])) {
            $breakdown['messages'] = $calc['messages'];
        }

        return $breakdown;
    }

    /**
     * Sync calculations from HMRC for a user
     */
    public function syncCalculationsFromHmrc(int $userId, string $nino, string $taxYear): int
    {
        try {
            $response = $this->listCalculations($nino, $taxYear);
            $calculations = $response['calculations'] ?? [];

            $synced = 0;

            foreach ($calculations as $calc) {
                $calculationId = $calc['calculationId'] ?? null;
                
                if (!$calculationId) {
                    continue;
                }

                // Check if we already have this calculation
                $existing = HmrcCalculation::where('calculation_id', $calculationId)->first();
                
                if (!$existing || $existing->status !== 'completed') {
                    try {
                        $this->getCalculation($userId, $nino, $taxYear, $calculationId);
                        $synced++;
                    } catch (\Exception $e) {
                        Log::warning('Failed to sync calculation', [
                            'calculation_id' => $calculationId,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }

            Log::info('Synced calculations from HMRC', [
                'user_id' => $userId,
                'tax_year' => $taxYear,
                'synced' => $synced,
            ]);

            return $synced;
        } catch (\Exception $e) {
            Log::error('Failed to sync calculations', [
                'user_id' => $userId,
                'tax_year' => $taxYear,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Helper to extract nested values from array
     */
    private function extractValue(array $data, string $path)
    {
        $keys = explode('.', $path);
        $value = $data;

        foreach ($keys as $key) {
            if (!isset($value[$key])) {
                return null;
            }
            $value = $value[$key];
        }

        return $value;
    }
}

