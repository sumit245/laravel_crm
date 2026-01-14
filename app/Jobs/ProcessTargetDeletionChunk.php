<?php

namespace App\Jobs;

use App\Models\TargetDeletionJob;
use App\Services\Task\TargetDeletionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessTargetDeletionChunk implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 5; // More retries for resilience
    public $timeout = 3600; // 1 hour timeout - handles very large deletions
    public $backoff = [60, 120, 300]; // Exponential backoff: 1min, 2min, 5min

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $jobId,
        public int $chunkSize = 50
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(TargetDeletionService $deletionService): void
    {
        // #region agent log
        file_put_contents(base_path('.cursor/debug.log'), json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'C','location'=>'ProcessTargetDeletionChunk.php:37','message'=>'Job handle started','data'=>['job_id'=>$this->jobId,'chunk_size'=>$this->chunkSize],'timestamp'=>time()*1000])."\n",FILE_APPEND);
        // #endregion
        $job = TargetDeletionJob::where('job_id', $this->jobId)->first();

        if (!$job) {
            Log::error('Target deletion job not found', ['job_id' => $this->jobId]);
            // #region agent log
            file_put_contents(base_path('.cursor/debug.log'), json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'C','location'=>'ProcessTargetDeletionChunk.php:42','message'=>'Job not found in database','data'=>['job_id'=>$this->jobId],'timestamp'=>time()*1000])."\n",FILE_APPEND);
            // #endregion
            return;
        }

        // Mark as processing if not already
        if ($job->status === 'pending') {
            $job->markAsProcessing();
        }

        // Get unprocessed task IDs
        $allTaskIds = $job->task_ids ?? [];
        $processedTaskIds = $job->processed_task_ids ?? [];
        $remainingTaskIds = array_diff($allTaskIds, $processedTaskIds);
        // #region agent log
        file_put_contents(base_path('.cursor/debug.log'), json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'C','location'=>'ProcessTargetDeletionChunk.php:54','message'=>'Remaining tasks calculated','data'=>['remaining_count'=>count($remainingTaskIds),'total_count'=>count($allTaskIds)],'timestamp'=>time()*1000])."\n",FILE_APPEND);
        // #endregion

        if (empty($remainingTaskIds)) {
            // All tasks processed, mark as completed
            $job->markAsCompleted();
            return;
        }

        // Process chunk
        $chunk = array_slice($remainingTaskIds, 0, $this->chunkSize);
        // #region agent log
        file_put_contents(base_path('.cursor/debug.log'), json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'C','location'=>'ProcessTargetDeletionChunk.php:64','message'=>'Processing chunk','data'=>['chunk_size'=>count($chunk),'task_ids'=>$chunk],'timestamp'=>time()*1000])."\n",FILE_APPEND);
        // #endregion

        try {
            foreach ($chunk as $taskId) {
                try {
                    // #region agent log
                    file_put_contents(base_path('.cursor/debug.log'), json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'D','location'=>'ProcessTargetDeletionChunk.php:69','message'=>'Deleting task','data'=>['task_id'=>$taskId],'timestamp'=>time()*1000])."\n",FILE_APPEND);
                    // #endregion
                    // Process deletion for this task within its own transaction
                    $result = $deletionService->deleteTargets([$taskId], $this->jobId);
                    // #region agent log
                    file_put_contents(base_path('.cursor/debug.log'), json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'D','location'=>'ProcessTargetDeletionChunk.php:73','message'=>'Task deleted successfully','data'=>['task_id'=>$taskId,'poles_deleted'=>$result['poles_deleted']??0],'timestamp'=>time()*1000])."\n",FILE_APPEND);
                    // #endregion

                    // Update progress after successful deletion
                    $job->addProcessedTaskId($taskId);
                    $job->increment('processed_poles', $result['poles_deleted'] ?? 0);

                    // Refresh job to get latest state
                    $job->refresh();
                    
                    Log::info('Successfully deleted task in chunk', [
                        'job_id' => $this->jobId,
                        'task_id' => $taskId,
                        'poles_deleted' => $result['poles_deleted'] ?? 0
                    ]);
                } catch (\Exception $taskException) {
                    // Log error but continue with next task
                    Log::error('Error deleting individual task in chunk', [
                        'job_id' => $this->jobId,
                        'task_id' => $taskId,
                        'error' => $taskException->getMessage()
                    ]);
                    
                    // Mark this task as failed but continue processing others
                    $job->addProcessedTaskId($taskId); // Mark as processed to avoid retry loop
                    $job->refresh();
                    
                    // Don't throw - continue with next task
                    continue;
                }
            }

            // Check if all tasks are processed
            $job->refresh();
            $remainingAfterChunk = array_diff($job->task_ids, $job->processed_task_ids);

            if (empty($remainingAfterChunk)) {
                // All done
                $job->markAsCompleted();
                Log::info('Target deletion job completed', ['job_id' => $this->jobId]);
            } else {
                // Queue next chunk with a small delay to prevent overwhelming the queue
                self::dispatch($this->jobId, $this->chunkSize)
                    ->onConnection(config('queue.default'))
                    ->onQueue('default')
                    ->delay(now()->addSeconds(2));
                Log::info('Queued next chunk for target deletion', [
                    'job_id' => $this->jobId,
                    'remaining_tasks' => count($remainingAfterChunk)
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Critical error processing target deletion chunk', [
                'job_id' => $this->jobId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Don't mark as failed immediately - allow retry
            // Only mark as failed if we've exhausted retries
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        $job = TargetDeletionJob::where('job_id', $this->jobId)->first();
        
        if ($job && $job->status !== 'completed') {
            $job->markAsFailed($exception->getMessage());
        }
    }
}
