<?php

namespace App\Services\Hmrc;

use App\Models\HmrcPeriodicSubmission;
use App\Models\HmrcBusiness;
use App\Models\HmrcObligation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HmrcPeriodicSubmissionService
{
    protected HmrcApiClient $apiClient;

    public function __construct(HmrcApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    /**
     * Create a draft submission
     */
    public function createDraft(
        int $userId,
        string $businessId,
        ?int $obligationId,
        array $data
    ): HmrcPeriodicSubmission {
        $business = HmrcBusiness::where('business_id', $businessId)
            ->where('user_id', $userId)
            ->firstOrFail();

        $obligation = $obligationId 
            ? HmrcObligation::findOrFail($obligationId) 
            : null;

        // Extract dates from obligation or data
        $periodStartDate = $obligation 
            ? $obligation->period_start_date 
            : Carbon::parse($data['period_start_date']);
            
        $periodEndDate = $obligation 
            ? $obligation->period_end_date 
            : Carbon::parse($data['period_end_date']);

        // Calculate tax year
        $taxYear = $this->calculateTaxYear($periodEndDate);

        return HmrcPeriodicSubmission::create([
            'user_id' => $userId,
            'business_id' => $businessId,
            'obligation_id' => $obligationId,
            'nino' => $data['nino'] ?? $business->nino ?? null,
            'tax_year' => $taxYear,
            'period_start_date' => $periodStartDate,
            'period_end_date' => $periodEndDate,
            // Store empty arrays instead of null to ensure proper payload generation
            'income_json' => $data['income'] ?? [],
            'expenses_json' => $data['expenses'] ?? [],
            'notes' => $data['notes'] ?? null,
            'status' => 'draft'
        ]);
    }

    /**
     * Update draft submission
     */
    public function updateDraft(int $submissionId, array $data): HmrcPeriodicSubmission
    {
        $submission = HmrcPeriodicSubmission::findOrFail($submissionId);

        if (!$submission->canEdit()) {
            throw new \RuntimeException('This submission cannot be edited.');
        }

        $submission->update([
            // Store empty arrays instead of null to ensure proper payload generation
            'income_json' => $data['income'] ?? $submission->income_json ?? [],
            'expenses_json' => $data['expenses'] ?? $submission->expenses_json ?? [],
            'notes' => $data['notes'] ?? $submission->notes,
        ]);

        return $submission->fresh();
    }

    /**
     * Submit to HMRC
     */
    public function submitToHmrc(int $submissionId): array
    {
        $submission = HmrcPeriodicSubmission::findOrFail($submissionId);

        if (!$submission->canSubmit()) {
            throw new \RuntimeException('This submission cannot be submitted.');
        }

        $business = $submission->business;
        $nino = $submission->nino ?? $business->nino;

        if (!$nino) {
            throw new \RuntimeException('NINO is required for submission.');
        }

        // Determine if we should use cumulative API (TY 2025-26 onwards)
        $useCumulativeApi = $this->shouldUseCumulativeApi($submission->tax_year);

        // Build payload
        $payload = $this->buildSubmissionPayload($submission, $useCumulativeApi);

        Log::info('Submitting periodic update to HMRC', [
            'submission_id' => $submissionId,
            'business_id' => $business->business_id,
            'nino' => $nino,
            'tax_year' => $submission->tax_year,
            'use_cumulative_api' => $useCumulativeApi,
            'payload' => $payload
        ]);

        try {
            if ($useCumulativeApi) {
                // Use PUT for cumulative API (TY 2025-26 onwards)
                $endpoint = "/individuals/business/self-employment/{$nino}/" .
                            "{$business->business_id}/cumulative/{$submission->tax_year}";

                $response = $this->apiClient->put($endpoint, $payload, [
                    'Accept' => 'application/vnd.hmrc.5.0+json'
                ]);
            } else {
                // Use POST for period API (TY 2024-25 and earlier)
                $endpoint = "/individuals/business/self-employment/{$nino}/" .
                            "{$business->business_id}/period";

                $response = $this->apiClient->post($endpoint, $payload, [
                    'Accept' => 'application/vnd.hmrc.5.0+json'
                ]);
            }

            // Update submission
            $submission->update([
                'period_id' => $response['periodId'] ?? null,
                'submission_date' => now(),
                'response_json' => $response,
                'status' => 'submitted'
            ]);

            // Update obligation status if linked
            if ($submission->obligation) {
                $submission->obligation->update([
                    'status' => 'fulfilled',
                    'received_date' => now()
                ]);
            }

            Log::info('Periodic update submitted successfully', [
                'submission_id' => $submissionId,
                'period_id' => $response['periodId'] ?? null,
                'api_type' => $useCumulativeApi ? 'cumulative' : 'period'
            ]);

            return $response;
        } catch (\Exception $e) {
            Log::error('Failed to submit periodic update to HMRC', [
                'submission_id' => $submissionId,
                'error' => $e->getMessage()
            ]);

            $submission->update([
                'status' => 'failed',
                'response_json' => [
                    'error' => $e->getMessage(),
                    'timestamp' => now()->toISOString()
                ]
            ]);

            throw $e;
        }
    }

    /**
     * Determine if cumulative API should be used
     */
    protected function shouldUseCumulativeApi(string $taxYear): bool
    {
        // Extract the starting year from tax year (e.g., "2025" from "2025-26")
        $year = (int) substr($taxYear, 0, 4);

        // Use cumulative API for tax year 2025-26 onwards
        return $year >= 2025;
    }

    /**
     * Build submission payload
     * All fields must be present even if 0 for both cumulative and period APIs
     */
    protected function buildSubmissionPayload(HmrcPeriodicSubmission $submission, bool $useCumulativeApi = false): array
    {
        $payload = [];

        if ($useCumulativeApi) {
            // Cumulative API format (TY 2025-26 onwards) - no date fields needed in payload
            // Build income - always include with defaults
            $incomeData = $submission->income_json ?? [];
            $income = $this->buildIncomePayload($incomeData);
            $payload['periodIncome'] = $income;

            // Build expenses - always include with defaults
            $expensesData = $submission->expenses_json ?? [];
            $expenses = $this->buildExpensesPayload($expensesData, true);
            $payload['periodExpenses'] = $expenses;

            // Add disallowable expenses if present
            $disallowableExpenses = $this->buildDisallowableExpensesPayload($expensesData);
            if ($disallowableExpenses) {
                $payload['periodDisallowableExpenses'] = $disallowableExpenses;
            }
        } else {
            // Period API format (TY 2024-25 and earlier)
            // Period dates wrapper
            $payload = [
                'periodDates' => [
                    'periodStartDate' => $submission->period_start_date->format('Y-m-d'),
                    'periodEndDate' => $submission->period_end_date->format('Y-m-d'),
                ]
            ];

            // Build income - always include with defaults
            $incomeData = $submission->income_json ?? [];
            $income = $this->buildIncomePayload($incomeData);
            $payload['periodIncome'] = $income;

            // Build expenses - always include with defaults
            $expensesData = $submission->expenses_json ?? [];
            $expenses = $this->buildExpensesPayload($expensesData, false);
            $payload['periodExpenses'] = $expenses;

            // Add disallowable expenses if present
            $disallowableExpenses = $this->buildDisallowableExpensesPayload($expensesData);
            if ($disallowableExpenses) {
                $payload['periodDisallowableExpenses'] = $disallowableExpenses;
            }
        }

        return $payload;
    }

    /**
     * Build income payload
     * Always includes all fields with 0 as default
     */
    protected function buildIncomePayload(array $incomeData): array
    {
        return [
            'turnover' => $this->formatAmount($incomeData['turnover'] ?? 0),
            'other' => $this->formatAmount($incomeData['other'] ?? 0),
        ];
    }

    /**
     * Build expenses payload
     * Always includes all fields with 0 as default
     */
    protected function buildExpensesPayload(array $expensesData, bool $useCumulativeApi = false): array
    {
        $expenses = [];

        // Check if consolidated expenses is explicitly set (even if 0)
        $hasConsolidated = isset($expensesData['consolidated_expenses']) &&
                          $expensesData['consolidated_expenses'] !== null &&
                          $expensesData['consolidated_expenses'] !== '';

        if ($hasConsolidated) {
            // Consolidated mode - only send consolidatedExpenses
            $expenses['consolidatedExpenses'] = $this->formatAmount($expensesData['consolidated_expenses']);
            return $expenses;
        }

        // Itemized mode - send all itemized fields with 0 as default
        // Handle breakdown expenses - both Period and Cumulative APIs use the same field names
        $breakdown = $expensesData['breakdown'] ?? [];

        // Field mappings (same for both Period and Cumulative APIs)
        $expenseFields = [
            'cost_of_goods' => 'costOfGoods',
            'payments_to_subcontractors' => 'paymentsToSubcontractors',
            'staff_costs' => 'wagesAndStaffCosts',
            'travel_costs' => 'carVanTravelExpenses',
            'premises_running_costs' => 'premisesRunningCosts',
            'maintenance_costs' => 'maintenanceCosts',
            'admin_costs' => 'adminCosts',
            'business_entertainment_costs' => 'businessEntertainmentCosts',
            'advertising_costs' => 'advertisingCosts',
            'interest_on_bank_other_loans' => 'interestOnBankOtherLoans',
            'financial_charges' => 'financeCharges',
            'bad_debt' => 'irrecoverableDebts',
            'professional_fees' => 'professionalFees',
            'depreciation' => 'depreciation',
            'other_expenses' => 'otherExpenses'
        ];

        // Always include all fields with 0 as default
        foreach ($expenseFields as $key => $apiKey) {
            $value = $breakdown[$key] ?? 0;
            $expenses[$apiKey] = $this->formatAmount($value);
        }

        return $expenses;
    }

    /**
     * Build disallowable expenses payload
     */
    protected function buildDisallowableExpensesPayload(array $expensesData): ?array
    {
        if (!isset($expensesData['breakdown']) || !is_array($expensesData['breakdown'])) {
            return null;
        }

        $breakdown = $expensesData['breakdown'];
        $disallowableExpenses = [];

        // Disallowable expense field mappings
        $disallowableFields = [
            'cost_of_goods_disallowable' => 'costOfGoodsDisallowable',
            'payments_to_subcontractors_disallowable' => 'paymentsToSubcontractorsDisallowable',
            'staff_costs_disallowable' => 'wagesAndStaffCostsDisallowable',
            'travel_costs_disallowable' => 'carVanTravelExpensesDisallowable',
            'premises_running_costs_disallowable' => 'premisesRunningCostsDisallowable',
            'maintenance_costs_disallowable' => 'maintenanceCostsDisallowable',
            'admin_costs_disallowable' => 'adminCostsDisallowable',
            'business_entertainment_costs_disallowable' => 'businessEntertainmentCostsDisallowable',
            'advertising_costs_disallowable' => 'advertisingCostsDisallowable',
            'interest_on_bank_other_loans_disallowable' => 'interestOnBankOtherLoansDisallowable',
            'financial_charges_disallowable' => 'financeChargesDisallowable',
            'bad_debt_disallowable' => 'irrecoverableDebtsDisallowable',
            'professional_fees_disallowable' => 'professionalFeesDisallowable',
            'depreciation_disallowable' => 'depreciationDisallowable',
            'other_expenses_disallowable' => 'otherExpensesDisallowable'
        ];

        foreach ($disallowableFields as $key => $apiKey) {
            if (isset($breakdown[$key]) && $breakdown[$key] !== null && $breakdown[$key] !== '') {
                $disallowableExpenses[$apiKey] = $this->formatAmount($breakdown[$key]);
            }
        }

        return !empty($disallowableExpenses) ? $disallowableExpenses : null;
    }

    /**
     * Get a periodic update from HMRC
     */
    public function getPeriodicUpdate(string $nino, string $businessId, string $periodId): array
    {
        $endpoint = "/individuals/business/self-employment/{$nino}/" .
                    "{$businessId}/period/{$periodId}";

        return $this->apiClient->get($endpoint, [
            'Accept' => 'application/vnd.hmrc.5.0+json'
        ]);
    }

    /**
     * Get all periodic updates for a business
     */
    public function getPeriodicUpdates(string $nino, string $businessId, ?Carbon $fromDate = null, ?Carbon $toDate = null): array
    {
        $params = [];
        
        if ($fromDate) {
            $params['fromDate'] = $fromDate->format('Y-m-d');
        }
        
        if ($toDate) {
            $params['toDate'] = $toDate->format('Y-m-d');
        }

        $endpoint = "/individuals/business/self-employment/{$nino}/{$businessId}/period";
        
        if (!empty($params)) {
            $endpoint .= '?' . http_build_query($params);
        }

        return $this->apiClient->get($endpoint, [
            'Accept' => 'application/vnd.hmrc.5.0+json'
        ]);
    }

    /**
     * Get submissions for user
     */
    public function getSubmissionsForUser(int $userId, ?string $status = null): Collection
    {
        $query = HmrcPeriodicSubmission::where('user_id', $userId)
            ->with(['business', 'obligation'])
            ->orderBy('period_end_date', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        return $query->get();
    }

    /**
     * Get submission statistics for user
     */
    public function getSubmissionStats(int $userId): array
    {
        $baseQuery = HmrcPeriodicSubmission::where('user_id', $userId);

        return [
            'total' => (clone $baseQuery)->count(),
            'submitted' => (clone $baseQuery)->submitted()->count(),
            'draft' => (clone $baseQuery)->draft()->count(),
            'failed' => (clone $baseQuery)->failed()->count(),
            'total_income' => (clone $baseQuery)->submitted()->get()->sum('total_income'),
            'total_expenses' => (clone $baseQuery)->submitted()->get()->sum('total_expenses'),
            'net_profit' => (clone $baseQuery)->submitted()->get()->sum('net_profit'),
        ];
    }

    /**
     * Delete draft submission
     */
    public function deleteDraft(int $submissionId): bool
    {
        $submission = HmrcPeriodicSubmission::findOrFail($submissionId);

        if ($submission->status !== 'draft') {
            throw new \RuntimeException('Only draft submissions can be deleted.');
        }

        return $submission->delete();
    }

    /**
     * Calculate tax year from date
     */
    protected function calculateTaxYear(Carbon $date): string
    {
        $year = $date->year;
        $month = $date->month;

        if ($month >= 4) {
            // April or later - tax year is current year to next year
            return $year . '-' . substr($year + 1, 2);
        } else {
            // Before April - tax year is previous year to current year
            return ($year - 1) . '-' . substr($year, 2);
        }
    }

    /**
     * Format amount to 2 decimal places
     */
    protected function formatAmount($value): float
    {
        return round((float) $value, 2);
    }
}

