<?php

namespace App\Repositories\CompanyModule\Interfaces;

interface ModuleRepositoryInterface
{
    /**
     * Get all active modules
     */
    public function getAllActive();

    /**
     * Get module by name
     */
    public function findByName($moduleName);

    /**
     * Get module by ID
     */
    public function find($id);

    /**
     * Get user's accessible modules
     */
    public function getUserModules($userId);

    /**
     * Grant module access to user
     */
    public function grantAccess($userId, $moduleId, $grantedBy = null);

    /**
     * Revoke module access from user
     */
    public function revokeAccess($userId, $moduleId);

    /**
     * Check if user has module access
     */
    public function hasAccess($userId, $moduleNameOrId);

    /**
     * Get modules user doesn't have access to
     */
    public function getAvailableModules($userId);
}