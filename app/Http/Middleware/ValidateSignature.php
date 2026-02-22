<?php

namespace App\Http\Middleware;

use Illuminate\Routing\Middleware\ValidateSignature as Middleware;

/**
 * URL signature validation middleware. Validates that signed URLs (used for email verification,
 * temporary download links) have not been tampered with and have not expired.
 *
 * Data Flow:
 *   Signed URL request → Validate HMAC signature → Check expiration → Valid: proceed →
 *   Invalid: abort 403
 *
 * @business-domain Security
 * @package App\Http\Middleware
 */
class ValidateSignature extends Middleware
{
    /**
     * The names of the query string parameters that should be ignored.
     *
     * @var array<int, string>
     */
    protected $except = [
        // 'fbclid',
        // 'utm_campaign',
        // 'utm_content',
        // 'utm_medium',
        // 'utm_source',
        // 'utm_term',
    ];
}
