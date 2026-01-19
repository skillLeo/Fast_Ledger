<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
 
use Barryvdh\DomPDF\Facade as PDF;
use App\DataTables\FileOpeningReport;
use App\Models\File;
use App\Models\VatType;
use App\Models\Client;
use App\Models\Transaction;
 use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use App\DataTables\BillofcostDataTable;
use Carbon\Carbon;
use Vtiful\Kernel\Format;

class BillOfCostReportController extends Controller
{
     public function index(BillofcostDataTable $dataTable)
    {
        return $dataTable->render('admin.reports.bill_of_cost_rep');
    }
    // public function index()
    // { 
    //     return view('admin.reports.bill_of_cost_rep');
    // }
    public function get_data(Request $request)
    {
        $File_id = $request->input('File_id');
        $ledger_ref = $request->input('ledger_ref');
        $userClientId = Auth::user()->Client_ID;
    
        // Fetch file data
        $file_data = File::where('Ledger_Ref', $ledger_ref)
                         ->where('File_id', $File_id)
                         ->first();
    
        // Fetch transaction data
        $transaction_data = Transaction::where('File_ID', $File_id)
                                       ->where('Is_Bill', '!=', '')
                                       ->get();
    
                                       $transactions = [];
                                       $chequeList = []; // Array to store all cheques
                                       $lastTransactionDate = null;
                                       
                                       foreach ($transaction_data as $transaction) {
                                           $TransactionDate = Carbon::parse($transaction->Transaction_Date)->format('d/m/Y'); 
                                           $description = $transaction->Description;
                                           $Transaction_ID = $transaction->Transaction_ID;
                                           $Cheque = $transaction->Cheque;
                                           $VAT_ID = $transaction->VAT_ID;
                                           $Amount = floatval($transaction->Amount); 
                                       
                                           $vat_data = VatType::where('VAT_ID', $VAT_ID)->first();
                                           $percentage = $vat_data ? $vat_data->Percentage : 0; // Ensure VAT data exists
                                           $vat_amount = ($Amount * $percentage) / 100; 
                                           $total_amount = $Amount - $vat_amount;
                                       
                                           // Store cheque values
                                           if (!empty($Cheque)) {
                                               $chequeList[] = $Cheque;
                                           }
                                       
                                           $lastTransactionDate = $TransactionDate;
                                       
                                           $transactions[] = [
                                               'TransactionDate' => $TransactionDate,
                                               'description' => $description,
                                               'Transaction_ID' => $Transaction_ID,
                                               'Cheque' => $Cheque,
                                               'Amount' => $Amount,
                                               'vat_amount' => $vat_amount,
                                               'total_amount' => $total_amount
                                           ];
                                       }
                                       
                                       return response()->json([
                                           'file_data' => $file_data,
                                           'transactions' => $transactions,
                                           'last_transaction_date' => $lastTransactionDate,
                                           'all_cheques' => implode(', ', $chequeList)  
                                       ]);
    }
    
    public function search(Request $request)
    {
        $query = $request->input('query');
        $userClientId = Auth::user()->Client_ID;
    
        $file_data = File::where('Ledger_Ref', 'like', "%{$query}%")
                         ->where('Client_ID', $userClientId)
                         ->limit(10)
                         ->get();
    
        $fileSummaries = [];
    
        foreach ($file_data as $data) {
            $fileSummaries[] = [
                'file_id' => $data->File_ID,
                'Ledger_Ref' => $data->Ledger_Ref,
            ];
        }
    
        return response()->json($fileSummaries);
    }
}
