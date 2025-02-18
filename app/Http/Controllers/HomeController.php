<?php

namespace App\Http\Controllers;

use App\Models\Project; // Model for vendors
use App\Models\Site;
use App\Models\Streetlight;
use App\Models\StreetlightTask;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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

        // Admins & Project Managers can select any project, others are restricted
        $projectId = $user->role == 0 || $user->role == 2
            ? $request->query('project_id', session('project_id'))
            : $user->project_id;

        // Get the project details
        $project = Project::findOrFail($projectId);

        // Check if the project_type is 1 (StreetLight-related project)
        $isStreetLightProject = $project->project_type == 1;

        // Get only the project assigned to the logged-in user
        $projects = ($user->role == 0) ? Project::all() : Project::where('id', $projectId)->get();

        // Site-related statistics (with conditional logic for project_type)
        if ($isStreetLightProject) {
            // If the project type is 1, use StreetLight and StreetLightTasks models
            $siteCount = Streetlight::where('project_id', $projectId)->count();
            $assignedSites = StreetlightTask::whereNotNull('site_id')
                ->whereHas('site', fn($q) => $q->where('project_id', $projectId))
                ->distinct('site_id')
                ->count();

            $completedSitesCount = StreetLight::whereHas('streetLightTasks', fn($query) =>
            $query->where('status', 'Completed'))
                ->where('project_id', $projectId)
                ->count();

            $pendingSitesCount = StreetLight::whereHas('streetLightTasks', fn($query) =>
            $query->whereIn('status', ['Pending', 'In Progress']))
                ->where('project_id', $projectId)
                ->count();

            $rejectedSitesCount = StreetLight::whereHas('streetLightTasks', fn($query) =>
            $query->where('status', 'Rejected'))
                ->where('project_id', $projectId)
                ->count();
        } else {
            // If the project type is not 1, use the existing Site and Task models
            $siteCount = Site::where('project_id', $projectId)->count();
            $assignedSites = Task::whereNotNull('site_id')
                ->whereHas('site', fn($q) => $q->where('project_id', $projectId))
                ->distinct('site_id')
                ->count();

            $completedSitesCount = Site::whereHas('tasks', fn($query) =>
            $query->where('status', 'Completed'))
                ->where('project_id', $projectId)
                ->count();

            $pendingSitesCount = Site::whereHas('tasks', fn($query) =>
            $query->whereIn('status', ['Pending', 'In Progress']))
                ->where('project_id', $projectId)
                ->count();

            $rejectedSitesCount = Site::whereHas('tasks', fn($query) =>
            $query->where('status', 'Rejected'))
                ->where('project_id', $projectId)
                ->count();
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

        // Fetch site engineers for the project
        $siteEngineers = User::where('role', 1)
            ->where('project_id', $projectId)
            ->whereIn('id', function ($query) use ($projectId) {
                $query->select('user_id')->from('project_user');
            })
            ->get()
            ->map(function ($se) use ($projectId, $isStreetLightProject) {
                if ($isStreetLightProject) {
                    // If it's a StreetLight project, use StreetlightTask
                    $totalTasksSE = StreetlightTask::where('engineer_id', $se->id)
                        ->where('project_id', $projectId)
                        ->count();
                    $completedTasksSE = StreetlightTask::where('engineer_id', $se->id)
                        ->where('project_id', $projectId)
                        ->where('status', 'Completed')
                        ->count();
                } else {
                    // For non-StreetLight projects, use Task model
                    $totalTasksSE = Task::where('engineer_id', $se->id)
                        ->where('project_id', $projectId)
                        ->count();
                    $completedTasksSE = Task::where('engineer_id', $se->id)
                        ->where('project_id', $projectId)
                        ->where('status', 'Completed')
                        ->count();
                }

                $performancePercentageSE = $totalTasksSE > 0 ? ($completedTasksSE / $totalTasksSE) * 100 : 0;

                return (object) [
                    'id' => $se->id,
                    'name' => $se->firstName . " " . $se->lastName,
                    'image' => $se->image,
                    'role' => "Site Engineer",
                    'performance' => "$completedTasksSE/$totalTasksSE",
                    'performancePercentage' => $performancePercentageSE,
                ];
            })->sortByDesc('performancePercentage')
            ->values();


        // Fetch vendors for the project
        $vendors = User::where('role', 3)
            ->where('project_id', $projectId)
            ->whereIn('id', function ($query) use ($projectId) {
                $query->select('user_id')->from('project_user');
            })
            ->get()
            ->map(function ($vendor) use ($projectId, $isStreetLightProject) {
                if ($isStreetLightProject) {
                    // If it's a StreetLight project, use StreetlightTask
                    $totalTasksVendor = StreetlightTask::where('vendor_id', $vendor->id)
                        ->where('project_id', $projectId)
                        ->count();
                    $completedTasksVendor = StreetlightTask::where('vendor_id', $vendor->id)
                        ->where('project_id', $projectId)
                        ->where('status', 'Completed')
                        ->count();
                } else {
                    // For non-StreetLight projects, use Task model
                    $totalTasksVendor = Task::where('vendor_id', $vendor->id)
                        ->where('project_id', $projectId)
                        ->count();
                    $completedTasksVendor = Task::where('vendor_id', $vendor->id)
                        ->where('project_id', $projectId)
                        ->where('status', 'Completed')
                        ->count();
                }

                $performancePercentageVendor = $totalTasksVendor > 0 ? ($completedTasksVendor / $totalTasksVendor) * 100 : 0;

                return (object) [
                    'id' => $vendor->id,
                    'name' => $vendor->name,
                    'image' => $vendor->image,
                    'role' => "Vendor",
                    'performance' => "$completedTasksVendor/$totalTasksVendor",
                    'performancePercentage' => $performancePercentageVendor,
                ];
            })->sortByDesc('performancePercentage')
            ->values();

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

        // Performance data
        $performanceData = [
            'top_performers' => [
                'title' => 'Top Performers',
                'color' => 'green',
                'data' => $projectManagers->sortByDesc('performancePercentage')->values(),
            ]
        ];

        return view('dashboard', compact('statistics', 'projects', 'projectManagers', 'performanceData', 'siteEngineers', 'vendors'));
    }
}
