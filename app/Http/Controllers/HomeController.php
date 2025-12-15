<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Pole;
use App\Models\Project;
use App\Models\Site;
use App\Models\Streetlight;
use App\Models\StreetlightTask;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

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
     * @return \Illuminate\Contracts\Support\Renderable|\Illuminate\Http\RedirectResponse
     */

    public function index(Request $request)
    {
        $user = auth()->user();
        $selectedProjectId = $this->getSelectedProject($request, $user);

        if (!$selectedProjectId) {
            return redirect()->route('projects.index')->with('error', 'No project assigned. Please select a project.');
        }

        $project = Project::findOrFail($selectedProjectId);
        $dateRange = $this->getDateRange($request->query('date_filter', 'today'));
        $isStreetLightProject = $project->project_type == 1;
        $taskModel = $isStreetLightProject ? StreetlightTask::class : Task::class;
        $siteModel = $isStreetLightProject ? Streetlight::class : Site::class;
        $siteStats = $this->getSiteStatistics($siteModel, $taskModel, $selectedProjectId, $dateRange, $isStreetLightProject);
        $poleStats = $isStreetLightProject ?
            $this->getPoleStatistics($selectedProjectId) :
            ['totalSurveyedPoles' => null, 'totalInstalledPoles' => null];
        $rolePerformances = $this->calculateRolePerformances(
            $user,
            $selectedProjectId,
            $taskModel,
            $dateRange,
            $isStreetLightProject
        );
        $userCounts = $this->getUserCounts($user, $selectedProjectId);
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
        $roles = [
            'Project Manager' => UserRole::PROJECT_MANAGER->value,
            'Site Engineer' => UserRole::SITE_ENGINEER->value,
            'Vendor' => UserRole::VENDOR->value
        ];
        $rolePerformances = [];

        foreach ($roles as $roleName => $roleId) {
            $usersQuery = User::where('role', $roleId);

            if ($user->role === UserRole::PROJECT_MANAGER->value) {
                if ($roleId === UserRole::PROJECT_MANAGER->value) {
                    $usersQuery->where('project_id', $projectId);
                } else {
                    $usersQuery->where('manager_id', $user->id)
                        ->where('project_id', $projectId);
                }
            } else {
                $usersQuery->where('project_id', $projectId);
            }

            $users = $usersQuery->get()->map(function ($user) use ($taskModel, $projectId, $dateRange, $roleName, $isStreetLightProject) {
                return $this->calculateUserPerformance($user, $taskModel, $projectId, $dateRange, $roleName, $isStreetLightProject);
            })->filter();

            if ($isStreetLightProject) {
                $users = $users->sortByDesc('surveyedPoles');
            } else {
                $users = $users->sortByDesc('completedTasks');
            }

            $users = $users->values();
            $rolePerformances[$roleName] = $users;
        }

        return $rolePerformances;
    }

    private function calculateUserPerformance($user, $taskModel, $projectId, $dateRange, $roleName, $isStreetLightProject)
    {
        if ($isStreetLightProject) {
            $totalPoles = Streetlight::whereHas('streetlightTasks', function ($q) use ($user, $dateRange) {
                $q->where($this->getRoleColumn($user->role), $user->id)
                    ->whereBetween('created_at', $dateRange);
            })->sum('total_poles');

            $surveyedPoles = Pole::whereHas('task', function ($q) use ($projectId, $user) {
                $q->where('project_id', $projectId)
                    ->where($this->getRoleColumn($user->role), $user->id);
            })->where('isSurveyDone', true)
                ->whereBetween('updated_at', $dateRange)->count();

            $installedPoles = Pole::whereHas('task', function ($q) use ($projectId, $user, $dateRange) {
                $q->where('project_id', $projectId)
                    ->where($this->getRoleColumn($user->role), $user->id);
            })->where('isInstallationDone', true)
                ->whereBetween('updated_at', $dateRange)->count();

            $performance = $totalPoles > 0 ? ($surveyedPoles / $totalPoles) * 100 : 0;
            $performanceSurvey = $totalPoles > 0 ? ($surveyedPoles / $totalPoles) * 100 : 0;
            $performanceinstallation = $totalPoles > 0 ? ($installedPoles / $totalPoles) * 100 : 0;

            return (object) [
                'id' => $user->id,
                'name' => $user->firstName . " " . $user->lastName,
                'vendor_name' => $user->name ?? "",
                'image' => $user->image,
                'role' => $roleName,
                'totalTasks' => $totalPoles,
                'surveyedPoles' => $surveyedPoles,
                'installedPoles' => $installedPoles,
                'performance' => $performance,
                'performanceSurvey' => $performanceSurvey,
                'performanceInstallation' => $performanceinstallation,
            ];
        } else {
            $tasksQuery = $taskModel::where('project_id', $projectId)
                ->where($this->getRoleColumn($user->role), $user->id)
                ->whereBetween('updated_at', $dateRange);

            $totalTasks = $tasksQuery->count();
            if ($totalTasks == 0) {
                return null;
            }

            $completedTasks = $tasksQuery->where('status', 'Completed')->count();
            $performance = ($completedTasks / $totalTasks) * 100;
        }

        return (object) [
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
            UserRole::SITE_ENGINEER->value => 'engineer_id',
            UserRole::PROJECT_MANAGER->value => 'manager_id',
            UserRole::VENDOR->value => 'vendor_id'
        ][$role];
    }

    private function getSelectedProject(Request $request, User $user)
    {
        if ($request->has('project_id')) {
            return $request->project_id;
        }

        if ($user->project_id) {
            return $user->project_id;
        }

        $project = Project::when($user->role !== UserRole::ADMIN->value, function ($query) use ($user) {
            $query->whereHas('users', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            });
        })->first();

        return $project ? $project->id : null;
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

        if ($user->role === UserRole::PROJECT_MANAGER->value) {
            $query->where(function ($q) use ($user) {
                $q->where('manager_id', $user->id)
                    ->orWhere('role', UserRole::PROJECT_MANAGER->value);
            });
        }

        return [
            'projectManagers' => (clone $query)->where('role', UserRole::PROJECT_MANAGER->value)->count(),
            'siteEngineers' => (clone $query)->where('role', UserRole::SITE_ENGINEER->value)->count(),
            'vendors' => (clone $query)->where('role', UserRole::VENDOR->value)->count()
        ];
    }

    private function getPoleStatistics($projectId)
    {
        $totalSurvey = Pole::whereHas('task.site', function ($q) use ($projectId) {
            $q->where('project_id', $projectId);
        })->where('isSurveyDone', true)->count();
        $totalInstalled = Pole::whereHas('task.site', function ($q) use ($projectId) {
            $q->where('project_id', $projectId);
        })->where('isInstallationDone', 1)->count();
        return [
            'totalSurveyedPoles' => $totalSurvey,
            'totalInstalledPoles' => $totalInstalled
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
        return Project::when($user->role !== UserRole::ADMIN->value, function ($query) use ($user) {
            $query->whereHas('users', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            });
        })->get();
    }

    private function getDateRange($filter)
    {
        switch ($filter) {
            case 'today':
                return [now()->subDay(), now()];
            case 'this_week':
                return [now()->startOfWeek(), now()->endOfWeek()];
            case 'this_month':
                return [now()->startOfMonth(), now()->endOfMonth()];
            case 'all_time':
                return [Carbon::createFromTimestamp(0), now()];
            case 'custom':
                $start = request()->start_date;
                $end = request()->end_date;
                return [Carbon::parse($start)->startOfDay(), Carbon::parse($end)->endOfDay()];
            default:
                return [now()->subDay(), now()];
        }
    }



    public function exportToExcel()
    {
        // TODO: Implement actual dashboard data export
        return redirect()->back()->with('error', 'Export functionality not yet implemented.');
    }
}
