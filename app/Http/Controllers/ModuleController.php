<?php

namespace App\Http\Controllers;

use App\Services\CompanyModule\ModuleService;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    protected $moduleService;

    public function __construct(ModuleService $moduleService)
    {
        $this->moduleService = $moduleService;
    }

    /**
     * Show module selection page (first-time login)
     */
    public function index()
    {
        $availableModules = $this->moduleService->getAvailableModules();
        $activeModules = $this->moduleService->getUserModules();

        return view('modules.select', compact('availableModules', 'activeModules'));
    }

    /**
     * Activate a module for the user
     */
    public function activate(Request $request, $moduleName)
    {
        $result = $this->moduleService->activateModule($moduleName);

        if (!$result['success']) {
            return back()->withErrors(['error' => $result['message']]);
        }

        // If activating Company Module, redirect to setup choice
        if ($moduleName === 'company_module') {
            return redirect()->route('company.setup.choice')
                ->with('success', 'Company Module activated! Let\'s set up your company.');
        }

        // For other modules, redirect to dashboard
        return redirect()->route('dashboard')
            ->with('success', $result['message']);
    }
}