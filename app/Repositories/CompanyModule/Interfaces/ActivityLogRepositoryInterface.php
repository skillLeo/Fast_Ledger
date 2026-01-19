<?php

namespace App\Repositories\CompanyModule\Interfaces;

interface ActivityLogRepositoryInterface
{
    /**
     * Create activity log
     */
    public function log(
        $companyId,
        $activityType,
        $entityType,
        $description,
        $entityId = null,
        $oldValues = null,
        $newValues = null
    );

    /**
     * Get logs for company
     */
    public function getCompanyLogs($companyId, $limit = 50);

    /**
     * Get logs for specific entity
     */
    public function getEntityLogs($companyId, $entityType, $entityId = null);

    /**
     * Get logs by activity type
     */
    public function getLogsByType($companyId, $activityType);

    /**
     * Get recent logs
     */
    public function getRecentLogs($companyId, $days = 30);

    /**
     * Get logs for user
     */
    public function getUserLogs($companyId, $userId);
}