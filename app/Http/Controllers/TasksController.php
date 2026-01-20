<?php

namespace App\Http\Controllers;

use App\Contracts\TaskServiceInterface;
use App\Enums\TaskStatus;
use App\Helpers\ExcelHelper;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Services\Logging\ActivityLogger;

class TasksController extends Controller
{
    public function __construct(
        protected TaskServiceInterface $taskService,
        protected ActivityLogger $activityLogger
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

        $this->activityLogger->log('task', 'created', $project, [
            'description' => 'Targets created.',
            'extra' => [
                'site_ids' => $request->sites,
            ],
        ]);

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
        $project = null;
        $task = null;
        $wardInfo = null;
        $inventoryStatus = null;

        if ($projectId) {
            $project = \App\Models\Project::find($projectId);
            if ($project && $project->project_type == 1) {
                $task = \App\Models\StreetlightTask::with(['engineer', 'vendor', 'manager', 'site', 'poles'])
                    ->findOrFail($id);
                
                // Get ward information for conflict checking
                if ($task->site) {
                    $wardInfo = [
                        'site_id' => $task->site->id,
                        'ward' => $task->site->ward,
                        'ward_type' => $task->site->ward_type,
                        'district' => $task->site->district,
                        'panchayat' => $task->site->panchayat,
                    ];
                }
                
                // Get inventory status for current vendor
                if ($task->vendor_id) {
                    $pendingInventory = \App\Models\InventoryDispatch::where('vendor_id', $task->vendor_id)
                        ->where('project_id', $projectId)
                        ->where('isDispatched', true)
                        ->where('is_consumed', false)
                        ->count();
                    
                    $inventoryStatus = [
                        'pending_count' => $pendingInventory,
                        'has_pending' => $pendingInventory > 0,
                    ];
                }
            } else {
                $task = $this->taskService->findById($id);
            }
        } else {
            $task = \App\Models\StreetlightTask::find($id);
            if (!$task) {
                $task = $this->taskService->findById($id);
            }
            if ($task && $task->project_id) {
                $project = \App\Models\Project::find($task->project_id);
            }
        }

        $engineers = $this->taskService->getAvailableEngineers($projectId ?? $task->project_id ?? null);
        $vendors = $this->taskService->getAvailableVendors($projectId ?? $task->project_id ?? null);
        $managers = $this->taskService->getAvailableManagers($projectId ?? $task->project_id ?? null);

        return view('tasks.edit', [
            'tasks' => $task, 
            'projectId' => $projectId ?? $task->project_id ?? null,
            'project' => $project,
            'engineers' => $engineers, 
            'vendors' => $vendors,
            'managers' => $managers,
            'wardInfo' => $wardInfo,
            'inventoryStatus' => $inventoryStatus,
        ]);
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

                    // Prevent reassignment of completed tasks (preserve historical data)
                    if ($task->status === 'Completed') {
                        // Only allow status and other non-assignment fields to be updated
                        $validData = $request->only([
                            'start_date',
                            'end_date',
                            'description',
                            'materials_consumed',
                            'approved_by',
                            'billed',
                            'extension_reason',
                        ]);
                        
                        // Log date extension for completed tasks
                        if ($request->has('end_date') && $task->end_date && $request->end_date != $task->end_date) {
                            \Log::info('Date extension for completed task', [
                                'task_id' => $id,
                                'original_end_date' => $task->end_date,
                                'new_end_date' => $request->end_date,
                                'extension_reason' => $request->input('extension_reason'),
                                'extended_by' => auth()->id()
                            ]);
                        }

                        // Log attempt to reassign completed task
                        if ($request->has('engineer_id') && $request->engineer_id != $task->engineer_id) {
                            \Log::info('Attempted to reassign engineer on completed task', [
                                'task_id' => $id,
                                'original_engineer_id' => $task->engineer_id,
                                'requested_engineer_id' => $request->engineer_id
                            ]);
                        }
                        if ($request->has('vendor_id') && $request->vendor_id != $task->vendor_id) {
                            \Log::info('Attempted to reassign vendor on completed task', [
                                'task_id' => $id,
                                'original_vendor_id' => $task->vendor_id,
                                'requested_vendor_id' => $request->vendor_id
                            ]);
                        }
                        if ($request->has('manager_id') && $request->manager_id != $task->manager_id) {
                            \Log::info('Attempted to reassign manager on completed task', [
                                'task_id' => $id,
                                'original_manager_id' => $task->manager_id,
                                'requested_manager_id' => $request->manager_id
                            ]);
                        }
                    } else {
                        // For pending/in-progress tasks, allow all updates
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
                            'extension_reason',
                        ]);

                        // Track date extensions for audit trail
                        if ($request->has('end_date') && $task->end_date && $request->end_date != $task->end_date) {
                            \Log::info('Target date extended', [
                                'task_id' => $id,
                                'project_id' => $projectId,
                                'original_end_date' => $task->end_date,
                                'new_end_date' => $request->end_date,
                                'extension_reason' => $request->input('extension_reason'),
                                'extended_by' => auth()->id()
                            ]);
                            
                            // Add extension info to description if provided
                            if ($request->has('extension_reason') && !empty($request->extension_reason)) {
                                $extensionNote = "\n\n[Date Extended on " . now()->format('Y-m-d H:i:s') . "]\n";
                                $extensionNote .= "Reason: " . $request->extension_reason . "\n";
                                $extensionNote .= "Extended from: " . \Carbon\Carbon::parse($task->end_date)->format('Y-m-d') . " to: " . \Carbon\Carbon::parse($request->end_date)->format('Y-m-d');
                                $validData['description'] = ($task->description ?? '') . $extensionNote;
                            }
                        }

                        // Track reassignments for audit trail
                        $reassignmentLog = [];
                        if ($request->has('engineer_id') && $request->engineer_id != $task->engineer_id) {
                            $reassignmentLog['engineer'] = [
                                'old' => $task->engineer_id,
                                'new' => $request->engineer_id,
                                'reassigned_at' => now()
                            ];
                        }
                        if ($request->has('vendor_id') && $request->vendor_id != $task->vendor_id) {
                            $reassignmentLog['vendor'] = [
                                'old' => $task->vendor_id,
                                'new' => $request->vendor_id,
                                'reassigned_at' => now()
                            ];
                        }
                        if ($request->has('manager_id') && $request->manager_id != $task->manager_id) {
                            $reassignmentLog['manager'] = [
                                'old' => $task->manager_id,
                                'new' => $request->manager_id,
                                'reassigned_at' => now()
                            ];
                        }

                        // Log reassignments
                        if (!empty($reassignmentLog)) {
                            \Log::info('Task stakeholder reassignment', [
                                'task_id' => $id,
                                'project_id' => $projectId,
                                'reassignments' => $reassignmentLog,
                                'reason' => $request->input('reassignment_reason'),
                                'reassigned_by' => auth()->id()
                            ]);
                        }
                    }

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

            // Handle rooftop tasks (Task model)
            $task = $this->taskService->findById($id);
            
            if ($task) {
                // Prevent reassignment of completed tasks
                if ($task->status === 'Completed') {
                    // Only allow non-assignment fields
                    $validData = $request->only([
                        'start_date',
                        'end_date',
                        'description',
                        'materials_consumed',
                        'approved_by',
                    ]);

                    // Log attempts to reassign
                    if ($request->has('engineer_id') && $request->engineer_id != $task->engineer_id) {
                        \Log::info('Attempted to reassign engineer on completed task', [
                            'task_id' => $id,
                            'original_engineer_id' => $task->engineer_id,
                            'requested_engineer_id' => $request->engineer_id
                        ]);
                    }
                    if ($request->has('vendor_id') && $request->vendor_id != $task->vendor_id) {
                        \Log::info('Attempted to reassign vendor on completed task', [
                            'task_id' => $id,
                            'original_vendor_id' => $task->vendor_id,
                            'requested_vendor_id' => $request->vendor_id
                        ]);
                    }
                    if ($request->has('manager_id') && $request->manager_id != $task->manager_id) {
                        \Log::info('Attempted to reassign manager on completed task', [
                            'task_id' => $id,
                            'original_manager_id' => $task->manager_id,
                            'requested_manager_id' => $request->manager_id
                        ]);
                    }

                    $task->update($validData);
                } else {
                    // For pending/in-progress tasks, allow all updates
                    $task = $this->taskService->updateTask($id, $request->validated());
                }
            } else {
                $task = $this->taskService->updateTask($id, $request->validated());
            }

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

    /**
     * Check for ward conflicts when reassigning vendor
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkWardConflict(Request $request)
    {
        try {
            $validated = $request->validate([
                'vendor_id' => 'required|exists:users,id',
                'project_id' => 'required|exists:projects,id',
                'site_id' => 'nullable|exists:streetlights,id',
                'district' => 'nullable|string',
                'panchayat' => 'nullable|string',
                'ward' => 'nullable|string',
            ]);

            $vendorId = $validated['vendor_id'];
            $projectId = $validated['project_id'];
            $ward = $validated['ward'] ?? null;
            $district = $validated['district'] ?? null;
            $panchayat = $validated['panchayat'] ?? null;

            // If no ward information provided, no conflict check possible
            if (!$ward && !$district && !$panchayat) {
                return response()->json([
                    'has_conflict' => false,
                    'message' => 'Insufficient ward information to check conflicts'
                ]);
            }

            // Get wards for the task (from site or pole data)
            $taskWards = [];
            if ($ward) {
                // Parse comma-separated wards
                $taskWards = array_map('trim', explode(',', $ward));
            }

            // Check if vendor has completed poles in the same wards
            $hasConflict = false;
            $conflictDetails = [
                'completed_poles_count' => 0,
                'conflicting_wards' => [],
            ];

            if (!empty($taskWards)) {
                // Get all poles for this vendor that are completed
                $completedPoles = \App\Models\Pole::where('vendor_id', $vendorId)
                    ->where('isInstallationDone', 1)
                    ->whereHas('task', function ($query) use ($projectId, $district, $panchayat) {
                        $query->where('project_id', $projectId);
                        if ($district) {
                            $query->whereHas('site', function ($q) use ($district) {
                                $q->where('district', $district);
                            });
                        }
                        if ($panchayat) {
                            $query->whereHas('site', function ($q) use ($panchayat) {
                                $q->where('panchayat', $panchayat);
                            });
                        }
                    })
                    ->get();

                // Check each completed pole's ward against task wards
                foreach ($completedPoles as $pole) {
                    $poleWard = $pole->ward_name ?? null;
                    
                    // Check if pole ward matches any task ward
                    if ($poleWard && in_array($poleWard, $taskWards)) {
                        $hasConflict = true;
                        $conflictDetails['completed_poles_count']++;
                        if (!in_array($poleWard, $conflictDetails['conflicting_wards'])) {
                            $conflictDetails['conflicting_wards'][] = $poleWard;
                        }
                    }
                }
            }

            return response()->json([
                'has_conflict' => $hasConflict,
                'conflict_details' => $conflictDetails,
                'message' => $hasConflict 
                    ? 'Vendor has completed installations in the same wards'
                    : 'No ward conflicts found'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error checking ward conflict', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'has_conflict' => false,
                'error' => 'Error checking ward conflicts: ' . $e->getMessage()
            ], 500);
        }
    }
}
