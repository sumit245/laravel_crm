<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
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
use Maatwebsite\Excel\Facades\Excel;

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
        $states = State::all();
        return view('projects.create', compact('states'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
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

        $assignedEngineers = User::whereIn('role', [UserRole::SITE_ENGINEER->value, UserRole::PROJECT_MANAGER->value])
            ->where('project_id', $project->id)
            ->whereIn('id', function ($query) use ($project) {
                $query->select('user_id')->from('project_user');
            })
            ->when($isProjectManager, fn($q) => $q->where('manager_id', $user->id))
            ->get();

        $availableEngineers = User::whereIn('role', [
            UserRole::SITE_ENGINEER->value,
            UserRole::PROJECT_MANAGER->value,
            UserRole::STORE_INCHARGE->value,
            UserRole::COORDINATOR->value
        ])
            ->where('project_id', $project->id)
            ->whereNotIn('id', $assignedEngineers->pluck('id'))
            ->when($isProjectManager, fn($q) => $q->where('manager_id', $user->id))
            ->get();

        $assignedVendors = User::where('role', UserRole::VENDOR->value)
            ->where('project_id', $project->id)
            ->whereIn('id', function ($query) use ($project) {
                $query->select('user_id')->from('project_user');
            })
            ->when($isProjectManager, fn($q) => $q->where('manager_id', $user->id))
            ->get();

        $availableVendors = User::where('role', UserRole::VENDOR->value)
            ->where('project_id', $project->id)
            ->whereNotIn('id', $assignedVendors->pluck('id'))
            ->when($isProjectManager, fn($q) => $q->where('manager_id', $user->id))
            ->get();

        $assignedEngineersMessage = $assignedEngineers->isEmpty() ? "No engineers assigned." : null;
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
            'initialStockValue' => $initialStockValue,
            'inStoreStockValue' => $inStoreStockValue,
            'dispatchedStockValue' => $dispatchedStockValue
        ];

        if ($project->project_type == 1) {
            $data['sites'] = Streetlight::where('project_id', $id)->get();
            $data['districts'] = Streetlight::where('project_id', $id)->select('district')->distinct()->get();
            $data['targets'] = StreetlightTask::where('project_id', $project->id)
                ->when($isProjectManager, fn($q) => $q->where('manager_id', $user->id))
                ->with('site', 'engineer')
                ->orderBy('created_at', 'desc')
                ->get();

            $data['totalPoles'] = Streetlight::where('project_id', $project->id)->sum('total_poles');
            $data['totalSurveyedPoles'] = Streetlight::where('project_id', $project->id)->sum('number_of_surveyed_poles');
            $data['totalInstalledPoles'] = Streetlight::where('project_id', $project->id)->sum('number_of_installed_poles');
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
        $state = State::where('id', $project->project_in_state)->get();

        return view('projects.edit', compact('project', 'state'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project)
    {
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

    public function assignUsers(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);
        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);
        $project->users()->syncWithoutDetaching($validated['user_ids']);
        return redirect()->back()->with('success', 'Users assigned successfully');
    }

    public function destroyTarget($id)
    {
        $task = StreetlightTask::findOrFail($id);
        $task->delete();

        return redirect()->back()->with('success', 'Task permanently deleted.');
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
