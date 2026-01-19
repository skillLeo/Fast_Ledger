<?php

namespace App\Http\Controllers;

use App\Services\Finexer\FinexerService;
use App\Services\Finexer\FinexerSyncService;
use App\Jobs\Finexer\SyncBankTransactionsJob;
use App\Exceptions\FinexerException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FinexerController extends Controller
{
    protected FinexerService $finexerService;
    protected FinexerSyncService $syncService;

    public function __construct(FinexerService $finexerService, FinexerSyncService $syncService)
    {
        $this->finexerService = $finexerService;
        $this->syncService = $syncService;
    }

    /**
     * ========================================
     * Show Bank Feed Settings Page
     * ========================================
     * GET /finexer/settings
     */
    public function settings()
    {
        $userId = auth()->user()->Client_ID;

        // Get ALL bank accounts for this user
        $bankAccounts = DB::table('bankaccount')
            ->where('Client_ID', $userId)
            ->where('Is_Deleted', 0)
            ->orderBy('Bank_Name')
            ->get();

        // Split into connected and not connected
        $connectedBanks = $bankAccounts->where('bank_feed_status', 'connected');
        $notConnectedBanks = $bankAccounts->whereIn('bank_feed_status', ['not_connected', null]);

        return view('admin.finexer.finexer-index', compact(
            'bankAccounts',
            'connectedBanks',
            'notConnectedBanks'
        ));
    }

    /**
     * ========================================
     * Start Bank Connection Process
     * ========================================
     * GET /finexer/connect?bank_type_id=1&bank_account_id=1 (optional)
     * 
     * SCENARIO 1: bank_account_id provided → Update existing bank
     * SCENARIO 2: bank_account_id NOT provided → Create new bank
     */
    public function connect(Request $request)
    {
        $request->validate([
            'bank_type_id' => 'required|in:1,2',
            'bank_account_id' => 'nullable|integer|exists:bankaccount,Bank_Account_ID'
        ]);

        $userId = auth()->user()->Client_ID;
        $bankTypeId = $request->bank_type_id;
        $bankAccountId = $request->bank_account_id;

        try {
            // Store in session for callback
            session([
                'pending_bank_type_id' => $bankTypeId,
                'pending_bank_account_id' => $bankAccountId,
            ]);

            if ($bankAccountId) {
                // ========================================
                // SCENARIO 1: Connect EXISTING Manual Bank
                // ========================================
                Log::info('Connecting existing bank', [
                    'bank_account_id' => $bankAccountId,
                    'bank_type_id' => $bankTypeId,
                ]);

                $result = $this->finexerService->connectExistingBank(
                    $bankAccountId,
                    $bankTypeId
                );
            } else {
                // ========================================
                // SCENARIO 2: Create NEW Bank Connection
                // ========================================
                Log::info('Creating new bank connection', [
                    'user_id' => $userId,
                    'bank_type_id' => $bankTypeId,
                ]);

                $result = $this->finexerService->createBankConnection(
                    $userId,
                    'Client', // Always Client for now
                    null, // customerId (will use from config)
                    $bankTypeId
                );
            }

            // Redirect to Finexer for user authorization
            return redirect()->away($result['redirect_url']);
        } catch (FinexerException $e) {
            Log::error('Failed to connect bank', [
                'user_id' => $userId,
                'bank_account_id' => $bankAccountId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('finexer.settings')
                ->with('error', 'Failed to connect bank: ' . $e->getMessage());
        }
    }

    /**
     * ========================================
     * Handle Callback from Bank Authorization
     * ========================================
     * GET /finexer/callback?fx_consent=cons_xxx
     * 
     * ⚠️ MUST be outside auth middleware!
     */
    public function callback(Request $request)
    {
        $consentId = $request->query('fx_consent');

        if (!$consentId) {
            Log::warning('Finexer callback missing consent ID', [
                'query' => $request->query(),
            ]);
            return redirect()->route('finexer.settings')
                ->with('error', 'Bank connection failed: Missing consent ID');
        }

        try {
            // Handle the callback (works for BOTH scenarios!)
            $result = $this->finexerService->handleCallback($consentId);

            // Get bank account that was just connected
            $bankAccount = DB::table('bankaccount')
                ->where('finexer_consent_id', $consentId)
                ->first();

            if ($bankAccount) {
                // Start initial sync in background
                SyncBankTransactionsJob::dispatch($bankAccount->Bank_Account_ID);
            }

            // Clear session
            session()->forget(['pending_bank_type_id', 'pending_bank_account_id']);

            return redirect()->route('finexer.settings')
                ->with('success', 'Bank connected successfully! Transactions are being synced...');
        } catch (\Exception $e) {
            Log::error('Callback handling failed', [
                'consent_id' => $consentId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('finexer.settings')
                ->with('error', 'Failed to complete bank connection: ' . $e->getMessage());
        }
    }

    /**
     * ========================================
     * Manually Sync Transactions
     * ========================================
     * POST /finexer/sync/{bankAccountId}
     */
    public function sync(Request $request, int $bankAccountId)
    {
        try {
            $fromDate = $request->get('from_date');
            $result = $this->finexerService->syncTransactions($bankAccountId, $fromDate);

            return response()->json([
                'success' => true,
                'message' => "Synced {$result['saved']} new transactions",
                'data' => $result,
            ]);
        } catch (FinexerException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->getStatusCode());
        }
    }

    /**
     * ========================================
     * Sync All Connected Banks
     * ========================================
     * POST /finexer/sync-all
     */
    public function syncAll()
    {
        try {
            SyncBankTransactionsJob::dispatch();

            return response()->json([
                'success' => true,
                'message' => 'Sync started in background',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * ========================================
     * Disconnect a Bank
     * ========================================
     * POST /finexer/disconnect/{bankAccountId}
     */
    public function disconnect(int $bankAccountId)
    {
        try {
            $this->finexerService->disconnectBank($bankAccountId);

            return response()->json([
                'success' => true,
                'message' => 'Bank disconnected successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * ========================================
     * Show Pending Transactions
     * ========================================
     * GET /finexer/pending-transactions
     */
    public function pendingTransactions(Request $request)
    {
        $bankAccountId = $request->get('bank_account_id');
        $source = $request->get('source');

        $query = DB::table('pending_transactions')
            ->where('status', 'pending');

        if ($bankAccountId) {
            $query->where('bank_account_id', $bankAccountId);
        }

        if ($source) {
            $query->where('source', $source);
        }

        $pendingTransactions = $query
            ->orderBy('date', 'desc')
            ->paginate(50);

        return view('finexer.pending-transactions', compact('pendingTransactions'));
    }

    /**
     * ========================================
     * Reconcile Pending Transaction
     * ========================================
     * POST /finexer/reconcile/{pendingTransactionId}
     */
    public function reconcile(Request $request, int $pendingTransactionId)
    {
        $request->validate([
            'matched_transaction_id' => 'nullable|integer|exists:transaction,Transaction_ID',
        ]);

        try {
            $this->syncService->reconcilePendingTransaction(
                $pendingTransactionId,
                $request->matched_transaction_id
            );

            return response()->json([
                'success' => true,
                'message' => 'Transaction reconciled successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * ========================================
     * Ignore Pending Transaction
     * ========================================
     * POST /finexer/ignore/{pendingTransactionId}
     */
    public function ignore(int $pendingTransactionId)
    {
        try {
            $this->syncService->ignorePendingTransaction($pendingTransactionId);

            return response()->json([
                'success' => true,
                'message' => 'Transaction ignored',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * ========================================
     * Get Sync Statistics
     * ========================================
     * GET /finexer/stats
     */
    public function stats()
    {
        try {
            $stats = $this->syncService->getSyncStatistics();

            return response()->json([
                'success' => true,
                'data' => $stats,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * ========================================
     * Test API Connection
     * ========================================
     * GET /finexer/test-connection
     */
    public function testConnection()
    {
        try {
            $connected = $this->finexerService->testConnection();

            return response()->json([
                'success' => $connected,
                'message' => $connected ? 'API connection successful' : 'API connection failed',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    // FinexerController.php

    /**
     * 🆕 Toggle import button visibility (Session-based)
     */
    public function toggleImportButton(Request $request)
    {
        try {
            $showImportButton = $request->input('show_import_button', false);

            // Store in session (automatically cleared on logout)
            session(['show_import_button' => $showImportButton]);

            return response()->json([
                'success' => true,
                'show_import_button' => $showImportButton,
                'message' => $showImportButton
                    ? 'Import button will now be visible'
                    : 'Import button will be hidden'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating preference: ' . $e->getMessage()
            ], 500);
        }
    }
}
