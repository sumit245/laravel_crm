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

        if ($projectId) {
            $siteCount = Site::where('project_id', $projectId)->count();
        } else {
            $siteCount = Site::count();
        }

        $staffCount = User::whereIn('role', [1, 2])->count(); // Count staff
        $vendorCount = User::where('role', 3)->count(); // Count vendors

        // Get Project Managers
        $projectManagers = User::where('role', 1)->get()->map(function ($pm) {
            $totalPmTasks = Task::where('engineer_id', $pm->id)->count();
            $completedPmTasks = Task::where('engineer_id', $pm->id)->where('status', 'completed')->count();

            // Get Site Engineers reporting to this PM
            $siteEngineers = User::where('role', 2)->where('manager_id', $pm->id)->get()->map(function ($se) {
                $totalSeTasks = Task::where('engineer_id', $se->id)->count();
                $completedSeTasks = Task::where('engineer_id', $se->id)->where('status', 'completed')->count();

                // Get Vendors reporting to this Site Engineer
                $vendors = User::where('role', 3)->where('site_engineer_id', $se->id)->get()->map(function ($vendor) {
                    $totalVendorTasks = Task::where('vendor_id', $vendor->id)->count();
                    $completedVendorTasks = Task::where('vendor_id', $vendor->id)->where('status', 'completed')->count();
                    return (object) [
                        'id' => $vendor->id,
                        'name' => $vendor->name,
                        'performance' => "$completedVendorTasks/$totalVendorTasks"
                    ];
                });

                return (object) [
                    'id' => $se->id,
                    'name' => $se->firstName,
                    'performance' => "$completedSeTasks/$totalSeTasks",
                    'vendors' => $vendors
                ];
            });

            return (object) [
                'id' => $pm->id,
                'name' => $pm->firstName,
                'performance' => "$completedPmTasks/$totalPmTasks",
                'siteEngineers' => $siteEngineers
            ];
        });

        $statistics = [
            [
                'title' => 'Sites',
                'value' => $siteCount,
                'change_class' => 'text-success',
                'change_icon' => 'mdi-menu-down',
                'change_percentage' => '+0.1%',
                'link' => route('sites.index'),
            ],
            [
                'title' => 'Vendors',
                'value' => $vendorCount,
                'change_class' => 'text-success',
                'change_icon' => 'mdi-menu-down',
                'change_percentage' => '+0.1%',
                'link' => route('uservendors.index'),
            ],
            [
                'title' => 'Staffs',
                'value' => $staffCount,
                'change_class' => 'text-success',
                'change_icon' => 'mdi-menu-down',
                'change_percentage' => '+0.1%',
                'link' => route('staff.index'),
            ],
        ];

        return view('dashboard', compact('statistics', 'projects', 'projectManagers'));
    }
}
