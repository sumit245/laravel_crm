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
use App\Services\Dashboard\DashboardAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    protected DashboardAnalyticsService $analyticsService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(DashboardAnalyticsService $analyticsService)
    {
        $this->middleware('auth');
        $this->analyticsService = $analyticsService;
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

        // Prepare filters
        $filters = [
            'project_id' => $selectedProjectId,
            'date_filter' => $request->query('date_filter', 'this_month'),
            'start_date' => $request->query('start_date'),
            'end_date' => $request->query('end_date'),
        ];

        // Get available projects
        $projects = $this->getAvailableProjects($user);

        // If no project selected but projects available, use first project as default (but allow "All Projects")
        if (!$selectedProjectId && $projects->isNotEmpty() && $user->role !== UserRole::ADMIN->value) {
            // Non-admin users must have a project selected
            return redirect()->route('dashboard', ['project_id' => $projects->first()->id]);
        }

        // Get all analytics data with error handling
        try {
            $performanceAnalytics = $this->analyticsService->getProjectPerformanceAnalytics($user, $filters);
        } catch (\Exception $e) {
            \Log::error('Dashboard Performance Analytics Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user_id' => $user->id,
                'filters' => $filters
            ]);
            $performanceAnalytics = [
                'district_performance' => [],
                'top_performers' => ['engineers' => [], 'vendors' => []],
                'unified_metrics' => [
                    'streetlight' => ['total_poles' => 0, 'surveyed_poles' => 0, 'installed_poles' => 0, 'progress' => 0],
                    'rooftop' => ['total_sites' => 0, 'completed_sites' => 0, 'in_progress_sites' => 0, 'progress' => 0],
                    'combined' => ['total' => 0, 'completed' => 0, 'progress' => 0]
                ],
                'pole_speed_metrics' => [],
                'leaderboard' => [],
            ];
        }

        try {
            $meetingAnalytics = $this->analyticsService->getMeetingAnalytics($user, $filters);
        } catch (\Exception $e) {
            \Log::error('Dashboard Meeting Analytics Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user_id' => $user->id,
                'filters' => $filters
            ]);
            $meetingAnalytics = [
                'overview' => ['total_meetings' => 0, 'active_discussions' => 0, 'discussions_this_month' => 0],
                'meeting_types' => [],
                'recent_meetings' => [],
                'discussion_points' => ['total' => 0, 'resolved' => 0, 'pending' => 0],
                'top_topics' => [],
            ];
        }

        try {
            $tadaAnalytics = $this->analyticsService->getTadaAnalytics($user, $filters);
        } catch (\Exception $e) {
            \Log::error('Dashboard TADA Analytics Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user_id' => $user->id,
                'filters' => $filters
            ]);
            $tadaAnalytics = [
                'financial_overview' => [
                    'total_amount' => 0,
                    'disbursed_this_month' => 0,
                    'pending_amount' => 0,
                    'distance_travelled' => 0,
                    'avg_per_travel' => 0,
                    'avg_per_km' => 0,
                    'highest_traveller' => null,
                ],
                'per_project_disbursals' => [],
                'top_travellers' => [],
                'travel_breakdown' => ['by_vehicle' => [], 'by_status' => []],
            ];
        }

        // Legacy data for backward compatibility (if needed)
        $project = $selectedProjectId ? Project::find($selectedProjectId) : null;
        $isStreetLightProject = $project ? $project->project_type == 1 : false;

        return view('dashboard', [
            'user' => $user,
            'project' => $project,
            'projects' => $projects,
            'selected_project_id' => $selectedProjectId,
            'filters' => $filters,
            'performance_analytics' => $performanceAnalytics,
            'meeting_analytics' => $meetingAnalytics,
            'tada_analytics' => $tadaAnalytics,
            'is_streetlight_project' => $isStreetLightProject,
        ]);
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



    /**
     * Export dashboard data to Excel
     */
    public function exportToExcel(Request $request)
    {
        try {
            $user = auth()->user();
            $filters = [
                'project_id' => $request->query('project_id'),
                'date_filter' => $request->query('date_filter', 'this_month'),
                'start_date' => $request->query('start_date'),
                'end_date' => $request->query('end_date'),
            ];

            // Get all analytics data
            $performanceAnalytics = $this->analyticsService->getProjectPerformanceAnalytics($user, $filters);
            $meetingAnalytics = $this->analyticsService->getMeetingAnalytics($user, $filters);
            $tadaAnalytics = $this->analyticsService->getTadaAnalytics($user, $filters);

            // Generate filename
            $project = $filters['project_id'] ? Project::find($filters['project_id']) : null;
            $projectName = $project ? str_replace(' ', '_', $project->project_name) : 'All';
            $dateFilter = $filters['date_filter'];
            $filename = "Dashboard_Export_{$projectName}_{$dateFilter}_" . now()->format('Y-m-d') . ".xlsx";

            // Prepare data for Excel export
            $sheets = [];

            // Performance Analytics Sheet
            if (!empty($performanceAnalytics['district_performance'])) {
                $performanceData = [];
                foreach ($performanceAnalytics['district_performance'] as $pm) {
                    $performanceData[] = [
                        'Project Manager' => $pm['pm_name'],
                        'Primary District' => $pm['primary_district'] ?? 'N/A',
                        'Total Districts' => $pm['district_count'],
                        'Total Poles' => $pm['total_poles'],
                        'Surveyed Poles' => $pm['surveyed_poles'],
                        'Surveyed Progress (%)' => number_format($pm['surveyed_progress'], 2),
                        'Installed Poles' => $pm['installed_poles'],
                        'Installed Progress (%)' => number_format($pm['installed_progress'], 2),
                        'Overall Progress (%)' => number_format($pm['overall_progress'], 2),
                    ];
                }
                $sheets['Performance by PM'] = $performanceData;
            }

            // Top Performers Sheet
            if (!empty($performanceAnalytics['top_performers'])) {
                $topPerformersData = [];
                
                // Engineers
                if (!empty($performanceAnalytics['top_performers']['engineers'])) {
                    foreach ($performanceAnalytics['top_performers']['engineers'] as $index => $engineer) {
                        $topPerformersData[] = [
                            'Rank' => $index + 1,
                            'Type' => 'Engineer',
                            'Name' => $engineer['name'],
                            'Sites' => $engineer['sites'],
                            'Poles' => $engineer['poles'],
                            'Progress (%)' => number_format($engineer['progress'], 2),
                        ];
                    }
                }
                
                // Vendors
                if (!empty($performanceAnalytics['top_performers']['vendors'])) {
                    foreach ($performanceAnalytics['top_performers']['vendors'] as $index => $vendor) {
                        $topPerformersData[] = [
                            'Rank' => $index + 1,
                            'Type' => 'Vendor',
                            'Name' => $vendor['name'],
                            'Sites' => 0,
                            'Poles' => $vendor['poles'],
                            'Progress (%)' => number_format($vendor['progress'], 2),
                        ];
                    }
                }
                
                if (!empty($topPerformersData)) {
                    $sheets['Top Performers'] = $topPerformersData;
                }
            }

            // Meeting Analytics Sheet
            if (!empty($meetingAnalytics)) {
                $meetingData = [];
                
                // Overview
                if (!empty($meetingAnalytics['overview'])) {
                    $meetingData[] = [
                        'Metric' => 'Total Meetings',
                        'Value' => $meetingAnalytics['overview']['total_meetings'] ?? 0,
                    ];
                    $meetingData[] = [
                        'Metric' => 'Active Discussions',
                        'Value' => $meetingAnalytics['overview']['active_discussions'] ?? 0,
                    ];
                    $meetingData[] = [
                        'Metric' => 'Discussions This Month',
                        'Value' => $meetingAnalytics['overview']['discussions_this_month'] ?? 0,
                    ];
                }
                
                // Discussion Points
                if (!empty($meetingAnalytics['discussion_points'])) {
                    $meetingData[] = [
                        'Metric' => 'Total Discussion Points',
                        'Value' => $meetingAnalytics['discussion_points']['total'] ?? 0,
                    ];
                    $meetingData[] = [
                        'Metric' => 'Resolved Points',
                        'Value' => $meetingAnalytics['discussion_points']['resolved'] ?? 0,
                    ];
                    $meetingData[] = [
                        'Metric' => 'Pending Points',
                        'Value' => $meetingAnalytics['discussion_points']['pending'] ?? 0,
                    ];
                }
                
                if (!empty($meetingData)) {
                    $sheets['Meeting Analytics'] = $meetingData;
                }
            }

            // TA/DA Analytics Sheet
            if (!empty($tadaAnalytics)) {
                $tadaData = [];
                
                // Financial Overview
                if (!empty($tadaAnalytics['financial_overview'])) {
                    $fo = $tadaAnalytics['financial_overview'];
                    $tadaData[] = [
                        'Metric' => 'Total Amount (₹)',
                        'Value' => number_format($fo['total_amount'] ?? 0, 2),
                    ];
                    $tadaData[] = [
                        'Metric' => 'Disbursed This Month (₹)',
                        'Value' => number_format($fo['disbursed_this_month'] ?? 0, 2),
                    ];
                    $tadaData[] = [
                        'Metric' => 'Pending Approval (₹)',
                        'Value' => number_format($fo['pending_amount'] ?? 0, 2),
                    ];
                    $tadaData[] = [
                        'Metric' => 'Distance Travelled (km)',
                        'Value' => number_format($fo['distance_travelled'] ?? 0, 0),
                    ];
                    $tadaData[] = [
                        'Metric' => 'Average per Travel (₹)',
                        'Value' => number_format($fo['avg_per_travel'] ?? 0, 2),
                    ];
                    $tadaData[] = [
                        'Metric' => 'Average per km (₹)',
                        'Value' => number_format($fo['avg_per_km'] ?? 0, 2),
                    ];
                }
                
                // Top Travellers
                if (!empty($tadaAnalytics['top_travellers'])) {
                    $travellersData = [];
                    foreach ($tadaAnalytics['top_travellers'] as $index => $traveller) {
                        $travellersData[] = [
                            'Rank' => $index + 1,
                            'Staff Name' => $traveller['name'],
                            'Travels' => $traveller['travels'],
                            'Distance (km)' => number_format($traveller['distance'], 0),
                            'Amount (₹)' => number_format($traveller['amount'], 2),
                        ];
                    }
                    if (!empty($travellersData)) {
                        $sheets['Top Travellers'] = $travellersData;
                    }
                }
                
                if (!empty($tadaData)) {
                    $sheets['TA/DA Financial'] = $tadaData;
                }
            }

            // Filter out empty sheets and ensure data is properly formatted
            $validSheets = [];
            foreach ($sheets as $sheetName => $sheetData) {
                if (!empty($sheetData) && is_array($sheetData)) {
                    // Ensure all rows are arrays
                    $validData = [];
                    foreach ($sheetData as $row) {
                        if (is_array($row) || is_object($row)) {
                            $validData[] = (array) $row;
                        }
                    }
                    if (!empty($validData)) {
                        $validSheets[$sheetName] = $validData;
                    }
                }
            }

            if (empty($validSheets)) {
                return response()->json(['message' => 'No data available to export'], 400);
            }

            return \App\Helpers\ExcelHelper::exportMultipleSheets($validSheets, $filename);
            
        } catch (\Exception $e) {
            Log::error('Dashboard export error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return response()->json([
                'error' => 'Error exporting dashboard data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * AJAX endpoint for filtering dashboard data
     */
    public function filterData(Request $request)
    {
        try {
            $user = auth()->user();
            $filters = [
                'project_id' => $request->input('project_id') ? (int)$request->input('project_id') : null,
                'date_filter' => $request->input('date_filter', 'this_month'),
                'start_date' => $request->input('start_date'),
                'end_date' => $request->input('end_date'),
            ];

            $performanceAnalytics = $this->analyticsService->getProjectPerformanceAnalytics($user, $filters);
            $meetingAnalytics = $this->analyticsService->getMeetingAnalytics($user, $filters);
            $tadaAnalytics = $this->analyticsService->getTadaAnalytics($user, $filters);

            return response()->json([
                'success' => true,
                'performance' => $performanceAnalytics,
                'meetings' => $meetingAnalytics,
                'tada' => $tadaAnalytics,
            ]);
        } catch (\Exception $e) {
            Log::error('Dashboard filter error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error loading dashboard data: ' . $e->getMessage()
            ], 500);
        }
    }
}
