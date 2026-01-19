<?php

namespace App\Services\Hmrc;

use App\Exceptions\HmrcApiException;
use App\Models\HmrcUkPropertyAnnualSubmission;
use App\Models\HmrcBusiness;
use Illuminate\Support\Facades\Log;

class HmrcUkPropertyAnnualSubmissionService
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
    ): HmrcUkPropertyAnnualSubmission {
        $business = HmrcBusiness::where('business_id', $businessId)
            ->where('user_id', $userId)
            ->firstOrFail();

        // For TY 2025-26+, FHL is not supported, so don't save FHL data
        $isFhlSupported = $taxYear < '2025-26';

        return HmrcUkPropertyAnnualSubmission::create([
            'user_id' => $userId,
            'business_id' => $businessId,
            'obligation_id' => $data['obligation_id'] ?? null,
            'nino' => $data['nino'] ?? $business->nino ?? null,
            'tax_year' => $taxYear,
            'adjustments_json' => $data['adjustments'] ?? null,
            'allowances_json' => $data['allowances'] ?? null,
            'test_scenario' => $data['test_scenario'] ?? null,
            'notes' => $data['notes'] ?? null,
            'status' => 'draft'
        ]);
    }

    /**
     * Update draft submission or amend submitted submission
     */
    public function updateDraft(int $submissionId, array $data): HmrcUkPropertyAnnualSubmission
    {
        $submission = HmrcUkPropertyAnnualSubmission::findOrFail($submissionId);

        // Allow editing drafts/failed submissions OR amending submitted submissions
        if (!$submission->canEdit() && !$submission->canAmend()) {
            throw new \RuntimeException('This submission cannot be edited or amended.');
        }

        // For TY 2025-26+, FHL is not supported, so don't save FHL data
        $isFhlSupported = $submission->tax_year < '2025-26';

        $submission->update([
            'adjustments_json' => $data['adjustments'] ?? $submission->adjustments_json,
            'allowances_json' => $data['allowances'] ?? $submission->allowances_json,
            'test_scenario' => $data['test_scenario'] ?? $submission->test_scenario,
            'notes' => $data['notes'] ?? $submission->notes,
        ]);

        return $submission->fresh();
    }

    /**
     * Submit to HMRC
     */
    public function submitToHmrc(int $submissionId): array
    {
        $submission = HmrcUkPropertyAnnualSubmission::findOrFail($submissionId);

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

        $isAmendment = $submission->status === 'submitted';

        Log::info($isAmendment ? 'Amending UK Property annual submission to HMRC' : 'Submitting UK Property annual submission to HMRC', [
            'submission_id' => $submissionId,
            'business_id' => $business->business_id,
            'nino' => $nino,
            'tax_year' => $submission->tax_year,
            'is_amendment' => $isAmendment,
            'test_scenario' => $submission->test_scenario,
            'payload' => $payload
        ]);

        try {
            $endpoint = "/individuals/business/property/uk/{$nino}/" .
                "{$business->business_id}/annual/{$submission->tax_year}";

            $response = $this->apiClient->put(
                $endpoint,
                $payload,
                ['Accept' => 'application/vnd.hmrc.6.0+json'],
                $submission->test_scenario
            );

            // Update submission
            $submission->update([
                'submission_date' => now(),
                'response_json' => array_merge($response, [
                    'is_amendment' => $isAmendment,
                    'amended_at' => $isAmendment ? now()->toISOString() : null,
                ]),
                'status' => 'submitted'
            ]);

            Log::info($isAmendment ? 'UK Property annual submission amended successfully' : 'UK Property annual submission submitted successfully', [
                'submission_id' => $submissionId,
                'is_amendment' => $isAmendment
            ]);

            return $response;
        } catch (HmrcApiException $e) {
            Log::error('Failed to submit UK Property annual submission to HMRC', [
                'submission_id' => $submissionId,
                'error' => $e->getMessage(),
                'hmrc_code' => $e->hmrcCode
            ]);

            $submission->update([
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
     * Retrieve submission from HMRC
     */
    public function retrieveFromHmrc(string $nino, string $businessId, string $taxYear, ?string $testScenario = null): array
    {
        $endpoint = "/individuals/business/property/uk/{$nino}/{$businessId}/annual/{$taxYear}";

        try {
            return $this->apiClient->get(
                $endpoint,
                ['Accept' => 'application/vnd.hmrc.6.0+json'],
                $testScenario
            );
        } catch (HmrcApiException $e) {
            Log::error('Failed to retrieve UK Property annual submission from HMRC', [
                'nino' => $nino,
                'business_id' => $businessId,
                'tax_year' => $taxYear,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Delete submission from HMRC
     */
    public function deleteFromHmrc(int $submissionId): bool
    {
        $submission = HmrcUkPropertyAnnualSubmission::findOrFail($submissionId);

        if (!$submission->canDelete()) {
            throw new \RuntimeException('This submission cannot be deleted.');
        }

        $business = $submission->business;
        $nino = $submission->nino ?? $business->nino;

        if (!$nino) {
            throw new \RuntimeException('NINO is required for deletion.');
        }

        $endpoint = "/individuals/business/property/{$nino}/{$business->business_id}/annual/{$submission->tax_year}";

        try {
            $this->apiClient->delete(
                $endpoint,
                ['Accept' => 'application/vnd.hmrc.6.0+json'],
                $submission->test_scenario
            );

            $submission->delete();

            Log::info('UK Property annual submission deleted successfully', [
                'submission_id' => $submissionId
            ]);

            return true;
        } catch (HmrcApiException $e) {
            Log::error('Failed to delete UK Property annual submission from HMRC', [
                'submission_id' => $submissionId,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Build submission payload for HMRC API
     * Annual submissions contain ONLY adjustments and allowances (no income/expenses)
     * Income and expenses are submitted via period summaries
     *
     * Structure differs based on tax year:
     * - TY BEFORE 2024-25: Both ukFhlProperty and ukProperty with separate data
     * - TY 2024-25: Both ukFhlProperty and ukProperty with separate data + new fields
     * - TY 2025-26+: ONLY ukProperty (FHL not supported)
     */
    protected function buildSubmissionPayload(HmrcUkPropertyAnnualSubmission $submission): array
    {
        $payload = [];
        $taxYear = $submission->tax_year;
        $taxYearNum = (int) substr($taxYear, 0, 4);

        // Determine structure based on tax year
        $isTY202425 = $taxYear === '2024-25';
        $isTY202526Plus = $taxYearNum >= 2025;

        // Get adjustments and allowances data from JSON
        $adjustmentsJson = $submission->adjustments_json ?? [];
        $allowancesJson = $submission->allowances_json ?? [];

        if ($isTY202526Plus) {
            // TY 2025-26+: ONLY ukProperty (no FHL)
            $ukProperty = [];

            // Build adjustments from the ukProperty key if exists, otherwise from root
            $adjustmentsData = $adjustmentsJson['ukProperty'] ?? $adjustmentsJson;
            if (!empty($adjustmentsData)) {
                $adjustments = $this->buildAdjustments($adjustmentsData, $taxYear, 'non_fhl');
                if (!empty($adjustments)) {
                    $ukProperty['adjustments'] = $adjustments;
                }
            }

            // Build allowances from the ukProperty key if exists, otherwise from root
            $allowancesData = $allowancesJson['ukProperty'] ?? $allowancesJson;
            if (!empty($allowancesData)) {
                $allowances = $this->buildAllowances($allowancesData, $taxYear, 'non_fhl');
                if (!empty($allowances)) {
                    $ukProperty['allowances'] = $allowances;
                }
            }

            if (!empty($ukProperty)) {
                $payload['ukProperty'] = $ukProperty;
            }
        } elseif ($isTY202425) {
            // TY 2024-25: Both ukFhlProperty and ukProperty with tabs
            // Build FHL property
            if (isset($adjustmentsJson['ukFhlProperty']) || isset($allowancesJson['ukFhlProperty'])) {
                $fhlProperty = [];

                if (isset($adjustmentsJson['ukFhlProperty'])) {
                    $adjustments = $this->buildAdjustments($adjustmentsJson['ukFhlProperty'], $taxYear, 'fhl');
                    if (!empty($adjustments)) {
                        $fhlProperty['adjustments'] = $adjustments;
                    }
                }

                if (isset($allowancesJson['ukFhlProperty'])) {
                    $allowances = $this->buildAllowances($allowancesJson['ukFhlProperty'], $taxYear, 'fhl');
                    if (!empty($allowances)) {
                        $fhlProperty['allowances'] = $allowances;
                    }
                }

                if (!empty($fhlProperty)) {
                    $payload['ukFhlProperty'] = $fhlProperty;
                }
            }

            // Build Non-FHL property
            if (isset($adjustmentsJson['ukProperty']) || isset($allowancesJson['ukProperty'])) {
                $ukProperty = [];

                if (isset($adjustmentsJson['ukProperty'])) {
                    $adjustments = $this->buildAdjustments($adjustmentsJson['ukProperty'], $taxYear, 'non_fhl');
                    if (!empty($adjustments)) {
                        $ukProperty['adjustments'] = $adjustments;
                    }
                }

                if (isset($allowancesJson['ukProperty'])) {
                    $allowances = $this->buildAllowances($allowancesJson['ukProperty'], $taxYear, 'non_fhl');
                    if (!empty($allowances)) {
                        $ukProperty['allowances'] = $allowances;
                    }
                }

                if (!empty($ukProperty)) {
                    $payload['ukProperty'] = $ukProperty;
                }
            }
        } else {
            // TY BEFORE 2024-25: Both ukFhlProperty and ukProperty with flat structure
            // Build FHL property
            if (isset($adjustmentsJson['ukFhlProperty']) || isset($allowancesJson['ukFhlProperty'])) {
                $fhlProperty = [];

                if (isset($adjustmentsJson['ukFhlProperty'])) {
                    $adjustments = $this->buildAdjustments($adjustmentsJson['ukFhlProperty'], $taxYear, 'fhl');
                    if (!empty($adjustments)) {
                        $fhlProperty['adjustments'] = $adjustments;
                    }
                }

                if (isset($allowancesJson['ukFhlProperty'])) {
                    $allowances = $this->buildAllowances($allowancesJson['ukFhlProperty'], $taxYear, 'fhl');
                    if (!empty($allowances)) {
                        $fhlProperty['allowances'] = $allowances;
                    }
                }

                if (!empty($fhlProperty)) {
                    $payload['ukFhlProperty'] = $fhlProperty;
                }
            }

            // Build Non-FHL property
            if (isset($adjustmentsJson['ukProperty']) || isset($allowancesJson['ukProperty'])) {
                $ukProperty = [];

                if (isset($adjustmentsJson['ukProperty'])) {
                    $adjustments = $this->buildAdjustments($adjustmentsJson['ukProperty'], $taxYear, 'non_fhl');
                    if (!empty($adjustments)) {
                        $ukProperty['adjustments'] = $adjustments;
                    }
                }

                if (isset($allowancesJson['ukProperty'])) {
                    $allowances = $this->buildAllowances($allowancesJson['ukProperty'], $taxYear, 'non_fhl');
                    if (!empty($allowances)) {
                        $ukProperty['allowances'] = $allowances;
                    }
                }

                if (!empty($ukProperty)) {
                    $payload['ukProperty'] = $ukProperty;
                }
            }
        }

        return $payload;
    }

    /**
     * Build adjustments payload
     * TY 2024-25+ includes new fields: periodOfGraceAdjustment (FHL only), nonResidentLandlord, rentARoom.jointlyLet
     */
    protected function buildAdjustments(array $data, string $taxYear, ?string $propertyType = null): array
    {
        $adjustments = [];
        $taxYearNum = (int) substr($taxYear, 0, 4);
        $isTY202425Plus = $taxYearNum >= 2024;

        // Common fields for all property types
        $fields = [
            'balancing_charge' => 'balancingCharge',
            'business_premises_renovation_allowance_balancing_charges' => 'businessPremisesRenovationAllowanceBalancingCharges',
        ];

        // Private Use Adjustment - NOT available for Non-FHL in TY 2025-26+
        if ($propertyType === 'fhl' || ($propertyType === 'non_fhl' && $taxYearNum < 2025)) {
            $fields['private_use_adjustment'] = 'privateUseAdjustment';
        }

        foreach ($fields as $key => $apiKey) {
            if (isset($data[$key]) && $data[$key] !== null && $data[$key] !== '') {
                $adjustments[$apiKey] = $this->formatAmount($data[$key]);
            }
        }

        // New fields for TY 2024-25+ (MUST be included as true/false, cannot be omitted)
        if ($isTY202425Plus) {
            // Period of Grace Adjustment - ONLY for FHL property
            if ($propertyType === 'fhl') {
                $adjustments['periodOfGraceAdjustment'] = isset($data['period_of_grace_adjustment'])
                    ? (bool) $data['period_of_grace_adjustment']
                    : false;
            }

            // Non-Resident Landlord (boolean - required, cannot be null)
            $adjustments['nonResidentLandlord'] = isset($data['non_resident_landlord'])
                ? (bool) $data['non_resident_landlord']
                : false;

            // Rent a Room - Jointly Let (boolean - required, cannot be null)
            $adjustments['rentARoom'] = [
                'jointlyLet' => isset($data['rent_a_room_jointly_let'])
                    ? (bool) $data['rent_a_room_jointly_let']
                    : false
            ];
        }

        return $adjustments;
    }

    /**
     * Build allowances payload
     * TY 2024-25+: propertyIncomeAllowance, structuredBuildingAllowance (array), enhancedStructuredBuildingAllowance (array)
     * TY 2025-26+: Some allowances removed based on property type
     *
     * Field availability:
     * - costOfReplacingDomesticItems: Non-FHL only (all tax years)
     * - electricChargePointAllowance: FHL only for TY 2024-25, Not available for TY 2025-26+
     * - zeroEmissionsGoodsVehicleAllowance: Non-FHL only, Not available for TY 2024-25+
     */
    protected function buildAllowances(array $data, string $taxYear, ?string $propertyType = null): array
    {
        $allowances = [];
        $taxYearNum = (int) substr($taxYear, 0, 4);
        $isTY202425Plus = $taxYearNum >= 2024;
        $isTY202526Plus = $taxYearNum >= 2025;

        // Property Income Allowance (new for TY 2024-25+, MUTUALLY EXCLUSIVE with other allowances)
        // If propertyIncomeAllowance is provided, ONLY include that and return (cannot be used with other allowances)
        if ($isTY202425Plus && isset($data['property_income_allowance']) && $data['property_income_allowance'] !== null && $data['property_income_allowance'] !== '') {
            return [
                'propertyIncomeAllowance' => $this->formatAmount($data['property_income_allowance'])
            ];
        }

        // If we reach here, propertyIncomeAllowance is NOT set, so include all other allowances

        // Common allowance fields (available for all property types and tax years)
        $fields = [
            'annual_investment_allowance' => 'annualInvestmentAllowance',
            'business_premises_renovation_allowance' => 'businessPremisesRenovationAllowance',
            'other_capital_allowance' => 'otherCapitalAllowance',
            'zero_emissions_car_allowance' => 'zeroEmissionsCarAllowance',
        ];

        // Cost of Replacing Domestic Items - Non-FHL ONLY
        if ($propertyType === 'non_fhl') {
            $fields['cost_of_replacing_domestic_goods'] = 'costOfReplacingDomesticItems';
        }

        // Electric Charge Point Allowance - Only for FHL in TY 2024-25, removed in TY 2025-26+
        if ($propertyType === 'fhl' && $taxYear === '2024-25') {
            $fields['electric_charge_point_allowance'] = 'electricChargePointAllowance';
        }

        // Zero Emissions Goods Vehicle Allowance - Non-FHL only, NOT available for TY 2024-25+
        if ($propertyType === 'non_fhl' && !$isTY202425Plus) {
            $fields['zero_emissions_goods_vehicle_allowance'] = 'zeroEmissionsGoodsVehicleAllowance';
        }

        // Old fields for TY before 2024-25
        if (!$isTY202425Plus) {
            $fields['electric_charge_point_allowance'] = 'electricChargePointAllowance';
            $fields['structure_and_buildings_allowance'] = 'structureAndBuildingsAllowance';
            $fields['enhanced_structure_and_buildings_allowance'] = 'enhancedStructureAndBuildingsAllowance';
        }

        foreach ($fields as $key => $apiKey) {
            if (isset($data[$key]) && $data[$key] !== null && $data[$key] !== '') {
                $allowances[$apiKey] = $this->formatAmount($data[$key]);
            }
        }

        // Structured Building Allowance (array format for TY 2024-25+)
        // IMPORTANT: building object is REQUIRED, not optional
        // Available for Non-FHL only
        if ($isTY202425Plus && $propertyType === 'non_fhl' && isset($data['structured_building_allowance']) && is_array($data['structured_building_allowance'])) {
            $sbaArray = [];
            foreach ($data['structured_building_allowance'] as $sba) {
                // Only include if we have both amount AND at least one building field
                if (!empty($sba['amount']) && (!empty($sba['building_name']) || !empty($sba['building_number']) || !empty($sba['building_postcode']))) {
                    $sbaItem = [
                        'amount' => $this->formatAmount($sba['amount']),
                    ];

                    // firstYear is optional
                    if (!empty($sba['first_year_qualifying_date']) && !empty($sba['first_year_qualifying_amount'])) {
                        $sbaItem['firstYear'] = [
                            'qualifyingDate' => $sba['first_year_qualifying_date'],
                            'qualifyingAmountExpenditure' => $this->formatAmount($sba['first_year_qualifying_amount'])
                        ];
                    }

                    // building object is REQUIRED - filter out null values but keep the object structure
                    $sbaItem['building'] = array_filter([
                        'name' => $sba['building_name'] ?? null,
                        'number' => $sba['building_number'] ?? null,
                        'postcode' => $sba['building_postcode'] ?? null,
                    ], function ($value) {
                        return $value !== null && $value !== '';
                    });

                    $sbaArray[] = $sbaItem;
                }
            }

            if (!empty($sbaArray)) {
                $allowances['structuredBuildingAllowance'] = $sbaArray;
            }
        }

        // Enhanced Structured Building Allowance (array format for TY 2024-25+)
        // IMPORTANT: building object is REQUIRED, not optional
        // Available for Non-FHL only
        if ($isTY202425Plus && $propertyType === 'non_fhl' && isset($data['enhanced_structured_building_allowance']) && is_array($data['enhanced_structured_building_allowance'])) {
            $esbaArray = [];
            foreach ($data['enhanced_structured_building_allowance'] as $esba) {
                // Only include if we have both amount AND at least one building field
                if (!empty($esba['amount']) && (!empty($esba['building_name']) || !empty($esba['building_number']) || !empty($esba['building_postcode']))) {
                    $esbaItem = [
                        'amount' => $this->formatAmount($esba['amount']),
                    ];

                    // firstYear is optional
                    if (!empty($esba['first_year_qualifying_date']) && !empty($esba['first_year_qualifying_amount'])) {
                        $esbaItem['firstYear'] = [
                            'qualifyingDate' => $esba['first_year_qualifying_date'],
                            'qualifyingAmountExpenditure' => $this->formatAmount($esba['first_year_qualifying_amount'])
                        ];
                    }

                    // building object is REQUIRED - filter out null values but keep the object structure
                    $esbaItem['building'] = array_filter([
                        'name' => $esba['building_name'] ?? null,
                        'number' => $esba['building_number'] ?? null,
                        'postcode' => $esba['building_postcode'] ?? null,
                    ], function ($value) {
                        return $value !== null && $value !== '';
                    });

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
     * Format amount to 2 decimal places
     */
    protected function formatAmount($value): float
    {
        return round((float) $value, 2);
    }

    /**
     * Get payload preview for user review before submission
     * This allows users to see exactly what will be sent to HMRC
     */
    public function getPayloadPreview(int $submissionId): array
    {
        $submission = HmrcUkPropertyAnnualSubmission::findOrFail($submissionId);

        return [
            'submission_id' => $submission->id,
            'tax_year' => $submission->tax_year,
            'business_id' => $submission->business_id,
            'nino' => $submission->nino,
            'endpoint' => "/individuals/business/property/uk/{$submission->nino}/{$submission->business_id}/annual/{$submission->tax_year}",
            'method' => 'PUT',
            'headers' => [
                'Accept' => 'application/vnd.hmrc.6.0+json',
                'Content-Type' => 'application/json',
            ],
            'payload' => $this->buildSubmissionPayload($submission),
            'is_amendment' => $submission->status === 'submitted',
        ];
    }
}
