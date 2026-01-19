<?php

namespace App\Services\Hmrc;

use Illuminate\Support\Facades\Auth;
use RuntimeException;

/**
 * HmrcApiClient - Facade for automatic token management
 *
 * This class provides automatic token management by wrapping HmrcClient and OAuthService.
 * It automatically retrieves and refreshes tokens for the authenticated user.
 */
class HmrcApiClient
{
    public function __construct(
        protected HmrcClient $client,
        protected OAuthService $oauthService
    ) {}

    /**
     * Make authenticated GET request with automatic token management
     */
    public function get(string $endpoint, array $headers = [], ?string $testScenario = null): array
    {
        $accessToken = $this->getAccessToken();
        return $this->client->get($endpoint, $accessToken, $headers, $testScenario);
    }

    /**
     * Make authenticated POST request with automatic token management
     */
    public function post(string $endpoint, array $data, array $headers = [], ?string $testScenario = null): array
    {
        $accessToken = $this->getAccessToken();
        return $this->client->post($endpoint, $accessToken, $data, $headers, $testScenario);
    }

    /**
     * Make authenticated PUT request with automatic token management
     */
    public function put(string $endpoint, array $data, array $headers = [], ?string $testScenario = null): array
    {
        $accessToken = $this->getAccessToken();
        return $this->client->put($endpoint, $accessToken, $data, $headers, $testScenario);
    }

    /**
     * Make authenticated DELETE request with automatic token management
     */
    public function delete(string $endpoint, array $headers = [], ?string $testScenario = null): array
    {
        $accessToken = $this->getAccessToken();
        return $this->client->delete($endpoint, $accessToken, $headers, $testScenario);
    }

    /**
     * Get valid access token for authenticated user
     */
    protected function getAccessToken(): string
    {
        $userId = Auth::id();
        if ($userId === null) {
            throw new RuntimeException('No authenticated user for HMRC API call.');
        }

        return $this->oauthService->getValidAccessToken((int) $userId);
    }
}


