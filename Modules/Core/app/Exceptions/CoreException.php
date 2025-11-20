<?php

namespace Modules\Core\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class CoreException extends Exception
{
    protected $statusCode = 500;
    protected $errors = [];

    /**
     * Create a new exception instance.
     */
    public function __construct(string $message = '', int $statusCode = 500, array $errors = [])
    {
        parent::__construct($message);
        $this->statusCode = $statusCode;
        $this->errors = $errors;
    }

    /**
     * Get the status code.
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Get the errors.
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Render the exception as an HTTP response.
     */
    public function render($request): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $this->getMessage(),
                'errors' => $this->errors,
            ], $this->statusCode);
        }

        return redirect()
            ->back()
            ->withErrors(['error' => $this->getMessage()])
            ->withInput();
    }

    /**
     * Report the exception.
     */
    public function report(): void
    {
        \Illuminate\Support\Facades\Log::error($this->getMessage(), [
            'exception' => static::class,
            'code' => $this->getCode(),
            'file' => $this->getFile(),
            'line' => $this->getLine(),
            'errors' => $this->errors,
        ]);
    }
}
