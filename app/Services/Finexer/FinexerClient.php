<?php

namespace App\Services\Finexer;

use App\Exceptions\FinexerException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\Response;

class FinexerClient
{
    protected string $apiKey;
    protected string $apiUrl;
    protected int $timeout;
    protected bool $logRequests;
    protected bool $logResponses;

    public function __construct()
    {
        $this->apiKey = config('finexer.api_key');
        $this->apiUrl = config('finexer.api_url');
        $this->timeout = config('finexer.timeout', 30);
        $this->logRequests = config('finexer.log_requests', false);
        $this->logResponses = config('finexer.log_responses', false);

        if (empty($this->apiKey)) {
            throw new FinexerException('Finexer API key not configured');
        }
    }

    /**
     * Create a consent for bank connection
     *
     * @param string $customerId Finexer customer ID (e.g., 'cus_xxx')
     * @param array $scopes ['accounts', 'balance', 'transactions']
     * @param string $returnUrl Your callback URL
     * @return array Consent data with redirect URL
     */
    public function createConsent(string $customerId, array $scopes, string $returnUrl): array
    {
        $data = [
            'customer' => $customerId,
            'scopes' => $scopes,
            'return_url' => $returnUrl,
            'metadata[track_id]' => uniqid('consent_'),
        ];

        $response = $this->post('/consents', $data, true); // asForm = true

        return $response->json();
    }

    /**
     * Get consent details
     */
    public function getConsent(string $consentId): array
    {
        $response = $this->get("/consents/{$consentId}");
        return $response->json();
    }

    /**
     * Fetch bank accounts for a consent
     *
     * @param string $consentId
     * @param string $customerId
     * @return array List of bank accounts
     */
    public function getBankAccounts(string $consentId, string $customerId): array
    {
        $response = $this->get('/bank_accounts', [
            'consent' => $consentId,
            'customer' => $customerId,
        ]);

        return $response->json()['data'] ?? [];
    }

    /**
     * Trigger sync for a bank account
     * This fetches the latest transactions from the bank
     * 
     * @param string $accountId Finexer bank account ID (e.g., ba_xxx)
     * @return array Sync response
     */
    public function syncBankAccount(string $accountId): array
    {
        // $response = $this->post("/bank_accounts/{$accountId}/sync");
        $response = $this->post("/bank_accounts/{$accountId}/sync", [], true);
        return $response->json();
    }

    /**
     * Fetch transactions for a bank account
     * 
     * ✅ CORRECT ENDPOINT: /bank_accounts/{bank_account_id}/transactions
     * ⚠️ NO QUERY PARAMETERS - API doesn't accept any!
     * 
     * Usage:
     * 1. Call syncBankAccount() first to fetch latest from bank
     * 2. Call this to get all synced transactions
     * 3. Filter by date in your code if needed
     *
     * @param string $accountId Finexer bank account ID (e.g., ba_xxx)
     * @return array List of ALL transactions
     */
    public function getTransactions(string $accountId): array
    {
        // ✅ FIXED: NO parameters - API returns all transactions
        $response = $this->get("/bank_accounts/{$accountId}/transactions");

        return $response->json()['data'] ?? [];
    }

    /**
     * Get account balance
     */
    public function getBalance(string $consentId, string $accountId): array
    {
        $response = $this->get('/bank_accounts/balance', [
            'consent' => $consentId,
            'account' => $accountId,
        ]);

        return $response->json();
    }

    /**
     * Revoke a consent
     */
    public function revokeConsent(string $consentId): array
    {
        $response = $this->delete("/consents/{$consentId}");
        return $response->json();
    }

    /**
     * Generic GET request
     */
    protected function get(string $endpoint, array $params = []): Response
    {
        return $this->request('GET', $endpoint, $params);
    }

    /**
     * Generic POST request
     */
    protected function post(string $endpoint, array $data = [], bool $asForm = false): Response
    {
        return $this->request('POST', $endpoint, $data, $asForm);
    }

    /**
     * Generic DELETE request
     */
    protected function delete(string $endpoint): Response
    {
        return $this->request('DELETE', $endpoint);
    }

    /**
     * Generic HTTP request handler
     */
    protected function request(
        string $method,
        string $endpoint,
        array $data = [],
        bool $asForm = false
    ): Response {
        $url = $this->apiUrl . $endpoint;

        // Log request if enabled
        if ($this->logRequests) {
            Log::info('Finexer API Request', [
                'method' => $method,
                'url' => $url,
                'data' => $data,
            ]);
        }

        try {
            // Build HTTP client with Basic Auth
            $client = Http::withBasicAuth($this->apiKey, '')
                ->timeout($this->timeout)
                ->retry(
                    config('finexer.retry.times', 3),
                    config('finexer.retry.sleep', 1000)
                );

            // Make request
            if ($method === 'GET') {
                $response = $client->get($url, $data);
            } elseif ($method === 'POST') {
                if ($asForm) {
                    $response = $client->asForm()->post($url, $data);
                } else {
                    $response = $client->post($url, $data);
                }
            } elseif ($method === 'DELETE') {
                $response = $client->delete($url);
            } else {
                throw new FinexerException("Unsupported HTTP method: {$method}");
            }

            // Log response if enabled
            if ($this->logResponses) {
                Log::info('Finexer API Response', [
                    'method' => $method,
                    'url' => $url,
                    'status' => $response->status(),
                    'body' => $response->json(),
                ]);
            }

            // Check for errors
            if ($response->failed()) {
                throw FinexerException::fromResponse($response, $endpoint);
            }

            return $response;

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Finexer Connection Error', [
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
            ]);
            throw FinexerException::connectionTimeout();
        } catch (FinexerException $e) {
            // Re-throw our custom exceptions
            throw $e;
        } catch (\Exception $e) {
            Log::error('Finexer Unexpected Error', [
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new FinexerException(
                "Unexpected error: {$e->getMessage()}",
                500,
                'unexpected_error'
            );
        }
    }

    /**
     * Test API connection
     */
    public function testConnection(): bool
    {
        try {
            // Try to get consents - if API key is valid, this should work
            $response = $this->get('/consents', ['limit' => 1]);
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }
}