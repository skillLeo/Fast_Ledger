<?php

namespace App\Services\Finexer;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class FinexerSyncService
{
    protected FinexerService $finexerService;

    public function __construct(FinexerService $finexerService)
    {
        $this->finexerService = $finexerService;
    }

    /**
     * Sync all connected bank accounts
     */
    public function syncAllAccounts(): array
    {
        Log::info('Starting bulk sync for all connected accounts');

        $accounts = DB::table('bankaccount')
            ->where('bank_feed_status', 'connected')
            ->where('auto_sync_enabled', 1)
            ->where('Is_Deleted', 0)
            ->get();

        $results = [
            'total_accounts' => count($accounts),
            'successful' => 0,
            'failed' => 0,
            'total_transactions' => 0,
            'details' => [],
        ];

        foreach ($accounts as $account) {
            try {
                $syncResult = $this->finexerService->syncTransactions($account->Bank_Account_ID);
                
                $results['successful']++;
                $results['total_transactions'] += $syncResult['saved'];
                $results['details'][] = [
                    'bank_account_id' => $account->Bank_Account_ID,
                    'bank_name' => $account->Bank_Name,
                    'status' => 'success',
                    'transactions' => $syncResult['saved'],
                ];

            } catch (\Exception $e) {
                $results['failed']++;
                $results['details'][] = [
                    'bank_account_id' => $account->Bank_Account_ID,
                    'bank_name' => $account->Bank_Name,
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                ];

                Log::error('Failed to sync account in bulk operation', [
                    'bank_account_id' => $account->Bank_Account_ID,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('Bulk sync completed', $results);

        return $results;
    }

    /**
     * Sync accounts for a specific entity
     */
    public function syncEntityAccounts(int $entityId, string $entityType = 'Client'): array
    {
        $accounts = DB::table('bankaccount')
            ->where('entity_id', $entityId)
            ->where('entity_type', $entityType)
            ->where('bank_feed_status', 'connected')
            ->where('auto_sync_enabled', 1)
            ->where('Is_Deleted', 0)
            ->get();

        $results = [
            'entity_id' => $entityId,
            'entity_type' => $entityType,
            'total_accounts' => count($accounts),
            'successful' => 0,
            'failed' => 0,
            'total_transactions' => 0,
            'details' => [],
        ];

        foreach ($accounts as $account) {
            try {
                $syncResult = $this->finexerService->syncTransactions($account->Bank_Account_ID);
                
                $results['successful']++;
                $results['total_transactions'] += $syncResult['saved'];
                $results['details'][] = [
                    'bank_account_id' => $account->Bank_Account_ID,
                    'bank_name' => $account->Bank_Name,
                    'status' => 'success',
                    'transactions' => $syncResult['saved'],
                ];

            } catch (\Exception $e) {
                $results['failed']++;
                $results['details'][] = [
                    'bank_account_id' => $account->Bank_Account_ID,
                    'bank_name' => $account->Bank_Name,
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Get sync statistics
     */
    public function getSyncStatistics(): array
    {
        return [
            'connected_accounts' => DB::table('bankaccount')
                ->where('bank_feed_status', 'connected')
                ->where('Is_Deleted', 0)
                ->count(),
            
            'auto_sync_enabled' => DB::table('bankaccount')
                ->where('bank_feed_status', 'connected')
                ->where('auto_sync_enabled', 1)
                ->where('Is_Deleted', 0)
                ->count(),
            
            'pending_bank_feed_transactions' => DB::table('pending_transactions')
                ->where('source', 'bank_feed')
                ->where('status', 'pending')
                ->count(),
            
            'synced_transactions_today' => DB::table('pending_transactions')
                ->where('source', 'bank_feed')
                ->whereDate('bank_feed_fetched_at', Carbon::today())
                ->count(),
            
            'last_sync_time' => DB::table('bankaccount')
                ->where('bank_feed_status', 'connected')
                ->where('Is_Deleted', 0)
                ->max('bank_feed_last_synced_at'),
            
            'accounts_needing_sync' => DB::table('bankaccount')
                ->where('bank_feed_status', 'connected')
                ->where('auto_sync_enabled', 1)
                ->where(function ($query) {
                    $query->whereNull('bank_feed_last_synced_at')
                        ->orWhere('bank_feed_last_synced_at', '<', Carbon::now()->subHours(
                            config('finexer.sync.auto_sync_interval_hours', 24)
                        ));
                })
                ->where('Is_Deleted', 0)
                ->count(),
        ];
    }

    /**
     * Check which accounts need syncing
     */
    public function getAccountsNeedingSync(): array
    {
        $syncIntervalHours = config('finexer.sync.auto_sync_interval_hours', 24);

        return DB::table('bankaccount')
            ->select([
                'Bank_Account_ID',
                'Bank_Name',
                'Account_No',
                'entity_type',
                'entity_id',
                'bank_feed_last_synced_at',
            ])
            ->where('bank_feed_status', 'connected')
            ->where('auto_sync_enabled', 1)
            ->where(function ($query) use ($syncIntervalHours) {
                $query->whereNull('bank_feed_last_synced_at')
                    ->orWhere('bank_feed_last_synced_at', '<', Carbon::now()->subHours($syncIntervalHours));
            })
            ->where('Is_Deleted', 0)
            ->get()
            ->toArray();
    }

    /**
     * Reconcile pending transaction to actual transaction
     */
    public function reconcilePendingTransaction(int $pendingTransactionId, ?int $matchedTransactionId = null): bool
    {
        try {
            DB::beginTransaction();

            $pending = DB::table('pending_transactions')
                ->where('id', $pendingTransactionId)
                ->first();

            if (!$pending) {
                throw new \Exception("Pending transaction {$pendingTransactionId} not found");
            }

            if ($matchedTransactionId) {
                // Update existing transaction with bank feed info
                DB::table('transaction')
                    ->where('Transaction_ID', $matchedTransactionId)
                    ->update([
                        'source' => 'bank_feed',
                        'finexer_transaction_id' => $pending->finexer_transaction_id,
                        'finexer_reference' => $pending->finexer_reference,
                        'bank_feed_synced_at' => now(),
                        'Modified_On' => now(),
                        'Modified_By' => auth()->id() ?? 1,
                    ]);

                DB::table('pending_transactions')
                    ->where('id', $pendingTransactionId)
                    ->update([
                        'status' => 'completed',
                        'completed_at' => now(),
                        'updated_at' => now(),
                    ]);

            } else {
                // Create new transaction from pending
                $transactionId = DB::table('transaction')->insertGetId([
                    'Transaction_Date' => $pending->date,
                    'Bank_Account_ID' => $pending->bank_account_id,
                    'Paid_In_Out' => $pending->type === 'Dr' ? 0 : 1,
                    'Description' => $pending->description ?? $pending->name,
                    'Amount' => $pending->amount,
                    'debit_amount' => $pending->money_out,
                    'credit_amount' => $pending->money_in,
                    'source' => 'bank_feed',
                    'finexer_transaction_id' => $pending->finexer_transaction_id,
                    'finexer_reference' => $pending->finexer_reference,
                    'bank_feed_synced_at' => now(),
                    'Created_By' => auth()->id() ?? 1,
                    'Created_On' => now(),
                    'Modified_By' => auth()->id() ?? 1,
                    'Modified_On' => now(),
                ]);

                DB::table('pending_transactions')
                    ->where('id', $pendingTransactionId)
                    ->update([
                        'status' => 'completed',
                        'completed_at' => now(),
                        'updated_at' => now(),
                    ]);
            }

            DB::commit();

            Log::info('Pending transaction reconciled', [
                'pending_id' => $pendingTransactionId,
                'matched_transaction_id' => $matchedTransactionId,
            ]);

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to reconcile pending transaction', [
                'pending_id' => $pendingTransactionId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Ignore/reject a pending transaction
     */
    public function ignorePendingTransaction(int $pendingTransactionId): bool
    {
        try {
            DB::table('pending_transactions')
                ->where('id', $pendingTransactionId)
                ->update([
                    'status' => 'rejected',
                    'updated_at' => now(),
                ]);

            Log::info('Pending transaction ignored', [
                'pending_id' => $pendingTransactionId,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to ignore pending transaction', [
                'pending_id' => $pendingTransactionId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}