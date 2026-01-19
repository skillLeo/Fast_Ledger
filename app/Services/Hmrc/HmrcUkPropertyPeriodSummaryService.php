<?php

namespace App\Services\Hmrc;

use App\Exceptions\HmrcApiException;
use App\Models\HmrcUkPropertyPeriodSummary;
use App\Models\HmrcBusiness;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class HmrcUkPropertyPeriodSummaryService
{
    protected HmrcApiClient $apiClient;

    public function __construct(HmrcApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    /**
     * Create a draft period summary
     */
    public function createDraft(
        int $userId,
        string $businessId,
        string $taxYear,
        array $data
    ): HmrcUkPropertyPeriodSummary {
        $business = HmrcBusiness::where('business_id', $businessId)
            ->where('user_id', $userId)
            ->firstOrFail();

        // Validate period dates
        $this->validatePeriodDates($businessId, $data['from_date'], $data['to_date'], null, $taxYear);

        // For 2025-26+, use unified property structure
        $isUnified = $taxYear >= '2025-26';

        return HmrcUkPropertyPeriodSummary::create([
            'user_id' => $userId,
            'business_id' => $businessId,
            'obligation_id' => $data['obligation_id'] ?? null,
            'nino' => $data['nino'] ?? $business->nino ?? null,
            'tax_year' => $taxYear,
            'from_date' => $data['from_date'],
            'to_date' => $data['to_date'],
            // For legacy properties (<=2024-25), ensure empty array instead of null
            'fhl_income_json' => $isUnified ? null : ($data['fhl_income'] ?? []),
            'fhl_expenses_json' => $isUnified ? null : ($data['fhl_expenses'] ?? []),
            'non_fhl_income_json' => $isUnified ? null : ($data['non_fhl_income'] ?? []),
            'non_fhl_expenses_json' => $isUnified ? null : ($data['non_fhl_expenses'] ?? []),
            // For unified properties (2025-26+), ensure empty array instead of null
            'uk_property_income_json' => $isUnified ? ($data['uk_property_income'] ?? []) : null,
            'uk_property_expenses_json' => $isUnified ? ($data['uk_property_expenses'] ?? []) : null,
            'test_scenario' => $data['test_scenario'] ?? null,
            'status' => 'draft'
        ]);
    }

    /**
     * Update draft period summary
     */
    public function updateDraft(int $summaryId, array $data): HmrcUkPropertyPeriodSummary
    {
        $summary = HmrcUkPropertyPeriodSummary::findOrFail($summaryId);

        if (!$summary->canEdit()) {
            throw new \RuntimeException('This period summary cannot be edited.');
        }

        // If dates are being changed, validate them
        if (isset($data['from_date']) || isset($data['to_date'])) {
            $fromDate = $data['from_date'] ?? $summary->from_date->format('Y-m-d');
            $toDate = $data['to_date'] ?? $summary->to_date->format('Y-m-d');
            $this->validatePeriodDates($summary->business_id, $fromDate, $toDate, $summaryId, $summary->tax_year);
        }

        $isUnified = $summary->isUnifiedProperty();

        $updateData = [
            'from_date' => $data['from_date'] ?? $summary->from_date,
            'to_date' => $data['to_date'] ?? $summary->to_date,
            'test_scenario' => $data['test_scenario'] ?? $summary->test_scenario,
        ];

        if ($isUnified) {
            // For unified properties (2025-26+), ensure empty array instead of null
            $updateData['uk_property_income_json'] = $data['uk_property_income'] ?? $summary->uk_property_income_json ?? [];
            $updateData['uk_property_expenses_json'] = $data['uk_property_expenses'] ?? $summary->uk_property_expenses_json ?? [];
        } else {
            // For legacy properties (<=2024-25), ensure empty array instead of null
            $updateData['fhl_income_json'] = $data['fhl_income'] ?? $summary->fhl_income_json ?? [];
            $updateData['fhl_expenses_json'] = $data['fhl_expenses'] ?? $summary->fhl_expenses_json ?? [];
            $updateData['non_fhl_income_json'] = $data['non_fhl_income'] ?? $summary->non_fhl_income_json ?? [];
            $updateData['non_fhl_expenses_json'] = $data['non_fhl_expenses'] ?? $summary->non_fhl_expenses_json ?? [];
        }

        $summary->update($updateData);

        return $summary->fresh();
    }

    /**
     * Amend a submitted period summary at HMRC
     * This updates the local data and then resubmits to HMRC using the appropriate endpoint
     */
    public function amendToHmrc(int $summaryId, array $data): HmrcUkPropertyPeriodSummary
    {
        $summary = HmrcUkPropertyPeriodSummary::findOrFail($summaryId);

        if (!$summary->canAmend()) {
            throw new \RuntimeException('This period summary cannot be amended. Only submitted summaries can be amended.');
        }

        $isUnified = $summary->isUnifiedProperty();

        // Update the local data first
        $updateData = [];

        if ($isUnified) {
            // For unified properties (2025-26+), ensure empty array instead of null
            $updateData['uk_property_income_json'] = $data['uk_property_income'] ?? $summary->uk_property_income_json ?? [];
            $updateData['uk_property_expenses_json'] = $data['uk_property_expenses'] ?? $summary->uk_property_expenses_json ?? [];
        } else {
            // For legacy properties (<=2024-25), ensure empty array instead of null
            $updateData['fhl_income_json'] = $data['fhl_income'] ?? $summary->fhl_income_json ?? [];
            $updateData['fhl_expenses_json'] = $data['fhl_expenses'] ?? $summary->fhl_expenses_json ?? [];
            $updateData['non_fhl_income_json'] = $data['non_fhl_income'] ?? $summary->non_fhl_income_json ?? [];
            $updateData['non_fhl_expenses_json'] = $data['non_fhl_expenses'] ?? $summary->non_fhl_expenses_json ?? [];
        }

        $updateData['test_scenario'] = $data['test_scenario'] ?? $summary->test_scenario;

        $summary->update($updateData);

        // Now submit the amendment to HMRC
        $this->submitToHmrc($summaryId);

        return $summary->fresh();
    }

    /**
     * Submit period summary to HMRC
     */
    public function submitToHmrc(int $summaryId): array
    {
        $summary = HmrcUkPropertyPeriodSummary::findOrFail($summaryId);

        if (!$summary->canSubmit() && !$summary->canAmend()) {
            throw new \RuntimeException('This period summary cannot be submitted.');
        }

        $business = $summary->business;
        $nino = $summary->nino ?? $business->nino;

        if (!$nino) {
            throw new \RuntimeException('NINO is required for submission.');
        }

        // Build payload
        $payload = $this->buildSubmissionPayload($summary);

        Log::info('Submitting UK Property period summary to HMRC', [
            'summary_id' => $summaryId,
            'business_id' => $business->business_id,
            'nino' => $nino,
            'tax_year' => $summary->tax_year,
            'from_date' => $summary->from_date->format('Y-m-d'),
            'to_date' => $summary->to_date->format('Y-m-d'),
            'test_scenario' => $summary->test_scenario,
            'payload' => $payload
        ]);

        try {
            // Tax yeaar 2024-25 uses different endpoint for amendment
            if ($summary->tax_year <= '2024-25') {
                // If submission_id exists, use PUT to amend, otherwise POST to create
                if ($summary->submission_id) {
                    $endpoint = "/individuals/business/property/uk/{$nino}/" .
                        "{$business->business_id}/period/{$summary->tax_year}/{$summary->submission_id}";

                    $response = $this->apiClient->put(
                        $endpoint,
                        $payload,
                        ['Accept' => 'application/vnd.hmrc.6.0+json'],
                        $summary->test_scenario
                    );
                } else {
                    $endpoint = "/individuals/business/property/uk/{$nino}/" .
                        "{$business->business_id}/period/{$summary->tax_year}";

                    $response = $this->apiClient->post(
                        $endpoint,
                        $payload,
                        ['Accept' => 'application/vnd.hmrc.6.0+json'],
                        $summary->test_scenario
                    );
                }
            }

            // Tax year 2025-26 use same endpoint for amendment and creation
            else {
                $endpoint = "/individuals/business/property/uk/{$nino}/" .
                    "{$business->business_id}/cumulative/{$summary->tax_year}";

                $response = $this->apiClient->put(
                    $endpoint,
                    $payload,
                    ['Accept' => 'application/vnd.hmrc.6.0+json'],
                    $summary->test_scenario
                );
            }

            // Update summary with submission ID from response
            $submissionId = $response['submissionId'] ?? $summary->submission_id;

            $summary->update([
                'submission_id' => $submissionId,
                'submission_date' => now(),
                'response_json' => $response,
                'status' => 'submitted'
            ]);

            Log::info('UK Property period summary submitted successfully', [
                'summary_id' => $summaryId,
                'submission_id' => $submissionId
            ]);

            return $response;
        } catch (HmrcApiException $e) {
            Log::error('Failed to submit UK Property period summary to HMRC', [
                'summary_id' => $summaryId,
                'error' => $e->getMessage(),
                'hmrc_code' => $e->hmrcCode
            ]);

            $summary->update([
                'status' => 'failed',
                'response_json' => [
                    'error' => $e->getMessage(),
                    'code' => $e->hmrcCode,
                    'errors' => $e->errors,
                    'timestamp' => now()->toISOString()
                ]
            ]);

            throw $e;
        }
    }

    /**
     * List period summaries from HMRC
     */
    public function listPeriodSummaries(string $nino, string $businessId, string $taxYear, ?string $testScenario = null): array
    {
        $endpoint = "/individuals/business/property/{$nino}/{$businessId}/period/{$taxYear}";

        try {
            return $this->apiClient->get(
                $endpoint,
                ['Accept' => 'application/vnd.hmrc.6.0+json'],
                $testScenario
            );
        } catch (HmrcApiException $e) {
            Log::error('Failed to list UK Property period summaries from HMRC', [
                'nino' => $nino,
                'business_id' => $businessId,
                'tax_year' => $taxYear,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Retrieve a single period summary from HMRC
     */
    public function retrieveFromHmrc(string $nino, string $businessId, string $taxYear, string $submissionId, ?string $testScenario = null): array
    {
        $endpoint = "/individuals/business/property/uk/{$nino}/{$businessId}/period/{$taxYear}/{$submissionId}";

        try {
            return $this->apiClient->get(
                $endpoint,
                ['Accept' => 'application/vnd.hmrc.6.0+json'],
                $testScenario
            );
        } catch (HmrcApiException $e) {
            Log::error('Failed to retrieve UK Property period summary from HMRC', [
                'nino' => $nino,
                'business_id' => $businessId,
                'tax_year' => $taxYear,
                'submission_id' => $submissionId,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Validate period dates to prevent overlaps
     *
     * For TY < 2025-26: No overlaps allowed at all (quarterly periods)
     * For TY >= 2025-26: Cumulative periods allowed with specific rules:
     *   - No exact duplicates (same from_date and to_date)
     *   - End date cannot move backwards (must be >= existing end dates)
     *   - Start date alignment may be enforced by HMRC API
     */
    protected function validatePeriodDates(string $businessId, string $fromDate, string $toDate, ?int $excludeId = null, ?string $taxYear = null): void
    {
        // Check that to_date is after from_date
        $from = Carbon::parse($fromDate);
        $to = Carbon::parse($toDate);

        if ($to->lte($from)) {
            throw new \InvalidArgumentException('The end date must be after the start date.');
        }

        // Determine if this is a cumulative period (TY 2025-26+)
        $isCumulative = $taxYear && $taxYear >= '2025-26';

        if ($isCumulative) {
            // CUMULATIVE PERIOD VALIDATION (TY 2025-26+)

            // 1. Check for exact duplicate (same from_date and to_date)
            $exactDuplicate = HmrcUkPropertyPeriodSummary::where('business_id', $businessId)
                ->where('from_date', $fromDate)
                ->where('to_date', $toDate)
                ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
                ->exists();

            if ($exactDuplicate) {
                throw new \InvalidArgumentException('A period summary with these exact dates already exists. Please use different dates.');
            }

            // 2. Check if end date is moving backwards (SUBMISSION_END_DATE_CANNOT_MOVE_BACKWARDS)
            // Get the latest to_date for this business
            $latestToDate = HmrcUkPropertyPeriodSummary::where('business_id', $businessId)
                ->where('tax_year', $taxYear)
                ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
                ->max('to_date');

            if ($latestToDate && $to->lt(Carbon::parse($latestToDate))) {
                throw new \InvalidArgumentException(
                    'The end date cannot be earlier than your latest submission end date (' .
                    Carbon::parse($latestToDate)->format('d M Y') . '). ' .
                    'Cumulative periods must move forward.'
                );
            }

            // Note: START_DATE_NOT_ALIGNED_WITH_REPORTING_TYPE and
            // ADVANCE_SUBMISSION_REQUIRES_PERIOD_END_DATE are handled by HMRC API

        } else {
            // LEGACY VALIDATION (TY < 2025-26) - No overlaps allowed
            $overlapping = HmrcUkPropertyPeriodSummary::where('business_id', $businessId)
                ->overlapping($fromDate, $toDate, $excludeId)
                ->exists();

            if ($overlapping) {
                throw new \InvalidArgumentException('This period overlaps with an existing period summary.');
            }
        }
    }

    /**
     * Build submission payload for HMRC API
     */
    protected function buildSubmissionPayload(HmrcUkPropertyPeriodSummary $summary): array
    {
        $payload = [
            'fromDate' => $summary->from_date->format('Y-m-d'),
            'toDate' => $summary->to_date->format('Y-m-d'),
        ];


        if ($summary->tax_year <= '2024-25') {
            return $this->buildSubmissionPayloadLegacy($summary);
        }

        return $this->buildSubmissionPayloadCumulative($summary);
    }

    // Build payload for <= 2024-25 tax years
    // Both ukFhlProperty and ukNonFhlProperty are always included even if all fields are 0
    private function buildSubmissionPayloadLegacy(HmrcUkPropertyPeriodSummary $summary): array
    {
        $payload = [
            'fromDate' => $summary->from_date->format('Y-m-d'),
            'toDate' => $summary->to_date->format('Y-m-d'),
        ];

        // Build FHL property data - always include with defaults
        $fhlProperty = [];

        // FHL Income - always include with defaults
        $fhlIncomeData = $summary->fhl_income_json ?? [];
        $income = $this->buildFhlIncome($fhlIncomeData);
        $fhlProperty['income'] = $income;

        // FHL Expenses - always include with defaults
        $fhlExpensesData = $summary->fhl_expenses_json ?? [];
        $expenses = $this->buildFhlExpenses($fhlExpensesData);
        $fhlProperty['expenses'] = $expenses;

        // ukFhlProperty is always included for legacy periods
        $payload['ukFhlProperty'] = $fhlProperty;

        // Build Non-FHL property data - always include with defaults
        $nonFhlProperty = [];

        // Non-FHL Income - always include with defaults
        $nonFhlIncomeData = $summary->non_fhl_income_json ?? [];
        $income = $this->buildNonFhlIncome($nonFhlIncomeData);
        $nonFhlProperty['income'] = $income;

        // Non-FHL Expenses - always include with defaults
        $nonFhlExpensesData = $summary->non_fhl_expenses_json ?? [];
        $expenses = $this->buildNonFhlExpenses($nonFhlExpensesData);
        $nonFhlProperty['expenses'] = $expenses;

        // ukNonFhlProperty is always included for legacy periods
        $payload['ukNonFhlProperty'] = $nonFhlProperty;

        return $payload;
    }

    // Build payload for 2025-26+ tax years (unified property structure)
    private function buildSubmissionPayloadCumulative(HmrcUkPropertyPeriodSummary $summary): array
    {
        $payload = [
            'fromDate' => $summary->from_date->format('Y-m-d'),
            'toDate' => $summary->to_date->format('Y-m-d'),
        ];

        // For 2025-26+, ukProperty is MANDATORY even if all fields are 0
        $ukProperty = [];

        // Build unified income - always include with defaults
        $incomeData = $summary->uk_property_income_json ?? [];
        $income = $this->buildUnifiedIncome($incomeData);
        $ukProperty['income'] = $income;

        // Build unified expenses - always include with defaults
        $expensesData = $summary->uk_property_expenses_json ?? [];
        $expenses = $this->buildUnifiedExpenses($expensesData);
        $ukProperty['expenses'] = $expenses;

        // ukProperty is always required for cumulative periods
        $payload['ukProperty'] = $ukProperty;

        return $payload;
    }

    /**
     * Build FHL income payload
     * For legacy periods (<=2024-25), all fields must be present even if 0
     */
    protected function buildFhlIncome(array $data): array
    {
        $income = [];

        $fields = [
            'period_amount' => 'periodAmount',
            'tax_deducted' => 'taxDeducted',
        ];

        // Always include all fields with 0 as default
        foreach ($fields as $key => $apiKey) {
            $value = $data[$key] ?? 0;
            $income[$apiKey] = $this->formatAmount($value);
        }

        // Rent a Room - always include with 0 as default
        $income['rentARoom'] = [
            'rentsReceived' => $this->formatAmount($data['rent_a_room']['rents_received'] ?? 0)
        ];

        return $income;
    }

    /**
     * Build FHL expenses payload
     * For legacy periods (<=2024-25), all fields must be present even if 0
     */
    protected function buildFhlExpenses(array $data): array
    {
        $expenses = [];

        // Check if consolidated expenses is explicitly set (even if 0)
        $hasConsolidated = isset($data['consolidated_expenses']) &&
                          $data['consolidated_expenses'] !== null &&
                          $data['consolidated_expenses'] !== '';

        if ($hasConsolidated) {
            // Consolidated mode - only send consolidatedExpenses + rentARoom
            $expenses['consolidatedExpenses'] = $this->formatAmount($data['consolidated_expenses']);

            // Can include rentARoom with consolidated expenses for TY 2024-25
            $expenses['rentARoom'] = [
                'amountClaimed' => $this->formatAmount($data['rent_a_room']['amount_claimed'] ?? 0)
            ];

            return $expenses;
        }

        // Itemized mode - send all itemized fields with 0 as default
        $fields = [
            'premises_running_costs' => 'premisesRunningCosts',
            'repairs_and_maintenance' => 'repairsAndMaintenance',
            'financial_costs' => 'financialCosts',
            'professional_fees' => 'professionalFees',
            'cost_of_services' => 'costOfServices',
            'travel_costs' => 'travelCosts',
            'other' => 'other',
        ];

        foreach ($fields as $key => $apiKey) {
            $value = $data[$key] ?? 0;
            $expenses[$apiKey] = $this->formatAmount($value);
        }

        // Rent a Room - always include with 0 as default
        $expenses['rentARoom'] = [
            'amountClaimed' => $this->formatAmount($data['rent_a_room']['amount_claimed'] ?? 0)
        ];

        return $expenses;
    }

    /**
     * Build Non-FHL income payload
     * For legacy periods (<=2024-25), all fields must be present even if 0
     */
    protected function buildNonFhlIncome(array $data): array
    {
        $income = [];

        $fields = [
            'premiums_of_lease_grant' => 'premiumsOfLeaseGrant',
            'reverse_premiums' => 'reversePremiums',
            'period_amount' => 'periodAmount',
            'tax_deducted' => 'taxDeducted',
            'other_income' => 'otherIncome',
        ];

        // Always include all fields with 0 as default
        foreach ($fields as $key => $apiKey) {
            $value = $data[$key] ?? 0;
            $income[$apiKey] = $this->formatAmount($value);
        }

        // Rent a Room - always include with 0 as default
        $income['rentARoom'] = [
            'rentsReceived' => $this->formatAmount($data['rent_a_room']['rents_received'] ?? 0)
        ];

        return $income;
    }

    /**
     * Build Non-FHL expenses payload
     * For legacy periods (<=2024-25), all fields must be present even if 0
     */
    protected function buildNonFhlExpenses(array $data): array
    {
        $expenses = [];

        // Check if consolidated expenses is explicitly set (even if 0)
        $hasConsolidated = isset($data['consolidated_expenses']) &&
                          $data['consolidated_expenses'] !== null &&
                          $data['consolidated_expenses'] !== '';

        if ($hasConsolidated) {
            // Consolidated mode - only send consolidatedExpenses + special fields
            $expenses['consolidatedExpenses'] = $this->formatAmount($data['consolidated_expenses']);

            // Can include these fields with consolidated expenses for TY 2024-25
            $expenses['residentialFinancialCost'] = $this->formatAmount($data['residential_financial_cost'] ?? 0);
            $expenses['residentialFinancialCostsCarriedForward'] = $this->formatAmount($data['residential_financial_costs_carried_forward'] ?? 0);
            $expenses['rentARoom'] = [
                'amountClaimed' => $this->formatAmount($data['rent_a_room']['amount_claimed'] ?? 0)
            ];

            return $expenses;
        }

        // Itemized mode - send all itemized fields with 0 as default
        $fields = [
            'premises_running_costs' => 'premisesRunningCosts',
            'repairs_and_maintenance' => 'repairsAndMaintenance',
            'financial_costs' => 'financialCosts',
            'professional_fees' => 'professionalFees',
            'cost_of_services' => 'costOfServices',
            'other' => 'other',
            'residential_financial_cost' => 'residentialFinancialCost',
            'travel_costs' => 'travelCosts',
            'residential_financial_costs_carried_forward' => 'residentialFinancialCostsCarriedForward',
        ];

        foreach ($fields as $key => $apiKey) {
            $value = $data[$key] ?? 0;
            $expenses[$apiKey] = $this->formatAmount($value);
        }

        // Rent a Room - always include with 0 as default
        $expenses['rentARoom'] = [
            'amountClaimed' => $this->formatAmount($data['rent_a_room']['amount_claimed'] ?? 0)
        ];

        return $expenses;
    }

    /**
     * Build unified income payload for 2025-26+
     * Combines all property income fields into single ukProperty.income structure
     * For 2025-26+, all fields must be present even if 0
     */
    protected function buildUnifiedIncome(array $data): array
    {
        $income = [];

        $fields = [
            'premiums_of_lease_grant' => 'premiumsOfLeaseGrant',
            'reverse_premiums' => 'reversePremiums',
            'period_amount' => 'periodAmount',
            'tax_deducted' => 'taxDeducted',
            'other_income' => 'otherIncome',
        ];

        // For cumulative periods (2025-26+), always include all fields with 0 as default
        foreach ($fields as $key => $apiKey) {
            $value = $data[$key] ?? 0;
            $income[$apiKey] = $this->formatAmount($value);
        }

        // Rent a Room - always include with 0 as default
        $income['rentARoom'] = [
            'rentsReceived' => $this->formatAmount($data['rent_a_room']['rents_received'] ?? 0)
        ];

        return $income;
    }

    /**
     * Build unified expenses payload for 2025-26+
     * Combines all property expense fields into single ukProperty.expenses structure
     * For 2025-26+, all fields must be present even if 0
     */
    protected function buildUnifiedExpenses(array $data): array
    {
        $expenses = [];

        // Check if consolidated expenses is explicitly set (even if 0)
        $hasConsolidated = isset($data['consolidated_expenses']) &&
                          $data['consolidated_expenses'] !== null &&
                          $data['consolidated_expenses'] !== '';

        if ($hasConsolidated) {
            // Consolidated mode - only send consolidatedExpenses + special fields
            $expenses['consolidatedExpenses'] = $this->formatAmount($data['consolidated_expenses']);

            // Can include these fields with consolidated expenses for TY 2025-26+
            $expenses['residentialFinancialCost'] = $this->formatAmount($data['residential_financial_cost'] ?? 0);
            $expenses['residentialFinancialCostsCarriedForward'] = $this->formatAmount($data['residential_financial_costs_carried_forward'] ?? 0);
            $expenses['rentARoom'] = [
                'amountClaimed' => $this->formatAmount($data['rent_a_room']['amount_claimed'] ?? 0)
            ];

            return $expenses;
        }

        // Itemized mode - send all itemized fields with 0 as default
        $fields = [
            'premises_running_costs' => 'premisesRunningCosts',
            'repairs_and_maintenance' => 'repairsAndMaintenance',
            'financial_costs' => 'financialCosts',
            'professional_fees' => 'professionalFees',
            'cost_of_services' => 'costOfServices',
            'other' => 'other',
            'residential_financial_cost' => 'residentialFinancialCost',
            'travel_costs' => 'travelCosts',
            'residential_financial_costs_carried_forward' => 'residentialFinancialCostsCarriedForward',
        ];

        foreach ($fields as $key => $apiKey) {
            $value = $data[$key] ?? 0;
            $expenses[$apiKey] = $this->formatAmount($value);
        }

        // Rent a Room - always include with 0 as default
        $expenses['rentARoom'] = [
            'amountClaimed' => $this->formatAmount($data['rent_a_room']['amount_claimed'] ?? 0)
        ];

        return $expenses;
    }

    /**
     * Format amount to 2 decimal places
     */
    protected function formatAmount($value): float
    {
        return round((float) $value, 2);
    }
}
