<?php

namespace App\Repositories\CompanyModule;

use App\Models\Module;
use App\Models\UserModuleAccess;
use App\Repositories\CompanyModule\Interfaces\ModuleRepositoryInterface;

class ModuleRepository implements ModuleRepositoryInterface
{
    protected $model;
    protected $userModuleAccess;

    public function __construct(Module $model, UserModuleAccess $userModuleAccess)
    {
        $this->model = $model;
        $this->userModuleAccess = $userModuleAccess;
    }

    /**
     * Get all active modules
     */
    public function getAllActive()
    {
        return $this->model
            ->active()
            ->ordered()
            ->get();
    }

    /**
     * Get module by name
     */
    public function findByName($moduleName)
    {
        return $this->model
            ->where('Module_Name', $moduleName)
            ->first();
    }

    /**
     * Get module by ID
     */
    public function find($id)
    {
        return $this->model->find($id);
    }

    /**
     * Get user's accessible modules
     */
    public function getUserModules($userId)
    {
        return $this->model
            ->whereHas('users', function($query) use ($userId) {
                $query->where('user_module_access.User_ID', $userId)
                      ->where('user_module_access.Has_Access', true)
                      ->where('user_module_access.Is_Active', true);
            })
            ->active()
            ->ordered()
            ->get();
    }

    /**
     * Grant module access to user
     */
    public function grantAccess($userId, $moduleId, $grantedBy = null)
    {
        return UserModuleAccess::grantAccess(
            $userId,
            $moduleId,
            $grantedBy ?? auth()->id()
        );
    }

    /**
     * Revoke module access from user
     */
    public function revokeAccess($userId, $moduleId)
    {
        return UserModuleAccess::revokeAccess($userId, $moduleId);
    }

    /**
     * Check if user has module access
     */
    public function hasAccess($userId, $moduleNameOrId)
    {
        if (is_numeric($moduleNameOrId)) {
            return $this->userModuleAccess
                ->where('User_ID', $userId)
                ->where('Module_ID', $moduleNameOrId)
                ->active()
                ->exists();
        }

        return $this->userModuleAccess
            ->where('User_ID', $userId)
            ->whereHas('module', function($query) use ($moduleNameOrId) {
                $query->where('Module_Name', $moduleNameOrId);
            })
            ->active()
            ->exists();
    }

    /**
     * Get modules user doesn't have access to
     */
    public function getAvailableModules($userId)
    {
        // Get IDs of modules user already has access to
        $accessedModuleIds = $this->userModuleAccess
            ->where('User_ID', $userId)
            ->active()
            ->pluck('Module_ID');

        // Return modules not in that list
        return $this->model
            ->active()
            ->whereNotIn('Module_ID', $accessedModuleIds)
            ->ordered()
            ->get();
    }
}
