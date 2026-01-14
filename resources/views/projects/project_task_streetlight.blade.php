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
                <form action="{{ route('tasks.store') }}" method="POST">
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
              <label for="panchayatSearch" class="form-label">Select Panchayat</label>
                            <select id="panchayatSearch" name="sites[]" multiple="multiple" class="form-select"
                                style="width: 100%;">
                <option value="">Select a Panchayat</option>
              </select>
            </div>

            <div class="mb-3">
              <label for="selectEngineer" class="form-label">Select Site Engineer</label>
              <select id="selectEngineer" name="engineer_id" class="form-select" required>
                @foreach ($assignedEngineers as $engineer)
                                    <option value="{{ $engineer->id }}">{{ $engineer->firstName }}
                                        {{ $engineer->lastName }}</option>
                @endforeach
              </select>
            </div>
            <div class="form-group mb-3">
              <label for="selectVendor" class="form-label">Select Vendor</label>
              <select id="selectVendor" name="vendor_id" class="form-select" required>
                @foreach ($assignedVendors as $vendor)
                  <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="form-group mb-3">
              <label for="startDate" class="form-label">Start Date</label>
                            <input onclick="document.getElementById('startDate').showPicker()" type="date"
                                id="startDate" name="start_date" class="form-control" required>
            </div>
            <div class="form-group mb-3">
              <label for="endDate" class="form-label">End Date</label>
                            <input onclick="document.getElementById('endDate').showPicker()" type="date"
                                id="endDate" name="end_date" class="form-control" required>
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
        ['title' => 'Assigned Date', 'width' => '10%'],
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
                <td>{{ $target->created_at ? $target->created_at->format('Y-m-d') : 'N/A' }}</td>
                <td>{{ $target->end_date ? \Carbon\Carbon::parse($target->end_date)->format('Y-m-d') : 'N/A' }}</td>
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
                const regularSelectHeight = $('.filter-select:not(.filter-select2)').first().outerHeight() || 31;
                
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
                    $(document).off('click', '#targetsTable_applyFilters').on('click', '#targetsTable_applyFilters', function(e) {
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
                        const statusSelect = filterContainer.find('.filter-select[data-filter="filter_status"]');
                        const statusFilter = statusSelect.hasClass('select2-hidden-accessible') ?
                            statusSelect.select2('val') : statusSelect.val();
                        const panchayatSelect = filterContainer.find('.filter-select[data-filter="filter_panchayat"]');
                        const panchayatFilter = panchayatSelect.hasClass(
                                'select2-hidden-accessible') ?
                            panchayatSelect.select2('val') : panchayatSelect.val();
                        const engineerSelect = filterContainer.find('.filter-select[data-filter="filter_engineer"]');
                        const engineerFilter = engineerSelect.hasClass(
                            'select2-hidden-accessible') ?
                            engineerSelect.select2('val') : engineerSelect.val();
                        const vendorSelect = filterContainer.find('.filter-select[data-filter="filter_vendor"]');
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
                    $(document).off('click', '#targetsTable_clearFilters').on('click', '#targetsTable_clearFilters', function(e) {
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
                        buttonsWrapper = $('<div>').addClass('d-flex flex-column gap-2 bulk-actions-buttons');
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
                        const engineerId = engineerSelect.hasClass('select2-hidden-accessible') 
                            ? engineerSelect.select2('val') 
                            : engineerSelect.val();
                        const vendorId = vendorSelect.hasClass('select2-hidden-accessible') 
                            ? vendorSelect.select2('val') 
                            : vendorSelect.val();

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
                                    text: response.message || 'Targets reassigned successfully',
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
                                        window.targetDeletionProgress.startProgressTracking(response.job_id);
                                    }
                                } else {
                                    // Synchronous deletion - show success and reload
                                    Swal.fire('Deleted!', response.message || 'Target has been deleted.', 'success');
                                    setTimeout(() => window.location.reload(), 1500);
                                }
                            },
                            error: function(xhr) {
                                Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to delete target.', 'error');
                            }
                        });
                    }
                });
            });
    
            // Select2 for panchayat search
      $('#panchayatSearch').select2({
        placeholder: "Select a Panchayat",
        allowClear: true,
        dropdownParent: $('#addTargetModal'),
        ajax: {
                    url: "{{ route('streetlights.search') }}",
          dataType: 'json',
          method: "GET",
          delay: 250,
          data: function(params) {
            return {
              search: params.term
            };
          },
          processResults: function(data) {
            return {
              results: data.map(item => ({
                id: item.id,
                text: item.text
              }))
            };
          }
        }
      });

      // Fetch Blocks Based on Selected District
      $('#districtSearch').change(function() {
        let district = $(this).val();
                $('#blockSearch').prop('disabled', false).empty().append(
                    '<option value="">Select a Block</option>');
        $('#panchayatSearch').prop('disabled', true).empty().append(
          '<option value="">Select a Panchayat</option>');

        if (district) {
          $.ajax({
            url: '/blocks-by-district/' + district,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
              $.each(data, function(index, block) {
                                $('#blockSearch').append('<option value="' + block +
                                    '">' + block + '</option>');
              });
            },
            error: function(xhr, status, error) {
              console.error("AJAX Error:", status, error);
            }
          });
        }
      });

      // Fetch Panchayats Based on Selected Block
      $('#blockSearch').change(function() {
        let block = $(this).val();
        $('#panchayatSearch').prop('disabled', false).empty().append(
          '<option value="">Select a Panchayat</option>');

                if (block) {
          $.ajax({
                        url: '/panchayats-by-block/' + block,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
              $.each(data, function(index, panchayat) {
                                $('#panchayatSearch').append('<option value="' +
                                    panchayat.panchayat + '">' + panchayat
                                    .panchayat + '</option>');
              });
            },
            error: function(xhr, status, error) {
              console.error("AJAX Error:", status, error);
            }
          });
        }
      });
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
        if (typeof window.targetDeletionProgress === 'undefined' && typeof TargetDeletionProgress !== 'undefined') {
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
                                    window.targetDeletionProgress = new TargetDeletionProgress();
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
                                    console.log('Response has job_id:', response && response.job_id);
                                    console.log('Response keys:', response ? Object.keys(response) : 'null');
                                    
                                    // Check if async deletion was initiated
                                    if (response && response.job_id) {
                                        // Async deletion - start progress tracking with real job_id
                                        console.log('Starting progress tracking for job:', response.job_id);
                                        
                                        if (window.targetDeletionProgress) {
                                            try {
                                                // Update modal with real job_id and start polling
                                                window.targetDeletionProgress.currentJobId = response.job_id;
                                                localStorage.setItem('target_deletion_job_id', response.job_id);
                                                window.targetDeletionProgress.startPolling(response.job_id);
                                                console.log('Progress tracking started successfully');
                                            } catch (error) {
                                                console.error('Error starting progress tracking:', error);
                                                console.error('Error stack:', error.stack);
                                                window.targetDeletionProgress.updateProgressModal({
                                                    status: 'failed',
                                                    error_message: 'Progress tracking failed: ' + error.message
                                                });
                                            }
                                        }
                                    } else {
                                        // Synchronous deletion - update modal and reload
                                        console.log('No job_id in response, treating as synchronous deletion');
                                        console.log('Response:', JSON.stringify(response));
                                        
                                        if (window.targetDeletionProgress) {
                                            window.targetDeletionProgress.updateProgressModal({
                                                progress_percentage: 100,
                                                status: 'completed',
                                                message: response.message || 'Targets deleted successfully.'
                                            });
                                            
                                            setTimeout(() => {
                                                const modal = document.getElementById('targetDeletionProgressModal');
                                                if (modal) {
                                                    const bsModal = bootstrap.Modal.getInstance(modal);
                                                    if (bsModal) {
                                                        bsModal.hide();
                                                    }
                                                }
                                                window.location.reload();
                                            }, 1500);
                                        } else {
                                            Swal.fire('Deleted!', response.message || 'Targets deleted successfully.', 'success');
                                            setTimeout(() => window.location.reload(), 1500);
                                        }
                                    }
                                },
                                error: function(xhr) {
                                    console.error('Bulk delete error:', xhr);
                                    
                                    // Update modal with error
                                    if (window.targetDeletionProgress) {
                                        const errorMsg = xhr.responseJSON?.message || xhr.responseJSON?.error || 'Failed to delete targets.';
                                        window.targetDeletionProgress.updateProgressModal({
                                            status: 'failed',
                                            error_message: errorMsg
                                        });
                                    } else {
                                        Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to delete targets.', 'error');
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
        .filter-select2 + .select2-container .select2-selection--single {
            height: 31px !important;
            min-height: 31px !important;
            line-height: 29px !important;
        }
        
        .filter-select2 + .select2-container .select2-selection__rendered {
            line-height: 29px !important;
            padding-left: 8px !important;
            padding-right: 20px !important;
        }
        
        .filter-select2 + .select2-container .select2-selection__arrow {
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
    </style>
@endpush
