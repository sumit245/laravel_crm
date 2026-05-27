<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\RmsPushLog;
use App\Models\Streetlight;
use App\Services\Logging\ActivityLogger;
use App\Services\Rms\RmsSyncService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Remote Monitoring System Integration — pushes pole installation data to the government's RMS
 * portal. Tracks push status, handles API communication, and logs responses for each pole.
 * Supports individual and bulk push operations.
 *
 * Data Flow:
 *   Select poles → Prepare payload (coordinates, serial numbers, photos) → POST to RMS API
 *   → Log response → Mark poles as pushed → Track push history
 *
 * @depends-on Pole, RmsPushLog, RemoteApiHelper, Project
 * @business-domain Government Integration
 * @package App\Http\Controllers
 */
class RMSController extends Controller
{
    public function __construct(
        protected ActivityLogger $activityLogger
    ) {
    }

    /**
     * Display a listing of the resource.
     *
     * Data flow: HTTP Request → Database Query → Blade View
     *
     * @param  Request  $request  The incoming HTTP request
     * @return void  
     */
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

    /**
     * Send panchayat to r m s.
     *
     * Data flow: HTTP Request → Processing → Response
     *
     * @param  Request  $request  The incoming HTTP request
     * @return void  
     */
    public function sendPanchayatToRMS(Request $request, RmsSyncService $rmsSyncService)
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
            $projectName = $project->id == 11 ? 'SUGS' : 'SUGS Phase 4'; // Default or fallback

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

            // 3. Ensure selected area exists
            $query = Streetlight::where('district', $validated['district'])
                ->where('block', $validated['block'])
                ->where('panchayat', $validated['panchayat'])
                ->where('project_id', $validated['project_id']);

            if (!$query->exists()) {
                return response()->json(['message' => 'No streetlights found for the selected panchayat.'], 404);
            }
            $batch = $rmsSyncService->queueSync([
                'source' => 'rms_portal_push',
                'project_id' => (int) $validated['project_id'],
                'district' => $validated['district'],
                'block' => $validated['block'],
                'panchayat' => $validated['panchayat'],
                'filter' => 'all',
            ], auth()->id(), $projectName);

            $this->activityLogger->log('rms', 'pushed', null, [
                'description' => 'Panchayat RMS sync queued.',
                'extra' => [
                    'district' => $validated['district'],
                    'block' => $validated['block'],
                    'panchayat' => $validated['panchayat'],
                    'project_id' => $validated['project_id'] ?? null,
                    'batch_id' => $batch->id,
                ],
            ]);

            return response()->json([
                'message' => 'Pole data sync queued for ' . $validated['panchayat'] . '.',
                'batch_id' => $batch->id,
                'status' => $batch->status,
                'status_url' => route('rms.sync.status', $batch),
            ], 202);
        } catch (Exception $e) {
            // Catch any unexpected errors during the initial data fetch.
            Log::critical('A critical error occurred during the RMS push preparation.', [
                'filters' => $validated,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['message' => 'A critical error occurred: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Export data to file.
     *
     * Data flow: HTTP Request → Processing → Response
     *
     * @param  Request  $request  The incoming HTTP request
     * @return void  
     */
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
