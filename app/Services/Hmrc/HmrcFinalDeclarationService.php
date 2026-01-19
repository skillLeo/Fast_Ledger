<?php

namespace App\Services\Hmrc;

use App\Models\HmrcFinalDeclaration;
use App\Models\HmrcCalculation;
use App\Models\HmrcObligation;
use App\Models\HmrcPeriodicSubmission;
use App\Models\HmrcAnnualSubmission;
use App\Exceptions\HmrcApiException;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HmrcFinalDeclarationService
{
    protected HmrcApiClient $apiClient;
    protected HmrcFinalDeclarationPrerequisitesService $prerequisitesService;
    protected HmrcCalculationService $calculationService;

    public function __construct(
        HmrcApiClient $apiClient,
        HmrcFinalDeclarationPrerequisitesService $prerequisitesService,
        HmrcCalculationService $calculationService
    ) {
        $this->apiClient = $apiClient;
        $this->prerequisitesService = $prerequisitesService;
        $this->calculationService = $calculationService;
    }

    /**
     * Initialize or retrieve existing declaration wizard
     */
    public function getOrCreateDeclaration(int $userId, string $nino, string $taxYear): HmrcFinalDeclaration
    {
        return HmrcFinalDeclaration::firstOrCreate(
            [
                'user_id' => $userId,
                'tax_year' => $taxYear,
            ],
            [
                'nino' => $nino,
                'status' => 'draft',
                'wizard_step' => 'prerequisites_check',
            ]
        );
    }

    /**
     * Validate prerequisites
     */
    public function validatePrerequisites(HmrcFinalDeclaration $declaration): array
    {
        $validation = $this->prerequisitesService->validatePrerequisites(
            $declaration->user_id,
            $declaration->tax_year
        );

        $declaration->update([
            'prerequisites_check' => $validation['checks'],
            'prerequisites_passed' => $validation['passed'],
        ]);

        return $validation;
    }

    /**
     * Mark wizard step as complete and advance
     */
    public function completeWizardStep(
        HmrcFinalDeclaration $declaration,
        string $step,
        array $data = []
    ): HmrcFinalDeclaration {
        $declaration->markStepComplete($step);

        if ($declaration->canProceedToNextStep()) {
            $nextStep = $declaration->getNextWizardStep();
            if ($nextStep) {
                $declaration->update(['wizard_step' => $nextStep]);
            }
        }

        return $declaration->fresh();
    }

    /**
     * Record declaration confirmation with audit trail
     */
    public function confirmDeclaration(
        HmrcFinalDeclaration $declaration,
        string $ipAddress,
        string $userAgent
    ): HmrcFinalDeclaration {
        $declaration->update([
            'declaration_confirmed' => true,
            'declaration_confirmed_at' => now(),
            'declaration_ip_address' => $ipAddress,
            'declaration_user_agent' => $userAgent,
            'status' => 'ready',
        ]);

        return $declaration->fresh();
    }

    /**
     * Submit final declaration to HMRC
     */
    public function submitFinalDeclaration(HmrcFinalDeclaration $declaration): array
    {
        // Final validation
        if (!$declaration->is_ready_for_submission) {
            throw new \Exception('Declaration is not ready for submission. Please complete all steps.');
        }

        if ($declaration->status === 'submitted') {
            throw new \Exception('This final declaration has already been submitted.');
        }

        DB::beginTransaction();

        try {
            // Mark as submitting
            $declaration->update(['status' => 'submitting']);

            // Call HMRC API to trigger crystallisation
            $endpoint = "/individuals/calculations/{$declaration->nino}/self-assessment/{$declaration->tax_year}";
            
            Log::info('Submitting final declaration to HMRC', [
                'declaration_id' => $declaration->id,
                'nino' => $declaration->nino,
                'tax_year' => $declaration->tax_year,
            ]);

            $response = $this->apiClient->post($endpoint, [
                'crystallise' => true,
            ], [
                'Accept' => 'application/vnd.hmrc.8.0+json',
            ]);

            $calculationId = (string) ($response['id'] ?? $response['calculationId']);

            // Store calculation record
            $calculation = HmrcCalculation::create([
                'user_id' => $declaration->user_id,
                'nino' => $declaration->nino,
                'tax_year' => $declaration->tax_year,
                'calculation_id' => $calculationId,
                'request_intent' => 'crystallisation',
                'status' => 'processing',
                'is_crystallised' => true,
                'crystallised_at' => now(),
                'crystallisation_response' => $response,
            ]);

            // Update declaration
            $declaration->update([
                'calculation_id' => $calculation->id,
                'status' => 'submitted',
                'submitted_at' => now(),
                'submission_response' => $response,
                'wizard_step' => 'completed',
            ]);

            // Update crystallisation obligation to fulfilled
            HmrcObligation::where('user_id', $declaration->user_id)
                ->where('obligation_type', 'crystallisation')
                ->where('period_start_date', '>=', $this->getTaxYearStart($declaration->tax_year))
                ->where('period_end_date', '<=', $this->getTaxYearEnd($declaration->tax_year))
                ->update(['status' => 'fulfilled', 'received_date' => now()]);

            DB::commit();

            // Fetch full calculation details (after short delay)
            try {
                sleep(3); // Give HMRC time to process
                $this->calculationService->getCalculation(
                    $declaration->user_id,
                    $declaration->nino,
                    $declaration->tax_year,
                    $calculationId
                );
            } catch (\Exception $e) {
                Log::warning('Failed to immediately fetch calculation details', [
                    'calculation_id' => $calculationId,
                    'error' => $e->getMessage(),
                ]);
            }

            Log::info('Final declaration submitted successfully', [
                'declaration_id' => $declaration->id,
                'calculation_id' => $calculationId,
            ]);

            return [
                'success' => true,
                'declaration' => $declaration,
                'calculation_id' => $calculationId,
                'message' => 'Final declaration submitted successfully',
            ];

        } catch (HmrcApiException $e) {
            DB::rollBack();

            $declaration->update([
                'status' => 'failed',
                'submission_errors' => [
                    'code' => $e->hmrcCode,
                    'message' => $e->hmrcMessage,
                    'errors' => $e->errors,
                    'timestamp' => now()->toIso8601String(),
                ],
            ]);

            Log::error('Final declaration submission failed', [
                'declaration_id' => $declaration->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;

        } catch (\Exception $e) {
            DB::rollBack();

            $declaration->update([
                'status' => 'failed',
                'submission_errors' => [
                    'message' => $e->getMessage(),
                    'timestamp' => now()->toIso8601String(),
                ],
            ]);

            Log::error('Final declaration submission failed with unexpected error', [
                'declaration_id' => $declaration->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Get summary of submissions for review
     */
    public function getSubmissionsSummary(int $userId, string $taxYear): array
    {
        [$startYear, $endYear] = explode('-', $taxYear);
        $periodStart = Carbon::create($startYear, 4, 6);
        $periodEnd = Carbon::create('20' . $endYear, 4, 5);

        // Get periodic submissions
        $periodicSubmissions = HmrcPeriodicSubmission::where('user_id', $userId)
            ->whereBetween('period_start_date', [$periodStart, $periodEnd])
            ->where('status', 'submitted')
            ->orderBy('period_start_date')
            ->get();

        // Get annual submissions
        $annualSubmissions = HmrcAnnualSubmission::where('user_id', $userId)
            ->where('tax_year', $taxYear)
            ->where('status', 'submitted')
            ->get();

        // Calculate totals
        $totalIncome = $periodicSubmissions->sum('total_income');
        $totalExpenses = $periodicSubmissions->sum('total_expenses');
        $netProfit = $totalIncome - $totalExpenses;

        return [
            'periodic_submissions' => $periodicSubmissions,
            'annual_submissions' => $annualSubmissions,
            'totals' => [
                'income' => $totalIncome,
                'expenses' => $totalExpenses,
                'net_profit' => $netProfit,
            ],
            'period' => [
                'start' => $periodStart,
                'end' => $periodEnd,
            ],
        ];
    }

    protected function getTaxYearStart(string $taxYear): string
    {
        [$startYear] = explode('-', $taxYear);
        return "{$startYear}-04-06";
    }

    protected function getTaxYearEnd(string $taxYear): string
    {
        [, $endYear] = explode('-', $taxYear);
        return "20{$endYear}-04-05";
    }
}

