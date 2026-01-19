<?php

namespace App\Jobs\Finexer;

use App\Services\Finexer\FinexerSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncBankTransactionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     */
    public $timeout = 300; // 5 minutes

    /**
     * Specific bank account ID to sync (optional)
     */
    protected ?int $bankAccountId;

    /**
     * Create a new job instance.
     */
    public function __construct(?int $bankAccountId = null)
    {
        $this->bankAccountId = $bankAccountId;
    }

    /**
     * Execute the job.
     */
    public function handle(FinexerSyncService $syncService): void
    {
        try {
            if ($this->bankAccountId) {
                // Sync specific bank account
                Log::info('Starting sync job for specific account', [
                    'bank_account_id' => $this->bankAccountId,
                ]);

                $result = app(\App\Services\Finexer\FinexerService::class)
                    ->syncTransactions($this->bankAccountId);

                Log::info('Sync job completed for specific account', [
                    'bank_account_id' => $this->bankAccountId,
                    'transactions_saved' => $result['saved'],
                ]);

            } else {
                // Sync all accounts
                Log::info('Starting bulk sync job for all accounts');

                $results = $syncService->syncAllAccounts();

                Log::info('Bulk sync job completed', [
                    'successful' => $results['successful'],
                    'failed' => $results['failed'],
                    'total_transactions' => $results['total_transactions'],
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Sync job failed', [
                'bank_account_id' => $this->bankAccountId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Re-throw to mark job as failed
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Sync job permanently failed after all retries', [
            'bank_account_id' => $this->bankAccountId,
            'error' => $exception->getMessage(),
        ]);

        // You could send notification to admin here
    }
}