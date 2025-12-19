@extends('layouts.main')

@php
    $projectId = request()->get('project_id');
    $storeId = request()->get('store_id');
    
    // Split data for optimistic loading: first 50 rows rendered, rest in JSON
    $initialRows = array_slice($unifiedInventory, 0, 50);
    $remainingRows = array_slice($unifiedInventory, 50);
@endphp

@section('content')
    <div class="container-fluid m-2">
        <div class="d-flex justify-content-between my-1">
            <a href="{{ url()->previous() }}" class="btn btn-secondary">Back</a>
            <div>
                <h4>Store: {{ $storeName }} (Project ID: {{ $projectId }})</h4>
                <h4>Incharge Name: {{ $inchargeName }}</h4>
            </div>
        </div>
        
        <!-- Summary Cards -->
        <div class="row">
            <div class="col-sm-3 mb-2 mt-2">
                <div class="card bg-success">
                    <div class="card-header">
                        <div class="d-flex justify-content-between">
                            <h3 class="card-title">Battery</h3>
                            <i class="mdi mdi-battery"></i>
                        </div>
                    </div>
                    <div class="card-body">
                        <p>Total Quantity: <span>{{ $totalBattery }}</span></p>
                        <p>Total Value: <span>₹{{ $totalBatteryValue }}</span></p>
                        <p><a style="color:black;"
                                href='{{ route('inventory.showDispatchInventory', ['item_code' => 'SL03', 'store_id' => $storeId]) }}'>Dispatched
                                Quantity</a></p>
                        <p>Available Quantity: <span>{{ $availableBattery }}</span></p>
                    </div>
                </div>
            </div>
            <div class="col-sm-3 mb-2 mt-2">
                <div class="card bg-warning">
                    <div class="card-header">
                        <div class="d-flex justify-content-between">
                            <h3 class="card-title">Luminary</h3>
                            <i class="mdi mdi-led-on"></i>
                        </div>
                    </div>
                    <div class="card-body">
                        <p>Total Quantity: <span>{{ $totalLuminary }}</span> </p>
                        <p>Total Value: <span>₹{{ $totalLuminaryValue }}</span></p>
                        <p><a style="color:black;"
                                href='{{ route('inventory.showDispatchInventory', ['item_code' => 'SL02', 'store_id' => $storeId]) }}'>Dispatched
                                Quantity</a></p>
                        <p>Available Quantity: <span>{{ $availableLuminary }}</span></p>
                    </div>
                </div>
            </div>
            <div class="col-sm-3 mb-2 mt-2">
                <div class="card" style="background-color: #FF5733; color: black;">
                    <div class="card-header">
                        <div class="d-flex justify-content-between">
                            <h3 class="card-title">Structure</h3>
                            <img width="20" height="20"
                                src="https://img.icons8.com/external-others-pike-picture/50/external-Steel-Frame-house-others-pike-picture-2.png"
                                alt="external-Steel-Frame-house-others-pike-picture-2" />
                        </div>
                    </div>
                    <div class="card-body">
                        <p>Total Quantity: <span>{{ $totalStructure }}</span></p>
                        <p>Total Value: <span>₹{{ $totalStructureValue }}</span></p>
                        <p><a style="color:black;"
                                href='{{ route('inventory.showDispatchInventory', ['item_code' => 'SL04', 'store_id' => $storeId]) }}'>Dispatched
                                Quantity</a></p>
                        <p>Available Quantity: <span>{{ $availableStructure }}</span></p>
                    </div>
                </div>
            </div>
            <div class="col-sm-3 mb-2 mt-2">
                <div class="card bg-info">
                    <div class="card-header">
                        <div class="d-flex justify-content-between">
                            <h3 class="card-title">Module</h3>
                            <i class="mdi mdi-solar-panel"></i>
                        </div>
                    </div>
                    <div class="card-body">
                        <p>Total Quantity: <span>{{ $totalModule }}</span> </p>
                        <p>Total Value: <span>₹{{ $totalModuleValue }}</span></p>
                        <p><a style="color:black;"
                                href='{{ route('inventory.showDispatchInventory', ['item_code' => 'SL01', 'store_id' => $storeId]) }}'>Dispatched
                                Quantity</a></p>
                        <p>Available Quantity: <span>{{ $availableModule }}</span></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters and Unified Inventory Table -->
        <div class="mt-4">
            <!-- Filters Bar -->
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label for="filterAvailability" class="form-label mb-1">Availability</label>
                            <select id="filterAvailability" class="form-select form-select-sm">
                                <option value="">All</option>
                                <option value="In Stock">In Stock</option>
                                <option value="Dispatched">Dispatched</option>
                                <option value="Consumed">Consumed</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="filterItemCode" class="form-label mb-1">Item Code</label>
                            <select id="filterItemCode" class="form-select form-select-sm">
                                <option value="">All</option>
                                @foreach($itemCodes as $code)
                                    <option value="{{ $code }}">{{ $code }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="filterCustody" class="form-label mb-1">Custody Status</label>
                            <select id="filterCustody" class="form-select form-select-sm">
                                <option value="">All</option>
                                <option value="vendor">In Vendor Custody</option>
                                <option value="consumed">Consumed</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="button" id="clearFilters" class="btn btn-secondary btn-sm w-100">Clear Filters</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Unified Datatable -->
            <x-datatable 
                id="unifiedInventoryTable"
                title="Inventory View"
                :columns="[
                    ['title' => 'Item Code'],
                    ['title' => 'Item Name'],
                    ['title' => 'Serial Number'],
                    ['title' => 'Availability'],
                    ['title' => 'Vendor', 'orderable' => false],
                    ['title' => 'Dispatch Date'],
                    ['title' => 'In Date'],
                ]"
                :exportEnabled="true"
                :bulkDeleteEnabled="Auth::user()->role === \App\Enums\UserRole::ADMIN->value"
                :bulkDeleteRoute="route('inventory.bulkDelete')"
                pageLength="50"
                searchPlaceholder="Search inventory..."
            >
                @foreach($initialRows as $item)
                    <tr data-id="{{ $item['id'] }}" 
                        data-availability="{{ $item['availability'] }}"
                        data-item-code="{{ $item['item_code'] }}"
                        data-custody="{{ $item['availability'] === 'Dispatched' ? 'vendor' : ($item['availability'] === 'Consumed' ? 'consumed' : '') }}">
                        @if(Auth::user()->role === \App\Enums\UserRole::ADMIN->value)
                            <td>
                                <input type="checkbox" class="row-checkbox" value="{{ $item['id'] }}">
                            </td>
                        @endif
                        <td>{{ $item['item_code'] }}</td>
                        <td>{{ $item['item'] }}</td>
                        <td>{{ $item['serial_number'] }}</td>
                        <td>
                            @if($item['availability'] === 'In Stock')
                                <span class="badge bg-success">In Stock</span>
                            @elseif($item['availability'] === 'Dispatched')
                                <span class="badge bg-warning">Dispatched</span>
                            @else
                                <span class="badge bg-danger">Consumed</span>
                            @endif
                        </td>
                        <td>{{ $item['vendor_name'] ?? '-' }}</td>
                        <td>{{ $item['dispatch_date'] ? \Carbon\Carbon::parse($item['dispatch_date'])->format('Y-m-d') : '-' }}</td>
                        <td>{{ \Carbon\Carbon::parse($item['created_at'])->format('Y-m-d') }}</td>
                        <td>
                            @if($item['availability'] === 'In Stock')
                                <a href="#modal{{ $item['id'] }}" data-bs-toggle="modal" class="btn btn-sm btn-info" title="View">
                                    <i class="mdi mdi-eye"></i>
                                </a>
                                @if(Auth::user()->role === \App\Enums\UserRole::ADMIN->value)
                                    <button type="button" class="btn btn-sm btn-danger delete-item" data-id="{{ $item['id'] }}" title="Delete">
                                        <i class="mdi mdi-delete"></i>
                                    </button>
                                @endif
                            @elseif($item['availability'] === 'Dispatched')
                                <form action="{{ route('inventory.return') }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to return this item?');">
                                    @csrf
                                    <input type="hidden" name="serial_number" value="{{ $item['serial_number'] }}">
                                    <button type="submit" class="btn btn-sm btn-warning" title="Return">
                                        <i class="mdi mdi-undo"></i>
                                    </button>
                                </form>
                            @elseif($item['availability'] === 'Consumed')
                                <button type="button" class="btn btn-sm btn-primary replace-item" 
                                        data-dispatch-id="{{ $item['dispatch_id'] }}"
                                        data-serial-number="{{ $item['serial_number'] }}"
                                        title="Replace">
                                    <i class="mdi mdi-swap-horizontal"></i>
                                </button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </x-datatable>
        </div>
    </div>

    <!-- Store remaining rows in JSON for background loading -->
    <script type="application/json" id="remaining-inventory-data">
        @json($remainingRows)
    </script>

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
                            <input type="text" class="form-control" id="new_serial_number" name="new_serial_number" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="authentication_code">Authentication Code:</label>
                            <input type="text" class="form-control" id="authentication_code" name="authentication_code" required>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="agreement_checkbox" name="agreement_checkbox" value="1" required>
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

    <!-- Inventory Details Modals -->
    @foreach($initialRows as $item)
        <div class="modal fade" id="modal{{ $item['id'] }}" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Item Details: {{ $item['item'] }}</h5>
                        <button type="button" class="close" data-bs-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <p><strong>Item Code:</strong> {{ $item['item_code'] }}</p>
                        <p><strong>Serial Number:</strong> {{ $item['serial_number'] }}</p>
                        <p><strong>Manufacturer:</strong> {{ $item['manufacturer'] }}</p>
                        <p><strong>Model:</strong> {{ $item['model'] }}</p>
                        <p><strong>HSN:</strong> {{ $item['hsn'] }}</p>
                        <p><strong>Quantity:</strong> {{ $item['quantity'] }}</p>
                        <p><strong>Rate:</strong> ₹{{ number_format($item['rate'], 2) }}</p>
                        <p><strong>Availability:</strong> {{ $item['availability'] }}</p>
                        @if($item['vendor_name'])
                            <p><strong>Vendor:</strong> {{ $item['vendor_name'] }}</p>
                        @endif
                        @if($item['dispatch_date'])
                            <p><strong>Dispatch Date:</strong> {{ \Carbon\Carbon::parse($item['dispatch_date'])->format('Y-m-d H:i') }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let dataTable;
    const remainingDataElement = document.getElementById('remaining-inventory-data');
    const remainingRows = remainingDataElement ? JSON.parse(remainingDataElement.textContent) : [];
    
    // Wait for DataTable to initialize
    setTimeout(function() {
        dataTable = $('#unifiedInventoryTable').DataTable();
        
        // Background batch loading of remaining rows
        if (remainingRows.length > 0) {
            loadRemainingRowsInBatches(remainingRows, dataTable);
        }
    }, 100);

    // Load remaining rows in small batches to avoid blocking UI
    function loadRemainingRowsInBatches(rows, table) {
        const batchSize = 25;
        let currentIndex = 0;
        
        function loadBatch() {
            const batch = rows.slice(currentIndex, currentIndex + batchSize);
            
            batch.forEach(function(item) {
                const row = createTableRow(item);
                table.row.add($(row)).draw(false);
            });
            
            currentIndex += batchSize;
            
            if (currentIndex < rows.length) {
                // Use requestAnimationFrame or setTimeout for next batch
                setTimeout(loadBatch, 50);
            } else {
                // Final draw after all rows are added
                table.draw();
            }
        }
        
        loadBatch();
    }

    function createTableRow(item) {
        const availabilityBadge = item.availability === 'In Stock' 
            ? '<span class="badge bg-success">In Stock</span>'
            : item.availability === 'Dispatched'
            ? '<span class="badge bg-warning">Dispatched</span>'
            : '<span class="badge bg-danger">Consumed</span>';

        let actionButtons = '';
        const isAdmin = {{ Auth::user()->role === \App\Enums\UserRole::ADMIN->value ? 'true' : 'false' }};
        const returnRoute = '{{ route('inventory.return') }}';
        const csrfToken = '{{ csrf_token() }}';
        
        if (item.availability === 'In Stock') {
            actionButtons = '<a href="#modal' + item.id + '" data-bs-toggle="modal" class="btn btn-sm btn-info" title="View"><i class="mdi mdi-eye"></i></a>';
            if (isAdmin) {
                actionButtons += '<button type="button" class="btn btn-sm btn-danger delete-item" data-id="' + item.id + '" title="Delete"><i class="mdi mdi-delete"></i></button>';
            }
        } else if (item.availability === 'Dispatched') {
            actionButtons = '<form action="' + returnRoute + '" method="POST" class="d-inline" onsubmit="return confirm(\'Are you sure you want to return this item?\');">' +
                '<input type="hidden" name="_token" value="' + csrfToken + '">' +
                '<input type="hidden" name="serial_number" value="' + item.serial_number + '">' +
                '<button type="submit" class="btn btn-sm btn-warning" title="Return"><i class="mdi mdi-undo"></i></button>' +
                '</form>';
        } else if (item.availability === 'Consumed') {
            actionButtons = '<button type="button" class="btn btn-sm btn-primary replace-item" ' +
                'data-dispatch-id="' + (item.dispatch_id || '') + '" ' +
                'data-serial-number="' + item.serial_number + '" ' +
                'title="Replace"><i class="mdi mdi-swap-horizontal"></i></button>';
        }

        const dispatchDate = item.dispatch_date 
            ? new Date(item.dispatch_date).toISOString().split('T')[0] 
            : '-';
        const createdDate = new Date(item.created_at).toISOString().split('T')[0];

        const custody = item.availability === 'Dispatched' ? 'vendor' : (item.availability === 'Consumed' ? 'consumed' : '');
        
        const checkboxTd = isAdmin ? '<td><input type="checkbox" class="row-checkbox" value="' + item.id + '"></td>' : '';

        return '<tr data-id="' + item.id + '" ' +
            'data-availability="' + item.availability + '" ' +
            'data-item-code="' + item.item_code + '" ' +
            'data-custody="' + custody + '">' +
            checkboxTd +
            '<td>' + (item.item_code || '') + '</td>' +
            '<td>' + (item.item || '') + '</td>' +
            '<td>' + (item.serial_number || '') + '</td>' +
            '<td>' + availabilityBadge + '</td>' +
            '<td>' + (item.vendor_name || '-') + '</td>' +
            '<td>' + dispatchDate + '</td>' +
            '<td>' + createdDate + '</td>' +
            '<td>' + actionButtons + '</td>' +
            '</tr>';
    }

    // Filter functionality
    function applyFilters() {
        const availability = $('#filterAvailability').val();
        const itemCode = $('#filterItemCode').val();
        const custody = $('#filterCustody').val();
        
        $.fn.dataTable.ext.search.push(
            function(settings, data, dataIndex) {
                const row = dataTable.row(dataIndex).node();
                const rowAvailability = $(row).data('availability') || '';
                const rowItemCode = $(row).data('item-code') || '';
                const rowCustody = $(row).data('custody') || '';
                
                if (availability && rowAvailability !== availability) return false;
                if (itemCode && rowItemCode !== itemCode) return false;
                if (custody && rowCustody !== custody) return false;
                
                return true;
            }
        );
        
        dataTable.draw();
    }

    $('#filterAvailability, #filterItemCode, #filterCustody').on('change', function() {
        $.fn.dataTable.ext.search.pop();
        applyFilters();
    });

    $('#clearFilters').on('click', function() {
        $('#filterAvailability, #filterItemCode, #filterCustody').val('');
        $.fn.dataTable.ext.search.pop();
        dataTable.draw();
    });

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
                            Swal.fire('Deleted!', response.message, 'success').then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error!', response.message || 'Failed to delete item', 'error');
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

    // Session messages
    @if (session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: '{{ session('success') }}',
            confirmButtonColor: '#28a745',
        });
    @endif

    @if (session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: '{{ session('error') }}',
            confirmButtonColor: '#dc3545',
        });
    @endif

    @if (session('replace_error'))
        Swal.fire({
            icon: 'error',
            title: 'Replace Error',
            text: '{{ session('replace_error') }}',
            confirmButtonColor: '#dc3545',
        });
    @endif
});
</script>
@endpush
