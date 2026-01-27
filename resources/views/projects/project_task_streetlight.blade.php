<div>
    <!-- Summary Cards -->
    <div class="row mb-3">
        <div class="col-md-4">
            <div class="summary-card summary-card-total">
                <div class="summary-card-body">
                    <h5 class="summary-card-title mb-0">{{ $totalPoles ?? 0 }}</h5>
                    <p class="summary-card-text mb-0">Total Poles</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="summary-card summary-card-surveyed">
                <div class="summary-card-body">
                    <h5 class="summary-card-title mb-0">{{ $totalSurveyedPoles ?? 0 }}</h5>
                    <p class="summary-card-text mb-0">Surveyed Poles</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="summary-card summary-card-installed">
                <div class="summary-card-body">
                    <h5 class="summary-card-title mb-0">{{ $totalInstalledPoles ?? 0 }}</h5>
                    <p class="summary-card-text mb-0">Installed Poles</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for adding a target -->
    <div class="modal fade" id="addTargetModal" tabindex="-1" aria-labelledby="addTargetModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="{{ route('tasks.store') }}" method="POST" id="targetForm">
                    @csrf
                    <input type="hidden" name="project_id" value="{{ $project->id }}" />
                    <div class="modal-header">
                        <h5 class="modal-title" id="addTargetModalLabel">Add Target for Project:
                            {{ $project->project_name }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- District Search -->
                        <div class="form-group mb-3">
                            <label for="districtSearch" class="form-label">Search District</label>
                            <select id="districtSearch" name="district" class="form-select">
                                <option value="">Select District</option>
                                @foreach ($districts as $district)
                                    <option value="{{ $district->district }}">{{ $district->district }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Block Search (Dependent on District) -->
                        <div class="form-group mb-3">
                            <label for="blockSearch" class="form-label">Search Block</label>
                            <select id="blockSearch" name="block_id" class="form-select" disabled>
                                <option value="">Select Block</option>
                            </select>
                        </div>

                        <!-- Panchayat Search (Dependent on Block) -->
                        <div class="mb-3">
                            <label for="panchayatSearch" class="form-label">Select Panchayat <span
                                    class="text-danger">*</span></label>
                            <select id="panchayatSearch" name="sites[]" multiple="multiple"
                                class="form-select modern-select" style="width: 100%;" required disabled>
                                <option value="">Please select district and block first</option>
                            </select>
                            <div class="invalid-feedback" id="sites_error"></div>
                        </div>

                        <!-- Ward Selection (Dependent on Panchayat) -->
                        <div class="mb-3" id="wardSelectionContainer" style="display: none;">
                            <label for="wardSelection" class="form-label">
                                <i class="mdi mdi-map-marker text-primary"></i> Select Wards
                            </label>
                            <div class="ward-selection-wrapper">
                                <div class="ward-selection-header mb-2">
                                    <button type="button" class="btn btn-sm btn-link p-0" id="selectAllWards">
                                        <i class="mdi mdi-checkbox-marked"></i> Select All
                                    </button>
                                    <button type="button" class="btn btn-sm btn-link p-0 ml-2" id="deselectAllWards">
                                        <i class="mdi mdi-checkbox-blank-outline"></i> Deselect All
                                    </button>
                                </div>
                                <div id="wardCheckboxes" class="ward-checkboxes">
                                    <!-- Wards will be populated here -->
                                </div>
                                <input type="hidden" name="selected_wards" id="selectedWardsInput" value="">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="selectEngineer" class="form-label">Select Site Engineer <span
                                    class="text-danger">*</span></label>
                            <select id="selectEngineer" name="engineer_id" class="form-select select2-engineer" style="width: 100%;" required>
                                <option value="">Select Engineer</option>
                                @foreach ($assignedEngineers as $engineer)
                                    <option value="{{ $engineer->id }}">{{ $engineer->firstName }}
                                        {{ $engineer->lastName }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" id="engineer_id_error"></div>
                        </div>
                        <div class="form-group mb-3">
                            <label for="selectVendor" class="form-label">Select Vendor <span
                                    class="text-danger">*</span></label>
                            <select id="selectVendor" name="vendor_id" class="form-select select2-vendor" style="width: 100%;" required>
                                <option value="">Select Vendor</option>
                                @foreach ($assignedVendors as $vendor)
                                    <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" id="vendor_id_error"></div>
                        </div>
                        <div class="form-group mb-3">
                            <label for="startDate" class="form-label">Start Date <span
                                    class="text-danger">*</span></label>
                            <input onclick="document.getElementById('startDate').showPicker()" type="date"
                                id="startDate" name="start_date" class="form-control" required>
                            <div class="invalid-feedback" id="start_date_error"></div>
                        </div>
                        <div class="form-group mb-3">
                            <label for="endDate" class="form-label">End Date <span
                                    class="text-danger">*</span></label>
                            <input onclick="document.getElementById('endDate').showPicker()" type="date"
                                id="endDate" name="end_date" class="form-control" required>
                            <div class="invalid-feedback" id="end_date_error"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="reset" class="btn btn-secondary">Reset</button>
                        <button type="submit" class="btn btn-primary">Allot Target</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Target Button (styled to match parent) -->
    <div class="mb-3 d-flex justify-content-end">
        <button type="button" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-2 add-target-btn"
            style="max-height: 2.8rem;" data-bs-toggle="modal" data-bs-target="#addTargetModal">
            <i class="mdi mdi-plus-circle"></i>
            <span>Add Target</span>
        </button>
    </div>

    <!-- DataTable Component -->
    <x-datatable id="targetsTable" title="Targets" :columns="[
        ['title' => 'Panchayat', 'width' => '12%'],
        ['title' => 'Engineer Name', 'width' => '12%'],
        ['title' => 'Vendor Name', 'width' => '12%'],
        ['title' => 'Start Date', 'width' => '10%'],
        ['title' => 'End Date', 'width' => '10%'],
        ['title' => 'Wards', 'width' => '15%'],
        ['title' => 'Status', 'width' => '10%'],
    ]" :exportEnabled="true" :importEnabled="true"
        :importRoute="route('tasks.import')" :importFormatUrl="route('tasks.importFormat')" :bulkDeleteEnabled="true" :bulkDeleteRoute="route('tasks.bulkDelete')" :deleteRoute="route('tasks.destroystreetlight', ':id')" :editRoute="route('tasks.edit', [':id', 'project_id' => $project->id])"
        :viewRoute="route('tasks.show', [':id', 'project_type' => 1])" pageLength="25" searchPlaceholder="Search Targets..." :filters="[
            [
                'type' => 'select',
                'name' => 'filter_status',
                'label' => 'Status',
                'column' => -1,
                'width' => 3,
                'options' => [
                    '' => 'All',
                    'Pending' => 'Pending',
                    'Installed' => 'Installed',
                ],
            ],
            [
                'type' => 'select',
                'name' => 'filter_panchayat',
                'label' => 'Panchayat',
                'column' => 1,
                'width' => 3,
                'options' => $filterPanchayats ?? ['All' => ''],
                'select2' => true,
            ],
            [
                'type' => 'select',
                'name' => 'filter_engineer',
                'label' => 'Engineer',
                'column' => 2,
                'width' => 3,
                'options' => $filterEngineers ?? ['All' => ''],
                'select2' => true,
            ],
            [
                'type' => 'select',
                'name' => 'filter_vendor',
                'label' => 'Vendor',
                'column' => 3,
                'width' => 3,
                'options' => $filterVendors ?? ['All' => ''],
                'select2' => true,
            ],
        ]">
        @foreach ($targets as $target)
            @php
                $isInstalled =
                    $target->status == 'Completed' ||
                    ($target->poles && $target->poles->where('isInstallationDone', 1)->count() > 0);
                $statusValue = $isInstalled ? 'Installed' : 'Pending';
                $panchayatName = $target->site->panchayat ?? 'N/A';
                $engineerName = trim(
                    ($target->engineer->firstName ?? 'N/A') . ' ' . ($target->engineer->lastName ?? ''),
                );
                $vendorName = $target->vendor->name ?? 'N/A';
            @endphp
            <tr data-status="{{ $statusValue }}" data-id="{{ $target->id }}"
                data-panchayat="{{ $panchayatName }}" data-engineer="{{ $engineerName }}"
                data-vendor="{{ $vendorName }}">
                <td>
                    <input type="checkbox" class="row-checkbox" value="{{ $target->id }}">
                </td>
                <td>{{ $panchayatName }}</td>
                <td>{{ $engineerName }}</td>
                <td>{{ $vendorName }}</td>
                <td>{{ $target->start_date ? \Carbon\Carbon::parse($target->start_date)->format('d/m/y') : 'N/A' }}</td>
                <td>{{ $target->end_date ? \Carbon\Carbon::parse($target->end_date)->format('d/m/y') : 'N/A' }}</td>
                <td class="wards-cell">
                    <div class="wards-content" title="{{ $target->site->ward ?? 'N/A' }}">
                        @php
                            $wards = $target->site->ward ?? 'N/A';
                            $wardsArray = is_string($wards) ? explode(',', $wards) : [$wards];
                            $wardsArray = array_map('trim', $wardsArray);
                        @endphp
                        @if (count($wardsArray) > 3)
                            <span class="wards-preview">{{ implode(', ', array_slice($wardsArray, 0, 3)) }},
                                ...</span>
                            <span class="wards-full d-none">{{ implode(', ', $wardsArray) }}</span>
                        @else
                            {{ implode(', ', $wardsArray) }}
                        @endif
                    </div>
                </td>
                <td>
                    @if ($isInstalled)
                        <span class="badge badge-success">Installed</span>
                    @else
                        <span class="badge badge-warning">Pending</span>
                    @endif
                </td>
                <td class="text-center">
                    <a href="{{ route('tasks.show', [$target->id, 'project_type' => 1]) }}"
                        class="btn btn-icon btn-info" data-toggle="tooltip" title="View Details">
                        <i class="mdi mdi-eye"></i>
                    </a>
                    <a href="{{ route('tasks.edit', [$target->id, 'project_id' => $project->id]) }}"
                        class="btn btn-icon btn-warning" data-toggle="tooltip" title="Edit Target">
                        <i class="mdi mdi-pencil"></i>
                    </a>
                    <button type="button" class="btn btn-icon btn-danger delete-task-btn" data-toggle="tooltip"
                        title="Delete Target" data-id="{{ $target->id }}"
                        data-url="{{ route('tasks.destroystreetlight', $target->id) }}">
                        <i class="mdi mdi-delete"></i>
                    </button>
                </td>
            </tr>
        @endforeach
    </x-datatable>
</div>

@push('scripts')
    <script>
        $(document).ready(function() {
            // Add project_id to import form
            $('form.import-form-group').append(
                '<input type="hidden" name="project_id" value="{{ $project->id }}">');

            // Initialize Select2 for filter dropdowns
            setTimeout(function() {
                // Get the height of regular select to match Select2
                const regularSelectHeight = $('.filter-select:not(.filter-select2)').first()
                    .outerHeight() || 31;

                $('.filter-select2').each(function() {
                    const $select = $(this);
                    if (!$select.hasClass('select2-hidden-accessible')) {
                        $select.select2({
                            placeholder: $select.closest('div').find('label').text() ||
                                'Select...',
                            allowClear: true,
                            width: '100%',
                            dropdownParent: $select.closest('.datatable-wrapper'),
                            minimumResultsForSearch: 0, // Always show search box
                        });

                        // Ensure Select2 has same height as regular select
                        $select.next('.select2-container').find('.select2-selection--single').css({
                            'height': regularSelectHeight + 'px',
                            'line-height': (regularSelectHeight - 2) + 'px',
                            'min-height': regularSelectHeight + 'px'
                        });
                    }
                });
            }, 500);

            // Custom filters - integrate with datatable component's filter system
            setTimeout(function() {
                const table = $('#targetsTable').DataTable();
                if (table) {
                    let filterFunctions = [];

                    // Intercept applyFilters button click
                    $(document).off('click', '#targetsTable_applyFilters').on('click',
                        '#targetsTable_applyFilters',
                        function(e) {
                            e.preventDefault();
                            e.stopImmediatePropagation();

                            // Remove previous filter functions
                            filterFunctions.forEach(function(filterFn) {
                                const index = $.fn.dataTable.ext.search.indexOf(filterFn);
                                if (index !== -1) {
                                    $.fn.dataTable.ext.search.splice(index, 1);
                                }
                            });
                            filterFunctions = [];

                            // Get filter container for this datatable
                            const filterContainer = $('#datatable-wrapper-targetsTable');

                            // Get filter values (Select2 compatible)
                            const statusSelect = filterContainer.find(
                                '.filter-select[data-filter="filter_status"]');
                            const statusFilter = statusSelect.hasClass('select2-hidden-accessible') ?
                                statusSelect.select2('val') : statusSelect.val();
                            const panchayatSelect = filterContainer.find(
                                '.filter-select[data-filter="filter_panchayat"]');
                            const panchayatFilter = panchayatSelect.hasClass(
                                    'select2-hidden-accessible') ?
                                panchayatSelect.select2('val') : panchayatSelect.val();
                            const engineerSelect = filterContainer.find(
                                '.filter-select[data-filter="filter_engineer"]');
                            const engineerFilter = engineerSelect.hasClass(
                                    'select2-hidden-accessible') ?
                                engineerSelect.select2('val') : engineerSelect.val();
                            const vendorSelect = filterContainer.find(
                                '.filter-select[data-filter="filter_vendor"]');
                            const vendorFilter = vendorSelect.hasClass('select2-hidden-accessible') ?
                                vendorSelect.select2('val') : vendorSelect.val();

                            // Create filter functions
                            if (statusFilter) {
                                const statusFilterFn = function(settings, data, dataIndex) {
                                    if (settings.nTable.id !== 'targetsTable') return true;
                                    const $row = $(table.row(dataIndex).node());
                                    const rowStatus = $row.data('status') || 'Pending';
                                    return rowStatus === statusFilter;
                                };
                                $.fn.dataTable.ext.search.push(statusFilterFn);
                                filterFunctions.push(statusFilterFn);
                            }

                            if (panchayatFilter) {
                                const panchayatFilterFn = function(settings, data, dataIndex) {
                                    if (settings.nTable.id !== 'targetsTable') return true;
                                    const $row = $(table.row(dataIndex).node());
                                    const rowPanchayat = $row.data('panchayat') || 'N/A';
                                    return rowPanchayat === panchayatFilter;
                                };
                                $.fn.dataTable.ext.search.push(panchayatFilterFn);
                                filterFunctions.push(panchayatFilterFn);
                            }

                            if (engineerFilter) {
                                const engineerFilterFn = function(settings, data, dataIndex) {
                                    if (settings.nTable.id !== 'targetsTable') return true;
                                    const $row = $(table.row(dataIndex).node());
                                    const rowEngineer = $row.data('engineer') || 'N/A';
                                    return rowEngineer === engineerFilter;
                                };
                                $.fn.dataTable.ext.search.push(engineerFilterFn);
                                filterFunctions.push(engineerFilterFn);
                            }

                            if (vendorFilter) {
                                const vendorFilterFn = function(settings, data, dataIndex) {
                                    if (settings.nTable.id !== 'targetsTable') return true;
                                    const $row = $(table.row(dataIndex).node());
                                    const rowVendor = $row.data('vendor') || 'N/A';
                                    return rowVendor === vendorFilter;
                                };
                                $.fn.dataTable.ext.search.push(vendorFilterFn);
                                filterFunctions.push(vendorFilterFn);
                            }

                            // Apply filters and redraw
                            table.draw();
                        });

                    // Clear filters
                    $(document).off('click', '#targetsTable_clearFilters').on('click',
                        '#targetsTable_clearFilters',
                        function(e) {
                            e.preventDefault();
                            e.stopImmediatePropagation();

                            // Get filter container for this datatable
                            const filterContainer = $('#datatable-wrapper-targetsTable');

                            // Remove all filter functions
                            filterFunctions.forEach(function(filterFn) {
                                const index = $.fn.dataTable.ext.search.indexOf(filterFn);
                                if (index !== -1) {
                                    $.fn.dataTable.ext.search.splice(index, 1);
                                }
                            });
                            filterFunctions = [];

                            // Clear filter inputs and redraw
                            filterContainer.find('.filter-select, .filter-date, .filter-text').val('');
                            // Clear Select2 dropdowns
                            filterContainer.find('.filter-select2').each(function() {
                                if ($(this).hasClass('select2-hidden-accessible')) {
                                    $(this).val(null).trigger('change');
                                }
                            });
                            table.search('').columns().search('').draw();
                        });
                }
            }, 1500);

            // Add Reassign button to bulk actions and ensure bulk actions are visible
            setTimeout(function() {
                const bulkActionsDiv = $('#targetsTable_bulkActions');
                if (bulkActionsDiv.length && $('#bulkReassignBtn').length === 0) {
                    const deleteBtn = $('#targetsTable_bulkDeleteBtn');
                    const deleteBtnParent = deleteBtn.parent();

                    // Check if buttons wrapper already exists, if not create it
                    let buttonsWrapper = deleteBtnParent.find('.bulk-actions-buttons');
                    if (buttonsWrapper.length === 0) {
                        // Create wrapper div for vertically stacked buttons
                        buttonsWrapper = $('<div>').addClass(
                            'd-flex flex-column gap-2 bulk-actions-buttons');
                        // Move delete button into wrapper
                        deleteBtn.detach().appendTo(buttonsWrapper);
                        // Append wrapper to parent
                        deleteBtnParent.append(buttonsWrapper);
                    }

                    // Create reassign button
                    const reassignBtn = $('<button>')
                        .attr('type', 'button')
                        .attr('id', 'bulkReassignBtn')
                        .addClass(
                            'btn btn-sm btn-warning d-inline-flex align-items-center gap-1 w-10 w-sm-auto'
                        )
                        .html('<i class="mdi mdi-account-switch"></i><span>Reassign Selected</span>');

                    // Append reassign button to wrapper (below delete button)
                    buttonsWrapper.append(reassignBtn);

                    $('#bulkReassignBtn').on('click', function() {
                        const table = $('#targetsTable').DataTable();
                        const selectedIds = [];

                        // Get all checked checkboxes
                        $('#targetsTable tbody .row-checkbox:checked').each(function() {
                            const taskId = $(this).val();
                            if (taskId) {
                                selectedIds.push(taskId);
                            }
                        });

                        if (selectedIds.length === 0) {
                            Swal.fire('Error', 'Please select at least one target.', 'error');
                            return;
                        }

                        reassignTargets(selectedIds);
                    });
                }

                // Ensure bulk actions are visible when checkboxes are checked
                function updateBulkActionsVisibility() {
                    // Wait for table to be initialized and tab to be active
                    const table = $('#targetsTable').DataTable();
                    if (!table) return;

                    // Check if the targets tab is active
                    const targetsTab = $('#tasks');
                    if (!targetsTab.hasClass('active') && !targetsTab.hasClass('show')) {
                        return; // Don't update if tab is not visible
                    }

                    const checkedCount = $('#targetsTable tbody .row-checkbox:checked').length;
                    const bulkActionsDiv = $('#targetsTable_bulkActions');
                    const selectedCountSpan = $('#targetsTable_selectedCount');

                    if (checkedCount > 0) {
                        if (bulkActionsDiv.is(':hidden')) {
                            bulkActionsDiv.slideDown(200);
                        }
                        selectedCountSpan.text(checkedCount);
                    } else {
                        if (bulkActionsDiv.is(':visible')) {
                            bulkActionsDiv.slideUp(200);
                        }
                    }
                }

                // Handle individual checkbox changes
                $(document).on('change', '#targetsTable tbody .row-checkbox', function() {
                    updateBulkActionsVisibility();
                });

                // Handle select all checkbox changes - trigger change on all checkboxes
                $(document).on('change', '#targetsTable_selectAll', function() {
                    const isChecked = $(this).is(':checked');
                    const table = $('#targetsTable').DataTable();
                    if (!table) return;

                    // Update all checkboxes on all pages
                    table.$('.row-checkbox').prop('checked', isChecked);

                    // Trigger change event on all checkboxes to ensure bulk actions update
                    table.$('.row-checkbox').trigger('change');

                    // Also directly update bulk actions
                    setTimeout(updateBulkActionsVisibility, 100);
                });

                // Update bulk actions when tab becomes active
                $('#tasks-tab').on('shown.bs.tab', function() {
                    setTimeout(updateBulkActionsVisibility, 300);
                });

                // Force update after table is fully initialized and tab is active
                setTimeout(function() {
                    // Check if tasks tab is active (either from hash or default)
                    const hash = window.location.hash;
                    const isTasksTabActive = hash === '#tasks' || $('#tasks').hasClass('active');

                    if (isTasksTabActive) {
                        updateBulkActionsVisibility();
                    }
                }, 2000);
            }, 500);

            // Reassign functionality
            function reassignTargets(selectedIds) {
                if (selectedIds.length === 0) {
                    Swal.fire('Error', 'Please select at least one target.', 'error');
                    return;
                }

                Swal.fire({
                    title: 'Reassign Targets',
                    html: `
            <div class="text-left">
              <div class="mb-3">
                <label for="reassignEngineer" class="form-label">Select Engineer (Optional)</label>
                <select id="reassignEngineer" class="form-select select2-reassign-engineer">
                  <option value="">No Change</option>
                  @foreach ($reassignEngineers as $engineer)
                    <option value="{{ $engineer->id }}">{{ $engineer->firstName }} {{ $engineer->lastName }}</option>
                  @endforeach
                </select>
              </div>
              <div class="mb-3">
                <label for="reassignVendor" class="form-label">Select Vendor (Optional)</label>
                <select id="reassignVendor" class="form-select select2-reassign-vendor">
                  <option value="">No Change</option>
                  @foreach ($reassignVendors as $vendor)
                    <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                  @endforeach
                </select>
              </div>
            </div>
          `,
                    showCancelButton: true,
                    confirmButtonText: 'Reassign',
                    cancelButtonText: 'Cancel',
                    didOpen: () => {
                        // Small delay to ensure modal DOM is fully rendered
                        setTimeout(() => {
                            // Initialize Select2 for engineer dropdown
                            $('#reassignEngineer').select2({
                                placeholder: 'Search engineer...',
                                allowClear: true,
                                width: '100%',
                                dropdownParent: $('.swal2-container'),
                                minimumResultsForSearch: 0, // Always show search box
                            });

                            // Initialize Select2 for vendor dropdown
                            $('#reassignVendor').select2({
                                placeholder: 'Search vendor...',
                                allowClear: true,
                                width: '100%',
                                dropdownParent: $('.swal2-container'),
                                minimumResultsForSearch: 0, // Always show search box
                            });
                        }, 100);
                    },
                    preConfirm: () => {
                        const engineerSelect = $('#reassignEngineer');
                        const vendorSelect = $('#reassignVendor');
                        const engineerId = engineerSelect.hasClass('select2-hidden-accessible') ?
                            engineerSelect.select2('val') :
                            engineerSelect.val();
                        const vendorId = vendorSelect.hasClass('select2-hidden-accessible') ?
                            vendorSelect.select2('val') :
                            vendorSelect.val();

                        if (!engineerId && !vendorId) {
                            Swal.showValidationMessage('Please select at least one field to reassign');
                            return false;
                        }

                        const data = {};
                        if (engineerId) {
                            data.engineer_id = engineerId;
                        }
                        if (vendorId) {
                            data.vendor_id = vendorId;
                        }
                        return data;
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('tasks.bulkReassign') }}",
                            method: 'POST',
                            data: {
                                _token: "{{ csrf_token() }}",
                                ids: selectedIds,
                                engineer_id: result.value.engineer_id,
                                vendor_id: result.value.vendor_id,
                            },
                            success: function(response) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success!',
                                    text: response.message ||
                                        'Targets reassigned successfully',
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => {
                                    // Reload the page to refresh the table, preserving hash
                                    const currentHash = window.location.hash;
                                    window.location.reload();
                                });
                            },
                            error: function(xhr) {
                                Swal.fire('Error', xhr.responseJSON?.message ||
                                    'Failed to reassign targets', 'error');
                            }
                        });
                    }
                });
            };

            // Delete button handler
            $(document).on('click', '.delete-task-btn', function(e) {
                e.preventDefault();
                const taskId = $(this).data('id');
                const deleteUrl = $(this).data('url');

                Swal.fire({
                    title: 'Are you sure?',
                    text: 'You are about to delete this target. This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel',
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: deleteUrl,
                            type: 'POST',
                            data: {
                                _token: "{{ csrf_token() }}",
                                _method: 'DELETE',
                            },
                            success: function(response) {
                                // Check if async deletion was initiated
                                if (response.job_id) {
                                    // Async deletion - start progress tracking
                                    if (window.targetDeletionProgress) {
                                        window.targetDeletionProgress
                                            .startProgressTracking(response.job_id);
                                    }
                                } else {
                                    // Synchronous deletion - show success and reload
                                    Swal.fire('Deleted!', response.message ||
                                        'Target has been deleted.', 'success');
                                    setTimeout(() => window.location.reload(), 1500);
                                }
                            },
                            error: function(xhr) {
                                Swal.fire('Error!', xhr.responseJSON?.message ||
                                    'Failed to delete target.', 'error');
                            }
                        });
                    }
                });
            });

            // Select2 for panchayat search - with district and block filtering
            function initializePanchayatSelect2() {
                const district = $('#districtSearch').val();
                const block = $('#blockSearch').val();
                
                // Destroy existing Select2 instance if it exists
                if ($('#panchayatSearch').hasClass('select2-hidden-accessible')) {
                    $('#panchayatSearch').select2('destroy');
                }
                
                // Disable panchayat search if district or block is not selected
                if (!district || !block) {
                    $('#panchayatSearch').prop('disabled', true).empty();
                    return;
                }
                
                $('#panchayatSearch').prop('disabled', false).empty();
                
                $('#panchayatSearch').select2({
                    placeholder: "Type to search panchayats in " + block + " block",
                    allowClear: true,
                    dropdownParent: $('#addTargetModal'),
                    ajax: {
                        url: "{{ route('streetlights.search') }}",
                        dataType: 'json',
                        method: "GET",
                        delay: 250,
                        data: function(params) {
                            return {
                                search: params.term,
                                district: district,
                                block: block,
                                project_id: {{ $project->id }}
                            };
                        },
                        processResults: function(data) {
                            if (data.length === 0) {
                                return {
                                    results: []
                                };
                            }
                            return {
                                results: data.map(item => ({
                                    id: item.id,
                                    text: item.text
                                }))
                            };
                        },
                        error: function(xhr) {
                            console.error('Error searching panchayats:', xhr);
                        }
                    }
                });
            }
            
            // Function to initialize Engineer and Vendor Select2
            function initializeEngineerVendorSelect2() {
                // Initialize Select2 for Engineer dropdown
                const engineerSelect = $('#selectEngineer');
                if (engineerSelect.length && !engineerSelect.hasClass('select2-hidden-accessible')) {
                    engineerSelect.select2({
                        placeholder: 'Search and select engineer...',
                        allowClear: true,
                        dropdownParent: $('#addTargetModal'),
                        width: '100%',
                        minimumResultsForSearch: 0, // Always show search box
                        dropdownCssClass: 'select2-dropdown-engineer',
                    });
                }
                
                // Initialize Select2 for Vendor dropdown
                const vendorSelect = $('#selectVendor');
                if (vendorSelect.length && !vendorSelect.hasClass('select2-hidden-accessible')) {
                    vendorSelect.select2({
                        placeholder: 'Search and select vendor...',
                        allowClear: true,
                        dropdownParent: $('#addTargetModal'),
                        width: '100%',
                        minimumResultsForSearch: 0, // Always show search box
                        dropdownCssClass: 'select2-dropdown-vendor',
                    });
                }
            }

            // Initialize Select2 when modal opens
            $('#addTargetModal').on('shown.bs.modal', function() {
                initializePanchayatSelect2();
                
                // Small delay to ensure modal is fully rendered
                setTimeout(function() {
                    initializeEngineerVendorSelect2();
                }, 100);
            });

            // Also initialize on modal show (earlier event) as fallback
            $('#addTargetModal').on('show.bs.modal', function() {
                // Reinitialize if already initialized (to ensure it works)
                const engineerSelect = $('#selectEngineer');
                const vendorSelect = $('#selectVendor');
                
                if (engineerSelect.hasClass('select2-hidden-accessible')) {
                    engineerSelect.select2('destroy');
                }
                if (vendorSelect.hasClass('select2-hidden-accessible')) {
                    vendorSelect.select2('destroy');
                }
            });
            
            // Reinitialize Select2 when district or block changes
            $(document).on('change', '#districtSearch, #blockSearch', function() {
                // Clear panchayat selection
                $('#panchayatSearch').val(null).trigger('change');
                $('#wardSelectionContainer').hide();
                $('#wardCheckboxes').empty();
                
                // Reinitialize Select2 with new filters
                initializePanchayatSelect2();
            });

            // Handle panchayat selection change for ward loading
            $('#panchayatSearch').on('select2:select select2:unselect', function(e) {
                const selectedPanchayats = $(this).val();

                if (!selectedPanchayats || selectedPanchayats.length === 0) {
                    $('#wardSelectionContainer').slideUp(300);
                    $('#wardCheckboxes').empty();
                    return;
                }

                // If multiple panchayats selected, show wards for the last selected one
                // If single panchayat selected, show its wards
                const siteId = Array.isArray(selectedPanchayats) ?
                    selectedPanchayats[selectedPanchayats.length - 1] :
                    selectedPanchayats;

                loadWardsForSite(siteId);
            });

            // Fetch Blocks Based on Selected District
            // Use event delegation to ensure it works even if modal is loaded dynamically
            $(document).on('change', '#districtSearch', function() {
                let district = $(this).val();
                console.log('District selected:', district);

                $('#blockSearch').prop('disabled', false).empty().append(
                    '<option value="">Select a Block</option>');
                $('#panchayatSearch').prop('disabled', true).empty();
                $('#wardSelectionContainer').hide();
                $('#wardCheckboxes').empty();

                if (district) {
                    // Encode the district name for URL
                    const encodedDistrict = encodeURIComponent(district);
                    console.log('Fetching blocks for district:', district, 'Encoded:', encodedDistrict);

                    $.ajax({
                        url: '/blocks-by-district/' + encodedDistrict,
                        type: 'GET',
                        dataType: 'json',
                        data: {
                            project_id: {{ $project->id }}
                        },
                        success: function(data) {
                            console.log('Blocks response:', data);

                            if (Array.isArray(data) && data.length > 0) {
                                $.each(data, function(index, block) {
                                    // Handle both string and object formats (JICR format uses objects with .block property)
                                    const blockValue = typeof block === 'string' ?
                                        block : (block.block || block);
                                    const blockText = typeof block === 'string' ?
                                        block : (block.block || block);

                                    if (blockValue && blockText) {
                                        $('#blockSearch').append('<option value="' +
                                            blockValue + '">' + blockText +
                                            '</option>');
                                    }
                                });

                                // Enable the block select if we have blocks
                                if ($('#blockSearch option').length > 1) {
                                    $('#blockSearch').prop('disabled', false);
                                }
                            } else {
                                console.warn('No blocks found for district:', district);
                                $('#blockSearch').append(
                                    '<option value="">No blocks available</option>');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error("AJAX Error fetching blocks:", status, error);
                            console.error("Response:", xhr.responseText);
                            console.error("Status Code:", xhr.status);
                            console.error("URL:", '/blocks-by-district/' + encodedDistrict);

                            $('#blockSearch').append(
                                '<option value="">Error loading blocks</option>');

                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Failed to load blocks. Please check the console for details.',
                                timer: 3000,
                                showConfirmButton: false,
                                toast: true,
                                position: 'top-end'
                            });
                        }
                    });
                } else {
                    $('#blockSearch').prop('disabled', true);
                }
            });

            // Fetch Panchayats Based on Selected Block
            // Use event delegation
            $(document).on('change', '#blockSearch', function() {
                let block = $(this).val();
                console.log('Block selected:', block);
                
                // Clear panchayat selection and wards
                $('#panchayatSearch').val(null).trigger('change');
                $('#wardSelectionContainer').hide();
                $('#wardCheckboxes').empty();
                
                // Reinitialize Select2 with new block filter (handled by initializePanchayatSelect2)
                // This will enable Select2 and set up AJAX search filtered by district and block
                initializePanchayatSelect2();
            });

            // Function to load wards for a specific site
            function loadWardsForSite(siteId) {
                if (!siteId) {
                    $('#wardSelectionContainer').hide();
                    $('#wardCheckboxes').empty();
                    return;
                }

                $.ajax({
                    url: '/wards-by-site/' + siteId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(wards) {
                        if (wards && wards.length > 0) {
                            displayWards(wards);
                            $('#wardSelectionContainer').slideDown(300);
                        } else {
                            $('#wardSelectionContainer').hide();
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX Error fetching wards:", status, error);
                        $('#wardSelectionContainer').hide();
                    }
                });
            }

            function displayWards(wards) {
                const container = $('#wardCheckboxes');
                container.empty();

                wards.forEach(function(ward) {
                    const wardValue = ward.trim();
                    if (wardValue) {
                        const checkbox = $('<div>').addClass(
                            'form-check form-check-inline ward-checkbox-item mb-2');
                        checkbox.html(`
              <input class="form-check-input ward-checkbox" type="checkbox" 
                     id="ward_${wardValue}" value="${wardValue}" checked>
              <label class="form-check-label" for="ward_${wardValue}">
                Ward ${wardValue}
              </label>
            `);
                        container.append(checkbox);
                    }
                });

                updateSelectedWards();
            }

            // Select/Deselect All Wards
            $('#selectAllWards').on('click', function() {
                $('.ward-checkbox').prop('checked', true);
                updateSelectedWards();
            });

            $('#deselectAllWards').on('click', function() {
                $('.ward-checkbox').prop('checked', false);
                updateSelectedWards();
            });

            // Update selected wards when checkboxes change
            $(document).on('change', '.ward-checkbox', function() {
                updateSelectedWards();
            });

            function updateSelectedWards() {
                const selectedWards = [];
                $('.ward-checkbox:checked').each(function() {
                    selectedWards.push($(this).val());
                });
                $('#selectedWardsInput').val(selectedWards.join(','));
            }

            // Helper function to clear all error states
            function clearFormErrors() {
                $('.form-control, .form-select').removeClass('is-invalid');
                $('.select2-container .select2-selection').removeClass('is-invalid');
                $('.invalid-feedback').text('').hide();
            }

            // Helper function to show field error
            function showFieldError(fieldName, message) {
                const field = $(`[name="${fieldName}"]`);
                const errorDiv = $(`#${fieldName}_error`);

                field.addClass('is-invalid');

                // Handle Select2 fields
                if (field.hasClass('select2-hidden-accessible')) {
                    field.next('.select2-container').find('.select2-selection').addClass('is-invalid');
                }

                if (errorDiv.length) {
                    errorDiv.text(message).show();
                } else {
                    // Create error div if it doesn't exist
                    const errorHtml = `<div class="invalid-feedback" id="${fieldName}_error">${message}</div>`;
                    field.closest('.form-group, .mb-3').append(errorHtml);
                }
            }

            // Client-side validation
            function validateForm() {
                let isValid = true;
                clearFormErrors();

                // Validate engineer
                const engineerSelect = $('#selectEngineer');
                const engineerId = engineerSelect.hasClass('select2-hidden-accessible') ?
                    engineerSelect.select2('val') : engineerSelect.val();
                if (!engineerId) {
                    showFieldError('engineer_id', 'Please select an engineer.');
                    isValid = false;
                }

                // Validate vendor
                const vendorSelect = $('#selectVendor');
                const vendorId = vendorSelect.hasClass('select2-hidden-accessible') ?
                    vendorSelect.select2('val') : vendorSelect.val();
                if (!vendorId) {
                    showFieldError('vendor_id', 'Please select a vendor.');
                    isValid = false;
                }

                // Validate panchayat/sites
                const sites = $('#panchayatSearch').val();
                if (!sites || sites.length === 0) {
                    showFieldError('sites', 'Please select at least one panchayat.');
                    isValid = false;
                }

                // Validate start date
                const startDate = $('#startDate').val();
                if (!startDate) {
                    showFieldError('start_date', 'Start date is required.');
                    isValid = false;
                }

                // Validate end date
                const endDate = $('#endDate').val();
                if (!endDate) {
                    showFieldError('end_date', 'End date is required.');
                    isValid = false;
                }

                // Validate date range
                if (startDate && endDate) {
                    const start = new Date(startDate);
                    const end = new Date(endDate);
                    if (end < start) {
                        showFieldError('end_date', 'End date must be equal to or after start date.');
                        isValid = false;
                    }
                }

                return isValid;
            }

            // Clear errors when modal is opened
            $('#addTargetModal').on('show.bs.modal', function() {
                clearFormErrors();
            });

            // Clear errors when modal is closed
            $('#addTargetModal').on('hidden.bs.modal', function() {
                clearFormErrors();
                // Reset form
                $('#targetForm')[0].reset();
                $('#panchayatSearch').val(null).trigger('change');
                
                // Reset select2 dropdowns properly
                const engineerSelect = $('#selectEngineer');
                const vendorSelect = $('#selectVendor');
                
                if (engineerSelect.hasClass('select2-hidden-accessible')) {
                    engineerSelect.val(null).trigger('change');
                } else {
                    engineerSelect.val(null);
                }
                
                if (vendorSelect.hasClass('select2-hidden-accessible')) {
                    vendorSelect.val(null).trigger('change');
                } else {
                    vendorSelect.val(null);
                }
            });

            // Clear errors when reset button is clicked
            $('#targetForm').on('reset', function() {
                setTimeout(function() {
                    clearFormErrors();
                }, 100);
            });
            e.preventDefault();

            // Clear previous errors
            clearFormErrors();

            // Client-side validation
            if (!validateForm()) {
                // Scroll to first error
                const firstError = $('.is-invalid').first();
                if (firstError.length) {
                    $('html, body').animate({
                        scrollTop: firstError.offset().top - 100
                    }, 500);
                }
                return false;
            }

            // Get form data
            const formData = new FormData(this);
            const submitButton = $(this).find('button[type="submit"]');
            const originalButtonText = submitButton.html();

            // Disable submit button and show loading state
            submitButton.prop('disabled', true).html(
                '<span class="spinner-border spinner-border-sm me-2"></span>Submitting...');

            // Make AJAX request
            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    if (response.success) {
                        // Show success toast
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message || 'Targets successfully added.',
                            timer: 2000,
                            showConfirmButton: false,
                            toast: true,
                            position: 'top-end'
                        }).then(() => {
                            // Close modal
                            const modal = bootstrap.Modal.getInstance(document.getElementById(
                                'addTargetModal'));
                            if (modal) {
                                modal.hide();
                            }
                            // Reset form
                            $('#targetForm')[0].reset();
                            $('#panchayatSearch').val(null).trigger('change');
                            clearFormErrors();
                            // Reload page to show new targets
                            if (response.redirect_url) {
                                window.location.href = response.redirect_url;
                            } else {
                                window.location.reload();
                            }
                        });
                    }
                },
                error: function(xhr) {
                    // Re-enable submit button
                    submitButton.prop('disabled', false).html(originalButtonText);

                    if (xhr.status === 422) {
                        // Validation errors
                        const errors = xhr.responseJSON?.errors || {};

                        // Show field-level errors
                        Object.keys(errors).forEach(function(field) {
                            const errorMessages = errors[field];
                            const errorMessage = Array.isArray(errorMessages) ? errorMessages[
                                0] : errorMessages;

                            // Handle array fields like sites[]
                            const fieldName = field.replace(/\./g, '_').replace(/\[\]/g, '');
                            showFieldError(fieldName, errorMessage);
                        });

                        // Scroll to first error
                        const firstError = $('.is-invalid').first();
                        if (firstError.length) {
                            $('html, body').animate({
                                scrollTop: firstError.offset().top - 100
                            }, 500);
                        }

                        // Show toast notification
                        Swal.fire({
                            icon: 'error',
                            title: 'Validation Error',
                            text: xhr.responseJSON?.message ||
                                'Please check your input and try again.',
                            timer: 3000,
                            showConfirmButton: false,
                            toast: true,
                            position: 'top-end'
                        });
                    } else if (xhr.status >= 500) {
                        // Server errors
                        Swal.fire({
                            icon: 'error',
                            title: 'Server Error',
                            html: `
                  <p><strong>Status:</strong> ${xhr.status}</p>
                  <p><strong>Error:</strong> ${xhr.responseJSON?.error || xhr.responseJSON?.message || 'An unexpected error occurred.'}</p>
                  ${xhr.responseJSON?.code ? `<p><strong>Code:</strong> ${xhr.responseJSON.code}</p>` : ''}
                `,
                            confirmButtonText: 'OK'
                        });
                    } else {
                        // Other errors
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.message || xhr.responseJSON?.error ||
                                'An error occurred. Please try again.',
                            confirmButtonText: 'OK'
                        });
                    }
                }
            });

            return false;
        });

    </script>

    <!-- Target Deletion Progress Tracker -->
    <script>
        // Set poll interval from config
        window.TARGET_DELETION_POLL_INTERVAL = {{ config('target_deletion.progress_poll_interval', 2000) }};
    </script>
    <script src="{{ asset('js/target-deletion-progress.js') }}?v={{ time() }}"></script>

    <!-- Override bulk delete to handle async responses -->
    <script>
        // Wait for both jQuery and the progress tracker script to be loaded
        $(document).ready(function() {
            // Ensure progress tracker is initialized
            if (typeof window.targetDeletionProgress === 'undefined' && typeof TargetDeletionProgress !==
                'undefined') {
                window.targetDeletionProgress = new TargetDeletionProgress();
                window.targetDeletionProgress.init();
            }
            // Function to attach our custom bulk delete handler
            function attachBulkDeleteHandler() {
                const bulkDeleteBtn = $('#targetsTable_bulkDeleteBtn');

                if (bulkDeleteBtn.length && !bulkDeleteBtn.data('custom-handler-attached')) {
                    // Mark as attached to prevent duplicate handlers
                    bulkDeleteBtn.data('custom-handler-attached', true);

                    // Unbind any existing handlers from datatable component
                    bulkDeleteBtn.off('click');

                    // Add our custom handler with higher priority
                    bulkDeleteBtn.on('click', function(e) {
                        e.preventDefault();
                        e.stopImmediatePropagation();
                        e.stopPropagation();

                        const table = $('#targetsTable').DataTable();
                        if (!table) {
                            Swal.fire('Error', 'Table not initialized.', 'error');
                            return false;
                        }

                        const selectedIds = [];

                        // Get all checked checkboxes
                        $('#targetsTable tbody .row-checkbox:checked').each(function() {
                            const taskId = $(this).val();
                            if (taskId) {
                                selectedIds.push(parseInt(taskId));
                            }
                        });

                        if (selectedIds.length === 0) {
                            Swal.fire('Error', 'Please select at least one target.', 'error');
                            return false;
                        }

                        Swal.fire({
                            title: 'Are you sure?',
                            text: `You are about to delete ${selectedIds.length} target(s). This action cannot be undone.`,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#3085d6',
                            confirmButtonText: 'Yes, delete them!',
                            cancelButtonText: 'Cancel',
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // Show progress modal IMMEDIATELY before making AJAX call
                                let tempJobId = 'temp-' + Date.now();

                                // Ensure progress tracker is initialized
                                if (typeof window.targetDeletionProgress === 'undefined') {
                                    if (typeof TargetDeletionProgress !== 'undefined') {
                                        window.targetDeletionProgress =
                                            new TargetDeletionProgress();
                                    } else {
                                        console.error('TargetDeletionProgress class not loaded!');
                                        Swal.fire({
                                            icon: 'warning',
                                            title: 'Progress Tracker Not Loaded',
                                            text: 'Please refresh the page and try again.',
                                            confirmButtonText: 'OK'
                                        });
                                        return;
                                    }
                                }

                                // Show modal immediately with "Initializing..." message
                                if (window.targetDeletionProgress) {
                                    window.targetDeletionProgress.showProgressModal(tempJobId);
                                    window.targetDeletionProgress.updateProgressModal({
                                        progress_percentage: 0,
                                        processed_tasks: 0,
                                        total_tasks: selectedIds.length,
                                        message: 'Initializing deletion...',
                                        status: 'pending'
                                    });
                                }

                                // Now make the AJAX call
                                $.ajax({
                                    url: "{{ route('tasks.bulkDelete') }}",
                                    method: 'POST',
                                    headers: {
                                        'X-Requested-With': 'XMLHttpRequest',
                                        'Accept': 'application/json',
                                    },
                                    data: {
                                        _token: "{{ csrf_token() }}",
                                        ids: selectedIds,
                                    },
                                    success: function(response) {
                                        console.log('Bulk delete response:', response);
                                        console.log('Response type:', typeof response);
                                        console.log('Response has job_id:', response &&
                                            response.job_id);
                                        console.log('Response keys:', response ? Object
                                            .keys(response) : 'null');

                                        // Check if async deletion was initiated
                                        if (response && response.job_id) {
                                            // Async deletion - start progress tracking with real job_id
                                            console.log(
                                                'Starting progress tracking for job:',
                                                response.job_id);

                                            if (window.targetDeletionProgress) {
                                                try {
                                                    // Update modal with real job_id and start polling
                                                    window.targetDeletionProgress
                                                        .currentJobId = response.job_id;
                                                    localStorage.setItem(
                                                        'target_deletion_job_id',
                                                        response.job_id);
                                                    window.targetDeletionProgress
                                                        .startPolling(response.job_id);
                                                    console.log(
                                                        'Progress tracking started successfully'
                                                    );
                                                } catch (error) {
                                                    console.error(
                                                        'Error starting progress tracking:',
                                                        error);
                                                    console.error('Error stack:', error
                                                        .stack);
                                                    window.targetDeletionProgress
                                                        .updateProgressModal({
                                                            status: 'failed',
                                                            error_message: 'Progress tracking failed: ' +
                                                                error.message
                                                        });
                                                }
                                            }
                                        } else {
                                            // Synchronous deletion - update modal and reload
                                            console.log(
                                                'No job_id in response, treating as synchronous deletion'
                                            );
                                            console.log('Response:', JSON.stringify(
                                                response));

                                            if (window.targetDeletionProgress) {
                                                window.targetDeletionProgress
                                                    .updateProgressModal({
                                                        progress_percentage: 100,
                                                        status: 'completed',
                                                        message: response.message ||
                                                            'Targets deleted successfully.'
                                                    });

                                                setTimeout(() => {
                                                    const modal = document
                                                        .getElementById(
                                                            'targetDeletionProgressModal'
                                                        );
                                                    if (modal) {
                                                        const bsModal =
                                                            bootstrap.Modal
                                                            .getInstance(modal);
                                                        if (bsModal) {
                                                            bsModal.hide();
                                                        }
                                                    }
                                                    window.location.reload();
                                                }, 1500);
                                            } else {
                                                Swal.fire('Deleted!', response
                                                    .message ||
                                                    'Targets deleted successfully.',
                                                    'success');
                                                setTimeout(() => window.location
                                                    .reload(), 1500);
                                            }
                                        }
                                    },
                                    error: function(xhr) {
                                        console.error('Bulk delete error:', xhr);

                                        // Update modal with error
                                        if (window.targetDeletionProgress) {
                                            const errorMsg = xhr.responseJSON
                                                ?.message || xhr.responseJSON?.error ||
                                                'Failed to delete targets.';
                                            window.targetDeletionProgress
                                                .updateProgressModal({
                                                    status: 'failed',
                                                    error_message: errorMsg
                                                });
                                        } else {
                                            Swal.fire('Error!', xhr.responseJSON
                                                ?.message ||
                                                'Failed to delete targets.', 'error'
                                            );
                                        }
                                    }
                                });
                            }
                        });

                        return false;
                    });
                }
            }

            // Try to attach immediately
            attachBulkDeleteHandler();

            // Also try after a short delay (in case datatable initializes later)
            setTimeout(attachBulkDeleteHandler, 500);
            setTimeout(attachBulkDeleteHandler, 1500);

            // Also try when the table is drawn
            if ($.fn.DataTable && $('#targetsTable').length) {
                $('#targetsTable').on('draw.dt', function() {
                    setTimeout(attachBulkDeleteHandler, 100);
                });
            }
        });
    </script>
@endpush

@push('styles')
    <style>
        /* Summary Cards Styling */
        .summary-card {
            border: 2px solid;
            border-radius: 8px;
            padding: 0;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .summary-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .summary-card-total {
            border-color: #28a745;
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        }

        .summary-card-surveyed {
            border-color: #ffc107;
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
        }

        .summary-card-installed {
            border-color: #17a2b8;
            background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
        }

        .summary-card-body {
            padding: 1.25rem;
        }

        .summary-card-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .summary-card-total .summary-card-title {
            color: #155724;
        }

        .summary-card-surveyed .summary-card-title {
            color: #856404;
        }

        .summary-card-installed .summary-card-title {
            color: #0c5460;
        }

        .summary-card-text {
            font-size: 0.95rem;
            font-weight: 500;
            margin-bottom: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .summary-card-total .summary-card-text {
            color: #155724;
        }

        .summary-card-surveyed .summary-card-text {
            color: #856404;
        }

        .summary-card-installed .summary-card-text {
            color: #0c5460;
        }

        /* Consistent button width for Add buttons */
        .add-target-btn {
            min-width: 140px;
        }

        /* Consistent height for all filter selects */
        .filter-select {
            height: 31px !important;
            min-height: 31px !important;
        }

        /* Select2 filter dropdowns - match regular select height */
        .filter-select2+.select2-container .select2-selection--single {
            height: 31px !important;
            min-height: 31px !important;
            line-height: 29px !important;
        }

        .filter-select2+.select2-container .select2-selection__rendered {
            line-height: 29px !important;
            padding-left: 8px !important;
            padding-right: 20px !important;
        }

        .filter-select2+.select2-container .select2-selection__arrow {
            height: 29px !important;
        }

        /* General Select2 styling (for other Select2 instances) */
        .select2-container--default .select2-selection--single {
            height: 38px;
            padding: 6px 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .select2-selection__choice {
            background-color: #E9EECF !important;
            padding: 6px 10px !important;
            color: #000 !important;
            font-size: 0.875rem !important;
        }

        /* Wards column styling */
        .wards-cell {
            max-width: 200px !important;
            width: 200px !important;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .wards-content {
            max-height: 60px;
            overflow-y: auto;
            font-size: 0.875rem;
            line-height: 1.4;
            white-space: normal;
            word-break: break-word;
            overflow-wrap: break-word;
        }

        .wards-preview {
            cursor: help;
        }

        /* Add Target button styling to match parent */
        .datatable-wrapper .add-new-btn {
            max-height: 2.8rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Modern form styling */
        .modern-select {
            border-radius: 8px;
            border: 1px solid #dee2e6;
            padding: 0.5rem 0.75rem;
            transition: all 0.2s ease;
        }

        .modern-select:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
            outline: none;
        }

        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
        }

        .form-label i {
            margin-right: 0.5rem;
        }

        /* Ward Selection Styling */
        .ward-selection-wrapper {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 1rem;
        }

        .ward-selection-header {
            display: flex;
            align-items: center;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #dee2e6;
        }

        .ward-selection-header .btn-link {
            color: #007bff;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .ward-selection-header .btn-link:hover {
            color: #0056b3;
            text-decoration: underline;
        }

        .ward-checkboxes {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-top: 0.75rem;
            max-height: 200px;
            overflow-y: auto;
            padding: 0.5rem;
        }

        .ward-checkbox-item {
            min-width: 120px;
        }

        .ward-checkbox-item .form-check-input {
            margin-top: 0.25rem;
            cursor: pointer;
        }

        .ward-checkbox-item .form-check-label {
            cursor: pointer;
        }

        /* Select2 Dropdown Z-Index Fix */
        #addTargetModal .select2-container--open {
            z-index: 9999 !important;
        }

        #addTargetModal .select2-dropdown {
            z-index: 9999 !important;
            position: absolute !important;
        }

        #addTargetModal .select2-dropdown-engineer,
        #addTargetModal .select2-dropdown-vendor {
            z-index: 9999 !important;
        }

        /* Ensure ward container doesn't interfere with Select2 */
        #wardSelectionContainer {
            position: relative;
            z-index: 1;
        }

        /* Select2 results container should appear above everything */
        #addTargetModal .select2-results {
            z-index: 10000 !important;
        }

        /* Ensure Select2 search input is accessible */
        #addTargetModal .select2-search--dropdown {
            z-index: 10001 !important;
            position: relative;
        }

        /* Fix for Select2 dropdown positioning in modal */
        #addTargetModal .modal-body .select2-container {
            z-index: 9998;
        }

        #addTargetModal .modal-body .select2-container--open {
            z-index: 9999;
        }
            font-size: 0.875rem;
            color: #495057;
            margin-left: 0.5rem;
        }

        .ward-checkbox-item:hover .form-check-label {
            color: #007bff;
        }

        /* Modal styling improvements */
        .modal-content {
            border-radius: 12px;
            border: none;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px 12px 0 0;
            padding: 1.25rem 1.5rem;
        }

        .modal-header .modal-title {
            font-weight: 600;
            font-size: 1.1rem;
        }

        .modal-header .btn-close {
            filter: brightness(0) invert(1);
        }

        .modal-body {
            padding: 1.5rem;
            overflow: visible;
        }

        .modal-footer {
            border-top: 1px solid #dee2e6;
            padding: 1rem 1.5rem;
        }

        .modal-footer .btn {
            border-radius: 6px;
            padding: 0.5rem 1.25rem;
            font-weight: 500;
        }

        /* Error styling */
        .form-control.is-invalid,
        .form-select.is-invalid {
            border-color: #dc3545;
            padding-right: calc(1.5em + 0.75rem);
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath d='m5.8 3.6 .4.4.4-.4m0 4.8h.8'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }

        .invalid-feedback {
            display: block;
            width: 100%;
            margin-top: 0.25rem;
            font-size: 0.875rem;
            color: #dc3545;
        }

        .form-label .text-danger {
            margin-left: 0.25rem;
        }

        /* Select2 error styling */
        .select2-container--default .select2-selection.is-invalid {
            border-color: #dc3545 !important;
        }
    </style>
@endpush
