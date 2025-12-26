<div class="dashboard-section">
    <!-- District-wise Performance by Project Manager -->
    <div class="mb-4">
        <h6 class="mb-3" style="font-weight: 600; color: #495057; font-size: 0.95rem;">
            <i class="mdi mdi-map-marker-multiple me-2"></i>District-wise Performance by Project Manager
        </h6>
        <div id="districtPerformance" class="row g-3">
            @if(isset($performance_analytics['district_performance']) && count($performance_analytics['district_performance']) > 0)
                @foreach($performance_analytics['district_performance'] as $pm)
                    <div class="col-md-4">
                        <div class="card shadow-sm" style="border: 1px solid #e3e6f0; border-radius: 8px; background: white; transition: all 0.3s ease;">
                            <div class="card-body" style="padding: 20px;">
                                <!-- Header -->
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <h5 class="mb-1" style="font-weight: 600; color: #212529; font-size: 1rem; margin: 0;">
                                            {{ $pm['pm_name'] }}
                                        </h5>
                                        @if($pm['primary_district'])
                                            <span class="badge bg-primary" style="font-size: 0.75rem; padding: 4px 8px; margin-top: 4px;">
                                                <i class="mdi mdi-map-marker me-1"></i>{{ $pm['primary_district'] }}
                                            </span>
                                        @endif
                                    </div>
                                    <a href="{{ route('staff.show', $pm['pm_id']) }}" class="btn btn-sm btn-outline-primary" style="font-size: 0.75rem; padding: 4px 10px;">
                                        <i class="mdi mdi-eye me-1"></i>View
                                    </a>
                                </div>

                                <!-- Key Metrics Grid -->
                                <div class="row g-2 mb-3">
                                    <div class="col-12">
                                        <div class="metric-item" style="background: #f8f9fa; border-radius: 6px; padding: 12px;">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span style="font-size: 0.8rem; color: #6c757d; font-weight: 500;">
                                                    <i class="mdi mdi-pole me-1"></i>Total Poles
                                                </span>
                                                <strong style="font-size: 1.1rem; color: #212529;">{{ number_format($pm['total_poles']) }}</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Progress Metrics -->
                                <div class="progress-metrics mb-3">
                                    <!-- Surveyed Progress -->
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span style="font-size: 0.85rem; color: #495057; font-weight: 500;">
                                                <i class="mdi mdi-clipboard-check-outline me-1 text-info"></i>Surveyed
                                            </span>
                                            <div class="text-end">
                                                <strong style="font-size: 0.9rem; color: #212529;">{{ number_format($pm['surveyed_poles']) }}</strong>
                                                <span class="badge bg-info ms-2" style="font-size: 0.7rem; padding: 2px 6px;">
                                                    {{ number_format($pm['surveyed_progress'], 1) }}%
                                                </span>
                                            </div>
                                        </div>
                                        <div class="progress" style="height: 6px; border-radius: 3px; background: #e9ecef;">
                                            <div class="progress-bar bg-info" role="progressbar" 
                                                 style="width: {{ min($pm['surveyed_progress'], 100) }}%"
                                                 aria-valuenow="{{ $pm['surveyed_progress'] }}" 
                                                 aria-valuemin="0" aria-valuemax="100">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Installed Progress -->
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span style="font-size: 0.85rem; color: #495057; font-weight: 500;">
                                                <i class="mdi mdi-check-circle-outline me-1 text-success"></i>Installed
                                            </span>
                                            <div class="text-end">
                                                <strong style="font-size: 0.9rem; color: #212529;">{{ number_format($pm['installed_poles']) }}</strong>
                                                <span class="badge bg-success ms-2" style="font-size: 0.7rem; padding: 2px 6px;">
                                                    {{ number_format($pm['installed_progress'], 1) }}%
                                                </span>
                                            </div>
                                        </div>
                                        <div class="progress" style="height: 6px; border-radius: 3px; background: #e9ecef;">
                                            <div class="progress-bar bg-success" role="progressbar" 
                                                 style="width: {{ min($pm['installed_progress'], 100) }}%"
                                                 aria-valuenow="{{ $pm['installed_progress'] }}" 
                                                 aria-valuemin="0" aria-valuemax="100">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Overall Progress -->
                                <div class="overall-progress" style="border-top: 2px solid #e9ecef; padding-top: 12px;">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span style="font-size: 0.85rem; color: #495057; font-weight: 600;">
                                            <i class="mdi mdi-chart-line me-1"></i>Overall Progress
                                        </span>
                                        <strong style="font-size: 1.1rem; color: {{ $pm['overall_progress'] >= 80 ? '#28a745' : ($pm['overall_progress'] >= 50 ? '#ffc107' : '#dc3545') }};">
                                            {{ number_format($pm['overall_progress'], 1) }}%
                                        </strong>
                                    </div>
                                    <div class="progress" style="height: 10px; border-radius: 5px; background: #e9ecef;">
                                        <div class="progress-bar {{ $pm['overall_progress'] >= 80 ? 'bg-success' : ($pm['overall_progress'] >= 50 ? 'bg-warning' : 'bg-danger') }}" 
                                             role="progressbar" 
                                             style="width: {{ min($pm['overall_progress'], 100) }}%"
                                             aria-valuenow="{{ $pm['overall_progress'] }}" 
                                             aria-valuemin="0" aria-valuemax="100">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="col-12">
                    <div class="text-center text-muted py-5">
                        <i class="mdi mdi-information-outline" style="font-size: 3rem; opacity: 0.3;"></i>
                        <p class="mt-3 mb-0" style="font-size: 0.9rem;">No district performance data available.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Competitive Leaderboard -->
    @if(isset($performance_analytics['leaderboard']) && count($performance_analytics['leaderboard']) > 0)
    <div class="mb-4">
        <h6 class="mb-3" style="font-weight: 600; color: #495057; font-size: 0.95rem;">
            <i class="mdi mdi-trophy-outline me-2"></i>Competitive Leaderboard
        </h6>
        <div class="table-responsive">
            <table class="table table-striped table-hover" style="margin-bottom: 0;">
                <thead style="background: #f8f9fa;">
                    <tr>
                        <th style="font-weight: 600; font-size: 0.85rem; color: #495057; padding: 10px 12px;">Rank</th>
                        <th style="font-weight: 600; font-size: 0.85rem; color: #495057; padding: 10px 12px;">PM Name</th>
                        <th style="font-weight: 600; font-size: 0.85rem; color: #495057; padding: 10px 12px;">Installed Poles</th>
                        <th style="font-weight: 600; font-size: 0.85rem; color: #495057; padding: 10px 12px;">Total Poles</th>
                        <th style="font-weight: 600; font-size: 0.85rem; color: #495057; padding: 10px 12px;">Progress</th>
                        <th style="font-weight: 600; font-size: 0.85rem; color: #495057; padding: 10px 12px;">Trend</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($performance_analytics['leaderboard'] as $entry)
                        <tr>
                            <td style="padding: 10px 12px;">
                                @if($entry['rank'] == 1)
                                    <span class="badge bg-warning text-dark">🥇 #{{ $entry['rank'] }}</span>
                                @elseif($entry['rank'] == 2)
                                    <span class="badge bg-secondary">🥈 #{{ $entry['rank'] }}</span>
                                @elseif($entry['rank'] == 3)
                                    <span class="badge bg-info">🥉 #{{ $entry['rank'] }}</span>
                                @else
                                    <span class="badge bg-light text-dark">#{{ $entry['rank'] }}</span>
                                @endif
                            </td>
                            <td style="padding: 10px 12px; font-size: 0.9rem;">{{ $entry['pm_name'] }}</td>
                            <td style="padding: 10px 12px; font-size: 0.9rem; font-weight: 600; color: #28a745;">
                                {{ number_format($entry['installed_poles'] ?? 0) }}
                            </td>
                            <td style="padding: 10px 12px; font-size: 0.9rem;">
                                {{ number_format($entry['total_poles'] ?? 0) }}
                            </td>
                            <td style="padding: 10px 12px;">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress" style="height: 18px; width: 100px; flex: 1;">
                                        <div class="progress-bar {{ $entry['progress'] >= 80 ? 'bg-success' : ($entry['progress'] >= 50 ? 'bg-warning' : 'bg-danger') }}" 
                                             style="width: {{ min($entry['progress'], 100) }}%">
                                        </div>
                                    </div>
                                    <span style="font-size: 0.85rem; font-weight: 500;">{{ number_format($entry['progress'], 1) }}%</span>
                                </div>
                            </td>
                            <td style="padding: 10px 12px;">
                                @if($entry['trend'] == 'up')
                                    <span class="text-success" style="font-size: 0.85rem;">↑ {{ $entry['trend_percent'] }}%</span>
                                @elseif($entry['trend'] == 'down')
                                    <span class="text-danger" style="font-size: 0.85rem;">↓ {{ $entry['trend_percent'] }}%</span>
                                @else
                                    <span class="text-muted" style="font-size: 0.85rem;">→</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Top Performers -->
    <div class="mb-4">
        <h6 class="mb-3" style="font-weight: 600; color: #495057; font-size: 0.95rem;">
            <i class="mdi mdi-star-outline me-2"></i>Top Performers
        </h6>
        <div class="row">
            <div class="col-md-6">
                <div class="card" style="border: 1px solid #dee2e6; border-radius: 4px; background: white;">
                    <div class="card-header" style="background: #f8f9fa; border-bottom: 1px solid #dee2e6; padding: 10px 12px;">
                        <h6 class="mb-0" style="font-weight: 600; font-size: 0.9rem; color: #495057;">
                            <i class="mdi mdi-account-hard-hat me-2"></i>Top Engineers
                        </h6>
                    </div>
                    <div class="card-body" style="padding: 12px;">
                        <div id="topEngineers">
                            @if(isset($performance_analytics['top_performers']['engineers']) && count($performance_analytics['top_performers']['engineers']) > 0)
                                @foreach($performance_analytics['top_performers']['engineers'] as $index => $engineer)
                                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom" style="border-color: #e9ecef !important;">
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <span class="badge bg-primary" style="font-size: 0.7rem; padding: 2px 6px;">#{{ $index + 1 }}</span>
                                                <strong style="font-size: 0.9rem; color: #212529;">{{ $engineer['name'] }}</strong>
                                            </div>
                                            <div style="font-size: 0.8rem; color: #6c757d;">
                                                <span>Sites: <strong>{{ $engineer['sites'] }}</strong></span>
                                                <span class="ms-2">Poles: <strong>{{ $engineer['poles'] }}</strong></span>
                                                <span class="ms-2">Progress: <strong>{{ number_format($engineer['progress'], 1) }}%</strong></span>
                                            </div>
                                        </div>
                                        <a href="{{ route('staff.show', $engineer['id']) }}" class="btn btn-sm btn-outline-primary" style="font-size: 0.75rem; padding: 4px 8px; white-space: nowrap;">
                                            <i class="mdi mdi-eye me-1"></i>View
                                        </a>
                                    </div>
                                @endforeach
                            @else
                                <div class="text-center text-muted py-3">
                                    <i class="mdi mdi-information-outline" style="font-size: 1.5rem; opacity: 0.4;"></i>
                                    <p class="mt-2 mb-0" style="font-size: 0.85rem;">No engineer data available.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card" style="border: 1px solid #dee2e6; border-radius: 4px; background: white;">
                    <div class="card-header" style="background: #f8f9fa; border-bottom: 1px solid #dee2e6; padding: 10px 12px;">
                        <h6 class="mb-0" style="font-weight: 600; font-size: 0.9rem; color: #495057;">
                            <i class="mdi mdi-account-tie me-2"></i>Top Vendors
                        </h6>
                    </div>
                    <div class="card-body" style="padding: 12px;">
                        <div id="topVendors">
                            @if(isset($performance_analytics['top_performers']['vendors']) && count($performance_analytics['top_performers']['vendors']) > 0)
                                @foreach($performance_analytics['top_performers']['vendors'] as $index => $vendor)
                                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom" style="border-color: #e9ecef !important;">
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <span class="badge bg-success" style="font-size: 0.7rem; padding: 2px 6px;">#{{ $index + 1 }}</span>
                                                <strong style="font-size: 0.9rem; color: #212529;">{{ $vendor['name'] }}</strong>
                                            </div>
                                            <div style="font-size: 0.8rem; color: #6c757d;">
                                                <span>Poles: <strong>{{ $vendor['poles'] }}</strong></span>
                                                <span class="ms-2">Progress: <strong>{{ number_format($vendor['progress'], 1) }}%</strong></span>
                                            </div>
                                        </div>
                                        <a href="{{ route('staff.show', $vendor['id']) }}" class="btn btn-sm btn-outline-primary" style="font-size: 0.75rem; padding: 4px 8px; white-space: nowrap;">
                                            <i class="mdi mdi-eye me-1"></i>View
                                        </a>
                                    </div>
                                @endforeach
                            @else
                                <div class="text-center text-muted py-3">
                                    <i class="mdi mdi-information-outline" style="font-size: 1.5rem; opacity: 0.4;"></i>
                                    <p class="mt-2 mb-0" style="font-size: 0.85rem;">No vendor data available.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Unified Metrics -->
    <div class="mb-4">
        <h6 class="mb-3" style="font-weight: 600; color: #495057; font-size: 0.95rem;">
            <i class="mdi mdi-chart-box-outline me-2"></i>Unified Metrics (Streetlight + Rooftop)
        </h6>
        <div class="row g-3">
            <div class="col-md-4">
                <div class="card" style="border: 2px solid #007bff; border-radius: 8px; background: linear-gradient(135deg, #ffffff 0%, #f0f7ff 100%);">
                    <div class="card-body text-center" style="padding: 1.25rem 1.5rem;">
                        <h6 style="font-weight: 600; color: #007bff; margin-bottom: 12px;">Streetlight Projects</h6>
                        <div class="mb-2">
                            <small class="text-muted d-block">Poles</small>
                            <strong style="font-size: 1.1rem;">{{ number_format($performance_analytics['unified_metrics']['streetlight']['total_poles'] ?? 0) }}</strong>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted d-block">Surveyed</small>
                            <strong>{{ number_format($performance_analytics['unified_metrics']['streetlight']['surveyed_poles'] ?? 0) }}</strong>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted d-block">Installed</small>
                            <strong>{{ number_format($performance_analytics['unified_metrics']['streetlight']['installed_poles'] ?? 0) }}</strong>
                        </div>
                        <div class="progress mt-2" style="height: 8px; border-radius: 4px;">
                            <div class="progress-bar bg-primary" style="width: {{ $performance_analytics['unified_metrics']['streetlight']['progress'] ?? 0 }}%">
                            </div>
                        </div>
                        <small class="text-muted mt-1 d-block">{{ number_format($performance_analytics['unified_metrics']['streetlight']['progress'] ?? 0, 1) }}% Complete</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card" style="border: 2px solid #28a745; border-radius: 8px; background: linear-gradient(135deg, #ffffff 0%, #f0fdf4 100%);">
                    <div class="card-body text-center" style="padding: 1.25rem 1.5rem;">
                        <h6 style="font-weight: 600; color: #28a745; margin-bottom: 12px;">Rooftop Projects</h6>
                        <div class="mb-2">
                            <small class="text-muted d-block">Sites</small>
                            <strong style="font-size: 1.1rem;">{{ number_format($performance_analytics['unified_metrics']['rooftop']['total_sites'] ?? 0) }}</strong>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted d-block">Completed</small>
                            <strong>{{ number_format($performance_analytics['unified_metrics']['rooftop']['completed_sites'] ?? 0) }}</strong>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted d-block">In Progress</small>
                            <strong>{{ number_format($performance_analytics['unified_metrics']['rooftop']['in_progress_sites'] ?? 0) }}</strong>
                        </div>
                        <div class="progress mt-2" style="height: 8px; border-radius: 4px;">
                            <div class="progress-bar bg-success" style="width: {{ $performance_analytics['unified_metrics']['rooftop']['progress'] ?? 0 }}%">
                            </div>
                        </div>
                        <small class="text-muted mt-1 d-block">{{ number_format($performance_analytics['unified_metrics']['rooftop']['progress'] ?? 0, 1) }}% Complete</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card" style="border: 2px solid #17a2b8; border-radius: 8px; background: linear-gradient(135deg, #ffffff 0%, #e7f7f9 100%);">
                    <div class="card-body text-center" style="padding: 1.25rem 1.5rem;">
                        <h6 style="font-weight: 600; color: #17a2b8; margin-bottom: 12px;">Combined Metrics</h6>
                        <div class="mb-2">
                            <small class="text-muted d-block">Total</small>
                            <strong style="font-size: 1.1rem;">{{ number_format($performance_analytics['unified_metrics']['combined']['total'] ?? 0) }}</strong>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted d-block">Completed</small>
                            <strong>{{ number_format($performance_analytics['unified_metrics']['combined']['completed'] ?? 0) }}</strong>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted d-block">Progress</small>
                            <strong>{{ number_format($performance_analytics['unified_metrics']['combined']['progress'] ?? 0, 1) }}%</strong>
                        </div>
                        <div class="progress mt-2" style="height: 8px; border-radius: 4px;">
                            <div class="progress-bar bg-info" style="width: {{ $performance_analytics['unified_metrics']['combined']['progress'] ?? 0 }}%">
                            </div>
                        </div>
                        <small class="text-muted mt-1 d-block">{{ number_format($performance_analytics['unified_metrics']['combined']['progress'] ?? 0, 1) }}% Complete</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pole Installation Speed -->
    @if(isset($performance_analytics['pole_speed_metrics']) && count($performance_analytics['pole_speed_metrics']) > 0)
    <div class="mb-4">
        <h6 class="mb-3" style="font-weight: 600; color: #495057; font-size: 0.95rem;">
            <i class="mdi mdi-speedometer me-2"></i>Pole Installation Speed Analysis
        </h6>
        <div class="table-responsive">
            <table class="table table-striped table-hover" style="margin-bottom: 0;">
                <thead style="background: #f8f9fa;">
                    <tr>
                        <th style="font-weight: 600; font-size: 0.85rem; color: #495057; padding: 10px 12px;">Panchayat</th>
                        <th style="font-weight: 600; font-size: 0.85rem; color: #495057; padding: 10px 12px;">District</th>
                        <th style="font-weight: 600; font-size: 0.85rem; color: #495057; padding: 10px 12px;">Total Poles</th>
                        <th style="font-weight: 600; font-size: 0.85rem; color: #495057; padding: 10px 12px;">Installed</th>
                        <th style="font-weight: 600; font-size: 0.85rem; color: #495057; padding: 10px 12px;">Speed (poles/day)</th>
                        <th style="font-weight: 600; font-size: 0.85rem; color: #495057; padding: 10px 12px;">Status</th>
                        <th style="font-weight: 600; font-size: 0.85rem; color: #495057; padding: 10px 12px;">Trend</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($performance_analytics['pole_speed_metrics'] as $panchayat)
                        <tr>
                            <td style="padding: 10px 12px; font-size: 0.9rem;">{{ $panchayat['panchayat'] }}</td>
                            <td style="padding: 10px 12px; font-size: 0.9rem;">{{ $panchayat['district'] }}</td>
                            <td style="padding: 10px 12px; font-size: 0.9rem;">{{ $panchayat['total_poles'] }}</td>
                            <td style="padding: 10px 12px; font-size: 0.9rem;">{{ $panchayat['installed_poles'] }}</td>
                            <td style="padding: 10px 12px; font-size: 0.9rem;">{{ number_format($panchayat['speed'], 2) }}</td>
                            <td style="padding: 10px 12px;">
                                @if($panchayat['speed_status'] == 'fast')
                                    <span class="badge bg-success" style="font-size: 0.75rem;">Fast</span>
                                @elseif($panchayat['speed_status'] == 'medium')
                                    <span class="badge bg-warning text-dark" style="font-size: 0.75rem;">Medium</span>
                                @else
                                    <span class="badge bg-danger" style="font-size: 0.75rem;">Slow</span>
                                @endif
                            </td>
                            <td style="padding: 10px 12px;">
                                @if($panchayat['trend'] == 'up')
                                    <span class="text-success" style="font-size: 0.85rem;">↑ {{ $panchayat['trend_percent'] }}%</span>
                                @elseif($panchayat['trend'] == 'down')
                                    <span class="text-danger" style="font-size: 0.85rem;">↓ {{ $panchayat['trend_percent'] }}%</span>
                                @else
                                    <span class="text-muted" style="font-size: 0.85rem;">→</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
