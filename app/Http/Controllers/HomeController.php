<?php

namespace App\Http\Controllers;

use App\Models\Project; // Model for vendors
use App\Models\Site;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;

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
        $projectId = $request->query('project_id', session('project_id'));
        $projects = Project::all();

        $siteCount = $projectId ? Site::where('project_id', $projectId)->count() : Site::count();

        // Assigned sites (Sites that have at least one task assigned)
        $assignedSites = Task::whereNotNull('site_id')
            ->when($projectId, function ($query) use ($projectId) {
                return $query->whereHas('site', function ($q) use ($projectId) {
                    $q->where('project_id', $projectId);
                });
            })
            ->distinct('site_id')
            ->count();

        // Completed sites
        $completedSites = Site::whereHas('tasks', function ($query) {
            $query->where('status', 'Completed');
        })
            ->when($projectId, function ($query) use ($projectId) {
                return $query->where('project_id', $projectId);
            })
            ->count();

        $rejectedSites = Site::whereHas('tasks', function ($query) {
            $query->where('status', 'Rejected');
        })
            ->when($projectId, function ($query) use ($projectId) {
                return $query->where('project_id', $projectId);
            })
            ->count();

        // Pending sites
        $pendingSites = Site::whereHas('tasks', function ($query) {
            $query->whereIn('status', ['Pending', 'In Progress']);
        })
            ->when($projectId, function ($query) use ($projectId) {
                return $query->where('project_id', $projectId);
            })
            ->count();

        $staffCount = User::whereIn('role', [1, 2])->count();
        $vendorCount = User::where('role', 3)->count();

        // Get Project Managers and filter performance based on the selected project
        $projectManagers = User::where('role', 2)->get()->map(function ($pm) use ($projectId) {
            // Total & completed tasks for the Project Manager in the selected project
            $totalTasksPM = Task::where('manager_id', $pm->id)
                ->when($projectId, fn($q) => $q->where('project_id', $projectId))
                ->count();
            $completedTasksPM = Task::where('manager_id', $pm->id)
                ->when($projectId, fn($q) => $q->where('project_id', $projectId))
                ->where('status', 'Completed')
                ->count();
            $performancePercentagePM = $totalTasksPM > 0 ? ($completedTasksPM / $totalTasksPM) * 100 : 0;

            // Get Site Engineers under this PM
            $siteEngineers = User::where('role', 1)
                ->where('manager_id', $pm->id)
                ->get()
                ->map(function ($se) use ($projectId) {
                    // Total & completed tasks for Site Engineer in the selected project
                    $totalTasksSE = Task::where('engineer_id', $se->id)
                        ->when($projectId, fn($q) => $q->where('project_id', $projectId))
                        ->count();
                    $completedTasksSE = Task::where('engineer_id', $se->id)
                        ->when($projectId, fn($q) => $q->where('project_id', $projectId))
                        ->where('status', 'Completed')
                        ->count();
                    $performancePercentageSE = $totalTasksSE > 0 ? ($completedTasksSE / $totalTasksSE) * 100 : 0;

                    // Get Vendors under this Site Engineer
                    $vendors = User::where('role', 3)
                        ->where('site_engineer_id', $se->id)
                        ->get()
                        ->map(function ($vendor) use ($projectId) {
                            // Total & completed tasks for Vendor in the selected project
                            $totalTasksVendor = Task::where('vendor_id', $vendor->id)
                                ->when($projectId, fn($q) => $q->where('project_id', $projectId))
                                ->count();
                            $completedTasksVendor = Task::where('vendor_id', $vendor->id)
                                ->when($projectId, fn($q) => $q->where('project_id', $projectId))
                                ->where('status', 'Completed')
                                ->count();
                            $performancePercentageVendor = $totalTasksVendor > 0 ? ($completedTasksVendor / $totalTasksVendor) * 100 : 0;

                            return (object) [
                                'id' => $vendor->id,
                                'name' => $vendor->name,
                                'performance' => "$completedTasksVendor/$totalTasksVendor",
                                'performancePercentage' => $performancePercentageVendor
                            ];
                        });

                    return (object) [
                        'id' => $se->id,
                        'name' => $se->firstName,
                        'performance' => "$completedTasksSE/$totalTasksSE",
                        'performancePercentage' => $performancePercentageSE,
                        'vendors' => $vendors
                    ];
                })->sortByDesc('performancePercentage') // Sort vendors by performance
                ->values();

            return (object) [
                'id' => $pm->id,
                'name' => $pm->firstName,
                'performance' => "$completedTasksPM/$totalTasksPM",
                'siteEngineers' => $siteEngineers,
                'performancePercentage' => $performancePercentagePM,
            ];
        })->sortByDesc('performancePercentage') // Sort vendors by performance
            ->values();

        // Sort project managers by performance percentage in descending order
        $projectManagers = $projectManagers->sortByDesc('performancePercentage')->values();

        $statistics = [
            [
                'title' => 'Sites',
                'values' => [
                    'Total' => $siteCount,
                    'Assigned' => $assignedSites,
                    'Completed' => $completedSites,
                    'Pending' => $pendingSites
                ],
                'link' => route('sites.index')
            ],
            ['title' => 'Vendors', 'value' => $vendorCount, 'link' => route('uservendors.index')],
            ['title' => 'Staffs', 'value' => $staffCount, 'link' => route('staff.index')],
        ];

        $performanceData = [
            'top_performers' => [
                'title' => 'Top Performers',
                'color' => 'green',
                'data' => $projectManagers->take(10)->map(function ($pm) {
                    return [
                        'id' => $pm->id,
                        'name' => $pm->name,
                        'role' => 'Project Manager',
                        'status' => $pm->performance, // e.g. "3/4 Projects"
                        'avatar' => 'path/to/avatar.jpg', // Replace with actual avatar path
                        'subordinates' => $pm->siteEngineers->map(function ($se) {
                            return [
                                'id' => $se->id,
                                'name' => $se->name,
                                'role' => 'Site Engineer',
                                'status' => $se->performance, // e.g. "2/3 Tasks"
                                'avatar' => 'path/to/avatar2.jpg', // Replace with actual avatar path
                                'subordinates' => $se->vendors->map(function ($vendor) {
                                    return [
                                        'id' => $vendor->id,
                                        'name' => $vendor->name,
                                        'role' => 'Vendor',
                                        'status' => $vendor->performance, // e.g. "5/7 Tasks"
                                        'avatar' => 'path/to/avatar3.jpg', // Replace with actual avatar path
                                    ];
                                })->toArray(), // Convert to array
                            ];
                        })->toArray(), // Convert to array
                    ];
                })->toArray(), // Convert to array
            ],
            'worst_performers' => [
                'title' => 'Weak Performers',
                'color' => 'red',
                'data' => $projectManagers->sortBy('performancePercentage')->take(10)->map(function ($pm) {
                    return [
                        'id' => $pm->id,
                        'name' => $pm->name,
                        'role' => 'Project Manager',
                        'status' => $pm->performance,
                        'avatar' => 'path/to/avatar.jpg',
                        'subordinates' => $pm->siteEngineers->map(function ($se) {
                            return [
                                'id' => $se->id,
                                'name' => $se->name,
                                'role' => 'Site Engineer',
                                'status' => $se->performance,
                                'avatar' => 'path/to/avatar2.jpg',
                                'subordinates' => $se->vendors->map(function ($vendor) {
                                    return [
                                        'id' => $vendor->id,
                                        'name' => $vendor->name,
                                        'role' => 'Vendor',
                                        'status' => $vendor->performance,
                                        'avatar' => 'path/to/avatar3.jpg',
                                    ];
                                })->toArray(),
                            ];
                        })->toArray(),
                    ];
                })->toArray(),
            ],
            'completed_projects' => [
                'title' => 'Completed Targets',
                'color' => 'green',
                'data' => [
                    ['count' => $completedSites],
                ],
            ],
            'rejected_projects' => [
                'title' => 'Rejected Targets',
                'color' => 'red',
                'data' => [
                    ['count' => $rejectedSites],
                ],
            ],
        ];


        return view('dashboard', compact('statistics', 'projects', 'projectManagers', 'performanceData'));
    }
}
