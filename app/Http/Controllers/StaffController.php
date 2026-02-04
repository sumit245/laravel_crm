<?php

namespace App\Http\Controllers;

use App\Enums\TaskStatus;
use App\Enums\UserRole;
use App\Helpers\ExcelHelper;
use App\Helpers\WhatsappHelper;
use App\Imports\StaffImport;
use App\Models\DiscussionPoint;
use App\Models\InventoryDispatch;
use App\Models\InventroyStreetLightModel;
use App\Models\Meet;
use App\Models\Pole;
use App\Models\Project;
use App\Models\Site;
use App\Models\Streetlight;
use App\Models\StreetlightTask;
use App\Models\Task;
use App\Models\User;
use App\Models\UserCategory;
use App\Traits\GeneratesUniqueUsername;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class StaffController extends Controller
{
    use GeneratesUniqueUsername;
    /**
     * Returns a list of all staff members.
     */
    public function index()
    {
        // Only non-admin staff are listed here for now. This can be extended later
        // based on UserRole rules from the global auth plan.
        $today = Carbon::today();
        $staff = User::whereIn('role', [
            UserRole::SITE_ENGINEER->value,
            UserRole::PROJECT_MANAGER->value,
            UserRole::STORE_INCHARGE->value,
            UserRole::COORDINATOR->value
        ])->with(['projects', 'usercategory'])->get();
        $staff->map(function ($staff) use ($today) {
            $staff->totalTasks = Task::where('engineer_id', $staff->id)->count();
            $staff->pendingTasks = Task::where('engineer_id', $staff->id)->where('status', 'Pending')->count();
            $staff->inProgressTasks = Task::where('engineer_id', $staff->id)->where('status', 'In Progress')->count();
            $staff->completedTasks = Task::where('engineer_id', $staff->id)->where('status', 'Done')->count();
            $staff->tasksAssignedToday = Task::where('engineer_id', $staff->id)->whereDate('created_at', $today)->count();
            $staff->tasksCompletedToday = Task::where('engineer_id', $staff->id)->whereDate('updated_at', $today)->where('status', 'Done')->count();

            return $staff;
        });
        $projects = Project::all();
        $departments = UserCategory::all();

        return view('staff.index', compact('staff', 'projects', 'departments'));
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
            'role' => 'required|integer',
            'category' => 'nullable|numeric',
            'department' => 'nullable|string|max:255',
            'address' => 'string|max:255',
            'password' => 'required|string|min:6|confirmed',
            'project_id' => 'required|exists:projects,id',
            'accountName' => 'nullable|string|max:255',
            'accountNumber' => 'nullable|string|max:255',
            'ifsc' => 'nullable|string|max:50',
            'bankName' => 'nullable|string|max:255',
            'branch' => 'nullable|string|max:255',
            'gstNumber' => 'nullable|string|max:50',
            'pan' => 'nullable|string|max:20',
            'aadharNumber' => 'nullable|string|max:20',
            'status' => 'nullable|string|max:50',
            'disableLogin' => 'nullable|boolean',
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
            $staff = User::with(['projectManager', 'siteEngineers', 'vendors', 'projects', 'usercategory', 'verticalHead'])
                ->findOrFail($id);
            $userId = $staff->id;

            // Get all assigned projects from pivot table
            $assignedProjects = $staff->getAssignedProjects();
            
            // If no projects in pivot, check legacy project_id
            if ($assignedProjects->isEmpty() && $staff->project_id) {
                $legacyProject = Project::find($staff->project_id);
                if ($legacyProject) {
                    $assignedProjects = collect([$legacyProject]);
                    // Sync to pivot table for consistency
                    $staff->assignToProject($legacyProject->id);
                }
            }
            
            // If still no projects, try to find projects from tasks (streetlight and rooftop)
            if ($assignedProjects->isEmpty()) {
                // Get unique project IDs from streetlight tasks
                $streetlightProjectIds = StreetlightTask::where(function($query) use ($userId) {
                    $query->where('engineer_id', $userId)
                        ->orWhere('manager_id', $userId)
                        ->orWhere('vendor_id', $userId);
                })->distinct()->pluck('project_id');
                
                // Get unique project IDs from rooftop tasks
                $rooftopProjectIds = Task::where(function($query) use ($userId) {
                    $query->where('engineer_id', $userId)
                        ->orWhere('manager_id', $userId)
                        ->orWhere('vendor_id', $userId);
                })->distinct()->pluck('project_id');
                
                // Merge and get unique projects
                $allProjectIds = $streetlightProjectIds->merge($rooftopProjectIds)->unique()->filter();
                if ($allProjectIds->isNotEmpty()) {
                    $assignedProjects = Project::whereIn('id', $allProjectIds)->get();
                    // Sync to pivot table for consistency
                    foreach ($assignedProjects as $project) {
                        $staff->assignToProject($project->id);
                    }
                }
            }

            // Calculate meeting tasks assigned to staff
            // Get discussion points where staff is assigned via pivot table or direct assignment
            $meetingTasks = DiscussionPoint::where(function($query) use ($userId) {
                $query->where('assignee_id', $userId)
                    ->orWhere('assigned_to', $userId)
                    ->orWhereExists(function($subQuery) use ($userId) {
                        // Check pivot table directly
                        $subQuery->select(DB::raw(1))
                            ->from('discussion_point_user')
                            ->whereColumn('discussion_point_user.discussion_point_id', 'discussion_points.id')
                            ->where('discussion_point_user.user_id', $userId);
                    });
            })
            ->with(['meet', 'project', 'assignee', 'assignedToUser', 'assignedUsers'])
            ->get();

            // Meeting tasks summary
            $meetingTasksSummary = [
                'total' => $meetingTasks->count(),
                'completed' => $meetingTasks->where('status', 'Completed')->count(),
                'in_progress' => $meetingTasks->where('status', 'In Progress')->count(),
                'pending' => $meetingTasks->where('status', 'Pending')->count(),
            ];

            // Gather streetlight data per project
            $streetlightDataByProject = [];
            foreach ($assignedProjects as $project) {
                if ($project->project_type == 1) {
                    // Get tasks where staff is engineer, manager, or vendor
                    $projectTasks = StreetlightTask::where('project_id', $project->id)
                        ->where(function($query) use ($userId) {
                            $query->where('engineer_id', $userId)
                                ->orWhere('manager_id', $userId)
                                ->orWhere('vendor_id', $userId);
                        })
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
                    $streetlightSites = $sites->map(function($site) use ($projectTasks) {
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

            // Gather rooftop data per project
            $rooftopDataByProject = [];
            foreach ($assignedProjects as $project) {
                if ($project->project_type == 0) {
                    // Get tasks where staff is engineer, manager, or vendor
                    $projectTaskIds = Task::where('project_id', $project->id)
                        ->where(function($query) use ($userId) {
                            $query->where('engineer_id', $userId)
                                ->orWhere('manager_id', $userId)
                                ->orWhere('vendor_id', $userId);
                        })
                        ->pluck('id');
                    
                    $rooftopSites = Site::whereIn('id', function($query) use ($projectTaskIds) {
                        $query->select('site_id')
                            ->from('tasks')
                            ->whereIn('id', $projectTaskIds);
                    })
                    ->with(['tasks' => function($q) use ($userId, $project) {
                        $q->where(function($query) use ($userId) {
                            $query->where('engineer_id', $userId)
                                ->orWhere('manager_id', $userId)
                                ->orWhere('vendor_id', $userId);
                        })
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

            // Calculate aggregate totals across all projects
            $totalTasksCount = 0;
            $completedTasksCount = 0;
            $pendingTasksCount = 0;
            $allTasks = collect();

            foreach ($assignedProjects as $project) {
                if ($project->project_type == 1) {
                    // Streetlight: Sum of total_poles
                    if (isset($streetlightDataByProject[$project->id])) {
                        $totalTasksCount += $streetlightDataByProject[$project->id]['total_poles'] ?? 0;
                        $completedTasksCount += $streetlightDataByProject[$project->id]['installed_poles'] ?? 0;
                    }
                } else {
                    // Rooftop: Count of sites/tasks
                    if (isset($rooftopDataByProject[$project->id])) {
                        $totalTasksCount += $rooftopDataByProject[$project->id]['total_sites'] ?? 0;
                        $completedTasksCount += $rooftopDataByProject[$project->id]['completed_sites'] ?? 0;
                    }
                }
            }

            // Calculate pending tasks count from all project tasks
            foreach ($assignedProjects as $project) {
                if ($project->project_type == 1) {
                    $projectTasks = StreetlightTask::where('project_id', $project->id)
                        ->where(function($query) use ($userId) {
                            $query->where('engineer_id', $userId)
                                ->orWhere('manager_id', $userId)
                                ->orWhere('vendor_id', $userId);
                        })
                        ->get();
                    $allTasks = $allTasks->merge($projectTasks);
                } else {
                    $projectTasks = Task::where('project_id', $project->id)
                        ->where(function($query) use ($userId) {
                            $query->where('engineer_id', $userId)
                                ->orWhere('manager_id', $userId)
                                ->orWhere('vendor_id', $userId);
                        })
                        ->get();
                    $allTasks = $allTasks->merge($projectTasks);
                }
            }

            $pendingTasks = $allTasks->whereIn('status', [
                TaskStatus::PENDING->value,
                TaskStatus::IN_PROGRESS->value
            ]);
            $pendingTasksCount = $pendingTasks->count();

            return view('staff.show', compact(
                'staff',
                'assignedProjects',
                'meetingTasks',
                'meetingTasksSummary',
                'streetlightDataByProject',
                'rooftopDataByProject',
                'totalTasksCount',
                'completedTasksCount',
                'pendingTasksCount',
                'allTasks'
            ));
        } catch (\Exception $e) {
            Log::error('Staff show page error: ' . $e->getMessage(), [
                'staff_id' => $id,
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }


    /**
     * Upload staff avatar image.
     */
    public function uploadAvatar(Request $request, $id)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = User::findOrFail($id);

        // Generate unique filename: username_YYYYMMDD_HHMMSS.jpg
        $timestamp = Carbon::now()->format('Ymd_His');
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
            'category' => 'nullable|numeric',
            'department' => 'nullable|string|max:255',
            'address' => 'string|max:255',
            'password' => 'nullable|string|min:6|confirmed',
            'role' => 'nullable|integer',
            'accountName' => 'nullable|string|max:255',
            'accountNumber' => 'nullable|string|max:255',
            'ifsc' => 'nullable|string|max:50',
            'bankName' => 'nullable|string|max:255',
            'branch' => 'nullable|string|max:255',
            'gstNumber' => 'nullable|string|max:50',
            'pan' => 'nullable|string|max:20',
            'aadharNumber' => 'nullable|string|max:20',
            'status' => 'nullable|string|max:50',
            'disableLogin' => 'nullable|boolean',

        ]);

        if ($request->password) {
            $validated['password'] = bcrypt($validated['password']);
        }

        $staff->update($validated);

        return redirect()
            ->route('staff.show', $staff->id)
            ->with('success', 'Staff updated successfully.');
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

    /**
     * Bulk delete staff members.
     */
    public function bulkDelete(Request $request)
    {
        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'exists:users,id',
            ]);

            $count = User::whereIn('id', $request->ids)->delete();

            return response()->json([
                'success' => true,
                'message' => "{$count} staff member(s) deleted successfully"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete staff members: ' . $e->getMessage()
            ], 500);
        }
    }



    public function updateProfile($id)
    {
        // Self-service: restrict to the logged-in user's own profile
        if ((int) $id !== (int) Auth::id()) {
            abort(403, 'You are not allowed to view this profile.');
        }

        $user = Auth::user();

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

    /**
     * Initiate mobile number change by sending an OTP over WhatsApp.
     *
     * This is a self-service operation and is always scoped to the logged-in user.
     */
    public function sendMobileChangeOtp(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'new_mobile' => ['required', 'string', 'regex:/^[0-9]{10}$/'],
        ]);

        $otp = (string) random_int(100000, 999999);

        // Store OTP details in session with a short expiry
        session([
            'mobile_change_otp'        => $otp,
            'mobile_change_mobile'     => $validated['new_mobile'],
            'mobile_change_expires_at' => now()->addMinutes(10)->toIso8601String(),
        ]);

        try {
            WhatsappHelper::sendMobileChangeOtp(
                $validated['new_mobile'],
                $otp,
                $user->name
            );

            return redirect()
                ->back()
                ->with('success', 'OTP has been sent to your new mobile number on WhatsApp.');
        } catch (\Throwable $e) {
            Log::error('Failed to send mobile change OTP', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);

            return redirect()
                ->back()
                ->with('error', 'Failed to send OTP over WhatsApp. Please try again later.');
        }
    }

    /**
     * Verify the OTP and update the logged-in user's mobile number.
     */
    public function verifyMobileChangeOtp(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'otp' => ['required', 'digits:6'],
        ]);

        $sessionOtp    = session('mobile_change_otp');
        $sessionMobile = session('mobile_change_mobile');
        $expiresAt     = session('mobile_change_expires_at');

        if (!$sessionOtp || !$sessionMobile || !$expiresAt) {
            return redirect()
                ->back()
                ->with('error', 'OTP session has expired. Please request a new OTP.');
        }

        if (now()->greaterThan(Carbon::parse($expiresAt))) {
            session()->forget(['mobile_change_otp', 'mobile_change_mobile', 'mobile_change_expires_at']);

            return redirect()
                ->back()
                ->with('error', 'OTP has expired. Please request a new OTP.');
        }

        if ($validated['otp'] !== $sessionOtp) {
            return redirect()
                ->back()
                ->with('error', 'Invalid OTP. Please check the code and try again.');
        }

        // All good: update the user's contact number
        $user->contactNo = $sessionMobile;
        $user->save();

        session()->forget(['mobile_change_otp', 'mobile_change_mobile', 'mobile_change_expires_at']);

        return redirect()
            ->back()
            ->with('success', 'Your mobile number has been updated successfully.');
    }
    public function changePassword($id)
    {
        $authUser = Auth::user();

        // Admins can change any user's password; others can only change their own
        if ($authUser && UserRole::tryFrom($authUser->role)?->isAdmin() === false && (int) $id !== (int) $authUser->id) {
            return redirect()
                ->route('staff.index')
                ->with('error', 'You are not allowed to change password for this user.');
        }

        $staff = User::findOrFail($id);
        return view('staff.change-password', compact('staff'));
    }

    public function updatePassword(Request $request, $id)
    {
        $authUser = Auth::user();
        if ($authUser && UserRole::tryFrom($authUser->role)?->isAdmin() === false && (int) $id !== (int) $authUser->id) {
            return redirect()
                ->route('staff.index')
                ->with('error', 'You are not allowed to change password for this user.');
        }

        $request->validate([
            'password' => 'required|min:8|confirmed',
        ]);

        User::where('id', $id)->update([
            'password' => bcrypt($request->password)
        ]);

        return redirect()
            ->route('staff.index')
            ->with('success', 'Password updated successfully.');
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

    /**
     * Delete all entries from a panchayat
     * Returns consumed inventory back to vendor in dispatched state
     * Updates number_of_surveyed_poles and number_of_installed_poles in streetlights table
     */
    public function deletePanchayat(Request $request, $projectId, $panchayat)
    {
        try {
            DB::beginTransaction();

            // Get all streetlight sites for this panchayat and project
            $streetlights = Streetlight::where('project_id', $projectId)
                ->where('panchayat', $panchayat)
                ->get();

            if ($streetlights->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No streetlight sites found for this panchayat.'
                ], 404);
            }

            $totalSurveyed = 0;
            $totalInstalled = 0;
            $polesDeleted = 0;
            $inventoryReturned = 0;

            // Process each streetlight site
            foreach ($streetlights as $streetlight) {
                // Get all tasks for this site
                $tasks = StreetlightTask::where('site_id', $streetlight->id)->get();

                foreach ($tasks as $task) {
                    // Get all poles for this task
                    $poles = Pole::where('task_id', $task->id)->get();

                    foreach ($poles as $pole) {
                        // Count surveyed and installed
                        if ($pole->isSurveyDone) {
                            $totalSurveyed++;
                        }
                        if ($pole->isInstallationDone) {
                            $totalInstalled++;
                        }

                        // Return inventory to dispatched state (not consumed)
                        $serialNumbers = array_filter([
                            $pole->panel_qr,
                            $pole->battery_qr,
                            $pole->luminary_qr,
                        ]);

                        foreach ($serialNumbers as $serialNumber) {
                            if (empty($serialNumber)) continue;

                            // Find dispatch record
                            $dispatch = InventoryDispatch::where('serial_number', $serialNumber)
                                ->where('streetlight_pole_id', $pole->id)
                                ->first();

                            if ($dispatch) {
                                // Update dispatch to dispatched state (not consumed)
                                $dispatch->update([
                                    'is_consumed' => false,
                                    'streetlight_pole_id' => null,
                                ]);

                                // Update inventory quantity back to 0 (dispatched)
                                $inventory = InventroyStreetLightModel::where('serial_number', $serialNumber)->first();
                                if ($inventory) {
                                    $inventory->quantity = 0; // Dispatched, not in stock
                                    $inventory->save();
                                }

                                $inventoryReturned++;
                            }
                        }

                        $polesDeleted++;
                    }

                    // Delete poles
                    Pole::where('task_id', $task->id)->delete();

                    // Delete task
                    $task->delete();
                }

                // Update streetlight counts
                $streetlight->update([
                    'number_of_surveyed_poles' => 0,
                    'number_of_installed_poles' => 0,
                ]);

                // Delete streetlight site
                $streetlight->delete();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Panchayat '{$panchayat}' deleted successfully.",
                'data' => [
                    'poles_deleted' => $polesDeleted,
                    'inventory_returned' => $inventoryReturned,
                    'surveyed_poles_removed' => $totalSurveyed,
                    'installed_poles_removed' => $totalInstalled,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete panchayat', [
                'project_id' => $projectId,
                'panchayat' => $panchayat,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete panchayat: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Push panchayat to RMS server
     */
    public function pushPanchayatToRMS(Request $request, $projectId, $panchayat)
    {
        try {
            // Get all streetlight sites for this panchayat and project
            $streetlights = Streetlight::where('project_id', $projectId)
                ->where('panchayat', $panchayat)
                ->get();

            if ($streetlights->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No streetlight sites found for this panchayat.'
                ], 404);
            }

            $responses = [];
            $successCount = 0;
            $errorCount = 0;

            foreach ($streetlights as $streetlight) {
                // Get all tasks for this site
                $tasks = StreetlightTask::where('site_id', $streetlight->id)->get();

                foreach ($tasks as $task) {
                    // Get all installed poles for this task
                    $poles = Pole::where('task_id', $task->id)
                        ->where('isInstallationDone', true)
                        ->get();

                    foreach ($poles as $pole) {
                        try {
                            $engineer = $task->engineer;
                            $approved_by = $engineer ? ($engineer->firstName . ' ' . $engineer->lastName) : 'System';

                            // Push to RMS
                            $apiResponse = \App\Helpers\RemoteApiHelper::sendPoleDataToRemoteServer($pole, $streetlight, $approved_by);

                            if ($apiResponse && $apiResponse->successful()) {
                                $successCount++;
                                $responses[] = [
                                    'pole_id' => $pole->id,
                                    'status' => 'success',
                                    'message' => 'Pushed successfully'
                                ];
                            } else {
                                $errorCount++;
                                $responses[] = [
                                    'pole_id' => $pole->id,
                                    'status' => 'error',
                                    'message' => $apiResponse ? 'API returned error' : 'No response from API'
                                ];
                            }
                        } catch (\Exception $e) {
                            $errorCount++;
                            Log::error('Failed to push pole to RMS', [
                                'pole_id' => $pole->id,
                                'error' => $e->getMessage()
                            ]);

                            $responses[] = [
                                'pole_id' => $pole->id,
                                'status' => 'error',
                                'message' => $e->getMessage()
                            ];
                        }
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Panchayat '{$panchayat}' pushed to RMS. Success: {$successCount}, Errors: {$errorCount}",
                'data' => [
                    'success_count' => $successCount,
                    'error_count' => $errorCount,
                    'responses' => $responses
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to push panchayat to RMS', [
                'project_id' => $projectId,
                'panchayat' => $panchayat,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to push panchayat to RMS: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export streetlight project data to Excel with multiple sheets
     * 
     * @param int $staffId
     * @param int $projectId
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportStreetlightExcel($staffId, $projectId)
    {
        try {
            // Get staff and project
            $staff = User::findOrFail($staffId);
            $project = Project::findOrFail($projectId);

            // Validate project is streetlight type
            if ($project->project_type != 1) {
                return back()->with('error', 'This export is only available for streetlight projects.');
            }

            // Get tasks where staff is engineer, manager, or vendor
            $projectTasks = StreetlightTask::where('project_id', $project->id)
                ->where(function($query) use ($staffId) {
                    $query->where('engineer_id', $staffId)
                        ->orWhere('manager_id', $staffId)
                        ->orWhere('vendor_id', $staffId);
                })
                ->with('site')
                ->get();
            
            $projectTaskIds = $projectTasks->pluck('id');
            $siteIds = $projectTasks->pluck('site_id')->filter()->unique();
            
            // Get sites from tasks
            $sites = Streetlight::whereIn('id', $siteIds)->get();
            
            // Build Sheet 1: Summary Data (same as displayed in table)
            $sheet1Data = [];
            $sheet1Headings = ['#', 'State', 'District', 'Block', 'Panchayat', 'Ward', 'Total Poles', 'Surveyed', 'Installed'];
            
            foreach ($sites as $index => $site) {
                // Get tasks for this site
                $siteTasks = $projectTasks->where('site_id', $site->id);
                $siteTaskIds = $siteTasks->pluck('id');
                
                // Get poles for these tasks
                $poles = Pole::whereIn('task_id', $siteTaskIds)->get();
                
                // Format ward (remove HTML links, just get ward numbers)
                $wardText = $site->ward ?? '-';
                if ($wardText && $wardText !== '-') {
                    // Extract ward numbers from comma-separated string
                    $wards = array_filter(array_map('trim', explode(',', $wardText)));
                    $wardText = implode(', ', $wards);
                }
                
                $sheet1Data[] = [
                    '#' => $index + 1,
                    'State' => $site->state ?? '-',
                    'District' => $site->district ?? '-',
                    'Block' => $site->block ?? '-',
                    'Panchayat' => $site->panchayat ?? '-',
                    'Ward' => $wardText,
                    'Total Poles' => $site->total_poles ?? 0,
                    'Surveyed' => $poles->where('isSurveyDone', 1)->count(),
                    'Installed' => $poles->where('isInstallationDone', 1)->count(),
                ];
            }

            // Build Sheet 2: Pole-level Detailed Data
            $sheet2Data = [];
            $sheet2Headings = ['District', 'Block', 'Panchayat', 'Ward', 'Pole Number', 'Beneficiary', 'Beneficiary Contact', 'IMEI', 'SIM Number', 'Battery', 'Panel'];
            
            // Get all installed poles for this project and staff
            $poles = Pole::whereIn('task_id', $projectTaskIds)
                ->where('isInstallationDone', 1)
                ->with(['task.site'])
                ->get();
            
            foreach ($poles as $pole) {
                $site = $pole->task->site ?? null;
                
                // Get ward from pole's ward_name field (not from site)
                $wardText = $pole->ward_name ?? '-';
                
                $sheet2Data[] = [
                    'District' => $site->district ?? '-',
                    'Block' => $site->block ?? '-',
                    'Panchayat' => $site->panchayat ?? '-',
                    'Ward' => $wardText,
                    'Pole Number' => $pole->complete_pole_number ?? '-',
                    'Beneficiary' => $pole->beneficiary ?? '-',
                    'Beneficiary Contact' => $pole->beneficiary_contact ?? '-',
                    'IMEI' => $pole->luminary_qr ?? '-',
                    'SIM Number' => $pole->sim_number ?? '-',
                    'Battery' => $pole->battery_qr ?? '-',
                    'Panel' => $pole->panel_qr ?? '-',
                ];
            }

            // Prepare sheets data
            $sheets = [
                'Summary' => $sheet1Data,
                'Pole Details' => $sheet2Data,
            ];

            // Generate filename: staff_name_project_name_date.xlsx
            $staffName = \Illuminate\Support\Str::slug($staff->name);
            $projectName = \Illuminate\Support\Str::slug($project->project_name);
            $date = now()->format('Y-m-d');
            $filename = "{$staffName}_{$projectName}_{$date}.xlsx";

            // Export using ExcelHelper
            return ExcelHelper::exportMultipleSheets($sheets, $filename);

        } catch (\Exception $e) {
            Log::error('Failed to export streetlight Excel', [
                'staff_id' => $staffId,
                'project_id' => $projectId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Failed to export Excel: ' . $e->getMessage());
        }
    }
}
