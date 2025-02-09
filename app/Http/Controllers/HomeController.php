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

        return view('dashboard', compact('statistics', 'projects', 'projectManagers'));
    }
}
