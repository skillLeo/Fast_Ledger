<?php

namespace App\Services\Hmrc;

use App\Models\HmrcToken;
use App\Repositories\HmrcTokenRepository;
use App\Exceptions\HmrcAuthenticationException;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class OAuthService
{
    protected HmrcClient $client;
    protected HmrcTokenRepository $tokenRepository;

    public function __construct(
        HmrcClient $client,
        HmrcTokenRepository $tokenRepository
    ) {
        $this->client = $client;
        $this->tokenRepository = $tokenRepository;
    }

    /**
     * Generate authorization URL for OAuth flow
     */
    public function getAuthorizationUrl(string $redirectUri, string $state, ?string $scopes = null): string
    {
        $params = http_build_query([
            'response_type' => 'code',
            'client_id' => config('hmrc.client_id'),
            'scope' => $scopes ?? config('hmrc.scopes'),
            'redirect_uri' => $redirectUri,
            'state' => $state,
        ]);

        return "{$this->client->getBaseUrl()}/oauth/authorize?{$params}";
    }

    /**
     * Exchange authorization code for access token
     */
    public function handleCallback(string $code, int $userId, ?string $vrn = null, ?string $scopes = null): HmrcToken
    {
        $redirectUri = config('hmrc.redirect_uri');
        $clientId = config('hmrc.client_id');
        $clientSecret = config('hmrc.client_secret');

        try {
            $tokenData = $this->client->postUnauthenticated('/oauth/token', [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'redirect_uri' => $redirectUri,
            ]);

            return $this->storeToken($tokenData, $userId, $vrn, $scopes);

        } catch (\Exception $e) {
            Log::error('OAuth token exchange failed', [
                'error' => $e->getMessage(),
            ]);

            throw new HmrcAuthenticationException(
                'Failed to obtain access token from HMRC'
            );
        }
    }

    /**
     * Refresh an expired access token
     */
    public function refreshToken(HmrcToken $token): HmrcToken
    {
        $clientId = config('hmrc.client_id');
        $clientSecret = config('hmrc.client_secret');

        try {
            $tokenData = $this->client->postUnauthenticated('/oauth/token', [
                'grant_type' => 'refresh_token',
                'refresh_token' => $token->refresh_token,
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
            ]);

            // Update existing token
            return $this->updateToken($token, $tokenData);

        } catch (\Exception $e) {
            Log::error('Token refresh failed', [
                'token_id' => $token->id,
                'error' => $e->getMessage(),
            ]);

            // Mark token as inactive if refresh fails
            $token->update(['is_active' => false]);

            throw new HmrcAuthenticationException(
                'Failed to refresh access token. Please reconnect your HMRC account.'
            );
        }
    }

    /**
     * Get valid access token (refresh if needed)
     */
    public function getValidAccessToken(int $userId, ?string $vrn = null): string
    {
        $token = $this->tokenRepository->getActiveToken($vrn, $userId);

        if (!$token) {
            throw new RuntimeException('No active HMRC connection found. Please connect your account.');
        }

        // Refresh if needed
        if ($token->needsRefresh() || $token->isExpired()) {
            Log::info('Token expired or expiring soon, refreshing...', [
                'token_id' => $token->id,
            ]);
            $token = $this->refreshToken($token);
        }

        return (string) $token->access_token;
    }

    /**
     * Get valid token object (refresh if needed)
     */
    public function getValidToken(?string $vrn = null, ?int $userId = null): HmrcToken
    {
        $userId = $userId ?? auth()->id();
        if (!$userId) {
            throw new RuntimeException('No authenticated user found.');
        }

        $token = $this->tokenRepository->getActiveToken($vrn, $userId);

        if (!$token) {
            throw new HmrcAuthenticationException(
                'No active HMRC token found. Please authenticate.'
            );
        }

        // Refresh if expired or expiring soon
        if ($token->isExpired() || $token->isExpiringSoon()) {
            Log::info('Token expired or expiring soon, refreshing...', [
                'token_id' => $token->id,
            ]);

            $token = $this->refreshToken($token);
        }

        return $token;
    }

    /**
     * Check if user has active connection
     */
    public function hasActiveConnection(int $userId, ?string $vrn = null): bool
    {
        $token = $this->tokenRepository->getActiveToken($vrn, $userId);
        return $token !== null && !$token->isExpired();
    }

    /**
     * Get active token for user
     */
    public function getActiveToken(int $userId, ?string $vrn = null): ?HmrcToken
    {
        return $this->tokenRepository->getActiveToken($vrn, $userId);
    }

    /**
     * Disconnect HMRC account
     */
    public function disconnect(int $userId, ?string $vrn = null): void
    {
        if ($vrn !== null) {
            $this->tokenRepository->deactivateTokensForVrn($vrn);
        } else {
            $this->tokenRepository->deactivateTokensForUser($userId);
        }
    }

    /**
     * Store new token in database
     */
    protected function storeToken(array $tokenData, int $userId, ?string $vrn = null, ?string $scopes = null): HmrcToken
    {
        $accessToken = (string) ($tokenData['access_token'] ?? '');
        $refreshToken = (string) ($tokenData['refresh_token'] ?? '');
        $expiresIn = (int) ($tokenData['expires_in'] ?? 0);
        $scope = $scopes ?? (string) ($tokenData['scope'] ?? config('hmrc.scopes'));

        if ($accessToken === '' || $refreshToken === '' || $expiresIn <= 0) {
            Log::error('HMRC OAuth token exchange returned invalid payload', ['payload' => $tokenData]);
            throw new RuntimeException('Invalid token payload from HMRC.');
        }

        $data = [
            'user_id' => $userId,
            'vrn' => $vrn,
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'expires_at' => now()->addSeconds($expiresIn),
            'token_type' => $tokenData['token_type'] ?? 'Bearer',
            'scope' => $scope,
            'is_active' => true,
        ];

        // Deactivate old tokens for this user/VRN combination
        if ($vrn !== null) {
            $this->tokenRepository->deactivateTokensForVrn($vrn);
        } else {
            $this->tokenRepository->deactivateTokensForUser($userId);
        }

        return $this->tokenRepository->create($data);
    }

    /**
     * Update existing token with new data
     */
    protected function updateToken(HmrcToken $token, array $tokenData): HmrcToken
    {
        $accessToken = (string) ($tokenData['access_token'] ?? '');
        $newRefreshToken = (string) ($tokenData['refresh_token'] ?? $token->refresh_token);
        $expiresIn = (int) ($tokenData['expires_in'] ?? 0);

        if ($accessToken === '' || $expiresIn <= 0) {
            Log::error('HMRC OAuth refresh returned invalid payload', ['payload' => $tokenData]);
            throw new RuntimeException('Invalid refresh payload from HMRC.');
        }

        return $this->tokenRepository->update($token, [
            'access_token' => $accessToken,
            'refresh_token' => $newRefreshToken,
            'expires_at' => now()->addSeconds($expiresIn),
            'token_type' => $tokenData['token_type'] ?? 'Bearer',
            'last_refreshed_at' => now(),
        ]);
    }

    /**
     * Generate secure state parameter for CSRF protection
     */
    public function generateState(): string
    {
        $state = bin2hex(random_bytes(16));
        session(['hmrc_oauth_state' => $state]);
        return $state;
    }

    /**
     * Verify state parameter
     */
    public function verifyState(string $state): bool
    {
        $sessionState = session('hmrc_oauth_state');
        session()->forget('hmrc_oauth_state');

        return $sessionState && hash_equals($sessionState, $state);
    }

    /**
     * Revoke token (mark as inactive)
     */
    public function revokeToken(HmrcToken $token): void
    {
        $this->tokenRepository->update($token, ['is_active' => false]);

        Log::info('Token revoked', ['token_id' => $token->id]);
    }
}