<?php

namespace App\Http\Controllers;

use App\Helpers\ExcelHelper;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\Site;
use App\Models\StreetlightTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TasksController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $tasks = Task::all();
        $today = now()->toDateString();
        // Query the top 5 engineers based on completed tasks today
        $topEngineers = Task::whereDate('end_date', $today)
            ->where('status', 'Completed') // Only count completed tasks
            ->groupBy('engineer_id')
            ->selectRaw('engineer_id, COUNT(*) as task_count')
            ->orderByDesc('task_count')
            ->with('user') // Load engineer details
            ->limit(5)
            ->get();
        // Query the top 5 vendors based on completed tasks today
        Log::info($topEngineers);
        $topVendors = Task::whereDate('end_date', $today)
            ->where('status', 'Completed') // Only count completed tasks
            ->groupBy('vendor_id')
            ->selectRaw('vendor_id, COUNT(*) as task_count')
            ->orderByDesc('task_count')
            ->with('vendor') // Load vendor details
            ->limit(5)
            ->get();
        return view('tasks.index', compact('tasks', 'topEngineers', 'topVendors'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'sites'       => 'required|array',
            'engineer_id' => 'required|exists:users,id',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after_or_equal:start_date',
        ]);
        $project = Project::findOrFail($request->project_id);


        if ($project->project_type == 1) {
            // Store in streetlight_tasks table
            foreach ($request->sites as $siteId) {
                StreetlightTask::create([
                    'project_id' => $request->project_id,
                    'site_id'     => $siteId,
                    'vendor_id'    => $request->vendor_id,
                    'engineer_id' => $request->engineer_id,
                    'start_date'  => $request->start_date,
                    'end_date'    => $request->end_date,
                    'manager_id' => auth()->id(), // Automatically assign the logged-in Project Manager
                ]);
            }
        } else {
            foreach ($request->sites as $siteId) {
                Task::create([
                    'project_id'  => $request->project_id,
                    'site_id'     => $siteId,
                    'activity'    => $request->activity,
                    'engineer_id' => $request->engineer_id,
                    'start_date'  => $request->start_date,
                    'end_date'    => $request->end_date,
                    'manager_id' => auth()->id(), // Automatically assign the logged-in Project Manager
                ]);
            }
        }
        return redirect()->route('projects.show', $request->project_id)
            ->with('success', 'Targets successfully added.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $task        = Task::findOrFail($id);
        $engineer_id = $task->engineer_id;
        $vendor = $task->vendor;
        $engineer    = User::findOrFail($engineer_id);
        $site =         Site::findOrFail($task->site_id);
        $images      = json_decode($task->image, true); // Ensure it's an array
        $fullUrls    = [];
        if (is_array($images)) {
            foreach ($images as $image) {
                $fullUrls[] = Storage::disk('s3')->url($image);
            }
        }

        // Add the full URLs to the image key
        $task->image = $fullUrls;

        return view('tasks.show', compact('task', 'engineer', 'vendor', 'site'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        try {
            $task = Task::findOrFail($id);
            $task->delete();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function exportToExcel()
    {
        $data = [
            (object) ['Name' => 'John Doe', 'Email' => 'john@example.com', 'Age' => 30],
            (object) ['Name' => 'Jane Smith', 'Email' => 'jane@example.com', 'Age' => 28],
        ];
        return ExcelHelper::exportToExcel($data, 'tasks.xlsx');
    }
}
