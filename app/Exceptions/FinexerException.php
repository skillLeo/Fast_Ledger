<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Response;

class FinexerException extends Exception
{
    protected $statusCode;
    protected $errorCode;
    protected $errorDetails;

    public function __construct(
        string $message = "",
        int $statusCode = 500,
        string $errorCode = null,
        array $errorDetails = []
    ) {
        parent::__construct($message);
        $this->statusCode = $statusCode;
        $this->errorCode = $errorCode;
        $this->errorDetails = $errorDetails;
    }

    /**
     * Get the HTTP status code
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Get the error code
     */
    public function getErrorCode(): ?string
    {
        return $this->errorCode;
    }

    /**
     * Get error details
     */
    public function getErrorDetails(): array
    {
        return $this->errorDetails;
    }

    /**
     * Render the exception as an HTTP response
     */
    public function render()
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
            'error_code' => $this->errorCode,
            'details' => $this->errorDetails,
        ], $this->statusCode);
    }

    /**
     * Create exception from API response
     */
    public static function fromResponse($response, string $context = 'API Request'): self
    {
        $statusCode = $response->status();
        $body = $response->json();
        
        $message = $body['message'] ?? $body['error'] ?? "Finexer {$context} failed";
        $errorCode = $body['code'] ?? $body['error_code'] ?? null;
        $errorDetails = $body['details'] ?? $body;

        return new self($message, $statusCode, $errorCode, $errorDetails);
    }

    /**
     * Static helper methods for common exceptions
     */
    public static function invalidCredentials(): self
    {
        return new self('Invalid Finexer API credentials', 401, 'invalid_credentials');
    }

    public static function consentNotFound(string $consentId): self
    {
        return new self("Consent not found: {$consentId}", 404, 'consent_not_found');
    }

    public static function accountNotConnected(int $bankAccountId): self
    {
        return new self("Bank account {$bankAccountId} is not connected", 400, 'account_not_connected');
    }

    public static function syncFailed(string $reason): self
    {
        return new self("Transaction sync failed: {$reason}", 500, 'sync_failed');
    }

    public static function connectionTimeout(): self
    {
        return new self('Finexer API request timed out', 504, 'timeout');
    }
}