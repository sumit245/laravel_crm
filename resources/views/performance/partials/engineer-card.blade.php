{{-- Engineer Performance Card --}}
<div class="performance-card {{ isset($isNested) && $isNested ? 'bg-white' : '' }}">
    <div class="d-flex justify-content-between align-items-start mb-2">
        <div class="d-flex align-items-center gap-2">
            <img src="{{ $engineerData['user']->image ?? asset('images/default-avatar.png') }}" 
                 alt="{{ $engineerData['user']->name }}" 
                 class="rounded-circle" 
                 style="width: 35px; height: 35px; object-fit: cover;">
            <div>
                <h6 class="mb-0 small">{{ $engineerData['user']->firstName }} {{ $engineerData['user']->lastName }}</h6>
                <small class="text-muted" style="font-size: 0.75rem;">Site Engineer</small>
            </div>
        </div>
        <span class="badge bg-{{ $engineerData['metrics']['performance_percentage'] >= 80 ? 'success' : ($engineerData['metrics']['performance_percentage'] >= 50 ? 'warning' : 'danger') }}">
            {{ $engineerData['metrics']['performance_percentage'] }}%
        </span>
    </div>

    {{-- Progress Bar --}}
    <div class="progress mb-2" style="height: 6px;">
        <div class="progress-bar bg-{{ $engineerData['metrics']['performance_percentage'] >= 80 ? 'success' : ($engineerData['metrics']['performance_percentage'] >= 50 ? 'warning' : 'danger') }}" 
             style="width: {{ $engineerData['metrics']['performance_percentage'] }}%"></div>
    </div>

    {{-- Metrics --}}
    <div class="d-flex justify-content-between text-center" style="font-size: 0.8rem;">
        @if($isStreetlight)
            <div>
                <div class="text-muted">Poles</div>
                <div class="fw-bold">{{ $engineerData['metrics']['total_poles'] }}</div>
            </div>
            <div>
                <div class="text-muted">Surveyed</div>
                <div class="fw-bold text-info">{{ $engineerData['metrics']['surveyed_poles'] }}</div>
            </div>
            <div>
                <div class="text-muted">Installed</div>
                <div class="fw-bold text-success">{{ $engineerData['metrics']['installed_poles'] }}</div>
            </div>
            <div>
                <div class="text-muted">Backlog</div>
                <div class="fw-bold text-{{ $engineerData['metrics']['backlog_tasks'] > 0 ? 'danger' : 'muted' }}">
                    {{ $engineerData['metrics']['backlog_tasks'] }}
                </div>
            </div>
        @else
            <div>
                <div class="text-muted">Tasks</div>
                <div class="fw-bold">{{ $engineerData['metrics']['total_tasks'] }}</div>
            </div>
            <div>
                <div class="text-muted">Done</div>
                <div class="fw-bold text-success">{{ $engineerData['metrics']['completed_tasks'] }}</div>
            </div>
            <div>
                <div class="text-muted">Pending</div>
                <div class="fw-bold text-warning">{{ $engineerData['metrics']['pending_tasks'] }}</div>
            </div>
            <div>
                <div class="text-muted">Backlog</div>
                <div class="fw-bold text-{{ $engineerData['metrics']['backlog_tasks'] > 0 ? 'danger' : 'muted' }}">
                    {{ $engineerData['metrics']['backlog_tasks'] }}
                </div>
            </div>
        @endif
    </div>

    {{-- Vendors under this engineer --}}
    @if(isset($engineerData['vendors']) && count($engineerData['vendors']) > 0)
    <div class="border-top mt-2 pt-2">
        <div class="collapse-toggle d-flex justify-content-between align-items-center" 
             data-toggle="collapse" 
             data-target="#engineer-vendors-{{ $engineerData['user']->id }}"
             style="font-size: 0.85rem;">
            <span class="text-muted">Vendors ({{ count($engineerData['vendors']) }})</span>
            <i class="bi bi-chevron-down small"></i>
        </div>
        
        <div class="collapse" id="engineer-vendors-{{ $engineerData['user']->id }}">
            <div class="mt-2">
                @foreach($engineerData['vendors'] as $vendorData)
                    <div class="mb-2 p-2 bg-light rounded" style="font-size: 0.8rem;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ $vendorData['user']->image ?? asset('images/default-avatar.png') }}" 
                                     alt="{{ $vendorData['user']->name }}" 
                                     class="rounded-circle" 
                                     style="width: 25px; height: 25px; object-fit: cover;">
                                <div>
                                    <div class="fw-bold small">{{ $vendorData['user']->firstName }} {{ $vendorData['user']->lastName }}</div>
                                    @if($isStreetlight)
                                        <small class="text-muted">{{ $vendorData['metrics']['installed_poles'] }}/{{ $vendorData['metrics']['total_poles'] }} poles</small>
                                    @else
                                        <small class="text-muted">{{ $vendorData['metrics']['completed_tasks'] }}/{{ $vendorData['metrics']['total_tasks'] }} tasks</small>
                                    @endif
                                </div>
                            </div>
                            <span class="badge bg-{{ $vendorData['metrics']['performance_percentage'] >= 80 ? 'success' : ($vendorData['metrics']['performance_percentage'] >= 50 ? 'warning' : 'danger') }} badge-sm">
                                {{ $vendorData['metrics']['performance_percentage'] }}%
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>
