<?php

namespace App\Http\Controllers;

use App\Enums\TaskStatus;
use App\Enums\UserRole;
use App\Imports\VendorImport;
use App\Models\City;
use App\Models\InventoryDispatch;
use App\Models\Pole;
use App\Models\Project;
use App\Models\Site;
use App\Models\Streetlight;
use App\Models\StreetlightTask;
use App\Models\Task;
use App\Models\User;
use App\Traits\GeneratesUniqueUsername;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class VendorController extends Controller
{
    use GeneratesUniqueUsername;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $userRole = UserRole::fromValue($user->role);
        
        // Only Admin, Project Manager, and HR Manager can view vendors
        if (!in_array($userRole, [UserRole::ADMIN, UserRole::PROJECT_MANAGER, UserRole::HR_MANAGER])) {
            abort(403, 'Unauthorized access');
        }
        
        // Load filter options for client-side filtering
        $projects = Project::all();
        $managers = User::where('role', UserRole::PROJECT_MANAGER->value)->get();
        
        // Get districts from vendor's assigned projects' sites (rooftop) and streetlights (streetlight)
        // First get districts from sites (rooftop projects - foreign key)
        $siteDistricts = DB::table('sites')
            ->join('project_user', 'sites.project_id', '=', 'project_user.project_id')
            ->join('users', 'project_user.user_id', '=', 'users.id')
            ->where('users.role', UserRole::VENDOR->value)
            ->whereNotNull('sites.district')
            ->distinct()
            ->pluck('sites.district');
        
        // Get district names from streetlights (streetlight projects - string field)
        $streetlightDistrictNames = DB::table('streetlights')
            ->join('project_user', 'streetlights.project_id', '=', 'project_user.project_id')
            ->join('users', 'project_user.user_id', '=', 'users.id')
            ->where('users.role', UserRole::VENDOR->value)
            ->whereNotNull('streetlights.district')
            ->distinct()
            ->pluck('streetlights.district');
        
        // Also check legacy project_id field
        $legacySiteDistricts = DB::table('sites')
            ->join('users', 'sites.project_id', '=', 'users.project_id')
            ->where('users.role', UserRole::VENDOR->value)
            ->whereNotNull('sites.district')
            ->distinct()
            ->pluck('sites.district');
        
        $legacyStreetlightDistricts = DB::table('streetlights')
            ->join('users', 'streetlights.project_id', '=', 'users.project_id')
            ->where('users.role', UserRole::VENDOR->value)
            ->whereNotNull('streetlights.district')
            ->distinct()
            ->pluck('streetlights.district');
        
        // Combine all district IDs and names
        $allDistrictIds = $siteDistricts->merge($legacySiteDistricts)->unique();
        $allDistrictNames = $streetlightDistrictNames->merge($legacyStreetlightDistricts)->unique();
        
        // Get City records by ID and by name
        $districts = City::whereIn('id', $allDistrictIds)
            ->orWhereIn('name', $allDistrictNames)
            ->distinct()
            ->get();
        
        // Return all vendors - filtering will be done client-side by DataTables
        $vendors = User::where('role', UserRole::VENDOR->value)
            ->with(['projects', 'projectManager'])
            ->get();
        
        return view('uservendors.index', compact('vendors', 'projects', 'managers', 'districts'));
    }

    /**
     * Import vendors from Excel file.
     */
    public function import(Request $request)
    {
        $user = Auth::user();
        $userRole = UserRole::fromValue($user->role);
        
        // Only Admin, Project Manager, and HR Manager can import vendors
        if (!in_array($userRole, [UserRole::ADMIN, UserRole::PROJECT_MANAGER, UserRole::HR_MANAGER])) {
            abort(403, 'Unauthorized access');
        }
        
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls,txt',
        ]);

        $file = $request->file('file');

        try {
            $import = new VendorImport();
            Excel::import($import, $file);

            $summary = $import->getSummary();

            return redirect()->back()
                ->with('success', $summary['message'])
                ->with('import_errors', $summary['errors']);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $messages = [];
            foreach ($failures as $failure) {
                $messages[] = "Row {$failure->row()}: " . implode(', ', $failure->errors());
            }
            return redirect()->back()->withErrors(['file' => 'Import validation failed'])->with('import_errors', $messages);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['file' => 'Import failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();
        $userRole = UserRole::fromValue($user->role);
        
        // Only Admin, Project Manager, and HR Manager can create vendors
        if (!in_array($userRole, [UserRole::ADMIN, UserRole::PROJECT_MANAGER, UserRole::HR_MANAGER])) {
            abort(403, 'Unauthorized access');
        }
        
        $siteEngineers = User::where('role', UserRole::PROJECT_MANAGER->value)->get();
        $projects = Project::all();

        // Only districts actually used in streetlight panchayats
        $streetlightDistrictNames = Streetlight::whereNotNull('district')
            ->distinct()
            ->pluck('district');

        $districts = City::whereIn('name', $streetlightDistrictNames)
            ->orderBy('name')
            ->get();

        return view('uservendors.create', compact('siteEngineers', 'projects', 'districts'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $userRole = UserRole::fromValue($user->role);
        
        // Only Admin, Project Manager, and HR Manager can create vendors
        if (!in_array($userRole, [UserRole::ADMIN, UserRole::PROJECT_MANAGER, UserRole::HR_MANAGER])) {
            abort(403, 'Unauthorized access');
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'firstName' => 'required|string|max:255',
            'manager_id' => 'nullable|exists:users,id',
            'project_id' => 'nullable|exists:projects,id',
            'district_id' => 'nullable|exists:cities,id',
            'lastName' => 'required|string|max:255',
            'contactNo' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'aadharNumber' => 'nullable|string|max:12',
            'pan' => 'nullable|string|max:10',
            'gstNumber' => 'nullable|string|max:15',
            'accountName' => 'nullable|string|max:255',
            'accountNumber' => 'nullable|string|max:255',
            'ifsc' => 'nullable|string|max:11',
            'bankName' => 'nullable|string|max:255',
            'branch' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        try {
            $validated['username'] = $this->generateUniqueUsername($validated['name']);
            $validated['password'] = bcrypt($validated['password']);
            $validated['role'] = UserRole::VENDOR->value;

            // district_id is only for pivot; do not persist on users table
            $districtId = $validated['district_id'] ?? null;
            unset($validated['district_id']);

            $vendor = User::create($validated);

            // Sync to project_user pivot table if project_id is provided
            if (!empty($validated['project_id'])) {
                $vendor->assignToProject($validated['project_id'], $districtId);
            }

            return redirect()->route('uservendors.index')
                ->with('success', 'Vendor created successfully.');
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();

            return redirect()->back()
                ->withErrors(['error' => $errorMessage])
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $user = Auth::user();
        $userRole = UserRole::fromValue($user->role);
        
        // Only Admin, Project Manager, and HR Manager can view vendors
        if (!in_array($userRole, [UserRole::ADMIN, UserRole::PROJECT_MANAGER, UserRole::HR_MANAGER])) {
            abort(403, 'Unauthorized access');
        }
        
        $vendor = User::where('role', UserRole::VENDOR->value)
            ->with(['siteEngineer', 'projectManager', 'projects'])
            ->findOrFail($id);

        // Get all assigned projects from pivot table
        $assignedProjects = $vendor->getAssignedProjects();
        
        // If no projects in pivot, check legacy project_id
        if ($assignedProjects->isEmpty() && $vendor->project_id) {
            $legacyProject = Project::find($vendor->project_id);
            if ($legacyProject) {
                $assignedProjects = collect([$legacyProject]);
                // Sync to pivot table for consistency
                $vendor->assignToProject($legacyProject->id);
            }
        }
        
        // Get primary project (first from assigned projects or legacy project_id)
        $primaryProject = $assignedProjects->first() ?? ($vendor->project_id ? Project::find($vendor->project_id) : null);
        
        // Group tasks by project
        $tasksByProject = [];
        $allTasks = collect();
        
        foreach ($assignedProjects as $project) {
            if ($project->project_type == 1) {
                $projectTasks = StreetlightTask::with('site')
                    ->where('vendor_id', $vendor->id)
                    ->where('project_id', $project->id)
                    ->get();
            } else {
                $projectTasks = Task::with('site')
                    ->where('vendor_id', $vendor->id)
                    ->where('project_id', $project->id)
                    ->get();
            }
            
            $tasksByProject[$project->id] = [
                'project' => $project,
                'tasks' => $projectTasks,
                'assigned' => $projectTasks,
                'assignedCount' => $projectTasks->count(),
                'completed' => $projectTasks->where('status', TaskStatus::COMPLETED->value),
                'completedCount' => $projectTasks->where('status', TaskStatus::COMPLETED->value)->count(),
                'pending' => $projectTasks->whereIn('status', [
                    TaskStatus::PENDING->value,
                    TaskStatus::IN_PROGRESS->value
                ]),
                'pendingCount' => $projectTasks->whereIn('status', [
                    TaskStatus::PENDING->value,
                    TaskStatus::IN_PROGRESS->value
                ])->count(),
                'rejected' => $projectTasks->where('status', TaskStatus::BLOCKED->value),
                'rejectedCount' => $projectTasks->where('status', TaskStatus::BLOCKED->value)->count(),
            ];
            
            $allTasks = $allTasks->merge($projectTasks);
        }
        
        // Calculate total tasks: For streetlight = sum of total_poles, for rooftop = count of sites/tasks
        $totalTasksCount = 0;
        foreach ($assignedProjects as $project) {
            if ($project->project_type == 1) {
                // Streetlight: Sum of total_poles from all panchayats/sites
                if (isset($streetlightDataByProject[$project->id])) {
                    $totalTasksCount += $streetlightDataByProject[$project->id]['total_poles'] ?? 0;
                }
            } else {
                // Rooftop: Count of sites/tasks
                if (isset($rooftopDataByProject[$project->id])) {
                    $totalTasksCount += $rooftopDataByProject[$project->id]['total_sites'] ?? 0;
                }
            }
        }
        
        // Calculate completed: For streetlight = installed poles, for rooftop = completed tasks
        $completedTasksCount = 0;
        foreach ($assignedProjects as $project) {
            if ($project->project_type == 1) {
                // Streetlight: Count installed poles
                if (isset($streetlightDataByProject[$project->id])) {
                    $completedTasksCount += $streetlightDataByProject[$project->id]['installed_poles'] ?? 0;
                }
            } else {
                // Rooftop: Count completed tasks
                $projectCompletedTasks = $allTasks->where('project_id', $project->id)
                    ->where('status', TaskStatus::COMPLETED->value);
                $completedTasksCount += $projectCompletedTasks->count();
            }
        }
        
        // Aggregate totals across all projects (for other metrics)
        $assignedTasks = $allTasks;
        $assignedTasksCount = $allTasks->count();
        $completedTasks = $allTasks->where('status', TaskStatus::COMPLETED->value);
        $pendingTasks = $allTasks->whereIn('status', [
            TaskStatus::PENDING->value,
            TaskStatus::IN_PROGRESS->value
        ]);
        $pendingTasksCount = $pendingTasks->count();
        $rejectedTasks = $allTasks->where('status', TaskStatus::BLOCKED->value);
        $rejectedTasksCount = $rejectedTasks->count();
        
        // Gather streetlight poles data per project
        $streetlightDataByProject = [];
        foreach ($assignedProjects as $project) {
            if ($project->project_type == 1) {
                $projectTasks = StreetlightTask::where('vendor_id', $vendor->id)
                    ->where('project_id', $project->id)
                    ->with('site')
                    ->get();
                
                $projectTaskIds = $projectTasks->pluck('id');
                $siteIds = $projectTasks->pluck('site_id')->filter()->unique();
                
                // Get sites from tasks
                $sites = Streetlight::whereIn('id', $siteIds)->get();
                
                // Calculate totals
                $totalPoles = $sites->sum('total_poles');
                
                $surveyedPoles = Pole::whereIn('task_id', $projectTaskIds)
                    ->where('isSurveyDone', 1)
                    ->count();
                
                $installedPoles = Pole::whereIn('task_id', $projectTaskIds)
                    ->where('isInstallationDone', 1)
                    ->count();
                
                // Get detailed streetlight sites with poles data
                $streetlightSites = $sites->map(function($site) use ($projectTaskIds, $projectTasks) {
                    // Get tasks for this site
                    $siteTasks = $projectTasks->where('site_id', $site->id);
                    $siteTaskIds = $siteTasks->pluck('id');
                    
                    // Get poles for these tasks
                    $poles = Pole::whereIn('task_id', $siteTaskIds)->get();
                    
                    return [
                        'id' => $site->id,
                        'state' => $site->state,
                        'district' => $site->district,
                        'block' => $site->block,
                        'panchayat' => $site->panchayat,
                        'ward' => $site->ward,
                        'total_poles' => $site->total_poles ?? 0,
                        'number_of_surveyed_poles' => $site->number_of_surveyed_poles ?? 0,
                        'number_of_installed_poles' => $site->number_of_installed_poles ?? 0,
                        'surveyed_poles_count' => $poles->where('isSurveyDone', 1)->count(),
                        'installed_poles_count' => $poles->where('isInstallationDone', 1)->count(),
                        'task' => $siteTasks->first(),
                    ];
                });
                
                $streetlightDataByProject[$project->id] = [
                    'project' => $project,
                    'total_poles' => $totalPoles,
                    'surveyed_poles' => $surveyedPoles,
                    'installed_poles' => $installedPoles,
                    'sites' => $streetlightSites,
                ];
            }
        }
        
        // Gather rooftop site data per project
        $rooftopDataByProject = [];
        foreach ($assignedProjects as $project) {
            if ($project->project_type == 0) {
                $projectTaskIds = Task::where('vendor_id', $vendor->id)
                    ->where('project_id', $project->id)
                    ->pluck('id');
                
                $rooftopSites = Site::whereIn('id', function($query) use ($projectTaskIds) {
                    $query->select('site_id')
                        ->from('tasks')
                        ->whereIn('id', $projectTaskIds);
                })
                ->with(['tasks' => function($q) use ($vendor, $project) {
                    $q->where('vendor_id', $vendor->id)
                      ->where('project_id', $project->id);
                }])
                ->get()
                ->map(function($site) {
                    return [
                        'id' => $site->id,
                        'site_name' => $site->site_name,
                        'breda_sl_no' => $site->breda_sl_no,
                        'location' => $site->location,
                        'district' => optional($site->districtRelation)->name ?? 'N/A',
                        'state' => optional($site->stateRelation)->name ?? 'N/A',
                        'installation_status' => $site->installation_status,
                        'commissioning_date' => $site->commissioning_date,
                        'task' => $site->tasks->first(),
                    ];
                });
                
                $rooftopDataByProject[$project->id] = [
                    'project' => $project,
                    'sites' => $rooftopSites,
                    'total_sites' => $rooftopSites->count(),
                    'completed_sites' => $rooftopSites->filter(function($site) {
                        return $site['task'] && $site['task']->status == TaskStatus::COMPLETED->value;
                    })->count(),
                ];
            }
        }
        
        // Gather inventory data per project
        $inventoryByProject = [];
        $allInventory = InventoryDispatch::where('vendor_id', $vendor->id)
            ->with(['project', 'store', 'streetlightPole'])
            ->get();
        
        foreach ($assignedProjects as $project) {
            $projectInventory = $allInventory->where('project_id', $project->id);
            
            // Group by item type
            $streetlightItems = ['battery', 'structure', 'luminary', 'panel'];
            $rooftopItems = ['meter', 'panel', 'wire', 'instruments'];
            
            $isStreetlight = $project->project_type == 1;
            $relevantItems = $isStreetlight ? $streetlightItems : $rooftopItems;
            
            $inventoryBreakdown = [];
            foreach ($projectInventory->groupBy('item') as $itemName => $items) {
                $itemLower = strtolower($itemName);
                $isRelevant = false;
                foreach ($relevantItems as $relevantItem) {
                    if (str_contains($itemLower, strtolower($relevantItem))) {
                        $isRelevant = true;
                        break;
                    }
                }
                
                if ($isRelevant || !$isStreetlight) {
                    $dispatched = $items->where('isDispatched', 1)->count();
                    $consumed = $items->where('is_consumed', 1)->count();
                    $inCustody = $items->where('isDispatched', 1)->where('is_consumed', 0)->count();
                    $totalValue = $items->sum('total_value');
                    
                    $inventoryBreakdown[] = [
                        'item_code' => $items->first()->item_code,
                        'item' => $itemName,
                        'make' => $items->first()->make,
                        'model' => $items->first()->model,
                        'dispatched' => $dispatched,
                        'consumed' => $consumed,
                        'in_custody' => $inCustody,
                        'total_value' => $totalValue,
                        'items' => $items,
                    ];
                }
            }
            
            // Calculate inventory values
            $inCustodyInventory = $projectInventory->where('isDispatched', 1)->where('is_consumed', 0);
            $consumedInventory = $projectInventory->where('is_consumed', 1);
            
            $valueInCustody = $inCustodyInventory->sum('total_value');
            $valueInstalled = $consumedInventory->sum('total_value');
            
            // Flatten all inventory items for consolidated table (keep as objects for easier relationship access)
            $allInventoryItems = $projectInventory;
            
            $inventoryByProject[$project->id] = [
                'project' => $project,
                'breakdown' => $inventoryBreakdown,
                'all_items' => $allInventoryItems,
                'total_dispatched' => $projectInventory->where('isDispatched', 1)->count(),
                'total_consumed' => $projectInventory->where('is_consumed', 1)->count(),
                'total_in_custody' => $projectInventory->where('isDispatched', 1)->where('is_consumed', 0)->count(),
                'total_value' => $projectInventory->sum('total_value'),
                'value_in_custody' => $valueInCustody,
                'value_installed' => $valueInstalled,
            ];
        }
        
        // Calculate earnings: ₹500 per installed pole (hardcoded for now)
        // Use pole.vendor_id instead of task.vendor_id for accurate tracking after reassignments
        $totalEarnings = 0;
        $earningsByProject = [];
        foreach ($assignedProjects as $project) {
            // For streetlight: calculate based on installed poles (₹500 per pole)
            if ($project->project_type == 1) {
                // Count installed poles where pole.vendor_id matches vendor (not task.vendor_id)
                // This ensures accurate earnings even after vendor reassignment
                $installedPoles = Pole::where('vendor_id', $vendor->id)
                    ->where('isInstallationDone', 1)
                    ->whereHas('task', function($query) use ($project) {
                        $query->where('project_id', $project->id);
                    })
                    ->count();
                
                $projectEarnings = $installedPoles * 500; // ₹500 per installed pole
                
                $earningsByProject[$project->id] = [
                    'project' => $project,
                    'earnings' => $projectEarnings,
                    'installed_poles' => $installedPoles,
                ];
            } else {
                // For rooftop: calculate based on completed sites
                $completedProjectTasks = $allTasks->where('project_id', $project->id)
                    ->where('status', TaskStatus::COMPLETED->value);
                $completedSites = $completedProjectTasks->count();
                $projectEarnings = $completedSites * ($project->rate ?? 0);
                
                $earningsByProject[$project->id] = [
                    'project' => $project,
                    'earnings' => $projectEarnings,
                    'completed_sites' => $completedSites,
                ];
            }
            
            $totalEarnings += $projectEarnings;
        }

        return view('uservendors.show', compact(
            'primaryProject',
            'assignedProjects',
            'tasksByProject',
            'vendor',
            'assignedTasks',
            'completedTasks',
            'pendingTasks',
            'rejectedTasks',
            'totalTasksCount',
            'completedTasksCount',
            'pendingTasksCount',
            'rejectedTasksCount',
            'streetlightDataByProject',
            'rooftopDataByProject',
            'inventoryByProject',
            'earningsByProject',
            'totalEarnings'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = Auth::user();
        $userRole = UserRole::fromValue($user->role);
        
        // Only Admin, Project Manager, and HR Manager can edit vendors
        if (!in_array($userRole, [UserRole::ADMIN, UserRole::PROJECT_MANAGER, UserRole::HR_MANAGER])) {
            abort(403, 'Unauthorized access');
        }
        
        $vendor = User::where('role', UserRole::VENDOR->value)->with('projects')->findOrFail($id);
        $projectEngineers = User::where('role', UserRole::PROJECT_MANAGER->value)->get();
        $projects = Project::all();
        $districts = City::orderBy('name')->get();

        // Get current primary project pivot (if any) to pre-select district in view
        $primaryProject = null;
        $primaryDistrictId = null;

        if ($vendor->project_id) {
            $primaryProject = $vendor->projects->firstWhere('id', $vendor->project_id);
        }

        if ($primaryProject && $primaryProject->pivot) {
            $primaryDistrictId = $primaryProject->pivot->district_id;
        }

        return view('uservendors.edit', compact('vendor', 'projects', 'projectEngineers', 'districts', 'primaryDistrictId'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = Auth::user();
        $userRole = UserRole::fromValue($user->role);
        
        // Only Admin, Project Manager, and HR Manager can update vendors
        if (!in_array($userRole, [UserRole::ADMIN, UserRole::PROJECT_MANAGER, UserRole::HR_MANAGER])) {
            abort(403, 'Unauthorized access');
        }
        
        $vendor = User::where('role', UserRole::VENDOR->value)->findOrFail($id);

        try {
            $validated = $request->validate([
                'project_id' => 'nullable|exists:projects,id',
                'district_id' => 'nullable|exists:cities,id',
                'manager_id' => 'nullable|exists:users,id',
                'name' => 'required|string|max:255',
                'firstName' => 'required|string|max:255',
                'lastName' => 'required|string|max:255',
                'contactNo' => 'nullable|string|max:255',
                'address' => 'nullable|string|max:255',
                'aadharNumber' => 'nullable|string|max:12',
                'pan' => 'nullable|string|max:10',
                'gstNumber' => 'nullable|string|max:15',
                'accountName' => 'nullable|string|max:255',
                'accountNumber' => 'nullable|string|max:255',
                'ifsc' => 'nullable|string|max:11',
                'bankName' => 'nullable|string|max:255',
                'branch' => 'nullable|string|max:255',
                'email' => 'required|email|unique:users,email,' . $id,
            ]);
            
            // district_id belongs to pivot, not users table
            $districtId = $validated['district_id'] ?? null;
            unset($validated['district_id']);

            $vendor->update($validated);
            
            // Sync to project_user pivot table if project_id is provided
            if (!empty($validated['project_id'])) {
                $vendor->assignToProject($validated['project_id'], $districtId);
            }
            
            return redirect()->route('uservendors.show', $id)->with('success', 'Vendor updated successfully.');
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
        $user = Auth::user();
        $userRole = UserRole::fromValue($user->role);
        
        // Only Admin, Project Manager, and HR Manager can delete vendors
        if (!in_array($userRole, [UserRole::ADMIN, UserRole::PROJECT_MANAGER, UserRole::HR_MANAGER])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }
        
        try {
            $vendor = User::where('role', UserRole::VENDOR->value)->findOrFail($id);
            $vendor->delete();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Bulk delete vendors.
     */
    public function bulkDelete(Request $request)
    {
        $user = Auth::user();
        $userRole = UserRole::fromValue($user->role);
        
        // Only Admin, Project Manager, and HR Manager can delete vendors
        if (!in_array($userRole, [UserRole::ADMIN, UserRole::PROJECT_MANAGER, UserRole::HR_MANAGER])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }
        
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|integer|exists:users,id',
        ]);
        
        try {
            $vendors = User::where('role', UserRole::VENDOR->value)
                ->whereIn('id', $validated['ids'])
                ->get();
            
            $deletedCount = 0;
            $errors = [];
            
            foreach ($vendors as $vendor) {
                try {
                    DB::beginTransaction();
                    
                    // Unassign active tasks instead of blocking deletion
                    $activeTasks = Task::where('vendor_id', $vendor->id)
                        ->whereIn('status', [TaskStatus::PENDING->value, TaskStatus::IN_PROGRESS->value])
                        ->get();
                    
                    foreach ($activeTasks as $task) {
                        $task->update(['vendor_id' => null]);
                    }
                    
                    $activeStreetlightTasks = StreetlightTask::where('vendor_id', $vendor->id)
                        ->whereIn('status', [TaskStatus::PENDING->value, TaskStatus::IN_PROGRESS->value])
                        ->get();
                    
                    foreach ($activeStreetlightTasks as $task) {
                        $task->update(['vendor_id' => null]);
                    }
                    
                    // Check for pending inventory dispatches (these should be handled separately)
                    $pendingDispatches = DB::table('inventory_dispatch')
                        ->where('vendor_id', $vendor->id)
                        ->where('isDispatched', 0)
                        ->count();
                    
                    if ($pendingDispatches > 0) {
                        // Cancel pending dispatches
                        DB::table('inventory_dispatch')
                            ->where('vendor_id', $vendor->id)
                            ->where('isDispatched', 0)
                            ->update(['vendor_id' => null]);
                    }
                    
                    // Remove from project_user pivot table
                    $vendor->projects()->detach();
                    
                    // Delete the vendor
                    $vendor->delete();
                    
                    DB::commit();
                    $deletedCount++;
                } catch (\Exception $e) {
                    DB::rollBack();
                    $errors[] = "Vendor {$vendor->name} deletion failed: " . $e->getMessage();
                }
            }
            
            if ($deletedCount > 0) {
                $message = "Successfully deleted {$deletedCount} vendor(s).";
                if (!empty($errors)) {
                    $message .= " " . implode(" ", $errors);
                }
                return response()->json([
                    'success' => true,
                    'message' => $message
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => !empty($errors) ? implode(" ", $errors) : 'No vendors were deleted.'
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting vendors: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk assign projects to vendors.
     */
    public function bulkAssignProjects(Request $request)
    {
        $user = Auth::user();
        $userRole = UserRole::fromValue($user->role);
        
        // Only Admin, Project Manager, and HR Manager can assign projects
        if (!in_array($userRole, [UserRole::ADMIN, UserRole::PROJECT_MANAGER, UserRole::HR_MANAGER])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }
        
        $validated = $request->validate([
            'vendor_ids' => 'required|array|min:1',
            'vendor_ids.*' => 'required|integer|exists:users,id',
            'project_ids' => 'required|array|min:1',
            'project_ids.*' => 'required|integer|exists:projects,id',
            'mode' => 'required|in:add,replace',
        ]);
        
        try {
            $vendors = User::where('role', UserRole::VENDOR->value)
                ->whereIn('id', $validated['vendor_ids'])
                ->get();
            
            if ($vendors->count() !== count($validated['vendor_ids'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Some selected users are not vendors.'
                ], 400);
            }
            
            $updatedCount = 0;
            
            foreach ($vendors as $vendor) {
                if ($validated['mode'] === 'add') {
                    // Add mode: Merge projects, avoid duplicates
                    foreach ($validated['project_ids'] as $projectId) {
                        $vendor->assignToProject($projectId);
                    }
                } else {
                    // Replace mode: Remove all existing, assign new
                    $vendor->replaceProjects($validated['project_ids']);
                }
                $updatedCount++;
            }
            
            $modeText = $validated['mode'] === 'add' ? 'added to' : 'assigned to';
            return response()->json([
                'success' => true,
                'message' => "Successfully {$modeText} {$updatedCount} vendor(s) to " . count($validated['project_ids']) . " project(s)."
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error assigning projects: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download import format template for vendors.
     */
    public function importFormat()
    {
        $headers = [
            'First Name',
            'Last Name',
            'Name',
            'Email',
            'Password (optional - will auto-generate if empty)',
            'Contact Number',
            'Address',
            'Project ID (optional)',
            'Project (optional - project name)',
            'Manager ID (optional)',
            'Manager (optional - manager name)',
            'Account Name',
            'Account Number',
            'IFSC',
            'Bank Name',
            'Branch',
            'PAN',
            'GST Number',
            'Aadhar Number',
        ];

        $filename = 'vendors_import_format_' . date('Y-m-d') . '.csv';

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

    public function uploadAvatar(Request $request, $id)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = User::findOrFail($id);

        // Generate unique filename: username_YYYYMMDD_HHMMSS.jpg
        $timestamp = \Carbon\Carbon::now()->format('Ymd_His');
        $filename = "{$user->username}_{$timestamp}.jpg";

        // Upload to S3 (path: users/avatar/{filename})
        $path = $request->file('image')->storeAs('users/avatar', $filename, 's3');

        // Save image path in the database
        $user->update(['image' => Storage::disk('s3')->url($path)]);

        return response()->json([
            'message' => 'Profile picture uploaded successfully',
            'image_url' => $user->image,
        ], 200);
    }
}
