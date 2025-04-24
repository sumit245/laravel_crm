<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next, $role)
    {
        logger('Required Role: ' . $role);
        logger('User Role: ' . Auth::user()->role);
        if (Auth::check() && Auth::user()->role == $role) {
            return $next($request); // Allow access if role matches
        }

        // Redirect or abort if user does not have the required role
        return redirect('/')->with('error', 'Unauthorized access');
    }
}
