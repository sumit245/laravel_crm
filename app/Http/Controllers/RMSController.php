<?php

namespace App\Http\Controllers;

use App\Helpers\RemoteApiHelper;
use App\Http\Controllers\Controller;
use App\Models\Pole;
use App\Models\Project;
use App\Models\RmsPushLog;
use App\Models\Streetlight;
use App\Models\StreetlightTask;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\Logging\ActivityLogger;

class RMSController extends Controller
{
    public function __construct(
        protected ActivityLogger $activityLogger
    ) {
    }
    public function index(Request $request)
    {
        // Validate incoming filter data
        $validated = $request->validate([
            'district' => 'sometimes|string|max:255',
            'block' => 'sometimes|string|max:255',
            'panchayat' => 'sometimes|string|max:255',
        ]);

        // Prepare data to pass to the view
        $data = [
            'districts' => Streetlight::select('district')->distinct()->get(),
            'blocks' => null,
            'panchayats' => null,
            'wards' => null,
            'selected' => $validated, // Pass all validated inputs
        ];

        // Fetch blocks if a district is selected
        if (!empty($validated['district'])) {
            $data['blocks'] = Streetlight::select('block')
                ->where('district', $validated['district'])
                ->distinct()
                ->get();
        }

        // Fetch panchayats if a block is selected
        if (!empty($validated['block'])) {
            $data['panchayats'] = Streetlight::select('panchayat')
                ->where('block', $validated['block'])
                ->distinct()
                ->get();
        }

        // Fetch wards if a panchayat is selected
        if (!empty($validated['panchayat'])) {
            $data['wards'] = Streetlight::select('ward')
                ->where('panchayat', $validated['panchayat'])
                ->distinct()
                ->get();
        }

        return view('rms.index', $data);
    }

    public function sendPanchayatToRMS(Request $request)
    {
        // 1. Validate the incoming request to ensure we have the location.
        $validated = $request->validate([
            'district' => 'required|string',
            'block' => 'required|string',
            'panchayat' => 'required|string',
        ]);

        try {
            // 2. Efficiently fetch all necessary data in bulk to prevent N+1 query issues.

            // Get all streetlights (sites) for the selected panchayat.
            $streetlights = Streetlight::where('district', $validated['district'])
                ->where('block', $validated['block'])
                ->where('panchayat', $validated['panchayat'])
                ->get();

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

            // 3. Process each pole and send its data, similar to your old method.
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

                    // Call your helper to send the data.
                    $apiResponse = RemoteApiHelper::sendPoleDataToRemoteServer($pole, $streetlight, $approved_by);

                    $responseData = $apiResponse ? $apiResponse->json() : null;
                    $status = 'error';
                    $message = 'Unknown error';

                    if ($apiResponse && $apiResponse->successful() && $responseData && isset($responseData['status']) && strtoupper((string) $responseData['status']) === 'OK') {
                        $status = 'success';
                        $message = $responseData['detail'] ?? $responseData['details'] ?? 'Successfully pushed to RMS';
                    } else {
                        $message = $responseData['detail'] ?? $responseData['details'] ?? ($apiResponse ? $apiResponse->body() : 'No response from RMS API');
                        if (! $responseData || ! isset($responseData['status'])) {
                            $responseData = ['status' => 'ERR', 'detail' => $message];
                        }
                    }

                    RmsPushLog::create([
                        'pole_id' => $pole->id,
                        'message' => $message,
                        'response_data' => $responseData,
                        'district' => $streetlight->district ?? null,
                        'block' => $streetlight->block ?? null,
                        'panchayat' => $streetlight->panchayat ?? null,
                        'pushed_by' => auth()->id(),
                        'pushed_at' => now(),
                    ]);

                    $responses[] = ['pole_id' => $pole->id, 'status' => $status, 'message' => $message];
                } catch (Exception $e) {
                    Log::error("Failed to send pole data to RMS", [
                        'pole_id' => $pole->id,
                        'error' => $e->getMessage(),
                    ]);
                    RmsPushLog::create([
                        'pole_id' => $pole->id,
                        'message' => $e->getMessage(),
                        'response_data' => ['status' => 'ERR', 'detail' => $e->getMessage()],
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

            $successLogs = $logs->filter(fn ($log) => strtoupper((string) ($log->response_data['status'] ?? '')) === 'OK');
            $errorLogs = $logs->filter(fn ($log) => strtoupper((string) ($log->response_data['status'] ?? '')) !== 'OK');

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
