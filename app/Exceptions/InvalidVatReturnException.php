<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Support\Facades\Log;

class InvalidVatReturnException extends Exception
{
    protected $validationErrors;

    public function __construct(
        string $message = 'Invalid VAT Return Data',
        array $validationErrors = [],
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
        $this->validationErrors = $validationErrors;
    }

    public function getValidationErrors(): array
    {
        return $this->validationErrors;
    }

    public function render($request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Validation Error',
                'message' => $this->getMessage(),
                'validation_errors' => $this->validationErrors,
            ], 422);
        }

        return redirect()->back()
            ->withErrors($this->validationErrors)
            ->withInput();
    }

    public function report()
    {
        Log::info('Invalid VAT Return', [
            'message' => $this->getMessage(),
            'validation_errors' => $this->validationErrors,
        ]);
    }
}
