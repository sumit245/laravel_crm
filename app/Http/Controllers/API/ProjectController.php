<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Project::with('sites')->get(); // List all projects with their sites
    }

    /**
     * Store a newly created resource in storage.
     */
    public function create(Request $request)
    {
        try {
            // Validate the incoming request
            $validated = $request->validate([
                'project_name' => 'required|string',
                'date' => 'required|date',
                'work_order_number' => 'required|string|unique:projects',
                'rate' => 'nullable|string',
            ]);

            // Create the project
            $project = Project::create([
                'project_name' => $request->project_name,
                'start_date' => $request->date,
                'work_order_number' => $request->work_order_number,
                'rate' => $request->rate,
            ]);

            // Return success response
            return response()->json([
                'message' => 'Project created successfully',
                'project' => $project,
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Custom error handling if validation fails
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        return Project::with('sites')->findOrFail($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $project = Project::findOrFail($id);
        $project->update($request->all());
        return $project;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $project = Project::findOrFail($id);
        $project->delete();
        return response()->json(['message' => 'Project deleted']);
    }
}
