<?php

namespace App\Http\Controllers\Report;

use Carbon\Carbon;
use App\Models\Client;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class ProfitAndLoosController extends Controller
{

    public function index(Request $request)
    {
        $clientId = auth()->user()->Client_ID ?? $request->input('client_id');
        $clientInfo = Client::find($clientId);
        $fromDate = $request->input('from_date') ? Carbon::parse($request->input('from_date'))->toDateString() : null;
        $toDate = $request->input('to_date') ? Carbon::parse($request->input('to_date'))->toDateString() : null;
        // Fetch all types of data
        $reportData = [
            'vat' => $this->getVatIncomeHead($fromDate, $toDate, $clientId, 'vat'),
            'income' => $this->fetchProfitAndLoss($fromDate, $toDate, $clientId, 'income'),
            'cost' => $this->fetchProfitAndLoss($fromDate, $toDate, $clientId, 'cost'),
            'expense' => $this->fetchProfitAndLoss($fromDate, $toDate, $clientId, 'expense'),
            'interestReceived' => $this->fetchProfitAndLoss($fromDate, $toDate, $clientId, 'InterestRecieved'),
            'interestPaid' => $this->fetchProfitAndLoss($fromDate, $toDate, $clientId, 'InterestPaid'),
            'bill' => $this->fetchProfitAndLoss($fromDate, $toDate, $clientId, 'bill'),
        ];
        // Now consolidate all and keep as a collection
        $reportData = collect($reportData)->map(function ($collection) {
            $consolidated = $this->consolidateReport($collection);
            $netOfVatSum = $consolidated->sum('NetOfVat');
            return [
                'data' => $consolidated,
                'netOfVatSum' => $netOfVatSum,
            ];
            return $this->consolidateReport($collection);
        });
        // For testing:
        // dd($reportData);
        return view('admin.reports.profit_and_loos', compact('reportData', 'fromDate', 'toDate', 'clientInfo'));
    }
    private function fetchProfitAndLoss($fromDate, $toDate, $clientId, $type)
    {
        $query = DB::table('transaction as transaction')
            ->selectRaw('
                SUM(transaction.Amount) AS Amount,
                transaction.Paid_In_Out,
                vattype.Percentage,
                accountref.Reference AS Description,
                accountref.Account_Ref_ID
            ')
            ->join('accountref', 'accountref.Account_Ref_ID', '=', 'transaction.Account_Ref_ID')
            ->leftJoin('vattype', 'vattype.VAT_ID', '=', 'transaction.VAT_ID')
            ->join('file as file', 'file.File_ID', '=', 'transaction.File_ID')
            ->whereBetween('Transaction_Date', [$fromDate, $toDate])
            ->where('transaction.Is_Imported', 1)
            ->where('file.Client_ID', $clientId);
        switch ($type) {
            case 'bill':
                $query->where('accountref.Base_Category_ID', 1);
                break;
            case 'income':
                $query->whereIn('transaction.Account_Ref_ID', [101, 99]);
                break;
            case 'cost':
                $query->whereIn('transaction.Account_Ref_ID', [108, 6, 7]);
                break;
            case 'expense':
                $query->where(function ($q) {
                    $q->where('accountref.Base_Category_ID', 7)
                        ->orWhere('transaction.Account_Ref_ID', 40);
                });
                break;
            case 'InterestRecieved':
                $query->whereIn('transaction.Account_Ref_ID', [100]);
                break;
            case 'InterestPaid':
                $query->whereIn('transaction.Account_Ref_ID', [38]);
                break;
        }
        $query->groupBy(
            'transaction.Account_Ref_ID',
            'accountref.Account_Ref_ID',
            'transaction.Paid_In_Out',
            'vattype.Percentage',
            'accountref.Reference'
        );
        return $query->get();
    }
    private function consolidateReport($collection)
    {
        return $collection
            ->groupBy('Account_Ref_ID')
            ->map(function ($group) {
                $amount = $group->sum('Amount');
                $percentage = $group->first()->Percentage ?? 0;
                // Calculate VAT
                $vatAmount = ($percentage > 0) ? ($amount * ($percentage / 100)) : 0;
                // Total including VAT
                $totalWithVat = $amount + $vatAmount;
                // If Amount is inclusive of VAT and you want to extract VAT:
                $netOfVat = ($percentage > 0) ? ($amount / (1 + ($percentage / 100))) : $amount;
                return (object) [
                    'Account_Ref_ID' => $group->first()->Account_Ref_ID,
                    'Description' => $group->first()->Description,
                    'Paid_In_Out' => $group->first()->Paid_In_Out,
                    'Percentage' => $percentage,
                    'Amount' => $amount,
                    'VatAmount' => $vatAmount,
                    'TotalWithVat' => $totalWithVat,
                    'NetOfVat' => $netOfVat, // Net value after VAT deduction
                ];
            })
            ->values(); // reset keys
    }
    public function getVatIncomeHead($date_from, $date_to, $clientId)
    {
        return Transaction::selectRaw('
            MAX(transaction.Transaction_ID) as Transaction_ID,
            MAX(transaction.Transaction_Date) as Transaction_Date,
            MAX(transaction.File_ID) as File_ID,
            MAX(transaction.Bank_Account_ID) as Bank_Account_ID,
            MAX(transaction.Paid_In_Out) as Paid_In_Out,
            MAX(transaction.Payment_Type_ID) as Payment_Type_ID,
            MAX(file.Ledger_Ref) as Ledger_Ref,
            MAX(vattype.Percentage) as Percentage,
            MAX(transaction.Description) as Description,
            MAX(accountref.Reference) as Reference,
            SUM(transaction.Amount) as Amount
        ')
            ->join('file', 'file.File_ID', '=', 'transaction.File_ID')
            ->leftJoin('vattype', 'vattype.VAT_ID', '=', 'transaction.VAT_ID')
            ->join('accountref', 'accountref.Account_Ref_ID', '=', 'transaction.Account_Ref_ID')
            ->where('transaction.Is_Imported', 1)
            ->whereHas('file', function ($query) use ($clientId) {
                $query->where('Client_ID', $clientId);
            })
            ->whereIn('transaction.Account_Ref_ID', [101, 99])
            ->whereBetween('transaction.Transaction_Date', [$date_from, $date_to])
            ->groupBy('transaction.Transaction_ID', 'transaction.Transaction_Date', 'transaction.File_ID', 'transaction.Bank_Account_ID', 'transaction.Paid_In_Out')
            ->get();
    }



public function generatePdf(Request $request)
{

    $clientId = auth()->user()->Client_ID ?? $request->input('client_id');
    $clientInfo = Client::find($clientId);

    $fromDate = $request->input('from_date') ? Carbon::parse($request->input('from_date'))->toDateString() : null;
    $toDate = $request->input('to_date') ? Carbon::parse($request->input('to_date'))->toDateString() : null;

    $reportData = [
        'vat' => $this->getVatIncomeHead($fromDate, $toDate, $clientId, 'vat'),
        'income' => $this->fetchProfitAndLoss($fromDate, $toDate, $clientId, 'income'),
        'cost' => $this->fetchProfitAndLoss($fromDate, $toDate, $clientId, 'cost'),
        'expense' => $this->fetchProfitAndLoss($fromDate, $toDate, $clientId, 'expense'),
        'interestReceived' => $this->fetchProfitAndLoss($fromDate, $toDate, $clientId, 'InterestRecieved'),
        'interestPaid' => $this->fetchProfitAndLoss($fromDate, $toDate, $clientId, 'InterestPaid'),
        'bill' => $this->fetchProfitAndLoss($fromDate, $toDate, $clientId, 'bill'),
    ];

    $reportData = collect($reportData)->map(function ($collection) {
        $consolidated = $this->consolidateReport($collection);
        $netOfVatSum = $consolidated->sum('NetOfVat');
        return [
            'data' => $consolidated,
            'netOfVatSum' => $netOfVatSum,
        ];
    });

    $pdf = Pdf::loadView('admin.reports.pdf.profit_and_loos_pdf', compact('reportData', 'fromDate', 'toDate', 'clientInfo'));
    return $pdf->download('ProfitAndLossReport.pdf');
}
}
