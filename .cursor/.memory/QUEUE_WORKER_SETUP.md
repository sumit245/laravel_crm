# Queue Worker Setup for Production

## Overview

This Laravel application uses queue jobs for asynchronous processing of bulk operations (e.g., bulk target deletion). Queue workers must run continuously in production to process queued jobs.

## Queue Configuration

The application is configured to use the `database` queue driver in production. Jobs are stored in the `jobs` table and processed by queue workers.

**Environment Variable:**
```env
QUEUE_CONNECTION=database
```

## Setup Instructions

### Option 1: Supervisor (Recommended for Linux)

Supervisor is a process control system that ensures the queue worker runs continuously and restarts automatically if it crashes.

#### 1. Install Supervisor

```bash
sudo apt-get update
sudo apt-get install supervisor
```

#### 2. Create Supervisor Configuration

Copy the provided Supervisor configuration file:

```bash
sudo cp deployment/queue-worker-supervisor.conf /etc/supervisor/conf.d/laravel-worker.conf
```

**Important:** Update the paths in the configuration file to match your server setup:
- Update `/var/www/vhosts/slldm.com/` to your actual application path
- Update `user=www-data` to match your web server user (usually `www-data` or `nginx`)

#### 3. Update Configuration

Edit `/etc/supervisor/conf.d/laravel-worker.conf`:

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/your/application/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/your/application/storage/logs/queue-worker.log
stopwaitsecs=3600
```

**Configuration Options:**
- `numprocs=2`: Number of worker processes (adjust based on server capacity)
- `--sleep=3`: Seconds to sleep when no jobs are available
- `--tries=3`: Number of times to retry failed jobs
- `--max-time=3600`: Maximum execution time per job (1 hour)

#### 4. Create Log Directory

```bash
sudo mkdir -p /path/to/your/application/storage/logs
sudo chown www-data:www-data /path/to/your/application/storage/logs
```

#### 5. Reread and Update Supervisor

```bash
sudo supervisorctl reread
sudo supervisorctl update
```

#### 6. Start Workers

```bash
sudo supervisorctl start laravel-worker:*
```

#### 7. Check Status

```bash
sudo supervisorctl status
```

You should see:
```
laravel-worker:laravel-worker_00   RUNNING   pid 12345, uptime 0:00:05
laravel-worker:laravel-worker_01   RUNNING   pid 12346, uptime 0:00:05
```

#### 8. Monitor Logs

```bash
tail -f /path/to/your/application/storage/logs/queue-worker.log
```

### Option 2: systemd (Alternative for Linux)

Create a systemd service file:

```bash
sudo nano /etc/systemd/system/laravel-worker.service
```

Add the following content:

```ini
[Unit]
Description=Laravel Queue Worker
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/path/to/your/application
ExecStart=/usr/bin/php artisan queue:work --sleep=3 --tries=3 --max-time=3600
Restart=always
RestartSec=3

[Install]
WantedBy=multi-user.target
```

Then:

```bash
sudo systemctl daemon-reload
sudo systemctl enable laravel-worker
sudo systemctl start laravel-worker
sudo systemctl status laravel-worker
```

### Option 3: Windows Service

For Windows servers, use NSSM (Non-Sucking Service Manager) or Task Scheduler to run the queue worker as a service.

#### Using NSSM:

1. Download NSSM from https://nssm.cc/download
2. Install the service:

```cmd
nssm install LaravelQueueWorker "C:\php\php.exe" "C:\path\to\your\application\artisan queue:work --sleep=3 --tries=3 --max-time=3600"
```

3. Configure service settings in NSSM GUI
4. Start the service:

```cmd
nssm start LaravelQueueWorker
```

## Verification

### Check Queue Worker Status

**Supervisor:**
```bash
sudo supervisorctl status laravel-worker:*
```

**systemd:**
```bash
sudo systemctl status laravel-worker
```

### Test Queue Processing

1. Set `QUEUE_CONNECTION=database` in your `.env` file
2. Ensure queue worker is running
3. Perform a bulk delete operation in the application
4. Check the queue logs to verify jobs are being processed

### View Queue Status

```bash
php artisan queue:listen
# Or
php artisan queue:work
```

## Troubleshooting

### Queue Worker Not Processing Jobs

1. **Check if worker is running:**
   ```bash
   sudo supervisorctl status
   # or
   sudo systemctl status laravel-worker
   ```

2. **Check queue connection:**
   ```bash
   php artisan config:show queue.default
   ```
   Should show `database` (not `sync`)

3. **Check jobs table:**
   ```bash
   php artisan tinker
   >>> DB::table('jobs')->count()
   ```
   If count > 0, jobs are queued but not being processed

4. **Check logs:**
   ```bash
   tail -f storage/logs/queue-worker.log
   tail -f storage/logs/laravel.log
   ```

5. **Restart worker:**
   ```bash
   sudo supervisorctl restart laravel-worker:*
   # or
   sudo systemctl restart laravel-worker
   ```

### Jobs Stuck in Queue

1. Check if worker process is running
2. Check for errors in logs
3. Verify database connection
4. Check if `jobs` table exists: `php artisan migrate`
5. Restart the queue worker

### Performance Optimization

- **Increase numprocs:** More worker processes = faster processing (be careful not to overload server)
- **Adjust --sleep:** Lower sleep time = faster processing but higher CPU usage
- **Use Redis queue:** For better performance, consider using Redis instead of database queue

## Production Checklist

- [ ] Set `QUEUE_CONNECTION=database` in `.env`
- [ ] Run `php artisan queue:table` and `php artisan migrate` to create jobs table
- [ ] Install and configure Supervisor/systemd/service
- [ ] Start queue worker service
- [ ] Verify worker is running and processing jobs
- [ ] Set up log rotation for queue-worker.log
- [ ] Monitor queue worker status regularly
- [ ] Set up alerts for worker failures (optional)

## Additional Notes

- Queue workers should run continuously in production
- Jobs will remain in the queue until a worker processes them
- Failed jobs are stored in the `failed_jobs` table
- Use `php artisan queue:failed` to view failed jobs
- Use `php artisan queue:retry all` to retry failed jobs
- For high-traffic applications, consider using Redis or RabbitMQ instead of database queue


