<div class="dashboard-section">
    <!-- Financial Overview -->
    <div class="mb-4">
        <h6 class="mb-3" style="font-weight: 600; color: #495057; font-size: 0.95rem;">
            <i class="mdi mdi-cash-multiple me-2"></i>Financial Overview
        </h6>
        <div class="row g-3">
            <div class="col-md-3">
                <div class="card" style="border: 1px solid #dee2e6; border-radius: 4px; background: white;">
                    <div class="card-body text-center" style="padding: 16px;">
                        <h6 style="font-weight: 600; color: #495057; font-size: 0.85rem; margin-bottom: 8px;">Total Amount</h6>
                        <h4 class="mb-0" style="font-weight: 700; color: #212529; font-size: 1.2rem;">₹{{ number_format($tada_analytics['financial_overview']['total_amount'] ?? 0, 2) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card" style="border: 1px solid #dee2e6; border-radius: 4px; background: white;">
                    <div class="card-body text-center" style="padding: 16px;">
                        <h6 style="font-weight: 600; color: #495057; font-size: 0.85rem; margin-bottom: 8px;">Disbursed This Month</h6>
                        <h4 class="mb-0" style="font-weight: 700; color: #28a745; font-size: 1.2rem;">₹{{ number_format($tada_analytics['financial_overview']['disbursed_this_month'] ?? 0, 2) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card" style="border: 1px solid #dee2e6; border-radius: 4px; background: white;">
                    <div class="card-body text-center" style="padding: 16px;">
                        <h6 style="font-weight: 600; color: #495057; font-size: 0.85rem; margin-bottom: 8px;">Pending Approval</h6>
                        <h4 class="mb-0" style="font-weight: 700; color: #ffc107; font-size: 1.2rem;">₹{{ number_format($tada_analytics['financial_overview']['pending_amount'] ?? 0, 2) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card" style="border: 1px solid #dee2e6; border-radius: 4px; background: white;">
                    <div class="card-body text-center" style="padding: 16px;">
                        <h6 style="font-weight: 600; color: #495057; font-size: 0.85rem; margin-bottom: 8px;">Distance Travelled</h6>
                        <h4 class="mb-0" style="font-weight: 700; color: #212529; font-size: 1.2rem;">{{ number_format($tada_analytics['financial_overview']['distance_travelled'] ?? 0, 0) }} km</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="row g-3 mt-2">
            <div class="col-md-4">
                <div class="card" style="border: 1px solid #dee2e6; border-radius: 4px; background: white;">
                    <div class="card-body text-center" style="padding: 16px;">
                        <h6 style="font-weight: 600; color: #495057; font-size: 0.85rem; margin-bottom: 8px;">Avg per Travel</h6>
                        <h5 class="mb-0" style="font-weight: 700; color: #212529;">₹{{ number_format($tada_analytics['financial_overview']['avg_per_travel'] ?? 0, 2) }}</h5>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card" style="border: 1px solid #dee2e6; border-radius: 4px; background: white;">
                    <div class="card-body text-center" style="padding: 16px;">
                        <h6 style="font-weight: 600; color: #495057; font-size: 0.85rem; margin-bottom: 8px;">Avg per km</h6>
                        <h5 class="mb-0" style="font-weight: 700; color: #212529;">₹{{ number_format($tada_analytics['financial_overview']['avg_per_km'] ?? 0, 2) }}</h5>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card" style="border: 1px solid #dee2e6; border-radius: 4px; background: white;">
                    <div class="card-body text-center" style="padding: 16px;">
                        <h6 style="font-weight: 600; color: #495057; font-size: 0.85rem; margin-bottom: 8px;">Highest Traveller</h6>
                        @if(isset($tada_analytics['financial_overview']['highest_traveller']) && $tada_analytics['financial_overview']['highest_traveller'])
                            <h5 class="mb-0" style="font-weight: 700; color: #212529; font-size: 0.95rem;">
                                {{ $tada_analytics['financial_overview']['highest_traveller']['name'] }}
                                <small class="text-muted d-block" style="font-size: 0.75rem;">({{ $tada_analytics['financial_overview']['highest_traveller']['travels'] }} travels)</small>
                            </h5>
                        @else
                            <h5 class="mb-0 text-muted" style="font-size: 0.9rem;">N/A</h5>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Per-Project Disbursals -->
    @if(isset($tada_analytics['per_project_disbursals']) && count($tada_analytics['per_project_disbursals']) > 0)
    <div class="mb-4">
        <h6 class="mb-3" style="font-weight: 600; color: #495057; font-size: 0.95rem;">
            <i class="mdi mdi-chart-bar me-2"></i>Disbursals by Project
        </h6>
        <div class="table-responsive">
            <table class="table table-striped table-hover" style="margin-bottom: 0;">
                <thead style="background: #f8f9fa;">
                    <tr>
                        <th style="font-weight: 600; font-size: 0.85rem; color: #495057; padding: 10px 12px;">Project Name</th>
                        <th style="font-weight: 600; font-size: 0.85rem; color: #495057; padding: 10px 12px;">Amount</th>
                        <th style="font-weight: 600; font-size: 0.85rem; color: #495057; padding: 10px 12px;">Travels</th>
                        <th style="font-weight: 600; font-size: 0.85rem; color: #495057; padding: 10px 12px;">Avg per Travel</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tada_analytics['per_project_disbursals'] as $disbursal)
                        <tr>
                            <td style="padding: 10px 12px; font-size: 0.9rem;">{{ $disbursal['project_name'] }}</td>
                            <td style="padding: 10px 12px; font-size: 0.9rem;">₹{{ number_format($disbursal['amount'], 2) }}</td>
                            <td style="padding: 10px 12px; font-size: 0.9rem;">{{ $disbursal['travels'] }}</td>
                            <td style="padding: 10px 12px; font-size: 0.9rem;">₹{{ number_format($disbursal['amount'] / max($disbursal['travels'], 1), 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Top Travellers -->
    @if(isset($tada_analytics['top_travellers']) && count($tada_analytics['top_travellers']) > 0)
    <div class="mb-4">
        <h6 class="mb-3" style="font-weight: 600; color: #495057; font-size: 0.95rem;">
            <i class="mdi mdi-account-multiple me-2"></i>Top Travellers
        </h6>
        <div class="table-responsive">
            <table class="table table-striped table-hover" style="margin-bottom: 0;">
                <thead style="background: #f8f9fa;">
                    <tr>
                        <th style="font-weight: 600; font-size: 0.85rem; color: #495057; padding: 10px 12px;">Rank</th>
                        <th style="font-weight: 600; font-size: 0.85rem; color: #495057; padding: 10px 12px;">Staff Name</th>
                        <th style="font-weight: 600; font-size: 0.85rem; color: #495057; padding: 10px 12px;">Travels</th>
                        <th style="font-weight: 600; font-size: 0.85rem; color: #495057; padding: 10px 12px;">Distance</th>
                        <th style="font-weight: 600; font-size: 0.85rem; color: #495057; padding: 10px 12px;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tada_analytics['top_travellers'] as $index => $traveller)
                        <tr>
                            <td style="padding: 10px 12px;">
                                @if($index == 0)
                                    <span class="badge bg-warning text-dark">🥇</span>
                                @elseif($index == 1)
                                    <span class="badge bg-secondary">🥈</span>
                                @elseif($index == 2)
                                    <span class="badge bg-info">🥉</span>
                                @else
                                    <span class="badge bg-light text-dark">#{{ $index + 1 }}</span>
                                @endif
                            </td>
                            <td style="padding: 10px 12px; font-size: 0.9rem;">
                                <a href="{{ route('staff.show', $traveller['user_id']) }}" class="text-decoration-none" style="color: #007bff;">
                                    {{ $traveller['name'] }}
                                </a>
                            </td>
                            <td style="padding: 10px 12px; font-size: 0.9rem;">{{ $traveller['travels'] }}</td>
                            <td style="padding: 10px 12px; font-size: 0.9rem;">{{ number_format($traveller['distance'], 0) }} km</td>
                            <td style="padding: 10px 12px; font-size: 0.9rem;">₹{{ number_format($traveller['amount'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Travel Breakdown -->
    <div class="mb-4">
        <h6 class="mb-3" style="font-weight: 600; color: #495057; font-size: 0.95rem;">
            <i class="mdi mdi-chart-donut me-2"></i>Travel Breakdown
        </h6>
        <div class="row">
            <div class="col-md-6">
                <div class="card" style="border: 1px solid #dee2e6; border-radius: 4px; background: white;">
                    <div class="card-header" style="background: #f8f9fa; border-bottom: 1px solid #dee2e6; padding: 10px 12px;">
                        <h6 class="mb-0" style="font-weight: 600; font-size: 0.9rem; color: #495057;">By Vehicle</h6>
                    </div>
                    <div class="card-body" style="padding: 12px;">
                        @if(isset($tada_analytics['travel_breakdown']['by_vehicle']) && count($tada_analytics['travel_breakdown']['by_vehicle']) > 0)
                            @foreach($tada_analytics['travel_breakdown']['by_vehicle'] as $vehicle)
                                <div class="mb-2">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span style="font-size: 0.85rem; color: #495057;">{{ $vehicle['category'] ?? 'Unknown' }}</span>
                                        <span style="font-size: 0.85rem; font-weight: 500;">{{ number_format($vehicle['percentage'], 1) }}%</span>
                                    </div>
                                    <div class="progress" style="height: 18px; border-radius: 4px;">
                                        <div class="progress-bar bg-primary" style="width: {{ $vehicle['percentage'] }}%">
                                            <small style="font-size: 0.7rem;">{{ $vehicle['count'] }}</small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center text-muted py-3">
                                <i class="mdi mdi-information-outline" style="font-size: 1.5rem; opacity: 0.4;"></i>
                                <p class="mt-2 mb-0" style="font-size: 0.85rem;">No vehicle data available.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card" style="border: 1px solid #dee2e6; border-radius: 4px; background: white;">
                    <div class="card-header" style="background: #f8f9fa; border-bottom: 1px solid #dee2e6; padding: 10px 12px;">
                        <h6 class="mb-0" style="font-weight: 600; font-size: 0.9rem; color: #495057;">By Status</h6>
                    </div>
                    <div class="card-body" style="padding: 12px;">
                        @if(isset($tada_analytics['travel_breakdown']['by_status']) && count($tada_analytics['travel_breakdown']['by_status']) > 0)
                            @foreach($tada_analytics['travel_breakdown']['by_status'] as $status)
                                <div class="mb-2">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span style="font-size: 0.85rem; color: #495057;">{{ ucfirst($status['status']) }}</span>
                                        <span style="font-size: 0.85rem; font-weight: 500;">{{ number_format($status['percentage'], 1) }}%</span>
                                    </div>
                                    <div class="progress" style="height: 18px; border-radius: 4px;">
                                        <div class="progress-bar 
                                            @if($status['status'] == 'Approved') bg-success
                                            @elseif($status['status'] == 'Pending') bg-warning
                                            @elseif($status['status'] == 'Rejected') bg-danger
                                            @else bg-secondary
                                            @endif" 
                                            style="width: {{ $status['percentage'] }}%">
                                            <small style="font-size: 0.7rem;">{{ $status['count'] }}</small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center text-muted py-3">
                                <i class="mdi mdi-information-outline" style="font-size: 1.5rem; opacity: 0.4;"></i>
                                <p class="mt-2 mb-0" style="font-size: 0.85rem;">No status data available.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
