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
        // $filter = request()->get('filter', 'today'); // Default filter is today

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

        // Apply filters on the selected task model
        $query = $taskModel::query();

        // Site Counts
        $totalSites = $siteModel::where('project_id', $selectedProjectId)->count();
        $completedSites = $siteModel::where('project_id', $selectedProjectId)->whereHas('tasks', function ($query) use ($dateRange) {
            $query->where('status', 'Completed')->whereBetween('created_at', $dateRange);
        })->count();
        $pendingSites = $totalSites - $completedSites;

        // Pole Counts (For Streetlight Projects)
        $totalSurveyedPoles = $isStreetLightProject ? Pole::where('isSurveyDone', true)->count() : null;
        $totalInstalledPoles = $isStreetLightProject ? Pole::where('isInstallationDone', true)->count() : null;

        // Fetch users (Engineers, Vendors, Managers) and calculate rankings
        $roles = ['Project Manager' => 2, 'Site Engineer' => 1, 'Vendor' => 3];
        $rolePerformances = [];

        foreach ($roles as $roleName => $role) {
            $users = User::where('role', $role)
                ->where('project_id', $selectedProjectId)
                ->get()
                ->map(function ($user) use ($taskModel, $selectedProjectId, $dateRange, $roleName) {
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

                    return (object) [
                        'id' => $user->id,
                        'name' => $user->firstName . " " . $user->lastName,
                        'image' => $user->image,
                        'role' => $roleName,
                        'totalTasks' => $totalTasks,
                        'completedTasks' => $completedTasks,
                        'performance' => $performance,
                        'medal' => ($completedTasks > 0) ? null : 'none', // No medal if completedTasks is 0
                    ];
                })
                ->filter() // Remove null values (users with no tasks)
                ->sortByDesc('completedTasks') // Sort by completed tasks in descending order
                ->values();

            $rolePerformances[$roleName] = $users;
        }

        // Fetch sites (Filtered for Project Manager)
        if ($isStreetLightProject) {
            // If it's a Streetlight project
            $siteQuery = Streetlight::where(
                'project_id',
                $projectId
            )
                ->when($user->role == 2, function ($query) use ($user) {
                    $query->whereIn('id', function ($subQuery) use ($user) {
                        $subQuery->select('site_id')->from('streetlight_tasks')
                            ->where('manager_id', $user->id);
                    });
                });

            $siteCount = $siteQuery->count();


            $assignedSites = StreetlightTask::whereNotNull('site_id')
                ->whereHas('site', fn($q) => $q->where('project_id', $projectId))
                ->when($user->role == 2, fn($q) => $q->where('manager_id', $user->id))
                ->distinct('site_id')
                ->count();

            $completedSitesCount = Streetlight::whereHas('streetLightTasks', function ($query) use ($user) {
                $query->where('status', 'Completed');
                if ($user->role == 2) {
                    $query->where('manager_id', $user->id);
                }
            })->where('project_id', $projectId)->count();

            $pendingSitesCount = Streetlight::whereHas('streetLightTasks', function ($query) use ($user) {
                $query->whereIn('status', ['Pending', 'In Progress']);
                if ($user->role == 2) {
                    $query->where('manager_id', $user->id);
                }
            })->where('project_id', $projectId)->count();

            $rejectedSitesCount = Streetlight::whereHas('streetLightTasks', function ($query) use ($user) {
                $query->where('status', 'Rejected');
                if ($user->role == 2) {
                    $query->where('manager_id', $user->id);
                }
            })->where('project_id', $projectId)->count();
        } else {
            // If it's a Rooftop project
            $siteQuery = Site::where('project_id', $projectId)
                ->when($user->role == 2, function ($query) use ($user) {
                    $query->whereIn('id', function ($subQuery) use ($user) {
                        $subQuery->select('site_id')->from('tasks')
                            ->where('manager_id', $user->id);
                    });
                });

            $siteCount = $siteQuery->count();


            $assignedSites = Task::whereNotNull('site_id')
                ->whereHas('site', fn($q) => $q->where('project_id', $projectId))
                ->when($user->role == 2, fn($q) => $q->where('manager_id', $user->id))
                ->distinct('site_id')
                ->count();

            $completedSitesCount = Site::whereHas('tasks', function ($query) use ($user) {
                $query->where('status', 'Completed');
                if ($user->role == 2) {
                    $query->where('manager_id', $user->id);
                }
            })->where('project_id', $projectId)->count();

            $pendingSitesCount = Site::whereHas('tasks', function ($query) use ($user) {
                $query->whereIn('status', ['Pending', 'In Progress']);
                if ($user->role == 2) {
                    $query->where('manager_id', $user->id);
                }
            })->where('project_id', $projectId)->count();

            $rejectedSitesCount = Site::whereHas('tasks', function ($query) use ($user) {
                $query->where('status', 'Rejected');
                if ($user->role == 2) {
                    $query->where('manager_id', $user->id);
                }
            })->where('project_id', $projectId)->count();
        }

        // Staff and vendor count for the project
        $staffCount = User::whereIn('role', [1, 2])
            ->where('project_id', $projectId)
            ->whereIn('id', function ($query) use ($projectId) {
                $query->select('user_id')->from('project_user');
            })
            ->count();
        $vendorCount = User::where('role', 3)
            ->where('project_id', $projectId)
            ->whereIn('id', function ($query) use ($projectId) {
                $query->select('user_id')->from('project_user');
            })
            ->count();

        // Fetch site engineers for the project (only those assigned to tasks managed by this project manager)
        $siteEngineersQuery = User::where('role', 1)
            ->where('project_id', $projectId)
            ->whereIn('id', function ($query) use ($projectId, $user, $isStreetLightProject) {
                $query->select('engineer_id')
                    ->from($isStreetLightProject ? 'streetlight_tasks' : 'tasks')
                    ->where('project_id', $projectId);

                if ($user->role == 2) { // If Project Manager
                    $query->where('manager_id', $user->id);
                }
            });

        $siteEngineers = $siteEngineersQuery->get()->map(function ($se) use ($projectId, $isStreetLightProject) {
            $taskModel = $isStreetLightProject ? StreetlightTask::class : Task::class;

            $totalTasksSE = $taskModel::where('engineer_id', $se->id)
                ->where('project_id', $projectId)
                ->count();
            $completedTasksSE = $taskModel::where('engineer_id', $se->id)
                ->where('project_id', $projectId)
                ->where('status', 'Completed')
                ->count();

            $performancePercentageSE = $totalTasksSE > 0 ? ($completedTasksSE / $totalTasksSE) * 100 : 0;

            return (object) [
                'id' => $se->id,
                'name' => $se->firstName . " " . $se->lastName,
                'image' => $se->image,
                'role' => "Site Engineer",
                'performance' => "$completedTasksSE/$totalTasksSE",
                'performancePercentage' => $performancePercentageSE,
            ];
        })->sortByDesc('performancePercentage')->values();

        // Fetch vendors for the project (only those assigned to tasks managed by this project manager)
        $vendorsQuery = User::where('role', 3)
            ->where('project_id', $projectId)
            ->whereIn('id', function ($query) use ($projectId, $user, $isStreetLightProject) {
                $query->select('vendor_id')
                    ->from($isStreetLightProject ? 'streetlight_tasks' : 'tasks')
                    ->where('project_id', $projectId);

                if ($user->role == 2) { // If Project Manager
                    $query->where('manager_id', $user->id);
                }
            });

        $vendors = $vendorsQuery->get()->map(function ($vendor) use ($projectId, $isStreetLightProject) {
            $taskModel = $isStreetLightProject ? StreetlightTask::class : Task::class;

            $totalTasksVendor = $taskModel::where('vendor_id', $vendor->id)
                ->where('project_id', $projectId)
                ->count();
            $completedTasksVendor = $taskModel::where('vendor_id', $vendor->id)
                ->where('project_id', $projectId)
                ->where('status', 'Completed')
                ->count();

            $performancePercentageVendor = $totalTasksVendor > 0 ? ($completedTasksVendor / $totalTasksVendor) * 100 : 0;

            return (object) [
                'id' => $vendor->id,
                'name' => $vendor->name,
                'image' => $vendor->image,
                'role' => "Vendor",
                'performance' => "$completedTasksVendor/$totalTasksVendor",
                'performancePercentage' => $performancePercentageVendor,
            ];
        })->sortByDesc('performancePercentage')->values();

        // Fetch project managers for the project
        $projectManagers = User::where('role', 2)
            ->where('project_id', $projectId)
            ->get()
            ->map(function ($pm) use ($projectId, $isStreetLightProject) {
                if ($isStreetLightProject) {
                    // If it's a StreetLight project, use StreetlightTask
                    $totalTasksPM = StreetlightTask::where('manager_id', $pm->id)
                        ->where('project_id', $projectId)
                        ->count();
                    $completedTasksPM = StreetlightTask::where('manager_id', $pm->id)
                        ->where('project_id', $projectId)
                        ->where('status', 'Completed')
                        ->count();
                } else {
                    // For non-StreetLight projects, use Task model
                    $totalTasksPM = Task::where('manager_id', $pm->id)
                        ->where('project_id', $projectId)
                        ->count();
                    $completedTasksPM = Task::where('manager_id', $pm->id)
                        ->where('project_id', $projectId)
                        ->where('status', 'Completed')
                        ->count();
                }

                $performancePercentagePM = $totalTasksPM > 0 ? ($completedTasksPM / $totalTasksPM) * 100 : 0;

                return (object) [
                    'id' => $pm->id,
                    'name' => $pm->firstName . " " . $pm->lastName,
                    'image' => $pm->image,
                    'role' => "Project Manager",
                    'performance' => "$completedTasksPM/$totalTasksPM",
                    'performancePercentage' => $performancePercentagePM,
                ];
            })->sortByDesc('performancePercentage')
            ->values();

        // Dashboard statistics
        $statistics = [
            [
                'title' => 'Sites',
                'values' => [
                    'Total' => $siteCount,
                    'Pending' => $pendingSitesCount,
                    'Completed' => $completedSitesCount,
                    'Rejected' => $rejectedSitesCount
                ],
                'link' => route('sites.index')
            ],
            ['title' => 'Vendors', 'value' => $vendorCount, 'link' => route('uservendors.index')],
            ['title' => 'Staffs', 'value' => $staffCount, 'link' => route('staff.index')],
        ];
        Log::info('Selected Project ID: ' . $selectedProjectId);
        Log::info('Total Sites: ' . $totalSites);
        Log::info('Completed Sites: ' . $completedSites);
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
                return [now()->startOfDay(), now()->endOfDay()]; // Default to today
        }
    }
}
