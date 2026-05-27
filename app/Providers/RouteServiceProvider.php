<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

/**
 * Route configuration provider. Loads route files (web.php, api.php), sets API rate limiting,
 * configures route model binding, and defines the application's URL namespace.
 *
 * Data Flow:
 *   Application boots → Load web routes + API routes → Apply middleware groups → Rate
 *   limiting configured → Routes ready to serve
 *
 * @business-domain Architecture
 * @package App\Providers
 */
class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/dashboard';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            if ($request->is('api/streetlight/tasks/update')) {
                return Limit::perMinute(300)->by($request->user()?->id ?: $request->ip());
            }

            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Rate limit for login attempts (web + API)
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->input('email', '') . '|' . $request->ip());
        });

        // Rate limit for OTP sending
        RateLimiter::for('otp', function (Request $request) {
            return Limit::perMinutes(5, 3)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}
