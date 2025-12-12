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
    ) {
    }
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
        // #region agent log
        file_put_contents('/Applications/XAMPP/xamppfiles/htdocs/laravel_crm/.cursor/debug.log', json_encode(['sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'A', 'location' => 'TasksController.php:45', 'message' => 'store method entry', 'data' => ['project_id' => $request->project_id, 'sites_count' => is_array($request->sites) ? count($request->sites) : 0, 'sites' => $request->sites, 'validated_keys' => array_keys($request->validated())], 'timestamp' => time() * 1000]) . "\n", FILE_APPEND);
        // #endregion

        $project = Project::findOrFail($request->project_id);

        // #region agent log
        file_put_contents('/Applications/XAMPP/xamppfiles/htdocs/laravel_crm/.cursor/debug.log', json_encode(['sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'A', 'location' => 'TasksController.php:52', 'message' => 'project found', 'data' => ['project_id' => $project->id, 'project_type' => $project->project_type], 'timestamp' => time() * 1000]) . "\n", FILE_APPEND);
        // #endregion

        // Use service to create tasks
        // #region agent log
        file_put_contents('/Applications/XAMPP/xamppfiles/htdocs/laravel_crm/.cursor/debug.log', json_encode(['sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'A', 'location' => 'TasksController.php:56', 'message' => 'before createBulkTasks call', 'data' => ['method_exists' => method_exists($this->taskService, 'createBulkTasks')], 'timestamp' => time() * 1000]) . "\n", FILE_APPEND);
        // #endregion

        $this->taskService->createBulkTasks(
            $request->project_id,
            $request->sites,
            $request->validated(),
            auth()->id()
        );

        // #region agent log
        file_put_contents('/Applications/XAMPP/xamppfiles/htdocs/laravel_crm/.cursor/debug.log', json_encode(['sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'A', 'location' => 'TasksController.php:65', 'message' => 'after createBulkTasks call', 'data' => [], 'timestamp' => time() * 1000]) . "\n", FILE_APPEND);
        // #endregion

        return redirect()->route('projects.show', $request->project_id)
            ->with('success', 'Targets successfully added.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        // Convert project_type to integer for comparison (query params come as strings)
        $projectType = $request->has('project_type') ? (int) $request->project_type : null;
        $taskData = $this->taskService->getTaskDetails($id, $projectType);

        if ($projectType != 1) {
            return view('tasks.show', ['tasks' => $taskData]);
        } else {
            // Extract variables for streetlight view
            $streetlightTask = $taskData['task'] ?? null;

            if (!$streetlightTask) {
                return redirect()->back()->with('error', 'Task not found.');
            }

            // Extract related models for the view
            // Use null-safe access to avoid errors if relationships are not loaded
            $vendor = $streetlightTask->vendor ?? null;
            $manager = $streetlightTask->manager ?? null;
            $engineer = $streetlightTask->engineer ?? null;
            $streetlight = $streetlightTask->site ?? null;
            $poles = $taskData['poles'] ?? collect();

            // Filter poles for installed and surveyed
            // Installed poles: isInstallationDone = 1
            $installedPoles = $poles->where('isInstallationDone', 1);

            // Surveyed poles: isSurveyDone = 1 and isInstallationDone = 0
            $surveyedPoles = $poles->where('isSurveyDone', 1)
                ->where('isInstallationDone', 0);

            return view('tasks.show_streetlight', [
                'streetlightTask' => $streetlightTask,
                'vendor' => $vendor,
                'manager' => $manager,
                'engineer' => $engineer,
                'streetlight' => $streetlight,
                'poles' => $poles,
                'installedPoles' => $installedPoles,
                'surveyedPoles' => $surveyedPoles,
            ]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id, Request $request)
    {
        $projectId = request()->query('project_id');

        // Determine project type to use correct model
        if ($projectId) {
            $project = \App\Models\Project::find($projectId);
            if ($project && $project->project_type == 1) {
                // Streetlight project - use StreetlightTask model
                $task = \App\Models\StreetlightTask::with(['engineer', 'vendor', 'manager', 'site'])
                    ->findOrFail($id);
            } else {
                // Rooftop project - use Task model via service
                $task = $this->taskService->findById($id);
            }
        } else {
            // Try to find in both models
            $task = \App\Models\StreetlightTask::find($id);
            if (!$task) {
                $task = $this->taskService->findById($id);
            }
        }

        $engineers = $this->taskService->getAvailableEngineers($projectId);
        $vendors = $this->taskService->getAvailableVendors($projectId);

        // Pass as 'tasks' to match the view variable name
        return view('tasks.edit', ['tasks' => $task, 'projectId' => $projectId, 'engineers' => $engineers, 'vendors' => $vendors]);
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
            $projectId = $request->input('project_id');

            // Determine project type to use correct model
            if ($projectId) {
                $project = \App\Models\Project::find($projectId);
                if ($project && $project->project_type == 1) {
                    // Streetlight project - use StreetlightTask model
                    $task = \App\Models\StreetlightTask::findOrFail($id);

                    // Filter only valid fields for StreetlightTask
                    $validData = $request->only([
                        'engineer_id',
                        'vendor_id',
                        'manager_id',
                        'start_date',
                        'end_date',
                        'description',
                        'materials_consumed',
                        'approved_by',
                        'billed',
                    ]);

                    // Handle status separately - streetlight_tasks only allows 'Pending' or 'Completed'
                    if ($request->has('status')) {
                        $status = $request->input('status');
                        // Only allow 'Pending' or 'Completed' for streetlight tasks
                        if (in_array($status, ['Pending', 'Completed'])) {
                            $validData['status'] = $status;
                        }
                    }

                    $task->update($validData);

                    return redirect()->route('projects.show', $projectId)
                        ->with('success', 'Task updated successfully.');
                }
            }

            // Default to regular Task model (rooftop projects)
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
