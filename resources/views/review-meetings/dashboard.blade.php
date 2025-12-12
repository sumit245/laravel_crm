@extends('layouts.main')

@section('content')
    <div class="content-wrapper p-2">
        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-0">Meeting Dashboard</h2>
                <p class="text-muted">Comprehensive overview of meetings, tasks, and performance metrics</p>
            </div>
        </div>

        <!-- Overview Cards Row -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card card-rounded" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <div class="card-body text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50 mb-2">Total Meetings</h6>
                                <h2 class="mb-0">{{ $totalMeetings }}</h2>
                            </div>
                            <div>
                                <i class="bi bi-calendar-check" style="font-size: 2.5rem; opacity: 0.5;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card card-rounded" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <div class="card-body text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50 mb-2">Tasks Overdue</h6>
                                <h2 class="mb-0">{{ $overdueTasksCount }}</h2>
                            </div>
                            <div>
                                <i class="bi bi-exclamation-triangle" style="font-size: 2.5rem; opacity: 0.5;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card card-rounded" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                    <div class="card-body text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50 mb-2">Tasks in Queue</h6>
                                <h2 class="mb-0">{{ $tasksInQueueCount }}</h2>
                            </div>
                            <div>
                                <i class="bi bi-list-task" style="font-size: 2.5rem; opacity: 0.5;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card card-rounded" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                    <div class="card-body text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50 mb-2">Completion Rate</h6>
                                <h2 class="mb-0">{{ $completionRate }}%</h2>
                            </div>
                            <div>
                                <i class="bi bi-check-circle" style="font-size: 2.5rem; opacity: 0.5;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Second Row Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card card-rounded" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                    <div class="card-body text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50 mb-2">Upcoming Meetings</h6>
                                <h2 class="mb-0">{{ $upcomingMeetingsCount }}</h2>
                            </div>
                            <div>
                                <i class="bi bi-calendar-event" style="font-size: 2.5rem; opacity: 0.5;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card card-rounded" style="background: linear-gradient(135deg, #30cfd0 0%, #330867 100%);">
                    <div class="card-body text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50 mb-2">Avg Tasks/Meeting</h6>
                                <h2 class="mb-0">{{ $avgTasksPerMeeting }}</h2>
                            </div>
                            <div>
                                <i class="bi bi-graph-up" style="font-size: 2.5rem; opacity: 0.5;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card card-rounded" style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">Total Tasks</h6>
                                <h2 class="mb-0 text-dark">{{ $totalTasks }}</h2>
                            </div>
                            <div>
                                <i class="bi bi-clipboard-check text-muted" style="font-size: 2.5rem; opacity: 0.5;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card card-rounded" style="background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">Completed Tasks</h6>
                                <h2 class="mb-0 text-dark">{{ $completedTasks }}</h2>
                            </div>
                            <div>
                                <i class="bi bi-check2-all text-muted" style="font-size: 2.5rem; opacity: 0.5;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts and Performance Section -->
        <div class="row g-3 mb-4">
            <!-- Tasks by Status Chart -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Tasks by Status</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="tasksByStatusChart" height="250"></canvas>
                    </div>
                </div>
            </div>

            <!-- Tasks by Priority Chart -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Tasks by Priority</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="tasksByPriorityChart" height="250"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Department Performance and Meeting Trends -->
        <div class="row g-3 mb-4">
            <!-- Department Performance -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Department Performance</h5>
                    </div>
                    <div class="card-body">
                        @if (count($departmentPerformance) > 0)
                            <canvas id="departmentPerformanceChart" height="250"></canvas>
                        @else
                            <p class="text-muted text-center py-5">No department data available</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Meeting Trends -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Meeting Trends (Last 6 Months)</h5>
                    </div>
                    <div class="card-body">
                        @if (count($meetingTrends) > 0)
                            <canvas id="meetingTrendsChart" height="250"></canvas>
                        @else
                            <p class="text-muted text-center py-5">No meeting trends data available</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Employee Performance Table -->
        <div class="row g-3 mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Employee Performance</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Employee</th>
                                        <th>Total Tasks</th>
                                        <th>Completed</th>
                                        <th>In Progress</th>
                                        <th>Pending</th>
                                        <th>Overdue</th>
                                        <th class="text-end pe-4">Completion Rate</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($employeePerformance as $employee)
                                        <tr>
                                            <td class="ps-4 fw-bold">{{ $employee['user_name'] }}</td>
                                            <td>{{ $employee['total_tasks'] }}</td>
                                            <td>
                                                <span class="badge bg-success">{{ $employee['completed_tasks'] }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ $employee['in_progress_tasks'] }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-warning">{{ $employee['pending_tasks'] }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-danger">{{ $employee['overdue_tasks'] }}</span>
                                            </td>
                                            <td class="text-end pe-4">
                                                <div class="d-flex align-items-center justify-content-end">
                                                    <div class="progress" style="width: 100px; height: 20px;">
                                                        <div class="progress-bar" role="progressbar"
                                                            style="width: {{ $employee['completion_rate'] }}%"
                                                            aria-valuenow="{{ $employee['completion_rate'] }}"
                                                            aria-valuemin="0" aria-valuemax="100">
                                                        </div>
                                                    </div>
                                                    <span class="ms-2">{{ $employee['completion_rate'] }}%</span>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-5 text-muted">No employee performance
                                                data available</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity Section -->
        <div class="row g-3 mb-4">
            <!-- Recent Meetings -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Recent Meetings</h5>
                        <a href="{{ route('meets.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            @forelse($recentMeetings as $meeting)
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1">{{ $meeting->title }}</h6>
                                            <p class="mb-1 text-muted small">
                                                <i class="bi bi-calendar"></i>
                                                {{ \Carbon\Carbon::parse($meeting->meet_date)->format('M d, Y') }}
                                            </p>
                                            <small class="text-muted">
                                                <i class="bi bi-list-task"></i> {{ $meeting->discussion_points_count }}
                                                tasks
                                            </small>
                                        </div>
                                        <a href="{{ route('meets.details', $meeting->id) }}"
                                            class="btn btn-sm btn-outline-secondary">
                                            View
                                        </a>
                                    </div>
                                </div>
                            @empty
                                <div class="list-group-item text-center py-5 text-muted">
                                    No recent meetings
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Performers -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Top Performers</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            @forelse($topPerformers as $index => $performer)
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            <div class="me-3">
                                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
                                                    style="width: 40px; height: 40px; font-weight: bold;">
                                                    {{ $index + 1 }}
                                                </div>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $performer['user_name'] }}</h6>
                                                <small class="text-muted">Employee</small>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-success" style="font-size: 1rem;">
                                                {{ $performer['completed_count'] }} completed
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="list-group-item text-center py-5 text-muted">
                                    No top performers data available
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Tasks by Status Chart (Pie Chart)
            const tasksByStatusCtx = document.getElementById('tasksByStatusChart');
            if (tasksByStatusCtx) {
                const tasksByStatusData = @json($tasksByStatus);
                new Chart(tasksByStatusCtx, {
                    type: 'doughnut',
                    data: {
                        labels: Object.keys(tasksByStatusData),
                        datasets: [{
                            data: Object.values(tasksByStatusData),
                            backgroundColor: [
                                'rgba(255, 99, 132, 0.8)',
                                'rgba(54, 162, 235, 0.8)',
                                'rgba(75, 192, 192, 0.8)',
                                'rgba(255, 206, 86, 0.8)',
                            ],
                            borderWidth: 2,
                            borderColor: '#fff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            }

            // Tasks by Priority Chart (Bar Chart)
            const tasksByPriorityCtx = document.getElementById('tasksByPriorityChart');
            if (tasksByPriorityCtx) {
                const tasksByPriorityData = @json($tasksByPriority);
                new Chart(tasksByPriorityCtx, {
                    type: 'bar',
                    data: {
                        labels: Object.keys(tasksByPriorityData),
                        datasets: [{
                            label: 'Tasks',
                            data: Object.values(tasksByPriorityData),
                            backgroundColor: [
                                'rgba(255, 99, 132, 0.8)',
                                'rgba(255, 206, 86, 0.8)',
                                'rgba(75, 192, 192, 0.8)',
                            ],
                            borderColor: [
                                'rgba(255, 99, 132, 1)',
                                'rgba(255, 206, 86, 1)',
                                'rgba(75, 192, 192, 1)',
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            }
                        }
                    }
                });
            }

            // Department Performance Chart (Bar Chart)
            const departmentPerformanceCtx = document.getElementById('departmentPerformanceChart');
            if (departmentPerformanceCtx) {
                const departmentData = @json($departmentPerformance);
                const departments = Object.keys(departmentData);
                const completed = departments.map(dept => departmentData[dept].completed || 0);
                const pending = departments.map(dept => departmentData[dept].pending || 0);
                const inProgress = departments.map(dept => departmentData[dept].in_progress || 0);

                new Chart(departmentPerformanceCtx, {
                    type: 'bar',
                    data: {
                        labels: departments,
                        datasets: [{
                                label: 'Completed',
                                data: completed,
                                backgroundColor: 'rgba(75, 192, 192, 0.8)',
                                borderColor: 'rgba(75, 192, 192, 1)',
                                borderWidth: 1
                            },
                            {
                                label: 'In Progress',
                                data: inProgress,
                                backgroundColor: 'rgba(54, 162, 235, 0.8)',
                                borderColor: 'rgba(54, 162, 235, 1)',
                                borderWidth: 1
                            },
                            {
                                label: 'Pending',
                                data: pending,
                                backgroundColor: 'rgba(255, 206, 86, 0.8)',
                                borderColor: 'rgba(255, 206, 86, 1)',
                                borderWidth: 1
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: {
                                stacked: true
                            },
                            y: {
                                stacked: true,
                                beginAtZero: true
                            }
                        },
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            }

            // Meeting Trends Chart (Line Chart)
            const meetingTrendsCtx = document.getElementById('meetingTrendsChart');
            if (meetingTrendsCtx) {
                const trendsData = @json($meetingTrends);
                const months = Object.keys(trendsData);
                const counts = Object.values(trendsData);

                new Chart(meetingTrendsCtx, {
                    type: 'line',
                    data: {
                        labels: months,
                        datasets: [{
                            label: 'Meetings',
                            data: counts,
                            borderColor: 'rgba(75, 192, 192, 1)',
                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                            tension: 0.4,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            }
                        }
                    }
                });
            }
        });
    </script>
@endpush

@push('styles')
    <style>
        .card-rounded {
            border-radius: 12px;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .card-rounded:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .card-header {
            border-bottom: 1px solid #e9ecef;
            padding: 1rem 1.5rem;
        }

        .card-body {
            padding: 1.5rem;
        }

        .table th {
            font-weight: 600;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .list-group-item {
            border-left: none;
            border-right: none;
            padding: 1rem 1.5rem;
        }

        .list-group-item:first-child {
            border-top: none;
        }

        .list-group-item:last-child {
            border-bottom: none;
        }

        .progress {
            border-radius: 10px;
        }

        .progress-bar {
            border-radius: 10px;
        }

        @media (max-width: 768px) {
            .card-body canvas {
                max-height: 200px;
            }
        }
    </style>
@endpush
