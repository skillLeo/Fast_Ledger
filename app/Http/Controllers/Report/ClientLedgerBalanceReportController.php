<?php

namespace App\Http\Controllers\Report;
use Barryvdh\DomPDF\Facade\Pdf;
 namespace App\Http\Controllers\Report;
 use App\Http\Controllers\Controller;
namespace App\Http\Controllers\Report;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Controller;
 
use App\Models\File;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth; 
use Carbon\Carbon;
 
 

class ClientLedgerBalanceReportController extends Controller
{



    public function index()
    {
        $userClientId = Auth::user()->Client_ID;
    
        //  $files = File::where('Client_ID', $userClientId)->where('Status','=','L')

        // Get all files related to the logged-in user
        $filess = File::where('Client_ID', $userClientId)->where('Status', '=', 'L')
            ->orderBy('File_Date', 'asc')
            ->get();

        $fileSummaries = [];
        $today = Carbon::today();
        foreach ($filess as $file) {
            $transactions = Transaction::where('File_ID', $file->File_ID)
                ->where('Bank_Account_ID', 22)
                ->where('Is_Imported', 1)
                ->orderBy('Transaction_Date', 'asc')
                ->get();
                // dd( $transactions);

            $runningBalance = 0;
            $lastTransactionDate = null;
            $formatted_date = null;

            foreach ($transactions as $transaction) {
                // dd( $transaction, 'sfsdf');

                if ($transaction->Paid_In_Out == 1) {
                    $runningBalance += $transaction->Amount;
                } elseif ($transaction->Paid_In_Out == 2) {
                    $runningBalance -= $transaction->Amount;
                }

                $lastTransactionDate = $transaction->Transaction_Date;
                $formatted_date = $lastTransactionDate
                    ? Carbon::parse($lastTransactionDate)->format('d/m/Y')
                    : null;

            }

            $daysSinceLastTransaction = $lastTransactionDate
                ? abs($today->diffInDays(Carbon::parse($lastTransactionDate)))
                : null;


            $fileSummaries[] = [
                'File_ID' => $file->File_ID,
                'Ledger_Ref' => $file->Ledger_Ref,
                'Matter' => $file->Matter,
                'Fee_Earner' => $file->Fee_Earner,
                'First_Name' => $file->First_Name,
                'Last_Name' => $file->Last_Name,
                'Address1' => $file->Address1,
                'Address2' => $file->Address2,
                'Total_Balance' => $runningBalance,
                'Last_Transaction_Date' => $formatted_date,
                'Days_Since_Last_Transaction' => $daysSinceLastTransaction,
            ];
        }
        return view('admin.reports.clientLedgerbalance', compact('fileSummaries'));
    }
    
    public function generatePDF()
    {
        $userClientId = Auth::user()->Client_ID;

        $files = File::where('Client_ID', $userClientId)
            ->where('Status', 'L')
            ->orderBy('File_Date', 'asc')
            ->get();

        $fileSummaries = [];
        $today = Carbon::today();

        foreach ($files as $file) {
            $transactions = Transaction::where('File_ID', $file->File_ID)
                ->where('Bank_Account_ID', 22)
                ->where('Is_Imported', 1)
                ->orderBy('Transaction_Date', 'asc')
                ->get();

            $runningBalance = 0;
            $lastTransactionDate = null;

            foreach ($transactions as $transaction) {
                if ($transaction->Paid_In_Out == 1) {
                    $runningBalance += $transaction->Amount;
                } elseif ($transaction->Paid_In_Out == 2) {
                    $runningBalance -= $transaction->Amount;
                }

                $lastTransactionDate = $transaction->Transaction_Date;
            }

            $formatted_date = $lastTransactionDate
                ? Carbon::parse($lastTransactionDate)->format('d/m/Y')
                : null;

            $daysSinceLastTransaction = $lastTransactionDate
                ? abs($today->diffInDays(Carbon::parse($lastTransactionDate)))
                : null;

            $fileSummaries[] = [
                'File_ID' => $file->File_ID,
                'Ledger_Ref' => $file->Ledger_Ref,
                'Matter' => $file->Matter,
                'Fee_Earner' => $file->Fee_Earner,
                'First_Name' => $file->First_Name,
                'Last_Name' => $file->Last_Name,
                'Address1' => $file->Address1,
                'Address2' => $file->Address2,
                'Total_Balance' => $runningBalance,
                'Last_Transaction_Date' => $formatted_date,
                'Days_Since_Last_Transaction' => $daysSinceLastTransaction,
            ];
        }

        $pdf = PDF::loadView('admin.pdf.clientLedgerbalance', compact('fileSummaries'));
        return $pdf->download('14_days_passed_check.pdf');
    }
}
