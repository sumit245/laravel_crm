<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Helpers\WhatsappHelper;
use App\Http\Controllers\Controller;
use App\Models\DiscussionPoint;
use App\Models\FollowUp;
use App\Models\Meet;
use App\Models\DiscussionPointUpdates;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class MeetController extends Controller
{
    public function dashboard()
    {
        $totalMeetings = Meet::where('meet_date', '<=', now())->count();
        $upcomingMeetingsCount = Meet::where('meet_date', '>', now())->count();
        $overdueTasksCount = DiscussionPoint::where('due_date', '<', now())
            ->where('status', '!=', 'Completed')
            ->count();
        $tasksInQueueCount = DiscussionPoint::where('status', 'Pending')->count();
        $totalTasks = DiscussionPoint::count();
        $completedTasks = DiscussionPoint::where('status', 'Completed')->count();
        $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 2) : 0;
        $avgTasksPerMeeting = $totalMeetings > 0 ? round($totalTasks / $totalMeetings, 2) : 0;
        $departmentStats = DiscussionPoint::selectRaw('department, 
            COUNT(*) as total,
            SUM(CASE WHEN status = "Completed" THEN 1 ELSE 0 END) as completed,
            SUM(CASE WHEN status = "Pending" THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = "In Progress" THEN 1 ELSE 0 END) as in_progress')
            ->whereNotNull('department')
            ->groupBy('department')
            ->get()
            ->mapWithKeys(function ($item) {
                return [
                    $item->department => [
                        'total' => $item->total,
                        'completed' => $item->completed,
                        'pending' => $item->pending,
                        'in_progress' => $item->in_progress,
                    ]
                ];
            });

        // Employee-wise performance
        $employeeStats = DiscussionPoint::selectRaw('
                COALESCE(assigned_to, assignee_id) as user_id,
                COUNT(*) as total_tasks,
                SUM(CASE WHEN status = "Completed" THEN 1 ELSE 0 END) as completed_tasks,
                SUM(CASE WHEN status = "Pending" THEN 1 ELSE 0 END) as pending_tasks,
                SUM(CASE WHEN status = "In Progress" THEN 1 ELSE 0 END) as in_progress_tasks,
                SUM(CASE WHEN due_date < CURDATE() AND status != "Completed" THEN 1 ELSE 0 END) as overdue_tasks
            ')
            ->where(function ($query) {
                $query->whereNotNull('assigned_to')
                    ->orWhereNotNull('assignee_id');
            })
            ->groupBy('user_id')
            ->get()
            ->map(function ($item) {
                $user = User::find($item->user_id);
                return [
                    'user' => $user,
                    'user_name' => $user ? ($user->firstName . ' ' . $user->lastName) : 'Unknown',
                    'total_tasks' => $item->total_tasks,
                    'completed_tasks' => $item->completed_tasks,
                    'pending_tasks' => $item->pending_tasks,
                    'in_progress_tasks' => $item->in_progress_tasks,
                    'overdue_tasks' => $item->overdue_tasks,
                    'completion_rate' => $item->total_tasks > 0 ? round(($item->completed_tasks / $item->total_tasks) * 100, 2) : 0,
                ];
            })
            ->sortByDesc('completed_tasks')
            ->values();

        $tasksByStatus = DiscussionPoint::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $tasksByPriority = DiscussionPoint::selectRaw('priority, COUNT(*) as count')
            ->whereNotNull('priority')
            ->groupBy('priority')
            ->pluck('count', 'priority')
            ->toArray();

        $meetingTrends = Meet::selectRaw('
                DATE_FORMAT(meet_date, "%Y-%m") as month,
                COUNT(*) as count
            ')
            ->where('meet_date', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->toArray();

        $recentMeetings = Meet::withCount('discussionPoints')
            ->latest('meet_date')
            ->limit(5)
            ->get();

        $topPerformers = DiscussionPoint::selectRaw('
                COALESCE(assigned_to, assignee_id) as user_id,
                SUM(CASE WHEN status = "Completed" THEN 1 ELSE 0 END) as completed_count
            ')
            ->where(function ($query) {
                $query->whereNotNull('assigned_to')
                    ->orWhereNotNull('assignee_id');
            })
            ->groupBy('user_id')
            ->orderByDesc('completed_count')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                $user = User::find($item->user_id);
                return [
                    'user' => $user,
                    'user_name' => $user ? ($user->firstName . ' ' . $user->lastName) : 'Unknown',
                    'completed_count' => $item->completed_count,
                ];
            });

        $meetingsByType = Meet::selectRaw('type, COUNT(*) as count')
            ->whereNotNull('type')
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();

        return view('review-meetings.dashboard', [
            'totalMeetings' => $totalMeetings,
            'overdueTasksCount' => $overdueTasksCount,
            'tasksInQueueCount' => $tasksInQueueCount,
            'completionRate' => $completionRate,
            'upcomingMeetingsCount' => $upcomingMeetingsCount,
            'avgTasksPerMeeting' => $avgTasksPerMeeting,
            'departmentPerformance' => $departmentStats,
            'employeePerformance' => $employeeStats,
            'tasksByStatus' => $tasksByStatus,
            'tasksByPriority' => $tasksByPriority,
            'meetingTrends' => $meetingTrends,
            'recentMeetings' => $recentMeetings,
            'topPerformers' => $topPerformers,
            'meetingsByType' => $meetingsByType,
            'totalTasks' => $totalTasks,
            'completedTasks' => $completedTasks,
        ]);
    }

    public function index()
    {
        $meets = Meet::withCount('attendees')->latest()->get();
        $projects = Project::all();
        $usersByRole = [
            'Admins' => User::where('role', UserRole::ADMIN->value)->get(),
            'Site Engineers' => User::where('role', UserRole::SITE_ENGINEER->value)->get(),
            'Project Managers' => User::where('role', UserRole::PROJECT_MANAGER->value)->get(),
            'Vendors' => User::where('role', UserRole::VENDOR->value)->get(),
            'Coordinators' => User::where('role', UserRole::STORE_INCHARGE->value)->get(),
        ];
        return view('review-meetings.index', compact('meets', 'usersByRole', 'projects'));
    }

    public function create()
    {
        $meets = Meet::latest()->get();
        $projects = Project::all();
        $users = User::where('role', '<>', UserRole::VENDOR->value)->get();
        return view('review-meetings.create', compact('meets', 'users', 'projects'));
    }

    public function details(Request $request, $id)
    {
        try {
            $meet = Meet::with([
                'attendees',
                'discussionPoints.assignee',
                'discussionPoints.assignedToUser',
                'discussionPoints.updates',
                'discussionPoints.project',
                'followUps'
            ])->findOrFail($id);

            $taskStatus = $meet->discussionPoints->countBy('status');
            $taskCounts = [
                'total' => $meet->discussionPoints->count(),
                'done' => $taskStatus->get('Completed', 0),
                'progress' => $taskStatus->get('In Progress', 0),
                'pending' => $taskStatus->get('Pending', 0),
            ];
            $responsibilities = $meet->discussionPoints->groupBy('assignedToUser.name');
            $departments = $meet->attendees->pluck('department')->filter()->unique();
            $assignees = $meet->attendees;
            $projects = Project::all();
            $discussionPointsByProject = $meet->discussionPoints->groupBy(function ($point) {
                return $point->project ? $point->project->id : 'no-project';
            });

            return view('review-meetings.meeting_details', compact('meet', 'taskCounts', 'responsibilities', 'departments', 'assignees', 'projects', 'discussionPointsByProject'));

        } catch (\Exception $e) {
            return response()->json([
                "error" => "Meeting not found or an error occurred.",
                "message" => $e->getMessage()
            ], 404);
        }
    }

    public function storeDiscussionPoint(Request $request)
    {
        $request->validate([
            'meet_id' => 'required|exists:meets,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assignee_id' => 'nullable|exists:users,id',
            'assigned_to' => 'nullable|exists:users,id',
            'department' => 'nullable|string',
            'priority' => 'required|string',
            'due_date' => 'nullable|date',
            'project_id' => 'nullable|exists:projects,id',
            'project_name' => 'nullable|string|max:255', // For new project
        ]);

        $data = $request->all();

        if (!empty($request->project_name) && empty($request->project_id)) {
            $project = Project::create([
                'project_name' => $request->project_name,
                'project_type' => 'General',
            ]);
            $data['project_id'] = $project->id;
        }

        // Remove project_name from data as it's not a field in discussion_points
        unset($data['project_name']);

        DiscussionPoint::create($data);

        return back()->with('success', 'New discussion point added successfully!');
    }

    public function updateDiscussionPointStatus(Request $request, DiscussionPoint $point)
    {
        $request->validate([
            'status' => 'required|string|in:Pending,In Progress,Completed',
        ]);

        $point->update(['status' => $request->status]);

        return back()->with('success', 'Task status updated successfully!');
    }


    public function storeDiscussionPointUpdate(Request $request)
    {
        $request->validate([
            'discussion_point_id' => 'required|exists:discussion_points,id',
            'update_text' => 'required|string',
            'vertical_head_remark' => 'nullable|string',
            'admin_remark' => 'nullable|string',
        ]);

        DiscussionPointUpdates::create($request->all());

        return back()->with('success', 'Note added successfully!');
    }

    public function store(Request $request)
    {

        $createdUserIds = [];
        $newParticipants = $request->input('new_participants', []);
        if (!empty($newParticipants) && is_array($newParticipants)) {
            foreach ($newParticipants as $idx => $np) {
                $np = array_map('trim', (array) $np);
                if (empty($np['firstName']) && empty($np['lastName']) && empty($np['email']) && empty($np['contactNo'])) {
                    continue;
                }

                $v = Validator::make($np, [
                    'firstName' => 'required|string|max:255',
                    'lastName' => 'nullable|string|max:255',
                    'email' => 'nullable|email|max:255',
                    'contactNo' => 'nullable|string|max:50',
                ]);

                if ($v->fails()) {
                    return back()->withErrors($v)->withInput();
                }

                $existing = User::where(function ($query) use ($np) {
                    if (!empty($np['email'])) {
                        $query->where('email', $np['email']);
                    }
                    if (!empty($np['contactNo'])) {
                        $query->orWhere('contactNo', $np['contactNo']);
                    }
                })->first();

                if ($existing) {
                    $createdUserIds[] = $existing->id;
                    continue;
                }

                $password = Str::random(12);

                // Ensure required DB constraints (email NOT NULL & unique, username NOT NULL & unique)
                $email = !empty($np['email'])
                    ? $np['email']
                    : ('noemail+' . uniqid('meet_', true) . '@example.local');

                $username = 'user_' . Str::random(10);

                $user = User::create([
                    'firstName' => $np['firstName'] ?? null,
                    'lastName' => $np['lastName'] ?? null,
                    'email' => $email,
                    'username' => $username,
                    'contactNo' => $np['contactNo'] ?? null,
                    'role' => UserRole::REVIEW_MEETING_ONLY->value,
                    'password' => bcrypt($password),
                ]);

                $createdUserIds[] = $user->id;
            }
        }

        if ($request->hasFile('import_participants')) {
            $file = $request->file('import_participants');
            if ($file->isValid()) {
                if (($handle = fopen($file->getRealPath(), 'r')) !== false) {
                    $header = null;
                    while (($row = fgetcsv($handle, 0, ',')) !== false) {
                        if (!$header) {
                            $header = array_map('trim', $row);
                            continue;
                        }
                        $data = array_combine($header, $row);
                        $first = trim($data['firstName'] ?? $data['firstName'] ?? ($data['first'] ?? ''));
                        $last = trim($data['lastName'] ?? $data['lastName'] ?? ($data['last'] ?? ''));
                        $email = trim($data['email'] ?? '');
                        $phone = trim($data['contactNo'] ?? $data['phone'] ?? '');

                        if (empty($first) && empty($email) && empty($phone)) {
                            continue;
                        }

                        $existing = User::where(function ($q) use ($email, $phone) {
                            if ($email)
                                $q->where('email', $email);
                            if ($phone)
                                $q->orWhere('contactNo', $phone);
                        })->first();

                        if ($existing) {
                            $createdUserIds[] = $existing->id;
                            continue;
                        }

                        // Ensure required DB constraints (email NOT NULL & unique, username NOT NULL & unique)
                        $emailValue = $email
                            ? $email
                            : ('noemail+' . uniqid('meet_csv_', true) . '@example.local');

                        $username = 'user_' . Str::random(10);

                        $user = User::create([
                            'firstName' => $first ?: null,
                            'lastName' => $last ?: null,
                            'email' => $emailValue,
                            'username' => $username,
                            'contactNo' => $phone ?: null,
                            'role' => UserRole::REVIEW_MEETING_ONLY->value,
                            'password' => bcrypt(Str::random(12)),
                        ]);
                        $createdUserIds[] = $user->id;
                    }
                    fclose($handle);
                }
            }
        }

        $selectedUsers = $request->input('users', []);
        if (!is_array($selectedUsers)) {
            $selectedUsers = [];
        }
        $allUserIds = array_values(array_unique(array_merge($selectedUsers, $createdUserIds)));
        $request->merge(['users' => $allUserIds]);
        $validated = $request->validate([
            'title' => 'required|string',
            'agenda' => 'nullable|string',
            'meet_link' => 'required|url',
            'platform' => 'required|in:Google Meet,Zoom,Teams,Other',
            'meet_date' => 'required|date',
            'meet_time_from' => 'required|date_format:H:i',
            'meet_time_to' => 'required|date_format:H:i',
            'type' => 'required|string',
            'users' => 'required|array|min:1',
        ]);

        // Normalize meet_time to a proper time string compatible with the DB column
        $meetTime = $validated['meet_time_from'] . ':00'; // from HTML time input "HH:MM" to "HH:MM:SS"

        // Use the type as-is (will be validated by database after migration)
        $meetingType = $validated['type'];

        $meet = Meet::create([
            'title' => $validated['title'],
            'agenda' => $validated['agenda'] ?? null,
            'meet_link' => $validated['meet_link'],
            'platform' => $validated['platform'],
            'meet_date' => $validated['meet_date'],
            'meet_time' => $meetTime,
            'type' => $meetingType,
        ]);

        $meet->attendees()->attach($validated['users']);
        $users = User::whereIn('id', $validated['users'])->get(['firstName', 'lastName', 'contactNo']);
        foreach ($users as $user) {
            try {
                WhatsappHelper::sendMeetLink($user->contactNo, $user->firstName . ' ' . $user->lastName, [
                    'firstName' => $user->firstName,
                    'lastName' => $user->lastName,
                    'title' => $validated['title'],
                    'meet_date' => $validated['meet_date'],
                    'meet_time' => $validated['meet_time_from'] . ' - ' . $validated['meet_time_to'],
                    'platform' => $validated['platform'],
                    'meet_link' => $validated['meet_link'],
                    'agenda' => $validated['agenda'] ?? '',
                    'type' => $validated['type'],
                ]);
            } catch (\Exception $e) {
                Log::error("Failed to send WhatsApp to {$user->contactNo}: " . $e->getMessage());
            }
        }

        return redirect()->route('meets.index')->with('success', 'Meeting created successfully!');
    }
    public function show(Meet $meet)
    {
        return view('review-meetings.show-details');
    }

    public function edit(Meet $meet)
    {
        $meet->load('attendees');
        $projects = Project::all();
        $users = User::where('role', '<>', UserRole::VENDOR->value)->get();
        return view('review-meetings.edit', compact('meet', 'users', 'projects'));
    }

    public function update(Request $request, Meet $meet)
    {

        $createdUserIds = [];
        $newParticipants = $request->input('new_participants', []);
        if (!empty($newParticipants) && is_array($newParticipants)) {
            foreach ($newParticipants as $idx => $np) {
                $np = array_map('trim', (array) $np);
                if (empty($np['firstName']) && empty($np['lastName']) && empty($np['email']) && empty($np['contactNo'])) {
                    continue;
                }

                $v = Validator::make($np, [
                    'firstName' => 'required|string|max:255',
                    'lastName' => 'nullable|string|max:255',
                    'email' => 'nullable|email|max:255',
                    'contactNo' => 'nullable|string|max:50',
                ]);

                if ($v->fails()) {
                    return back()->withErrors($v)->withInput();
                }

                $existing = User::where(function ($query) use ($np) {
                    if (!empty($np['email'])) {
                        $query->where('email', $np['email']);
                    }
                    if (!empty($np['contactNo'])) {
                        $query->orWhere('contactNo', $np['contactNo']);
                    }
                })->first();

                if ($existing) {
                    $createdUserIds[] = $existing->id;
                    continue;
                }

                $password = Str::random(12);

                // Ensure required DB constraints (email NOT NULL & unique, username NOT NULL & unique)
                $email = !empty($np['email'])
                    ? $np['email']
                    : ('noemail+' . uniqid('meet_update_', true) . '@example.local');

                $username = 'user_' . Str::random(10);

                $user = User::create([
                    'firstName' => $np['firstName'] ?? null,
                    'lastName' => $np['lastName'] ?? null,
                    'email' => $email,
                    'username' => $username,
                    'contactNo' => $np['contactNo'] ?? null,
                    'role' => UserRole::COORDINATOR->value,
                    'password' => bcrypt($password),
                ]);

                $createdUserIds[] = $user->id;
            }
        }

        $selectedUsers = $request->input('users', []);
        if (!is_array($selectedUsers)) {
            $selectedUsers = [];
        }
        $allUserIds = array_values(array_unique(array_merge($selectedUsers, $createdUserIds)));
        $request->merge(['users' => $allUserIds]);
        $validated = $request->validate([
            'title' => 'required|string',
            'agenda' => 'nullable|string',
            'meet_link' => 'required|url',
            'platform' => 'required|in:Google Meet,Zoom,Teams,Other',
            'meet_date' => 'required|date',
            'meet_time_from' => 'required|date_format:H:i',
            'meet_time_to' => 'required|date_format:H:i',
            'type' => 'required|string',
            'users' => 'required|array|min:1',
        ]);

        // Normalize meet_time to a proper time string compatible with the DB column
        $meetTime = $validated['meet_time_from'] . ':00'; // from HTML time input "HH:MM" to "HH:MM:SS"

        $meet->update([
            'title' => $validated['title'],
            'agenda' => $validated['agenda'] ?? null,
            'meet_link' => $validated['meet_link'],
            'platform' => $validated['platform'],
            'meet_date' => $validated['meet_date'],
            'meet_time' => $meetTime,
            'type' => $validated['type'],
        ]);

        $existingAttendeeIds = $meet->attendees->pluck('id')->toArray();
        $meet->attendees()->sync($validated['users']);
        $newAttendeeIds = array_diff($validated['users'], $existingAttendeeIds);

        if (!empty($newAttendeeIds)) {
            $newUsers = User::whereIn('id', $newAttendeeIds)->get(['firstName', 'lastName', 'contactNo']);
            foreach ($newUsers as $user) {
                if ($user->contactNo) {
                    try {
                        WhatsappHelper::sendMeetLink($user->contactNo, $user->firstName . ' ' . $user->lastName, [
                            'firstName' => $user->firstName,
                            'lastName' => $user->lastName,
                            'title' => $validated['title'],
                            'meet_date' => $validated['meet_date'],
                            'meet_time' => $validated['meet_time_from'] . ' - ' . $validated['meet_time_to'],
                            'platform' => $validated['platform'],
                            'meet_link' => $validated['meet_link'],
                            'agenda' => $validated['agenda'] ?? '',
                            'type' => $validated['type'],
                        ]);
                    } catch (\Exception $e) {
                        Log::error("Failed to send WhatsApp to {$user->contactNo}: " . $e->getMessage());
                    }
                }
            }
        }

        return redirect()->route('meets.index')->with('success', 'Meeting updated successfully!');
    }

    public function destroy(Meet $meet)
    {
        $meet->delete();
        return redirect()->route('meets.index')->with('success', 'Meeting deleted');
    }

    public function scheduleFollowUp(Request $request, Meet $meet)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'meet_date' => 'required|date',
            'meet_time_from' => 'required|date_format:H:i',
            'meet_time_to' => 'required|date_format:H:i',
        ]);

        // Normalize meet_time to "HH:MM:SS" for DB compatibility
        $followupStart = $validated['meet_time_from'] . ':00';

        $followUpMeet = Meet::create([
            'title' => $validated['title'],
            'agenda' => 'Follow-up for: ' . $meet->title,
            'meet_link' => $meet->meet_link, // Re-use parent link or generate new
            'platform' => $meet->platform,
            'meet_date' => $validated['meet_date'],
            'meet_time' => $followupStart,
            'type' => $meet->type,
            // Note: parent_meet_id and meet_end_time don't exist in meets table
            // The relationship is tracked via the follow_ups table instead
        ]);

        $followUpMeet->attendees()->attach($meet->attendees->pluck('id'));
        FollowUp::create([
            'parent_meet_id' => $meet->id,
            'meet_id' => $followUpMeet->id,
            'title' => $validated['title'],
            'meet_date' => $validated['meet_date'],
            'status' => 'scheduled',
        ]);

        return back()->with('success', 'Follow-up meeting scheduled successfully!');
    }

    public function notes(Meet $meet)
    {
        return view('review-meetings.notes', compact('meet'));
    }

    public function updateNotes(Request $request, Meet $meet)
    {
        $data = $request->validate([
            'notes' => 'nullable|string',
            'whiteboard_dataurl' => 'nullable|string', // data:image/png;base64,....
            'insert_whiteboard_into_notes' => 'nullable|boolean',
        ]);

        if (!empty($data['whiteboard_dataurl']) && str_starts_with($data['whiteboard_dataurl'], 'data:image/png;base64,')) {
            $png = substr($data['whiteboard_dataurl'], strpos($data['whiteboard_dataurl'], ',') + 1);
            $png = base64_decode(str_replace(' ', '+', $png));
            $path = 'whiteboards/meet-' . $meet->id . '-' . time() . '.png';
            Storage::disk('public')->put($path, $png);
            $meet->whiteboard_image_path = $path;

            if ($request->boolean('insert_whiteboard_into_notes')) {
                $imgTag = '<p><img src="' . asset('storage/' . $path) . '" alt="Whiteboard" style="max-width:100%"></p>';
                $data['notes'] = ($data['notes'] ?? '') . $imgTag;
            }
        }

        if (array_key_exists('notes', $data)) {
            $meet->notes = $data['notes'];
        }

        $meet->save();

        return back()->with('success', 'Notes and whiteboard saved.');
    }

    public function exportPdf(Meet $meet)
    {
        $whiteboardBase64 = null;
        if ($meet->whiteboard_image_path && Storage::disk('public')->exists($meet->whiteboard_image_path)) {
            $bytes = Storage::disk('public')->get($meet->whiteboard_image_path);
            $whiteboardBase64 = 'data:image/png;base64,' . base64_encode($bytes);
        }

        $pdf = Pdf::loadView('meets.pdf', [
            'meet' => $meet,
            'whiteboardBase64' => $whiteboardBase64,
        ])->setPaper('a4');

        return $pdf->download('meeting_' . $meet->id . '.pdf');
    }

    public function exportExcel(Meet $meet)
    {
        return Excel::download(new \App\Exports\MeetingNotesExport($meet), 'meeting_' . $meet->id . '.xlsx');
    }
}
