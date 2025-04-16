<?php

namespace App\Http\Controllers;

use App\Helpers\ExcelHelper;
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
        $selectedProjectId = $this->getSelectedProject($request, $user);
        $project = Project::findOrFail($selectedProjectId);
        $dateRange = $this->getDateRange($request->query('date_filter', 'today'));

        // Determine task and site models based on project type
        $isStreetLightProject = $project->project_type == 1;
        $taskModel = $isStreetLightProject ? StreetlightTask::class : Task::class;
        $siteModel = $isStreetLightProject ? Streetlight::class : Site::class;

        // Get site statistics
        $siteStats = $this->getSiteStatistics($siteModel, $taskModel, $selectedProjectId, $dateRange, $isStreetLightProject);

        // Get pole statistics for streetlight projects
        $poleStats = $isStreetLightProject ?
            $this->getPoleStatistics($selectedProjectId) :
            ['totalSurveyedPoles' => null, 'totalInstalledPoles' => null];

        // Calculate role performances
        $rolePerformances = $this->calculateRolePerformances(
            $user,
            $selectedProjectId,
            $taskModel,
            $dateRange,
            $isStreetLightProject
        );

        // Get user counts
        $userCounts = $this->getUserCounts($user, $selectedProjectId);

        // Prepare statistics array
        $statistics = $this->prepareStatistics($siteStats, $userCounts, $isStreetLightProject);
        return view('dashboard', array_merge(
            compact('rolePerformances', 'statistics', 'isStreetLightProject', 'project'),
            $siteStats,
            $poleStats,
            ['projects' => $this->getAvailableProjects($user)]
        ));
    }

    private function calculateRolePerformances($user, $projectId, $taskModel, $dateRange, $isStreetLightProject)
    {
        $roles = ['Project Manager' => 2, 'Site Engineer' => 1, 'Vendor' => 3];
        $rolePerformances = [];

        foreach ($roles as $roleName => $roleId) {
            $usersQuery = User::where('role', $roleId);

            // Apply role-based filters
            if ($user->role == 2) { // Project Manager
                if ($roleId == 2) {
                    // Show all project managers for the selected project
                    $usersQuery->where('project_id', $projectId);
                } else {
                    // Show only related engineers and vendors for the selected project
                    $usersQuery->where('manager_id', $user->id)
                        ->where('project_id', $projectId);
                }
            } else {
                // Non-admin users see only their project
                $usersQuery->where('project_id', $projectId);
            }

            $users = $usersQuery->get()->map(function ($user) use ($taskModel, $projectId, $dateRange, $roleName, $isStreetLightProject) {
                return $this->calculateUserPerformance($user, $taskModel, $projectId, $dateRange, $roleName, $isStreetLightProject);
            })->filter();

            // Custom sorting logic based on the conditions provided
            if ($isStreetLightProject) {
                $users = $users->sortByDesc('surveyedPoles');
                // function ($user) {
                //     // Check if surveyedPoles is not 0
                //     if ($user->installedPoles > 0) {
                //         return $user->installedPoles;
                //     }
                //     // Check if installedPoles is not 0
                //     elseif ($user->surveyedPoles > 0) {
                //         return $user->surveyedPoles;
                //     }
                //     // Fallback to totalTasks
                //     return $user->totalTasks;
                // }
            } else {
                // Default sorting for non-streetlight projects
                $users = $users->sortByDesc('completedTasks');
            }

            $users = $users->values(); // Re-index the collection after sorting

            $rolePerformances[$roleName] = $users;
            Log::info($users);
        }

        return $rolePerformances;
    }

    private function calculateUserPerformance($user, $taskModel, $projectId, $dateRange, $roleName, $isStreetLightProject)
    {
        if ($isStreetLightProject) {
            // Get total poles for all panchayats assigned to this user in date range
            $totalPoles = Streetlight::whereHas('streetlightTasks', function ($q) use ($user, $dateRange) {
                $q->where($this->getRoleColumn($user->role), $user->id)
                    ->whereBetween('updated_at', $dateRange);
            })->sum('total_poles');

            // Get surveyed and installed poles by this user in date range
            $surveyedPoles = Pole::whereHas('task', function ($q) use ($projectId, $user, $dateRange) {
                $q->where('project_id', $projectId)
                    ->where($this->getRoleColumn($user->role), $user->id)
                    ->whereBetween('updated_at', $dateRange);
            })->where('isSurveyDone', true)->count();

            $installedPoles = Pole::whereHas('task', function ($q) use ($projectId, $user, $dateRange) {
                $q->where('project_id', $projectId)
                    ->where($this->getRoleColumn($user->role), $user->id)
                    ->whereBetween('updated_at', $dateRange);
            })->where('isInstallationDone', true)->count();

            $performance = $totalPoles > 0 ? ($surveyedPoles / $totalPoles) * 100 : 0;

            return (object)[
                'id' => $user->id,
                'name' => $user->firstName . " " . $user->lastName,
                'vendor_name' => $user->name ?? "",
                'image' => $user->image,
                'role' => $roleName,
                'totalTasks' => $totalPoles,
                'surveyedPoles' => $surveyedPoles,
                'installedPoles' => $installedPoles,
                'performance' => $performance,
                'medal' => $surveyedPoles > 0 ? null : 'none'
            ];
        } else {
            $tasksQuery = $taskModel::where('project_id', $projectId)
                ->where($this->getRoleColumn($user->role), $user->id)
                ->whereBetween('updated_at', $dateRange);

            $totalTasks = $tasksQuery->count();
            if ($totalTasks == 0) return null;

            $completedTasks = $tasksQuery->where('status', 'Completed')->count();
            $performance = ($completedTasks / $totalTasks) * 100;
        }

        return (object)[
            'id' => $user->id,
            'name' => $user->firstName . " " . $user->lastName,
            'vendor_name' => $user->name ?? "",
            'image' => $user->image,
            'role' => $roleName,
            'totalTasks' => $totalTasks,
            'completedTasks' => $completedTasks,
            'performance' => $performance,
            'medal' => $completedTasks > 0 ? null : 'none'
        ];
    }

    private function getRoleColumn($role)
    {
        return [
            1 => 'engineer_id',
            2 => 'manager_id',
            3 => 'vendor_id'
        ][$role];
    }

    private function getSelectedProject(Request $request, User $user)
    {
        // First try to get from request
        if ($request->has('project_id')) {
            return $request->project_id;
        }

        // Then try user's assigned project
        if ($user->project_id) {
            return $user->project_id;
        }

        // Finally get first project user has access to
        return Project::when($user->role !== 0, function ($query) use ($user) {
            $query->whereHas('users', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            });
        })->first()->id;
    }

    private function getSiteStatistics($siteModel, $taskModel, $projectId, $dateRange, $isStreetLightProject)
    {
        if ($isStreetLightProject) {
            $streetlightStats = Streetlight::where('project_id', $projectId)
                ->selectRaw('COUNT(DISTINCT panchayat) as total_sites, SUM(total_poles) as total_poles')
                ->first();

            return [
                'totalSites' => $streetlightStats->total_sites,
                'totalPoles' => $streetlightStats->total_poles,
                'surveyedPoles' => $this->getPoleStatistics($projectId)['totalSurveyedPoles'],
                'installedPoles' => $this->getPoleStatistics($projectId)['totalInstalledPoles']
            ];
        }

        // For rooftop projects - lifetime statistics
        return [
            'totalSites' => $siteModel::where('project_id', $projectId)->count(),
            'completedTasks' => $taskModel::where('project_id', $projectId)
                ->where('status', 'Completed')->count(),
            'pendingTasks' => $taskModel::where('project_id', $projectId)
                ->where('status', 'Pending')->count(),
            'inProgressTasks' => $taskModel::where('project_id', $projectId)
                ->where('status', 'In Progress')->count()
        ];
    }

    private function getUserCounts(User $user, $projectId)
    {
        $query = User::where('project_id', $projectId);

        // If user is project manager, only show their assigned users
        if ($user->role == 2) {
            $query->where(function ($q) use ($user) {
                $q->where('manager_id', $user->id)
                    ->orWhere('role', 2); // Include all project managers
            });
        }

        return [
            'projectManagers' => (clone $query)->where('role', 2)->count(),
            'siteEngineers' => (clone $query)->where('role', 1)->count(),
            'vendors' => (clone $query)->where('role', 3)->count()
        ];
    }

    private function getPoleStatistics($projectId)
    {
        $totalSurvey = Pole::whereHas('task.site', function ($q) use ($projectId) {
            $q->where('project_id', $projectId);
        })->where('isSurveyDone', true)->count();
        return [
            'totalSurveyedPoles' => $totalSurvey,
            'totalInstalledPoles' => Pole::whereHas('task.site', function ($q) use ($projectId) {
                $q->where('project_id', $projectId);
            })->where('isInstallationDone', 1)->count()
        ];
    }

    private function getUserPoleStatistics($user, $projectId, $dateRange)
    {
        $poles = Pole::whereHas('task.site', function ($q) use ($projectId, $user) {
            $q->where('project_id', $projectId)
                ->where($this->getRoleColumn($user->role), $user->id);
        })->whereBetween('updated_at', $dateRange);

        return [
            'surveyedPoles' => (clone $poles)->where('isSurveyDone', true)->count(),
            'installedPoles' => (clone $poles)->where('isInstallationDone', true)->count()
        ];
    }

    private function prepareStatistics($siteStats, $userCounts, $isStreetLightProject = false)
    {
        if ($isStreetLightProject) {
            return [
                [
                    'title' => 'Total Panchayats',
                    'value' => $siteStats['totalSites'],
                    'color' => '#cc943e'
                ],
                [
                    'title' => 'Total Poles',
                    'value' => $siteStats['totalPoles'],
                    'color' => '#fcbda1'
                ],
                [
                    'title' => 'Surveyed Poles',
                    'value' => $siteStats['surveyedPoles'],
                    'color' => '#51b1e1'
                ],
                [
                    'title' => 'Installed Poles',
                    'value' => $siteStats['installedPoles'],
                    'color' => '#4da761'
                ]
            ];
        }

        return [
            [
                'title' => 'Total Sites',
                'value' => $siteStats['totalSites'],
                'color' => '#cc943e'
            ],
            [
                'title' => 'Completed Sites',
                'value' => $siteStats['completedTasks'],
                'color' => '#4da761'
            ],
            [
                'title' => 'Pending Sites',
                'value' => $siteStats['pendingTasks'],
                'color' => '#51b1e1'
            ],
            [
                'title' => 'Rejected',
                'value' => $siteStats['inProgressTasks'],
                'color' => '#fcbda1'
            ]
        ];
    }


    private function getAvailableProjects(User $user)
    {
        return Project::when($user->role !== 0, function ($query) use ($user) {
            // For non-admin users, get projects through project_user pivot table
            $query->whereHas('users', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            });
        })->get();
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
            case 'custom':
                return [request()->start_date, request()->end_date];
            default:
                // Return all time data
                return ['1970-01-01 00:00:00', now()]; // From the Unix epoch to now
        }
    }

    public function exportToExcel()
    {
        $data = [
            (object) ['Name' => 'John Doe', 'Email' => 'john@example.com', 'Age' => 30],
            (object) ['Name' => 'Jane Smith', 'Email' => 'jane@example.com', 'Age' => 28],
        ];
        return ExcelHelper::exportMultipleSheets($data, 'users.xlsx');
    }
}
