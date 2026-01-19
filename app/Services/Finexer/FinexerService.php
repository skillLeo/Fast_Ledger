<?php

namespace App\Services\Finexer;

use App\Exceptions\FinexerException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class FinexerService
{
    protected FinexerClient $client;

    public function __construct(FinexerClient $client)
    {
        $this->client = $client;
    }

    /**
     * ========================================
     * SCENARIO 1: Connect EXISTING Manual Bank
     * ========================================
     * User clicks "Connect" on an existing bank that was created manually
     * 
     * @param int $bankAccountId Existing Bank_Account_ID
     * @param int $bankTypeId Bank Type (1=Client, 2=Office)
     * @return array ['redirect_url' => '...']
     */
    public function connectExistingBank(int $bankAccountId, int $bankTypeId): array
    {
        try {
            // Get existing bank record
            $existingBank = DB::table('bankaccount')
                ->where('Bank_Account_ID', $bankAccountId)
                ->where('Is_Deleted', 0)
                ->first();

            if (!$existingBank) {
                throw new FinexerException("Bank account {$bankAccountId} not found");
            }

            // Get customer ID
            $customerId = config('finexer.sync.default_customer_id') ?? env('FINEXER_DEFAULT_CUSTOMER_ID');

            if (empty($customerId)) {
                throw new FinexerException('Finexer customer ID not configured');
            }

            // Create consent via API
            $consent = $this->client->createConsent(
                $customerId,
                ['accounts', 'balance', 'transactions'],
                route('finexer.callback')
            );

            // UPDATE existing record (don't create new!)
            DB::table('bankaccount')
                ->where('Bank_Account_ID', $bankAccountId)
                ->update([
                    'finexer_consent_id' => $consent['id'],
                    'bank_feed_status' => 'not_connected', // Will become 'connected' after callback
                    'Last_Modified_On' => now(),
                    'Last_Modified_By' => auth()->id() ?? 1,
                ]);

            // Get redirect URL
            $redirectUrl = $consent['redirect']['consent_url'] 
                ?? $consent['redirect_url'] 
                ?? $consent['url'] 
                ?? null;

            if (!$redirectUrl) {
                throw new FinexerException('Redirect URL not found in consent response');
            }

            Log::info('Finexer Consent Created for Existing Bank', [
                'bank_account_id' => $bankAccountId,
                'consent_id' => $consent['id'],
                'bank_name' => $existingBank->Bank_Name,
            ]);

            return [
                'bank_account_id' => $bankAccountId,
                'consent_id' => $consent['id'],
                'redirect_url' => $redirectUrl,
            ];

        } catch (FinexerException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Failed to connect existing bank', [
                'bank_account_id' => $bankAccountId,
                'error' => $e->getMessage(),
            ]);
            throw new FinexerException('Failed to connect existing bank: ' . $e->getMessage());
        }
    }

    /**
     * ========================================
     * SCENARIO 2: Create NEW Bank Connection
     * ========================================
     * User clicks "Connect Bank Feed" (no existing bank selected)
     * 
     * @param int $entityId Client_ID or Supplier_ID
     * @param string $entityType 'Client' or 'Supplier'
     * @param string $customerId Finexer customer ID (optional)
     * @param int|null $bankTypeId Bank Type ID (1=Client, 2=Office)
     * @return array ['consent_id' => 'cons_xxx', 'redirect_url' => 'https://...']
     */
    public function createBankConnection(
        int $entityId,
        string $entityType = 'Client',
        ?string $customerId = null,
        ?int $bankTypeId = null
    ): array {
        // Use default customer ID if not provided
        $customerId = $customerId ?? config('finexer.sync.default_customer_id') ?? env('FINEXER_DEFAULT_CUSTOMER_ID');

        if (empty($customerId)) {
            throw new FinexerException('Finexer customer ID not configured');
        }

        // Get Bank_Type_ID from session if not provided
        if (!$bankTypeId) {
            $bankTypeId = session('pending_bank_type_id', 1); // Default to Client Bank
        }

        try {
            // Create consent via API
            $consent = $this->client->createConsent(
                $customerId,
                ['accounts', 'balance', 'transactions'],
                route('finexer.callback')
            );

            // CREATE NEW bankaccount record with ALL required fields
            $bankAccountId = DB::table('bankaccount')->insertGetId([
                // ✅ REQUIRED FIELDS
                'Client_ID' => $entityType === 'Client' ? $entityId : null,
                'Bank_Type_ID' => $bankTypeId,
                'Bank_Name' => 'Pending Authorization', // ✅ FIX: Temporary name!
                'Account_Name' => null,
                'Account_No' => null,
                'Sort_Code' => null,
                'bank_address' => null,
                
                // ✅ FINEXER FIELDS
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'finexer_consent_id' => $consent['id'],
                'finexer_account_id' => null, // Will be filled after callback
                'finexer_institution_id' => null, // Will be filled after callback
                'bank_feed_status' => 'not_connected',
                'bank_feed_connected_at' => null,
                'bank_feed_last_synced_at' => null,
                'bank_feed_sync_from_date' => null,
                'bank_feed_error' => null,
                'auto_sync_enabled' => 0, // Will be enabled after callback
                
                // ✅ AUDIT FIELDS
                'Created_On' => now(),
                'Created_By' => auth()->id() ?? 1,
                'Last_Modified_On' => now(),
                'Last_Modified_By' => auth()->id() ?? 1,
                'Is_Deleted' => 0,
            ]);

            // Get redirect URL
            $redirectUrl = $consent['redirect']['consent_url'] 
                ?? $consent['redirect_url'] 
                ?? $consent['url'] 
                ?? null;

            if (!$redirectUrl) {
                throw new FinexerException('Redirect URL not found in consent response');
            }

            Log::info('Finexer Consent Created for New Bank', [
                'bank_account_id' => $bankAccountId,
                'consent_id' => $consent['id'],
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'bank_type_id' => $bankTypeId,
            ]);

            return [
                'bank_account_id' => $bankAccountId,
                'consent_id' => $consent['id'],
                'redirect_url' => $redirectUrl,
            ];

        } catch (FinexerException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Failed to create bank connection', [
                'entity_id' => $entityId,
                'entity_type' => $entityType,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new FinexerException('Failed to create bank connection: ' . $e->getMessage());
        }
    }

    /**
     * ========================================
     * STEP 2: Handle Callback (BOTH Scenarios)
     * ========================================
     * After user authorizes at their bank
     * 
     * @param string $consentId From query string fx_consent
     * @return array Bank account details
     */
    public function handleCallback(string $consentId): array
    {
        try {
            // Find bankaccount by consent ID
            $bankAccount = DB::table('bankaccount')
                ->where('finexer_consent_id', $consentId)
                ->where('Is_Deleted', 0)
                ->first();

            if (!$bankAccount) {
                throw FinexerException::consentNotFound($consentId);
            }

            // Fetch and save connected accounts
            $accounts = $this->fetchAndSaveAccounts($consentId, $bankAccount);

            Log::info('Bank connection authorized', [
                'consent_id' => $consentId,
                'bank_account_id' => $bankAccount->Bank_Account_ID,
                'accounts_found' => count($accounts),
            ]);

            return [
                'success' => true,
                'consent_id' => $consentId,
                'bank_account_id' => $bankAccount->Bank_Account_ID,
                'accounts' => $accounts,
            ];

        } catch (\Exception $e) {
            Log::error('Failed to handle callback', [
                'consent_id' => $consentId,
                'error' => $e->getMessage(),
            ]);

            // Update error status
            DB::table('bankaccount')
                ->where('finexer_consent_id', $consentId)
                ->update([
                    'bank_feed_status' => 'error',
                    'bank_feed_error' => $e->getMessage(),
                    'Last_Modified_On' => now(),
                ]);

            throw $e;
        }
    }

    /**
     * ========================================
     * STEP 3: Fetch Accounts from Finexer API
     * ========================================
     * Update database with REAL bank details from API
     */
    protected function fetchAndSaveAccounts(string $consentId, $bankAccount): array
    {
        // Determine customer ID
        $customerId = config('finexer.sync.default_customer_id') ?? env('FINEXER_DEFAULT_CUSTOMER_ID');

        // Fetch accounts from API
        $accounts = $this->client->getBankAccounts($consentId, $customerId);

        if (empty($accounts)) {
            throw new FinexerException('No bank accounts found for this consent');
        }

        // Update the bankaccount record with REAL account details
        $firstAccount = $accounts[0];
        
        DB::table('bankaccount')
            ->where('Bank_Account_ID', $bankAccount->Bank_Account_ID)
            ->update([
                // ✅ UPDATE with REAL bank details from Finexer API
                'Bank_Name' => $firstAccount['provider'] ?? $firstAccount['holder_name'] ?? 'Unknown Bank',
                'Account_Name' => $firstAccount['holder_name'] ?? null,
                'Account_No' => $firstAccount['identification']['account_number'] ?? null,
                'Sort_Code' => $firstAccount['identification']['sort_code'] ?? null,
                'finexer_account_id' => $firstAccount['id'],
                'finexer_institution_id' => $firstAccount['provider_id'] ?? $firstAccount['provider'] ?? null,
                'bank_feed_status' => 'connected', // ✅ NOW CONNECTED!
                'bank_feed_connected_at' => now(),
                'bank_feed_sync_from_date' => Carbon::now()->subDays(config('finexer.sync.initial_sync_days', 90)),
                'auto_sync_enabled' => 1, // ✅ Enable auto sync
                'bank_feed_error' => null,
                'Last_Modified_On' => now(),
                'Last_Modified_By' => auth()->id() ?? 1,
            ]);

        // If there are multiple accounts, create additional records
        if (count($accounts) > 1) {
            for ($i = 1; $i < count($accounts); $i++) {
                $account = $accounts[$i];
                
                DB::table('bankaccount')->insert([
                    'Client_ID' => $bankAccount->Client_ID,
                    'Bank_Type_ID' => $bankAccount->Bank_Type_ID,
                    'entity_type' => $bankAccount->entity_type,
                    'entity_id' => $bankAccount->entity_id,
                    'Bank_Name' => $account['provider'] ?? $account['holder_name'] ?? 'Unknown Bank',
                    'Account_Name' => $account['holder_name'] ?? null,
                    'Account_No' => $account['identification']['account_number'] ?? null,
                    'Sort_Code' => $account['identification']['sort_code'] ?? null,
                    'finexer_account_id' => $account['id'],
                    'finexer_institution_id' => $account['provider_id'] ?? $account['provider'] ?? null,
                    'finexer_consent_id' => $consentId,
                    'bank_feed_status' => 'connected',
                    'bank_feed_connected_at' => now(),
                    'bank_feed_sync_from_date' => Carbon::now()->subDays(config('finexer.sync.initial_sync_days', 90)),
                    'auto_sync_enabled' => 1,
                    'Created_On' => now(),
                    'Created_By' => auth()->id() ?? 1,
                    'Last_Modified_On' => now(),
                    'Last_Modified_By' => auth()->id() ?? 1,
                    'Is_Deleted' => 0,
                ]);
            }
        }

        return $accounts;
    }

    /**
     * ========================================
     * STEP 4: Sync Transactions
     * ========================================
     */
    public function syncTransactions(int $bankAccountId, ?string $fromDate = null): array
    {
        try {
            // Get bankaccount
            $bankAccount = DB::table('bankaccount')
                ->where('Bank_Account_ID', $bankAccountId)
                ->where('Is_Deleted', 0)
                ->first();

            if (!$bankAccount) {
                throw new FinexerException("Bank account {$bankAccountId} not found");
            }

            if ($bankAccount->bank_feed_status !== 'connected') {
                throw FinexerException::accountNotConnected($bankAccountId);
            }

            // Determine from date
            if (!$fromDate) {
                $fromDate = $bankAccount->bank_feed_last_synced_at 
                    ? Carbon::parse($bankAccount->bank_feed_last_synced_at)->format('Y-m-d')
                    : ($bankAccount->bank_feed_sync_from_date 
                        ? Carbon::parse($bankAccount->bank_feed_sync_from_date)->format('Y-m-d')
                        : Carbon::now()->subDays(90)->format('Y-m-d')
                    );
            }

            $toDate = Carbon::now()->format('Y-m-d');

            Log::info('Starting transaction sync', [
                'bank_account_id' => $bankAccountId,
                'from_date' => $fromDate,
                'to_date' => $toDate,
            ]);

            // Step 1: Trigger sync at Finexer (fetches latest from bank)
            try {
                $this->client->syncBankAccount($bankAccount->finexer_account_id);
                Log::info('Bank account sync triggered', [
                    'finexer_account_id' => $bankAccount->finexer_account_id,
                ]);
            } catch (\Exception $e) {
                Log::warning('Sync trigger failed, continuing with fetch', [
                    'error' => $e->getMessage(),
                ]);
            }

            // Step 2: Fetch ALL transactions from Finexer
            // ✅ API returns all synced transactions, we filter by date in PHP
            $allTransactions = $this->client->getTransactions(
                $bankAccount->finexer_account_id
            );

            // Step 3: Filter transactions by date
            $transactions = array_filter($allTransactions, function ($txn) use ($fromDate, $toDate) {
                $txnDate = isset($txn['timestamp']) 
                    ? Carbon::parse($txn['timestamp'])->format('Y-m-d')
                    : null;
                
                if (!$txnDate) {
                    return false;
                }

                return $txnDate >= $fromDate && $txnDate <= $toDate;
            });

            Log::info('Transactions fetched and filtered', [
                'total_fetched' => count($allTransactions),
                'after_date_filter' => count($transactions),
            ]);

            // Save to pending_transactions
            $saved = 0;
            $skipped = 0;

            foreach ($transactions as $txn) {
                $result = $this->savePendingTransaction($bankAccount, $txn);
                if ($result) {
                    $saved++;
                } else {
                    $skipped++;
                }
            }

            // Update last synced timestamp
            DB::table('bankaccount')
                ->where('Bank_Account_ID', $bankAccountId)
                ->update([
                    'bank_feed_last_synced_at' => now(),
                    'bank_feed_status' => 'connected',
                    'bank_feed_error' => null,
                    'Last_Modified_On' => now(),
                ]);

            Log::info('Transaction sync completed', [
                'bank_account_id' => $bankAccountId,
                'total_fetched' => count($transactions),
                'saved' => $saved,
                'skipped' => $skipped,
            ]);

            return [
                'success' => true,
                'bank_account_id' => $bankAccountId,
                'total_fetched' => count($allTransactions),
                'filtered' => count($transactions),
                'saved' => $saved,
                'skipped' => $skipped,
                'from_date' => $fromDate,
                'to_date' => $toDate,
            ];

        } catch (\Exception $e) {
            Log::error('Transaction sync failed', [
                'bank_account_id' => $bankAccountId,
                'error' => $e->getMessage(),
            ]);

            // Update error status
            DB::table('bankaccount')
                ->where('Bank_Account_ID', $bankAccountId)
                ->update([
                    'bank_feed_status' => 'error',
                    'bank_feed_error' => $e->getMessage(),
                    'Last_Modified_On' => now(),
                ]);

            throw $e;
        }
    }

    /**
     * Save transaction to pending_transactions table
     */
    protected function savePendingTransaction($bankAccount, array $transaction): bool
    {
        try {
            // Check if already exists
            $exists = DB::table('pending_transactions')
                ->where('finexer_transaction_id', $transaction['id'])
                ->exists();

            if ($exists) {
                return false; // Skip duplicate
            }

            // Determine transaction type and amounts
            $amount = abs($transaction['amount']);
            $type = $transaction['type'] ?? ($transaction['amount'] < 0 ? 'Dr' : 'Cr');
            $moneyOut = $type === 'Dr' || $transaction['amount'] < 0 ? $amount : 0;
            $moneyIn = $type === 'Cr' || $transaction['amount'] > 0 ? $amount : 0;

            // Insert into pending_transactions
            DB::table('pending_transactions')->insert([
                'bank_account_id' => $bankAccount->Bank_Account_ID,
                'transaction_id' => $transaction['reference'] ?? null,
                'source' => 'bank_feed', // ✅ Mark as bank feed source
                'date' => $transaction['date'] ?? $transaction['booking_date'] ?? now(),
                'time' => $transaction['time'] ?? now()->format('H:i:s'),
                'type' => $type,
                'name' => $transaction['description'] ?? $transaction['merchant_name'] ?? 'Unknown',
                'description' => $transaction['description'] ?? null,
                'category' => $transaction['category'] ?? null,
                'amount' => $amount,
                'currency' => $transaction['currency'] ?? 'GBP',
                'money_out' => $moneyOut,
                'money_in' => $moneyIn,
                'status' => 'pending',
                'finexer_transaction_id' => $transaction['id'],
                'finexer_reference' => $transaction['reference'] ?? null,
                'finexer_raw_data' => json_encode($transaction),
                'bank_feed_fetched_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to save pending transaction', [
                'transaction_id' => $transaction['id'] ?? 'unknown',
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Disconnect bank account
     */
    public function disconnectBank(int $bankAccountId): bool
    {
        try {
            $bankAccount = DB::table('bankaccount')
                ->where('Bank_Account_ID', $bankAccountId)
                ->first();

            if (!$bankAccount) {
                throw new FinexerException("Bank account {$bankAccountId} not found");
            }

            // Revoke consent at Finexer
            if ($bankAccount->finexer_consent_id) {
                try {
                    $this->client->revokeConsent($bankAccount->finexer_consent_id);
                } catch (\Exception $e) {
                    Log::warning('Failed to revoke consent at Finexer', [
                        'consent_id' => $bankAccount->finexer_consent_id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Update database
            DB::table('bankaccount')
                ->where('Bank_Account_ID', $bankAccountId)
                ->update([
                    'bank_feed_status' => 'not_connected',
                    'auto_sync_enabled' => 0,
                    'bank_feed_error' => null,
                    'Last_Modified_On' => now(),
                    'Last_Modified_By' => auth()->id() ?? 1,
                ]);

            Log::info('Bank account disconnected', [
                'bank_account_id' => $bankAccountId,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to disconnect bank', [
                'bank_account_id' => $bankAccountId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Get all connected banks for an entity
     */
    public function getConnectedBanks(int $entityId, string $entityType = 'Client'): array
    {
        return DB::table('bankaccount')
            ->where('entity_id', $entityId)
            ->where('entity_type', $entityType)
            ->where('bank_feed_status', 'connected')
            ->where('Is_Deleted', 0)
            ->get()
            ->toArray();
    }

    /**
     * Test if API connection is working
     */
    public function testConnection(): bool
    {
        return $this->client->testConnection();
    }
}