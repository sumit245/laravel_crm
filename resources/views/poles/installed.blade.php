@extends('layouts.main')

@section('content')
    <div class="container-fluid p-3">
        <h3 class="fw-bold mt-2">Installed Lights</h3>

        <x-data-table id="installedPole" class="table-striped table">
            <x-slot:thead>
                <tr>
                    <th data-select="true">
                        <input type="checkbox" id="selectAll" />
                    </th>
                    <th>Block</th>
                    <th>Panchayat</th>
                    <th>Pole Number</th>
                    <th>IMEI</th>
                    <th>Sim Number</th>
                    <th>Battery</th>
                    <th>Panel</th>
                    <th>Bill Raised</th>
                    <th>RMS</th>
                    <th>Actions</th>
                </tr>
            </x-slot:thead>
            <x-slot:tbody>
                {{-- Data will be loaded via AJAX --}}
            </x-slot:tbody>
        </x-data-table>
    </div>
@endsection

@push('scripts')
    <script>
        function locateOnMap(lat, lng) {
            if (lat && lng) {
                // Using a more standard Google Maps URL
                const url = `https://www.google.com/maps?q=${lat},${lng}`;
                window.open(url, '_blank');
            } else {
                alert('Location coordinates not available.');
            }
        }

        $(document).ready(function() {
            // Destroy existing DataTable if it exists
            if ($.fn.DataTable.isDataTable('#installedPole')) {
                $('#installedPole').DataTable().destroy();
            }

            // Get filter parameters from URL
            const urlParams = new URLSearchParams(window.location.search);
            const projectId = urlParams.get('project_id');
            const projectManager = urlParams.get('project_manager');
            const siteEngineer = urlParams.get('site_engineer');
            const vendor = urlParams.get('vendor');

            $('#installedPole').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('installed.poles.data') }}',
                    dataSrc: 'data',
                    data: function(d) {
                        d.project_id = projectId;
                        d.project_manager = projectManager;
                        d.site_engineer = siteEngineer;
                        d.vendor = vendor;
                    }
                },
                columns: [{
                        data: 'checkbox',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'block'
                    },
                    {
                        data: 'panchayat'
                    },
                    {
                        data: 'pole_number'
                    },
                    {
                        data: 'imei'
                    },
                    {
                        data: 'sim_number'
                    },
                    {
                        data: 'battery'
                    },
                    {
                        data: 'panel'
                    },
                    {
                        data: 'bill_raised'
                    },
                    {
                        data: 'rms'
                    },
                    {
                        data: 'actions',
                        orderable: false,
                        searchable: false
                    }
                ],
                dom: "<'row'<'col-sm-6 d-flex align-items-center'f><'col-sm-6 d-flex justify-content-end'B>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row my-4'<'col-sm-5'i><'col-sm-7'p>>",
                buttons: [{
                        extend: 'excel',
                        text: '<i class="mdi mdi-file-excel text-light"></i>',
                        className: 'btn btn-icon btn-success',
                        titleAttr: 'Export to Excel',
                        // TODO: REFACTOR - User prefers Laravel MVC approach instead of heavy JavaScript
                        // This export function should be moved to a Laravel controller/route for better maintainability
                        // Current implementation uses client-side JavaScript with SheetJS library
                        // Preferred approach: Create a dedicated export route in TaskController that handles Excel generation server-side
                        action: function(e, dt, button, config) {
                            // For server-side processing, fetch all data
                            const urlParams = new URLSearchParams(window.location.search);
                            const params = {
                                project_id: urlParams.get('project_id'),
                                project_manager: urlParams.get('project_manager'),
                                site_engineer: urlParams.get('site_engineer'),
                                vendor: urlParams.get('vendor'),
                                length: -1, // Get all records
                                start: 0,
                                draw: 1
                            };

                            // Show loading indicator
                            const originalText = button.text();
                            button.prop('disabled', true).html(
                                '<i class="mdi mdi-loading mdi-spin"></i> Exporting...');

                            // Fetch all data
                            $.ajax({
                                url: '{{ route('installed.poles.data') }}',
                                data: params,
                                success: function(response) {
                                    // Prepare data for export (remove HTML tags)
                                    const exportData = response.data.map(function(row) {
                                        return {
                                            district: row.district || 'N/A',
                                            block: row.block || 'N/A',
                                            panchayat: row.panchayat || 'N/A',
                                            pole_number: $(row.pole_number)
                                                .text() || 'N/A',
                                            imei: row.imei || 'N/A',
                                            sim_number: row.sim_number || 'N/A',
                                            battery: row.battery || 'N/A',
                                            panel: row.panel || 'N/A',
                                            // bill_raised: row.bill_raised || '0',
                                            // rms: row.rms || 'N/A'
                                        };
                                    });

                                    // Use SheetJS (xlsx) library for proper Excel export
                                    function exportToExcel(data) {
                                        // Prepare worksheet data
                                        const headers = [
                                            ['District', 'Block', 'Panchayat',
                                                'Pole Number',
                                                'IMEI', 'Sim Number', 'Battery',
                                                'Panel'
                                            ]
                                        ];
                                        const rows = data.map(function(row) {
                                            return [
                                                row.district || 'N/A',
                                                row.block || 'N/A',
                                                row.panchayat || 'N/A',
                                                row.pole_number || 'N/A',
                                                row.imei || 'N/A',
                                                row.sim_number || 'N/A',
                                                row.battery || 'N/A',
                                                row.panel || 'N/A',
                                                // row.bill_raised || '0',
                                                // row.rms || 'N/A'
                                            ];
                                        });

                                        const wsData = headers.concat(rows);
                                        const ws = XLSX.utils.aoa_to_sheet(wsData);

                                        // Create workbook and add worksheet
                                        const wb = XLSX.utils.book_new();
                                        XLSX.utils.book_append_sheet(wb, ws,
                                            'Installed Poles');

                                        // Generate Excel file and download
                                        XLSX.writeFile(wb, 'installed_poles_' +
                                            new Date().getTime() + '.xlsx');

                                        // Restore button
                                        button.prop('disabled', false).html(
                                            originalText);
                                    }

                                    // Load SheetJS from CDN if not already loaded
                                    if (typeof XLSX === 'undefined') {
                                        const script = document.createElement('script');
                                        script.src =
                                            'https://cdn.sheetjs.com/xlsx-0.20.1/package/dist/xlsx.full.min.js';
                                        script.onload = function() {
                                            exportToExcel(exportData);
                                        };
                                        script.onerror = function() {
                                            alert(
                                                'Failed to load Excel export library. Please try again.'
                                            );
                                            button.prop('disabled', false).html(
                                                originalText);
                                        };
                                        document.head.appendChild(script);
                                    } else {
                                        exportToExcel(exportData);
                                    }

                                },
                                error: function() {
                                    alert('Error exporting data. Please try again.');
                                    button.prop('disabled', false).html(originalText);
                                }
                            });
                        }
                    },
                    {
                        extend: 'pdf',
                        text: '<i class="mdi mdi-file-pdf"></i>',
                        className: 'btn btn-icon btn-danger',
                        titleAttr: 'Export to PDF',
                        exportOptions: {
                            columns: ':not(.actions)',
                            modifier: {
                                page: 'all'
                            }
                        }
                    },
                    {
                        extend: 'print',
                        text: '<i class="mdi mdi-printer"></i>',
                        className: 'btn btn-icon btn-info',
                        titleAttr: 'Print Table',
                        exportOptions: {
                            columns: ':not(.actions)',
                            modifier: {
                                page: 'all'
                            }
                        }
                    }
                ],
                pageLength: 50,
                lengthMenu: [
                    [25, 50, 100, 500, 1000],
                    [25, 50, 100, 500, 1000]
                ],
                order: [
                    [3, 'asc']
                ], // Order by pole number
                language: {
                    search: '',
                    searchPlaceholder: 'Search...',
                    processing: '<div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div>'
                },
                drawCallback: function() {
                    // Reinitialize tooltips after each draw
                    $('[data-toggle="tooltip"]').tooltip();
                    // Reinitialize delete buttons
                    initializeDeleteButtons();
                }
            });

            $('.dataTables_filter input').addClass('form-control form-control-sm');
        });

        // Extract delete button logic into a function
        function initializeDeleteButtons() {
            // Remove old event handlers to prevent duplicates
            $('.delete-pole-btn').off('click');

            // Add new event handlers
            $('.delete-pole-btn').on('click', function() {
                // Get the data from the button
                let poleId = $(this).data('id');
                let poleName = $(this).data('name');
                let deleteUrl = $(this).data('url');

                // Show the confirmation dialog
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
                    // If the user confirms
                    if (result.isConfirmed) {
                        // We make the AJAX call to the delete route
                        $.ajax({
                            url: deleteUrl,
                            type: 'POST',
                            data: {
                                _method: 'DELETE', // Laravel's method spoofing
                                _token: "{{ csrf_token() }}",
                            },
                            success: function(response) {
                                Swal.fire(
                                    'Deleted!',
                                    `Pole "${poleName}" has been deleted.`,
                                    'success'
                                );
                                // Remove the table row with a fade-out effect for better UX
                                $(`.delete-pole-btn[data-id="${poleId}"]`).closest('tr')
                                    .fadeOut(500, function() {
                                        $(this).remove();
                                    });
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

        // Initialize delete buttons on page load
        $(document).ready(function() {
            initializeDeleteButtons();
        });
    </script>
@endpush

@push('styles')
    <style>
        #installedPole {
            margin-top: 15px;
        }
    </style>
@endpush
