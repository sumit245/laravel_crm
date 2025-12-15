@props([
    'id' => 'dataTable',
    'columns' => [],
    'data' => [],
    'pageLength' => 25,
    'searchPlaceholder' => 'Search...',
    'exportEnabled' => true,
    'importEnabled' => false,
    'importRoute' => null,
    'importFormatUrl' => null,
    'bulkDeleteEnabled' => true,
    'bulkDeleteRoute' => null,
    'editRoute' => null,
    'deleteRoute' => null,
    'viewRoute' => null,
    'addRoute' => null,
    'addButtonText' => 'Add New',
    'title' => null,
    'filters' => [],
    'customActions' => [],
    'responsive' => true,
    'order' => [[0, 'desc']],
])

<div class="datatable-wrapper">
    {{-- Header Section: Import and Add Button on Same Line --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start mb-4 gap-3">
        @if ($importEnabled && $importRoute)
            <div class="import-section d-flex flex-column gap-2 w-100 w-md-auto">
                <form action="{{ $importRoute }}" method="POST" enctype="multipart/form-data"
                    class="import-form-group d-flex align-items-stretch">
                    @csrf
                    <div class="input-group input-group-sm import-input-wrapper">
                        <input type="file" name="file" class="form-control form-control-sm import-file-input"
                            accept=".xlsx,.xls,.csv" required>
                        <button type="submit"
                            class="btn btn-success import-submit-btn d-inline-flex align-items-center gap-1">
                            <i class="mdi mdi-upload"></i>
                            <span>Import</span>
                        </button>
                    </div>
                </form>
                @if ($importFormatUrl)
                    <a href="{{ $importFormatUrl }}" class="download-format-link" download>
                        <i class="mdi mdi-download"></i>
                        <span>Download Format</span>
                    </a>
                @endif
            </div>
        @else
            <div></div>
        @endif

        @if ($addRoute)
            <a href="{{ $addRoute }}"
                class="btn btn-primary btn-sm add-new-btn d-inline-flex align-items-center gap-2 align-self-start"
                data-toggle="tooltip" title="{{ $addButtonText }}">
                <i class="mdi mdi-plus-circle"></i>
                <span>{{ $addButtonText }}</span>
            </a>
        @endif
    </div>

    {{-- Filters Section: Simplified with Apply Button --}}
    @if (!empty($filters))
        <div class="mb-3 p-3 bg-light border rounded">
            <div class="row g-2 align-items-end">
                @foreach ($filters as $filter)
                    <div class="col-12 col-sm-6 col-md-{{ $filter['width'] ?? 3 }}">
                        <label class="form-label small mb-1 fw-semibold">{{ $filter['label'] ?? '' }}</label>
                        @if ($filter['type'] === 'select')
                            <select class="form-control form-control-sm filter-select"
                                data-column="{{ $filter['column'] }}" data-filter="{{ $filter['name'] }}">
                                <option value="">{{ $filter['label'] ?? 'All' }}</option>
                                @foreach ($filter['options'] as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        @elseif($filter['type'] === 'date')
                            <input type="date" class="form-control form-control-sm filter-date"
                                data-column="{{ $filter['column'] }}" data-filter="{{ $filter['name'] }}"
                                placeholder="{{ $filter['label'] ?? '' }}">
                        @elseif($filter['type'] === 'text')
                            <input type="text" class="form-control form-control-sm filter-text"
                                data-column="{{ $filter['column'] }}" data-filter="{{ $filter['name'] }}"
                                placeholder="{{ $filter['label'] ?? '' }}">
                        @endif
                    </div>
                @endforeach
                <div class="col-12 col-md-auto">
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-primary flex-fill flex-md-auto" id="applyFilters">
                            <i class="mdi mdi-filter-check"></i> Apply Filters
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary flex-fill flex-md-auto"
                            id="clearFilters">
                            <i class="mdi mdi-filter-off"></i> Clear
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Bulk Actions Bar --}}
    @if ($bulkDeleteEnabled)
        <div class="mb-3" id="bulkActions" style="display: none;">
            <div
                class="alert alert-warning mb-0 d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between py-2 gap-2">
                <span><i class="mdi mdi-information"></i> <strong id="selectedCount">0</strong> item(s) selected</span>
                <button type="button"
                    class="btn btn-sm btn-danger d-inline-flex align-items-center gap-1 w-10 w-sm-auto"
                    id="bulkDeleteBtn">
                    <i class="mdi mdi-delete"></i>
                    <span>Delete Selected</span>
                </button>
            </div>
        </div>
    @endif

    {{-- Table Controls Bar: Search, Export, Columns - All Above Table --}}
    <div class="row align-items-center g-3 mb-3">
        <div class="col-12 col-md-6">
            <div class="input-group input-group-sm">
                <span class="input-group-text"><i class="mdi mdi-magnify"></i></span>
                <input type="search" class="form-control" id="{{ $id }}_search"
                    placeholder="{{ $searchPlaceholder }}">
            </div>
        </div>
        <div class="col-12 col-md-6 text-start text-md-end">
            @if ($exportEnabled)
                <div class="btn-group btn-group-sm d-flex flex-wrap" role="group">
                    <button type="button" class="btn btn-success flex-fill flex-sm-auto"
                        id="{{ $id }}_excel" title="Export to Excel">
                        <i class="mdi mdi-file-excel"></i> <span class="d-none d-sm-inline">Excel</span>
                    </button>
                    <button type="button" class="btn btn-danger flex-fill flex-sm-auto" id="{{ $id }}_pdf"
                        title="Export to PDF">
                        <i class="mdi mdi-file-pdf"></i> <span class="d-none d-sm-inline">PDF</span>
                    </button>
                    <button type="button" class="btn btn-info flex-fill flex-sm-auto" id="{{ $id }}_print"
                        title="Print">
                        <i class="mdi mdi-printer"></i> <span class="d-none d-sm-inline">Print</span>
                    </button>
                    <button type="button" class="btn btn-secondary flex-fill flex-sm-auto"
                        id="{{ $id }}_columns" title="Show/Hide Columns">
                        <i class="mdi mdi-eye"></i> <span class="d-none d-sm-inline">Columns</span>
                    </button>
                </div>
            @endif
        </div>
    </div>
    {{-- Data Table --}}
    <div class="table-responsive" style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
        <table id="{{ $id }}" class="table table-striped table-bordered table-hover"
            style="width:100%; min-width: 600px;">
            <thead>
                <tr>
                    @if ($bulkDeleteEnabled)
                        <th width="30px" class="no-export no-colvis no-sort">
                            <input type="checkbox" id="{{ $id }}_selectAll" class="select-all-checkbox">
                        </th>
                    @endif
                    @foreach ($columns as $column)
                        <th {{ isset($column['width']) ? 'width=' . $column['width'] : '' }}
                            {{ isset($column['orderable']) && !$column['orderable'] ? 'data-orderable=false' : '' }}
                            {{ isset($column['searchable']) && !$column['searchable'] ? 'data-searchable=false' : '' }}>
                            {{ $column['title'] ?? '' }}
                        </th>
                    @endforeach
                    <th width="120px" class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                {{ $slot }}
            </tbody>
        </table>
    </div>

    {{-- Pagination Info Bar: Two Lines --}}
    <div class="mt-3 d-flex flex-column gap-2">
        {{-- Line 1: Showing info and Pagination buttons --}}
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="small text-muted" id="{{ $id }}_info"></div>
            <div class="dataTables_paginate paging_simple_numbers" id="{{ $id }}_pagination_wrapper"></div>
        </div>
        {{-- Line 2: Show entries control --}}
        <div class="d-flex align-items-center gap-2">
            <label class="mb-0 small fw-semibold">Show:</label>
            <select class="form-control form-control-sm" id="{{ $id }}_length" style="width: auto;">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50" selected>50</option>
                <option value="100">100</option>
                <option value="-1">All</option>
            </select>
            <span class="small">entries</span>
        </div>
    </div>
</div>

@push('styles')
    <style>
        .datatable-wrapper {
            background: transparent;
            border-radius: 0;
            padding: 0;
            box-shadow: none;
            border: none;
        }

        .datatable-wrapper .table {
            margin-bottom: 0;
        }

        .datatable-wrapper .table thead th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            color: #495057;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            padding: 0.25rem 0.5rem !important;
            height: 32px !important;
            min-height: 32px !important;
            max-height: 32px !important;
            line-height: 1.2 !important;
            vertical-align: middle !important;
            box-sizing: border-box !important;
        }

        .datatable-wrapper .table tbody tr {
            transition: background-color 0.15s ease;
            height: 32px !important;
            min-height: 32px !important;
            max-height: 32px !important;
            line-height: 1.2 !important;
            box-sizing: border-box !important;
        }

        .datatable-wrapper .table tbody tr:hover {
            background-color: #f8f9fa;
        }

        .datatable-wrapper .table tbody td {
            padding: 0.25rem 0.5rem !important;
            padding-top: 0.25rem !important;
            padding-bottom: 0.25rem !important;
            padding-left: 0.5rem !important;
            padding-right: 0.5rem !important;
            vertical-align: middle !important;
            height: 32px !important;
            min-height: 32px !important;
            max-height: 32px !important;
            line-height: 1.2 !important;
            box-sizing: border-box !important;
        }

        /* CRITICAL: Override ALL conflicting table styles for this datatable */
        #{{ $id }} thead th,
        #{{ $id }} tbody td,
        #{{ $id }} tbody tr,
        table#{{ $id }} thead th,
        table#{{ $id }} tbody td,
        table#{{ $id }} tbody tr {
            height: 32px !important;
            min-height: 32px !important;
            max-height: 32px !important;
            padding-top: 0.25rem !important;
            padding-bottom: 0.25rem !important;
            padding-left: 0.5rem !important;
            padding-right: 0.5rem !important;
            line-height: 1.2 !important;
            box-sizing: border-box !important;
            margin: 0 !important;
        }

        .datatable-wrapper .btn {
            border-radius: 4px;
            font-weight: 500;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .datatable-wrapper .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
        }

        .datatable-wrapper .btn-icon {
            padding: 6px 10px;
            margin: 0 2px;
        }

        .datatable-wrapper .select-all-checkbox,
        .datatable-wrapper .row-checkbox {
            cursor: pointer;
            width: 18px;
            height: 18px;
        }

        #bulkActions .alert {
            margin-bottom: 0;
            padding: 10px 15px;
        }

        /* Pagination Bar Styling - Two Lines */
        .datatable-wrapper .mt-3.d-flex.flex-column {
            padding-top: 0.75rem;
        }

        /* Pagination styling - Target both default location and custom wrapper */
        .datatable-wrapper .dataTables_wrapper .dataTables_paginate,
        #{{ $id }}_pagination_wrapper.dataTables_paginate,
        #{{ $id }}_pagination_wrapper .dataTables_paginate,
        #{{ $id }}_pagination_wrapper {
            display: flex !important;
            gap: 4px !important;
            align-items: center !important;
            flex-wrap: wrap !important;
            margin: 0 !important;
        }

        /* Pagination button styling - Multiple selectors for maximum coverage */
        .datatable-wrapper .dataTables_wrapper .dataTables_paginate .paginate_button,
        .datatable-wrapper .dataTables_wrapper .dataTables_paginate a.paginate_button,
        #{{ $id }}_pagination_wrapper .paginate_button,
        #{{ $id }}_pagination_wrapper a.paginate_button,
        #{{ $id }}_pagination_wrapper.dataTables_paginate .paginate_button,
        #{{ $id }}_pagination_wrapper.dataTables_paginate a.paginate_button,
        .dataTables_paginate .paginate_button,
        .dataTables_paginate a.paginate_button {
            border-radius: 4px !important;
            padding: 6px 12px !important;
            border: 1px solid #dee2e6 !important;
            background: #fff !important;
            color: #495057 !important;
            cursor: pointer !important;
            transition: all 0.2s ease !important;
            margin: 0 2px !important;
            display: inline-block !important;
            text-decoration: none !important;
            min-width: 36px !important;
            text-align: center !important;
            line-height: 1.5 !important;
            box-sizing: border-box !important;
            font-size: 0.875rem !important;
        }

        /* Remove underline from pagination links */
        #{{ $id }}_pagination_wrapper a,
        #{{ $id }}_pagination_wrapper .paginate_button a,
        .dataTables_paginate a.paginate_button {
            text-decoration: none !important;
            color: inherit !important;
        }

        .datatable-wrapper .dataTables_wrapper .dataTables_paginate .paginate_button:hover:not(.disabled):not(.current),
        .datatable-wrapper .dataTables_wrapper .dataTables_paginate a.paginate_button:hover:not(.disabled):not(.current),
        #{{ $id }}_pagination_wrapper .paginate_button:hover:not(.disabled):not(.current),
        #{{ $id }}_pagination_wrapper a.paginate_button:hover:not(.disabled):not(.current) {
            background: #f8f9fa !important;
            border-color: #adb5bd !important;
            color: #495057 !important;
            text-decoration: none !important;
        }

        .datatable-wrapper .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .datatable-wrapper .dataTables_wrapper .dataTables_paginate a.paginate_button.current,
        #{{ $id }}_pagination_wrapper .paginate_button.current,
        #{{ $id }}_pagination_wrapper a.paginate_button.current {
            background: #007bff !important;
            color: white !important;
            border-color: #007bff !important;
            font-weight: 600 !important;
            text-decoration: none !important;
        }

        .datatable-wrapper .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
        .datatable-wrapper .dataTables_wrapper .dataTables_paginate a.paginate_button.disabled,
        #{{ $id }}_pagination_wrapper .paginate_button.disabled,
        #{{ $id }}_pagination_wrapper a.paginate_button.disabled {
            opacity: 0.5 !important;
            cursor: not-allowed !important;
            pointer-events: none !important;
        }

        /* Ellipsis styling */
        #{{ $id }}_pagination_wrapper .ellipsis,
        .dataTables_paginate .ellipsis {
            padding: 6px 8px !important;
            color: #6c757d !important;
            cursor: default !important;
            margin: 0 2px !important;
        }

        /* Remove default link styling */
        .dataTables_paginate .paginate_button a,
        #{{ $id }}_pagination_wrapper .paginate_button a {
            text-decoration: none !important;
            color: inherit !important;
            display: block !important;
        }

        /* Consistent Color Scheme */
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }

        .btn-success {
            background-color: #28a745;
            border-color: #28a745;
        }

        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }

        .btn-warning {
            background-color: #ffc107;
            border-color: #ffc107;
            color: #212529;
        }

        .btn-info {
            background-color: #17a2b8;
            border-color: #17a2b8;
        }

        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
        }

        /* Mobile Responsive Styles */
        @media (max-width: 767.98px) {
            .datatable-wrapper {
                padding: 1rem;
            }

            .datatable-wrapper .table {
                font-size: 0.875rem;
            }

            .datatable-wrapper .table thead th {
                font-size: 0.7rem;
                padding: 8px 4px;
            }

            .datatable-wrapper .table tbody td {
                padding: 8px 4px;
            }

            .datatable-wrapper .btn {
                font-size: 0.875rem;
                padding: 0.375rem 0.5rem;
            }

            .datatable-wrapper .btn-group {
                width: 100%;
            }

            .datatable-wrapper .btn-group .btn {
                flex: 1 1 auto;
                min-width: 0;
            }

            /* Ensure DataTables responsive child rows are visible */
            .dtr-details {
                display: block !important;
            }

            .dtr-details li {
                padding: 0.5rem 0;
                border-bottom: 1px solid #dee2e6;
            }

            .dtr-details li:last-child {
                border-bottom: none;
            }

            .dtr-title {
                font-weight: 600;
                margin-right: 0.5rem;
            }
        }

        /* Ensure responsive table wrapper doesn't break layout */
        .table-responsive {
            display: block;
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        /* DataTables responsive styling */
        table.dataTable.dtr-inline.collapsed>tbody>tr>td.child,
        table.dataTable.dtr-inline.collapsed>tbody>tr>th.child,
        table.dataTable.dtr-inline.collapsed>tbody>tr>td.dataTables_empty {
            cursor: default !important;
        }

        table.dataTable.dtr-inline.collapsed>tbody>tr>td:first-child:before,
        table.dataTable.dtr-inline.collapsed>tbody>tr>th:first-child:before {
            background-color: #007bff;
            border-radius: 50%;
            color: white;
            content: "+";
            display: inline-block;
            font-weight: bold;
            height: 1.2em;
            line-height: 1.2em;
            margin-right: 0.5em;
            text-align: center;
            width: 1.2em;
        }

        table.dataTable.dtr-inline.collapsed>tbody>tr.parent>td:first-child:before,
        table.dataTable.dtr-inline.collapsed>tbody>tr.parent>th:first-child:before {
            content: "-";
            background-color: #dc3545;
        }
    </style>

    <!-- CRITICAL: Inline style to force 32px height - Highest priority -->
    <!-- This MUST load after DataTables CDN CSS -->
    <style>
        /* Force 32px height with MAXIMUM specificity - Override DataTables CDN CSS */
        #{{ $id }} thead th,
        #{{ $id }} tbody td,
        #{{ $id }} tbody tr,
        table#{{ $id }} thead th,
        table#{{ $id }} tbody td,
        table#{{ $id }} tbody tr,
        .dataTables_wrapper #{{ $id }} thead th,
        .dataTables_wrapper #{{ $id }} tbody td,
        .dataTables_wrapper #{{ $id }} tbody tr,
        table.dataTable#{{ $id }} thead th,
        table.dataTable#{{ $id }} tbody td,
        table.dataTable#{{ $id }} tbody tr,
        .dataTables_wrapper table.dataTable#{{ $id }} thead th,
        .dataTables_wrapper table.dataTable#{{ $id }} tbody td,
        .dataTables_wrapper table.dataTable#{{ $id }} tbody tr {
            height: 32px !important;
            min-height: 32px !important;
            max-height: 32px !important;
            padding-top: 4px !important;
            padding-bottom: 4px !important;
            padding-left: 8px !important;
            padding-right: 8px !important;
            padding: 4px 8px !important;
            line-height: 1.2 !important;
            box-sizing: border-box !important;
            margin: 0 !important;
            vertical-align: middle !important;
        }

        /* Override DataTables CDN default styles - MAXIMUM SPECIFICITY */
        .dataTables_wrapper table.dataTable#{{ $id }} thead th,
        .dataTables_wrapper table.dataTable#{{ $id }} tbody td,
        .dataTables_wrapper table.dataTable#{{ $id }} tbody tr {
            padding: 4px 8px !important;
        }

        /* Override any inline styles that DataTables might add */
        #{{ $id }} thead th[style],
        #{{ $id }} tbody td[style],
        #{{ $id }} tbody tr[style] {
            height: 32px !important;
            min-height: 32px !important;
            max-height: 32px !important;
            padding-top: 4px !important;
            padding-bottom: 4px !important;
            padding: 4px 8px !important;
        }

        /* Custom column visibility dropdown styling */
        .custom-colvis-dropdown {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            padding: 0.5rem 0;
            max-height: 400px;
            overflow-y: auto;
        }

        .custom-colvis-dropdown .dropdown-item-text {
            transition: background-color 0.15s ease-in-out;
        }

        .custom-colvis-dropdown .dropdown-item-text:hover {
            background-color: #f8f9fa;
        }

        .custom-colvis-dropdown .form-check-input {
            margin-top: 0;
            cursor: pointer;
        }

        .custom-colvis-dropdown label {
            user-select: none;
        }

        /* Disable sorting indicator on checkbox column */
        #{{ $id }} thead th.select-checkbox,
        #{{ $id }} thead th.no-sort {
            cursor: default !important;
        }

        #{{ $id }} thead th.select-checkbox:before,
        #{{ $id }} thead th.select-checkbox:after,
        #{{ $id }} thead th.no-sort:before,
        #{{ $id }} thead th.no-sort:after {
            display: none !important;
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {
            const tableId = '#{{ $id }}';
            let table; // Declare table variable first

            // Define helper functions before table initialization
            function updateBulkActions() {
                if (!table || typeof table.rows === 'undefined') return;
                try {
                    // Count only checked checkboxes, not DataTables selected rows
                    const checkedCount = $(tableId + ' tbody .row-checkbox:checked').length;
                    if (checkedCount > 0) {
                        $('#bulkActions').slideDown(200);
                        $('#selectedCount').text(checkedCount);
                    } else {
                        $('#bulkActions').slideUp(200);
                    }
                } catch (e) {
                    // Silently fail if table not ready
                }
            }

            function updateSelectAllState() {
                if (!table || typeof table.rows === 'undefined') return;
                try {
                    const currentPageRows = table.rows({
                        page: 'current'
                    }).nodes().to$();
                    const totalOnPage = currentPageRows.length;
                    const checkedOnPage = currentPageRows.find('.row-checkbox:checked').length;
                    $('#{{ $id }}_selectAll').prop('checked', totalOnPage > 0 && totalOnPage ===
                        checkedOnPage);
                } catch (e) {
                    // Silently fail if table not ready
                }
            }

            function updatePaginationInfo() {
                if (!table || typeof table.page === 'undefined') return;
                try {
                    const info = table.page.info();
                    const text = `Showing ${info.start + 1} to ${info.end} of ${info.recordsTotal} entries`;
                    const infoElement = $('#' + '{{ $id }}' + '_info');
                    if (infoElement.length) {
                        infoElement.text(text).show();
                    }

                    // Move pagination to custom container and ensure proper styling
                    setTimeout(function() {
                        const defaultPagination = $(tableId + '_wrapper').find('.dataTables_paginate');
                        if (defaultPagination.length) {
                            defaultPagination.appendTo('#' + '{{ $id }}' +
                                '_pagination_wrapper');
                            // Ensure pagination is visible
                            const paginationWrapper = $('#' + '{{ $id }}' +
                                '_pagination_wrapper');
                            paginationWrapper.show();

                            // Force proper styling on pagination buttons
                            paginationWrapper.find('.paginate_button, a.paginate_button').each(function() {
                                $(this).css({
                                    'display': 'inline-block',
                                    'margin': '0 2px',
                                    'text-decoration': 'none',
                                    'border': '1px solid #dee2e6',
                                    'border-radius': '4px',
                                    'padding': '6px 12px',
                                    'background': '#fff',
                                    'color': '#495057',
                                    'min-width': '36px',
                                    'text-align': 'center'
                                });
                            });
                        } else {
                            // If pagination doesn't exist, check if we need it
                            const info = table.page.info();
                            if (info.pages > 1) {
                                // Force DataTables to render pagination
                                table.draw(false);
                            }
                        }
                    }, 100);

                    // Also try after a longer delay to catch late renders
                    setTimeout(function() {
                        const defaultPagination = $(tableId + '_wrapper').find('.dataTables_paginate');
                        if (defaultPagination.length) {
                            defaultPagination.appendTo('#' + '{{ $id }}' +
                                '_pagination_wrapper');
                            const paginationWrapper = $('#' + '{{ $id }}' +
                                '_pagination_wrapper');
                            paginationWrapper.show();

                            // Force proper styling on pagination buttons
                            paginationWrapper.find('.paginate_button, a.paginate_button').each(function() {
                                $(this).css({
                                    'display': 'inline-block',
                                    'margin': '0 2px',
                                    'text-decoration': 'none',
                                    'border': '1px solid #dee2e6',
                                    'border-radius': '4px',
                                    'padding': '6px 12px',
                                    'background': '#fff',
                                    'color': '#495057',
                                    'min-width': '36px',
                                    'text-align': 'center'
                                });
                            });
                        }
                    }, 500);
                } catch (e) {
                    // Silently fail if table not ready
                }
            }

            // Function to force 32px height on table elements - SIMPLIFIED to prevent infinite loops
            var isApplyingStyles = false; // Flag to prevent infinite loops
            function forceTableHeight32px() {
                if (isApplyingStyles) return; // Prevent recursive calls
                isApplyingStyles = true;

                try {
                    $(tableId + ' thead th, ' + tableId + ' tbody td, ' + tableId + ' tbody tr').each(function() {
                        var el = this;
                        // Use setProperty with important flag - most reliable
                        el.style.setProperty('height', '32px', 'important');
                        el.style.setProperty('min-height', '32px', 'important');
                        el.style.setProperty('max-height', '32px', 'important');
                        el.style.setProperty('padding-top', '4px', 'important');
                        el.style.setProperty('padding-bottom', '4px', 'important');
                        el.style.setProperty('padding-left', '8px', 'important');
                        el.style.setProperty('padding-right', '8px', 'important');
                        el.style.setProperty('line-height', '1.2', 'important');
                        el.style.setProperty('box-sizing', 'border-box', 'important');
                        el.style.setProperty('margin', '0', 'important');
                        el.style.setProperty('vertical-align', 'middle', 'important');
                    });
                } finally {
                    isApplyingStyles = false;
                }
            }

            // Remove any completely empty rows before DataTables initializes (prevents ghost/blank rows)
            $(tableId + ' tbody tr').each(function() {
                if ($(this).text().trim() === '') {
                    $(this).remove();
                }
            });

            table = $(tableId).DataTable({
                dom: "<'row'<'col-sm-12'tr>><'row'<'col-sm-12'p>>",
                scrollX: true,
                scrollCollapse: true,
                autoWidth: false,
                buttons: [
                    @if ($exportEnabled)
                        {
                            extend: 'excelHtml5',
                            text: 'Excel',
                            className: 'd-none',
                            exportOptions: {
                                columns: ':visible:not(.no-export)',
                                format: {
                                    body: function(data, row, column, node) {
                                        return $(data).text() || data;
                                    }
                                }
                            }
                        }, {
                            extend: 'pdfHtml5',
                            text: 'PDF',
                            className: 'd-none',
                            exportOptions: {
                                columns: ':visible:not(.no-export)',
                                format: {
                                    body: function(data, row, column, node) {
                                        return $(data).text() || data;
                                    }
                                }
                            },
                            orientation: 'landscape',
                            pageSize: 'A4'
                        }, {
                            extend: 'print',
                            text: 'Print',
                            className: 'd-none',
                            exportOptions: {
                                columns: ':visible:not(.no-export)'
                            }
                        }, {
                            extend: 'colvis',
                            text: 'Columns',
                            className: 'd-none',
                            columns: ':not(.no-colvis)',
                            collectionLayout: 'three-column',
                            postfixButtons: ['colvisRestore']
                        }
                    @endif
                ],
                pageLength: {{ $pageLength }},
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                searching: true,
                ordering: true,
                order: {!! json_encode($order) !!},
                @if ($responsive)
                    responsive: {
                        details: {
                            type: 'column',
                            target: 'tr'
                        }
                    },
                @endif
                language: {
                    search: '',
                    searchPlaceholder: '{{ $searchPlaceholder }}',
                    lengthMenu: "",
                    info: "",
                    infoEmpty: "",
                    infoFiltered: "",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    }
                },
                columnDefs: [
                    @if ($bulkDeleteEnabled)
                        {
                            orderable: false,
                            searchable: false,
                            targets: 0,
                            className: 'select-checkbox no-export no-colvis no-sort'
                        },
                    @endif {
                        orderable: false,
                        targets: -1,
                        className: 'text-center no-export'
                    }
                ],
                drawCallback: function() {
                    // Use setTimeout to ensure table is fully initialized
                    setTimeout(function() {
                        if (table && typeof table.page !== 'undefined') {
                            updateBulkActions();
                            updateSelectAllState();
                            updatePaginationInfo();
                            // Force 32px height after every draw - but only once per draw cycle
                            if (!isApplyingStyles) {
                                forceTableHeight32px();
                            }
                        }
                    }, 0);
                    $('[data-toggle="tooltip"]').tooltip();
                },
                initComplete: function() {
                    // Force 32px height after initialization
                    setTimeout(function() {
                        forceTableHeight32px();
                        updatePaginationInfo();
                        table.columns.adjust();
                    }, 100);
                }
            });

            // Initial pagination info update
            setTimeout(function() {
                updatePaginationInfo();
                if (table && table.columns) {
                    table.columns.adjust();
                }
            }, 200);

            // Custom length menu
            $('#' + '{{ $id }}' + '_length').on('change', function() {
                table.page.len($(this).val()).draw();
                setTimeout(function() {
                    updatePaginationInfo();
                }, 100);
            });

            // Custom search
            $('#' + '{{ $id }}' + '_search').on('keyup', function() {
                table.search($(this).val()).draw();
            });

            // Custom column visibility with localStorage persistence
            // Use a versioned storage key so structural changes to the table
            // automatically reset stale column-visibility preferences.
            var storageKey = 'datatable_colvis_{{ $id }}_v2';
            // Clean up legacy key to avoid stale visibility states
            localStorage.removeItem('datatable_colvis_{{ $id }}');

            // Load saved column visibility on init
            function loadColumnVisibility() {
                try {
                    var saved = localStorage.getItem(storageKey);
                    if (saved) {
                        var columns = JSON.parse(saved);
                        var currentCount = table.columns().count();
                        var savedCount = Object.keys(columns).length;

                        // If structure changed, drop the old preferences
                        if (savedCount !== currentCount) {
                            localStorage.removeItem(storageKey);
                        } else {
                            table.columns().every(function(index) {
                                var column = this;
                                var columnIndex = column.index();
                                if (columns.hasOwnProperty(columnIndex)) {
                                    column.visible(columns[columnIndex], false);
                                }
                            });
                            table.columns.adjust().draw(false);
                        }
                    }
                } catch (e) {
                    console.error('Error loading column visibility:', e);
                }
            }

            // Save column visibility to localStorage
            function saveColumnVisibility() {
                try {
                    var columns = {};
                    table.columns().every(function() {
                        var column = this;
                        columns[column.index()] = column.visible();
                    });
                    localStorage.setItem(storageKey, JSON.stringify(columns));
                } catch (e) {
                    console.error('Error saving column visibility:', e);
                }
            }

            // Override default colvis button behavior
            $('#' + '{{ $id }}' + '_columns').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                // Get button position for dropdown placement
                var $button = $(this);
                var buttonOffset = $button.offset();
                var buttonWidth = $button.outerWidth();
                var buttonHeight = $button.outerHeight();

                // Remove existing custom dropdown if any
                $('.custom-colvis-dropdown').remove();

                // Calculate dropdown position - align to right edge of button
                var dropdownLeft = buttonOffset.left + buttonWidth - 200; // 200px is dropdown width
                var dropdownTop = buttonOffset.top + buttonHeight + 5;

                // Ensure dropdown doesn't go off screen
                if (dropdownLeft < 10) dropdownLeft = 10;
                if (dropdownLeft + 200 > $(window).width() - 10) {
                    dropdownLeft = $(window).width() - 210;
                }

                // Create custom dropdown
                var $dropdown = $(
                    '<div class="custom-colvis-dropdown dropdown-menu show position-fixed" style="top: ' +
                    dropdownTop + 'px; left: ' + dropdownLeft +
                    'px; min-width: 200px; z-index: 1050;"></div>');

                // Add column checkboxes
                table.columns().every(function() {
                    var column = this;
                    var columnIndex = column.index();
                    var columnHeader = $(column.header());
                    var columnTitle = columnHeader.text().trim();
                    var isVisible = column.visible();
                    var isNoColvis = columnHeader.hasClass('no-colvis') || columnHeader.closest(
                        'th').hasClass('no-colvis');
                    var isCheckboxColumn = columnHeader.hasClass('select-checkbox') || columnHeader
                        .closest(
                            'th').hasClass('select-checkbox');

                    // Skip columns marked as no-colvis or checkbox column
                    if (isNoColvis || isCheckboxColumn) return;

                    var $item = $(
                        '<div class="dropdown-item-text d-flex align-items-center justify-content-between p-2" style="cursor: pointer;"></div>'
                    );
                    var $label = $('<label class="mb-0 flex-fill" style="cursor: pointer;">' +
                        columnTitle + '</label>');
                    var $checkbox = $('<input type="checkbox" class="form-check-input ms-2" ' + (
                            isVisible ? 'checked' : '') + ' data-column="' + columnIndex +
                        '" style="cursor: pointer; flex-shrink: 0;"></input>');

                    $item.append($label).append($checkbox);
                    $dropdown.append($item);

                    // Handle checkbox change
                    $checkbox.on('change', function() {
                        var isChecked = $(this).is(':checked');
                        column.visible(isChecked, false);
                        table.columns.adjust().draw(false);
                        saveColumnVisibility();
                    });

                    // Make entire item clickable
                    $item.on('click', function(e) {
                        if (e.target.type !== 'checkbox') {
                            $checkbox.prop('checked', !$checkbox.prop('checked')).trigger(
                                'change');
                        }
                    });
                });

                // Add to body and show
                $('body').append($dropdown);

                // Close on outside click
                $(document).on('click.customColvis', function(e) {
                    if (!$dropdown.is(e.target) && $dropdown.has(e.target).length === 0 && !$button
                        .is(e.target)) {
                        $dropdown.remove();
                        $(document).off('click.customColvis');
                    }
                });
            });

            // Save column visibility on column visibility change (fallback)
            table.on('column-visibility', function(e, settings, column, state) {
                saveColumnVisibility();
            });

            // Load saved preferences after table init
            setTimeout(function() {
                loadColumnVisibility();
            }, 500);

            // Force 32px height after table initialization - Limited attempts to prevent performance issues
            setTimeout(function() {
                forceTableHeight32px();
            }, 100);

            setTimeout(function() {
                forceTableHeight32px();
            }, 500);

            setTimeout(function() {
                forceTableHeight32px();
            }, 1000);

            // Also force on every draw event - but with protection against loops
            table.on('draw', function() {
                setTimeout(function() {
                    if (!isApplyingStyles) {
                        forceTableHeight32px();
                    }
                }, 50);
            });

            // Force on page change
            table.on('page', function() {
                setTimeout(function() {
                    if (!isApplyingStyles) {
                        forceTableHeight32px();
                    }
                }, 100);
            });

            // Export button handlers
            @if ($exportEnabled)
                $('#' + '{{ $id }}' + '_excel').on('click', function() {
                    table.button('.buttons-excel').trigger();
                });

                $('#' + '{{ $id }}' + '_pdf').on('click', function() {
                    table.button('.buttons-pdf').trigger();
                });

                $('#' + '{{ $id }}' + '_print').on('click', function() {
                    table.button('.buttons-print').trigger();
                });

                // Custom column visibility handler is defined above
            @endif


            @if ($bulkDeleteEnabled)
                // Select All functionality - FIXED
                $(document).on('click', tableId + ' thead #{{ $id }}_selectAll', function() {
                    const isChecked = $(this).is(':checked');
                    const currentPageRows = table.rows({
                        page: 'current'
                    }).nodes();
                    $(currentPageRows).find('.row-checkbox').prop('checked', isChecked);
                    // Don't use DataTables select API, just track checkbox state
                    updateBulkActions();
                });

                // Individual checkbox selection
                $(document).on('change', tableId + ' tbody .row-checkbox', function() {
                    // Don't use DataTables select API, just track checkbox state
                    updateSelectAllState();
                    updateBulkActions();
                });


                // Bulk delete function
                function performBulkDelete() {
                    const selectedIds = [];

                    // Get IDs from checked checkboxes only
                    $(tableId + ' tbody .row-checkbox:checked').each(function() {
                        const id = $(this).val();
                        if (id) {
                            selectedIds.push(id);
                        }
                    });

                    if (selectedIds.length === 0) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'No Selection',
                            text: 'Please select at least one row to delete.',
                            confirmButtonText: 'OK'
                        });
                        return;
                    }

                    Swal.fire({
                        title: 'Are you sure?',
                        text: `You are about to delete ${selectedIds.length} item(s). This action cannot be undone!`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Yes, delete them!',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: '{{ $bulkDeleteRoute ?? '#' }}',
                                type: 'POST',
                                data: {
                                    _token: '{{ csrf_token() }}',
                                    ids: selectedIds
                                },
                                success: function(response) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Deleted!',
                                        text: response.message ||
                                            'Selected items have been deleted.',
                                        timer: 2000,
                                        showConfirmButton: false
                                    });
                                    // Remove rows with checked checkboxes
                                    $(tableId + ' tbody .row-checkbox:checked').each(
                                        function() {
                                            table.row($(this).closest('tr')).remove();
                                        });
                                    table.draw();
                                    updateBulkActions();
                                },
                                error: function(xhr) {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error!',
                                        text: xhr.responseJSON?.message ||
                                            'Failed to delete items. Please try again.',
                                        confirmButtonText: 'OK'
                                    });
                                }
                            });
                        }
                    });
                }

                $('#bulkDeleteBtn').on('click', function() {
                    performBulkDelete();
                });
            @endif

            @if (!empty($filters))
                let filterFunctions = [];

                // Advanced filter functionality
                function applyFilters() {
                    // Clear previous filters
                    $.fn.dataTable.ext.search = filterFunctions = [];
                    table.search('').columns().search('');

                    @foreach ($filters as $filter)
                        @if ($filter['type'] === 'select')
                            const filter{{ $loop->index }} = $(
                                '.filter-select[data-filter="{{ $filter['name'] }}"]').val();
                            if (filter{{ $loop->index }}) {
                                table.column({{ $filter['column'] }}).search('^' + filter{{ $loop->index }} +
                                    '$', true, false);
                            }
                        @elseif ($filter['type'] === 'date')
                            const filter{{ $loop->index }} = $(
                                '.filter-date[data-filter="{{ $filter['name'] }}"]').val();
                            if (filter{{ $loop->index }}) {
                                @if (str_contains($filter['name'], 'from'))
                                    filterFunctions.push(function(settings, data, dataIndex) {
                                        if (settings.nTable.id !== '{{ $id }}') return true;
                                        const filterVal = filter{{ $loop->index }};
                                        if (!filterVal) return true;
                                        try {
                                            const cellDate = new Date(data[{{ $filter['column'] }}]);
                                            const filterDate = new Date(filterVal);
                                            return !isNaN(cellDate.getTime()) && cellDate >= filterDate;
                                        } catch (e) {
                                            return true;
                                        }
                                    });
                                @elseif (str_contains($filter['name'], 'to'))
                                    filterFunctions.push(function(settings, data, dataIndex) {
                                        if (settings.nTable.id !== '{{ $id }}') return true;
                                        const filterVal = filter{{ $loop->index }};
                                        if (!filterVal) return true;
                                        try {
                                            const cellDate = new Date(data[{{ $filter['column'] }}]);
                                            const filterDate = new Date(filterVal);
                                            return !isNaN(cellDate.getTime()) && cellDate <= filterDate;
                                        } catch (e) {
                                            return true;
                                        }
                                    });
                                @endif
                            }
                        @elseif ($filter['type'] === 'text')
                            const filter{{ $loop->index }} = $(
                                '.filter-text[data-filter="{{ $filter['name'] }}"]').val();
                            if (filter{{ $loop->index }}) {
                                @if (str_contains($filter['name'], 'min'))
                                    filterFunctions.push(function(settings, data, dataIndex) {
                                        if (settings.nTable.id !== '{{ $id }}') return true;
                                        const filterVal = parseFloat(filter{{ $loop->index }}) || 0;
                                        if (!filterVal) return true;
                                        const cellValue = parseFloat(String(data[{{ $filter['column'] }}])
                                            .replace(/[^0-9.-]+/g, '')) || 0;
                                        return cellValue >= filterVal;
                                    });
                                @else
                                    table.column({{ $filter['column'] }}).search(filter{{ $loop->index }});
                                @endif
                            }
                        @endif
                    @endforeach

                    // Apply custom filter functions
                    $.fn.dataTable.ext.search = filterFunctions;
                    table.draw();
                }

                // Apply Filters button
                $('#applyFilters').on('click', function() {
                    applyFilters();
                });

                // Clear Filters button
                $('#clearFilters').on('click', function() {
                    $('.filter-select, .filter-date, .filter-text').val('');
                    $.fn.dataTable.ext.search = filterFunctions = [];
                    table.search('').columns().search('').draw();
                });
            @endif

            // Individual delete functionality
            $(tableId + ' tbody').on('click', '.delete-row', function(e) {
                e.preventDefault();
                const id = $(this).data('id');
                const name = $(this).data('name') || 'this item';
                const deleteUrl = $(this).data('url') || '{{ $deleteRoute ?? '#' }}'.replace(':id', id);

                Swal.fire({
                    title: 'Are you sure?',
                    text: `You are about to delete ${name}. This action cannot be undone!`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: deleteUrl,
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                _method: 'DELETE'
                            },
                            success: function(response) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: response.message ||
                                        'Item has been deleted.',
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                                table.row($(e.target).closest('tr')).remove().draw();
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: xhr.responseJSON?.message ||
                                        'Failed to delete item. Please try again.',
                                    confirmButtonText: 'OK'
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>
@endpush
