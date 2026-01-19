<?php

namespace App\Services\CompanyModule;

use App\Repositories\CompanyModule\Interfaces\ModuleRepositoryInterface;

class ModuleService
{
    protected $moduleRepo;

    public function __construct(ModuleRepositoryInterface $moduleRepo)
    {
        $this->moduleRepo = $moduleRepo;
    }

    /**
     * Get all active modules
     */
    public function getAllModules()
    {
        return $this->moduleRepo->getAllActive();
    }

    /**
     * Get user's accessible modules
     */
    public function getUserModules($userId = null)
    {
        $userId = $userId ?? auth()->id();
        return $this->moduleRepo->getUserModules($userId);
    }

    /**
     * Get available modules (user doesn't have access yet)
     */
    public function getAvailableModules($userId = null)
    {
        $userId = $userId ?? auth()->id();
        return $this->moduleRepo->getAvailableModules($userId);
    }

    /**
     * Grant module access to user
     */
    public function grantModuleAccess($userId, $moduleName, $grantedBy = null)
    {
        $module = $this->moduleRepo->findByName($moduleName);

        if (!$module) {
            return [
                'success' => false,
                'message' => 'Module not found'
            ];
        }

        if ($this->moduleRepo->hasAccess($userId, $moduleName)) {
            return [
                'success' => false,
                'message' => 'User already has access to this module'
            ];
        }

        $grantedBy = $grantedBy ?? auth()->id();

        $this->moduleRepo->grantAccess($userId, $module->Module_ID, $grantedBy);

        return [
            'success' => true,
            'message' => 'Module access granted successfully',
            'module' => $module
        ];
    }

    /**
     * Revoke module access from user
     */
    public function revokeModuleAccess($userId, $moduleName)
    {
        $module = $this->moduleRepo->findByName($moduleName);

        if (!$module) {
            return [
                'success' => false,
                'message' => 'Module not found'
            ];
        }

        if (!$this->moduleRepo->hasAccess($userId, $moduleName)) {
            return [
                'success' => false,
                'message' => 'User does not have access to this module'
            ];
        }

        $this->moduleRepo->revokeAccess($userId, $module->Module_ID);

        return [
            'success' => true,
            'message' => 'Module access revoked successfully'
        ];
    }

    /**
     * Check if user has access to module
     */
    public function hasAccess($userId, $moduleName)
    {
        return $this->moduleRepo->hasAccess($userId, $moduleName);
    }

    /**
     * Get module by name
     */
    public function getModule($moduleName)
    {
        return $this->moduleRepo->findByName($moduleName);
    }

    /**
     * Activate module for user (shortcut method)
     */
    public function activateModule($moduleName, $userId = null)
    {
        $userId = $userId ?? auth()->id();
        return $this->grantModuleAccess($userId, $moduleName);
    }
}