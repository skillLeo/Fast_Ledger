<?php

namespace App\Http\Controllers\Hmrc;

use App\Http\Controllers\Controller;
use App\Http\Requests\UkPropertyAnnualSubmissionRequest;
use App\Models\HmrcBusiness;
use App\Models\HmrcObligation;
use App\Models\HmrcUkPropertyAnnualSubmission;
use App\Services\Hmrc\HmrcUkPropertyAnnualSubmissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class HmrcUkPropertyAnnualSubmissionController extends Controller
{
    protected HmrcUkPropertyAnnualSubmissionService $submissionService;

    public function __construct(HmrcUkPropertyAnnualSubmissionService $submissionService)
    {
        $this->submissionService = $submissionService;
    }

    /**
     * Display a listing of UK Property annual submissions
     */
    public function index(Request $request)
    {
        $userId = Auth::id();
        $status = $request->input('status');
        $taxYear = $request->input('tax_year');
        $testScenario = $request->input('test_scenario');

        $submissions = HmrcUkPropertyAnnualSubmission::where('user_id', $userId)
            ->with('business')
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($taxYear, fn($q) => $q->where('tax_year', $taxYear))
            ->when($testScenario && config('hmrc.environment') === 'sandbox', fn($q) => $q->where('test_scenario', $testScenario))
            ->orderBy('tax_year', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Calculate stats
        $stats = [
            'total' => HmrcUkPropertyAnnualSubmission::where('user_id', $userId)->count(),
            'draft' => HmrcUkPropertyAnnualSubmission::where('user_id', $userId)->draft()->count(),
            'submitted' => HmrcUkPropertyAnnualSubmission::where('user_id', $userId)->submitted()->count(),
            'failed' => HmrcUkPropertyAnnualSubmission::where('user_id', $userId)->failed()->count(),
        ];

        // Fetch related obligations for UK Property crystallisation submissions
        $obligations = HmrcObligation::where('user_id', $userId)
            ->where('type_of_business', 'uk-property')
            ->crystallisation()
            ->with('business')
            ->orderByRaw("CASE WHEN status = 'open' THEN 0 ELSE 1 END")
            ->orderByRaw("CASE WHEN is_overdue = 1 THEN 0 ELSE 1 END")
            ->orderBy('due_date', 'asc')
            ->get();

        return view('hmrc.uk-property-annual-submissions.index', compact('submissions', 'stats', 'obligations'));
    }

    /**
     * Show the form for creating a new submission
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
            return redirect()->route('hmrc.businesses.index')
                ->with('error', 'You need to have UK Property businesses synced from HMRC first.');
        }

        // Get obligation if specified
        $obligation = null;
        $obligationId = $request->input('obligation_id');

        if ($obligationId) {
            $obligation = \App\Models\HmrcObligation::where('user_id', $userId)
                ->findOrFail($obligationId);
        }

        $taxYear = $request->input('tax_year', $obligation?->tax_year ?? $this->getCurrentTaxYear());

        return view('hmrc.uk-property-annual-submissions.create', compact('businesses', 'taxYear', 'obligation'));
    }

    /**
     * Store a newly created submission
     */
    public function store(UkPropertyAnnualSubmissionRequest $request)
    {
        $userId = Auth::id();

        try {
            $data = $this->prepareSubmissionData($request);

            $submission = $this->submissionService->createDraft(
                $userId,
                $request->input('business_id'),
                $request->input('tax_year'),
                $data
            );

            Log::info('Draft UK Property annual submission created', ['submission_id' => $submission->id]);

            return redirect()->route('hmrc.uk-property-annual-submissions.show', $submission)
                ->with('success', 'Draft annual submission created successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to create UK Property annual submission', [
                'error' => $e->getMessage(),
                'user_id' => $userId
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to create submission: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified submission
     */
    public function show(HmrcUkPropertyAnnualSubmission $submission)
    {
        // Authorization check
        if ($submission->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this submission.');
        }

        $submission->load('business');

        return view('hmrc.uk-property-annual-submissions.show', compact('submission'));
    }

    /**
     * Show the form for editing the specified submission
     */
    public function edit(HmrcUkPropertyAnnualSubmission $submission)
    {
        // Authorization check
        if ($submission->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this submission.');
        }

        // Allow editing drafts/failed OR amending submitted submissions
        if (!$submission->canEdit() && !$submission->canAmend()) {
            return redirect()->route('hmrc.uk-property-annual-submissions.show', $submission)
                ->with('error', 'This submission cannot be edited or amended.');
        }

        $businesses = HmrcBusiness::where('user_id', Auth::id())
            ->ukProperty()
            ->active()
            ->get();

        $isAmendment = $submission->canAmend();

        return view('hmrc.uk-property-annual-submissions.edit', compact('submission', 'businesses', 'isAmendment'));
    }

    /**
     * Update the specified submission
     */
    public function update(UkPropertyAnnualSubmissionRequest $request, HmrcUkPropertyAnnualSubmission $submission)
    {
        // Authorization check
        if ($submission->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this submission.');
        }

        try {
            // Debug: Log incoming request data
            Log::debug('Update request data', [
                'all' => $request->all(),
                'adjustments' => $request->input('adjustments'),
                'allowances' => $request->input('allowances'),
            ]);

            $data = $this->prepareSubmissionData($request);

            // Debug: Log prepared data
            Log::debug('Prepared submission data', $data);

            $submission = $this->submissionService->updateDraft($submission->id, $data);

            Log::info('UK Property annual submission updated', ['submission_id' => $submission->id]);

            return redirect()->route('hmrc.uk-property-annual-submissions.show', $submission)
                ->with('success', 'Submission updated successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to update UK Property annual submission', [
                'error' => $e->getMessage(),
                'submission_id' => $submission->id
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to update submission: ' . $e->getMessage());
        }
    }

    /**
     * Submit the submission to HMRC
     */
    public function submit(HmrcUkPropertyAnnualSubmission $submission)
    {
        // Authorization check
        if ($submission->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this submission.');
        }

        try {
            $response = $this->submissionService->submitToHmrc($submission->id);

            Log::info('UK Property annual submission submitted to HMRC', [
                'submission_id' => $submission->id
            ]);

            return redirect()->route('hmrc.uk-property-annual-submissions.show', $submission)
                ->with('success', 'Submission successfully sent to HMRC.');
        } catch (\Exception $e) {
            Log::error('Failed to submit UK Property annual submission to HMRC', [
                'error' => $e->getMessage(),
                'submission_id' => $submission->id
            ]);

            return redirect()->route('hmrc.uk-property-annual-submissions.show', $submission)
                ->with('error', 'Failed to submit to HMRC: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified submission
     */
    public function destroy(HmrcUkPropertyAnnualSubmission $submission)
    {
        // Authorization check
        if ($submission->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this submission.');
        }

        try {
            // If submitted to HMRC, try to delete from HMRC first
            if ($submission->status === 'submitted') {
                $this->submissionService->deleteFromHmrc($submission->id);
            } else {
                // Just delete locally if it's a draft
                $submission->delete();
            }

            Log::info('UK Property annual submission deleted', ['submission_id' => $submission->id]);

            return redirect()->route('hmrc.uk-property-annual-submissions.index')
                ->with('success', 'Submission deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to delete UK Property annual submission', [
                'error' => $e->getMessage(),
                'submission_id' => $submission->id
            ]);

            return back()->with('error', 'Failed to delete submission: ' . $e->getMessage());
        }
    }

    /**
     * Preview the HMRC API payload before submission
     */
    public function previewPayload(HmrcUkPropertyAnnualSubmission $submission)
    {
        // Authorization check
        if ($submission->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this submission.');
        }

        try {
            $preview = $this->submissionService->getPayloadPreview($submission->id);

            return response()->json([
                'success' => true,
                'preview' => $preview,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to generate payload preview', [
                'error' => $e->getMessage(),
                'submission_id' => $submission->id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate preview: ' . $e->getMessage()
            ], 500);
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

    /**
     * Prepare submission data in the correct nested structure based on tax year
     *
     * Data structure:
     * - TY BEFORE 2024-25: adjustments/allowances => { ukFhlProperty: {...}, ukProperty: {...} }
     * - TY 2024-25: adjustments/allowances => { ukFhlProperty: {...}, ukProperty: {...} }
     * - TY 2025-26+: adjustments/allowances => { ukProperty: {...} }
     */
    protected function prepareSubmissionData(UkPropertyAnnualSubmissionRequest $request): array
    {
        $taxYear = $request->input('tax_year');
        $taxYearNum = (int) substr($taxYear, 0, 4);
        $isTY202425 = $taxYear === '2024-25';
        $isTY202526Plus = $taxYearNum >= 2025;

        $data = [
            'nino' => $request->input('nino'),
            'test_scenario' => $request->input('test_scenario'),
            'notes' => $request->input('notes'),
        ];

        // Get form data
        $fhlAdjustments = $request->input('fhl_adjustments', []);
        $nonFhlAdjustments = $request->input('non_fhl_adjustments', []);
        $adjustments = $request->input('adjustments', []);

        $fhlAllowances = $request->input('fhl_allowances', []);
        $nonFhlAllowances = $request->input('non_fhl_allowances', []);
        $allowances = $request->input('allowances', []);

        if ($isTY202526Plus) {
            // TY 2025-26+: ONLY ukProperty (no FHL)
            $data['adjustments'] = [
                'ukProperty' => !empty($adjustments) ? $adjustments : (!empty($nonFhlAdjustments) ? $nonFhlAdjustments : [])
            ];
            $data['allowances'] = [
                'ukProperty' => !empty($allowances) ? $allowances : (!empty($nonFhlAllowances) ? $nonFhlAllowances : [])
            ];
        } elseif ($isTY202425) {
            // TY 2024-25: Both ukFhlProperty and ukProperty with tabs
            $adjustmentsData = [];
            if (!empty($fhlAdjustments)) {
                $adjustmentsData['ukFhlProperty'] = $fhlAdjustments;
            }
            if (!empty($nonFhlAdjustments)) {
                $adjustmentsData['ukProperty'] = $nonFhlAdjustments;
            }
            $data['adjustments'] = $adjustmentsData;

            $allowancesData = [];
            if (!empty($fhlAllowances)) {
                $allowancesData['ukFhlProperty'] = $fhlAllowances;
            }
            if (!empty($nonFhlAllowances)) {
                $allowancesData['ukProperty'] = $nonFhlAllowances;
            }
            $data['allowances'] = $allowancesData;
        } else {
            // TY BEFORE 2024-25: Both ukFhlProperty and ukProperty
            $adjustmentsData = [];
            if (!empty($fhlAdjustments)) {
                $adjustmentsData['ukFhlProperty'] = $fhlAdjustments;
            }
            if (!empty($nonFhlAdjustments)) {
                $adjustmentsData['ukProperty'] = $nonFhlAdjustments;
            }
            $data['adjustments'] = $adjustmentsData;

            $allowancesData = [];
            if (!empty($fhlAllowances)) {
                $allowancesData['ukFhlProperty'] = $fhlAllowances;
            }
            if (!empty($nonFhlAllowances)) {
                $allowancesData['ukProperty'] = $nonFhlAllowances;
            }
            $data['allowances'] = $allowancesData;
        }

        return $data;
    }
}
