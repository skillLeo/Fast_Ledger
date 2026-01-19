<?php

namespace App\Services\Hmrc;

use App\Models\VatObligation;
use App\Repositories\VatObligationRepository;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class VatObligationService
{
    protected VatObligationRepository $obligationRepository;

    public function __construct(
        VatObligationRepository $obligationRepository
    ) {
        $this->obligationRepository = $obligationRepository;
    }

    /**
     * Sync obligations from HMRC to database
     * HMRC limits: max 18-month range per request
     */
    public function syncObligations(?string $vrn = null, ?array $dateRange = null): int
    {
        $vrn = $vrn ?? config('hmrc.vat.vrn');
        
        // If no date range provided, fetch last 4 years in chunks
        if (!$dateRange) {
            return $this->syncObligationsInChunks($vrn);
        }

        // Single range sync
        return $this->syncSingleRange($vrn, $dateRange);
    }

    /**
     * Sync obligations in 18-month chunks (HMRC requirement)
     */
    protected function syncObligationsInChunks(string $vrn): int
    {
        $totalSynced = 0;
        
        // Start from 4 years ago
        $startDate = Carbon::now()->subYears(4);
        $endDate = Carbon::yesterday();
        
        $currentStart = $startDate->copy();
        
        Log::info('Starting chunked obligation sync', [
            'vrn' => $vrn,
            'full_range' => [
                'from' => $startDate->format('Y-m-d'),
                'to' => $endDate->format('Y-m-d'),
            ],
        ]);
        
        // Fetch in 18-month chunks
        while ($currentStart->lt($endDate)) {
            // Calculate end of this chunk (max 18 months)
            $currentEnd = $currentStart->copy()->addMonths(18);
            
            // Don't go past the overall end date
            if ($currentEnd->gt($endDate)) {
                $currentEnd = $endDate->copy();
            }
            
            $dateRange = [
                'from' => $currentStart->format('Y-m-d'),
                'to' => $currentEnd->format('Y-m-d'),
            ];
            
            Log::debug('Fetching chunk', [
                'chunk_range' => $dateRange,
            ]);
            
            // Sync this chunk
            $chunkCount = $this->syncSingleRange($vrn, $dateRange);
            $totalSynced += $chunkCount;
            
            // Move to next chunk
            $currentStart = $currentEnd->copy()->addDay();
        }
        
        Log::info('Chunked sync completed', [
            'vrn' => $vrn,
            'total_synced' => $totalSynced,
        ]);
        
        return $totalSynced;
    }

    /**
     * Sync a single date range (max 18 months)
     */
    protected function syncSingleRange(string $vrn, array $dateRange): int
    {
        try {
            Log::info('Syncing VAT obligations for range', [
                'vrn' => $vrn,
                'date_range' => $dateRange,
            ]);

            // Get VatService lazily
            $vatService = app(VatService::class);
            
            // Fetch from HMRC
            $hmrcData = $vatService->getObligations($vrn, $dateRange);
            
            $obligationCount = count($hmrcData['obligations'] ?? []);
            
            Log::debug('HMRC obligations received', [
                'obligation_count' => $obligationCount,
            ]);
            
            $syncedCount = 0;
            
            // Process each obligation
            foreach ($hmrcData['obligations'] ?? [] as $obligation) {
                $this->upsertObligation($vrn, $obligation);
                $syncedCount++;
            }

            Log::info('Range sync completed', [
                'vrn' => $vrn,
                'synced' => $syncedCount,
            ]);

            return $syncedCount;

        } catch (\Exception $e) {
            Log::error('Failed to sync VAT obligations for range', [
                'vrn' => $vrn,
                'date_range' => $dateRange,
                'error' => $e->getMessage(),
            ]);

            // Don't throw - just return 0 for this chunk
            return 0;
        }
    }

    /**
     * Upsert a single obligation
     */
    protected function upsertObligation(string $vrn, array $obligation): void
    {
        $periodKey = $obligation['periodKey'] ?? null;

        if (!$periodKey) {
            Log::warning('Skipping obligation - missing periodKey');
            return;
        }

        try {
            VatObligation::updateOrCreate(
                [
                    'vrn' => $vrn,
                    'period_key' => $periodKey,
                ],
                [
                    'start_date' => $obligation['start'] ?? null,
                    'end_date' => $obligation['end'] ?? null,
                    'due_date' => $obligation['due'] ?? null,
                    'status' => $obligation['status'] ?? 'O',
                    'received_date' => $obligation['received'] ?? null,
                ]
            );
            
            Log::debug('Obligation saved', [
                'period_key' => $periodKey,
                'status' => $obligation['status'] ?? 'O',
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to save obligation', [
                'period_key' => $periodKey,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Mark obligation as fulfilled
     */
    public function markAsFulfilled(string $periodKey, ?string $vrn = null): void
    {
        $vrn = $vrn ?? config('hmrc.vat.vrn');

        $updated = VatObligation::where('vrn', $vrn)
            ->where('period_key', $periodKey)
            ->update([
                'status' => 'F',
                'received_date' => now(),
            ]);

        Log::info('Obligation marked as fulfilled', [
            'vrn' => $vrn,
            'period_key' => $periodKey,
            'rows_updated' => $updated,
        ]);
    }

    /**
     * Get obligations from database
     */
    public function getObligationsFromDatabase(?string $vrn = null): array
    {
        $vrn = $vrn ?? config('hmrc.vat.vrn');

        $obligations = VatObligation::where('vrn', $vrn)
            ->orderBy('due_date', 'desc')
            ->get();

        return [
            'open' => $obligations->where('status', 'O')
                ->where('due_date', '>=', now())
                ->sortBy('due_date')
                ->values()
                ->all(),
            
            'overdue' => $obligations->where('status', 'O')
                ->where('due_date', '<', now())
                ->sortByDesc('due_date')
                ->values()
                ->all(),
            
            'fulfilled' => $obligations->where('status', 'F')
                ->sortByDesc('received_date')
                ->values()
                ->all(),
        ];
    }
}