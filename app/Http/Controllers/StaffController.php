<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;


class StaffController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $today = Carbon::today();
        $staff = User::whereIn('role', [1, 2, 4, 5])->get();
        $staff->map(function ($staff) use ($today) {
            // Total Tasks
            $staff->totalTasks = Task::where('engineer_id', $staff->id)->count();

            // Task counts by status
            $staff->pendingTasks = Task::where('engineer_id', $staff->id)->where('status', 'Pending')->count();
            $staff->inProgressTasks = Task::where('engineer_id', $staff->id)->where('status', 'In Progress')->count();
            $staff->completedTasks = Task::where('engineer_id', $staff->id)->where('status', 'Done')->count();

            // Today's Performance
            $staff->tasksAssignedToday = Task::where('engineer_id', $staff->id)->whereDate('created_at', $today)->count();
            $staff->tasksCompletedToday = Task::where('engineer_id', $staff->id)->whereDate('updated_at', $today)->where('status', 'Done')->count();

            return $staff;
        });
        return view('staff.index', compact('staff'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $teamLeads = User::where('role', 2)->get();
        return view('staff.create', compact('teamLeads'));
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
        // Validate the incoming data without requiring a username
        $validated = $request->validate([
            'manager_id' => 'nullable|exists:users,id',
            'firstName' => 'required|string',
            'lastName'  => 'required|string',
            'email'     => 'required|email|unique:users,email',
            'contactNo' => 'string',
            'role'      => 'string',
            'address'   => 'string|max:255',
            'password'  => 'required|string|min:6|confirmed',
        ]);

        try {
            // Generate a random unique username
            $validated['username'] = $this->__generateUniqueUsername($validated['firstName']);
            $validated['password'] = bcrypt($validated['password']); // Hash password
            // Create the staff user
            $staff = User::create($validated);

            return redirect()->route('staff.show', $staff->id)
                ->with('success', 'Staff created successfully.');
        } catch (\Exception $e) {
            // Catch database or other errors
            $errorMessage = $e->getMessage();

            return redirect()->back()
                ->withErrors(['error' => $errorMessage])
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $staff = User::findOrFail($id);
        return view('staff.show', compact('staff'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
        $staff = User::findOrFail($id);
        return view('staff.edit', compact('staff')); // Form to edit staff
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $staff)
    {
        //
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'firstName' => 'nullable|string|max:25',
            'lastName'  => 'nullable|string|max:25',
            'email'     => 'required|email|unique:users,email,' . $staff->id,
            'contactNo' => 'string',
            'address'   => 'string|max:255',
            'password'  => 'nullable|string|min:6|confirmed',
            'role'      => 'nullable|string',

        ]);

        if ($request->password) {
            $validated['password'] = bcrypt($validated['password']);
        }

        $staff->update($validated);

        return redirect()->route('staff.show', compact('staff'))->with('success', 'Staff updated successfully.');
    }

    public function changePassword($id)
    {
        $staff = User::findOrFail($id);
        return view('staff.change-password', compact('staff'));
    }

    public function updatePassword(Request $request, $id)
    {
        $request->validate([
            'password' => 'required|min:8|confirmed',
        ]);

        $staff           = User::findOrFail($id);
        $staff->password = bcrypt($request->password);
        $staff->save();

        return redirect()->route('staff.index')->with('success', 'Password updated successfully.');
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $staff)
    {
        //
        try {
            $staff->delete();
            return response()->json(['success' => true, 'message' => 'Staff deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to delete staff member.'], 500);
        }
    }
}
