<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\{User, StreetlightTask, Task, Pole, Project};
use Illuminate\Http\Request;

class PerformanceDebugController extends Controller
{
    /**
     * Debug performance data for a specific project and user.
     */
    public function debug(Request $request)
    {
        $projectId = $request->get('project_id', 11);
        $userId = $request->get('user_id', auth()->id());
        
        $project = Project::find($projectId);
        $user = User::find($userId);
        
        if (!$project || !$user) {
            return response()->json(['error' => 'Project or User not found']);
        }
        
        $isStreetlight = $project->project_type == 1;
        $roleColumn = match($user->role) {
            UserRole::SITE_ENGINEER->value => 'engineer_id',
            UserRole::PROJECT_MANAGER->value => 'manager_id',
            UserRole::VENDOR->value => 'vendor_id',
            default => 'engineer_id'
        };
        
        $debug = [
            'project' => [
                'id' => $project->id,
                'name' => $project->project_name,
                'type' => $isStreetlight ? 'Streetlight' : 'Rooftop',
            ],
            'user' => [
                'id' => $user->id,
                'name' => $user->firstName . ' ' . $user->lastName,
                'role' => $user->role,
                'role_name' => UserRole::fromValue($user->role)?->label() ?? 'Unknown',
                'project_id' => $user->project_id,
                'manager_id' => $user->manager_id,
                'site_engineer_id' => $user->site_engineer_id,
            ],
            'role_column' => $roleColumn,
        ];
        
        if ($isStreetlight) {
            // Check streetlight tasks
            $allTasks = StreetlightTask::where('project_id', $projectId)->get();
            $userTasks = StreetlightTask::where($roleColumn, $userId)
                ->where('project_id', $projectId)
                ->with('site')
                ->get();
            
            $taskIds = $userTasks->pluck('id');
            $poles = Pole::whereIn('task_id', $taskIds)->get();
            
            $debug['tasks'] = [
                'total_in_project' => $allTasks->count(),
                'user_tasks_count' => $userTasks->count(),
                'user_task_ids' => $taskIds->toArray(),
                'sample_tasks' => $userTasks->take(3)->map(function($task) {
                    return [
                        'id' => $task->id,
                        'manager_id' => $task->manager_id,
                        'engineer_id' => $task->engineer_id,
                        'vendor_id' => $task->vendor_id,
                        'site_id' => $task->site_id,
                        'total_poles' => optional($task->site)->total_poles,
                        'status' => $task->status,
                    ];
                }),
                'poles' => [
                    'total' => $poles->count(),
                    'surveyed' => $poles->where('isSurveyDone', 1)->count(),
                    'installed' => $poles->where('isInstallationDone', 1)->count(),
                ],
            ];
            
            // Check all tasks in project by role
            $debug['tasks_by_role'] = [
                'manager_tasks' => StreetlightTask::where('manager_id', $userId)->where('project_id', $projectId)->count(),
                'engineer_tasks' => StreetlightTask::where('engineer_id', $userId)->where('project_id', $projectId)->count(),
                'vendor_tasks' => StreetlightTask::where('vendor_id', $userId)->where('project_id', $projectId)->count(),
            ];
        } else {
            // Check rooftop tasks
            $allTasks = Task::where('project_id', $projectId)->get();
            $userTasks = Task::where($roleColumn, $userId)
                ->where('project_id', $projectId)
                ->get();
            
            $debug['tasks'] = [
                'total_in_project' => $allTasks->count(),
                'user_tasks_count' => $userTasks->count(),
                'user_task_ids' => $userTasks->pluck('id')->toArray(),
                'sample_tasks' => $userTasks->take(3)->map(function($task) {
                    return [
                        'id' => $task->id,
                        'manager_id' => $task->manager_id,
                        'engineer_id' => $task->engineer_id,
                        'vendor_id' => $task->vendor_id,
                        'status' => $task->status,
                    ];
                }),
                'completed' => $userTasks->where('status', 'Completed')->count(),
                'pending' => $userTasks->where('status', 'Pending')->count(),
            ];
            
            $debug['tasks_by_role'] = [
                'manager_tasks' => Task::where('manager_id', $userId)->where('project_id', $projectId)->count(),
                'engineer_tasks' => Task::where('engineer_id', $userId)->where('project_id', $projectId)->count(),
                'vendor_tasks' => Task::where('vendor_id', $userId)->where('project_id', $projectId)->count(),
            ];
        }
        
        return response()->json($debug, 200, [], JSON_PRETTY_PRINT);
    }
}
