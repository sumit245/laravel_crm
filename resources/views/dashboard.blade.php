@extends('layouts.main')

@php
    $activeDateFilter = $filters['date_filter'] ?? 'this_month';
    $resolvedStartDate = null;
    $resolvedEndDate = null;

    if ($activeDateFilter === 'custom' && !empty($filters['start_date']) && !empty($filters['end_date'])) {
        try {
            $resolvedStartDate = \Illuminate\Support\Carbon::parse($filters['start_date'])->startOfDay();
            $resolvedEndDate = \Illuminate\Support\Carbon::parse($filters['end_date'])->endOfDay();
        } catch (\Exception $e) {
            $resolvedStartDate = null;
            $resolvedEndDate = null;
        }
    } else {
        switch ($activeDateFilter) {
            case 'today':
                $resolvedStartDate = now()->startOfDay();
                $resolvedEndDate = now()->endOfDay();
                break;
            case 'this_week':
                $resolvedStartDate = now()->startOfWeek();
                $resolvedEndDate = now()->endOfWeek();
                break;
            case 'this_month':
                $resolvedStartDate = now()->startOfMonth();
                $resolvedEndDate = now()->endOfMonth();
                break;
            case 'all_time':
                $resolvedStartDate = null;
                $resolvedEndDate = null;
                break;
            default:
                $resolvedStartDate = now()->startOfMonth();
                $resolvedEndDate = now()->endOfMonth();
                break;
        }
    }

    $resolvedDateEcho = $resolvedStartDate && $resolvedEndDate
        ? $resolvedStartDate->format('M j') . ' – ' . $resolvedEndDate->format('M j, Y')
        : 'All available dates';
@endphp

@section('content')
<main class="container-fluid p-2 dashboard-page" aria-labelledby="dashboardPageTitle">
    <!-- Header with Filters -->
    <div class="row my-2">
        <div class="col-12">
            <div class="dashboard-command-bar d-lg-flex align-items-start justify-content-between gap-3 pb-3 mb-3">
                <div class="dashboard-title-block mb-3 mb-lg-0">
                    <p class="dashboard-kicker mb-1">Operations Dashboard</p>
                    <h2 id="dashboardPageTitle" class="mb-1">Performance Overview</h2>
                    <p class="dashboard-context mb-0">
                        Project, meeting, and TA/DA signals for {{ $resolvedDateEcho }}.
                    </p>
                </div>
                <div class="d-inline-flex align-items-center gap-2 flex-wrap">
                    <!-- Project Selector -->
                    <div class="dropdown">
                        <button class="btn btn-primary dropdown-toggle" type="button" id="projectDropDown"
                            data-bs-toggle="dropdown" aria-haspopup="false" aria-expanded="false"
                            aria-label="Select project for dashboard">
                            <span id="selectedProject" class="text-light">
                                {{ $project ? $project->project_name : 'Select Project' }}
                            </span>
                        </button>
                        <div class="dropdown-menu" aria-labelledby="projectDropDown" style="max-height: 400px; overflow-y: auto;">
                            @if(auth()->user()->role === \App\Enums\UserRole::ADMIN->value)
                                <a href="#" class="dropdown-item project-item" data-project-id="" data-project-name="All Projects">
                                    <strong>All Projects</strong>
                                </a>
                                <div class="dropdown-divider"></div>
                            @endif
                            @foreach ($projects as $proj)
                                <a href="#" class="dropdown-item project-item"
                                   data-project-id="{{ $proj->id }}"
                                   data-project-name="{{ $proj->project_name }}">
                                    {{ $proj->project_name }}
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <!-- Date Filter -->
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle dashboard-date-filter-btn" type="button" id="dateFilterDropdown"
                            data-bs-toggle="dropdown" aria-expanded="false" aria-label="Select dashboard date range">
                            <span id="selectedDateFilter">{{ ucfirst(str_replace('_', ' ', $filters['date_filter'] ?? 'this_month')) }}</span>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="dateFilterDropdown">
                            <li><a href="#" class="dropdown-item date-filter-item" data-filter="today">Today</a></li>
                            <li><a href="#" class="dropdown-item date-filter-item" data-filter="this_week">This Week</a></li>
                            <li><a href="#" class="dropdown-item date-filter-item" data-filter="this_month">This Month</a></li>
                            <li><a href="#" class="dropdown-item date-filter-item" data-filter="all_time">All Time</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#customDateModal">Custom Range</a></li>
                        </ul>
                    </div>

                    <div class="d-inline-flex align-items-center gap-1 text-muted ms-sm-2" id="dashboardFreshnessContainer"
                        style="font-size: 0.82rem;">
                        <span id="dashboardFreshnessLabel">Updated just now</span>
                        <span aria-hidden="true">•</span>
                        <button type="button" class="btn btn-link p-0 text-decoration-none" id="dashboardRefreshBtn"
                            aria-label="Refresh dashboard data" style="font-size: 0.82rem;">
                            Refresh
                        </button>
                    </div>

                    @if (!empty($selected_project_id))
                        <a href="{{ route('projects.show', $selected_project_id) }}#installed-poles"
                            class="btn btn-outline-success d-inline-flex align-items-center gap-1">
                            <i class="mdi mdi-lightning-bolt" aria-hidden="true"></i>
                            Installed Poles
                        </a>
                    @endif

                    <!-- Print Action -->
                    <div class="btn-wrapper">
                        <button type="button" class="btn btn-outline-dark d-none d-md-inline-flex"
                            onclick="openPrintPreviewModal()"
                            aria-label="Open dashboard print options"
                            title="Choose what to print before continuing">
                            <i class="mdi mdi-printer"></i> Print
                        </button>
                    </div>
                </div>
            </div>
            <div class="dashboard-active-filters d-flex flex-wrap align-items-center gap-2 text-muted mt-2">
                <span class="badge bg-light text-dark border px-2 py-1">
                    {{ $project ? $project->project_name : 'All Projects' }}
                </span>
                <span class="badge bg-light text-dark border px-2 py-1">
                    {{ ucfirst(str_replace('_', ' ', $filters['date_filter'] ?? 'this_month')) }}
                </span>
                <span class="fw-semibold">
                    {{ $resolvedDateEcho }}
                </span>
            </div>
        </div>
    </div>

    <!-- Section 1: Project Performance Analytics -->
    <div class="row my-2" id="dashboardPerformanceSection">
        <div class="col-12">
            <div class="card dashboard-panel" style="border: 1px solid #dee2e6; border-radius: 4px;">
                <div class="card-header dashboard-panel-header" style="background: #f8f9fa; border-bottom: 1px solid #dee2e6; padding: 12px 16px;">
                    <h5 class="mb-0" style="font-weight: 600; font-size: 1rem; color: #495057;">
                        <i class="mdi mdi-chart-line me-2"></i>Project Performance Analytics
                    </h5>
                </div>
                <div class="card-body dashboard-panel-body" style="padding: 16px;">
                    @include('dashboard.sections.performance')
                </div>
            </div>
        </div>
    </div>

    <!-- Section 2: Meeting Summary Analytics -->
    <div class="row my-2" id="dashboardMeetingSection">
        <div class="col-12">
            <div class="card dashboard-panel" style="border: 1px solid #dee2e6; border-radius: 4px;">
                <div class="card-header dashboard-panel-header" style="background: #f8f9fa; border-bottom: 1px solid #dee2e6; padding: 12px 16px;">
                    <h5 class="mb-0" style="font-weight: 600; font-size: 1rem; color: #495057;">
                        <i class="mdi mdi-calendar-clock me-2"></i>Meeting Summary Analytics
                    </h5>
                </div>
                <div class="card-body dashboard-panel-body" style="padding: 16px;">
                    @include('dashboard.sections.meetings')
                </div>
            </div>
        </div>
    </div>

    <!-- Section 3: TA/DA Bills Analytics -->
    <div class="row my-2" id="dashboardTadaSection">
        <div class="col-12">
            <div class="card dashboard-panel" style="border: 1px solid #dee2e6; border-radius: 4px;">
                <div class="card-header dashboard-panel-header" style="background: #f8f9fa; border-bottom: 1px solid #dee2e6; padding: 12px 16px;">
                    <h5 class="mb-0" style="font-weight: 600; font-size: 1rem; color: #495057;">
                        <i class="mdi mdi-cash-multiple me-2"></i>TA/DA Bills Analytics
                    </h5>
                </div>
                <div class="card-body dashboard-panel-body" style="padding: 16px;">
                    @include('dashboard.sections.tada')
                </div>
            </div>
        </div>
    </div>
</main>

<div class="dashboard-mobile-actions d-md-none">
    <button type="button" class="btn btn-primary w-100" onclick="openPrintPreviewModal()" aria-label="Open dashboard print options">
        <i class="mdi mdi-printer me-1"></i> Print Dashboard
    </button>
</div>

<!-- Print Scope Modal -->
<div class="modal fade" id="printScopeModal" tabindex="-1" aria-labelledby="printScopeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="printScopeModalLabel">Print Scope Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2 text-muted" style="font-size: 0.92rem;">
                    Select what to include before printing.
                </p>
                <p class="mb-3">
                    <strong>Include:</strong> PM cards, Speed analysis, TA/DA
                </p>

                <div class="print-scope-row mb-2">
                    <input class="print-scope-option" type="checkbox" value="pm" id="printScopePm" checked>
                    <label for="printScopePm" class="mb-0">
                        PM cards
                    </label>
                </div>
                <div class="print-scope-row mb-2">
                    <input class="print-scope-option" type="checkbox" value="speed" id="printScopeSpeed" checked>
                    <label for="printScopeSpeed" class="mb-0">
                        Speed analysis
                    </label>
                </div>
                <div class="print-scope-row mb-3">
                    <input class="print-scope-option" type="checkbox" value="tada" id="printScopeTada">
                    <label for="printScopeTada" class="mb-0">
                        TA/DA
                    </label>
                </div>

                <div class="alert mb-0 print-scope-summary" id="printScopeSummary" role="status">
                    Included rows/items: 0
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmPrintBtn" onclick="printSelectedDashboardScope()">Print</button>
            </div>
        </div>
    </div>
</div>

<!-- Custom Date Range Modal -->
<div class="modal fade" id="customDateModal" tabindex="-1" aria-labelledby="customDateModalLabel" aria-describedby="customDateModalHelp" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="customDateModalLabel">Custom Date Range</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close custom date range"></button>
            </div>
            <div class="modal-body">
                <p id="customDateModalHelp" class="text-muted small mb-3">Choose an inclusive start and end date for dashboard metrics.</p>
                <div id="customDateError" class="alert alert-danger py-2 d-none" role="alert"></div>
                <div class="mb-3">
                    <label for="modalStartDate" class="form-label">Start Date</label>
                    <input type="date" id="modalStartDate" class="form-control">
                </div>
                <div class="mb-3">
                    <label for="modalEndDate" class="form-label">End Date</label>
                    <input type="date" id="modalEndDate" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="applyCustomDateFromModal()">Apply</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.dashboard-page {
    --dashboard-ink: #1f2933;
    --dashboard-muted: #64717a;
    --dashboard-border: #d9e2e7;
    --dashboard-panel: #ffffff;
    --dashboard-panel-soft: #f6f9fb;
    --dashboard-accent: #176f86;
    --dashboard-accent-soft: #e6f5f8;
    --dashboard-radius-panel: 8px;
    --dashboard-radius-control: 6px;
    --dashboard-radius-small: 4px;
    max-width: 100%;
}

.dashboard-command-bar {
    background: var(--dashboard-panel);
    border: 1px solid var(--dashboard-border);
    border-radius: var(--dashboard-radius-panel);
    padding: 14px 16px;
    box-shadow: 0 1px 2px rgba(31, 41, 51, 0.04);
}

.dashboard-title-block {
    min-width: 240px;
}

.dashboard-kicker {
    color: var(--dashboard-accent);
    font-size: 0.74rem;
    font-weight: 700;
    letter-spacing: 0;
    text-transform: uppercase;
}

.dashboard-page h2 {
    color: var(--dashboard-ink);
    font-size: 1.35rem;
    font-weight: 700;
    letter-spacing: 0;
    line-height: 1.25;
}

.dashboard-context,
.dashboard-active-filters {
    color: var(--dashboard-muted);
    font-size: 0.84rem;
}

.dashboard-page .btn {
    border-radius: var(--dashboard-radius-control) !important;
    min-height: 38px;
}

.dashboard-page .btn-sm {
    border-radius: var(--dashboard-radius-control) !important;
}

.dashboard-page .btn-link {
    border-radius: var(--dashboard-radius-small) !important;
}

.dashboard-page .btn:focus-visible,
.dashboard-page .form-control:focus,
.dashboard-page .dropdown-item:focus {
    box-shadow: 0 0 0 0.18rem rgba(23, 111, 134, 0.22);
    outline: 0;
}

.dashboard-page .card {
    border-radius: var(--dashboard-radius-panel) !important;
}

.dashboard-page .dashboard-panel {
    border-color: var(--dashboard-border) !important;
    border-radius: var(--dashboard-radius-panel) !important;
    box-shadow: 0 1px 2px rgba(31, 41, 51, 0.04);
}

.dashboard-page .dashboard-panel-header {
    background: var(--dashboard-panel-soft) !important;
    border-bottom-color: var(--dashboard-border) !important;
    padding: 12px 16px !important;
}

.dashboard-page .dashboard-panel-header h5 {
    color: var(--dashboard-ink) !important;
}

.dashboard-page .dashboard-panel-body {
    padding: 16px !important;
}

.dashboard-page .table-responsive {
    border: 1px solid #eef2f5;
    border-radius: var(--dashboard-radius-panel);
}

.dashboard-page .table-responsive table {
    min-width: 680px;
}

.dashboard-page .progress {
    background-color: #e8eef2;
    border-radius: var(--dashboard-radius-small) !important;
}

.dashboard-page .progress-bar {
    border-radius: var(--dashboard-radius-small) !important;
}

.dashboard-page .badge {
    border-radius: var(--dashboard-radius-small) !important;
}

.dashboard-page .dropdown-menu,
.dashboard-page .form-control,
.dashboard-page .input-group-text {
    border-radius: var(--dashboard-radius-control) !important;
}

.dashboard-page .input-group > .form-control,
.dashboard-page .input-group > .input-group-text {
    border-radius: 0 !important;
}

.dashboard-page .input-group > :first-child {
    border-top-left-radius: var(--dashboard-radius-control) !important;
    border-bottom-left-radius: var(--dashboard-radius-control) !important;
}

.dashboard-page .input-group > :last-child {
    border-top-right-radius: var(--dashboard-radius-control) !important;
    border-bottom-right-radius: var(--dashboard-radius-control) !important;
}

.dashboard-page .pole-speed-district-chip {
    border-radius: var(--dashboard-radius-control) !important;
}

.dashboard-page .metric-item {
    border-radius: var(--dashboard-radius-control) !important;
}

.dashboard-page #travelBreakdownVehicle,
.dashboard-page #travelBreakdownStatus {
    min-height: 142px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.dashboard-page #travelBreakdownVehicle > .text-center,
.dashboard-page #travelBreakdownStatus > .text-center {
    width: 100%;
}

.dashboard-page #topEngineers,
.dashboard-page #topVendors {
    min-height: 142px;
    display: flex;
    flex-direction: column;
    align-items: stretch;
    justify-content: flex-start;
}

.dashboard-page #topEngineers > *,
.dashboard-page #topVendors > * {
    flex: 0 0 auto;
    min-width: 0;
}

.dashboard-page #topEngineers > .text-center,
.dashboard-page #topVendors > .text-center {
    flex: 1 1 auto;
    width: 100%;
    display: flex;
    min-height: 142px;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.dashboard-page #topEngineers .d-flex,
.dashboard-page #topVendors .d-flex,
.dashboard-page #topEngineers .flex-grow-1,
.dashboard-page #topVendors .flex-grow-1 {
    min-width: 0;
}

.dashboard-page #topEngineers strong,
.dashboard-page #topVendors strong {
    overflow-wrap: anywhere;
}

.dashboard-page #topEngineers .btn,
.dashboard-page #topVendors .btn {
    flex: 0 0 auto;
}

.dashboard-page #travelBreakdownVehicle,
.dashboard-page #travelBreakdownStatus,
.dashboard-page #travelBreakdownVehicle > *,
.dashboard-page #travelBreakdownStatus > * {
    flex: 1 1 auto;
}

.dashboard-page #topEngineers .btn-link,
.dashboard-page #topVendors .btn-link,
.dashboard-page #travelBreakdownVehicle .btn-link,
.dashboard-page #travelBreakdownStatus .btn-link {
    min-height: 28px;
}

#printScopeModal .modal-content,
#customDateModal .modal-content {
    border-radius: var(--dashboard-radius-panel);
}

#printScopeModal .btn,
#customDateModal .btn {
    border-radius: var(--dashboard-radius-control) !important;
}

.dashboard-mobile-actions .btn {
    border-radius: var(--dashboard-radius-control) !important;
    min-height: 44px;
}

@media (max-width: 767.98px) {
    .dashboard-command-bar {
        padding: 12px;
    }

    .dashboard-page h2 {
        font-size: 1.12rem;
    }

    .dashboard-page .dropdown,
    .dashboard-page .dropdown > .btn,
    #dashboardFreshnessContainer {
        width: 100%;
    }

    #dashboardFreshnessContainer {
        justify-content: space-between;
    }
}
</style>
@endpush

@push('scripts')
<style>
.print-scope-row {
    display: flex;
    align-items: center;
    gap: 10px;
    min-height: 32px;
}

.print-scope-row input[type="checkbox"] {
    width: 18px;
    height: 18px;
    margin: 0;
    cursor: pointer;
}

.print-scope-row label {
    font-size: 0.95rem;
    cursor: pointer;
}

.print-scope-summary {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    color: #212529;
    font-size: 0.88rem;
}

.dashboard-date-filter-btn {
    color: #343a40;
    border-color: #6c757d;
    background: #ffffff;
    font-weight: 500;
}

.dashboard-date-filter-btn:hover,
.dashboard-date-filter-btn:focus,
.dashboard-date-filter-btn:active {
    color: #212529 !important;
    border-color: #495057 !important;
    background: #f5f6f8 !important;
}

@media (max-width: 1199.98px) {
    .page-body-wrapper {
        display: block !important;
        padding-left: 0 !important;
    }

    .main-panel {
        width: 100% !important;
        margin-left: 0 !important;
    }

    .content-wrapper {
        width: 100% !important;
        padding-left: 12px !important;
        padding-right: 12px !important;
    }

    .sidebar,
    .sidebar-offcanvas,
    nav.sidebar {
        display: none !important;
        width: 0 !important;
        min-width: 0 !important;
        max-width: 0 !important;
        overflow: hidden !important;
    }

    .btn-wrapper {
        display: none !important;
    }

    .dashboard-page {
        max-width: 100% !important;
        overflow-x: hidden !important;
    }

    #printScopeModal .modal-dialog {
        margin: 0.75rem;
        max-width: calc(100% - 1.5rem);
    }

    #printScopeModal .modal-footer {
        gap: 8px;
    }

    #printScopeModal .modal-footer .btn {
        min-width: 90px;
    }

    .dashboard-mobile-actions {
        display: block !important;
        position: sticky;
        bottom: 0;
        z-index: 1030;
        background: rgba(255, 255, 255, 0.98);
        border-top: 1px solid #e9ecef;
        padding: 10px 12px;
        box-shadow: 0 -4px 16px rgba(0, 0, 0, 0.08);
    }
    .dashboard-page {
        padding-bottom: 72px !important;
    }
}

@media print {
    body * {
        visibility: hidden !important;
    }

    #dashboardPrintRoot,
    #dashboardPrintRoot * {
        visibility: visible !important;
    }

    #dashboardPrintRoot {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        padding: 0;
        margin: 0;
    }
}
</style>
<script>
let currentFilters = {
    project_id: {{ $selected_project_id ?? 'null' }},
    date_filter: '{{ $filters["date_filter"] ?? "this_month" }}',
    start_date: null,
    end_date: null
};
const dashboardLoadedAt = '{{ now()->toIso8601String() }}';
let printScopeModalInstance = null;

function getRelativeTimeLabel(timestamp) {
    const loadedAt = new Date(timestamp);
    const now = new Date();
    const diffSeconds = Math.max(0, Math.floor((now - loadedAt) / 1000));

    if (diffSeconds < 60) {
        return 'Updated just now';
    }

    const diffMinutes = Math.floor(diffSeconds / 60);
    if (diffMinutes < 60) {
        return `Updated ${diffMinutes} min ago`;
    }

    const diffHours = Math.floor(diffMinutes / 60);
    if (diffHours < 24) {
        return `Updated ${diffHours} hr ago`;
    }

    const diffDays = Math.floor(diffHours / 24);
    return `Updated ${diffDays} day${diffDays === 1 ? '' : 's'} ago`;
}

function refreshDashboardFreshnessLabel() {
    const labelEl = document.getElementById('dashboardFreshnessLabel');
    if (!labelEl) return;
    labelEl.textContent = getRelativeTimeLabel(dashboardLoadedAt);
}

document.addEventListener('DOMContentLoaded', function() {
    refreshDashboardFreshnessLabel();
    setInterval(refreshDashboardFreshnessLabel, 60000);

    const refreshBtn = document.getElementById('dashboardRefreshBtn');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            window.location.reload();
        });
    }

    // Project selector - redirect to dashboard with project_id
    document.querySelectorAll('.project-item').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const projectId = this.getAttribute('data-project-id');
            const projectName = this.getAttribute('data-project-name') || 'All Projects';
            
            // Build URL with current filters
            const url = new URL(window.location.origin + '/dashboard');
            if (projectId) {
                url.searchParams.set('project_id', projectId);
            } else {
                url.searchParams.delete('project_id');
            }
            url.searchParams.set('date_filter', currentFilters.date_filter);
            if (currentFilters.start_date) {
                url.searchParams.set('start_date', currentFilters.start_date);
            }
            if (currentFilters.end_date) {
                url.searchParams.set('end_date', currentFilters.end_date);
            }
            
            // Redirect to reload page with new project
            window.location.href = url.toString();
        });
    });

    // Date filter - redirect to dashboard with date filter
    document.querySelectorAll('.date-filter-item').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const filter = this.getAttribute('data-filter');
            
            // Build URL with current project and new date filter
            const url = new URL(window.location.origin + '/dashboard');
            if (currentFilters.project_id) {
                url.searchParams.set('project_id', currentFilters.project_id);
            }
            url.searchParams.set('date_filter', filter);
            url.searchParams.delete('start_date');
            url.searchParams.delete('end_date');
            
            // Redirect to reload page with new filter
            window.location.href = url.toString();
        });
    });

    const printModalElement = document.getElementById('printScopeModal');
    if (printModalElement) {
        printScopeModalInstance = new bootstrap.Modal(printModalElement);
        printModalElement.addEventListener('shown.bs.modal', updatePrintScopeSummary);
        document.querySelectorAll('.print-scope-option').forEach(option => {
            option.addEventListener('change', updatePrintScopeSummary);
        });
    }
});

function applyCustomDateFromModal() {
    const startDateEl = document.getElementById('modalStartDate');
    const endDateEl = document.getElementById('modalEndDate');
    const errorEl = document.getElementById('customDateError');
    const startDate = startDateEl?.value;
    const endDate = endDateEl?.value;

    if (errorEl) {
        errorEl.classList.add('d-none');
        errorEl.textContent = '';
    }

    if (!startDate || !endDate) {
        if (errorEl) {
            errorEl.textContent = 'Select both start and end dates.';
            errorEl.classList.remove('d-none');
        }
        return;
    }

    if (startDate > endDate) {
        if (errorEl) {
            errorEl.textContent = 'Start date must be before or equal to end date.';
            errorEl.classList.remove('d-none');
        }
        return;
    }

    // Build URL with custom dates
    const url = new URL(window.location.origin + '/dashboard');
    if (currentFilters.project_id) {
        url.searchParams.set('project_id', currentFilters.project_id);
    }
    url.searchParams.set('date_filter', 'custom');
    url.searchParams.set('start_date', startDate);
    url.searchParams.set('end_date', endDate);

    $('#customDateModal').modal('hide');
    window.location.href = url.toString();
}

function openPrintPreviewModal() {
    if (!printScopeModalInstance) {
        return;
    }
    updatePrintScopeSummary();
    printScopeModalInstance.show();
}

function countVisibleSpeedRows() {
    const rows = document.querySelectorAll('#poleSpeedTableBody tr');
    return Array.from(rows).filter(row => row.style.display !== 'none').length;
}

function getPrintScopeCounts() {
    const includePm = document.getElementById('printScopePm')?.checked;
    const includeSpeed = document.getElementById('printScopeSpeed')?.checked;
    const includeTada = document.getElementById('printScopeTada')?.checked;

    let total = 0;
    let segments = [];

    if (includePm) {
        const pmCount = document.querySelectorAll('#districtPerformance > div .card').length;
        total += pmCount;
        segments.push(`PM cards: ${pmCount}`);
    }

    if (includeSpeed) {
        const speedCount = countVisibleSpeedRows();
        total += speedCount;
        segments.push(`Speed rows: ${speedCount}`);
    }

    if (includeTada) {
        const tadaCount = document.querySelectorAll('#dashboardTadaSection tbody tr').length;
        total += tadaCount;
        segments.push(`TA/DA rows: ${tadaCount}`);
    }

    return { total, segments };
}

function updatePrintScopeSummary() {
    const summaryEl = document.getElementById('printScopeSummary');
    const confirmBtn = document.getElementById('confirmPrintBtn');
    if (!summaryEl || !confirmBtn) return;

    const { total, segments } = getPrintScopeCounts();
    const selectedScopeCount = document.querySelectorAll('.print-scope-option:checked').length;
    confirmBtn.disabled = selectedScopeCount === 0;

    if (selectedScopeCount === 0) {
        summaryEl.textContent = 'Select at least one section to print.';
        return;
    }

    summaryEl.textContent = `Included rows/items: ${total}${segments.length ? ' (' + segments.join(' • ') + ')' : ''}`;
}

function cloneSectionForPrint(selector, heading) {
    const source = document.querySelector(selector);
    if (!source) return null;

    const wrapper = document.createElement('section');
    wrapper.style.marginBottom = '16px';
    const title = document.createElement('h3');
    title.textContent = heading;
    title.style.fontSize = '16px';
    title.style.marginBottom = '10px';
    title.style.fontWeight = '700';
    wrapper.appendChild(title);

    const clone = source.cloneNode(true);
    clone.querySelectorAll('button, .btn, a[href], .dropdown, .input-group, input, select, textarea, script').forEach(el => el.remove());
    wrapper.appendChild(clone);
    return wrapper;
}

function printSelectedDashboardScope() {
    const includePm = document.getElementById('printScopePm')?.checked;
    const includeSpeed = document.getElementById('printScopeSpeed')?.checked;
    const includeTada = document.getElementById('printScopeTada')?.checked;

    if (!includePm && !includeSpeed && !includeTada) {
        return;
    }

    const printRoot = document.createElement('div');
    printRoot.id = 'dashboardPrintRoot';
    printRoot.style.background = '#fff';
    printRoot.style.padding = '16px';

    const heading = document.createElement('h2');
    heading.textContent = 'Dashboard Print View';
    heading.style.fontSize = '18px';
    heading.style.fontWeight = '700';
    heading.style.marginBottom = '6px';
    printRoot.appendChild(heading);

    const subHeading = document.createElement('p');
    subHeading.style.marginBottom = '14px';
    subHeading.style.fontSize = '12px';
    subHeading.style.color = '#6c757d';
    subHeading.textContent = `Generated ${new Date().toLocaleString()}`;
    printRoot.appendChild(subHeading);

    if (includePm) {
        const pmBlock = cloneSectionForPrint('#districtPerformance', 'PM Cards');
        if (pmBlock) printRoot.appendChild(pmBlock);
    }
    if (includeSpeed) {
        const speedBlock = cloneSectionForPrint('#poleSpeedAnalysisSection', 'Speed Analysis');
        if (speedBlock) printRoot.appendChild(speedBlock);
    }
    if (includeTada) {
        const tadaBlock = cloneSectionForPrint('#dashboardTadaSection', 'TA/DA');
        if (tadaBlock) printRoot.appendChild(tadaBlock);
    }

    document.body.appendChild(printRoot);
    if (printScopeModalInstance) {
        printScopeModalInstance.hide();
    }

    const onAfterPrint = function() {
        const root = document.getElementById('dashboardPrintRoot');
        if (root) root.remove();
        window.removeEventListener('afterprint', onAfterPrint);
    };

    window.addEventListener('afterprint', onAfterPrint);
    window.print();
}
</script>
@endpush
