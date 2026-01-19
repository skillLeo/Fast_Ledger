<?php

namespace App\Services\Hmrc;

use App\Models\VatSubmission;
use App\Repositories\VatSubmissionRepository;
use App\Exceptions\HmrcApiException;
use App\Exceptions\InvalidVatReturnException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class VatService
{
    protected HmrcClient $client;
    protected OAuthService $oauthService;
    protected VatSubmissionRepository $submissionRepository;
    // protected VatObligationService $obligationService;

    public function __construct(
        HmrcClient $client,
        OAuthService $oauthService,
        VatSubmissionRepository $submissionRepository,
        // VatObligationService $obligationService
    ) {
        $this->client = $client;
        $this->oauthService = $oauthService;
        $this->submissionRepository = $submissionRepository;
        // $this->obligationService = $obligationService;
    }

    /**
     * Get VAT obligations from HMRC
     */
    public function getObligations(?string $vrn = null, array $params = []): array
    {
        $vrn = $vrn ?? config('hmrc.vat.vrn');
        $token = $this->oauthService->getValidToken($vrn);

        $queryString = http_build_query(array_filter($params));
        $endpoint = "/organisations/vat/{$vrn}/obligations" . ($queryString ? "?{$queryString}" : '');

        try {
            return $this->client->get($endpoint, $token->access_token);
        } catch (HmrcApiException $e) {
            Log::error('Failed to fetch VAT obligations', [
                'vrn' => $vrn,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Get VAT return for specific period
     */
    public function getReturn(string $periodKey, ?string $vrn = null): array
    {
        $vrn = $vrn ?? config('hmrc.vat.vrn');
        $token = $this->oauthService->getValidToken($vrn);

        $endpoint = "/organisations/vat/{$vrn}/returns/{$periodKey}";

        try {
            return $this->client->get($endpoint, $token->access_token);
        } catch (HmrcApiException $e) {
            Log::error('Failed to fetch VAT return', [
                'vrn' => $vrn,
                'period_key' => $periodKey,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Submit VAT return to HMRC
     */
    public function submitReturn(array $data, ?string $vrn = null): VatSubmission
    {
        $vrn = $vrn ?? config('hmrc.vat.vrn');

        // Validate data
        $this->validateVatReturnData($data);

        // Check if already submitted
        if ($this->submissionRepository->isPeriodSubmitted($vrn, $data['periodKey'])) {
            throw new InvalidVatReturnException(
                'This period has already been submitted',
                ['periodKey' => ['Period already submitted to HMRC']]
            );
        }

        DB::beginTransaction();

        try {
            // Get valid token
            $token = $this->oauthService->getValidToken($vrn);

            // Submit to HMRC
            $endpoint = "/organisations/vat/{$vrn}/returns";
            $response = $this->client->post($endpoint, $token->access_token, $data);

            // Save successful submission
            $submission = $this->submissionRepository->create([
                'vrn' => $vrn,
                'period_key' => $data['periodKey'],
                'vat_due_sales' => $data['vatDueSales'],
                'vat_due_acquisitions' => $data['vatDueAcquisitions'],
                'total_vat_due' => $data['totalVatDue'],
                'vat_reclaimed_curr_period' => $data['vatReclaimedCurrPeriod'],
                'net_vat_due' => $data['netVatDue'],
                'total_value_sales_ex_vat' => $data['totalValueSalesExVAT'],
                'total_value_purchases_ex_vat' => $data['totalValuePurchasesExVAT'],
                'total_value_goods_supplied_ex_vat' => $data['totalValueGoodsSuppliedExVAT'],
                'total_acquisitions_ex_vat' => $data['totalAcquisitionsExVAT'],
                'submitted_by_user_id' => auth()->id(),
                'submitted_at' => now(),
                'hmrc_response' => $response,
                'successful' => true,
                'processing_date' => $response['processingDate'] ?? null,
            ]);

            // 🎯 MARK OBLIGATION AS FULFILLED - RESOLVE SERVICE HERE
            try {
                // ✅ Resolve the service when needed (lazy resolution)
                $obligationService = app(VatObligationService::class);

                // ✅ STEP 1: Sync obligations from HMRC to get the latest data
                Log::info('Syncing obligations after VAT submission', [
                    'period_key' => $data['periodKey'],
                    'vrn' => $vrn,
                ]);

                $syncedCount = $obligationService->syncObligations($vrn);

                Log::info('Obligations synced successfully', [
                    'vrn' => $vrn,
                    'synced_count' => $syncedCount,
                ]);

                // ✅ STEP 2: Now mark the specific obligation as fulfilled
                $obligationService->markAsFulfilled($data['periodKey'], $vrn);

                Log::info('Obligation marked as fulfilled after sync', [
                    'period_key' => $data['periodKey'],
                    'vrn' => $vrn,
                ]);
            } catch (\Exception $e) {
                Log::warning('Failed to sync/mark obligation as fulfilled', [
                    'period_key' => $data['periodKey'],
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                // Don't throw - submission was successful, this is auxiliary
            }

            DB::commit();

            return $submission;
        } catch (HmrcApiException $e) {
            DB::rollBack();

            // Save failed submission
            $failedSubmission = $this->submissionRepository->create([
                'vrn' => $vrn,
                'period_key' => $data['periodKey'] ?? null,
                'vat_due_sales' => $data['vatDueSales'] ?? 0,
                'vat_due_acquisitions' => $data['vatDueAcquisitions'] ?? 0,
                'total_vat_due' => $data['totalVatDue'] ?? 0,
                'vat_reclaimed_curr_period' => $data['vatReclaimedCurrPeriod'] ?? 0,
                'net_vat_due' => $data['netVatDue'] ?? 0,
                'total_value_sales_ex_vat' => $data['totalValueSalesExVAT'] ?? 0,
                'total_value_purchases_ex_vat' => $data['totalValuePurchasesExVAT'] ?? 0,
                'total_value_goods_supplied_ex_vat' => $data['totalValueGoodsSuppliedExVAT'] ?? 0,
                'total_acquisitions_ex_vat' => $data['totalAcquisitionsExVAT'] ?? 0,
                'submitted_by_user_id' => auth()->id(),
                'submitted_at' => now(),
                'hmrc_response' => $e->getErrors(), 
                'successful' => false,
                'error_message' => $e->getMessage(),
            ]);

            Log::error('VAT return submission failed', [
                'submission_id' => $failedSubmission->id,
                'period_key' => $data['periodKey'] ?? null,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Get VAT liabilities
     */
    public function getLiabilities(?string $vrn = null, array $params = []): array
    {
        $vrn = $vrn ?? config('hmrc.vat.vrn');
        $token = $this->oauthService->getValidToken($vrn);

        $queryString = http_build_query(array_filter($params));
        $endpoint = "/organisations/vat/{$vrn}/liabilities" . ($queryString ? "?{$queryString}" : '');

        try {
            return $this->client->get($endpoint, $token->access_token);
        } catch (HmrcApiException $e) {
            Log::error('Failed to fetch VAT liabilities', [
                'vrn' => $vrn,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Get VAT payments
     */
    public function getPayments(?string $vrn = null, array $params = []): array
    {
        $vrn = $vrn ?? config('hmrc.vat.vrn');
        $token = $this->oauthService->getValidToken($vrn);

        $queryString = http_build_query(array_filter($params));
        $endpoint = "/organisations/vat/{$vrn}/payments" . ($queryString ? "?{$queryString}" : '');

        try {
            return $this->client->get($endpoint, $token->access_token);
        } catch (HmrcApiException $e) {
            Log::error('Failed to fetch VAT payments', [
                'vrn' => $vrn,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Validate VAT return data
     */
    protected function validateVatReturnData(array $data): void
    {
        $errors = [];

        // Required fields
        $requiredFields = [
            'periodKey',
            'vatDueSales',
            'vatDueAcquisitions',
            'totalVatDue',
            'vatReclaimedCurrPeriod',
            'netVatDue',
            'totalValueSalesExVAT',
            'totalValuePurchasesExVAT',
            'totalValueGoodsSuppliedExVAT',
            'totalAcquisitionsExVAT',
            'finalised',
        ];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                $errors[$field] = ["The {$field} field is required"];
            }
        }

        // Validate calculations
        if (isset($data['vatDueSales'], $data['vatDueAcquisitions'], $data['totalVatDue'])) {
            $calculatedTotal = round($data['vatDueSales'] + $data['vatDueAcquisitions'], 2);
            if ($calculatedTotal != $data['totalVatDue']) {
                $errors['totalVatDue'] = ['Total VAT due calculation is incorrect'];
            }
        }

        // Validate finalised flag
        if (isset($data['finalised']) && $data['finalised'] !== true) {
            $errors['finalised'] = ['VAT return must be finalised before submission'];
        }

        if (!empty($errors)) {
            throw new InvalidVatReturnException(
                'VAT return validation failed',
                $errors
            );
        }
    }
}
