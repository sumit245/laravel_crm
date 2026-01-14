<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected $commands = [
        \App\Console\Commands\BackupDatabase::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        $schedule->command('backup:database')->dailyAt('00:00');
        
        // Process queue jobs every minute (if cron is available)
        // This works even on shared hosting if cron is available
        $schedule->command('queue:work --stop-when-empty --tries=3 --max-time=300')
            ->everyMinute()
            ->withoutOverlapping()
            ->runInBackground();
    }

    // protected function schedule(Schedule $schedule): void
    // {
    //     // $schedule->command('inspire')->hourly();
    // }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
