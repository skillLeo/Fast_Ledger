<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Support\Facades\Log;

class HmrcAuthenticationException extends Exception
{
    protected $statusCode = 401;

    public function __construct(
        string $message = 'HMRC Authentication Failed',
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
    }

    public function render($request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Authentication Error',
                'message' => $this->getMessage(),
            ], 401);
        }

        return redirect()->route('hmrc.connect')->withErrors([
            'auth_error' => $this->getMessage(),
        ]);
    }

    public function report()
    {
        Log::warning('HMRC Authentication Exception', [
            'message' => $this->getMessage(),
        ]);
    }
}
