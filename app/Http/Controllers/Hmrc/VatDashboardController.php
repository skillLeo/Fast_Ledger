<?php

namespace App\Http\Controllers\Hmrc;

use App\Models\Transaction;
use App\Models\VatObligation;
use Illuminate\Support\Carbon;
use App\Services\Hmrc\OAuthService;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\Hmrc\VatObligationService;
use App\Repositories\VatSubmissionRepository;
use App\Exceptions\HmrcAuthenticationException;

class VatDashboardController extends Controller
{
    protected OAuthService $oauthService;
    protected VatObligationService $obligationService;
    protected VatSubmissionRepository $submissionRepository;

    public function __construct(
        OAuthService $oauthService,
        VatObligationService $obligationService,
        VatSubmissionRepository $submissionRepository
    ) {
        $this->oauthService = $oauthService;
        $this->obligationService = $obligationService;
        $this->submissionRepository = $submissionRepository;
    }

    /**
     * Display VAT Dashboard
     * Obligations are loaded from database (already synced after OAuth)
     */
    public function index()
    {
        try {
            $vrn = config('hmrc.vat.vrn');

            // Check if connected to HMRC
            try {
                $token = $this->oauthService->getValidToken($vrn);
                $isConnected = true;
            } catch (HmrcAuthenticationException $e) {
                $isConnected = false;
                $token = null;
            }

            // ✅ Simply load obligations from database (fast, no API call)
            $obligations = $this->obligationService->getObligationsFromDatabase($vrn);

            // Get recent submissions from database
            $recentSubmissions = $this->submissionRepository->getRecentSubmissions(10);

            return view('admin.hmrc.vat.dashboard', compact(
                'obligations',
                'recentSubmissions',
                'vrn',
                'token',
                'isConnected'
            ));
        } catch (\Exception $e) {
            Log::error('Dashboard error', ['error' => $e->getMessage()]);

            return view('admin.hmrc.vat.dashboard', [
                'error' => 'An error occurred loading the dashboard',
                'obligations' => ['open' => [], 'fulfilled' => [], 'overdue' => []],
                'recentSubmissions' => collect([]),
                'vrn' => config('hmrc.vat.vrn'),
                'isConnected' => false,
            ]);
        }
    }


    /**
     * Review a specific VAT obligation period
     * Redirects to VAT report with period dates pre-filled
     */
    /**
     * Review a specific VAT obligation period
     * Redirects to VAT report with period dates pre-filled
     */
    public function review(string $periodKey)
    {
        try {
            $vrn = config('hmrc.vat.vrn');

            // Find the obligation from database
            $obligation = VatObligation::where('vrn', $vrn)
                ->where('period_key', $periodKey)
                ->firstOrFail();

            // ✅ IMPORTANT: Ensure dates are in correct format (Y-m-d)
            $startDate = Carbon::parse($obligation->start_date)->format('Y-m-d');
            $endDate = Carbon::parse($obligation->end_date)->format('Y-m-d');

            Log::info('📊 Reviewing obligation', [
                'period_key' => $periodKey,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'start_date_raw' => $obligation->start_date,
                'end_date_raw' => $obligation->end_date,
                'status' => $obligation->status,
            ]);

            // ✅ Check if transactions exist in this exact period
            $clientId = auth()->user()->Client_ID;

            $transactionCount = Transaction::join('file', 'file.File_ID', '=', 'transaction.File_ID')
                ->where('file.Client_ID', $clientId)
                ->whereBetween('transaction.Transaction_Date', [$startDate, $endDate])
                ->whereNotNull('transaction.VAT_ID')
                ->count();

            Log::info('🔍 Transaction count check', [
                'period' => "$startDate to $endDate",
                'count' => $transactionCount,
                'client_id' => $clientId,
            ]);

            // dd($transactionCount);
            return redirect()->route('vat.report', [
                'from_date' => $startDate,     // ✅ CORRECT! Use formatted date
                'to_date' => $endDate,         // ✅ CORRECT! Use formatted date
                'period_key' => $periodKey,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Obligation not found', [
                'period_key' => $periodKey,
                'vrn' => $vrn ?? 'N/A',
            ]);

            return redirect()->route('hmrc.vat.dashboard')
                ->with('error', "Obligation with period key '{$periodKey}' not found");
        } catch (\Exception $e) {
            Log::error('Failed to load obligation for review', [
                'period_key' => $periodKey,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('hmrc.vat.dashboard')
                ->with('error', 'Unable to load obligation. Please try again.');
        }
    }
}
