<?php

namespace App\Services\Hmrc;

use App\Models\HmrcAnnualSubmission;
use App\Models\HmrcBusiness;
use App\Models\HmrcPeriodicSubmission;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HmrcAnnualSubmissionService
{
    protected HmrcApiClient $apiClient;

    public function __construct(HmrcApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    /**
     * Create a draft annual submission
     */
    public function createDraft(
        int $userId,
        string $businessId,
        string $taxYear,
        array $data
    ): HmrcAnnualSubmission {
        $business = HmrcBusiness::where('business_id', $businessId)
            ->where('user_id', $userId)
            ->firstOrFail();

        return HmrcAnnualSubmission::create([
            'user_id' => $userId,
            'business_id' => $businessId,
            'nino' => $data['nino'] ?? $business->nino ?? null,
            'tax_year' => $taxYear,
            'adjustments_json' => $data['adjustments'] ?? null,
            'allowances_json' => $data['allowances'] ?? null,
            'non_financials_json' => $data['non_financials'] ?? null,
            'notes' => $data['notes'] ?? null,
            'status' => 'draft'
        ]);
    }

    /**
     * Update draft submission
     */
    public function updateDraft(int $submissionId, array $data): HmrcAnnualSubmission
    {
        $submission = HmrcAnnualSubmission::findOrFail($submissionId);

        if (!$submission->canEdit()) {
            throw new \RuntimeException('This submission cannot be edited.');
        }

        $submission->update([
            'adjustments_json' => $data['adjustments'] ?? $submission->adjustments_json,
            'allowances_json' => $data['allowances'] ?? $submission->allowances_json,
            'non_financials_json' => $data['non_financials'] ?? $submission->non_financials_json,
            'notes' => $data['notes'] ?? $submission->notes,
        ]);

        return $submission->fresh();
    }

    /**
     * Submit to HMRC
     */
    public function submitToHmrc(int $submissionId): array
    {
        $submission = HmrcAnnualSubmission::findOrFail($submissionId);

        if (!$submission->canSubmit()) {
            throw new \RuntimeException('This submission cannot be submitted.');
        }

        $business = $submission->business;
        $nino = $submission->nino ?? $business->nino;

        if (!$nino) {
            throw new \RuntimeException('NINO is required for submission.');
        }

        // Build payload
        $payload = $this->buildSubmissionPayload($submission);

        Log::info('Submitting annual update to HMRC', [
            'submission_id' => $submissionId,
            'business_id' => $business->business_id,
            'nino' => $nino,
            'tax_year' => $submission->tax_year,
            'payload' => $payload
        ]);

        try {
            $endpoint = "/individuals/business/self-employment/{$nino}/" .
                "{$business->business_id}/annual/{$submission->tax_year}";

            $response = $this->apiClient->put($endpoint, $payload, [
                'Accept' => 'application/vnd.hmrc.5.0+json'
            ]);

            // Update submission
            $submission->update([
                'submission_date' => now(),
                'response_json' => $response,
                'status' => 'submitted'
            ]);

            Log::info('Annual update submitted successfully', [
                'submission_id' => $submissionId
            ]);

            return $response;
        } catch (\Exception $e) {
            Log::error('Failed to submit annual update to HMRC', [
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
     * Build submission payload based on tax year and trading allowance choice
     */
    protected function buildSubmissionPayload(HmrcAnnualSubmission $submission): array
    {
        $payload = [];
        $taxYear = $submission->tax_year;
        $taxYearNum = (int) substr($taxYear, 0, 4);

        // Add adjustments if present (flat structure)
        if ($submission->adjustments_json && !empty(array_filter($submission->adjustments_json, fn($v) => !is_null($v)))) {
            $adjustments = $this->buildAdjustments($submission->adjustments_json, $taxYearNum);
            if (!empty($adjustments)) {
                $payload['adjustments'] = $adjustments;
            }
        }

        // Add allowances if present (trading allowance OR capital allowances)
        if ($submission->allowances_json && !empty(array_filter($submission->allowances_json, fn($v) => !is_null($v)))) {
            $allowances = $this->buildAllowances($submission->allowances_json, $taxYearNum);
            if (!empty($allowances)) {
                $payload['allowances'] = $allowances;
            }
        }

        // Add non-financials if present
        if ($submission->non_financials_json && !empty(array_filter($submission->non_financials_json, fn($v) => !is_null($v)))) {
            $nonFinancials = $this->buildNonFinancials($submission->non_financials_json);
            if (!empty($nonFinancials)) {
                $payload['nonFinancials'] = $nonFinancials;
            }
        }

        return $payload;
    }

    /**
     * Build adjustments payload (flat structure)
     * Different fields available for different tax years
     */
    protected function buildAdjustments(array $data, int $taxYearNum): array
    {
        $adjustments = [];

        // Base fields for all tax years
        $baseFields = [
            'included_non_taxable_profits' => 'includedNonTaxableProfits',
            'basis_adjustment' => 'basisAdjustment',
            'overlap_relief_used' => 'overlapReliefUsed',
            'accounting_adjustment' => 'accountingAdjustment',
            'averaging_adjustment' => 'averagingAdjustment',
            'outstanding_business_income' => 'outstandingBusinessIncome',
            'balancing_charge_bpra' => 'balancingChargeBpra',
            'balancing_charge_other' => 'balancingChargeOther',
            'goods_and_services_own_use' => 'goodsAndServicesOwnUse'
        ];

        foreach ($baseFields as $key => $apiKey) {
            if (isset($data[$key]) && $data[$key] !== null && $data[$key] !== '') {
                $adjustments[$apiKey] = $this->formatAmount($data[$key]);
            }
        }

        // TY 2024-25+ additional fields
        if ($taxYearNum >= 2024) {
            $additionalFields = [
                'transition_profit_amount' => 'transitionProfitAmount',
                'transition_profit_acceleration_amount' => 'transitionProfitAccelerationAmount'
            ];

            foreach ($additionalFields as $key => $apiKey) {
                if (isset($data[$key]) && $data[$key] !== null && $data[$key] !== '') {
                    $adjustments[$apiKey] = $this->formatAmount($data[$key]);
                }
            }
        }

        return $adjustments;
    }

    /**
     * Build allowances payload
     * Either trading allowance OR capital allowances
     */
    protected function buildAllowances(array $data, int $taxYearNum): array
    {
        $allowances = [];

        // Check if using trading income allowance
        if (isset($data['trading_income_allowance']) && !empty($data['trading_income_allowance'])) {
            $allowances['tradingIncomeAllowance'] = $this->formatAmount($data['trading_income_allowance']);
            return $allowances;
        }

        // Otherwise, use capital allowances
        $baseFields = [
            'annual_investment_allowance' => 'annualInvestmentAllowance',
            'capital_allowance_main_pool' => 'capitalAllowanceMainPool',
            'capital_allowance_special_rate_pool' => 'capitalAllowanceSpecialRatePool',
            'business_premises_renovation_allowance' => 'businessPremisesRenovationAllowance',
            'enhanced_capital_allowance' => 'enhancedCapitalAllowance',
            'allowance_on_sales' => 'allowanceOnSales',
            'capital_allowance_single_asset_pool' => 'capitalAllowanceSingleAssetPool',
            'zero_emissions_car_allowance' => 'zeroEmissionsCarAllowance'
        ];

        foreach ($baseFields as $key => $apiKey) {
            if (isset($data[$key]) && $data[$key] !== null && $data[$key] !== '') {
                $allowances[$apiKey] = $this->formatAmount($data[$key]);
            }
        }

        // TY 2023-24 and 2024-25 only fields
        if ($taxYearNum <= 2024) {
            if (isset($data['zero_emissions_goods_vehicle_allowance']) && $data['zero_emissions_goods_vehicle_allowance'] !== null && $data['zero_emissions_goods_vehicle_allowance'] !== '') {
                $allowances['zeroEmissionsGoodsVehicleAllowance'] = $this->formatAmount($data['zero_emissions_goods_vehicle_allowance']);
            }
            if (isset($data['electric_charge_point_allowance']) && $data['electric_charge_point_allowance'] !== null && $data['electric_charge_point_allowance'] !== '') {
                $allowances['electricChargePointAllowance'] = $this->formatAmount($data['electric_charge_point_allowance']);
            }
        }

        // Structured Building Allowances (arrays)
        if (isset($data['structured_building_allowance']) && is_array($data['structured_building_allowance']) && !empty($data['structured_building_allowance'])) {
            $sbaArray = [];
            foreach ($data['structured_building_allowance'] as $sba) {
                if (isset($sba['amount'])) {
                    $sbaItem = [
                        'amount' => $this->formatAmount($sba['amount'])
                    ];

                    // First year details
                    if (isset($sba['first_year_qualifying_date']) || isset($sba['first_year_qualifying_amount'])) {
                        $sbaItem['firstYear'] = [];
                        if (isset($sba['first_year_qualifying_date']) && !empty($sba['first_year_qualifying_date'])) {
                            $sbaItem['firstYear']['qualifyingDate'] = $sba['first_year_qualifying_date'];
                        }
                        if (isset($sba['first_year_qualifying_amount']) && !empty($sba['first_year_qualifying_amount'])) {
                            $sbaItem['firstYear']['qualifyingAmountExpenditure'] = $this->formatAmount($sba['first_year_qualifying_amount']);
                        }
                    }

                    // Building details
                    if (isset($sba['building_name']) || isset($sba['building_postcode'])) {
                        $sbaItem['building'] = [];
                        if (isset($sba['building_name']) && !empty($sba['building_name'])) {
                            $sbaItem['building']['name'] = $sba['building_name'];
                        }
                        if (isset($sba['building_postcode']) && !empty($sba['building_postcode'])) {
                            $sbaItem['building']['postcode'] = strtoupper($sba['building_postcode']);
                        }
                    }

                    $sbaArray[] = $sbaItem;
                }
            }
            if (!empty($sbaArray)) {
                $allowances['structuredBuildingAllowance'] = $sbaArray;
            }
        }

        // Enhanced Structured Building Allowances (arrays)
        if (isset($data['enhanced_structured_building_allowance']) && is_array($data['enhanced_structured_building_allowance']) && !empty($data['enhanced_structured_building_allowance'])) {
            $esbaArray = [];
            foreach ($data['enhanced_structured_building_allowance'] as $esba) {
                if (isset($esba['amount'])) {
                    $esbaItem = [
                        'amount' => $this->formatAmount($esba['amount'])
                    ];

                    // First year details
                    if (isset($esba['first_year_qualifying_date']) || isset($esba['first_year_qualifying_amount'])) {
                        $esbaItem['firstYear'] = [];
                        if (isset($esba['first_year_qualifying_date']) && !empty($esba['first_year_qualifying_date'])) {
                            $esbaItem['firstYear']['qualifyingDate'] = $esba['first_year_qualifying_date'];
                        }
                        if (isset($esba['first_year_qualifying_amount']) && !empty($esba['first_year_qualifying_amount'])) {
                            $esbaItem['firstYear']['qualifyingAmountExpenditure'] = $this->formatAmount($esba['first_year_qualifying_amount']);
                        }
                    }

                    // Building details
                    if (isset($esba['building_name']) || isset($esba['building_postcode'])) {
                        $esbaItem['building'] = [];
                        if (isset($esba['building_name']) && !empty($esba['building_name'])) {
                            $esbaItem['building']['name'] = $esba['building_name'];
                        }
                        if (isset($esba['building_postcode']) && !empty($esba['building_postcode'])) {
                            $esbaItem['building']['postcode'] = strtoupper($esba['building_postcode']);
                        }
                    }

                    $esbaArray[] = $esbaItem;
                }
            }
            if (!empty($esbaArray)) {
                $allowances['enhancedStructuredBuildingAllowance'] = $esbaArray;
            }
        }

        return $allowances;
    }

    /**
     * Build non-financials payload
     */
    protected function buildNonFinancials(array $data): array
    {
        $nonFinancials = [];

        // Business structure fields
        if (isset($data['business_address_line_1']) && $data['business_address_line_1']) {
            $nonFinancials['businessAddressLine1'] = $data['business_address_line_1'];
        }
        if (isset($data['business_address_line_2']) && $data['business_address_line_2']) {
            $nonFinancials['businessAddressLine2'] = $data['business_address_line_2'];
        }
        if (isset($data['business_address_line_3']) && $data['business_address_line_3']) {
            $nonFinancials['businessAddressLine3'] = $data['business_address_line_3'];
        }
        if (isset($data['business_address_line_4']) && $data['business_address_line_4']) {
            $nonFinancials['businessAddressLine4'] = $data['business_address_line_4'];
        }
        if (isset($data['business_address_postcode']) && $data['business_address_postcode']) {
            $nonFinancials['businessAddressPostcode'] = $data['business_address_postcode'];
        }
        if (isset($data['business_address_country_code']) && $data['business_address_country_code']) {
            $nonFinancials['businessAddressCountryCode'] = $data['business_address_country_code'];
        }

        // Class 4 NICs exemption
        if (isset($data['class_4_nics_exemption_reason']) && $data['class_4_nics_exemption_reason']) {
            $nonFinancials['class4NicsExemptionReason'] = $data['class_4_nics_exemption_reason'];
        }

        // Business details changed recently
        if (isset($data['business_details_changed_recently'])) {
            $nonFinancials['businessDetailsChangedRecently'] = (bool) $data['business_details_changed_recently'];
        }

        return $nonFinancials;
    }

    /**
     * Get annual submission from HMRC
     */
    public function getAnnualSubmission(string $nino, string $businessId, string $taxYear): array
    {
        $endpoint = "/individuals/business/self-employment/{$nino}/" .
            "{$businessId}/annual/{$taxYear}";

        return $this->apiClient->get($endpoint, [
            'Accept' => 'application/vnd.hmrc.5.0+json'
        ]);
    }

    /**
     * Delete annual submission from HMRC
     */
    public function deleteAnnualSubmission(string $nino, string $businessId, string $taxYear): void
    {
        $endpoint = "/individuals/business/self-employment/{$nino}/" .
            "{$businessId}/annual/{$taxYear}";

        $this->apiClient->delete($endpoint, [
            'Accept' => 'application/vnd.hmrc.5.0+json'
        ]);
    }

    /**
     * Get submissions for user
     */
    public function getSubmissionsForUser(int $userId, ?string $status = null): Collection
    {
        $query = HmrcAnnualSubmission::where('user_id', $userId)
            ->with('business')
            ->orderBy('tax_year', 'desc');

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
        $baseQuery = HmrcAnnualSubmission::where('user_id', $userId);

        return [
            'total' => (clone $baseQuery)->count(),
            'submitted' => (clone $baseQuery)->submitted()->count(),
            'draft' => (clone $baseQuery)->draft()->count(),
            'failed' => (clone $baseQuery)->failed()->count(),
        ];
    }

    /**
     * Delete draft submission
     */
    public function deleteDraft(int $submissionId): bool
    {
        $submission = HmrcAnnualSubmission::findOrFail($submissionId);

        if (!$submission->canDelete()) {
            throw new \RuntimeException('Only draft submissions can be deleted.');
        }

        return $submission->delete();
    }

    /**
     * Get quarterly summary for tax year
     * This pre-populates the annual submission with sum of quarterly data
     */
    public function getQuarterlySummary(int $userId, string $businessId, string $taxYear): array
    {
        $periodicSubmissions = HmrcPeriodicSubmission::where('user_id', $userId)
            ->where('business_id', $businessId)
            ->where('tax_year', $taxYear)
            ->where('status', 'submitted')
            ->get();

        $summary = [
            'total_income' => 0,
            'total_expenses' => 0,
            'net_profit' => 0,
            'period_count' => $periodicSubmissions->count(),
            'periods' => []
        ];

        foreach ($periodicSubmissions as $submission) {
            $summary['total_income'] += $submission->total_income;
            $summary['total_expenses'] += $submission->total_expenses;
            $summary['net_profit'] += $submission->net_profit;

            $summary['periods'][] = [
                'period_label' => $submission->period_label,
                'income' => $submission->total_income,
                'expenses' => $submission->total_expenses,
                'profit' => $submission->net_profit
            ];
        }

        return $summary;
    }

    /**
     * Format amount to 2 decimal places
     */
    protected function formatAmount($value): float
    {
        return round((float) $value, 2);
    }
}
