<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\User;
use App\Models\Client;
use App\Models\Matter;
use App\Models\Country;
use App\Models\Supplier;
use App\Models\SubMatter;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\DataTables\FileDataTable;
use App\Http\Requests\FileRequest;
use App\Models\Employees\Employee;
use Illuminate\Support\Facades\Auth;


class FileController extends Controller
{
    public function index()
    {
        $userClientId = Auth::user()->Client_ID;

        // Get all files (ledgers) for the current user
        $files = File::where('Client_ID', $userClientId)->get()->map(function ($file) {
            // Get transactions for this file
            $transactions = Transaction::where('File_ID', $file->File_ID)->get();

            $client_balance = 0;
            $office_balance = 0;

            foreach ($transactions as $transaction) {
                // Client Account (Bank_Account_ID = 22)
                if ($transaction->Bank_Account_ID == 22 && $transaction->Is_Imported == 1) {
                    if ($transaction->Paid_In_Out == 1) {
                        $client_balance += $transaction->Amount;
                    } elseif ($transaction->Paid_In_Out == 2) {
                        $client_balance -= $transaction->Amount;
                    }
                }

                // Office Account (Bank_Account_ID = 23)
                if ($transaction->Bank_Account_ID == 23 && $transaction->Is_Imported == 1) {
                    if ($transaction->Paid_In_Out == 1) {
                        $office_balance += $transaction->Amount;
                    } elseif ($transaction->Paid_In_Out == 2) {
                        $office_balance -= $transaction->Amount;
                    }
                }
            }

            // Add calculated balances to the file object
            $file->client_balance = $client_balance;
            $file->office_balance = $office_balance;

            return $file;
        });

        // Get matters
        $matters = Matter::with('submatters')->get();

        // Get statuses
        $statuses = File::where('Client_ID', $userClientId)
            ->select('status')
            ->distinct()
            ->pluck('status');

        // Get employees for the current client
        $employees = Employee::where('client_id', $userClientId)
            ->active() // Using your scope
            ->get()
            ->map(function ($employee) {
                // Calculate balance for each employee
                $employee->balance = $this->calculateEmployeeBalance($employee->id);
                return $employee;
            });

        $suppliers = Supplier::where('user_id', Auth::id())
            ->get()
            ->map(function ($supplier) {
                // Calculate balance for each supplier
                $supplier->balance = $this->calculateSupplierBalance($supplier->id);
                return $supplier;
            });
        // dd($employees);

        return view('admin.file_opening_book.index', compact('files', 'matters', 'statuses', 'employees', 'suppliers'));
    }



    public function getLedgerData(Request $request)
    {
        $ledger_ref = $request->input('ledger_ref');
        $userClientId = Auth::user()->Client_ID;

        // Get client details
        $client_data = Client::where('Client_ID', $userClientId)->first();
        $Client_Ref = $client_data->Client_Ref ?? 'N/A';

        // Get file details for the specific ledger
        $file_data = File::where('Ledger_Ref', $ledger_ref)
            ->where('Client_ID', $userClientId)
            ->first();

        if (!$file_data) {
            return response()->json(['error' => 'File not found'], 404);
        }

        // Get transactions for this file
        $transaction_data = Transaction::where('File_ID', $file_data->File_ID)->get();

        $client_balance = 0;
        $office_balance = 0;
        $results = [];

        foreach ($transaction_data as $transaction) {
            $TransactionDate = Carbon::parse($transaction->Transaction_Date)->format('d/m/Y');
            $description = $transaction->Description;
            $Cheque = $transaction->Cheque;

            $client_Credit = 0;
            $client_Debit = 0;
            $office_Credit = 0;
            $office_Debit = 0;

            // Client Account (Bank_Account_ID = 22)
            if ($transaction->Bank_Account_ID == 22 && $transaction->Is_Imported == 1) {
                if ($transaction->Paid_In_Out == 1) {
                    $client_Credit = $transaction->Amount;
                    $client_balance += $transaction->Amount;
                } elseif ($transaction->Paid_In_Out == 2) {
                    $client_Debit = $transaction->Amount;
                    $client_balance -= $transaction->Amount;
                }
            }

            // Office Account (Bank_Account_ID = 23)
            if ($transaction->Bank_Account_ID == 23 && $transaction->Is_Imported == 1) {
                if ($transaction->Paid_In_Out == 1) {
                    $office_Credit = $transaction->Amount;
                    $office_balance += $transaction->Amount;
                } elseif ($transaction->Paid_In_Out == 2) {
                    $office_Debit = $transaction->Amount;
                    $office_balance -= $transaction->Amount;
                }
            }

            $row = [
                'TransactionDate' => $TransactionDate,
                'Description' => $description,
                'Cheque' => $Cheque,
                'Client_Debit' => $client_Debit,
                'Client_Credit' => $client_Credit,
                'Client_Balance' => $client_balance,
                'Office_Debit' => $office_Debit,
                'Office_Credit' => $office_Credit,
                'Office_Balance' => $office_balance,
            ];

            $results[] = $row;
        }

        return response()->json([
            'Client_Ref' => $Client_Ref,
            'file_data' => $file_data,
            'transactions' => $results,
        ]);
    }


    public function filterByMatter(Request $request)
    {
        $userClientId = Auth::user()->Client_ID;
        $matterId = $request->input('matter_id');

        $query = File::where('Client_ID', $userClientId);

        if ($matterId && $matterId !== 'all') {
            $query->where('Matter', $matterId);
        }

        $files = $query->get()->map(function ($file) {
            // Get transactions for this file
            $transactions = Transaction::where('File_ID', $file->File_ID)->get();

            $client_balance = 0;
            $office_balance = 0;

            foreach ($transactions as $transaction) {
                // Client Account (Bank_Account_ID = 22)
                if ($transaction->Bank_Account_ID == 22 && $transaction->Is_Imported == 1) {
                    if ($transaction->Paid_In_Out == 1) {
                        $client_balance += $transaction->Amount;
                    } elseif ($transaction->Paid_In_Out == 2) {
                        $client_balance -= $transaction->Amount;
                    }
                }

                // Office Account (Bank_Account_ID = 23)
                if ($transaction->Bank_Account_ID == 23 && $transaction->Is_Imported == 1) {
                    if ($transaction->Paid_In_Out == 1) {
                        $office_balance += $transaction->Amount;
                    } elseif ($transaction->Paid_In_Out == 2) {
                        $office_balance -= $transaction->Amount;
                    }
                }
            }

            $file->client_balance = $client_balance;
            $file->office_balance = $office_balance;

            return $file;
        });

        return response()->json([
            'files' => $files
        ]);
    }


    // New method to get filter suggestions
    public function getFilterSuggestions(Request $request)
    {
        $field = $request->get('field');
        $term = $request->get('term', '');
        $userClientId = auth()->user()->Client_ID;

        $suggestions = [];

        switch ($field) {
            case 'ledger_ref':
                $suggestions = File::where('Client_ID', $userClientId)
                    ->where('Ledger_Ref', 'LIKE', "%{$term}%")
                    ->distinct()
                    ->pluck('Ledger_Ref')
                    ->filter()
                    ->take(10)
                    ->values()
                    ->toArray();
                break;

            case 'matter':
                $suggestions = File::where('Client_ID', $userClientId)
                    ->where('Matter', 'LIKE', "%{$term}%")
                    ->distinct()
                    ->pluck('Matter')
                    ->filter()
                    ->take(10)
                    ->values()
                    ->toArray();
                break;

            case 'name':
                $suggestions = File::where('Client_ID', $userClientId)
                    ->where(function ($query) use ($term) {
                        $query->where('First_Name', 'LIKE', "%{$term}%")
                            ->orWhere('Last_Name', 'LIKE', "%{$term}%")
                            ->orWhereRaw("CONCAT(First_Name, ' ', Last_Name) LIKE ?", ["%{$term}%"]);
                    })
                    ->distinct()
                    ->selectRaw("CONCAT(First_Name, ' ', Last_Name) as full_name")
                    ->pluck('full_name')
                    ->filter()
                    ->take(10)
                    ->values()
                    ->toArray();
                break;

            case 'address':
                $suggestions = File::where('Client_ID', $userClientId)
                    ->where('Address1', 'LIKE', "%{$term}%")
                    ->distinct()
                    ->pluck('Address1')
                    ->filter()
                    ->take(10)
                    ->values()
                    ->toArray();
                break;

            case 'post_code':
                $suggestions = File::where('Client_ID', $userClientId)
                    ->where('Post_Code', 'LIKE', "%{$term}%")
                    ->distinct()
                    ->pluck('Post_Code')
                    ->filter()
                    ->take(10)
                    ->values()
                    ->toArray();
                break;

            case 'fee_earner':
                $suggestions = File::where('Client_ID', $userClientId)
                    ->where('Fee_Earner', 'LIKE', "%{$term}%")
                    ->distinct()
                    ->pluck('Fee_Earner')
                    ->filter()
                    ->take(10)
                    ->values()
                    ->toArray();
                break;
        }

        return response()->json($suggestions);
    }

    public function trashed(FileTrashedrecode $dataTable)
    {
        return $dataTable->render('admin.file_opening_book.trashed');
    }

    public function downloadPDF(Request $request)
    {
        // Fetch data based on date filter
        $query = File::query();
        $matters = Matter::all();

        $submatters = SubMatter::all();
        $countries = Country::all();
        $files = $query->where('Client_ID', auth()->user()->Client_ID)->get();

        $pdf = Pdf::loadView('admin.pdf.files', compact('files'));

        return $pdf->download('Files_Report.pdf');
    }


    public function getdata($id)
    {
        $file = File::findOrFail($id);
        $matters = Matter::all();

        $submatters = SubMatter::all();
        $countries = Country::all();
        $feeEarnerList = User::where('Client_ID', $file->Client_ID)->where('User_Role', '=', 2)->get();

        return view('admin.file_opening_book.view', compact('file', 'feeEarnerList', 'countries', 'matters', 'submatters'));
    }

    public function create()
    {
        $countries = Country::all();
        $matters = Matter::all();
        $submatters = SubMatter::all();

        return view('admin.file_opening_book.create', compact('countries', 'matters', 'submatters'));
    }

    public function update_file_recode(FileRequest $request)
    {

        $data = $request->validated();
        foreach (['File_Date', 'Date_Of_Birth', 'Key_Date'] as $field) {
            if (!empty($data[$field])) {
                try {
                    $data[$field] = \Carbon\Carbon::parse($data[$field])->format('Y-m-d');
                } catch (\Exception $e) {
                    return back()->withErrors([$field => "The $field format is invalid."]);
                }
            }
        }

        $file_id = $request->File_ID;

        $file = File::find($file_id);
        if ($file) {
            $file->update($data);
            return redirect()->route('files.index')->with('success', 'File Updated successfully.');
        } else {
            return back()->withErrors(['File_ID' => 'File not found.']);
        }
    } //  


    public function store(FileRequest $request)
    {
        $data = $request->validated();

        foreach (['File_Date', 'Date_Of_Birth', 'Key_Date'] as $field) {
            if (!empty($data[$field])) {
                try {
                    $data[$field] = \Carbon\Carbon::parse($data[$field])->format('Y-m-d');
                } catch (\Exception $e) {
                    return back()->withErrors([$field => "The $field format is invalid."]);
                }
            }
        }


        // Add additional fields and save
        $user = Auth::user();

        $data['Client_ID'] = $user->Client_ID;

        $data['Created_By'] = Auth::id();
        $data['Created_On'] = now();

        $file = File::create($data);

        return redirect()->route('files.index')->with('success', 'File created successfully.');
    }

    public function softDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:file,File_ID',
        ]);

        File::whereIn('File_ID', $request->ids)->delete();


        return response()->json(['message' => 'Selected records soft deleted successfully.']);
    }
    public function restore(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:file,File_ID',
        ]);

        File::onlyTrashed()
            ->whereIn('File_ID', $request->ids)
            ->restore();

        return response()->json(['message' => 'Selected records have been restored.']);
    }

    public function forceDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:file,File_ID',
        ]);

        File::onlyTrashed()
            ->whereIn('File_ID', $request->ids)
            ->forceDelete();

        return response()->json(['message' => 'Selected records have been permanently deleted.']);
    }
    public function destroy(Request $request)
    {
        $id = $request->id; // Get the file ID
        $record = File::findOrFail($id);
        $record->delete();

        return response()->json([
            'success' => true,
            'message' => 'Record deleted successfully!'
        ]);
    }


    public function updateStatus(Request $request)
    {
        $file = File::find($request->File_ID);
        if ($file) {
            $file->Status = $request->status;
            $file->save();

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'File not found.']);
    }


    public function getFileData(Request $request)
    {
        $fileId = $request->input('id');
        $fileData = File::find($fileId);

        if ($fileData) {
            return response()->json([
                'success' => true,
                'data' => $fileData
            ]);
        }

        return response()->json(['success' => false]);
    }


    /**
     * Get employee data with balance calculation
     */
    public function getEmployeeData(Request $request)
    {
        $employeeId = $request->input('employee_id');
        $userClientId = Auth::user()->Client_ID;

        $employee = Employee::where('id', $employeeId)
            ->where('client_id', $userClientId)
            ->first();

        if (!$employee) {
            return response()->json(['error' => 'Employee not found'], 404);
        }

        $balance = $this->calculateEmployeeBalance($employeeId);
        $transactions = $this->getEmployeeTransactions($employeeId);

        return response()->json([
            'employee' => [
                'id' => $employee->id,
                'full_name' => $employee->full_name,
                'job_title' => $employee->job_title,
                'primary_phone' => $employee->primary_phone,
                'email' => $employee->email,
                'date_of_birth' => $employee->date_of_birth
                    ? Carbon::parse($employee->date_of_birth)->format('d/m/Y')
                    : '-',
                'ni_number' => $employee->ni_number,
            ],
            'employee_full' => $employee->toArray(), // Full employee data for form
            'balance' => number_format($balance, 2),
            'transactions' => $transactions
        ]);
    }

    /**
     * Calculate employee balance
     */
    private function calculateEmployeeBalance($employeeId)
    {
        // TODO: Replace with actual transaction calculation
        // This is a placeholder - adjust based on your transaction model
        return 5250.00; // Mock balance
    }

    /**
     * Get employee transactions
     */
    private function getEmployeeTransactions($employeeId)
    {
        // TODO: Replace with actual transaction query when you have employee transactions table
        // For now returning mock data
        return [
            [
                'reference' => 'SAL001',
                'date' => '2025-01-31',
                'due_date' => '2025-01-31',
                'description' => 'Monthly Salary',
                'debit' => '0.00',
                'credit' => '4,500.00',
                'balance' => '4,500.00',
            ],
            [
                'reference' => 'EXP001',
                'date' => '2025-01-15',
                'due_date' => '2025-01-20',
                'description' => 'Travel Expenses',
                'debit' => '250.00',
                'credit' => '0.00',
                'balance' => '4,250.00',
            ],
        ];
    }


    /**
     * Get supplier data with balance calculation
     */
    public function getSupplierData(Request $request)
    {
        
        $supplierId = $request->input('supplier_id');
        $userId = Auth::id();

        $supplier = Supplier::where('id', $supplierId)
            ->where('user_id', $userId)
            ->first();

        if (!$supplier) {
            return response()->json(['error' => 'Supplier not found'], 404);
        }

        $balance = $this->calculateSupplierBalance($supplierId);
        $transactions = $this->getSupplierTransactions($supplierId);

        return response()->json([
            'supplier' => [
                'id' => $supplier->id,
                'contact_name' => $supplier->contact_name,
                'account_number' => $supplier->account_number,
                'phone' => $supplier->phone,
                'email' => $supplier->email,
                'billing_address' => $supplier->billing_address,
                'website' => $supplier->website,
                'vat_number' => $supplier->vat_number,
                'payment_terms' => $supplier->payment_terms,
                'status' => $supplier->status,
            ],
            'supplier_full' => $supplier->toArray(), // Full supplier data for form
            'balance' => number_format($balance, 2),
            'transactions' => $transactions
        ]);
    }

    /**
     * Calculate supplier balance
     */
    private function calculateSupplierBalance($supplierId)
    {
        // TODO: Replace with actual transaction calculation
        // This is a placeholder - adjust based on your transaction model
        return 2850.00; // Mock balance
    }

    /**
     * Get supplier transactions
     */
    private function getSupplierTransactions($supplierId)
    {
        // TODO: Replace with actual transaction query when you have supplier transactions table
        // For now returning mock data
        return [
            [
                'reference' => 'INV001',
                'date' => '2025-01-15',
                'due_date' => '2025-02-15',
                'description' => 'Legal Research Services',
                'debit' => '1,500.00',
                'credit' => '0.00',
                'balance' => '1,500.00',
            ],
            [
                'reference' => 'PAY001',
                'date' => '2025-01-20',
                'due_date' => '2025-01-20',
                'description' => 'Payment Received',
                'debit' => '0.00',
                'credit' => '1,500.00',
                'balance' => '0.00',
            ],
        ];
    }
}
