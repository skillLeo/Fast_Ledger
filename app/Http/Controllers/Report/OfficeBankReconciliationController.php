<?php

namespace App\Http\Controllers\Report;

use App\Models\BankAccount;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class OfficeBankReconciliationController extends Controller
{

    public function index(Request $request)
    {
        // Get the logged-in user's Client_ID
        $clientId = Auth::user()->Client_ID;

        // Fetch bank accounts related to the client
        $banks = BankAccount::where('client_id', $clientId)
            ->where('Bank_Type_ID', 2)
            ->get();

        return view('admin.reports.office-bank-reconciliation', compact('banks'));
    }

    public function getData(Request $request)
    {
        $clientId = Auth::user()->Client_ID;
        $bankId = $request->input('bank_account_id');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        // Validate inputs
        if (!$bankId || !$fromDate || !$toDate) {
            return response()->json(['error' => 'Invalid input parameters'], 400);
        }

        // Execute query
        $transactions = DB::select("
        SELECT 
            file.File_ID, 
            file.Ledger_Ref, 
            transaction.Transaction_ID,
            SUM(transaction.Amount) AS Amount,
            transaction.Cheque,
            transaction.Paid_In_Out,
            CONCAT(file.First_Name, ' ' ,file.Last_Name) AS Client_Name,
            accountref.Reference AS AccountRefDescription,
            transaction.Account_Ref_ID,
            accountref.Base_Category_ID
        FROM 
             file 
        INNER JOIN 
             transaction ON file.File_ID = transaction.File_ID
        INNER JOIN 
            accountref ON transaction.Account_Ref_ID = accountref.Account_Ref_ID
        WHERE 
            Date(transaction.Transaction_Date) BETWEEN ? AND ?
            AND transaction.Is_Imported = 1
            AND transaction.Bank_Account_ID = ?
            AND file.Client_ID = ?
            AND (
                transaction.Account_Ref_ID IN (93, 86) 
                OR transaction.Account_Ref_ID IN (102, 95, 87)
                OR transaction.Account_Ref_ID IN (2, 100, 96)
                OR transaction.Account_Ref_ID IN (40, 108)  -- Moved this up for clarity
                OR accountref.Base_Category_ID = 7
                OR transaction.Account_Ref_ID IN (103, 106, 109, 105)
                OR accountref.Base_Category_ID = 5
            )
        GROUP BY 
            transaction.Transaction_ID, file.File_ID, file.Ledger_Ref, 
            transaction.Cheque, transaction.Paid_In_Out, Client_Name, 
            AccountRefDescription, transaction.Account_Ref_ID, accountref.Base_Category_ID
    ", [$fromDate, $toDate, $bankId, $clientId]);

        // Initialize categorized arrays
        $bookledger = [];
        $disbursments = [];
        $salesBook = [];
        $paymentRefund = [];
        $paymentTransfer = [];
        $miscellaneous = [];

        // Categorize transactions
        foreach ($transactions as $transaction) {
            if (in_array($transaction->Account_Ref_ID, [93, 86])) {
                $bookledger[] = $transaction;
            } elseif (in_array($transaction->Account_Ref_ID, [102, 95, 87])) {
                $disbursments[] = $transaction;
            } elseif (in_array($transaction->Account_Ref_ID, [2, 100, 96])) {
                $salesBook[] = $transaction;
            } elseif ($transaction->Base_Category_ID == 7 || in_array($transaction->Account_Ref_ID, [40, 108])) {
                $paymentRefund[] = $transaction;
            } elseif (in_array($transaction->Account_Ref_ID, [103, 106, 109, 105])) {
                $paymentTransfer[] = $transaction;
            } elseif ($transaction->Base_Category_ID == 5) {
                $miscellaneous[] = $transaction;
            }
        }
        return response()->json([
            'book_ledger' => $bookledger,
            'disbursments' => $disbursments,
            'sales_book' => $salesBook,
            'payment_refund' => $paymentRefund,
            'payment_transfer' => $paymentTransfer,
            'miscellaneous' => $miscellaneous,
        ]);
    }



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
        return response()->json(['initial_balance' => $initialBalance]);
    }

     public function downloadPDF(Request $request)
    {
        $user = Auth::user();
        $clientRef = $user?->client?->Client_Ref ?? 'N/A';

        $bankAccountId = $request->input('bank_account_id');
        $bank = \App\Models\BankAccount::find($bankAccountId);
        $bankName = $bank?->Bank_Name ?? 'Unknown Bank';

        $banks = $this->getData($request);
        $response = $this->getInitialBalance($request);

        if ($banks instanceof \Illuminate\Http\JsonResponse) {
            $banks = $banks->getData();
        } elseif (is_string($banks)) {
            $banks = json_decode($banks, true);
        }

        $initialBalance = 0;
        if ($response instanceof \Illuminate\Http\JsonResponse) {
            $data = $response->getData(true);
            $initialBalance = floatval($data['initial_balance'] ?? 0);
        }

        $totals = [];
        $grandTotal = 0;
        foreach ($banks as $section => $items) {
            if (!is_array($items) && !is_object($items)) {
                continue;
            }

            $totals[$section] = 0;
            foreach ($items as $item) {
                $amount = floatval(is_array($item) ? $item['Amount'] : $item->Amount);
                $paidInOut = is_array($item) ? $item['Paid_In_Out'] : $item->Paid_In_Out;

                $totals[$section] += $paidInOut == 2 ? -abs($amount) : $amount;
            }

            $grandTotal += $totals[$section];
        }

        $flowBalance = $initialBalance + $grandTotal;

        $bankBalance = floatval($request->input('bank_balance', 0));
        $finalDifference = floatval($request->input('final_difference', 0));
        $interestRows = json_decode($request->input('interest_rows', '[]'), true);
        $chequeRows = json_decode($request->input('cheque_rows', '[]'), true);

        $bladeContent = View::make('admin.reports.pdf.office_bank_reconciliation', [
            'banks' => $banks,
            'totals' => $totals,
            'grandTotal' => $grandTotal,
            'initialBalance' => $initialBalance,
            'flowBalance' => $flowBalance,
            'bankName' => $bankName,
            'bankBalance' => $bankBalance,
            'finalDifference' => $finalDifference,
            'interestRows' => $interestRows,
            'chequeRows' => $chequeRows,
            'clientRef' => $clientRef,
        ])->render();

        $pdf = Pdf::loadHTML($bladeContent)->setPaper('a4', 'landscape');

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'Office_Bank_Reconciliation_Report_' . now()->format('Ymd_His') . '.pdf');
    }

}