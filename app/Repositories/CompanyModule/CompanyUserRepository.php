<?php

namespace App\Repositories\CompanyModule;

use App\Models\CompanyModule\CompanyUser;
use App\Repositories\CompanyModule\Interfaces\CompanyUserRepositoryInterface;
use Illuminate\Support\Str;

class CompanyUserRepository implements CompanyUserRepositoryInterface
{
    protected $model;

    public function __construct(CompanyUser $model)
    {
        $this->model = $model;
    }

    /**
     * Add user to company
     */
    public function addUser($companyId, $userId, $role = 'viewer', $isPrimary = false)
    {
        return $this->model->create([
            'Company_ID' => $companyId,
            'User_ID' => $userId,
            'Role' => $role,
            'Is_Primary' => $isPrimary,
            'Is_Active' => true,
            'Accepted_At' => now(),
        ]);
    }

    /**
     * Remove user from company
     */
    public function removeUser($companyId, $userId)
    {
        return $this->model
            ->where('Company_ID', $companyId)
            ->where('User_ID', $userId)
            ->update(['Is_Active' => false]);
    }

    /**
     * Update user role in company
     */
    public function updateRole($companyId, $userId, $role)
    {
        return $this->model
            ->where('Company_ID', $companyId)
            ->where('User_ID', $userId)
            ->update(['Role' => $role]);
    }

    /**
     * Get all users for a company
     */
    public function getCompanyUsers($companyId)
    {
        return $this->model
            ->with('user')
            ->where('Company_ID', $companyId)
            ->active()
            ->orderBy('Is_Primary', 'desc')
            ->orderBy('Role')
            ->get();
    }

    /**
     * Get user's role in company
     */
    public function getUserRole($companyId, $userId)
    {
        $companyUser = $this->model
            ->where('Company_ID', $companyId)
            ->where('User_ID', $userId)
            ->active()
            ->first();

        return $companyUser ? $companyUser->Role : null;
    }

    /**
     * Check if user is company owner
     */
    public function isOwner($companyId, $userId)
    {
        return $this->model
            ->where('Company_ID', $companyId)
            ->where('User_ID', $userId)
            ->where('Role', 'owner')
            ->active()
            ->exists();
    }

    /**
     * Check if user is company admin
     */
    public function isAdmin($companyId, $userId)
    {
        return $this->model
            ->where('Company_ID', $companyId)
            ->where('User_ID', $userId)
            ->whereIn('Role', ['owner', 'admin'])
            ->active()
            ->exists();
    }

    /**
     * Create invitation
     */
    public function createInvitation($companyId, $email, $role, $invitedBy)
    {
        // Find user by email
        $user = \App\Models\User::where('email', $email)->first();

        if (!$user) {
            return [
                'success' => false,
                'message' => 'User not found with this email address'
            ];
        }

        // Check if user already has access
        $existing = $this->model
            ->where('Company_ID', $companyId)
            ->where('User_ID', $user->User_ID)
            ->first();

        if ($existing && $existing->Is_Active) {
            return [
                'success' => false,
                'message' => 'User already has access to this company'
            ];
        }

        // Create or update invitation
        $invitation = $this->model->updateOrCreate(
            [
                'Company_ID' => $companyId,
                'User_ID' => $user->User_ID,
            ],
            [
                'Role' => $role,
                'Invited_By' => $invitedBy,
                'Invited_At' => now(),
                'Invitation_Token' => Str::random(64),
                'Invitation_Expires_At' => now()->addDays(7),
                'Is_Active' => false,
            ]
        );

        return [
            'success' => true,
            'invitation' => $invitation
        ];
    }

    /**
     * Accept invitation
     */
    public function acceptInvitation($token)
    {
        $invitation = $this->model
            ->where('Invitation_Token', $token)
            ->whereNull('Accepted_At')
            ->where('Invitation_Expires_At', '>', now())
            ->first();

        if (!$invitation) {
            return [
                'success' => false,
                'message' => 'Invalid or expired invitation'
            ];
        }

        $invitation->acceptInvitation();

        return [
            'success' => true,
            'company_user' => $invitation
        ];
    }

    /**
     * Get pending invitations for company
     */
    public function getPendingInvitations($companyId)
    {
        return $this->model
            ->with(['user', 'invitedBy'])
            ->where('Company_ID', $companyId)
            ->pendingInvitation()
            ->orderBy('Invited_At', 'desc')
            ->get();
    }
}