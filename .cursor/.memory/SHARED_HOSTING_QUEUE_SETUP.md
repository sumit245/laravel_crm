# Queue Worker Setup for Shared Hosting (Hostinger)

## Overview

On shared hosting (like Hostinger), you cannot run continuous background processes like Supervisor. This guide provides solutions to process queue jobs on shared hosting.

## Solution Options

### Option 1: Web Endpoint + Cron Job (Recommended)

Create a web-accessible endpoint that processes queue jobs, then call it via cron job.

#### Step 1: Set Queue Connection

In your `.env` file, set:
```env
QUEUE_CONNECTION=database
```

#### Step 2: Create Jobs Table

Run migrations to create the jobs table:
```bash
php artisan queue:table
php artisan migrate
```

#### Step 3: Generate Secret Token

Generate a secure token for the queue processor endpoint:
```bash
php artisan key:generate --show
```

Or create a custom token and add to `.env`:
```env
QUEUE_PROCESSOR_TOKEN=your-secret-token-here
```

#### Step 4: Set Up Cron Job in Hostinger

1. Log in to your Hostinger control panel
2. Navigate to **Cron Jobs** section
3. Create a new cron job with these settings:
   - **Frequency:** Every minute (`* * * * *`)
   - **Command:** 
     ```bash
     curl -s "https://yourdomain.com/queue/process?token=YOUR_SECRET_TOKEN" > /dev/null 2>&1
     ```
   - Replace `YOUR_SECRET_TOKEN` with the token from Step 3
   - Replace `yourdomain.com` with your actual domain

**Alternative using PHP:**
```bash
php /home/username/public_html/artisan queue:work --stop-when-empty --tries=3 --max-time=55
```
(Replace `/home/username/public_html` with your actual path)

#### Step 5: Configure Environment Variables (Optional)

Add these to `.env` for fine-tuning:
```env
QUEUE_PROCESSOR_TOKEN=your-secret-token-here
QUEUE_PROCESSOR_MAX_JOBS=10
QUEUE_PROCESSOR_TIMEOUT=55
```

### Option 2: Use Laravel Scheduler (If Cron Available)

Laravel's scheduler can process queue jobs if you have access to a single cron job.

#### Step 1: Set Up Single Cron Job

In Hostinger cron jobs, add:
```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

#### Step 2: Scheduler Already Configured

The scheduler is already configured in `app/Console/Kernel.php` to process queue jobs every minute. No additional steps needed!

### Option 3: Synchronous Processing (Fallback)

If cron is not available, you can process jobs synchronously but in smaller chunks.

#### Modify `.env`:
```env
QUEUE_CONNECTION=sync
```

**Note:** With sync driver, jobs execute immediately in the same request. The progress modal will still work, but jobs won't process in true background. This is suitable for smaller operations.

## Verification

### Test Queue Processing

1. **Test Web Endpoint:**
   ```
   https://yourdomain.com/queue/process?token=YOUR_SECRET_TOKEN
   ```
   Should return JSON: `{"success":true,"message":"Queue processed successfully"}`

2. **Check Queue Status:**
   ```bash
   php artisan tinker
   >>> DB::table('jobs')->count()
   ```
   If count > 0, jobs are queued

3. **Check Logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```
   Look for "Queue processor executed" messages

4. **Test Bulk Delete:**
   - Perform a bulk delete operation
   - Check if jobs are processed within 1-2 minutes (if cron is running)

## Troubleshooting

### Jobs Not Processing

1. **Check Cron Job:**
   - Verify cron job is active in Hostinger control panel
   - Check cron job logs in Hostinger
   - Ensure the URL/command is correct

2. **Check Queue Connection:**
   ```bash
   php artisan config:show queue.default
   ```
   Should show `database`, not `sync`

3. **Check Token:**
   - Verify `QUEUE_PROCESSOR_TOKEN` in `.env` matches the token in cron URL
   - Test endpoint manually with correct token

4. **Check Jobs Table:**
   ```bash
   php artisan tinker
   >>> DB::table('jobs')->get()
   ```
   Check if jobs are being created

5. **Check Logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```
   Look for errors or "Queue processor" messages

### Timeout Issues

If jobs are timing out:
- Reduce `QUEUE_PROCESSOR_MAX_JOBS` (default: 10)
- Reduce `QUEUE_PROCESSOR_TIMEOUT` (default: 55 seconds)
- Process fewer items per deletion operation

### Token Security

- Keep `QUEUE_PROCESSOR_TOKEN` secret
- Don't commit it to version control
- Use a strong, random token
- Change it periodically

## Performance Tips

1. **Adjust Processing Frequency:**
   - Process every minute for faster job processing
   - Process every 5 minutes to reduce server load

2. **Optimize Job Processing:**
   - Process fewer jobs per request if timeouts occur
   - Use smaller chunk sizes for large operations

3. **Monitor Server Resources:**
   - Check CPU and memory usage
   - Adjust `QUEUE_PROCESSOR_MAX_JOBS` based on server capacity

## Important Notes

- **Queue jobs will only process when cron runs** - They won't process in real-time like with Supervisor
- **Processing frequency depends on cron schedule** - Every minute is recommended for timely processing
- **Jobs are stored in database** - Make sure your database can handle the jobs table
- **Failed jobs are stored separately** - Check `failed_jobs` table if jobs fail
- **Progress modal still works** - The frontend polls for progress, which works independently of queue processing

## Alternative: External Queue Service

If shared hosting limitations are too restrictive, consider:
- **Laravel Horizon** with Redis (requires Redis support)
- **External queue service** like AWS SQS (requires API access)
- **Dedicated worker server** (VPS with Supervisor)

## Quick Setup Checklist

- [ ] Set `QUEUE_CONNECTION=database` in `.env`
- [ ] Run `php artisan queue:table && php artisan migrate`
- [ ] Generate/set `QUEUE_PROCESSOR_TOKEN` in `.env`
- [ ] Test web endpoint manually
- [ ] Set up cron job in Hostinger (Option 1) OR Laravel scheduler cron (Option 2)
- [ ] Verify cron is running (check Hostinger logs)
- [ ] Test bulk delete operation
- [ ] Monitor logs for successful job processing


