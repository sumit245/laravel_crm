<?php

namespace App\Http\Controllers;

use App\Helpers\RemoteApiHelper;
use App\Http\Controllers\Controller;
use App\Models\Pole;
use App\Models\Project;
use App\Models\Streetlight;
use App\Models\StreetlightTask;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RMSController extends Controller
{
    //
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
                    RemoteApiHelper::sendPoleDataToRemoteServer($pole, $streetlight, $approved_by);

                    $responses[] = ['pole_id' => $pole->id, 'status' => 'success'];
                } catch (Exception $e) {
                    Log::error("Failed to send pole data to RMS", [
                        'pole_id' => $pole->id,
                        'error' => $e->getMessage(),
                    ]);
                    $responses[] = [
                        'pole_id' => $pole->id,
                        'status' => 'error',
                        'message' => $e->getMessage(),
                    ];
                }
            }

            return response()->json([
                'message' => 'Pole data sync process completed for ' . $validated['panchayat'] . '.',
                'result' => $responses,
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
}
