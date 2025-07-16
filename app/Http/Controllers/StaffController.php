<?php

namespace App\Http\Controllers;

use App\Models\Pole;
use App\Models\Task;
use App\Models\StreetlightTask;
use App\Models\Project;
use App\Models\User;
use App\Models\UserCategory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use function Laravel\Prompts\confirm;

class StaffController extends Controller
{
    /**
     * Returns a list of all staff members.
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
        $projects = Project::all();
        $usercategories = UserCategory::all();
        return view('staff.create', compact('teamLeads', 'projects', 'usercategories'));
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        \Log::info('Received request staff data:', $request->all());
        // Validate the incoming data without requiring a username
        $validated = $request->validate([
            'manager_id' => 'nullable|exists:users,id',
            'firstName' => 'required|string',
            'lastName'  => 'required|string',
            'email'     => 'required|email|unique:users,email',
            'contactNo' => 'string',
            'role'      => 'string',
            'category'  => 'nullable|numeric',
            'address'   => 'string|max:255',
            'password'  => 'required|string|min:6|confirmed',
            'project_id' => 'required|exists:projects,id', // validate project_id
        ]);

        try {
            // Generate a random unique username
            $validated['username'] = $this->__generateUniqueUsername($validated['firstName']);
            $validated['password'] = bcrypt($validated['password']); // Hash password
            // Create the staff user
            $staff = User::create($validated);
            // Save to pivot table `project_user`
            DB::table('project_user')->insert([
                'user_id'    => $staff->id,
                'project_id' => $validated['project_id'],
                'role'       => $validated['role'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return redirect()->route('staff.index')
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
    public function show($id)
    {
        try {
            // Fetch the staff details along with relationships
            $staff = User::with(['projectManager', 'siteEngineers', 'vendors'])->findOrFail($id);
            $userId = $staff->id;
            // $vendors = User::where('role', 3)->where('manager_id', $id)->get();
            // $siteEngineers = User::where('role', 1)->where('manager_id', $id)->get();

            // Get the project_id of the staff
            $projectId = $staff->project_id;

            // Get the project and its type
            $project = Project::findOrFail($projectId);

            $surveyedPolesCount = 0;
            $installedPolesCount = 0;
            $surveyedPoles = 0;
            $installedPoles = 0;

            $isStreetlightProject = ($project->project_type == 1) ? true : false;
            // Check if the project type is 1 (indicating a streetlight project)
            if ($isStreetlightProject) {
                // Fetch StreetlightTasks (equivalent to Task in the streetlight project)
                $tasks = StreetlightTask::with(['site', 'poles'])
                    ->whereDate('created_at', Carbon::today())
                    ->where(function ($query) use ($staff) {
                        $query->where('engineer_id', $staff->id)
                            ->orWhere('manager_id', $staff->id)
                            ->orWhere('vendor_id', $staff->id);
                    })
                    ->get();
                $surveyedPolesCount = Pole::whereHas('task', function ($query) use ($userId) {
                    $query->where(function ($q) use ($userId) {
                        $q->where('manager_id', $userId)
                            ->orWhere('engineer_id', $userId)
                            ->orWhere('vendor_id', $userId);
                    });
                })->where('isSurveyDone', 1)->count();
                $installedPolesCount = Pole::whereHas('task', function ($query) use ($projectId, $userId) {
                    $query->where(function ($q) use ($userId) {
                        $q->where('manager_id', $userId)
                            ->orWhere('engineer_id', $userId)
                            ->orWhere('vendor_id', $userId);
                    });
                })->where('isInstallationDone', 1)->count();
                $surveyedPoles = Pole::where('isSurveyDone', 1)
                    ->whereDate('created_at', Carbon::today())
                    ->whereHas('task', function ($query) use ($userId) {
                        $query->where('manager_id', $userId);
                    })
                    ->get();
                // TODO: modify the m
                $installedPoles = Pole::where('isInstallationDone', 1)
                    ->whereDate('created_at', Carbon::today())
                    ->whereHas('task', function ($query) use ($userId) {
                        $query->where('manager_id', $userId);
                    })
                    ->get();
            } else {
                // Fetch regular Tasks
                $tasks = Task::with('site')
                    ->where(function ($query) use ($staff) {
                        $query->where('engineer_id', $staff->id)
                            ->orWhere('manager_id', $staff->id)
                            ->orWhere('vendor_id', $staff->id);
                    })
                    ->get();
            }

            // Categorize tasks
            $assignedTasks = $tasks;
            $assignedTasksCount = $tasks->count();
            Log::info($assignedTasks);
            $completedTasks = $tasks->where('status', 'Completed');
            $completedTasksCount = $completedTasks->count();
            $pendingTasks = $tasks->whereIn('status', ['Pending', 'In Progress']);
            $pendingTasksCount = $pendingTasks->count();
            $rejectedTasks = $tasks->where('status', 'Rejected');
            $rejectedTasksCount = $rejectedTasks->count();

            // Return the view with necessary data
            return view('staff.show', compact(
                'project',
                'staff',
                'assignedTasks',
                'completedTasks',
                'pendingTasks',
                'rejectedTasks',
                'surveyedPolesCount',
                'surveyedPoles',
                'isStreetlightProject',
                'installedPoles',
                'installedPolesCount',
                'assignedTasksCount',
                'completedTasksCount',
                'pendingTasksCount',
                'rejectedTasksCount',
            ));
        } catch (\Exception $e) {
            //throw $th;
            Log::info($e->getMessage());
            return back()->with('error');
        }
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
        $staff = User::findOrFail($id);
        $projects = Project::all();
        $projectEngineers = User::where('role',2)->get();
        $usercategory = DB::table('user_categories')
                ->select(DB::raw('MIN(id) as id'), 'category_code')
                ->groupBy('category_code')
                ->get();

        return view('staff.edit', compact('staff', 'projects', 'usercategory', 'projectEngineers')); // Form to edit staff
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $staff)
    {
        //
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'manager_id' => 'required|numeric',
            'project_id' => 'nullable|integer',
            'firstName' => 'nullable|string|max:25',
            'lastName'  => 'nullable|string|max:25',
            'email'     => 'required|email|unique:users,email,' . $staff->id,
            'contactNo' => 'string',
            'category'  => 'nullable|string',
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



    public function updateProfile($id)
    {
        $user = User::findOrFail($id);
        return view('staff.profile', compact('user'));
    }

    // Update Profile Picture
    public function updateProfilePicture(Request $request)
    {
        $request->validate([
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg,gif,heic,heif|max:2048',
        ]);

        $user = Auth::user();

        try {
            if (!$request->hasFile('profile_picture') || !$request->file('profile_picture')->isValid()) {
                \Log::error('Invalid file upload attempt');
                return redirect()->back()->with('error', 'Invalid file upload. Please try again.');
            }

            $file = $request->file('profile_picture');

            \Log::info('File upload attempt', [
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize()
            ]);

            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

            // Attempt S3 upload
            $imagePath = Storage::disk('s3')->putFileAs('profile_pictures', $file, $filename);

            if (!$imagePath) {
                throw new \Exception('S3 upload failed.');
            }

            $imageUrl = Storage::disk('s3')->url($imagePath);

            \Log::info('Profile picture uploaded to S3', [
                'user_id' => $user->id,
                'path' => $imagePath,
                'url' => $imageUrl
            ]);

            // Save image URL to user record
            $user->image = $imageUrl;
            $user->save();
            Log::info(('Profile picture URL saved to user record'), [
                'user_id' => $user->id,
                'image_url' => $imageUrl
            ]);

            return redirect()->back()->with('success', 'Profile picture updated successfully!');
        } catch (\Exception $e) {
            \Log::error('Profile picture update error: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->with('error', 'Failed to update profile picture: ' . $e->getMessage());
        }
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

        User::where('id', $id)->update([
            'password' => bcrypt($request->password)
        ]);


        // $staff           = User::findOrFail($id);
        // $staff->password = $request->password;
        // Log::info('Update staff password ' . $request->password);
        // $staff->save();

        return redirect()->route('staff.index')->with('success', 'Password updated successfully.');
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

    public function showSurveyedPoles()
    {
        $surveyedPoles = Pole::all();
        return view('staff.surveyedPoles', compact('surveyedPoles'));
    }

   public function vendorData($id)
    {
        $managerid = $id;

        // Get distinct vendor IDs for this manager
        $vendorids = StreetlightTask::where('manager_id', $managerid)
            ->whereNotNull('vendor_id')
            ->pluck('vendor_id')
            ->unique();

        $vendorPoleCounts = [];

        foreach ($vendorids as $vendorId) {
            // Get all tasks for this vendor under this manager
            $tasks = StreetlightTask::where('manager_id', $managerid)
                ->where('vendor_id', $vendorId)
                ->with('site', 'vendor')
                ->get();

            $taskIds = $tasks->pluck('id');

            // Get pole counts (survey/install) from pole table
            $surveyCount = Pole::whereIn('task_id', $taskIds)->where('isSurveyDone', 1)->count();
            $installCount = Pole::whereIn('task_id', $taskIds)->where('isInstallationDone', 1)->count();

            // Sum total poles from Streetlight.site model
            $totalPoles = $tasks->reduce(function ($carry, $task) {
                return $carry + ($task->site)->total_poles ?? 0;
            }, 0);

            // Optional: fetch vendor name if needed in view
            $vendor = optional($tasks->first()->vendor);
            $vendorName = trim(($vendor->firstName ?? '') . ' ' . ($vendor->lastName ?? ''));
            // Add to final array
            $vendorPoleCounts[$vendorId] = [
                'id'           => $vendorId,
                'vendor_name'  => $vendorName,
                'survey'       => $surveyCount,
                'install'      => $installCount,
                'tasks'        => $tasks->count(),
                'total_poles'  => $totalPoles,
            ];
            // === Today's Data ===
            $today = now()->toDateString();
            $todayTasks = $tasks->filter(function ($task) use ($today) {
                return $task->start_date === $today || $task->end_date === $today;
            });

            $todayTaskIds = $todayTasks->pluck('id');

            $todaySurvey = Pole::whereIn('task_id', $todayTaskIds)
                ->where('isSurveyDone', 1)->count();

            $todayInstall = Pole::whereIn('task_id', $todayTaskIds)
                ->where('isInstallationDone', 1)->count();


            $todayTargetTasks = $tasks->filter(function ($task) use ($today) {
                return $task->end_date >= $today;
            });

            $todayTotalPoles = $tasks->reduce(function ($carry, $task) use ($today) {
                // Only count if end_date is today or in future
                if ($task->end_date >= $today) {
                    return $carry + (optional($task->site)->total_poles ?? 0);
                }
                return $carry;
            }, 0);
            $backLogPoles = $tasks->reduce(function ($carry, $task) use ($today) {
                // Only count if end_date is today or in future
                if ($task->end_date < $today) {
                    return $carry + (optional($task->site)->total_poles ?? 0);
                }
                return $carry;
            }, 0);

            $backlogSites = $tasks->filter(function ($task) use ($today) {
                return $task->end_date < $today && $task->site;
            })->map(function ($task) {
                return $task->site;
            })->unique('id')->values();

            $vendorPoleCountsToday[$vendorId] = [
                'vendor_name'  => $vendorName,
                'survey'       => $todaySurvey,
                'install'      => $todayInstall,
                'tasks'        => $todayTasks->count(),
                'total_poles'  => $totalPoles,
                'today_target' => $todayTotalPoles,
                'backlog'      => $backLogPoles,
                'backlog_sites' => $backlogSites,
            ];
        }

        return view('vendor', compact('vendorids', 'vendorPoleCounts', 'vendorPoleCountsToday'));
    }

    public function engineerData($id)
    {
        $managerid = $id;

        // Get distinct engineer IDs for this manager
        $engineerids = StreetlightTask::where('manager_id', $managerid)
            ->whereNotNull('engineer_id')
            ->pluck('engineer_id')
            ->unique();

        $engineerPoleCounts = [];

        foreach ($engineerids as $engineerId) {
            // Get all tasks for this engineer under this manager
            $tasks = StreetlightTask::where('manager_id', $managerid)
                ->where('engineer_id', $engineerId)
                ->with('site', 'engineer')
                ->get();

            $taskIds = $tasks->pluck('id');

            // Get pole counts (survey/install) from pole table
            $surveyCount = Pole::whereIn('task_id', $taskIds)->where('isSurveyDone', 1)->count();
            $installCount = Pole::whereIn('task_id', $taskIds)->where('isInstallationDone', 1)->count();

            // Sum total poles from Streetlight.site model
            $totalPoles = $tasks->reduce(function ($carry, $task) {
                return $carry + ($task->site)->total_poles ?? 0;
            }, 0);

            // Optional: fetch engineer name if needed in view
            $engineer = optional($tasks->first()->engineer);
            $engineerName = trim(($engineer->firstName ?? '') . ' ' . ($engineer->lastName ?? ''));

            // Add to final array
            $engineerPoleCounts[$engineerId] = [
                'id'           => $engineerId,
                'engineer_name'  => $engineerName,
                'survey'       => $surveyCount,
                'install'      => $installCount,
                'tasks'        => $tasks->count(),
                'total_poles'  => $totalPoles,
            ];

            // === Today's Data ===
            $today = now()->toDateString();
            $todayTasks = $tasks->filter(function ($task) use ($today) {
                return $task->start_date === $today || $task->end_date === $today;
            });

            $todayTaskIds = $todayTasks->pluck('id');

            $todaySurvey = Pole::whereIn('task_id', $todayTaskIds)
                ->where('isSurveyDone', 1)->count();

            $todayInstall = Pole::whereIn('task_id', $todayTaskIds)
                ->where('isInstallationDone', 1)->count();

            $todayTargetTasks = $tasks->filter(function ($task) use ($today) {
                return $task->end_date >= $today;
            });

            $todayTotalPoles = $tasks->reduce(function ($carry, $task) use ($today) {
                // Only count if end_date is today or in future
                if ($task->end_date >= $today) {
                    return $carry + (optional($task->site)->total_poles ?? 0);
                }
                return $carry;
            }, 0);

            $backLogPoles = $tasks->reduce(function ($carry, $task) use ($today) {
                // Only count if end_date is in the past
                if ($task->end_date < $today) {
                    return $carry + (optional($task->site)->total_poles ?? 0);
                }
                return $carry;
            }, 0);

            $backlogSites = $tasks->filter(function ($task) use ($today) {
                return $task->end_date < $today && $task->site;
            })->map(function ($task) {
                return $task->site;
            })->unique('id')->values();

            $engineerPoleCountsToday[$engineerId] = [
                'engineer_name'  => $engineerName,
                'survey'         => $todaySurvey,
                'install'        => $todayInstall,
                'tasks'          => $todayTasks->count(),
                'total_poles'    => $totalPoles,
                'today_target'   => $todayTotalPoles,
                'backlog'        => $backLogPoles,
                'backlog_sites'  => $backlogSites,
            ];
        }

        return view('engineer', compact('engineerids', 'engineerPoleCounts', 'engineerPoleCountsToday'));
    }
}
