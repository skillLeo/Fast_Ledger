<?php
// app/Http/Controllers/CompanyModule/CompanySetupController.php

namespace App\Http\Controllers\CompanyModule;

use App\Http\Controllers\Controller;
use App\Http\Controllers\CompanyModule\Traits\CompanyDataTrait;
use App\Http\Requests\CompanyModule\StoreCompanyRequest;
use App\Services\CompanyModule\CompanySetupService;
use App\Services\CompanyModule\ModuleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CompanySetupController extends Controller
{
    use CompanyDataTrait;

    protected $setupService;
    protected $moduleService;

    const PRICE_PER_COMPANY = 10;
    const CURRENCY = '£';

    public function __construct(
        CompanySetupService $setupService,
        ModuleService $moduleService
    ) {
        $this->setupService = $setupService;
        $this->moduleService = $moduleService;
    }

    /**
     * ✅ Show company setup form
     */
    public function create()
    {
        $user = auth()->user();

        // ✅ CHECK 1: First time user (no companies at all)
        $hasAnyCompany = $user->companies()->exists();
        
        if (!$hasAnyCompany) {
            // First company - allow creation (will go to payment after)
            $countries = $this->getCountries();
            $companyTypesES = $this->getCompanyTypesES();
            $companyTypesUK = $this->getCompanyTypesUK();
            $taxRegimes = $this->getTaxRegimes();

            return view('company-module.setup.create', compact(
                'countries',
                'companyTypesES',
                'companyTypesUK',
                'taxRegimes'
            ));
        }

        // ✅ CHECK 2: User wants to add ANOTHER company
        if ($user->subscription_status && $user->allowed_companies) {
            $currentCompanyCount = $user->companies()->count();
            
            // ✅ Reached limit - redirect to payment to upgrade
            if ($currentCompanyCount >= $user->allowed_companies) {
                return redirect()->route('company.payment.create')
                    ->with('info', "You've reached your limit of {$user->allowed_companies} companies. Upgrade to add more.");
            }
        }

        // ✅ CHECK 3: Has companies but no subscription (edge case - shouldn't happen)
        if (!$user->subscription_status) {
            return redirect()->route('company.payment.create')
                ->with('error', 'Please complete your subscription first.');
        }

        // ✅ User has active subscription and can add more companies
        $countries = $this->getCountries();
        $companyTypesES = $this->getCompanyTypesES();
        $companyTypesUK = $this->getCompanyTypesUK();
        $taxRegimes = $this->getTaxRegimes();

        return view('company-module.setup.create', compact(
            'countries',
            'companyTypesES',
            'companyTypesUK',
            'taxRegimes'
        ));
    }

    /**
     * ✅ Store company
     */
    public function store(StoreCompanyRequest $request)
    {
        $user = auth()->user();

        // ✅ VALIDATION 1: Check company limit for existing subscribers
        if ($user->subscription_status && $user->allowed_companies) {
            $currentCompanyCount = $user->companies()->count();
            
            if ($currentCompanyCount >= $user->allowed_companies) {
                // Redirect to payment to upgrade
                return redirect()->route('company.payment.create')
                    ->with('info', 'Please upgrade your subscription to add more companies.')
                    ->withInput($request->all());
            }
        }

        try {
            // ✅ Create company using service
            $result = $this->setupService->setupCompany($request->validated());

            if (!$result['success']) {
                return back()
                    ->withInput()
                    ->withErrors(['error' => $result['message']]);
            }

            $company = $result['company'];

            // ✅ Get updated company count
            $currentCompanyCount = $user->fresh()->companies()->count();

            // ============================================
            // ROUTING LOGIC AFTER COMPANY CREATION
            // ============================================

            // ✅ CASE 1: First company created + No subscription yet
            if ($currentCompanyCount === 1 && !$user->subscription_status) {
                return redirect()->route('company.payment.create')
                    ->with('success', 'Company created! Please complete your subscription to get started.');
            }

            // ✅ CASE 2: First company created + Already have subscription (edge case)
            if ($currentCompanyCount === 1 && $user->subscription_status) {
                return redirect()->route('company.select')
                    ->with('success', 'Your first company has been created! You can start managing it now.');
            }

            // ✅ CASE 3: Additional company + Have active subscription
            if ($user->subscription_status) {
                return redirect()->route('company.select')
                    ->with('success', "Company '{$company->Company_Name}' created successfully! You can now switch to it.");
            }

            // ✅ CASE 4: Fallback (shouldn't reach here, but just in case)
            return redirect()->route('company.payment.create')
                ->with('info', 'Please complete your subscription setup.');

        } catch (\Exception $e) {
            Log::error('Company creation failed in controller: ' . $e->getMessage(), [
                'user_id' => $user->User_ID,
                'trace' => $e->getTraceAsString(),
            ]);

            return back()
                ->withInput()
                ->withErrors(['error' => 'An error occurred while creating your company. Please try again.']);
        }
    }

    /**
     * ✅ Show company setup choice page
     */
    public function showChoice()
    {
        // Check if user needs setup
        if (!$this->setupService->needsSetup()) {
            return redirect()->route('company.index')
                ->with('info', 'You already have companies set up.');
        }

        return view('company-module.setup.choice');
    }

    /**
     * ✅ Show setup success page
     */
    public function success($companyId)
    {
        $progressResult = $this->setupService->getSetupProgress($companyId);

        if (!$progressResult['success']) {
            return redirect()->route('company.index')
                ->with('error', 'Company not found.');
        }

        $company = $progressResult['company'];
        $percentage = $progressResult['percentage'];
        $missingFields = $progressResult['missing_fields'];

        return view('company-module.setup.success', compact(
            'company',
            'percentage',
            'missingFields'
        ));
    }

    /**
     * ✅ Skip setup (for testing/demo purposes)
     */
    public function skip()
    {
        $result = $this->setupService->skipSetup();

        if (!$result['success']) {
            return redirect()->route('company.select')
                ->withErrors(['error' => $result['message']]);
        }

        return redirect()->route('company.select')
            ->with('info', $result['message']);
    }

    /**
     * ✅ Get pricing configuration
     * Used by PaymentController and other controllers
     */
    public static function getPricingConfig(): array
    {
        return [
            'price_per_company' => self::PRICE_PER_COMPANY,
            'currency' => self::CURRENCY,
        ];
    }

    /**
     * ✅ Check if user can create more companies
     * Helper method for views/middleware
     */
    public function canCreateMoreCompanies(): bool
    {
        $user = auth()->user();

        // No subscription yet - can create first company
        if (!$user->subscription_status) {
            return $user->companies()->count() === 0;
        }

        // Has subscription - check limit
        if ($user->allowed_companies) {
            $currentCount = $user->companies()->count();
            return $currentCount < $user->allowed_companies;
        }

        return false;
    }

    /**
     * ✅ Get remaining companies user can create
     * Helper method for dashboard/UI
     */
    public function getRemainingCompanies(): int
    {
        $user = auth()->user();

        if (!$user->subscription_status || !$user->allowed_companies) {
            return 0;
        }

        $currentCount = $user->companies()->count();
        $remaining = $user->allowed_companies - $currentCount;

        return max(0, $remaining);
    }
}
