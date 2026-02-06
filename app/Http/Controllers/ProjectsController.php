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
use App\Models\TargetDeletionJob;
use App\Models\Task;
use App\Models\User;
use App\Jobs\ProcessTargetDeletionChunk;
use App\Services\Task\TargetDeletionService;
use App\Services\Logging\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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
    public function __construct(
        public Project $project,
        protected ActivityLogger $activityLogger
    ) {
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

            $this->activityLogger->log('project', 'created', $project, [
                'description' => 'Project created.',
            ]);

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

        // Districts for this project (for vendor assignment)
        // For streetlight projects, use distinct streetlight.district names mapped to City records
        // For rooftop projects, use distinct site districts via districtRelation
        $projectDistricts = collect();
        if ($project->project_type == 1) {
            $districtNames = Streetlight::where('project_id', $project->id)
                ->whereNotNull('district')
                ->distinct()
                ->pluck('district');

            if ($districtNames->isNotEmpty()) {
                $projectDistricts = \App\Models\City::whereIn('name', $districtNames->toArray())
                    ->orderBy('name')
                    ->get();
            }
        } else {
            $siteDistricts = $project->sites()
                ->whereNotNull('district')
                ->with('districtRelation')
                ->get()
                ->map(function (Site $site) {
                    return $site->districtRelation;
                })
                ->filter();

            $projectDistricts = $siteDistricts->unique('id')->sortBy('name')->values();
        }

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
            'dispatchedStockValue' => $dispatchedStockValue,
            'projectDistricts' => $projectDistricts,
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
                'district_id' => 'nullable|exists:cities,id',
            ]);

            $user = auth()->user();
            $isProjectManager = $user->role === UserRole::PROJECT_MANAGER->value;

            // Get users with their roles to set pivot role correctly
            $usersToAssign = User::whereIn('id', $validated['user_ids'])->get();

            $isVendorAssignmentRoute = $request->routeIs('projects.assignVendors');

            // For vendor assignment route: require district when at least one vendor is being assigned
            if ($isVendorAssignmentRoute) {
                $hasVendor = $usersToAssign->contains(function (User $u) {
                    return (int) $u->role === UserRole::VENDOR->value;
                });

                if ($hasVendor && empty($validated['district_id'])) {
                    $message = 'District is required when assigning vendors to a project.';
                    if ($request->expectsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => $message,
                        ], 422);
                    }

                    return redirect()->back()->with('error', $message);
                }
            }

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

            // Sync with pivot role data - format: [user_id => ['role' => role_value, 'district_id' => x]]
            $syncData = [];
            $districtId = $validated['district_id'] ?? null;

            foreach ($usersToAssign as $userToAssign) {
                $pivot = ['role' => $userToAssign->role];

                // Only attach district for vendors when provided
                if (!is_null($districtId) && (int) $userToAssign->role === UserRole::VENDOR->value) {
                    $pivot['district_id'] = $districtId;
                }

                $syncData[$userToAssign->id] = $pivot;
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

    public function destroyTarget(Request $request, $id)
    {
        try {
            $deletionService = app(TargetDeletionService::class);
            $task = StreetlightTask::with('poles')->findOrFail($id);

            // Count total poles to determine if we should process synchronously
            $totalPoles = $task->poles->count();
            $syncThreshold = config('target_deletion.sync_threshold', 100);

            if ($totalPoles < $syncThreshold) {
                // Small deletion - process synchronously
                $result = $deletionService->deleteTargets([$id]);

                $message = 'Target deleted successfully. ';
                $message .= $result['poles_deleted'] . ' pole(s) deleted. ';
                $message .= $result['inventory_items_returned'] . ' inventory item(s) returned to stock.';

                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => $message
                    ]);
                }

                return redirect()->back()->with('success', $message);
            } else {
                // Large deletion - use async processing
                return $this->initiateAsyncDeletion([$id], $request);
            }
        } catch (\Exception $e) {
            Log::error('Failed to delete target', [
                'task_id' => $id,
                'error' => $e->getMessage()
            ]);

            $message = 'Failed to delete target: ' . $e->getMessage();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message
                ], 500);
            }

            return redirect()->back()->with('error', $message);
        }
    }

    public function bulkDeleteTargets(Request $request)
    {
        // #region agent log
        file_put_contents(base_path('.cursor/debug.log'), json_encode(['sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'A', 'location' => 'ProjectsController.php:724', 'message' => 'bulkDeleteTargets called', 'data' => ['ids_count' => count($request->ids ?? []), 'user_id' => auth()->id()], 'timestamp' => time() * 1000]) . "\n", FILE_APPEND);
        // #endregion
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:streetlight_tasks,id',
        ]);

        try {
            $deletionService = app(TargetDeletionService::class);
            // Count total poles to determine processing method
            $totalPoles = Pole::whereIn('task_id', $request->ids)->count();
            $syncThreshold = config('target_deletion.sync_threshold', 100);
            $isBulkDeletion = count($request->ids) > 1;
            // #region agent log
            file_put_contents(base_path('.cursor/debug.log'), json_encode(['sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'A', 'location' => 'ProjectsController.php:736', 'message' => 'Pole count calculated', 'data' => ['total_poles' => $totalPoles, 'is_bulk' => $isBulkDeletion, 'sync_threshold' => $syncThreshold], 'timestamp' => time() * 1000]) . "\n", FILE_APPEND);
            // #endregion

            // Use async processing for bulk deletions OR large single deletions
            // Always use async for bulk deletions (more than 1 item) regardless of pole count
            if ($isBulkDeletion || $totalPoles >= $syncThreshold) {
                // #region agent log
                file_put_contents(base_path('.cursor/debug.log'), json_encode(['sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'A', 'location' => 'ProjectsController.php:742', 'message' => 'Using async deletion path', 'data' => ['reason' => $isBulkDeletion ? 'bulk_deletion' : 'large_pole_count'], 'timestamp' => time() * 1000]) . "\n", FILE_APPEND);
                // #endregion
                // Large or bulk deletion - use async processing
                return $this->initiateAsyncDeletion($request->ids, $request);
            } else {
                // #region agent log
                file_put_contents(base_path('.cursor/debug.log'), json_encode(['sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'B', 'location' => 'ProjectsController.php:747', 'message' => 'Using sync deletion path', 'data' => [], 'timestamp' => time() * 1000]) . "\n", FILE_APPEND);
                // #endregion
                // Small single deletion - process synchronously
                $result = $deletionService->deleteTargets($request->ids);

                $message = count($request->ids) . ' target(s) deleted successfully. ';
                $message .= $result['poles_deleted'] . ' pole(s) deleted. ';
                $message .= $result['inventory_items_returned'] . ' inventory item(s) returned to stock.';

                // Always return JSON for bulk delete endpoint (it's always called via AJAX)
                return response()->json([
                    'success' => true,
                    'message' => $message
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to bulk delete targets', [
                'task_ids' => $request->ids,
                'error' => $e->getMessage()
            ]);

            // Always return JSON for bulk delete endpoint
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete targets: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Initiate async deletion with progress tracking
     */
    protected function initiateAsyncDeletion(array $taskIds, ?Request $request = null)
    {
        // #region agent log
        file_put_contents(base_path('.cursor/debug.log'), json_encode(['sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'A', 'location' => 'ProjectsController.php:776', 'message' => 'initiateAsyncDeletion started', 'data' => ['task_ids_count' => count($taskIds)], 'timestamp' => time() * 1000]) . "\n", FILE_APPEND);
        // #endregion
        // Count total poles
        $totalPoles = Pole::whereIn('task_id', $taskIds)->count();

        // Create deletion job
        $job = TargetDeletionJob::create([
            'task_ids' => $taskIds,
            'total_tasks' => count($taskIds),
            'processed_tasks' => 0, // Initialize to 0
            'total_poles' => $totalPoles,
            'processed_poles' => 0, // Initialize to 0
            'processed_task_ids' => [], // Initialize empty array
            'user_id' => auth()->id(),
            'status' => 'pending',
        ]);
        // #region agent log
        file_put_contents(base_path('.cursor/debug.log'), json_encode(['sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'A', 'location' => 'ProjectsController.php:791', 'message' => 'TargetDeletionJob created', 'data' => ['job_id' => $job->job_id, 'total_poles' => $totalPoles, 'queue_connection' => config('queue.default')], 'timestamp' => time() * 1000]) . "\n", FILE_APPEND);
        // #endregion

        // Queue first chunk
        $chunkSize = config('target_deletion.chunk_size', 50);
        // Ensure job is dispatched to the correct queue connection
        ProcessTargetDeletionChunk::dispatch($job->job_id, $chunkSize)
            ->onConnection(config('queue.default'))
            ->onQueue('default');
        // #region agent log
        file_put_contents(base_path('.cursor/debug.log'), json_encode(['sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'A', 'location' => 'ProjectsController.php:798', 'message' => 'ProcessTargetDeletionChunk dispatched', 'data' => ['job_id' => $job->job_id, 'chunk_size' => $chunkSize], 'timestamp' => time() * 1000]) . "\n", FILE_APPEND);
        // #endregion

        // Always return JSON for bulk delete operations (they're always AJAX)
        // Check if it's a bulk delete by checking if request has 'ids' array
        $isBulkDelete = $request && $request->has('ids') && is_array($request->ids) && count($request->ids) > 0;

        // For bulk delete endpoint, always return JSON
        if ($isBulkDelete || ($request && ($request->expectsJson() || $request->ajax() || $request->wantsJson()))) {
            return response()->json([
                'success' => true,
                'message' => 'Deletion started. Processing in background...',
                'job_id' => $job->job_id,
                'total_tasks' => count($taskIds),
                'total_poles' => $totalPoles,
            ]);
        }

        // Fallback for non-AJAX requests (shouldn't happen for bulk delete)
        return redirect()->back()->with([
            'success' => 'Deletion started. Processing in background...',
            'deletion_job_id' => $job->job_id,
        ]);
    }

    /**
     * Get deletion progress
     */
    public function getDeletionProgress($jobId)
    {
        $job = TargetDeletionJob::where('job_id', $jobId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        // If job is pending, re-dispatch it if not already in queue
        if ($job->status === 'pending') {
            $chunkSize = config('target_deletion.chunk_size', 50);
            $allTaskIds = $job->task_ids ?? [];
            $processedTaskIds = $job->processed_task_ids ?? [];
            $remainingTaskIds = array_diff($allTaskIds, $processedTaskIds);

            // Only re-dispatch if there are remaining tasks
            if (!empty($remainingTaskIds)) {
                try {
                    $queueConnection = config('queue.default');

                    // Check if job is already in queue to avoid duplicates
                    $jobsInQueue = 0;
                    if ($queueConnection === 'database') {
                        $jobsInQueue = \DB::table('jobs')
                            ->where('payload', 'like', '%' . $job->job_id . '%')
                            ->count();
                    }

                    // If jobs are stuck in queue (older than 30 seconds), process synchronously as fallback
                    $shouldProcessSync = false;
                    if ($queueConnection === 'database' && $jobsInQueue > 0) {
                        $stuckJobs = \DB::table('jobs')
                            ->where('payload', 'like', '%' . $job->job_id . '%')
                            ->where('available_at', '<', time() - 30)
                            ->whereNull('reserved_at')
                            ->count();

                        if ($stuckJobs > 0 && $job->created_at->diffInSeconds(now()) > 60) {
                            $shouldProcessSync = true;
                            Log::warning('Jobs stuck in queue, processing synchronously as fallback', [
                                'job_id' => $jobId,
                                'stuck_jobs' => $stuckJobs,
                                'age_seconds' => $job->created_at->diffInSeconds(now())
                            ]);
                        }
                    }

                    if ($shouldProcessSync) {
                        // Process first chunk synchronously
                        $deletionService = app(\App\Services\Task\TargetDeletionService::class);
                        $chunk = array_slice($remainingTaskIds, 0, min($chunkSize, 10)); // Process max 10 at a time

                        foreach ($chunk as $taskId) {
                            try {
                                $result = $deletionService->deleteTargets([$taskId], $job->job_id);
                                $job->addProcessedTaskId($taskId);
                                $job->increment('processed_poles', $result['poles_deleted'] ?? 0);
                                $job->refresh();
                            } catch (\Exception $taskException) {
                                Log::error('Error deleting task in sync fallback', [
                                    'job_id' => $jobId,
                                    'task_id' => $taskId,
                                    'error' => $taskException->getMessage()
                                ]);
                                $job->addProcessedTaskId($taskId);
                            }
                        }

                        // Queue next chunk if more remain
                        $job->refresh();
                        $remainingAfterChunk = array_diff($job->task_ids, $job->processed_task_ids);
                        if (!empty($remainingAfterChunk)) {
                            ProcessTargetDeletionChunk::dispatch($job->job_id, $chunkSize)
                                ->onConnection($queueConnection)
                                ->onQueue('default');
                        } else {
                            $job->markAsCompleted();
                        }
                    } elseif ($jobsInQueue === 0) {
                        try {
                            $dispatchedJob = ProcessTargetDeletionChunk::dispatch($job->job_id, $chunkSize)
                                ->onConnection($queueConnection)
                                ->onQueue('default');

                            Log::info('Dispatched deletion job', [
                                'job_id' => $jobId,
                                'queue_connection' => $queueConnection
                            ]);
                        } catch (\Exception $dispatchException) {
                            Log::error('Exception during job dispatch', [
                                'job_id' => $jobId,
                                'error' => $dispatchException->getMessage()
                            ]);
                        }
                    } else {
                        Log::debug('Job already in queue, skipping re-dispatch', [
                            'job_id' => $jobId,
                            'jobs_in_queue' => $jobsInQueue
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to re-dispatch deletion job', [
                        'job_id' => $jobId,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }
        }

        $progress = $job->progress_percentage;
        $status = $job->status;

        // Use processed_tasks from the model (calculated from processed_task_ids array)
        $processedCount = $job->processed_tasks ?? (is_array($job->processed_task_ids) ? count($job->processed_task_ids) : 0);
        $processedPoles = $job->processed_poles ?? 0;
        $message = "Deleting {$processedCount} item(s) out of {$job->total_tasks}";
        if ($job->total_poles > 0) {
            $message .= " ({$processedPoles} poles processed)";
        }

        return response()->json([
            'status' => 'success',
            'job_id' => $job->job_id,
            'job_status' => $status, // For JavaScript compatibility - this is the actual job status
            'progress_percentage' => $progress,
            'processed_tasks' => $processedCount,
            'total_tasks' => $job->total_tasks,
            'processed_poles' => $job->processed_poles ?? 0,
            'total_poles' => $job->total_poles,
            'message' => $message,
            'error_message' => $job->error_message,
            'started_at' => $job->started_at?->toIso8601String(),
            'completed_at' => $job->completed_at?->toIso8601String(),
        ]);
    }

    /**
     * Get active deletion jobs for current user
     */
    public function getActiveDeletionJobs()
    {
        $jobs = TargetDeletionJob::where('user_id', auth()->id())
            ->whereIn('status', ['pending', 'processing'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Re-dispatch any pending jobs that haven't been processed
        foreach ($jobs as $job) {
            if ($job->status === 'pending') {
                $chunkSize = config('target_deletion.chunk_size', 50);
                $allTaskIds = $job->task_ids ?? [];
                $processedTaskIds = $job->processed_task_ids ?? [];
                $remainingTaskIds = array_diff($allTaskIds, $processedTaskIds);

                if (!empty($remainingTaskIds)) {
                    try {
                        ProcessTargetDeletionChunk::dispatch($job->job_id, $chunkSize)
                            ->onConnection(config('queue.default'))
                            ->onQueue('default');
                        Log::info('Re-dispatched pending deletion job from active jobs list', ['job_id' => $job->job_id]);
                    } catch (\Exception $e) {
                        Log::error('Failed to re-dispatch deletion job from active jobs', [
                            'job_id' => $job->job_id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }
        }

        return response()->json([
            'jobs' => $jobs->map(function ($job) {
                return [
                    'job_id' => $job->job_id,
                    'status' => $job->status,
                    'progress_percentage' => $job->progress_percentage,
                    'total_tasks' => $job->total_tasks,
                    'processed_tasks' => $job->processed_tasks,
                ];
            }),
        ]);
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
            $projectId = $request->project_id;
            $currentUser = auth()->user();

            $import = new \App\Imports\TargetImport($projectId, $currentUser);
            Excel::import($import, $file);

            $errors = $import->getErrors();
            $importedCount = $import->getImportedCount();
            $errorFileUrl = null;

            // Generate error.txt file if there are errors
            if (!empty($errors)) {
                $lines = [];
                $lines[] = 'Target Import Errors - ' . now()->toDateTimeString();
                $lines[] = 'Project ID: ' . $projectId;
                $lines[] = str_repeat('=', 80);
                $lines[] = '';

                $errorCounter = 1;
                foreach ($errors as $err) {
                    $reason = $err['reason'] ?? 'unknown';
                    $row = $err['row'] ?? 'Unknown';
                    $panchayat = $err['panchayat'] ?? '';
                    $engineerName = $err['engineer_name'] ?? '';
                    $vendorName = $err['vendor_name'] ?? '';
                    $wards = $err['wards'] ?? '';

                    $lines[] = "T{$errorCounter} failed because {$reason}";
                    $lines[] = "  Row: {$row}";
                    $lines[] = "  Panchayat: {$panchayat}";
                    $lines[] = "  Engineer Name: {$engineerName}";
                    $lines[] = "  Vendor Name: {$vendorName}";
                    $lines[] = "  Wards: {$wards}";
                    $lines[] = str_repeat('-', 40);
                    $lines[] = '';

                    $errorCounter++;
                }

                $content = implode(PHP_EOL, $lines) . PHP_EOL;

                // Use the public disk so URL generation is reliable
                $disk = Storage::disk('public');
                if (!$disk->exists('import_errors')) {
                    $disk->makeDirectory('import_errors');
                }

                $fileName = 'target_errors_project_' . $projectId . '_' . time() . '.txt';
                $relativePath = 'import_errors/' . $fileName;
                $disk->put($relativePath, $content);

                // Public URL (requires `php artisan storage:link` once)
                $errorFileUrl = $disk->url($relativePath);
            }

            // Set proper session flash messages based on results
            $redirect = redirect()->back();

            if ($importedCount > 0 && empty($errors)) {
                // All successful
                $message = "{$importedCount} target(s) imported successfully.";
                $redirect->with('success', $message);
            } elseif ($importedCount > 0 && !empty($errors)) {
                // Partial success
                $message = "{$importedCount} target(s) imported successfully. " . count($errors) . " error(s) occurred.";
                $redirect->with('warning', $message)
                    ->with('import_errors_url', $errorFileUrl)
                    ->with('import_errors_count', count($errors));
            } else {
                // All failed
                $message = "0 target(s) imported successfully. " . count($errors) . " error(s) occurred.";
                $redirect->with('error', $message)
                    ->with('import_errors_url', $errorFileUrl)
                    ->with('import_errors_count', count($errors));
            }

            return $redirect;
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
