<?php

namespace App\Http\Controllers;

use App\Jobs\SyncPolesToRmsJob;
use App\Models\Pole;
use App\Models\Project;
use App\Models\RmsPushLog;
use App\Models\Streetlight;
use App\Models\StreetlightTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RmsSyncController extends Controller
{
    /**
     * Display the minimal UI for bulk syncing
     */
    public function index()
    {
        $projects = Project::where('project_type', 1)->get(); // 1 = Streetlight
        return view('rms.sync', compact('projects'));
    }

    /**
     * Validate poles for a given project to see if there are any missing location codes
     */
    public function validatePoles(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id'
        ]);

        $projectId = $validated['project_id'];

        // Find all streetlights for this project that have missing codes
        $missingCodesSites = Streetlight::where('project_id', $projectId)
            ->where(function ($query) {
                $query->whereNull('district_code')
                    ->orWhereNull('block_code')
                    ->orWhereNull('panchayat_code')
                    ->orWhere('district_code', '')
                    ->orWhere('block_code', '')
                    ->orWhere('panchayat_code', '');
            })
            ->select('id', 'district', 'block', 'panchayat', 'district_code', 'block_code', 'panchayat_code')
            ->distinct('district', 'block', 'panchayat') // We just need unique missing combinations
            ->get();

        if ($missingCodesSites->isEmpty()) {
            return response()->json([
                'status' => 'success',
                'message' => 'All validation passed. No missing codes.'
            ]);
        }

        // Group by unique district/block/panchayat combinations
        $uniqueMissingCombinations = collect();
        $seen = [];

        foreach ($missingCodesSites as $site) {
            $key = $site->district . '|' . $site->block . '|' . $site->panchayat;
            if (!isset($seen[$key])) {
                $seen[$key] = true;
                $uniqueMissingCombinations->push($site);
            }
        }

        return response()->json([
            'status' => 'missing_codes',
            'data' => $uniqueMissingCombinations
        ]);
    }

    /**
     * Update the missing location codes
     */
    public function updateCodes(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'codes' => 'required|array',
            'codes.*.district' => 'required|string',
            'codes.*.block' => 'required|string',
            'codes.*.panchayat' => 'required|string',
            'codes.*.district_code' => 'required|string',
            'codes.*.block_code' => 'required|string',
            'codes.*.panchayat_code' => 'required|string',
        ]);

        try {
            DB::beginTransaction();
            foreach ($validated['codes'] as $codeData) {
                Streetlight::where('project_id', $validated['project_id'])
                    ->where('district', $codeData['district'])
                    ->where('block', $codeData['block'])
                    ->where('panchayat', $codeData['panchayat'])
                    ->update([
                        'district_code' => $codeData['district_code'],
                        'block_code' => $codeData['block_code'],
                        'panchayat_code' => $codeData['panchayat_code'],
                    ]);
            }
            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Codes updated successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update RMS sync location codes', ['error' => $e->getMessage()]);
            return response()->json(['status' => 'error', 'message' => 'Failed to update codes.'], 500);
        }
    }

    /**
     * Start the sync background job
     */
    public function startSync(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id'
        ]);

        $projectId = $validated['project_id'];

        // Find poles for this project that haven't been successfully synced yet
        // 1. Get sites for the project
        $siteIds = Streetlight::where('project_id', $projectId)->pluck('id');

        if ($siteIds->isEmpty()) {
            return response()->json(['status' => 'error', 'message' => 'No sites found for this project.'], 404);
        }

        // 2. Get tasks for those sites
        $taskIds = StreetlightTask::whereIn('site_id', $siteIds)->pluck('id');

        if ($taskIds->isEmpty()) {
            // Check if project 19 task logic applies
            if ($projectId == 19) {
                $taskIds = collect([96492409]); // The mock task ID for project 19
            } else {
                return response()->json(['status' => 'error', 'message' => 'No tasks found for this project.'], 404);
            }
        }

        // 3. Get all poles
        $poleQuery = Pole::whereIn('task_id', $taskIds);

        // Filter out poles that already have a successful push log
        $successfullyPushedPoleIds = RmsPushLog::where('response_data->status', 'OK')
            ->whereIn('pole_id', $poleQuery->pluck('id'))
            ->pluck('pole_id');

        $polesToSync = $poleQuery->whereNotIn('id', $successfullyPushedPoleIds)->get();

        if ($polesToSync->isEmpty()) {
            return response()->json(['status' => 'error', 'message' => 'No unsynced poles found for this project.']);
        }

        // Dispatch the job with the user ID who initiated it
        SyncPolesToRmsJob::dispatch($projectId, $polesToSync->pluck('id')->toArray(), auth()->id());

        return response()->json([
            'status' => 'success',
            'message' => 'Sync job started successfully.',
            'total_poles' => $polesToSync->count()
        ]);
    }

    /**
     * Fetch progress logs for the UI
     */
    public function getProgress(Request $request)
    {
        $lastFetch = $request->input('last_fetch'); // Timestamp

        $query = RmsPushLog::orderBy('created_at', 'desc')->take(50); // Get latest 50 logs

        if ($lastFetch) {
            $query->where('created_at', '>', date('Y-m-d H:i:s', $lastFetch));
        }

        $logs = $query->get()->map(function ($log) {
            $responseStatus = is_array($log->response_data) ? ($log->response_data['status'] ?? 'unknown') : 'unknown';
            $success = strtoupper(strval($responseStatus)) === 'OK';
            return [
                'id' => $log->id,
                'pole_id' => $log->pole_id,
                'status' => $success ? 'success' : 'error',
                'message' => $log->message,
                'time' => $log->created_at->format('H:i:s'),
                // Include partial pole data if available
                'pole_number' => $log->pole ? $log->pole->complete_pole_number : null,
                'panchayat' => $log->panchayat
            ];
        });

        return response()->json([
            'logs' => $logs,
            'timestamp' => time()
        ]);
    }
}
