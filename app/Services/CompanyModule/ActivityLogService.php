<?php

namespace App\Services\CompanyModule;

use App\Repositories\CompanyModule\Interfaces\ActivityLogRepositoryInterface;

class ActivityLogService
{
    protected $activityLogRepo;

    public function __construct(ActivityLogRepositoryInterface $activityLogRepo)
    {
        $this->activityLogRepo = $activityLogRepo;
    }

    /**
     * Log an activity
     */
    public function log(
        $companyId,
        $activityType,
        $entityType,
        $description,
        $entityId = null,
        $oldValues = null,
        $newValues = null
    ) {
        return $this->activityLogRepo->log(
            $companyId,
            $activityType,
            $entityType,
            $description,
            $entityId,
            $oldValues,
            $newValues
        );
    }

    /**
     * Get company logs
     */
    public function getCompanyLogs($companyId, $limit = 50)
    {
        return $this->activityLogRepo->getCompanyLogs($companyId, $limit);
    }

    /**
     * Get logs for specific entity
     */
    public function getEntityLogs($companyId, $entityType, $entityId = null)
    {
        return $this->activityLogRepo->getEntityLogs($companyId, $entityType, $entityId);
    }

    /**
     * Get logs by activity type
     */
    public function getLogsByType($companyId, $activityType)
    {
        return $this->activityLogRepo->getLogsByType($companyId, $activityType);
    }

    /**
     * Get recent logs
     */
    public function getRecentLogs($companyId, $days = 30)
    {
        return $this->activityLogRepo->getRecentLogs($companyId, $days);
    }

    /**
     * Get user logs
     */
    public function getUserLogs($companyId, $userId = null)
    {
        $userId = $userId ?? auth()->id();
        return $this->activityLogRepo->getUserLogs($companyId, $userId);
    }

    /**
     * Helper: Log company created
     */
    public function logCompanyCreated($companyId, $companyName)
    {
        return $this->log(
            $companyId,
            'company_created',
            'company',
            "Company created: {$companyName}",
            $companyId
        );
    }

    /**
     * Helper: Log company updated
     */
    public function logCompanyUpdated($companyId, $companyName, $oldValues = null, $newValues = null)
    {
        return $this->log(
            $companyId,
            'company_updated',
            'company',
            "Company updated: {$companyName}",
            $companyId,
            $oldValues,
            $newValues
        );
    }

    /**
     * Helper: Log user invited
     */
    public function logUserInvited($companyId, $email, $role)
    {
        return $this->log(
            $companyId,
            'user_invited',
            'company_user',
            "User invited: {$email} as {$role}",
            null,
            null,
            ['email' => $email, 'role' => $role]
        );
    }

    /**
     * Helper: Log user login
     */
    public function logUserLogin($companyId)
    {
        return $this->log(
            $companyId,
            'user_login',
            'user',
            "User logged in",
            auth()->id()
        );
    }

    /**
     * Helper: Log settings updated
     */
    public function logSettingsUpdated($companyId, $settingName, $oldValue, $newValue)
    {
        return $this->log(
            $companyId,
            'settings_updated',
            'settings',
            "Setting updated: {$settingName}",
            null,
            ['setting' => $settingName, 'value' => $oldValue],
            ['setting' => $settingName, 'value' => $newValue]
        );
    }
}