<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Project; // Model for vendors
use App\Models\Site;
use App\Models\Task;
use App\Models\User;

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
 public function index()
 {
  $projectCount   = Project::count();
  $siteCount      = Site::count();
  $taskCount      = Task::count();
  $inventoryCount = Inventory::count();
  $staffCount     = User::whereIn('role', [1, 2])->count(); // Count staff
  $vendorCount    = User::where('role', 3)->count(); // Count vendors

  $statistics = [
   [
    'title'             => 'Projects',
    'value'             => $projectCount,
    'change_class'      => 'text-success',
    'change_icon'       => 'mdi-menu-up',
    'change_percentage' => '-0.5%',
    'link'              => route('projects.index'), // Link to the projects page
   ],
   [
    'title'             => 'Sites',
    'value'             => $siteCount,
    'change_class'      => 'text-success',
    'change_icon'       => 'mdi-menu-down',
    'change_percentage' => '+0.1%',
    'link'              => route('sites.index'), // Link to the sites page
   ],
   [
    'title'             => 'Tasks',
    'value'             => $taskCount,
    'change_class'      => 'text-success',
    'change_icon'       => 'mdi-menu-down',
    'change_percentage' => '+0.1%',
    'link'              => route('tasks.index'), // Link to the sites page
   ],
   [
    'title'             => 'Inventory',
    'value'             => $inventoryCount,
    'change_class'      => 'text-success',
    'change_icon'       => 'mdi-menu-down',
    'change_percentage' => '+0.1%',
    'link'              => route('inventory.index'), // Link to the sites page
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
  return view('dashboard', compact('statistics'));
 }
}

//  public function dashboard()
//  {
//   // Fetch the data dynamically from the database
//  }
// }
