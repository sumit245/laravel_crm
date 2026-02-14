<?php

namespace App\Http\Controllers;

use App\Helpers\RemoteApiHelper;
use App\Models\Pole;
use App\Models\Project;
use App\Models\RmsPushLog;
use App\Models\Streetlight;
use App\Models\StreetlightTask;
use App\Services\Logging\ActivityLogger;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RMSController extends Controller
{
    public function __construct(
        protected ActivityLogger $activityLogger
    ) {
    }

    public function index(Request $request)
    {
        // 1. Fetch all street light projects for the dropdown
        $projects = Project::where('project_type', 1)->get(); // 1 = Streetlight

        // Validate incoming filter data
        $validated = $request->validate([
            'district' => 'sometimes|string|max:255',
            'block' => 'sometimes|string|max:255',
            'panchayat' => 'sometimes|string|max:255',
            'project_id' => 'sometimes|integer|exists:projects,id',
        ]);

        $projectId = $request->input('project_id');

        // Base query for fetching districts
        $districtQuery = Streetlight::select('district')->distinct();

        if ($projectId) {
            $districtQuery->where('project_id', $projectId);
            $districts = $districtQuery->get();
        } else {
            // New Requirement: "If I select a project then only such districts... will appear"
            // So initially, keep empty to force project selection.
            $districts = collect([]);
        }

        // Prepare data to pass to the view
        $data = [
            'projects' => $projects,
            'districts' => $districts,
            'blocks' => null,
            'panchayats' => null,
            'wards' => null,
            'selected' => $validated, // Pass all validated inputs
            'project_id' => $projectId,
        ];

        // Fetch blocks if a district is selected
        if (!empty($validated['district']) && $projectId) {
            $blockQuery = Streetlight::select('block')
                ->where('district', $validated['district'])
                ->distinct();

            if ($projectId) {
                $blockQuery->where('project_id', $projectId);
            }

            $data['blocks'] = $blockQuery->get();
        }

        // Fetch panchayats if a block is selected
        if (!empty($validated['block']) && $projectId) {
            $panchayatQuery = Streetlight::select('panchayat')
                ->where('block', $validated['block'])
                ->distinct();

            if ($projectId) {
                $panchayatQuery->where('project_id', $projectId);
            }

            $data['panchayats'] = $panchayatQuery->get();
        }

        // Fetch wards if a panchayat is selected
        if (!empty($validated['panchayat']) && $projectId) {
            $wardQuery = Streetlight::select('ward')
                ->where('panchayat', $validated['panchayat'])
                ->distinct();

            if ($projectId) {
                $wardQuery->where('project_id', $projectId);
            }

            $data['wards'] = $wardQuery->get();
        }

        return view('rms.index', $data);
    }

    public function sendPanchayatToRMS(Request $request)
    {
        Log::info('Controller: sendPanchayatToRMS');
        // 1. Validate the incoming request to ensure we have the location and optional codes.
        $validated = $request->validate([
            'district' => 'required|string',
            'block' => 'required|string',
            'panchayat' => 'required|string',
            'project_id' => 'required|integer|exists:projects,id',
            'district_code' => 'sometimes|nullable|string',
            'block_code' => 'sometimes|nullable|string',
            'panchayat_code' => 'sometimes|nullable|string',
        ]);

        try {
            // Fetch project details
            $project = Project::find($validated['project_id']);
            $projectName = $project ? $project->project_name : 'SUGS'; // Default or fallback

            // 2. Update codes if provided (Transactions for data integrity)
            if (!empty($validated['district_code']) || !empty($validated['block_code']) || !empty($validated['panchayat_code'])) {
                $updateData = [];
                if (!empty($validated['district_code']))
                    $updateData['district_code'] = $validated['district_code'];
                if (!empty($validated['block_code']))
                    $updateData['block_code'] = $validated['block_code'];
                if (!empty($validated['panchayat_code']))
                    $updateData['panchayat_code'] = $validated['panchayat_code'];

                if (!empty($updateData)) {
                    Streetlight::where('district', $validated['district'])
                        ->where('block', $validated['block'])
                        ->where('panchayat', $validated['panchayat'])
                        ->where('project_id', $validated['project_id'])
                        ->update($updateData);

                    Log::info('Updated location codes', $updateData);
                }
            }

            // 3. Fetch Data for RMS Push
            $query = Streetlight::where('district', $validated['district'])
                ->where('block', $validated['block'])
                ->where('panchayat', $validated['panchayat'])
                ->where('project_id', $validated['project_id']);

            $streetlights = $query->get();

            if ($streetlights->isEmpty()) {
                return response()->json(['message' => 'No streetlights found for the selected panchayat.'], 404);
            }

            // Create a map for quick lookups: [site_id => streetlight_model]
            $streetlightMap = $streetlights->keyBy('id');
            $streetlightIds = $streetlightMap->keys();

            // Get all related tasks, eager-loading the engineer to prevent another N+1 query.
            $tasks = StreetlightTask::whereIn('site_id', $streetlightIds)
                ->with('engineer') // Eager load the engineer relationship
                ->get();

            // Create a map for quick lookups: [task_id => task_model]
            $taskMap = $tasks->keyBy('id');
            $taskIds = $taskMap->keys();

            // Finally, get all poles associated with these tasks.
            $poles = Pole::whereIn('task_id', $taskIds)->get();

            if ($poles->isEmpty()) {
                return response()->json(['message' => 'No poles found for the selected tasks in this panchayat.'], 404);
            }

            // 4. Process each pole and send its data
            $responses = [];

            foreach ($poles as $pole) {
                try {
                    // Look up the related data from our pre-fetched maps.
                    $task = $taskMap->get($pole->task_id);
                    $streetlight = $task ? $streetlightMap->get($task->site_id) : null;

                    // Ensure all required data exists before proceeding.
                    if (!$task || !$streetlight || !$task->engineer) {
                        throw new Exception('Missing related task, streetlight, or engineer data.');
                    }

                    $approved_by = $task->engineer->firstName . ' ' . $task->engineer->lastName;

                    // Call your helper to send the data, passing the dynamic project name
                    $apiResponse = RemoteApiHelper::sendPoleDataToRemoteServer($pole, $streetlight, $approved_by, $projectName);

                    $responseData = $apiResponse ? $apiResponse->json() : null;
                    $status = 'error';
                    $message = 'Unknown error';

                    if ($apiResponse && $apiResponse->successful() && $responseData && isset($responseData['status']) && strtoupper((string) $responseData['status']) === 'OK') {
                        $status = 'success';
                        $message = $responseData['detail'] ?? $responseData['details'] ?? 'Successfully pushed to RMS';
                    } else {
                        $message = $responseData['detail'] ?? $responseData['details'] ?? ($apiResponse ? $apiResponse->body() : 'No response from RMS API');
                        if (!$responseData || !isset($responseData['status'])) {
                            $responseData = ['status' => 'ERR', 'detail' => $message];
                        }
                    }

                    RmsPushLog::create([
                        'pole_id' => $pole->id,
                        'message' => $message,
                        'response_data' => $responseData,
                        'status' => $status,
                        'district' => $streetlight->district ?? null,
                        'block' => $streetlight->block ?? null,
                        'panchayat' => $streetlight->panchayat ?? null,
                        'pushed_by' => auth()->id(),
                        'pushed_at' => now(),
                    ]);

                    $responses[] = ['pole_id' => $pole->id, 'status' => $status, 'message' => $message];
                } catch (Exception $e) {
                    Log::error('Failed to send pole data to RMS', [
                        'pole_id' => $pole->id,
                        'error' => $e->getMessage(),
                    ]);
                    RmsPushLog::create([
                        'pole_id' => $pole->id,
                        'message' => $e->getMessage(),
                        'response_data' => ['status' => 'ERR', 'detail' => $e->getMessage()],
                        'status' => 'error',
                        'district' => $validated['district'] ?? null,
                        'block' => $validated['block'] ?? null,
                        'panchayat' => $validated['panchayat'] ?? null,
                        'pushed_by' => auth()->id(),
                        'pushed_at' => now(),
                    ]);

                    $responses[] = [
                        'pole_id' => $pole->id,
                        'status' => 'error',
                        'message' => $e->getMessage(),
                    ];
                }
            }

            $successCount = collect($responses)->where('status', 'success')->count();
            $errorCount = collect($responses)->where('status', 'error')->count();

            $this->activityLogger->log('rms', 'pushed', null, [
                'description' => 'Panchayat data pushed to RMS.',
                'extra' => [
                    'district' => $validated['district'],
                    'block' => $validated['block'],
                    'panchayat' => $validated['panchayat'],
                    'project_id' => $validated['project_id'] ?? null,
                    'success_count' => $successCount,
                    'error_count' => $errorCount,
                ],
            ]);

            return response()->json([
                'message' => 'Pole data sync process completed for ' . $validated['panchayat'] . '.',
                'result' => $responses,
                'success_count' => $successCount,
                'error_count' => $errorCount,
            ]);
        } catch (Exception $e) {
            // Catch any unexpected errors during the initial data fetch.
            Log::critical('A critical error occurred during the RMS push preparation.', [
                'filters' => $validated,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['message' => 'A critical error occurred: ' . $e->getMessage()], 500);
        }
    }

    public function export(Request $request)
    {
        try {
            $query = RmsPushLog::with(['pole', 'pushedBy']);

            // Filter by pole_id if provided
            if ($request->filled('pole_id')) {
                $query->where('pole_id', $request->pole_id);
            }

            // Filter by project_id if provided (through pole->task->streetlight)
            if ($request->filled('project_id')) {
                $query->whereHas('pole.task.streetlight', function ($q) use ($request) {
                    $q->where('project_id', $request->project_id);
                });
            }

            // Filter by panchayat if provided
            if ($request->filled('panchayat')) {
                $query->where('panchayat', $request->panchayat);
            }

            $logs = $query->orderBy('pushed_at', 'desc')->get();

            $successLogs = $logs->filter(fn($log) => strtoupper((string) ($log->response_data['status'] ?? '')) === 'OK');
            $errorLogs = $logs->filter(fn($log) => strtoupper((string) ($log->response_data['status'] ?? '')) !== 'OK');

            return view('rms.export', compact('logs', 'successLogs', 'errorLogs'));
        } catch (\Exception $e) {
            Log::error('RMS Export Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return view('rms.export', [
                'logs' => collect(),
                'successLogs' => collect(),
                'errorLogs' => collect(),
                'error' => 'Error loading RMS export data: ' . $e->getMessage(),
            ]);
        }
    }
}
