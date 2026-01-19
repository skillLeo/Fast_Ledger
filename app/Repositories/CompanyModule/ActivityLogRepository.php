<?php

namespace App\Repositories\CompanyModule;

use App\Models\CompanyModule\CompanyActivityLog;
use App\Repositories\CompanyModule\Interfaces\ActivityLogRepositoryInterface;

class ActivityLogRepository implements ActivityLogRepositoryInterface
{
    protected $model;

    public function __construct(CompanyActivityLog $model)
    {
        $this->model = $model;
    }

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
    ) {
        return CompanyActivityLog::log(
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
     * Get logs for company
     */
    public function getCompanyLogs($companyId, $limit = 50)
    {
        return $this->model
            ->with('user')
            ->where('Company_ID', $companyId)
            ->orderBy('Created_At', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get logs for specific entity
     */
    public function getEntityLogs($companyId, $entityType, $entityId = null)
    {
        $query = $this->model
            ->with('user')
            ->where('Company_ID', $companyId)
            ->forEntity($entityType, $entityId);

        return $query->orderBy('Created_At', 'desc')->get();
    }

    /**
     * Get logs by activity type
     */
    public function getLogsByType($companyId, $activityType)
    {
        return $this->model
            ->with('user')
            ->where('Company_ID', $companyId)
            ->ofType($activityType)
            ->orderBy('Created_At', 'desc')
            ->get();
    }

    /**
     * Get recent logs
     */
    public function getRecentLogs($companyId, $days = 30)
    {
        return $this->model
            ->with('user')
            ->where('Company_ID', $companyId)
            ->recent($days)
            ->orderBy('Created_At', 'desc')
            ->get();
    }

    /**
     * Get logs for user
     */
    public function getUserLogs($companyId, $userId)
    {
        return $this->model
            ->with('user')
            ->where('Company_ID', $companyId)
            ->where('User_ID', $userId)
            ->orderBy('Created_At', 'desc')
            ->get();
    }
}