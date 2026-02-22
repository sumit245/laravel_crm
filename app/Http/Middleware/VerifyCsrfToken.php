<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

/**
 * CSRF protection middleware. Validates that POST/PUT/DELETE requests include a valid CSRF token,
 * preventing cross-site request forgery attacks. API routes are typically excluded.
 *
 * Data Flow:
 *   Form submission → Check _token field or X-CSRF-TOKEN header → Match session token →
 *   Valid: proceed → Invalid: abort 419
 *
 * @business-domain Security
 * @package App\Http\Middleware
 */
class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        //
        'api/*',
    ];
}
