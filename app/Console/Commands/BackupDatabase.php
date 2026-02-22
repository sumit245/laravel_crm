<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;

/**
 * Artisan console command for automated database backup. Creates a compressed SQL dump of the
 * entire database and stores it in the configured backup location. Can be scheduled via Laravel's
 * task scheduler for daily/weekly backups.
 *
 * Data Flow:
 *   php artisan db:backup → Connect to MySQL → mysqldump → Compress → Store in backup
 *   directory → Log success/failure
 *
 * @business-domain System Administration
 * @package App\Console\Commands
 */
class BackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:backup-database';
    protected $description = 'Backup the database and save it with date and time';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filename = 'backup_' . Carbon::now()->format('Y_m_d_His') . '.sql';
        $path = storage_path('app/backups');

        if (!File::exists($path)) {
            File::makeDirectory($path, 0755, true);
        }

        $db = config('database.connections.mysql');
        $command = sprintf(
            'mysqldump -u%s -p%s -h%s %s > %s/%s',
            escapeshellarg($db['username']),
            escapeshellarg($db['password']),
            escapeshellarg($db['host']),
            escapeshellarg($db['database']),
            escapeshellarg($path),
            escapeshellarg($filename)
        );

        $result = null;
        $output = null;
        exec($command, $output, $result);

        if ($result === 0) {
            $this->info("Database backup was successful: $filename");
        } else {
            $this->error("Database backup failed.");
        }
    }
}
