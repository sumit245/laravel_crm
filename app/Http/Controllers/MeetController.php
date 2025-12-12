<?php

namespace App\Http\Controllers;

use App\Helpers\WhatsappHelper;
use App\Http\Controllers\Controller;
use App\Models\DiscussionPoint;
use App\Models\FollowUp;
use App\Models\Meet;
use App\Models\DiscussionPointUpdates; // Already imported
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class MeetController extends Controller
{
    public function dashboard()
    {
        // Total meetings done till date
        $totalMeetings = Meet::where('meet_date', '<=', now())->count();

        // Upcoming meetings count
        $upcomingMeetingsCount = Meet::where('meet_date', '>', now())->count();

        // Overdue tasks (due_date < today AND status != 'Completed')
        $overdueTasksCount = DiscussionPoint::where('due_date', '<', now())
            ->where('status', '!=', 'Completed')
            ->count();

        // Tasks in queue (status = 'Pending')
        $tasksInQueueCount = DiscussionPoint::where('status', 'Pending')->count();

        // Total tasks
        $totalTasks = DiscussionPoint::count();
        $completedTasks = DiscussionPoint::where('status', 'Completed')->count();

        // Completion rate
        $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 2) : 0;

        // Average tasks per meeting
        $avgTasksPerMeeting = $totalMeetings > 0 ? round($totalTasks / $totalMeetings, 2) : 0;

        // Department-wise performance
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

        // Tasks by status
        $tasksByStatus = DiscussionPoint::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Tasks by priority
        $tasksByPriority = DiscussionPoint::selectRaw('priority, COUNT(*) as count')
            ->whereNotNull('priority')
            ->groupBy('priority')
            ->pluck('count', 'priority')
            ->toArray();

        // Meeting trends (monthly meeting counts for last 6 months)
        $meetingTrends = Meet::selectRaw('
                DATE_FORMAT(meet_date, "%Y-%m") as month,
                COUNT(*) as count
            ')
            ->where('meet_date', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->toArray();

        // Recent meetings (last 5)
        $recentMeetings = Meet::withCount('discussionPoints')
            ->latest('meet_date')
            ->limit(5)
            ->get();

        // Top performers (top 5 employees by completed tasks)
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

        // Meetings by type
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
        // $users = User::all()->groupBy('role'); // Assuming you have role-based categories
        $usersByRole = [
            'Admins' => User::where('role', 0)->get(),
            'Site Engineers' => User::where('role', 1)->get(),
            'Project Managers' => User::where('role', 2)->get(),
            'Vendors' => User::where('role', 3)->get(),
            'Coordinators' => User::where('role', 4)->get(),
            // Add more roles as needed
        ];
        return view('review-meetings.index', compact('meets', 'usersByRole', 'projects'));
    }

    public function create()
    {
        $meets = Meet::latest()->get();
        $projects = Project::all();

        // Return all users except role 3 (Vendors)
        $users = User::where('role', '<>', 3)->get();

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

            // Calculate task statuses for the summary card
            $taskStatus = $meet->discussionPoints->countBy('status');
            $taskCounts = [
                'total' => $meet->discussionPoints->count(),
                'done' => $taskStatus->get('Completed', 0),
                'progress' => $taskStatus->get('In Progress', 0),
                'pending' => $taskStatus->get('Pending', 0),
            ];

            // Group discussion points by assignee for the Responsibilities tab
            $responsibilities = $meet->discussionPoints->groupBy('assignedToUser.name');

            // Get unique departments from attendees for the filter dropdown
            $departments = $meet->attendees->pluck('department')->filter()->unique();

            // Get all users who can be assigned tasks (attendees of the current meeting)
            $assignees = $meet->attendees;

            // Get all projects for the dropdown
            $projects = Project::all();

            // Group discussion points by project
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

        // Handle project - if project_name is provided but project_id is not, create new project
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

    // TODO: while saving user_ids are not being saved in meet_user. The structure in meet_user table is meet_id, user_id,created_at_updated_at
    public function store(Request $request)
    {
        Log::info($request->all());

        $createdUserIds = [];

        // 2) Handle "new_participants" from the form (array of objects)
        $newParticipants = $request->input('new_participants', []);
        if (!empty($newParticipants) && is_array($newParticipants)) {
            foreach ($newParticipants as $idx => $np) {
                // Basic validation per participant (skip empty rows)
                $np = array_map('trim', (array) $np);
                if (empty($np['firstName']) && empty($np['lastName']) && empty($np['email']) && empty($np['contactNo'])) {
                    continue; // skip entirely empty row
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

                // Try to find existing user by email or contactNo; create if not found
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

                // Create a minimal user. Adjust role as needed (using 4 as generic participant).
                $password = Str::random(12);
                $user = User::create([
                    'firstName' => $np['firstName'] ?? null,
                    'lastName' => $np['lastName'] ?? null,
                    'email' => $np['email'] ?? null,
                    'contactNo' => $np['contactNo'] ?? null,
                    'role' => 100,
                    'password' => bcrypt($password),
                ]);

                $createdUserIds[] = $user->id;
            }
        }

        // 3) Handle CSV import (optional file input name = import_participants)
        if ($request->hasFile('import_participants')) {
            $file = $request->file('import_participants');
            if ($file->isValid()) {
                if (($handle = fopen($file->getRealPath(), 'r')) !== false) {
                    $header = null;
                    while (($row = fgetcsv($handle, 0, ',')) !== false) {
                        if (!$header) {
                            // assume header exists, normalize
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

                        $user = User::create([
                            'firstName' => $first ?: null,
                            'lastName' => $last ?: null,
                            'email' => $email ?: null,
                            'contactNo' => $phone ?: null,
                            'role' => 100,
                            'password' => bcrypt(Str::random(12)),
                        ]);
                        $createdUserIds[] = $user->id;
                    }
                    fclose($handle);
                }
            }
        }

        // 4) Combine selected checkboxes with created users
        $selectedUsers = $request->input('users', []);
        if (!is_array($selectedUsers)) {
            $selectedUsers = [];
        }
        $allUserIds = array_values(array_unique(array_merge($selectedUsers, $createdUserIds)));

        // 5) Now validate users array (ensures they exist)
        $request->merge(['users' => $allUserIds]);
        $validated = $request->validate([
            'title' => 'required|string',
            'agenda' => 'nullable|string',
            'meet_link' => 'required|url',
            'platform' => 'required|string',
            'meet_date' => 'required|date',
            'meet_time_from' => 'required',
            'meet_time_to' => 'required',
            'type' => 'required|string',
            'users' => 'required|array|min:1',
        ]);

        $meet = Meet::create([
            ...$validated,
            'meet_time' => $validated['meet_time_from'],
        ]);

        $meet->attendees()->attach($validated['users']);
        // 7) Send WhatsApp invites
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
        // Load the relationship
        // $historicalNotes = $meet->notesHistory()->with('user')->get();

        // return view('review-meetings.notes', [
        //     'meet' => $meet,
        //     'historicalNotes' => $historicalNotes,
        // ]);
        return view('review-meetings.show-details');
    }

    public function edit(Meet $meet)
    {
        $meet->load('attendees');
        $projects = Project::all();
        // Return all users except role 3 (Vendors) - same as create method
        $users = User::where('role', '<>', 3)->get();
        return view('review-meetings.edit', compact('meet', 'users', 'projects'));
    }

    public function update(Request $request, Meet $meet)
    {
        Log::info('Update request', $request->all());

        $createdUserIds = [];

        // Handle "new_participants" from the form (array of objects)
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

                // Try to find existing user by email or contactNo; create if not found
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

                // Create a minimal user
                $password = Str::random(12);
                $user = User::create([
                    'firstName' => $np['firstName'] ?? null,
                    'lastName' => $np['lastName'] ?? null,
                    'email' => $np['email'] ?? null,
                    'contactNo' => $np['contactNo'] ?? null,
                    'role' => 100,
                    'password' => bcrypt($password),
                ]);

                $createdUserIds[] = $user->id;
            }
        }

        // Combine selected users with created users
        $selectedUsers = $request->input('users', []);
        if (!is_array($selectedUsers)) {
            $selectedUsers = [];
        }
        $allUserIds = array_values(array_unique(array_merge($selectedUsers, $createdUserIds)));

        // Validate the request
        $request->merge(['users' => $allUserIds]);
        $validated = $request->validate([
            'title' => 'required|string',
            'agenda' => 'nullable|string',
            'meet_link' => 'required|url',
            'platform' => 'required|string',
            'meet_date' => 'required|date',
            'meet_time_from' => 'required',
            'meet_time_to' => 'required',
            'type' => 'required|string',
            'users' => 'required|array|min:1',
        ]);

        // Update the meeting
        $meet->update([
            'title' => $validated['title'],
            'agenda' => $validated['agenda'] ?? null,
            'meet_link' => $validated['meet_link'],
            'platform' => $validated['platform'],
            'meet_date' => $validated['meet_date'],
            'meet_time' => $validated['meet_time_from'],
            'type' => $validated['type'],
        ]);

        // Get existing attendee IDs to determine which are new
        $existingAttendeeIds = $meet->attendees->pluck('id')->toArray();

        // Sync attendees (this will add new ones and remove ones not in the list)
        $meet->attendees()->sync($validated['users']);

        // Determine new attendees (those not in the existing list)
        $newAttendeeIds = array_diff($validated['users'], $existingAttendeeIds);

        // Send WhatsApp invites only to new attendees
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
            'meet_time_from' => 'required',
            'meet_time_to' => 'required',
        ]);

        // Create the new follow-up meeting
        $followUpMeet = Meet::create([
            'title' => $validated['title'],
            'agenda' => 'Follow-up for: ' . $meet->title,
            'meet_link' => $meet->meet_link, // Re-use parent link or generate new
            'platform' => $meet->platform,
            'meet_date' => $validated['meet_date'],
            'meet_time' => $validated['meet_time_from'],
            'meet_end_time' => $validated['meet_time_to'],
            'type' => $meet->type,
            'parent_meet_id' => $meet->id, // Link to the parent meeting
        ]);

        // Attach the same attendees from the parent meeting
        $followUpMeet->attendees()->attach($meet->attendees->pluck('id'));

        // Create the FollowUp record to track this followup in the follow_ups table
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
        // Show editor + whiteboard page
        return view('review-meetings.notes', compact('meet'));
    }

    public function updateNotes(Request $request, Meet $meet)
    {
        $data = $request->validate([
            'notes' => 'nullable|string',
            'whiteboard_dataurl' => 'nullable|string', // data:image/png;base64,....
            'insert_whiteboard_into_notes' => 'nullable|boolean',
        ]);

        // Save drawing if provided
        if (!empty($data['whiteboard_dataurl']) && str_starts_with($data['whiteboard_dataurl'], 'data:image/png;base64,')) {
            $png = substr($data['whiteboard_dataurl'], strpos($data['whiteboard_dataurl'], ',') + 1);
            $png = base64_decode(str_replace(' ', '+', $png));
            $path = 'whiteboards/meet-' . $meet->id . '-' . time() . '.png';
            Storage::disk('public')->put($path, $png);
            $meet->whiteboard_image_path = $path;

            // optionally insert the drawing <img> into notes content
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
        // Embed whiteboard as base64 for dompdf (reliable)
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
    // OPTIONAL Excel export (simple single-sheet)
    public function exportExcel(Meet $meet)
    {
        return Excel::download(new \App\Exports\MeetingNotesExport($meet), 'meeting_' . $meet->id . '.xlsx');
    }
}
