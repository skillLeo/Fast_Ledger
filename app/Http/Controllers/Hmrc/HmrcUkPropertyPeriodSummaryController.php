<?php

namespace App\Http\Controllers\Hmrc;

use App\Http\Controllers\Controller;
use App\Http\Requests\UkPropertyPeriodSummaryRequest;
use App\Models\HmrcBusiness;
use App\Models\HmrcObligation;
use App\Models\HmrcUkPropertyPeriodSummary;
use App\Services\Hmrc\HmrcUkPropertyPeriodSummaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class HmrcUkPropertyPeriodSummaryController extends Controller
{
    protected HmrcUkPropertyPeriodSummaryService $summaryService;

    public function __construct(HmrcUkPropertyPeriodSummaryService $summaryService)
    {
        $this->summaryService = $summaryService;
    }

    /**
     * Display a listing of UK Property period summaries
     */
    public function index(Request $request)
    {
        $userId = Auth::id();
        $status = $request->input('status');
        $taxYear = $request->input('tax_year');
        $businessId = $request->input('business_id');
        $testScenario = $request->input('test_scenario');

        $summaries = HmrcUkPropertyPeriodSummary::where('user_id', $userId)
            ->with('business')
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($taxYear, fn($q) => $q->where('tax_year', $taxYear))
            ->when($businessId, fn($q) => $q->where('business_id', $businessId))
            ->when($testScenario && config('hmrc.environment') === 'sandbox', fn($q) => $q->where('test_scenario', $testScenario))
            ->orderBy('tax_year', 'desc')
            ->orderBy('from_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Calculate stats
        $stats = [
            'total' => HmrcUkPropertyPeriodSummary::where('user_id', $userId)->count(),
            'draft' => HmrcUkPropertyPeriodSummary::where('user_id', $userId)->draft()->count(),
            'submitted' => HmrcUkPropertyPeriodSummary::where('user_id', $userId)->submitted()->count(),
            'failed' => HmrcUkPropertyPeriodSummary::where('user_id', $userId)->failed()->count(),
        ];

        // Get businesses for filter
        $businesses = HmrcBusiness::where('user_id', $userId)
            ->ukProperty()
            ->active()
            ->get();

        // Fetch related obligations for UK Property periodic submissions
        $obligations = HmrcObligation::where('user_id', $userId)
            ->where('type_of_business', 'uk-property')
            ->periodic()
            ->with('business')
            ->orderByRaw("CASE WHEN status = 'open' THEN 0 ELSE 1 END")
            ->orderByRaw("CASE WHEN is_overdue = 1 THEN 0 ELSE 1 END")
            ->orderBy('due_date', 'asc')
            ->get();

        return view('hmrc.uk-property-period-summaries.index', compact('summaries', 'stats', 'businesses', 'obligations'));
    }

    /**
     * Show the form for creating a new period summary
     */
    public function create(Request $request)
    {
        $userId = Auth::id();

        // Get UK Property businesses only
        $businesses = HmrcBusiness::where('user_id', $userId)
            ->ukProperty()
            ->active()
            ->get();

        if ($businesses->isEmpty()) {
            return redirect()
                ->route('hmrc.businesses.index')
                ->with('error', 'You have no active UK property business.');
        }

        // Get obligation if specified
        $obligation = null;
        $obligationId = $request->input('obligation_id');

        if ($obligationId) {
            $obligation = \App\Models\HmrcObligation::where('user_id', $userId)
                ->findOrFail($obligationId);
        }

        $taxYear = $request->input('tax_year', $obligation?->tax_year ?? $this->getCurrentTaxYear());
        $businessId = $request->input('business_id', $obligation?->business_id);

        // Get existing periods if business is selected
        $existingPeriods = null;
        if ($businessId) {
            $existingPeriods = HmrcUkPropertyPeriodSummary::where('business_id', $businessId)
                ->where('tax_year', $taxYear)
                ->orderBy('from_date')
                ->get();
        }

        return view('hmrc.uk-property-period-summaries.create', compact('businesses', 'taxYear', 'existingPeriods', 'obligation'));
    }

    /**
     * Store a newly created period summary
     */
    public function store(UkPropertyPeriodSummaryRequest $request)
    {
        $userId = Auth::id();

        try {
            $summary = $this->summaryService->createDraft(
                $userId,
                $request->input('business_id'),
                $request->input('tax_year'),
                $request->validated()
            );

            Log::info('Draft UK Property period summary created', ['summary_id' => $summary->id]);

            return redirect()->route('hmrc.uk-property-period-summaries.show', $summary)
                ->with('success', 'Draft period summary created successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to create UK Property period summary', [
                'error' => $e->getMessage(),
                'user_id' => $userId
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to create period summary: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified period summary
     */
    public function show(HmrcUkPropertyPeriodSummary $summary)
    {
        // Authorization check
        if ($summary->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this period summary.');
        }

        $summary->load('business');

        return view('hmrc.uk-property-period-summaries.show', compact('summary'));
    }

    /**
     * Show the form for editing the specified period summary
     */
    public function edit(HmrcUkPropertyPeriodSummary $summary)
    {
        // Authorization check
        if ($summary->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this period summary.');
        }

        if (!$summary->canEdit()) {
            return redirect()->route('hmrc.uk-property-period-summaries.show', $summary)
                ->with('error', 'This period summary cannot be edited.');
        }

        $businesses = HmrcBusiness::where('user_id', Auth::id())
            ->ukProperty()
            ->active()
            ->get();

        // Get existing periods for overlap detection
        $existingPeriods = HmrcUkPropertyPeriodSummary::where('business_id', $summary->business_id)
            ->where('tax_year', $summary->tax_year)
            ->where('id', '!=', $summary->id)
            ->orderBy('from_date')
            ->get();

        return view('hmrc.uk-property-period-summaries.edit', compact('summary', 'businesses', 'existingPeriods'));
    }

    /**
     * Update the specified period summary
     */
    public function update(UkPropertyPeriodSummaryRequest $request, HmrcUkPropertyPeriodSummary $summary)
    {
        // Authorization check
        if ($summary->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this period summary.');
        }

        try {
            $summary = $this->summaryService->updateDraft($summary->id, $request->validated());

            Log::info('UK Property period summary updated', ['summary_id' => $summary->id]);

            return redirect()->route('hmrc.uk-property-period-summaries.show', $summary)
                ->with('success', 'Period summary updated successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to update UK Property period summary', [
                'error' => $e->getMessage(),
                'summary_id' => $summary->id
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to update period summary: ' . $e->getMessage());
        }
    }

    /**
     * Submit the period summary to HMRC
     */
    public function submit(HmrcUkPropertyPeriodSummary $summary)
    {
        // Authorization check
        if ($summary->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this period summary.');
        }

        try {
            $response = $this->summaryService->submitToHmrc($summary->id);

            Log::info('UK Property period summary submitted to HMRC', [
                'summary_id' => $summary->id
            ]);

            return redirect()->route('hmrc.uk-property-period-summaries.show', $summary)
                ->with('success', 'Period summary successfully sent to HMRC.');
        } catch (\Exception $e) {
            Log::error('Failed to submit UK Property period summary to HMRC', [
                'error' => $e->getMessage(),
                'summary_id' => $summary->id
            ]);

            return redirect()->route('hmrc.uk-property-period-summaries.show', $summary)
                ->with('error', 'Failed to submit to HMRC: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified period summary
     */
    public function destroy(HmrcUkPropertyPeriodSummary $summary)
    {
        // Authorization check
        if ($summary->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this period summary.');
        }

        try {
            // Period summaries can only be deleted if they're drafts
            // HMRC doesn't support deleting submitted period summaries
            if ($summary->status !== 'draft') {
                return back()->with('error', 'Only draft period summaries can be deleted. Submitted summaries must be amended instead.');
            }

            $summary->delete();

            Log::info('UK Property period summary deleted', ['summary_id' => $summary->id]);

            return redirect()->route('hmrc.uk-property-period-summaries.index')
                ->with('success', 'Period summary deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to delete UK Property period summary', [
                'error' => $e->getMessage(),
                'summary_id' => $summary->id
            ]);

            return back()->with('error', 'Failed to delete period summary: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for amending a submitted period summary
     */
    public function amend(HmrcUkPropertyPeriodSummary $summary)
    {
        // Authorization check
        if ($summary->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this period summary.');
        }

        if (!$summary->canAmend()) {
            return redirect()->route('hmrc.uk-property-period-summaries.show', $summary)
                ->with('error', 'This period summary cannot be amended. Only submitted summaries can be amended.');
        }

        $businesses = HmrcBusiness::where('user_id', Auth::id())
            ->ukProperty()
            ->active()
            ->get();

        return view('hmrc.uk-property-period-summaries.amend', compact('summary', 'businesses'));
    }

    /**
     * Submit the amendment to HMRC
     */
    public function amendSubmit(UkPropertyPeriodSummaryRequest $request, HmrcUkPropertyPeriodSummary $summary)
    {
        // Authorization check
        if ($summary->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this period summary.');
        }

        if (!$summary->canAmend()) {
            return redirect()->route('hmrc.uk-property-period-summaries.show', $summary)
                ->with('error', 'This period summary cannot be amended.');
        }

        try {
            $summary = $this->summaryService->amendToHmrc($summary->id, $request->validated());

            Log::info('UK Property period summary amended successfully', ['summary_id' => $summary->id]);

            return redirect()->route('hmrc.uk-property-period-summaries.show', $summary)
                ->with('success', 'Period summary amended successfully at HMRC.');
        } catch (\Exception $e) {
            Log::error('Failed to amend UK Property period summary', [
                'error' => $e->getMessage(),
                'summary_id' => $summary->id
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to amend period summary: ' . $e->getMessage());
        }
    }

    /**
     * Get current tax year in YYYY-YY format
     */
    protected function getCurrentTaxYear(): string
    {
        $now = now();
        $taxYearStart = $now->month >= 4 ? $now->year : $now->year - 1;
        $taxYearEnd = substr($taxYearStart + 1, 2, 2);
        return "{$taxYearStart}-{$taxYearEnd}";
    }
}
