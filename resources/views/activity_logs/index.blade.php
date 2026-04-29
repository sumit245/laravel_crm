@extends('layouts.main')

@section('content')
<style>
    /* Aesthetic Direction: Refined Minimalist / Utilitarian */
    .logs-header {
        background: linear-gradient(135deg, #fdfbfb 0%, #ebedee 100%);
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
        border: 1px solid #e2e8f0;
        margin-bottom: 24px;
        position: relative;
        overflow: hidden;
    }

    .logs-header::before {
        content: '';
        position: absolute;
        top: 0; left: 0; width: 4px; height: 100%;
        background: #3b82f6;
    }

    .logs-title {
        font-family: 'Inter', system-ui, sans-serif;
        font-weight: 700;
        letter-spacing: -0.02em;
        color: #1e293b;
        font-size: 1.5rem;
        margin-bottom: 4px;
    }

    .logs-subtitle {
        color: #64748b;
        font-size: 0.875rem;
        font-weight: 500;
    }

    .filter-card {
        background: #ffffff;
        border-radius: 12px;
        padding: 24px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        margin-bottom: 24px;
        transition: all 0.3s ease;
    }

    .filter-card:hover {
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
    }

    .custom-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-weight: 600;
        color: #475569;
        margin-bottom: 8px;
    }

    .custom-select, .custom-input {
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        padding: 8px 12px;
        font-size: 0.875rem;
        background-color: #f8fafc;
        color: #334155;
        transition: all 0.2s ease;
    }

    .custom-select:focus, .custom-input:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        background-color: #ffffff;
        outline: none;
    }

    .btn-apply {
        background: #2563eb;
        color: white;
        font-weight: 600;
        border-radius: 8px;
        padding: 8px 24px;
        border: none;
        transition: transform 0.2s, background 0.2s;
    }

    .btn-apply:hover {
        background: #1d4ed8;
        transform: translateY(-1px);
    }

    .table-container {
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #e2e8f0;
        background: white;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }

    .table {
        margin-bottom: 0;
    }

    .table thead th {
        background: #f8fafc;
        color: #475569;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
        padding: 16px;
        border-bottom: 2px solid #e2e8f0;
    }

    .table tbody td {
        padding: 16px;
        color: #334155;
        font-size: 0.875rem;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        transition: background 0.2s;
    }

    .table tbody tr:hover td {
        background-color: #f8fafc;
    }

    .badge-module {
        background-color: #eff6ff;
        color: #2563eb;
        border: 1px solid #bfdbfe;
        padding: 4px 8px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.7rem;
        letter-spacing: 0.02em;
    }

    .badge-action {
        background-color: #f1f5f9;
        color: #475569;
        border: 1px solid #e2e8f0;
        padding: 4px 8px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.7rem;
        margin-left: 4px;
    }

    .log-description {
        color: #1e293b;
        font-weight: 500;
    }

    .no-logs-state {
        padding: 60px 20px;
        text-align: center;
        background: #f8fafc;
        border-radius: 12px;
        border: 1px dashed #cbd5e1;
    }

    .pagination-wrapper .pagination {
        margin: 0;
    }
</style>

<div class="container-fluid py-4">
    <div class="logs-header">
        <h1 class="logs-title">System Activity Logs</h1>
        <p class="logs-subtitle mb-0">Monitor all critical interactions, imports, and state transitions across the platform.</p>
    </div>

    <div class="filter-card">
        <form method="GET" action="{{ route('activity-logs.index') }}" class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="custom-label">Target User</label>
                <select name="user_id" class="form-select custom-select">
                    <option value="">All Users</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" @selected(request('user_id') == $user->id)>
                            {{ $user->name ?? $user->firstName . ' ' . $user->lastName }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="custom-label">Project</label>
                <select name="project_id" class="form-select custom-select">
                    <option value="">All Projects</option>
                    @foreach ($projects as $project)
                        <option value="{{ $project->id }}" @selected(request('project_id') == $project->id)>
                            {{ $project->project_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="custom-label">Module</label>
                <select name="module" class="form-select custom-select">
                    <option value="">All Modules</option>
                    @foreach ($modules as $module)
                        <option value="{{ $module }}" @selected(request('module') === $module)>{{ $module }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="custom-label">Action</label>
                <select name="action" class="form-select custom-select">
                    <option value="">All Actions</option>
                    @foreach ($actions as $action)
                        <option value="{{ $action }}" @selected(request('action') === $action)>{{ $action }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="custom-label">Date Range</label>
                <div class="d-flex gap-2">
                    <input type="date" name="from" value="{{ request('from') }}" class="form-control custom-input" title="From">
                    <input type="date" name="to" value="{{ request('to') }}" class="form-control custom-input" title="To">
                </div>
            </div>

            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-apply flex-grow-1">Apply Filters</button>
                <a href="{{ route('activity-logs.index') }}" class="btn btn-light" title="Clear Filters" style="border: 1px solid #cbd5e1; border-radius: 8px;">
                    <i class="mdi mdi-refresh"></i>
                </a>
            </div>
            
            <div class="col-12 mt-3">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control custom-input w-100"
                       placeholder="Search inside descriptions or entity references...">
            </div>
        </form>
    </div>

    @if($logs->isEmpty())
        <div class="no-logs-state">
            <i class="mdi mdi-clipboard-text-outline text-muted" style="font-size: 48px;"></i>
            <h5 class="mt-3 text-dark font-weight-bold">No Activity Found</h5>
            <p class="text-muted">There are no logs matching your current filter criteria.</p>
        </div>
    @else
        <div class="table-container">
            <div class="table-responsive">
                <table class="table table-hover border-0">
                    <thead>
                        <tr>
                            <th>Timestamp</th>
                            <th>Initiator</th>
                            <th>Project Scope</th>
                            <th>Subsystem & Action</th>
                            <th>Entity Reference</th>
                            <th>Event Description</th>
                            <th class="text-center">Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($logs as $log)
                            <tr>
                                <td style="white-space: nowrap;">
                                    <span class="d-block fw-bold text-dark">{{ $log->created_at?->format('M d, Y') }}</span>
                                    <span class="text-muted small">{{ $log->created_at?->format('H:i:s') }}</span>
                                </td>
                                <td>
                                    @if ($log->user)
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center text-primary fw-bold me-2" style="width: 32px; height: 32px; border: 1px solid #e2e8f0;">
                                                {{ substr($log->user->firstName ?? $log->user->name ?? 'U', 0, 1) }}
                                            </div>
                                            <span>{{ $log->user->name ?? $log->user->firstName . ' ' . $log->user->lastName }}</span>
                                        </div>
                                    @else
                                        <span class="text-muted fst-italic">System Automated</span>
                                    @endif
                                </td>
                                <td>
                                    @if($log->project)
                                        <span class="text-dark fw-medium">{{ $log->project->project_name }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge-module">{{ strtoupper($log->module) }}</span>
                                    <span class="badge-action">{{ $log->action }}</span>
                                </td>
                                <td>
                                    @if ($log->entity_type && $log->entity_id)
                                        <span class="text-muted font-monospace bg-light px-2 py-1 rounded border">{{ class_basename($log->entity_type) }} #{{ $log->entity_id }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="log-description">{{ Str::limit($log->description, 60) }}</td>
                                <td class="text-center">
                                    <a href="{{ route('activity-logs.show', $log) }}" class="btn btn-sm btn-light"
                                       title="Inspect Event" style="border-radius: 8px; border: 1px solid #e2e8f0; color: #475569;">
                                        <i class="mdi mdi-arrow-right"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            @if($logs->hasPages())
            <div class="d-flex justify-content-between align-items-center p-3 border-top pagination-wrapper bg-white">
                <span class="text-muted small">Showing {{ $logs->firstItem() }} to {{ $logs->lastItem() }} of {{ $logs->total() }} events</span>
                {{ $logs->links('pagination::bootstrap-5') }}
            </div>
            @endif
        </div>
    @endif
</div>
@endsection
