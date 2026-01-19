<?php

namespace App\Http\Controllers\Report;
use App\Models\Client;

use App\Models\BankAccount;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\DataTables\OfficeCashBookDataTable;

class OfficeCashBookController extends Controller
{
    protected $transactionService;

    public function __construct(OfficeCashBookDataTable $transactionService)
    {
        $this->transactionService = $transactionService;
    }
    public function index(OfficeCashBookDataTable $dataTable)
    {
        $clientId = auth()->user()->Client_ID;

        $fromDate = request('from_date', now()->startOfMonth()->format('Y-m-d'));
        $toDate = request('to_date', now()->format('Y-m-d'));

        request()->merge([
            'from_date' => $fromDate,
            'to_date' => $toDate,
        ]);
        // Fetch the banks for the client
        $banks = $this->getOfficeBanks($clientId, config('constants.OFFICE_BANK_TYPE_ID'));

        $hasFilter = request()->filled('bank_account_id')
            || (request()->filled('from_date') && request()->filled('to_date'));

        $initialBalance = 0;

        // Calculate the initial balance based on transactions before the 'from_date' (if filter is applied)
        if ($hasFilter) {
            $initialBalanceQuery = Transaction::join('file', 'file.File_ID', '=', 'transaction.File_ID')
                ->whereNull('transaction.Deleted_On')
                ->where('transaction.Is_Imported', 1)
                ->where('transaction.Is_Bill', 0)
                ->where('file.Client_ID', $clientId)
                ->when(request()->filled('bank_account_id'), function ($q) {
                    $q->where('transaction.Bank_Account_ID', request('bank_account_id'));
                })
                ->when(request()->filled('from_date'), function ($q) {
                    $q->where('transaction.Transaction_Date', '<', request('from_date')); // Transactions before 'from_date'
                });

            // Calculate the initial balance as the sum of all transactions before the selected date
            $initialBalance = $initialBalanceQuery->sum(DB::raw("CASE WHEN transaction.Paid_In_Out = 1 THEN transaction.Amount ELSE -transaction.Amount END"));
            $initialBalance = $initialBalance === null ? 0 : $initialBalance;
        }

        // Pass the banks and initial balance to the view
        return $dataTable->render('admin.reports.offic_cash_book', compact('banks', 'initialBalance'));
    }

    // Method to fetch updated initial balance via AJAX
    public function getInitialBalance(Request $request)
    {
        $clientId = auth()->user()->Client_ID;
        $initialBalance = 0;

        // Check if filters are applied
        $hasFilter = $request->filled('bank_account_id')
            || ($request->filled('from_date') && $request->filled('to_date'));

        if ($hasFilter) {
            $initialBalanceQuery = Transaction::join('file', 'file.File_ID', '=', 'transaction.File_ID')
                ->whereNull('transaction.Deleted_On')
                ->where('transaction.Is_Imported', 1)
                ->where('transaction.Is_Bill', 0)
                ->where('file.Client_ID', $clientId)
                ->when($request->filled('bank_account_id'), function ($q) use ($request) {
                    $q->where('transaction.Bank_Account_ID', $request->input('bank_account_id'));
                })
                ->when($request->filled('from_date'), function ($q) use ($request) {
                    $q->where('transaction.Transaction_Date', '<', $request->input('from_date'));
                });

            // Calculate the initial balance as the sum of all transactions before the selected date
            $initialBalance = $initialBalanceQuery->sum(DB::raw("CASE WHEN transaction.Paid_In_Out = 1 THEN transaction.Amount ELSE -transaction.Amount END"));
            $initialBalance = $initialBalance === null ? 0 : $initialBalance;
        }

        // Return the updated initial balance as JSON response
        return response()->json(['initial_balance' => number_format($initialBalance, 2)]);
    }

    // Helper function to fetch client banks
    public function getOfficeBanks($clientId, $bankTypeId = null)
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
    public function exportOfficeCashBookPDF(Request $request)
    {
        // Run the query to get transactions
        $transactions = $this->transactionService->query(new Transaction())->get();

        $initialBalance = $transactions->isNotEmpty() ? $transactions->first()->initial_Balance : 0;

        $clientId = auth()->user()->Client_ID;
        $client = Client::find($clientId);
        $initialBalance = $transactions->isNotEmpty() ? $transactions->first()->initial_Balance : 0;

        $firstTransaction = $transactions->first();
        $accountNo = $firstTransaction->Account_No;
        $sortCode = $firstTransaction->Sort_Code;
    

        // dd($bankaccountDetails);
        // Generate PDF
        $pdf = Pdf::loadView('admin.reports.pdf.office_cash_book', compact('transactions', 'initialBalance', 'accountNo', 'sortCode'));

        // Return PDF for download
        return $pdf->download('office_cash_book.pdf');
    }
}