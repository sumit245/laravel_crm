<?php

namespace App\Http\Controllers;

use App\Models\Project; // Model for vendors
use App\Models\Site;
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
        $projects =     Project::all();

        if ($projectId) {
            $siteCount    = Site::where('project_id', $projectId)->count();
        } else {
            $siteCount = Site::count();
        }
        $staffCount   = User::whereIn('role', [1, 2])->count(); // Count staff
        $vendorCount  = User::where('role', 3)->count(); // Count vendors

        $statistics = [
            [
                'title'             => 'Sites',
                'value'             => $siteCount,
                'change_class'      => 'text-success',
                'change_icon'       => 'mdi-menu-down',
                'change_percentage' => '+0.1%',
                'link'              => route('sites.index'), // Link to the sites page
            ],
            [
                'title'             => 'Vendors',
                'value'             => $vendorCount,
                'change_class'      => 'text-success',
                'change_icon'       => 'mdi-menu-down',
                'change_percentage' => '+0.1%',
                'link'              => route('uservendors.index'), // Link to the sites page
            ],

            [
                'title'             => 'Staffs',
                'value'             => $staffCount,
                'change_class'      => 'text-success',
                'change_icon'       => 'mdi-menu-down',
                'change_percentage' => '+0.1%',
                'link'              => route('staff.index'), // Link to the sites page
            ],
        ];
        return view('dashboard', compact('statistics', 'projects'));
    }
}
