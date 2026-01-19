<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\VatFormLabel;
use Illuminate\Http\Request;
use App\Models\ChartOfAccount;
use App\Models\VatType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ChartsOfAccountController extends Controller
{
    public function index()
    {
        try {
            // 1) Page accounts (no joins)
            $accounts = ChartOfAccount::query()
                ->active()
                ->select(['id', 'ledger_ref', 'account_ref', 'vat_id', 'description', 'pl_bs'])
                ->orderBy('ledger_ref')
                ->orderBy('account_ref')
                ->paginate(250);

            $ids = $accounts->getCollection()->pluck('id');

            if ($ids->isNotEmpty()) {

                $balances = Transaction::query()
                    ->join('chart_of_accounts as coa', 'coa.id', '=', 'transaction.chart_of_account_id')
                    ->whereIn('transaction.chart_of_account_id', $ids)
                    ->select('transaction.chart_of_account_id', DB::raw("
                    SUM(
                    CASE
                        -- PAY/CHQ (Money OUT) - Always DEBIT entry
                        WHEN LEFT(transaction.Transaction_Code, 3) IN ('PAY','CHQ') THEN
                            CASE WHEN UPPER(COALESCE(coa.normal_balance,'')) = 'DR'
                                THEN transaction.Amount      -- DR accounts: Debit increases balance
                                ELSE -transaction.Amount     -- CR accounts: Debit decreases balance
                            END

                        -- REC (Money IN) - Always CREDIT entry
                        WHEN LEFT(transaction.Transaction_Code, 3) = 'REC' THEN
                            CASE WHEN UPPER(COALESCE(coa.normal_balance,'')) = 'DR'  
                                THEN -transaction.Amount     -- DR accounts: Credit decreases balance
                                ELSE transaction.Amount      -- CR accounts: Credit increases balance
                            END

                        -- Other transaction types remain standard
                        WHEN LEFT(transaction.Transaction_Code,3) = 'SIN' THEN transaction.Amount
                        WHEN LEFT(transaction.Transaction_Code,3) = 'SCN' THEN -transaction.Amount
                        WHEN LEFT(transaction.Transaction_Code,3) = 'PUR' THEN transaction.Amount
                        WHEN LEFT(transaction.Transaction_Code,3) = 'PUC' THEN -transaction.Amount

                        -- Entry type fallbacks
                        WHEN UPPER(transaction.entry_type) = 'DR' THEN
                            CASE WHEN UPPER(COALESCE(coa.normal_balance,'')) = 'DR'
                                THEN transaction.Amount      
                                ELSE -transaction.Amount     
                            END
                            
                        WHEN UPPER(transaction.entry_type) = 'CR' THEN
                            CASE WHEN UPPER(COALESCE(coa.normal_balance,'')) = 'CR'
                                THEN transaction.Amount      
                                ELSE -transaction.Amount     
                            END
                            
                        ELSE transaction.Amount
                    END
                    ) AS balance
                "))
                    ->groupBy('transaction.chart_of_account_id')
                    ->pluck('balance', 'transaction.chart_of_account_id');

                // 3) Attach balance to each model
                $accounts->getCollection()->transform(function ($acc) use ($balances) {
                    $acc->balance = (float) ($balances[$acc->id] ?? 0);
                    return $acc;
                });

                // 4) Eager load VAT — use actual column names present in your VatType
                // Relationship: belongsTo(VatType::class, 'VAT_ID', 'vat_id') in your model snippet.
                // Select fields you will show in Blade: VAT_Name, Percentage (adjust if your columns differ)
                $accounts->getCollection()->load(['vatType:VAT_ID,VAT_Name,Percentage']);
            }

            // 5) Group by ledger_ref for sections
            $groupedAccounts = $accounts->getCollection()->groupBy('ledger_ref');

            return view('admin.charts_of_accounts.index', compact('accounts', 'groupedAccounts'));
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['error' => $e->getMessage()], 500); // TEMP
        }
    }



    public function getTransactions($id)
    {
        try {
            $account = ChartOfAccount::with(['transactions' => function ($q) {
                $q->orderBy('Transaction_Date', 'asc');
            }])->findOrFail($id);

            $running = 0.0;
            $normalBalance = strtoupper((string) ($account->normal_balance ?? 'DR'));

            $transactions = $account->transactions->map(function ($txn) use (&$running, $normalBalance) {
                $amount     = (float) ($txn->Amount ?? 0);
                $abs        = abs($amount);
                $code       = (string) ($txn->Transaction_Code ?? '');
                $prefix     = strtoupper(substr($code, 0, 3));
                $entryType  = strtoupper((string) ($txn->entry_type ?? ''));

                $debit = 0.0;
                $credit = 0.0;
                $side = null;

                switch ($prefix) {
                    case 'PAY':
                    case 'CHQ':
                        // Money OUT - Always DEBIT entry
                        $debit = $abs;               // Always show in Expense(Debit) column
                        $side = 'DR';

                        if ($normalBalance === 'DR') {
                            $running += $abs;        // DR accounts: Debit INCREASES balance
                        } else {
                            $running -= $abs;        // CR accounts: Debit DECREASES balance
                        }
                        break;

                    case 'REC':
                        // Money IN - Always CREDIT entry
                        $credit = $abs;              // Always show in Income(Credit) column
                        $side = 'CR';

                        if ($normalBalance === 'DR') {
                            $running -= $abs;        // DR accounts: Credit DECREASES balance
                        } else {
                            $running += $abs;        // CR accounts: Credit INCREASES balance
                        }
                        break;

                    case 'SIN':                      // Sales Invoice → Credit
                        $credit = $abs;
                        $side = 'CR';
                        if ($normalBalance === 'CR') {
                            $running += $abs;
                        } else {
                            $running -= $abs;
                        }
                        break;

                    case 'SCN':                      // Sales Credit Note → Debit  
                        $debit = $abs;
                        $side = 'DR';
                        if ($normalBalance === 'DR') {
                            $running += $abs;
                        } else {
                            $running -= $abs;
                        }
                        break;

                    case 'PUR':                      // Purchase Invoice → Debit
                        $debit = $abs;
                        $side = 'DR';
                        if ($normalBalance === 'DR') {
                            $running += $abs;
                        } else {
                            $running -= $abs;
                        }
                        break;

                    case 'PUC':                      // Purchase Credit → Credit
                        $credit = $abs;
                        $side = 'CR';
                        if ($normalBalance === 'CR') {
                            $running += $abs;
                        } else {
                            $running -= $abs;
                        }
                        break;

                    default:                         // Fallback to entry_type
                        if ($entryType === 'CR') {
                            $credit = $abs;
                            $side = 'CR';
                            if ($normalBalance === 'CR') {
                                $running += $abs;
                            } else {
                                $running -= $abs;
                            }
                        } elseif ($entryType === 'DR') {
                            $debit = $abs;
                            $side = 'DR';
                            if ($normalBalance === 'DR') {
                                $running += $abs;
                            } else {
                                $running -= $abs;
                            }
                        } else {
                            if ($amount >= 0) {
                                $credit = $abs;
                                $side = 'CR';
                                if ($normalBalance === 'CR') {
                                    $running += $abs;
                                } else {
                                    $running -= $abs;
                                }
                            } else {
                                $debit = $abs;
                                $side = 'DR';
                                if ($normalBalance === 'DR') {
                                    $running += $abs;
                                } else {
                                    $running -= $abs;
                                }
                            }
                        }
                }

                return [
                    'date'             => $txn->Transaction_Date ?? '—',
                    'reference'        => $code ?: '—',
                    'details'          => $txn->Description ?? '—',
                    'debit'            => $debit > 0 ? $debit : '',
                    'credit'           => $credit > 0 ? $credit : '',
                    'dr_cr'            => $side ?? ($entryType ?: ''),
                    'running_balance'  => $running,
                ];
            });

            return response()->json([
                'transactions' => $transactions,
                'balance'      => $running,
            ]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['error' => 'Could not fetch transactions'], 500);
        }
    }
    // public function getTransactions($id)
    // {
    //     try {
    //         $account = ChartOfAccount::with(['transactions' => function ($q) {
    //             $q->orderBy('Transaction_Date', 'asc');
    //         }])->findOrFail($id);

    //         $running = 0.0;

    //         $transactions = $account->transactions->map(function ($txn) use (&$running) {
    //             $amount     = (float) ($txn->Amount ?? 0);
    //             $abs        = abs($amount);
    //             $code       = (string) ($txn->Transaction_Code ?? '');
    //             $prefix     = strtoupper(substr($code, 0, 3));
    //             $paidInOut  = (int) ($txn->paid_in_out ?? 0);
    //             $entryType  = strtoupper((string) ($txn->entry_type ?? ''));

    //             $debit = 0.0;
    //             $credit = 0.0;
    //             $side = null; // 'DR' or 'CR'

    //             switch ($prefix) {
    //                 case 'PAY':
    //                 case 'CHQ':
    //                 case 'REC':
    //                     if ($paidInOut === 1) {           // In → Credit (+)
    //                         $credit = $abs;
    //                         $side = 'CR';
    //                         $running += $abs;
    //                     } elseif ($paidInOut === 2) {     // Out → Debit (−)
    //                         $debit  = $abs;
    //                         $side = 'DR';
    //                         $running -= $abs;
    //                     } else {                           // Fallback
    //                         if ($entryType === 'CR') {
    //                             $credit = $abs;
    //                             $side = 'CR';
    //                             $running += $abs;
    //                         } elseif ($entryType === 'DR') {
    //                             $debit = $abs;
    //                             $side = 'DR';
    //                             $running -= $abs;
    //                         } else { // last resort: use sign of Amount
    //                             if ($amount >= 0) {
    //                                 $credit = $abs;
    //                                 $side = 'CR';
    //                                 $running += $abs;
    //                             } else {
    //                                 $debit = $abs;
    //                                 $side = 'DR';
    //                                 $running -= $abs;
    //                             }
    //                         }
    //                     }
    //                     break;

    //                 case 'SIN':                          // Sales Invoice → Credit (+)
    //                 case 'PUC':                          // Purchase Credit → Credit (+)
    //                     $credit = $abs;
    //                     $side = 'CR';
    //                     $running += $abs;
    //                     break;

    //                 case 'SCN':                          // Sales Credit Note → Debit (−)
    //                 case 'PUR':                          // Purchase Invoice → Debit (−)
    //                     $debit  = $abs;
    //                     $side = 'DR';
    //                     $running -= $abs;
    //                     break;

    //                 default:                              // Fallback to entry_type or Amount sign
    //                     if ($entryType === 'CR') {
    //                         $credit = $abs;
    //                         $side = 'CR';
    //                         $running += $abs;
    //                     } elseif ($entryType === 'DR') {
    //                         $debit = $abs;
    //                         $side = 'DR';
    //                         $running -= $abs;
    //                     } else {
    //                         if ($amount >= 0) {
    //                             $credit = $abs;
    //                             $side = 'CR';
    //                             $running += $abs;
    //                         } else {
    //                             $debit = $abs;
    //                             $side = 'DR';
    //                             $running -= $abs;
    //                         }
    //                     }
    //             }

    //             return [
    //                 'date'             => $txn->Transaction_Date ?? '—',
    //                 'reference'        => $code ?: '—',
    //                 'details'          => $txn->Description ?? '—',
    //                 'debit'            => $debit > 0 ? $debit : '',
    //                 'credit'           => $credit > 0 ? $credit : '',
    //                 'dr_cr'            => $side ?? ($entryType ?: ''),
    //                 'running_balance'  => $running,
    //             ];
    //         });

    //         return response()->json([
    //             'transactions' => $transactions,
    //             'balance'      => $running,
    //         ]);
    //     } catch (\Exception $e) {
    //         Log::error($e);
    //         return response()->json(['error' => 'Could not fetch transactions'], 500);
    //     }
    // }




    public function getForModal()
    {
        try {
            $data = Cache::remember('chart_of_accounts_modal_v2', 300, function () {
                // 1) Pre-aggregate txns once
                $tx = DB::table('transaction') // <-- your actual table name
                    ->select('chart_of_account_id', DB::raw('SUM(Amount) AS balance'))
                    ->whereNull('Deleted_On')
                    ->where('Is_Imported', 1)
                    ->groupBy('chart_of_account_id');

                // 2) Join the aggregated subquery (no GROUP BY on COA needed)
                $rows = DB::table('chart_of_accounts as c')
                    ->leftJoinSub($tx, 'tx', 'tx.chart_of_account_id', '=', 'c.id')
                    ->select(
                        'c.id',
                        'c.ledger_ref',
                        'c.account_ref',
                        'c.description',
                        DB::raw('COALESCE(tx.balance,0) AS balance')
                    )
                    ->where('c.is_active', 1)
                    ->orderBy('c.ledger_ref')
                    ->orderBy('c.account_ref')
                    ->get();

                // 3) Shape for the modal: group by ledger, include ledger total
                return $rows
                    ->groupBy('ledger_ref')
                    ->map(function ($accounts, $ledgerRef) {
                        $ledgerBalance = (float) $accounts->sum('balance');
                        $accountsData  = $accounts->map(fn($a) => [
                            'id'          => $a->id,
                            'account_ref' => $a->account_ref,
                            'balance'     => (float) $a->balance,
                            'description' => $a->description,
                        ])->values();

                        return [
                            'ledger_ref' => $ledgerRef,
                            'balance'    => $ledgerBalance,
                            'accounts'   => $accountsData,
                        ];
                    })
                    ->values()
                    ->all();
            });

            // 4) Return JSON here (not inside remember)
            return response()->json($data);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['error' => 'Failed to load chart of accounts'], 500);
        }
    }


    /**
     * ✅ SIMPLE: Get ALL chart of accounts for dropdown (no client filtering)
     */
    public function getChartOfAccounts(Request $request)
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated.'
                ], 401);
            }

            $clientId = $user->Client_ID;

            // 🔍 Filter files based on the authenticated user's client ID
            $chartOfAccounts = \App\Models\File::where('Client_ID', $clientId)
                ->whereNotNull('Ledger_Ref')
                ->select('File_ID as id', 'Ledger_Ref as ledger_ref')
                ->orderBy('Ledger_Ref')
                ->distinct()
                ->get();

            return response()->json([
                'success' => true,
                'chart_of_accounts' => $chartOfAccounts
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching chart of accounts: ' . $e->getMessage()
            ], 500);
        }
    }




    /**
     * ✅ SIMPLE: Get ALL ledger refs for dropdown (no client filtering)
     */
    public function getLedgerRefsForDropdown(Request $request)
    {
        try {
            $ledgerRefs = ChartOfAccount::query()
                ->whereNotNull('ledger_ref')
                ->selectRaw('MIN(id) as id, ledger_ref')  // one id per ledger_ref
                ->groupBy('ledger_ref')
                ->orderBy('ledger_ref')
                ->get();

            return response()->json([
                'success' => true,
                'ledger_refs' => $ledgerRefs
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Error fetching ledger refs: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ SIMPLE: Get ALL account refs for a specific ledger (no client filtering)
     */
    public function getAccountRefsByLedger(Request $request)
    {
        try {
            $ledgerRef = $request->input('ledger_ref');

            if (!$ledgerRef) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ledger reference is required'
                ], 400);
            }

            // Get ALL account refs for the ledger (same pattern as getForModal)
            $accountRefs = \App\Models\ChartOfAccount::where('ledger_ref', $ledgerRef)
                ->where('is_active', 1)
                ->select('id', 'account_ref', 'description', 'vat_id')
                ->orderBy('account_ref')
                ->get();

            return response()->json([
                'success' => true,
                'account_refs' => $accountRefs
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in getAccountRefsByLedger: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error fetching account refs: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ NEW: Get VAT types by form/payment type
     */
    public function getVatTypesByForm($formKey)
    {
        try {
            // Validate form_key
            $validFormKeys = [
                'sales_invoice',
                'purchase',
                'sales_credit',
                'purchase_credit',
                'receipt',
                'inter_bank_office',
                'payment',
                'cheque',
                'journal'
            ];

            if (!in_array($formKey, $validFormKeys)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid form key: ' . $formKey,
                    'vat_types' => []
                ], 400);
            }

            $vatLabels = VatFormLabel::where('form_key', $formKey)
                ->orderBy('percentage', 'DESC')  // 20% first, then 5%, then 0%
                ->get();

            if ($vatLabels->isEmpty()) {
                Log::warning("No VAT types found for form_key: {$formKey}");

                return response()->json([
                    'success' => false,
                    'message' => "No VAT types configured for {$formKey}. Please contact administrator.",
                    'vat_types' => []
                ], 404);
            }

            $result = $vatLabels->map(function ($label) {
                return [
                    'id' => $label->id,
                    'vat_type_id' => $label->vat_type_id,
                    'vat_name' => $label->display_name,
                    'percentage' => (float) $label->percentage,
                    'display_name' => $label->display_name,
                    'form_key' => $label->form_key
                ];
            });

            return response()->json([
                'success' => true,
                'vat_types' => $result,
                'count' => $result->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getVatTypesByForm', [
                'form_key' => $formKey,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load VAT types. Please try again.',
                'vat_types' => []
            ], 500);
        }
    }

    /**
     * ✅ NEW: Get all available VAT types (fallback)
     */
    public function getAllVatTypes()
    {
        try {
            // ✅ FIXED: Use "Percentage" column
            $vatTypes = VatType::orderBy('Percentage')->get()->map(function ($vatType) {
                return [
                    'vat_type_id' => $vatType->VAT_ID,
                    'vat_name' => $vatType->VAT_Name,
                    'percentage' => $vatType->Percentage ?? 0,  // ✅ FIXED
                    'display_name' => $vatType->VAT_Name
                ];
            });

            return response()->json([
                'success' => true,
                'vat_types' => $vatTypes
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getAllVatTypes: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error fetching VAT types: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * ✅ Get ALL chart of accounts records (for ID lookup during edit)
     */
    public function getAllChartOfAccounts(Request $request)
    {
        try {
            $accounts = ChartOfAccount::query()
                ->where('is_active', 1)
                ->select('id', 'ledger_ref', 'account_ref', 'description', 'vat_id', 'normal_balance')
                ->orderBy('ledger_ref')
                ->orderBy('account_ref')
                ->get();

            Log::info('getAllChartOfAccounts called', [
                'count' => $accounts->count(),
                'sample' => $accounts->take(3)->toArray(),
            ]);

            return response()->json([
                'success' => true,
                'accounts' => $accounts
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getAllChartOfAccounts: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error fetching chart of accounts: ' . $e->getMessage()
            ], 500);
        }
    }
}
