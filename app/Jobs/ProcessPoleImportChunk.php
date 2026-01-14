<?php

namespace App\Jobs;

use App\Models\PoleImportJob;
use App\Imports\StreetlightPoleImport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ProcessPoleImportChunk implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 3600; // 1 hour timeout
    public $backoff = [60, 120, 300]; // Exponential backoff

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $jobId,
        public int $chunkSize = 100
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $job = PoleImportJob::where('job_id', $this->jobId)->first();

        if (!$job) {
            Log::error('Pole import job not found', ['job_id' => $this->jobId]);
            return;
        }

        // Mark as processing if not already
        if ($job->status === 'pending') {
            $job->markAsProcessing();
        }

        try {
            // Check if file exists (file was stored to 'local' disk)
            if (!Storage::disk('local')->exists($job->file_path)) {
                throw new \Exception("Import file not found at path: {$job->file_path}");
            }

            // Get file path
            $filePath = Storage::disk('local')->path($job->file_path);

            // Create import instance - it will handle chunking internally
            $import = new StreetlightPoleImport($this->jobId, $job);
            
            // Import the file - this will process in chunks
            // The import instance will be modified during import, so we can access methods after
            Excel::import($import, $filePath);

            // Get results from import (methods are called on the instance after import completes)
            $successCount = $import->getSuccessCount();
            $errorCount = $import->getErrorCount();
            $errors = $import->getErrors();

            // Update job with final counts
            $job->refresh();
            $job->update([
                'processed_rows' => $job->total_rows,
                'success_count' => $successCount,
                'error_count' => $errorCount,
            ]);

            // Generate error file if there are errors
            if (!empty($errors)) {
                $errorFilePath = $this->generateErrorFile($errors, $job->job_id);
                $job->update(['error_file_path' => $errorFilePath]);
            }

            // Mark as completed
            $job->markAsCompleted();

            Log::info('Pole import job completed', [
                'job_id' => $this->jobId,
                'success_count' => $successCount,
                'error_count' => $errorCount
            ]);

        } catch (\Exception $e) {
            Log::error('Critical error processing pole import', [
                'job_id' => $this->jobId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Mark as failed
            $job->markAsFailed($e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate error file from errors array
     */
    private function generateErrorFile(array $errors, string $jobId): string
    {
        $lines = [];
        $lines[] = 'Streetlight Pole Import Errors - ' . now()->toDateTimeString();
        $lines[] = 'Job ID: ' . $jobId;
        $lines[] = str_repeat('=', 80);
        $lines[] = '';

        foreach ($errors as $error) {
            $lines[] = "Row: " . ($error['row'] ?? 'Unknown');
            $lines[] = "  Pole Number: " . ($error['complete_pole_number'] ?? '');
            $lines[] = "  Reason: " . ($error['reason'] ?? 'Unknown error');
            if (isset($error['details'])) {
                $lines[] = "  Details: " . $error['details'];
            }
            $lines[] = str_repeat('-', 40);
        }

        $content = implode(PHP_EOL, $lines) . PHP_EOL;

        $disk = Storage::disk('public');
        if (!$disk->exists('import_errors')) {
            $disk->makeDirectory('import_errors');
        }

        $fileName = 'pole_import_errors_' . $jobId . '_' . time() . '.txt';
        $relativePath = 'import_errors/' . $fileName;
        $disk->put($relativePath, $content);

        return $relativePath;
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        $job = PoleImportJob::where('job_id', $this->jobId)->first();
        
        if ($job && $job->status !== 'completed') {
            $job->markAsFailed($exception->getMessage());
        }
    }
}

