<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\StreetlightTask;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VendorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $vendors = User::where('role', 3)->get();
        return view('uservendors.index', compact('vendors'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $siteEngineers = User::where('role', 2)->get();
        $projects = Project::all();
        return view('uservendors.create', compact('siteEngineers', 'projects'));
    }

    /**
     * Generate a unique username based on the user's name.
     *
     * @param string $name
     * @return string
     */
    private function __generateUniqueUsername($name)
    {
        $baseUsername = strtolower(preg_replace('/\s+/', '', $name)); // Remove spaces and make lowercase
        $randomSuffix = mt_rand(1000, 9999); // Generate a random 4-digit number
        $username     = $baseUsername . $randomSuffix;

        // Ensure the username is unique
        while (User::where('username', $username)->exists()) {
            $randomSuffix = mt_rand(1000, 9999); // Generate a new random suffix if it exists
            $username     = $baseUsername . $randomSuffix;
        }

        return $username;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Log::info('Received request vendor data:', $request->all());
        // Validate the incoming data without requiring a username
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'firstName'     => 'required|string',
            'manager_id'    => 'nullable|exists:users,id',
            'project_id'    => 'nullable|exists:projects,id',
            'lastName'      => 'required|string',
            'contactPerson' => 'string',
            'contactNo'     => 'string',
            'address'       => 'string|max:255',
            'aadharNumber'  => 'string',
            'pan'     => 'string|max:10',
            'gstNumber'     => 'nullable|string|max:15',
            'accountName'   => 'string',
            'accountNumber' => 'string',
            'ifscCode'      => 'string|max:11',
            'bankName'      => 'string',
            'branch'        => 'string',
            'email'         => 'required|email|unique:users,email',
            'password'      => 'required|string|min:6|confirmed',
        ]);

        try {
            // Generate a random unique username
            $validated['username'] = $this->__generateUniqueUsername($validated['name']);
            $validated['password'] = bcrypt($validated['password']); // Hash
            $validated['role']     = 3;
            // Create the staff user
            // Log::info('Creating vendor: ' . $validated['project_id']);
            $vendor = User::create($validated);
            Log::info('Vendor created successfully: ' . $vendor->name);
            return redirect()->route('uservendors.index', $vendor->id)
                ->with('success', 'Vendor created successfully.');
        } catch (\Exception $e) {
            // Catch database or other errors
            $errorMessage = $e->getMessage();
            Log::info('Error creating vendor: ' . $errorMessage);

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
        $vendor = User::where('role', 3) // Ensure it's a vendor
            ->with(['siteEngineer', 'projects']) // Load relationships if needed
            ->findOrFail($id);

        // Get the project_id of the staff
        $projectId = $vendor->project_id;

        // Get the project and its type
        $project = Project::findOrFail($projectId);

        // Check if the project type is 1 (indicating a streetlight project)
        if ($project->project_type == 1) {
            // Fetch StreetlightTasks (equivalent to Task in the streetlight project)
            $tasks = StreetlightTask::with('site')
                ->where(function ($query) use ($vendor) {
                    $query->where('vendor_id', $vendor->id);
                })
                ->get();
        } else {
            // Fetch regular Tasks
            $tasks = Task::with('site')
                ->where(function ($query) use ($vendor) {
                    $query->where('vendor_id', $vendor->id);
                })
                ->get();
        }

        // // Fetch tasks assigned to the vendor
        // $tasks = Task::with('site')
        //     ->where('vendor_id', $vendor->id)
        //     ->get();

        // Categorize tasks
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
        //
        $vendor = User::find($id);
        $projects = Project::all();
        return view('uservendors.edit', compact('vendor', 'projects'));
    }

    // This is only for viewing the page both these method are not in use made a common method
    // in staff controller app
    // public function changePassword(Request $request, $id){
    //     $vendor = User::findOrFail($id);
    //     return view('uservendors.change-password', compact('vendor'));
    // }
    // Update the vendor's password
    // public function updatePassword(Request $request, $id)
    // {
    //     $request->validate([
    //         'password' => 'required|min:8|confirmed',
    //     ]);

    //     User::where('id', $id)->update([
    //         'password' => bcrypt($request->password),
    //     ]);

    //     return redirect()->route('uservendors.index')->with('success', 'Password updated successfully.');
    // }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        try {
            $validated = $request->validate([
                'name'          => 'required|string|max:255',
                'firstName'     => 'required|string',
                'lastName'      => 'required|string',
                'contactPerson' => 'string',
                'contactNo'     => 'string',
                'address'       => 'string|max:255',
                'aadharNumber'  => 'string|max:12',
                'pan'     => 'string|max:10',
                'gstNumber'     => 'nullable|string|max:15',
                'accountName'   => 'string',
                'accountNumber' => 'string',
                'ifscCode'      => 'string|max:11',
                'bankName'      => 'string',
                'branch'        => 'string',
                'email'         => 'required|email',
            ]);
            $vendor = User::find($id)->update($validated);
            Log::info('Vendor Edit' . $vendor);
            return redirect()->route('uservendors.show', $id)->with('success', 'Vendor updated successfully.');
        } catch (\Exception $e) {
            // Catch database or other errors
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
        //
        try {
            $vendor = User::findOrFail($id);
            $vendor->delete();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
