<?php

namespace App\Repositories\CompanyModule;

use App\Models\CompanyModule\Company;
use App\Repositories\CompanyModule\Interfaces\CompanyRepositoryInterface;
use Illuminate\Support\Facades\DB;

class CompanyRepository implements CompanyRepositoryInterface
{
    protected $model;

    public function __construct(Company $model)
    {
        $this->model = $model;
    }

    /**
     * Get all companies for a user
     */
    public function getUserCompanies($userId)
    {
        return $this->model
            ->where('User_ID', $userId)
            ->active()
            ->orderBy('Created_On', 'desc')
            ->get();
    }

    /**
     * Get a company by ID
     */
    public function find($id)
    {
        return $this->model->find($id);
    }

    /**
     * Get company by ID with relationships
     */
    public function findWithRelations($id, array $relations = [])
    {
        return $this->model->with($relations)->find($id);
    }

    /**
     * Create a new company
     */
    public function create(array $data)
    {
        // Set defaults
        $data['User_ID'] = $data['User_ID'] ?? auth()->id();
        $data['Created_By'] = auth()->id();
        $data['Created_On'] = now();
        $data['Is_Active'] = true;
        $data['Is_Archive'] = false;
        
        // Set currency based on country
        if (!isset($data['Currency'])) {
            $data['Currency'] = $data['Country'] === 'GB' ? 'GBP' : 'EUR';
        }

        // Set invoice prefix if not provided
        if (!isset($data['Invoice_Prefix'])) {
            $data['Invoice_Prefix'] = strtoupper(substr($data['Company_Name'], 0, 3));
        }

        $company = $this->model->create($data);

        // Calculate initial profile completion
        $company->calculateProfileCompletion();
        $company->save();

        return $company;
    }

    /**
     * Update a company
     */
    public function update($id, array $data)
    {
        $company = $this->find($id);
        
        if (!$company) {
            return false;
        }

        $data['Modified_By'] = auth()->id();
        $data['Modified_On'] = now();

        $company->update($data);

        // Recalculate profile completion
        $company->calculateProfileCompletion();
        $company->save();

        return $company->fresh();
    }

    /**
     * Delete a company (soft delete)
     */
    public function delete($id)
    {
        $company = $this->find($id);
        
        if (!$company) {
            return false;
        }

        return $company->update([
            'Is_Archive' => true,
            'Deleted_By' => auth()->id(),
            'Deleted_On' => now(),
        ]);
    }

    /**
     * Get active companies for a user
     */
    public function getActiveCompanies($userId)
    {
        return $this->model
            ->where('User_ID', $userId)
            ->active()
            ->orderBy('Company_Name')
            ->get();
    }

    /**
     * Get companies with incomplete profiles
     */
    public function getIncompleteProfiles($userId)
    {
        return $this->model
            ->where('User_ID', $userId)
            ->incompleteProfile()
            ->active()
            ->orderBy('Created_On', 'desc')
            ->get();
    }

    /**
     * Check if user owns a company
     */
    public function userOwnsCompany($userId, $companyId)
    {
        return $this->model
            ->where('id', $companyId)
            ->where('User_ID', $userId)
            ->exists();
    }

    /**
     * Check if user has access to company
     */
    public function userHasAccess($userId, $companyId)
    {
        // Check if user owns the company
        if ($this->userOwnsCompany($userId, $companyId)) {
            return true;
        }

        // Check if user is in company_module_users table
        return DB::table('company_module_users')
            ->where('Company_ID', $companyId)
            ->where('User_ID', $userId)
            ->where('Is_Active', true)
            ->exists();
    }

    /**
     * Get company by tax ID
     */
    public function findByTaxId($taxId)
    {
        return $this->model
            ->where('Tax_ID', $taxId)
            ->active()
            ->first();
    }

    /**
     * Update profile completion
     */
    public function updateProfileCompletion($companyId)
    {
        $company = $this->find($companyId);
        
        if (!$company) {
            return false;
        }

        $percentage = $company->calculateProfileCompletion();
        $company->save();

        return $percentage;
    }

    /**
     * Get companies needing profile reminder
     */
    public function getCompaniesNeedingReminder()
    {
        return $this->model
            ->incompleteProfile()
            ->active()
            ->where(function($query) {
                $query->whereNull('Last_Profile_Reminder_At')
                      ->orWhere('Last_Profile_Reminder_At', '<', now()->subDays(7));
            })
            ->get();
    }
}