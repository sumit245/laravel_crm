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
                    <ul class="nav nav-tabs mb-3" id="projectTabs" role="tablist">
                        @foreach ($assignedProjects as $index => $project)
                        <li class="nav-item">
                            <a class="nav-link {{ $index === 0 ? 'active' : '' }}" id="project-{{ $project->id }}-tab" 
                               data-toggle="tab" href="#project-{{ $project->id }}" role="tab" aria-controls="project-{{ $project->id }}">
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
                                        ['title' => 'Ward', 'width' => '10%'],
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
                                        <td>
                                            @if ($site['ward'])
                                                @php
                                                    $wards = array_filter(array_map('trim', explode(',', $site['ward'])));
                                                    $routeParams = ['project_id' => $project->id, 'ward' => ''];
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
                                                @foreach ($wards as $ward)
                                                    @php $routeParams['ward'] = $ward; @endphp
                                                    <a href="{{ route('installed.poles', $routeParams) }}" 
                                                       class="badge badge-info ward-link" title="View installed poles for Ward {{ $ward }}">
                                                        Ward {{ $ward }}
                                                    </a>
                                                @endforeach
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td><strong>{{ $site['total_poles'] }}</strong></td>
                                        <td>
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
                                               class="badge badge-success clickable-count" title="View surveyed poles for {{ $site['panchayat'] }}">
                                                {{ $site['surveyed_poles_count'] }}
                                            </a>
                                        </td>
                                        <td>
                                            <a href="{{ route('installed.poles', $routeParams) }}" 
                                               class="badge badge-primary clickable-count" title="View installed poles for {{ $site['panchayat'] }}">
                                                {{ $site['installed_poles_count'] }}
                                            </a>
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('installed.poles', $routeParams) }}" 
                                               class="btn btn-icon btn-info" data-toggle="tooltip" title="View Poles">
                                                <i class="mdi mdi-eye"></i>
                                            </a>
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
    
    .nav-tabs {
        border-bottom: 2px solid #dee2e6;
        margin-bottom: 1.5rem;
        position: relative;
    }
    
    .nav-tabs .nav-item {
        margin-bottom: -2px;
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
    
    .ward-link, .clickable-count {
        cursor: pointer;
        transition: opacity 0.2s ease;
    }
    
    .ward-link:hover, .clickable-count:hover {
        opacity: 0.8;
        text-decoration: none;
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
    });
</script>
@endpush
