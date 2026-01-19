<?php

namespace App\Repositories;

use App\Models\HmrcToken;
use Illuminate\Support\Collection;

class HmrcTokenRepository
{
    /**
     * Create a new token
     */
    public function create(array $data): HmrcToken
    {
        return HmrcToken::create($data);
    }

    /**
     * Update existing token
     */
    public function update(HmrcToken $token, array $data): HmrcToken
    {
        $token->update($data);
        return $token->fresh();
    }

    /**
     * Get active token for VRN
     */
    public function getActiveToken(?string $vrn = null, ?int $userId = null): ?HmrcToken
    {
        $query = HmrcToken::active();

        if ($vrn) {
            $query->forVrn($vrn);
        }

        if ($userId) {
            $query->where('user_id', $userId);
        }

        return $query->latest()->first();
    }

    /**
     * Deactivate all tokens for a VRN
     */
    public function deactivateTokensForVrn(string $vrn): int
    {
        return HmrcToken::where('vrn', $vrn)
            ->update(['is_active' => false]);
    }

    /**
     * Get tokens expiring soon
     */
    public function getExpiringSoonTokens(int $minutes = 30): Collection
    {
        return HmrcToken::active()
            ->where('expires_at', '<=', now()->addMinutes($minutes))
            ->where('expires_at', '>', now())
            ->get();
    }

    /**
     * Delete expired inactive tokens
     */
    public function deleteExpiredTokens(): int
    {
        return HmrcToken::where('is_active', false)
            ->where('expires_at', '<', now()->subDays(30))
            ->delete();
    }

    /**
     * Find token by ID
     */
    public function find(int $id): ?HmrcToken
    {
        return HmrcToken::find($id);
    }
}