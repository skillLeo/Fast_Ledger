<?php

namespace App\Http\Controllers\CompanyModule;

use App\Http\Controllers\Controller;
use App\Services\CompanyModule\ModuleService;
use App\Services\CompanyModule\CompanyService;
use App\Services\CompanyModule\CompanySetupService;
use Illuminate\Http\Request;

class CompanyDashboardController extends Controller
{
    protected $moduleService;
    protected $companyService;
    protected $setupService;

    public function __construct(
        ModuleService $moduleService,
        CompanyService $companyService,
        CompanySetupService $setupService
    ) {
        $this->moduleService = $moduleService;
        $this->companyService = $companyService;
        $this->setupService = $setupService;
    }

    /**
     * Display the Company Module dashboard
     */
    public function index()
    {
        $user = auth()->user();

        // Get user's companies
        $companies = $this->companyService->getUserCompanies();

        // Check if user needs company setup
        $needsCompanySetup = $this->setupService->needsSetup();

        // Check for incomplete profiles
        $hasIncompleteProfiles = $this->setupService->hasIncompleteProfiles();
        $incompleteCompanies = $hasIncompleteProfiles 
            ? $this->setupService->getIncompleteCompanies() 
            : collect();

        // Statistics (you can expand this)
        $stats = [
            'total_companies' => $companies->count(),
            'active_companies' => $companies->where('Is_Active', true)->count(),
            'incomplete_profiles' => $incompleteCompanies->count(),
        ];

        return view('company-module.dashboard.index', compact(
            'user',
            'companies',
            'needsCompanySetup',
            'hasIncompleteProfiles',
            'incompleteCompanies',
            'stats'
        ));
    }
}