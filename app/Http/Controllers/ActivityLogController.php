<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Audit Trail & Activity Logging — displays a chronological log of all important actions
 * performed in the system (inventory imports, dispatches, task assignments, etc.). Provides
 * accountability and traceability for operations.
 *
 * Data Flow:
 *   System actions trigger ActivityLogger → ActivityLog records created → Controller
 *   fetches paginated logs → Display in timeline view
 *
 * @depends-on ActivityLog, ActivityLogger
 * @business-domain Audit & Compliance
 * @package App\Http\Controllers
 */
class ActivityLogController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * Data flow: HTTP Request → Database Query → Blade View
     *
     * @param  Request  $request  The incoming HTTP request
     * @return void  
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', ActivityLog::class);

        $query = ActivityLog::with(['user', 'project'])
            ->orderByDesc('created_at');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('entity_type', 'like', "%{$search}%");
            });
        }

        $logs = $query->paginate(50)->appends($request->query());

        $modules = config('activity_log.modules');
        $actions = config('activity_log.actions');

        // Use firstName/lastName for deterministic ordering in dropdown
        $users = User::orderBy('firstName')->orderBy('lastName')->get();
        $projects = Project::orderBy('project_name')->get();

        return view('activity_logs.index', compact('logs', 'modules', 'actions', 'users', 'projects'));
    }

    /**
     * Display the specified resource.
     *
     * @param  ActivityLog  $activityLog  The activity log record
     * @return void  
     */
    public function show(ActivityLog $activityLog)
    {
        $this->authorize('view', $activityLog);

        return view('activity_logs.show', [
            'log' => $activityLog->load(['user', 'project']),
        ]);
    }
}
