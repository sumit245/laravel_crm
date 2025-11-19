<?php

namespace App\Http\Controllers;

use App\Contracts\TaskServiceInterface;
use App\Helpers\ExcelHelper;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TasksController extends Controller
{
    public function __construct(
        protected TaskServiceInterface $taskService
    ) {}
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $projectId = auth()->user()->project_id;
        $tasks = $this->taskService->getTasksByProject($projectId);
        
        // For now, return empty arrays for top performers
        // This can be implemented later in the service
        $topEngineers = [];
        $topVendors = [];
        
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
    public function store(StoreTaskRequest $request)
    {
        $project = Project::findOrFail($request->project_id);
        
        // Use service to create tasks
        $this->taskService->createBulkTasks(
            $request->project_id,
            $request->sites,
            $request->validated(),
            auth()->id()
        );
        
        return redirect()->route('projects.show', $request->project_id)
            ->with('success', 'Targets successfully added.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $taskData = $this->taskService->getTaskDetails($id, $request->project_type);
        
        if ($request->project_type != 1) {
            return view('tasks.show', ['tasks' => $taskData]);
        } else {
            return view('tasks.show_streetlight', $taskData);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id, Request $request)
    {
        $projectId = request()->query('project_id');
        $task = $this->taskService->findById($id);
        $engineers = $this->taskService->getAvailableEngineers($projectId);
        $vendors = $this->taskService->getAvailableVendors($projectId);
        
        return view('tasks.edit', compact('task', 'projectId', 'engineers', 'vendors'));
    }

    public function editrooftop(string $id)
    {
        $task = $this->taskService->findById($id);
        $engineers = $this->taskService->getAvailableEngineers($task->project_id);
        $sites = $this->taskService->getAvailableSites($task->project_id);
        
        return view('tasks.editRooftop', compact('task', 'engineers', 'sites'));
    }

    /**
     * Update the specified rooftop task in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function updateRooftop(UpdateTaskRequest $request, string $id)
    {
        try {
            $task = $this->taskService->updateTask($id, $request->validated());
            
            return redirect()->route('projects.show', $task->project_id)
                ->with('success', 'Task updated successfully');
        } catch (\Exception $e) {
            Log::error('Error updating rooftop task: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update task: ' . $e->getMessage());
        }
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaskRequest $request, string $id)
    {
        try {
            $task = $this->taskService->updateTask($id, $request->validated());
            
            return redirect()->route('projects.show', $request->project_id)
                ->with('success', 'Task updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating task: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $this->taskService->deleteTask($id);
            
            return redirect()->back()
                ->with('success', 'Task Deleted successfully.');
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
