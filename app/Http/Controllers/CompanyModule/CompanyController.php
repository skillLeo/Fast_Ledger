<?php
// app/Http/Controllers/CompanyModule/CompanyController.php

namespace App\Http\Controllers\CompanyModule;

use App\Http\Controllers\Controller;
use App\Http\Controllers\CompanyModule\Traits\CompanyDataTrait;
use App\Http\Requests\CompanyModule\StoreCompanyRequest;
use App\Http\Requests\CompanyModule\UpdateCompanyRequest;
use App\Services\CompanyModule\CompanyService;
use App\Services\CompanyModule\ActivityLogService;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    use CompanyDataTrait;

    protected $companyService;
    protected $activityLogService;

    public function __construct(
        CompanyService $companyService,
        ActivityLogService $activityLogService
    ) {
        $this->companyService = $companyService;
        $this->activityLogService = $activityLogService;
    }

    /**
     * ✅ Display a listing of user's companies
     */
    public function index()
    {
        $companies = $this->companyService->getUserCompanies();
        $incompleteCompanies = $this->companyService->getIncompleteProfiles();

        return view('company-module.companies.index', compact('companies', 'incompleteCompanies'));
    }

    /**
     * ✅ Show form to create a NEW company (called from /company route)
     */
    public function create()
    {
        $user = auth()->user();

        // ✅ CHECK COMPANY LIMIT
        if ($user->subscription_status && $user->allowed_companies) {
            $currentCompanyCount = $user->companies()->count();
            
            if ($currentCompanyCount >= $user->allowed_companies) {
                // ✅ REDIRECT TO PAYMENT PAGE FOR UPGRADE
                return redirect()->route('company.payment.create')
                    ->with('info', "You've reached your limit of {$user->allowed_companies} companies. Upgrade your subscription to add more.");
            }
        }

        // ✅ Load form data
        $countries = $this->getCountries();
        $companyTypesES = $this->getCompanyTypesES();
        $companyTypesUK = $this->getCompanyTypesUK();
        $taxRegimes = $this->getTaxRegimes();

        // ✅ USE SETUP VIEW (not companies.create which doesn't exist)
        return view('company-module.setup.create', compact(
            'countries',
            'companyTypesES',
            'companyTypesUK',
            'taxRegimes'
        ));
    }

    /**
     * ✅ Store a newly created company (from /company route)
     */
    public function store(StoreCompanyRequest $request)
    {
        $user = auth()->user();

        // ✅ CHECK COMPANY LIMIT BEFORE CREATING
        if ($user->subscription_status && $user->allowed_companies) {
            $currentCompanyCount = $user->companies()->count();
            
            if ($currentCompanyCount >= $user->allowed_companies) {
                // ✅ REDIRECT TO PAYMENT FOR UPGRADE
                return redirect()->route('company.payment.create')
                    ->with('info', "Please upgrade your subscription to add more companies.")
                    ->withInput($request->all());
            }
        }

        // ✅ Create company using service
        $result = $this->companyService->createCompany($request->validated());

        if (!$result['success']) {
            return back()
                ->withInput()
                ->withErrors(['error' => $result['message']]);
        }

        // ✅ Redirect to company list
        return redirect()->route('company.index')
            ->with('success', 'Company created successfully!');
    }

    /**
     * ✅ Display the specified company
     */
    public function show($id)
    {
        $company = auth()->user()->companies()
            ->withPivot('Role', 'Is_Primary', 'Created_At')
            ->with([
                'users' => function($query) {
                    $query->wherePivot('Is_Active', true)
                          ->withPivot('Role', 'Is_Primary', 'Created_At');
                },
                'activityLogs' => function($query) {
                    $query->orderBy('Created_At', 'desc')->limit(10);
                },
                'activityLogs.user',
                'creator'
            ])
            ->findOrFail($id);

        return view('company-module.companies.show', compact('company'));
    }

    /**
     * ✅ Show the form for editing the specified company
     */
    public function edit($id)
    {
        $result = $this->companyService->getCompany($id);

        if (!$result['success']) {
            return redirect()->route('company.index')
                ->withErrors(['error' => $result['message']]);
        }

        $company = $result['company'];
        $countries = $this->getCountries();
        $companyTypesES = $this->getCompanyTypesES();
        $companyTypesUK = $this->getCompanyTypesUK();
        $taxRegimes = $this->getTaxRegimes();

        return view('company-module.companies.edit', compact(
            'company',
            'countries',
            'companyTypesES',
            'companyTypesUK',
            'taxRegimes'
        ));
    }

    /**
     * ✅ Update the specified company
     */
    public function update(UpdateCompanyRequest $request, $id)
    {
        $result = $this->companyService->updateCompany($id, $request->validated());

        if (!$result['success']) {
            return back()
                ->withInput()
                ->withErrors(['error' => $result['message']]);
        }

        return redirect()->route('company.show', $id)
            ->with('success', $result['message']);
    }

    /**
     * ✅ Remove the specified company (soft delete)
     */
    public function destroy($id)
    {
        $result = $this->companyService->deleteCompany($id);

        if (!$result['success']) {
            return back()->withErrors(['error' => $result['message']]);
        }

        return redirect()->route('company.index')
            ->with('success', $result['message']);
    }
}