@extends('layouts.main')

@section('content')
<div class="content-wrapper p-3">
    {{-- Header Section --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">📊 Performance Overview</h3>
            <p class="text-muted mb-0">{{ $project->project_name ?? 'All Projects' }}</p>
        </div>
        
        <div class="d-flex gap-2">
            {{-- Date Filter --}}
            <form method="GET" action="{{ route('performance.index') }}" id="filterForm">
                <input type="hidden" name="project_id" value="{{ $projectId }}">
                <select class="form-select" name="date_filter" onchange="handleFilterChange(this.value)">
                    <option value="today" {{ request('date_filter', 'today') == 'today' ? 'selected' : '' }}>Today</option>
                    <option value="this_week" {{ request('date_filter') == 'this_week' ? 'selected' : '' }}>This Week</option>
                    <option value="this_month" {{ request('date_filter') == 'this_month' ? 'selected' : '' }}>This Month</option>
                    <option value="all_time" {{ request('date_filter') == 'all_time' ? 'selected' : '' }}>All Time</option>
                    <option value="custom" {{ request('date_filter') == 'custom' ? 'selected' : '' }}>Custom Range</option>
                </select>
            </form>

            {{-- Export Button --}}
            <button class="btn btn-outline-primary" onclick="window.print()">
                <i class="bi bi-download"></i> Export
            </button>
        </div>
    </div>

    {{-- Leaderboard Section (Top 3 Performers) --}}
    @if(isset($leaderboard) && count($leaderboard) > 0)
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title mb-4">🏆 Top Performers</h5>
            <div class="row">
                @foreach(array_slice($leaderboard, 0, 3) as $index => $performer)
                <div class="col-md-4">
                    <div class="leaderboard-card position-{{ $index + 1 }}">
                        <div class="text-center">
                            <div class="medal mb-2">
                                @if($index === 0) 🥇
                                @elseif($index === 1) 🥈
                                @elseif($index === 2) 🥉
                                @endif
                            </div>
                            <img src="{{ $performer['user']->image ?? asset('images/default-avatar.png') }}" 
                                 alt="{{ $performer['user']->name }}" 
                                 class="rounded-circle mb-2"
                                 style="width: 60px; height: 60px; object-fit: cover;">
                            <h6 class="mb-1">{{ $performer['user']->firstName }} {{ $performer['user']->lastName }}</h6>
                            <p class="text-muted small mb-2">
                                @if($performer['user']->role == 2) Project Manager
                                @elseif($performer['user']->role == 1) Site Engineer
                                @elseif($performer['user']->role == 3) Vendor
                                @endif
                            </p>
                            <div class="progress mb-2" style="height: 8px;">
                                <div class="progress-bar bg-{{ $index === 0 ? 'success' : ($index === 1 ? 'info' : 'warning') }}" 
                                     style="width: {{ $performer['metrics']['performance_percentage'] }}%"></div>
                            </div>
                            <span class="badge bg-{{ $performer['metrics']['performance_percentage'] >= 80 ? 'success' : ($performer['metrics']['performance_percentage'] >= 50 ? 'warning' : 'danger') }}">
                                {{ $performer['metrics']['performance_percentage'] }}%
                            </span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- Hierarchical Performance Display --}}
    @if($userRole == 0)
        {{-- Admin View: Shows all Project Managers --}}
        <h5 class="mb-3">📋 Project Managers Performance</h5>
        @forelse($performanceData as $managerData)
            @include('performance.partials.manager-card', ['managerData' => $managerData, 'isStreetlight' => $isStreetlight])
        @empty
            <div class="alert alert-info">No project managers found for this project.</div>
        @endforelse

    @elseif($userRole == 2)
        {{-- Project Manager View: Shows Engineers and Vendors --}}
        <div class="row">
            <div class="col-md-6">
                <h5 class="mb-3">👷 Site Engineers</h5>
                @forelse($performanceData['engineers'] ?? [] as $engineerData)
                    @include('performance.partials.engineer-card', ['engineerData' => $engineerData, 'isStreetlight' => $isStreetlight])
                @empty
                    <div class="alert alert-info">No site engineers assigned.</div>
                @endforelse
            </div>
            <div class="col-md-6">
                <h5 class="mb-3">🔧 Vendors</h5>
                @forelse($performanceData['vendors'] ?? [] as $vendorData)
                    @include('performance.partials.vendor-card', ['vendorData' => $vendorData, 'isStreetlight' => $isStreetlight])
                @empty
                    <div class="alert alert-info">No vendors assigned.</div>
                @endforelse
            </div>
        </div>

    @elseif($userRole == 1)
        {{-- Site Engineer View: Shows Vendors --}}
        <h5 class="mb-3">🔧 Vendors</h5>
        @forelse($performanceData['vendors'] ?? [] as $vendorData)
            @include('performance.partials.vendor-card', ['vendorData' => $vendorData, 'isStreetlight' => $isStreetlight])
        @empty
            <div class="alert alert-info">No vendors assigned.</div>
        @endforelse
    @endif
</div>

{{-- Custom Date Range Modal --}}
<div class="modal fade" id="customDateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Select Date Range</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="GET" action="{{ route('performance.index') }}">
                <div class="modal-body">
                    <input type="hidden" name="project_id" value="{{ $projectId }}">
                    <input type="hidden" name="date_filter" value="custom">
                    <div class="mb-3">
                        <label class="form-label">Start Date</label>
                        <input type="date" class="form-control" name="start_date" 
                               value="{{ request('start_date') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">End Date</label>
                        <input type="date" class="form-control" name="end_date" 
                               value="{{ request('end_date') }}" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Apply</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function handleFilterChange(value) {
    if (value === 'custom') {
        const modal = new bootstrap.Modal(document.getElementById('customDateModal'));
        modal.show();
    } else {
        document.getElementById('filterForm').submit();
    }
}

// Collapse/Expand functionality
document.addEventListener('DOMContentLoaded', function() {
    const toggleButtons = document.querySelectorAll('[data-toggle="collapse"]');
    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const target = document.querySelector(this.getAttribute('data-target'));
            if (target) {
                target.classList.toggle('show');
                const icon = this.querySelector('i');
                if (icon) {
                    icon.classList.toggle('bi-chevron-down');
                    icon.classList.toggle('bi-chevron-up');
                }
            }
        });
    });
});
</script>
@endpush

@push('styles')
<style>
.leaderboard-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 1.5rem;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.leaderboard-card.position-1 {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.leaderboard-card.position-2 {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.leaderboard-card.position-3 {
    background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
}

.leaderboard-card:hover {
    transform: translateY(-5px);
}

.medal {
    font-size: 2.5rem;
}

.performance-card {
    background: white;
    border: 1px solid #e3e6f0;
    border-radius: 10px;
    padding: 1.25rem;
    margin-bottom: 1rem;
    transition: all 0.3s ease;
}

.performance-card:hover {
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    border-color: #4e73df;
}

.metric-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: #f8f9fc;
    border-radius: 8px;
    margin: 0.25rem;
    font-size: 0.875rem;
}

.metric-badge .icon {
    font-size: 1.25rem;
}

.nested-section {
    background: #f8f9fc;
    border-left: 3px solid #4e73df;
    padding: 1rem;
    margin-top: 1rem;
    border-radius: 0 8px 8px 0;
}

.collapse-toggle {
    cursor: pointer;
    user-select: none;
}

.collapse-toggle:hover {
    opacity: 0.8;
}

.avatar-small {
    width: 40px;
    height: 40px;
    object-fit: cover;
}

@media print {
    .btn, .form-select, .modal {
        display: none !important;
    }
}
</style>
@endpush
