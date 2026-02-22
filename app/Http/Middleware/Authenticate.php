<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

/**
 * Authentication guard middleware. Checks if the current request has a valid authenticated
 * session. Unauthenticated users are redirected to the login page. Applied to all routes
 * requiring login.
 *
 * Data Flow:
 *   HTTP Request → Check session/token → Authenticated: proceed → Unauthenticated:
 *   redirect to /login
 *
 * @depends-on User
 * @business-domain Security
 * @package App\Http\Middleware
 */
class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? null : route('login');
    }
}
