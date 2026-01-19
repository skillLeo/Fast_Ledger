<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\File;
use App\Models\VatType;
use App\Models\AccountRef;
use App\Models\BankAccount;
use App\Models\PaymentType;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\DataTables\TransactionDataTable;
use App\Http\Requests\StoreTransactionRequest;
use App\Models\Client;
use Barryvdh\DomPDF\Facade\Pdf;

class TransactionController extends Controller
{
    public function index()
    {
        $currentClientId = auth()->user()->Client_ID;
        $data = $this->getFilterData($currentClientId);

        // Create DataTable instance with filter data
        $dataTable = new TransactionDataTable();
        $dataTable->bankSelectHTML = $this->buildBankAccountSelect($data['bankAccounts']);
        $dataTable->paidInOutSelectHTML = $this->buildPaidInOutSelect();
        $dataTable->paymentTypeSelectHTML = $this->buildPaymentTypeSelect($data['paymentTypes']);
        $dataTable->accountRefSelectHTML = $this->buildAccountRefSelect($data['accountRefs']);

        return $dataTable->render('admin.transaction_report.transaction_report', [
            'bankAccounts' => $data['bankAccounts'],
            'paymentTypes' => $data['paymentTypes'],
            'accountRefs' => $data['accountRefs']
        ]);
    }

    public function getLedgerRefsForAutocomplete(Request $request)
    {
        $query = $request->input('query', '');
        $clientId = auth()->user()->Client_ID;

        $files = File::where('Client_ID', $clientId)
            ->where('Ledger_Ref', 'LIKE', "%{$query}%")
            ->select('Ledger_Ref as ledger_ref') // Make sure the field name matches what JS expects
            ->distinct()
            ->limit(15)
            ->get();

        return response()->json($files);
    }

    public function getReferencesForAutocomplete(Request $request)
    {
        $query = $request->input('query', '');
        
        $references = AccountRef::where('Reference', 'LIKE', "%{$query}%")
            ->select('Reference as reference') // Make sure the field name matches what JS expects
            ->distinct()
            ->limit(15)
            ->get();

        return response()->json($references);
    }

    private function getFilterData($currentClientId)
    {
        return [
            'bankAccounts' => BankAccount::with('bankAccountType')
                ->where('Client_ID', $currentClientId)
                ->where('Is_Deleted', 0)
                ->get(),

            'paymentTypes' => PaymentType::select('Payment_Type_ID', 'Payment_Type_Name')
                ->distinct()
                ->orderBy('Payment_Type_Name')
                ->get(),

            'accountRefs' => AccountRef::select('Account_Ref_ID', 'Reference')
                ->distinct()
                ->orderBy('Reference')
                ->get()
        ];
    }

    private function buildBankAccountSelect($bankAccounts)
    {
        $options = collect($bankAccounts)->map(function ($account) {
            $label = $account->Account_Name . ' (' . ($account->bankAccountType->Bank_Type ?? 'N/A') . ')';
            return '<option value="' . $account->Bank_Account_ID . '">' . htmlentities($label) . '</option>';
        })->implode('');

        return '<option value="">All</option>' . $options;
    }

    private function buildPaidInOutSelect()
    {
        return '<option value="">All</option>' .
            '<option value="1">Paid In</option>' .
            '<option value="2">Paid Out</option>';
    }

    private function buildPaymentTypeSelect($paymentTypes)
    {
        $options = collect($paymentTypes)->map(function ($paymentType) {
            return '<option value="' . $paymentType->Payment_Type_ID . '">' .
                htmlentities($paymentType->Payment_Type_Name) . '</option>';
        })->implode('');

        return '<option value="">All</option>' . $options;
    }

    private function buildAccountRefSelect($accountRefs)
    {
        $options = collect($accountRefs)->map(function ($accountRef) {
            return '<option value="' . $accountRef->Account_Ref_ID . '">' .
                htmlentities($accountRef->Reference) . '</option>';
        })->implode('');

        return '<option value="">All</option>' . $options;
    }
  public function importdata(Request $request)
{
    $ids = $request->input('selected_ids');

    try {
        Transaction::whereIn('Transaction_ID', $ids)->update(['Is_Imported' => 1]);

        return response()->json([
            'success' => true,
            'redirect_url' => route('transactions.imported')
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to import transactions: ' . $e->getMessage()
        ]);
    }
}



    
    public function downloadtransactionpdf(Request $request)
    {
        $clientId = auth()->user()->Client_ID;
        $getclient = Client::where('Client_ID', $clientId)->first();
        $client_name = $getclient->Business_Name;

        $transactions = Transaction::with([
            'file.client',
            'bankAccount.bankAccountType',
            'paymentType',
            'accountRef',
            'vatType',
        ])
            ->whereHas('file.client', function ($query) use ($clientId) {
                $query->where('Client_ID', $clientId);
            })
            ->where('Is_Imported', 1)
            ->whereNull('Deleted_On')
            ->orderByDesc('Transaction_Date')
            ->get();

        $pdf = Pdf::loadView('admin.pdf.transactionpdf', compact('transactions', 'client_name'));
        return $pdf->download('daybook_report.pdf');
    }

    public function destroy($id)
    {
        $transaction = Transaction::findOrFail($id);

        try {
            $transaction->delete();
            return redirect()->back()->with('success', 'Transaction deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete transaction: ' . $e->getMessage());
        }
    }
    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids');

        if (empty($ids) || !is_array($ids)) {
            return response()->json(['message' => 'Invalid request.'], 400);
        }

        try {
            Transaction::whereIn('Transaction_ID', $ids)->update(['Deleted_On' => now()]);
            return response()->json(['message' => 'Selected transactions deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error deleting transactions: ' . $e->getMessage()], 500);
        }
    }


}
