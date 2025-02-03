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
        $staffCount = User::whereIn('role', [1, 2])->count();
        $vendorCount = User::where('role', 3)->count();

        // Get Project Managers
        $projectManagers = User::where('role', 2)->get()->map(function ($pm) {
            // Total & completed tasks for the Project Manager
            $totalTasksPM = Task::where('manager_id', $pm->id)->count();
            $completedTasksPM = Task::where('manager_id', $pm->id)->where('status', 'Completed')->count();

            // Get Site Engineers under this PM
            $siteEngineers = User::where('role', 1)
                ->where('manager_id', $pm->id)
                ->get()
                ->map(function ($se) {
                    // Total & completed tasks for Site Engineer
                    $totalTasksSE = Task::where('engineer_id', $se->id)->count();
                    $completedTasksSE = Task::where('engineer_id', $se->id)->where('status', 'Completed')->count();

                    // Get Vendors under this Site Engineer
                    $vendors = User::where('role', 3)
                        ->where('site_engineer_id', $se->id)
                        ->get()
                        ->map(function ($vendor) {
                            // Total & completed tasks for Vendor
                            $totalTasksVendor = Task::where('vendor_id', $vendor->id)->count();
                            $completedTasksVendor = Task::where('vendor_id', $vendor->id)->where('status', 'Completed')->count();

                            return (object) [
                                'id' => $vendor->id,
                                'name' => $vendor->firstName,
                                'performance' => "$completedTasksVendor/$totalTasksVendor"
                            ];
                        });

                    return (object) [
                        'id' => $se->id,
                        'name' => $se->firstName,
                        'performance' => "$completedTasksSE/$totalTasksSE",
                        'vendors' => $vendors
                    ];
                });

            return (object) [
                'id' => $pm->id,
                'name' => $pm->firstName,
                'performance' => "$completedTasksPM/$totalTasksPM",
                'siteEngineers' => $siteEngineers
            ];
        });

        $statistics = [
            ['title' => 'Sites', 'value' => $siteCount, 'link' => route('sites.index')],
            ['title' => 'Vendors', 'value' => $vendorCount, 'link' => route('uservendors.index')],
            ['title' => 'Staffs', 'value' => $staffCount, 'link' => route('staff.index')],
        ];

        return view('dashboard', compact('statistics', 'projects', 'projectManagers'));
    }
}
