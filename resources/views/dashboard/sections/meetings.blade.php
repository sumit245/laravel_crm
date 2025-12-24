<div class="dashboard-section">
    <!-- Meeting Overview -->
    <div class="mb-4">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="card" style="border: 1px solid #dee2e6; border-radius: 4px; background: white;">
                    <div class="card-body text-center" style="padding: 16px;">
                        <h6 style="font-weight: 600; color: #495057; font-size: 0.85rem; margin-bottom: 8px;">Total Meetings</h6>
                        <h3 class="mb-0" style="font-weight: 700; color: #212529;">{{ $meeting_analytics['overview']['total_meetings'] ?? 0 }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card" style="border: 1px solid #dee2e6; border-radius: 4px; background: white;">
                    <div class="card-body text-center" style="padding: 16px;">
                        <h6 style="font-weight: 600; color: #495057; font-size: 0.85rem; margin-bottom: 8px;">Active Discussions</h6>
                        <h3 class="mb-0" style="font-weight: 700; color: #212529;">{{ $meeting_analytics['overview']['active_discussions'] ?? 0 }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card" style="border: 1px solid #dee2e6; border-radius: 4px; background: white;">
                    <div class="card-body text-center" style="padding: 16px;">
                        <h6 style="font-weight: 600; color: #495057; font-size: 0.85rem; margin-bottom: 8px;">Discussions This Month</h6>
                        <h3 class="mb-0" style="font-weight: 700; color: #212529;">{{ $meeting_analytics['overview']['discussions_this_month'] ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Meeting Breakdown by Type -->
    @if(isset($meeting_analytics['meeting_types']) && count($meeting_analytics['meeting_types']) > 0)
    <div class="mb-4">
        <h6 class="mb-3" style="font-weight: 600; color: #495057; font-size: 0.95rem;">
            <i class="mdi mdi-chart-pie me-2"></i>Meeting Breakdown by Type
        </h6>
        <div class="table-responsive">
            <table class="table table-striped table-hover" style="margin-bottom: 0;">
                <thead style="background: #f8f9fa;">
                    <tr>
                        <th style="font-weight: 600; font-size: 0.85rem; color: #495057; padding: 10px 12px;">Type</th>
                        <th style="font-weight: 600; font-size: 0.85rem; color: #495057; padding: 10px 12px;">Count</th>
                        <th style="font-weight: 600; font-size: 0.85rem; color: #495057; padding: 10px 12px;">Avg Duration</th>
                        <th style="font-weight: 600; font-size: 0.85rem; color: #495057; padding: 10px 12px;">Trend</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($meeting_analytics['meeting_types'] as $type)
                        <tr>
                            <td style="padding: 10px 12px; font-size: 0.9rem;">{{ $type['type'] }}</td>
                            <td style="padding: 10px 12px; font-size: 0.9rem;">{{ $type['count'] }}</td>
                            <td style="padding: 10px 12px; font-size: 0.9rem;">{{ $type['avg_duration'] }} min</td>
                            <td style="padding: 10px 12px;">
                                @if($type['trend'] == 'up')
                                    <span class="text-success" style="font-size: 0.85rem;">↑ {{ $type['trend'] ?? 0 }}</span>
                                @elseif($type['trend'] == 'down')
                                    <span class="text-danger" style="font-size: 0.85rem;">↓ {{ abs($type['trend'] ?? 0) }}</span>
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

    <!-- Recent Meetings -->
    @if(isset($meeting_analytics['recent_meetings']) && count($meeting_analytics['recent_meetings']) > 0)
    <div class="mb-4">
        <h6 class="mb-3" style="font-weight: 600; color: #495057; font-size: 0.95rem;">
            <i class="mdi mdi-calendar-clock me-2"></i>Recent Meetings
        </h6>
        <div class="table-responsive">
            <table class="table table-striped table-hover" style="margin-bottom: 0;">
                <thead style="background: #f8f9fa;">
                    <tr>
                        <th style="font-weight: 600; font-size: 0.85rem; color: #495057; padding: 10px 12px;">Date</th>
                        <th style="font-weight: 600; font-size: 0.85rem; color: #495057; padding: 10px 12px;">Title</th>
                        <th style="font-weight: 600; font-size: 0.85rem; color: #495057; padding: 10px 12px;">Participants</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($meeting_analytics['recent_meetings'] as $meeting)
                        <tr>
                            <td style="padding: 10px 12px; font-size: 0.9rem;">{{ $meeting['date'] }}</td>
                            <td style="padding: 10px 12px; font-size: 0.9rem;">{{ $meeting['title'] }}</td>
                            <td style="padding: 10px 12px; font-size: 0.9rem;">
                                {{ $meeting['participants_count'] }} 
                                @if($meeting['you_participated'])
                                    <span class="badge bg-success" style="font-size: 0.7rem;">You + {{ $meeting['other_count'] }}</span>
                                @else
                                    <span class="badge bg-secondary" style="font-size: 0.7rem;">Not participating</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Discussion Points Summary -->
    <div class="mb-4">
        <h6 class="mb-3" style="font-weight: 600; color: #495057; font-size: 0.95rem;">
            <i class="mdi mdi-comment-text-multiple-outline me-2"></i>Discussion Points Summary
        </h6>
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <div class="card" style="border: 1px solid #dee2e6; border-radius: 4px; background: white;">
                    <div class="card-body text-center" style="padding: 16px;">
                        <h6 style="font-weight: 600; color: #495057; font-size: 0.85rem; margin-bottom: 8px;">Total Points</h6>
                        <h3 class="mb-0" style="font-weight: 700; color: #212529;">{{ $meeting_analytics['discussion_points']['total'] ?? 0 }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card" style="border: 1px solid #dee2e6; border-radius: 4px; background: white;">
                    <div class="card-body text-center" style="padding: 16px;">
                        <h6 style="font-weight: 600; color: #495057; font-size: 0.85rem; margin-bottom: 8px;">Resolved</h6>
                        <h3 class="mb-0" style="font-weight: 700; color: #28a745;">{{ $meeting_analytics['discussion_points']['resolved'] ?? 0 }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card" style="border: 1px solid #dee2e6; border-radius: 4px; background: white;">
                    <div class="card-body text-center" style="padding: 16px;">
                        <h6 style="font-weight: 600; color: #495057; font-size: 0.85rem; margin-bottom: 8px;">Pending</h6>
                        <h3 class="mb-0" style="font-weight: 700; color: #ffc107;">{{ $meeting_analytics['discussion_points']['pending'] ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Discussion Topics -->
        @if(isset($meeting_analytics['top_topics']) && count($meeting_analytics['top_topics']) > 0)
        <div class="mt-3">
            <h6 style="font-weight: 600; color: #495057; font-size: 0.9rem; margin-bottom: 12px;">Top Discussion Topics</h6>
            <div class="table-responsive">
                <table class="table table-striped" style="margin-bottom: 0;">
                    <thead style="background: #f8f9fa;">
                        <tr>
                            <th style="font-weight: 600; font-size: 0.85rem; color: #495057; padding: 10px 12px;">Topic</th>
                            <th style="font-weight: 600; font-size: 0.85rem; color: #495057; padding: 10px 12px;">Count</th>
                            <th style="font-weight: 600; font-size: 0.85rem; color: #495057; padding: 10px 12px;">Resolution Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($meeting_analytics['top_topics'] as $topic)
                            <tr>
                                <td style="padding: 10px 12px; font-size: 0.9rem;">{{ $topic['topic'] }}</td>
                                <td style="padding: 10px 12px; font-size: 0.9rem;">{{ $topic['count'] }}</td>
                                <td style="padding: 10px 12px;">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress" style="height: 18px; width: 150px;">
                                            <div class="progress-bar {{ $topic['resolution_rate'] >= 80 ? 'bg-success' : ($topic['resolution_rate'] >= 50 ? 'bg-warning' : 'bg-danger') }}" 
                                                 style="width: {{ $topic['resolution_rate'] }}%">
                                            </div>
                                        </div>
                                        <span style="font-size: 0.85rem; font-weight: 500;">{{ number_format($topic['resolution_rate'], 1) }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
</div>
