<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Site;
use Illuminate\Http\Request;

class SiteController extends Controller
{
 /**
  * Display a listing of the resource.
  */
 public function index()
 {
  return Site::with('project')->get(); // List all sites with their projects
 }

 /**
  * Store a newly created resource in storage.
  */
 public function store(Request $request)
 {
  try {
   $validated = $request->validate([
    'project_id' => 'required|exists:projects,id',
    'site_name'  => 'required|string',
    'state'      => 'required|string',
    'district'   => 'required|string',
    'location'   => 'required|string',
   ]);
   $site = Site::create($validated);
   return response()->json([
    'message' => 'Site Created Successfully!',
    'data'    => $site,
   ], 201);

  } catch (\Illuminate\Validation\ValidationException $e) {
   return response()->json([
    'errors' => $e->errors(),
   ], 422);
  } catch (\Exception $e) {
   return response()->json([
    'message' => 'Something went wrong',
    'error'   => $e->getMessage()], 500);
  }
 }
 /**
  * Display the specified resource.
  */
 public function show($id)
 {
  return Site::with('project')->findOrFail($id);
 }

 /**
  * Update the specified resource in storage.
  */
 public function update(Request $request, $id)
 {
  $site = Site::findOrFail($id);
  $site->update($request->all());
  return $site;
 }

 /**
  * Remove the specified resource from storage.
  */
 public function destroy($id)
 {
  $site = Site::findOrFail($id);
  $site->delete();
  return response()->json(['message' => 'Site deleted']);
 }
}
