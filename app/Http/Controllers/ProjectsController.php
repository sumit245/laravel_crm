<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Project;
use App\Models\State;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProjectsController extends Controller
{
 /**
  * @var string[]
  */
 protected array $sortFields = ['start_date', 'end_date', 'rate', 'project_capacity'];

 /**
  * UsersController constructor.
  *
  * @param User $user
  */
 public function __construct(public Project $project)
 {
 }

 /**
  * Display a listing of the resource.
  */
 public function index()
 {
  //
  $projects = Project::all();
  return view('projects.index', compact('projects'));
 }

 /**
  * Show the form for creating a new resource.
  */
 public function create()
 {
  //
  $states = State::all();
  return view('projects.create', compact('states'));
 }

 /**
  * Store a newly created resource in storage.
  */
 public function store(Request $request)
 {
  // Validate the incoming data without requiring a username
  $validated = $request->validate([
   'project_name'      => 'required|string',
   'project_in_state'  => 'string',
   'start_date'        => 'required|date',
   'end_date'          => 'required|date',
   'work_order_number' => 'required|string|unique:projects',
   'rate'              => 'nullable|string',
   'project_capacity'  => 'nullable|string',
   'total'             => 'string',
   'description'       => 'string',
  ]);

  try {
   $project = Project::create($validated);

   return redirect()->route('projects.show', $project->id)
    ->with('success', 'Inventory created successfully.');
  } catch (\Exception $e) {
   // Catch database or other errors
   $errorMessage = $e->getMessage();
   return redirect()->back()
    ->withErrors(['error' => $errorMessage])
    ->withInput();
  }
 }

 /**
  * Display the specified resource.
  */
 public function show(string $id)
 {
  $project = Project::with([
   'stores',
   'sites.districtRelation',
   'sites.stateRelation',
  ])->findOrFail($id);
  $users          = User::where('role', '!=', 3)->get();
  $targets        = Task::where('project_id', $project->id)->with('site', 'engineer')->get();
  $sites          = $project->sites; // All sites related to this project
  $engineers      = User::where('role', 1)->get(); // Engineers with role 1
  $state          = State::where('id', $project->project_in_state)->get();
  $inventoryItems = Inventory::all();

  Log::info($state);
  return view('projects.show', compact('project', 'state', 'inventoryItems', 'users', 'sites', 'engineers', 'targets'));
 }

 /**
  * Show the form for editing the specified resource.
  */
 public function edit(string $id)
 {
  $project = Project::findOrFail($id);
  $state   = State::where('id', $project->project_in_state)->get();

  return view('projects.edit', compact('project', 'state'));
 }

 /**
  * Update the specified resource in storage.
  */
 public function update(Request $request, Project $project)
 {
  try {

   // Validate the incoming data without requiring a username
   $validated = $request->validate([
    'project_name'      => 'required|string',
    'project_in_state'  => 'string',
    'start_date'        => 'required|date',
    'end_date'          => 'required|date',
    'work_order_number' => 'required',
    'rate'              => 'nullable|string',
    'project_capacity'  => 'nullable|string',
    'total'             => 'string',
    'description'       => 'string',
   ]);
   $project->update($validated);
   return redirect()->route('projects.show', compact('project'))
    ->with('success', 'Inventory updated successfully.');
  } catch (\Exception $e) {
   // Catch database or other errors
   $errorMessage = $e->getMessage();

   return redirect()->back()
    ->withErrors(['error' => $errorMessage])
    ->withInput();
  }
  //
 }

 /**
  * Remove the specified resource from storage.
  */
 public function destroy(string $id)
 {
  //
  try {
   $project = Project::findOrFail($id);
   $project->delete();
   return response()->json(['message' => 'Project deleted']);
  } catch (\Exception $e) {
   return response()->json(['message' => $e->getMessage()]);
  }
 }
}
