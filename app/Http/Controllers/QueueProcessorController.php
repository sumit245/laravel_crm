<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class QueueProcessorController extends Controller
{
    /**
     * Process queued jobs via web endpoint (for shared hosting)
     * 
     * This endpoint can be called via cron job:
     * * * * * * curl -s "https://yourdomain.com/queue/process?token=YOUR_SECRET_TOKEN" > /dev/null 2>&1
     */
    public function process(Request $request)
    {
        // Verify secret token to prevent unauthorized access
        $token = $request->query('token');
        $expectedToken = env('QUEUE_PROCESSOR_TOKEN', config('app.key'));
        
        if (!$token || $token !== $expectedToken) {
            Log::warning('Queue processor accessed with invalid token', [
                'ip' => $request->ip(),
                'token_provided' => $token ? 'yes' : 'no'
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        try {
            // Process a limited number of jobs per request to avoid timeout
            $maxJobs = (int) env('QUEUE_PROCESSOR_MAX_JOBS', 10);
            $timeout = (int) env('QUEUE_PROCESSOR_TIMEOUT', 55); // Leave buffer for shared hosting timeout
            
            // Use Artisan to process queue jobs with --stop-when-empty
            // This processes available jobs and exits, perfect for cron
            Artisan::call('queue:work', [
                '--stop-when-empty' => true,
                '--max-jobs' => $maxJobs,
                '--timeout' => $timeout,
                '--tries' => 3,
            ]);

            $output = Artisan::output();
            $processed = trim($output) !== '';
            
            Log::info('Queue processor executed', [
                'max_jobs' => $maxJobs,
                'timeout' => $timeout,
                'jobs_processed' => $processed,
                'output' => $output
            ]);

            return response()->json([
                'success' => true,
                'message' => $processed ? 'Jobs processed successfully' : 'No jobs to process',
                'jobs_processed' => $processed
            ]);

        } catch (\Exception $e) {
            Log::error('Queue processor error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error processing queue: ' . $e->getMessage()
            ], 500);
        }
    }
}

