@php
  use App\Enums\UserRole;
@endphp

<style>
    .vendor-management-container {
        padding: 0;
    }

    .vendor-section-card {
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

    .vendor-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 12px;
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        margin-bottom: 6px;
    }

    .vendor-item:hover {
        background: #f8f9fa;
    }

    .vendor-item:last-child {
        margin-bottom: 0;
    }

    .vendor-info {
        display: flex;
        align-items: center;
        gap: 10px;
        flex: 1;
    }

    .vendor-checkbox {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }

    .vendor-name {
        font-weight: 400;
        color: #212529;
        margin: 0;
        font-size: 0.9rem;
    }

    .vendor-actions {
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

    .search-input {
        border-radius: 4px;
        border: 1px solid #ced4da;
        font-size: 0.9rem;
    }

    .search-input:focus {
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

    .role-count {
        background: #343a40;
        color: white;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 0.85rem;
        font-weight: 600;
    }
</style>

<div class="vendor-management-container">
    <div class="row my-2">
        <!-- Assigned Vendors Section -->
        <div class="col-lg-5">
            <div class="vendor-section-card position-relative">
                <div class="loading-overlay" id="assignedVendorLoadingOverlay">
                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <div class="section-header">
                    <span>
                        <i class="mdi mdi-account-group me-2"></i>
                        Assigned Vendors
                    </span>
                    <span class="badge bg-dark text-white" id="assignedVendorTotalCount">{{ $assignedVendors->count() }}</span>
                </div>
                <div class="bulk-actions" id="assignedVendorBulkActions">
                    <span id="assignedVendorSelectedCount" class="fw-bold">0 selected</span>
                    <button type="button" class="btn btn-danger btn-sm" onclick="removeSelectedVendors()">
                        <i class="mdi mdi-delete"></i> Remove Selected
                    </button>
                </div>
                <div class="section-body">
                    <div id="assignedVendorContainer">
                        @if($assignedVendors->isNotEmpty())
                            @foreach($assignedVendors as $vendor)
                                <div class="vendor-item" data-vendor-id="{{ $vendor->id }}" data-name="{{ strtolower(trim($vendor->firstName . ' ' . $vendor->lastName)) }}">
                                    <div class="vendor-info">
                                        <input type="checkbox" class="vendor-checkbox vendor-checkbox-assigned" value="{{ $vendor->id }}" onchange="updateVendorBulkActions()">
                                        <p class="vendor-name mb-0">{{ trim($vendor->firstName . ' ' . $vendor->lastName) }}</p>
                                    </div>
                                    <div class="vendor-actions">
                                        <button type="button" class="btn-action btn-remove" onclick="removeVendor([{{ $vendor->id }}], '{{ addslashes(trim($vendor->firstName . ' ' . $vendor->lastName)) }}')">
                                            <i class="mdi mdi-delete"></i>
                                            Remove
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="empty-state">
                                <i class="mdi mdi-account-off"></i>
                                <p class="mb-0">No vendors assigned to this project</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Available Vendors Section -->
        <div class="col-lg-7">
            <div class="vendor-section-card position-relative">
                <div class="loading-overlay" id="availableVendorLoadingOverlay">
                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <div class="section-header">
                    <span>
                        <i class="mdi mdi-account-plus me-2"></i>
                        Available Vendors
                    </span>
                    <span class="badge bg-dark text-white" id="availableVendorTotalCount">{{ $availableVendors->count() }}</span>
                </div>
                <div class="bulk-actions" id="availableVendorBulkActions">
                    <span id="availableVendorSelectedCount" class="fw-bold">0 selected</span>
                    <button type="button" class="btn btn-primary btn-sm" onclick="assignSelectedVendors()">
                        <i class="mdi mdi-plus"></i> Assign Selected
                    </button>
                </div>
                <div class="section-body">
                    <!-- Search Bar -->
                    <div class="search-filter-bar">
                        <input type="text" class="form-control search-input" id="vendorSearch" placeholder="Search by name..." onkeyup="filterVendors()">
                    </div>

                    <div id="availableVendorContainer">
                        @if($availableVendors->isNotEmpty())
                            @foreach($availableVendors as $vendor)
                                <div class="vendor-item" data-vendor-id="{{ $vendor->id }}" data-name="{{ strtolower(trim($vendor->firstName . ' ' . $vendor->lastName)) }}">
                                    <div class="vendor-info">
                                        <input type="checkbox" class="vendor-checkbox vendor-checkbox-available" value="{{ $vendor->id }}" onchange="updateVendorBulkActions()">
                                        <p class="vendor-name mb-0">{{ trim($vendor->firstName . ' ' . $vendor->lastName) }}</p>
                                    </div>
                                    <div class="vendor-actions">
                                        <button type="button" class="btn-action btn-assign" onclick="assignVendor([{{ $vendor->id }}], '{{ addslashes(trim($vendor->firstName . ' ' . $vendor->lastName)) }}')">
                                            <i class="mdi mdi-plus"></i>
                                            Assign
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="empty-state">
                                <i class="mdi mdi-account-search"></i>
                                <p class="mb-0">No available vendors found</p>
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
    // Initialize project vendor management - set as global variables
    window.projectId = {{ $project->id }};
    window.csrfToken = '{{ csrf_token() }}';
    window.projectDistricts = @json(($projectDistricts ?? collect())->map(function ($district) {
        return [
            'id' => $district->id,
            'name' => $district->name,
        ];
    }));
    
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
<script src="{{ asset('js/project-vendor-management.js') }}"></script>
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
