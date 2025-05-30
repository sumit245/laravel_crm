<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\InventoryDispatch;
use App\Models\InventroyStreetLightModel;
use App\Models\Project;
use App\Models\State;
use App\Models\Streetlight;
use App\Models\StreetlightTask;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
    public function __construct(public Project $project) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $projects = Project::all();
        return view('projects.index', compact('projects'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        $states = State::all();
        return view('projects.create', compact('states'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the incoming data without requiring a username
        $validated = $request->validate([
            'project_type' => 'required|in:0,1',
            'project_name'      => 'required|string',
            'project_in_state'  => 'string',
            'start_date'        => 'required|date',
            'end_date'          => 'required|date',
            'work_order_number' => 'required|string|unique:projects',
            'rate'              => 'nullable|numeric',
            'project_capacity'  => 'nullable|string',
            'total'             => 'numeric',
            'description'       => 'string',
            'agreement_number' => 'nullable|string|required_if:project_type,1',
            'agreement_date' => 'nullable|date|required_if:project_type,1',
        ]);

        try {
            $project = Project::create($validated);

            return redirect()->route('projects.show', $project->id)
                ->with('success', 'Project created successfully.');
        } catch (\Exception $e) {
            // Catch database or other errors
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
        $user = auth()->user(); // Get the logged-in user

        $isAdmin = $user->role == 0; // Admin check
        $isProjectManager = $user->role == 2; // Project Manager check

        // Get users except admin (role = 0) and vendors (role = 3)
        $users = User::whereNotIn('role', [0, 3])->get();

        // Fetch inventory items based on project type
        $inventoryModel = ($project->project_type == 1) ? InventroyStreetLightModel::class : Inventory::class;

        // Consolidated inventory items for streetlight projects
        if ($project->project_type == 1) {
            // Group by item name and sum quantities
            $inventoryItems = $inventoryModel::where('project_id', $project->id)
                ->select(
                    'item_code',
                    'item',
                    DB::raw('SUM(quantity) as total_quantity'),
                    DB::raw('MAX(rate) as rate'),  // Use MAX or AVG to avoid conflict
                    DB::raw('SUM(quantity * rate) as total_value'),
                    DB::raw('MAX(make) as make'),  // Use MAX or AVG to avoid conflict
                    DB::raw('MAX(model) as model')  // Use MAX or AVG to avoid conflict  
                )
                ->groupBy('item_code', 'item')
                ->get();
            Log::info($inventoryItems);
        } else {
            $inventoryItems = $inventoryModel::where('project_id', $project->id)->get();
        }

        // Calculate Stock Values
        $initialStockValue = 0;
        $dispatchedStockValue = 0;
        $inStoreStockValue = 0;
        if ($project->project_type == 1) {
            $initialStockValue = $inventoryModel::where('project_id', $project->id)->sum(DB::raw('rate * quantity'));
            $dispatchedStockValue = InventoryDispatch::where('project_id', $project->id)
                ->sum('total_value');

            $inStoreStockValue = (float)$initialStockValue - $dispatchedStockValue;
        }

        // Get engineers and vendors for this project
        $engineers = User::where('role', 1)->get();
        $vendors = User::where('role', 3)->get();
        $state = State::where('id', $project->project_in_state)->get();
        // $inventoryItems = Inventory::where('project_id', $project->id)->get();

        // Get assigned engineers based on manager_id for Project Managers
        $assignedEngineers = User::whereIn('role', [1, 2])
            ->where('project_id', $project->id)
            ->whereIn('id', function ($query) use ($project) {
                $query->select('user_id')->from('project_user');
            })
            ->when($isProjectManager, fn($q) => $q->where('manager_id', $user->id)) // Filter by manager_id
            ->get();

        // Fetch available engineers whose project_id matches the current project
        $availableEngineers = User::whereIn('role', [1, 2, 4, 5])
            ->where('project_id', $project->id)
            ->whereNotIn('id', $assignedEngineers->pluck('id'))
            ->when($isProjectManager, fn($q) => $q->where('manager_id', $user->id)) // Filter by manager_id
            ->get();

        // Get assigned vendors based on manager_id for Project Managers
        $assignedVendors = User::where('role', 3)
            ->where('project_id', $project->id)
            ->whereIn('id', function ($query) use ($project) {
                $query->select('user_id')->from('project_user');
            })
            ->when($isProjectManager, fn($q) => $q->where('manager_id', $user->id)) // Filter by manager_id
            ->get();

        // Fetch available vendors whose project_id matches the current project
        $availableVendors = User::where('role', 3)
            ->where('project_id', $project->id)
            ->whereNotIn('id', $assignedVendors->pluck('id'))
            ->when($isProjectManager, fn($q) => $q->where('manager_id', $user->id)) // Filter by manager_id
            ->get();

        // If no site engineers or project managers are assigned
        $assignedEngineersMessage = $assignedEngineers->isEmpty() ? "No engineers assigned." : null;
        // Get distinct districts from Streetlights (direct column)
        $streetlightDistricts = Streetlight::where('project_id', $id)
            ->select('district')
            ->distinct()
            ->get();

        $data = [
            'project'       => $project,
            'state'         => $state,
            'inventoryItems' => $inventoryItems,
            'users'         => $users,
            'engineers'     => $engineers,
            'vendors' => $vendors,
            'assignedEngineers' => $assignedEngineers,
            'availableEngineers' => $availableEngineers,
            'assignedEngineersMessage' => $assignedEngineersMessage,
            'assignedVendors'  => $assignedVendors,
            'availableVendors' => $availableVendors,
            'initialStockValue' => $initialStockValue,
            'inStoreStockValue' => $inStoreStockValue,
            'dispatchedStockValue' => $dispatchedStockValue
        ];

        if ($project->project_type == 1) {
            // Streetlight installation - Filtered by manager_id**
            $data['sites'] = Streetlight::where('project_id', $id)->get();
            $data['districts'] = Streetlight::where('project_id', $id)->select('district')->distinct()->get();
            $data['targets'] = StreetlightTask::where('project_id', $project->id)
                ->when($isProjectManager, fn($q) => $q->where('manager_id', $user->id))
                ->with('site', 'engineer')
                // Added order by Desc 'Y'
                ->orderBy('created_at', 'desc')
                ->get();

            // Replace these lines in the show method
            $data['totalPoles'] = Streetlight::where('project_id', $project->id)->sum('total_poles');
            $data['totalSurveyedPoles'] = Streetlight::where('project_id', $project->id)->sum('number_of_surveyed_poles');
            $data['totalInstalledPoles'] = Streetlight::where('project_id', $project->id)->sum('number_of_installed_poles');
        } else {
            // Rooftop installation - Filtered by manager_id**
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
        $state   = State::where('id', $project->project_in_state)->get();

        return view('projects.edit', compact('project', 'state'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project)
    {
        try {

            // Validate the incoming data without requiring a username
            $validated = $request->validate([
                'project_name'      => 'required|string',
                'project_in_state'  => 'string',
                'start_date'        => 'required|date',
                'end_date'          => 'required|date',
                'work_order_number' => 'required',
                'rate'              => 'nullable|string',
                'project_capacity'  => 'nullable|string',
                'total'             => 'string',
                'description'       => 'string',
            ]);
            $project->update($validated);
            return redirect()->route('projects.show', compact('project'))
                ->with('success', 'Inventory updated successfully.');
        } catch (\Exception $e) {
            // Catch database or other errors
            $errorMessage = $e->getMessage();

            return redirect()->back()
                ->withErrors(['error' => $errorMessage])
                ->withInput();
        }
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        try {
            $project = Project::findOrFail($id);
            $project->delete();
            return response()->json(['message' => 'Project deleted']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()]);
        }
    }

    public function assignUsers(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);
        $validated = $request->validate([
            'user_ids'   => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);
        Log::info($request->all());

        // Sync users to the project (removing unselected ones)
        $project->users()->syncWithoutDetaching($validated['user_ids']);
        return redirect()->back()->with('success', 'Users assigned successfully');
    }

    // Delete controller
    public function destroyTarget($id)
    {
        $task = StreetlightTask::findOrFail($id);
        $task->delete(); // Permanently deletes from DB

        return redirect()->back()->with('success', 'Task permanently deleted.');
    }
}
