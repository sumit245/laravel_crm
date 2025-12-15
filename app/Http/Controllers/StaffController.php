<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Pole;
use App\Models\Task;
use App\Models\StreetlightTask;
use App\Models\Project;
use App\Models\User;
use App\Models\UserCategory;
use App\Traits\GeneratesUniqueUsername;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use function Laravel\Prompts\confirm;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\StaffImport;

class StaffController extends Controller
{
    use GeneratesUniqueUsername;
    /**
     * Returns a list of all staff members.
     */
    public function index()
    {
        $today = Carbon::today();
        $staff = User::whereIn('role', [
            UserRole::SITE_ENGINEER->value,
            UserRole::PROJECT_MANAGER->value,
            UserRole::STORE_INCHARGE->value,
            UserRole::COORDINATOR->value
        ])->get();
        $staff->map(function ($staff) use ($today) {
            $staff->totalTasks = Task::where('engineer_id', $staff->id)->count();
            $staff->pendingTasks = Task::where('engineer_id', $staff->id)->where('status', 'Pending')->count();
            $staff->inProgressTasks = Task::where('engineer_id', $staff->id)->where('status', 'In Progress')->count();
            $staff->completedTasks = Task::where('engineer_id', $staff->id)->where('status', 'Done')->count();
            $staff->tasksAssignedToday = Task::where('engineer_id', $staff->id)->whereDate('created_at', $today)->count();
            $staff->tasksCompletedToday = Task::where('engineer_id', $staff->id)->whereDate('updated_at', $today)->where('status', 'Done')->count();

            return $staff;
        });
        return view('staff.index', compact('staff'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls,txt',
        ]);

        $file = $request->file('file');

        try {
            $import = new StaffImport();
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
        $teamLeads = User::where('role', UserRole::PROJECT_MANAGER->value)->get();
        $projects = Project::all();
        $usercategories = UserCategory::all();
        return view('staff.create', compact('teamLeads', 'projects', 'usercategories'));
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'manager_id' => 'nullable|exists:users,id',
            'firstName' => 'required|string',
            'lastName' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'contactNo' => 'string',
            'role' => 'string',
            'category' => 'nullable|numeric',
            'address' => 'string|max:255',
            'password' => 'required|string|min:6|confirmed',
            'project_id' => 'required|exists:projects,id',
        ]);

        try {
            $validated['username'] = $this->generateUniqueUsername($validated['firstName']);
            $validated['password'] = bcrypt($validated['password']);
            $staff = User::create($validated);
            DB::table('project_user')->insert([
                'user_id' => $staff->id,
                'project_id' => $validated['project_id'],
                'role' => $validated['role'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return redirect()->route('staff.index')
                ->with('success', 'Staff created successfully.');
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
        try {
            $staff = User::with(['projectManager', 'siteEngineers', 'vendors'])->findOrFail($id);
            $userId = $staff->id;
            $projectId = $staff->project_id;
            $project = Project::findOrFail($projectId);

            $surveyedPolesCount = 0;
            $installedPolesCount = 0;
            $surveyedPoles = 0;
            $installedPoles = 0;

            $isStreetlightProject = ($project->project_type == 1) ? true : false;
            if ($isStreetlightProject) {
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
                $installedPoles = Pole::where('isInstallationDone', 1)
                    ->whereDate('created_at', Carbon::today())
                    ->whereHas('task', function ($query) use ($userId) {
                        $query->where('manager_id', $userId);
                    })
                    ->get();
            } else {
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
            $completedTasks = $tasks->where('status', 'Completed');
            $completedTasksCount = $completedTasks->count();
            $pendingTasks = $tasks->whereIn('status', ['Pending', 'In Progress']);
            $pendingTasksCount = $pendingTasks->count();
            $rejectedTasks = $tasks->where('status', 'Rejected');
            $rejectedTasksCount = $rejectedTasks->count();

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
            return back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $staff = User::findOrFail($id);
        $projects = Project::all();
        $projectEngineers = User::where('role', 2)->get();
        $usercategory = DB::table('user_categories')
            ->select(DB::raw('MIN(id) as id'), 'category_code')
            ->groupBy('category_code')
            ->get();

        return view('staff.edit', compact('staff', 'projects', 'usercategory', 'projectEngineers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $staff)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'manager_id' => 'required|numeric',
            'project_id' => 'nullable|integer',
            'firstName' => 'nullable|string|max:25',
            'lastName' => 'nullable|string|max:25',
            'email' => 'required|email|unique:users,email,' . $staff->id,
            'contactNo' => 'string',
            'category' => 'nullable|string',
            'address' => 'string|max:255',
            'password' => 'nullable|string|min:6|confirmed',
            'role' => 'nullable|string',

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

    public function updateProfilePicture(Request $request)
    {
        $request->validate([
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg,gif,heic,heif|max:2048',
        ]);

        $user = Auth::user();

        try {
            if (!$request->hasFile('profile_picture') || !$request->file('profile_picture')->isValid()) {
                return redirect()->back()->with('error', 'Invalid file upload. Please try again.');
            }

            $file = $request->file('profile_picture');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $imagePath = Storage::disk('s3')->putFileAs('profile_pictures', $file, $filename);

            if (!$imagePath) {
                throw new \Exception('S3 upload failed.');
            }

            $imageUrl = Storage::disk('s3')->url($imagePath);
            $user->image = $imageUrl;
            $user->save();

            return redirect()->back()->with('success', 'Profile picture updated successfully!');
        } catch (\Exception $e) {
            Log::error('Profile picture update error: ' . $e->getMessage(), [
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


        return redirect()->route('staff.index')->with('success', 'Password updated successfully.');
    }

    public function showSurveyedPoles()
    {
        $surveyedPoles = Pole::all();
        return view('staff.surveyedPoles', compact('surveyedPoles'));
    }

    public function vendorData($id)
    {
        $managerid = $id;

        $vendorids = StreetlightTask::where('manager_id', $managerid)
            ->whereNotNull('vendor_id')
            ->pluck('vendor_id')
            ->unique();

        $vendorPoleCounts = [];

        foreach ($vendorids as $vendorId) {
            $tasks = StreetlightTask::where('manager_id', $managerid)
                ->where('vendor_id', $vendorId)
                ->with('site', 'vendor')
                ->get();

            $taskIds = $tasks->pluck('id');
            $surveyCount = Pole::whereIn('task_id', $taskIds)->where('isSurveyDone', 1)->count();
            $installCount = Pole::whereIn('task_id', $taskIds)->where('isInstallationDone', 1)->count();

            $totalPoles = $tasks->reduce(function ($carry, $task) {
                return $carry + ($task->site)->total_poles ?? 0;
            }, 0);

            $vendor = optional($tasks->first()->vendor);
            $vendorName = trim(($vendor->firstName ?? '') . ' ' . ($vendor->lastName ?? ''));
            $vendorPoleCounts[$vendorId] = [
                'id' => $vendorId,
                'vendor_name' => $vendorName,
                'survey' => $surveyCount,
                'install' => $installCount,
                'tasks' => $tasks->count(),
                'total_poles' => $totalPoles,
            ];
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
                if ($task->end_date >= $today) {
                    return $carry + (optional($task->site)->total_poles ?? 0);
                }
                return $carry;
            }, 0);
            $backLogPoles = $tasks->reduce(function ($carry, $task) use ($today) {
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
                'vendor_name' => $vendorName,
                'survey' => $todaySurvey,
                'install' => $todayInstall,
                'tasks' => $todayTasks->count(),
                'total_poles' => $totalPoles,
                'today_target' => $todayTotalPoles,
                'backlog' => $backLogPoles,
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
                'id' => $engineerId,
                'engineer_name' => $engineerName,
                'survey' => $surveyCount,
                'install' => $installCount,
                'tasks' => $tasks->count(),
                'total_poles' => $totalPoles,
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
                'engineer_name' => $engineerName,
                'survey' => $todaySurvey,
                'install' => $todayInstall,
                'tasks' => $todayTasks->count(),
                'total_poles' => $totalPoles,
                'today_target' => $todayTotalPoles,
                'backlog' => $backLogPoles,
                'backlog_sites' => $backlogSites,
            ];
        }

        return view('engineer', compact('engineerids', 'engineerPoleCounts', 'engineerPoleCountsToday'));
    }
}
