<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TasksController extends Controller
{
 /**
  * Display a listing of the resource.
  */
 public function index()
 {
  //
  $tasks = Task::all();
  return view('tasks.index', compact('tasks'));
 }

 /**
  * Show the form for creating a new resource.
  */
 public function create()
 {
  //
 }

 /**
  * Store a newly created resource in storage.
  */
 public function store(Request $request)
 {
  $request->validate([
   'sites'       => 'required|array',
   'activity'    => 'required|string',
   'engineer_id' => 'required|exists:users,id',
   'start_date'  => 'required|date',
   'end_date'    => 'required|date|after_or_equal:start_date',
  ]);

  foreach ($request->sites as $siteId) {
   Task::create([
    'project_id'  => $request->project_id,
    'site_id'     => $siteId,
    'activity'    => $request->activity,
    'engineer_id' => $request->engineer_id,
    'start_date'  => $request->start_date,
    'end_date'    => $request->end_date,
   ]);
  }

  return redirect()->route('projects.show', $request->project_id)
   ->with('success', 'Targets successfully added.');
 }

 /**
  * Display the specified resource.
  */
 public function show(string $id)
 {
  //
 }

 /**
  * Show the form for editing the specified resource.
  */
 public function edit(string $id)
 {
  //
 }

 /**
  * Update the specified resource in storage.
  */
 public function update(Request $request, string $id)
 {
  //
 }

 /**
  * Remove the specified resource from storage.
  */
 public function destroy(string $id)
 {
  //
  try {
   $task = Task::findOrFail($id);
   $task->delete();
   return response()->json(['success' => true]);
  } catch (\Exception $e) {
   return response()->json(['success' => false, 'message' => $e->getMessage()]);
  }
 }
}
