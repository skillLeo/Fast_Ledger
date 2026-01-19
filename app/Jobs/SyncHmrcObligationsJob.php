<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\HmrcBusiness;
use App\Services\Hmrc\HmrcObligationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncHmrcObligationsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $userId,
        public ?string $businessId = null
    ) {}

    public function handle(HmrcObligationService $service): void
    {
        $user = User::find($this->userId);

        if (!$user) {
            Log::warning('Cannot sync obligations: User not found', [
                'user_id' => $this->userId
            ]);
            return;
        }

        // Get NINO from first business
        $firstBusiness = HmrcBusiness::where('user_id', $this->userId)->first();
        $nino = $firstBusiness?->nino ?? null;

        if (!$nino) {
            Log::warning('Cannot sync obligations: NINO not found', [
                'user_id' => $this->userId
            ]);
            return;
        }

        try {
            $results = $service->syncAllObligations(
                $this->userId,
                $nino
            );

            Log::info('Obligations synced successfully', [
                'user_id' => $this->userId,
                'results' => $results
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to sync obligations', [
                'user_id' => $this->userId,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }
}
