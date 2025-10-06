@extends('layouts.main')

@section('content')
    <div class="container-fluid my-4">
        <div class="d-flex justify-content-between align-items-center mb-4 px-3">
            <div>
                <h2 class="mb-0">Discussion Points Kanban</h2>
                <p class="text-muted-light">Track and manage discussion points from all your meetings.</p>
            </div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDiscussionPointModal">
                <i class="bi bi-plus-lg me-2"></i>Add Discussion Point
            </button>
        </div>

        {{-- Header Section with 3 Columns --}}
        <div class="row mb-3 px-2">
            <div class="col-md-4 kanban-column-wrapper">
                <div class="kanban-column-header">
                    <h1 class="mb-1">2</h1>
                    <p class="mb-0 text-muted">To Do</p>
                </div>
            </div>
            <div class="col-md-4 kanban-column-wrapper">
                <div class="kanban-column-header">
                    <h1 class="mb-1">1</h1>
                    <p class="mb-0 text-muted">In Progress</p>
                </div>
            </div>
            <div class="col-md-4 kanban-column-wrapper">
                <div class="kanban-column-header">
                    <h1 class="mb-1">1</h1>
                    <p class="mb-0 text-muted">Done</p>
                </div>
            </div>
        </div>

        {{-- Kanban Body with 3 Columns --}}
        <div class="row gx-2 kanban-container px-2">
            {{-- To Do Column --}}
            <div class="col-md-4 kanban-column-wrapper">
                <h5 class="mb-3 ps-2">To Do <span class="column-count-badge">2</span></h5>
                <div class="kanban-column p-1">
                    <div class="kanban-card">
                        <div class="dropdown position-absolute top-0 end-0 me-2 mt-2">
                            <button class="btn btn-dots p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#">Add Remark</a></li>
                                <li><a class="dropdown-item" href="#">Move</a></li>
                                <li><a class="dropdown-item text-danger" href="#">Delete</a></li>
                            </ul>
                        </div>
                        <h6 class="card-title">Update user authentication flow</h6>
                        <p class="card-text">Discussed improving the login process with 2FA...</p>
                        <div class="d-flex align-items-center mb-2">
                            {{-- <i class="bi bi-people me-1 text-muted"></i> <span class="small text-muted">Security Review
                                Meeting</span> --}}
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge bg-primary">High</span>
                                <span class="badge bg-light text-dark ms-2"><i class="bi bi-person me-1"></i>John Doe</span>
                            </div>
                            <span class="small text-muted">2024-01-25</span>
                        </div>
                    </div>
                    <div class="kanban-card">
                        <div class="dropdown position-absolute top-0 end-0 me-2 mt-2">
                            <button class="btn btn-dots p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#">Edit</a></li>
                                <li><a class="dropdown-item" href="#">Move</a></li>
                                <li><a class="dropdown-item text-danger" href="#">Delete</a></li>
                            </ul>
                        </div>
                        <h6 class="card-title">Performance monitoring setup</h6>
                        <p class="card-text">Implement comprehensive monitoring for application...</p>
                        <div class="d-flex align-items-center mb-2">
                            {{-- <i class="bi bi-building me-1 text-muted"></i> <span class="small text-muted">Infrastructure
                                Planning</span> --}}
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge bg-primary">High</span>
                                <span class="badge bg-light text-dark ms-2"><i class="bi bi-person me-1"></i>Tom
                                    Brown</span>
                            </div>
                            <span class="small text-muted">2024-01-30</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- In Progress Column --}}
            <div class="col-md-4 kanban-column-wrapper">
                <h5 class="mb-3 ps-2">In Progress <span class="column-count-badge">1</span></h5>
                <div class="kanban-column p-1">
                    <div class="kanban-card">
                        <div class="dropdown position-absolute top-0 end-0 me-2 mt-2">
                            <button class="btn btn-dots p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#">Edit</a></li>
                                <li><a class="dropdown-item" href="#">Move</a></li>
                                <li><a class="dropdown-item text-danger" href="#">Delete</a></li>
                            </ul>
                        </div>
                        <h6 class="card-title">Database optimization strategy</h6>
                        <p class="card-text">Need to analyze current query performance and...</p>
                        <div class="d-flex align-items-center mb-2">
                            {{-- <i class="bi bi-pc-display-horizontal me-1 text-muted"></i> <span
                                class="small text-muted">Technical Architecture R...</span> --}}
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge bg-warning text-dark">Medium</span>
                                <span class="badge bg-light text-dark ms-2"><i class="bi bi-person me-1"></i>Jane
                                    Smith</span>
                            </div>
                            <span class="small text-muted">2024-01-20</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Done Column --}}
            <div class="col-md-4 kanban-column-wrapper">
                <h5 class="mb-3 ps-2">Done <span class="column-count-badge">1</span></h5>
                <div class="kanban-column p-1">
                    <div class="kanban-card">
                        <div class="dropdown position-absolute top-0 end-0 me-2 mt-2">
                            <button class="btn btn-dots p-0" type="button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#">Edit</a></li>
                                <li><a class="dropdown-item" href="#">Move</a></li>
                                <li><a class="dropdown-item text-danger" href="#">Delete</a></li>
                            </ul>
                        </div>
                        <h6 class="card-title">API documentation update</h6>
                        <p class="card-text">Complete documentation for new endpoints and depreca...</p>
                        <div class="d-flex align-items-center mb-2">
                            {{-- <i class="bi bi-file-earmark-code me-1 text-muted"></i> <span
                                class="small text-muted">Security Review Meeting</span> --}}
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge bg-info text-dark">Low</span>
                                <span class="badge bg-light text-dark ms-2"><i class="bi bi-person me-1"></i>Sarah
                                    Wilson</span>
                            </div>
                            <span class="small text-muted">2024-01-22</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal --}}
    <div class="modal fade" id="addDiscussionPointModal" tabindex="-1" aria-labelledby="addDiscussionPointModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="addDiscussionPointModalLabel">Add Discussion Point</h5>
                        <p class="text-muted small mb-0">Create a new discussion point from a meeting to track progress.
                        </p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="mb-3 form-group">
                            <label for="discussionPointTitle" class="form-label">Department</label>
                            <input type="text" class="form-control" id="discussionPointTitle"
                                placeholder="Enter discussion point title">
                        </div>
                        <div class="mb-3 form-group">
                            <label for="discussionPointTitle" class="form-label">Project Title</label>
                            <input type="text" class="form-control" id="discussionPointTitle"
                                placeholder="Enter discussion point title">
                        </div>
                        <div class="mb-3 form-group">
                            <label for="discussionPointDescription" class="form-label">Discussion Point</label>
                            <textarea id="discussionPointDescription" class="form-control" rows=3 placeholder="Describe the discussion point"
                                style="height:100px;"></textarea>
                        </div>
                        <div class="mb-3 form-group">
                            <label for="discussionPointDescription" class="form-label">Discussion Detail</label>
                            <textarea id="discussionPointDescription" class="form-control" rows=3 placeholder="Describe the discussion point"
                                style="height:100px;"></textarea>
                        </div>
                        <div class="mb-3 form-group">
                            <label for="discussionPointTitle" class="form-label">Action required</label>
                            <input type="text" class="form-control" id="discussionPointTitle"
                                placeholder="Enter discussion point title">
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6 form-group">
                                <label for="discussionPointPriority" class="form-label">Priority</label>
                                <select class="form-select" id="discussionPointPriority">
                                    <option selected>Medium</option>
                                    <option value="1">High</option>
                                    <option value="2">Low</option>
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="discussionPointDueDate" class="form-label">Due Date</label>
                                <input type="text" class="form-control" id="discussionPointDueDate"
                                    placeholder="dd/mm/yyyy">
                            </div>
                        </div>
                        <div class="mb-3 form-group">
                            <label for="discussionPointAssignee" class="form-label">Assignee</label>
                            <input type="text" class="form-control" id="discussionPointAssignee"
                                placeholder="Enter assignee name">
                        </div>
                        <div class="mb-3 form-group">
                            <label for="discussionPointAssignedTo" class="form-label">Assigned To</label>
                            <input type="text" class="form-control" id="discussionPointAssignedTo"
                                placeholder="Participant to whom this is assigned">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary">Add Discussion Point</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .kanban-column-header {
            background-color: #fff;
            border-radius: .5rem;
            border: 1px solid #dee2e6;
            padding: 1rem;
            text-align: center;
            margin-bottom: 1rem;
        }

        .kanban-card {
            background-color: #fff;
            border: 1px solid #dee2e6;
            border-radius: .5rem;
            padding: 1rem;
            margin-bottom: 1rem;
            box-shadow: 0 .125rem .25rem rgba(0, 0, 0, .075);
            position: relative;
        }

        .kanban-card .card-title {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: .5rem;
        }

        .kanban-card .card-text {
            font-size: .875rem;
            color: #6c757d;
        }

        .badge-pill-light {
            background-color: #e9ecef;
            color: #495057;
            padding: .35em .35em;
            border-radius: 50rem;
            font-size: .75em;
            font-weight: normal;
        }

        .badge-count {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: .7em;
            padding: .3em .6em;
        }

        .column-count-badge {
            background-color: rgba(0, 0, 0, .05);
            color: #343a40;
            padding: .2em .5em;
            border-radius: .3rem;
            font-size: .8em;
            font-weight: normal;
        }

        .kanban-column {
            min-height: 200px;
            padding-right: 0.5rem;
            padding-left: 0.5rem;
            border: 1px solid #dee2e6;
            border-radius: .5rem;
            margin-bottom: 1rem;
            overflow: auto;
        }

        .kanban-container {
            flex-wrap: nowrap;
            overflow-x: auto;
            padding-bottom: 1rem;
        }

        .kanban-column-wrapper {
            flex-basis: 0;
            flex-grow: 1;
            max-width: 100%;
        }

        /* Updated for 3 columns */
        @media (min-width: 768px) {
            .kanban-column-wrapper {
                flex: 0 0 33.33333%;
                max-width: 33.33333%;
            }
        }

        .text-muted-light {
            color: #6c757d !important;
        }

        .btn-dots {
            background: none;
            border: none;
            padding: 0;
            position: absolute;
            top: 8px;
            right: 8px;
            color: #6c757d;
        }

        .btn-dots:hover {
            color: #343a40;
        }

        .dropdown-toggle::after {
            display: none;
        }

        .form-select-sm {
            padding-top: .25rem;
            padding-bottom: .25rem;
            padding-left: .5rem;
            font-size: .875rem;
        }
    </style>
@endpush
