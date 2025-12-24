<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Inventory;
use App\Models\InventoryDispatch;
use App\Models\InventroyStreetLightModel;
use App\Models\Pole;
use App\Models\Project;
use App\Models\State;
use App\Models\Streetlight;
use App\Models\StreetlightTask;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Helpers\ExcelHelper;

class ProjectsController extends Controller
{
    /**
     * @var string[]
     */
    protected array $sortFields = ['start_date', 'end_date', 'rate', 'project_capacity'];

    /**
     * UsersController constructor.
     *
     * @param User $user
     */
    public function __construct(public Project $project)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $projects = Project::all();
        return view('projects.index', compact('projects'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Project::class);
        $states = State::all();
        return view('projects.create', compact('states'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Project::class);
        $validated = $request->validate([
            'project_type' => 'required|in:0,1',
            'project_name' => 'required|string',
            'project_in_state' => 'string',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'work_order_number' => 'required|string|unique:projects',
            'rate' => 'nullable|numeric',
            'project_capacity' => 'nullable|string',
            'total' => 'numeric',
            'description' => 'string',
            'agreement_number' => 'nullable|string|required_if:project_type,1',
            'agreement_date' => 'nullable|date|required_if:project_type,1',
        ]);

        try {
            $project = Project::create($validated);

            return redirect()->route('projects.show', $project->id)
                ->with('success', 'Project created successfully.');
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            return redirect()->back()
                ->withErrors(['error' => $errorMessage])
                ->withInput();
        }
    }

    /**
     * Display the specified Project
     */
    public function show(string $id)
    {
        $project = Project::with(['stores', 'sites.districtRelation', 'sites.stateRelation'])->findOrFail($id);
        $user = auth()->user();

        $isAdmin = $user->role === UserRole::ADMIN->value;
        $isProjectManager = $user->role === UserRole::PROJECT_MANAGER->value;

        // For Project Managers, ensure they can only see projects they're assigned to
        if ($isProjectManager) {
            $isAssigned = DB::table('project_user')
                ->where('project_id', $project->id)
                ->where('user_id', $user->id)
                ->exists();

            if (!$isAssigned) {
                abort(403, 'You do not have access to this project.');
            }
        }

        $users = User::whereNotIn('role', [UserRole::ADMIN->value, UserRole::VENDOR->value])->get();

        // Fetch inventory items based on project type
        $inventoryModel = ($project->project_type == 1) ? InventroyStreetLightModel::class : Inventory::class;

        if ($project->project_type == 1) {
            $inventoryItems = $inventoryModel::where('project_id', $project->id)
                ->select(
                    'item_code',
                    'item',
                    DB::raw('SUM(quantity) as total_quantity'),
                    DB::raw('MAX(rate) as rate'),
                    DB::raw('SUM(quantity * rate) as total_value'),
                    DB::raw('MAX(make) as make'),
                    DB::raw('MAX(model) as model')
                )
                ->groupBy('item_code', 'item')
                ->get();
        } else {
            $inventoryItems = $inventoryModel::where('project_id', $project->id)->get();
        }

        $initialStockValue = 0;
        $dispatchedStockValue = 0;
        $inStoreStockValue = 0;
        if ($project->project_type == 1) {
            $initialStockValue = $inventoryModel::where('project_id', $project->id)->sum(DB::raw('total_value'));
            $dispatchedStockValue = InventoryDispatch::where('project_id', $project->id)
                ->sum('total_value');
            $inStoreStockValue = (float) $initialStockValue - $dispatchedStockValue;
        }

        $engineers = User::where('role', UserRole::SITE_ENGINEER->value)->get();
        $vendors = User::where('role', UserRole::VENDOR->value)->get();
        $state = State::where('id', $project->project_in_state)->get();

        // Get assigned staff IDs from pivot table (excluding vendors - they're in Vendor Management tab)
        $assignedStaffIds = DB::table('project_user')
            ->join('users', 'project_user.user_id', '=', 'users.id')
            ->where('project_user.project_id', $project->id)
            ->whereNotIn('users.role', [UserRole::VENDOR->value])
            ->pluck('project_user.user_id')
            ->toArray();

        // Get assigned staff with role from pivot table (excluding vendors)
        $assignedStaffQuery = User::whereIn('id', $assignedStaffIds)
            ->whereNotIn('role', [UserRole::VENDOR->value])
            ->when($isProjectManager, function ($q) use ($user) {
                $q->where('manager_id', $user->id);
            });

        $assignedStaff = $assignedStaffQuery->get()->map(function ($staff) use ($project) {
            $pivot = DB::table('project_user')
                ->where('project_id', $project->id)
                ->where('user_id', $staff->id)
                ->first();
            $staff->pivot_role = $pivot->role ?? $staff->role;
            return $staff;
        });

        // Group assigned staff by role (excluding vendors - they're in Vendor Management tab)
        $assignedStaffByRole = $assignedStaff->groupBy('pivot_role')
            ->filter(function ($group, $role) {
                // Exclude vendors from staff management
                return (int) $role !== UserRole::VENDOR->value;
            })
            ->map(function ($group, $role) {
                return $group->map(function ($staff) {
                    return [
                        'id' => $staff->id,
                        'name' => trim($staff->firstName . ' ' . $staff->lastName),
                        'firstName' => $staff->firstName,
                        'lastName' => $staff->lastName,
                        'role' => $staff->pivot_role ?? $staff->role,
                        'manager_id' => $staff->manager_id
                    ];
                });
            });

        // Get available staff (not already assigned)
        // Exclude vendors - they are managed in Vendor Management tab
        // For Admin: Show all users with relevant roles
        // For PM: Show only users with manager_id = current_user->id
        $availableStaffQuery = User::whereIn('role', [
            UserRole::SITE_ENGINEER->value,
            UserRole::PROJECT_MANAGER->value,
            UserRole::STORE_INCHARGE->value,
            UserRole::COORDINATOR->value
        ])
            ->whereNotIn('id', $assignedStaffIds)
            ->when($isProjectManager, function ($q) use ($user) {
                $q->where('manager_id', $user->id);
            });

        $availableStaff = $availableStaffQuery->get()->map(function ($staff) {
            return [
                'id' => $staff->id,
                'name' => trim($staff->firstName . ' ' . $staff->lastName),
                'firstName' => $staff->firstName,
                'lastName' => $staff->lastName,
                'role' => $staff->role,
                'manager_id' => $staff->manager_id
            ];
        });

        // Group available staff by role
        $availableStaffByRole = $availableStaff->groupBy('role');

        // Legacy variables for backward compatibility
        $assignedEngineers = $assignedStaff->filter(function ($staff) {
            $role = $staff->pivot_role ?? $staff->role;
            return in_array($role, [UserRole::SITE_ENGINEER->value, UserRole::PROJECT_MANAGER->value]);
        });

        $availableEngineers = $availableStaff->filter(function ($staff) {
            return in_array($staff['role'], [
                UserRole::SITE_ENGINEER->value,
                UserRole::PROJECT_MANAGER->value,
                UserRole::STORE_INCHARGE->value,
                UserRole::COORDINATOR->value
            ]);
        })->map(function ($staff) {
            $user = new User();
            $user->id = $staff['id'];
            $user->firstName = $staff['firstName'];
            $user->lastName = $staff['lastName'];
            $user->role = $staff['role'];
            return $user;
        });

        // Get assigned vendors separately (they're excluded from $assignedStaff)
        $assignedVendorIds = DB::table('project_user')
            ->where('project_user.project_id', $project->id)
            ->join('users', 'project_user.user_id', '=', 'users.id')
            ->where('users.role', UserRole::VENDOR->value)
            ->pluck('project_user.user_id')
            ->toArray();

        $assignedVendorsQuery = User::whereIn('id', $assignedVendorIds)
            ->when($isProjectManager, function ($q) use ($user) {
                $q->where('manager_id', $user->id);
            });

        $assignedVendors = $assignedVendorsQuery->get();

        // Get available vendors (not already assigned)
        $availableVendors = User::where('role', UserRole::VENDOR->value)
            ->whereNotIn('id', $assignedVendorIds)
            ->when($isProjectManager, function ($q) use ($user) {
                $q->where('manager_id', $user->id);
            })
            ->get();

        $assignedEngineersMessage = $assignedEngineers->isEmpty() ? "No engineers assigned." : null;
        
        // Get engineers for reassignment: All engineers for Admin, filtered by manager_id for Project Managers
        $reassignEngineersQuery = User::whereIn('role', [
            UserRole::SITE_ENGINEER->value,
            UserRole::PROJECT_MANAGER->value,
            UserRole::STORE_INCHARGE->value,
            UserRole::COORDINATOR->value
        ])
            ->when($isProjectManager, function ($q) use ($user) {
                $q->where('manager_id', $user->id);
            });
        $reassignEngineers = $reassignEngineersQuery->get();

        // Get vendors for reassignment: All vendors for Admin, filtered by manager_id for Project Managers
        $reassignVendorsQuery = User::where('role', UserRole::VENDOR->value)
            ->when($isProjectManager, function ($q) use ($user) {
                $q->where('manager_id', $user->id);
            });
        $reassignVendors = $reassignVendorsQuery->get();

        $streetlightDistricts = Streetlight::where('project_id', $id)
            ->select('district')
            ->distinct()
            ->get();

        $data = [
            'project' => $project,
            'state' => $state,
            'inventoryItems' => $inventoryItems,
            'users' => $users,
            'engineers' => $engineers,
            'vendors' => $vendors,
            'assignedEngineers' => $assignedEngineers,
            'availableEngineers' => $availableEngineers,
            'assignedEngineersMessage' => $assignedEngineersMessage,
            'assignedVendors' => $assignedVendors,
            'availableVendors' => $availableVendors,
            'reassignEngineers' => $reassignEngineers,
            'reassignVendors' => $reassignVendors,
            'assignedStaffByRole' => $assignedStaffByRole,
            'availableStaffByRole' => $availableStaffByRole,
            'isAdmin' => $isAdmin,
            'isProjectManager' => $isProjectManager,
            'initialStockValue' => $initialStockValue,
            'inStoreStockValue' => $inStoreStockValue,
            'dispatchedStockValue' => $dispatchedStockValue
        ];

        if ($project->project_type == 1) {
            $data['sites'] = Streetlight::where('project_id', $id)->get();
            $data['districts'] = Streetlight::where('project_id', $id)->select('district')->distinct()->get();
            $data['targets'] = StreetlightTask::where('project_id', $project->id)
                ->when($isProjectManager, fn($q) => $q->where('manager_id', $user->id))
                ->with('site', 'engineer', 'vendor', 'poles')
                ->orderBy('created_at', 'desc')
                ->get();

            $data['totalPoles'] = Streetlight::where('project_id', $project->id)->sum('total_poles');
            $data['totalSurveyedPoles'] = Streetlight::where('project_id', $project->id)->sum('number_of_surveyed_poles');
            $data['totalInstalledPoles'] = Streetlight::where('project_id', $project->id)->sum('number_of_installed_poles');

            // Prepare filter options
            $data['filterPanchayats'] = $data['targets']->pluck('site.panchayat')
                ->filter()
                ->unique()
                ->sort()
                ->mapWithKeys(fn($panchayat) => [$panchayat => $panchayat])
                ->prepend('All', '')
                ->toArray();

            $data['filterEngineers'] = $data['targets']->pluck('engineer')
                ->filter()
                ->map(fn($engineer) => trim(($engineer->firstName ?? '') . ' ' . ($engineer->lastName ?? '')))
                ->filter()
                ->unique()
                ->sort()
                ->mapWithKeys(fn($name) => [$name => $name])
                ->prepend('All', '')
                ->toArray();

            $data['filterVendors'] = $data['targets']->pluck('vendor.name')
                ->filter()
                ->unique()
                ->sort()
                ->mapWithKeys(fn($name) => [$name => $name])
                ->prepend('All', '')
                ->toArray();
        } else {
            $data['sites'] = $project->sites()->when($isProjectManager, fn($q) => $q->whereHas('tasks', fn($t) => $t->where('manager_id', $user->id)))
                ->get();

            $data['installationCount'] = Task::where('project_id', $project->id)
                ->where('activity', 'Installation')
                ->when($isProjectManager, fn($q) => $q->where('manager_id', $user->id))
                ->count();

            $data['rmsCount'] = Task::where('project_id', $project->id)
                ->where('activity', 'RMS')
                ->when($isProjectManager, fn($q) => $q->where('manager_id', $user->id))
                ->count();

            $data['inspectionCount'] = Task::where('project_id', $project->id)
                ->where('activity', 'Inspection')
                ->when($isProjectManager, fn($q) => $q->where('manager_id', $user->id))
                ->count();

            $data['targets'] = Task::where('project_id', $project->id)
                ->when($isProjectManager, fn($q) => $q->where('manager_id', $user->id))
                ->with('site', 'engineer')
                ->get();
        }

        return view('projects.show', $data);
    }



    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $project = Project::findOrFail($id);
        $this->authorize('update', $project);
        $state = State::where('id', $project->project_in_state)->get();

        return view('projects.edit', compact('project', 'state'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project)
    {
        $this->authorize('update', $project);
        try {
            $validated = $request->validate([
                'project_name' => 'required|string',
                'project_in_state' => 'string',
                'start_date' => 'required|date',
                'end_date' => 'required|date',
                'work_order_number' => 'required',
                'rate' => 'nullable|string',
                'project_capacity' => 'nullable|string',
                'total' => 'string',
                'description' => 'string',
            ]);
            $project->update($validated);
            return redirect()->route('projects.show', compact('project'))
                ->with('success', 'Project updated successfully.');
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();

            return redirect()->back()
                ->withErrors(['error' => $errorMessage])
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $project = Project::findOrFail($id);
            $this->authorize('delete', $project);
            $project->delete();
            return response()->json(['message' => 'Project deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Bulk delete projects
     */
    public function bulkDelete(Request $request)
    {
        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'exists:projects,id',
            ]);

            // Check authorization for each project
            $projects = Project::whereIn('id', $request->ids)->get();
            foreach ($projects as $project) {
                $this->authorize('delete', $project);
            }

            $count = Project::whereIn('id', $request->ids)->delete();

            return response()->json([
                'message' => "{$count} project(s) deleted successfully"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete projects: ' . $e->getMessage()
            ], 500);
        }
    }

    public function assignUsers(Request $request, $id)
    {
        try {
            $project = Project::findOrFail($id);
            $this->authorize('assignStaff', $project);

            $validated = $request->validate([
                'user_ids' => 'required|array',
                'user_ids.*' => 'exists:users,id',
            ]);

            $user = auth()->user();
            $isProjectManager = $user->role === UserRole::PROJECT_MANAGER->value;

            // Get users with their roles to set pivot role correctly
            $usersToAssign = User::whereIn('id', $validated['user_ids'])->get();

            // For Project Managers: Verify all users being assigned have manager_id = current_user->id
            if ($isProjectManager) {
                foreach ($usersToAssign as $userToAssign) {
                    if ($userToAssign->manager_id !== $user->id) {
                        if ($request->expectsJson()) {
                            return response()->json([
                                'success' => false,
                                'message' => 'You can only assign staff members assigned to you as team lead.'
                            ], 403);
                        }
                        return redirect()->back()->with('error', 'You can only assign staff members assigned to you as team lead.');
                    }
                }
            }

            DB::beginTransaction();

            // Sync with pivot role data - format: [user_id => ['role' => role_value]]
            $syncData = [];
            foreach ($usersToAssign as $userToAssign) {
                $syncData[$userToAssign->id] = ['role' => $userToAssign->role];
            }
            $project->users()->syncWithoutDetaching($syncData);

            Log::info('Staff assigned to project', [
                'project_id' => $id,
                'user_ids' => $validated['user_ids'],
                'assigned_by' => $user->id
            ]);

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Staff assigned successfully'
                ]);
            }

            return redirect()->back()->with('success', 'Users assigned successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to assign staff to project', [
                'project_id' => $id,
                'error' => $e->getMessage()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to assign staff: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Failed to assign staff: ' . $e->getMessage());
        }
    }

    public function removeStaff(Request $request, $id)
    {
        try {
            $project = Project::findOrFail($id);
            $this->authorize('removeStaff', $project);

            $validated = $request->validate([
                'user_ids' => 'required|array',
                'user_ids.*' => 'exists:users,id',
            ]);

            $user = auth()->user();
            $isAdmin = $user->role === UserRole::ADMIN->value;
            $isProjectManager = $user->role === UserRole::PROJECT_MANAGER->value;

            // For Project Managers: Verify all users being removed have manager_id = current_user->id
            if ($isProjectManager) {
                $usersToRemove = User::whereIn('id', $validated['user_ids'])->get();
                foreach ($usersToRemove as $userToRemove) {
                    if ($userToRemove->manager_id !== $user->id) {
                        if ($request->expectsJson()) {
                            return response()->json([
                                'success' => false,
                                'message' => 'You can only remove staff members assigned to you as team lead.'
                            ], 403);
                        }
                        return redirect()->back()->with('error', 'You can only remove staff members assigned to you as team lead.');
                    }
                }
            }

            DB::beginTransaction();

            // Determine who will receive reassigned targets
            $reassignToUserId = $user->id; // Default to current user (PM or Admin)

            // For admin removing staff, try to find the Project Manager for the project
            if ($isAdmin) {
                $projectManager = $project->users()
                    ->wherePivot('role', UserRole::PROJECT_MANAGER->value)
                    ->where('users.id', '!=', $user->id)
                    ->first();
                if ($projectManager) {
                    $reassignToUserId = $projectManager->id;
                } else {
                    // If no other PM found, use admin's ID
                    $reassignToUserId = $user->id;
                }
            }
            // For Project Manager removing their team members, reassign to themselves
            elseif ($isProjectManager) {
                $reassignToUserId = $user->id;
            }

            $removedUserIds = $validated['user_ids'];
            $targetsReassigned = 0;

            // Reassign StreetlightTask targets
            foreach ($removedUserIds as $removedUserId) {
                // Reassign tasks where removed user was engineer
                $engineerTasks = StreetlightTask::where('project_id', $id)
                    ->where('engineer_id', $removedUserId)
                    ->get();

                foreach ($engineerTasks as $task) {
                    $task->update(['engineer_id' => $reassignToUserId]);
                    $targetsReassigned++;
                }

                // Reassign tasks where removed user was vendor
                $vendorTasks = StreetlightTask::where('project_id', $id)
                    ->where('vendor_id', $removedUserId)
                    ->get();

                foreach ($vendorTasks as $task) {
                    $task->update(['vendor_id' => $reassignToUserId]);
                    $targetsReassigned++;
                }
            }

            // Detach users from project
            $project->users()->detach($removedUserIds);

            Log::info('Staff removed from project', [
                'project_id' => $id,
                'user_ids' => $removedUserIds,
                'targets_reassigned' => $targetsReassigned,
                'reassigned_to' => $reassignToUserId,
                'removed_by' => $user->id
            ]);

            DB::commit();

            $message = count($removedUserIds) . ' staff member(s) removed successfully';
            if ($targetsReassigned > 0) {
                $message .= '. ' . $targetsReassigned . ' target(s) reassigned.';
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'targets_reassigned' => $targetsReassigned
                ]);
            }

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to remove staff from project', [
                'project_id' => $id,
                'error' => $e->getMessage()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to remove staff: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Failed to remove staff: ' . $e->getMessage());
        }
    }

    public function destroyTarget($id)
    {
        $task = StreetlightTask::findOrFail($id);
        $task->delete();

        return redirect()->back()->with('success', 'Task permanently deleted.');
    }

    public function bulkDeleteTargets(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:streetlight_tasks,id',
        ]);

        try {
            $deleted = StreetlightTask::whereIn('id', $request->ids)->delete();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "{$deleted} target(s) deleted successfully."
                ]);
            }

            return redirect()->back()->with('success', "{$deleted} target(s) deleted successfully.");
        } catch (\Exception $e) {
            Log::error('Failed to bulk delete targets', ['error' => $e->getMessage()]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete targets: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Failed to delete targets: ' . $e->getMessage());
        }
    }

    public function bulkReassignTargets(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:streetlight_tasks,id',
            'engineer_id' => 'nullable|exists:users,id',
            'vendor_id' => 'nullable|exists:users,id',
        ]);

        try {
            $tasks = StreetlightTask::whereIn('id', $request->ids)->get();
            $updated = 0;
            $polesReassigned = 0;

            foreach ($tasks as $task) {
                if ($request->filled('engineer_id')) {
                    $task->engineer_id = $request->engineer_id;
                }
                if ($request->filled('vendor_id')) {
                    // Update task vendor_id for future pole assignments
                    $task->vendor_id = $request->vendor_id;

                    // Implement pole-level reassignment: only reassign pending poles
                    // Installed poles keep their original vendor_id
                    $pendingPoles = Pole::where('task_id', $task->id)
                        ->where('isInstallationDone', 0)
                        ->get();

                    foreach ($pendingPoles as $pole) {
                        $pole->vendor_id = $request->vendor_id;
                        $pole->save();
                        $polesReassigned++;
                    }
                }
                $task->save();
                $updated++;
            }

            $message = "{$updated} target(s) reassigned successfully.";
            if ($polesReassigned > 0) {
                $message .= " {$polesReassigned} pending pole(s) reassigned to new vendor.";
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'poles_reassigned' => $polesReassigned
                ]);
            }

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Failed to bulk reassign targets', ['error' => $e->getMessage()]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to reassign targets: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Failed to reassign targets: ' . $e->getMessage());
        }
    }

    public function downloadTargetImportFormat()
    {
        try {
            $data = [
                [
                    'Panchayat' => 'Example Panchayat Name',
                    'Engineer Name' => 'John Doe',
                    'Vendor Name' => 'Vendor ABC',
                    'Assigned Date' => '2024-01-01',
                    'End Date' => '2024-12-31',
                    'Wards' => 'Ward 1, Ward 2, Ward 3',
                ]
            ];

            $filename = 'targets_import_format_' . date('Y-m-d') . '.xlsx';

            return ExcelHelper::exportToExcel($data, $filename);
        } catch (\Exception $e) {
            Log::error('Failed to download target import format', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Failed to download import format: ' . $e->getMessage());
        }
    }

    public function importTargets(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
            'project_id' => 'required|exists:projects,id',
        ]);

        try {
            $file = $request->file('file');
            $data = Excel::toArray([], $file);

            if (empty($data) || empty($data[0])) {
                return redirect()->back()->with('error', 'The file is empty or invalid.');
            }

            $rows = $data[0];
            $header = array_shift($rows); // Remove header row

            $imported = 0;
            $errors = [];

            foreach ($rows as $index => $row) {
                try {
                    $siteId = $row[0] ?? null;
                    $engineerId = $row[1] ?? null;
                    $vendorId = $row[2] ?? null;
                    $startDate = $row[3] ?? now();
                    $endDate = $row[4] ?? null;

                    if (!$siteId) {
                        $errors[] = "Row " . ($index + 2) . ": Site ID is required";
                        continue;
                    }

                    // Check if task already exists
                    $existingTask = StreetlightTask::where('site_id', $siteId)
                        ->where('project_id', $request->project_id)
                        ->first();

                    if ($existingTask) {
                        $errors[] = "Row " . ($index + 2) . ": Task already exists for this site";
                        continue;
                    }

                    StreetlightTask::create([
                        'project_id' => $request->project_id,
                        'site_id' => $siteId,
                        'engineer_id' => $engineerId,
                        'vendor_id' => $vendorId,
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'status' => 'Pending',
                    ]);

                    $imported++;
                } catch (\Exception $e) {
                    $errors[] = "Row " . ($index + 2) . ": " . $e->getMessage();
                }
            }

            $message = "{$imported} target(s) imported successfully.";
            if (!empty($errors)) {
                $message .= " " . count($errors) . " error(s) occurred.";
            }

            return redirect()->back()->with('success', $message)->with('import_errors', $errors);
        } catch (\Exception $e) {
            Log::error('Failed to import targets', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Failed to import targets: ' . $e->getMessage());
        }
    }

    /**
     * Import projects from Excel
     */
    public function import(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|mimes:xlsx,xls,csv|max:2048',
            ]);

            // Simple import logic - you can create a dedicated Import class later
            $file = $request->file('file');
            $data = Excel::toArray([], $file);

            if (empty($data) || empty($data[0])) {
                return redirect()->back()->with('error', 'The file is empty or invalid.');
            }

            $rows = $data[0];
            $header = array_shift($rows); // Remove header row

            $imported = 0;
            $errors = [];

            foreach ($rows as $index => $row) {
                try {
                    if (empty($row[0]))
                        continue; // Skip empty rows

                    Project::create([
                        'project_name' => $row[0] ?? 'Untitled Project',
                        'work_order_number' => $row[1] ?? '',
                        'start_date' => $row[2] ?? now(),
                        'end_date' => $row[3] ?? now()->addYear(),
                        'rate' => $row[4] ?? '0',
                        'project_type' => isset($row[5]) ? (int) $row[5] : 0,
                        'project_capacity' => $row[6] ?? null,
                        'description' => $row[7] ?? null,
                    ]);
                    $imported++;
                } catch (\Exception $e) {
                    $errors[] = "Row " . ($index + 2) . ": " . $e->getMessage();
                }
            }

            $message = "Successfully imported {$imported} project(s).";
            if (!empty($errors)) {
                $message .= " Errors: " . implode(', ', array_slice($errors, 0, 5));
            }

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Download import format template
     */
    public function downloadFormat()
    {
        $headers = [
            'Project Name',
            'Work Order Number',
            'Start Date (YYYY-MM-DD)',
            'End Date (YYYY-MM-DD)',
            'Order Value',
            'Project Type (0=Rooftop, 1=Streetlight)',
        ];

        $filename = 'projects_import_format_' . date('Y-m-d') . '.csv';

        $callback = function () use ($headers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
