<?php

namespace App\Http\Controllers;

use App\Contracts\TaskServiceInterface;
use App\Enums\TaskStatus;
use App\Helpers\ExcelHelper;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Models\Project;
use Illuminate\Http\Request;

class TasksController extends Controller
{
    public function __construct(
        protected TaskServiceInterface $taskService
    ) {
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $projectId = $this->getSelectedProject($request, $user);

        // If no project is found, redirect to projects page or show error
        if (!$projectId) {
            return redirect()->route('projects.index')->with('error', 'No project assigned. Please select a project.');
        }

        $tasks = $this->taskService->getTasksByProject($projectId);
        $project = Project::findOrFail($projectId);
        $topEngineers = [];
        $topVendors = [];

        return view('tasks.index', compact('tasks', 'topEngineers', 'topVendors', 'project'));
    }

    /**
     * Get selected project ID.
     */
    private function getSelectedProject(Request $request, $user)
    {
        if ($request->has('project_id')) {
            return (int) $request->project_id;
        }

        if ($user->project_id) {
            return (int) $user->project_id;
        }

        $project = Project::when($user->role !== \App\Enums\UserRole::ADMIN->value, function ($query) use ($user) {
            $query->whereHas('users', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            });
        })->first();

        return $project ? $project->id : null;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $projectId = $request->input('project_id');
        
        if (!$projectId) {
            $user = auth()->user();
            $projectId = $this->getSelectedProject($request, $user);
        }

        if (!$projectId) {
            return redirect()->route('projects.index')
                ->with('error', 'Please select a project first.');
        }

        $project = Project::findOrFail($projectId);
        $engineers = $this->taskService->getAvailableEngineers($projectId);
        $vendors = $this->taskService->getAvailableVendors($projectId);
        $sites = $this->taskService->getAvailableSites($projectId);

        return view('tasks.create', compact('project', 'engineers', 'vendors', 'sites'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTaskRequest $request)
    {
        $project = Project::findOrFail($request->project_id);

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
        $projectType = $request->has('project_type') ? (int) $request->project_type : null;
        $taskData = $this->taskService->getTaskDetails($id, $projectType);

        if ($projectType != 1) {
            return view('tasks.show', ['tasks' => $taskData]);
        } else {
            $streetlightTask = $taskData['task'] ?? null;

            if (!$streetlightTask) {
                return redirect()->back()->with('error', 'Task not found.');
            }

            $vendor = $streetlightTask->vendor ?? null;
            $manager = $streetlightTask->manager ?? null;
            $engineer = $streetlightTask->engineer ?? null;
            $streetlight = $streetlightTask->site ?? null;
            $poles = $taskData['poles'] ?? collect();

            $installedPoles = $poles->where('isInstallationDone', 1);
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

        if ($projectId) {
            $project = \App\Models\Project::find($projectId);
            if ($project && $project->project_type == 1) {
                $task = \App\Models\StreetlightTask::with(['engineer', 'vendor', 'manager', 'site'])
                    ->findOrFail($id);
            } else {
                $task = $this->taskService->findById($id);
            }
        } else {
            $task = \App\Models\StreetlightTask::find($id);
            if (!$task) {
                $task = $this->taskService->findById($id);
            }
        }

        $engineers = $this->taskService->getAvailableEngineers($projectId);
        $vendors = $this->taskService->getAvailableVendors($projectId);

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

            if ($projectId) {
                $project = \App\Models\Project::find($projectId);
                if ($project && $project->project_type == 1) {
                    $task = \App\Models\StreetlightTask::findOrFail($id);

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

                    if ($request->has('status')) {
                        $statusValue = $request->input('status');
                        // Validate status using TaskStatus enum
                        try {
                            $status = TaskStatus::from($statusValue);
                            $validData['status'] = $status->value;
                        } catch (\ValueError $e) {
                            // Invalid status value, skip it
                            \Log::warning('Invalid task status provided', [
                                'status' => $statusValue,
                                'task_id' => $id
                            ]);
                        }
                    }

                    $task->update($validData);

                    return redirect()->route('projects.show', $projectId)
                        ->with('success', 'Task updated successfully.');
                }
            }

            $task = $this->taskService->updateTask($id, $request->validated());

            return redirect()->route('projects.show', $request->project_id)
                ->with('success', 'Task updated successfully.');
        } catch (\Exception $e) {
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

    public function exportToExcel(Request $request)
    {
        $user = auth()->user();
        $projectId = $this->getSelectedProject($request, $user);

        if (!$projectId) {
            return redirect()->route('projects.index')
                ->with('error', 'No project assigned. Please select a project.');
        }

        $project = Project::findOrFail($projectId);
        $tasks = $this->taskService->getTasksByProject($projectId);

        if ($project->project_type == 1) {
            // Streetlight tasks export
            $exportData = $tasks->map(function ($task) {
                return [
                    'ID' => $task->id,
                    'Panchayat' => $task->site->panchayat ?? 'N/A',
                    'Block' => $task->site->block ?? 'N/A',
                    'District' => $task->site->district ?? 'N/A',
                    'Engineer' => $task->engineer ? ($task->engineer->firstName . ' ' . $task->engineer->lastName) : 'N/A',
                    'Vendor' => $task->vendor ? $task->vendor->name : 'N/A',
                    'Manager' => $task->manager ? ($task->manager->firstName . ' ' . $task->manager->lastName) : 'N/A',
                    'Status' => $task->status ?? 'N/A',
                    'Start Date' => $task->start_date ? $task->start_date->format('Y-m-d') : 'N/A',
                    'End Date' => $task->end_date ? $task->end_date->format('Y-m-d') : 'N/A',
                    'Billed' => $task->billed ? 'Yes' : 'No',
                    'Description' => $task->description ?? 'N/A',
                ];
            })->toArray();
        } else {
            // Rooftop tasks export
            $exportData = $tasks->map(function ($task) {
                return [
                    'ID' => $task->id,
                    'Task Name' => $task->task_name ?? 'N/A',
                    'Activity' => $task->activity ?? 'N/A',
                    'Site Name' => $task->site->site_name ?? 'N/A',
                    'Engineer' => $task->engineer ? ($task->engineer->firstName . ' ' . $task->engineer->lastName) : 'N/A',
                    'Vendor' => $task->vendor ? $task->vendor->name : 'N/A',
                    'Manager' => $task->manager ? ($task->manager->firstName . ' ' . $task->manager->lastName) : 'N/A',
                    'Status' => $task->status ?? 'N/A',
                    'Start Date' => $task->start_date ? $task->start_date->format('Y-m-d') : 'N/A',
                    'End Date' => $task->end_date ? $task->end_date->format('Y-m-d') : 'N/A',
                    'Approved By' => $task->approved_by ?? 'N/A',
                    'Description' => $task->description ?? 'N/A',
                ];
            })->toArray();
        }

        $filename = 'tasks_' . $project->project_name . '_' . date('Y-m-d') . '.xlsx';
        return ExcelHelper::exportToExcel($exportData, $filename);
    }
}
