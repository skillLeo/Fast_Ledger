<?php

namespace App\Http\Controllers\Hmrc;

use App\Http\Controllers\Controller;
use App\Http\Requests\SyncObligationsRequest;
use App\Models\HmrcBusiness;
use App\Models\HmrcObligation;
use App\Services\Hmrc\HmrcObligationService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HmrcObligationController extends Controller
{
    protected HmrcObligationService $obligationService;

    public function __construct(HmrcObligationService $obligationService)
    {
        $this->obligationService = $obligationService;
    }

    /**
     * Display obligations dashboard
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Get statistics
        $stats = $this->obligationService->getDashboardStats($user->User_ID);

        // Get upcoming obligations
        $upcomingObligations = $this->obligationService
            ->getUpcomingObligations($user->User_ID, 5);

        // Get overdue obligations
        $overdueObligations = $this->obligationService
            ->getOverdueObligations($user->User_ID);

        // Check if user has HMRC connection
        $hasConnection = $user->hmrcOAuthToken()->where('is_active', true)->exists();

        // Get filter options
        $filterOptions = $this->obligationService->getFilterOptions($user->User_ID);

        // Default view
        $view = $request->get('view', 'dashboard');

        return view('hmrc.obligations.index', compact(
            'stats',
            'upcomingObligations',
            'overdueObligations',
            'hasConnection',
            'filterOptions',
            'view'
        ));
    }

    /**
     * Display obligations in list/table view
     */
    public function list(Request $request)
    {
        $user = auth()->user();

        $query = HmrcObligation::where('user_id', $user->User_ID)
            ->with('business');

        // Apply filters
        if ($request->filled('business_id')) {
            $query->where('business_id', $request->business_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('obligation_type')) {
            $query->where('obligation_type', $request->obligation_type);
        }

        if ($request->filled('tax_year')) {
            $query->where('tax_year', $request->tax_year);
        }

        if ($request->filled('urgency')) {
            $this->applyUrgencyFilter($query, $request->urgency);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'due_date');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $obligations = $query->paginate(20);

        $filterOptions = $this->obligationService->getFilterOptions($user->User_ID);

        if ($request->wantsJson()) {
            return response()->json([
                'data' => $obligations->items(),
                'pagination' => [
                    'total' => $obligations->total(),
                    'per_page' => $obligations->perPage(),
                    'current_page' => $obligations->currentPage(),
                    'last_page' => $obligations->lastPage(),
                ],
            ]);
        }

        return view('hmrc.obligations.list', compact('obligations', 'filterOptions'));
    }

    /**
     * Display calendar view
     */
    public function calendar(Request $request)
    {
        $user = auth()->user();

        $start = $request->has('start')
            ? Carbon::parse($request->start)
            : now()->startOfMonth();

        $end = $request->has('end')
            ? Carbon::parse($request->end)
            : now()->endOfMonth()->addMonth();

        $events = $this->obligationService->getObligationsForCalendar(
            $user->User_ID,
            $start,
            $end
        );

        if ($request->wantsJson()) {
            return response()->json($events);
        }

        $filterOptions = $this->obligationService->getFilterOptions($user->User_ID);

        return view('hmrc.obligations.calendar', compact('filterOptions'));
    }

    /**
     * Show single obligation
     */
    public function show(HmrcObligation $obligation)
    {
        // Check authorization
        if ($obligation->user_id !== auth()->user()->User_ID) {
            abort(403, 'Unauthorized access to this obligation.');
        }

        $obligation->load('business');

        return view('hmrc.obligations.show', compact('obligation'));
    }

    /**
     * Sync obligations from HMRC
     */
    public function sync(SyncObligationsRequest $request)
    {
        $user = auth()->user();

        // Get user's NINO from hmrc_businesses table (first business)
        $firstBusiness = HmrcBusiness::where('user_id', $user->User_ID)->first();
        $nino = $firstBusiness?->nino ?? null;

        if (! $nino) {
            return response()->json([
                'success' => false,
                'message' => 'NINO not found. Please set up your HMRC business first.',
            ], 400);
        }

        try {
            $fromDate = $request->has('from_date')
                ? Carbon::parse($request->from_date)
                : now();

            $toDate = $request->has('to_date')
                ? Carbon::parse($request->to_date)
                : now()->addYear();

            $govTestScenario = $request->get('gov_test_scenario');

            $customBusinessId = $request->get('business_id');
            $results = $this->obligationService->syncAllObligations(
                $user->User_ID,
                $nino,
                $fromDate,
                $toDate,
                $govTestScenario,
                $customBusinessId
            );

            $message = "Synced {$results['periodic']} periodic and " .
                "{$results['crystallisation']} crystallisation obligations.";

            if ($govTestScenario && config('hmrc.environment') === 'sandbox') {
                $message .= " (Using test scenario: {$govTestScenario})";
            }

            if (! empty($results['errors'])) {
                $message .= ' Some errors occurred during sync.';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'results' => $results,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to sync obligations: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Export obligations to CSV
     */
    public function export(Request $request)
    {
        $user = auth()->user();

        $obligations = HmrcObligation::where('user_id', $user->User_ID)
            ->with('business')
            ->get();

        $filename = 'obligations_' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function () use ($obligations) {
            $file = fopen('php://output', 'w');

            // Headers
            fputcsv($file, [
                'Business ID',
                'Business Name',
                'Type',
                'Obligation Type',
                'Period Start',
                'Period End',
                'Due Date',
                'Status',
                'Quarter',
                'Tax Year',
                'Days Until Due',
            ]);

            // Data
            foreach ($obligations as $obligation) {
                fputcsv($file, [
                    $obligation->business_id,
                    $obligation->business?->trading_name ?? '',
                    $obligation->type_of_business,
                    $obligation->obligation_type,
                    $obligation->period_start_date->format('Y-m-d'),
                    $obligation->period_end_date->format('Y-m-d'),
                    $obligation->due_date->format('Y-m-d'),
                    $obligation->status,
                    $obligation->quarter,
                    $obligation->tax_year,
                    $obligation->days_until_due,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Apply urgency filter to query
     */
    protected function applyUrgencyFilter($query, string $urgency): void
    {
        $now = now();

        match ($urgency) {
            'critical' => $query->where('is_overdue', true),
            'urgent' => $query->where('due_date', '<=', $now->copy()->addDays(3))
                ->where('due_date', '>=', $now)
                ->where('status', 'open'),
            'warning' => $query->where('due_date', '<=', $now->copy()->addDays(7))
                ->where('due_date', '>', $now->copy()->addDays(3))
                ->where('status', 'open'),
            'attention' => $query->where('due_date', '<=', $now->copy()->addDays(14))
                ->where('due_date', '>', $now->copy()->addDays(7))
                ->where('status', 'open'),
            default => null
        };
    }
}
