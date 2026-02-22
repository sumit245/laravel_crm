<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance as Middleware;

/**
 * Maintenance mode gate. When the application is put into maintenance mode (php artisan down),
 * this middleware returns a 503 Service Unavailable response to all incoming requests, except
 * whitelisted IPs or paths.
 *
 * Data Flow:
 *   HTTP Request → Check maintenance mode flag → Active: return 503 page → Inactive:
 *   proceed normally
 *
 * @business-domain System Administration
 * @package App\Http\Middleware
 */
class PreventRequestsDuringMaintenance extends Middleware
{
    /**
     * The URIs that should be reachable while maintenance mode is enabled.
     *
     * @var array<int, string>
     */
    protected $except = [
        //
    ];
}
