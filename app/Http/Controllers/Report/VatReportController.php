<?php

namespace App\Http\Controllers\Report;

use Carbon\Carbon;
use App\Models\Client;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\Hmrc\OAuthService;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Exceptions\HmrcAuthenticationException;

class VatReportController extends Controller
{
    /**
     * Form keys that represent SALES (Output VAT)
     * These increase Box 1 and Box 6
     */
    const OUTPUT_VAT_FORM_KEYS = [
        'sales_invoice',
        'sales_credit',
        'receipt',
        'inter_bank_office'
    ];

    /**
     * Form keys that represent PURCHASES (Input VAT)
     * These increase Box 4 and Box 7
     */
    const INPUT_VAT_FORM_KEYS = [
        'purchase',
        'purchase_credit',
        'payment',
        'cheque',
        'inter_bank_office'
    ];

    /**
     * Form keys that don't affect VAT boxes
     */
    const NON_VAT_FORM_KEYS = [
        'journal'
    ];

    public function index(Request $request)
    {
        // Validation
        $validated = $request->validate([
            'from_date' => 'nullable|date|before_or_equal:to_date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
            'client_id' => 'nullable|exists:clients,Client_ID',
            'period_key' => 'nullable|string',  // ✅ Add period_key validation
        ]);

        $clientId = auth()->user()->Client_ID ?? $request->input('client_id');

        if (!$clientId) {
            return redirect()->back()->with('error', 'Client Id is required');
        }

        $clientInfo = Client::find($clientId);

        // ✅ Get dates from request (from Review button) or use defaults
        $dateFrom = $request->input('from_date')
            ? Carbon::parse($request->input('from_date'))->toDateString()
            : Carbon::now()->startOfMonth()->toDateString();

        $dateTo = $request->input('to_date')
            ? Carbon::parse($request->input('to_date'))->toDateString()
            : Carbon::now()->toDateString();

        // ✅ Get period key from request or generate default
        $periodKey = $request->input('period_key', $this->generatePeriodKey($dateFrom, $dateTo));

        $obligation = null;
        $isObligationFulfilled = false;

        if ($request->has('period_key')) {
            $vrn = config('hmrc.vat.vrn');
            $obligation = \App\Models\VatObligation::where('vrn', $vrn)
                ->where('period_key', $periodKey)
                ->first();

            $isObligationFulfilled = $obligation && $obligation->status === 'F';
        }
        // Fetch all VAT-related transactions (both Output and Input)
        $allTransactions = $this->getAllVatTransactions($dateFrom, $dateTo, $clientId);


        // Initialize all VAT boxes
        $_box1Amount = 0;
        $_box2Amount = 0;
        $_box4Amount = 0;
        $_box6Amount = 0;
        $_box7Amount = 0;
        $_box8Amount = 0;
        $_box9Amount = 0;

        $outputVatDetails = [];
        $inputVatDetails = [];

        // Process each transaction
        foreach ($allTransactions as $transaction) {
            $vatTypeId = $transaction->VAT_ID;
            $formKey = $transaction->form_key;
            $isCredit = in_array($formKey, ['sales_credit', 'purchase_credit']);

            // Calculate net and VAT from gross amount
            list($net, $vat) = $this->calculateVat($transaction->Amount, $transaction->Percentage);

            // For credit notes, make amounts negative
            if ($isCredit) {
                $net = -$net;
                $vat = -$vat;
            }

            // Determine if this is Output VAT (Sales) or Input VAT (Purchase)
            $isOutputVat = $this->isOutputVatTransaction($formKey, $transaction);
            $isInputVat = $this->isInputVatTransaction($formKey, $transaction);

            // Process Output VAT (Sales)
            if ($isOutputVat) {
                $this->processOutputVat($transaction, $net, $vat, $_box1Amount, $_box6Amount, $_box8Amount);

                $outputVatDetails[] = [
                    'date' => $transaction->Transaction_Date ?? '',
                    'ledger_ref' => $transaction->Ledger_Ref ?? '',
                    'account_ref' => $transaction->account_ref ?? '',
                    'description' => $transaction->Description ?? '',
                    'vat_type' => $transaction->display_name ?? '',
                    'form_key' => $formKey,
                    'net' => $net,
                    'vat' => $vat,
                    'gross' => $net + $vat,
                    'rate' => $transaction->Percentage ? $transaction->Percentage . '%' : '0%',
                ];
            }

            // Process Input VAT (Purchases)
            if ($isInputVat) {
                $this->processInputVat($transaction, $net, $vat, $_box2Amount, $_box4Amount, $_box7Amount, $_box9Amount);

                $inputVatDetails[] = [
                    'date' => $transaction->Transaction_Date ?? '',
                    'ledger_ref' => $transaction->Ledger_Ref ?? '',
                    'account_ref' => $transaction->account_ref ?? '',
                    'description' => $transaction->Description ?? '',
                    'vat_type' => $transaction->display_name ?? '',
                    'form_key' => $formKey,
                    'net' => $net,
                    'vat' => $vat,
                    'gross' => $net + $vat,
                    'rate' => $transaction->Percentage ? $transaction->Percentage . '%' : '0%',
                ];
            }
        }

        // Calculate derived boxes
        $_box3Amount = $_box1Amount + $_box2Amount;
        $_box5Amount = $_box3Amount - $_box4Amount;

        // ✅ Use helper methods for HMRC connection
        $isConnectedToHmrc = $this->checkHmrcConnection();
        $hmrcToken = $this->getHmrcToken();
        // Return view with all data
        return view('admin.reports.vat_report', [
            'clientInfo' => $clientInfo,
            '_box1Amount' => round($_box1Amount, 2),
            '_box2Amount' => round($_box2Amount, 2),
            '_box3Amount' => round($_box3Amount, 2),
            '_box4Amount' => round($_box4Amount, 2),
            '_box5Amount' => round($_box5Amount, 2),
            '_box6Amount' => round($_box6Amount, 2),
            '_box7Amount' => round($_box7Amount, 2),
            '_box8Amount' => round($_box8Amount, 2),
            '_box9Amount' => round($_box9Amount, 2),
            'outputVatDetails' => $outputVatDetails,
            'inputVatDetails' => $inputVatDetails,
            'fromDate' => $dateFrom,
            'toDate' => $dateTo,
            'periodKey' => $periodKey,  // ✅ Add period key
            'isConnectedToHmrc' => $isConnectedToHmrc,
            'hmrcToken' => $hmrcToken,
            'obligation' => $obligation,                    // ✅ NEW
            'isObligationFulfilled' => $isObligationFulfilled
        ]);
    }

    /**
     * ✅ NEW: Generate a period key from date range
     * Format: YY-Q# (e.g., 24-Q1 for Q1 2024)
     */
    private function generatePeriodKey(string $fromDate, string $toDate): string
    {
        $start = Carbon::parse($fromDate);
        $year = $start->format('y'); // 2-digit year
        $quarter = $start->quarter;

        return "{$year}-Q{$quarter}";
    }

    /**
     * ✅ NEW: Check if connected to HMRC
     */
    private function checkHmrcConnection(): bool
    {
        try {
            $oauthService = app(OAuthService::class);
            $oauthService->getValidToken(config('hmrc.vat.vrn'));
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * ✅ NEW: Get HMRC token
     */
    private function getHmrcToken()
    {
        try {
            $oauthService = app(OAuthService::class);
            return $oauthService->getValidToken(config('hmrc.vat.vrn'));
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Determine if a transaction is Output VAT (Sales)
     */
    private function isOutputVatTransaction($formKey, $transaction)
    {
        if (in_array($formKey, ['sales_invoice', 'sales_credit', 'receipt'])) {
            return true;
        }

        if ($formKey === 'inter_bank_office' && $transaction->Paid_In_Out === 'In') {
            return true;
        }

        return false;
    }

    /**
     * Determine if a transaction is Input VAT (Purchase)
     */
    private function isInputVatTransaction($formKey, $transaction)
    {
        if (in_array($formKey, ['purchase', 'purchase_credit', 'payment', 'cheque'])) {
            return true;
        }

        if ($formKey === 'inter_bank_office' && $transaction->Paid_In_Out === 'Out') {
            return true;
        }

        return false;
    }

    /**
     * Process Output VAT (Sales) and update boxes
     */
    private function processOutputVat($transaction, $net, $vat, &$box1, &$box6, &$box8)
    {
        $displayName = strtolower($transaction->display_name ?? '');
        $percentage = (float)($transaction->Percentage ?? 0);

        if (str_contains($displayName, 'vat on income') && $percentage > 0) {
            $box1 += $vat;
            $box6 += $net;
            return;
        }

        if (str_contains($displayName, 'vat on income') && $percentage == 0) {
            $box6 += $net;
            return;
        }

        if (str_contains($displayName, 'ec goods income')) {
            $box6 += $net;
            $box8 += $net;
            return;
        }

        if (str_contains($displayName, 'ec services income')) {
            $box6 += $net;
            $box8 += $net;
            return;
        }

        if (str_contains($displayName, 'exempt')) {
            return;
        }
    }

    /**
     * Process Input VAT (Purchases) and update boxes
     */
    private function processInputVat($transaction, $net, $vat, &$box2, &$box4, &$box7, &$box9)
    {
        $displayName = strtolower($transaction->display_name ?? '');
        $percentage = (float)($transaction->Percentage ?? 0);

        if (str_contains($displayName, 'vat input') && !str_contains($displayName, 'reverse') && !str_contains($displayName, 'ec')) {
            if ($percentage > 0) {
                $box4 += $vat;
            }
            $box7 += $net;
            return;
        }

        if (str_contains($displayName, 'reverse charge')) {
            $box2 += $vat;
            $box4 += $vat;
            $box7 += $net;
            return;
        }

        if (str_contains($displayName, 'ec acquisition')) {
            if ($percentage > 0) {
                $box2 += $vat;
                $box4 += $vat;
            }
            $box7 += $net;
            $box9 += $net;
            return;
        }

        if (str_contains($displayName, 'exempt')) {
            return;
        }
    }

    /**
     * Calculate net and VAT from gross amount
     */
    private function calculateVat($amount, $percentage)
    {
        if (!$percentage || $percentage == 0) {
            return [round($amount, 2), 0.00];
        }

        $net = $amount / (1 + ($percentage / 100));
        $vat = $amount - $net;

        return [round($net, 2), round($vat, 2)];
    }

    public function getAllVatTransactions($date_from, $date_to, $clientId)
    {
        $excludedFormKeys = self::NON_VAT_FORM_KEYS;

        return Transaction::select(
            'transaction.Transaction_ID',
            'transaction.Transaction_Date',
            'transaction.Description',
            'transaction.Amount',
            'transaction.VAT_ID',
            'transaction.Paid_In_Out',
            // ✅ Get ledger_ref from file OR chart_of_account
            DB::raw('COALESCE(file.Ledger_Ref, coa1.ledger_ref) as Ledger_Ref'),
            'coa2.account_ref',
            'vat_form_labels.form_key',
            'vat_form_labels.display_name',
            'vat_form_labels.vat_type_id',
            'vattype.Percentage as Percentage',
            'transaction.File_ID',
            'transaction.chart_of_account_id'
        )
            // ✅ LEFT JOIN to files table (for File_ID case)
            ->leftJoin('file', 'file.File_ID', '=', 'transaction.File_ID')

            // ✅ LEFT JOIN to chart_of_accounts (for chart_of_account_id case)
            ->leftJoin('chart_of_accounts as coa1', 'coa1.id', '=', 'transaction.chart_of_account_id')

            // ✅ Join for Account_Ref_ID
            ->leftJoin('chart_of_accounts as coa2', 'coa2.id', '=', 'transaction.Account_Ref_ID')

            ->leftJoin('vat_form_labels', 'vat_form_labels.id', '=', 'transaction.VAT_ID')
            ->leftJoin('vattype', 'vattype.VAT_ID', '=', 'vat_form_labels.vat_type_id')

            // ✅ Filter conditions
            ->whereNotNull('transaction.VAT_ID')
            ->whereNotNull('vat_form_labels.form_key')
            ->whereNotIn('vat_form_labels.form_key', $excludedFormKeys)
            ->whereBetween('transaction.Transaction_Date', [$date_from, $date_to])

            // ✅ Filter by client for logged-in user
            ->where(function ($query) use ($clientId) {
                $query->where(function ($subQuery) use ($clientId) {
                    // Case 1: Has File_ID - filter by file's client
                    $subQuery->whereNotNull('transaction.File_ID')
                        ->where('file.Client_ID', $clientId);
                })
                    ->orWhere(function ($subQuery) {
                        // Case 2: No File_ID but has chart_of_account_id
                        // Since chart_of_accounts is shared, just ensure it exists
                        $subQuery->whereNull('transaction.File_ID')
                            ->whereNotNull('transaction.chart_of_account_id');
                    });
            })

            ->orderBy('transaction.Transaction_Date')
            ->orderBy('transaction.Transaction_ID')
            ->get();
    }
}
