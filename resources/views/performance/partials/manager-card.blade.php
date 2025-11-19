{{-- Manager Performance Card --}}
<div class="performance-card">
    <div class="d-flex justify-content-between align-items-start mb-3">
        <div class="d-flex align-items-center gap-3">
            <img src="{{ $managerData['user']->image ?? asset('images/default-avatar.png') }}" 
                 alt="{{ $managerData['user']->name }}" 
                 class="rounded-circle avatar-small">
            <div>
                <h6 class="mb-0">{{ $managerData['user']->firstName }} {{ $managerData['user']->lastName }}</h6>
                <small class="text-muted">Project Manager</small>
            </div>
        </div>
        <div class="text-end">
            <span class="badge bg-{{ $managerData['metrics']['performance_percentage'] >= 80 ? 'success' : ($managerData['metrics']['performance_percentage'] >= 50 ? 'warning' : 'danger') }} fs-6">
                {{ $managerData['metrics']['performance_percentage'] }}%
            </span>
        </div>
    </div>

    {{-- Progress Bar --}}
    <div class="progress mb-3" style="height: 10px;">
        <div class="progress-bar bg-{{ $managerData['metrics']['performance_percentage'] >= 80 ? 'success' : ($managerData['metrics']['performance_percentage'] >= 50 ? 'warning' : 'danger') }}" 
             style="width: {{ $managerData['metrics']['performance_percentage'] }}%"></div>
    </div>

    {{-- Metrics Grid --}}
    <div class="row g-2 mb-3">
        @if($isStreetlight)
            <div class="col-6 col-md-3">
                <div class="metric-badge w-100">
                    <span class="icon">📍</span>
                    <div>
                        <div class="small text-muted">Total Poles</div>
                        <div class="fw-bold">{{ $managerData['metrics']['total_poles'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="metric-badge w-100">
                    <span class="icon">🔍</span>
                    <div>
                        <div class="small text-muted">Surveyed</div>
                        <div class="fw-bold">{{ $managerData['metrics']['surveyed_poles'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="metric-badge w-100">
                    <span class="icon">💡</span>
                    <div>
                        <div class="small text-muted">Installed</div>
                        <div class="fw-bold">{{ $managerData['metrics']['installed_poles'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="metric-badge w-100">
                    <span class="icon">⏰</span>
                    <div>
                        <div class="small text-muted">Backlog</div>
                        <div class="fw-bold text-{{ $managerData['metrics']['backlog_tasks'] > 0 ? 'danger' : 'success' }}">
                            {{ $managerData['metrics']['backlog_tasks'] }}
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="col-6 col-md-3">
                <div class="metric-badge w-100">
                    <span class="icon">📋</span>
                    <div>
                        <div class="small text-muted">Total Tasks</div>
                        <div class="fw-bold">{{ $managerData['metrics']['total_tasks'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="metric-badge w-100">
                    <span class="icon">✅</span>
                    <div>
                        <div class="small text-muted">Completed</div>
                        <div class="fw-bold text-success">{{ $managerData['metrics']['completed_tasks'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="metric-badge w-100">
                    <span class="icon">⏳</span>
                    <div>
                        <div class="small text-muted">Pending</div>
                        <div class="fw-bold text-warning">{{ $managerData['metrics']['pending_tasks'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="metric-badge w-100">
                    <span class="icon">⏰</span>
                    <div>
                        <div class="small text-muted">Backlog</div>
                        <div class="fw-bold text-{{ $managerData['metrics']['backlog_tasks'] > 0 ? 'danger' : 'success' }}">
                            {{ $managerData['metrics']['backlog_tasks'] }}
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Subordinates Section --}}
    @if(count($managerData['subordinates']['engineers']) > 0 || count($managerData['subordinates']['vendors']) > 0)
    <div class="border-top pt-3">
        <div class="collapse-toggle d-flex justify-content-between align-items-center" 
             data-toggle="collapse" 
             data-target="#subordinates-{{ $managerData['user']->id }}">
            <h6 class="mb-0">View Team ({{ count($managerData['subordinates']['engineers']) }} Engineers, {{ count($managerData['subordinates']['vendors']) }} Vendors)</h6>
            <i class="bi bi-chevron-down"></i>
        </div>
        
        <div class="collapse" id="subordinates-{{ $managerData['user']->id }}">
            <div class="nested-section mt-3">
                {{-- Engineers --}}
                @if(count($managerData['subordinates']['engineers']) > 0)
                <h6 class="text-primary mb-3">👷 Site Engineers</h6>
                <div class="row">
                    @foreach($managerData['subordinates']['engineers'] as $engineerData)
                        <div class="col-md-6 mb-3">
                            @include('performance.partials.engineer-card', ['engineerData' => $engineerData, 'isStreetlight' => $isStreetlight, 'isNested' => true])
                        </div>
                    @endforeach
                </div>
                @endif

                {{-- Vendors --}}
                @if(count($managerData['subordinates']['vendors']) > 0)
                <h6 class="text-success mb-3 mt-3">🔧 Vendors</h6>
                <div class="row">
                    @foreach($managerData['subordinates']['vendors'] as $vendorData)
                        <div class="col-md-6 mb-3">
                            @include('performance.partials.vendor-card', ['vendorData' => $vendorData, 'isStreetlight' => $isStreetlight, 'isNested' => true])
                        </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>
