<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Project;
use App\Models\StreetlightTask;
use App\Models\Task;
use App\Models\User;
use App\Traits\GeneratesUniqueUsername;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    use GeneratesUniqueUsername;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $vendors = User::where('role', UserRole::VENDOR->value)->get();
        return view('uservendors.index', compact('vendors'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $siteEngineers = User::where('role', UserRole::PROJECT_MANAGER->value)->get();
        $projects = Project::all();
        return view('uservendors.create', compact('siteEngineers', 'projects'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'firstName' => 'required|string',
            'manager_id' => 'nullable|exists:users,id',
            'project_id' => 'nullable|exists:projects,id',
            'lastName' => 'required|string',
            'contactPerson' => 'string',
            'contactNo' => 'string',
            'address' => 'string|max:255',
            'aadharNumber' => 'string',
            'pan' => 'string|max:10',
            'gstNumber' => 'nullable|string|max:15',
            'accountName' => 'string',
            'accountNumber' => 'string',
            'ifscCode' => 'string|max:11',
            'bankName' => 'string',
            'branch' => 'string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        try {
            $validated['username'] = $this->generateUniqueUsername($validated['name']);
            $validated['password'] = bcrypt($validated['password']);
            $validated['role'] = UserRole::VENDOR->value;
            $vendor = User::create($validated);

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
        $vendor = User::where('role', UserRole::VENDOR->value)
            ->with(['siteEngineer', 'projects'])
            ->findOrFail($id);

        $projectId = $vendor->project_id;
        $project = Project::findOrFail($projectId);

        if ($project->project_type == 1) {
            $tasks = StreetlightTask::with('site')
                ->where('vendor_id', $vendor->id)
                ->get();
        } else {
            $tasks = Task::with('site')
                ->where('vendor_id', $vendor->id)
                ->get();
        }

        $assignedTasks = $tasks;
        $assignedTasksCount = $tasks->count();
        $completedTasks = $tasks->where('status', 'Completed');
        $completedTasksCount = $completedTasks->count();
        $pendingTasks = $tasks->whereIn('status', ['Pending', 'In Progress']);
        $pendingTasksCount = $pendingTasks->count();
        $rejectedTasks = $tasks->where('status', 'Rejected');
        $rejectedTasksCount = $rejectedTasks->count();

        return view('uservendors.show', compact(
            'project',
            'vendor',
            'assignedTasks',
            'completedTasks',
            'pendingTasks',
            'rejectedTasks',
            'assignedTasksCount',
            'completedTasksCount',
            'pendingTasksCount',
            'rejectedTasksCount'

        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $vendor = User::find($id);
        $projectEngineers = User::where('role', UserRole::PROJECT_MANAGER->value)->get();
        $projects = Project::all();
        return view('uservendors.edit', compact('vendor', 'projects', 'projectEngineers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        try {
            $validated = $request->validate([
                'project_id' => 'required|numeric',
                'manager_id' => 'required|numeric',
                'name' => 'required|string|max:255',
                'firstName' => 'required|string',
                'lastName' => 'required|string',
                'contactPerson' => 'string',
                'contactNo' => 'string',
                'address' => 'string|max:255',
                'aadharNumber' => 'string|max:12',
                'pan' => 'string|max:10',
                'gstNumber' => 'nullable|string|max:15',
                'accountName' => 'string',
                'accountNumber' => 'string',
                'ifscCode' => 'string|max:11',
                'bankName' => 'string',
                'branch' => 'string',
                'email' => 'required|email',
            ]);
            $vendor = User::find($id)->update($validated);
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
        try {
            $vendor = User::findOrFail($id);
            $vendor->delete();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
