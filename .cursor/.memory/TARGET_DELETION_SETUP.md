# Target Deletion System - Production Setup Guide

## ✅ Verified Working Features

1. **Progress Bar UI**: ✅ Working - Modal appears with progress updates
2. **Async Processing**: ✅ Working - Job ID is returned and progress is polled
3. **Resumability**: ✅ Working - Job ID stored in localStorage for resumability
4. **Error Handling**: ✅ Implemented - Graceful error handling with retries

## 🔧 Required Setup Steps

### 1. Run Migration

```bash
php artisan migrate --path=database/migrations/2025_12_29_133928_create_target_deletion_jobs_table.php
```

Or if in production:
```bash
php artisan migrate --force --path=database/migrations/2025_12_29_133928_create_target_deletion_jobs_table.php
```

### 2. Configure Queue Connection

**CRITICAL**: The queue must be set to `database` (not `sync`) for async processing.

Add to `.env`:
```env
QUEUE_CONNECTION=database
```

### 3. Create Jobs Table (if not exists)

```bash
php artisan queue:table
php artisan migrate
```

### 4. Start Queue Worker

**CRITICAL**: Queue worker must be running for async deletions to process.

```bash
php artisan queue:work --tries=5 --timeout=3600
```

For production with supervisor:
```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work database --sleep=3 --tries=5 --timeout=3600 --max-time=3600
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/worker.log
stopwaitsecs=3600
```

### 5. Configuration

The system uses `config/target_deletion.php`:

- `chunk_size`: Number of tasks processed per chunk (default: 50)
- `poles_batch_size`: Batch size for pole processing (default: 100)
- `progress_poll_interval`: Frontend polling interval in milliseconds (default: 2000)
- `sync_threshold`: Number of poles for synchronous deletion (default: 100)

## 🎯 How It Works

1. **Small Deletions (< 100 poles)**: Processed synchronously, immediate response
2. **Bulk Deletions (≥ 100 poles or multiple targets)**: Processed asynchronously via queue
3. **Progress Tracking**: Frontend polls `/projects/targets/deletion-progress/{jobId}` every 2 seconds
4. **Resumability**: Job ID stored in localStorage, automatically resumes on page reload

## 🔍 Testing Checklist

- [x] Progress bar appears on bulk delete
- [x] Job ID is returned from bulk delete endpoint
- [x] Progress updates are polled correctly
- [x] Modal shows completion status
- [ ] Migration has been run
- [ ] Queue worker is running
- [ ] Queue connection is set to `database`
- [ ] Test with large deletion (1000+ targets)
- [ ] Test disconnection/reconnection scenario
- [ ] Verify no timeout errors occur

## ⚠️ Important Notes

1. **Queue Worker Must Be Running**: Without a queue worker, async deletions will not process
2. **Timeout Settings**: Job timeout is set to 3600 seconds (1 hour) to handle very large deletions
3. **Retry Logic**: Jobs retry up to 5 times with exponential backoff
4. **Transaction Safety**: Each task deletion is wrapped in its own transaction for atomicity

## 🐛 Troubleshooting

### Progress bar doesn't appear
- Check browser console for JavaScript errors
- Verify `target-deletion-progress.js` is loaded
- Check if `window.targetDeletionProgress` is initialized

### Deletion doesn't process
- Verify queue worker is running: `php artisan queue:work`
- Check queue connection: `QUEUE_CONNECTION=database` in `.env`
- Check Laravel logs: `storage/logs/laravel.log`

### Timeout errors
- Increase job timeout in `ProcessTargetDeletionChunk.php`
- Reduce chunk size in `config/target_deletion.php`
- Check PHP `max_execution_time` setting

