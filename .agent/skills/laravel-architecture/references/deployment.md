# Deployment & Operations — Full Reference

## Table of Contents
1. [Environment Overview](#1-environment-overview)
2. [Local Development](#2-local-development)
3. [Production Deployment](#3-production-deployment)
4. [Queue Worker Setup](#4-queue-worker-setup)
5. [Troubleshooting](#5-troubleshooting)
6. [Production Checklist](#6-production-checklist)

---

## 1. Environment Overview

| Environment | Stack | Database | Queue | Purpose |
|-------------|-------|----------|-------|---------|
| Local (dev) | XAMPP (Apache, PHP 8.1, MySQL) | `sugs` via MySQL | `sync` | Development |
| Testing | PHPUnit / `php artisan test` | SQLite `:memory:` | `sync` | Automated tests |
| Production | EC2 (Nginx, PHP 8.1, MySQL) | MySQL (remote) | `database` | Live |

### Environment Files
- `.env` — Local development (MySQL, `DB_DATABASE=sugs`)
- `.env.testing` — Test runs (SQLite in-memory, `APP_ENV=testing`)
- Production `.env` — Managed on EC2 directly

---

## 2. Local Development

### Starting the Dev Server
```bash
# From project root
php artisan serve
# App accessible at https://localhost:8000
```

### Login Credentials
```
Email: admin@sugslloyd.com
Password: password123
```

### Key Artisan Commands
```bash
php artisan migrate              # Run pending migrations
php artisan migrate:status       # Check migration status
php artisan tinker               # Interactive REPL
php artisan queue:work           # Process queued jobs locally
php artisan config:clear         # Clear config cache
php artisan route:list           # List all routes
php artisan test                 # Run full test suite
```

---

## 3. Production Deployment

### Standard Deployment Steps
```bash
# SSH into EC2
ssh -i sugs_lloyds.pem ec2-user@<server-ip>

# Navigate to application directory
cd /var/www/vhosts/slldm.com/

# Pull latest code
git pull origin main

# Install dependencies (no dev packages)
composer install --no-dev --optimize-autoloader

# Clear and rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
php artisan migrate --force

# Restart queue workers
sudo supervisorctl restart laravel-worker:*
```

### File Permissions
```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### Nginx Fix Script
The project includes `deployment/ec2-nginx-fix.sh` for resolving common Nginx configuration issues on EC2.

---

## 4. Queue Worker Setup

The application uses the `database` queue driver in production for asynchronous processing of bulk operations (pole imports, target deletions).

### Queue Jobs
| Job | Purpose |
|-----|---------|
| `ProcessPoleImportChunk` | Chunked processing of bulk pole imports from Excel |
| `ProcessTargetDeletionChunk` | Chunked deletion of streetlight targets |
| `SyncPolesToRmsJob` | Push pole data to Remote Monitoring System |

### Option 1: Supervisor (Recommended)

```bash
# Install Supervisor
sudo apt-get update && sudo apt-get install supervisor

# Copy config
sudo cp deployment/queue-worker-supervisor.conf /etc/supervisor/conf.d/laravel-worker.conf
```

Supervisor configuration (`queue-worker-supervisor.conf`):
```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/storage/logs/queue-worker.log
stopwaitsecs=3600
```

```bash
# Apply and start
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*

# Check status
sudo supervisorctl status
```

### Option 2: systemd

```ini
[Unit]
Description=Laravel Queue Worker
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/path/to/application
ExecStart=/usr/bin/php artisan queue:work --sleep=3 --tries=3 --max-time=3600
Restart=always
RestartSec=3

[Install]
WantedBy=multi-user.target
```

```bash
sudo systemctl daemon-reload
sudo systemctl enable laravel-worker
sudo systemctl start laravel-worker
```

### Shared Hosting Queue Processing
For hosts without Supervisor/systemd, a cron-based queue processor is available at `/queue/process` (`QueueProcessorController`).

---

## 5. Troubleshooting

### Queue Worker Not Processing Jobs
1. Check if worker is running: `sudo supervisorctl status`
2. Check queue connection: `php artisan config:show queue.default` (should be `database`)
3. Check jobs table: `php artisan tinker` → `DB::table('jobs')->count()`
4. Check logs: `tail -f storage/logs/queue-worker.log` and `tail -f storage/logs/laravel.log`
5. Restart worker: `sudo supervisorctl restart laravel-worker:*`

### Storage Permission Errors
```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### Gateway Timeout on Large Imports
- Increase PHP limits: `upload_max_filesize`, `post_max_size`, `max_execution_time`
- Force imports through queue jobs for files with >10K rows
- Use chunked reading in Maatwebsite/Excel imports

### Failed Jobs
```bash
php artisan queue:failed         # List failed jobs
php artisan queue:retry all      # Retry all failed jobs
php artisan queue:flush          # Delete all failed jobs
```

---

## 6. Production Checklist

- [ ] Set `APP_ENV=production` and `APP_DEBUG=false`
- [ ] Set `QUEUE_CONNECTION=database`
- [ ] Ensure `jobs` and `failed_jobs` tables exist (`php artisan queue:table && php artisan migrate`)
- [ ] Install and configure Supervisor/systemd
- [ ] Start and verify queue workers are running
- [ ] Set up log rotation for `queue-worker.log`
- [ ] Verify file permissions on `storage/` and `bootstrap/cache/`
- [ ] Ensure S3 credentials are configured for file uploads
- [ ] Run `composer install --no-dev --optimize-autoloader`
- [ ] Run `php artisan config:cache && route:cache && view:cache`
- [ ] Verify Nginx config points to `public/` directory
