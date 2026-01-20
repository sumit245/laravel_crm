<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
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

    public function show(ActivityLog $activityLog)
    {
        $this->authorize('view', $activityLog);

        return view('activity_logs.show', [
            'log' => $activityLog->load(['user', 'project']),
        ]);
    }
}
