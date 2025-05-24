<?php

namespace App\Http\Controllers;

use App\Helpers\ExcelHelper;
use App\Models\Pole;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\Site;
use App\Models\StreetlightTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Response;

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
            // Log::info('Target added' , $request->all());
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
    public function show(Request $request, string $id)
    {
        //
        Log::info("Project type " . $request->project_type);
        if ($request->project_type != 1) {
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
        } else {
            $streetlightTask = StreetlightTask::findOrFail($id);
            $manager = $streetlightTask->manager;
            $vendor = $streetlightTask->vendor;
            $engineer = $streetlightTask->engineer;
            $streetlight = $streetlightTask->site;
            $surveyedPoles = Pole::where('task_id', $id)
                ->where('isSurveyDone', true)
                ->get();

            $installedPoles = Pole::where('task_id', $id)
                ->where('isInstallationDone', true)
                ->get();
            return view('tasks.show_streetlight', compact('streetlightTask', 'manager', 'engineer', 'vendor', 'streetlight', 'surveyedPoles', 'installedPoles'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id, Request $request)
    {
        // Target in streetlight project is being edited
        Log::info("Edit target", $request->all() ,  $id);
        $projectId = request()->query('project_id');
        if($projectId==11){
            $tasks = StreetlightTask::with(['site', 'engineer', 'vendor']) // eager load relationships
                ->findOrFail($id);
            // Get all engineers and vendors from the users table based on role
            $engineers = User::where('role', 1)->get();
            $vendors = User::where('role', 3)->get();

    
        return view('tasks.edit', compact('tasks', 'projectId', 'engineers', 'vendors'));
        }
        
    }

    public function editrooftop(string $id){
       
        
        //code...
           $taskId = (int) $id;
        
        $task = Task::where('id', $taskId)
                ->first();
        $engineers = User::where('role', 1)->get();
        $sites = Site::all();
        Log::error($task);
            return view('tasks.editRooftop', compact('task', 'engineers', 'sites'));
       
     }

     /**
 * Update the specified rooftop task in storage.
 *
 * @param  \Illuminate\Http\Request  $request
 * @param  string  $id
 * @return \Illuminate\Http\Response
 */
public function updateRooftop(Request $request, string $id)
{
    try {
        // Convert string ID to integer
        $taskId = (int) $id;
        
        // Validate the request data
        $validatedData = $request->validate([
            'site_id' => 'required|exists:sites,id',
            'activity' => 'required|string',
            'engineer_id' => 'required|exists:users,id',
        ]);
        
        // Find the task
        $task = Task::findOrFail($taskId);
        
        // Update the task with validated data
        $task->update($validatedData);
        
        // Redirect with success message
        return redirect()->route('projects.show', $task->project_id=10)
                         ->with('success', 'Task updated successfully');
                         
    } catch (\Exception $e) {
        // Log the error
        \Log::error('Error updating rooftop task: ' . $e->getMessage());
        
        // Redirect with error message
        return redirect()->back()
                         ->withInput()
                         ->with('error', 'Failed to update task: ' . $e->getMessage());
    }
}


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        Log::info("Update target", $request->all());
        $projectId = $request->input('project_id');
    
        if ($projectId == 11) {
            $request->validate([
                'engineer_id' => 'required|exists:users,id',
                'vendor_id' => 'required|exists:users,id',
                'billed' => 'required|boolean',
            ]);
    
            try {
                $task = StreetlightTask::findOrFail($id);
    
                $task->engineer_id = $request->engineer_id;
                $task->vendor_id = $request->vendor_id;
                $task->billed = $request->billed;
    
                $task->save();
    
                return redirect()->route('projects.show', $projectId)
                    ->with('success', 'Task updated successfully.');
            } catch (\Exception $e) {
                Log::error('Error updating task: ' . $e->getMessage());
                return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
            }
        }
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