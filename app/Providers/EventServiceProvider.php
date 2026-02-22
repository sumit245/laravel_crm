<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

/**
 * Event-listener mapping provider. Registers all event-to-listener mappings, enabling the
 * event-driven architecture. Maps events like InventoryDispatched to listeners like
 * LogInventoryHistory.
 *
 * Data Flow:
 *   Application boots → Register event → listener mappings → Events dispatched →
 *   Matching listeners execute
 *
 * @business-domain Architecture
 * @package App\Providers
 */
class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
