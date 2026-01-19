<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\BankAccount;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use App\DataTables\ClientCashBookDataTable;

class ClientCashBookController extends Controller
{
    protected $transactionService;

    public function __construct(ClientCashBookDataTable $transactionService)
    {
        $this->transactionService = $transactionService;
    }
    
    public function index(ClientCashBookDataTable $dataTable)
    {
        $clientId = auth()->user()->Client_ID;
        
        $fromDate = request('from_date', now()->startOfMonth()->format('Y-m-d'));
        $toDate = request('to_date', now()->format('Y-m-d'));

        request()->merge([
            'from_date' => $fromDate,
            'to_date' => $toDate,
        ]);

        // Fetch the banks for the client
        $banks = $this->getClientBanks($clientId, config('constants.CLIENT_BANK_TYPE_ID'));

        // Get bank account ID
        $bankAccountId = request('bank_account_id');
        
        // Always calculate initial balance when date filters are present
        $initialBalance = 0;
        
        $initialBalanceQuery = Transaction::join('file', 'file.File_ID', '=', 'transaction.File_ID')
            ->leftJoin('bankaccount', 'bankaccount.Bank_Account_ID', '=', 'transaction.Bank_Account_ID')
            ->active()
            ->where('file.Client_ID', $clientId);

        // Apply specific filters based on bank selection
        if ($bankAccountId === 'ledger_to_ledger') {
            // For ledger_to_ledger mode, filter by LTLC prefix only
            $initialBalanceQuery->where('transaction.Transaction_code', 'LIKE', 'LTLC%');
        } elseif ($bankAccountId === 'all_banks') {
            // For "All Banks" option, include all bank transactions (no additional filtering)
        } elseif (!empty($bankAccountId) && $bankAccountId !== '') {
            // For specific bank selection
            $initialBalanceQuery->where('transaction.Bank_Account_ID', $bankAccountId);
        } else {
            // When no bank selected, don't calculate any balance
            $initialBalance = 0;
            return $dataTable->render('admin.reports.client_cash_book', compact('banks', 'initialBalance'));
        }

        $initialBalanceQuery->when(request()->filled('from_date'), function ($q) {
            $q->where('transaction.Transaction_Date', '<', request('from_date'));
        });

        // Calculate the initial balance as the sum of all transactions before the selected date
        $initialBalance = $initialBalanceQuery->sum(DB::raw("CASE WHEN transaction.Paid_In_Out = 1 THEN transaction.Amount ELSE -transaction.Amount END"));
        $initialBalance = $initialBalance === null ? 0 : $initialBalance;

        // Pass the banks and initial balance to the view
        return $dataTable->render('admin.reports.client_cash_book', compact('banks', 'initialBalance'));
    }

    // Method to fetch updated initial balance via AJAX
    public function getInitialBalance(Request $request)
    {
        $request->validate([
            'from_date' => 'required|date',
            'to_date'   => 'required|date',
            'bank_account_id' => 'nullable|string',
        ]);

        $clientId = auth()->user()->Client_ID;
        $bankAccountId = $request->input('bank_account_id');
        $fromDate = $request->input('from_date');

        $initialBalance = 0;

        $query = Transaction::join('file', 'file.File_ID', '=', 'transaction.File_ID')
            ->leftJoin('bankaccount', 'bankaccount.Bank_Account_ID', '=', 'transaction.Bank_Account_ID')
            ->active()
            ->where('file.Client_ID', $clientId);

        // Apply filters based on bank selection
        if ($bankAccountId === 'ledger_to_ledger') {
            // For ledger_to_ledger, filter by LTLC prefix only
            $query->where('transaction.Transaction_code', 'LIKE', 'LTLC%');
        } elseif ($bankAccountId === 'all_banks') {
            // For "All Banks" option, include all bank transactions (no additional filtering)
        } elseif (!empty($bankAccountId) && $bankAccountId !== '') {
            // For specific bank selection
            $query->where('transaction.Bank_Account_ID', $bankAccountId);
        }

        $query->when($fromDate, function ($query) use ($fromDate) {
            $query->where('transaction.Transaction_Date', '<', $fromDate);
        });

        $initialBalance = $query->sum(DB::raw("CASE WHEN transaction.Paid_In_Out = 1 THEN transaction.Amount ELSE -transaction.Amount END")) ?? 0;

        return response()->json(['initial_balance' => number_format($initialBalance, 2)]);
    }

    // Helper function to fetch client banks
    public function getClientBanks($clientId, $bankTypeId = null)
    {
        $query = BankAccount::join('bankaccounttype', 'bankaccount.Bank_Type_ID', '=', 'bankaccounttype.Bank_Type_ID')
            ->where('bankaccount.Client_ID', $clientId)
            ->orderBy('bankaccount.Bank_Name', 'asc');

        if (!is_null($bankTypeId)) {
            $query->where('bankaccount.Bank_Type_ID', $bankTypeId);
        }

        $banks = $query->get([
            'bankaccount.Bank_Account_ID',
            'bankaccount.Bank_Name',
            'bankaccounttype.Bank_Type',
            'bankaccount.Bank_Type_ID',
        ]);

        return $banks->map(function ($bank) {
            return [
                'Bank_Account_ID' => $bank->Bank_Account_ID,
                'Bank_Account_Name' => "{$bank->Bank_Name} ({$bank->Bank_Type})",
                'Bank_Type_ID' => $bank->Bank_Type_ID,
            ];
        });
    }

    public function exportClientCashBookPDF(Request $request)
    {
        // Run the query to get transactions
        $transactions = $this->transactionService->query(new Transaction())->get();

        $initialBalance = $transactions->isNotEmpty() ? $transactions->first()->initial_Balance : 0;

        $clientId = auth()->user()->Client_ID;
        $client = Client::find($clientId);
        
        $firstTransaction = $transactions->first();
        $accountNo = $firstTransaction ? $firstTransaction->Account_No : '';
        $sortCode = $firstTransaction ? $firstTransaction->Sort_Code : '';

        // Generate PDF
        $pdf = Pdf::loadView('admin.reports.pdf.client_cash_book_pdf', compact('transactions', 'initialBalance', 'accountNo', 'sortCode'));

        // Return PDF for download
        return $pdf->download('client_cash_book.pdf');
    }
}