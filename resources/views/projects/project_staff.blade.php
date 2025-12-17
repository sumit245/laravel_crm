@php
  use App\Enums\UserRole;
  // Only include staff roles, exclude vendors (they have separate Vendor Management tab)
  $roles = [
      UserRole::SITE_ENGINEER->value => "Site Engineer",
      UserRole::PROJECT_MANAGER->value => "Project Manager",
      UserRole::STORE_INCHARGE->value => "Store Incharge",
      UserRole::COORDINATOR->value => "Coordinator",
  ];
  
  $roleColors = [
      UserRole::SITE_ENGINEER->value => 'bg-info',
      UserRole::PROJECT_MANAGER->value => 'bg-primary',
      UserRole::STORE_INCHARGE->value => 'bg-warning',
      UserRole::COORDINATOR->value => 'bg-success',
  ];
@endphp

<style>
    .staff-management-container {
        padding: 0;
    }

    .staff-section-card {
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        margin-bottom: 20px;
    }

    .section-header {
        background: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
        padding: 12px 16px;
        font-weight: 600;
        font-size: 1rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: #495057;
    }

    .section-body {
        padding: 16px;
    }

    .bulk-actions {
        padding: 10px 16px;
        background: #e7f3ff;
        border-bottom: 1px solid #dee2e6;
        display: none;
        justify-content: space-between;
        align-items: center;
    }

    .bulk-actions.show {
        display: flex;
    }

    .role-group {
        margin-bottom: 16px;
    }

    .role-group:last-child {
        margin-bottom: 0;
    }

    .role-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 12px;
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        margin-bottom: 8px;
        cursor: pointer;
    }

    .role-header:hover {
        background: #e9ecef;
    }

    .role-header h6 {
        margin: 0;
        font-weight: 600;
        color: #495057;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.9rem;
    }

    .role-badge {
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 500;
        color: white;
    }

    .role-count {
        background: white;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 0.85rem;
        font-weight: 500;
        color: #495057;
        border: 1px solid #dee2e6;
    }

    .staff-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 12px;
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        margin-bottom: 6px;
    }

    .staff-item:hover {
        background: #f8f9fa;
    }

    .staff-item:last-child {
        margin-bottom: 0;
    }

    .staff-info {
        display: flex;
        align-items: center;
        gap: 10px;
        flex: 1;
    }

    .staff-checkbox {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }

    .staff-name {
        font-weight: 400;
        color: #212529;
        margin: 0;
        font-size: 0.9rem;
    }

    .staff-actions {
        display: flex;
        gap: 6px;
    }

    .btn-action {
        padding: 4px 10px;
        border-radius: 4px;
        font-size: 0.8rem;
        font-weight: 400;
        border: 1px solid;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .btn-remove {
        background: white;
        color: #dc3545;
        border-color: #dc3545;
    }

    .btn-remove:hover {
        background: #dc3545;
        color: white;
    }

    .btn-assign {
        background: white;
        color: #0d6efd;
        border-color: #0d6efd;
    }

    .btn-assign:hover {
        background: #0d6efd;
        color: white;
    }

    .search-filter-bar {
        background: #f8f9fa;
        padding: 12px;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        margin-bottom: 16px;
    }

    .search-input,
    .filter-select {
        border-radius: 4px;
        border: 1px solid #ced4da;
        font-size: 0.9rem;
    }

    .search-input:focus,
    .filter-select:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        outline: none;
    }

    .empty-state {
        text-align: center;
        padding: 30px 20px;
        color: #6c757d;
    }

    .empty-state i {
        font-size: 2rem;
        margin-bottom: 12px;
        opacity: 0.4;
    }

    .loading-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.9);
        display: none;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
        z-index: 10;
    }

    .loading-overlay.show {
        display: flex;
    }

    .spinner-border-sm {
        width: 2rem;
        height: 2rem;
        border-width: 0.2em;
    }

    .role-group.collapsed .role-staff-list {
        display: none;
    }

    .role-group.collapsed .role-header i {
        transform: rotate(-90deg);
    }
</style>

<div class="staff-management-container">
    <div class="row">
        <!-- Assigned Staff Section -->
        <div class="col-lg-5">
            <div class="staff-section-card position-relative">
                <div class="loading-overlay" id="assignedLoadingOverlay">
                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <div class="section-header">
                    <span>
                        <i class="mdi mdi-account-group me-2"></i>
                        Assigned Staff
                    </span>
                    <span class="badge bg-dark text-white" id="assignedTotalCount">0</span>
                </div>
                <div class="bulk-actions" id="assignedBulkActions">
                    <span id="assignedSelectedCount" class="fw-bold">0 selected</span>
                    <button type="button" class="btn btn-danger btn-sm" onclick="removeSelectedStaff()">
                        <i class="mdi mdi-delete"></i> Remove Selected
                    </button>
                </div>
                <div class="section-body">
                    <div id="assignedStaffContainer">
                        @if(isset($assignedStaffByRole) && $assignedStaffByRole->isNotEmpty() && $assignedStaffByRole->flatten()->isNotEmpty())
                            @foreach($assignedStaffByRole as $roleValue => $staffGroup)
                                @if($staffGroup->isNotEmpty())
                                    @php
                                        $roleValueInt = (int)$roleValue;
                                        $roleName = $roles[$roleValueInt] ?? 'Unknown';
                                        $roleColor = $roleColors[$roleValueInt] ?? 'bg-secondary';
                                    @endphp
                                    <div class="role-group" data-role="{{ $roleValueInt }}">
                                        <div class="role-header" onclick="toggleRoleGroup(this)">
                                            <h6>
                                                <i class="mdi mdi-chevron-down"></i>
                                                <span class="role-badge {{ $roleColor }}">{{ $roleName }}</span>
                                            </h6>
                                            <div class="d-flex align-items-center gap-3">
                                                <input type="checkbox" class="role-select-all" data-role="{{ $roleValueInt }}" onchange="toggleRoleSelection(this)">
                                                <span class="role-count" data-role-count="{{ $roleValueInt }}">{{ $staffGroup->count() }}</span>
                                            </div>
                                        </div>
                                        <div class="role-staff-list">
                                            @foreach($staffGroup as $staff)
                                                <div class="staff-item" data-staff-id="{{ $staff['id'] }}" data-role="{{ $roleValueInt }}">
                                                    <div class="staff-info">
                                                        <input type="checkbox" class="staff-checkbox staff-checkbox-assigned" value="{{ $staff['id'] }}" data-role="{{ $roleValueInt }}" onchange="updateBulkActions()">
                                                        <p class="staff-name mb-0">{{ $staff['name'] }}</p>
                                                    </div>
                                                    <div class="staff-actions">
                                                        <button type="button" class="btn-action btn-remove" onclick="removeStaff([{{ $staff['id'] }}], '{{ addslashes($staff['name']) }}')">
                                                            <i class="mdi mdi-delete"></i>
                                                            Remove
                                                        </button>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        @else
                            <div class="empty-state">
                                <i class="mdi mdi-account-off"></i>
                                <p class="mb-0">No staff assigned to this project</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Available Staff Section -->
        <div class="col-lg-7">
            <div class="staff-section-card position-relative">
                <div class="loading-overlay" id="availableLoadingOverlay">
                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <div class="section-header">
                    <span>
                        <i class="mdi mdi-account-plus me-2"></i>
                        Available Staff
                    </span>
                    <span class="badge bg-dark text-white" id="availableTotalCount">0</span>
                </div>
                <div class="bulk-actions" id="availableBulkActions">
                    <span id="availableSelectedCount" class="fw-bold">0 selected</span>
                    <button type="button" class="btn btn-primary btn-sm" onclick="assignSelectedStaff()">
                        <i class="mdi mdi-plus"></i> Assign Selected
                    </button>
                </div>
                <div class="section-body">
                    <!-- Search and Filter Bar -->
                    <div class="search-filter-bar">
                        <div class="row g-2">
                            <div class="col-md-8">
                                <input type="text" class="form-control search-input" id="staffSearch" placeholder="Search by name..." onkeyup="filterStaff()">
                            </div>
                            <div class="col-md-4">
                                <select class="form-select filter-select" id="roleFilter" onchange="filterStaff()">
                                    <option value="">All Roles</option>
                                    @foreach($roles as $roleValue => $roleName)
                                        <option value="{{ $roleValue }}">{{ $roleName }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div id="availableStaffContainer">
                        @if(isset($availableStaffByRole) && $availableStaffByRole->isNotEmpty() && $availableStaffByRole->flatten()->isNotEmpty())
                            @foreach($availableStaffByRole as $roleValue => $staffGroup)
                                @if($staffGroup->isNotEmpty())
                                    @php
                                        $roleValueInt = (int)$roleValue;
                                        $roleName = $roles[$roleValueInt] ?? 'Unknown';
                                        $roleColor = $roleColors[$roleValueInt] ?? 'bg-secondary';
                                    @endphp
                                    <div class="role-group" data-role="{{ $roleValueInt }}">
                                        <div class="role-header" onclick="toggleRoleGroup(this)">
                                            <h6>
                                                <i class="mdi mdi-chevron-down"></i>
                                                <span class="role-badge {{ $roleColor }}">{{ $roleName }}</span>
                                            </h6>
                                            <div class="d-flex align-items-center gap-3">
                                                <input type="checkbox" class="role-select-all role-select-all-available" data-role="{{ $roleValueInt }}" onchange="toggleRoleSelection(this)">
                                                <span class="role-count" data-role-count="{{ $roleValueInt }}">{{ $staffGroup->count() }}</span>
                                            </div>
                                        </div>
                                        <div class="role-staff-list">
                                            @foreach($staffGroup as $staff)
                                                <div class="staff-item" data-staff-id="{{ $staff['id'] }}" data-role="{{ $roleValueInt }}" data-name="{{ strtolower($staff['name']) }}">
                                                    <div class="staff-info">
                                                        <input type="checkbox" class="staff-checkbox staff-checkbox-available" value="{{ $staff['id'] }}" data-role="{{ $roleValueInt }}" onchange="updateBulkActions()">
                                                        <p class="staff-name mb-0">{{ $staff['name'] }}</p>
                                                    </div>
                                                    <div class="staff-actions">
                                                        <button type="button" class="btn-action btn-assign" onclick="assignStaff([{{ $staff['id'] }}], '{{ addslashes($staff['name']) }}')">
                                                            <i class="mdi mdi-plus"></i>
                                                            Assign
                                                        </button>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        @else
                            <div class="empty-state">
                                <i class="mdi mdi-account-search"></i>
                                <p class="mb-0">No available staff found</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Initialize project staff management - set as global variables
    window.projectId = {{ $project->id }};
    window.csrfToken = '{{ csrf_token() }}';
    
    // Also set it in meta tag if not already set
    if (!document.querySelector('meta[name="csrf-token"]')) {
        const meta = document.createElement('meta');
        meta.name = 'csrf-token';
        meta.content = window.csrfToken;
        document.getElementsByTagName('head')[0].appendChild(meta);
    } else {
        document.querySelector('meta[name="csrf-token"]').setAttribute('content', window.csrfToken);
    }
</script>
<script src="{{ asset('js/project-staff-management.js') }}"></script>
<script>
    // Handle session messages
    @if(session()->has('success'))
        showToast('success', {!! json_encode(session('success')) !!});
    @endif
    
    @if(session()->has('error'))
        showToast('error', {!! json_encode(session('error')) !!});
    @endif
</script>
@endpush
