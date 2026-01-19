<?php

namespace App\Repositories;

use App\Models\VatObligation;
use Illuminate\Support\Collection;

class VatObligationRepository
{
    /**
     * Get all obligations for VRN
     */
    public function getByVrn(string $vrn): Collection
    {
        return VatObligation::where('vrn', $vrn)
            ->orderBy('due_date', 'asc')
            ->get();
    }

    /**
     * Get open obligations
     */
    public function getOpenObligations(string $vrn): Collection
    {
        return VatObligation::where('vrn', $vrn)
            ->where('status', 'O')
            ->orderBy('due_date', 'asc')
            ->get();
    }

    /**
     * Get overdue obligations
     */
    public function getOverdueObligations(string $vrn): Collection
    {
        return VatObligation::where('vrn', $vrn)
            ->where('status', 'O')
            ->where('due_date', '<', now())
            ->orderBy('due_date', 'asc')
            ->get();
    }

    /**
     * Find by period key
     */
    public function findByPeriodKey(string $vrn, string $periodKey): ?VatObligation
    {
        return VatObligation::where('vrn', $vrn)
            ->where('period_key', $periodKey)
            ->first();
    }
}