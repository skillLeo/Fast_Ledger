<?php

namespace App\Http\Controllers\Report;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Client;

class ClientBankReconciliationController extends Controller
{
    public function index()
    {
        $date = now()->format('Y-m-d'); // Default to current date
        $client_id = Auth::user()->Client_ID;

        // Fetch initial data (Client Balance and Office Balance)
        $finalResult = $this->fetchBankReconciliationData($client_id, $date);

        // Return the view with initial data
        return view('admin.reports.client_bank_reconciliation', ['resultSet' => $finalResult]);
    }

      public function fetchBankReconciliation($date)
    {
        $client_id = Auth::user()->Client_ID;
        
        // 1. Main reconciliation data
        $reconciliation = $this->fetchBankReconciliationData($client_id, $date);

        // 2. Fetch interest and cheques from BankReconciliationDetail (only where Chq_Date is NOT NULL)
        $chequesOrInterest = DB::table('BankReconciliationDetail as BRD')
            ->select('BRD.Amount', 'BRD.Add_Type', 'F.Ledger_Ref', 'T.Cheque', 'T.Transaction_ID')
            ->join('transaction as T', 'T.Transaction_ID', '=', 'BRD.Transaction_ID')
            ->join('file as F', 'F.File_ID', '=', 'T.File_ID')
            ->where('F.Client_ID', $client_id)
            ->where('T.Is_Imported', 1)
            ->whereNotNull('BRD.Chq_Date') // only when Chq_Date IS NOT NULL
            ->whereDate('BRD.Chq_Date', '>', $date)
            ->whereDate('T.Transaction_Date', '<', $date)
            ->where(function ($query) {
                $query->where('BRD.Add_Type', 1) // Interest
                    ->orWhere('BRD.Add_Type', 2); // Cheques
            })
            ->get();

        // Separate
        $interest = $chequesOrInterest->where('Add_Type', 1);
        $cheques = $chequesOrInterest->where('Add_Type', 2);

        // 3. Now fetch from Transaction if NOT present in BRD or BRD.Chq_Date IS NULL
        $transactionCheques = DB::table('transaction as T')
            ->select('T.Transaction_ID', 'T.Transaction_Date', 'T.Amount')
            ->leftJoin('BankReconciliationDetail as BRD', function ($join) {
                $join->on('BRD.Transaction_ID', '=', 'T.Transaction_ID');
            })
            ->join('file as F', 'F.File_ID', '=', 'T.File_ID')
            ->where('F.Client_ID', $client_id)
            ->where('T.Is_Imported', 1)
            ->where('T.Payment_Type_ID', 17)
            ->whereDate('T.Transaction_Date', '<=', $date)
            ->whereDate('T.Transaction_Date', '>=', Carbon::parse($date)->subMonth()) // Added 1 month back
            ->where(function ($query) {
                $query->whereNull('BRD.Transaction_ID') // not linked to BRD
                    ->orWhereNull('BRD.Chq_Date');    // OR if linked but Chq_Date is NULL
            })
            ->get();

        // Merge cheques from BRD + Transaction based on your rules
        $cheques = $cheques->merge($transactionCheques);

        // 4. Return
        return response()->json([
            'reconciliation' => $reconciliation,
            'interest' => $interest,
            'cheques' => $cheques,
        ]);
    }

    // Reusable method to fetch client & office balances
    private function fetchBankReconciliationData($client_id, $date)
    {
        try {
            // Fetch Client Balance (Bank_Type_ID = 1)
             $clientBalanceQuery = DB::table('file as file')
    ->join('transaction as transaction', 'file.File_ID', '=', 'transaction.File_ID')
    ->leftJoin('bankaccount as bankaccount', 'bankaccount.Bank_Account_ID', '=', 'transaction.Bank_Account_ID')
    ->select(
        'file.File_ID',
        'file.Ledger_Ref',

        // Client Balance ONLY for bank accounts with Bank_Type_ID = 1
        DB::raw(
            "SUM(
                CASE
                    WHEN transaction.paid_in_out = 1 
                        AND bankaccount.Bank_Type_ID = 1
                        THEN transaction.Amount
                    WHEN transaction.paid_in_out = 2 
                        AND bankaccount.Bank_Type_ID = 1
                        THEN -transaction.Amount
                    ELSE 0
                END
            ) as `Client Balance`"
        ),

        // LTLC Balance includes transactions without bankaccount and ignores Bank_Type_ID filter
        DB::raw(
            "SUM(
                CASE
                    WHEN transaction.paid_in_out = 1 
                        AND (transaction.Bank_Account_ID IS NULL OR TRIM(transaction.Transaction_Code) LIKE 'LTLC%')
                        AND TRIM(transaction.Transaction_Code) LIKE 'LTLC%' 
                        THEN transaction.Amount
                    WHEN transaction.paid_in_out = 2 
                        AND (transaction.Bank_Account_ID IS NULL OR TRIM(transaction.Transaction_Code) LIKE 'LTLC%')
                        AND TRIM(transaction.Transaction_Code) LIKE 'LTLC%' 
                        THEN -transaction.Amount
                    ELSE 0
                END
            ) as `LTLC Balance`"
        ),

        DB::raw("CONCAT(file.First_Name, ' ', file.Last_Name) as Client_Name")
    )
    ->whereDate('transaction.Transaction_Date', '<=', $date)
    ->where('transaction.Is_Imported', 1)
    // REMOVE global where('bankaccount.Bank_Type_ID', 1)
    ->where('file.Client_ID', $client_id)
    ->groupBy('file.File_ID', 'file.Ledger_Ref', 'file.First_Name', 'file.Last_Name');


            // Fetch Office Balance (Bank_Type_ID = 2)
            $officeBalanceQuery = DB::table('file as file')
                ->join('transaction as transaction', 'file.File_ID', '=', 'transaction.File_ID')
                ->join('bankaccount as bankaccount', 'bankaccount.Bank_Account_ID', '=', 'transaction.Bank_Account_ID')
                ->select(
                    'file.File_ID',
                    'file.Ledger_Ref',
                    DB::raw(
                        "SUM(
                            CASE
                                WHEN transaction.paid_in_out = 1 AND bankaccount.Bank_Type_ID = 2 THEN transaction.Amount
                                WHEN transaction.paid_in_out = 2 AND bankaccount.Bank_Type_ID = 2 THEN -transaction.Amount
                                ELSE 0
                            END
                        ) as `Office Balance`"
                    ),
                    DB::raw("CONCAT(file.First_Name, ' ', file.Last_Name) as Client_Name")
                )
                ->whereDate('transaction.Transaction_Date', '<=', $date)
                ->where('transaction.Is_Imported', 1)
                ->where('bankaccount.Bank_Type_ID', 2)  // Office Bank Accounts
                ->where('file.Client_ID', $client_id)
                ->groupBy('file.File_ID', 'file.Ledger_Ref', 'file.First_Name', 'file.Last_Name');

            // Get results
            $clientBalances = $clientBalanceQuery->get();
            $officeBalances = $officeBalanceQuery->get();

            // Combine results
            return $clientBalances->map(function ($clientBalance) use ($officeBalances) {
                $officeBalance = $officeBalances->firstWhere('File_ID', $clientBalance->File_ID);
                $clientBalance->Office_Balance = isset($officeBalance) ? $officeBalance->{'Office Balance'} : '0.00';

                return $clientBalance;
            });
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error fetching data: ' . $e->getMessage()], 500);
        }
    }
private function getBankReconciliationFullData($client_id, $date)
{
    $reconciliation = $this->fetchBankReconciliationData($client_id, $date);

    $chequesOrInterest = DB::table('BankReconciliationDetail as BRD')
        ->select('BRD.Amount', 'BRD.Add_Type', 'F.Ledger_Ref', 'T.Cheque', 'T.Transaction_ID')
        ->join('transaction as T', 'T.Transaction_ID', '=', 'BRD.Transaction_ID')
        ->join('file as F', 'F.File_ID', '=', 'T.File_ID')
        ->where('F.Client_ID', $client_id)
        ->where('T.Is_Imported', 1)
        ->whereNotNull('BRD.Chq_Date')
        ->whereDate('BRD.Chq_Date', '>', $date)
        ->whereDate('T.Transaction_Date', '<', $date)
        ->whereIn('BRD.Add_Type', [1, 2])
        ->get();

    $interest = $chequesOrInterest->where('Add_Type', 1);
    $cheques = $chequesOrInterest->where('Add_Type', 2);

    $transactionCheques = DB::table('transaction as T')
        ->select('T.Transaction_ID', 'T.Transaction_Date', 'T.Amount', 'T.Cheque', 'F.Ledger_Ref')
        ->leftJoin('BankReconciliationDetail as BRD', function ($join) {
            $join->on('BRD.Transaction_ID', '=', 'T.Transaction_ID');
        })
        ->join('file as F', 'F.File_ID', '=', 'T.File_ID')
        ->where('F.Client_ID', $client_id)
        ->where('T.Is_Imported', 1)
        ->where('T.Payment_Type_ID', 17)
        ->whereDate('T.Transaction_Date', '<=', $date)
        ->whereDate('T.Transaction_Date', '>=', Carbon::parse($date)->subMonth())
        ->where(function ($query) {
            $query->whereNull('BRD.Transaction_ID')->orWhereNull('BRD.Chq_Date');
        })
        ->get();

    $cheques = $cheques->merge($transactionCheques);

    return [
        'reconciliation' => $reconciliation,
        'interest' => $interest,
        'cheques' => $cheques,
    ];
}

  public function exportPdf(Request $request, $date)
{
    $client_id = Auth::user()->Client_ID;
 
         $client_data = Client::where('Client_ID', $client_id)->first();
        $Client_Ref = $client_data->Client_Ref ?? 'N/A';

    $balance = $request->query('balance', 0);

    $data = $this->getBankReconciliationFullData($client_id, $date);

    $pdf = Pdf::loadView('admin.reports.pdf.client_bank_reconciliation_pdf', [
        'resultSet' => $data['reconciliation'],
        'cheques' => $data['cheques'],
        'interest' => $data['interest'],
        'date' => $date,
        'balance' => $balance,
        'client_name' => $Client_Ref,
    ]);

    return $pdf->download('client_bank_reconciliation_' . $date . '.pdf');
}

}
