@extends('layouts.main')

@section('content')
    <div class="content-wrapper p-2">
        <div class="row mb-3">
            <div class="col-12">
                <h3 class="fw-bold">Installed Poles</h3>
            </div>
        </div>

        <x-datatable id="installedPole" 
            title="Installed Poles" 
            :columns="[
                ['title' => 'Pole Number', 'width' => '12%'],
                ['title' => 'Beneficiary', 'width' => '12%'],
                ['title' => 'Beneficiary Contact', 'width' => '12%'],
                ['title' => 'IMEI', 'width' => '10%'],
                ['title' => 'SIM Number', 'width' => '10%'],
                ['title' => 'Battery', 'width' => '10%'],
                ['title' => 'Panel', 'width' => '10%'],
                ['title' => 'Bill Raised', 'width' => '8%'],
                ['title' => 'RMS Status', 'width' => '10%'],
            ]" 
            :exportEnabled="true" 
            :importEnabled="false" 
            :bulkDeleteEnabled="true"
            :bulkDeleteRoute="route('poles.bulkDelete')"
            pageLength="50" 
            searchPlaceholder="Search Poles..."
            :filters="[
                [
                    'type' => 'select',
                    'name' => 'filter_surveyed',
                    'label' => 'Survey Status',
                    'column' => -1,
                    'width' => 3,
                    'useDataAttribute' => 'surveyed',
                    'options' => [
                        '' => 'All',
                        '1' => 'Yes',
                        '0' => 'No',
                    ],
                ],
                [
                    'type' => 'select',
                    'name' => 'filter_installed',
                    'label' => 'Installation Status',
                    'column' => -1,
                    'width' => 3,
                    'useDataAttribute' => 'installed',
                    'options' => [
                        '' => 'All',
                        '1' => 'Yes',
                        '0' => 'No',
                    ],
                ],
                [
                    'type' => 'select',
                    'name' => 'filter_billed',
                    'label' => 'Bill Raised',
                    'column' => -1,
                    'width' => 3,
                    'useDataAttribute' => 'billed',
                    'options' => [
                        '' => 'All',
                        '1' => 'Yes',
                        '0' => 'No',
                    ],
                ],
                [
                    'type' => 'select',
                    'name' => 'filter_rms',
                    'label' => 'RMS Status',
                    'column' => -1,
                    'width' => 3,
                    'useDataAttribute' => 'rms-status',
                    'options' => [
                        '' => 'All',
                        'success' => 'Success',
                        'error' => 'Error',
                        'pending' => 'Pending',
                        'partial' => 'Partial',
                    ],
                ],
            ]">
            @foreach ($poles as $pole)
            @php
                // Calculate RMS status for this pole using eager-loaded logs
                $rmsLogs = $pole->rmsLogs ?? collect();
                $rmsSuccessCount = $rmsLogs->where('status', 'success')->count();
                $rmsErrorCount = $rmsLogs->where('status', 'error')->count();
                $rmsTotal = $rmsSuccessCount + $rmsErrorCount;
                $rmsStatus = $rmsTotal > 0 ? ($rmsErrorCount > 0 ? 'partial' : 'success') : 'pending';
            @endphp
            <tr data-surveyed="{{ $pole->isSurveyDone ? '1' : '0' }}" 
                data-installed="{{ $pole->isInstallationDone ? '1' : '0' }}" 
                data-billed="{{ $pole->task && $pole->task->billed ? '1' : '0' }}"
                data-rms-status="{{ $rmsStatus }}"
                data-rms-success="{{ $rmsSuccessCount }}"
                data-rms-error="{{ $rmsErrorCount }}"
                data-rms-total="{{ $rmsTotal }}"
                data-pole-id="{{ $pole->id }}">
                <td>
                    <input type="checkbox" class="row-checkbox" value="{{ $pole->id }}">
                </td>
                <td>
                    @if ($pole->lat && $pole->lng)
                        <span class="text-primary" style="cursor:pointer;" onclick="locateOnMap({{ $pole->lat }}, {{ $pole->lng }})">
                            {{ $pole->complete_pole_number ?? 'N/A' }}
                        </span>
                    @else
                        {{ $pole->complete_pole_number ?? 'N/A' }}
                    @endif
                </td>
                <td>{{ $pole->beneficiary ?? 'N/A' }}</td>
                <td>{{ $pole->beneficiary_contact ?? 'N/A' }}</td>
                <td>{{ $pole->luminary_qr ?? 'N/A' }}</td>
                <td>{{ $pole->sim_number ?? 'N/A' }}</td>
                <td>{{ $pole->battery_qr ?? 'N/A' }}</td>
                <td>{{ $pole->panel_qr ?? 'N/A' }}</td>
                <td>
                    @if ($pole->task && $pole->task->billed)
                        <span class="badge badge-success">Yes</span>
                    @else
                        <span class="badge badge-readable badge-no">No</span>
                    @endif
                </td>
                <td>
                    @if ($rmsTotal > 0)
                        <div class="rms-status-indicator" 
                             data-success="{{ $rmsSuccessCount }}" 
                             data-error="{{ $rmsErrorCount }}"
                             data-total="{{ $rmsTotal }}"
                             data-pole-id="{{ $pole->id }}"
                             style="cursor: pointer;"
                             title="Success: {{ $rmsSuccessCount }}, Errors: {{ $rmsErrorCount }} - Click to download report">
                            <div class="rms-progress-bar">
                                <div class="rms-success-bar" style="width: {{ $rmsTotal > 0 ? ($rmsSuccessCount / $rmsTotal * 100) : 0 }}%"></div>
                                <div class="rms-error-bar" style="width: {{ $rmsTotal > 0 ? ($rmsErrorCount / $rmsTotal * 100) : 0 }}%"></div>
                            </div>
                            <small class="rms-status-text">
                                {{ $rmsSuccessCount }}/{{ $rmsTotal }} Success
                            </small>
                        </div>
                    @else
                        <span class="badge badge-readable badge-not-pushed">Not Pushed</span>
                    @endif
                </td>
                <td class="text-center">
                    <a href="{{ route('poles.show', $pole->id) }}" class="btn btn-icon btn-info" data-toggle="tooltip" title="View Details">
                        <i class="mdi mdi-eye"></i>
                    </a>
                    <a href="{{ route('poles.edit', $pole->id) }}" class="btn btn-icon btn-warning" data-toggle="tooltip" title="Edit Pole">
                        <i class="mdi mdi-pencil"></i>
                    </a>
                    <button type="button" class="btn btn-icon btn-danger delete-pole-btn" data-toggle="tooltip"
                        title="Delete Pole" data-id="{{ $pole->id }}"
                        data-name="{{ $pole->complete_pole_number ?? 'this pole' }}"
                        data-url="{{ route('poles.destroy', $pole->id) }}">
                        <i class="mdi mdi-delete"></i>
                    </button>
                </td>
            </tr>
            @endforeach
        </x-datatable>

        {{-- Bulk Actions Bar --}}
        <div id="bulkActionsBar" class="mt-3 p-3 bg-light border rounded" style="display: none;">
            <div class="d-flex align-items-center gap-2">
                <span id="selectedCount" class="fw-bold">0</span> <span>poles selected</span>
                <button type="button" class="btn btn-sm btn-danger" id="bulkDeleteBtn">
                    <i class="mdi mdi-delete"></i> Delete Selected
                </button>
                <button type="button" class="btn btn-sm btn-warning" id="bulkPushRmsBtn">
                    <i class="mdi mdi-cloud-upload"></i> Push to RMS
                </button>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .rms-status-indicator {
        position: relative;
    }
    .rms-progress-bar {
        width: 100%;
        height: 20px;
        background-color: #e0e0e0;
        border-radius: 4px;
        overflow: hidden;
        position: relative;
        display: flex;
    }
    .rms-success-bar {
        background-color: #28a745;
        height: 100%;
        transition: width 0.3s;
    }
    .rms-error-bar {
        background-color: #dc3545;
        height: 100%;
        transition: width 0.3s;
    }
    .rms-status-text {
        display: block;
        margin-top: 4px;
        font-size: 0.85em;
    }
    
    /* Bill Raised and RMS Status badge styles for better readability */
    /* Custom readable badge classes to avoid global badge-light color issues */
    .badge.badge-readable {
        background-color: #e9ecef !important;
        color: #495057 !important;
        border: 1px solid #adb5bd !important;
        font-weight: 600 !important;
        padding: 0.35em 0.65em !important;
        display: inline-block !important;
    }
    
    .badge.badge-readable.badge-no,
    .badge.badge-readable.badge-not-pushed {
        background-color: #e9ecef !important;
        color: #495057 !important;
        border: 1px solid #adb5bd !important;
    }
    
    .badge.badge-success {
        font-weight: 600;
        padding: 0.35em 0.65em;
    }
</style>
@endpush

@push('scripts')
<script>
    function locateOnMap(lat, lng) {
        if (lat && lng) {
            const url = `https://www.google.com/maps?q=${lat},${lng}`;
            window.open(url, '_blank');
        } else {
            alert('Location coordinates not available.');
        }
    }

    $(document).ready(function() {
        // Filters are now handled by the datatable component itself
        // No need for custom filter code - the component uses useDataAttribute

        // Initialize delete buttons
        function initializeDeleteButtons() {
            $('.delete-pole-btn').off('click').on('click', function() {
                let poleId = $(this).data('id');
                let poleName = $(this).data('name');
                let deleteUrl = $(this).data('url');

                Swal.fire({
                    title: `Are you sure?`,
                    text: `You are about to delete pole "${poleName}". This action cannot be undone.`,
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
                                _method: 'DELETE',
                                _token: "{{ csrf_token() }}",
                            },
                            success: function(response) {
                                Swal.fire(
                                    'Deleted!',
                                    `Pole "${poleName}" has been deleted.`,
                                    'success'
                                );
                                setTimeout(function() {
                                    window.location.reload();
                                }, 1500);
                            },
                            error: function(xhr) {
                                Swal.fire(
                                    'Error!',
                                    'There was an error deleting the pole. Please try again.',
                                    'error'
                                );
                            }
                        });
                    }
                });
            });
        }

        // Bulk actions
        function updateBulkActionsBar() {
            const selectedCount = $('#installedPole tbody .row-checkbox:checked').length;
            if (selectedCount > 0) {
                $('#bulkActionsBar').show();
                $('#selectedCount').text(selectedCount);
            } else {
                $('#bulkActionsBar').hide();
            }
        }

        // Bulk delete
        $('#bulkDeleteBtn').on('click', function() {
            const selectedIds = [];
            $('#installedPole tbody .row-checkbox:checked').each(function() {
                selectedIds.push($(this).closest('tr').data('pole-id'));
            });

            if (selectedIds.length === 0) {
                Swal.fire({ icon: 'warning', title: 'No Selection', text: 'Please select at least one pole.', confirmButtonText: 'OK' });
                return;
            }

            Swal.fire({
                title: `Are you sure?`,
                text: `You are about to delete ${selectedIds.length} pole(s). This action cannot be undone.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete!',
                cancelButtonText: 'Cancel',
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('poles.bulkDelete') }}",
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}",
                            ids: selectedIds,
                        },
                        success: function(response) {
                            Swal.fire({ icon: 'success', title: 'Deleted!', text: response.message || `${selectedIds.length} pole(s) deleted.`, timer: 1500, showConfirmButton: false })
                                .then(() => window.location.reload());
                        },
                        error: function(xhr) {
                            Swal.fire({ icon: 'error', title: 'Error!', text: xhr.responseJSON?.message || 'Failed to delete poles.', confirmButtonText: 'OK' });
                        }
                    });
                }
            });
        });

        // Bulk push to RMS
        $('#bulkPushRmsBtn').on('click', function() {
            const selectedIds = [];
            $('#installedPole tbody .row-checkbox:checked').each(function() {
                selectedIds.push($(this).closest('tr').data('pole-id'));
            });

            if (selectedIds.length === 0) {
                Swal.fire({ icon: 'warning', title: 'No Selection', text: 'Please select at least one pole.', confirmButtonText: 'OK' });
                return;
            }

            Swal.fire({
                title: `Push to RMS?`,
                text: `Push ${selectedIds.length} pole(s) to RMS?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, push!',
                cancelButtonText: 'Cancel',
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading
                    Swal.fire({
                        title: 'Pushing to RMS...',
                        text: 'Please wait while we push the poles to RMS.',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: "{{ route('poles.bulkPushRms') }}",
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}",
                            pole_ids: selectedIds,
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Pushed!',
                                html: `Successfully pushed ${response.success_count || 0} pole(s).<br>Errors: ${response.error_count || 0}`,
                                confirmButtonText: 'OK'
                            }).then(() => window.location.reload());
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: xhr.responseJSON?.message || 'Failed to push poles to RMS.',
                                confirmButtonText: 'OK'
                            });
                        }
                    });
                }
            });
        });

        // RMS status indicator click handler
        $(document).on('click', '.rms-status-indicator', function() {
            const poleId = $(this).data('pole-id');
            // Download RMS report
            window.location.href = `{{ route('rms.export') }}?pole_id=${poleId}`;
        });

        // Update bulk actions bar on checkbox change
        $(document).on('change', '#installedPole tbody .row-checkbox, #installedPole_selectAll', function() {
            updateBulkActionsBar();
        });

        // Wait for DataTable to initialize
        setTimeout(function() {
            const table = $('#installedPole').DataTable();
            if (table) {
                initializeDeleteButtons();
                updateBulkActionsBar();
                
                // Reinitialize on draw
                table.on('draw', function() {
                    initializeDeleteButtons();
                    updateBulkActionsBar();
                });
            }
        }, 500);
    });
</script>
@endpush
