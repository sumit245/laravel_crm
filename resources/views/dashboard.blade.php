@extends('layouts.main')

@section('content')
<div class="container p-2">
    <!-- Header with Filters -->
    <div class="row my-2">
        <div class="col-12">
            <div class="d-sm-flex align-items-center justify-content-between border-bottom pb-2 mb-3">
                <div class="d-inline-flex align-items-center gap-2">
                    <!-- Project Selector -->
                    <div class="dropdown">
                        <button class="btn btn-primary dropdown-toggle" type="button" id="projectDropDown"
                            data-bs-toggle="dropdown" aria-haspopup="false" aria-expanded="false">
                            <span id="selectedProject" class="text-light">
                                {{ $project ? $project->project_name : 'Select Project' }}
                            </span>
                        </button>
                        <div class="dropdown-menu" aria-labelledby="projectDropDown" style="max-height: 400px; overflow-y: auto;">
                            @if(auth()->user()->role === \App\Enums\UserRole::ADMIN->value)
                                <a class="dropdown-item project-item" data-project-id="" data-project-name="All Projects">
                                    <strong>All Projects</strong>
                                </a>
                                <div class="dropdown-divider"></div>
                            @endif
                            @foreach ($projects as $proj)
                                <a class="dropdown-item project-item" 
                                   data-project-id="{{ $proj->id }}"
                                   data-project-name="{{ $proj->project_name }}">
                                    {{ $proj->project_name }}
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <!-- Date Filter -->
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="dateFilterDropdown"
                            data-bs-toggle="dropdown">
                            <span id="selectedDateFilter">{{ ucfirst(str_replace('_', ' ', $filters['date_filter'] ?? 'this_month')) }}</span>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="dateFilterDropdown">
                            <li><a class="dropdown-item date-filter-item" data-filter="today">Today</a></li>
                            <li><a class="dropdown-item date-filter-item" data-filter="this_week">This Week</a></li>
                            <li><a class="dropdown-item date-filter-item" data-filter="this_month">This Month</a></li>
                            <li><a class="dropdown-item date-filter-item" data-filter="all_time">All Time</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#customDateModal">Custom Range</a></li>
                        </ul>
                    </div>
                </div>

                <!-- Export Button -->
                <div class="btn-wrapper mt-2 mt-sm-0">
                    <a href="#" class="btn btn-outline-dark" onclick="window.print(); return false;">
                        <i class="mdi mdi-printer"></i> Print
                    </a>
                    <button class="btn btn-primary me-0 text-white" onclick="exportDashboard()">
                        <i class="mdi mdi-download"></i> Export
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 1: Project Performance Analytics -->
    <div class="row my-2">
        <div class="col-12">
            <div class="card" style="border: 1px solid #dee2e6; border-radius: 4px;">
                <div class="card-header" style="background: #f8f9fa; border-bottom: 1px solid #dee2e6; padding: 12px 16px;">
                    <h5 class="mb-0" style="font-weight: 600; font-size: 1rem; color: #495057;">
                        <i class="mdi mdi-chart-line me-2"></i>Project Performance Analytics
                    </h5>
                </div>
                <div class="card-body" style="padding: 16px;">
                    @include('dashboard.sections.performance')
                </div>
            </div>
        </div>
    </div>

    <!-- Section 2: Meeting Summary Analytics -->
    <div class="row my-2">
        <div class="col-12">
            <div class="card" style="border: 1px solid #dee2e6; border-radius: 4px;">
                <div class="card-header" style="background: #f8f9fa; border-bottom: 1px solid #dee2e6; padding: 12px 16px;">
                    <h5 class="mb-0" style="font-weight: 600; font-size: 1rem; color: #495057;">
                        <i class="mdi mdi-calendar-clock me-2"></i>Meeting Summary Analytics
                    </h5>
                </div>
                <div class="card-body" style="padding: 16px;">
                    @include('dashboard.sections.meetings')
                </div>
            </div>
        </div>
    </div>

    <!-- Section 3: TA/DA Bills Analytics -->
    <div class="row my-2">
        <div class="col-12">
            <div class="card" style="border: 1px solid #dee2e6; border-radius: 4px;">
                <div class="card-header" style="background: #f8f9fa; border-bottom: 1px solid #dee2e6; padding: 12px 16px;">
                    <h5 class="mb-0" style="font-weight: 600; font-size: 1rem; color: #495057;">
                        <i class="mdi mdi-cash-multiple me-2"></i>TA/DA Bills Analytics
                    </h5>
                </div>
                <div class="card-body" style="padding: 16px;">
                    @include('dashboard.sections.tada')
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Custom Date Range Modal -->
<div class="modal fade" id="customDateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Custom Date Range</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
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

@push('scripts')
<script>
let currentFilters = {
    project_id: {{ $selected_project_id ?? 'null' }},
    date_filter: '{{ $filters["date_filter"] ?? "this_month" }}',
    start_date: null,
    end_date: null
};

document.addEventListener('DOMContentLoaded', function() {
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
});

function applyCustomDateFromModal() {
    const startDate = document.getElementById('modalStartDate').value;
    const endDate = document.getElementById('modalEndDate').value;
    
    if (startDate && endDate) {
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
    } else {
        alert('Please select both start and end dates');
    }
}

function exportDashboard() {
    const params = new URLSearchParams({
        project_id: currentFilters.project_id || '',
        date_filter: currentFilters.date_filter,
        start_date: currentFilters.start_date || '',
        end_date: currentFilters.end_date || ''
    });

    window.location.href = '{{ route("export.excel") }}?' + params.toString();
}
</script>
@endpush
