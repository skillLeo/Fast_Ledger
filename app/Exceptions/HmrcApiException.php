<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class HmrcApiException extends Exception
{
    protected int $statusCode;
    protected array $errors;
    protected ?array $response;

    public function __construct(
        string $message = 'HMRC API Error',
        int $statusCode = 500,
        array $errors = [],
        ?array $response = null,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
        $this->statusCode = $statusCode;
        $this->errors = $errors;
        $this->response = $response;
    }

    /**
     * Get HTTP status code
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Get HMRC error details
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Alias for getErrors() (for backward compatibility)
     */
    public function getHmrcErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get full HMRC response
     */
    public function getResponse(): ?array
    {
        return $this->response;
    }

    /**
     * Check if exception contains specific error code
     */
    public function hasErrorCode(string $code): bool
    {
        foreach ($this->errors as $error) {
            if (($error['code'] ?? '') === $code) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Get first error message
     */
    public function getFirstError(): ?string
    {
        if (empty($this->errors)) {
            return null;
        }

        $firstError = $this->errors[0];
        return $firstError['message'] ?? null;
    }

    /**
     * Get formatted error messages
     */
    public function getFormattedErrors(): string
    {
        if (empty($this->errors)) {
            return $this->getMessage();
        }

        $messages = [];
        foreach ($this->errors as $error) {
            $code = $error['code'] ?? 'UNKNOWN';
            $message = $error['message'] ?? 'Unknown error';
            $messages[] = "{$code}: {$message}";
        }

        return implode('; ', $messages);
    }

    /**
     * Render exception for HTTP response
     */
    public function render($request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'HMRC API Error',
                'message' => $this->getMessage(),
                'errors' => $this->errors,
                'status_code' => $this->statusCode,
            ], min($this->statusCode, 500));
        }

        return redirect()->back()->withErrors([
            'hmrc_error' => $this->getMessage(),
        ]);
    }

    /**
     * Report exception to logs
     */
    public function report()
    {
        Log::error('HMRC API Exception', [
            'message' => $this->getMessage(),
            'status_code' => $this->statusCode,
            'errors' => $this->errors,
            'response' => $this->response,
            'trace' => $this->getTraceAsString(),
        ]);
    }
}