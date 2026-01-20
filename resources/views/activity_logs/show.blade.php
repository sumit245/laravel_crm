@extends('layouts.main')

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center mb-3">
        <h4 class="card-title mb-0">Activity Log #{{ $log->id }}</h4>
        <a href="{{ route('activity-logs.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="mdi mdi-arrow-left"></i> Back to logs
        </a>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">Summary</h5>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Time</dt>
                        <dd class="col-sm-8">{{ $log->created_at?->format('Y-m-d H:i:s') }}</dd>

                        <dt class="col-sm-4">User</dt>
                        <dd class="col-sm-8">
                            @if ($log->user)
                                {{ $log->user->name ?? $log->user->firstName . ' ' . $log->user->lastName }}
                            @else
                                <span class="text-muted">System</span>
                            @endif
                        </dd>

                        <dt class="col-sm-4">Project</dt>
                        <dd class="col-sm-8">{{ $log->project?->project_name ?? '-' }}</dd>

                        <dt class="col-sm-4">Module</dt>
                        <dd class="col-sm-8">{{ $log->module }}</dd>

                        <dt class="col-sm-4">Action</dt>
                        <dd class="col-sm-8">{{ $log->action }}</dd>

                        <dt class="col-sm-4">Entity</dt>
                        <dd class="col-sm-8">
                            @if ($log->entity_type && $log->entity_id)
                                {{ class_basename($log->entity_type) }} #{{ $log->entity_id }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </dd>

                        <dt class="col-sm-4">IP Address</dt>
                        <dd class="col-sm-8">{{ $log->ip_address ?? '-' }}</dd>

                        <dt class="col-sm-4">Request ID</dt>
                        <dd class="col-sm-8">{{ $log->request_id ?? '-' }}</dd>

                        <dt class="col-sm-4">Batch ID</dt>
                        <dd class="col-sm-8">{{ $log->batch_id ?? '-' }}</dd>

                        <dt class="col-sm-4">Description</dt>
                        <dd class="col-sm-8">{{ $log->description ?? '-' }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">Changes</h5>
                </div>
                <div class="card-body">
                    @if (is_array($log->changes) && (!empty($log->changes['before']) || !empty($log->changes['after'])))
                        <pre class="small mb-0">{{ json_encode($log->changes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    @else
                        <span class="text-muted">No change set recorded.</span>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Extra</h5>
                </div>
                <div class="card-body">
                    @if (is_array($log->extra) && !empty($log->extra))
                        <pre class="small mb-0">{{ json_encode($log->extra, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    @else
                        <span class="text-muted">No extra data recorded.</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

