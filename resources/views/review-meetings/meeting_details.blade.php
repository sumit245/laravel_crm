@extends('layouts.main')
@section('content')
    <div class="container my-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="javascript:history.back()" class="btn btn-light border mb-2"><i class="bi bi-arrow-left me-2"></i>Back
                    to Meetings</a>
                <h2 class="mb-0">{{ $meet->title }}</h2>
                <p class="text-muted">{{ \Carbon\Carbon::parse($meet->meet_date)->format('Y-m-d') }} •
                    {{ \Carbon\Carbon::parse($meet->meet_time)->format('h:i A') }}</p>
            </div>
            <a href="{{ $meet->meet_link }}" target="_blank" class="btn btn-primary"><i
                    class="bi bi-camera-video me-2"></i>Join Meeting</a>
        </div>

        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card summary-card h-100">
                    <div class="card-body d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1">Platform</p>
                            <h1 class="mb-0">{{ $meet->platform }}</h1>
                        </div>
                        <i class="bi bi-camera-video fs-4 text-muted"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card summary-card h-100">
                    <div class="card-body d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1">Attendees</p>
                            <h1 class="mb-0">{{ $meet->attendees->count() }}</h1>
                        </div>
                        <i class="bi bi-people fs-4 text-muted"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card summary-card h-100">
                    <div class="card-body d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1">Task Status</p>
                            <h1 class="mb-0">{{ $taskCounts['total'] }} Total</h1>
                            <div>
                                <span class="small"><span class="task-status-dot dot-done"></span>{{ $taskCounts['done'] }}
                                    Done</span>
                                <span class="small mx-2"><span
                                        class="task-status-dot dot-progress"></span>{{ $taskCounts['progress'] }}
                                    Progress</span>
                                <span class="small"><span
                                        class="task-status-dot dot-pending"></span>{{ $taskCounts['pending'] }}
                                    Pending</span>
                            </div>
                        </div>
                        <i class="bi bi-check2-circle fs-4 text-muted"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="card" style="border-radius: 0.5rem;">
            <div class="tab-content" id="meetingTabContent">
                <ul class="nav nav-tabs" id="meetingTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview"
                            type="button" role="tab" aria-controls="overview" aria-selected="true">Overview</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="discussion-tab" data-bs-toggle="tab" data-bs-target="#discussion"
                            type="button" role="tab" aria-controls="discussion" aria-selected="false">Discussion
                            Points</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="attendees-tab" data-bs-toggle="tab" data-bs-target="#attendees"
                            type="button" role="tab" aria-controls="attendees" aria-selected="false">Attendees</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="responsibilities-tab" data-bs-toggle="tab"
                            data-bs-target="#responsibilities" type="button" role="tab"
                            aria-controls="responsibilities" aria-selected="false">Responsibilities</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="followups-tab" data-bs-toggle="tab" data-bs-target="#followups"
                            type="button" role="tab" aria-controls="followups"
                            aria-selected="false">Follow-ups</button>
                    </li>
                </ul>
                {{-- Tab 1 - Overview --}}
                <div class="tab-pane mt-4 fade show active" id="overview" role="tabpanel" aria-labelledby="overview-tab">
                    <h4 class="mb-3">Meeting Details</h4>
                    <div class="mb-3">
                        <h6 class="text-muted">Agenda</h6>
                        <p>{{ $meet->agenda ?? 'No agenda provided.' }}</p>
                    </div>
                    {{-- Description field commented out - not provided during meeting creation --}}
                    {{-- <div class="mb-3">
                        <h6 class="text-muted">Description</h6>
                        <p>{{ $meet->description ?? 'No description provided.' }}</p>
                    </div> --}}
                    <div>
                        <h6 class="text-muted">Status</h6>
                        <p>
                            This meeting has tasks in various stages:
                            <span class="badge bg-success">{{ $taskCounts['done'] }} Done</span>
                            <span class="badge bg-warning text-dark">{{ $taskCounts['progress'] }} In Progress</span>
                            <span class="badge bg-secondary">{{ $taskCounts['pending'] }} Pending</span>
                        </p>
                    </div>
                </div>

                {{-- Tab 2 - Discussion Points & Tasks --}}
                <div class="tab-pane mt-4 fade" id="discussion" role="tabpanel" aria-labelledby="discussion-tab">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="mb-0">Discussion Points & Tasks</h4>
                        <div class="d-flex gap-2" id="action-buttons">
                            <div class="input-group">
                                <input type="text" id="task-search-input" class="form-control"
                                    style="height: 2.4rem;" placeholder="Search tasks by user...">
                            </div>
                            <select class="form-select" style="height: 2.4rem;">
                                <option selected>All Departments</option>
                                @foreach ($departments as $department)
                                    @if ($department)
                                        <option value="{{ $department }}">{{ $department }}</option>
                                    @endif
                                @endforeach
                            </select>
                            <button class="btn btn-primary d-flex align-items-center justify-content-center"
                                style="width:400px;height:2.4rem;" data-bs-toggle="modal"
                                data-bs-target="#addDiscussionPointModal"><i class="bi bi-plus me-2"></i>Add
                                Task</button>
                        </div>
                    </div>
                    @php
                        // Color palettes for different projects - each project gets a distinct color scheme
                        $projectColors = [
                            'no-project' => ['border' => '#e0e0e0', 'bg' => '#f8f9fa', 'accent' => '#6c757d'],
                        ];
                        $colorIndex = 0;
                        $colorSchemes = [
                            ['border' => '#d4e5f7', 'bg' => '#e8f4f8', 'accent' => '#2196F3', 'text' => '#1565C0'],
                            ['border' => '#ffe0b2', 'bg' => '#fff3e0', 'accent' => '#FF9800', 'text' => '#E65100'],
                            ['border' => '#c8e6c9', 'bg' => '#e8f5e9', 'accent' => '#4CAF50', 'text' => '#2E7D32'],
                            ['border' => '#f8bbd0', 'bg' => '#fce4ec', 'accent' => '#E91E63', 'text' => '#C2185B'],
                            ['border' => '#d1c4e9', 'bg' => '#ede7f6', 'accent' => '#9C27B0', 'text' => '#7B1FA2'],
                            ['border' => '#b3e5fc', 'bg' => '#e0f7fa', 'accent' => '#00BCD4', 'text' => '#00838F'],
                        ];
                    @endphp
                    @forelse ($discussionPointsByProject as $projectKey => $points)
                        @php
                            if ($projectKey === 'no-project') {
                                $project = null;
                                $projectName = 'No Project';
                                $colorScheme = $projectColors['no-project'];
                            } else {
                                $project = $points->first()->project;
                                $projectName = $project->project_name ?? 'Unknown Project';
                                if (!isset($projectColors[$projectKey])) {
                                    $projectColors[$projectKey] = $colorSchemes[$colorIndex % count($colorSchemes)];
                                    $colorIndex++;
                                }
                                $colorScheme = $projectColors[$projectKey];
                            }
                        @endphp
                        <div class="project-group mb-4">
                            <div class="project-header p-3 mb-3 rounded"
                                style="background: linear-gradient(135deg, {{ $colorScheme['bg'] }} 0%, {{ $colorScheme['border'] }} 100%); border-left: 4px solid {{ $colorScheme['accent'] }};">
                                <h5 class="mb-0 fw-bold"
                                    style="color: {{ $colorScheme['text'] ?? $colorScheme['accent'] }};">
                                    <i class="bi bi-folder me-2"></i>{{ $projectName }}
                                    <span class="badge ms-2"
                                        style="background-color: {{ $colorScheme['accent'] }}; color: white;">{{ $points->count() }}
                                        Task{{ $points->count() > 1 ? 's' : '' }}</span>
                                </h5>
                            </div>
                            @foreach ($points as $pointIndex => $point)
                                <div class="task-card mb-3" data-assignee-name="{{ $point->assignedToUser->name ?? '' }}"
                                    style="border-left: 4px solid {{ $colorScheme['accent'] }}; background-color: {{ $colorScheme['bg'] }}; border-top: 1px solid {{ $colorScheme['border'] }}; border-right: 1px solid {{ $colorScheme['border'] }}; border-bottom: 1px solid {{ $colorScheme['border'] }};">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h5 style="color: {{ $colorScheme['text'] ?? $colorScheme['accent'] }};">
                                                @if ($point->status == 'Completed')
                                                    <i class="bi bi-check-circle-fill me-2" style="color: #28a745;"></i>
                                                @elseif($point->status == 'In Progress')
                                                    <i class="bi bi-clock-history me-2" style="color: #ffc107;"></i>
                                                @else
                                                    <i class="bi bi-exclamation-circle-fill me-2"
                                                        style="color: #dc3545;"></i>
                                                @endif
                                                {{ $point->title }}
                                                @if ($point->priority == 'High')
                                                    <span class="badge bg-danger ms-2"
                                                        style="font-size: 0.7rem;">HIGH</span>
                                                @elseif ($point->priority == 'Medium')
                                                    <span class="badge bg-warning text-dark ms-2"
                                                        style="font-size: 0.7rem;">MED</span>
                                                @else
                                                    <span class="badge bg-info ms-2" style="font-size: 0.7rem;">LOW</span>
                                                @endif
                                            </h5>
                                            <p class="small" style="color: #495057; font-weight: 500;">
                                                {{ $point->description }}</p>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <div class="dropdown" style="width: 110px; ">
                                                <button
                                                    class="badge-dropdown dropdown-toggle badge w-100
                                            @if ($point->status == 'Completed') bg-success
                                            @elseif ($point->status == 'In Progress') bg-warning text-dark
                                            @else bg-secondary text-dark @endif"
                                                    type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    {{ $point->status }}
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="#"
                                                            onclick="event.preventDefault(); document.getElementById('status-form-{{ $point->id }}-pending').submit();">Pending</a>
                                                    </li>
                                                    <li><a class="dropdown-item" href="#"
                                                            onclick="event.preventDefault(); document.getElementById('status-form-{{ $point->id }}-progress').submit();">In
                                                            Progress</a></li>
                                                    <li><a class="dropdown-item" href="#"
                                                            onclick="event.preventDefault(); document.getElementById('status-form-{{ $point->id }}-completed').submit();">Completed</a>
                                                    </li>
                                                </ul>
                                            </div>

                                            <button
                                                class="btn btn-sm btn-outline-secondary d-flex align-items-center justify-content-center"
                                                style="width: 110px;max-height:1.8rem;" data-bs-toggle="modal"
                                                data-bs-target="#addNoteModal" data-point-id="{{ $point->id }}"><i
                                                    class="bi bi-plus-lg me-1"></i>Add
                                                Note</button>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-3 pt-2"
                                        style="border-top: 1px solid {{ $colorScheme['border'] }};">
                                        <span class="small"
                                            style="color: {{ $colorScheme['text'] ?? $colorScheme['accent'] }}; font-weight: 600;">
                                            <i class="bi bi-person-fill me-1"
                                                style="color: {{ $colorScheme['accent'] }};"></i>
                                            <strong>Responsible:</strong>
                                            {{ $point->assignedToUser->name ?? 'Unassigned' }}
                                            @if ($point->assignedToUser?->department)
                                                <span class="badge ms-2"
                                                    style="background-color: {{ $colorScheme['accent'] }}; color: white; font-size: 0.75rem;">{{ $point->assignedToUser->department }}</span>
                                            @endif
                                        </span>
                                        @if ($point->due_date)
                                            <span class="small fw-bold"
                                                style="color: {{ $point->due_date < now() && $point->status != 'Completed' ? '#dc3545' : $colorScheme['text'] ?? $colorScheme['accent'] }};">
                                                <i class="bi bi-calendar-event-fill me-1"></i>
                                                Due: {{ \Carbon\Carbon::parse($point->due_date)->format('d-m-Y') }}
                                            </span>
                                        @endif
                                    </div>
                                    @if ($point->updates->isNotEmpty())
                                        <div class="task-updates mt-3 pt-3"
                                            style="border-top: 2px dashed {{ $colorScheme['border'] }};">
                                            <h6 class="small mb-3 fw-bold" style="color: {{ $colorScheme['accent'] }};">
                                                <i class="bi bi-clock-history me-1"></i>Task Updates & Progress
                                            </h6>
                                            <ul class="timeliness">
                                                @foreach ($point->updates as $updateIndex => $update)
                                                    <li class="timeliness-item">
                                                        <div class="timeliness-marker"
                                                            style="border-color: {{ $colorScheme['accent'] }}; background-color: {{ $colorScheme['bg'] }};">
                                                        </div>
                                                        <div class="timeliness-content"
                                                            style="background-color: rgba(255,255,255,0.7); padding: 12px; border-radius: 8px; margin-bottom: 12px; border-left: 3px solid {{ $colorScheme['accent'] }};">
                                                            <p class="mb-2 fw-bold"
                                                                style="color: {{ $colorScheme['text'] ?? $colorScheme['accent'] }}; font-size: 0.9rem;">
                                                                <i class="bi bi-pencil-square me-1"></i>Update
                                                                #{{ $updateIndex + 1 }}
                                                                <span class="text-muted"
                                                                    style="font-size: 0.8rem; font-weight: normal;">({{ $update->created_at->format('d-m-Y H:i') }})</span>
                                                            </p>
                                                            <p class="mb-2" style="color: #495057; line-height: 1.6;">
                                                                {{ $update->update_text }}
                                                            </p>
                                                            @if ($update->vertical_head_remark)
                                                                <div class="remark mb-2 p-2 rounded"
                                                                    style="background-color: #fff3cd; border-left: 3px solid #ffc107;">
                                                                    <strong style="color: #856404;"><i
                                                                            class="bi bi-person-badge me-1"></i>Vertical
                                                                        Head Remark:</strong>
                                                                    <span
                                                                        style="color: #856404;">{{ $update->vertical_head_remark }}</span>
                                                                </div>
                                                            @endif
                                                            @if ($update->admin_remark)
                                                                <div class="remark mb-0 p-2 rounded"
                                                                    style="background-color: #d1ecf1; border-left: 3px solid #0dcaf0;">
                                                                    <strong style="color: #0c5460;"><i
                                                                            class="bi bi-shield-check me-1"></i>Admin
                                                                        Remark:</strong>
                                                                    <span
                                                                        style="color: #0c5460;">{{ $update->admin_remark }}</span>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        {{-- Hidden forms for status update --}}
                        @foreach ($points as $point)
                            {{-- Hidden forms for status update --}}
                            <form id="status-form-{{ $point->id }}-pending"
                                action="{{ route('discussion-points.update-status', $point->id) }}" method="POST"
                                class="d-none">
                                @csrf <input type="hidden" name="status" value="Pending"></form>
                            <form id="status-form-{{ $point->id }}-progress"
                                action="{{ route('discussion-points.update-status', $point->id) }}" method="POST"
                                class="d-none">
                                @csrf <input type="hidden" name="status" value="In Progress"></form>
                            <form id="status-form-{{ $point->id }}-completed"
                                action="{{ route('discussion-points.update-status', $point->id) }}" method="POST"
                                class="d-none">
                                @csrf <input type="hidden" name="status" value="Completed"></form>
                        @endforeach
                    @empty
                        <div class="text-center text-muted p-4">No discussion points or tasks have been added yet.</div>
                    @endforelse
                </div>

                {{-- Tab 3 - Attendees --}}
                <div class="tab-pane mt-4 fade" id="attendees" role="tabpanel" aria-labelledby="attendees-tab">
                    <h4 class="mb-3">Meeting Attendees</h4>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col">Name</th>
                                    <th scope="col">Department</th>
                                    <th scope="col">Role</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($meet->attendees as $attendee)
                                    <tr>
                                        <td>{{ $attendee->name }}</td>
                                        <td>{{ $attendee->department ?? 'N/A' }}</td>
                                        <td>{{ $attendee->role_name ?? 'Attendee' }}</td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                                data-bs-target="#addDiscussionPointModal"
                                                data-assignee-id="{{ $attendee->id }}"><i class="bi bi-plus"></i> Add
                                                Task</button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">No attendees found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Tab 4 - Responsibilities --}}
                <div class="tab-pane mt-4 fade" id="responsibilities" role="tabpanel"
                    aria-labelledby="responsibilities-tab">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="mb-0">Staff Responsibilities Summary</h4>
                        <select class="form-select w-auto" id="responsibility-filter">
                            <option value="all">All Attendees</option>
                            @foreach ($responsibilities->keys() as $assigneeName)
                                @if ($assigneeName)
                                    <option value="{{ $assigneeName }}">{{ $assigneeName }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div id="responsibility-list">
                        @forelse ($responsibilities as $assigneeName => $tasks)
                            @if ($assigneeName)
                                <div class="responsibility-card mb-3" data-assignee-name="{{ $assigneeName }}">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h5 class="mb-0">{{ $assigneeName }}</h5>
                                            <small
                                                class="text-muted">{{ $tasks->first()->assignee->department ?? 'N/A' }}</small>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-secondary text-dark mb-1">{{ $tasks->count() }}
                                                Total</span>
                                            <span
                                                class="badge bg-success mb-1">{{ $tasks->where('status', 'Completed')->count() }}
                                                Done</span>
                                            <span
                                                class="badge bg-warning text-dark mb-1">{{ $tasks->where('status', 'In Progress')->count() }}
                                                Progress</span>
                                            <span
                                                class="badge bg-light text-dark mb-1">{{ $tasks->where('status', 'Pending')->count() }}
                                                Pending</span>
                                        </div>
                                    </div>
                                    <hr>
                                    <h6>All Tasks:</h6>
                                    @foreach ($tasks as $task)
                                        <div class="p-2 rounded d-flex justify-content-between align-items-center mb-2"
                                            style="background-color: #f8f9fa;">
                                            <div>
                                                @if ($task->status == 'Completed')
                                                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                                                @endif
                                                {{ $task->title }}
                                            </div>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="dropdown">
                                                    <button
                                                        class="badge-dropdown dropdown-toggle badge 
                                                        @if ($task->status == 'Completed') bg-success
                                                        @elseif($task->status == 'In Progress') bg-warning text-dark
                                                        @else bg-secondary text-dark @endif"
                                                        type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                        {{ $task->status }}
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li><a class="dropdown-item" href="#"
                                                                onclick="event.preventDefault(); document.getElementById('status-form-{{ $task->id }}-pending').submit();">Pending</a>
                                                        </li>
                                                        <li><a class="dropdown-item" href="#"
                                                                onclick="event.preventDefault(); document.getElementById('status-form-{{ $task->id }}-progress').submit();">In
                                                                Progress</a></li>
                                                        <li><a class="dropdown-item" href="#"
                                                                onclick="event.preventDefault(); document.getElementById('status-form-{{ $task->id }}-completed').submit();">Completed</a>
                                                        </li>
                                                    </ul>
                                                </div>
                                                @if ($task->due_date)
                                                    <span class="small text-muted">Due:
                                                        {{ \Carbon\Carbon::parse($task->due_date)->format('Y-m-d') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        @empty
                            <div class="text-center text-muted p-4">No one has been assigned any responsibilities yet.
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Tab 5 - Follow-up Meetings --}}
                <div class="tab-pane mt-4 fade" id="followups" role="tabpanel" aria-labelledby="followups-tab">
                    <h4 class="mb-3">Follow-up Meetings</h4>
                    <div class="list-group">
                        @forelse ($meet->followUps as $followUp)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0">{{ $followUp->title }}</h6>
                                    <small
                                        class="text-muted">{{ \Carbon\Carbon::parse($followUp->meet_date)->format('Y-m-d') }}</small>
                                </div>
                                <div>
                                    <span
                                        class="badge bg-light text-dark me-2">{{ $followUp->status ?? 'scheduled' }}</span>
                                    <a href="{{ route('meets.details', $followUp->id) }}"
                                        class="btn btn-sm btn-outline-secondary">View Details</a>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted p-4">No follow-up meetings have been scheduled.</div>
                        @endforelse
                    </div>
                    <div class="mt-3">
                        <button class="btn btn-primary w-100" data-bs-toggle="modal"
                            data-bs-target="#scheduleFollowUpModal"><i class="bi bi-plus-lg me-2"></i>Schedule
                            Follow-up Meeting</button>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <!-- Add New Discussion Point Modal -->
    <div class="modal fade" id="addDiscussionPointModal" tabindex="-1" aria-labelledby="addDiscussionPointModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-lg">
            <div class="modal-content">
                <form action="{{ route('discussion-points.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="meet_id" value="{{ $meet->id }}">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addDiscussionPointModalLabel">Add New Discussion Point</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control" id="title" name="title"
                                placeholder="Enter task title" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3" style="height:100px;"
                                placeholder="Enter task description"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="project_select" class="form-label">Project <small class="text-muted">(Select
                                    existing or type new)</small></label>
                            <input type="text" class="form-control" id="project_select" name="project_name"
                                list="projects_list" placeholder="Select project or type to create new..."
                                autocomplete="off">
                            <datalist id="projects_list">
                                @foreach ($projects as $project)
                                    <option value="{{ $project->project_name }}" data-project-id="{{ $project->id }}">
                                @endforeach
                            </datalist>
                            <input type="hidden" id="project_id" name="project_id" value="">
                            <small class="text-muted">Type to search existing projects or enter a new project name</small>
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="assignee_id" class="form-label">Assigned To</label>
                                <select class="form-select" id="assignee_id" name="assigned_to">
                                    <option value="">Select Assignee</option>
                                    @foreach ($assignees as $assignee)
                                        <option value="{{ $assignee->id }}">{{ $assignee->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="assignee_id" class="form-label">Assigned By</label>
                                <select class="form-select" id="assignee_id" name="assignee_id">
                                    <option value="">Select Assignee</option>
                                    @foreach ($assignees as $assignee)
                                        <option value="{{ $assignee->id }}">{{ $assignee->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="department" class="form-label">Department</label>
                                <select class="form-select" id="department" name="department">
                                    <option value="">Select Department</option>
                                    <option value="Design">Design</option>
                                    <option value="Engineering">Engineering</option>
                                    <option value="Product">Product</option>
                                    <option value="QA">QA</option>
                                    <option value="Client">Client</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="priority" class="form-label">Priority</label>
                                <select class="form-select" id="priority" name="priority">
                                    <option value="High">High</option>
                                    <option value="Medium" selected>Medium</option>
                                    <option value="Low">Low</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="due_date" class="form-label">Due Date</label>
                                <input type="date" class="form-control" id="due_date" name="due_date">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Task</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Note Modal -->
    <div class="modal fade" id="addNoteModal" tabindex="-1" aria-labelledby="addNoteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('discussion-points.updates.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="discussion_point_id" id="modal_discussion_point_id">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addNoteModalLabel">Add Note/Update</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="update_text" class="form-label">Update Note</label>
                            <textarea class="form-control" id="update_text" name="update_text" rows="3" style="height:100px;"required
                                placeholder="Provide a short update on the task..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="vertical_head_remark" class="form-label">Vertical Head Remark</label>
                            <textarea class="form-control" id="vertical_head_remark" name="vertical_head_remark"
                                rows="2"style="height:100px;" placeholder="Add remark from the vertical head (optional)"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="admin_remark" class="form-label">Admin Remark</label>
                            <textarea class="form-control" id="admin_remark" name="admin_remark" rows="2"
                                style="height:100px;"placeholder="Add admin remark (optional)"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Note</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Schedule Follow-up Modal -->
    <div class="modal fade" id="scheduleFollowUpModal" tabindex="-1" aria-labelledby="scheduleFollowUpModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('meets.schedule-follow-up', $meet->id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="scheduleFollowUpModalLabel">Schedule Follow-up Meeting</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="followup_title" class="form-label">Follow-up Title</label>
                            <input type="text" class="form-control" id="followup_title" name="title"
                                value="Follow-up: {{ $meet->title }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="followup_date" class="form-label">New Date</label>
                            <input type="date" class="form-control" id="followup_date" name="meet_date" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="followup_time_from" class="form-label">Start Time</label>
                                <input type="time" class="form-control" id="followup_time_from" name="meet_time_from"
                                    required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="followup_time_to" class="form-label">End Time</label>
                                <input type="time" class="form-control" id="followup_time_to" name="meet_time_to"
                                    required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Schedule Follow-up</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .summary-card {
            border: 1px solid #e9ecef;
            border-radius: 0.5rem;
        }

        .summary-card .card-body {
            padding: 1.25rem;
        }

        .summary-card h1,
        .summary-card .h1 {
            font-size: 2rem;
            font-weight: 600;
        }

        /* FIX: This style block overrides the conflicting rule from your style.css */
        .card .nav-tabs {
            position: relative;
            /* Overrides position: fixed */
            max-width: 100%;
            /* Overrides max-width: 220px */
            flex-wrap: nowrap;
            /* Prevents wrapping */
        }

        /* END OF FIX */

        .nav-tabs .nav-link {
            border: 0;
            color: #6c757d;
            border-bottom: 2px solid transparent;
            padding-left: 1rem;
            padding-right: 1rem;
        }

        .nav-tabs .nav-link.active {
            color: #0d6efd;
            background-color: transparent;
            border-bottom: 2px solid #0d6efd;
        }

        .task-status-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 4px;
        }

        .dot-done {
            background-color: #198754;
        }

        .dot-progress {
            background-color: #ffc107;
        }

        .dot-pending {
            background-color: #6c757d;
        }

        .task-card,
        .responsibility-card {
            border: 1px solid #e9ecef;
            border-radius: 0.5rem;
            padding: 1.25rem;
        }

        .timeliness {
            list-style-type: none;
            padding-left: 0;
            margin-left: 10px;
            border-left: 2px solid #e9ecef;
            border-right: none;
        }

        .timeliness-item {
            position: relative;
            padding: 0.5rem 0 0.5rem 1.5rem;
        }

        .timeliness-marker {
            position: absolute;
            top: 1.0rem;
            left: -7px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: #fff;
            border: 2px solid #0d6efd;
        }

        .timeliness-item:last-child {
            padding-bottom: 0;
        }

        .timeliness-content {
            font-size: 0.875rem;
            color: #6c757d;
        }

        .timeliness-content strong {
            color: #343a40;
        }

        .remark {
            font-size: 0.8rem;
            padding-left: 1rem;
            border-left: 2px solid #f0f2f5;
            margin-top: 0.5rem;
            color: #495057;
            max-width: 460px;
            white-space: normal;
            word-wrap: break-word;
        }

        .table {
            border: 1px solid #e9ecef;
            border-radius: 0.5rem;
            overflow: hidden;
        }

        .table thead {
            background-color: #f8f9fa;
        }

        .project-group {
            margin-bottom: 2rem;
        }

        .project-header {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }

        .project-header:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .task-card {
            transition: all 0.3s ease;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .task-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transform: translateX(4px);
        }

        .task-updates {
            background: linear-gradient(90deg, transparent 0%, rgba(255, 255, 255, 0.3) 50%, transparent 100%);
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('task-search-input');
            if (searchInput) {
                const taskCards = document.querySelectorAll('.task-card');
                searchInput.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase();

                    taskCards.forEach(card => {
                        const assigneeName = (card.dataset.assigneeName || '').toLowerCase();
                        if (assigneeName.includes(searchTerm)) {
                            card.style.display = 'block';
                        } else {
                            card.style.display = 'none';
                        }
                    });
                });
            }

            var addNoteModal = document.getElementById('addNoteModal');
            addNoteModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;
                var pointId = button.getAttribute('data-point-id');
                var modalPointIdInput = addNoteModal.querySelector('#modal_discussion_point_id');
                modalPointIdInput.value = pointId;
            });

            var addTaskModal = document.getElementById('addDiscussionPointModal');
            addTaskModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;
                var assigneeId = button.getAttribute('data-assignee-id');
                if (assigneeId) {
                    var assigneeSelect = addTaskModal.querySelector('select[name="assigned_to"]');
                    assigneeSelect.value = assigneeId;
                }
            });

            const responsibilityFilter = document.getElementById('responsibility-filter');
            if (responsibilityFilter) {
                responsibilityFilter.addEventListener('change', function() {
                    const selectedAssignee = this.value;
                    const responsibilityCards = document.querySelectorAll(
                        '#responsibility-list .responsibility-card');

                    responsibilityCards.forEach(card => {
                        card.style.display = (selectedAssignee === 'all' || card.dataset
                            .assigneeName === selectedAssignee) ? 'block' : 'none';
                    });
                });
            }

            // Project selection handler
            const projectSelect = document.getElementById('project_select');
            const projectIdInput = document.getElementById('project_id');
            const projectsList = document.getElementById('projects_list');

            if (projectSelect) {
                projectSelect.addEventListener('input', function() {
                    const selectedValue = this.value;
                    const matchingOption = Array.from(projectsList.options).find(option => option.value ===
                        selectedValue);

                    if (matchingOption && matchingOption.dataset.projectId) {
                        projectIdInput.value = matchingOption.dataset.projectId;
                    } else {
                        projectIdInput.value = ''; // New project - will be created
                    }
                });

                projectSelect.addEventListener('change', function() {
                    const selectedValue = this.value;
                    const matchingOption = Array.from(projectsList.options).find(option => option.value ===
                        selectedValue);

                    if (matchingOption && matchingOption.dataset.projectId) {
                        projectIdInput.value = matchingOption.dataset.projectId;
                    } else {
                        projectIdInput.value = '';
                    }
                });
            }
        });
    </script>
@endpush
