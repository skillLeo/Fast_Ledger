<?php

namespace App\Services\CompanyModule;
use App\Models\CompanyModule\Company; // ✅ ADD THIS IMPORT

use App\Repositories\CompanyModule\Interfaces\CompanyRepositoryInterface;
use App\Repositories\CompanyModule\Interfaces\CompanyUserRepositoryInterface;
use App\Repositories\CompanyModule\Interfaces\ActivityLogRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Exception;

class CompanyService
{
    protected $companyRepo;
    protected $companyUserRepo;
    protected $activityLogRepo;

    public function __construct(
        CompanyRepositoryInterface $companyRepo,
        CompanyUserRepositoryInterface $companyUserRepo,
        ActivityLogRepositoryInterface $activityLogRepo
    ) {
        $this->companyRepo = $companyRepo;
        $this->companyUserRepo = $companyUserRepo;
        $this->activityLogRepo = $activityLogRepo;
    }

    /**
     * Create a new company
     */
 

    /**
     * Update company
     */
    public function updateCompany($companyId, array $data, $userId = null)
    {
        DB::beginTransaction();
        
        try {
            $userId = $userId ?? auth()->id();

            // Check if user has access
            if (!$this->companyRepo->userHasAccess($userId, $companyId)) {
                return [
                    'success' => false,
                    'message' => 'You do not have permission to update this company'
                ];
            }

            // Get old data for logging
            $oldCompany = $this->companyRepo->find($companyId);
            $oldData = $oldCompany->toArray();

            // Handle logo upload if present
            if (isset($data['logo']) && $data['logo']) {
                // Delete old logo if exists
                if ($oldCompany->Logo_Path) {
                    Storage::disk('public')->delete($oldCompany->Logo_Path);
                }
                $data['Logo_Path'] = $this->uploadLogo($data['logo']);
                unset($data['logo']);
            }

            // Handle AEAT certificate upload if present
            if (isset($data['aeat_certificate']) && $data['aeat_certificate']) {
                // Delete old certificate if exists
                if ($oldCompany->AEAT_Certificate_Path) {
                    Storage::disk('public')->delete($oldCompany->AEAT_Certificate_Path);
                }
                $data['AEAT_Certificate_Path'] = $this->uploadCertificate($data['aeat_certificate']);
                unset($data['aeat_certificate']);
            }

            // Update company
            $company = $this->companyRepo->update($companyId, $data);

            // Log activity
            $this->activityLogRepo->log(
                $companyId,
                'company_updated',
                'company',
                'Company profile updated: ' . $company->Company_Name,
                $companyId,
                $oldData,
                $company->toArray()
            );

            DB::commit();

            return [
                'success' => true,
                'company' => $company,
                'message' => 'Company updated successfully'
            ];

        } catch (Exception $e) {
            DB::rollBack();

            return [
                'success' => false,
                'message' => 'Failed to update company: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Delete company (soft delete)
     */
    public function deleteCompany($companyId, $userId = null)
    {
        DB::beginTransaction();
        
        try {
            $userId = $userId ?? auth()->id();

            // Check if user is owner
            if (!$this->companyRepo->userOwnsCompany($userId, $companyId)) {
                return [
                    'success' => false,
                    'message' => 'Only the company owner can delete the company'
                ];
            }

            $company = $this->companyRepo->find($companyId);

            // Soft delete
            $this->companyRepo->delete($companyId);

            // Log activity
            $this->activityLogRepo->log(
                $companyId,
                'company_deleted',
                'company',
                'Company archived: ' . $company->Company_Name,
                $companyId,
                $company->toArray(),
                null
            );

            DB::commit();

            return [
                'success' => true,
                'message' => 'Company deleted successfully'
            ];

        } catch (Exception $e) {
            DB::rollBack();

            return [
                'success' => false,
                'message' => 'Failed to delete company: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get user's companies
     */
    public function getUserCompanies($userId = null)
    {
        $userId = $userId ?? auth()->id();
        return $this->companyRepo->getUserCompanies($userId);
    }

    /**
     * Get company by ID
     */
    public function getCompany($companyId, $userId = null)
    {
        $userId = $userId ?? auth()->id();

        // Check if user has access
        if (!$this->companyRepo->userHasAccess($userId, $companyId)) {
            return [
                'success' => false,
                'message' => 'You do not have access to this company'
            ];
        }

        $company = $this->companyRepo->findWithRelations($companyId, ['users', 'owner']);

        return [
            'success' => true,
            'company' => $company
        ];
    }

    /**
     * Get companies with incomplete profiles
     */
    public function getIncompleteProfiles($userId = null)
    {
        $userId = $userId ?? auth()->id();
        return $this->companyRepo->getIncompleteProfiles($userId);
    }

    /**
     * Update profile completion
     */
    public function updateProfileCompletion($companyId)
    {
        return $this->companyRepo->updateProfileCompletion($companyId);
    }

    /**
     * Upload company logo
     */
    protected function uploadLogo($file)
    {
        $path = $file->store('company-logos', 'public');
        return $path;
    }

    /**
     * Upload AEAT certificate
     */
    protected function uploadCertificate($file)
    {
        $path = $file->store('company-certificates', 'public');
        return $path;
    }

    /**
     * Get company users
     */
    public function getCompanyUsers($companyId, $userId = null)
    {
        $userId = $userId ?? auth()->id();

        // Check if user has access
        if (!$this->companyRepo->userHasAccess($userId, $companyId)) {
            return [
                'success' => false,
                'message' => 'You do not have access to this company'
            ];
        }

        $users = $this->companyUserRepo->getCompanyUsers($companyId);

        return [
            'success' => true,
            'users' => $users
        ];
    }

    /**
     * Invite user to company
     */
    public function inviteUser($companyId, $email, $role, $userId = null)
    {
        $userId = $userId ?? auth()->id();

        // Check if user is admin
        if (!$this->companyUserRepo->isAdmin($companyId, $userId)) {
            return [
                'success' => false,
                'message' => 'Only admins can invite users'
            ];
        }

        $result = $this->companyUserRepo->createInvitation($companyId, $email, $role, $userId);

        if ($result['success']) {
            // Log activity
            $this->activityLogRepo->log(
                $companyId,
                'user_invited',
                'company_user',
                "User invited: {$email} as {$role}",
                null,
                null,
                ['email' => $email, 'role' => $role]
            );

            // TODO: Send invitation email
        }

        return $result;
    }

    /**
     * Remove user from company
     */
    public function removeUser($companyId, $userIdToRemove, $userId = null)
    {
        $userId = $userId ?? auth()->id();

        // Check if user is admin
        if (!$this->companyUserRepo->isAdmin($companyId, $userId)) {
            return [
                'success' => false,
                'message' => 'Only admins can remove users'
            ];
        }

        // Cannot remove owner
        if ($this->companyUserRepo->isOwner($companyId, $userIdToRemove)) {
            return [
                'success' => false,
                'message' => 'Cannot remove the company owner'
            ];
        }

        $this->companyUserRepo->removeUser($companyId, $userIdToRemove);

        // Log activity
        $this->activityLogRepo->log(
            $companyId,
            'user_removed',
            'company_user',
            "User removed from company",
            $userIdToRemove
        );

        return [
            'success' => true,
            'message' => 'User removed successfully'
        ];
    }

    /**
     * Update user role
     */
    public function updateUserRole($companyId, $userIdToUpdate, $newRole, $userId = null)
    {
        $userId = $userId ?? auth()->id();

        // Check if user is owner
        if (!$this->companyUserRepo->isOwner($companyId, $userId)) {
            return [
                'success' => false,
                'message' => 'Only the owner can change user roles'
            ];
        }

        // Cannot change owner role
        if ($this->companyUserRepo->isOwner($companyId, $userIdToUpdate)) {
            return [
                'success' => false,
                'message' => 'Cannot change the owner role'
            ];
        }

        $oldRole = $this->companyUserRepo->getUserRole($companyId, $userIdToUpdate);

        $this->companyUserRepo->updateRole($companyId, $userIdToUpdate, $newRole);

        // Log activity
        $this->activityLogRepo->log(
            $companyId,
            'user_role_updated',
            'company_user',
            "User role changed from {$oldRole} to {$newRole}",
            $userIdToUpdate,
            ['role' => $oldRole],
            ['role' => $newRole]
        );

        return [
            'success' => true,
            'message' => 'User role updated successfully'
        ];
    }

    /**
     * Get activity logs
     */
    public function getActivityLogs($companyId, $userId = null, $limit = 50)
    {
        $userId = $userId ?? auth()->id();

        // Check if user has access
        if (!$this->companyRepo->userHasAccess($userId, $companyId)) {
            return [
                'success' => false,
                'message' => 'You do not have access to this company'
            ];
        }

        $logs = $this->activityLogRepo->getCompanyLogs($companyId, $limit);

        return [
            'success' => true,
            'logs' => $logs
        ];
    }















    public function createCompany(array $data): array
    {
        try {
            DB::beginTransaction();

            $user = auth()->user();

            // Handle logo upload
            if (isset($data['logo']) && $data['logo']) {
                $logoPath = $data['logo']->store('company_logos', 'public');
                $data['Logo_Path'] = $logoPath;
                unset($data['logo']);
            }

            // Handle certificate upload
            if (isset($data['aeat_certificate']) && $data['aeat_certificate']) {
                $certPath = $data['aeat_certificate']->store('aeat_certificates', 'private');
                $data['Verifactu_Certificate_Path'] = $certPath;
                unset($data['aeat_certificate']);
            }

            // Set defaults
            $data['Created_By'] = $user->User_ID;
            $data['Is_Active'] = true;
            $data['Profile_Completion_Percentage'] = $this->calculateProfileCompletion($data);

            // Create company
            $company = Company::create($data);

            // Attach user as owner
            $company->users()->attach($user->User_ID, [
                'Role' => 'owner',
                'Is_Primary' => $user->companies()->count() === 1, // First company is primary
                'Is_Active' => true,
                'Created_At' => now(),
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Company created successfully',
                'company' => $company
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Company creation failed: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to create company: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Calculate profile completion percentage
     */
    protected function calculateProfileCompletion(array $data): int
    {
        $requiredFields = [
            'Company_Name', 'Street_Address', 'City', 'Postal_Code',
            'Country', 'Tax_ID', 'Country_Tax_Residence'
        ];

        $optionalFields = [
            'Trade_Name', 'State', 'Phone_Number', 'Email', 
            'Website', 'Logo_Path', 'Invoice_Prefix'
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
        $requiredPercentage = ($filledRequired / count($requiredFields)) * 70;
        $optionalPercentage = ($filledOptional / count($optionalFields)) * 30;

        return (int) round($requiredPercentage + $optionalPercentage);
    }

}