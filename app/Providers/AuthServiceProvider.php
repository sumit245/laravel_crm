<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;
use App\Models\Project;
use App\Models\Stores;
use App\Models\User;
use App\Policies\ProjectPolicy;
use App\Policies\StorePolicy;
use App\Policies\UserPolicy;
use App\Models\ActivityLog;
use App\Policies\ActivityLogPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Project::class => ProjectPolicy::class,
        User::class => UserPolicy::class,
        Stores::class => StorePolicy::class,
        ActivityLog::class => ActivityLogPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
        $this->registerPolicies();

        // Define the location of Passport keys (optional but recommended)
        Passport::loadKeysFrom(storage_path('oauth'));

        // Configure tokens expiry (optional)
        Passport::tokensExpireIn(now()->addDays(15));
        Passport::refreshTokensExpireIn(now()->addDays(30));
    }
}
