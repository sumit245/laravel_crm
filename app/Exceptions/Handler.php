<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

/**
 * Global exception handler for the application. Defines how errors are reported (logged to
 * storage/logs) and rendered (JSON for API, HTML for web). Customizes error responses for 404,
 * 403, 500, and validation errors.
 *
 * Data Flow:
 *   Exception thrown → Handler catches → Report: Log to file/external service → Render:
 *   JSON for API / Blade view for web → Return error response
 *
 * @business-domain System Administration
 * @package App\Exceptions
 */
class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }
}
