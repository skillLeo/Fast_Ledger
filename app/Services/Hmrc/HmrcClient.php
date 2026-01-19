<?php

namespace App\Services\Hmrc;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;
use App\Exceptions\HmrcApiException;
use App\Exceptions\HmrcAuthenticationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;

class HmrcClient
{
    protected string $baseUrl;
    protected int $timeout;
    protected int $retryTimes;
    protected int $retryDelayMs;

    public function __construct()
    {
        $environment = config('hmrc.environment');
        $this->baseUrl = config("hmrc.base_urls.{$environment}");
        $this->timeout = config('hmrc.timeout', 30);
        $this->retryTimes = config('hmrc.retry_times', 3);
        $this->retryDelayMs = config('hmrc.retry_delay_ms', 200);
    }

    /**
     * Get base URL for current environment
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * Make authenticated GET request to HMRC API
     */
    public function get(string $endpoint, string $accessToken, array $headers = [], ?string $testScenario = null): array
    {
        return $this->send('GET', $endpoint, $accessToken, [], $headers, $testScenario);
    }

    /**
     * Make authenticated POST request to HMRC API
     */
    public function post(string $endpoint, string $accessToken, array $data = [], array $headers = [], ?string $testScenario = null): array
    {
        return $this->send('POST', $endpoint, $accessToken, $data, $headers, $testScenario);
    }

    /**
     * Make authenticated PUT request to HMRC API
     */
    public function put(string $endpoint, string $accessToken, array $data = [], array $headers = [], ?string $testScenario = null): array
    {
        return $this->send('PUT', $endpoint, $accessToken, $data, $headers, $testScenario);
    }

    /**
     * Make authenticated DELETE request to HMRC API
     */
    public function delete(string $endpoint, string $accessToken, array $headers = [], ?string $testScenario = null): array
    {
        return $this->send('DELETE', $endpoint, $accessToken, [], $headers, $testScenario);
    }

    /**
     * Make unauthenticated POST request (for OAuth token exchange)
     */
    public function postUnauthenticated(string $endpoint, array $data = []): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->retry($this->retryTimes, 100)
                ->asForm()
                ->post("{$this->baseUrl}{$endpoint}", $data);

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('HMRC Unauthenticated Request Failed', [
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
            ]);

            throw new HmrcApiException(
                'Failed to communicate with HMRC: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Make authenticated HTTP request with retry and rate limiting support
     */
    protected function send(
        string $method,
        string $endpoint,
        string $accessToken,
        array $data = [],
        array $additionalHeaders = [],
        ?string $testScenario = null
    ): array {
        $url = rtrim($this->baseUrl, '/') . '/' . ltrim($endpoint, '/');

        $headers = array_merge([
            'Authorization' => "Bearer {$accessToken}",
            'Accept' => 'application/vnd.hmrc.2.0+json',
            'Content-Type' => 'application/json',
        ], $this->getFraudPreventionHeaders(), $additionalHeaders);

        // Add Gov-Test-Scenario header if provided and in sandbox mode
        if ($testScenario && config('hmrc.environment') === 'sandbox') {
            $headers['Gov-Test-Scenario'] = $testScenario;
        }

        $attempt = 0;
        $maxAttempts = 5; // exponential backoff attempts for 429

        while (true) {
            $attempt++;
            try {
                $pending = Http::timeout($this->timeout)->withHeaders($headers);

                Log::info('HMRC API Request', [
                    'method' => $method,
                    'url' => $url,
                    'attempt' => $attempt,
                ]);

                /** @var Response $response */
                $response = match ($method) {
                    'GET' => $pending->get($url),
                    'POST' => $pending->post($url, $data),
                    'PUT' => $pending->put($url, $data),
                    'DELETE' => $pending->delete($url),
                    default => throw new \InvalidArgumentException("Unsupported HTTP method: {$method}"),
                };

                // Handle rate limiting with exponential backoff
                if ($response->status() === 429 && $attempt < $maxAttempts) {
                    $retryAfter = (int) $response->header('Retry-After', 0);
                    $delayMs = $retryAfter > 0 ? $retryAfter * 1000 : $this->exponentialBackoffMs($attempt);

                    Log::warning('HMRC rate limited, retrying with backoff', [
                        'attempt' => $attempt,
                        'delay_ms' => $delayMs,
                    ]);

                    $this->sleepMilliseconds($delayMs);
                    continue;
                }

                return $this->handleResponse($response);

            } catch (HmrcApiException | HmrcAuthenticationException $e) {
                // Don't retry HMRC-specific errors
                throw $e;
            } catch (\Throwable $e) {
                // Retry transient network errors up to 3 times
                if ($attempt < 3) {
                    $delayMs = $this->exponentialBackoffMs($attempt);
                    Log::warning('HMRC API transient error, retrying', [
                        'attempt' => $attempt,
                        'delay_ms' => $delayMs,
                        'error' => $e->getMessage(),
                    ]);
                    $this->sleepMilliseconds($delayMs);
                    continue;
                }

                Log::error('HMRC API request failed', [
                    'method' => $method,
                    'url' => $url,
                    'error' => $e->getMessage(),
                ]);
                throw $e;
            }
        }
    }

    /**
     * Handle HMRC API response
     */
    protected function handleResponse(Response $response): array
    {
        $statusCode = $response->status();

        // Success responses (2xx)
        if ($response->successful()) {
            return (array) $response->json();
        }

        // Parse error response
        $payload = [];
        try {
            $payload = (array) $response->json();
        } catch (\Throwable $e) {
            // keep payload empty
        }

        $code = (string) Arr::get($payload, 'code', 'UNKNOWN_ERROR');
        $message = (string) Arr::get($payload, 'message', 'An error occurred while calling HMRC API.');
        $errors = (array) Arr::get($payload, 'errors', []);

        Log::error('HMRC API Error', [
            'status' => $statusCode,
            'code' => $code,
            'message' => $message,
            'errors' => $errors,
            'body' => $response->body(),
        ]);

        // Handle specific error codes
        $this->handleErrorResponse($statusCode, $message, $code, $errors);

        // Default error handling
        throw new HmrcApiException($message, $statusCode, $errors);
    }

    /**
     * Handle specific HMRC error responses
     */
    protected function handleErrorResponse(int $statusCode, string $message, string $code, array $errors): void
    {
        switch ($statusCode) {
            case 400:
                throw new HmrcApiException("Bad Request: {$message}", 400, $errors);

            case 401:
                throw new HmrcAuthenticationException("Unauthorized: {$message}");

            case 403:
                throw new HmrcApiException("Forbidden: {$message}", 403, $errors);

            case 404:
                throw new HmrcApiException("Resource not found: {$message}", 404, $errors);

            case 409:
                throw new HmrcApiException("Conflict: {$message}", 409, $errors);

            case 422:
                throw new HmrcApiException("Validation failed: {$message}", 422, $errors);

            case 429:
                throw new HmrcApiException("Too many requests. Please try again later.", 429, $errors);

            case 500:
            case 502:
            case 503:
                throw new HmrcApiException("HMRC server error: {$message}", $statusCode, $errors);

            default:
                throw new HmrcApiException($message, $statusCode, $errors);
        }
    }

    /**
     * Calculate exponential backoff delay in milliseconds
     */
    protected function exponentialBackoffMs(int $attempt): int
    {
        $maxMs = 8000;
        $ms = $this->retryDelayMs * (2 ** max(0, $attempt - 1));
        return min($ms, $maxMs);
    }

    /**
     * Sleep for specified milliseconds
     */
    protected function sleepMilliseconds(int $ms): void
    {
        if (app()->environment('testing')) {
            return; // don't actually sleep in tests
        }
        usleep($ms * 1000);
    }

    /**
     * Get fraud prevention headers for HMRC API
     */
    protected function getFraudPreventionHeaders(): array
    {
        $request = request();

        $deviceId = Session::get('hmrc_device_id');
        if (!$deviceId) {
            $deviceId = (string) Str::uuid();
            Session::put('hmrc_device_id', $deviceId);
        }

        $user = auth()->user();
        $userId = $user?->User_ID ?? null;
        $userName = $user?->User_Name ?? '';

        $tz = new \DateTimeZone(date_default_timezone_get());
        $now = new \DateTime('now', $tz);
        $offsetSeconds = $tz->getOffset($now);
        $sign = $offsetSeconds >= 0 ? '+' : '-';
        $hours = str_pad((string) floor(abs($offsetSeconds) / 3600), 2, '0', STR_PAD_LEFT);
        $minutes = str_pad((string) floor((abs($offsetSeconds) % 3600) / 60), 2, '0', STR_PAD_LEFT);
        $tzHeader = 'UTC' . $sign . $hours . ':' . $minutes;

        $ips = method_exists($request, 'ips') ? $request->ips() : [$request->ip()];
        $localIps = implode(',', array_filter(array_map('strval', $ips)));

        $dnt = $request->header('DNT');
        $dntHeader = $dnt === null ? 'not-collected' : (string) $dnt;

        $appName = (string) config('app.name', 'FastLedgerV2');
        $appVersion = (string) (config('app.version') ?? '1.0.0');

        // Fraud prevention headers (currently commented out - enable when ready for production)
        return array_filter([
            // 'Gov-Client-Connection-Method' => 'WEB_APP_VIA_SERVER',
            // 'Gov-Client-Device-ID' => $deviceId,
            // 'Gov-Client-User-IDs' => $userId ? 'internal:' . $userId . ($userName ? ';username:' . $userName : '') : 'not-collected',
            // 'Gov-Client-Timezone' => $tzHeader,
            // 'Gov-Client-Local-IPs' => $localIps ?: 'not-collected',
            // 'Gov-Client-Screens' => 'not-collected',
            // 'Gov-Client-Window-Size' => 'not-collected',
            // 'Gov-Client-Browser-Plugins' => 'not-collected',
            // 'Gov-Client-Browser-JS-User-Agent' => (string) ($request->userAgent() ?: 'not-collected'),
            // 'Gov-Client-Browser-Do-Not-Track' => $dntHeader,
            // 'Gov-Client-Multi-Factor' => 'not-collected',
            // 'Gov-Vendor-Version' => $appName . '=' . $appVersion,
        ], static fn ($v) => $v !== null && $v !== '');
    }

    /**
     * Check if response indicates token is expired
     */
    public function isTokenExpiredError(HmrcApiException $exception): bool
    {
        return $exception->getStatusCode() === 401;
    }
}