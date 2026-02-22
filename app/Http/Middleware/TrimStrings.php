<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\TrimStrings as Middleware;

/**
 * Input sanitization middleware. Automatically trims leading and trailing whitespace from all
 * incoming request string values. Prevents data quality issues from accidental spaces in form
 * inputs.
 *
 * Data Flow:
 *   HTTP Request → Iterate all string inputs → Trim whitespace → Pass cleaned data to
 *   controller
 *
 * @business-domain Data Validation
 * @package App\Http\Middleware
 */
class TrimStrings extends Middleware
{
    /**
     * The names of the attributes that should not be trimmed.
     *
     * @var array<int, string>
     */
    protected $except = [
        'current_password',
        'password',
        'password_confirmation',
        'image',
    ];
}
