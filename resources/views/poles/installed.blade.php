@extends('layouts.main')

@section('content')
    <div class="content-wrapper p-2">
        <div class="row mb-3 align-items-center">
            <div class="col-12 col-lg">
                <h3 class="fw-bold mb-1">Installed Poles</h3>
                <p class="text-muted mb-0">
                    <strong>{{ number_format($totalInstalled) }}</strong> installed pole{{ $totalInstalled === 1 ? '' : 's' }}
                    @if (!empty($activeFilters['district']) || !empty($activeFilters['block']) || !empty($activeFilters['panchayat']))
                        <span class="ms-2">
                            @if (!empty($activeFilters['district']))
                                <span class="badge bg-secondary">District: {{ $activeFilters['district'] }}</span>
                            @endif
                            @if (!empty($activeFilters['block']))
                                <span class="badge bg-secondary">Block: {{ $activeFilters['block'] }}</span>
                            @endif
                            @if (!empty($activeFilters['panchayat']))
                                <span class="badge bg-secondary">Panchayat: {{ $activeFilters['panchayat'] }}</span>
                            @endif
                        </span>
                    @endif
                </p>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <h6 class="card-title mb-3">Filter by location</h6>
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label for="installedDistrict" class="form-label">District</label>
                        <select id="installedDistrict" class="form-select">
                            <option value="">All districts</option>
                            @foreach ($districts as $districtRow)
                                <option value="{{ $districtRow->district }}"
                                    @selected(request('district') === $districtRow->district)>
                                    {{ $districtRow->district }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="installedBlock" class="form-label">Block</label>
                        <select id="installedBlock" class="form-select" disabled>
                            <option value="">All blocks</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="installedPanchayat" class="form-label">Panchayat</label>
                        <select id="installedPanchayat" class="form-select" disabled>
                            <option value="">All panchayats</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-primary" id="installedGeoApplyBtn">
                            <i class="mdi mdi-filter"></i> Apply
                        </button>
                        <a href="{{ route('installed.poles') }}" class="btn btn-outline-secondary">
                            Clear
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <x-datatable id="installedPole" 
            title="Installed Poles" 
            :columns="[
                ['title' => 'Pole Number', 'width' => '10%'],
                ['title' => 'Beneficiary', 'width' => '9%'],
                ['title' => 'Beneficiary Contact', 'width' => '9%'],
                ['title' => 'District', 'width' => '8%', 'orderable' => false, 'searchable' => false],
                ['title' => 'Block', 'width' => '8%', 'orderable' => false, 'searchable' => false],
                ['title' => 'Panchayat', 'width' => '9%', 'orderable' => false, 'searchable' => false],
                ['title' => 'IMEI', 'width' => '8%'],
                ['title' => 'SIM Number', 'width' => '8%'],
                ['title' => 'Battery', 'width' => '8%'],
                ['title' => 'Panel', 'width' => '8%'],
                ['title' => 'Bill Raised', 'width' => '7%'],
                ['title' => 'RMS Status', 'width' => '8%'],
            ]" 
            :exportEnabled="true" 
            :importEnabled="false" 
            :bulkDeleteEnabled="true"
            :bulkDeleteRoute="route('poles.bulkDelete')"
            :serverSide="true"
            :ajaxUrl="$installedPolesAjaxUrl"
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
            ]"></x-datatable>

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

    const installedPolesBaseUrl = @json(route('installed.poles'));
    const installedActiveFilters = @json($activeFilters ?? []);
    const installedProjectId = @json(request('project_id'));

    function installedJicrQuerySuffix() {
        const params = new URLSearchParams();
        if (installedProjectId) {
            params.set('project_id', installedProjectId);
        }
        const qs = params.toString();
        return qs ? '?' + qs : '';
    }

    function resetInstalledBlockSelect() {
        $('#installedBlock').prop('disabled', true).empty().append('<option value="">All blocks</option>');
    }

    function resetInstalledPanchayatSelect() {
        $('#installedPanchayat').prop('disabled', true).empty().append('<option value="">All panchayats</option>');
    }

    function loadInstalledBlocks(district, selectedBlock) {
        return $.ajax({
            url: '/jicr/blocks/' + encodeURIComponent(district) + installedJicrQuerySuffix(),
            type: 'GET',
            dataType: 'json',
        }).then(function (response) {
            resetInstalledBlockSelect();
            $('#installedBlock').prop('disabled', false);
            $.each(response.blocks || [], function (index, item) {
                const val = item.block;
                const selected = selectedBlock && selectedBlock === val ? ' selected' : '';
                $('#installedBlock').append('<option value="' + val + '"' + selected + '>' + val + '</option>');
            });
        });
    }

    function loadInstalledPanchayats(block, district, selectedPanchayat) {
        const params = new URLSearchParams(installedJicrQuerySuffix().replace(/^\?/, ''));
        if (district) {
            params.set('district', district);
        }
        const qs = params.toString();
        const suffix = qs ? '?' + qs : '';

        return $.ajax({
            url: '/jicr/panchayats/' + encodeURIComponent(block) + suffix,
            type: 'GET',
            dataType: 'json',
        }).then(function (response) {
            resetInstalledPanchayatSelect();
            $('#installedPanchayat').prop('disabled', false);
            $.each(response.panchayats || [], function (index, item) {
                const val = item.panchayat;
                const selected = selectedPanchayat && selectedPanchayat === val ? ' selected' : '';
                $('#installedPanchayat').append('<option value="' + val + '"' + selected + '>' + val + '</option>');
            });
        });
    }

    function applyInstalledGeoFilters() {
        const params = new URLSearchParams(window.location.search);
        const district = $('#installedDistrict').val();
        const block = $('#installedBlock').val();
        const panchayat = $('#installedPanchayat').val();

        if (district) {
            params.set('district', district);
        } else {
            params.delete('district');
        }
        if (block) {
            params.set('block', block);
        } else {
            params.delete('block');
        }
        if (panchayat) {
            params.set('panchayat', panchayat);
        } else {
            params.delete('panchayat');
        }

        const qs = params.toString();
        window.location.href = installedPolesBaseUrl + (qs ? '?' + qs : '');
    }

    $(document).ready(function() {
        $('#installedDistrict').on('change', function () {
            const district = $(this).val();
            resetInstalledBlockSelect();
            resetInstalledPanchayatSelect();
            if (district) {
                loadInstalledBlocks(district, null);
            }
        });

        $('#installedBlock').on('change', function () {
            const block = $(this).val();
            const district = $('#installedDistrict').val();
            resetInstalledPanchayatSelect();
            if (block) {
                loadInstalledPanchayats(block, district, null);
            }
        });

        $('#installedGeoApplyBtn').on('click', function () {
            applyInstalledGeoFilters();
        });

        if (installedActiveFilters.district) {
            loadInstalledBlocks(installedActiveFilters.district, installedActiveFilters.block || null)
                .then(function () {
                    if (installedActiveFilters.block) {
                        return loadInstalledPanchayats(
                            installedActiveFilters.block,
                            installedActiveFilters.district,
                            installedActiveFilters.panchayat || null
                        );
                    }
                })
                .fail(function (xhr, status, error) {
                    console.error('Failed to restore location filters:', status, error);
                });
        }

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
