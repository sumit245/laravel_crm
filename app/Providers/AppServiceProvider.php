<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\State;
use Illuminate\Support\Facades\Cache;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // In testing environment, never hit the real database for global state lists.
        // This prevents accidental coupling of tests to production data.
        if (app()->environment('testing')) {
            View::share('states', collect());
            return;
        }

        $states = Cache::remember('states_list', 3600, function () {
            return State::all();
        });

        View::share('states', $states);
    }
}
