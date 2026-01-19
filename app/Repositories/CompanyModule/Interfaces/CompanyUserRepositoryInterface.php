<?php

namespace App\Repositories\CompanyModule\Interfaces;

interface CompanyUserRepositoryInterface
{
    /**
     * Add user to company
     */
    public function addUser($companyId, $userId, $role = 'viewer', $isPrimary = false);

    /**
     * Remove user from company
     */
    public function removeUser($companyId, $userId);

    /**
     * Update user role in company
     */
    public function updateRole($companyId, $userId, $role);

    /**
     * Get all users for a company
     */
    public function getCompanyUsers($companyId);

    /**
     * Get user's role in company
     */
    public function getUserRole($companyId, $userId);

    /**
     * Check if user is company owner
     */
    public function isOwner($companyId, $userId);

    /**
     * Check if user is company admin
     */
    public function isAdmin($companyId, $userId);

    /**
     * Create invitation
     */
    public function createInvitation($companyId, $email, $role, $invitedBy);

    /**
     * Accept invitation
     */
    public function acceptInvitation($token);

    /**
     * Get pending invitations for company
     */
    public function getPendingInvitations($companyId);
}