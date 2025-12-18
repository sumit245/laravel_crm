@extends('layouts.main')

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

        @if (session('success') || session('error'))
            <div class="alert {{ session('success') ? 'alert-success' : 'alert-danger' }} alert-dismissible fade show"
                role="alert">
                {{ session('success') ?? session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

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
                                <form
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
                                            Make <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" id="make" name="make"
                                            class="form-control form-control-sm @error('make') is-invalid @enderror"
                                            required>
                                        @error('make')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                        <div class="invalid-feedback">Please provide a make name.</div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="rate" class="form-label">
                                            Rate <span class="text-danger">*</span>
                                        </label>
                                        <input type="number" id="rate" name="rate" step="0.01"
                                            min="0"
                                            class="form-control form-control-sm @error('rate') is-invalid @enderror"
                                            required>
                                        @error('rate')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                        <div class="invalid-feedback">Please provide a valid rate (must be a positive
                                            number).</div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="receiveddate" class="form-label">
                                            Received Date <span class="text-danger">*</span>
                                        </label>
                                        <input type="date" id="receiveddate" name="receiveddate"
                                            class="form-control form-control-sm @error('receiveddate') is-invalid @enderror"
                                            required>
                                        @error('receiveddate')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                        <div class="invalid-feedback">Please select a received date.</div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="hsncode" class="form-label">
                                            HSN Code <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" id="hsncode" name="hsncode"
                                            class="form-control form-control-sm @error('hsncode') is-invalid @enderror"
                                            required>
                                        @error('hsncode')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                        <div class="invalid-feedback">Please provide an HSN code.</div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <label for="totalvalue" class="form-label">
                                            Total Value <span class="text-danger">*</span>
                                        </label>
                                        <input type="number" id="totalvalue" name="totalvalue" step="0.01"
                                            min="0"
                                            class="form-control form-control-sm @error('totalvalue') is-invalid @enderror"
                                            required>
                                        @error('totalvalue')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                        <div class="invalid-feedback">Please provide a valid total value (must be a
                                            positive number).</div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="unit" class="form-label">
                                            Unit <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" id="unit" name="unit"
                                            class="form-control form-control-sm @error('unit') is-invalid @enderror"
                                            required>
                                        @error('unit')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                        <div class="invalid-feedback">Please provide a unit.</div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="description" class="form-label">
                                            Description <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" id="description" name="description"
                                            class="form-control form-control-sm @error('description') is-invalid @enderror"
                                            required>
                                        @error('description')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                        <div class="invalid-feedback">Please provide a description.</div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="sim_number" class="form-label">
                                            SIM Number <small class="text-muted">(Luminary only)</small>
                                        </label>
                                        <input type="text" id="sim_number" name="sim_number"
                                            class="form-control form-control-sm @error('sim_number') is-invalid @enderror">
                                        @error('sim_number')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
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
                <ul class="nav nav-pills mb-3" id="viewTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="in-stock-tab" data-bs-toggle="tab"
                            data-bs-target="#in-stock" type="button" role="tab" aria-controls="in-stock"
                            aria-selected="true">
                            In Stock
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="dispatched-tab" data-bs-toggle="tab"
                            data-bs-target="#dispatched-view" type="button" role="tab"
                            aria-controls="dispatched-view" aria-selected="false">
                            Dispatched
                        </button>
                    </li>
                </ul>
                <div class="tab-content" id="viewTabContent">
                    <div class="tab-pane fade show active" id="in-stock" role="tabpanel"
                        aria-labelledby="in-stock-tab">
                        <x-datatable id="inStockTable" title="In Stock Items" :columns="[
                            ['title' => 'Item Code', 'width' => '12%'],
                            ['title' => 'Item', 'width' => '15%'],
                            ['title' => 'Serial Number', 'width' => '20%'],
                            ['title' => 'Quantity', 'width' => '10%'],
                            ['title' => 'Rate', 'width' => '12%'],
                            ['title' => 'Total Value', 'width' => '12%'],
                            ['title' => 'In Date', 'width' => '12%'],
                        ]" :exportEnabled="true"
                            :importEnabled="false" :bulkDeleteEnabled="true" 
                            :bulkDeleteRoute="route('inventory.bulkDelete')" 
                            pageLength="25" searchPlaceholder="Search items..."
                            :filters="[
                                [
                                    'type' => 'select',
                                    'name' => 'filter_item_code',
                                    'label' => 'Item Code',
                                    'column' => 0,
                                    'width' => 3,
                                    'options' => [
                                        '' => 'All',
                                        'SL01' => 'SL01 - Panel',
                                        'SL02' => 'SL02 - Luminary',
                                        'SL03' => 'SL03 - Battery',
                                        'SL04' => 'SL04 - Structure',
                                    ],
                                ],
                            ]">
                            @forelse($inStock as $item)
                                <tr>
                                    <td>
                                        <input type="checkbox" class="row-checkbox" value="{{ $item->id ?? '' }}">
                                    </td>
                                    <td>{{ $item->item_code ?? 'N/A' }}</td>
                                    <td>{{ $item->item ?? 'N/A' }}</td>
                                    <td>{{ $item->serial_number ?? 'N/A' }}</td>
                                    <td>{{ $item->quantity ?? 0 }}</td>
                                    <td>₹{{ number_format($item->rate ?? 0, 2) }}</td>
                                    <td>₹{{ number_format($item->total_value ?? 0, 2) }}</td>
                                    <td>{{ $item->created_at ? $item->created_at->format('d/m/Y') : 'N/A' }}</td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-danger delete-item" 
                                            data-id="{{ $item->id ?? '' }}" 
                                            data-url="{{ route('inventory.destroy', $item->id ?? 0) }}">
                                            <i class="mdi mdi-delete"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center">No items in stock</td>
                                </tr>
                            @endforelse
                        </x-datatable>
                    </div>
                    <div class="tab-pane fade" id="dispatched-view" role="tabpanel" aria-labelledby="dispatched-tab">
                        <x-datatable id="dispatchedTable" title="Dispatched Items" :columns="[
                            ['title' => 'Item Code', 'width' => '12%'],
                            ['title' => 'Item', 'width' => '15%'],
                            ['title' => 'Serial Number', 'width' => '18%'],
                            ['title' => 'Vendor', 'width' => '15%'],
                            ['title' => 'Dispatch Date', 'width' => '12%'],
                            ['title' => 'Value', 'width' => '12%'],
                        ]" :exportEnabled="true"
                            :importEnabled="false" :bulkDeleteEnabled="true"
                            :bulkDeleteRoute="route('inventory.bulkDelete')" 
                            pageLength="25"
                            searchPlaceholder="Search dispatched items..." :filters="[
                                [
                                    'type' => 'select',
                                    'name' => 'filter_item_code',
                                    'label' => 'Item Code',
                                    'column' => 0,
                                    'width' => 3,
                                    'options' => [
                                        '' => 'All',
                                        'SL01' => 'SL01 - Panel',
                                        'SL02' => 'SL02 - Luminary',
                                        'SL03' => 'SL03 - Battery',
                                        'SL04' => 'SL04 - Structure',
                                    ],
                                ],
                                [
                                    'type' => 'date',
                                    'name' => 'filter_dispatch_date',
                                    'label' => 'Dispatch Date',
                                    'column' => 4,
                                    'width' => 3,
                                ],
                            ]">
                            @forelse($dispatched as $dispatch)
                                <tr>
                                    <td>
                                        <input type="checkbox" class="row-checkbox" value="{{ $dispatch->id ?? '' }}">
                                    </td>
                                    <td>{{ $dispatch->item_code ?? 'N/A' }}</td>
                                    <td>{{ $dispatch->item ?? 'N/A' }}</td>
                                    <td>{{ $dispatch->serial_number ?? 'N/A' }}</td>
                                    <td>{{ $dispatch->vendor->name ?? 'N/A' }}</td>
                                    <td>{{ $dispatch->dispatch_date ? \Carbon\Carbon::parse($dispatch->dispatch_date)->format('d/m/Y') : 'N/A' }}
                                    </td>
                                    <td>₹{{ number_format($dispatch->total_value ?? 0, 2) }}</td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-danger delete-item" 
                                            data-id="{{ $dispatch->id ?? '' }}" 
                                            data-url="{{ route('inventory.destroy', $dispatch->id ?? 0) }}">
                                            <i class="mdi mdi-delete"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">No dispatched items</td>
                                </tr>
                            @endforelse
                        </x-datatable>
                    </div>
                </div>
            </div>

            <!-- Dispatch Material Tab -->
            <div class="tab-pane fade" id="dispatch" role="tabpanel">
                <div class="card">
                    <div class="card-body">
                        <button type="button" class="btn btn-primary btn-sm"
                            onclick="openDispatchModal({{ $store->id }})">
                            <i class="mdi mdi-truck-delivery"></i> Dispatch Material
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dispatch Modal - Outside tab-content for proper visibility -->
        @include('projects.dispatchInventory', [
            'store' => $store,
            'project' => $project,
            'assignedVendors' => $assignedVendors,
        ])
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Handle item selection
            const itemCombined = document.getElementById('item_combined');
            if (itemCombined) {
                itemCombined.addEventListener('change', function() {
                    const [code, name] = this.value.split('|');
                    document.getElementById('item_code').value = code || '';
                    document.getElementById('item_name').value = name || '';

                    // Clear validation state when item changes
                    this.classList.remove('is-invalid', 'is-valid');
                    const feedback = this.parentElement.querySelector('.invalid-feedback');
                    if (feedback && !feedback.classList.contains('d-block')) {
                        feedback.classList.add('d-none');
                    }
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
                    inputs.forEach(input => {
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

            function openDispatchModal(storeId) {
                const dispatchStoreIdInput = document.getElementById("dispatchStoreId");
                if (dispatchStoreIdInput) {
                    dispatchStoreIdInput.value = storeId;
                }
                const dispatchModal = document.getElementById('dispatchModal');
                if (dispatchModal) {
                    const modal = new bootstrap.Modal(dispatchModal);
                    modal.show();
                }
            }

            // Prevent DataTables from initializing on hidden tables
            // The datatable component initializes on document.ready, but we need to
            // ensure tables in hidden tabs don't initialize until visible
            function initializeDataTablesWhenVisible() {
                // Check if view tab is active
                const viewTabPane = document.getElementById('view');
                const inStockPane = document.getElementById('in-stock');
                const dispatchedPane = document.getElementById('dispatched-view');

                // Initialize in-stock table if visible
                if (viewTabPane && viewTabPane.classList.contains('show') &&
                    inStockPane && inStockPane.classList.contains('show') &&
                    $.fn.DataTable && !$.fn.DataTable.isDataTable('#inStockTable')) {
                    // Table will be initialized by component, just adjust after
                    setTimeout(function() {
                        if ($.fn.DataTable.isDataTable('#inStockTable')) {
                            $('#inStockTable').DataTable().columns.adjust().draw();
                        }
                    }, 100);
                }

                // Initialize dispatched table if visible
                if (viewTabPane && viewTabPane.classList.contains('show') &&
                    dispatchedPane && dispatchedPane.classList.contains('show') &&
                    $.fn.DataTable && !$.fn.DataTable.isDataTable('#dispatchedTable')) {
                    setTimeout(function() {
                        if ($.fn.DataTable.isDataTable('#dispatchedTable')) {
                            $('#dispatchedTable').DataTable().columns.adjust().draw();
                        }
                    }, 100);
                }
            }

            // Adjust datatables when tabs are shown
            function adjustDataTables() {
                setTimeout(function() {
                    if ($.fn.DataTable) {
                        if ($.fn.DataTable.isDataTable('#inStockTable')) {
                            $('#inStockTable').DataTable().columns.adjust().draw();
                        }
                        if ($.fn.DataTable.isDataTable('#dispatchedTable')) {
                            $('#dispatchedTable').DataTable().columns.adjust().draw();
                        }
                    }
                }, 300);
            }

            // Function to ensure actions column is always visible
            function ensureActionsColumnVisible() {
                setTimeout(function() {
                    if ($.fn.DataTable) {
                        // For inStockTable
                        if ($.fn.DataTable.isDataTable('#inStockTable')) {
                            const table = $('#inStockTable').DataTable();
                            const lastColIndex = table.columns().count() - 1;
                            table.column(lastColIndex).visible(true, false);
                            table.columns.adjust().draw(false);
                        }
                        // For dispatchedTable
                        if ($.fn.DataTable.isDataTable('#dispatchedTable')) {
                            const table = $('#dispatchedTable').DataTable();
                            const lastColIndex = table.columns().count() - 1;
                            table.column(lastColIndex).visible(true, false);
                            table.columns.adjust().draw(false);
                        }
                    }
                }, 500);
            }

            // Handle main view tab - CRITICAL: Force nav-pills to be visible
            const viewTab = document.getElementById('view-tab');
            if (viewTab) {
                // Function to force show nav-pills
                function forceShowNavPills() {
                    const viewPane = document.getElementById('view');
                    const viewTabs = document.getElementById('viewTabs');
                    
                    if (viewPane) {
                        // Force show the parent tab-pane
                        viewPane.classList.add('show');
                        viewPane.style.setProperty('display', 'block', 'important');
                        viewPane.style.setProperty('opacity', '1', 'important');
                        viewPane.style.setProperty('visibility', 'visible', 'important');
                    }
                    
                    if (viewTabs) {
                        // Force show the nav-pills with maximum specificity
                        viewTabs.style.setProperty('display', 'flex', 'important');
                        viewTabs.style.setProperty('visibility', 'visible', 'important');
                        viewTabs.style.setProperty('opacity', '1', 'important');
                        viewTabs.style.setProperty('position', 'relative', 'important');
                        viewTabs.style.setProperty('z-index', '10', 'important');
                        
                        // Also force show each nav-item and nav-link
                        const navItems = viewTabs.querySelectorAll('.nav-item');
                        navItems.forEach(function(item) {
                            item.style.setProperty('display', 'list-item', 'important');
                            item.style.setProperty('visibility', 'visible', 'important');
                            item.style.setProperty('opacity', '1', 'important');
                        });
                        
                        const navLinks = viewTabs.querySelectorAll('.nav-link');
                        navLinks.forEach(function(link) {
                            link.style.setProperty('display', 'block', 'important');
                            link.style.setProperty('visibility', 'visible', 'important');
                            link.style.setProperty('opacity', '1', 'important');
                        });
                    }
                }
                
                viewTab.addEventListener('shown.bs.tab', function() {
                    forceShowNavPills();
                    setTimeout(function() {
                        initializeDataTablesWhenVisible();
                        adjustDataTables();
                        ensureActionsColumnVisible();
                    }, 200);
                });
                
                // Also check on click (before shown event)
                viewTab.addEventListener('click', function() {
                    setTimeout(forceShowNavPills, 50);
                });
                
                // Check immediately if tab is already active
                if (viewTab.classList.contains('active') || viewTab.getAttribute('aria-selected') === 'true') {
                    setTimeout(forceShowNavPills, 100);
                }
            }

            // Handle nested tab visibility for in-stock and dispatched tabs
            const inStockTab = document.getElementById('in-stock-tab');
            const dispatchedTab = document.getElementById('dispatched-tab');

            if (inStockTab) {
                inStockTab.addEventListener('shown.bs.tab', function() {
                    setTimeout(function() {
                        initializeDataTablesWhenVisible();
                        adjustDataTables();
                        ensureActionsColumnVisible();
                    }, 200);
                });
            }

            if (dispatchedTab) {
                dispatchedTab.addEventListener('shown.bs.tab', function() {
                    setTimeout(function() {
                        initializeDataTablesWhenVisible();
                        adjustDataTables();
                        ensureActionsColumnVisible();
                    }, 200);
                });
            }
            
            // Ensure nav-pills are visible on page load if view tab is active
            setTimeout(function() {
                const viewTabPane = document.getElementById('view');
                const viewTab = document.getElementById('view-tab');
                // Check if view tab is active (has active class or aria-selected)
                const isViewTabActive = viewTab && (
                    viewTab.classList.contains('active') || 
                    viewTab.getAttribute('aria-selected') === 'true'
                );
                
                if (viewTabPane && (viewTabPane.classList.contains('show') || isViewTabActive)) {
                    viewTabPane.classList.add('show');
                    viewTabPane.style.cssText = 'display: block !important; opacity: 1 !important; visibility: visible !important;';
                    
                    const viewTabs = document.getElementById('viewTabs');
                    if (viewTabs) {
                        viewTabs.style.cssText = 'display: flex !important; visibility: visible !important; opacity: 1 !important;';
                    }
                    ensureActionsColumnVisible();
                }
            }, 500);

            // Initialize immediately if view tab is already active on page load
            setTimeout(function() {
                const viewTabPane = document.getElementById('view');
                if (viewTabPane && viewTabPane.classList.contains('show')) {
                    initializeDataTablesWhenVisible();
                }
            }, 500);

            // Also adjust on window resize
            let resizeTimer;
            window.addEventListener('resize', function() {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(adjustDataTables, 250);
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
        });
    </script>
@endpush

@push('styles')
    <style>
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
        #viewTabContent.tab-content > .tab-pane {
            display: none !important;
        }
        
        #viewTabContent.tab-content > .tab-pane.show,
        #viewTabContent.tab-content > .tab-pane.fade.show {
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
    </style>
@endpush
