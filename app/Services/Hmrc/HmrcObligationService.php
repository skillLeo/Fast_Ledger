<?php

namespace App\Services\Hmrc;

use App\Models\HmrcBusiness;
use App\Models\HmrcObligation;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class HmrcObligationService
{
    protected HmrcApiClient $apiClient;

    public function __construct(HmrcApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    /**
     * Sync all obligations for a user
     */
    public function syncAllObligations(
        int $userId,
        string $nino,
        ?Carbon $fromDate = null,
        ?Carbon $toDate = null,
        ?string $govTestScenario = null,
        ?string $customBusinessId = null
    ): array {
        $businesses = HmrcBusiness::where('user_id', $userId)
            ->where('type_of_business', '!=', 'property-unspecified')
            ->get();

        $syncResults = [
            'periodic' => 0,
            'crystallisation' => 0,
            'errors' => [],
        ];

        foreach ($businesses as $business) {
            try {

                $businessId = $business->business_id;

                if ($govTestScenario !== '') {
                    $businessId = $customBusinessId;
                }

                // Sync periodic obligations
                $periodicCount = $this->syncPeriodicObligations(
                    $userId,
                    $nino,
                    $businessId,
                    $fromDate,
                    $toDate,
                    'open',
                    $govTestScenario
                );
                $syncResults['periodic'] += $periodicCount;

                // Sync crystallisation obligations
                $crystCount = $this->syncCrystallisationObligations(
                    $userId,
                    $nino,
                    null,
                    null,
                    $govTestScenario
                );
                $syncResults['crystallisation'] += $crystCount;
            } catch (\Exception $e) {
                Log::error('Failed to sync obligations for business', [
                    'business_id' => $business->business_id,
                    'error' => $e->getMessage(),
                ]);
                $syncResults['errors'][] = [
                    'business_id' => $business->business_id,
                    'message' => $e->getMessage(),
                ];
            }
        }

        // Update overdue statuses
        $this->updateOverdueStatuses($userId);

        return $syncResults;
    }

    /**
     * Sync periodic obligations (income and expenditure)
     */
    public function syncPeriodicObligations(
        int $userId,
        string $nino,
        ?string $businessId = null,
        ?Carbon $fromDate = null,
        ?Carbon $toDate = null,
        ?string $status = null,
        ?string $govTestScenario = null
    ): int {
        $params = [];

        if ($businessId) {
            $business = HmrcBusiness::where('business_id', $businessId)->first();
            $params['businessId'] = $businessId;
            $params['typeOfBusiness'] = $business->type_of_business;
        }

        if ($fromDate) {
            $params['fromDate'] = $fromDate->format('Y-m-d');
        }
        if ($toDate) {
            $params['toDate'] = $toDate->format('Y-m-d');
        }
        if ($status) {
            $params['status'] = $status;
        }

        $endpoint = "/obligations/details/{$nino}/income-and-expenditure";
        if (! empty($params)) {
            $endpoint .= '?'.http_build_query($params);
        }

        $headers = [
            'Accept' => 'application/vnd.hmrc.3.0+json',
        ];

        // Add Gov-Test-Scenario header for sandbox testing
        if ($govTestScenario && config('hmrc.environment') === 'sandbox') {
            $headers['Gov-Test-Scenario'] = $govTestScenario;
        }

        $response = $this->apiClient->get($endpoint, $headers);

        $count = 0;
        if (isset($response['obligations'])) {
            foreach ($response['obligations'] as $businessObligation) {
                $businessId = $businessObligation['businessId'];
                $typeOfBusiness = $businessObligation['typeOfBusiness'];

                foreach ($businessObligation['obligationDetails'] as $detail) {
                    HmrcObligation::updateOrCreate(
                        [
                            'user_id' => $userId,
                            'business_id' => $businessId,
                            'obligation_type' => 'periodic',
                            'period_start_date' => $detail['periodStartDate'],
                            'period_end_date' => $detail['periodEndDate'],
                        ],
                        [
                            'type_of_business' => $typeOfBusiness,
                            'due_date' => $detail['dueDate'],
                            'status' => $detail['status'],
                            'received_date' => $detail['receivedDate'] ?? null,
                            'last_synced_at' => now(),
                        ]
                    );
                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * Sync crystallisation obligations (final declaration)
     */
    public function syncCrystallisationObligations(
        int $userId,
        string $nino,
        ?string $taxYear = null,
        ?string $status = null,
        ?string $govTestScenario = null
    ): int {
        $params = [];
        if ($taxYear) {
            $params['taxYear'] = $taxYear;
        }
        if ($status) {
            $params['status'] = $status;
        }

        $endpoint = "/obligations/details/{$nino}/crystallisation";
        if (! empty($params)) {
            $endpoint .= '?'.http_build_query($params);
        }

        $headers = [
            'Accept' => 'application/vnd.hmrc.3.0+json',
        ];

        // Add Gov-Test-Scenario header for sandbox testing
        if ($govTestScenario && config('hmrc.environment') === 'sandbox') {
            $headers['Gov-Test-Scenario'] = $govTestScenario;
        }

        $response = $this->apiClient->get($endpoint, $headers);

        $count = 0;
        if (isset($response['obligations'])) {
            foreach ($response['obligations'] as $obligation) {
                HmrcObligation::updateOrCreate(
                    [
                        'user_id' => $userId,
                        'business_id' => 'CRYSTALLISATION', // Special identifier
                        'obligation_type' => 'crystallisation',
                        'period_start_date' => $obligation['periodStartDate'],
                        'period_end_date' => $obligation['periodEndDate'],
                    ],
                    [
                        'type_of_business' => 'self-employment', // Default
                        'due_date' => $obligation['dueDate'],
                        'status' => $obligation['status'],
                        'received_date' => $obligation['receivedDate'] ?? null,
                        'last_synced_at' => now(),
                    ]
                );
                $count++;
            }
        }

        return $count;
    }

    /**
     * Get obligations dashboard statistics
     */
    public function getDashboardStats(int $userId): array
    {
        $baseQuery = HmrcObligation::where('user_id', $userId);

        return [
            'overdue' => (clone $baseQuery)->overdue()->count(),
            'due_this_week' => (clone $baseQuery)->open()->dueWithin(7)->count(),
            'due_this_month' => (clone $baseQuery)->open()->dueWithin(30)->count(),
            'upcoming' => (clone $baseQuery)->upcoming()->count(),
            'fulfilled_this_year' => (clone $baseQuery)
                ->fulfilled()
                ->whereYear('received_date', now()->year)
                ->count(),
            'total_open' => (clone $baseQuery)->open()->count(),
            'total_fulfilled' => (clone $baseQuery)->fulfilled()->count(),
        ];
    }

    /**
     * Get upcoming obligations
     */
    public function getUpcomingObligations(int $userId, int $limit = 5): Collection
    {
        return HmrcObligation::where('user_id', $userId)
            ->upcoming()
            ->orderBy('due_date', 'asc')
            ->limit($limit)
            ->with('business')
            ->get();
    }

    /**
     * Get overdue obligations
     */
    public function getOverdueObligations(int $userId): Collection
    {
        return HmrcObligation::where('user_id', $userId)
            ->overdue()
            ->orderBy('due_date', 'asc')
            ->with('business')
            ->get();
    }

    /**
     * Get obligations for calendar view
     */
    public function getObligationsForCalendar(
        int $userId,
        ?Carbon $start = null,
        ?Carbon $end = null
    ): array {
        $query = HmrcObligation::where('user_id', $userId)
            ->with('business');

        if ($start) {
            $query->where('due_date', '>=', $start);
        }
        if ($end) {
            $query->where('due_date', '<=', $end);
        }

        $obligations = $query->get();

        return $obligations->map(function ($obligation) {
            return [
                'id' => $obligation->id,
                'title' => $obligation->period_label,
                'start' => $obligation->due_date->format('Y-m-d'),
                'className' => $this->getCalendarClassName($obligation),
                'extendedProps' => [
                    'business_id' => $obligation->business_id,
                    'business_name' => $obligation->business?->trading_name ?? 'Unknown',
                    'status' => $obligation->status,
                    'type' => $obligation->obligation_type,
                    'is_overdue' => $obligation->is_overdue,
                    'url' => route('hmrc.obligations.show', $obligation),
                ],
            ];
        })->toArray();
    }

    /**
     * Update overdue statuses for all user obligations
     */
    public function updateOverdueStatuses(int $userId): int
    {
        $obligations = HmrcObligation::where('user_id', $userId)
            ->open()
            ->get();

        $count = 0;
        foreach ($obligations as $obligation) {
            $wasOverdue = $obligation->is_overdue;
            $obligation->updateOverdueStatus();

            if (! $wasOverdue && $obligation->is_overdue) {
                // Obligation just became overdue
                $count++;
                // TODO: Trigger notification
            }
        }

        return $count;
    }

    /**
     * Get filter options for obligations
     */
    public function getFilterOptions(int $userId): array
    {
        $businesses = HmrcBusiness::where('user_id', $userId)->get();

        $taxYears = HmrcObligation::where('user_id', $userId)
            ->distinct()
            ->pluck('tax_year')
            ->filter()
            ->sort()
            ->values();

        return [
            'businesses' => $businesses->map(fn ($b) => [
                'id' => $b->business_id,
                'name' => $b->trading_name ?? $b->business_id,
                'type' => $b->type_of_business,
            ]),
            'tax_years' => $taxYears,
            'statuses' => ['open', 'fulfilled'],
            'obligation_types' => ['periodic', 'crystallisation'],
            'urgency_levels' => ['critical', 'urgent', 'warning', 'attention', 'normal'],
        ];
    }

    /**
     * Get calendar class name based on obligation status
     */
    protected function getCalendarClassName(HmrcObligation $obligation): string
    {
        if ($obligation->status === 'fulfilled') {
            return 'event-success';
        }

        if ($obligation->is_overdue) {
            return 'event-danger';
        }

        $daysUntil = $obligation->daysUntilDue();
        if ($daysUntil <= 3) {
            return 'event-critical';
        }
        if ($daysUntil <= 7) {
            return 'event-warning';
        }

        return 'event-info';
    }
}
