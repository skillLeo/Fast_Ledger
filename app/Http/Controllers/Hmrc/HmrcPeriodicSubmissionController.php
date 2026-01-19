<?php

namespace App\Http\Controllers\Hmrc;

use App\Http\Controllers\Controller;
use App\Http\Requests\PeriodicSubmissionRequest;
use App\Models\HmrcBusiness;
use App\Models\HmrcObligation;
use App\Models\HmrcPeriodicSubmission;
use App\Services\Hmrc\HmrcPeriodicSubmissionService;
use App\Services\ProfitLossReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class HmrcPeriodicSubmissionController extends Controller
{
    protected HmrcPeriodicSubmissionService $submissionService;
    protected ProfitLossReportService $plService;

    public function __construct(
        HmrcPeriodicSubmissionService $submissionService,
        ProfitLossReportService $plService
    ) {
        $this->submissionService = $submissionService;
        $this->plService = $plService;
    }

    /**
     * Display a listing of periodic submissions
     */
    public function index(Request $request)
    {
        $userId = Auth::id();
        $status = $request->input('status');

        $submissions = HmrcPeriodicSubmission::where('user_id', $userId)
            ->with(['business', 'obligation'])
            ->when($status, fn($q) => $q->where('status', $status))
            ->orderBy('period_end_date', 'desc')
            ->paginate(15);

        $stats = $this->submissionService->getSubmissionStats($userId);

        // Fetch related obligations for self-employment periodic submissions
        $obligations = HmrcObligation::where('user_id', $userId)
            ->where('type_of_business', 'self-employment')
            ->periodic()
            ->with('business')
            ->orderByRaw("CASE WHEN status = 'open' THEN 0 ELSE 1 END")
            ->orderByRaw("CASE WHEN is_overdue = 1 THEN 0 ELSE 1 END")
            ->orderBy('due_date', 'asc')
            ->get();

        return view('hmrc.submissions.index', compact('submissions', 'stats', 'obligations'));
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

        // Get obligation if specified
        $obligation = null;
        $obligationId = $request->input('obligation_id');

        if ($obligationId) {
            $obligation = HmrcObligation::where('user_id', $userId)
                ->findOrFail($obligationId);
        }

        // Get business_id from request
        $businessId = $request->input('business_id');

        // Get existing period date ranges for this user and business
        // to prevent duplicate submissions for the same period
        $existingPeriods = HmrcPeriodicSubmission::where('user_id', $userId)
            ->when($businessId, fn($q) => $q->where('business_id', $businessId))
            ->get()
            ->map(function ($submission) {
                return [
                    'start' => $submission->period_start_date->format('Y-m-d'),
                    'end' => $submission->period_end_date->format('Y-m-d'),
                    'obligation_id' => $submission->obligation_id
                ];
            })
            ->toArray();

        return view('hmrc.submissions.create', compact('businesses', 'obligation', 'existingPeriods'));
    }

    /**
     * Store a newly created submission
     */
    public function store(PeriodicSubmissionRequest $request)
    {
        $userId = Auth::id();

        try {
            $submission = $this->submissionService->createDraft(
                $userId,
                $request->input('business_id'),
                $request->input('obligation_id'),
                $request->validated()
            );

            Log::info('Draft submission created', ['submission_id' => $submission->id]);

            return redirect()->route('hmrc.submissions.show', $submission)
                ->with('success', 'Draft submission created successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to create submission', [
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
    public function show(HmrcPeriodicSubmission $submission)
    {
        // Authorization check
        if ($submission->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this submission.');
        }

        $submission->load(['business', 'obligation']);

        return view('hmrc.submissions.show', compact('submission'));
    }

    /**
     * Show the form for editing the specified submission
     */
    public function edit(HmrcPeriodicSubmission $submission)
    {
        // Authorization check
        if ($submission->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this submission.');
        }

        if (!$submission->canEdit()) {
            return redirect()->route('hmrc.submissions.show', $submission)
                ->with('error', 'This submission cannot be edited.');
        }

        $businesses = HmrcBusiness::where('user_id', Auth::id())
            ->where('is_active', true)
            ->get();

        return view('hmrc.submissions.edit', compact('submission', 'businesses'));
    }

    /**
     * Update the specified submission
     */
    public function update(PeriodicSubmissionRequest $request, HmrcPeriodicSubmission $submission)
    {
        // Authorization check
        if ($submission->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this submission.');
        }

        try {
            $this->submissionService->updateDraft($submission->id, $request->validated());

            return redirect()->route('hmrc.submissions.show', $submission)
                ->with('success', 'Submission updated successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to update submission', [
                'submission_id' => $submission->id,
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
    public function submit(HmrcPeriodicSubmission $submission)
    {
        // Authorization check
        if ($submission->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this submission.');
        }

        if (!$submission->canSubmit()) {
            return redirect()->route('hmrc.submissions.show', $submission)
                ->with('error', 'This submission cannot be submitted.');
        }

        try {
            $response = $this->submissionService->submitToHmrc($submission->id);

            Log::info('Submission sent to HMRC', [
                'submission_id' => $submission->id,
                'period_id' => $response['periodId'] ?? null
            ]);

            return redirect()->route('hmrc.submissions.show', $submission)
                ->with('success', 'Submission sent to HMRC successfully! Period ID: ' . ($response['periodId'] ?? 'N/A'));
        } catch (\Exception $e) {
            Log::error('Failed to submit to HMRC', [
                'submission_id' => $submission->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('hmrc.submissions.show', $submission)
                ->with('error', 'Failed to submit to HMRC: ' . $e->getMessage());
        }
    }

    /**
     * Delete the specified submission
     */
    public function destroy(HmrcPeriodicSubmission $submission)
    {
        // Authorization check
        if ($submission->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this submission.');
        }

        try {
            $this->submissionService->deleteDraft($submission->id);

            return redirect()->route('hmrc.submissions.index')
                ->with('success', 'Draft submission deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->route('hmrc.submissions.show', $submission)
                ->with('error', 'Failed to delete submission: ' . $e->getMessage());
        }
    }

    /**
     * Get P&L data for a specific period (AJAX)
     */
    public function getProfitLossData(Request $request)
    {
        $userId = Auth::id();

        $request->validate([
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date'
        ]);

        try {
            $fromDate = Carbon::parse($request->input('from_date'));
            $toDate = Carbon::parse($request->input('to_date'));

            $plData = $this->plService->getProfitLossData($userId, $fromDate, $toDate);
            $suggestions = $this->plService->getSuggestedHmrcValues($plData);

            return response()->json([
                'success' => true,
                'data' => $plData,
                'suggestions' => $suggestions
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch P&L data', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch P&L data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export submissions
     */
    public function export(Request $request)
    {
        $userId = Auth::id();

        $submissions = HmrcPeriodicSubmission::where('user_id', $userId)
            ->with(['business', 'obligation'])
            ->get();

        // Return CSV export
        $filename = 'hmrc-submissions-' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($submissions) {
            $file = fopen('php://output', 'w');

            // CSV headers
            fputcsv($file, [
                'Period Start',
                'Period End',
                'Business',
                'Tax Year',
                'Total Income',
                'Total Expenses',
                'Net Profit',
                'Status',
                'Submission Date',
                'Period ID'
            ]);

            foreach ($submissions as $submission) {
                fputcsv($file, [
                    $submission->period_start_date->format('Y-m-d'),
                    $submission->period_end_date->format('Y-m-d'),
                    $submission->business?->trading_name ?? $submission->business_id,
                    $submission->tax_year,
                    number_format($submission->total_income, 2),
                    number_format($submission->total_expenses, 2),
                    number_format($submission->net_profit, 2),
                    $submission->status,
                    $submission->submission_date?->format('Y-m-d H:i:s') ?? '',
                    $submission->period_id ?? ''
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
