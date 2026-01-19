<?php

namespace App\Http\Controllers\CompanyModule;

use App\Http\Controllers\Controller;
use App\Models\CompanyModule\Company;
use Illuminate\Http\Request;

class CompanySelectionController extends Controller
{
    public function select()
    {
        $companies = auth()->user()->companies()
            ->orderBy('Company_Name')
            ->get();

        if ($companies->count() === 1) {
            return $this->setCurrentCompany($companies->first());
        }

        return view('company-module.select-company', compact('companies'));
    }

    public function setCurrentCompany(Company $company)
    {
        // ✅ Now using $company->id (the actual primary key)
        if (!auth()->user()->hasAccessToCompany($company->id)) {
            abort(403, 'You do not have access to this company.');
        }

        session([
            'current_company_id' => $company->id,  // ✅ Using id
            'current_company_name' => $company->Company_Name,
            'current_company_role' => auth()->user()->getCompanyRole($company->id),
        ]);

        return redirect()->route('company.dashboard', $company->id)  // ✅ Using id
            ->with('success', 'Now working in: ' . $company->Company_Name);
    }

    public function switchCompany(Request $request)
    {
        $request->validate([
            'company_id' => 'required|exists:company_module_companies,id',  // ✅ Using id
        ]);

        $company = Company::findOrFail($request->company_id);
        
        return $this->setCurrentCompany($company);
    }
}