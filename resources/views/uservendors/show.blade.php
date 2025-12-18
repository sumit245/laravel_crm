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
                            <div class="vendor-avatar-wrapper position-relative me-3">
                                <img src="{{ $vendor->image ?? asset('images/faces/face8.jpg') }}" 
                                     alt="{{ $vendor->name }}" 
                                     class="vendor-avatar rounded-circle"
                                     id="vendorAvatar">
                                <label for="avatarInput" class="avatar-change-btn" title="Change Photo">
                                    <i class="mdi mdi-camera"></i>
                                </label>
                                <input type="file" id="avatarInput" accept="image/*" style="display: none;">
                            </div>
                            <div>
                                <h5 class="mb-1 vendor-name">{{ $vendor->name }}</h5>
                                <p class="text-muted mb-0 small vendor-email">{{ $vendor->email }}</p>
                            </div>
                        </div>
                        <a href="{{ route('uservendors.edit', $vendor->id) }}" class="btn btn-sm btn-outline-warning edit-vendor-btn">
                            <i class="mdi mdi-pencil"></i> Edit
                        </a>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="info-group mb-3">
                                <div class="info-item">
                                    <i class="mdi mdi-phone text-muted me-2"></i>
                                    <span class="small">{{ $vendor->contactNo ?? 'N/A' }}</span>
                                </div>
                                <div class="info-item mt-2">
                                    <i class="mdi mdi-map-marker text-muted me-2"></i>
                                    <span class="small">{{ $vendor->address ?? 'N/A' }}</span>
                                </div>
                            </div>
                            
                            <div class="info-group">
                                <div class="info-label small text-muted mb-1">Team</div>
                                @if ($vendor->projectManager)
                                <div class="info-item">
                                    <i class="mdi mdi-account-tie text-muted me-2"></i>
                                    <span class="small">{{ $vendor->projectManager->firstName }} {{ $vendor->projectManager->lastName }}</span>
                                </div>
                                @endif
                                @if ($vendor->siteEngineer)
                                <div class="info-item mt-1">
                                    <i class="mdi mdi-account-hard-hat text-muted me-2"></i>
                                    <span class="small">{{ $vendor->siteEngineer->firstName }} {{ $vendor->siteEngineer->lastName }}</span>
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
                            
                            @if ($vendor->bankName || $vendor->accountNumber)
                            <div class="info-group">
                                <div class="info-label small text-muted mb-1">Banking</div>
                                <div class="info-item">
                                    <span class="small">{{ $vendor->bankName ?? 'N/A' }}</span>
                                </div>
                                @if ($vendor->accountNumber)
                                <div class="info-item mt-1">
                                    <span class="small text-muted">A/C:</span>
                                    <span class="small ms-1">{{ $vendor->accountNumber }}</span>
                                </div>
                                @endif
                                @if ($vendor->ifsc)
                                <div class="info-item mt-1">
                                    <span class="small text-muted">IFSC:</span>
                                    <span class="small ms-1">{{ $vendor->ifsc }}</span>
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
                            <h6 class="text-muted mb-1">Total Earnings</h6>
                            <h3 class="mb-0 text-primary">₹{{ number_format($totalEarnings, 2) }}</h3>
                        </div>
                        <div class="text-primary">
                            <i class="mdi mdi-currency-inr mdi-36px"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
                                $projectData = $tasksByProject[$project->id] ?? null;
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
                                
                                <!-- Earnings Card -->
                                @if (isset($earningsByProject[$project->id]))
                                <div class="col-md-12 mt-3">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6 class="text-muted mb-1">Project Earnings</h6>
                                            <h4 class="mb-0 text-primary">₹{{ number_format($earningsByProject[$project->id]['earnings'], 2) }}</h4>
                                            @if ($isStreetlight)
                                                <small class="text-muted">Based on {{ $earningsByProject[$project->id]['installed_poles'] }} installed poles @ ₹500 per pole</small>
                                            @else
                                                <small class="text-muted">Based on completed sites</small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
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
                                                @endphp
                                                @foreach ($wards as $ward)
                                                    <a href="{{ route('installed.poles', ['vendor' => $vendor->id, 'project_id' => $project->id, 'ward' => $ward]) }}" 
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
                                            <a href="{{ route('surveyed.poles', ['vendor' => $vendor->id, 'project_id' => $project->id, 'panchayat' => $site['panchayat']]) }}" 
                                               class="badge badge-success clickable-count" title="View surveyed poles for {{ $site['panchayat'] }}">
                                                {{ $site['surveyed_poles_count'] }}
                                            </a>
                                        </td>
                                        <td>
                                            <a href="{{ route('installed.poles', ['vendor' => $vendor->id, 'project_id' => $project->id, 'panchayat' => $site['panchayat']]) }}" 
                                               class="badge badge-primary clickable-count" title="View installed poles for {{ $site['panchayat'] }}">
                                                {{ $site['installed_poles_count'] }}
                                            </a>
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('installed.poles', ['vendor' => $vendor->id, 'project_id' => $project->id, 'panchayat' => $site['panchayat']]) }}" 
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
                <i class="mdi mdi-alert"></i> No projects assigned to this vendor.
            </div>
        </div>
    </div>
    @endif

    <!-- Inventory Section - Separate Card -->
    @if ($assignedProjects->count() > 0)
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-3">Inventory</h5>
                    <ul class="nav nav-tabs mb-3" id="inventoryTabs" role="tablist">
                        @foreach ($assignedProjects as $index => $project)
                        <li class="nav-item">
                            <a class="nav-link {{ $index === 0 ? 'active' : '' }}" id="inventory-{{ $project->id }}-tab" 
                               data-toggle="tab" href="#inventory-{{ $project->id }}" role="tab" aria-controls="inventory-{{ $project->id }}">
                                {{ $project->project_name }}
                            </a>
                        </li>
                        @endforeach
                    </ul>

                    <div class="tab-content" id="inventoryTabContent">
                        @foreach ($assignedProjects as $index => $project)
                        <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}" id="inventory-{{ $project->id }}" role="tabpanel" aria-labelledby="inventory-{{ $project->id }}-tab">
                            @if (isset($inventoryByProject[$project->id]))
                                @php $inventoryData = $inventoryByProject[$project->id]; @endphp
                                
                                <!-- Inventory Summary Cards -->
                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <div class="card">
                                            <div class="card-body text-center">
                                                <h6 class="text-muted mb-1">Total Dispatched</h6>
                                                <h4 class="mb-0 text-info">{{ $inventoryData['total_dispatched'] }}</h4>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card">
                                            <div class="card-body text-center">
                                                <h6 class="text-muted mb-1">Consumed</h6>
                                                <h4 class="mb-0 text-success">{{ $inventoryData['total_consumed'] }}</h4>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card">
                                            <div class="card-body text-center">
                                                <h6 class="text-muted mb-1">In Custody</h6>
                                                <h4 class="mb-0 text-warning">{{ $inventoryData['total_in_custody'] }}</h4>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card">
                                            <div class="card-body text-center">
                                                <h6 class="text-muted mb-1">Value in Custody</h6>
                                                <h4 class="mb-0 text-warning">₹{{ number_format($inventoryData['value_in_custody'] ?? 0, 2) }}</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Value Installed Card -->
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <div class="card">
                                            <div class="card-body text-center">
                                                <h6 class="text-muted mb-1">Value of Materials Installed</h6>
                                                <h4 class="mb-0 text-success">₹{{ number_format($inventoryData['value_installed'] ?? 0, 2) }}</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Consolidated Inventory Table -->
                                @if (!empty($inventoryData['all_items']) && $inventoryData['all_items']->count() > 0)
                                    <x-datatable id="inventoryTable-{{ $project->id }}" 
                                        title="All Inventory Items" 
                                        :columns="[
                                            ['title' => '#', 'width' => '5%'],
                                            ['title' => 'Item Type', 'width' => '15%'],
                                            ['title' => 'Item Code', 'width' => '12%'],
                                            ['title' => 'Make', 'width' => '12%'],
                                            ['title' => 'Model', 'width' => '12%'],
                                            ['title' => 'Serial Number', 'width' => '12%'],
                                            ['title' => 'Status', 'width' => '12%'],
                                            ['title' => 'Used At', 'width' => '12%'],
                                            ['title' => 'Value', 'width' => '8%'],
                                        ]" 
                                        :exportEnabled="true" 
                                        :importEnabled="false" 
                                        :bulkDeleteEnabled="false"
                                        pageLength="25" 
                                        searchPlaceholder="Search Inventory...">
                                        @foreach ($inventoryData['all_items'] as $invItem)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td><strong>{{ $invItem->item }}</strong></td>
                                            <td>{{ $invItem->item_code }}</td>
                                            <td>{{ $invItem->make ?? '-' }}</td>
                                            <td>{{ $invItem->model ?? '-' }}</td>
                                            <td>{{ $invItem->serial_number ?? '-' }}</td>
                                            <td>
                                                @if ($invItem->is_consumed)
                                                    <span class="badge badge-success">Consumed</span>
                                                @elseif ($invItem->isDispatched)
                                                    <span class="badge badge-warning">In Custody</span>
                                                @else
                                                    <span class="badge badge-info">Dispatched</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($invItem->is_consumed && $invItem->streetlightPole)
                                                    <a href="{{ route('poles.show', $invItem->streetlightPole->id) }}" class="text-primary">
                                                        {{ $invItem->streetlightPole->complete_pole_number ?? 'N/A' }}
                                                    </a>
                                                @elseif ($invItem->isDispatched && !$invItem->is_consumed)
                                                    @php
                                                        $daysInCustody = $invItem->dispatch_date ? \Carbon\Carbon::parse($invItem->dispatch_date)->diffInDays(\Carbon\Carbon::now()) : 0;
                                                    @endphp
                                                    <span class="text-muted">In Vendor Custody</span>
                                                    <span style="color: {{ $daysInCustody > 5 ? 'red' : 'green' }}">
                                                        ({{ $daysInCustody }} days)
                                                    </span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>₹{{ number_format($invItem->total_value ?? 0, 2) }}</td>
                                            <td class="text-center">
                                                @if ($invItem->is_consumed && $invItem->streetlightPole)
                                                    <button type="button" class="btn btn-icon btn-danger btn-sm" data-bs-toggle="modal"
                                                        data-bs-target="#itemDetailsModal-{{ $project->id }}" 
                                                        data-item-id="{{ $invItem->id }}"
                                                        data-item-code="{{ $invItem->item_code }}" 
                                                        data-item-name="{{ $invItem->item }}"
                                                        data-manufacturer="{{ $invItem->make }}" 
                                                        data-serial-number="{{ $invItem->serial_number }}"
                                                        data-model="{{ $invItem->model }}" 
                                                        data-quantity="{{ $invItem->total_quantity ?? 1 }}"
                                                        data-vendor="{{ $vendor->name }}" 
                                                        data-total="{{ $invItem->total_value }}" 
                                                        data-date="{{ $invItem->dispatch_date }}"
                                                        data-site="{{ $invItem->streetlightPole->complete_pole_number ?? 'N/A' }}"
                                                        data-toggle="tooltip" title="Replace Item">
                                                        <i class="mdi mdi-swap-horizontal"></i>
                                                    </button>
                                                @elseif ($invItem->isDispatched && !$invItem->is_consumed)
                                                    <form action="{{ route('inventory.return') }}" method="POST" style="display:inline;">
                                                        @csrf
                                                        <input type="hidden" name="serial_number" value="{{ $invItem->serial_number }}">
                                                        <button type="submit" class="btn btn-icon btn-warning btn-sm" 
                                                                onclick="return confirm('Are you sure you want to return this item?');"
                                                                data-toggle="tooltip" title="Return Item">
                                                            <i class="mdi mdi-undo"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </x-datatable>
                                @else
                                    <div class="alert alert-info">
                                        <i class="mdi mdi-information"></i> No inventory data available for this project.
                                    </div>
                                @endif
                                
                                <!-- Replace Item Modal for this project -->
                                @if (isset($inventoryData['all_items']) && $inventoryData['all_items']->where('is_consumed', 1)->count() > 0)
                                <div class="modal fade" id="itemDetailsModal-{{ $project->id }}" tabindex="-1" role="dialog" aria-labelledby="itemDetailsModalLabel-{{ $project->id }}" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="itemDetailsModalLabel-{{ $project->id }}">Replace Item: <span id="modal-serial-number-{{ $project->id }}"></span></h5>
                                                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <form id="replaceItemForm-{{ $project->id }}" method="POST" action="{{ route('inventory.replace') }}">
                                                @csrf
                                                <input type="hidden" name="item_id" id="replace_item_id-{{ $project->id }}">
                                                <input type="hidden" name="old_serial_number" id="old_serial_number-{{ $project->id }}">
                                                <div class="modal-body">
                                                    <div class="d-flex">
                                                        <div>
                                                            <h6><span id="modal-item-code-{{ $project->id }}"></span></h6>
                                                            <p><span id="modal-item-name-{{ $project->id }}"></span></p>
                                                            <p><strong>Manufacturer:</strong> <span id="modal-manufacturer-{{ $project->id }}"></span></p>
                                                            <p><strong>Model:</strong> <span id="modal-model-{{ $project->id }}"></span></p>
                                                        </div>
                                                        <div>
                                                            <h6><strong>Dispatched to: </strong> <span id="modal-vendor-{{ $project->id }}"></span></h6>
                                                            <p><strong>On (date and time): </strong> <span id="modal-date-{{ $project->id }}"></span></p>
                                                            <p><strong>Used at</strong> <span id="modal-site-{{ $project->id }}"></span></p>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="new_serial_number-{{ $project->id }}">New Serial Number</label>
                                                        <input type="text" class="form-control" id="new_serial_number-{{ $project->id }}" name="new_serial_number"
                                                            placeholder="New Serial Number" required>
                                                    </div>
                                                    <div class="form-group" id="authentication_code_group-{{ $project->id }}" style="display: none;">
                                                        <label for="authentication_code-{{ $project->id }}">Authentication Code</label>
                                                        <input type="password" class="form-control" id="authentication_code-{{ $project->id }}" name="authentication_code"
                                                            placeholder="Authentication Code" required>
                                                    </div>
                                                    <div class="form-group mx-2">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" id="agreement_checkbox-{{ $project->id }}" name="agreement_checkbox"
                                                                required>
                                                            <label class="form-check-label" for="agreement_checkbox-{{ $project->id }}">
                                                                Our Team has replaced the old item during maintenance and agrees to return the inventory. I understand
                                                                that the action is irreversible.
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div id="replace-message-{{ $project->id }}"></div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    <button type="submit" class="btn btn-primary">Replace Item</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            @else
                                <div class="alert alert-info">
                                    <i class="mdi mdi-information"></i> No inventory data available for this project.
                                </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
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
    .vendor-avatar-wrapper {
        position: relative;
    }
    
    .vendor-avatar {
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
    
    .vendor-name {
        font-size: 1.1rem;
        font-weight: 600;
        color: #212529;
    }
    
    .vendor-email {
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
    
    .edit-vendor-btn {
        border-width: 1px;
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
    }
    
    .edit-vendor-btn:hover {
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
                var $avatar = $('#vendorAvatar');
                var originalSrc = $avatar.attr('src');
                $avatar.css('opacity', '0.5');

                $.ajax({
                    url: '{{ route("uservendors.uploadAvatar", $vendor->id) }}',
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

        // Initialize replace modals for each project
        @foreach ($assignedProjects as $project)
            @if (isset($inventoryByProject[$project->id]) && $inventoryByProject[$project->id]['all_items']->where('is_consumed', 1)->count() > 0)
            $('#itemDetailsModal-{{ $project->id }}').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var item_id = button.data('item-id');
                var item_code = button.data('item-code');
                var item_name = button.data('item-name');
                var manufacturer = button.data('manufacturer');
                var serial_number = button.data('serial-number');
                var model = button.data('model');
                var vendor = button.data('vendor');
                var date = button.data('date');
                var site = button.data('site');

                var modal = $(this);
                modal.find('#modal-item-code-{{ $project->id }}').text(item_code);
                modal.find("#old_serial_number-{{ $project->id }}").val(serial_number);
                modal.find('#modal-item-name-{{ $project->id }}').text(item_name);
                modal.find('#modal-manufacturer-{{ $project->id }}').text(manufacturer);
                modal.find('#modal-serial-number-{{ $project->id }}').text(serial_number);
                modal.find('#modal-model-{{ $project->id }}').text(model);
                modal.find('#modal-vendor-{{ $project->id }}').text(vendor);
                modal.find('#modal-date-{{ $project->id }}').text(date);
                modal.find('#modal-site-{{ $project->id }}').text(site);
                modal.find('#replace_item_id-{{ $project->id }}').val(item_id);
            });

            // Show/hide authentication code based on serial number input
            $('#new_serial_number-{{ $project->id }}').on('input', function() {
                if ($(this).val().length > 0) {
                    $('#authentication_code_group-{{ $project->id }}').show();
                } else {
                    $('#authentication_code_group-{{ $project->id }}').hide();
                }
            });

            // Form validation
            function checkFormValidity{{ $project->id }}() {
                var authenticationCode = $('#authentication_code-{{ $project->id }}').val();
                var agreementChecked = $('#agreement_checkbox-{{ $project->id }}').is(':checked');
                var authenticationCodeValid = authenticationCode.length > 0;

                if (authenticationCodeValid && agreementChecked) {
                    $('#replaceItemForm-{{ $project->id }} .btn-primary[type="submit"]').prop('disabled', false);
                } else {
                    $('#replaceItemForm-{{ $project->id }} .btn-primary[type="submit"]').prop('disabled', true);
                }
            }

            $('#replaceItemForm-{{ $project->id }} .btn-primary[type="submit"]').prop('disabled', true);
            $('#authentication_code-{{ $project->id }}, #agreement_checkbox-{{ $project->id }}').on('input change', function() {
                checkFormValidity{{ $project->id }}();
            });

            $('#itemDetailsModal-{{ $project->id }}').on('show.bs.modal', function() {
                checkFormValidity{{ $project->id }}();
            });
            @endif
        @endforeach

        // Initialize tooltips for replace/return buttons
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>
@endpush
