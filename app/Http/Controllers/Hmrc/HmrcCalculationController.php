<?php

namespace App\Http\Controllers\Hmrc;

use App\Http\Controllers\Controller;
use App\Models\HmrcBusiness;
use App\Models\HmrcCalculation;
use App\Services\Hmrc\HmrcCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class HmrcCalculationController extends Controller
{
    public function __construct(protected HmrcCalculationService $calculationService) {}

    /**
     * Display a listing of calculations
     */
    public function index(Request $request)
    {
        $userId = Auth::id();
        $taxYear = $request->input('tax_year');
        $status = $request->input('status');

        $query = HmrcCalculation::where('user_id', $userId)
            ->orderBy('calculation_timestamp', 'desc')
            ->orderBy('created_at', 'desc');

        if ($taxYear) {
            $query->forTaxYear($taxYear);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $calculations = $query->paginate(15);
        $stats = $this->calculationService->getCalculationStats($userId);

        // Get unique tax years for filter
        $taxYears = HmrcCalculation::where('user_id', $userId)
            ->select('tax_year')
            ->distinct()
            ->orderBy('tax_year', 'desc')
            ->pluck('tax_year');

        // Get user's businesses for sync modal
        $businesses = HmrcBusiness::where('user_id', $userId)
            ->where('is_active', true)
            ->get();

        return view('hmrc.calculations.index', compact('calculations', 'stats', 'taxYears', 'businesses'));
    }

    /**
     * Show the form for creating a new calculation
     */
    public function create(Request $request)
    {
        $userId = Auth::id();

        // Get user's businesses
        $businesses = HmrcBusiness::where('user_id', $userId)
            ->where('is_active', true)
            ->get();

        if ($businesses->isEmpty()) {
            return redirect()->route('hmrc.businesses.index')
                ->with('error', 'You need to sync your businesses from HMRC first.');
        }

        // Get default NINO from first business
        $defaultNino = $businesses->first()->nino ?? null;

        // Tax year options (current and previous 4 years)
        $currentYear = now()->year;
        $currentMonth = now()->month;

        // UK tax year runs from April 6 to April 5
        $taxYearStart = $currentMonth >= 4 ? $currentYear : $currentYear - 1;

        $taxYears = [];
        for ($i = 0; $i < 5; $i++) {
            $year = $taxYearStart - $i;
            $taxYears[] = "{$year}-" . substr($year + 1, 2, 2);
        }

        return view('hmrc.calculations.create', compact('businesses', 'defaultNino', 'taxYears'));
    }

    /**
     * Store a newly created calculation (trigger calculation)
     */
    public function store(Request $request)
    {
        $userId = Auth::id();

        $request->validate([
            'nino' => 'required|string|regex:/^[A-Z]{2}[0-9]{6}[A-Z]$/',
            'tax_year' => 'required|string|regex:/^\d{4}-\d{2}$/',
            'crystallise' => 'nullable|boolean',
            'final_declaration' => 'nullable|boolean',
        ]);

        try {
            $calculation = $this->calculationService->triggerCalculation(
                $userId,
                $request->input('nino'),
                $request->input('tax_year'),
                $request->boolean('crystallise'),
                $request->input('final_declaration') ? 'true' : null
            );

            Log::info('Calculation triggered via UI', [
                'calculation_id' => $calculation->calculation_id,
                'user_id' => $userId,
            ]);

            return redirect()->route('hmrc.calculations.show', $calculation)
                ->with('success', 'Tax calculation has been triggered. Please wait while HMRC processes it.');
        } catch (\Exception $e) {
            Log::error('Failed to trigger calculation', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to trigger calculation: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified calculation
     */
    public function show(HmrcCalculation $calculation)
    {
        // Authorization check
        if ($calculation->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this calculation.');
        }

        // Get detailed breakdown
        $breakdown = $this->calculationService->getCalculationBreakdown($calculation->id);

        return view('hmrc.calculations.show', compact('calculation', 'breakdown'));
    }

    /**
     * Refresh calculation details from HMRC
     */
    public function refresh(HmrcCalculation $calculation)
    {
        // Authorization check
        if ($calculation->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this calculation.');
        }

        try {
            $this->calculationService->getCalculation(
                $calculation->user_id,
                $calculation->nino,
                $calculation->tax_year,
                $calculation->calculation_id
            );

            return redirect()->route('hmrc.calculations.show', $calculation)
                ->with('success', 'Calculation details refreshed successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to refresh calculation', [
                'calculation_id' => $calculation->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('hmrc.calculations.show', $calculation)
                ->with('error', 'Failed to refresh calculation: ' . $e->getMessage());
        }
    }

    /**
     * Sync calculations from HMRC for a specific tax year
     */
    public function sync(Request $request)
    {
        $userId = Auth::id();

        $request->validate([
            'nino' => 'required|string|regex:/^[A-Z]{2}[0-9]{6}[A-Z]$/',
            'tax_year' => 'required|string|regex:/^\d{4}-\d{2}$/',
        ]);

        try {
            $synced = $this->calculationService->syncCalculationsFromHmrc(
                $userId,
                $request->input('nino'),
                $request->input('tax_year')
            );

            return redirect()->route('hmrc.calculations.index')
                ->with('success', "Successfully synced {$synced} calculation(s) from HMRC.");
        } catch (\Exception $e) {
            Log::error('Failed to sync calculations', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
            ]);

            return back()
                ->with('error', 'Failed to sync calculations: ' . $e->getMessage());
        }
    }

    /**
     * Delete the specified calculation
     */
    public function destroy(HmrcCalculation $calculation)
    {
        // Authorization check
        if ($calculation->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this calculation.');
        }

        // Only allow deletion of failed or processing calculations
        if ($calculation->status === 'completed' && $calculation->isCrystallisation()) {
            return redirect()->route('hmrc.calculations.show', $calculation)
                ->with('error', 'Cannot delete a completed final declaration calculation.');
        }

        try {
            $calculation->delete();

            return redirect()->route('hmrc.calculations.index')
                ->with('success', 'Calculation deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->route('hmrc.calculations.show', $calculation)
                ->with('error', 'Failed to delete calculation: ' . $e->getMessage());
        }
    }

    /**
     * Export calculations to CSV
     */
    public function export(Request $request)
    {
        $userId = Auth::id();

        $calculations = HmrcCalculation::where('user_id', $userId)
            ->orderBy('calculation_timestamp', 'desc')
            ->get();

        $filename = 'hmrc-calculations-' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($calculations) {
            $file = fopen('php://output', 'w');

            // CSV headers
            fputcsv($file, [
                'Calculation ID',
                'NINO',
                'Tax Year',
                'Type',
                'Calculation Date',
                'Total Income Received',
                'Total Taxable Income',
                'Income Tax & NICs Due',
                'Status',
            ]);

            foreach ($calculations as $calculation) {
                fputcsv($file, [
                    $calculation->calculation_id,
                    $calculation->nino,
                    $calculation->tax_year,
                    $calculation->type_label,
                    $calculation->calculation_timestamp?->format('Y-m-d H:i:s') ?? '',
                    number_format($calculation->total_income_received ?? 0, 2),
                    number_format($calculation->total_taxable_income ?? 0, 2),
                    number_format($calculation->income_tax_and_nics_due ?? 0, 2),
                    $calculation->status,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
