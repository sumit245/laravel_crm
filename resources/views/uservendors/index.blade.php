@extends('layouts.main')

@section('content')
    <div class="container p-2">
        <x-datatable id="vendorsTable" title="Vendor Management" :columns="[
            ['title' => '#', 'width' => '4%'],
            ['title' => 'Name', 'width' => '12%'],
            ['title' => 'Email', 'width' => '18%'],
            ['title' => 'First Name', 'width' => '10%'],
            ['title' => 'Last Name', 'width' => '10%'],
            ['title' => 'Address', 'width' => '20%'],
            ['title' => 'Phone', 'width' => '10%'],
            ['title' => 'Project', 'width' => '0%', 'orderable' => false, 'searchable' => true],
            ['title' => 'District', 'width' => '0%', 'orderable' => false, 'searchable' => true],
            ['title' => 'Manager', 'width' => '0%', 'orderable' => false, 'searchable' => true],
        ]" :addRoute="route('uservendors.create')"
            addButtonText="Add New Vendor" :exportEnabled="true" :importEnabled="true" :importRoute="route('import.vendors')" :importFormatUrl="route('vendors.importFormat')"
            :bulkDeleteEnabled="true" :bulkDeleteRoute="route('uservendors.bulkDelete')" :deleteRoute="route('uservendors.destroy', ':id')" :editRoute="route('uservendors.edit', ':id')" :viewRoute="route('uservendors.show', ':id')" pageLength="50"
            searchPlaceholder="Search Vendors..." :order="[[1, 'desc']]" :filters="[
                [
                    'type' => 'select',
                    'name' => 'project',
                    'label' => 'Project',
                    'column' => 7,
                    'width' => 3,
                    'options' => $projects->pluck('project_name', 'project_name')->toArray(),
                ],
                [
                    'type' => 'select',
                    'name' => 'district',
                    'label' => 'District',
                    'column' => 8,
                    'width' => 3,
                    'options' => $districts->pluck('name', 'name')->toArray(),
                ],
                [
                    'type' => 'select',
                    'name' => 'manager',
                    'label' => 'Manager',
                    'column' => 9,
                    'width' => 3,
                    'options' => $managers->pluck('name', 'name')->toArray(),
                ],
            ]">
            @foreach ($vendors as $member)
                @php
                    $projectNames =
                        $member->projects->pluck('project_name')->filter()->join(', ') ?:
                        ($member->project_id
                            ? ($member->project
                                ? $member->project->project_name
                                : '')
                            : '');
                    $managerName = $member->projectManager ? $member->projectManager->name : '';
                    // Get districts from vendor's projects
$districtNames = collect();
foreach ($member->projects as $project) {
    if ($project->project_type == 1) {
        // Streetlight project - get districts from streetlights
        $districts = \App\Models\Streetlight::where('project_id', $project->id)
            ->whereNotNull('district')
            ->distinct()
            ->pluck('district');
        $districtNames = $districtNames->merge($districts);
    } else {
        // Rooftop project - get districts from sites
        $districts = \App\Models\Site::where('project_id', $project->id)
            ->whereNotNull('district')
            ->with('districtRelation')
            ->get()
            ->map(function ($site) {
                return $site->districtRelation ? $site->districtRelation->name : null;
            })
            ->filter();
        $districtNames = $districtNames->merge($districts);
    }
}
$districtNames = $districtNames->unique()->filter()->join(', ');
                @endphp
                <tr>
                    <td>
                        <input type="checkbox" class="row-checkbox" value="{{ $member->id }}">
                    </td>
                    <td>{{ $member->id }}</td>
                    <td>{{ $member->name }}</td>
                    <td>{{ $member->email }}</td>
                    <td>{{ $member->firstName }}</td>
                    <td>{{ $member->lastName }}</td>
                    <td class="address-cell" title="{{ $member->address ?? '-' }}">
                        <span class="address-text">{{ $member->address ?? '-' }}</span>
                    </td>
                    <td>{{ $member->contactNo ?? '-' }}</td>
                    <td>{{ $projectNames }}</td>
                    <td>{{ $districtNames }}</td>
                    <td>{{ $managerName }}</td>
                    <td class="text-center">
                        <a href="{{ route('uservendors.show', $member->id) }}" class="btn btn-icon btn-info"
                            data-toggle="tooltip" title="View Details">
                            <i class="mdi mdi-eye"></i>
                        </a>
                        <a href="{{ route('uservendors.edit', $member->id) }}" class="btn btn-icon btn-warning"
                            data-toggle="tooltip" title="Edit Vendor">
                            <i class="mdi mdi-pencil"></i>
                        </a>
                        <button type="button" class="btn btn-icon btn-danger delete-row" data-toggle="tooltip"
                            title="Delete Vendor" data-id="{{ $member->id }}" data-name="{{ $member->name }}"
                            data-url="{{ route('uservendors.destroy', $member->id) }}">
                            <i class="mdi mdi-delete"></i>
                        </button>
                    </td>
                </tr>
            @endforeach
        </x-datatable>
    </div>

    @if (session()->has('success'))
        <script>
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: {!! json_encode(session('success')) !!},
                showConfirmButton: false,
                timer: 4000,
                timerProgressBar: true
            });
        </script>
    @endif

    @if (session()->has('error'))
        <script>
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'error',
                title: {!! json_encode(session('error')) !!},
                showConfirmButton: false,
                timer: 5000,
                timerProgressBar: true
            });
        </script>
    @endif

    @if (session()->has('import_errors'))
        <script>
            const importErrors = {!! json_encode(session('import_errors')) !!};
            const maxShow = 10;
            const shortList = Array.isArray(importErrors) ? importErrors.slice(0, maxShow) : [importErrors];
            Swal.fire({
                title: 'Import completed with errors',
                icon: 'warning',
                html: shortList.join('<br>') + (importErrors.length > maxShow ? '<br><em>...more errors omitted</em>' :
                    ''),
                confirmButtonText: 'OK',
                width: '600px'
            });
        </script>
    @endif

    @if ($errors->any())
        <script>
            const validationErrors = {!! json_encode($errors->all()) !!};
            Swal.fire({
                title: 'Validation errors',
                icon: 'error',
                html: validationErrors.join('<br>'),
                confirmButtonText: 'OK',
                width: '600px'
            });
        </script>
    @endif

    <!-- Bulk Project Assignment Modal -->
    <div class="modal fade" id="bulkAssignProjectsModal" tabindex="-1" aria-labelledby="bulkAssignProjectsModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bulkAssignProjectsModalLabel">Assign Projects to Vendors</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info mb-3">
                        <i class="mdi mdi-information"></i>
                        <span id="selectedVendorsCount">0</span> vendor(s) selected
                    </div>

                    <div class="form-group mb-3">
                        <label for="project_ids" class="form-label">Select Projects <span
                                class="text-danger">*</span></label>
                        <select class="form-control" id="project_ids" name="project_ids[]" multiple required>
                            @foreach ($projects as $project)
                                <option value="{{ $project->id }}">{{ $project->project_name }}</option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Select one or more projects</small>
                    </div>

                    <div class="form-group">
                        <label class="form-label mb-2">Assignment Mode <span class="text-danger">*</span></label>
                        <div class="d-flex flex-column gap-2">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="assignment_mode" id="mode_add"
                                    value="add" checked>
                                <label class="form-check-label" for="mode_add">
                                    <strong>Add Projects</strong> - Add selected projects to existing assignments (vendor
                                    will work on all projects)
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="assignment_mode" id="mode_replace"
                                    value="replace">
                                <label class="form-check-label" for="mode_replace">
                                    <strong>Replace Projects</strong> - Remove all existing project assignments and assign
                                    only selected projects
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmAssignProjects">
                        <i class="mdi mdi-check"></i> Assign Projects
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            /* Override datatable component's global white-space for address column */
            #vendorsTable tbody td.address-cell {
                white-space: normal !important;
                overflow: visible !important;
                max-width: 200px !important;
                min-width: 150px !important;
            }

            #vendorsTable tbody td.address-cell .address-text {
                display: block;
                max-width: 100%;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            /* Fix actions column padding - reduce horizontal padding */
            #vendorsTable tbody td:last-child {
                padding-left: 4px !important;
                padding-right: 4px !important;
            }

            #vendorsTable tbody td:last-child .btn-icon {
                padding: 4px 6px !important;
                margin: 0 1px !important;
            }

            /* Address column (7th column) - ensure it's visible and truncates */
            #vendorsTable thead th:nth-child(7),
            #vendorsTable tbody td:nth-child(7).address-cell {
                width: 200px !important;
                min-width: 150px !important;
                max-width: 250px !important;
            }

            /* Adjust other column widths to balance */
            /* ID column (2nd) */
            #vendorsTable thead th:nth-child(2),
            #vendorsTable tbody td:nth-child(2) {
                width: 50px !important;
                min-width: 50px !important;
            }

            /* Name column (3rd) */
            #vendorsTable thead th:nth-child(3),
            #vendorsTable tbody td:nth-child(3) {
                width: 120px !important;
                min-width: 100px !important;
            }

            /* Email column (4th) */
            #vendorsTable thead th:nth-child(4),
            #vendorsTable tbody td:nth-child(4) {
                width: 180px !important;
                min-width: 150px !important;
            }

            /* First Name column (5th) */
            #vendorsTable thead th:nth-child(5),
            #vendorsTable tbody td:nth-child(5) {
                width: 100px !important;
                min-width: 80px !important;
            }

            /* Last Name column (6th) */
            #vendorsTable thead th:nth-child(6),
            #vendorsTable tbody td:nth-child(6) {
                width: 100px !important;
                min-width: 80px !important;
            }

            /* Phone column (8th) */
            #vendorsTable thead th:nth-child(8),
            #vendorsTable tbody td:nth-child(8) {
                width: 100px !important;
                min-width: 80px !important;
            }

            /* Ensure download format link is visible */
            .download-format-link {
                display: inline-flex !important;
                align-items: center;
                gap: 4px;
                color: #007bff;
                text-decoration: none;
                font-size: 0.875rem;
                margin-top: 4px;
            }

            .download-format-link:hover {
                color: #0056b3;
                text-decoration: underline;
            }

            /* Ensure bulk actions buttons stack vertically */
            #bulkActions .alert {
                flex-direction: column !important;
            }

            #bulkActions .alert>div:last-child {
                width: 100%;
                display: flex;
                flex-direction: column;
                gap: 8px;
            }

            #bulkActions .alert button {
                width: 100%;
            }

            /* Modal styling improvements */
            #bulkAssignProjectsModal .form-check {
                padding: 0.5rem;
                border: 1px solid #dee2e6;
                border-radius: 0.25rem;
                margin-bottom: 0.5rem;
            }

            #bulkAssignProjectsModal .form-check:last-child {
                margin-bottom: 0;
            }

            #bulkAssignProjectsModal .form-check-input {
                margin-top: 0.4rem;
            }

            #bulkAssignProjectsModal .form-check-label {
                margin-left: 0.5rem;
                cursor: pointer;
            }

            /* Select2 chips styling */
            .select2-container--default .select2-selection--multiple .select2-selection__choice {
                background-color: #007bff;
                border: 1px solid #007bff;
                color: #fff;
                padding: 2px 8px;
                margin: 3px 3px 3px 0;
                border-radius: 3px;
            }

            .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
                color: #fff;
                margin-right: 5px;
            }

            .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
                color: #fff;
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            $(document).ready(function() {
                // Wait for datatable to initialize
                setTimeout(function() {
                    // Get reference to the DataTable instance
                    const vendorsTable = $('#vendorsTable').DataTable();

                    // Hide filter columns (project, district, manager) - columns 8, 9, 10 (0-indexed: 7, 8, 9)
                    vendorsTable.columns([7, 8, 9]).visible(false);

                    // Add "Assign Projects" button to bulk actions bar - stack vertically
                    if ($('#bulkActions').length && $('#bulkDeleteBtn').length) {
                        // Change bulk actions container to stack buttons vertically
                        const $bulkActionsContainer = $('#bulkActions .alert');
                        $bulkActionsContainer.removeClass('flex-sm-row').addClass('flex-column');

                        // Create button container for vertical stacking
                        const $buttonContainer = $(
                            '<div class="d-flex flex-column gap-2 w-100 w-sm-auto"></div>');

                        // Move delete button into container
                        const $deleteBtn = $('#bulkDeleteBtn').detach();
                        $buttonContainer.append($deleteBtn);

                        // Create and add assign projects button
                        let assignProjectsBtn = $(
                            '<button type="button" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1 w-100" id="assignProjectsBtn" style="display: none;"><i class="mdi mdi-link-variant"></i><span>Assign Projects</span></button>'
                            );
                        $buttonContainer.append(assignProjectsBtn);

                        // Add button container to bulk actions
                        $bulkActionsContainer.append($buttonContainer);
                    }
                }, 500);

                // Show/hide assign projects button based on selection
                function updateAssignProjectsButton() {
                    const checkedCount = $('#vendorsTable tbody .row-checkbox:checked').length;
                    if (checkedCount > 0 && $('#assignProjectsBtn').length) {
                        $('#assignProjectsBtn').show();
                    } else if ($('#assignProjectsBtn').length) {
                        $('#assignProjectsBtn').hide();
                    }
                }

                // Update button visibility when checkboxes change (use event delegation)
                $(document).on('change', '#vendorsTable tbody .row-checkbox, #vendorsTable_selectAll', function() {
                    setTimeout(updateAssignProjectsButton, 100);
                });

                // Also check on table draw (when pagination changes) - use DataTable API
                if (typeof vendorsTable !== 'undefined' && vendorsTable && typeof vendorsTable.on === 'function') {
                    vendorsTable.on('draw', function() {
                        setTimeout(updateAssignProjectsButton, 100);
                    });
                } else if (typeof vendorsTable !== 'undefined' && vendorsTable) {
                    // Fallback: use jQuery on the table element
                    $('#vendorsTable').on('draw.dt', function() {
                        setTimeout(updateAssignProjectsButton, 100);
                    });
                }

                // Initialize Select2 for project selection when modal is shown
                $('#bulkAssignProjectsModal').on('shown.bs.modal', function() {
                    if (!$('#project_ids').hasClass('select2-hidden-accessible')) {
                        $('#project_ids').select2({
                            placeholder: 'Select projects...',
                            allowClear: true,
                            width: '100%',
                            dropdownParent: $('#bulkAssignProjectsModal'),
                            closeOnSelect: false
                        });
                    }
                });

                // Destroy Select2 when modal is hidden to prevent conflicts
                $('#bulkAssignProjectsModal').on('hidden.bs.modal', function() {
                    if ($('#project_ids').hasClass('select2-hidden-accessible')) {
                        $('#project_ids').select2('destroy');
                    }
                });

                // Show modal when assign projects button is clicked - use event delegation
                $(document).on('click', '#assignProjectsBtn', function() {
                    const selectedIds = [];
                    $('#vendorsTable tbody .row-checkbox:checked').each(function() {
                        selectedIds.push($(this).val());
                    });

                    if (selectedIds.length === 0) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'No Selection',
                            text: 'Please select at least one vendor.',
                            confirmButtonText: 'OK'
                        });
                        return;
                    }

                    $('#selectedVendorsCount').text(selectedIds.length);
                    const modal = new bootstrap.Modal(document.getElementById('bulkAssignProjectsModal'));
                    modal.show();
                });

                // Handle confirm assignment
                $('#confirmAssignProjects').on('click', function() {
                    const selectedVendorIds = [];
                    $('#vendorsTable tbody .row-checkbox:checked').each(function() {
                        selectedVendorIds.push($(this).val());
                    });

                    const projectIds = $('#project_ids').val();
                    const mode = $('input[name="assignment_mode"]:checked').val();

                    if (!projectIds || projectIds.length === 0) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'No Projects Selected',
                            text: 'Please select at least one project.',
                            confirmButtonText: 'OK'
                        });
                        return;
                    }

                    // Disable button during request
                    $(this).prop('disabled', true).html(
                        '<i class="mdi mdi-loading mdi-spin"></i> Processing...');

                    $.ajax({
                        url: '{{ route('uservendors.bulkAssignProjects') }}',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            vendor_ids: selectedVendorIds,
                            project_ids: projectIds,
                            mode: mode
                        },
                        success: function(response) {
                            const modal = bootstrap.Modal.getInstance(document.getElementById(
                                'bulkAssignProjectsModal'));
                            if (modal) {
                                modal.hide();
                            }
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: response.message,
                                timer: 3000,
                                showConfirmButton: false
                            });

                            // Reload page after short delay
                            setTimeout(function() {
                                window.location.reload();
                            }, 1500);
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: xhr.responseJSON?.message ||
                                    'Failed to assign projects. Please try again.',
                                confirmButtonText: 'OK'
                            });
                        },
                        complete: function() {
                            $('#confirmAssignProjects').prop('disabled', false).html(
                                '<i class="mdi mdi-check"></i> Assign Projects');
                        }
                    });
                });
            });
        </script>
    @endpush
@endsection
