<div class="dashboard-section">
    <!-- District-wise Performance by Project Manager -->
    <div class="mb-4">
        <h6 class="mb-3" style="font-weight: 600; color: #495057; font-size: 0.95rem;">
            <i class="mdi mdi-map-marker-multiple me-2"></i>District-wise Performance by Project Manager
        </h6>
        <div id="districtPerformance" class="row g-3">
            @if(isset($performance_analytics['district_performance']) && count($performance_analytics['district_performance']) > 0)
                @foreach($performance_analytics['district_performance'] as $pm)
                    <div class="col-md-6 col-lg-6 col-xl-3">
                        <div class="card shadow-sm"
                            style="border: 1px solid #e3e6f0; border-radius: 8px; background: white; transition: all 0.3s ease;">
                            <div class="card-body" style="padding: 20px;">
                                <!-- Header -->
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <h5 class="mb-1" style="font-weight: 600; color: #212529; font-size: 1rem; margin: 0;">
                                            {{ $pm['pm_name'] }}
                                        </h5>
                                        @if($pm['primary_district'])
                                            <span class="badge bg-primary"
                                                style="font-size: 0.75rem; padding: 4px 8px; margin-top: 4px;">
                                                <i class="mdi mdi-map-marker me-1"></i>{{ $pm['primary_district'] }}
                                            </span>
                                        @endif
                                    </div>
                                    <a href="{{ route('staff.show', $pm['pm_id']) }}" class="btn btn-sm btn-outline-primary"
                                        style="font-size: 0.75rem; padding: 4px 10px;">
                                        <i class="mdi mdi-eye me-1"></i>View
                                    </a>
                                </div>

                                <!-- Key Metrics Grid -->
                                <div class="row g-2 mb-3">
                                    <div class="col-12">
                                        <div class="metric-item"
                                            style="background: #f8f9fa; border-radius: 6px; padding: 12px;">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span style="font-size: 0.8rem; color: #6c757d; font-weight: 500;">
                                                    <i class="mdi mdi-pole me-1"></i>Total Poles
                                                </span>
                                                <strong
                                                    style="font-size: 1.1rem; color: #212529;">{{ number_format($pm['total_poles']) }}</strong>
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
                                                <strong
                                                    style="font-size: 0.9rem; color: #212529;">{{ number_format($pm['surveyed_poles']) }}</strong>
                                                <span class="badge bg-info ms-2" style="font-size: 0.7rem; padding: 2px 6px;">
                                                    {{ number_format($pm['surveyed_progress'], 1) }}%
                                                </span>
                                            </div>
                                        </div>
                                        <div class="progress" style="height: 6px; border-radius: 3px; background: #e9ecef;">
                                            <div class="progress-bar bg-info" role="progressbar"
                                                style="width: {{ min($pm['surveyed_progress'], 100) }}%"
                                                aria-label="Surveyed progress for {{ $pm['pm_name'] }}"
                                                aria-valuenow="{{ $pm['surveyed_progress'] }}" aria-valuemin="0"
                                                aria-valuemax="100">
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
                                                <strong
                                                    style="font-size: 0.9rem; color: #212529;">{{ number_format($pm['installed_poles']) }}</strong>
                                                <span class="badge bg-success ms-2"
                                                    style="font-size: 0.7rem; padding: 2px 6px;">
                                                    {{ number_format($pm['installed_progress'], 1) }}%
                                                </span>
                                            </div>
                                        </div>
                                        <div class="progress" style="height: 6px; border-radius: 3px; background: #e9ecef;">
                                            <div class="progress-bar bg-success" role="progressbar"
                                                style="width: {{ min($pm['installed_progress'], 100) }}%"
                                                aria-label="Installed progress for {{ $pm['pm_name'] }}"
                                                aria-valuenow="{{ $pm['installed_progress'] }}" aria-valuemin="0"
                                                aria-valuemax="100">
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
                                        <strong
                                            style="font-size: 1.1rem; color: {{ $pm['overall_progress'] >= 80 ? '#28a745' : ($pm['overall_progress'] >= 50 ? '#ffc107' : '#dc3545') }};">
                                            {{ number_format($pm['overall_progress'], 1) }}%
                                        </strong>
                                    </div>
                                    <div class="progress" style="height: 10px; border-radius: 5px; background: #e9ecef;">
                                        <div class="progress-bar {{ $pm['overall_progress'] >= 80 ? 'bg-success' : ($pm['overall_progress'] >= 50 ? 'bg-warning' : 'bg-danger') }}"
                                            role="progressbar" style="width: {{ min($pm['overall_progress'], 100) }}%"
                                            aria-label="Overall progress for {{ $pm['pm_name'] }}"
                                            aria-valuenow="{{ $pm['overall_progress'] }}" aria-valuemin="0" aria-valuemax="100">
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
                            <th style="font-weight: 600; font-size: 0.85rem; color: #495057; padding: 10px 12px;">PM Name
                            </th>
                            <th style="font-weight: 600; font-size: 0.85rem; color: #495057; padding: 10px 12px;">Installed
                                Poles</th>
                            <th style="font-weight: 600; font-size: 0.85rem; color: #495057; padding: 10px 12px;">Total
                                Poles</th>
                            <th style="font-weight: 600; font-size: 0.85rem; color: #495057; padding: 10px 12px;">Progress
                            </th>
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
                                                role="progressbar"
                                                style="width: {{ min($entry['progress'], 100) }}%"
                                                aria-label="Leaderboard progress for {{ $entry['pm_name'] }}"
                                                aria-valuenow="{{ $entry['progress'] }}" aria-valuemin="0" aria-valuemax="100">
                                            </div>
                                        </div>
                                        <span
                                            style="font-size: 0.85rem; font-weight: 500;">{{ number_format($entry['progress'], 1) }}%</span>
                                    </div>
                                </td>
                                <td style="padding: 10px 12px;">
                                    @if($entry['trend'] == 'up')
                                        <span class="text-success" style="font-size: 0.85rem;">↑
                                            {{ $entry['trend_percent'] }}%</span>
                                    @elseif($entry['trend'] == 'down')
                                        <span class="text-danger" style="font-size: 0.85rem;">↓
                                            {{ $entry['trend_percent'] }}%</span>
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
                    <div class="card-header"
                        style="background: #f8f9fa; border-bottom: 1px solid #dee2e6; padding: 10px 12px;">
                        <h6 class="mb-0" style="font-weight: 600; font-size: 0.9rem; color: #495057;">
                            <i class="mdi mdi-account-hard-hat me-2"></i>Top Engineers
                        </h6>
                    </div>
                    <div class="card-body" style="padding: 12px;">
                        <div id="topEngineers">
                            @if(isset($performance_analytics['top_performers']['engineers']) && count($performance_analytics['top_performers']['engineers']) > 0)
                                @foreach($performance_analytics['top_performers']['engineers'] as $index => $engineer)
                                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom"
                                        style="border-color: #e9ecef !important;">
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <span class="badge bg-primary"
                                                    style="font-size: 0.7rem; padding: 2px 6px;">#{{ $index + 1 }}</span>
                                                <strong
                                                    style="font-size: 0.9rem; color: #212529;">{{ $engineer['name'] }}</strong>
                                            </div>
                                            <div style="font-size: 0.8rem; color: #6c757d;">
                                                <span>Sites: <strong>{{ $engineer['sites'] }}</strong></span>
                                                <span class="ms-2">Poles: <strong>{{ $engineer['poles'] }}</strong></span>
                                                <span class="ms-2">Progress:
                                                    <strong>{{ number_format($engineer['progress'], 1) }}%</strong></span>
                                            </div>
                                        </div>
                                        <a href="{{ route('staff.show', $engineer['id']) }}"
                                            class="btn btn-sm btn-outline-primary"
                                            style="font-size: 0.75rem; padding: 4px 8px; white-space: nowrap;">
                                            <i class="mdi mdi-eye me-1"></i>View
                                        </a>
                                    </div>
                                @endforeach
                            @else
                                <div class="text-center text-muted py-3">
                                    <i class="mdi mdi-account-search-outline" style="font-size: 1.5rem; opacity: 0.5;"></i>
                                    <p class="mt-2 mb-1" style="font-size: 0.85rem;">No engineers logged survey or installation updates this month.</p>
                                    <a href="{{ route('staff.index') }}" class="btn btn-sm btn-link p-0 text-decoration-none" style="font-size: 0.8rem;">
                                        View staff <i class="mdi mdi-arrow-right"></i>
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card" style="border: 1px solid #dee2e6; border-radius: 4px; background: white;">
                    <div class="card-header"
                        style="background: #f8f9fa; border-bottom: 1px solid #dee2e6; padding: 10px 12px;">
                        <h6 class="mb-0" style="font-weight: 600; font-size: 0.9rem; color: #495057;">
                            <i class="mdi mdi-account-tie me-2"></i>Top Vendors
                        </h6>
                    </div>
                    <div class="card-body" style="padding: 12px;">
                        <div id="topVendors">
                            @if(isset($performance_analytics['top_performers']['vendors']) && count($performance_analytics['top_performers']['vendors']) > 0)
                                @foreach($performance_analytics['top_performers']['vendors'] as $index => $vendor)
                                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom"
                                        style="border-color: #e9ecef !important;">
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <span class="badge bg-success"
                                                    style="font-size: 0.7rem; padding: 2px 6px;">#{{ $index + 1 }}</span>
                                                <strong
                                                    style="font-size: 0.9rem; color: #212529;">{{ $vendor['name'] }}</strong>
                                            </div>
                                            <div style="font-size: 0.8rem; color: #6c757d;">
                                                <span>Poles: <strong>{{ $vendor['poles'] }}</strong></span>
                                                <span class="ms-2">Progress:
                                                    <strong>{{ number_format($vendor['progress'], 1) }}%</strong></span>
                                            </div>
                                        </div>
                                        <a href="{{ route('staff.show', $vendor['id']) }}"
                                            class="btn btn-sm btn-outline-primary"
                                            style="font-size: 0.75rem; padding: 4px 8px; white-space: nowrap;">
                                            <i class="mdi mdi-eye me-1"></i>View
                                        </a>
                                    </div>
                                @endforeach
                            @else
                                <div class="text-center text-muted py-3">
                                    <i class="mdi mdi-account-tie-outline" style="font-size: 1.5rem; opacity: 0.5;"></i>
                                    <p class="mt-2 mb-1" style="font-size: 0.85rem;">No vendor progress has been reported for this month yet.</p>
                                    <a href="{{ route('uservendors.index') }}" class="btn btn-sm btn-link p-0 text-decoration-none" style="font-size: 0.8rem;">
                                        View vendors <i class="mdi mdi-arrow-right"></i>
                                    </a>
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
        @php
            $rooftopSiteCount = (int) ($performance_analytics['unified_metrics']['rooftop']['total_sites'] ?? 0);
            $showCombinedMetrics = $rooftopSiteCount > 0;
            $unifiedMetricColumnClass = $showCombinedMetrics ? 'col-md-4' : 'col-md-6';
        @endphp
        <div class="row g-3">
            <div class="{{ $unifiedMetricColumnClass }}">
                <div class="card"
                    style="border: 2px solid #007bff; border-radius: 8px; background: linear-gradient(135deg, #ffffff 0%, #f0f7ff 100%);">
                    <div class="card-body text-center" style="padding: 1.25rem 1.5rem;">
                        <h6 style="font-weight: 600; color: #007bff; margin-bottom: 12px;">Streetlight Projects</h6>
                        <div class="mb-2">
                            <small class="text-muted d-block">Poles</small>
                            <strong
                                style="font-size: 1.1rem;">{{ number_format($performance_analytics['unified_metrics']['streetlight']['total_poles'] ?? 0) }}</strong>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted d-block">Surveyed</small>
                            <strong>{{ number_format($performance_analytics['unified_metrics']['streetlight']['surveyed_poles'] ?? 0) }}</strong>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted d-block">Installed</small>
                            <strong>{{ number_format($performance_analytics['unified_metrics']['streetlight']['installed_poles'] ?? 0) }}</strong>
                        </div>
                        @if (!empty($selected_project_id))
                            <a href="{{ route('projects.show', $selected_project_id) }}#installed-poles"
                                class="btn btn-sm btn-outline-success mt-2">
                                <i class="mdi mdi-lightning-bolt me-1" aria-hidden="true"></i>View installed poles
                            </a>
                        @endif
                        <div class="progress mt-2" style="height: 8px; border-radius: 4px;">
                            <div class="progress-bar bg-primary" role="progressbar"
                                style="width: {{ $performance_analytics['unified_metrics']['streetlight']['progress'] ?? 0 }}%"
                                aria-label="Streetlight project completion"
                                aria-valuenow="{{ $performance_analytics['unified_metrics']['streetlight']['progress'] ?? 0 }}"
                                aria-valuemin="0" aria-valuemax="100">
                            </div>
                        </div>
                        <small
                            class="text-muted mt-1 d-block">{{ number_format($performance_analytics['unified_metrics']['streetlight']['progress'] ?? 0, 1) }}%
                            Complete</small>
                    </div>
                </div>
            </div>
            <div class="{{ $unifiedMetricColumnClass }}">
                <div class="card"
                    style="border: 2px solid #28a745; border-radius: 8px; background: linear-gradient(135deg, #ffffff 0%, #f0fdf4 100%);">
                    <div class="card-body text-center" style="padding: 1.25rem 1.5rem;">
                        <h6 style="font-weight: 600; color: #28a745; margin-bottom: 12px;">Rooftop Projects</h6>
                        <div class="mb-2">
                            <small class="text-muted d-block">Sites</small>
                            <strong
                                style="font-size: 1.1rem;">{{ number_format($performance_analytics['unified_metrics']['rooftop']['total_sites'] ?? 0) }}</strong>
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
                            <div class="progress-bar bg-success" role="progressbar"
                                style="width: {{ $performance_analytics['unified_metrics']['rooftop']['progress'] ?? 0 }}%"
                                aria-label="Rooftop project completion"
                                aria-valuenow="{{ $performance_analytics['unified_metrics']['rooftop']['progress'] ?? 0 }}"
                                aria-valuemin="0" aria-valuemax="100">
                            </div>
                        </div>
                        <small
                            class="text-muted mt-1 d-block">{{ number_format($performance_analytics['unified_metrics']['rooftop']['progress'] ?? 0, 1) }}%
                            Complete</small>
                    </div>
                </div>
            </div>
            @if($showCombinedMetrics)
            <div class="col-md-4">
                <div class="card"
                    style="border: 2px solid #17a2b8; border-radius: 8px; background: linear-gradient(135deg, #ffffff 0%, #e7f7f9 100%);">
                    <div class="card-body text-center" style="padding: 1.25rem 1.5rem;">
                        <h6 style="font-weight: 600; color: #17a2b8; margin-bottom: 12px;">Combined Metrics</h6>
                        <div class="mb-2">
                            <small class="text-muted d-block">Total</small>
                            <strong
                                style="font-size: 1.1rem;">{{ number_format($performance_analytics['unified_metrics']['combined']['total'] ?? 0) }}</strong>
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
                            <div class="progress-bar bg-info" role="progressbar"
                                style="width: {{ $performance_analytics['unified_metrics']['combined']['progress'] ?? 0 }}%"
                                aria-label="Combined project completion"
                                aria-valuenow="{{ $performance_analytics['unified_metrics']['combined']['progress'] ?? 0 }}"
                                aria-valuemin="0" aria-valuemax="100">
                            </div>
                        </div>
                        <small
                            class="text-muted mt-1 d-block">{{ number_format($performance_analytics['unified_metrics']['combined']['progress'] ?? 0, 1) }}%
                            Complete</small>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Pole Installation Speed -->
    @if(isset($performance_analytics['pole_speed_metrics']) && count($performance_analytics['pole_speed_metrics']) > 0)
        @php
            $poleSpeedMetrics = $performance_analytics['pole_speed_metrics'];
            $formatLocationName = function ($value) {
                $value = trim((string) $value);
                $value = preg_replace('/\s+/', ' ', $value);
                $value = strtolower($value);
                return $value === '' ? '' : ucwords($value);
            };
            $normalizeDistrictKey = function ($district) {
                $district = trim((string) $district);
                $district = preg_replace('/\s+/', ' ', $district);
                return strtolower($district);
            };
            $normalizeDistrictLabel = function ($district) use ($normalizeDistrictKey) {
                $normalized = $normalizeDistrictKey($district);
                return $normalized === '' ? '' : ucwords($normalized);
            };

            $poleSpeedDistrictMap = collect($poleSpeedMetrics)
                ->mapWithKeys(function ($item) use ($normalizeDistrictKey, $normalizeDistrictLabel) {
                    $key = $normalizeDistrictKey($item['district'] ?? '');
                    if ($key === '') {
                        return [];
                    }
                    return [$key => $normalizeDistrictLabel($item['district'] ?? '')];
                })
                ->sortKeys();
        @endphp
        <div class="mb-4" id="poleSpeedAnalysisSection">
            <h6 class="mb-3" style="font-weight: 600; color: #495057; font-size: 0.95rem;">
                <i class="mdi mdi-speedometer me-2"></i>Pole Installation Speed Analysis
            </h6>

            <div style="border: 1px solid #e9ecef; border-radius: 8px; background: #fff; padding: 12px;">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <span class="text-muted" style="font-size: 0.82rem; font-weight: 600;">District:</span>
                        <button type="button" class="btn btn-sm btn-primary pole-speed-district-chip active"
                            data-district="all" aria-pressed="true" style="font-size: 0.78rem; padding: 4px 12px; border-radius: 999px;">
                            All
                        </button>
                        @foreach($poleSpeedDistrictMap as $districtKey => $districtLabel)
                            <button type="button" class="btn btn-sm btn-outline-primary pole-speed-district-chip"
                                data-district="{{ $districtKey }}" aria-pressed="false" style="font-size: 0.78rem; padding: 4px 12px; border-radius: 999px;">
                                {{ $districtLabel }}
                            </button>
                        @endforeach
                    </div>

                    <div class="d-flex align-items-center gap-2 pole-speed-controls-row">
                        <div class="input-group input-group-sm pole-speed-search-group" style="width: 300px; max-width: 100%;">
                            <span class="input-group-text bg-white">
                                <i class="mdi mdi-magnify"></i>
                            </span>
                            <input type="text" id="poleSpeedSearchInput" class="form-control"
                                placeholder="Search panchayat or district" aria-label="Search panchayat or district"
                                style="font-size: 0.85rem;">
                        </div>
                        <button type="button" id="poleSpeedToggleBtn" class="btn btn-sm btn-outline-primary pole-speed-toggle-btn"
                            aria-controls="poleSpeedTableBody" aria-expanded="false"
                            style="font-size: 0.78rem; font-weight: 600; white-space: nowrap; min-width: 130px;">
                            Show all rows
                        </button>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <small id="poleSpeedSummaryText" class="text-muted" role="status" aria-live="polite" style="font-size: 0.8rem;"></small>
                </div>

                <div class="table-responsive pole-speed-table-wrap" style="max-height: 520px; overflow: auto; border: 1px solid #eef1f4; border-radius: 8px;">
                    <table class="table table-striped table-hover" style="margin-bottom: 0;">
                        <thead style="background: #f8f9fa;">
                            <tr>
                                <th style="position: sticky; top: 0; z-index: 2; background: #f8f9fa; font-weight: 600; font-size: 0.85rem; color: #495057; padding: 10px 12px;">Panchayat</th>
                                <th style="position: sticky; top: 0; z-index: 2; background: #f8f9fa; font-weight: 600; font-size: 0.85rem; color: #495057; padding: 10px 12px;">District</th>
                                <th style="position: sticky; top: 0; z-index: 2; background: #f8f9fa; font-weight: 600; font-size: 0.85rem; color: #495057; padding: 10px 12px;">Total Poles</th>
                                <th style="position: sticky; top: 0; z-index: 2; background: #f8f9fa; font-weight: 600; font-size: 0.85rem; color: #495057; padding: 10px 12px;">Installed</th>
                                <th style="position: sticky; top: 0; z-index: 2; background: #f8f9fa; font-weight: 600; font-size: 0.85rem; color: #495057; padding: 10px 12px;">Speed (poles/day)</th>
                            </tr>
                        </thead>
                        <tbody id="poleSpeedTableBody">
                            @foreach($poleSpeedMetrics as $panchayat)
                                @php
                                    $districtKey = $normalizeDistrictKey($panchayat['district'] ?? '');
                                    $districtLabel = $normalizeDistrictLabel($panchayat['district'] ?? '');
                                $panchayatLabel = $formatLocationName($panchayat['panchayat'] ?? '');
                                @endphp
                                <tr data-district="{{ $districtKey }}"
                                data-search="{{ strtolower(trim($panchayatLabel . ' ' . $districtLabel)) }}">
                                    <td style="padding: 10px 12px; font-size: 0.9rem; font-weight: 600; color: {{ $panchayat['speed_status'] == 'fast' ? '#28a745' : ($panchayat['speed_status'] == 'medium' ? '#f0ad4e' : '#dc3545') }};">
                                        {{ $panchayatLabel }}
                                    </td>
                                    <td style="padding: 10px 12px; font-size: 0.9rem;">{{ $districtLabel }}</td>
                                    <td style="padding: 10px 12px; font-size: 0.9rem;">{{ $panchayat['total_poles'] }}</td>
                                    <td style="padding: 10px 12px; font-size: 0.9rem;">{{ $panchayat['installed_poles'] }}</td>
                                    <td style="padding: 10px 12px; font-size: 0.9rem;">{{ number_format($panchayat['speed'], 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <style>
            @media (max-width: 767.98px) {
                #poleSpeedAnalysisSection .pole-speed-controls-row {
                    width: 100%;
                    flex-wrap: wrap;
                    align-items: stretch !important;
                    gap: 8px !important;
                }

                #poleSpeedAnalysisSection .pole-speed-search-group {
                    width: 100% !important;
                    max-width: 100% !important;
                    min-width: 0;
                    flex: 1 1 100%;
                }

                #poleSpeedAnalysisSection .pole-speed-toggle-btn {
                    width: 100%;
                    min-width: 0 !important;
                }

                #poleSpeedAnalysisSection .pole-speed-table-wrap {
                    width: 100%;
                    overflow-x: auto !important;
                }

                #poleSpeedAnalysisSection table th,
                #poleSpeedAnalysisSection table td {
                    font-size: 0.78rem !important;
                    padding: 8px 7px !important;
                    white-space: normal;
                }
            }
        </style>
        <script>
            (function() {
                const section = document.getElementById('poleSpeedAnalysisSection');
                if (!section) return;

                const chips = Array.from(section.querySelectorAll('.pole-speed-district-chip'));
                const searchInput = section.querySelector('#poleSpeedSearchInput');
                const toggleBtn = section.querySelector('#poleSpeedToggleBtn');
                const summaryText = section.querySelector('#poleSpeedSummaryText');
                const rows = Array.from(section.querySelectorAll('#poleSpeedTableBody tr'));
                const topLimit = 10;

                let selectedDistrict = 'all';
                let searchQuery = '';
                let showAll = false;

                function applyChipStyles() {
                    chips.forEach(function(chip) {
                        const isActive = chip.dataset.district.toLowerCase() === selectedDistrict;
                        chip.classList.toggle('active', isActive);
                        chip.classList.toggle('btn-primary', isActive);
                        chip.classList.toggle('btn-outline-primary', !isActive);
                        chip.setAttribute('aria-pressed', isActive ? 'true' : 'false');
                    });
                }

                function applyFilters() {
                    const filtered = rows.filter(function(row) {
                        const rowDistrict = (row.dataset.district || '').toLowerCase();
                        const rowSearch = (row.dataset.search || '').toLowerCase();
                        const districtOk = selectedDistrict === 'all' || rowDistrict === selectedDistrict;
                        const searchOk = searchQuery === '' || rowSearch.includes(searchQuery);
                        return districtOk && searchOk;
                    });

                    rows.forEach(function(row) {
                        row.style.display = 'none';
                    });

                    const visibleRows = showAll ? filtered : filtered.slice(0, topLimit);
                    visibleRows.forEach(function(row) {
                        row.style.display = '';
                    });

                    const hiddenCount = Math.max(0, filtered.length - visibleRows.length);
                    toggleBtn.textContent = showAll ? 'Show top 10 rows' : 'Show all rows';
                    toggleBtn.disabled = filtered.length <= topLimit;
                    toggleBtn.setAttribute('aria-expanded', showAll ? 'true' : 'false');

                    if (filtered.length === 0) {
                        summaryText.textContent = 'No panchayats match current filters.';
                    } else if (!showAll && hiddenCount > 0) {
                        summaryText.textContent = 'Showing ' + visibleRows.length + ' worst performers out of ' + filtered.length + '.';
                    } else {
                        summaryText.textContent = 'Showing ' + visibleRows.length + ' result' + (visibleRows.length === 1 ? '' : 's') + '.';
                    }
                }

                chips.forEach(function(chip) {
                    chip.addEventListener('click', function() {
                        selectedDistrict = (chip.dataset.district || 'all').toLowerCase();
                        showAll = false;
                        applyChipStyles();
                        applyFilters();
                    });
                });

                searchInput.addEventListener('input', function(event) {
                    searchQuery = (event.target.value || '').trim().toLowerCase();
                    showAll = false;
                    applyFilters();
                });

                toggleBtn.addEventListener('click', function() {
                    if (toggleBtn.disabled) return;
                    showAll = !showAll;
                    applyFilters();
                });

                applyChipStyles();
                applyFilters();
            })();
        </script>
    @endif
</div>
