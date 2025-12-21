@extends('layouts.main')

@push('styles')
<script>
// Set flag in head section - runs before any body scripts
if (typeof window === 'undefined') window = {};
window['skipAutoInit_unifiedInventoryTable'] = true;
</script>
@endpush

@section('content')
    <div class="container-fluid p-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">{{ $store->store_name }}</h4>
                <p class="text-muted mb-0 small">{{ $store->address }}</p>
                <p class="text-muted mb-0 small">Incharge: {{ $store->storeIncharge->firstName ?? 'N/A' }}
                    {{ $store->storeIncharge->lastName ?? '' }}</p>
            </div>
            <a href="{{ route('projects.show', $project->id) }}#inventory" class="btn btn-secondary btn-sm">
                <i class="mdi mdi-arrow-left"></i> Back to Project
            </a>
        </div>

        @if (session('success') || session('error') || $errors->any())
            <div class="alert {{ session('success') ? 'alert-success' : 'alert-danger' }} alert-dismissible fade show"
                role="alert">
                {{ session('success') ?? session('error') ?? $errors->first() }}
                @if (session('import_errors_url') && session('import_errors_count') > 0)
                    <br>
                    <small>
                        {{ session('import_errors_count') }} row(s) were skipped during import.
                        <a href="{{ session('import_errors_url') }}" target="_blank">Download error details</a>
                    </small>
                @endif
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Global import overlay for smooth transitions during bulk import -->
        <div id="importOverlay" class="import-overlay d-none">
            <div class="import-overlay-content text-center">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <div class="small text-muted">Processing inventory import, please wait...</div>
            </div>
        </div>

        <!-- Metrics Cards -->
        @if ($project->project_type == 1)
            <div class="row mb-4">
                <div class="col-md-4 mb-3">
                    <div class="metric-card-initial">
                        <div class="metric-card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <label class="font-10 text-uppercase mg-b-10 fw-bold text-muted">Initial Stock
                                        Value</label>
                                    <h5 class="metric-card-title mb-0">₹{{ number_format($initialStockValue, 2) }}</h5>
                                </div>
                                <div class="text-primary">
                                    <i class="mdi mdi-package-variant" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="metric-card-instore">
                        <div class="metric-card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <label class="font-10 text-uppercase mg-b-10 fw-bold text-muted">In Store Stock
                                        Value</label>
                                    <h5 class="metric-card-title mb-0">₹{{ number_format($inStoreStockValue, 2) }}</h5>
                                </div>
                                <div class="text-success">
                                    <i class="mdi mdi-warehouse" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="metric-card-dispatched">
                        <div class="metric-card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <label class="font-10 text-uppercase mg-b-10 fw-bold text-muted">Dispatched Stock
                                        Value</label>
                                    <h5 class="metric-card-title mb-0">₹{{ number_format($dispatchedStockValue, 2) }}</h5>
                                </div>
                                <div class="text-warning">
                                    <i class="mdi mdi-truck-delivery" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Item-wise Statistics Cards -->
            @if (!empty($itemStats))
                <div class="row mb-4">
                    @foreach ($itemStats as $code => $stat)
                        <div class="col-md-3 mb-3">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title mb-3">{{ $stat['name'] ?? 'N/A' }} ({{ $code }})</h6>
                                    <div class="mb-2">
                                        <small class="text-muted">Total: </small><strong>{{ $stat['total'] ?? 0 }}</strong>
                                    </div>
                                    <div class="mb-2">
                                        <small class="text-muted">In Stock: </small><strong
                                            class="text-success">{{ $stat['in_stock'] ?? 0 }}</strong>
                                    </div>
                                    <div>
                                        <small class="text-muted">Dispatched: </small><strong
                                            class="text-warning">{{ $stat['dispatched'] ?? 0 }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Pie Chart -->
            <div class="row mb-4">
                <div class="col-md-6 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title mb-3">Stock Distribution</h6>
                            <div class="chart-container"
                                style="position: relative; height: 300px; width: 100%; max-width: 300px; margin: 0 auto;">
                                <canvas id="stockChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title mb-3">Item-wise Distribution</h6>
                            <div class="chart-container"
                                style="position: relative; height: 300px; width: 100%; max-width: 300px; margin: 0 auto;">
                                <canvas id="itemChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Tabs -->
        <div class="row my-3">
            <div class="col-12">
                <ul class="nav nav-tabs fixed-navbar-project mb-3" id="storeTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="inventory-tab" data-bs-toggle="tab" data-bs-target="#inventory"
                            type="button" role="tab" aria-controls="inventory" aria-selected="true">
                            Add Inventory
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="view-tab" data-bs-toggle="tab" data-bs-target="#view" type="button"
                            role="tab" aria-controls="view" aria-selected="false">
                            View Inventory
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="dispatch-tab" data-bs-toggle="tab" data-bs-target="#dispatch"
                            type="button" role="tab" aria-controls="dispatch" aria-selected="false">
                            Dispatch Material
                        </button>
                    </li>
                </ul>
            </div>
        </div>

        <div class="tab-content" id="storeTabContent">
            <!-- Add Inventory Tab -->
            <div class="tab-pane fade show active" id="inventory" role="tabpanel">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start mb-3 gap-3">
                            <h6 class="card-title mb-0">Add Inventory</h6>
                            <div class="import-section d-flex flex-column gap-2 w-100 w-md-auto">
                                <form id="importInventoryForm"
                                    action="{{ route($project->project_type == 1 ? 'inventory.import-streetlight' : 'inventory.import', ['projectId' => $project->id, 'storeId' => $store->id]) }}"
                                    method="POST" enctype="multipart/form-data"
                                    class="import-form-group d-flex align-items-stretch">
                                    @csrf
                                    <div class="input-group input-group-sm import-input-wrapper">
                                        <input type="file" name="file"
                                            class="form-control form-control-sm import-file-input"
                                            accept=".xlsx,.xls,.csv" required>
                                        <button type="submit"
                                            class="btn btn-success import-submit-btn d-inline-flex align-items-center gap-1">
                                            <i class="mdi mdi-upload"></i>
                                            <span>Import</span>
                                        </button>
                                    </div>
                                </form>
                                <a href="{{ route('inventory.download-format', $project->id) }}"
                                    class="download-format-link" target="_blank">
                                    <i class="mdi mdi-download"></i>
                                    <span>Download Format</span>
                                </a>
                            </div>
                        </div>

                        <!-- Divider with "or" text -->
                        <div class="position-relative my-4">
                            <hr class="my-4">
                            <div class="position-absolute top-50 start-50 translate-middle bg-white px-3">
                                <span class="text-muted small fw-semibold">OR</span>
                            </div>
                        </div>

                        @if ($project->project_type == 1)
                            <form action="{{ route('inventory.store') }}" method="POST" id="addInventoryForm"
                                novalidate>
                                @csrf
                                <input type="hidden" name="project_type" value="{{ $project->project_type }}">
                                <input type="hidden" name="project_id" value="{{ $project->id }}">
                                <input type="hidden" name="store_id" value="{{ $store->id }}">

                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <label for="item_combined" class="form-label">
                                            Item <span class="text-danger">*</span>
                                        </label>
                                        <select id="item_combined"
                                            class="form-select form-select-sm @error('code') is-invalid @enderror"
                                            required>
                                            <option value="">-- Select Item --</option>
                                            <option value="SL01|Module">SL01 - Module</option>
                                            <option value="SL02|Luminary">SL02 - Luminary</option>
                                            <option value="SL03|Battery">SL03 - Battery</option>
                                            <option value="SL04|Structure">SL04 - Structure</option>
                                        </select>
                                        <input type="hidden" name="code" id="item_code">
                                        <input type="hidden" name="dropdown" id="item_name">
                                        @error('code')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                        <div class="invalid-feedback d-none">Please select an item.</div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="manufacturer" class="form-label">
                                            Manufacturer <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" id="manufacturer" name="manufacturer"
                                            class="form-control form-control-sm @error('manufacturer') is-invalid @enderror"
                                            required>
                                        @error('manufacturer')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                        <div class="invalid-feedback">Please provide a manufacturer name.</div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="model" class="form-label">
                                            Model <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" id="model" name="model"
                                            class="form-control form-control-sm @error('model') is-invalid @enderror"
                                            required>
                                        @error('model')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                        <div class="invalid-feedback">Please provide a model name.</div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="serialnumber" class="form-label">
                                            Serial Number <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" id="serialnumber" name="serialnumber"
                                            class="form-control form-control-sm @error('serialnumber') is-invalid @enderror"
                                            required>
                                        @error('serialnumber')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                        <div class="invalid-feedback">Please provide a serial number.</div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <label for="make" class="form-label">
                                            Make
                                        </label>
                                        <input type="text" id="make" name="make" value="Sugs"
                                            class="form-control form-control-sm @error('make') is-invalid @enderror">
                                        @error('make')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="rate" class="form-label">
                                            Rate
                                        </label>
                                        <input type="number" id="rate" name="rate" step="0.01"
                                            min="0" value="100"
                                            class="form-control form-control-sm @error('rate') is-invalid @enderror">
                                        @error('rate')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="receiveddate" class="form-label">
                                            Received Date
                                        </label>
                                        <input type="date" id="receiveddate" name="receiveddate"
                                            class="form-control form-control-sm @error('receiveddate') is-invalid @enderror">
                                        @error('receiveddate')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="hsncode" class="form-label">
                                            HSN Code
                                        </label>
                                        <input type="text" id="hsncode" name="hsncode" value="123456"
                                            class="form-control form-control-sm @error('hsncode') is-invalid @enderror">
                                        @error('hsncode')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <label for="totalvalue" class="form-label">
                                            Total Value
                                        </label>
                                        <input type="number" id="totalvalue" name="totalvalue" step="0.01"
                                            min="0" readonly
                                            class="form-control form-control-sm @error('totalvalue') is-invalid @enderror">
                                        @error('totalvalue')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="unit" class="form-label">
                                            Unit
                                        </label>
                                        <input type="text" id="unit" name="unit" value="PCS"
                                            class="form-control form-control-sm @error('unit') is-invalid @enderror">
                                        @error('unit')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="description" class="form-label">
                                            Description
                                        </label>
                                        <input type="text" id="description" name="description" value=""
                                            class="form-control form-control-sm @error('description') is-invalid @enderror">
                                        @error('description')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-3 mb-3" id="sim_number_wrapper" style="display: none;">
                                        <label for="sim_number" class="form-label">
                                            SIM Number <span class="text-danger">*</span> <small class="text-muted">(Luminary only)</small>
                                        </label>
                                        <input type="text" id="sim_number" name="sim_number"
                                            class="form-control form-control-sm @error('sim_number') is-invalid @enderror">
                                        @error('sim_number')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                        <div class="invalid-feedback">Please provide a SIM number for luminary items.</div>
                                    </div>
                                </div>
                                <input type="hidden" name="number" value="1">
                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary btn-sm">Save</button>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            <!-- View Inventory Tab -->
            <div class="tab-pane fade" id="view" role="tabpanel">
                {{-- Prevent component auto-initialization - must run IMMEDIATELY --}}
                <script>
                // Set flag immediately - before any other scripts run
                if (typeof window === 'undefined') window = {};
                window['skipAutoInit_unifiedInventoryTable'] = true;
                </script>
                
                {{-- Manual table structure for server-side processing --}}
                {{-- Using datatable-wrapper class for UI consistency, but preventing component initialization --}}
                <div class="datatable-wrapper" id="datatable-wrapper-unifiedInventoryTable" data-server-side="true">
                    {{-- Filter Section with Border --}}
                    <div class="border rounded p-3 mb-3" style="background-color: #f8f9fa;">
                        <div class="d-flex flex-column flex-md-row align-items-end gap-3">
                            <div class="flex-fill">
                                <label class="form-label small mb-1 fw-semibold">Availability</label>
                                <select name="availability" class="form-control form-control-sm filter-select" style="width: 100%;">
                                    <option value="">Availability</option>
                                    <option value="In Stock">In Stock</option>
                                    <option value="Dispatched">Dispatched</option>
                                    <option value="Consumed">Consumed</option>
                                </select>
                            </div>
                            <div class="flex-fill">
                                <label class="form-label small mb-1 fw-semibold">Vendor</label>
                                <select name="vendor" id="vendor_filter" class="form-control form-control-sm filter-select2" style="width: 100%;">
                                    <option value="">Vendor</option>
                                    @foreach($assignedVendors as $vendor)
                                        <option value="{{ $vendor->name }}">{{ $vendor->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex-fill">
                                <label class="form-label small mb-1 fw-semibold">Item</label>
                                <select name="item" class="form-control form-control-sm filter-select" style="width: 100%;">
                                    <option value="">Item</option>
                                    <option value="SL01">Panel Module (SL01 Panel)</option>
                                    <option value="SL02">Luminary (SL02 Luminary)</option>
                                    <option value="SL03">Battery (SL03 Battery)</option>
                                    <option value="SL04">Structure (SL04 Structure)</option>
                                </select>
                            </div>
                            <div class="d-flex gap-2 align-items-end">
                                <button type="button" id="applyFiltersBtn" class="btn btn-primary btn-sm">
                                    Apply Filters
                                        </button>
                                <button type="button" id="clearFiltersBtn" class="btn btn-outline-secondary btn-sm">
                                    Clear
                                        </button>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Search and Export Section --}}
                    <div class="row align-items-center p-3 g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="mdi mdi-magnify"></i></span>
                                <input type="search" class="form-control" id="unifiedInventoryTable_search" placeholder="Search inventory...">
                            </div>
                        </div>
                        <div class="col-12 col-md-6 text-start text-md-end">
                            <div class="btn-group btn-group-sm d-flex flex-wrap" role="group">
                                <button type="button" class="btn btn-success flex-fill flex-sm-auto" id="unifiedInventoryTable_excel" title="Export to Excel">
                                    <i class="mdi mdi-file-excel"></i> <span class="d-none d-sm-inline">Excel</span>
                                    </button>
                                <button type="button" class="btn btn-danger flex-fill flex-sm-auto" id="unifiedInventoryTable_pdf" title="Export to PDF">
                                    <i class="mdi mdi-file-pdf"></i> <span class="d-none d-sm-inline">PDF</span>
                                        </button>
                                <button type="button" class="btn btn-info flex-fill flex-sm-auto" id="unifiedInventoryTable_print" title="Print">
                                    <i class="mdi mdi-printer"></i> <span class="d-none d-sm-inline">Print</span>
                                    </button>
                                <button type="button" class="btn btn-secondary flex-fill flex-sm-auto" id="unifiedInventoryTable_columns" title="Show/Hide Columns">
                                    <i class="mdi mdi-eye"></i> <span class="d-none d-sm-inline">Columns</span>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="table-responsive" style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
                        <table id="unifiedInventoryTable" class="table table-striped table-bordered table-hover" style="width:100%; min-width: 600px;" data-server-side="true">
                            <thead>
                                <tr>
                            @if ($isAdmin)
                                    <th width="30px"><input type="checkbox" id="unifiedInventoryTable_selectAll" class="select-all-checkbox"></th>
                                @endif
                                    <th>Item Code</th>
                                    <th>Item</th>
                                    <th>Serial Number</th>
                                    <th>Availability</th>
                                    <th>Vendor</th>
                                    <th>Dispatch Date</th>
                                    <th>In Date</th>
                                    <th width="120px" class="text-center">Actions</th>
                        </tr>
                            </thead>
                            <tbody>
                                {{-- Server-side processing: tbody is empty, data loaded via AJAX --}}
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-3 d-flex flex-column gap-2">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div class="small text-muted" id="unifiedInventoryTable_info"></div>
                            <div id="unifiedInventoryTable_paginate"></div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <label class="mb-0 small fw-semibold">Show:</label>
                            <select class="form-control form-control-sm" id="unifiedInventoryTable_length" style="width: auto;">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50" selected>50</option>
                                <option value="100">100</option>
                            </select>
                            <span class="small">entries</span>
                        </div>
                    </div>
                </div>
                
                {{-- Enable server-side processing for the DataTable --}}
                @push('scripts')
                <script>
                // Set flag IMMEDIATELY to prevent component initialization
                window['skipAutoInit_unifiedInventoryTable'] = true;
                
                // Set flag IMMEDIATELY - before document.ready
                window['skipAutoInit_unifiedInventoryTable'] = true;
                
                // #region agent log
                fetch('http://127.0.0.1:7242/ingest/c8dd4f40-9714-49ad-ad6c-e2b0cb29dd16',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'show.blade.php:470',message:'SERVER-SIDE SCRIPT LOADED - flag set',data:{skipFlag:window['skipAutoInit_unifiedInventoryTable']},timestamp:Date.now(),sessionId:'debug-session',runId:'server-side-init',hypothesisId:'SCRIPT_LOADED'})}).catch(()=>{});
                // #endregion agent log
                
                $(document).ready(function() {
                    console.log('=== SERVER-SIDE SCRIPT LOADED ===');
                    console.log('Skip flag:', window['skipAutoInit_unifiedInventoryTable']);
                    
                    // #region agent log
                    fetch('http://127.0.0.1:7242/ingest/c8dd4f40-9714-49ad-ad6c-e2b0cb29dd16',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'show.blade.php:473',message:'document.ready fired',data:{skipFlag:window['skipAutoInit_unifiedInventoryTable']},timestamp:Date.now(),sessionId:'debug-session',runId:'server-side-init',hypothesisId:'DOC_READY'})}).catch(()=>{});
                    // #endregion agent log
                    
                    // Mark table immediately
                    var $table = $('#unifiedInventoryTable');
                    if ($table.length) {
                        $table.attr('data-server-side', 'true');
                        console.log('Table found and marked:', $table.attr('data-server-side'));
                    } else {
                        console.error('Table #unifiedInventoryTable NOT FOUND in DOM');
                    }
                    
                    function initializeServerSideTable() {
                        console.log('=== initializeServerSideTable() CALLED ===');
                        
                        // #region agent log
                        fetch('http://127.0.0.1:7242/ingest/c8dd4f40-9714-49ad-ad6c-e2b0cb29dd16',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'show.blade.php:494',message:'initializeServerSideTable() CALLED',data:{},timestamp:Date.now(),sessionId:'debug-session',runId:'server-side-init',hypothesisId:'INIT_FUNC_CALLED'})}).catch(()=>{});
                        // #endregion agent log
                        
                        // Check if already initialized
                        if ($.fn.DataTable.isDataTable('#unifiedInventoryTable')) {
                            var existing = $('#unifiedInventoryTable').DataTable();
                            if (existing.settings()[0].serverSide) {
                                console.log('Server-side table already initialized');
                                return;
                            } else {
                                console.log('Destroying client-side table, recreating as server-side');
                                existing.destroy();
                            }
                        }
                        
                        // Make sure table exists and has proper structure
                        var $table = $('#unifiedInventoryTable');
                        if ($table.length === 0) {
                            console.error('Table #unifiedInventoryTable not found');
                            return;
                        }
                        
                        // Verify thead structure
                        var $thead = $table.find('thead tr');
                        if ($thead.length === 0) {
                            console.error('Table thead not found');
                            return;
                        }
                        
                        var expectedCols = {{ $isAdmin ? 9 : 8 }};
                        var actualCols = $thead.find('th').length;
                        if (actualCols !== expectedCols) {
                            console.error('Column count mismatch. Expected: ' + expectedCols + ', Actual: ' + actualCols);
                            console.log('Actual columns:', $thead.find('th').map(function() { return $(this).text().trim(); }).get());
                            return;
                        }
                        
                        console.log('Initializing server-side DataTable...', {
                            skipFlag: window['skipAutoInit_unifiedInventoryTable'],
                            tableAttr: $table.attr('data-server-side'),
                            wrapperAttr: $('#datatable-wrapper-unifiedInventoryTable').attr('data-server-side')
                        });
                        
                        // Initialize with server-side processing
                        var table = $('#unifiedInventoryTable').DataTable({
                            processing: true,
                            serverSide: true,
                            ajax: {
                                url: '{{ route("store.inventory.data", $store->id) }}',
                                type: 'GET',
                                beforeSend: function(xhr, settings) {
                                    // #region agent log
                                    fetch('http://127.0.0.1:7242/ingest/c8dd4f40-9714-49ad-ad6c-e2b0cb29dd16',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'show.blade.php:544',message:'AJAX REQUEST SENT',data:{url:settings.url,type:settings.type},timestamp:Date.now(),sessionId:'debug-session',runId:'server-side-init',hypothesisId:'AJAX_SENT'})}).catch(()=>{});
                                    // #endregion agent log
                                    console.log('AJAX request sent to:', settings.url);
                                },
                                error: function(xhr, error, thrown) {
                                    console.error('DataTables AJAX error:', error, thrown);
                                    console.error('Response:', xhr.responseText);
                                    console.error('Status:', xhr.status);
                                    // #region agent log
                                    fetch('http://127.0.0.1:7242/ingest/c8dd4f40-9714-49ad-ad6c-e2b0cb29dd16',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'show.blade.php:547',message:'AJAX ERROR',data:{error:error,thrown:thrown,status:xhr.status,response:xhr.responseText.substring(0,500)},timestamp:Date.now(),sessionId:'debug-session',runId:'server-side-init',hypothesisId:'AJAX_ERROR'})}).catch(()=>{});
                                    // #endregion agent log
                                },
                                dataSrc: function(json) {
                                    console.log('DataTables response:', json);
                                    // #region agent log
                                    fetch('http://127.0.0.1:7242/ingest/c8dd4f40-9714-49ad-ad6c-e2b0cb29dd16',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'show.blade.php:552',message:'AJAX RESPONSE RECEIVED',data:{recordsTotal:json.recordsTotal,recordsFiltered:json.recordsFiltered,dataLength:json.data?json.data.length:0},timestamp:Date.now(),sessionId:'debug-session',runId:'server-side-init',hypothesisId:'AJAX_RESPONSE'})}).catch(()=>{});
                                    // #endregion agent log
                                    return json.data;
                                },
                                data: function(d) {
                                    // Add filter values
                                    var tabPane = $('#view');
                                    d.availability = tabPane.find('select[name="availability"]').val() || '';
                                    d.item_code = tabPane.find('select[name="item"]').val() || '';
                                    d.vendor_name = $('#vendor_filter').val() || '';
                                    console.log('DataTables request data:', d);
                                    // #region agent log
                                    fetch('http://127.0.0.1:7242/ingest/c8dd4f40-9714-49ad-ad6c-e2b0cb29dd16',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'show.blade.php:556',message:'AJAX REQUEST DATA',data:{draw:d.draw,start:d.start,length:d.length,availability:d.availability,item_code:d.item_code,vendor_name:d.vendor_name},timestamp:Date.now(),sessionId:'debug-session',runId:'server-side-init',hypothesisId:'AJAX_DATA'})}).catch(()=>{});
                                    // #endregion agent log
                                }
                            },
                            columns: [
                                @if ($isAdmin)
                                { data: 0, name: 'checkbox', orderable: false, searchable: false },
                                { data: 1, name: 'item_code' },
                                { data: 2, name: 'item' },
                                { data: 3, name: 'serial_number' },
                                { data: 4, name: 'availability', orderable: true },
                                { data: 5, name: 'vendor_name', orderable: true },
                                { data: 6, name: 'dispatch_date' },
                                { data: 7, name: 'created_at' },
                                { data: 8, name: 'actions', orderable: false, searchable: false }
                                @else
                                { data: 0, name: 'item_code' },
                                { data: 1, name: 'item' },
                                { data: 2, name: 'serial_number' },
                                { data: 3, name: 'availability', orderable: true },
                                { data: 4, name: 'vendor_name', orderable: true },
                                { data: 5, name: 'dispatch_date' },
                                { data: 6, name: 'created_at' },
                                { data: 7, name: 'actions', orderable: false, searchable: false }
                                @endif
                            ],
                            order: [[{{ $isAdmin ? 7 : 6 }}, 'desc']],
                            pageLength: 50,
                            deferLoading: null,
                            dom: "<'row'<'col-sm-12'tr>>" +
                                "<'row'<'col-sm-5'i><'col-sm-7'p>>",
                            buttons: [
                                {
                                    extend: 'excel',
                                    text: '<i class="mdi mdi-file-excel"></i> Excel',
                                    className: 'd-none',
                                    exportOptions: {
                                        columns: ':visible:not(.no-export)',
                                        format: {
                                            body: function(data, row, column, node) {
                                                // Remove HTML tags for export
                                                if (typeof data === 'string') {
                                                    return data.replace(/<[^>]*>/g, '').trim();
                                                }
                                                return data;
                                            }
                                        }
                                    }
                                },
                                {
                                    extend: 'pdf',
                                    text: '<i class="mdi mdi-file-pdf"></i> PDF',
                                    className: 'd-none',
                                    orientation: 'landscape',
                                    pageSize: 'A4',
                                    exportOptions: {
                                        columns: ':visible:not(.no-export)',
                                        format: {
                                            body: function(data, row, column, node) {
                                                if (typeof data === 'string') {
                                                    return data.replace(/<[^>]*>/g, '').trim();
                                                }
                                                return data;
                                            }
                                        }
                                    }
                                },
                                {
                                    extend: 'print',
                                    text: '<i class="mdi mdi-printer"></i> Print',
                                    className: 'd-none',
                                    exportOptions: {
                                        columns: ':visible:not(.no-export)',
                                        format: {
                                            body: function(data, row, column, node) {
                                                if (typeof data === 'string') {
                                                    return data.replace(/<[^>]*>/g, '').trim();
                                                }
                                                return data;
                                            }
                                        }
                                    }
                                },
                                {
                                    extend: 'colvis',
                                    text: '<i class="mdi mdi-eye"></i> Columns',
                                    className: 'd-none',
                                    columns: ':not(.no-colvis)',
                                    collectionLayout: 'three-column',
                                    postfixButtons: ['colvisRestore']
                                }
                            ],
                            language: {
                                processing: '<div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Loading...</span></div> Loading data...',
                                search: '',
                                searchPlaceholder: 'Search inventory...',
                                lengthMenu: '',
                                info: '',
                                infoEmpty: '',
                                infoFiltered: ''
                            },
                            pagingType: 'simple_numbers',
                            drawCallback: function() {
                                // Update info text with filtered and total counts
                                var info = this.api().page.info();
                                var totalRecords = info.recordsTotal; // Total records (no filters)
                                var filteredRecords = info.recordsFiltered; // Filtered records
                                
                                var infoText = 'Showing ' + (info.start + 1) + ' to ' + info.end + ' of ' + filteredRecords + ' entries';
                                if (filteredRecords < totalRecords) {
                                    infoText += ' (filtered from ' + totalRecords + ' total entries)';
                                }
                                $('#unifiedInventoryTable_info').text(infoText);
                                
                                // Move pagination to custom wrapper - use appendTo to preserve event handlers
                                var $dtPagination = $('#unifiedInventoryTable_wrapper .dataTables_paginate');
                                var $customPagination = $('#unifiedInventoryTable_paginate');
                                
                                if ($dtPagination.length > 0 && $dtPagination.parent()[0] !== $customPagination[0]) {
                                    // Move the entire pagination element (not just HTML) to preserve DataTables event handlers
                                    $dtPagination.appendTo($customPagination);
                                }
                                
                                // Handle select all checkbox
                                $('#unifiedInventoryTable_selectAll').off('change').on('change', function() {
                                    var isChecked = $(this).is(':checked');
                                    $('#unifiedInventoryTable tbody input[type="checkbox"]').prop('checked', isChecked);
                                });
                                
                                // Reattach delete handlers
                                $('#unifiedInventoryTable').off('click', '.delete-item').on('click', '.delete-item', function() {
                                    var id = $(this).data('id');
                                    if (confirm('Are you sure you want to delete this item?')) {
                                        console.log('Delete item:', id);
                                    }
                                });
                            },
                            initComplete: function() {
                                // Store the total count from first response to prevent recalculation
                                var settings = this.api().settings()[0];
                                if (settings.ajax && settings.ajax.json) {
                                    var json = settings.ajax.json;
                                    if (json && json.recordsTotal) {
                                        // Lock the total count so it doesn't change
                                        settings._iRecordsTotal = json.recordsTotal;
                                        settings._iRecordsDisplay = json.recordsTotal;
                                    }
                                }
                            }
                        });
                        
                        window['table_unifiedInventoryTable'] = table;
                        console.log('Server-side DataTable initialized successfully');
                        
                        // #region agent log
                        fetch('http://127.0.0.1:7242/ingest/c8dd4f40-9714-49ad-ad6c-e2b0cb29dd16',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'show.blade.php:607',message:'Server-side DataTable INITIALIZED SUCCESSFULLY',data:{},timestamp:Date.now(),sessionId:'debug-session',runId:'server-side-init',hypothesisId:'INIT_SUCCESS'})}).catch(()=>{});
                        // #endregion agent log
                        
                        // Handle length change
                        $('#unifiedInventoryTable_length').off('change').on('change', function() {
                            table.page.len(parseInt($(this).val())).draw();
                        });
                        
                        // Event delegation for pagination clicks (fallback if DataTables handlers don't work)
                        $('#unifiedInventoryTable_paginate').off('click', 'a').on('click', 'a', function(e) {
                            // Only handle if it's a pagination button and DataTables hasn't handled it
                            if ($(this).hasClass('paginate_button') && !$(this).hasClass('disabled') && !$(this).hasClass('current')) {
                                var href = $(this).attr('href');
                                if (href === '#' || !href) {
                                    e.preventDefault();
                                    var text = $(this).text().trim();
                                    if (text === 'Previous') {
                                        table.page('previous').draw('page');
                                    } else if (text === 'Next') {
                                        table.page('next').draw('page');
                                    } else if (!isNaN(text)) {
                                        table.page(parseInt(text) - 1).draw('page');
                                    }
                                }
                            }
                        });
                        
                        // Initialize Select2 for Vendor dropdown
                        $('#vendor_filter').select2({
                            placeholder: 'Vendor',
                            allowClear: true,
                            width: '100%',
                            dropdownParent: $('#datatable-wrapper-unifiedInventoryTable'),
                            minimumResultsForSearch: 0, // Always show search box
                        });
                        
                        // Handle Apply Filters button
                        $('#applyFiltersBtn').off('click').on('click', function() {
                            table.ajax.reload();
                        });
                        
                        // Handle Clear Filters button
                        $('#clearFiltersBtn').off('click').on('click', function() {
                            $('#view select[name="availability"]').val('').trigger('change');
                            $('#vendor_filter').val(null).trigger('change');
                            $('#view select[name="item"]').val('').trigger('change');
                            table.ajax.reload();
                        });
                        
                        // Handle search input - debounced for better performance
                        var searchTimeout;
                        $('#unifiedInventoryTable_search').off('keyup input').on('keyup input', function() {
                            var searchValue = $(this).val();
                            clearTimeout(searchTimeout);
                            searchTimeout = setTimeout(function() {
                                table.search(searchValue).draw();
                            }, 300); // 300ms debounce
                        });
                        
                        // Handle Enter key in search
                        $('#unifiedInventoryTable_search').off('keypress').on('keypress', function(e) {
                            if (e.which === 13) {
                                e.preventDefault();
                                clearTimeout(searchTimeout);
                                table.search($(this).val()).draw();
                            }
                        });
                        
                        // Wire up export buttons - custom export to get all filtered data
                        $('#unifiedInventoryTable_excel').off('click').on('click', function() {
                            // Get current filter values
                            var availability = $('#view select[name="availability"]').val() || '';
                            var itemCode = $('#view select[name="item"]').val() || '';
                            var vendorName = $('#vendor_filter').val() || '';
                            var search = $('#unifiedInventoryTable_search').val() || '';
                            
                            // Build export URL with filters
                            var exportUrl = '{{ route("store.inventory.export", $store->id) }}?';
                            var params = [];
                            if (availability) params.push('availability=' + encodeURIComponent(availability));
                            if (itemCode) params.push('item_code=' + encodeURIComponent(itemCode));
                            if (vendorName) params.push('vendor_name=' + encodeURIComponent(vendorName));
                            if (search) params.push('search=' + encodeURIComponent(search));
                            
                            exportUrl += params.join('&');
                            
                            // Open export URL in new window to trigger download
                            window.location.href = exportUrl;
                        });
                        
                        $('#unifiedInventoryTable_pdf').off('click').on('click', function() {
                            table.button('.buttons-pdf').trigger();
                        });
                        
                        $('#unifiedInventoryTable_print').off('click').on('click', function() {
                            table.button('.buttons-print').trigger();
                        });
                        
                        $('#unifiedInventoryTable_columns').off('click').on('click', function(e) {
                            e.preventDefault();
                            table.button('.buttons-colvis').trigger();
                        });
                    }
                    
                    // Initialize when tab is shown - use correct selector for button tabs
                    $('#view-tab, [data-bs-target="#view"]').on('shown.bs.tab', function() {
                        console.log('=== TAB SHOWN EVENT FIRED ===');
                        // #region agent log
                        fetch('http://127.0.0.1:7242/ingest/c8dd4f40-9714-49ad-ad6c-e2b0cb29dd16',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'show.blade.php:663',message:'TAB SHOWN EVENT FIRED for View Inventory',data:{},timestamp:Date.now(),sessionId:'debug-session',runId:'server-side-init',hypothesisId:'TAB_SHOWN'})}).catch(()=>{});
                        // #endregion agent log
                        setTimeout(function() {
                            if ($.fn.DataTable.isDataTable('#unifiedInventoryTable')) {
                                var existing = $('#unifiedInventoryTable').DataTable();
                                if (!existing.settings()[0].serverSide) {
                                    console.log('Destroying client-side, recreating server-side');
                                    existing.destroy();
                                } else {
                                    console.log('Server-side table already initialized');
                                    return;
                                }
                            }
                            initializeServerSideTable();
                        }, 100);
                    });
                    
                    // Also listen on the tab pane itself for when it becomes visible
                    $('#view').on('shown.bs.tab', function() {
                        console.log('=== TAB PANE SHOWN EVENT FIRED ===');
                        setTimeout(function() {
                            if (!$.fn.DataTable.isDataTable('#unifiedInventoryTable')) {
                                initializeServerSideTable();
                            }
                        }, 100);
                    });
                    
                    // Initialize immediately if tab is already active
                    if ($('#view').hasClass('active') && $('#view').hasClass('show')) {
                        console.log('=== TAB ALREADY ACTIVE, INITIALIZING NOW ===');
                        setTimeout(function() {
                            initializeServerSideTable();
                        }, 500);
                    } else {
                        console.log('Tab not active yet, waiting for tab show event');
                    }
                });
                </script>
                @endpush
            </div>

            <!-- Dispatch Material Tab -->
            <div class="tab-pane fade" id="dispatch" role="tabpanel">
                <!-- Dispatch Form Card -->
                <div class="card mb-3">
                    <div class="card-body">
                        <form id="dispatchForm">
                            @csrf
                            <input type="hidden" id="dispatchStoreId" name="store_id" value="{{ $store->id }}">
                            <input type="hidden" name="project_id" value="{{ $project->id }}">
                            <input type="hidden" name="store_incharge_id" value="{{ $store->store_incharge_id ?? 'N/A' }}">
                            
                            <!-- Vendor Selection and Entry Mode - Inline -->
                            <div class="d-flex justify-content-between align-items-end mb-3 gap-3">
                                <div class="flex-grow-1" style="max-width: 300px;">
                                    <label for="vendorName" class="form-label">Vendor Name:</label>
                                    <select class="form-select form-select-sm" id="vendorName" name="vendor_id" required>
                                        <option value="">Select Vendor</option>
                                        @foreach ($assignedVendors as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label">Entry Mode:</label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="dispatchModeSwitch" 
                                            onchange="switchDispatchMode(this.checked ? 'bulk' : 'manual')">
                                        <label class="form-check-label" for="dispatchModeSwitch">
                                            <span id="modeLabel">Manual Entry</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Bulk Upload Section (Initially Hidden) -->
                            <div id="bulkUploadSection" style="display: none;" class="mb-3">
                                <div class="d-flex justify-content-between align-items-start gap-4">
                                    <div class="flex-shrink-0">
                                        <div class="import-section d-flex flex-column gap-2">
                                            <div class="import-form-group d-flex align-items-stretch">
                                                <div class="input-group input-group-sm import-input-wrapper">
                                                    <input type="file" class="form-control form-control-sm import-file-input" 
                                                        id="bulkDispatchFile" accept=".xlsx,.xls,.csv">
                                                    <button type="button" class="btn btn-success import-submit-btn d-inline-flex align-items-center gap-1" 
                                                        id="processBulkUpload">
                                                        <i class="mdi mdi-upload"></i>
                                                        <span>Process Upload</span>
                                                    </button>
                                                </div>
                                            </div>
                                            <a href="{{ route('inventory.download-format', $project->id) }}" 
                                                class="download-format-link" target="_blank">
                                                <i class="mdi mdi-download"></i>
                                                <span>Download Format</span>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="bulk-upload-instructions">
                                            <p class="mb-1"><strong>Bulk Upload Format:</strong></p>
                                            <p class="mb-1 small"><strong>Columns:</strong> ITEM_CODE, ITEM NAME (or item), serial_number (or SERIAL_NUMBER)</p>
                                            <p class="mb-1 small"><strong>For Luminary (SL02):</strong> Include sim_number (or SIM_NUMBER) column</p>
                                            <p class="mb-0 small"><strong>Note:</strong> Each row should have quantity = 1 for each serial number</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Already Dispatched Items Display -->
                                <div id="alreadyDispatchedSection" style="display: none;" class="mt-3">
                                    <div class="alert alert-warning">
                                        <strong>Already Dispatched Items:</strong>
                                        <button type="button" class="btn btn-sm btn-danger float-end" id="removeDispatchedBtn">
                                            <i class="mdi mdi-delete"></i> Remove All
                                        </button>
                                        <div id="alreadyDispatchedList" class="mt-2"></div>
                                    </div>
                                </div>

                                <!-- Invalid Items Display -->
                                <div id="invalidItemsSection" style="display: none;" class="mt-3">
                                    <div class="alert alert-danger">
                                        <strong>Invalid Items:</strong>
                                        <div id="invalidItemsList" class="mt-2"></div>
                                    </div>
                                </div>

                                <!-- Bulk Dispatch Preview Section -->
                                <div id="bulkDispatchPreview" style="display: none;" class="mt-4">
                                    <!-- Items Ready to Dispatch -->
                                    <div id="readyToDispatchSection" class="preview-section ready-to-dispatch mb-3">
                                        <h6 class="mb-2"><strong>Items Ready to Dispatch:</strong></h6>
                                        <div id="readyToDispatchList" class="serial-numbers-grid"></div>
                                    </div>

                                    <!-- Already Dispatched Items -->
                                    <div id="alreadyDispatchedPreviewSection" class="preview-section already-dispatched mb-3" style="display: none;">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="mb-0"><strong>Items Could not be Dispatched:</strong></h6>
                                            <button type="button" class="btn btn-sm btn-danger" id="removeAllDispatchedBtn">
                                                <i class="mdi mdi-delete"></i> Remove All
                                            </button>
                                        </div>
                                        <p class="text-muted small mb-2">Reason: Already Dispatched</p>
                                        <div id="alreadyDispatchedPreviewList" class="serial-numbers-grid"></div>
                                    </div>

                                    <!-- Duplicate Serial Numbers -->
                                    <div id="duplicateSerialsSection" class="preview-section duplicate-serials mb-3" style="display: none;">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="mb-0"><strong>Items Could not be Dispatched:</strong></h6>
                                            <button type="button" class="btn btn-sm btn-danger" id="removeAllDuplicatesBtn">
                                                <i class="mdi mdi-delete"></i> Remove All
                                            </button>
                                        </div>
                                        <p class="text-muted small mb-2">Reason: Duplicate serial numbers</p>
                                        <div id="duplicateSerialsList" class="serial-numbers-grid"></div>
                                    </div>

                                    <!-- Non Existing Items -->
                                    <div id="nonExistingSection" class="preview-section non-existing mb-3" style="display: none;">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="mb-0"><strong>Items Could not be Dispatched:</strong></h6>
                                            <button type="button" class="btn btn-sm btn-danger" id="removeAllNonExistingBtn">
                                                <i class="mdi mdi-delete"></i> Remove All
                                            </button>
                                        </div>
                                        <p class="text-muted small mb-2">Reason: Non existing items</p>
                                        <div id="nonExistingList" class="serial-numbers-grid"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Manual Entry Section -->
                            <div id="manualEntrySection">
                                <div class="d-flex justify-content-end align-items-center mb-3">
                                    <button type="button" class="btn btn-success btn-sm" id="addMoreItems">
                                        <i class="mdi mdi-plus"></i>
                                        Add More Items
                                    </button>
                                </div>
                                <!-- Dynamic Items Section -->
                                <div id="itemsContainer">
                                    <div class="item-row mb-3">
                                        <div class="row">
                                            <div class="col-sm-8 form-group">
                                                <label for="items">Item:</label>
                                                <select class="form-select item-select" name="item_code" required>
                                                    <option value="">Select Item</option>
                                                    @foreach ($inventoryItems as $item)
                                                        <option value="{{ $item->item_code }}"
                                                            data-stock="{{ $item->total_quantity }}"
                                                            data-item="{{ $item->item }}" data-rate="{{ $item->rate }}"
                                                            data-make="{{ $item->make }}" data-model="{{ $item->model }}">
                                                            {{ $item->item_code }} {{ $item->item }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <input type="hidden" name="item" id="item_namesss">
                                                <input type="hidden" name="rate" id="item_rate">
                                                <input type="hidden" name="make" id="item_make">
                                                <input type="hidden" name="model" id="item_model">
                                            </div>
                                            <div class="col-sm-4 form-group">
                                                <label for="quantity">Quantity:</label>
                                                <input type="number" class="form-control item-quantity"
                                                    name="total_quantity" min="1" required>
                                                <input type="hidden" name="total_value" id="total_value">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-4">
                                                <!-- QR Code Scanning -->
                                                <div class="form-group">
                                                    <label for="qr_scanner" class="form-label">Scan Item QR Code:</label>
                                                    <input type="text" id="qr_scanner" class="form-control" autofocus />
                                                    <small class="text-muted">Keep scanning QR codes...</small>
                                                    <div id="qr_error" class="text-danger mt-2"></div>
                                                </div>
                                            </div>
                                            <div class="col-sm-8">
                                                <!-- Scanned QR Codes List -->
                                                <ul id="scanned_qrs" class="list-group my-1"></ul>
                                                <div id="serial_numbers_container"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- End Manual Entry Section -->

                            <!-- Form Actions -->
                            <div class="d-flex justify-content-end gap-2 mt-3">
                                <button type="button" class="btn btn-primary printbtn" id="printButton">
                                    <i class="mdi mdi-printer"></i> Print
                                </button>
                                <button type="button" id="issueMaterial" class="btn btn-primary">
                                    <i class="mdi mdi-truck-delivery"></i> Issue Items
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Dispatched items summary inside Dispatch tab -->
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title mb-3">Already Dispatched Items</h6>

                        {{-- Filter Section --}}
                        <div class="card border rounded p-3 mb-3" style="background-color: #f8f9fa;">
                            <div class="d-flex flex-column flex-md-row align-items-end gap-3">
                                <div class="flex-fill">
                                    <label class="form-label small mb-1 fw-semibold">Item Code</label>
                                    <select name="dispatched_item_code" class="form-control form-control-sm filter-select" style="width: 100%;">
                                        <option value="">All Items</option>
                                        <option value="SL01">SL01 - Panel</option>
                                        <option value="SL02">SL02 - Luminary</option>
                                        <option value="SL03">SL03 - Battery</option>
                                        <option value="SL04">SL04 - Structure</option>
                                    </select>
                                </div>
                                <div class="flex-fill">
                                    <label class="form-label small mb-1 fw-semibold">Vendor</label>
                                    <select name="dispatched_vendor" id="dispatched_vendor_filter" class="form-control form-control-sm filter-select2" style="width: 100%;">
                                        <option value="">All Vendors</option>
                                        @foreach($assignedVendors as $vendor)
                                            <option value="{{ $vendor->name }}">{{ $vendor->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="flex-fill">
                                    <label class="form-label small mb-1 fw-semibold">Dispatch Date</label>
                                    <input type="date" name="dispatched_date" class="form-control form-control-sm filter-select" style="width: 100%;">
                                </div>
                                <div class="d-flex gap-2 align-items-end">
                                    <button type="button" id="applyDispatchedFiltersBtn" class="btn btn-primary btn-sm">
                                        Apply Filters
                                        </button>
                                    <button type="button" id="clearDispatchedFiltersBtn" class="btn btn-outline-secondary btn-sm">
                                        Clear
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Search and Export Section --}}
                        <div class="row align-items-center g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="mdi mdi-magnify"></i></span>
                                    <input type="search" class="form-control" id="dispatchTabDispatchedTable_search" placeholder="Search dispatched items...">
                                </div>
                            </div>
                            <div class="col-12 col-md-6 text-start text-md-end">
                                <div class="btn-group btn-group-sm d-flex flex-wrap" role="group">
                                    <button type="button" class="btn btn-success flex-fill flex-sm-auto" id="dispatchTabDispatchedTable_excel" title="Export to Excel">
                                        <i class="mdi mdi-file-excel"></i> <span class="d-none d-sm-inline">Excel</span>
                                    </button>
                                    <button type="button" class="btn btn-danger flex-fill flex-sm-auto" id="dispatchTabDispatchedTable_pdf" title="Export to PDF">
                                        <i class="mdi mdi-file-pdf"></i> <span class="d-none d-sm-inline">PDF</span>
                                    </button>
                                    <button type="button" class="btn btn-info flex-fill flex-sm-auto" id="dispatchTabDispatchedTable_print" title="Print">
                                        <i class="mdi mdi-printer"></i> <span class="d-none d-sm-inline">Print</span>
                                    </button>
                                    <button type="button" class="btn btn-secondary flex-fill flex-sm-auto" id="dispatchTabDispatchedTable_columns" title="Show/Hide Columns">
                                        <i class="mdi mdi-eye"></i> <span class="d-none d-sm-inline">Columns</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive" style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
                            <table id="dispatchTabDispatchedTable" class="table table-striped table-bordered table-hover" style="width:100%;" data-server-side="true">
                                <thead>
                                    <tr>
                                        <th width="30px"><input type="checkbox" id="dispatchTabDispatchedTable_selectAll" class="select-all-checkbox"></th>
                                        <th>Item Code</th>
                                        <th>Item</th>
                                        <th>Serial Number</th>
                                        <th>Vendor</th>
                                        <th>Dispatch Date</th>
                                        <th>Value</th>
                                        <th width="120px" class="text-center">Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                    {{-- Server-side processing: tbody is empty, data loaded via AJAX --}}
                                </tbody>
                            </table>
                    </div>
                        
                        <div class="mt-3 d-flex flex-column gap-2">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <div class="small text-muted" id="dispatchTabDispatchedTable_info"></div>
                                <div id="dispatchTabDispatchedTable_paginate"></div>
                </div>
                            <div class="d-flex align-items-center gap-2">
                                <label class="mb-0 small fw-semibold">Show:</label>
                                <select class="form-control form-control-sm" id="dispatchTabDispatchedTable_length" style="width: auto;">
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50" selected>50</option>
                                    <option value="100">100</option>
                                </select>
                                <span class="small">entries</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                {{-- Server-side DataTable for Dispatched Items --}}
                @push('scripts')
                <script>
                // Set flag to prevent component auto-initialization
                window['skipAutoInit_dispatchTabDispatchedTable'] = true;
                
                $(document).ready(function() {
                    function initializeDispatchedTable() {
                        // Check if already initialized
                        if ($.fn.DataTable.isDataTable('#dispatchTabDispatchedTable')) {
                            return;
                        }
                        
                        var table = $('#dispatchTabDispatchedTable').DataTable({
                            processing: true,
                            serverSide: true,
                            ajax: {
                                url: '{{ route("store.dispatched.data", $store->id) }}',
                                type: 'GET',
                                data: function(d) {
                                    d.item_code = $('select[name="dispatched_item_code"]').val() || '';
                                    d.vendor_name = $('#dispatched_vendor_filter').val() || '';
                                    d.dispatch_date = $('input[name="dispatched_date"]').val() || '';
                                }
                            },
                            columns: [
                                { data: 0, name: 'checkbox', orderable: false, searchable: false },
                                { data: 1, name: 'item_code' },
                                { data: 2, name: 'item' },
                                { data: 3, name: 'serial_number' },
                                { data: 4, name: 'vendor_name' },
                                { data: 5, name: 'dispatch_date' },
                                { data: 6, name: 'total_value' },
                                { data: 7, name: 'actions', orderable: false, searchable: false }
                            ],
                            order: [[5, 'desc']],
                            pageLength: 50,
                            deferLoading: null,
                            dom: "<'row'<'col-sm-12'tr>>" +
                                "<'row'<'col-sm-5'i><'col-sm-7'p>>",
                            buttons: [
                                {
                                    extend: 'excel',
                                    text: '<i class="mdi mdi-file-excel"></i> Excel',
                                    className: 'd-none',
                                    exportOptions: {
                                        columns: ':visible:not(.no-export)',
                                        format: {
                                            body: function(data) {
                                                if (typeof data === 'string') {
                                                    return data.replace(/<[^>]*>/g, '').trim();
                                                }
                                                return data;
                                            }
                                        }
                                    }
                                },
                                {
                                    extend: 'pdf',
                                    text: '<i class="mdi mdi-file-pdf"></i> PDF',
                                    className: 'd-none',
                                    orientation: 'landscape',
                                    pageSize: 'A4',
                                    exportOptions: {
                                        columns: ':visible:not(.no-export)',
                                        format: {
                                            body: function(data) {
                                                if (typeof data === 'string') {
                                                    return data.replace(/<[^>]*>/g, '').trim();
                                                }
                                                return data;
                                            }
                                        }
                                    }
                                },
                                {
                                    extend: 'print',
                                    text: '<i class="mdi mdi-printer"></i> Print',
                                    className: 'd-none',
                                    exportOptions: {
                                        columns: ':visible:not(.no-export)',
                                        format: {
                                            body: function(data) {
                                                if (typeof data === 'string') {
                                                    return data.replace(/<[^>]*>/g, '').trim();
                                                }
                                                return data;
                                            }
                                        }
                                    }
                                },
                                {
                                    extend: 'colvis',
                                    text: '<i class="mdi mdi-eye"></i> Columns',
                                    className: 'd-none',
                                    columns: ':not(.no-colvis)',
                                    collectionLayout: 'three-column',
                                    postfixButtons: ['colvisRestore']
                                }
                            ],
                            language: {
                                processing: '<div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Loading...</span></div> Loading data...',
                                search: '',
                                searchPlaceholder: 'Search dispatched items...',
                                lengthMenu: '',
                                info: '',
                                infoEmpty: '',
                                infoFiltered: ''
                            },
                            pagingType: 'simple_numbers',
                            drawCallback: function() {
                                var info = this.api().page.info();
                                var totalRecords = info.recordsTotal;
                                var filteredRecords = info.recordsFiltered;
                                
                                var infoText = 'Showing ' + (info.start + 1) + ' to ' + info.end + ' of ' + filteredRecords + ' entries';
                                if (filteredRecords < totalRecords) {
                                    infoText += ' (filtered from ' + totalRecords + ' total entries)';
                                }
                                $('#dispatchTabDispatchedTable_info').text(infoText);
                                
                                // Move pagination to custom wrapper
                                var $dtPagination = $('#dispatchTabDispatchedTable_wrapper .dataTables_paginate');
                                var $customPagination = $('#dispatchTabDispatchedTable_paginate');
                                
                                if ($dtPagination.length > 0 && $dtPagination.parent()[0] !== $customPagination[0]) {
                                    $dtPagination.appendTo($customPagination);
                                }
                                
                                // Handle select all checkbox
                                $('#dispatchTabDispatchedTable_selectAll').off('change').on('change', function() {
                                    var isChecked = $(this).is(':checked');
                                    $('#dispatchTabDispatchedTable tbody input[type="checkbox"]').prop('checked', isChecked);
                                });
                                
                                // Reattach delete handlers
                                $('#dispatchTabDispatchedTable').off('click', '.delete-item').on('click', '.delete-item', function() {
                                    var id = $(this).data('id');
                                    var url = $(this).data('url');
                                    if (confirm('Are you sure you want to delete this item?')) {
                                        $.ajax({
                                            url: url,
                                            type: 'DELETE',
                                            data: {
                                                _token: '{{ csrf_token() }}'
                                            },
                                            success: function() {
                                                table.ajax.reload();
                                            }
                                        });
                                    }
                                });
                            }
                        });
                        
                        window['table_dispatchTabDispatchedTable'] = table;
                        
                        // Handle length change
                        $('#dispatchTabDispatchedTable_length').off('change').on('change', function() {
                            table.page.len(parseInt($(this).val())).draw();
                        });
                        
                        // Handle filter buttons
                        $('#applyDispatchedFiltersBtn').off('click').on('click', function() {
                            table.ajax.reload();
                        });
                        
                        $('#clearDispatchedFiltersBtn').off('click').on('click', function() {
                            $('select[name="dispatched_item_code"]').val('').trigger('change');
                            $('#dispatched_vendor_filter').val(null).trigger('change');
                            $('input[name="dispatched_date"]').val('');
                            table.ajax.reload();
                        });
                        
                        // Initialize Select2 for Vendor dropdown
                        $('#dispatched_vendor_filter').select2({
                            placeholder: 'Vendor',
                            allowClear: true,
                            width: '100%',
                            dropdownParent: $('#dispatch'),
                            minimumResultsForSearch: 0,
                        });
                        
                        // Handle search input
                        var searchTimeout;
                        $('#dispatchTabDispatchedTable_search').off('keyup input').on('keyup input', function() {
                            var searchValue = $(this).val();
                            clearTimeout(searchTimeout);
                            searchTimeout = setTimeout(function() {
                                table.search(searchValue).draw();
                            }, 300);
                        });
                        
                        // Handle Enter key in search
                        $('#dispatchTabDispatchedTable_search').off('keypress').on('keypress', function(e) {
                            if (e.which === 13) {
                                e.preventDefault();
                                clearTimeout(searchTimeout);
                                table.search($(this).val()).draw();
                            }
                        });
                        
                        // Wire up export buttons
                        $('#dispatchTabDispatchedTable_excel').off('click').on('click', function() {
                            table.button('.buttons-excel').trigger();
                        });
                        
                        $('#dispatchTabDispatchedTable_pdf').off('click').on('click', function() {
                            table.button('.buttons-pdf').trigger();
                        });
                        
                        $('#dispatchTabDispatchedTable_print').off('click').on('click', function() {
                            table.button('.buttons-print').trigger();
                        });
                        
                        $('#dispatchTabDispatchedTable_columns').off('click').on('click', function(e) {
                            e.preventDefault();
                            table.button('.buttons-colvis').trigger();
                        });
                        
                        // Event delegation for pagination
                        $('#dispatchTabDispatchedTable_paginate').off('click', 'a').on('click', 'a', function(e) {
                            if ($(this).hasClass('paginate_button') && !$(this).hasClass('disabled') && !$(this).hasClass('current')) {
                                var href = $(this).attr('href');
                                if (href === '#' || !href) {
                                    e.preventDefault();
                                    var text = $(this).text().trim();
                                    if (text === 'Previous') {
                                        table.page('previous').draw('page');
                                    } else if (text === 'Next') {
                                        table.page('next').draw('page');
                                    } else if (!isNaN(text)) {
                                        table.page(parseInt(text) - 1).draw('page');
                                    }
                                }
                            }
                        });
                    }
                    
                    // Initialize when dispatch tab is shown
                    $('#dispatch-tab, [data-bs-target="#dispatch"]').on('shown.bs.tab', function() {
                        setTimeout(function() {
                            if (!$.fn.DataTable.isDataTable('#dispatchTabDispatchedTable')) {
                                initializeDispatchedTable();
                            }
                        }, 100);
                    });
                    
                    $('#dispatch').on('shown.bs.tab', function() {
                        setTimeout(function() {
                            if (!$.fn.DataTable.isDataTable('#dispatchTabDispatchedTable')) {
                                initializeDispatchedTable();
                            }
                        }, 100);
                    });
                    
                    // Initialize immediately if tab is already active
                    if ($('#dispatch').hasClass('active') && $('#dispatch').hasClass('show')) {
                        setTimeout(function() {
                            initializeDispatchedTable();
                        }, 500);
                    }
                });
                </script>
                @endpush
            </div>
        </div>

        <!-- Replace Item Modal -->
        <div class="modal fade" id="replaceItemModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Replace Item</h5>
                        <button type="button" class="close" data-bs-dismiss="modal">&times;</button>
                    </div>
                    <form id="replaceItemForm" action="{{ route('inventory.replace') }}" method="POST">
                        @csrf
                        <input type="hidden" name="item_id" id="replace_dispatch_id">
                        <input type="hidden" name="old_serial_number" id="replace_old_serial">
                        <div class="modal-body">
                            <div class="form-group mb-3">
                                <label for="new_serial_number">New Serial Number:</label>
                                <input type="text" class="form-control" id="new_serial_number"
                                    name="new_serial_number" required>
                            </div>
                            <div class="form-group mb-3">
                                <label for="authentication_code">Authentication Code:</label>
                                <input type="text" class="form-control" id="authentication_code"
                                    name="authentication_code" required>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="agreement_checkbox"
                                    name="agreement_checkbox" value="1" required>
                                <label class="form-check-label" for="agreement_checkbox">
                                    I agree to replace this item
                                </label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Replace Item</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Activate correct tab based on URL hash (e.g. #view after successful save)
            const hash = window.location.hash;
            if (hash === '#view') {
                const viewTabTrigger = document.querySelector('#view-tab');
                if (viewTabTrigger && window.bootstrap && bootstrap.Tab) {
                    const tab = new bootstrap.Tab(viewTabTrigger);
                    tab.show();
                } else if (viewTabTrigger) {
                    // Fallback: manually switch active classes
                    document.querySelectorAll('#storeTabs .nav-link').forEach(btn => {
                        btn.classList.remove('active');
                    });
                    document.querySelectorAll('#storeTabContent .tab-pane').forEach(pane => {
                        pane.classList.remove('show', 'active');
                    });
                    viewTabTrigger.classList.add('active');
                    const viewPane = document.querySelector('#view');
                    if (viewPane) {
                        viewPane.classList.add('show', 'active');
                    }
                }
            }
            // Set default received date to current date
            const receivedDateField = document.getElementById('receiveddate');
            if (receivedDateField && !receivedDateField.value) {
                const today = new Date().toISOString().split('T')[0];
                receivedDateField.value = today;
            }

            // Calculate Total Value = Rate * 1
            const rateField = document.getElementById('rate');
            const totalValueField = document.getElementById('totalvalue');
            function calculateTotalValue() {
                if (rateField && totalValueField) {
                    const rate = parseFloat(rateField.value) || 0;
                    totalValueField.value = (rate * 1).toFixed(2);
                }
            }
            
            if (rateField) {
                rateField.addEventListener('input', calculateTotalValue);
                rateField.addEventListener('change', calculateTotalValue);
                // Set initial value
                calculateTotalValue();
            }

            // Handle item selection
            const itemCombined = document.getElementById('item_combined');
            const simNumberWrapper = document.getElementById('sim_number_wrapper');
            const simNumberField = document.getElementById('sim_number');
            const serialField = document.getElementById('serialnumber');
            const importForm = document.getElementById('importInventoryForm');
            const importOverlay = document.getElementById('importOverlay');
            
            function toggleSimNumberField(itemCode) {
                if (simNumberWrapper && simNumberField) {
                    if (itemCode === 'SL02') {
                        // Show and make required for Luminary
                        simNumberWrapper.style.display = 'block';
                        simNumberField.setAttribute('required', 'required');
                    } else {
                        // Hide and remove required for other items
                        simNumberWrapper.style.display = 'none';
                        simNumberField.removeAttribute('required');
                        simNumberField.value = '';
                        simNumberField.classList.remove('is-invalid', 'is-valid');
                    }
                }
            }

            if (itemCombined) {
                itemCombined.addEventListener('change', function() {
                    const [code, name] = this.value.split('|');
                    document.getElementById('item_code').value = code || '';
                    document.getElementById('item_name').value = name || '';

                    // Toggle SIM number field visibility
                    toggleSimNumberField(code);

                    // Clear validation state when item changes
                    this.classList.remove('is-invalid', 'is-valid');
                    const feedback = this.parentElement.querySelector('.invalid-feedback');
                    if (feedback && !feedback.classList.contains('d-block')) {
                        feedback.classList.add('d-none');
                    }
                });
                
                // Initialize SIM number field visibility on page load
                if (itemCombined.value) {
                    const [code] = itemCombined.value.split('|');
                    toggleSimNumberField(code);
                }
            }

            // Show overlay during bulk import to smooth transitions
            if (importForm && importOverlay) {
                importForm.addEventListener('submit', function () {
                    importOverlay.classList.remove('d-none');
                });
            }

            // Real-time serial number uniqueness validation (AJAX)
            if (serialField) {
                let serialCheckTimeout = null;
                const serialFeedback = serialField.parentElement.querySelector('.invalid-feedback') || null;

                async function checkSerialUnique() {
                    const value = serialField.value.trim();
                    if (!value) {
                        // Don't check empty value
                        return;
                    }

                    try {
                        const token = document.querySelector('meta[name=\"csrf-token\"]')?.getAttribute('content');
                        const response = await fetch('{{ route('inventory.checkSerial') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': token || ''
                            },
                            body: JSON.stringify({
                                project_type: {{ $project->project_type }},
                                project_id: {{ $project->id }},
                                store_id: {{ $store->id }},
                                serialnumber: value
                            })
                        });

                        if (!response.ok) {
                            // On error, don't block user, just log to console
                            console.error('Serial check failed with status', response.status);
                            return;
                        }

                        const data = await response.json();

                        if (data.exists) {
                            serialField.classList.add('is-invalid');
                            serialField.classList.remove('is-valid');
                            if (serialFeedback) {
                                serialFeedback.textContent = data.message || 'This serial number is already in use.';
                                serialFeedback.classList.remove('d-none');
                                serialFeedback.classList.add('d-block');
                            }
                        } else {
                            serialField.classList.remove('is-invalid');
                            serialField.classList.add('is-valid');
                            if (serialFeedback) {
                                serialFeedback.classList.remove('d-block');
                                serialFeedback.classList.add('d-none');
                            }
                        }
                    } catch (error) {
                        console.error('Error checking serial number:', error);
                    }
                }

                // Debounce input to avoid spamming server
                serialField.addEventListener('input', function () {
                    serialField.classList.remove('is-valid'); // reset while typing
                    if (serialCheckTimeout) {
                        clearTimeout(serialCheckTimeout);
                    }
                    serialCheckTimeout = setTimeout(checkSerialUnique, 400);
                });

                serialField.addEventListener('blur', function () {
                    if (serialCheckTimeout) {
                        clearTimeout(serialCheckTimeout);
                    }
                    checkSerialUnique();
                });
            }

            // Form validation
            const addInventoryForm = document.getElementById('addInventoryForm');
            if (addInventoryForm) {
                // Real-time validation on input
                const inputs = addInventoryForm.querySelectorAll('input[required], select[required]');
                inputs.forEach(input => {
                    input.addEventListener('blur', function() {
                        validateField(this);
                    });

                    input.addEventListener('input', function() {
                        if (this.classList.contains('is-invalid')) {
                            validateField(this);
                        }
                    });
                });

                // Form submission validation
                addInventoryForm.addEventListener('submit', function(e) {
                    let isValid = true;
                    
                    // Get all required fields including dynamically required ones
                    const allRequiredFields = addInventoryForm.querySelectorAll('input[required], select[required]');
                    allRequiredFields.forEach(input => {
                        if (!validateField(input)) {
                            isValid = false;
                        }
                    });

                    // Validate item selection
                    if (itemCombined && !itemCombined.value) {
                        itemCombined.classList.add('is-invalid');
                        const feedback = itemCombined.parentElement.querySelector('.invalid-feedback');
                        if (feedback) {
                            feedback.classList.remove('d-none');
                            feedback.classList.add('d-block');
                        }
                        isValid = false;
                    }
                    
                    // Validate SIM number if SL02 is selected
                    if (itemCombined && itemCombined.value.includes('SL02')) {
                        if (simNumberField && (!simNumberField.value || !simNumberField.value.trim())) {
                            simNumberField.classList.add('is-invalid');
                            const simFeedback = simNumberField.parentElement.querySelector('.invalid-feedback');
                            if (simFeedback) {
                                simFeedback.classList.remove('d-none');
                                simFeedback.classList.add('d-block');
                            }
                            isValid = false;
                        }
                    }

                    if (!isValid) {
                        e.preventDefault();
                        e.stopPropagation();
                        // Scroll to first invalid field
                        const firstInvalid = addInventoryForm.querySelector('.is-invalid');
                        if (firstInvalid) {
                            firstInvalid.scrollIntoView({
                                behavior: 'smooth',
                                block: 'center'
                            });
                            firstInvalid.focus();
                        }
                    }

                    addInventoryForm.classList.add('was-validated');
                });
            }

            function validateField(field) {
                const feedback = field.parentElement.querySelector('.invalid-feedback');

                if (field.hasAttribute('required') && !field.value.trim()) {
                    field.classList.remove('is-valid');
                    field.classList.add('is-invalid');
                    if (feedback) {
                        feedback.classList.remove('d-none');
                        feedback.classList.add('d-block');
                    }
                    return false;
                }

                // Validate number fields
                if (field.type === 'number') {
                    const value = parseFloat(field.value);
                    if (isNaN(value) || value < 0) {
                        field.classList.remove('is-valid');
                        field.classList.add('is-invalid');
                        if (feedback) {
                            feedback.classList.remove('d-none');
                            feedback.classList.add('d-block');
                        }
                        return false;
                    }
                }

                // Validate date fields
                if (field.type === 'date') {
                    if (!field.value) {
                        field.classList.remove('is-valid');
                        field.classList.add('is-invalid');
                        if (feedback) {
                            feedback.classList.remove('d-none');
                            feedback.classList.add('d-block');
                        }
                        return false;
                    }
                }

                // Valid field
                field.classList.remove('is-invalid');
                field.classList.add('is-valid');
                if (feedback) {
                    feedback.classList.add('d-none');
                    feedback.classList.remove('d-block');
                }
                return true;
            }


            // REMOVED: Custom length menu code - the x-datatable component handles this automatically
            // The datatable component already removes the top length menu and creates a custom bottom one


            // Delete item handler
            $(document).on('click', '.delete-item', function() {
                const itemId = $(this).data('id');
                const deleteUrl = '{{ url('inventory') }}/' + itemId;

                Swal.fire({
                    title: 'Are you sure?',
                    text: 'This will permanently delete this item.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: deleteUrl,
                            method: 'POST',
                            data: {
                                _method: 'DELETE',
                                _token: $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire('Deleted!', response.message, 'success')
                                        .then(() => {
                                            location.reload();
                                        });
                                } else {
                                    Swal.fire('Error!', response.message ||
                                        'Failed to delete item', 'error');
                                }
                            },
                            error: function() {
                                Swal.fire('Error!', 'Failed to delete item', 'error');
                            }
                        });
                    }
                });
            });

            // Replace item handler
            $(document).on('click', '.replace-item', function() {
                const dispatchId = $(this).data('dispatch-id');
                const serialNumber = $(this).data('serial-number');

                $('#replace_dispatch_id').val(dispatchId);
                $('#replace_old_serial').val(serialNumber);
                $('#replaceItemModal').modal('show');
            });

            @if ($project->project_type == 1)
                // Store chart instances to prevent re-rendering
                let stockChartInstance = null;
                let itemChartInstance = null;

                // Stock Distribution Chart
                const stockCtx = document.getElementById('stockChart');
                if (stockCtx && !stockChartInstance) {
                    stockChartInstance = new Chart(stockCtx, {
                        type: 'pie',
                        data: {
                            labels: ['In Store', 'Dispatched'],
                            datasets: [{
                                data: [{{ $inStoreStockValue }}, {{ $dispatchedStockValue }}],
                                backgroundColor: ['#28a745', '#ffc107'],
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                }
                            }
                        }
                    });
                }

                // Item-wise Distribution Chart
                const itemCtx = document.getElementById('itemChart');
                if (itemCtx && !itemChartInstance && @json(!empty($itemStats))) {
                    const itemLabels = @json(array_column($itemStats ?? [], 'name'));
                    const itemData = @json(array_column($itemStats ?? [], 'total'));

                    if (itemLabels.length > 0 && itemData.length > 0) {
                        itemChartInstance = new Chart(itemCtx, {
                            type: 'pie',
                            data: {
                                labels: itemLabels,
                                datasets: [{
                                    data: itemData,
                                    backgroundColor: ['#007bff', '#28a745', '#ffc107', '#dc3545'],
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: true,
                                aspectRatio: 1,
                                plugins: {
                                    legend: {
                                        position: 'bottom',
                                    }
                                }
                            }
                        });
                    }
                }
            @endif

            // ========== Dispatch Form JavaScript ==========
            const itemsContainer = document.getElementById('itemsContainer');
            const addMoreItemsButton = document.getElementById('addMoreItems');
            let availableQuantity = 0;
            let scannedQRs = [];
            let loadingIssue = false;

            // Add New Item Row
            let rowCount = 1;
            if (addMoreItemsButton) {
                addMoreItemsButton.addEventListener("click", function() {
                    const originalRow = document.querySelector(".item-row");
                    if (!originalRow) return;

                    const newItemRow = originalRow.cloneNode(true);
                    rowCount++;

                    newItemRow.querySelector(".item-select").value = "";
                    newItemRow.querySelector(".item-quantity").value = "";

                    const scannedList = newItemRow.querySelector("#scanned_qrs");
                    if (scannedList) {
                        scannedList.innerHTML = "";
                        scannedList.id = `scanned_qrs_${rowCount}`;
                    }

                    const qrScannerInput = newItemRow.querySelector("#qr_scanner");
                    if (qrScannerInput) {
                        qrScannerInput.value = "";
                        qrScannerInput.setAttribute("data-row", rowCount);
                    }

                    const serialContainer = newItemRow.querySelector("#serial_numbers_container");
                    if (serialContainer) {
                        serialContainer.id = `serial_numbers_container_${rowCount}`;
                    }

                    let removeButton = newItemRow.querySelector(".remove-item-btn");
                    if (!removeButton) {
                        removeButton = document.createElement("button");
                        removeButton.className = "btn btn-danger btn-sm remove-item-btn m-1";
                        removeButton.innerHTML = '<i class="mdi mdi-delete"></i> Remove';
                        newItemRow.appendChild(removeButton);
                    }
                    itemsContainer.appendChild(newItemRow);
                });
            }

            // Remove Item Row
            if (itemsContainer) {
                itemsContainer.addEventListener("click", function(e) {
                    if (e.target.closest(".remove-item-btn")) {
                        const rows = itemsContainer.querySelectorAll(".item-row");
                        if (rows.length > 1) {
                            e.target.closest(".item-row").remove();
                        }
                    }
                });
            }

            // Handle QR Scanning
            const qrScanner = document.getElementById('qr_scanner');
            if (qrScanner) {
                qrScanner.addEventListener('keyup', function(event) {
                    if (event.key === 'Enter' && this.value.trim() !== '') {
                        let scannedCode = this.value.trim();
                        this.value = '';

                        if (scannedQRs.includes(scannedCode)) {
                            showError('QR code already scanned!', 'qr_error');
                            return;
                        }
                        
                        const currentRow = this.closest('.item-row');
                        if (!currentRow) {
                            showError('Cannot determine which item row this scanner belongs to!', 'qr_error');
                            return;
                        }
                        
                        const selectedItemCode = currentRow.querySelector('.item-select').value;
                        if (!selectedItemCode) {
                            showError('Please select an item first before scanning QR codes!', 'qr_error');
                            return;
                        }
                        
                        if (selectedItemCode === "SL02") {
                            scannedCode = scannedCode.split(';')[0];
                        }

                        const storeId = document.getElementById('dispatchStoreId').value;
                        
                        fetch('{{ route('inventory.checkQR') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({
                                    qr_code: scannedCode,
                                    store_id: storeId,
                                    item_code: selectedItemCode
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.exists) {
                                    scannedQRs.push(scannedCode);
                                    updateScannedQRs();
                                    addSerialNumberInput(scannedCode);
                                    updateQuantityAndTotal();
                                    clearError();
                                } else {
                                    showError('Invalid QR code! Item not found in inventory.', 'qr_error');
                                }
                            })
                            .catch(() => showError('Error checking QR code!', 'qr_error'));
                    }
                });
            }

            function showError(message, context) {
                const errorElement = document.getElementById(context);
                if (errorElement) {
                    errorElement.textContent = message;
                }
            }

            function clearError() {
                const errorElement = document.getElementById('qr_error');
                if (errorElement) {
                    errorElement.textContent = '';
                }
            }

            // Validate Quantity Against Stock
            if (itemsContainer) {
                itemsContainer.addEventListener('input', function(e) {
                    if (e.target.classList.contains('item-quantity')) {
                        const select = e.target.closest('.item-row').querySelector('.item-select');
                        if (select.selectedIndex > 0) {
                            const stock = select.selectedOptions[0].getAttribute('data-stock');
                            if (parseInt(e.target.value) > parseInt(stock)) {
                                alert('Quantity cannot exceed stock.');
                                e.target.value = stock;
                            }
                        }
                    }
                });
            }

            const itemSelect = document.querySelector('.item-select');
            if (itemSelect) {
                itemSelect.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    document.getElementById('item_namesss').value = selectedOption.dataset.item || '';
                    document.getElementById('item_rate').value = selectedOption.dataset.rate || '';
                    document.getElementById('item_make').value = selectedOption.dataset.make || '';
                    document.getElementById('item_model').value = selectedOption.dataset.model || '';
                    
                    scannedQRs = [];
                    updateScannedQRs();
                    updateQuantityAndTotal();
                });
            }

            function updateScannedQRs() {
                const list = document.getElementById('scanned_qrs');
                if (!list) return;

                list.innerHTML = '';

                scannedQRs.forEach((qr, index) => {
                    const li = document.createElement('li');
                    li.className = 'list-group-item';

                    const wrapper = document.createElement('div');
                    wrapper.className = 'd-flex justify-content-between align-items-center';

                    const qrText = document.createElement('span');
                    qrText.textContent = qr;

                    const deleteBtn = document.createElement('button');
                    deleteBtn.type = 'button';
                    deleteBtn.className = 'btn btn-sm btn-danger';
                    deleteBtn.innerHTML = '&times;';
                    deleteBtn.onclick = (e) => {
                        e.preventDefault();
                        scannedQRs.splice(index, 1);
                        updateScannedQRs();
                        updateQuantityAndTotal();
                    };

                    wrapper.appendChild(qrText);
                    wrapper.appendChild(deleteBtn);
                    li.appendChild(wrapper);
                    list.appendChild(li);
                });
            }

            function addSerialNumberInput(serialNumber) {
                const container = document.getElementById('serial_numbers_container');
                if (container) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'serial_numbers[]';
                    input.value = serialNumber;
                    container.appendChild(input);
                }
            }

            function updateQuantityAndTotal() {
                const quantityInput = document.querySelector('.item-quantity');
                const rate = parseFloat(document.getElementById('item_rate').value) || 0;
                const quantity = scannedQRs.length;
                if (quantityInput) {
                    quantityInput.value = quantity;
                }
                const totalValue = rate * quantity;
                const totalValueInput = document.getElementById('total_value');
                if (totalValueInput) {
                    totalValueInput.value = totalValue.toFixed(2);
                }
            }

            // Print Functionality
            const printButton = document.getElementById('printButton');
            if (printButton) {
                printButton.addEventListener('click', function(e) {
                    e.preventDefault();

                    const vendorSelect = document.getElementById('vendorName');
                    if (vendorSelect.selectedIndex === 0) {
                        alert('Please select a vendor first.');
                        return;
                    }
                    const vendorName = vendorSelect.options[vendorSelect.selectedIndex].textContent;

                    // Check if in bulk mode
                    const bulkPreview = document.getElementById('bulkDispatchPreview');
                    const isBulkMode = bulkPreview && bulkPreview.style.display !== 'none';

                    let itemsData = [];
                    let nonDispatchableItems = [];

                    if (isBulkMode) {
                        // Bulk mode: Include all items (dispatchable and non-dispatchable)
                        
                        // Group valid items by item_code
                        const groupedItems = {};
                        bulkDispatchPreviewData.validItems.forEach(item => {
                            const key = item.item_code;
                            if (!groupedItems[key]) {
                                groupedItems[key] = {
                                    code: item.item_code,
                                    name: item.item,
                                    rate: item.rate,
                                    make: item.make || '',
                                    model: item.model || '',
                                    serials: []
                                };
                            }
                            groupedItems[key].serials.push(item.serial_number);
                        });

                        itemsData = Object.values(groupedItems).map(item => ({
                            ...item,
                            quantity: item.serials.length
                        }));

                        // Collect non-dispatchable items with error messages
                        // Already dispatched items
                        bulkDispatchPreviewData.alreadyDispatched.forEach(item => {
                            nonDispatchableItems.push({
                                code: item.item_code || 'N/A',
                                name: item.item || 'N/A',
                                serial: item.serial_number || 'N/A',
                                error: 'Already Dispatched'
                            });
                        });

                        // Duplicate serial numbers
                        bulkDispatchPreviewData.duplicateSerials.forEach(item => {
                            nonDispatchableItems.push({
                                code: item.item_code || 'N/A',
                                name: item.item || 'N/A',
                                serial: item.serial_number || 'N/A',
                                error: 'Duplicate serial numbers'
                            });
                        });

                        // Non-existing items
                        bulkDispatchPreviewData.nonExisting.forEach(item => {
                            nonDispatchableItems.push({
                                code: item.item_code || 'N/A',
                                name: item.item || 'N/A',
                                serial: item.serial_number || 'N/A',
                                error: item.reason || 'Non existing items'
                            });
                        });
                    } else {
                        // Manual entry mode (existing behavior)
                    const itemRows = document.querySelectorAll('#itemsContainer .item-row');

                    itemRows.forEach(row => {
                        const itemSelect = row.querySelector('.item-select');
                        if (itemSelect.selectedIndex === 0) return;

                        const selectedOption = itemSelect.options[itemSelect.selectedIndex];
                        const scannedQRsList = row.querySelector('ul.list-group.my-1');
                        const scannedQRs = Array.from(scannedQRsList.querySelectorAll('li')).map(
                            li => li.textContent);

                        itemsData.push({
                            code: selectedOption.value,
                            name: selectedOption.dataset.item,
                            rate: selectedOption.dataset.rate,
                            make: selectedOption.dataset.make,
                            model: selectedOption.dataset.model,
                            quantity: row.querySelector('.item-quantity').value,
                            serials: scannedQRs
                        });
                    });
                    }

                    if (itemsData.length === 0 && nonDispatchableItems.length === 0) {
                        alert('Please add at least one item to print.');
                        return;
                    }

                    const printWindow = window.open('');
                    printWindow.document.write(`
                        <html>
                          <head>
                            <title>Dispatch Report</title>
                            <style>
                              body { font-family: Arial; margin: 20px; }
                              .header { text-align: center; margin-bottom: 30px; }
                              table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                              th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
                              th { background-color: #f5f5f5; }
                              .serial-list { max-width: 300px; word-break: break-all; }
                              .section-title { margin-top: 30px; margin-bottom: 15px; font-size: 18px; font-weight: bold; color: #333; }
                              .error-row { background-color: #fff5f5; }
                              .error-cell { color: #dc3545; font-weight: bold; }
                            </style>
                          </head>
                          <body>
                            <div class="header">
                              <h2>Inventory Dispatch Report</h2>
                              <p><strong>Vendor:</strong> ${vendorName}</p>
                              <p><strong>Date:</strong> ${new Date().toLocaleDateString()}</p>
                            </div>
                            
                            ${itemsData.length > 0 ? `
                            <div class="section-title">Items Ready to Dispatch</div>
                            <table>
                              <thead>
                                <tr>
                                  <th>Item Code</th>
                                  <th>Item Name</th>
                                  <th>Quantity</th>
                                  <th>Rate</th>
                                  <th>Make/Model</th>
                                  <th>Serial Numbers</th>
                                </tr>
                              </thead>
                              <tbody>
                                ${itemsData.map(item => `
                                  <tr>
                                    <td>${item.code}</td>
                                    <td>${item.name}</td>
                                    <td>${item.quantity}</td>
                                    <td>₹${item.rate}</td>
                                    <td>${item.make} ${item.model}</td>
                                    <td class="serial-list">${item.serials.join(', ')}</td>
                                  </tr>
                                `).join('')}
                              </tbody>
                            </table>
                            ` : ''}

                            ${nonDispatchableItems.length > 0 ? `
                            <div class="section-title">Items Could not be Dispatched</div>
                            <table>
                              <thead>
                                <tr>
                                  <th>Item Code</th>
                                  <th>Item Name</th>
                                  <th>Serial Number</th>
                                  <th>Error/Reason</th>
                                </tr>
                              </thead>
                              <tbody>
                                ${nonDispatchableItems.map(item => `
                                  <tr class="error-row">
                                    <td>${item.code}</td>
                                    <td>${item.name}</td>
                                    <td>${item.serial}</td>
                                    <td class="error-cell">${item.error}</td>
                                  </tr>
                                `).join('')}
                              </tbody>
                            </table>
                            ` : ''}

                            <script>
                              window.onload = function() {
                                window.print();
                                setTimeout(() => window.close(), 500);
                              }
                            <\/script>
                          </body>
                        </html>
                      `);
                    printWindow.document.close();
                });
            }

            // Issue Material Button
            const issueMaterialBtn = document.getElementById('issueMaterial');
            if (issueMaterialBtn) {
                issueMaterialBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Check if in bulk mode (preview is visible)
                    const bulkPreview = document.getElementById('bulkDispatchPreview');
                    const isBulkMode = bulkPreview && bulkPreview.style.display !== 'none' && 
                                      bulkDispatchPreviewData.validItems.length > 0;

                    if (isBulkMode) {
                        // Bulk dispatch mode
                        const vendorId = document.getElementById('vendorName').value;
                        const projectId = document.querySelector('input[name="project_id"]').value;
                        const storeId = document.getElementById('dispatchStoreId').value;
                        const storeInchargeId = document.querySelector('input[name="store_incharge_id"]').value;

                        if (!vendorId) {
                            Swal.fire('Error', 'Please select a vendor', 'error');
                            return;
                        }

                        const button = this;
                        const originalText = button.innerHTML;
                        button.disabled = true;
                        button.innerHTML = `
                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            Processing...
                        `;

                        // Get serial numbers from valid items - ensure they are strings and filter out invalid values
                        const serialNumbers = bulkDispatchPreviewData.validItems
                            .map(item => item.serial_number)
                            .filter(sn => sn != null && sn !== undefined && sn !== '')
                            .map(sn => String(sn));

                        if (serialNumbers.length === 0) {
                            Swal.fire('Error', 'No valid items to dispatch', 'error');
                            button.disabled = false;
                            button.innerHTML = originalText;
                            return;
                        }

                        fetch("{{ route('inventory.confirm-bulk-dispatch') }}", {
                                method: "POST",
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify({
                                    vendor_id: vendorId,
                                    project_id: projectId,
                                    store_id: storeId,
                                    store_incharge_id: storeInchargeId,
                                    serial_numbers: serialNumbers
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                button.disabled = false;
                                button.innerHTML = originalText;
                                if (data.status === 'success') {
                                    Swal.fire({
                                        title: 'Success!',
                                        text: data.message,
                                        icon: 'success',
                                        confirmButtonText: 'OK'
                                    }).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire({
                                        title: 'Error!',
                                        text: data.message,
                                        icon: 'error',
                                        confirmButtonText: 'OK'
                                    });
                                }
                            })
                            .catch(error => {
                                console.error(error);
                                button.disabled = false;
                                button.innerHTML = originalText;
                                Swal.fire({
                                    title: 'Error!',
                                    text: 'Something went wrong. Please try again.',
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                });
                            });
                    } else {
                        // Manual entry mode (existing behavior)
                    loadingIssue = true;
                    const button = this;
                    const originalText = button.innerHTML;
                    button.disabled = true;
                    button.innerHTML = `
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Processing...
                    `;
                    const form = document.getElementById('dispatchForm');
                    const formData = new FormData(form);

                    fetch("{{ route('inventory.dispatchweb') }}", {
                            method: "POST",
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            loadingIssue = false;
                            button.disabled = false;
                            button.innerHTML = originalText;
                            if (data.status === 'success') {
                                Swal.fire({
                                    title: 'Success!',
                                    text: data.message,
                                    icon: 'success',
                                    confirmButtonText: 'OK'
                                }).then(() => {
                                    form.reset();
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    title: 'Error!',
                                    text: data.message,
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                }).then(() => {
                                    loadingIssue = false;
                                    button.disabled = false;
                                    button.innerHTML = originalText;
                                });
                            }
                        })
                        .catch(error => {
                            console.error(error);
                            Swal.fire({
                                title: 'Error!',
                                text: 'Something went wrong. Please try again.',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                loadingIssue = false;
                                button.disabled = false;
                                button.innerHTML = originalText;
                            });
                        });
                    }
                });
            }

            // Dispatch Mode Toggle and Bulk Upload
            let alreadyDispatchedItems = [];
            let dispatchMode = 'manual';

            window.switchDispatchMode = function(mode) {
                dispatchMode = mode;
                const manualSection = document.getElementById('manualEntrySection');
                const bulkSection = document.getElementById('bulkUploadSection');
                const modeSwitch = document.getElementById('dispatchModeSwitch');
                const modeLabel = document.getElementById('modeLabel');
                const issueBtn = document.getElementById('issueMaterial');

                if (mode === 'bulk') {
                    manualSection.style.display = 'none';
                    bulkSection.style.display = 'block';
                    if (modeSwitch) modeSwitch.checked = true;
                    if (modeLabel) modeLabel.textContent = 'Bulk Upload (Excel)';
                    if (issueBtn) issueBtn.disabled = alreadyDispatchedItems.length > 0;
                } else {
                    manualSection.style.display = 'block';
                    bulkSection.style.display = 'none';
                    if (modeSwitch) modeSwitch.checked = false;
                    if (modeLabel) modeLabel.textContent = 'Manual Entry';
                    if (issueBtn) issueBtn.disabled = false;
                }
            };

            // Store bulk dispatch preview data
            let bulkDispatchPreviewData = {
                validItems: [],
                alreadyDispatched: [],
                duplicateSerials: [],
                nonExisting: []
            };

            const processBulkUploadBtn = document.getElementById('processBulkUpload');
            if (processBulkUploadBtn) {
                processBulkUploadBtn.addEventListener('click', function() {
                    const fileInput = document.getElementById('bulkDispatchFile');
                    const vendorId = document.getElementById('vendorName').value;
                    const projectId = document.querySelector('input[name="project_id"]').value;
                    const storeId = document.getElementById('dispatchStoreId').value;
                    const storeInchargeId = document.querySelector('input[name="store_incharge_id"]').value;

                    if (!fileInput.files.length) {
                        Swal.fire('Error', 'Please select an Excel file', 'error');
                        return;
                    }

                    if (!vendorId) {
                        Swal.fire('Error', 'Please select a vendor', 'error');
                        return;
                    }

                    const formData = new FormData();
                    formData.append('file', fileInput.files[0]);
                    formData.append('vendor_id', vendorId);
                    formData.append('project_id', projectId);
                    formData.append('store_id', storeId);
                    formData.append('store_incharge_id', storeInchargeId);
                    formData.append('_token', '{{ csrf_token() }}');

                    const btn = this;
                    const originalText = btn.innerHTML;
                    btn.disabled = true;
                    btn.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Processing...';

                    fetch('{{ route('inventory.bulk-dispatch') }}', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            btn.disabled = false;
                            btn.innerHTML = originalText;

                            if (data.status === 'preview') {
                                // Store preview data
                                bulkDispatchPreviewData = {
                                    validItems: data.valid_items || [],
                                    alreadyDispatched: data.already_dispatched || [],
                                    duplicateSerials: data.duplicate_serials || [],
                                    nonExisting: data.non_existing || []
                                };

                                // Display preview
                                displayBulkDispatchPreview(bulkDispatchPreviewData);

                                // Enable/disable issue button based on valid items
                                const issueMaterialBtn = document.getElementById('issueMaterial');
                                if (issueMaterialBtn) {
                                    issueMaterialBtn.disabled = bulkDispatchPreviewData.validItems.length === 0;
                                }

                                // Show success message with summary
                                const totalItems = bulkDispatchPreviewData.validItems.length + 
                                                 bulkDispatchPreviewData.alreadyDispatched.length + 
                                                 bulkDispatchPreviewData.duplicateSerials.length + 
                                                 bulkDispatchPreviewData.nonExisting.length;
                                const validCount = bulkDispatchPreviewData.validItems.length;

                                Swal.fire({
                                    title: 'Preview Ready',
                                    html: `Processed ${totalItems} item(s).<br>${validCount} ready to dispatch.`,
                                    icon: 'info',
                                    confirmButtonText: 'OK'
                                });
                            } else if (data.status === 'error') {
                                Swal.fire('Error', data.message || 'Failed to process bulk upload', 'error');
                            } else {
                                Swal.fire('Error', 'Unexpected response from server', 'error');
                            }
                        })
                        .catch(error => {
                            console.error(error);
                            btn.disabled = false;
                            btn.innerHTML = originalText;
                            Swal.fire('Error', 'Something went wrong. Please try again.', 'error');
                        });
                });
            }

            function displayAlreadyDispatched(items) {
                const section = document.getElementById('alreadyDispatchedSection');
                const list = document.getElementById('alreadyDispatchedList');

                if (items.length > 0) {
                    section.style.display = 'block';
                    list.innerHTML = '<ul class="list-group">';
                    items.forEach(item => {
                        list.innerHTML += `
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                              ${item.item_code} - ${item.item} (SN: ${item.serial_number}${item.sim_number ? ', SIM: ' + item.sim_number : ''})
                              <button type="button" class="btn btn-sm btn-danger" onclick="removeDispatchedItem('${item.serial_number}')">
                                <i class="mdi mdi-close"></i>
                              </button>
                            </li>
                          `;
                    });
                    list.innerHTML += '</ul>';
                } else {
                    section.style.display = 'none';
                }
            }

            function displayInvalidItems(items) {
                const section = document.getElementById('invalidItemsSection');
                const list = document.getElementById('invalidItemsList');

                if (items.length > 0) {
                    section.style.display = 'block';
                    list.innerHTML = '<ul class="list-group">';
                    items.forEach(item => {
                        list.innerHTML += `
                            <li class="list-group-item text-danger">
                              <strong>Error:</strong> ${item.error}<br>
                              <small>Row: ${JSON.stringify(item.row)}</small>
                            </li>
                          `;
                    });
                    list.innerHTML += '</ul>';
                } else {
                    section.style.display = 'none';
                }
            }

            window.removeDispatchedItem = function(serialNumber) {
                alreadyDispatchedItems = alreadyDispatchedItems.filter(item => item.serial_number !== serialNumber);
                displayAlreadyDispatched(alreadyDispatchedItems);

                if (alreadyDispatchedItems.length === 0) {
                    const issueMaterialBtn = document.getElementById('issueMaterial');
                    if (issueMaterialBtn) {
                        issueMaterialBtn.disabled = false;
                    }
                    const alreadyDispatchedSection = document.getElementById('alreadyDispatchedSection');
                    if (alreadyDispatchedSection) {
                        alreadyDispatchedSection.style.display = 'none';
                    }
                }
            };

            // Display bulk dispatch preview with multi-column serial number layout
            function displayBulkDispatchPreview(data) {
                const previewSection = document.getElementById('bulkDispatchPreview');
                if (!previewSection) return;

                // Hide old sections if they exist
                const oldAlreadyDispatchedSection = document.getElementById('alreadyDispatchedSection');
                const oldInvalidItemsSection = document.getElementById('invalidItemsSection');
                if (oldAlreadyDispatchedSection) oldAlreadyDispatchedSection.style.display = 'none';
                if (oldInvalidItemsSection) oldInvalidItemsSection.style.display = 'none';

                // Show preview section
                previewSection.style.display = 'block';

                // Display valid items ready to dispatch
                displaySerialNumbersGrid('readyToDispatchList', data.validItems, false);

                // Display already dispatched items
                if (data.alreadyDispatched.length > 0) {
                    document.getElementById('alreadyDispatchedPreviewSection').style.display = 'block';
                    displaySerialNumbersGrid('alreadyDispatchedPreviewList', data.alreadyDispatched, true, 'alreadyDispatched');
                } else {
                    document.getElementById('alreadyDispatchedPreviewSection').style.display = 'none';
                }

                // Display duplicate serials
                if (data.duplicateSerials.length > 0) {
                    document.getElementById('duplicateSerialsSection').style.display = 'block';
                    displaySerialNumbersGrid('duplicateSerialsList', data.duplicateSerials, true, 'duplicateSerials');
                } else {
                    document.getElementById('duplicateSerialsSection').style.display = 'none';
                }

                // Display non-existing items
                if (data.nonExisting.length > 0) {
                    document.getElementById('nonExistingSection').style.display = 'block';
                    displaySerialNumbersGrid('nonExistingList', data.nonExisting, true, 'nonExisting');
                } else {
                    document.getElementById('nonExistingSection').style.display = 'none';
                }
            }

            // Display serial numbers in multi-column grid layout
            function displaySerialNumbersGrid(containerId, items, showRemoveButton, category) {
                const container = document.getElementById(containerId);
                if (!container) return;

                if (items.length === 0) {
                    container.innerHTML = '<p class="text-muted small">No items</p>';
                    return;
                }

                // Create grid with 4 columns (col-md-3 = 4 columns on medium screens)
                let html = '<div class="row">';
                items.forEach((item, index) => {
                    const serialNumber = (item.serial_number || 'N/A').toString();
                    // Use data attributes instead of inline onclick for better reliability
                    const removeBtn = showRemoveButton ? 
                        `<button type="button" class="btn btn-sm btn-danger ms-2 remove-bulk-item-btn" 
                            data-serial="${serialNumber.replace(/"/g, '&quot;')}" 
                            data-category="${(category || '').replace(/"/g, '&quot;')}" 
                            title="Remove">
                            <i class="mdi mdi-close"></i>
                        </button>` : '';
                    
                    html += `
                        <div class="col-md-3 col-sm-4 col-6 mb-2">
                            <div class="d-flex align-items-center">
                                <span class="serial-number-badge">${serialNumber}</span>
                                ${removeBtn}
                            </div>
                        </div>
                    `;
                });
                html += '</div>';
                container.innerHTML = html;

                // Attach event listeners to remove buttons using event delegation
                if (showRemoveButton) {
                    container.querySelectorAll('.remove-bulk-item-btn').forEach(btn => {
                        btn.addEventListener('click', function() {
                            const serialNumber = this.getAttribute('data-serial');
                            const category = this.getAttribute('data-category');
                            removeBulkPreviewItem(serialNumber, category);
                        });
                    });
                }
            }

            // Remove item from bulk preview
            window.removeBulkPreviewItem = function(serialNumber, category) {
                if (!serialNumber) return;

                // Convert serialNumber to string for comparison
                const serialStr = String(serialNumber);

                if (category === 'alreadyDispatched') {
                    bulkDispatchPreviewData.alreadyDispatched = bulkDispatchPreviewData.alreadyDispatched.filter(
                        item => String(item.serial_number) !== serialStr
                    );
                } else if (category === 'duplicateSerials') {
                    bulkDispatchPreviewData.duplicateSerials = bulkDispatchPreviewData.duplicateSerials.filter(
                        item => String(item.serial_number) !== serialStr
                    );
                } else if (category === 'nonExisting') {
                    bulkDispatchPreviewData.nonExisting = bulkDispatchPreviewData.nonExisting.filter(
                        item => String(item.serial_number) !== serialStr
                    );
                }

                // Re-render preview
                displayBulkDispatchPreview(bulkDispatchPreviewData);
            };

            // Remove all items from a category
            const removeAllDispatchedBtn = document.getElementById('removeAllDispatchedBtn');
            if (removeAllDispatchedBtn) {
                removeAllDispatchedBtn.addEventListener('click', function() {
                    bulkDispatchPreviewData.alreadyDispatched = [];
                    displayBulkDispatchPreview(bulkDispatchPreviewData);
                });
            }

            const removeAllDuplicatesBtn = document.getElementById('removeAllDuplicatesBtn');
            if (removeAllDuplicatesBtn) {
                removeAllDuplicatesBtn.addEventListener('click', function() {
                    bulkDispatchPreviewData.duplicateSerials = [];
                    displayBulkDispatchPreview(bulkDispatchPreviewData);
                });
            }

            const removeAllNonExistingBtn = document.getElementById('removeAllNonExistingBtn');
            if (removeAllNonExistingBtn) {
                removeAllNonExistingBtn.addEventListener('click', function() {
                    bulkDispatchPreviewData.nonExisting = [];
                    displayBulkDispatchPreview(bulkDispatchPreviewData);
                });
            }

            const removeDispatchedBtn = document.getElementById('removeDispatchedBtn');
            if (removeDispatchedBtn) {
                removeDispatchedBtn.addEventListener('click', function() {
                    alreadyDispatchedItems = [];
                    displayAlreadyDispatched([]);
                    displayInvalidItems([]);
                    const issueMaterialBtn = document.getElementById('issueMaterial');
                    if (issueMaterialBtn) {
                        issueMaterialBtn.disabled = false;
                    }
                    const alreadyDispatchedSection = document.getElementById('alreadyDispatchedSection');
                    if (alreadyDispatchedSection) {
                        alreadyDispatchedSection.style.display = 'none';
                    }
                });
            }
            // ========== End Dispatch Form JavaScript ==========
        });
    </script>
@endpush

@push('styles')
    <style>
        .import-overlay {
            position: fixed;
            inset: 0;
            background: rgba(255, 255, 255, 0.7);
            z-index: 2000;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .import-overlay-content {
            background: #ffffff;
            border-radius: 8px;
            padding: 1.5rem 2rem;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.08);
        }

        /* Bulk Dispatch Preview Styles */
        .preview-section {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            background-color: #fafafa;
        }

        .preview-section.ready-to-dispatch {
            border-color: #28a745;
            background-color: #f0fff4;
        }

        .preview-section.already-dispatched {
            border-color: #ffc107;
            background-color: #fffbf0;
        }

        .preview-section.duplicate-serials {
            border-color: #ff9800;
            background-color: #fff8f0;
        }

        .preview-section.non-existing {
            border-color: #dc3545;
            background-color: #fff5f5;
        }

        .serial-numbers-grid {
            margin-top: 10px;
        }

        .serial-number-badge {
            display: inline-block;
            padding: 6px 12px;
            background-color: #ffffff;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            font-weight: 500;
            color: #333;
            min-width: 100px;
            text-align: center;
        }

        .preview-section h6 {
            color: #333;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .preview-section .text-muted {
            font-size: 12px;
            margin-bottom: 8px;
        }

        /* Metric Cards - Enhanced Visual Distinction */
        .row.mb-4 .metric-card-initial,
        .row.mb-4 .metric-card-instore,
        .row.mb-4 .metric-card-dispatched {
            border-radius: 8px;
            border: 2px solid;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.2s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        /* Initial Stock Value Card - Blue Theme */
        .row.mb-4 .metric-card-initial {
            border-color: #007bff;
            background: linear-gradient(135deg, #ffffff 0%, #f0f7ff 100%);
        }

        .row.mb-4 .metric-card-initial:hover {
            border-color: #0056b3;
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.2);
            transform: translateY(-2px);
        }

        /* In Store Stock Value Card - Green Theme */
        .row.mb-4 .metric-card-instore {
            border-color: #28a745;
            background: linear-gradient(135deg, #ffffff 0%, #f0fdf4 100%);
        }

        .row.mb-4 .metric-card-instore:hover {
            border-color: #1e7e34;
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.2);
            transform: translateY(-2px);
        }

        /* Dispatched Stock Value Card - Orange/Amber Theme */
        .row.mb-4 .metric-card-dispatched {
            border-color: #ffc107;
            background: linear-gradient(135deg, #ffffff 0%, #fffbf0 100%);
        }

        .row.mb-4 .metric-card-dispatched:hover {
            border-color: #e0a800;
            box-shadow: 0 4px 12px rgba(255, 193, 7, 0.2);
            transform: translateY(-2px);
        }

        .row.mb-4 .metric-card-body {
            padding: 1.25rem 1.5rem;
            flex: 1 1 auto;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .row.mb-4 .metric-card-body>div {
            width: 100%;
        }

        .row.mb-4 .metric-card-body .d-flex {
            align-items: center;
            gap: 1rem;
        }

        .row.mb-4 .metric-card-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #212529;
            margin-bottom: 0;
            line-height: 1.2;
        }

        .row.mb-4 .metric-card-body label {
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            color: #6c757d;
            margin-bottom: 0.5rem;
            display: block;
            font-weight: 600;
        }

        /* Nav Tabs Styling - Clean and Professional, matching project tabs */
        #storeTabs.fixed-navbar-project {
            border-bottom: 1px solid #ebedf2;
            margin-bottom: 1.5rem;
            margin-top: 0;
            padding: 0;
            display: flex !important;
            flex-wrap: wrap;
            width: 100%;
            list-style: none;
        }

        #storeTabs.fixed-navbar-project .nav-item {
            margin-bottom: -1px;
            display: list-item;
            list-style: none;
        }

        #storeTabs.fixed-navbar-project .nav-link {
            padding: 0.75rem 1.5rem;
            color: #6c757d;
            background-color: transparent;
            border: none;
            border-bottom: 2px solid transparent;
            font-weight: 500;
            transition: all 0.2s ease;
            display: block;
            text-decoration: none;
            cursor: pointer;
        }

        #storeTabs.fixed-navbar-project .nav-link:hover {
            color: #1F3BB3;
            background-color: transparent;
            border-bottom-color: #e9ecef;
        }

        #storeTabs.fixed-navbar-project .nav-link.active {
            color: #1F3BB3;
            background-color: transparent;
            border-bottom: 2px solid #1F3BB3;
            font-weight: 600;
        }

        #storeTabs.fixed-navbar-project .nav-link:focus {
            outline: none;
            box-shadow: none;
        }

        /* Nav Pills Styling for nested tabs */
        #viewTabs.nav-pills,
        ul.nav.nav-pills#viewTabs {
            border-bottom: 1px solid #ebedf2;
            margin-bottom: 1.5rem;
            padding: 0;
            display: flex !important;
            visibility: visible !important;
            opacity: 1 !important;
            list-style: none !important;
            flex-wrap: wrap;
            width: 100%;
        }

        #viewTabs.nav-pills .nav-item,
        ul.nav.nav-pills#viewTabs .nav-item {
            margin-right: 0.5rem;
            display: list-item !important;
            visibility: visible !important;
            opacity: 1 !important;
            list-style: none !important;
        }

        #viewTabs.nav-pills .nav-link,
        ul.nav.nav-pills#viewTabs .nav-link {
            padding: 0.5rem 1rem;
            color: #6c757d !important;
            background-color: transparent;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            font-weight: 500;
            transition: all 0.2s ease;
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
            cursor: pointer;
            text-decoration: none;
        }

        #viewTabs.nav-pills .nav-link:hover,
        ul.nav.nav-pills#viewTabs .nav-link:hover {
            color: #1F3BB3 !important;
            background-color: #f8f9fa;
            border-color: #1F3BB3;
        }

        #viewTabs.nav-pills .nav-link.active,
        ul.nav.nav-pills#viewTabs .nav-link.active {
            color: #ffffff !important;
            background-color: #1F3BB3 !important;
            border-color: #1F3BB3;
            font-weight: 600;
        }

        #viewTabs.nav-pills .nav-link:focus,
        ul.nav.nav-pills#viewTabs .nav-link:focus {
            outline: none;
            box-shadow: 0 0 0 0.2rem rgba(31, 59, 179, 0.25);
        }

        /* CRITICAL OVERRIDES for global CSS from style.css */
        /* Global CSS has: .tab-content>.tab-pane { display: none; } at line 14324 */
        /* Global CSS has: .fade:not(.show) { opacity: 0; } at line 13622 */

        /* Force show the view tab-pane when it has .show class - override ALL global rules */
        #view.tab-pane.fade.show,
        #storeTabContent .tab-pane#view.fade.show,
        .tab-content .tab-pane#view.fade.show {
            display: block !important;
            opacity: 1 !important;
            visibility: visible !important;
        }

        /* Hide the view tab-pane when it doesn't have .show class */
        #view.tab-pane.fade:not(.show),
        #storeTabContent .tab-pane#view.fade:not(.show) {
            display: none !important;
        }

        /* CRITICAL: Force nav-pills to be visible - override ALL possible hiding rules */
        /* These rules must override: .tab-content>.tab-pane { display: none; } */
        #viewTabs.nav-pills,
        ul.nav.nav-pills#viewTabs,
        #view #viewTabs.nav-pills,
        #view ul.nav.nav-pills#viewTabs,
        #view.tab-pane #viewTabs.nav-pills,
        #view.tab-pane ul.nav.nav-pills#viewTabs,
        #view.tab-pane.show #viewTabs.nav-pills,
        #view.tab-pane.show ul.nav.nav-pills#viewTabs,
        #view.tab-pane.fade.show #viewTabs.nav-pills,
        #view.tab-pane.fade.show ul.nav.nav-pills#viewTabs,
        #storeTabContent .tab-pane#view.show #viewTabs.nav-pills,
        #storeTabContent .tab-pane#view.show ul.nav.nav-pills#viewTabs,
        .tab-content .tab-pane#view.show #viewTabs.nav-pills,
        .tab-content .tab-pane#view.show ul.nav.nav-pills#viewTabs {
            display: flex !important;
            visibility: visible !important;
            opacity: 1 !important;
            position: relative !important;
            z-index: 1 !important;
        }

        /* Hide nav-pills ONLY when parent tab is explicitly hidden */
        #view.tab-pane:not(.show) #viewTabs.nav-pills,
        #view.tab-pane:not(.show) ul.nav.nav-pills#viewTabs,
        #view.tab-pane.fade:not(.show) #viewTabs.nav-pills,
        #view.tab-pane.fade:not(.show) ul.nav.nav-pills#viewTabs {
            display: none !important;
        }

        /* Override global .tab-content>.tab-pane { display: none; } rule for nested tabs */
        #viewTabContent.tab-content>.tab-pane {
            display: none !important;
        }

        #viewTabContent.tab-content>.tab-pane.show,
        #viewTabContent.tab-content>.tab-pane.fade.show {
            display: block !important;
        }

        /* Ensure actions column is visible */
        #inStockTable th:last-child,
        #inStockTable td:last-child,
        #dispatchedTable th:last-child,
        #dispatchedTable td:last-child {
            display: table-cell !important;
            visibility: visible !important;
            opacity: 1 !important;
        }

        /* Ensure tab content is visible */
        #storeTabContent {
            width: 100%;
            min-height: 200px;
        }

        /* Fix Chart Container Sizing */
        .chart-container {
            position: relative !important;
            height: 300px !important;
            width: 100% !important;
            max-width: 300px !important;
            margin: 0 auto !important;
        }

        .chart-container canvas {
            max-width: 100% !important;
            max-height: 100% !important;
        }

        /* Tab Content Cards - Higher Specificity */
        .tab-content .card,
        .tab-pane .card {
            border-radius: 8px !important;
            border: 1px solid #e3e6f0 !important;
            background: #ffffff !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05) !important;
        }

        .tab-content .card .card-body,
        .tab-pane .card .card-body {
            padding: 1.5rem !important;
        }

        /* Buttons - Higher Specificity */
        .tab-content .btn,
        .tab-pane .btn {
            border-radius: 4px !important;
            font-weight: 500 !important;
        }

        .tab-content .btn-sm,
        .tab-pane .btn-sm {
            padding: 0.375rem 0.75rem !important;
            font-size: 0.875rem !important;
        }

        .tab-content .form-control-sm,
        .tab-content .form-select-sm,
        .tab-pane .form-control-sm,
        .tab-pane .form-select-sm {
            border-radius: 4px !important;
            font-size: 0.875rem !important;
        }

        .tab-content .input-group-sm .form-control,
        .tab-content .input-group-sm .btn,
        .tab-pane .input-group-sm .form-control,
        .tab-pane .input-group-sm .btn {
            border-radius: 4px !important;
        }

        .import-section {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            flex-shrink: 0;
        }

        .import-form-group {
            display: flex;
            align-items: stretch;
        }

        .import-input-wrapper {
            display: flex;
        }

        .import-file-input {
            border-radius: 4px 0 0 4px;
            border-right: none;
        }

        .import-submit-btn {
            border-radius: 0 4px 4px 0;
            border-left: none;
        }

                .download-format-link {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            color: #28a745;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            transition: color 0.2s ease;
        }

        .download-format-link:hover {
            color: #218838;
            text-decoration: underline;
        }

        /* Form validation styles */
        .form-label .text-danger {
            font-weight: 600;
        }

        .form-control.is-invalid,
        .form-select.is-invalid {
            border-color: #dc3545;
        }

        .form-control.is-valid,
        .form-select.is-valid {
            border-color: #28a745;
        }

        .invalid-feedback {
            display: none;
            width: 100%;
            margin-top: 0.25rem;
            font-size: 0.875rem;
            color: #dc3545;
        }

        .invalid-feedback.d-block {
            display: block;
        }

        /* OR divider styling */
        .position-relative hr {
            border-color: #dee2e6;
            margin: 1.5rem 0;
        }

        .position-absolute.bg-white {
            background-color: #ffffff;
            padding: 0 1rem;
        }

        @media (min-width: 768px) {
            .import-section {
                width: auto;
                min-width: 250px;
            }
        }

        /* Ensure main-panel takes full width */
        .main-panel {
            width: 100%;
            flex: 1;
        }

        /* Fix container-fluid to work with sidebar */
        .container-fluid.p-4 {
            width: 100%;
            max-width: 100%;
            padding: 1.5rem;
        }

        /* Ensure tab content is visible and takes full width */
        .tab-content {
            width: 100%;
        }

        .tab-pane {
            width: 100%;
        }

        /* Fix datatable wrapper in tabs */
        .tab-pane .datatable-wrapper,
        .tab-pane table {
            width: 100% !important;
        }

        /* Dispatch Form Styles */
        .printbtn {
            background: #ffaf00;
            border: none;
        }

        .printbtn:hover {
            background: rgb(223, 152, 1);
            border: none;
        }

        #dispatchForm .text-danger {
            color: #F95F53 !important;
            font-size: 14px;
        }

        #dispatchForm .list-group-item {
            padding: 5px;
            top: 25px;
        }

        #dispatchForm .form-group {
            margin-bottom: 1rem;
        }

        #dispatchForm .btn-group .btn.active {
            background-color: #007bff;
            color: white;
            border-color: #007bff;
        }

        /* Form Switch Styling */
        #dispatchForm .form-check-switch {
            padding-left: 2.5em;
        }

        #dispatchForm .form-check-input[type="checkbox"] {
            width: 2.5em;
            height: 1.25em;
            cursor: pointer;
        }

        #dispatchForm .form-check-label {
            cursor: pointer;
            font-weight: 500;
            margin-left: 0.5rem;
        }

        /* Bulk Upload Instructions Styling */
        .bulk-upload-instructions {
            padding-top: 0.5rem;
        }

        .bulk-upload-instructions p {
            color: #212529;
            font-size: 0.875rem;
            line-height: 1.5;
        }

        .bulk-upload-instructions p strong {
            font-weight: 600;
            color: #212529;
        }

        .bulk-upload-instructions .small {
            font-size: 0.8125rem;
        }
    </style>
@endpush
