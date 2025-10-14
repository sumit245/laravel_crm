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
                    <div class="mb-3">
                        <h6 class="text-muted">Description</h6>
                        {{-- Assuming description is part of notes or another field. Let's use agenda for now if no description field exists. --}}
                        <p>{{ $meet->description ?? 'No description provided.' }}</p>
                    </div>
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
                        <div class="d-flex gap-2">
                            <select class="form-select w-auto">
                                <option selected>All Departments</option>
                                @foreach ($departments as $department)
                                    @if ($department)
                                        <option value="{{ $department }}">{{ $department }}</option>
                                    @endif
                                @endforeach
                            </select>
                            <button class="btn btn-primary" data-bs-toggle="modal"
                                data-bs-target="#addDiscussionPointModal"><i class="bi bi-plus me-2"></i>Add
                                Task</button>
                        </div>
                    </div>
                    @forelse ($meet->discussionPoints as $point)
                        <div class="task-card mb-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h5>
                                        @if ($point->status == 'Completed')
                                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                                        @elseif($point->status == 'In Progress')
                                            <i class="bi bi-clock-history text-warning me-2"></i>
                                        @else
                                            <i class="bi bi-exclamation-circle-fill text-secondary me-2"></i>
                                        @endif
                                        {{ $point->title }}
                                    </h5>
                                    <p class="text-muted small">{{ $point->description }}</p>
                                </div>
                                <div class="d-flex gap-2">
                                    @if ($point->priority)
                                        <span class="badge bg-primary">{{ $point->priority }}</span>
                                    @endif
                                    <div class="dropdown">
                                        <button
                                            class="badge-dropdown dropdown-toggle badge 
                                            @if ($point->status == 'Completed') bg-success
                                            @elseif($point->status == 'In Progress') bg-warning text-dark
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

                                    <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal"
                                        data-bs-target="#addNoteModal" data-point-id="{{ $point->id }}"><i
                                            class="bi bi-plus-lg"></i> Add Note</button>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between text-muted small mt-2">
                                <span>
                                    <i class="bi bi-person me-1"></i> Assigned To:
                                    {{ $point->assignedToUser->name ?? 'Unassigned' }}
                                    @if ($point->assignedToUser?->department)
                                        <span
                                            class="badge bg-light text-dark ms-1">{{ $point->assignedToUser->department }}</span>
                                    @endif
                                </span>
                                @if ($point->due_date)
                                    <span><i class="bi bi-calendar-event me-1"></i> Due:
                                        {{ \Carbon\Carbon::parse($point->due_date)->format('d-m-Y') }}</span>
                                @endif
                            </div>
                            @if ($point->updates->isNotEmpty())
                                <hr>
                                <div class="task-updates">
                                    <h6 class="small">Updates</h6>
                                    <ul class="timeline">
                                        @foreach ($point->updates as $update)
                                            <li class="timeline-item">
                                                <div class="timeline-marker"></div>
                                                <div class="timeline-content">
                                                    <p class="mb-1">
                                                        <strong>Update
                                                            ({{ $update->created_at->format('Y-m-d') }})
                                                            :</strong>
                                                        {{ $update->update_text }}
                                                    </p>
                                                    @if ($update->vertical_head_remark)
                                                        <p class="remark mb-1"><strong>Vertical Head:</strong>
                                                            {{ $update->vertical_head_remark }}</p>
                                                    @endif
                                                    @if ($update->admin_remark)
                                                        <p class="remark mb-0"><strong>Admin:</strong>
                                                            {{ $update->admin_remark }}</p>
                                                    @endif
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
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
                                            <span class="badge bg-secondary mb-1">{{ $tasks->count() }} Total</span>
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
                                                        @else bg-secondary @endif"
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

        .timeline {
            list-style-type: none;
            padding-left: 0;
            margin-left: 10px;
            border-left: 2px solid #e9ecef;
        }

        .timeline-item {
            position: relative;
            padding: 0.5rem 0 0.5rem 1.5rem;
        }

        .timeline-marker {
            position: absolute;
            top: 1.0rem;
            left: -7px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: #fff;
            border: 2px solid #0d6efd;
        }

        .timeline-item:last-child {
            padding-bottom: 0;
        }

        .timeline-content {
            font-size: 0.875rem;
            color: #6c757d;
        }

        .timeline-content strong {
            color: #343a40;
        }

        .remark {
            font-size: 0.8rem;
            padding-left: 1rem;
            border-left: 2px solid #f0f2f5;
            margin-top: 0.5rem;
            color: #495057;
        }

        .table {
            border: 1px solid #e9ecef;
            border-radius: 0.5rem;
            overflow: hidden;
        }

        .table thead {
            background-color: #f8f9fa;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
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
        });
    </script>
@endpush
