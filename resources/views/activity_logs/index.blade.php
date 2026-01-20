@extends('layouts.main')

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center mb-3">
        <h4 class="card-title mb-0">Activity Logs</h4>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('activity-logs.index') }}" class="row g-2">
                <div class="col-md-2">
                    <label class="form-label text-muted mb-1">User</label>
                    <select name="user_id" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" @selected(request('user_id') == $user->id)>
                                {{ $user->name ?? $user->firstName . ' ' . $user->lastName }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label text-muted mb-1">Project</label>
                    <select name="project_id" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach ($projects as $project)
                            <option value="{{ $project->id }}" @selected(request('project_id') == $project->id)>
                                {{ $project->project_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label text-muted mb-1">Module</label>
                    <select name="module" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach ($modules as $module)
                            <option value="{{ $module }}" @selected(request('module') === $module)>{{ $module }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label text-muted mb-1">Action</label>
                    <select name="action" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach ($actions as $action)
                            <option value="{{ $action }}" @selected(request('action') === $action)>{{ $action }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label text-muted mb-1">From</label>
                    <input type="date" name="from" value="{{ request('from') }}" class="form-control form-control-sm">
                </div>

                <div class="col-md-2">
                    <label class="form-label text-muted mb-1">To</label>
                    <input type="date" name="to" value="{{ request('to') }}" class="form-control form-control-sm">
                </div>

                <div class="col-md-3 mt-2">
                    <label class="form-label text-muted mb-1">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm"
                           placeholder="Description / entity">
                </div>

                <div class="col-md-3 d-flex align-items-end mt-2">
                    <button type="submit" class="btn btn-outline-primary btn-sm me-2">Filter</button>
                    <a href="{{ route('activity-logs.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <x-datatable id="activity-logs-table">
                <x-slot name="header">
                    <tr>
                        <th>Time</th>
                        <th>User</th>
                        <th>Project</th>
                        <th>Module / Action</th>
                        <th>Entity</th>
                        <th>Description</th>
                        <th class="text-center" style="width: 60px;">View</th>
                    </tr>
                </x-slot>

                @foreach ($logs as $log)
                    <tr>
                        <td>{{ $log->created_at?->format('Y-m-d H:i:s') }}</td>
                        <td>
                            @if ($log->user)
                                {{ $log->user->name ?? $log->user->firstName . ' ' . $log->user->lastName }}
                            @else
                                <span class="text-muted">System</span>
                            @endif
                        </td>
                        <td>
                            {{ $log->project?->project_name ?? '-' }}
                        </td>
                        <td>
                            <span class="badge bg-light text-dark">{{ $log->module }}</span>
                            <span class="badge bg-primary">{{ $log->action }}</span>
                        </td>
                        <td>
                            @if ($log->entity_type && $log->entity_id)
                                <span class="text-muted small">{{ class_basename($log->entity_type) }} #{{ $log->entity_id }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>{{ $log->description }}</td>
                        <td class="text-center">
                            <a href="{{ route('activity-logs.show', $log) }}" class="btn btn-outline-secondary btn-sm"
                               title="View details">
                                <i class="mdi mdi-eye"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
            </x-datatable>

            <div class="mt-3">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
@endsection

