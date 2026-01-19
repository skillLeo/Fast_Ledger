<?php

namespace App\Http\Controllers\Hmrc;

use App\Http\Controllers\Controller;
use App\Http\Requests\AnnualSubmissionRequest;
use App\Models\HmrcBusiness;
use App\Models\HmrcAnnualSubmission;
use App\Models\HmrcObligation;
use App\Services\Hmrc\HmrcAnnualSubmissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class HmrcAnnualSubmissionController extends Controller
{
    protected HmrcAnnualSubmissionService $submissionService;

    public function __construct(HmrcAnnualSubmissionService $submissionService)
    {
        $this->submissionService = $submissionService;
    }

    /**
     * Display a listing of annual submissions
     */
    public function index(Request $request)
    {
        $userId = Auth::id();
        $status = $request->input('status');

        $submissions = HmrcAnnualSubmission::where('user_id', $userId)
            ->with('business')
            ->when($status, fn($q) => $q->where('status', $status))
            ->orderBy('tax_year', 'desc')
            ->paginate(15);

        $stats = $this->submissionService->getSubmissionStats($userId);

        // Fetch related obligations for self-employment crystallisation submissions
        $obligations = HmrcObligation::where('user_id', $userId)
            ->where('type_of_business', 'self-employment')
            ->crystallisation()
            ->with('business')
            ->orderByRaw("CASE WHEN status = 'open' THEN 0 ELSE 1 END")
            ->orderByRaw("CASE WHEN is_overdue = 1 THEN 0 ELSE 1 END")
            ->orderBy('due_date', 'asc')
            ->get();

        return view('hmrc.annual-submissions.index', compact('submissions', 'stats', 'obligations'));
    }

    /**
     * Show the form for creating a new submission
     */
    public function create(Request $request)
    {
        $userId = Auth::id();

        // Get businesses
        $businesses = HmrcBusiness::where('user_id', $userId)
            ->where('is_active', true)
            ->get();

        if ($businesses->isEmpty()) {
            return redirect()->route('hmrc.businesses.index')
                ->with('error', 'You need to sync your businesses from HMRC first.');
        }

        // Get business_id from request
        $businessId = $request->input('business_id');

        // Get existing tax years for this user and business
        $existingTaxYears = HmrcAnnualSubmission::where('user_id', $userId)
            ->when($businessId, fn($q) => $q->where('business_id', $businessId))
            ->pluck('tax_year')
            ->unique()
            ->toArray();

        // Get tax year from request or default to current
        $taxYear = $request->input('tax_year', $this->getCurrentTaxYear());

        // Get quarterly summary if business_id provided
        $quarterlySummary = null;
        if ($businessId) {
            $quarterlySummary = $this->submissionService->getQuarterlySummary($userId, $businessId, $taxYear);
        }

        return view('hmrc.annual-submissions.create', compact('businesses', 'taxYear', 'quarterlySummary', 'existingTaxYears'));
    }

    /**
     * Store a newly created submission
     */
    public function store(AnnualSubmissionRequest $request)
    {
        $userId = Auth::id();

        try {
            $submission = $this->submissionService->createDraft(
                $userId,
                $request->input('business_id'),
                $request->input('tax_year'),
                $request->validated()
            );

            Log::info('Draft annual submission created', ['submission_id' => $submission->id]);

            return redirect()->route('hmrc.annual-submissions.show', $submission)
                ->with('success', 'Draft annual submission created successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to create annual submission', [
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
    public function show(HmrcAnnualSubmission $annualSubmission)
    {
        // Authorization check
        if ($annualSubmission->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this submission.');
        }

        $annualSubmission->load('business');

        // Get quarterly summary
        $quarterlySummary = $this->submissionService->getQuarterlySummary(
            Auth::id(),
            $annualSubmission->business_id,
            $annualSubmission->tax_year
        );

        return view('hmrc.annual-submissions.show', compact('annualSubmission', 'quarterlySummary'));
    }

    /**
     * Show the form for editing the specified submission
     */
    public function edit(HmrcAnnualSubmission $annualSubmission)
    {
        // Authorization check
        if ($annualSubmission->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this submission.');
        }

        if (!$annualSubmission->canEdit()) {
            return redirect()->route('hmrc.annual-submissions.show', $annualSubmission)
                ->with('error', 'This submission cannot be edited.');
        }

        // Get active businesses for user
        $businesses = HmrcBusiness::where('user_id', Auth::id())
            ->where('is_active', true)
            ->get();

        // Get quarterly summary
        $quarterlySummary = $this->submissionService->getQuarterlySummary(
            Auth::id(),
            $annualSubmission->business_id,
            $annualSubmission->tax_year
        );

        return view('hmrc.annual-submissions.edit', compact('annualSubmission', 'businesses', 'quarterlySummary'));
    }

    /**
     * Update the specified submission
     */
    public function update(AnnualSubmissionRequest $request, HmrcAnnualSubmission $annualSubmission)
    {
        // Authorization check
        if ($annualSubmission->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this submission.');
        }

        try {
            $this->submissionService->updateDraft($annualSubmission->id, $request->validated());

            return redirect()->route('hmrc.annual-submissions.show', $annualSubmission)
                ->with('success', 'Annual submission updated successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to update annual submission', [
                'submission_id' => $annualSubmission->id,
                'error' => $e->getMessage()
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to update submission: ' . $e->getMessage());
        }
    }

    /**
     * Submit to HMRC
     */
    public function submit(HmrcAnnualSubmission $annualSubmission)
    {
        // Authorization check
        if ($annualSubmission->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this submission.');
        }

        if (!$annualSubmission->canSubmit()) {
            return redirect()->route('hmrc.annual-submissions.show', $annualSubmission)
                ->with('error', 'This submission cannot be submitted.');
        }

        try {
            $response = $this->submissionService->submitToHmrc($annualSubmission->id);

            Log::info('Annual submission sent to HMRC', [
                'submission_id' => $annualSubmission->id
            ]);

            return redirect()->route('hmrc.annual-submissions.show', $annualSubmission)
                ->with('success', 'Annual submission sent to HMRC successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to submit annual submission to HMRC', [
                'submission_id' => $annualSubmission->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('hmrc.annual-submissions.show', $annualSubmission)
                ->with('error', 'Failed to submit to HMRC: ' . $e->getMessage());
        }
    }

    /**
     * Delete the specified submission
     */
    public function destroy(HmrcAnnualSubmission $annualSubmission)
    {
        // Authorization check
        if ($annualSubmission->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this submission.');
        }

        try {
            $this->submissionService->deleteDraft($annualSubmission->id);

            return redirect()->route('hmrc.annual-submissions.index')
                ->with('success', 'Draft annual submission deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->route('hmrc.annual-submissions.show', $annualSubmission)
                ->with('error', 'Failed to delete submission: ' . $e->getMessage());
        }
    }

    /**
     * Get quarterly summary (AJAX)
     */
    public function getQuarterlySummary(Request $request)
    {
        $userId = Auth::id();

        $request->validate([
            'business_id' => 'required|string',
            'tax_year' => 'required|string|regex:/^\d{4}-\d{2}$/'
        ]);

        try {
            $summary = $this->submissionService->getQuarterlySummary(
                $userId,
                $request->input('business_id'),
                $request->input('tax_year')
            );

            return response()->json([
                'success' => true,
                'data' => $summary
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch quarterly summary', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch quarterly summary: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export submissions
     */
    public function export(Request $request)
    {
        $userId = Auth::id();

        $submissions = HmrcAnnualSubmission::where('user_id', $userId)
            ->with('business')
            ->get();

        // Return CSV export
        $filename = 'hmrc-annual-submissions-' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($submissions) {
            $file = fopen('php://output', 'w');

            // CSV headers
            fputcsv($file, [
                'Tax Year',
                'Business',
                'Total Allowances',
                'Income Adjustments',
                'Expense Adjustments',
                'Status',
                'Submission Date'
            ]);

            foreach ($submissions as $submission) {
                fputcsv($file, [
                    $submission->tax_year,
                    $submission->business?->trading_name ?? $submission->business_id,
                    number_format($submission->total_allowances, 2),
                    number_format($submission->net_income_adjustment, 2),
                    number_format($submission->net_expense_adjustment, 2),
                    $submission->status,
                    $submission->submission_date?->format('Y-m-d H:i:s') ?? ''
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get current tax year
     */
    protected function getCurrentTaxYear(): string
    {
        $now = now();
        $year = $now->year;
        $month = $now->month;

        if ($month >= 4) {
            return $year . '-' . substr($year + 1, 2);
        } else {
            return ($year - 1) . '-' . substr($year, 2);
        }
    }
}
