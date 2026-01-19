<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\BankAccount;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\BankReconciliationDetail;

class TransactionChequeController extends Controller
{

//     public function index()
// {
//     // Step 1: Get current user's bank account
//     $user  = auth()->user()->Client_ID;
//     $clientInfo = Client::find($user);

// // dd($clientInfo);
//     $bankAccounts = BankAccount::with('bankAccountType')
//         ->where('Client_ID', $user)
//         ->where('Is_Deleted', 0)
//         ->first();

//     if (!$bankAccounts) {
//         return back()->with('error', 'Bank account not found for current user.');
//     }

//     // Step 2: Fetch filtered transactions with aggregation and percentage
//     $transactions = Transaction::selectRaw('
//             MAX(transaction.Transaction_ID) as Transaction_ID,
//             MAX(transaction.Transaction_Date) as Transaction_Date,
//             MAX(transaction.File_ID) as File_ID,
//             MAX(transaction.Bank_Account_ID) as Bank_Account_ID,
//             MAX(transaction.Paid_In_Out) as Paid_In_Out,
//             MAX(transaction.Payment_Type_ID) as Payment_Type_ID,
//             MAX(file.Ledger_Ref) as Ledger_Ref,
//             MAX(vattype.Percentage) as Percentage,
//             MAX(transaction.Description) as Description,
//             MAX(accountref.Reference) as Reference,
//             SUM(transaction.Amount) as Amount
//         ')
//         ->join('file', 'file.File_ID', '=', 'transaction.File_ID')
//         ->leftJoin('vattype', 'vattype.VAT_ID', '=', 'transaction.VAT_ID')
//         ->join('accountref', 'accountref.Account_Ref_ID', '=', 'transaction.Account_Ref_ID')
//         ->where('transaction.Bank_Account_ID', $bankAccounts->Bank_Account_ID)
//         ->where('transaction.Paid_In_Out', 2)
//         ->where('transaction.Payment_Type_ID', 17)
//         ->where('transaction.Account_Ref_ID', 92)
//         ->groupBy(
//             'transaction.Transaction_ID',
//             'transaction.Transaction_Date',
//             'transaction.File_ID',
//             'transaction.Bank_Account_ID',
//             'transaction.Paid_In_Out',
//             'transaction.Payment_Type_ID',
//             'transaction.Account_Ref_ID'
//         )
//         ->get();
//   return view('admin.transaction_report.transaction_cheque', compact('transactions'));
// }



// public function saveBankCheque(Request $request)
// {
//     // Validate incoming data
//     $validated = $request->validate([
//         'amount' => 'required|numeric',
//         'transaction_date' => 'required|date',
//     ]);

//     // Create new record
//     BankReconciliation::create([
//         'Amount' => $validated['amount'],
//         'Transaction_Date' => $validated['transaction_date'],
//         'User_ID' => auth()->id(), // logged-in user ID
//     ]);

//     return redirect()->back()->with('success', 'Bank reconciliation saved successfully!');
// }

 public function index()
    {
        $user = auth()->user()->Client_ID;

        $bankAccount = BankAccount::with('bankAccountType')
            ->where('Client_ID', $user)
            ->where('Is_Deleted', 0)
            ->first();

        if (!$bankAccount) {
            return back()->with('error', 'Bank account not found for current user.');
        }

        $transactions = Transaction::with('bankReconciliation')
            ->join('file', 'file.File_ID', '=', 'transaction.File_ID')
            ->leftJoin('vattype', 'vattype.VAT_ID', '=', 'transaction.VAT_ID')
            ->join('accountref', 'accountref.Account_Ref_ID', '=', 'transaction.Account_Ref_ID')
            ->where('transaction.Bank_Account_ID', $bankAccount->Bank_Account_ID)
            ->where('transaction.Paid_In_Out', 2)
            ->where('transaction.Payment_Type_ID', 17)
            ->where('transaction.Account_Ref_ID', 92)
            ->active()
            ->select(
                'transaction.*',
                'file.Ledger_Ref',
                'vattype.Percentage',
                'accountref.Reference'
            )
            ->get();

        return view('admin.transaction_report.transaction_cheque', compact('transactions'));
    }



    public function saveBankCheque(Request $request)
    {
        // dd($request->all());

        $validated = $request->validate([
            'transaction_id' => 'required|numeric|exists:transaction,Transaction_ID',
            'amount' => 'required|numeric',
            'transaction_date' => 'required|date',
            'transaction_type' => 'required|in:2', // restrict to type 2 for cheques
        ]);
        // dd($validated);
        BankReconciliationDetail::create([
            'Transaction_ID' => $validated['transaction_id'],
            'Add_Type' => $validated['transaction_type'], // 2 for cheque
            'Amount' => $validated['amount'],
            'Chq_Date' => $validated['transaction_date'],
            // 'Created_By' => auth()->id(),
            // 'Created_On' => now(),
        ]);

        return redirect()->back()->with('success', 'Cheque saved in bank reconciliation!');
    }

}
