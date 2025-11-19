{{-- Vendor Performance Card --}}
<div class="performance-card {{ isset($isNested) && $isNested ? 'bg-white' : '' }}">
    <div class="d-flex justify-content-between align-items-start mb-2">
        <div class="d-flex align-items-center gap-2">
            <img src="{{ $vendorData['user']->image ?? asset('images/default-avatar.png') }}" 
                 alt="{{ $vendorData['user']->name }}" 
                 class="rounded-circle" 
                 style="width: 35px; height: 35px; object-fit: cover;">
            <div>
                <h6 class="mb-0 small">{{ $vendorData['user']->firstName }} {{ $vendorData['user']->lastName }}</h6>
                <small class="text-muted" style="font-size: 0.75rem;">Vendor</small>
            </div>
        </div>
        <span class="badge bg-{{ $vendorData['metrics']['performance_percentage'] >= 80 ? 'success' : ($vendorData['metrics']['performance_percentage'] >= 50 ? 'warning' : 'danger') }}">
            {{ $vendorData['metrics']['performance_percentage'] }}%
        </span>
    </div>

    {{-- Progress Bar --}}
    <div class="progress mb-2" style="height: 6px;">
        <div class="progress-bar bg-{{ $vendorData['metrics']['performance_percentage'] >= 80 ? 'success' : ($vendorData['metrics']['performance_percentage'] >= 50 ? 'warning' : 'danger') }}" 
             style="width: {{ $vendorData['metrics']['performance_percentage'] }}%"></div>
    </div>

    {{-- Metrics --}}
    <div class="d-flex justify-content-between text-center" style="font-size: 0.8rem;">
        @if($isStreetlight)
            <div>
                <div class="text-muted">Poles</div>
                <div class="fw-bold">{{ $vendorData['metrics']['total_poles'] }}</div>
            </div>
            <div>
                <div class="text-muted">Surveyed</div>
                <div class="fw-bold text-info">{{ $vendorData['metrics']['surveyed_poles'] }}</div>
            </div>
            <div>
                <div class="text-muted">Installed</div>
                <div class="fw-bold text-success">{{ $vendorData['metrics']['installed_poles'] }}</div>
            </div>
            <div>
                <div class="text-muted">Backlog</div>
                <div class="fw-bold text-{{ $vendorData['metrics']['backlog_tasks'] > 0 ? 'danger' : 'muted' }}">
                    {{ $vendorData['metrics']['backlog_tasks'] }}
                </div>
            </div>
        @else
            <div>
                <div class="text-muted">Tasks</div>
                <div class="fw-bold">{{ $vendorData['metrics']['total_tasks'] }}</div>
            </div>
            <div>
                <div class="text-muted">Done</div>
                <div class="fw-bold text-success">{{ $vendorData['metrics']['completed_tasks'] }}</div>
            </div>
            <div>
                <div class="text-muted">Pending</div>
                <div class="fw-bold text-warning">{{ $vendorData['metrics']['pending_tasks'] }}</div>
            </div>
            <div>
                <div class="text-muted">Backlog</div>
                <div class="fw-bold text-{{ $vendorData['metrics']['backlog_tasks'] > 0 ? 'danger' : 'muted' }}">
                    {{ $vendorData['metrics']['backlog_tasks'] }}
                </div>
            </div>
        @endif
    </div>
</div>
