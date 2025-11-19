<?php

namespace App\Http\Controllers;

use App\Contracts\DashboardServiceInterface;
use App\Contracts\AnalyticsServiceInterface;
use App\Contracts\PerformanceServiceInterface;
use App\Helpers\ExcelHelper;
use App\Models\Project;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected DashboardServiceInterface $dashboardService,
        protected AnalyticsServiceInterface $analyticsService,
        protected PerformanceServiceInterface $performanceService
    ) {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    /**
     * Show the application dashboard with cached data.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $selectedProjectId = $this->getSelectedProject($request, $user);
        $project = Project::findOrFail($selectedProjectId);
        $isStreetLightProject = $project->project_type == 1;

        // Build filters from request
        $filters = [
            'project_id' => $selectedProjectId,
            'date_filter' => $request->query('date_filter', 'today'),
            'start_date' => $request->query('start_date'),
            'end_date' => $request->query('end_date'),
        ];

        // Get dashboard data from service (with caching)
        $dashboardData = $this->dashboardService->getDashboardData(
            $user->id,
            $this->getRoleName($user->role),
            $filters
        );

        // Get performance data for role-based hierarchies
        $rolePerformances = $this->performanceService->getHierarchicalPerformance(
            $user->id,
            $user->role,
            $selectedProjectId,
            $filters
        );

        // Transform data for legacy view format
        $statistics = $this->prepareStatistics($project, $selectedProjectId, $isStreetLightProject);
        
        // Get available projects for project switcher
        $projects = $this->getAvailableProjects($user);

        return view('dashboard', compact(
            'statistics',
            'rolePerformances', 
            'isStreetLightProject',
            'project',
            'projects'
        ));
    }

    /**
     * Map role ID to role name for service.
     */
    private function getRoleName($role): string
    {
        // Handle both integer and enum values
        $roleValue = is_int($role) ? $role : $role->value;
        
        return match($roleValue) {
            0 => 'Administrator',
            1 => 'Site Engineer',
            2 => 'Project Manager',
            3 => 'Vendor',
            default => 'Unknown',
        };
    }

    /**
     * Get selected project ID based on request or user context.
     */
    private function getSelectedProject(Request $request, $user): int
    {
        if ($request->has('project_id')) {
            return (int) $request->project_id;
        }

        if ($user->project_id) {
            return (int) $user->project_id;
        }

        return Project::when($user->role !== 0, function ($query) use ($user) {
            $query->whereHas('users', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            });
        })->first()->id;
    }


    /**
     * Get projects accessible by the user.
     */
    private function getAvailableProjects($user)
    {
        return Project::when($user->role !== 0, function ($query) use ($user) {
            $query->whereHas('users', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            });
        })->get();
    }

    /**
     * Prepare statistics for dashboard display.
     */
    private function prepareStatistics($project, $projectId, $isStreetLightProject)
    {
        if ($isStreetLightProject) {
            $streetlightStats = \App\Models\Streetlight::where('project_id', $projectId)
                ->selectRaw('COUNT(DISTINCT panchayat) as total_sites, SUM(total_poles) as total_poles')
                ->first();

            $totalSurvey = \App\Models\Pole::whereHas('task.site', function ($q) use ($projectId) {
                $q->where('project_id', $projectId);
            })->where('isSurveyDone', true)->count();
            
            $totalInstalled = \App\Models\Pole::whereHas('task.site', function ($q) use ($projectId) {
                $q->where('project_id', $projectId);
            })->where('isInstallationDone', 1)->count();

            return [
                [
                    'title' => 'Total Panchayats',
                    'value' => $streetlightStats->total_sites ?? 0,
                    'color' => '#cc943e'
                ],
                [
                    'title' => 'Total Poles',
                    'value' => $streetlightStats->total_poles ?? 0,
                    'color' => '#fcbda1'
                ],
                [
                    'title' => 'Surveyed Poles',
                    'value' => $totalSurvey,
                    'color' => '#51b1e1'
                ],
                [
                    'title' => 'Installed Poles',
                    'value' => $totalInstalled,
                    'color' => '#4da761'
                ]
            ];
        }

        // Rooftop project statistics
        $totalSites = \App\Models\Site::where('project_id', $projectId)->count();
        $completedTasks = \App\Models\Task::where('project_id', $projectId)
            ->where('status', 'Completed')->count();
        $pendingTasks = \App\Models\Task::where('project_id', $projectId)
            ->where('status', 'Pending')->count();
        $inProgressTasks = \App\Models\Task::where('project_id', $projectId)
            ->where('status', 'In Progress')->count();

        return [
            [
                'title' => 'Total Sites',
                'value' => $totalSites,
                'color' => '#cc943e'
            ],
            [
                'title' => 'Completed Sites',
                'value' => $completedTasks,
                'color' => '#4da761'
            ],
            [
                'title' => 'Pending Sites',
                'value' => $pendingTasks,
                'color' => '#51b1e1'
            ],
            [
                'title' => 'Rejected',
                'value' => $inProgressTasks,
                'color' => '#fcbda1'
            ]
        ];
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
