<?php

namespace App\Repositories;

use App\Models\VatSubmission;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class VatSubmissionRepository
{
    /**
     * Create a new VAT submission
     */
    public function create(array $data): VatSubmission
    {
        return VatSubmission::create($data);
    }

    /**
     * Get submission by ID
     */
    public function find(int $id): ?VatSubmission
    {
        return VatSubmission::find($id);
    }

    /**
     * Get submission by period key
     */
    public function findByPeriodKey(string $vrn, string $periodKey): ?VatSubmission
    {
        return VatSubmission::where('vrn', $vrn)
            ->forPeriod($periodKey)
            ->first();
    }

    /**
     * Get all submissions for VRN
     */
    public function getByVrn(string $vrn, int $perPage = 15): LengthAwarePaginator
    {
        return VatSubmission::where('vrn', $vrn)
            ->with('submittedBy')
            ->latest('submitted_at')
            ->paginate($perPage);
    }

    /**
     * Get successful submissions
     */
    public function getSuccessfulSubmissions(string $vrn): Collection
    {
        return VatSubmission::where('vrn', $vrn)
            ->successful()
            ->orderBy('submitted_at', 'desc')
            ->get();
    }

    /**
     * Get failed submissions
     */
    public function getFailedSubmissions(string $vrn): Collection
    {
        return VatSubmission::where('vrn', $vrn)
            ->failed()
            ->orderBy('submitted_at', 'desc')
            ->get();
    }

    /**
     * Check if period already submitted
     */
    public function isPeriodSubmitted(string $vrn, string $periodKey): bool
    {
        return VatSubmission::where('vrn', $vrn)
            ->forPeriod($periodKey)
            ->successful()
            ->exists();
    }

    /**
     * Get recent submissions
     */
    public function getRecentSubmissions(int $limit = 10): Collection
    {
        return VatSubmission::with('submittedBy')
            ->latest('submitted_at')
            ->limit($limit)
            ->get();
    }
}
