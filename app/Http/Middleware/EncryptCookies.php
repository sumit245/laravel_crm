<?php

namespace App\Http\Middleware;

use Illuminate\Cookie\Middleware\EncryptCookies as Middleware;

/**
 * Cookie encryption middleware. Automatically encrypts all outgoing cookies and decrypts incoming
 * cookies to prevent tampering and ensure data privacy. Part of Laravel's default security stack.
 *
 * Data Flow:
 *   Response cookies → Encrypt with APP_KEY → Send to browser → Next request: Decrypt
 *   incoming cookies → Pass to application
 *
 * @business-domain Security
 * @package App\Http\Middleware
 */
class EncryptCookies extends Middleware
{
    /**
     * The names of the cookies that should not be encrypted.
     *
     * @var array<int, string>
     */
    protected $except = [
        //
    ];
}
