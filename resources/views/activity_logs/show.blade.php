@extends('layouts.main')

@section('content')
<style>
    /* Aesthetic Direction: Refined Minimalist / Utilitarian - Show Page */
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
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .detail-card {
        background: #ffffff;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        margin-bottom: 24px;
        overflow: hidden;
    }

    .detail-card-header {
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        padding: 16px 24px;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
        color: #475569;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .detail-row {
        display: flex;
        padding: 16px 24px;
        border-bottom: 1px solid #f1f5f9;
    }

    .detail-row:last-child {
        border-bottom: none;
    }

    .detail-label {
        flex: 0 0 140px;
        color: #64748b;
        font-weight: 500;
        font-size: 0.875rem;
    }

    .detail-value {
        flex: 1;
        color: #1e293b;
        font-weight: 500;
        font-size: 0.875rem;
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
        display: inline-block;
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
        display: inline-block;
    }

    .json-preview {
        background: #0f172a;
        color: #e2e8f0;
        padding: 20px;
        border-radius: 0 0 12px 12px;
        font-family: 'JetBrains Mono', 'Fira Code', monospace;
        font-size: 0.8rem;
        line-height: 1.5;
        overflow-x: auto;
        margin: 0;
        border: none;
    }
    
    .json-empty {
        padding: 30px 20px;
        text-align: center;
        background: #f8fafc;
        color: #64748b;
        font-style: italic;
        font-size: 0.875rem;
    }
</style>

<div class="container-fluid py-4">
    <div class="logs-header d-flex justify-content-between align-items-center">
        <div>
            <h1 class="logs-title">Event Inspection #{{ $log->id }}</h1>
            <div class="logs-subtitle">
                <i class="mdi mdi-calendar-clock"></i> 
                {{ $log->created_at?->format('F d, Y \a\t H:i:s') }}
                <span class="mx-2">•</span>
                <span class="badge-module">{{ strtoupper($log->module) }}</span>
                <span class="badge-action">{{ $log->action }}</span>
            </div>
        </div>
        <a href="{{ route('activity-logs.index') }}" class="btn btn-light" style="border: 1px solid #cbd5e1; border-radius: 8px; font-weight: 500; font-size: 0.875rem;">
            <i class="mdi mdi-arrow-left me-1"></i> Return to Registry
        </a>
    </div>

    <div class="row">
        <div class="col-lg-5">
            <div class="detail-card">
                <div class="detail-card-header">
                    <i class="mdi mdi-information-outline fs-5 text-primary"></i>
                    Context & Metadata
                </div>
                
                <div class="detail-row">
                    <div class="detail-label">Initiator</div>
                    <div class="detail-value text-primary fw-bold">
                        @if ($log->user)
                            <div class="d-flex align-items-center">
                                <i class="mdi mdi-account-circle me-2 text-secondary fs-5"></i>
                                {{ $log->user->name ?? $log->user->firstName . ' ' . $log->user->lastName }}
                            </div>
                        @else
                            <div class="d-flex align-items-center text-muted">
                                <i class="mdi mdi-robot-outline me-2 fs-5"></i>
                                System Automated
                            </div>
                        @endif
                    </div>
                </div>

                <div class="detail-row">
                    <div class="detail-label">Project Scope</div>
                    <div class="detail-value">
                        @if($log->project)
                            <i class="mdi mdi-domain me-1 text-secondary"></i> {{ $log->project->project_name }}
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </div>
                </div>

                <div class="detail-row">
                    <div class="detail-label">Entity Reference</div>
                    <div class="detail-value">
                        @if ($log->entity_type && $log->entity_id)
                            <span class="font-monospace bg-light px-2 py-1 rounded border">{{ class_basename($log->entity_type) }} #{{ $log->entity_id }}</span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </div>
                </div>

                <div class="detail-row">
                    <div class="detail-label">IP Address</div>
                    <div class="detail-value font-monospace text-muted">{{ $log->ip_address ?? 'Unknown' }}</div>
                </div>

                <div class="detail-row">
                    <div class="detail-label">Trace Identifiers</div>
                    <div class="detail-value">
                        <div class="mb-1"><span class="text-muted small me-2">REQ:</span><span class="font-monospace">{{ $log->request_id ?? 'N/A' }}</span></div>
                        <div><span class="text-muted small me-2">BAT:</span><span class="font-monospace">{{ $log->batch_id ?? 'N/A' }}</span></div>
                    </div>
                </div>

                <div class="detail-row">
                    <div class="detail-label">Description</div>
                    <div class="detail-value text-dark">{{ $log->description ?? '-' }}</div>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="detail-card">
                <div class="detail-card-header bg-white border-bottom-0 pb-0">
                    <i class="mdi mdi-swap-horizontal fs-5 text-success"></i>
                    State Delta (Changes)
                </div>
                @if (is_array($log->changes) && (!empty($log->changes['before']) || !empty($log->changes['after'])))
                    <div class="p-3 bg-white">
                        <pre class="json-preview rounded">{{ json_encode($log->changes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                @else
                    <div class="json-empty">
                        <i class="mdi mdi-code-json me-1"></i> No structured state delta recorded for this event.
                    </div>
                @endif
            </div>

            <div class="detail-card">
                <div class="detail-card-header bg-white border-bottom-0 pb-0">
                    <i class="mdi mdi-database-plus px-1 fs-5 text-info"></i>
                    Extraneous Payload
                </div>
                @if (is_array($log->extra) && !empty($log->extra))
                    <div class="p-3 bg-white">
                        <pre class="json-preview rounded">{{ json_encode($log->extra, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                @else
                    <div class="json-empty">
                        <i class="mdi mdi-database-minus me-1"></i> No auxiliary payload data attached.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
