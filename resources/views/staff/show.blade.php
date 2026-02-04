@extends("layouts.main")

@section("content")
<div class="content-wrapper p-2">
    <!-- Basic Details Section -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center">
                            <div class="staff-avatar-wrapper position-relative me-3">
                                <img src="{{ $staff->image ?? asset('images/faces/face8.jpg') }}" 
                                     alt="{{ $staff->name }}" 
                                     class="staff-avatar rounded-circle"
                                     id="staffAvatar">
                                <label for="avatarInput" class="avatar-change-btn" title="Change Photo">
                                    <i class="mdi mdi-camera"></i>
                                </label>
                                <input type="file" id="avatarInput" accept="image/*" style="display: none;">
                            </div>
                            <div>
                                <h5 class="mb-1 staff-name">{{ $staff->name }}</h5>
                                <p class="text-muted mb-0 small staff-email">{{ $staff->email }}</p>
                            </div>
                        </div>
                        <a href="{{ route('staff.edit', $staff->id) }}" class="btn btn-sm btn-outline-warning edit-staff-btn">
                            <i class="mdi mdi-pencil"></i> Edit
                        </a>
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
                                    <span class="badge badge-primary">{{ \App\Enums\UserRole::fromValue($staff->role)->label() }}</span>
                                </div>
                            </div>
                            
                            @if ($staff->bankName || $staff->accountNumber)
                            <div class="info-group mt-3">
                                <div class="info-label small text-muted mb-1">Banking</div>
                                <div class="info-item">
                                    <span class="small">{{ $staff->bankName ?? 'N/A' }}</span>
                                </div>
                                @if ($staff->accountNumber)
                                <div class="info-item mt-1">
                                    <span class="small text-muted">A/C:</span>
                                    <span class="small ms-1">{{ $staff->accountNumber }}</span>
                                </div>
                                @endif
                                @if ($staff->ifsc)
                                <div class="info-item mt-1">
                                    <span class="small text-muted">IFSC:</span>
                                    <span class="small ms-1">{{ $staff->ifsc }}</span>
                                </div>
                                @endif
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Total Tasks</h6>
                            <h3 class="mb-0">{{ $totalTasksCount }}</h3>
                        </div>
                        <div class="text-primary">
                            <i class="mdi mdi-clipboard-list mdi-36px"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Completed</h6>
                            <h3 class="mb-0 text-success">{{ $completedTasksCount }}</h3>
                        </div>
                        <div class="text-success">
                            <i class="mdi mdi-check-circle mdi-36px"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Pending</h6>
                            <h3 class="mb-0 text-warning">{{ $pendingTasksCount }}</h3>
                        </div>
                        <div class="text-warning">
                            <i class="mdi mdi-clock-outline mdi-36px"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Meeting Tasks</h6>
                            <h3 class="mb-0 text-info">{{ $meetingTasksSummary['total'] ?? 0 }}</h3>
                        </div>
                        <div class="text-info">
                            <i class="mdi mdi-calendar-check mdi-36px"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Meeting Tasks Section -->
    @if ($meetingTasks && $meetingTasks->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-3">Meeting Related Tasks</h5>
                    
                    <!-- Meeting Tasks Summary Cards -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h6 class="text-muted mb-1">Total Assigned</h6>
                                    <h4 class="mb-0 text-info">{{ $meetingTasksSummary['total'] ?? 0 }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h6 class="text-muted mb-1">Completed</h6>
                                    <h4 class="mb-0 text-success">{{ $meetingTasksSummary['completed'] ?? 0 }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h6 class="text-muted mb-1">In Progress</h6>
                                    <h4 class="mb-0 text-primary">{{ $meetingTasksSummary['in_progress'] ?? 0 }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h6 class="text-muted mb-1">Pending</h6>
                                    <h4 class="mb-0 text-warning">{{ $meetingTasksSummary['pending'] ?? 0 }}</h4>
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
                                    <a href="{{ route('meets.details', $task->meet->id) }}" class="btn btn-icon btn-info btn-sm" data-toggle="tooltip" title="View Meeting">
                                        <i class="mdi mdi-eye"></i>
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
                        <li class="nav-item">
                            <a class="nav-link {{ $index === 0 ? 'active' : '' }}" id="project-{{ $project->id }}-tab" 
                               data-bs-toggle="tab" href="#project-{{ $project->id }}" role="tab" aria-controls="project-{{ $project->id }}">
                                {{ $project->project_name }}
                                <span class="badge badge-info ml-1">{{ $project->project_type == 1 ? 'Streetlight' : 'Rooftop' }}</span>
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
                                                <h6 class="text-muted mb-1">Total Poles</h6>
                                                <h3 class="mb-0">{{ $streetlightData['total_poles'] }}</h3>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card project-summary-card">
                                            <div class="card-body">
                                                <h6 class="text-muted mb-1">Surveyed Poles</h6>
                                                <h3 class="mb-0 text-success">{{ $streetlightData['surveyed_poles'] }}</h3>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card project-summary-card">
                                            <div class="card-body">
                                                <h6 class="text-muted mb-1">Installed Poles</h6>
                                                <h3 class="mb-0 text-primary">{{ $streetlightData['installed_poles'] }}</h3>
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
                                                <h6 class="text-muted mb-1">Total Sites</h6>
                                                <h3 class="mb-0">{{ $rooftopData['total_sites'] }}</h3>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card project-summary-card">
                                            <div class="card-body">
                                                <h6 class="text-muted mb-1">Completed Sites</h6>
                                                <h3 class="mb-0 text-success">{{ $rooftopData['completed_sites'] }}</h3>
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
                                                           title="View installed poles for Ward {{ $ward }}">
                                                            <i class="mdi mdi-map-marker"></i> {{ $ward }}
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
                                                   class="btn btn-icon btn-sm btn-info" data-toggle="tooltip" title="View Poles">
                                                    <i class="mdi mdi-eye"></i>
                                                </a>
                                                <button type="button" 
                                                    class="btn btn-icon btn-sm btn-danger delete-panchayat-btn" 
                                                    data-project-id="{{ $project->id }}"
                                                    data-panchayat="{{ $site['panchayat'] }}"
                                                    data-toggle="tooltip" 
                                                    title="Delete Panchayat">
                                                    <i class="mdi mdi-delete"></i>
                                                </button>
                                                <button type="button" 
                                                    class="btn btn-icon btn-sm btn-warning push-rms-btn" 
                                                    data-project-id="{{ $project->id }}"
                                                    data-panchayat="{{ $site['panchayat'] }}"
                                                    data-toggle="tooltip" 
                                                    title="Push to RMS">
                                                    <i class="mdi mdi-cloud-upload"></i>
                                                </button>
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
                                                <a href="{{ route('tasks.show', $site['task']->id) }}" class="btn btn-icon btn-info" data-toggle="tooltip" title="View Task">
                                                    <i class="mdi mdi-eye"></i>
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
    .card {
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        border: none;
        border-radius: 0.375rem;
        margin-bottom: 1rem;
    }
    
    /* Basic Details Section Styles */
    .staff-avatar-wrapper {
        position: relative;
    }
    
    .staff-avatar {
        width: 64px;
        height: 64px;
        object-fit: cover;
        border: 2px solid #e9ecef;
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
    
    .edit-staff-btn {
        border-width: 1px;
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
    }
    
    .edit-staff-btn:hover {
        border-width: 1px;
    }
    
    .project-summary-card {
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
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
    
    .badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
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
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
        border: none;
    }
    
    .surveyed-badge:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(40,167,69,0.4);
        text-decoration: none;
        color: white;
    }
    
    .installed-badge {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        color: white;
        border: none;
    }
    
    .installed-badge:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,123,255,0.4);
        text-decoration: none;
        color: white;
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
    }
    
    .btn-icon {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
    }
    
    .delete-panchayat-btn:hover {
        background-color: #c82333;
        border-color: #bd2130;
        color: white;
    }
    
    .push-rms-btn:hover {
        background-color: #e0a800;
        border-color: #d39e00;
        color: white;
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
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
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

        // Delete Panchayat handler
        $(document).on('click', '.delete-panchayat-btn', function(e) {
            e.preventDefault();
            const projectId = $(this).data('project-id');
            const panchayat = $(this).data('panchayat');
            const $btn = $(this);
            
            Swal.fire({
                title: 'Are you sure?',
                html: `This will delete all entries from <strong>${panchayat}</strong>.<br><br>
                       All consumed inventory will be returned to vendor in dispatched state.<br>
                       Surveyed and installed pole counts will be reset.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel',
                showLoaderOnConfirm: true,
                preConfirm: () => {
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

        // Push to RMS handler
        $(document).on('click', '.push-rms-btn', function(e) {
            e.preventDefault();
            const projectId = $(this).data('project-id');
            const panchayat = $(this).data('panchayat');
            const $btn = $(this);
            
            Swal.fire({
                title: 'Push to RMS?',
                html: `Push all installed poles from <strong>${panchayat}</strong> to RMS server?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#ffc107',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, push it!',
                cancelButtonText: 'Cancel',
                showLoaderOnConfirm: true,
                preConfirm: () => {
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
                    const data = result.value.data || {};
                    const successCount = data.success_count || 0;
                    const errorCount = data.error_count || 0;
                    
                    Swal.fire({
                        title: 'Push Complete!',
                        html: `Successfully pushed: <strong>${successCount}</strong><br>
                               Errors: <strong>${errorCount}</strong>`,
                        icon: successCount > 0 ? 'success' : 'warning',
                        timer: 3000
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
