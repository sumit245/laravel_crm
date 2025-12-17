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
                ['title' => '#', 'width' => '5%'],
                ['title' => 'District', 'width' => '10%'],
                ['title' => 'Block', 'width' => '10%'],
                ['title' => 'Panchayat', 'width' => '12%'],
                ['title' => 'Pole Number', 'width' => '12%'],
                ['title' => 'IMEI', 'width' => '10%'],
                ['title' => 'Sim Number', 'width' => '10%'],
                ['title' => 'Battery', 'width' => '10%'],
                ['title' => 'Panel', 'width' => '10%'],
                ['title' => 'Bill Raised', 'width' => '8%'],
                ['title' => 'RMS', 'width' => '8%'],
            ]" 
            :exportEnabled="true" 
            :importEnabled="false" 
            :bulkDeleteEnabled="false"
            pageLength="50" 
            searchPlaceholder="Search Poles..."
            :filters="[
                [
                    'type' => 'select',
                    'name' => 'filter_surveyed',
                    'label' => 'Surveyed',
                    'column' => -1,
                    'width' => 3,
                    'options' => [
                        '' => 'All',
                        '1' => 'Yes',
                        '0' => 'No',
                    ],
                ],
                [
                    'type' => 'select',
                    'name' => 'filter_installed',
                    'label' => 'Installed',
                    'column' => -1,
                    'width' => 3,
                    'options' => [
                        '' => 'All',
                        '1' => 'Yes',
                        '0' => 'No',
                    ],
                ],
                [
                    'type' => 'select',
                    'name' => 'filter_billed',
                    'label' => 'Billed',
                    'column' => -1,
                    'width' => 3,
                    'options' => [
                        '' => 'All',
                        '1' => 'Yes',
                        '0' => 'No',
                    ],
                ],
            ]">
            @foreach ($poles as $pole)
            <tr data-surveyed="{{ $pole->isSurveyDone ? '1' : '0' }}" 
                data-installed="{{ $pole->isInstallationDone ? '1' : '0' }}" 
                data-billed="{{ $pole->task && $pole->task->billed ? '1' : '0' }}">
                <td>{{ $loop->iteration }}</td>
                <td>{{ $pole->task?->streetlight?->district ?? 'N/A' }}</td>
                <td>{{ $pole->task?->streetlight?->block ?? 'N/A' }}</td>
                <td>{{ $pole->task?->streetlight?->panchayat ?? 'N/A' }}</td>
                <td>
                    @if ($pole->lat && $pole->lng)
                        <span class="text-primary" style="cursor:pointer;" onclick="locateOnMap({{ $pole->lat }}, {{ $pole->lng }})">
                            {{ $pole->complete_pole_number ?? 'N/A' }}
                        </span>
                    @else
                        {{ $pole->complete_pole_number ?? 'N/A' }}
                    @endif
                </td>
                <td>{{ $pole->luminary_qr ?? 'N/A' }}</td>
                <td>{{ $pole->sim_number ?? 'N/A' }}</td>
                <td>{{ $pole->battery_qr ?? 'N/A' }}</td>
                <td>{{ $pole->panel_qr ?? 'N/A' }}</td>
                <td>
                    @if ($pole->task && $pole->task->billed)
                        <span class="badge badge-success">Yes</span>
                    @else
                        <span class="badge badge-secondary">No</span>
                    @endif
                </td>
                <td>{{ $pole->rms_status ?? 'N/A' }}</td>
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
    </div>
@endsection

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
        // Custom filter functions for data-attribute based filtering
        let customFilterFunctions = [];

        // Intercept applyFilters button click
        $(document).on('click', '#applyFilters', function(e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            applyCustomFilters();
        });

        // Intercept clearFilters button click
        $(document).on('click', '#clearFilters', function(e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            $('.filter-select, .filter-date, .filter-text').val('');
            customFilterFunctions = [];
            $.fn.dataTable.ext.search = [];
            const table = $('#installedPole').DataTable();
            if (table) {
                table.search('').columns().search('').draw();
            }
        });

        function applyCustomFilters() {
            const table = $('#installedPole').DataTable();
            if (!table) return;

            // Clear previous custom filters
            customFilterFunctions = [];

            // Get filter values
            const surveyedFilter = $('.filter-select[data-filter="filter_surveyed"]').val();
            const installedFilter = $('.filter-select[data-filter="filter_installed"]').val();
            const billedFilter = $('.filter-select[data-filter="filter_billed"]').val();

            // Create custom filter functions based on data attributes
            if (surveyedFilter !== '') {
                customFilterFunctions.push(function(settings, data, dataIndex) {
                    if (settings.nTable.id !== 'installedPole') return true;
                    const $row = $(table.row(dataIndex).node());
                    const rowSurveyed = $row.data('surveyed') || '0';
                    return rowSurveyed === surveyedFilter;
                });
            }

            if (installedFilter !== '') {
                customFilterFunctions.push(function(settings, data, dataIndex) {
                    if (settings.nTable.id !== 'installedPole') return true;
                    const $row = $(table.row(dataIndex).node());
                    const rowInstalled = $row.data('installed') || '0';
                    return rowInstalled === installedFilter;
                });
            }

            if (billedFilter !== '') {
                customFilterFunctions.push(function(settings, data, dataIndex) {
                    if (settings.nTable.id !== 'installedPole') return true;
                    const $row = $(table.row(dataIndex).node());
                    const rowBilled = $row.data('billed') || '0';
                    return rowBilled === billedFilter;
                });
            }

            // Apply custom filters
            $.fn.dataTable.ext.search = customFilterFunctions;
            table.draw();
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

        // Wait for DataTable to initialize
        setTimeout(function() {
            const table = $('#installedPole').DataTable();
            if (table) {
                initializeDeleteButtons();
                
                // Reinitialize on draw
                table.on('draw', function() {
                    initializeDeleteButtons();
                });
            }
        }, 500);
    });
</script>
@endpush
