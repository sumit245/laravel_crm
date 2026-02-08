<?php

namespace App\Http\Controllers;

use App\Imports\StreetlightPoleImport;
use App\Exports\StreetlightPoleImportFormatExport;
use App\Jobs\ProcessPoleImportChunk;
use App\Models\PoleImportJob;
use App\Models\Streetlight;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class DeviceController extends Controller
{
    public function index()
    {
        $districts = Streetlight::select('district')->distinct()->get();
        $projectId = env('JICR_DEFAULT_PROJECT_ID', null);
        $project = $projectId ? Project::find($projectId) : Project::first();
        $projects = Project::select('id', 'project_name')->get();
        return view('poles.index', compact('districts', 'project', 'projects'));
    }

    /**
     * Download sample import file
     */
    public function downloadSample()
    {
        try {
            $filename = 'streetlight_pole_import_format_' . date('Y-m-d') . '.xlsx';
            return Excel::download(new StreetlightPoleImportFormatExport, $filename);
        } catch (\Exception $e) {
            Log::error('Failed to download pole import format', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Failed to download import format: ' . $e->getMessage());
        }
    }

    /**
     * Import pole data from Excel file
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // 10MB max
            'project_id' => 'required|exists:projects,id',
        ]);

        try {
            $file = $request->file('file');
            $fileSize = $file->getSize();
            $threshold = 1024 * 1024; // 1MB - files larger than this will be queued

            $projectId = $request->input('project_id');

            // For small files, process synchronously
            if ($fileSize < $threshold) {
                return $this->processSyncImport($file, $projectId);
            } else {
                // For large files, queue the import
                return $this->processQueuedImport($file, $projectId);
            }
        } catch (\Exception $e) {
            Log::error('Error importing file: ' . $e->getMessage());
            return back()->with('error', 'Error importing file: ' . $e->getMessage());
        }
    }

    /**
     * Process import synchronously (for small files)
     */
    protected function processSyncImport($file, $projectId)
    {
        try {
            $import = new StreetlightPoleImport(null, null, $projectId);
            Excel::import($import, $file);

            $errors = $import->getErrors();
            $successCount = $import->getSuccessCount();
            $errorCount = $import->getErrorCount();
            $errorFileUrl = null;

            // Generate error file if there are errors
            if (!empty($errors)) {
                $errorFileUrl = $this->generateErrorFile($errors, 'sync_' . time());
            }

            $redirect = back();

            // Always show error file link if there are errors
            if ($errorFileUrl) {
                $redirect->with('import_errors_url', $errorFileUrl)
                    ->with('import_errors_count', $errorCount);
            }

            if ($successCount > 0) {
                // Show success message if there are successful imports
                $message = "Pole data imported successfully! Imported: {$successCount}";
                if ($errorCount > 0) {
                    $message .= ", Errors: {$errorCount}";
                }
                $redirect->with('success', $message);
            }
            // If successCount == 0, only the warning message with error file link will be shown

            return $redirect;
        } catch (\Exception $e) {
            Log::error('Error in sync import: ' . $e->getMessage());
            return back()->with('error', 'Error importing file: ' . $e->getMessage());
        }
    }

    /**
     * Process import asynchronously (for large files)
     */
    protected function processQueuedImport($file, $projectId)
    {
        try {
            // Store file temporarily
            $fileName = 'pole_import_' . time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('imports', $fileName, 'local');

            // Count total rows (estimate - read first sheet)
            $totalRows = 0;
            try {
                $data = Excel::toArray([], $file);
                if (!empty($data[0])) {
                    $totalRows = count($data[0]) - 1; // Subtract header row
                }
            } catch (\Exception $e) {
                Log::warning('Could not count rows for import', ['error' => $e->getMessage()]);
                // Estimate based on file size (rough estimate: 1KB per row)
                $totalRows = max(1000, (int) ($file->getSize() / 1024));
            }

            // Create job record
            $job = PoleImportJob::create([
                'file_path' => $filePath,
                'total_rows' => $totalRows,
                'status' => 'pending',
                'user_id' => auth()->id(),
                'project_id' => $projectId,
            ]);

            // Dispatch job
            ProcessPoleImportChunk::dispatch($job->job_id, 100);

            Log::info('Pole import job queued', [
                'job_id' => $job->job_id,
                'file_path' => $filePath,
                'total_rows' => $totalRows
            ]);

            return back()->with([
                'success' => 'Import started. Processing in background...',
                'import_job_id' => $job->job_id
            ]);
        } catch (\Exception $e) {
            Log::error('Error queuing import: ' . $e->getMessage());
            return back()->with('error', 'Error starting import: ' . $e->getMessage());
        }
    }

    /**
     * Get import progress
     */
    public function getImportProgress($jobId)
    {
        $job = PoleImportJob::where('job_id', $jobId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $progress = $job->progress_percentage;
        $status = $job->status;

        $message = "Processing {$job->processed_rows} row(s) out of {$job->total_rows}";
        if ($job->success_count > 0 || $job->error_count > 0) {
            $message .= " (Success: {$job->success_count}, Errors: {$job->error_count})";
        }

        $response = [
            'status' => 'success',
            'job_id' => $job->job_id,
            'job_status' => $status,
            'progress_percentage' => $progress,
            'processed_rows' => $job->processed_rows,
            'total_rows' => $job->total_rows,
            'success_count' => $job->success_count,
            'error_count' => $job->error_count,
            'message' => $message,
            'error_file_path' => $job->error_file_path,
            'started_at' => $job->started_at?->toIso8601String(),
            'completed_at' => $job->completed_at?->toIso8601String(),
        ];

        // If completed, add error file URL
        if ($status === 'completed' && $job->error_file_path) {
            $response['error_file_url'] = Storage::disk('public')->url($job->error_file_path);
        }

        return response()->json($response);
    }

    /**
     * Generate error file from errors array
     */
    protected function generateErrorFile(array $errors, string $identifier): string
    {
        $lines = [];
        $lines[] = 'Streetlight Pole Import Errors - ' . now()->toDateTimeString();
        $lines[] = 'Import ID: ' . $identifier;
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

        $fileName = 'pole_import_errors_' . $identifier . '_' . time() . '.txt';
        $relativePath = 'import_errors/' . $fileName;
        $disk->put($relativePath, $content);

        return $disk->url($relativePath);
    }
}
