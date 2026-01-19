<?php

namespace App\Services\Hmrc;

use App\Models\HmrcBusiness;
use App\Models\HmrcObligation;
use App\Models\HmrcPeriodicSubmission;
use App\Models\HmrcAnnualSubmission;
use App\Models\HmrcCalculation;
use Carbon\Carbon;

class HmrcFinalDeclarationPrerequisitesService
{
    public function validatePrerequisites(int $userId, string $taxYear): array
    {
        $checks = [];
        $passed = true;

        // 1. Check quarterly obligations
        $quarterlyCheck = $this->checkQuarterlyObligations($userId, $taxYear);
        $checks['quarterly_obligations'] = $quarterlyCheck;
        if (!$quarterlyCheck['passed']) {
            $passed = false;
        }

        // 2. Check annual submissions
        $annualCheck = $this->checkAnnualSubmissions($userId, $taxYear);
        $checks['annual_submissions'] = $annualCheck;
        if (!$annualCheck['passed']) {
            $passed = false;
        }

        // 3. Check crystallisation obligations
        $crystallisationCheck = $this->checkCrystallisationObligation($userId, $taxYear);
        $checks['crystallisation_obligation'] = $crystallisationCheck;
        if (!$crystallisationCheck['passed']) {
            $passed = false;
        }

        // 4. Check tax calculation exists
        $calculationCheck = $this->checkTaxCalculation($userId, $taxYear);
        $checks['tax_calculation'] = $calculationCheck;
        if (!$calculationCheck['passed']) {
            $passed = false;
        }

        // 5. Check for existing crystallisation
        $existingCheck = $this->checkExistingCrystallisation($userId, $taxYear);
        $checks['existing_crystallisation'] = $existingCheck;
        if (!$existingCheck['passed']) {
            $passed = false;
        }

        return [
            'passed' => $passed,
            'checks' => $checks,
            'checked_at' => now()->toIso8601String(),
        ];
    }

    protected function checkQuarterlyObligations(int $userId, string $taxYear): array
    {
        // Parse tax year (e.g., "2025-26" -> start: 2025-04-06, end: 2026-04-05)
        [$startYear, $endYear] = explode('-', $taxYear);
        $periodStart = Carbon::create($startYear, 4, 6);
        $periodEnd = Carbon::create('20' . $endYear, 4, 5);

        $openObligations = HmrcObligation::where('user_id', $userId)
            ->where('obligation_type', 'periodic')
            ->where('status', 'open')
            ->whereBetween('period_end_date', [$periodStart, $periodEnd])
            ->get();

        $totalObligations = HmrcObligation::where('user_id', $userId)
            ->where('obligation_type', 'periodic')
            ->whereBetween('period_end_date', [$periodStart, $periodEnd])
            ->count();

        $fulfilledObligations = HmrcObligation::where('user_id', $userId)
            ->where('obligation_type', 'periodic')
            ->where('status', 'fulfilled')
            ->whereBetween('period_end_date', [$periodStart, $periodEnd])
            ->count();

        return [
            'passed' => $openObligations->isEmpty(),
            'message' => $openObligations->isEmpty() 
                ? 'All quarterly obligations fulfilled' 
                : "You have {$openObligations->count()} open quarterly obligations",
            'total' => $totalObligations,
            'fulfilled' => $fulfilledObligations,
            'open' => $openObligations->count(),
            'open_obligations' => $openObligations->map(fn($o) => [
                'business_id' => $o->business_id,
                'period' => "{$o->period_start_date->format('Y-m-d')} to {$o->period_end_date->format('Y-m-d')}",
                'due_date' => $o->due_date->format('Y-m-d'),
            ])->toArray(),
        ];
    }

    protected function checkAnnualSubmissions(int $userId, string $taxYear): array
    {
        $businesses = HmrcBusiness::where('user_id', $userId)
            ->where('type_of_business', 'self-employment')
            ->get();

        $missingAnnual = [];

        foreach ($businesses as $business) {
            $annualSubmission = HmrcAnnualSubmission::where('user_id', $userId)
                ->where('business_id', $business->business_id)
                ->where('tax_year', $taxYear)
                ->where('status', 'submitted')
                ->first();

            if (!$annualSubmission) {
                $missingAnnual[] = [
                    'business_id' => $business->business_id,
                    'trading_name' => $business->trading_name,
                ];
            }
        }

        return [
            'passed' => empty($missingAnnual),
            'message' => empty($missingAnnual)
                ? 'All annual submissions completed'
                : 'Some businesses are missing annual submissions',
            'total_businesses' => $businesses->count(),
            'missing_annual' => $missingAnnual,
        ];
    }

    protected function checkCrystallisationObligation(int $userId, string $taxYear): array
    {
        [$startYear, $endYear] = explode('-', $taxYear);
        $periodStart = Carbon::create($startYear, 4, 6);
        $periodEnd = Carbon::create('20' . $endYear, 4, 5);

        $crystallisationObligation = HmrcObligation::where('user_id', $userId)
            ->where('obligation_type', 'crystallisation')
            ->whereBetween('period_start_date', [$periodStart, $periodEnd])
            ->first();

        if (!$crystallisationObligation) {
            return [
                'passed' => true,
                'message' => 'No crystallisation obligation found for this tax year',
            ];
        }

        return [
            'passed' => $crystallisationObligation->status === 'open',
            'message' => $crystallisationObligation->status === 'open'
                ? 'Crystallisation obligation is open and ready'
                : 'Crystallisation obligation has already been fulfilled',
            'obligation' => [
                'due_date' => $crystallisationObligation->due_date->format('Y-m-d'),
                'status' => $crystallisationObligation->status,
            ],
        ];
    }

    protected function checkTaxCalculation(int $userId, string $taxYear): array
    {
        $calculation = HmrcCalculation::where('user_id', $userId)
            ->where('tax_year', $taxYear)
            ->where('status', 'completed')
            ->latest('calculation_timestamp')
            ->first();

        return [
            'passed' => !is_null($calculation),
            'message' => $calculation 
                ? 'Tax calculation available' 
                : 'No tax calculation found. Please trigger a calculation first.',
            'calculation' => $calculation ? [
                'calculation_id' => $calculation->calculation_id,
                'timestamp' => $calculation->calculation_timestamp,
                'total_tax_due' => $calculation->income_tax_and_nics_due,
            ] : null,
        ];
    }

    protected function checkExistingCrystallisation(int $userId, string $taxYear): array
    {
        $existing = HmrcCalculation::where('user_id', $userId)
            ->where('tax_year', $taxYear)
            ->where('is_crystallised', true)
            ->first();

        return [
            'passed' => is_null($existing),
            'message' => $existing
                ? 'Final declaration already submitted for this tax year'
                : 'No previous final declaration found',
            'existing' => $existing ? [
                'crystallised_at' => $existing->crystallised_at,
                'calculation_id' => $existing->calculation_id,
            ] : null,
        ];
    }
}

