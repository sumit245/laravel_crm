<?php

namespace App\Http\Controllers;

use App\Models\Pole;
use App\Models\Project; // Model for vendors
use App\Models\Site;
use App\Models\Streetlight;
use App\Models\StreetlightTask;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

// Model for projects
// Model for sites
// (Optional) Model for revenue if stored in DB

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $selectedProjectId = $request->query('project_id', session('project_id'));

        // If the user is not an admin, restrict to their assigned project
        if ($user->role != 0) {
            $selectedProjectId = $user->project_id;
        }

        // Set the selected project ID in session
        session(['project_id' => $selectedProjectId]);

        $project = Project::findOrFail($selectedProjectId);
        $isStreetLightProject = $project->project_type == 1;

        // Date Filters
        $dateFilter = $request->query('date_filter', 'today');
        $dateRange = $this->getDateRange($dateFilter);

        // Admins & Project Managers can select any project, others are restricted
        $projectId = $user->role == 0 || $user->role == 2
            ? $request->query('project_id', session('project_id'))
            : $user->project_id;

        // Get only the project assigned to the logged-in user
        $projects = ($user->role == 0) ? Project::all() : Project::where('id', $projectId)->get();

        // Fetch Tasks based on project type
        $taskModel = $isStreetLightProject ? StreetlightTask::class : Task::class;
        $siteModel = $isStreetLightProject ? Streetlight::class : Site::class;

        // Site Counts
        $totalSites = $siteModel::where('project_id', $selectedProjectId)->count();
        $completedSites = $siteModel::where('project_id', $selectedProjectId)->whereHas('tasks', function ($query) use ($dateRange) {
            $query->where('status', 'Completed')->whereBetween('created_at', $dateRange);
        })->count();
        $pendingSites = $totalSites - $completedSites;

        // Pole Counts (For Streetlight Projects)
        $totalSurveyedPoles = $isStreetLightProject ? Pole::where('isSurveyDone', true)
            ->whereHas('task', function ($query) use ($selectedProjectId) {
                $query->whereHas('site', function ($query) use ($selectedProjectId) {
                    $query->where('project_id', $selectedProjectId);
                });
            })->count() : null;
        $totalInstalledPoles = $isStreetLightProject ? Pole::where('isInstallationDone', true)
            ->whereHas('task', function ($query) use ($selectedProjectId) {
                $query->whereHas('site', function ($query) use ($selectedProjectId) {
                    $query->where('project_id', $selectedProjectId);
                });
            })->count() : null;
        // Fetch users (Engineers, Vendors, Managers) and calculate rankings
        $roles = ['Project Manager' => 2, 'Site Engineer' => 1, 'Vendor' => 3];
        $rolePerformances = [];

        foreach ($roles as $roleName => $role) {
            // Adjust user query based on role
            $usersQuery = User::where('role', $role);
            if ($user->role == 2) { // If logged in user is a Project Manager
                $usersQuery->where('manager_id', $user->id);
            } elseif ($user->role != 0) { // If not admin, restrict to their project
                $usersQuery->where('project_id', $selectedProjectId);
            }

            $users = $usersQuery->get()->map(function ($user) use ($taskModel, $selectedProjectId, $dateRange, $roleName, $isStreetLightProject) {
                $totalTasks = $taskModel::where('project_id', $selectedProjectId)
                    ->where(function ($q) use ($user) {
                        if ($user->role == 1) $q->where('engineer_id', $user->id);
                        if ($user->role == 3) $q->where('vendor_id', $user->id);
                        if ($user->role == 2) $q->where('manager_id', $user->id);
                    })
                    ->whereBetween('created_at', $dateRange)
                    ->count();

                if ($totalTasks == 0) {
                    return null; // Skip users with no tasks
                }

                $completedTasks = $taskModel::where('project_id', $selectedProjectId)
                    ->where(function ($q) use ($user) {
                        if ($user->role == 1) $q->where('engineer_id', $user->id);
                        if ($user->role == 3) $q->where('vendor_id', $user->id);
                        if ($user->role == 2) $q->where('manager_id', $user->id);
                    })
                    ->where('status', 'Completed')
                    ->whereBetween('created_at', $dateRange)
                    ->count();

                $performance = ($completedTasks > 0) ? ($completedTasks / $totalTasks) * 100 : 0;

                // Add surveyed and installed poles counts
                if ($isStreetLightProject) {
                    $user->surveyedPoles = Pole::where('isSurveyDone', true)
                        ->whereHas('task', function ($query) use ($selectedProjectId, $user) {
                            $query->whereHas('site', function ($query) use ($selectedProjectId) {
                                $query->where('project_id', $selectedProjectId);
                            })->where(function ($q) use ($user) {
                                if ($user->role == 2) $q->where('manager_id', $user->id);
                                if ($user->role == 1) $q->where('engineer_id', $user->id);
                                if ($user->role == 3) $q->where('vendor_id', $user->id);
                            });
                        })->count();

                    $user->installedPoles = Pole::where('isInstallationDone', true)
                        ->whereHas('task', function ($query) use ($selectedProjectId, $user) {
                            $query->whereHas('site', function ($query) use ($selectedProjectId) {
                                $query->where('project_id', $selectedProjectId);
                            })->where(function ($q) use ($user) {
                                if ($user->role == 2) $q->where('manager_id', $user->id);
                                if ($user->role == 1) $q->where('engineer_id', $user->id);
                                if ($user->role == 3) $q->where('vendor_id', $user->id);
                            });
                        })->count();
                } else {
                    $user->surveyedPoles = null;
                    $user->installedPoles = null;
                }



                return (object) [
                    'id' => $user->id,
                    'name' => $user->firstName . " " . $user->lastName,
                    'image' => $user->image,
                    'role' => $roleName,
                    'totalTasks' => $totalTasks,
                    'completedTasks' => $completedTasks,
                    'performance' => $performance,
                    'surveyedPoles' => $user->surveyedPoles,
                    'installedPoles' => $user->installedPoles,
                    'medal' => ($completedTasks > 0) ? null : 'none', // No medal if completedTasks is 0
                ];
            })
                ->filter() // Remove null values (users with no tasks)
                ->sortByDesc('completedTasks') // Sort by completed tasks in descending order
                ->values();

            $rolePerformances[$roleName] = $users;
        }
        $vendorCount = User::where('role', 3)
            ->where('project_id', $selectedProjectId)
            ->when($user->role == 0, function ($query) {
                // Admin can see all vendors
                return $query;
            }, function ($query) use ($user) {
                // Project Manager sees only their vendors
                return $query->where('manager_id', $user->id);
            })
            ->count();
        $staffCount = User::where('role', 1)
            ->where('project_id', $selectedProjectId)
            ->when($user->role == 0, function ($query) {
                // Admin can see all vendors
                return $query;
            }, function ($query) use ($user) {
                // Project Manager sees only their vendors
                return $query->where('manager_id', $user->id);
            })
            ->count();
        // Dashboard statistics
        $statistics = [
            [
                'title' => 'Sites',
                'values' => [
                    'Total' => $totalSites,
                    'Pending' => $pendingSites,
                    'Completed' => $completedSites,
                    'Rejected' => 0
                ],
                'link' => route('sites.index')
            ],
            ['title' => 'Vendors', 'value' => $vendorCount, 'link' => route('uservendors.index')],
            ['title' => 'Staffs', 'value' => $staffCount, 'link' => route('staff.index')],
        ];
        return view('dashboard', compact(
            'rolePerformances',
            'statistics',
            'projects',
            'totalSites',
            'completedSites',
            'pendingSites',
            'totalSurveyedPoles',
            'totalInstalledPoles',
            'isStreetLightProject'
        ));
    }


    // Helper Function for Date Range
    private function getDateRange($filter)
    {
        switch ($filter) {
            case 'today':
                return [now()->startOfDay(), now()->endOfDay()];
            case 'this_week':
                return [now()->startOfWeek(), now()->endOfWeek()];
            case 'this_month':
                return [now()->startOfMonth(), now()->endOfMonth()];
            default:
                // Return all time data
                return ['1970-01-01 00:00:00', now()]; // From the Unix epoch to now
        }
    }
}
