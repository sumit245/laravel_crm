@extends("layouts.main")
@section('title', ($staff->name ?? 'Staff') . ' · Staff')

@section("content")
<div class="content-wrapper p-2 staff-profile-show">
    <!-- Basic Details Section -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center">
                            <div class="staff-avatar-wrapper position-relative me-3 flex-shrink-0">
                                <img src="{{ $staff->image ?? asset('images/faces/face8.jpg') }}"
                                     alt=""
                                     class="staff-avatar"
                                     id="staffAvatar"
                                     width="64"
                                     height="64"
                                     decoding="async"
                                     onerror="this.onerror=null;this.src='{{ asset('images/faces/face8.jpg') }}';">
                                @can('update', $staff)
                                <label for="avatarInput" class="avatar-change-btn" title="Change Photo" aria-label="Change profile photo">
                                    <i class="mdi mdi-camera" aria-hidden="true"></i>
                                </label>
                                <input type="file" id="avatarInput" accept="image/*" style="display: none;">
                                @endcan
                            </div>
                            <div>
                                <h2 class="mb-1 staff-name">{{ $staff->name }}</h2>
                                <p class="text-muted mb-0 small staff-email">{{ $staff->email }}</p>
                            </div>
                        </div>
                        @can('update', $staff)
                        <a href="{{ route('staff.edit', $staff->id) }}" class="btn btn-sm btn-outline-warning edit-staff-btn">
                            <i class="mdi mdi-pencil" aria-hidden="true"></i> Edit
                        </a>
                        @endcan
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="info-group mb-3">
                                <div class="info-item">
                                    <i class="mdi mdi-phone text-muted me-2"></i>
                                    <span class="small">{{ $staff->contactNo ?? 'N/A' }}</span>
                                </div>
                                <div class="info-item mt-2">
                                    <i class="mdi mdi-map-marker text-muted me-2"></i>
                                    <span class="small">{{ $staff->address ?? 'N/A' }}</span>
                                </div>
                            </div>
                            
                            <div class="info-group">
                                <div class="info-label small text-muted mb-1">Team</div>
                                @if ($staff->projectManager)
                                <div class="info-item">
                                    <i class="mdi mdi-account-tie text-muted me-2"></i>
                                    <span class="small">{{ $staff->projectManager->firstName }} {{ $staff->projectManager->lastName }}</span>
                                </div>
                                @endif
                                @if ($staff->usercategory)
                                <div class="info-item mt-1">
                                    <i class="mdi mdi-briefcase text-muted me-2"></i>
                                    <span class="small">{{ $staff->usercategory->category_code ?? 'N/A' }}</span>
                                </div>
                                @endif
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            @if ($assignedProjects->isNotEmpty())
                            <div class="info-group mb-3">
                                <div class="info-label small text-muted mb-1">Projects</div>
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach ($assignedProjects as $project)
                                        <span class="badge badge-info small">{{ $project->project_name }}</span>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                            
                            <div class="info-group">
                                <div class="info-label small text-muted mb-1">Role</div>
                                <div class="info-item">
                                    <span class="badge badge-primary">{{ \App\Enums\UserRole::tryFrom((int) $staff->role)?->label() ?? 'Unknown' }}</span>
                                </div>
                            </div>
                            
                            @can('update', $staff)
                            @if ($staff->bankName || $staff->accountNumber || $staff->ifsc)
                            <div class="info-group mt-3">
                                <div class="info-label small text-muted mb-1">Banking</div>
                                <button class="btn btn-sm btn-outline-secondary staff-banking-toggle"
                                    type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#staffBankingDetails"
                                    aria-expanded="false"
                                    aria-controls="staffBankingDetails">
                                    <i class="mdi mdi-eye-lock-outline me-1" aria-hidden="true"></i>
                                    Show banking details
                                </button>
                                <div class="collapse mt-2" id="staffBankingDetails">
                                    <div class="staff-banking-panel">
                                        <div class="info-item">
                                            <span class="small">{{ $staff->bankName ?? 'N/A' }}</span>
                                        </div>
                                        @if ($staff->accountNumber)
                                        <div class="info-item mt-1">
                                            <span class="small text-muted">A/C:</span>
                                            <span class="small ms-1 staff-banking-account-masked">{{ str_repeat('•', max(0, strlen((string) $staff->accountNumber) - 4)) . substr((string) $staff->accountNumber, -4) }}</span>
                                        </div>
                                        @endif
                                        @if ($staff->ifsc)
                                        <div class="info-item mt-1">
                                            <span class="small text-muted">IFSC:</span>
                                            <span class="small ms-1">{{ $staff->ifsc }}</span>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endif
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @php
        $meetingTaskTotal = (int) ($meetingTasksSummary['total'] ?? 0);
        $showMeetingMetric = $meetingTaskTotal > 0;
        $metricColClass = $showMeetingMetric ? 'col-md-3' : 'col-md-4';
        $hasWorkloadData = ((int) $totalTasksCount + (int) $completedTasksCount + (int) $pendingTasksCount + $meetingTaskTotal) > 0;
    @endphp

    <!-- Summary Cards (equal-height row; label band avoids wrap skew) -->
    <div class="row staff-metric-cards-row g-3 mb-4">
        <div class="{{ $metricColClass }}">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="d-flex align-items-center w-100">
                        <div class="flex-grow-1 min-w-0">
                            <p class="text-muted mb-1 small fw-semibold text-uppercase metric-label">Total Workload Units</p>
                            <p class="mb-0 fs-3 fw-semibold metric-value">{{ $totalTasksCount }}</p>
                        </div>
                        <div class="text-primary flex-shrink-0 ms-2" aria-hidden="true">
                            <i class="mdi mdi-clipboard-list mdi-36px"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="{{ $metricColClass }}">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="d-flex align-items-center w-100">
                        <div class="flex-grow-1 min-w-0">
                            <p class="text-muted mb-1 small fw-semibold text-uppercase metric-label">Completed</p>
                            <p class="mb-0 fs-3 fw-semibold text-success metric-value">{{ $completedTasksCount }}</p>
                        </div>
                        <div class="text-success flex-shrink-0 ms-2" aria-hidden="true">
                            <i class="mdi mdi-check-circle mdi-36px"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="{{ $metricColClass }}">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="d-flex align-items-center w-100">
                        <div class="flex-grow-1 min-w-0">
                            <p class="text-muted mb-1 small fw-semibold text-uppercase metric-label">Pending</p>
                            <p class="mb-0 fs-3 fw-semibold text-warning metric-value">{{ $pendingTasksCount }}</p>
                        </div>
                        <div class="text-warning flex-shrink-0 ms-2" aria-hidden="true">
                            <i class="mdi mdi-clock-outline mdi-36px"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @if ($showMeetingMetric)
        <div class="{{ $metricColClass }}">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="d-flex align-items-center w-100">
                        <div class="flex-grow-1 min-w-0">
                            <p class="text-muted mb-1 small fw-semibold text-uppercase metric-label"><span class="staff-metric-nowrap-label">Meeting Tasks</span></p>
                            <p class="mb-0 fs-3 fw-semibold text-info metric-value">{{ $meetingTasksSummary['total'] ?? 0 }}</p>
                        </div>
                        <div class="text-info flex-shrink-0 ms-2" aria-hidden="true">
                            <i class="mdi mdi-calendar-check mdi-36px"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
    @if ($hasWorkloadData)
    <div class="row mb-3">
        <div class="col-12">
            <p class="text-muted small mb-0">
                Workload units represent field output totals: streetlight poles for streetlight projects and sites for rooftop projects.
            </p>
        </div>
    </div>
    @else
    <div class="row mb-3">
        <div class="col-12">
            <div class="alert alert-info mb-0">
                <i class="mdi mdi-information-outline me-1" aria-hidden="true"></i>
                No staff workload data is available yet. This staff member may not have assigned projects or field entries.
                @can('update', $staff)
                    <a href="{{ route('staff.edit', $staff->id) }}" class="alert-link">Assign projects or update profile</a> to start tracking workload.
                @endcan
            </div>
        </div>
    </div>
    @endif

    <!-- Meeting Tasks Section -->
    @if ($meetingTasks && $meetingTasks->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title mb-3 fs-5">Meeting Related Tasks</h3>
                    
                    <!-- Meeting Tasks Summary Cards -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <div class="card">
                                <div class="card-body text-center">
                                    <p class="text-muted mb-1 small fw-semibold text-uppercase metric-label">Total Assigned</p>
                                    <p class="mb-0 fs-4 fw-semibold text-info metric-value">{{ $meetingTasksSummary['total'] ?? 0 }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card">
                                <div class="card-body text-center">
                                    <p class="text-muted mb-1 small fw-semibold text-uppercase metric-label">Completed</p>
                                    <p class="mb-0 fs-4 fw-semibold text-success metric-value">{{ $meetingTasksSummary['completed'] ?? 0 }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card">
                                <div class="card-body text-center">
                                    <p class="text-muted mb-1 small fw-semibold text-uppercase metric-label">In Progress</p>
                                    <p class="mb-0 fs-4 fw-semibold text-primary metric-value">{{ $meetingTasksSummary['in_progress'] ?? 0 }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card">
                                <div class="card-body text-center">
                                    <p class="text-muted mb-1 small fw-semibold text-uppercase metric-label">Pending</p>
                                    <p class="mb-0 fs-4 fw-semibold text-warning metric-value">{{ $meetingTasksSummary['pending'] ?? 0 }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Meeting Tasks DataTable -->
                    <x-datatable id="meetingTasksTable" 
                        title="All Meeting Tasks" 
                        :columns="[
                            ['title' => '#', 'width' => '5%'],
                            ['title' => 'Meeting Title', 'width' => '20%'],
                            ['title' => 'Task Title', 'width' => '20%'],
                            ['title' => 'Project', 'width' => '15%'],
                            ['title' => 'Priority', 'width' => '10%'],
                            ['title' => 'Status', 'width' => '10%'],
                            ['title' => 'Due Date', 'width' => '10%'],
                            ['title' => 'Assigned Date', 'width' => '10%'],
                        ]" 
                        :exportEnabled="true" 
                        :importEnabled="false" 
                        :bulkDeleteEnabled="false"
                        pageLength="25" 
                        searchPlaceholder="Search Meeting Tasks...">
                        @foreach ($meetingTasks as $task)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                @if ($task->meet)
                                    <a href="{{ route('meets.details', $task->meet->id) }}" class="text-primary">
                                        {{ $task->meet->title }}
                                    </a>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td><strong>{{ $task->title }}</strong></td>
                            <td>
                                @if ($task->project)
                                    <span class="badge badge-info">{{ $task->project->project_name }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $priorityClass = match($task->priority) {
                                        'High' => 'badge-danger',
                                        'Medium' => 'badge-warning',
                                        'Low' => 'badge-info',
                                        default => 'badge-secondary',
                                    };
                                @endphp
                                <span class="badge {{ $priorityClass }}">{{ $task->priority ?? 'N/A' }}</span>
                            </td>
                            <td>
                                @php
                                    $statusClass = match($task->status) {
                                        'Completed' => 'badge-success',
                                        'In Progress' => 'badge-primary',
                                        'Pending' => 'badge-warning',
                                        default => 'badge-secondary',
                                    };
                                @endphp
                                <span class="badge {{ $statusClass }}">{{ $task->status ?? 'N/A' }}</span>
                            </td>
                            <td>{{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('d M Y') : '-' }}</td>
                            <td>{{ $task->created_at ? \Carbon\Carbon::parse($task->created_at)->format('d M Y') : '-' }}</td>
                            <td class="text-center">
                                @if ($task->meet)
                                    <a href="{{ route('meets.details', $task->meet->id) }}" class="btn btn-icon btn-info btn-sm" data-toggle="tooltip" title="View Meeting" aria-label="View meeting: {{ $task->meet->title }}">
                                        <i class="mdi mdi-eye" aria-hidden="true"></i>
                                    </a>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </x-datatable>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Project Tabs -->
    @if ($assignedProjects->count() > 0)
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <ul class="nav nav-tabs mb-3 flex-row" id="projectTabs" role="tablist">
                        @foreach ($assignedProjects as $index => $project)
                        @php
                            $projectTypeLabel = $project->project_type == 1 ? 'Streetlight' : 'Rooftop';
                        @endphp
                        <li class="nav-item" role="presentation">
                            <a class="nav-link {{ $index === 0 ? 'active' : '' }}" id="project-{{ $project->id }}-tab"
                               data-bs-toggle="tab" href="#project-{{ $project->id }}" role="tab"
                               aria-controls="project-{{ $project->id }}"
                               aria-selected="{{ $index === 0 ? 'true' : 'false' }}"
                               title="{{ $project->project_name }} · {{ $projectTypeLabel }} · Project #{{ $project->id }}"
                               aria-label="{{ $project->project_name }}, {{ $projectTypeLabel }}, Project #{{ $project->id }}">
                                <span class="project-tab-name">{{ $project->project_name }}</span>
                                <span class="badge badge-light text-dark border ml-1">#{{ $project->id }}</span>
                                <span class="badge badge-info ml-1">{{ $projectTypeLabel }}</span>
                            </a>
                        </li>
                        @endforeach
                    </ul>

                    <div class="tab-content" id="projectTabContent">
                        @foreach ($assignedProjects as $index => $project)
                        <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}" id="project-{{ $project->id }}" role="tabpanel" aria-labelledby="project-{{ $project->id }}-tab">
                            @php
                                $isStreetlight = $project->project_type == 1;
                            @endphp

                            <!-- Project Summary Cards -->
                            <div class="row mb-4">
                                @if ($isStreetlight)
                                    @php
                                        $streetlightData = $streetlightDataByProject[$project->id] ?? null;
                                    @endphp
                                    @if ($streetlightData)
                                    <div class="col-md-4">
                                        <div class="card project-summary-card">
                                            <div class="card-body">
                                                <p class="text-muted mb-1 small fw-semibold text-uppercase metric-label">Total Poles</p>
                                                <p class="mb-0 fs-3 fw-semibold metric-value">{{ $streetlightData['total_poles'] }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card project-summary-card">
                                            <div class="card-body">
                                                <p class="text-muted mb-1 small fw-semibold text-uppercase metric-label">Surveyed Poles</p>
                                                <p class="mb-0 fs-3 fw-semibold text-success metric-value">{{ $streetlightData['surveyed_poles'] }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card project-summary-card">
                                            <div class="card-body">
                                                <p class="text-muted mb-1 small fw-semibold text-uppercase metric-label">Installed Poles</p>
                                                <p class="mb-0 fs-3 fw-semibold text-primary metric-value">{{ $streetlightData['installed_poles'] }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                @else
                                    @php
                                        $rooftopData = $rooftopDataByProject[$project->id] ?? null;
                                    @endphp
                                    @if ($rooftopData)
                                    <div class="col-md-6">
                                        <div class="card project-summary-card">
                                            <div class="card-body">
                                                <p class="text-muted mb-1 small fw-semibold text-uppercase metric-label">Total Sites</p>
                                                <p class="mb-0 fs-3 fw-semibold metric-value">{{ $rooftopData['total_sites'] }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card project-summary-card">
                                            <div class="card-body">
                                                <p class="text-muted mb-1 small fw-semibold text-uppercase metric-label">Completed Sites</p>
                                                <p class="mb-0 fs-3 fw-semibold text-success metric-value">{{ $rooftopData['completed_sites'] }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                @endif
                            </div>

                            <!-- Streetlight Projects DataTable -->
                            @if ($isStreetlight && isset($streetlightDataByProject[$project->id]))
                                @php $streetlightData = $streetlightDataByProject[$project->id]; @endphp
                                <x-datatable id="streetlightTable-{{ $project->id }}" 
                                    title="Streetlight Sites - {{ $project->project_name }}" 
                                    :columns="[
                                        ['title' => '#', 'width' => '5%'],
                                        ['title' => 'State', 'width' => '10%'],
                                        ['title' => 'District', 'width' => '12%'],
                                        ['title' => 'Block', 'width' => '12%'],
                                        ['title' => 'Panchayat', 'width' => '12%'],
                                        ['title' => 'Ward', 'width' => '15%'],
                                        ['title' => 'Total Poles', 'width' => '10%'],
                                        ['title' => 'Surveyed', 'width' => '10%'],
                                        ['title' => 'Installed', 'width' => '10%'],
                                    ]" 
                                    :exportEnabled="true" 
                                    :importEnabled="false" 
                                    :bulkDeleteEnabled="false"
                                    pageLength="25" 
                                    searchPlaceholder="Search Sites...">
                                    @foreach ($streetlightData['sites'] as $site)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $site['state'] ?? '-' }}</td>
                                        <td>{{ $site['district'] ?? '-' }}</td>
                                        <td>{{ $site['block'] ?? '-' }}</td>
                                        <td>{{ $site['panchayat'] ?? '-' }}</td>
                                        <td style="min-width: 180px; overflow: visible !important;">
                                            @if ($site['ward'])
                                                @php
                                                    $wards = array_filter(array_map('trim', explode(',', $site['ward'])));
                                                    $routeParams = ['project_id' => $project->id, 'panchayat' => $site['panchayat'], 'ward' => ''];
                                                    if ($staff->role == \App\Enums\UserRole::SITE_ENGINEER->value) {
                                                        $routeParams['site_engineer'] = $staff->id;
                                                        $routeParams['role'] = 1;
                                                    } elseif ($staff->role == \App\Enums\UserRole::PROJECT_MANAGER->value) {
                                                        $routeParams['project_manager'] = $staff->id;
                                                        $routeParams['role'] = 1;
                                                    } elseif ($staff->role == \App\Enums\UserRole::VENDOR->value) {
                                                        $routeParams['vendor'] = $staff->id;
                                                        $routeParams['role'] = 1;
                                                    }
                                                @endphp
                                                <div class="ward-container">
                                                    @foreach ($wards as $ward)
                                                        @php $routeParams['ward'] = $ward; @endphp
                                                        <a href="{{ route('installed.poles', $routeParams) }}" 
                                                           class="ward-badge" 
                                                           title="View installed poles for Ward {{ $ward }}, {{ $site['panchayat'] }}, {{ $site['district'] ?? 'district not set' }}"
                                                           aria-label="Ward {{ $ward }}, {{ $site['panchayat'] }}, {{ $site['district'] ?? 'district not set' }}. View installed poles.">
                                                            <i class="mdi mdi-map-marker" aria-hidden="true"></i> Ward {{ $ward }}
                                                        </a>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td><strong class="text-primary">{{ $site['total_poles'] }}</strong></td>
                                        <td style="white-space: nowrap;">
                                            @php
                                                $routeParams = ['project_id' => $project->id, 'panchayat' => $site['panchayat']];
                                                if ($staff->role == \App\Enums\UserRole::SITE_ENGINEER->value) {
                                                    $routeParams['site_engineer'] = $staff->id;
                                                    $routeParams['role'] = 1;
                                                } elseif ($staff->role == \App\Enums\UserRole::PROJECT_MANAGER->value) {
                                                    $routeParams['project_manager'] = $staff->id;
                                                    $routeParams['role'] = 1;
                                                } elseif ($staff->role == \App\Enums\UserRole::VENDOR->value) {
                                                    $routeParams['vendor'] = $staff->id;
                                                    $routeParams['role'] = 1;
                                                }
                                            @endphp
                                            <a href="{{ route('surveyed.poles', $routeParams) }}" 
                                               class="count-badge surveyed-badge" 
                                               title="View {{ $site['surveyed_poles_count'] }} surveyed poles for {{ $site['panchayat'] }}">
                                                <i class="mdi mdi-check-circle"></i>
                                                <span>{{ $site['surveyed_poles_count'] }}</span>
                                            </a>
                                        </td>
                                        <td style="white-space: nowrap;">
                                            <a href="{{ route('installed.poles', $routeParams) }}" 
                                               class="count-badge installed-badge" 
                                               title="View {{ $site['installed_poles_count'] }} installed poles for {{ $site['panchayat'] }}">
                                                <i class="mdi mdi-lightning-bolt"></i>
                                                <span>{{ $site['installed_poles_count'] }}</span>
                                            </a>
                                        </td>
                                        <td class="text-center" style="white-space: nowrap;">
                                            <div class="action-buttons">
                                                <a href="{{ route('installed.poles', $routeParams) }}" 
                                                   class="btn btn-icon btn-sm btn-info" data-toggle="tooltip" title="View Poles"
                                                   aria-label="View installed poles for {{ $site['panchayat'] }}">
                                                    <i class="mdi mdi-eye" aria-hidden="true"></i>
                                                </a>
                                                <div class="btn-group staff-row-actions">
                                                    <button type="button"
                                                        class="btn btn-icon btn-sm btn-outline-secondary dropdown-toggle staff-row-actions-toggle"
                                                        data-bs-toggle="dropdown"
                                                        data-bs-boundary="viewport"
                                                        data-bs-display="static"
                                                        aria-expanded="false"
                                                        data-toggle="tooltip"
                                                        title="More actions"
                                                        aria-label="More actions for {{ $site['panchayat'] }}">
                                                        <i class="mdi mdi-dots-vertical" aria-hidden="true"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end staff-row-actions-menu">
                                                        <li>
                                                            <button type="button"
                                                                class="dropdown-item push-rms-btn"
                                                                data-project-id="{{ $project->id }}"
                                                                data-panchayat="{{ $site['panchayat'] }}">
                                                                <i class="mdi mdi-cloud-upload me-2 text-muted" aria-hidden="true"></i>
                                                                Push to RMS
                                                            </button>
                                                        </li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <button type="button"
                                                                class="dropdown-item text-danger delete-panchayat-btn"
                                                                data-project-id="{{ $project->id }}"
                                                                data-panchayat="{{ $site['panchayat'] }}">
                                                                <i class="mdi mdi-delete me-2" aria-hidden="true"></i>
                                                                Delete panchayat
                                                            </button>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </x-datatable>
                            @endif

                            <!-- Rooftop Projects DataTable -->
                            @if (!$isStreetlight && isset($rooftopDataByProject[$project->id]))
                                @php $rooftopData = $rooftopDataByProject[$project->id]; @endphp
                                <x-datatable id="rooftopTable-{{ $project->id }}" 
                                    title="Rooftop Sites - {{ $project->project_name }}" 
                                    :columns="[
                                        ['title' => '#', 'width' => '5%'],
                                        ['title' => 'BREDA SL No', 'width' => '12%'],
                                        ['title' => 'Site Name', 'width' => '20%'],
                                        ['title' => 'Location', 'width' => '20%'],
                                        ['title' => 'District', 'width' => '12%'],
                                        ['title' => 'State', 'width' => '10%'],
                                        ['title' => 'Status', 'width' => '10%'],
                                        ['title' => 'Commissioning Date', 'width' => '11%'],
                                    ]" 
                                    :exportEnabled="true" 
                                    :importEnabled="false" 
                                    :bulkDeleteEnabled="false"
                                    pageLength="25" 
                                    searchPlaceholder="Search Sites...">
                                    @foreach ($rooftopData['sites'] as $site)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $site['breda_sl_no'] ?? '-' }}</td>
                                        <td><strong>{{ $site['site_name'] ?? '-' }}</strong></td>
                                        <td>{{ $site['location'] ?? '-' }}</td>
                                        <td>{{ $site['district'] }}</td>
                                        <td>{{ $site['state'] }}</td>
                                        <td>
                                            @if ($site['task'])
                                                @php
                                                    $status = $site['task']->status;
                                                    $badgeClass = match($status) {
                                                        'Completed' => 'badge-success',
                                                        'In Progress' => 'badge-warning',
                                                        'Pending' => 'badge-info',
                                                        'Blocked' => 'badge-danger',
                                                        default => 'badge-secondary',
                                                    };
                                                @endphp
                                                <span class="badge {{ $badgeClass }}">{{ $status }}</span>
                                            @else
                                                <span class="badge badge-secondary">{{ $site['installation_status'] ?? 'N/A' }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $site['commissioning_date'] ? \Carbon\Carbon::parse($site['commissioning_date'])->format('d M Y') : '-' }}</td>
                                        <td class="text-center">
                                            @if ($site['task'])
                                                <a href="{{ route('tasks.show', $site['task']->id) }}" class="btn btn-icon btn-info" data-toggle="tooltip" title="View Task"
                                                   aria-label="View task for {{ $site['site_name'] ?? 'site' }}">
                                                    <i class="mdi mdi-eye" aria-hidden="true"></i>
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </x-datatable>
                            @endif

                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="row">
        <div class="col-12">
            <div class="alert alert-warning">
                <i class="mdi mdi-alert"></i> No projects assigned to this staff member.
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@push("styles")
<style>
    /* One corner scale for this page: avoids pill Edit vs soft-square badges vs sharp card */
    .staff-profile-show {
        --staff-surface-radius: 0.5rem;
    }

    /* Top summary strip: same card height per row; labels share one line height on sm+ */
    .staff-profile-show .staff-metric-cards-row .metric-label {
        min-height: 2.25rem;
        line-height: 1.2;
        display: flex;
        align-items: flex-end;
    }

    @media (min-width: 768px) {
        .staff-profile-show .staff-metric-nowrap-label {
            white-space: nowrap;
        }
    }

    .staff-profile-show .card {
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        border: none;
        border-radius: var(--staff-surface-radius);
        margin-bottom: 1rem;
    }
    
    /* Basic Details Section Styles */
    .staff-avatar-wrapper {
        position: relative;
        width: 64px;
        height: 64px;
        flex-shrink: 0;
        border-radius: 50%;
        overflow: hidden;
        border: 2px solid #e9ecef;
        background: #f1f3f5;
    }
    
    .staff-avatar {
        display: block;
        width: 100%;
        height: 100%;
        max-width: none;
        object-fit: cover;
        object-position: center;
        border: none;
        border-radius: 50%;
    }
    
    .avatar-change-btn {
        position: absolute;
        bottom: 0;
        right: 0;
        width: 24px;
        height: 24px;
        background: #007bff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        border: 2px solid white;
        color: white;
        font-size: 12px;
        transition: all 0.2s ease;
    }
    
    .avatar-change-btn:hover {
        background: #0056b3;
        transform: scale(1.1);
    }

    .avatar-change-btn:focus-visible {
        outline: 2px solid #0d6efd;
        outline-offset: 2px;
    }
    
    .staff-name {
        font-size: 1.1rem;
        font-weight: 600;
        color: #212529;
    }
    
    .staff-email {
        font-size: 0.85rem;
    }
    
    .info-group {
        margin-bottom: 1rem;
    }
    
    .info-label {
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 0.7rem;
    }
    
    .info-item {
        display: flex;
        align-items: center;
        font-size: 0.875rem;
        color: #495057;
    }

    .staff-profile-show .staff-banking-toggle {
        min-height: 38px;
        border-radius: var(--staff-surface-radius);
    }

    .staff-profile-show .staff-banking-panel {
        border: 1px solid #e9ecef;
        border-radius: var(--staff-surface-radius);
        background: #f8f9fa;
        padding: 0.75rem;
    }
    
    .staff-profile-show .edit-staff-btn {
        border-width: 1px;
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
        border-radius: var(--staff-surface-radius);
    }
    
    .staff-profile-show .edit-staff-btn:hover {
        border-width: 1px;
    }
    
    .project-summary-card {
        background: #f8f9fa;
    }
    
    /* Override template's .nav (position:fixed, max-width:220px) - ensure horizontal layout */
    #projectTabs.nav.nav-tabs {
        position: static !important;
        display: flex !important;
        flex-direction: row !important;
        flex-wrap: wrap !important;
        max-width: none !important;
        width: 100% !important;
        border-bottom: 2px solid #dee2e6;
        margin-bottom: 1.5rem;
    }
    
    #projectTabs.nav.nav-tabs .nav-item {
        margin-bottom: -2px;
        flex-shrink: 0;
    }
    
    .nav-tabs .nav-link {
        border: none;
        border-bottom: 2px solid transparent;
        color: #6c757d;
        padding: 0.75rem 1.25rem;
        transition: all 0.3s ease;
    }

    .staff-profile-show .project-tab-name {
        display: inline-block;
        max-width: 18rem;
        overflow: hidden;
        text-overflow: ellipsis;
        vertical-align: bottom;
        white-space: nowrap;
    }
    
    .nav-tabs .nav-link:hover {
        border-bottom-color: #007bff;
        color: #007bff;
    }
    
    .nav-tabs .nav-link.active {
        border-bottom-color: #007bff;
        color: #007bff;
        font-weight: 600;
        background-color: transparent;
    }
    
    .staff-profile-show .badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        border-radius: var(--staff-surface-radius);
    }
    
    /* Ward column styling */
    .ward-container {
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
        width: 100%;
        align-items: flex-start;
        line-height: 1.6;
        min-height: 28px;
    }
    
    .ward-badge {
        display: inline-block;
        align-items: center;
        padding: 4px 8px;
        background-color: #e7f3ff;
        color: #0066cc;
        border: 1px solid #b3d9ff;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.2s ease;
        white-space: nowrap;
        margin: 2px;
        line-height: 1.5;
    }

    .ward-badge:focus-visible,
    .count-badge:focus-visible,
    .staff-profile-show .btn-icon:focus-visible {
        outline: 2px solid #0d6efd;
        outline-offset: 2px;
    }
    
    .ward-badge:hover {
        background-color: #0066cc;
        color: white;
        border-color: #0066cc;
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,102,204,0.3);
        text-decoration: none;
    }
    
    .ward-badge i {
        font-size: 0.7rem;
        margin-right: 3px;
    }
    
    /* Surveyed and Installed badges */
    .count-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 8px 12px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.875rem;
        text-decoration: none;
        transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        min-width: 60px;
    }
    
    .surveyed-badge {
        background: #1c7430;
        color: #f8f9fa;
        border: none;
    }
    
    .surveyed-badge:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 6px rgba(28, 116, 48, 0.35);
        text-decoration: none;
        color: #f8f9fa;
    }
    
    .installed-badge {
        background: #0b5ed7;
        color: #f8f9fa;
        border: none;
    }
    
    .installed-badge:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 6px rgba(11, 94, 215, 0.35);
        text-decoration: none;
        color: #f8f9fa;
    }
    
    .count-badge i {
        font-size: 1rem;
    }
    
    .count-badge span {
        font-size: 0.9rem;
    }
    
    /* Action buttons */
    .action-buttons {
        display: inline-flex;
        gap: 4px;
        align-items: center;
        position: relative;
    }

    .staff-profile-show .staff-row-actions .dropdown-toggle::after {
        display: none;
    }

    .staff-profile-show .staff-row-actions-menu {
        min-width: 12rem;
        z-index: 1060;
    }

    .staff-profile-show .staff-row-actions-menu .dropdown-item {
        display: flex;
        align-items: center;
        min-height: 44px;
        padding: 0.5rem 0.75rem;
    }

    .staff-profile-show .staff-row-actions-menu .dropdown-item.text-danger:focus,
    .staff-profile-show .staff-row-actions-menu .dropdown-item.text-danger:hover {
        background-color: #f8d7da;
        color: #842029;
    }
    
    .staff-profile-show .btn-icon {
        min-width: 44px;
        min-height: 44px;
        width: auto;
        height: auto;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
    }
    
    /* Ensure table cells don't overflow */
    .datatable-wrapper table td {
        vertical-align: middle;
        word-wrap: break-word;
        position: relative;
    }
    
    /* Ward column specific styling - prevent overflow */
    .datatable-wrapper table td .ward-container {
        overflow: visible !important;
        width: 100%;
    }

    .staff-profile-show .dataTables_wrapper .dt-buttons,
    .staff-profile-show .datatable-wrapper .dt-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-bottom: 0.75rem;
    }

    .staff-profile-show .dataTables_wrapper .dt-button,
    .staff-profile-show .datatable-wrapper .dt-button {
        min-height: 38px;
        border-radius: var(--staff-surface-radius) !important;
    }
    
    /* Ensure ward column can expand */
    .datatable-wrapper table th:nth-child(6),
    .datatable-wrapper table td:nth-child(6) {
        min-width: 180px !important;
        max-width: 300px !important;
        overflow: visible !important;
    }
    
    /* Ensure count badges and action buttons don't wrap */
    .datatable-wrapper table td:has(.count-badge),
    .datatable-wrapper table td:has(.action-buttons) {
        white-space: nowrap;
        text-align: center;
        overflow: visible !important;
    }
    
    /* Override DataTables width constraints for ward column */
    .datatable-wrapper table th:nth-child(6),
    .datatable-wrapper table td:nth-child(6) {
        min-width: 180px !important;
        max-width: 350px !important;
        overflow: visible !important;
        width: auto !important;
    }
    
    /* Specific fix for streetlight tables */
    .datatable-wrapper[id*="streetlightTable"] table th:nth-child(6),
    .datatable-wrapper[id*="streetlightTable"] table td:nth-child(6) {
        min-width: 200px !important;
        max-width: 400px !important;
    }
    
    /* Ensure ward badges wrap properly */
    .ward-container {
        width: 100%;
        min-height: 30px;
    }

    @media (max-width: 767.98px) {
        .staff-profile-show .project-tab-name {
            max-width: 12rem;
        }

        #projectTabs.nav.nav-tabs {
            overflow-x: auto;
            flex-wrap: nowrap !important;
            padding-bottom: 0.25rem;
        }

        .staff-profile-show .dataTables_wrapper .dt-buttons,
        .staff-profile-show .datatable-wrapper .dt-buttons {
            width: 100%;
        }

        .staff-profile-show .dataTables_wrapper .dt-button,
        .staff-profile-show .datatable-wrapper .dt-button {
            min-height: 44px;
            flex: 1 1 calc(50% - 0.5rem);
        }

        .staff-profile-show .datatable-wrapper {
            overflow-x: auto;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        var projectTabList = document.getElementById('projectTabs');
        if (projectTabList) {
            projectTabList.addEventListener('shown.bs.tab', function () {
                projectTabList.querySelectorAll('[role="tab"]').forEach(function (t) {
                    t.setAttribute('aria-selected', t.classList.contains('active') ? 'true' : 'false');
                });
            });
        }

        function escapeHtml(text) {
            return $('<div>').text(text || '').html();
        }

        function pollBatchStatus(statusUrl, onComplete) {
            const pollIntervalMs = 3000;
            const timer = setInterval(() => {
                $.get(statusUrl)
                    .done(function(statusResponse) {
                        const processed = statusResponse.processed_poles || 0;
                        const total = statusResponse.total_poles || 0;
                        const success = statusResponse.success_count || 0;
                        const error = statusResponse.error_count || 0;
                        const status = statusResponse.status || 'queued';

                        Swal.update({
                            title: 'RMS Sync In Progress',
                            html: `<p>Status: <strong>${status}</strong></p>
                                   <p>Processed: <strong>${processed}</strong> / ${total}</p>
                                   <p>Success: <strong>${success}</strong> | Errors: <strong>${error}</strong></p>`,
                            allowOutsideClick: false
                        });

                        if (status === 'completed' || status === 'failed') {
                            clearInterval(timer);
                            onComplete(statusResponse);
                        }
                    })
                    .fail(function() {
                        clearInterval(timer);
                        Swal.fire('Sync Monitor Error', 'Could not fetch RMS sync status.', 'warning');
                    });
            }, pollIntervalMs);
        }

        // Avatar upload functionality
        $('#avatarInput').on('change', function(e) {
            var file = e.target.files[0];
            if (file) {
                var formData = new FormData();
                formData.append('image', file);
                formData.append('_token', '{{ csrf_token() }}');

                // Show loading state
                var $avatar = $('#staffAvatar');
                var originalSrc = $avatar.attr('src');
                $avatar.css('opacity', '0.5');

                $.ajax({
                    url: '{{ route("staff.uploadAvatar", $staff->id) }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.image_url) {
                            $avatar.attr('src', response.image_url);
                            $avatar.css('opacity', '1');
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: 'Profile picture updated successfully!',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        }
                    },
                    error: function(xhr) {
                        $avatar.css('opacity', '1');
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.message || 'Failed to upload image. Please try again.'
                        });
                    }
                });
            }
        });

        // Initialize tooltips
        $('[data-toggle="tooltip"]').tooltip();

        // Fix ward column width after DataTables initialization
        setTimeout(function() {
            $('[id^="streetlightTable-"]').each(function() {
                const tableId = '#' + $(this).attr('id');
                if ($.fn.DataTable.isDataTable(tableId)) {
                    const table = $(tableId).DataTable();
                    // Adjust ward column (6th column, index 5) width
                    table.column(5).visible(true);
                    setTimeout(function() {
                        table.columns.adjust().draw(false);
                        // Force ward column to have proper width
                        $(tableId + '_wrapper table th:nth-child(6), ' + tableId + '_wrapper table td:nth-child(6)').css({
                            'min-width': '200px',
                            'max-width': '400px',
                            'overflow': 'visible',
                            'width': 'auto'
                        });
                    }, 500);
                }
            });
        }, 1000);

        // Intercept Excel export for streetlight tables - redirect to custom export endpoint
        @foreach ($assignedProjects as $project)
            @if ($project->project_type == 1)
                $(document).ready(function() {
                    // Wait for DataTables to initialize
                    setTimeout(function() {
                        const excelButtonId = '#streetlightTable-{{ $project->id }}_excel';
                        $(excelButtonId).off('click').on('click', function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            
                            // Redirect to custom export endpoint
                            window.location.href = '{{ route("staff.exportStreetlight", ["staffId" => $staff->id, "projectId" => $project->id]) }}';
                        });
                    }, 1500);
                });
            @endif
        @endforeach

        // Delete Panchayat handler (type panchayat name to confirm)
        $(document).on('click', '.delete-panchayat-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const projectId = $(this).data('project-id');
            const panchayat = $(this).attr('data-panchayat') || '';
            const safePanchayat = escapeHtml(panchayat);

            Swal.fire({
                title: 'Delete panchayat permanently?',
                html: `This will permanently delete all entries for <strong>${safePanchayat}</strong>.<br><br>
                       Consumed inventory will return to dispatched state and pole counts will be reset.<br><br>
                       Type <strong>${safePanchayat}</strong> below to confirm.`,
                icon: 'warning',
                input: 'text',
                inputPlaceholder: 'Type panchayat name exactly',
                inputAttributes: {
                    autocapitalize: 'off',
                    autocorrect: 'off',
                    spellcheck: 'false',
                },
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Delete permanently',
                cancelButtonText: 'Cancel',
                focusCancel: true,
                showLoaderOnConfirm: true,
                preConfirm: (typedName) => {
                    const value = (typedName || '').trim();
                    if (value !== panchayat.trim()) {
                        Swal.showValidationMessage('Panchayat name must match exactly.');
                        return false;
                    }

                    const url = '/staff/projects/' + projectId + '/panchayat/' + encodeURIComponent(panchayat) + '/delete';
                    return $.ajax({
                        url: url,
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        error: function(xhr) {
                            throw new Error(xhr.responseJSON?.message || 'Failed to delete panchayat');
                        }
                    });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    Swal.fire({
                        title: 'Deleted!',
                        html: result.value.message || 'Panchayat deleted successfully.',
                        icon: 'success',
                        timer: 3000
                    }).then(() => {
                        location.reload();
                    });
                }
            }).catch((error) => {
                Swal.fire({
                    title: 'Error!',
                    text: error.message || 'Failed to delete panchayat.',
                    icon: 'error'
                });
            });
        });

        // Push to RMS handler (type PUSH to confirm)
        $(document).on('click', '.push-rms-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const projectId = $(this).data('project-id');
            const panchayat = $(this).attr('data-panchayat') || '';
            const safePanchayat = escapeHtml(panchayat);

            Swal.fire({
                title: 'Queue RMS sync?',
                html: `Queue an RMS sync for all installed poles in <strong>${safePanchayat}</strong>.<br><br>
                       Type <strong>PUSH</strong> below to confirm.`,
                icon: 'warning',
                input: 'text',
                inputPlaceholder: 'Type PUSH',
                inputAttributes: {
                    autocapitalize: 'characters',
                    autocorrect: 'off',
                    spellcheck: 'false',
                },
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Queue sync',
                cancelButtonText: 'Cancel',
                focusCancel: true,
                showLoaderOnConfirm: true,
                preConfirm: (typedValue) => {
                    if ((typedValue || '').trim().toUpperCase() !== 'PUSH') {
                        Swal.showValidationMessage('Type PUSH to confirm.');
                        return false;
                    }

                    const url = '/staff/projects/' + projectId + '/panchayat/' + encodeURIComponent(panchayat) + '/push-rms';
                    return $.ajax({
                        url: url,
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        error: function(xhr) {
                            throw new Error(xhr.responseJSON?.message || 'Failed to push to RMS');
                        }
                    });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    Swal.fire({
                        title: 'Sync Queued',
                        html: `Batch ID: <code>${result.value.data.batch_id}</code>`,
                        icon: 'info',
                        showConfirmButton: false,
                        allowOutsideClick: false
                    });

                    pollBatchStatus(result.value.data.status_url, function(finalStatus) {
                        Swal.fire({
                            title: finalStatus.status === 'completed' ? 'Push Complete!' : 'Push Failed',
                            html: `Successfully pushed: <strong>${finalStatus.success_count || 0}</strong><br>
                                   Errors: <strong>${finalStatus.error_count || 0}</strong>`,
                            icon: finalStatus.status === 'completed' ? 'success' : 'error',
                            timer: 4000
                        });
                    });
                }
            }).catch((error) => {
                Swal.fire({
                    title: 'Error!',
                    text: error.message || 'Failed to push to RMS.',
                    icon: 'error'
                });
            });
        });
    });
</script>
@endpush
