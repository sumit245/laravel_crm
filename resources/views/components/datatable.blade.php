@props([
    'id' => 'dataTable',
    'columns' => [],
    'data' => [],
    'pageLength' => 50,
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
    'serverSide' => false,
    'deferLoading' => null,
    'ajaxUrl' => null,
    'ajaxData' => null,
    'processing' => true,
])

<div class="datatable-wrapper" id="datatable-wrapper-{{ $id }}">
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
                    <a href="{{ $importFormatUrl }}" class="download-format-link" target="_blank">
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
                            <select
                                class="form-control form-control-sm filter-select {{ isset($filter['select2']) && $filter['select2'] ? 'filter-select2' : '' }}"
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
                        <button type="button" class="btn btn-sm btn-primary flex-fill flex-md-auto"
                            id="{{ $id }}_applyFilters">
                            <i class="mdi mdi-filter-check"></i> Apply Filters
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary flex-fill flex-md-auto"
                            id="{{ $id }}_clearFilters">
                            <i class="mdi mdi-filter-off"></i> Clear
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Bulk Actions Bar --}}
    @if ($bulkDeleteEnabled)
        <div class="mb-3" id="{{ $id }}_bulkActions" style="display: none;">
            <div
                class="alert alert-warning mb-0 d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between py-2 gap-2">
                <span><i class="mdi mdi-information"></i> <strong id="{{ $id }}_selectedCount">0</strong>
                    item(s) selected</span>
                <button type="button"
                    class="btn btn-sm btn-danger d-inline-flex align-items-center gap-1 w-10 w-sm-auto"
                    id="{{ $id }}_bulkDeleteBtn">
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

    {{-- Pagination Info Bar --}}
    <div class="mt-3 d-flex flex-column gap-2">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="small text-muted" id="{{ $id }}_info"></div>
            <div class="dataTables_paginate paging_simple_numbers" id="{{ $id }}_pagination_wrapper"></div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <label class="mb-0 small fw-semibold">Show:</label>
            <select class="form-control form-control-sm" id="{{ $id }}_length" style="width: auto;">
                <option value="50">50</option>
                <option value="100">100</option>
                <option value="200" selected>200</option>
                <option value="500">500</option>
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
        .datatable-wrapper .dataTables_length:not(.dataTables_length_bottom),
        #{{ $id }}_wrapper .dataTables_length:not(.dataTables_length_bottom),
        .dataTables_wrapper .dataTables_length:not(.dataTables_length_bottom) {
            display: none !important;
            visibility: hidden !important;
            height: 0 !important;
            overflow: hidden !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        .datatable-wrapper .table {
            margin-bottom: 0;
        }
        .datatable-wrapper .table thead th {
            position: relative;
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
            cursor: pointer;
        }
        .datatable-wrapper .table thead th>span,
        .datatable-wrapper .table thead th>i,
        .datatable-wrapper .table thead th>.dtr-details,
        .datatable-wrapper .table thead th .dt-orderable-asc,
        .datatable-wrapper .table thead th .dt-orderable-desc,
        .datatable-wrapper .table thead th .dt-orderable-none,
        table.dataTable thead th>span,
        table.dataTable thead th>i,
        table.dataTable thead th::before,
        #{{ $id }} thead th>span,
        #{{ $id }} thead th>i,
        #{{ $id }} thead th::before {
            display: none !important;
            content: none !important;
            visibility: hidden !important;
        }
        .datatable-wrapper .table thead th::before,
        .datatable-wrapper .table thead th.sorting::before,
        .datatable-wrapper .table thead th.sorting_asc::before,
        .datatable-wrapper .table thead th.sorting_desc::before,
        table.dataTable thead th::before,
        table.dataTable thead th.sorting::before,
        table.dataTable thead th.sorting_asc::before,
        table.dataTable thead th.sorting_desc::before,
        #{{ $id }} thead th::before,
        #{{ $id }} thead th.sorting::before,
        #{{ $id }} thead th.sorting_asc::before,
        #{{ $id }} thead th.sorting_desc::before {
            display: none !important;
            content: none !important;
            visibility: hidden !important;
        }
        .datatable-wrapper .table thead th .dtr-details,
        .datatable-wrapper .table thead th>span:not(.sorting-indicator),
        .datatable-wrapper .table thead th>i:last-child,
        .datatable-wrapper .table thead th .dt-orderable-asc,
        .datatable-wrapper .table thead th .dt-orderable-desc,
        .datatable-wrapper .table thead th .dt-orderable-none,
        table.dataTable thead th .dtr-details,
        table.dataTable thead th>span,
        table.dataTable thead th>i,
        #{{ $id }} thead th .dtr-details,
        #{{ $id }} thead th>span,
        #{{ $id }} thead th>i {
            display: none !important;
            visibility: hidden !important;
        }
        .datatable-wrapper .table thead th.sorting:before,
        .datatable-wrapper .table thead th.sorting_asc:before,
        .datatable-wrapper .table thead th.sorting_desc:before,
        table.dataTable thead th.sorting:before,
        table.dataTable thead th.sorting_asc:before,
        table.dataTable thead th.sorting_desc:before,
        #{{ $id }} thead th.sorting:before,
        #{{ $id }} thead th.sorting_asc:before,
        #{{ $id }} thead th.sorting_desc:before {
            display: none !important;
            content: none !important;
            visibility: hidden !important;
        }
        .datatable-wrapper .table thead th.sorting,
        .datatable-wrapper .table thead th.sorting_asc,
        .datatable-wrapper .table thead th.sorting_desc {
            padding-left: 1.5rem !important;
            padding-right: 0.5rem !important;
        }
        .datatable-wrapper .table thead th.sorting::after,
        .datatable-wrapper .table thead th.sorting_asc::after,
        .datatable-wrapper .table thead th.sorting_desc::after {
            position: absolute;
            left: 0.5rem;
            top: 50%;
            transform: translateY(-50%);
            display: block;
            opacity: 0.3;
            font-size: 0.8rem;
            font-family: 'Material Design Icons';
            font-weight: normal;
        }
        .datatable-wrapper .table thead th.sorting::after { content: "\F0045"; }
        .datatable-wrapper .table thead th.sorting_asc::after { content: "\F005D"; opacity: 1; }
        .datatable-wrapper .table thead th.sorting_desc::after { content: "\F0045"; opacity: 1; }
        .datatable-wrapper .table thead th.select-checkbox::after,
        .datatable-wrapper .table thead th.no-sort::after {
            display: none !important;
        }
        .datatable-wrapper .table tbody tr {
            transition: background-color 0.15s ease;
            height: 32px !important;
            min-height: 32px !important;
            max-height: 32px !important;
            line-height: 1.15 !important;
            box-sizing: border-box !important;
            will-change: background-color;
        }
        .datatable-wrapper .table tbody tr:hover {
            background-color: #f8f9fa;
        }
        .datatable-wrapper .table tbody td {
            padding: 0.125rem 0.25rem !important;
            vertical-align: middle !important;
            height: 32px !important;
            min-height: 32px !important;
            max-height: 32px !important;
            line-height: 1.15 !important;
            box-sizing: border-box !important;
        }
        #{{ $id }} thead th,
        #{{ $id }} tbody td,
        #{{ $id }} tbody tr,
        table#{{ $id }} thead th,
        table#{{ $id }} tbody td,
        table#{{ $id }} tbody tr {
            height: 32px !important;
            min-height: 32px !important;
            max-height: 32px !important;
            padding-top: 0.125rem !important;
            padding-bottom: 0.125rem !important;
            padding-left: 0.25rem !important;
            padding-right: 0.25rem !important;
            line-height: 1.15 !important;
            box-sizing: border-box !important;
            margin: 0 !important;
        }
        .datatable-wrapper .btn {
            border-radius: 4px;
            font-weight: 500;
            transition: box-shadow 0.15s ease;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            will-change: box-shadow;
        }
        .datatable-wrapper .btn:hover {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
        }
        .datatable-wrapper .btn-icon {
            padding: 6px 10px;
            margin: 0 2px;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            min-width: 30px !important;
            opacity: 1 !important;
            visibility: visible !important;
        }
        .datatable-wrapper .add-new-btn { min-width: 140px; }
        .datatable-wrapper .btn-icon i { font-size: 1rem !important; }
        .datatable-wrapper .btn-danger {
            background-color: #dc3545 !important;
            border-color: #dc3545 !important;
            color: white !important;
        }
        .datatable-wrapper .btn-danger:hover {
            background-color: #c82333 !important;
            border-color: #bd2130 !important;
        }
        .datatable-wrapper .table td:last-child,
        .datatable-wrapper .table th:last-child {
            display: table-cell !important;
            visibility: visible !important;
            opacity: 1 !important;
            width: 140px !important;
            min-width: 140px !important;
            max-width: 140px !important;
            white-space: nowrap !important;
            overflow: visible !important;
            text-align: center !important;
            box-sizing: border-box !important;
        }
        .datatable-wrapper .table tbody td.dt-truncate {
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            max-width: 120px !important;
        }
        .datatable-wrapper .table tbody tr.dt-row-active {
            background-color: #eef5ff !important;
        }
        .datatable-wrapper .table tbody tr.dt-row-active td:last-child .btn-icon {
            box-shadow: 0 0 0 1px rgba(0, 123, 255, 0.35);
        }
        .datatable-wrapper .table td:last-child .btn-icon {
            margin: 0 1px !important;
            padding: 4px 6px !important;
            display: inline-flex !important;
            flex-shrink: 0 !important;
            width: auto !important;
            min-width: auto !important;
            max-width: none !important;
        }
        .datatable-wrapper .select-all-checkbox,
        .datatable-wrapper .row-checkbox {
            cursor: pointer;
            width: 18px;
            height: 18px;
        }
        [id$="_bulkActions"] .alert {
            margin-bottom: 0;
            padding: 10px 15px;
        }
        .datatable-wrapper .mt-3.d-flex.flex-column { padding-top: 0.75rem; }
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
        .datatable-wrapper .dataTables_wrapper .dataTables_paginate .paginate_button,
        .datatable-wrapper .dataTables_wrapper .dataTables_paginate a.paginate_button,
        #{{ $id }}_pagination_wrapper .paginate_button,
        #{{ $id }}_pagination_wrapper a.paginate_button,
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
        #{{ $id }}_pagination_wrapper .ellipsis,
        .dataTables_paginate .ellipsis {
            padding: 6px 8px !important;
            color: #6c757d !important;
            cursor: default !important;
            margin: 0 2px !important;
        }
        .dataTables_paginate .paginate_button a,
        #{{ $id }}_pagination_wrapper .paginate_button a {
            text-decoration: none !important;
            color: inherit !important;
            display: block !important;
        }
        .btn-primary { background-color: #007bff; border-color: #007bff; }
        .btn-success { background-color: #28a745; border-color: #28a745; }
        .btn-danger { background-color: #dc3545; border-color: #dc3545; }
        .btn-warning { background-color: #ffc107; border-color: #ffc107; color: #212529; }
        .btn-info { background-color: #17a2b8; border-color: #17a2b8; }
        .btn-secondary { background-color: #6c757d; border-color: #6c757d; }
        @media (max-width: 767.98px) {
            .datatable-wrapper { padding: 1rem; }
            .datatable-wrapper .table { font-size: 0.875rem; }
            .datatable-wrapper .table thead th { font-size: 0.7rem; padding: 8px 4px; }
            .datatable-wrapper .table tbody td { padding: 8px 4px; }
            .datatable-wrapper .btn { font-size: 0.875rem; padding: 0.375rem 0.5rem; }
            .datatable-wrapper .btn-group { width: 100%; }
            .datatable-wrapper .btn-group .btn { flex: 1 1 auto; min-width: 0; }
            .dtr-details { display: block !important; }
            .dtr-details li { padding: 0.5rem 0; border-bottom: 1px solid #dee2e6; }
            .dtr-details li:last-child { border-bottom: none; }
            .dtr-title { font-weight: 600; margin-right: 0.5rem; }
        }
        .table-responsive {
            display: block;
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
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
        .dataTables_filter { display: none !important; }
        .datatable-wrapper .input-group { width: 100%; max-width: 100%; }
        @media (min-width: 768px) {
            .datatable-wrapper .input-group { max-width: 400px; }
        }
        .datatable-wrapper .form-control {
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
        }
        .datatable-wrapper .input-group-text {
            background-color: #e9ecef;
            border: 1px solid #ced4da;
            border-radius: 0.25rem 0 0 0.25rem;
            padding: 0.375rem 0.75rem;
        }
        .dataTables_paginate { transition: none !important; -webkit-transition: none !important; }
        .dataTables_paginate .paginate_button { transition: none !important; -webkit-transition: none !important; cursor: pointer !important; }
        .dataTables_paginate .paginate_button:hover { transition: none !important; -webkit-transition: none !important; }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            background: #007bff !important; color: white !important; border: 1px solid #007bff !important; transition: none !important; -webkit-transition: none !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #e9ecef !important; color: #0056b3 !important; border: 1px solid #dee2e6 !important; transition: none !important; -webkit-transition: none !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover,
        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:active {
            cursor: default !important; color: #6c757d !important; border: 1px solid transparent !important; background: transparent !important; transition: none !important; -webkit-transition: none !important;
        }
        
        /* Force 32px height with MAXIMUM specificity - Override DataTables CDN CSS */
        #{{ $id }} thead th, #{{ $id }} tbody td, #{{ $id }} tbody tr,
        table#{{ $id }} thead th, table#{{ $id }} tbody td, table#{{ $id }} tbody tr,
        .dataTables_wrapper #{{ $id }} thead th, .dataTables_wrapper #{{ $id }} tbody td, .dataTables_wrapper #{{ $id }} tbody tr,
        table.dataTable#{{ $id }} thead th, table.dataTable#{{ $id }} tbody td, table.dataTable#{{ $id }} tbody tr {
            height: 32px !important; min-height: 32px !important; max-height: 32px !important;
            padding: 4px 8px !important; line-height: 1.2 !important; box-sizing: border-box !important; margin: 0 !important; vertical-align: middle !important;
        }
        .dataTables_wrapper table.dataTable#{{ $id }} thead th,
        .dataTables_wrapper table.dataTable#{{ $id }} tbody td,
        .dataTables_wrapper table.dataTable#{{ $id }} tbody tr {
            padding: 4px 8px !important;
        }
        #{{ $id }} thead th[style], #{{ $id }} tbody td[style], #{{ $id }} tbody tr[style] {
            height: 32px !important; min-height: 32px !important; max-height: 32px !important; padding: 4px 8px !important;
        }
        .custom-colvis-dropdown {
            background: white; border: 1px solid #dee2e6; border-radius: 0.375rem; box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15); padding: 0.5rem 0; max-height: 400px; overflow-y: auto;
        }
        .custom-colvis-dropdown .dropdown-item-text { transition: background-color 0.15s ease-in-out; }
        .custom-colvis-dropdown .dropdown-item-text:hover { background-color: #f8f9fa; }
        .custom-colvis-dropdown .form-check-input { margin-top: 0; cursor: pointer; }
        .custom-colvis-dropdown label { user-select: none; }
        #{{ $id }} thead th.select-checkbox, #{{ $id }} thead th.no-sort { cursor: default !important; }
        #{{ $id }} thead th.select-checkbox input[type="checkbox"],
        #{{ $id }} thead th.select-checkbox label,
        #{{ $id }} thead th.select-checkbox { cursor: pointer !important; pointer-events: auto !important; }
        #{{ $id }} thead th.select-checkbox { position: relative; }
        #{{ $id }} thead th.select-checkbox:before, #{{ $id }} thead th.select-checkbox:after,
        #{{ $id }} thead th.no-sort:before, #{{ $id }} thead th.no-sort:after { display: none !important; }
        #{{ $id }} thead th.select-checkbox.sorting:before, #{{ $id }} thead th.select-checkbox.sorting:after,
        #{{ $id }} thead th.select-checkbox.sorting_asc:before, #{{ $id }} thead th.select-checkbox.sorting_asc:after,
        #{{ $id }} thead th.select-checkbox.sorting_desc:before, #{{ $id }} thead th.select-checkbox.sorting_desc:after {
            display: none !important;
        }
        #{{ $id }} { table-layout: auto !important; width: 100% !important; }
        #{{ $id }} thead th, #{{ $id }} tbody td { overflow: hidden !important; text-overflow: ellipsis !important; }
        #{{ $id }} thead th.select-checkbox, #{{ $id }} tbody td:first-child { width: 30px !important; min-width: 30px !important; max-width: 30px !important; }
        #{{ $id }} thead th:last-child, #{{ $id }} tbody td:last-child,
        table#{{ $id }} thead th:last-child, table#{{ $id }} tbody td:last-child,
        .datatable-wrapper #{{ $id }} thead th:last-child, .datatable-wrapper #{{ $id }} tbody td:last-child {
            width: 140px !important; min-width: 140px !important; max-width: 140px !important; white-space: nowrap !important; text-align: center !important; padding: 4px 8px !important; box-sizing: border-box !important;
        }
        #{{ $id }} tbody td:last-child .btn-icon,
        table#{{ $id }} tbody td:last-child .btn-icon,
        .datatable-wrapper #{{ $id }} tbody td:last-child .btn-icon {
            display: inline-flex !important; flex-shrink: 0 !important; margin: 0 2px !important; padding: 6px 10px !important; min-width: auto !important; max-width: none !important; width: auto !important;
        }
    </style>
@endpush

@push('scripts')
    <script>
        var skipInit_{{ $id }} = false;
        if (typeof window !== 'undefined') {
            skipInit_{{ $id }} = window['skipAutoInit_{{ $id }}'] === true;
        }

        $(document).ready(function() {
            // Check if DataTables is loaded
            const dataTablesAvailable = typeof $.fn !== 'undefined' && typeof $.fn.DataTable !== 'undefined';
            
            if (!dataTablesAvailable) {
                let waitAttempts = 0;
                const waitForDataTables = setInterval(function() {
                    waitAttempts++;
                    if (typeof $.fn !== 'undefined' && typeof $.fn.DataTable !== 'undefined') {
                        clearInterval(waitForDataTables);
                        initializeTableAfterReady_{{ $id }}();
                    } else if (waitAttempts >= 50) { 
                        clearInterval(waitForDataTables);
                        console.error('DataTables failed to load after 5 seconds');
                    }
                }, 100);
                return;
            }

            initializeTableAfterReady_{{ $id }}();
        });

        // Unique function name to prevent conflicts if multiple tables exist
        function initializeTableAfterReady_{{ $id }}() {
            // MOVED UP: Define table variables in the main scope so inner functions can access them
            const tableId = '#{{ $id }}';
            let table;
            let initializationAttempted = false; // Track if we've tried to initialize

            // Check if server-side processing is enabled
            const isServerSide = {{ $serverSide || $ajaxUrl ? 'true' : 'false' }};
            console.log("Table is processing server side: ", isServerSide);

            // Legacy skip flag check
            if (!skipInit_{{ $id }}) {
                var $tableCheck = $(tableId);
                var $wrapperCheck = $('#datatable-wrapper-{{ $id }}');
                skipInit_{{ $id }} = (window['skipAutoInit_{{ $id }}'] === true ||
                    $tableCheck.attr('data-server-side') === 'true' ||
                    $tableCheck.closest('[data-server-side="true"]').length > 0 ||
                    $wrapperCheck.attr('data-server-side') === 'true') && !isServerSide;
            }

            if (skipInit_{{ $id }} && !isServerSide) {
                return;
            }

            function isTableVisible() {
                console.log("isTableVisible function called");
                const $table = $(tableId);
                if (!$table.length) return false;
                
                const $tabPane = $table.closest('.tab-pane');
                if ($tabPane.length) {
                    // Check multiple conditions for Bootstrap 5 tab visibility
                    const hasShowClass = $tabPane.hasClass('show');
                    const isDisplayed = $tabPane.css('display') !== 'none';
                    
                    // Check if tab button is active (Bootstrap 5 uses aria-selected)
                    const tabId = $tabPane.attr('id');
                    if (tabId) {
                        const $tabButton = $('[data-bs-target="#' + tabId + '"], [href="#' + tabId + '"]');
                        const isTabActive = $tabButton.length > 0 && (
                            $tabButton.attr('aria-selected') === 'true' ||
                            $tabButton.hasClass('active')
                        );
                        
                        if (isTabActive) return true;
                    }
                    
                    // Check if element is actually in the render tree and has dimensions
                    const rect = $tabPane[0].getBoundingClientRect();
                    const hasDimensions = rect.width > 0 && rect.height > 0;
                    
                    // Return true if any of these conditions are met
                    return hasShowClass || (isDisplayed && hasDimensions);
                }
                
                // Fallback: check if table itself is visible
                return $table.is(':visible');
            }

            function initializeTable() {
                var $table = $(tableId);
                if (!$table.length) return;

                // Use the isServerSide variable from outer scope
                console.log("Table is processing server side initializeTable: ", isServerSide);

                if (!isServerSide) {
                    var $wrapper = $('#datatable-wrapper-{{ $id }}');
                    var shouldSkip = window['skipAutoInit_{{ $id }}'] === true ||
                        $table.attr('data-server-side') === 'true' ||
                        $table.closest('[data-server-side="true"]').length > 0 ||
                        ($wrapper.length > 0 && $wrapper.attr('data-server-side') === 'true');

                    if (!shouldSkip) {
                        var $tbody = $table.find('tbody');
                        if ($tbody.length > 0) {
                            var tbodyContent = $tbody.html().trim();
                            if (tbodyContent === '' || tbodyContent.replace(/<!--.*?-->/g, '').trim() === '') {
                                shouldSkip = true;
                            }
                        }
                    }

                    if (shouldSkip) return;
                }

                setTimeout(function() {
                    const isVisible = isTableVisible();
                    // Use the isServerSide variable from outer scope
                    const $tabPane = $(tableId).closest('.tab-pane');
                    const inTabPane = $tabPane.length > 0;
                    const tabPaneIsShown = inTabPane && $tabPane.hasClass('show');
                    
                    // For server-side tables in tab panes, ONLY initialize if tab is shown
                    if (isServerSide && inTabPane && !tabPaneIsShown) {
                        console.log('Server-side table in hidden tab pane - deferring initialization until tab is shown');
                        return; // Don't initialize yet, wait for tab event
                    }
                    
                    if (!isVisible && !isServerSide && !inTabPane) {
                        // Only do polling for client-side tables not in tabs
                        let attempts = 0;
                        const checkInterval = setInterval(function() {
                            attempts++;
                            const nowVisible = isTableVisible();
                            if (nowVisible || attempts >= 10) {
                                console.log(`Table visiblity:${nowVisible} after ${attempts} attempts`);
                                clearInterval(checkInterval);
                                if (nowVisible && !$.fn.DataTable.isDataTable(tableId)) {
                                    initializeDataTable();
                                }
                            }
                        }, 200);
                        return;
                    }

                    // For server-side tables (when tab is shown) or visible tables, initialize directly
                    if (!$.fn.DataTable.isDataTable(tableId)) {
                        if (isServerSide || isVisible || (inTabPane && tabPaneIsShown)) {
                            console.log('Initializing DataTable - isServerSide:', isServerSide, 'isVisible:', isVisible, 'tabPaneIsShown:', tabPaneIsShown);
                            initializeDataTable();
                        } else {
                            console.log('Skipping initialization - table not visible and not in shown tab');
                        }
                    }
                }, 100);
            }

            function updateBulkActions() {
                if (!table || typeof table.rows === 'undefined') return;
                try {
                    const checkedCount = $(tableId + ' tbody .row-checkbox:checked').length;
                    const bulkActionsDiv = $('#{{ $id }}_bulkActions');
                    const selectedCountSpan = $('#{{ $id }}_selectedCount');

                    if (checkedCount > 0) {
                        bulkActionsDiv.slideDown(200);
                        selectedCountSpan.text(checkedCount);
                    } else {
                        bulkActionsDiv.slideUp(200);
                    }
                } catch (e) {}
            }

            function updateSelectAllState() {
                if (!table || typeof table.rows === 'undefined') return;
                try {
                    const currentPageRows = table.rows({ page: 'current' }).nodes().to$();
                    const totalOnPage = currentPageRows.length;
                    const checkedOnPage = currentPageRows.find('.row-checkbox:checked').length;
                    $('#{{ $id }}_selectAll').prop('checked', totalOnPage > 0 && totalOnPage === checkedOnPage);
                } catch (e) {}
            }

            function debounce(func, wait) {
                let timeout;
                return function executedFunction(...args) {
                    const later = () => {
                        clearTimeout(timeout);
                        func(...args);
                    };
                    clearTimeout(timeout);
                    timeout = setTimeout(later, wait);
                };
            }

            const debouncedUpdateBulkActions = debounce(updateBulkActions, 50);
            const debouncedUpdateSelectAllState = debounce(updateSelectAllState, 50);
            let paginationMoved = false;

            // FIXME: This function is not being called either when dom loads or when the page length is changed.
            function updatePaginationInfo() {
                if (!table || typeof table.page === 'undefined') return;
                try {
                    const info = table.page.info();
                    console.log("info", info);
                    const text = `Showing ${info.start + 1} to ${info.end} of ${info.recordsTotal} entries`;
                    const infoElement = $('#' + '{{ $id }}' + '_info');
                    if (infoElement.length) {
                        infoElement.text(text).show();
                    }

                    if (!paginationMoved) {
                        setTimeout(function() {
                            const defaultPagination = $(tableId + '_wrapper').find('.dataTables_paginate');
                            if (defaultPagination.length && defaultPagination.parent().attr('id') !== '{{ $id }}_pagination_wrapper') {
                                defaultPagination.appendTo('#' + '{{ $id }}' + '_pagination_wrapper');
                                const paginationWrapper = $('#' + '{{ $id }}' + '_pagination_wrapper');
                                paginationWrapper.show();
                                paginationMoved = true;
                            }
                        }, 100);
                    }
                } catch (e) {}
            }

            // Remove empty rows synchronously
            $(tableId + ' tbody tr').each(function() {
                const $row = $(this);
                const $cells = $row.find('td');
                let isEmpty = true;
                $cells.each(function() {
                    const cellText = $(this).text().trim();
                    const hasCheckbox = $(this).find('.row-checkbox').length > 0;
                    const hasInput = $(this).find('input, select, textarea').length > 0;
                    if (cellText !== '' || hasCheckbox || hasInput) {
                        isEmpty = false;
                        return false;
                    }
                });
                if (isEmpty && $cells.length > 0) {
                    $row.remove();
                }
            });

            @if (!empty($filters))
                const filterContainer_{{ $id }} = $('#datatable-wrapper-{{ $id }}');
                let filterFunctions_{{ $id }} = [];
                window['filterFunctions_{{ $id }}'] = filterFunctions_{{ $id }};

                window['applyFilters_{{ $id }}'] = function() {
                    const table = window['table_{{ $id }}'] || $(tableId).DataTable();
                    if (!table || typeof table.draw !== 'function') return;

                    // Use the isServerSide variable from outer scope
                    if (isServerSide) {
                        table.draw();
                        return;
                    }

                    const currentSearchFunctions = $.fn.dataTable.ext.search || [];
                    const existingTableFilters = window['filterFunctions_{{ $id }}'] || [];
                    $.fn.dataTable.ext.search = currentSearchFunctions.filter(function(fn) {
                        return existingTableFilters.indexOf(fn) === -1;
                    });

                    filterFunctions_{{ $id }} = [];
                    window['filterFunctions_{{ $id }}'] = filterFunctions_{{ $id }};
                    table.search('').columns().search('');

                    @foreach ($filters as $filter)
                        @if ($filter['type'] === 'select')
                            @if (isset($filter['select2']) && $filter['select2'])
                                const select2Val_{{ $loop->index }} = filterContainer_{{ $id }}.find('.filter-select2[data-filter="{{ $filter['name'] }}"]').select2('val');
                                const filter{{ $loop->index }} = Array.isArray(select2Val_{{ $loop->index }}) ? select2Val_{{ $loop->index }}[0] : select2Val_{{ $loop->index }};
                            @else
                                const filter{{ $loop->index }} = filterContainer_{{ $id }}.find('.filter-select[data-filter="{{ $filter['name'] }}"]').val();
                            @endif

                            if (filter{{ $loop->index }}) {
                                @if (isset($filter['useDataAttribute']) && $filter['useDataAttribute'])
                                    const filterValue{{ $loop->index }} = filter{{ $loop->index }};
                                    const filterFn{{ $loop->index }} = function(settings, data, dataIndex) {
                                        if (settings.nTable.id !== '{{ $id }}') return true;
                                        const $row = $(table.row(dataIndex).node());
                                        const attrName = '{{ $filter['useDataAttribute'] }}';
                                        let rowValue = $row.attr('data-' + attrName);
                                        if (rowValue === null || rowValue === undefined) rowValue = '';
                                        if (rowValue === '') {
                                            const camelCaseName = attrName.replace(/-([a-z])/g, function(g) { return g[1].toUpperCase(); });
                                            rowValue = $row.data(camelCaseName) || $row.data(attrName) || '';
                                        }
                                        const normalizedRowValue = (rowValue === '-' || rowValue === '') ? '' : String(rowValue);
                                        const normalizedFilterValue = (filterValue{{ $loop->index }} === '-' || filterValue{{ $loop->index }} === '') ? '' : String(filterValue{{ $loop->index }});
                                        return normalizedRowValue === normalizedFilterValue;
                                    };
                                    filterFunctions_{{ $id }}.push(filterFn{{ $loop->index }});
                                    if (!$.fn.dataTable.ext.search) $.fn.dataTable.ext.search = [];
                                    $.fn.dataTable.ext.search.push(filterFn{{ $loop->index }});
                                @else
                                    table.column({{ $filter['column'] }}).search('^' + filter{{ $loop->index }} + '$', true, false);
                                @endif
                            }
                        @elseif ($filter['type'] === 'date')
                            const filter{{ $loop->index }} = filterContainer_{{ $id }}.find('.filter-date[data-filter="{{ $filter['name'] }}"]').val();
                            if (filter{{ $loop->index }}) {
                                @if (str_contains($filter['name'], 'from'))
                                    const filterFn{{ $loop->index }} = function(settings, data, dataIndex) {
                                        if (settings.nTable.id !== '{{ $id }}') return true;
                                        const filterVal = filter{{ $loop->index }};
                                        if (!filterVal) return true;
                                        try {
                                            const cellDate = new Date(data[{{ $filter['column'] }}]);
                                            const filterDate = new Date(filterVal);
                                            return !isNaN(cellDate.getTime()) && cellDate >= filterDate;
                                        } catch (e) { return true; }
                                    };
                                    filterFunctions_{{ $id }}.push(filterFn{{ $loop->index }});
                                    if (!$.fn.dataTable.ext.search) $.fn.dataTable.ext.search = [];
                                    $.fn.dataTable.ext.search.push(filterFn{{ $loop->index }});
                                @elseif (str_contains($filter['name'], 'to'))
                                    const filterFn{{ $loop->index }} = function(settings, data, dataIndex) {
                                        if (settings.nTable.id !== '{{ $id }}') return true;
                                        const filterVal = filter{{ $loop->index }};
                                        if (!filterVal) return true;
                                        try {
                                            const cellDate = new Date(data[{{ $filter['column'] }}]);
                                            const filterDate = new Date(filterVal);
                                            return !isNaN(cellDate.getTime()) && cellDate <= filterDate;
                                        } catch (e) { return true; }
                                    };
                                    filterFunctions_{{ $id }}.push(filterFn{{ $loop->index }});
                                    if (!$.fn.dataTable.ext.search) $.fn.dataTable.ext.search = [];
                                    $.fn.dataTable.ext.search.push(filterFn{{ $loop->index }});
                                @endif
                            }
                        @elseif ($filter['type'] === 'text')
                            const filter{{ $loop->index }} = filterContainer_{{ $id }}.find('.filter-text[data-filter="{{ $filter['name'] }}"]').val();
                            if (filter{{ $loop->index }}) {
                                @if (str_contains($filter['name'], 'min'))
                                    const filterFn{{ $loop->index }} = function(settings, data, dataIndex) {
                                        if (settings.nTable.id !== '{{ $id }}') return true;
                                        const filterVal = parseFloat(filter{{ $loop->index }}) || 0;
                                        if (!filterVal) return true;
                                        const cellValue = parseFloat(String(data[{{ $filter['column'] }}]).replace(/[^0-9.-]+/g, '')) || 0;
                                        return cellValue >= filterVal;
                                    };
                                    filterFunctions_{{ $id }}.push(filterFn{{ $loop->index }});
                                    if (!$.fn.dataTable.ext.search) $.fn.dataTable.ext.search = [];
                                    $.fn.dataTable.ext.search.push(filterFn{{ $loop->index }});
                                @else
                                    table.column({{ $filter['column'] }}).search(filter{{ $loop->index }});
                                @endif
                            }
                        @endif
                    @endforeach

                    window['filterFunctions_{{ $id }}'] = filterFunctions_{{ $id }};
                    table.draw();
                };

                $(document).off('click', '#{{ $id }}_applyFilters').on('click', '#{{ $id }}_applyFilters', function() {
                    if (typeof window['applyFilters_{{ $id }}'] === 'function') {
                        window['applyFilters_{{ $id }}']();
                    }
                });

                $(document).off('click', '#{{ $id }}_clearFilters').on('click', '#{{ $id }}_clearFilters', function() {
                    filterContainer_{{ $id }}.find('.filter-select, .filter-date, .filter-text').val('');
                    filterContainer_{{ $id }}.find('.filter-select2').each(function() {
                        $(this).val(null).trigger('change');
                    });

                    const currentSearchFunctions = $.fn.dataTable.ext.search || [];
                    const tableFilterFunctions = window['filterFunctions_{{ $id }}'] || [];
                    $.fn.dataTable.ext.search = currentSearchFunctions.filter(function(fn) {
                        return tableFilterFunctions.indexOf(fn) === -1;
                    });

                    filterFunctions_{{ $id }} = [];
                    window['filterFunctions_{{ $id }}'] = filterFunctions_{{ $id }};

                    const table = window['table_{{ $id }}'] || $(tableId).DataTable();
                    if (table && typeof table.draw === 'function') {
                        // Use the isServerSide variable from outer scope
                        if (isServerSide) {
                            table.draw();
                        } else {
                            table.search('').columns().search('').draw();
                        }
                    }
                });
            @endif

            // FIXME: This function is not being called either when dom loads or when the page length is changed.
            function initializeDataTable(forceInit = false) {
                var $table = $(tableId);
                if (!$table.length) return;
                
                // For server-side tables in hidden tabs, only initialize if forced (from tab event)
                if (isServerSide && !forceInit) {
                    const $tabPane = $table.closest('.tab-pane');
                    if ($tabPane.length && !$tabPane.hasClass('show')) {
                        console.log('initializeDataTable called but tab is hidden - skipping (use forceInit=true to override)');
                        return;
                    }
                }
                
                // Check if already initialized
                if ($.fn.DataTable.isDataTable(tableId)) {
                    console.log('DataTable already initialized, skipping');
                    return;
                }

                // Use the isServerSide variable from outer scope
                console.log("Table is processing server side initializeDataTable: ", isServerSide);
                
                var $tbody = $table.find('tbody');
                const domRowCount = $tbody.length > 0 ? $tbody.find('tr').length : 0;

                if (!isServerSide) {
                    if ($tbody.length > 0) {
                        var tbodyContent = $tbody.html().trim().replace(/<!--.*?-->/g, '').trim();
                        if ($tbody.find('tr').length === 0 || tbodyContent === '') {
                             return;
                        }
                    }
                } else {
                    // CRITICAL FIX: For server-side tables, ALWAYS clear DOM rows
                    // DataTables will switch to client-side mode if it detects rows in DOM,
                    // even when serverSide: true is set. This forces server-side AJAX mode.
                    // Also, deferLoading with DOM rows can cause client-side mode, so we clear rows.
                    if (domRowCount > 0) {
                        $tbody.empty();
                    }
                    // Store flag to remove deferLoading from config if we cleared rows
                    window['_clearDomRows_{{ $id }}'] = (domRowCount > 0);
                }

                try {
                    const ajaxUrl = @json($ajaxUrl);
                    
                    var dtConfig = {
                        dom: "<'row'<'col-sm-12'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-5'i><'col-sm-7'p>>",
                        scrollX: false,
                        scrollCollapse: false,
                        autoWidth: false,
                        fixedColumns: false,
                        columnDefs: [
                            @if ($bulkDeleteEnabled)
                                { orderable: false, searchable: false, targets: 0, className: 'select-checkbox no-export no-colvis no-sort', width: '30px' },
                            @endif
                            @foreach ($columns as $index => $column)
                                @if (isset($column['width']) || (isset($column['orderable']) && !$column['orderable']))
                                    {
                                        targets: {{ $bulkDeleteEnabled ? $index + 1 : $index }},
                                        @if (isset($column['width'])) width: '{{ $column['width'] }}', @endif
                                        @if (isset($column['orderable']) && !$column['orderable']) orderable: false, @endif
                                        @if (isset($column['searchable']) && !$column['searchable']) searchable: false, @endif
                                    },
                                @endif
                            @endforeach
                            {
                                orderable: false, searchable: false, targets: -1, className: 'text-center no-export', width: '140px', visible: true,
                                createdCell: function(td) {
                                    $(td).css({ 'width': '140px', 'min-width': '140px', 'max-width': '140px', 'white-space': 'nowrap', 'text-align': 'center' });
                                }
                            }
                        ],
                        buttons: [
                            @if ($exportEnabled)
                                { extend: 'excelHtml5', text: 'Excel', className: 'd-none', exportOptions: { columns: ':visible:not(.no-export)' } },
                                { extend: 'pdfHtml5', text: 'PDF', className: 'd-none', exportOptions: { columns: ':visible:not(.no-export)' }, orientation: 'landscape', pageSize: 'A4' },
                                { extend: 'print', text: 'Print', className: 'd-none', exportOptions: { columns: ':visible:not(.no-export)' } },
                                { extend: 'colvis', text: 'Columns', className: 'd-none', columns: ':not(.no-colvis)', collectionLayout: 'three-column', postfixButtons: ['colvisRestore'] }
                            @endif
                        ],
                        @if ($serverSide || (isset($ajaxUrl) && !empty($ajaxUrl)))
                            processing: {{ $processing ? 'true' : 'false' }},
                            serverSide: true,
                            @if (isset($deferLoading) && $deferLoading) deferLoading: {{ (int) $deferLoading }}, @endif
                            ajax: {
                                url: ajaxUrl,
                                type: 'GET',
                                dataSrc: function(json) { return json.data; },
                                @if ($ajaxData)
                                    data: function(d) {
                                        if (typeof {{ $ajaxData }} === 'function') {{ $ajaxData }}(d);
                                        return d;
                                    },
                                @endif
                                error: function(xhr, error, thrown) { console.error('DataTables AJAX error:', error); }
                            },
                            columns: [
                                @if ($bulkDeleteEnabled)
                                    { data: 0, name: 'checkbox', orderable: false, searchable: false, className: 'select-checkbox no-export no-colvis no-sort', width: '30px' },
                                @endif
                                @foreach ($columns as $index => $column)
                                    {
                                        data: {{ $bulkDeleteEnabled ? $index + 1 : $index }},
                                        name: '{{ $column['title'] ?? 'col_' . $index }}',
                                        orderable: {{ isset($column['orderable']) && !$column['orderable'] ? 'false' : 'true' }},
                                        searchable: {{ isset($column['searchable']) && !$column['searchable'] ? 'false' : 'true' }},
                                        @if (isset($column['width'])) width: '{{ $column['width'] }}', @endif
                                        render: function(data, type) {
                                            if (type === 'display' || type === 'type') return data;
                                            if (typeof data === 'string' && data.trim().startsWith('<')) {
                                                try { return $('<div>').html(data).text() || data; } catch (e) { return data; }
                                            }
                                            return data;
                                        }
                                    },
                                @endforeach
                                { data: {{ $bulkDeleteEnabled ? count($columns) + 1 : count($columns) }}, name: 'actions', orderable: false, searchable: false, className: 'text-center no-export', width: '140px' }
                            ],
                        @endif
                        pageLength: {{ $pageLength }},
                        lengthMenu: [],
                        searching: true,
                        ordering: true,
                        order: {!! json_encode($order) !!},
                        @if ($responsive)
                            responsive: { details: { type: 'column', target: 'tr' } },
                        @endif
                        language: {
                            search: '', searchPlaceholder: '{{ $searchPlaceholder }}', lengthMenu: "", info: "", infoEmpty: "", infoFiltered: "",
                            paginate: { first: "First", last: "Last", next: "Next", previous: "Previous" }
                        },
                        drawCallback: function() {
                            $(tableId + ' thead th').each(function() {
                                $(this).find('span, i, .dtr-details, .dt-orderable-asc, .dt-orderable-desc, .dt-orderable-none').remove();
                            });

                            var $actionsHeader = $(tableId + ' thead th:last-child');
                            var $actionsCells = $(tableId + ' tbody td:last-child');
                            $actionsHeader.css({ 'width': '120px', 'min-width': '120px' });
                            $actionsCells.css({ 'width': '120px', 'min-width': '120px', 'white-space': 'nowrap' });

                            try {
                                var hasCheckbox = {{ $bulkDeleteEnabled ? 'true' : 'false' }};
                                $(tableId + ' tbody tr').each(function() {
                                    var $row = $(this);
                                    var $cells = $row.children('td');
                                    $cells.each(function(idx) {
                                        var isCheckboxCol = hasCheckbox && idx === 0;
                                        var isActionsCol = idx === $cells.length - 1;
                                        if (!isCheckboxCol && !isActionsCol) {
                                            var $cell = $(this);
                                            $cell.addClass('dt-truncate');
                                            var text = $.trim($cell.text());
                                            if (text) $cell.attr('title', text);
                                        }
                                    });
                                });
                            } catch (e) {}

                            setTimeout(function() {
                                if (table && typeof table.page !== 'undefined') {
                                    debouncedUpdateBulkActions();
                                    debouncedUpdateSelectAllState();
                                    const info = table.page.info();
                                    const text = `Showing ${info.start + 1} to ${info.end} of ${info.recordsTotal} entries`;
                                    $('#' + '{{ $id }}' + '_info').text(text);
                                }
                            }, 0);

                            $(tableId + ' [data-toggle="tooltip"]').each(function() {
                                if (!$(this).data('bs.tooltip')) $(this).tooltip();
                            });
                        },
                        initComplete: function(settings) {
                            const dtInstance = $(tableId).DataTable();
                            const expectedPageLength = {{ $pageLength }};
                            const $lengthSelect = $('#{{ $id }}_length');
                            
                            if ($lengthSelect.length && dtInstance && typeof dtInstance.page === 'function') {
                                if (dtInstance.page.len() !== expectedPageLength) {
                                    dtInstance.page.len(expectedPageLength).draw(false);
                                }
                                $lengthSelect.val(expectedPageLength);
                            }

                            $(tableId + ' thead th').each(function() {
                                $(this).find('span, i, .dtr-details, .dt-orderable-asc, .dt-orderable-desc, .dt-orderable-none').remove();
                                $(this).addClass('custom-sort-header');
                            });

                            if (window['datatableInitComplete_{{ $id }}']) return;

                            @foreach ($columns as $index => $column)
                                @if (isset($column['width']))
                                    var colIndex = {{ $bulkDeleteEnabled ? $index + 1 : $index }};
                                    var $th = $(tableId + ' thead th').eq(colIndex);
                                    var $tds = $(tableId + ' tbody td:nth-child(' + (colIndex + 1) + ')');
                                    var widthValue = '{{ $column['width'] }}';
                                    if ($th.length && widthValue) {
                                        $th.css('width', widthValue);
                                        $tds.css('width', widthValue);
                                        if (widthValue.includes('px')) {
                                            $th.css('min-width', widthValue).css('max-width', widthValue);
                                            $tds.css('min-width', widthValue).css('max-width', widthValue);
                                        }
                                    }
                                @endif
                            @endforeach

                            setTimeout(function() {
                                if (table && table.columns) {
                                    table.columns.adjust().draw(false);
                                }
                            }, 100);

                            setTimeout(function() {
                                const defaultPagination = $(tableId + '_wrapper').find('.dataTables_paginate');
                                if (defaultPagination.length && defaultPagination.parent().attr('id') !== '{{ $id }}_pagination_wrapper') {
                                    defaultPagination.appendTo('#' + '{{ $id }}' + '_pagination_wrapper');
                                    $('#' + '{{ $id }}' + '_pagination_wrapper').show();
                                    paginationMoved = true;
                                }
                                if (table && typeof table.page !== 'undefined') {
                                    const info = table.page.info();
                                    $('#' + '{{ $id }}' + '_info').text(`Showing ${info.start + 1} to ${info.end} of ${info.recordsTotal} entries`);
                                }
                            }, 200);

                            window['datatableInitComplete_{{ $id }}'] = true;

                            function removeLengthMenu() {
                                const wrapperId = tableId.replace('#', '');
                                $('#' + wrapperId + '_wrapper .dataTables_length:not(.dataTables_length_bottom)').remove();
                                $('.dataTables_length:not(.dataTables_length_bottom)').filter(function() {
                                    return $(this).closest('#' + wrapperId + '_wrapper').length > 0;
                                }).remove();
                            }
                            
                            removeLengthMenu();
                            setTimeout(removeLengthMenu, 500);
                        }
                    };

                    // CRITICAL: If we cleared DOM rows, remove deferLoading to force AJAX request
                    // deferLoading is meant to work WITH DOM rows, not without them
                    if (isServerSide && window['_clearDomRows_{{ $id }}'] && dtConfig.hasOwnProperty('deferLoading')) {
                        delete dtConfig.deferLoading;
                    }
                    
                    table = $(tableId).DataTable(dtConfig);

                    if (table) {
                        window['table_{{ $id }}'] = table;
                        window['datatable_{{ $id }}'] = table;
                    }

                } catch (err) {
                    console.error('DataTable initialization failed:', err);
                }

                setTimeout(function() {
                    if (table && typeof table.page !== 'undefined') {
                        const info = table.page.info();
                        $('#' + '{{ $id }}' + '_info').text(`Showing ${info.start + 1} to ${info.end} of ${info.recordsTotal} entries`);
                        if (table.columns) table.columns.adjust();
                    }
                }, 300);

                $(document).off('change', '#{{ $id }}_length').on('change', '#{{ $id }}_length', function() {
                    const table = window['table_{{ $id }}'] || $(tableId).DataTable();
                    if (table && typeof table.page === 'function') {
                        table.page.len($(this).val()).draw();
                        setTimeout(function() {
                             if (table && typeof table.page !== 'undefined') {
                                const info = table.page.info();
                                $('#' + '{{ $id }}' + '_info').text(`Showing ${info.start + 1} to ${info.end} of ${info.recordsTotal} entries`);
                             }
                        }, 100);
                    }
                });

                var storageKey = 'datatable_colvis_{{ $id }}_v2';
                localStorage.removeItem('datatable_colvis_{{ $id }}');

                function loadColumnVisibility() {
                    try {
                        var saved = localStorage.getItem(storageKey);
                        if (saved) {
                            var columns = JSON.parse(saved);
                            if (Object.keys(columns).length === table.columns().count()) {
                                table.columns().every(function() {
                                    if (columns.hasOwnProperty(this.index())) this.visible(columns[this.index()], false);
                                });
                                table.columns.adjust().draw(false);
                            } else {
                                localStorage.removeItem(storageKey);
                            }
                        }
                    } catch (e) {}
                }

                function saveColumnVisibility() {
                    try {
                        var columns = {};
                        table.columns().every(function() { columns[this.index()] = this.visible(); });
                        localStorage.setItem(storageKey, JSON.stringify(columns));
                    } catch (e) {}
                }

                table.on('column-visibility', function() { saveColumnVisibility(); });
                setTimeout(loadColumnVisibility, 500);

                @if ($bulkDeleteEnabled)
                    $(tableId + ' thead th.select-checkbox').on('click', function(e) {
                        if (!$(e.target).is('input[type="checkbox"], label')) {
                            e.stopPropagation();
                            e.preventDefault();
                        }
                    });

                    $(tableId + ' thead th.select-checkbox input[type="checkbox"]').on('click', function(e) {
                        e.stopPropagation();
                    });

                    $(document).on('change', tableId + ' tbody .row-checkbox', function() {
                        debouncedUpdateSelectAllState();
                        debouncedUpdateBulkActions();
                    });

                    $('#{{ $id }}_bulkDeleteBtn').on('click', function() {
                        const selectedIds = [];
                        $(tableId + ' tbody .row-checkbox:checked').each(function() {
                            if ($(this).val()) selectedIds.push($(this).val());
                        });

                        if (selectedIds.length === 0) {
                            Swal.fire({ icon: 'warning', title: 'No Selection', text: 'Please select at least one row.', confirmButtonText: 'OK' });
                            return;
                        }

                        Swal.fire({
                            title: 'Are you sure?',
                            text: `Delete ${selectedIds.length} item(s)?`,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            confirmButtonText: 'Yes, delete!',
                            cancelButtonText: 'Cancel'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $.ajax({
                                    url: '{{ $bulkDeleteRoute ?? '#' }}',
                                    type: 'POST',
                                    data: { _token: '{{ csrf_token() }}', ids: selectedIds },
                                    success: function(response) {
                                        Swal.fire({ icon: 'success', title: 'Deleted!', text: response.message, timer: 1500, showConfirmButton: false })
                                            .then(() => window.location.reload());
                                    },
                                    error: function(xhr) {
                                        Swal.fire({ icon: 'error', title: 'Error!', text: xhr.responseJSON?.message || 'Failed.', confirmButtonText: 'OK' });
                                    }
                                });
                            }
                        });
                    });

                    table.on('draw', function() { debouncedUpdateSelectAllState(); });

                    $(document).on('change', '#{{ $id }}_selectAll', function() {
                        if (!table || typeof table.rows === 'undefined') return;
                        const isChecked = $(this).is(':checked');
                        $(table.rows({ page: 'current' }).nodes()).find('.row-checkbox').prop('checked', isChecked);
                        debouncedUpdateBulkActions();
                        debouncedUpdateSelectAllState();
                    });
                @endif

                $(document).off('keyup', '#{{ $id }}_search').on('keyup', '#{{ $id }}_search', function() {
                    const table = window['table_{{ $id }}'] || $(tableId).DataTable();
                    if (table && typeof table.search === 'function') table.search($(this).val()).draw();
                });

                $(document).off('keypress', '#{{ $id }}_search').on('keypress', '#{{ $id }}_search', function(e) {
                    if (e.which === 13) {
                         const table = window['table_{{ $id }}'] || $(tableId).DataTable();
                         if (table && typeof table.search === 'function') table.search($(this).val()).draw();
                    }
                });

                $(document).off('click', tableId + ' tbody tr').on('click', tableId + ' tbody tr', function(e) {
                    const $target = $(e.target);
                    if ($target.closest('a, button, input, label, .row-checkbox, .select2-container').length) return;
                    $(this).toggleClass('dt-row-active');
                });

                @if ($exportEnabled)
                    $(document).off('click', '#{{ $id }}_excel').on('click', '#{{ $id }}_excel', function() {
                        const table = window['table_{{ $id }}'] || $(tableId).DataTable();
                        if (table && typeof table.button === 'function') table.button('.buttons-excel').trigger();
                    });
                    $(document).off('click', '#{{ $id }}_pdf').on('click', '#{{ $id }}_pdf', function() {
                        const table = window['table_{{ $id }}'] || $(tableId).DataTable();
                        if (table && typeof table.button === 'function') table.button('.buttons-pdf').trigger();
                    });
                    $(document).off('click', '#{{ $id }}_print').on('click', '#{{ $id }}_print', function() {
                        const table = window['table_{{ $id }}'] || $(tableId).DataTable();
                        if (table && typeof table.button === 'function') table.button('.buttons-print').trigger();
                    });

                    $(document).off('click', '#{{ $id }}_columns').on('click', '#{{ $id }}_columns', function(e) {
                        e.preventDefault(); e.stopPropagation();
                        const table = window['table_{{ $id }}'] || $(tableId).DataTable();
                        if (!table) return;

                        var $button = $(this);
                        var buttonRect = this.getBoundingClientRect();
                        var dropdownWidth = 200;
                        $('.custom-colvis-dropdown').remove();

                        var dropdownLeft = buttonRect.right - dropdownWidth;
                        if (dropdownLeft < 10) dropdownLeft = 10;
                        
                        var dropdownTop = buttonRect.bottom + 5;
                        if (dropdownTop + 300 > window.innerHeight) dropdownTop = buttonRect.top - 300 - 5;

                        var $dropdown = $('<div class="custom-colvis-dropdown dropdown-menu show position-fixed" style="top: ' + dropdownTop + 'px; left: ' + dropdownLeft + 'px; min-width: ' + dropdownWidth + 'px; max-width: ' + dropdownWidth + 'px; z-index: 1050;"></div>');

                        table.columns().every(function() {
                            var column = this;
                            var columnHeader = $(column.header());
                            if (columnHeader.hasClass('no-colvis') || columnHeader.hasClass('select-checkbox')) return;
                            
                            var $item = $('<div class="dropdown-item-text d-flex align-items-center justify-content-between p-2" style="cursor: pointer;"></div>');
                            var $label = $('<label class="mb-0 flex-fill" style="cursor: pointer;">' + columnHeader.text().trim() + '</label>');
                            var $checkbox = $('<input type="checkbox" class="form-check-input ms-2" ' + (column.visible() ? 'checked' : '') + ' style="cursor: pointer;"></input>');
                            
                            $item.append($label).append($checkbox);
                            $dropdown.append($item);
                            
                            $checkbox.on('change', function() {
                                column.visible($(this).is(':checked'), false);
                                table.columns.adjust().draw(false);
                                saveColumnVisibility();
                            });
                            
                            $item.on('click', function(e) {
                                if (e.target.type !== 'checkbox') $checkbox.prop('checked', !$checkbox.prop('checked')).trigger('change');
                            });
                        });
                        $('body').append($dropdown);
                        $(document).on('click.customColvis', function(e) {
                            if (!$dropdown.is(e.target) && $dropdown.has(e.target).length === 0 && !$button.is(e.target)) {
                                $dropdown.remove();
                                $(document).off('click.customColvis');
                            }
                        });
                    });
                @endif

                $(tableId + ' tbody').on('click', '.delete-row', function(e) {
                    e.preventDefault();
                    const id = $(this).data('id');
                    const name = $(this).data('name') || 'this item';
                    const deleteUrl = $(this).data('url') || '{{ $deleteRoute ?? '#' }}'.replace(':id', id);

                    Swal.fire({
                        title: 'Are you sure?',
                        text: `Delete ${name}?`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        confirmButtonText: 'Yes, delete!',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: deleteUrl,
                                type: 'POST',
                                data: { _token: '{{ csrf_token() }}', _method: 'DELETE' },
                                success: function(response) {
                                    Swal.fire({ icon: 'success', title: 'Deleted!', text: response.message, timer: 2000, showConfirmButton: false });
                                    table.row($(e.target).closest('tr')).remove().draw();
                                },
                                error: function(xhr) {
                                    Swal.fire({ icon: 'error', title: 'Error!', text: xhr.responseJSON?.message || 'Failed.', confirmButtonText: 'OK' });
                                }
                            });
                        }
                    });
                });
            }

            // CRITICAL: Call the function immediately to load the table
            // But skip server-side tables in hidden tab panes (they'll initialize when tab is shown)
            const $table = $(tableId);
            const $tabPane = $table.closest('.tab-pane');
            // Use the isServerSide variable from outer scope
            const inHiddenTab = $tabPane.length > 0 && !$tabPane.hasClass('show');
            
            if (isServerSide && inHiddenTab) {
                console.log('Skipping initial table load - server-side table in hidden tab, will initialize when tab is shown');
            } else {
                initializeTable();
            }

            function initializeSelect2Filters() {
                $('.filter-select2').each(function() {
                    if (!$(this).hasClass('select2-hidden-accessible')) {
                        $(this).select2({ placeholder: $(this).find('option:first').text(), allowClear: true, width: '100%' });
                    }
                });
            }

            if (!skipInit_{{ $id }}) {
                setTimeout(initializeSelect2Filters, 500);
            }

            // Fallback initialization - but skip server-side tables in hidden tabs
            setTimeout(function() {
                if (!skipInit_{{ $id }} && !$.fn.DataTable.isDataTable(tableId)) {
                    const $table = $(tableId);
                    const $tabPane = $table.closest('.tab-pane');
                    // Use the isServerSide variable from outer scope
                    const inHiddenTab = $tabPane.length > 0 && !$tabPane.hasClass('show');
                    
                    if (isServerSide && inHiddenTab) {
                        console.log('Skipping fallback initialization - server-side table in hidden tab');
                    } else {
                        initializeTable();
                    }
                }
            }, 1000);

            // Listen for Bootstrap tab events - try both Bootstrap 4 and 5 event names
            $(document).on('shown.bs.tab', function(e) {
                console.log('Bootstrap tab event fired', e.target);
                
                // Get the target tab pane
                const targetSelector = $(e.target).attr('data-bs-target') || 
                                     $(e.target).attr('data-target') || 
                                     $(e.target).attr('href');
                const $targetTab = $(targetSelector);
                const $table = $(tableId);
                const $tableTabPane = $table.closest('.tab-pane');
                
                console.log('Target tab:', targetSelector, 'Table tab pane:', $tableTabPane.attr('id'));
                
                // Check if the shown tab contains our table
                const tabMatches = $targetTab.length && $tableTabPane.length && 
                                 ($targetTab.is($tableTabPane) || $targetTab.attr('id') === $tableTabPane.attr('id'));
                
                console.log('Tab matches:', tabMatches, 'Table already initialized:', $.fn.DataTable.isDataTable(tableId));
                
                if (tabMatches) {
                    setTimeout(function() {
                        const isDataTable = $.fn.DataTable.isDataTable(tableId);
                        console.log('Tab matches, checking table state. Is DataTable:', isDataTable);
                        
                        if (!isDataTable) {
                            console.log('Initializing DataTable from tab event - table not yet initialized');
                            // Force initialization when tab is shown - this is the correct time for server-side tables
                            initializeDataTable(true); // Pass true to force initialization even if tab was hidden
                        } else {
                            // Table exists, but for server-side tables, verify it has loaded data
                            try {
                                const table = $(tableId).DataTable();
                                const info = table.page.info();
                                console.log('Table exists. Records:', info.recordsTotal, 'Displayed:', info.recordsDisplay);
                                
                                // For server-side tables, if no data has been loaded, re-initialize
                                if (isServerSide && info.recordsTotal === 0 && info.recordsDisplay === 0) {
                                    console.log('Server-side table has no data, destroying and re-initializing');
                                    table.destroy();
                                    initializeDataTable(true); // Force re-initialization
                                } else {
                                    console.log('Table already initialized with data, adjusting columns');
                                    table.columns.adjust().draw(false);
                                }
                            } catch (err) {
                                console.error('Error accessing DataTable, re-initializing:', err);
                                // If there's an error accessing the table, destroy and re-initialize
                                try {
                                    if ($.fn.DataTable.isDataTable(tableId)) {
                                        $(tableId).DataTable().destroy();
                                    }
                                } catch (e) {}
                                initializeDataTable(true); // Force re-initialization
                            }
                        }
                        initializeSelect2Filters();
                    }, 150);
                } else {
                    console.log('Tab does not match, skipping initialization');
                }
            });
            
            // Also listen directly on the tab pane and button as fallbacks
            setTimeout(function() {
                const $table = $(tableId);
                const $tableTabPane = $table.closest('.tab-pane');
                if ($tableTabPane.length) {
                    const tabPaneId = $tableTabPane.attr('id');
                    if (tabPaneId) {
                        // Listen on the tab pane itself
                        $tableTabPane.on('shown.bs.tab', function(e) {
                            console.log('Tab pane event fired for', tabPaneId);
                            setTimeout(function() {
                                if (!$.fn.DataTable.isDataTable(tableId)) {
                                    console.log('Initializing DataTable from tab pane event');
                                    initializeDataTable(true); // Force initialization from tab event
                                }
                            }, 150);
                        });
                        
                        // Also listen for click on the tab button as a backup
                        const $tabButton = $('[data-bs-target="#' + tabPaneId + '"], [href="#' + tabPaneId + '"]');
                        if ($tabButton.length) {
                            console.log('Found tab button for table, attaching click listener');
                            $tabButton.on('click', function(e) {
                                console.log('Tab button clicked for', tabPaneId);
                                // Wait for Bootstrap to show the tab, then initialize
                                setTimeout(function() {
                                    if ($tableTabPane.hasClass('show') && !$.fn.DataTable.isDataTable(tableId)) {
                                        console.log('Initializing DataTable from tab button click');
                                        initializeDataTable(true); // Force initialization from tab button click
                                    }
                                }, 300);
                            });
                        }
                    }
                }
            }, 500);
        }
    </script>
@endpush