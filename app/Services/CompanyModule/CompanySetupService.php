<?php
// app/Services/CompanyModule/CompanySetupService.php

namespace App\Services\CompanyModule;

use App\Models\CompanyModule\Company;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class CompanySetupService
{
    /**
     * Setup a new company
     */
    public function setupCompany(array $data): array
    {
        try {
            DB::beginTransaction();

            $user = auth()->user();

            // ✅ Prepare company data
            $companyData = [
                'User_ID' => $user->User_ID,
                'Company_Name' => $data['Company_Name'],
                'Trade_Name' => $data['Trade_Name'] ?? null,
                'Country' => $data['Country'],
                'Company_Type_UK' => $data['Company_Type_UK'] ?? null,
                'Company_Type_ES' => $data['Company_Type_ES'] ?? null,
                'Tax_ID' => $data['Tax_ID'],
                'Country_Tax_Residence' => $data['Country_Tax_Residence'],
                'Tax_Regime' => $data['Tax_Regime'] ?? null,
                'Street_Address' => $data['Street_Address'],
                'City' => $data['City'],
                'State' => $data['State'] ?? null,
                'Postal_Code' => $data['Postal_Code'],
                'Phone_Number' => $data['Phone_Number'] ?? null,
                'Email' => $data['Email'] ?? null,
                'Website' => $data['Website'] ?? null,
                'Invoice_Prefix' => $data['Invoice_Prefix'] ?? 'INV-',
                'Verifactu_Enabled' => isset($data['Verifactu_Enabled']) ? 1 : 0,
                'Is_Test_Mode' => isset($data['Is_Test_Mode']) ? 1 : 0,
                'Is_Active' => 1,
                'Created_By' => $user->User_ID,
                'Created_On' => now(),
            ];

            // ✅ Handle logo upload
            if (isset($data['logo']) && $data['logo']) {
                $logoPath = $data['logo']->store('company-logos', 'public');
                $companyData['Logo_Path'] = $logoPath;
            }

            // ✅ Handle AEAT certificate upload
            if (isset($data['aeat_certificate']) && $data['aeat_certificate']) {
                $certPath = $data['aeat_certificate']->store('company-certificates', 'private');
                $companyData['Verifactu_Certificate_Path'] = $certPath;
            }

            // ✅ Calculate profile completion
            $companyData['Profile_Completion_Percentage'] = $this->calculateProfileCompletion($companyData);

            // ✅ Create company
            $company = Company::create($companyData);

            // ✅ Attach user as owner
            $company->users()->attach($user->User_ID, [
                'Role' => 'owner',
                'Is_Primary' => $user->companies()->count() === 1,
                'Is_Active' => true,
                'Created_At' => now(),
            ]);

            DB::commit();

            Log::info('Company created successfully', [
                'company_id' => $company->id,
                'company_name' => $company->Company_Name,
                'user_id' => $user->User_ID,
            ]);

            return [
                'success' => true,
                'company' => $company,
                'message' => 'Company created successfully',
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Company setup failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'data' => $data ?? [],
            ]);

            return [
                'success' => false,
                'message' => 'Failed to create company: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Calculate profile completion percentage
     */
    protected function calculateProfileCompletion(array $data): int
    {
        $requiredFields = [
            'Company_Name',
            'Street_Address',
            'City',
            'Postal_Code',
            'Country',
            'Tax_ID',
            'Country_Tax_Residence',
        ];

        $optionalFields = [
            'Trade_Name',
            'State',
            'Phone_Number',
            'Email',
            'Website',
            'Logo_Path',
            'Invoice_Prefix',
        ];

        $filledRequired = 0;
        foreach ($requiredFields as $field) {
            if (!empty($data[$field])) {
                $filledRequired++;
            }
        }

        $filledOptional = 0;
        foreach ($optionalFields as $field) {
            if (!empty($data[$field])) {
                $filledOptional++;
            }
        }

        // Required fields = 70%, Optional = 30%
        $requiredPercentage = (count($requiredFields) > 0) 
            ? ($filledRequired / count($requiredFields)) * 70 
            : 0;
        
        $optionalPercentage = (count($optionalFields) > 0) 
            ? ($filledOptional / count($optionalFields)) * 30 
            : 0;

        return (int) round($requiredPercentage + $optionalPercentage);
    }

    /**
     * Check if user needs setup
     */
    public function needsSetup(): bool
    {
        $user = auth()->user();
        return $user->companies()->count() === 0;
    }

    /**
     * ✅ NEW: Check if user has incomplete profiles
     */
    public function hasIncompleteProfiles(): bool
    {
        $user = auth()->user();
        
        $incompleteCount = $user->companies()
            ->where('Profile_Completion_Percentage', '<', 100)
            ->count();
        
        return $incompleteCount > 0;
    }

    /**
     * ✅ NEW: Get incomplete company profiles
     */
    public function getIncompleteCompanies()
    {
        $user = auth()->user();
        
        return $user->companies()
            ->where('Profile_Completion_Percentage', '<', 100)
            ->orderBy('Profile_Completion_Percentage', 'asc')
            ->get();
    }

    /**
     * Get setup progress for a company
     */
    public function getSetupProgress(int $companyId): array
    {
        try {
            $company = Company::findOrFail($companyId);

            $requiredFields = [
                'Company_Name' => 'Company Name',
                'Country' => 'Country',
                'Tax_ID' => 'Tax ID',
                'Street_Address' => 'Street Address',
                'City' => 'City',
                'Postal_Code' => 'Postal Code',
            ];

            $optionalFields = [
                'Trade_Name' => 'Trade Name',
                'Phone_Number' => 'Phone Number',
                'Email' => 'Email',
                'Website' => 'Website',
                'Logo_Path' => 'Company Logo',
            ];

            $missingFields = [];
            $completedFields = 0;
            $totalFields = count($requiredFields) + count($optionalFields);

            foreach ($requiredFields as $field => $label) {
                if (empty($company->$field)) {
                    $missingFields[] = $label;
                } else {
                    $completedFields++;
                }
            }

            foreach ($optionalFields as $field => $label) {
                if (!empty($company->$field)) {
                    $completedFields++;
                }
            }

            $percentage = ($totalFields > 0) 
                ? round(($completedFields / $totalFields) * 100) 
                : 0;

            return [
                'success' => true,
                'company' => $company,
                'percentage' => $percentage,
                'missing_fields' => $missingFields,
            ];

        } catch (\Exception $e) {
            Log::error('Get setup progress failed: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Company not found',
            ];
        }
    }

    /**
     * Skip setup (for testing purposes)
     */
    public function skipSetup(): array
    {
        $user = auth()->user();

        if ($user->companies()->count() > 0) {
            return [
                'success' => false,
                'message' => 'You already have companies set up',
            ];
        }

        return [
            'success' => true,
            'message' => 'Setup skipped',
        ];
    }
}