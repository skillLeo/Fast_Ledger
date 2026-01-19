<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\File;
use League\Csv\Reader;
use App\Models\VatType;
use App\Models\BankAccount;
use App\Models\Transaction;
use App\Models\UploadedFile;
use Illuminate\Http\Request;
use App\Models\ChartOfAccount;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use App\Services\BalanceService;
use App\Models\PendingTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Validator;

class BulkTransactionController extends Controller
{
    const MONEY_IN = 1;
    const MONEY_OUT = 2;
    const MAX_FILE_SIZE = 10240; // 10MB in KB
    const SAMPLE_ROWS = 5;

    protected $balanceService;

    public function __construct(BalanceService $balanceService)
    {
        $this->balanceService = $balanceService;
    }

    public function showUploadForm()
    {
        return view('admin.bulk-transactions.upload');
    }

    public function uploadFile(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls|max:10240', // 10MB max
        ]);

        try {
            DB::beginTransaction();

            $file = $request->file('file');

            $originalFilename = $file->getClientOriginalName();
            $storedFilename = time() . '_' . $originalFilename;

            // Store file
            $filePath = $file->storeAs('bank-statements', $storedFilename, 'local');
            $fullPath = Storage::disk('local')->path($filePath);

            if (!Storage::disk('local')->exists($filePath) || !is_readable($fullPath)) {
                // throw new \Exception('File storage failed.');
                throw new \RuntimeException("Uploaded file not found or not readable. Storage path: {$filePath} — Resolved full path: {$fullPath}");
            }
            // Extract headers
            $headers = $this->extractHeaders($fullPath);
            // Create uploaded file record
            $uploadedFile = UploadedFile::create([
                'original_filename' => $originalFilename,
                'stored_filename' => $storedFilename,
                'file_path' => $filePath,
                'status' => 'pending_mapping',
                'file_headers' => $headers,
                'total_rows' => 0
            ]);

            DB::commit();

            return redirect()->route('bulk-transactions.mapping', $uploadedFile->id)
                ->with('success', 'File uploaded successfully. Please map the columns.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error uploading file: ' . $e->getMessage());
        }
    }

    public function extractHeaders(string $filePath): array
    {


        $extension = pathinfo($filePath, PATHINFO_EXTENSION);

        try {
            if ($extension === 'csv') {

                $csv = Reader::createFromPath($filePath, 'r');
                $csv->setHeaderOffset(0);
                return $csv->getHeader();
            } else {
                // For Excel files
                $spreadsheet = IOFactory::load($filePath);
                $worksheet = $spreadsheet->getActiveSheet();
                $headers = $worksheet->rangeToArray('A1:' . $worksheet->getHighestColumn() . '1')[0];

                // Clean up memory
                $spreadsheet->disconnectWorksheets();
                unset($spreadsheet);

                return array_filter($headers); // Remove empty headers
            }
        } catch (\Exception $e) {
            throw new \Exception('Unable to extract headers from file: ' . $e->getMessage());
        }
    }



    /**
     * Get ledger refs for dropdown
     */
    public function getLedgerRefsForDropdown(Request $request)
    {
        try {
            $ledgerRefs = ChartOfAccount::query()
                ->where('Is_Active', 1) // Match your existing column name
                ->whereNotNull('ledger_ref')
                ->selectRaw('MIN(id) as id, ledger_ref')
                ->groupBy('ledger_ref')
                ->orderBy('ledger_ref')
                ->get();

            return response()->json([
                'success' => true,
                'ledger_refs' => $ledgerRefs
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching ledger refs: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getLedgerRefs()
    {
        return ChartOfAccount::where('Is_Active', 1)
            ->whereNotNull('ledger_ref')
            ->selectRaw('MIN(id) as id, ledger_ref')
            ->groupBy('ledger_ref')
            ->orderBy('ledger_ref')
            ->get();
    }

    /**
     * Get account refs for a specific ledger
     */
    public function getAccountRefsByLedger(Request $request)
    {
        try {

            $ledgerRef = $request->input('ledger_ref');

            if (!$ledgerRef) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ledger reference is required'
                ], 400);
            }

            // Get ALL account refs for the ledger (same pattern as your working example)
            $accountRefs = ChartOfAccount::where('ledger_ref', $ledgerRef)
                ->where('is_active', 1)
                ->select('id', 'account_ref', 'description')
                ->orderBy('account_ref')
                ->get();

            return response()->json([
                'success' => true,
                'account_refs' => $accountRefs
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getAccountRefsByLedger: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error fetching account refs: ' . $e->getMessage()
            ], 500);
        }
    }

    public function showMapping($uploadedFileId)
    {
        $uploadedFile = UploadedFile::findOrFail($uploadedFileId);

        // Get file data
        $filePath = storage_path('app/' . $uploadedFile->file_path);
        $fileData = $this->extractFileDataForMapping($filePath);

        // Get bank accounts
        $bankAccounts = $this->getUserBankAccounts();

        // ✅ NEW: Pass ALL preview rows (not just essential preview)
        $fullPreviewData = $this->getFullPreviewData($filePath);

        return view('admin.bulk-transactions.column_mapping', compact('uploadedFile', 'fileData', 'bankAccounts', 'fullPreviewData'));
    }

    private function getFullPreviewData($filePath)
    {
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);

        try {
            if ($extension === 'csv') {
                $csv = Reader::createFromPath($filePath, 'r');
                $csv->setHeaderOffset(0);
                $records = iterator_to_array($csv->getRecords());
            } else {
                $spreadsheet = IOFactory::load($filePath);
                $worksheet = $spreadsheet->getActiveSheet();
                $headers = $worksheet->rangeToArray('A1:' . $worksheet->getHighestColumn() . '1')[0];
                $headers = array_filter($headers);

                $highestRow = $worksheet->getHighestRow();
                $records = [];

                for ($row = 2; $row <= min($highestRow, 11); $row++) { // Max 10 rows for preview
                    $rowData = $worksheet->rangeToArray('A' . $row . ':' . $worksheet->getHighestColumn() . $row)[0];
                    $records[] = array_combine($headers, $rowData);
                }

                $spreadsheet->disconnectWorksheets();
                unset($spreadsheet);
            }

            // Return first 10 rows with ALL columns
            return array_slice($records, 0, 10);
        } catch (\Exception $e) {
            Log::error('Full preview data extraction error', ['error' => $e->getMessage()]);
            return [];
        }
    }


    // Helper: Extract file data for mapping page
    private function extractFileDataForMapping($filePath)
    {
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);

        try {
            if ($extension === 'csv') {
                return $this->extractCsvDataForMapping($filePath);
            } else {
                return $this->extractExcelDataForMapping($filePath);
            }
        } catch (\Exception $e) {
            Log::error('File data extraction error', ['error' => $e->getMessage()]);
            throw new \Exception('Failed to extract file data: ' . $e->getMessage());
        }
    }

    // Helper: Extract CSV data
    private function extractCsvDataForMapping($filePath)
    {
        $csv = Reader::createFromPath($filePath, 'r');
        $csv->setHeaderOffset(0);

        $headers = $csv->getHeader();
        $records = iterator_to_array($csv->getRecords());

        // Auto-detect columns
        $autoDetected = $this->autoDetectColumns($headers);

        // Get preview data (first 5 rows)
        $previewData = array_slice($records, 0, 5);

        // Extract essential preview with calculated balance
        $essentialPreview = $this->buildEssentialPreview($previewData, $autoDetected);

        // Process all data for balance calculation
        $extractedData = $this->calculateRunningBalance($records, $autoDetected);

        return [
            'headers' => $headers,
            'auto_detected' => $autoDetected,
            'essential_preview' => $essentialPreview,
            'extracted_data' => $extractedData,
            'total_rows' => count($records),
        ];
    }

    // Helper: Extract Excel data
    private function extractExcelDataForMapping($filePath)
    {
        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $headers = $worksheet->rangeToArray('A1:' . $worksheet->getHighestColumn() . '1')[0];
        $headers = array_filter($headers); // Remove empty headers

        $highestRow = $worksheet->getHighestRow();
        $records = [];

        for ($row = 2; $row <= $highestRow; $row++) {
            $rowData = $worksheet->rangeToArray('A' . $row . ':' . $worksheet->getHighestColumn() . $row)[0];
            $records[] = array_combine($headers, $rowData);
        }

        // Clean up
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        // Auto-detect columns
        $autoDetected = $this->autoDetectColumns($headers);

        // Get preview data (first 5 rows)
        $previewData = array_slice($records, 0, 5);

        // Extract essential preview with calculated balance
        $essentialPreview = $this->buildEssentialPreview($previewData, $autoDetected);

        // Process all data for balance calculation
        $extractedData = $this->calculateRunningBalance($records, $autoDetected);

        return [
            'headers' => $headers,
            'auto_detected' => $autoDetected,
            'essential_preview' => $essentialPreview,
            'extracted_data' => $extractedData,
            'total_rows' => count($records),
        ];
    }

    // Helper: Auto-detect columns based on header names
    private function autoDetectColumns($headers)
    {
        $detected = [
            'date' => null,
            'amount' => null,
            'description' => null,
            'balance' => null,
        ];

        $patterns = [
            'date' => ['date', 'transaction date', 'trans date', 'posting date', 'value date'],
            'amount' => ['amount', 'value', 'transaction amount', 'debit', 'credit'],
            'description' => ['description', 'details', 'reference', 'narrative', 'remarks', 'memo', 'particulars'],
            'balance' => ['balance', 'running balance', 'account balance', 'closing balance'],
        ];

        foreach ($headers as $header) {
            $headerLower = strtolower(trim($header));

            foreach ($patterns as $field => $keywords) {
                foreach ($keywords as $keyword) {
                    if (stripos($headerLower, $keyword) !== false) {
                        $detected[$field] = $header;
                        break 2;
                    }
                }
            }
        }

        return $detected;
    }

    // Helper: Build essential preview
    private function buildEssentialPreview($previewData, $autoDetected)
    {
        $essentialPreview = [];
        $runningBalance = 0;

        foreach ($previewData as $row) {
            $date = $autoDetected['date'] ? ($row[$autoDetected['date']] ?? 'N/A') : 'N/A';
            $amount = $autoDetected['amount'] ? ($row[$autoDetected['amount']] ?? 0) : 0;
            $description = $autoDetected['description'] ? ($row[$autoDetected['description']] ?? 'N/A') : 'N/A';

            // Clean and convert amount
            $cleanAmount = $this->cleanAmount($amount);
            $runningBalance += $cleanAmount;

            $essentialPreview[] = [
                'Date' => $this->formatDate($date),
                'Amount' => $cleanAmount,
                'Description' => $description,
                'calculated_balance' => $runningBalance,
            ];
        }

        return $essentialPreview;
    }

    // Helper: Calculate running balance for all records
    private function calculateRunningBalance($records, $autoDetected)
    {
        $extractedData = [];
        $runningBalance = 0;

        foreach ($records as $row) {
            $amount = $autoDetected['amount'] ? ($row[$autoDetected['amount']] ?? 0) : 0;
            $cleanAmount = $this->cleanAmount($amount);
            $runningBalance += $cleanAmount;

            $extractedData[] = [
                'date' => $autoDetected['date'] ? ($row[$autoDetected['date']] ?? null) : null,
                'amount' => $cleanAmount,
                'description' => $autoDetected['description'] ? ($row[$autoDetected['description']] ?? null) : null,
                'balance' => $autoDetected['balance'] ? ($row[$autoDetected['balance']] ?? null) : null,
                'calculated_balance' => $runningBalance,
                'raw_data' => $row,
            ];
        }

        return $extractedData;
    }

    // Helper: Clean amount (remove currency symbols, commas, etc.)
    private function cleanAmount($amount)
    {
        if (is_numeric($amount)) {
            return (float) $amount;
        }

        // Remove currency symbols, commas, spaces
        $cleaned = preg_replace('/[£$€,\s]/', '', $amount);

        // Handle brackets as negative (accounting format)
        if (preg_match('/\((.+)\)/', $cleaned, $matches)) {
            return -1 * (float) $matches[1];
        }

        return (float) $cleaned;
    }

    // Helper: Format date
    private function formatDate($date)
    {
        if (empty($date) || $date === 'N/A') {
            return $date;
        }

        try {
            // Try to parse various date formats
            $parsedDate = \Carbon\Carbon::parse($date);
            return $parsedDate->format('d/m/Y');
        } catch (\Exception $e) {
            return $date; // Return original if parsing fails
        }
    }


    public function saveMapping(Request $request, $uploadedFileId)
    {
        $uploadedFile = UploadedFile::findOrFail($uploadedFileId);

        // Validate individual fields (matching your form)
        $request->validate([
            // 'bank_account_id' => 'required|exists:bankaccount,Bank_Account_ID',
            'bank_account_id' => [
                'required',
                'exists:bankaccount,Bank_Account_ID',
                Rule::exists('bankaccount', 'Bank_Account_ID')
                    ->where('Client_ID', auth()->user()->Client_ID), // Authorization
            ],
            'date_column' => 'required|string',
            'amount_column' => 'required|string',
            'description_column' => 'required|string',
            'balance_column' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Build mapping array from individual fields
            $mapping = [
                'bank_account_id' => $request->bank_account_id,
                'date_column' => $request->date_column,
                'amount_column' => $request->amount_column,
                'description_column' => $request->description_column,
                'balance_column' => $request->balance_column,
            ];

            // Save mapping
            $uploadedFile->update([
                'column_mapping' => $mapping,
                'status' => 'processing'
            ]);

            // Process file and insert into pending_transactions
            $this->processFileWithMapping($uploadedFile);

            $uploadedFile->update(['status' => 'completed']);

            DB::commit();

            return redirect()->route('bulk-transactions.pending', $mapping['bank_account_id'])
                ->with('success', 'File processed successfully. Review pending transactions.');
        } catch (\Exception $e) {
            DB::rollBack();
            $uploadedFile->update([
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);
            return back()->with('error', 'Error processing file: ' . $e->getMessage());
        }
    }


    // Helper: Process file with mapping
    private function processFileWithMapping(UploadedFile $uploadedFile)
    {
        $filePath = storage_path('app/' . $uploadedFile->file_path);
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $mapping = $uploadedFile->column_mapping ?? [];
        if (!$mapping) {
            throw new \Exception('Column mapping not found');
        }

        $rows = [];

        if ($extension === 'csv') {
            $csv = Reader::createFromPath($filePath, 'r');
            $csv->setHeaderOffset(0);
            $records = $csv->getRecords();
            foreach ($records as $record) {
                $rows[] = $record;
            }
        } else {
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $headers = $worksheet->rangeToArray('A1:' . $worksheet->getHighestColumn() . '1')[0];

            $highestRow = $worksheet->getHighestRow();
            for ($row = 2; $row <= $highestRow; $row++) {
                $rowData = $worksheet->rangeToArray('A' . $row . ':' . $worksheet->getHighestColumn() . $row)[0];
                $rows[] = array_combine($headers, $rowData);
            }
        }

        $uploadedFile->update(['total_rows' => count($rows)]);

        $dateColumn = $mapping['date_column'];
        $amountColumn = $mapping['amount_column'];
        $descriptionColumn = $mapping['description_column'];
        $balanceColumn = $mapping['balance_column'] ?? null;
        $bankAccountId = $mapping['bank_account_id'];

        $insertedCount = 0;
        foreach ($rows as $index => $row) {
            try {
                // Parse the essential columns
                $date = $this->parseDate($row[$dateColumn] ?? null);
                $amount = $this->parseAmount($row[$amountColumn] ?? 0);
                $description = $row[$descriptionColumn] ?? null;
                $localAmount = $balanceColumn ? $this->parseAmount($row[$balanceColumn] ?? 0) : null;

                // Determine money_in and money_out
                $moneyIn = $amount > 0 ? $amount : 0;
                $moneyOut = $amount < 0 ? abs($amount) : 0;

                // If money_in/money_out columns exist in CSV, use them
                if (isset($row['Money In'])) {
                    $moneyIn = $this->parseAmount($row['Money In']);
                }
                if (isset($row['Money Out'])) {
                    $moneyOut = $this->parseAmount($row['Money Out']);
                }

                // Create pending transaction
                PendingTransaction::create([
                    'uploaded_file_id' => $uploadedFile->id,
                    'bank_account_id' => $bankAccountId,
                    'transaction_id' => $row['Transaction ID'] ?? null,
                    'date' => $date,
                    'time' => $row['Time'] ?? null,
                    'type' => $row['Type'] ?? null,
                    'name' => $row['Name'] ?? null,
                    'emoji' => $row['Emoji'] ?? null,
                    'category' => $row['Category'] ?? null,
                    'amount' => $amount,
                    'currency' => $row['Currency'] ?? null,
                    'local_amount' => $localAmount,
                    'local_currency' => $row['Local currency'] ?? null,
                    'notes_and_tags' => $row['Notes and #tags'] ?? null,
                    'address' => $row['Address'] ?? null,
                    'receipt' => $row['Receipt'] ?? null,
                    'description' => $description,
                    'category_split' => $row['Category split'] ?? null,
                    'money_out' => $moneyOut,
                    'money_in' => $moneyIn,
                    'status' => 'pending',
                    'raw_data' => json_encode($row),
                ]);

                $insertedCount++;
            } catch (\Exception $e) {
                Log::error("Error processing row " . ($index + 1) . ": " . $e->getMessage());
            }
        }

        $uploadedFile->update(['processed_rows' => $insertedCount]);

        return $insertedCount;
    }

    // Helper method to parse dates
    private function parseDate($value)
    {
        if (empty($value)) {
            return null;
        }

        try {
            // UK format: DD/MM/YYYY
            if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $value)) {
                return Carbon::createFromFormat('d/m/Y', $value)->format('Y-m-d');
            }

            // ISO format
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            Log::warning("Could not parse date: " . $value);
            return null;
        }
    }

    // Helper method to parse amounts
    private function parseAmount($value)
    {
        if (empty($value)) {
            return 0;
        }

        // Remove currency symbols, commas, and spaces
        $cleaned = preg_replace('/[£$€,\s]/', '', $value);

        // Handle parentheses for negative numbers
        if (preg_match('/^\((.*)\)$/', $cleaned, $matches)) {
            $cleaned = '-' . $matches[1];
        }

        return (float) $cleaned;
    }

    /**
     * Get next sequential number for the given prefix using an advisory lock
     * to avoid duplicate numbers under concurrency.
     */
    private function getNextSequentialNumberForPrefix(string $prefix): int
    {
        $lockName = "trans_code_" . $prefix;

        $got = DB::selectOne('SELECT GET_LOCK(?, 5) as got', [$lockName])->got ?? 0;

        if (!$got) {
            // Failed to acquire lock — choose fallback: throw or wait/retry
            throw new \Exception("Unable to acquire lock to generate transaction code for prefix {$prefix}");
        }

        try {
            // compute position where numeric part starts (prefix length + 1)
            $startPos = strlen($prefix) + 1;

            // We use DB::table to run a select raw that returns max_num
            $row = DB::table('transaction')
                ->where('Transaction_Code', 'like', $prefix . '%')
                ->selectRaw("MAX(CAST(SUBSTRING(Transaction_Code, ?, 999) AS UNSIGNED)) as max_num", [$startPos])
                ->first();

            $maxNum = $row->max_num ?? 0;
            $next = intval($maxNum) + 1;

            if ($next > 999999) {
                throw new \Exception("Transaction code sequence exceeded for prefix {$prefix}");
            }

            return $next;
        } finally {
            // Always release lock
            DB::select('SELECT RELEASE_LOCK(?)', [$lockName]);
        }
    }

    /**
     * Build final transaction code: prefix + 6-digit zero-padded number
     */
    private function generateTransactionCodeFromPrefix(string $prefix): string
    {
        $next = $this->getNextSequentialNumberForPrefix($prefix);
        return $prefix . str_pad($next, 6, '0', STR_PAD_LEFT);
    }

    private function buildPrefix(int $bankTypeId, string $transactionType): string
    {
        if ($bankTypeId === config('constants.OFFICE_BANK_TYPE_ID')) {
            return $transactionType === 'Payment' ? 'PAY' : 'REC';
        }

        // default -> client bank
        return $transactionType === 'Payment' ? 'PAYC' : 'RECC';
    }

    /**
     * Show pending transactions for a specific bank account
     */

    public function showPendingTransactions($bankAccountId)
    {
        $userId = auth()->user()->Client_ID;

        $bankAccount = BankAccount::with('bankAccountType')
            ->where('Bank_Account_ID', $bankAccountId)
            ->where('Client_ID', $userId)
            ->firstOrFail();

        // ============================================
        // 🆕 NEW: Determine source based on connection status
        // ============================================
        $isConnected = $bankAccount->bank_feed_status === 'connected';
        $source = $isConnected ? 'bank_feed' : 'manual';

        // ============================================
        // 🆕 NEW: Filter pending transactions by source
        // ============================================
        $query = PendingTransaction::where('bank_account_id', $bankAccountId)
            ->where('status', 'pending')
            ->orderBy('date', 'desc');

        if ($isConnected) {
            // Show ONLY bank feed transactions
            $query->where('source', 'bank_feed');
        } else {
            // Show ONLY manual/uploaded transactions (NOT bank_feed)
            $query->where(function ($q) {
                $q->where('source', '!=', 'bank_feed')
                    ->orWhereNull('source');
            });
        }

        $pendingTransactions = $query->get()
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'date' => Carbon::parse($transaction->date)->format('d/m/Y'),
                    'description' => $transaction->description ?? $transaction->name ?? '',
                    'amount' => abs($transaction->amount ?? 0),
                    'is_credit' => $transaction->amount > 0,
                    'type' => $transaction->amount > 0 ? 'receipt' : 'payment',
                    'money_in' => $transaction->money_in,
                    'money_out' => $transaction->money_out,
                    'category' => $transaction->category,
                    'local_amount' => $transaction->local_amount,
                    'transaction_id' => $transaction->transaction_id,
                    'raw_data' => json_decode($transaction->raw_data, true),
                ];
            });

        // Calculate Balance Information
        $bankAccount = $this->balanceService->addBalancesToBankAccount($bankAccount, $userId);

        // Existing code continues...
        $chartOfAccounts = $this->getChartOfAccounts();
        $ledgerRefs = $this->getLedgerRefs();
        $vatTypes = VatType::all();
        $files = File::where('Client_ID', $userId)->get();

        $bankTypeId = optional($bankAccount->bankAccountType)->Bank_Type_ID;

        $transactionCodes = [];
        $provisionalCode = null;

        if ($bankTypeId) {
            $paymentPrefix = $this->buildPrefix($bankTypeId, 'Payment');
            $receiptPrefix = $this->buildPrefix($bankTypeId, 'Receipt');

            $currentPayment = $this->getCurrentMaxNumber($paymentPrefix);
            $currentReceipt = $this->getCurrentMaxNumber($receiptPrefix);

            foreach ($pendingTransactions as $i => $transaction) {
                $transactionType = $transaction['type'] === 'payment' ? 'Payment' : 'Receipt';
                $prefix = $this->buildPrefix($bankTypeId, $transactionType);

                if ($transactionType === 'Payment') {
                    $currentPayment++;
                    $transactionCodes[$i] = $prefix . str_pad($currentPayment, 6, '0', STR_PAD_LEFT);
                } else {
                    $currentReceipt++;
                    $transactionCodes[$i] = $prefix . str_pad($currentReceipt, 6, '0', STR_PAD_LEFT);
                }
            }

            $provisionalCode = $paymentPrefix . str_pad($this->getCurrentMaxNumber($paymentPrefix) + 1, 6, '0', STR_PAD_LEFT);
        }

        return view('admin.bulk-transactions.preview', compact(
            'bankAccount',
            'pendingTransactions',
            'chartOfAccounts',
            'ledgerRefs',
            'vatTypes',
            'files',
            'bankTypeId',
            'transactionCodes',
            'provisionalCode',
            'isConnected',  // 🆕 Pass to view
            'source'        // 🆕 Pass to view
        ));
    }

    /**
     * Get current maximum number for a given prefix (without lock - for preview only)
     */
    private function getCurrentMaxNumber(string $prefix): int
    {
        $startPos = strlen($prefix) + 1;

        $row = DB::table('transaction')
            ->where('Transaction_Code', 'like', $prefix . '%')
            ->selectRaw("MAX(CAST(SUBSTRING(Transaction_Code, ?, 999) AS UNSIGNED)) as max_num", [$startPos])
            ->first();

        return intval($row->max_num ?? 0);
    }

    // Update your existing method to also get ledger refs
    private function getChartOfAccounts()
    {
        return ChartOfAccount::where('Is_Active', 1)->get();
    }

    public function saveRow(Request $request)
    {
        // 1) Basic validation - transactions must be an array
        $request->validate([
            'bank_account_id' => 'required|exists:bankaccount,Bank_Account_ID',
            'transactions'    => 'required|array|min:1',
        ]);


        // 2) Get all transactions - they're already in array format from frontend
        $allTransactions = $request->input('transactions', []);

        // Filter selected transactions (preserve array indices)
        $selectedTransactions = [];
        foreach ($allTransactions as $index => $txn) {
            // Check if transaction is selected (default to true if not specified)
            $isSelected = !isset($txn['selected']) ||
                $txn['selected'] === true ||
                $txn['selected'] === 'true' ||
                $txn['selected'] === 1 ||
                $txn['selected'] === '1';

            if ($isSelected) {
                $selectedTransactions[] = $txn;
            }
        }

        // Check if at least one transaction is selected
        if (empty($selectedTransactions)) {
            return response()->json([
                'success' => false,
                'message' => 'Please select at least one transaction to save',
            ], 422);
        }

        // 3) Validate selected transactions
        $validationErrors = [];
        $validatedTransactions = [];
        $bank = BankAccount::with('bankAccountType')->findOrFail($request->input('bank_account_id'));
        $bankTypeId = optional($bank->bankAccountType)->Bank_Type_ID;

        foreach ($selectedTransactions as $index => $txn) {
            // ============================================
            // 🆕 UPDATED: Conditional Validation Rules
            // ============================================
            $rules = [
                'pending_id'          => 'nullable',
                'date'                => ['required', 'date_format:d/m/Y'],
                'description'         => 'nullable|string|max:500',
                'amount'              => 'required|numeric',
                'type'                => 'required|in:payment,receipt',
                'file_id'             => 'nullable|integer|exists:file,File_ID',
                'enabled'             => 'nullable|boolean',
                'selected'            => 'nullable',
                '_index'              => 'nullable',
                'payee_id'            => 'nullable|integer',
            ];

            if ($bankTypeId == 2) {
                // Office Bank - Require Ledger, Account, optional VAT
                $rules['ledger_ref'] = 'required|string';
                $rules['chart_of_account_id'] = 'required|integer|exists:chart_of_accounts,id';
                $rules['vat_id'] = 'nullable|integer|exists:vattype,VAT_ID';
                $rules['entry_details'] = 'nullable|string|max:500';
            } else {
                // Client Bank - Require Entry Details
                $rules['ledger_ref'] = 'nullable|string';
                $rules['chart_of_account_id'] = 'nullable|integer';
                $rules['vat_id'] = 'nullable|integer';
                $rules['entry_details'] = 'required|string|max:500';
            }

            $validator = Validator::make($txn, $rules);

            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $field => $messages) {
                    $validationErrors["transactions.{$index}.{$field}"] = $messages;
                }
            } else {
                $validatedTransactions[] = $validator->validated();
            }
        }

        // Return validation errors if any
        if (!empty($validationErrors)) {
            return response()->json([
                'success' => false,
                'errors' => $validationErrors,
                'message' => 'Validation failed for one or more transactions'
            ], 422);
        }


        $created = [];

        // 5) Save transactions in database transaction
        try {
            DB::transaction(function () use ($validatedTransactions, $request, $bankTypeId, &$created) {
                $pendingUpdates = [];

                foreach ($validatedTransactions as $txn) {

                    // Determine transaction type
                    $type = strtolower($txn['type']);
                    $map = [
                        'payment' => ['entry' => 'Dr', 'paid_in_out' => Transaction::MONEY_OUT],
                        'receipt' => ['entry' => 'Cr', 'paid_in_out' => Transaction::MONEY_IN],
                    ];

                    $entryType = $map[$type]['entry'] ?? null;
                    $paidInOut = $map[$type]['paid_in_out'] ?? Transaction::MONEY_NEUTRAL;
                    $transactionType = $type === 'payment' ? 'Payment' : 'Receipt';
                    $prefix = $this->buildPrefix($bankTypeId, $transactionType);

                    // Generate unique transaction code
                    $code = $this->generateTransactionCodeFromPrefix($prefix);

                    // Convert date from d/m/Y to Y-m-d
                    $date = Carbon::createFromFormat('d/m/Y', $txn['date'])->toDateString();

                    // ============================================
                    // 🆕 UPDATED: Handle Entry Details
                    // ============================================
                    $description = $txn['description'] ?? null;

                    // If Client Bank (Type 1) and entry_details provided, append to description
                    if ($bankTypeId == 1 && !empty($txn['entry_details'])) {
                        $description = trim($description . "\n\n--- Entry Details ---\n" . $txn['entry_details']);
                    }

                    // Prepare transaction attributes
                    $attrs = [
                        'Transaction_Code'       => $code,
                        'Bank_Account_ID'        => $request->input('bank_account_id'),
                        'Transaction_Date'       => $date,
                        'Description'            => $description,
                        'Amount'                 => $txn['amount'] ?? 0,
                        'File_ID'                => $txn['file_id'] ?? null,
                        'Ledger_Ref'             => $txn['ledger_ref'] ?? null,
                        'chart_of_account_id'    => $txn['chart_of_account_id'] ?? null,
                        'Account_Ref_ID'         => $txn['chart_of_account_id'] ?? null,
                        'VAT_ID'                 => $txn['vat_id'] ?? null,
                        'Status'                 => 'saved',
                        'is_imported'            => 0,
                        'Created_By'             => auth()->id(),
                        'Created_On'             => now(),
                        'is_bill'                => 0,
                        'entry_type'             => $entryType,
                        'Paid_In_Out'            => $paidInOut,
                        'Payee_ID'               => $txn['payee_id'] ?? null,
                    ];
                    // Create transaction record
                    $transactionRecord = Transaction::create($attrs);
                    if (!empty($txn['pending_id'])) {
                        $pendingUpdates[] = [
                            'pending_id' => $txn['pending_id'],
                            'transaction_id' => $transactionRecord->getKey(),
                        ];
                    }


                    $created[] = [
                        'id' => $transactionRecord->getKey(),
                        'code' => $transactionRecord->Transaction_Code,
                        '_index' => $txn['_index'] ?? null,
                        'pending_id' => $txn['pending_id'] ?? null,
                    ];
                }

                if (!empty($pendingUpdates)) {
                    $pendingIds = array_column($pendingUpdates, 'pending_id');

                    // Bulk update status and completed_at
                    PendingTransaction::whereIn('id', $pendingIds)
                        ->update([
                            'status' => 'completed',
                            'completed_at' => now(),
                        ]);

                    // Update transaction_id individually (if column exists)
                    if (Schema::hasColumn('pending_transactions', 'transaction_id')) {
                        foreach ($pendingUpdates as $pu) {
                            PendingTransaction::where('id', $pu['pending_id'])
                                ->update(['transaction_id' => $pu['transaction_id']]);
                        }
                    }

                    Log::info('Updated pending transactions', [
                        'count' => count($pendingIds),
                        'ids' => $pendingIds,
                    ]);
                }
            });

            return response()->json([
                'success' => true,
                'created' => $created,
                'message' => count($created) . ' transaction(s) saved successfully',
                'transaction_code' => $created[0]['code'] ?? null, // For backward compatibility
            ]);
        } catch (\Exception $e) {
            Log::error('Error saving transactions', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while saving transactions: ' . $e->getMessage(),
            ], 500);
        }
    }


    private function getUserBankAccounts()
    {
        return BankAccount::with('bankAccountType')
            ->where('Client_ID', auth()->user()->Client_ID)
            ->orderBy('Bank_Name')
            ->get([
                'Bank_Account_ID',
                'Bank_Name',
                'Bank_Type_ID',
                'Account_No',
                'Sort_Code'
            ]);
    }


    public function bankReconcile()
    {
        $userId = auth()->user()->Client_ID;

        // Get all bank accounts
        $allBankAccounts = BankAccount::with(['bankAccountType', 'pendingTransactions'])
            ->where('Client_ID', $userId)
            ->orderBy('Bank_Name')
            ->get();

        // ============================================
        // 🆕 NEW: Separate connected vs manual banks
        // ============================================
        $connectedBanks = $allBankAccounts->where('bank_feed_status', 'connected');
        $manualBanks = $allBankAccounts->whereNotIn('bank_feed_status', ['connected']);

        $hasConnectedBanks = $connectedBanks->count() > 0;
        $hasManualBanks = $manualBanks->count() > 0;

        // ============================================
        // ✅ Show ALL banks (connected and manual together)
        // ============================================
        $bankAccountsToShow = $allBankAccounts;

        // ============================================
        // Process selected banks (either connected or manual)
        // ============================================
        $bankAccounts = $bankAccountsToShow->map(function ($bank) use ($userId) {

            // ✅ Determine source based on connection status
            $isConnected = $bank->bank_feed_status === 'connected';
            $source = $isConnected ? 'bank_feed' : 'manual';

            // ✅ Filter pending transactions by source AUTOMATICALLY
            $pendingTransactions = $bank->pendingTransactions
                ->where('status', 'pending')
                ->when($isConnected, function ($collection) {
                    return $collection->where('source', 'bank_feed');
                })
                ->when(!$isConnected, function ($collection) {
                    return $collection->where(function ($item) {
                        return $item->source !== 'bank_feed' || $item->source === null;
                    });
                });

            $itemsToReconcile = $pendingTransactions->count();

            // Totals from pending transactions
            $pendingMoneyIn = (float) $pendingTransactions->sum('money_in');
            $pendingMoneyOut = (float) $pendingTransactions->sum('money_out');
            $pendingBalance = $pendingMoneyIn - $pendingMoneyOut;

            // Completed transactions
            $completedSums = DB::table('transaction')
                ->where('bank_account_id', $bank->Bank_Account_ID)
                ->selectRaw("
                COALESCE(SUM(CASE WHEN Paid_In_Out = 1 THEN Amount ELSE 0 END), 0) AS total_in,
                COALESCE(SUM(CASE WHEN Paid_In_Out = 2 THEN Amount ELSE 0 END), 0) AS total_out
            ")
                ->first();

            $completedMoneyIn = (float) ($completedSums->total_in ?? 0);
            $completedMoneyOut = (float) ($completedSums->total_out ?? 0);
            $fastLedgerBalance = $completedMoneyIn - $completedMoneyOut;

            $statementBalance = $fastLedgerBalance + $pendingBalance;
            $balanceToReconcile = $pendingBalance;

            $chartData = $this->getFilteredChartData($bank->Bank_Account_ID, 'week');

            $completedCount = $bank->pendingTransactions
                ->where('status', 'completed')
                ->count();

            return (object)[
                'id' => $bank->Bank_Account_ID,
                'bank_name' => $bank->Bank_Name,
                'logo' => $this->getBankLogo($bank->Bank_Name),
                'sort_code' => $bank->Sort_Code ?? 'N/A',
                'account_number' => $bank->Account_No ?? 'N/A',
                'statement_balance' => $statementBalance,
                'fast_ledger_balance' => $fastLedgerBalance,
                'balance_to_reconcile' => $balanceToReconcile,
                'items_to_reconcile' => $itemsToReconcile,
                'chart_data' => $chartData,
                'bank_account_id' => $bank->Bank_Account_ID,
                'pending_money_in' => $pendingMoneyIn,
                'pending_money_out' => $pendingMoneyOut,
                'completed_count' => $completedCount,
                // 🆕 NEW: Connection status flags
                'is_connected' => $isConnected,
                'source' => $source,
                'bank_feed_status' => $bank->bank_feed_status,
            ];
        });

        return view('admin.bulk-transactions.bank_reconcile', compact(
            'bankAccounts',
            'hasConnectedBanks',
            'hasManualBanks'
        ));
    }

    /**
     * AJAX endpoint to get filtered chart data
     */
    public function getChartData(Request $request)
    {
        $bankId = $request->input('bank_id');
        $filter = $request->input('filter', 'week'); // default to week

        // Validate bank account belongs to user
        $bankAccount = BankAccount::where('Bank_Account_ID', $bankId)
            ->where('Client_ID', auth()->user()->Client_ID)
            ->first();

        if (!$bankAccount) {
            return response()->json([
                'success' => false,
                'message' => 'Bank account not found'
            ], 404);
        }

        // Get filtered chart data
        $chartData = $this->getFilteredChartData($bankId, $filter);

        return response()->json([
            'success' => true,
            'data' => $chartData
        ]);
    }


    /**
     * AJAX endpoint to get updated balances
     */
    public function getBalances($bankAccountId)
    {
        try {
            $userId = auth()->user()->Client_ID;

            // Validate bank account belongs to user
            $bankAccount = BankAccount::where('Bank_Account_ID', $bankAccountId)
                ->where('Client_ID', $userId)
                ->firstOrFail();

            // Calculate balances using BalanceService
            $balances = $this->balanceService->calculateBalances($bankAccountId, $userId);

            return response()->json([
                'success' => true,
                'statement_balance' => $balances['statement_balance'],
                'fast_ledger_balance' => $balances['fast_ledger_balance'],
                'balance_to_reconcile' => $balances['balance_to_reconcile'],
                'pending_count' => $balances['pending_count'],
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching balances', [
                'bank_account_id' => $bankAccountId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error fetching balances: ' . $e->getMessage()
            ], 500);
        }
    }



    /**
     * Get chart data based on filter type (day/month/year) - OPTIMIZED
     */
    private function getFilteredChartData($bankAccountId, $filter)
    {
        $labels = [];
        $totals = [];

        switch ($filter) {
            case 'week':
                // Current week - daily breakdown (Monday to Sunday)
                $startOfWeek = Carbon::now()->startOfWeek(); // Monday
                $endOfWeek = Carbon::now()->endOfWeek(); // Sunday

                $weeklyData = DB::table('transaction')
                    ->selectRaw('DATE(Transaction_Date) as date, SUM(Amount) as total')
                    ->where('bank_account_id', $bankAccountId)
                    ->whereBetween('Transaction_Date', [$startOfWeek, $endOfWeek])
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get()
                    ->keyBy('date');

                // Generate all 7 days of the week
                for ($i = 0; $i < 7; $i++) {
                    $currentDay = $startOfWeek->copy()->addDays($i);
                    $labels[] = $currentDay->format('D'); // Mon, Tue, Wed, etc.
                    $dateKey = $currentDay->format('Y-m-d');
                    $totals[] = isset($weeklyData[$dateKey]) ? round((float)$weeklyData[$dateKey]->total, 2) : 0;
                }
                break;

            case 'month':
                // Current month - daily breakdown
                $dailyData = DB::table('transaction')
                    ->selectRaw('DAY(Transaction_Date) as day, SUM(Amount) as total')
                    ->where('bank_account_id', $bankAccountId)
                    ->whereYear('Transaction_Date', Carbon::now()->year)
                    ->whereMonth('Transaction_Date', Carbon::now()->month)
                    ->groupBy('day')
                    ->orderBy('day')
                    ->get()
                    ->keyBy('day');

                $daysInMonth = Carbon::now()->daysInMonth;

                for ($day = 1; $day <= $daysInMonth; $day++) {
                    $labels[] = (string)$day;
                    $totals[] = isset($dailyData[$day]) ? round((float)$dailyData[$day]->total, 2) : 0;
                }
                break;

            case 'year':
                // Current year - monthly breakdown
                $monthlyData = DB::table('transaction')
                    ->selectRaw('MONTH(Transaction_Date) as month, SUM(Amount) as total')
                    ->where('bank_account_id', $bankAccountId)
                    ->whereYear('Transaction_Date', Carbon::now()->year)
                    ->groupBy('month')
                    ->orderBy('month')
                    ->get()
                    ->keyBy('month');

                for ($month = 1; $month <= 12; $month++) {
                    $date = Carbon::create(Carbon::now()->year, $month, 1);
                    $labels[] = $date->format('M');
                    $totals[] = isset($monthlyData[$month]) ? round((float)$monthlyData[$month]->total, 2) : 0;
                }
                break;

            default:
                // Fallback to last 9 months
                $nineMonthsData = DB::table('transaction')
                    ->selectRaw('YEAR(Transaction_Date) as year, MONTH(Transaction_Date) as month, SUM(Amount) as total')
                    ->where('bank_account_id', $bankAccountId)
                    ->where('Transaction_Date', '>=', Carbon::now()->subMonths(8)->startOfMonth())
                    ->groupBy('year', 'month')
                    ->orderBy('year')
                    ->orderBy('month')
                    ->get();

                for ($i = 8; $i >= 0; $i--) {
                    $date = Carbon::now()->subMonths($i);
                    $labels[] = $date->format('M');

                    $monthData = $nineMonthsData->first(function ($item) use ($date) {
                        return $item->year == $date->year && $item->month == $date->month;
                    });

                    $totals[] = $monthData ? round((float)$monthData->total, 2) : 0;
                }
        }

        return [
            'months' => $labels,
            'totals' => $totals,
        ];
    }

    /**
     * Get bank logo based on bank name
     */
    private function getBankLogo($bankName)
    {
        $bankName = strtolower($bankName);

        $logos = [
            'hsbc' => asset('assets/images/banks/hsbc.png'),
            'barclays' => asset('assets/images/banks/barclays.png'),
            'lloyds' => asset('assets/images/banks/lloyds.png'),
            'natwest' => asset('assets/images/banks/natwest.png'),
            'santander' => asset('assets/images/banks/santander.png'),
            'rbs' => asset('assets/images/banks/rbs.png'),
            'nationwide' => asset('assets/images/banks/nationwide.png'),
            'halifax' => asset('assets/images/banks/halifax.png'),
            'monzo' => asset('assets/images/banks/monzo.png'),
            'revolut' => asset('assets/images/banks/revolut.png'),
            'metro' => asset('assets/images/banks/metro.png'),
            'first direct' => asset('assets/images/banks/first-direct.png'),
            'tsb' => asset('assets/images/banks/tsb.png'),
        ];

        foreach ($logos as $key => $logo) {
            if (str_contains($bankName, $key)) {
                return $logo;
            }
        }

        // Default bank icon if no match
        return asset('assets/images/banks/default-bank.png');
    }
}
