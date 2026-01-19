<?php

namespace App\Http\Controllers\CompanyModule;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\CompanyModule\Company;
use App\Models\CompanyModule\Customer;
use App\Http\Requests\CompanyModule\StoreCustomerRequest;
use App\Http\Requests\CompanyModule\UpdateCustomerRequest;

class CustomerController extends Controller
{
    public function index(Company $company)
    {
        $customers = Customer::where('Company_ID', $company->id)  // ✅ Using id
            ->latest()
            ->paginate(10);
        
        return view('company-module.customers.index', compact('customers', 'company'));
    }

    public function create(Company $company)
    {
        return view('company-module.customers.create', compact('company'));
    }

    public function store(StoreCustomerRequest $request, Company $company)
    {
        $validated = $request->validated();
        $validated['User_ID'] = Auth::id();
        $validated['Company_ID'] = $company->id;  // ✅ Using id

        Customer::create($validated);

        return redirect()->route('company.customers.index', $company)
            ->with('success', 'Customer created successfully!');
    }

    public function show(Company $company, Customer $customer)
    {
        abort_if($customer->Company_ID != $company->id, 404);  // ✅ Using id
        
        return view('company-module.customers.show', compact('customer', 'company'));
    }

    public function edit(Company $company, Customer $customer)
    {
        
        abort_if($customer->Company_ID != $company->id, 404);  // ✅ Using id
        
        return view('company-module.customers.edit', compact('customer', 'company'));
    }

    public function update(UpdateCustomerRequest $request, Company $company, Customer $customer)
    {
        abort_if($customer->Company_ID != $company->id, 404);  // ✅ Using id

        $customer->update($request->validated());

        return redirect()->route('company.customers.index', $company)
            ->with('success', 'Customer updated successfully!');
    }

    public function destroy(Company $company, Customer $customer)
    {
        abort_if($customer->Company_ID != $company->id, 404);  // ✅ Using id
        
        $customer->delete();

        return redirect()->route('company.customers.index', $company)
            ->with('success', 'Customer deleted successfully!');
    }
}