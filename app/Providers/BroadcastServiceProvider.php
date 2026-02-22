<?php

namespace App\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

/**
 * Real-time broadcasting configuration provider. Sets up channel authentication for WebSocket
 * broadcasts (if used). Registers broadcast channel authorization callbacks.
 *
 * Data Flow:
 *   Application boots → Register broadcast channels → Authenticate channel access →
 *   Enable real-time notifications
 *
 * @business-domain Architecture
 * @package App\Providers
 */
class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Broadcast::routes();

        require base_path('routes/channels.php');
    }
}
