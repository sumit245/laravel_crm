<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// Repository Interfaces
use App\Contracts\ProjectRepositoryInterface;
use App\Contracts\UserRepositoryInterface;
use App\Contracts\TaskRepositoryInterface;
use App\Contracts\TaskStateMachineInterface;
use App\Contracts\MeetingRepositoryInterface;
use App\Contracts\SiteRepositoryInterface;

// Repository Implementations
use App\Repositories\Project\ProjectRepository;
use App\Repositories\User\UserRepository;
use App\Repositories\Task\TaskRepository;
use App\Repositories\Meeting\MeetingRepository;
use App\Repositories\Site\SiteRepository;

// Service Interfaces
use App\Contracts\ProjectServiceInterface;
use App\Contracts\UserServiceInterface;
use App\Contracts\TaskServiceInterface;
use App\Contracts\DashboardServiceInterface;
use App\Contracts\AnalyticsServiceInterface;
use App\Contracts\MeetingServiceInterface;
use App\Contracts\SiteServiceInterface;
use App\Contracts\PerformanceServiceInterface;

// Service Implementations
use App\Services\Project\ProjectService;
use App\Services\User\UserService;
use App\Services\Task\TaskManagementService;
use App\Services\Task\TaskStateMachine;
use App\Services\Dashboard\DashboardService;
use App\Services\Dashboard\AnalyticsService;
use App\Services\Meeting\MeetingManagementService;
use App\Services\Site\SiteManagementService;
use App\Services\Performance\PerformanceService;

// Models
use App\Models\Project;
use App\Models\User;
use App\Models\Task;
use App\Models\Meet;
use App\Models\Site;

/**
 * Repository Service Provider
 * 
 * Binds repository and service interfaces to their implementations
 */
class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services
     */
    public function register(): void
    {
        // Bind Repository Interfaces to Implementations
        $this->app->bind(ProjectRepositoryInterface::class, function ($app) {
            return new ProjectRepository(new Project());
        });

        $this->app->bind(UserRepositoryInterface::class, function ($app) {
            return new UserRepository(new User());
        });

        // Bind Service Interfaces to Implementations
        $this->app->bind(ProjectServiceInterface::class, function ($app) {
            return new ProjectService(
                $app->make(ProjectRepositoryInterface::class)
            );
        });

        $this->app->bind(UserServiceInterface::class, function ($app) {
            return new UserService(
                $app->make(UserRepositoryInterface::class)
            );
        });

        // Bind Task Repository Interface to Implementation
        $this->app->bind(TaskRepositoryInterface::class, function ($app) {
            return new TaskRepository(new Task());
        });

        // Bind Task State Machine Interface to Implementation
        $this->app->bind(TaskStateMachineInterface::class, function ($app) {
            return new TaskStateMachine();
        });

        // Bind Task Service Interface to Implementation
        $this->app->bind(TaskServiceInterface::class, function ($app) {
            return new TaskManagementService(
                $app->make(TaskRepositoryInterface::class),
                $app->make(TaskStateMachineInterface::class)
            );
        });

        // Bind Dashboard Service Interface to Implementation
        $this->app->bind(DashboardServiceInterface::class, function ($app) {
            return new DashboardService(
                $app->make(AnalyticsServiceInterface::class)
            );
        });

        // Bind Analytics Service Interface to Implementation
        $this->app->bind(AnalyticsServiceInterface::class, function ($app) {
            return new AnalyticsService();
        });

        // Bind Meeting Repository and Service
        $this->app->bind(MeetingRepositoryInterface::class, function ($app) {
            return new MeetingRepository(new Meet());
        });
        
        $this->app->bind(MeetingServiceInterface::class, function ($app) {
            return new MeetingManagementService(
                $app->make(MeetingRepositoryInterface::class)
            );
        });

        // Bind Site Repository and Service
        $this->app->bind(SiteRepositoryInterface::class, function ($app) {
            return new SiteRepository(new Site());
        });
        
        $this->app->bind(SiteServiceInterface::class, function ($app) {
            return new SiteManagementService(
                $app->make(SiteRepositoryInterface::class)
            );
        });

        // Bind Inventory Service
        $this->app->bind(
            \App\Contracts\Services\Inventory\InventoryServiceInterface::class,
            \App\Services\Inventory\InventoryService::class
        );

        // Bind Performance Service
        $this->app->bind(PerformanceServiceInterface::class, function ($app) {
            return new PerformanceService();
        });
    }

    /**
     * Bootstrap services
     */
    public function boot(): void
    {
        //
    }
}
