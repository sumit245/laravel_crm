<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Services\Logging\ActivityLogger;

/**
 * Project Data API — provides project information for the mobile app. Returns project details,
 * assigned staff, and project configuration needed by the mobile app.
 *
 * Data Flow:
 *   GET /api/projects → Return user's assigned projects → GET /api/projects/{id} →
 *   Return project details with staff assignments
 *
 * @depends-on Project, User
 * @business-domain Mobile API
 * @package App\Http\Controllers\API
 */
class ProjectController extends Controller
{
    public function __construct(
        protected ActivityLogger $activityLogger
    ) {
    }

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
                'project_name'      => 'required|string',
                'date'              => 'required|date',
                'work_order_number' => 'required|string|unique:projects',
                'rate'              => 'nullable|string',
            ]);

            // Create the project
            $project = Project::create([
                'project_name'      => $request->project_name,
                'start_date'        => $request->date,
                'work_order_number' => $request->work_order_number,
                'rate'              => $request->rate,
            ]);

            $this->activityLogger->log('project', 'created', $project, [
                'description' => "Created project '{$project->project_name}' via API"
            ]);

            // Return success response
            return response()->json([
                'message' => 'Project created successfully',
                'project' => $project,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Custom error handling if validation fails
            return response()->json([
                'error'    => 'Validation failed',
                'messages' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {

        return Project::with('sites',)->findOrFail($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProjectRequest $request, $id)
    {
        $project = Project::findOrFail($id);
        $this->activityLogger->diff($project);
        $project->update($request->validated());

        $this->activityLogger->log('project', 'updated', $project, [
            'description' => "Updated project '{$project->project_name}' via API"
        ]);

        return $project;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $project = Project::findOrFail($id);
        $projectName = $project->project_name;
        $project->delete();

        $this->activityLogger->log('project', 'deleted', null, [
            'description' => "Deleted project '{$projectName}' via API"
        ]);

        return response()->json(['message' => 'Project deleted']);
    }
}
