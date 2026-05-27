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
    'bulkReturnEnabled' => false,
    'bulkReturnRoute' => null,
    'editRoute' => null,
    'deleteRoute' => null,
    'viewRoute' => null,
    'addRoute' => null,
    'addButtonText' => 'Add New',
    'title' => null,
    'filters' => [],
    'customActions' => [],
    'actionsColumnEnabled' => true,
    'responsive' => true,
    'order' => [[0, 'desc']],
    'serverSide' => false,
    'deferLoading' => null,
    'ajaxUrl' => null,
    'ajaxData' => null,
    'processing' => true,
    'availabilityColumnIndex' => null,
    'vendorColumnIndex' => null,
    'serialColumnIndex' => null,
    'columnFilterEnabled' => true,
    'exportRoute' => null,
    'exportConfig' => [],
    'exportMaxRows' => 50000,
])

@php
    $availColIdx = $availabilityColumnIndex ?? ($bulkDeleteEnabled ? 4 : 3);
    $vendorColIdx = $vendorColumnIndex ?? ($bulkDeleteEnabled ? 5 : 4);
    $serialColIdx = $serialColumnIndex ?? ($bulkDeleteEnabled ? 3 : 2);
@endphp
@php
    $exportRouteUrl = $exportRoute ?? null;
    $exportDateColumns = $exportConfig['dateColumns'] ?? [];
    $exportDefaultScope = $exportConfig['defaultScope'] ?? 'filtered_all';
    $exportMaxRowsCap = (int) ($exportConfig['maxRows'] ?? $exportMaxRows);
    // CRITICAL: Sanitize ID for use in JavaScript variable names
    // Replace hyphens, dots, and spaces with underscores to create valid JavaScript identifiers
    // Example: "streetlightTable-11" becomes "streetlightTable_11"
    // This prevents JavaScript syntax errors like "var skipInit_streetlightTable-11 = false;" which is invalid
    $jsSafeId = str_replace(['-', '.', ' '], '_', $id);
@endphp

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
    @if ($bulkDeleteEnabled || $bulkReturnEnabled)
        <div class="mb-3" id="{{ $id }}_bulkActions" style="display: none;">
            <div
                class="alert alert-warning mb-0 d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between py-2 gap-2">
                <span><i class="mdi mdi-information"></i> <strong id="{{ $id }}_selectedCount">0</strong>
                    item(s) selected</span>
                <div class="d-flex flex-column gap-2">
                    @if ($bulkDeleteEnabled)
                        <button type="button"
                            class="btn btn-sm btn-danger d-inline-flex align-items-center justify-content-center gap-1"
                            id="{{ $id }}_bulkDeleteBtn">
                            <i class="mdi mdi-delete"></i>
                            <span>Delete Selected</span>
                        </button>
                    @endif
                    @if ($bulkReturnEnabled)
                        <button type="button"
                            class="btn btn-sm btn-warning d-inline-flex align-items-center justify-content-center gap-1"
                            id="{{ $id }}_bulkReturnBtn" style="display: none;">
                            <i class="mdi mdi-undo"></i>
                            <span>Return Selected</span>
                        </button>
                    @endif
                </div>
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
                        id="{{ $id }}_excel" title="Export to Excel"
                        @if($exportRouteUrl) data-export-route="{{ $exportRouteUrl }}" @endif>
                        <i class="mdi mdi-file-excel"></i> <span class="d-none d-sm-inline">Export</span>
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

    @if ($exportEnabled && $exportRouteUrl)
        <div class="modal fade" id="{{ $id }}_exportModal" tabindex="-1" aria-labelledby="{{ $id }}_exportModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-light text-dark border-bottom">
                        <h5 class="modal-title fw-semibold" id="{{ $id }}_exportModalLabel">Export data</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">What to export</label>
                            <div class="d-flex flex-column gap-2">
                                <label class="form-check">
                                    <input class="form-check-input" type="radio" name="{{ $id }}_export_scope"
                                        value="filtered_all" @checked($exportDefaultScope === 'filtered_all')>
                                    <span class="form-check-label">All rows matching current filters and search</span>
                                </label>
                                @if ($serverSide || $ajaxUrl)
                                    <label class="form-check">
                                        <input class="form-check-input" type="radio" name="{{ $id }}_export_scope"
                                            value="current_page">
                                        <span class="form-check-label">Current page only</span>
                                    </label>
                                    <label class="form-check">
                                        <input class="form-check-input" type="radio" name="{{ $id }}_export_scope"
                                            value="page_range">
                                        <span class="form-check-label">Page range</span>
                                    </label>
                                    <div class="row g-2 ms-3" id="{{ $id }}_exportPageRange" style="display:none;">
                                        <div class="col-6">
                                            <label class="form-label small">From page</label>
                                            <input type="number" min="1" class="form-control form-control-sm"
                                                id="{{ $id }}_export_page_from" value="1">
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small">To page</label>
                                            <input type="number" min="1" class="form-control form-control-sm"
                                                id="{{ $id }}_export_page_to" value="1">
                                        </div>
                                    </div>
                                @endif
                                <label class="form-check">
                                    <input class="form-check-input" type="radio" name="{{ $id }}_export_scope"
                                        value="row_limit">
                                    <span class="form-check-label">First N rows (after filters)</span>
                                </label>
                                <div class="ms-3" id="{{ $id }}_exportRowLimit" style="display:none;">
                                    <input type="number" min="1" max="{{ $exportMaxRowsCap }}"
                                        class="form-control form-control-sm" id="{{ $id }}_export_max_rows"
                                        value="1000" placeholder="Max rows">
                                </div>
                                @if (!empty($exportDateColumns))
                                    <label class="form-check">
                                        <input class="form-check-input" type="radio" name="{{ $id }}_export_scope"
                                            value="date_range">
                                        <span class="form-check-label">Date range (additional filter)</span>
                                    </label>
                                    <div class="ms-3" id="{{ $id }}_exportDateRange" style="display:none;">
                                        <label class="form-label small">Date field</label>
                                        <select class="form-select form-select-sm mb-2" id="{{ $id }}_export_date_column">
                                            @foreach ($exportDateColumns as $dateCol)
                                                <option value="{{ $dateCol['key'] }}">{{ $dateCol['label'] }}</option>
                                            @endforeach
                                        </select>
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <label class="form-label small">From</label>
                                                <input type="date" class="form-control form-control-sm"
                                                    id="{{ $id }}_export_date_from">
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label small">To</label>
                                                <input type="date" class="form-control form-control-sm"
                                                    id="{{ $id }}_export_date_to">
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <p class="small text-muted mb-0">Maximum {{ number_format($exportMaxRowsCap) }} rows per export.
                            Visible columns only (actions excluded).</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-success" id="{{ $id }}_exportSubmit">
                            <i class="mdi mdi-download"></i> Download Excel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

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
                    @foreach ($columns as $colIndex => $column)
                        <th {{ isset($column['width']) ? 'width=' . $column['width'] : '' }}
                            {{ isset($column['orderable']) && !$column['orderable'] ? 'data-orderable=false' : '' }}
                            {{ isset($column['searchable']) && !$column['searchable'] ? 'data-searchable=false' : '' }}
                            @if ($columnFilterEnabled && ((isset($column['columnFilter']) && $column['columnFilter']) || !(isset($column['orderable']) && !$column['orderable'])))
                                data-cf-enabled="1"
                                data-cf-type="{{ $column['type'] ?? 'text' }}"
                                data-cf-options="{{ json_encode($column['filterOptions'] ?? []) }}"
                            @endif>
                            {{ $column['title'] ?? '' }}
                        </th>
                    @endforeach
                    @if ($actionsColumnEnabled)
                        <th width="120px" class="text-center">Actions</th>
                    @endif
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
                <option value="25" {{ $pageLength == 25 ? 'selected' : '' }}>25</option>
                <option value="50" {{ $pageLength == 50 ? 'selected' : '' }}>50</option>
                <option value="100" {{ $pageLength == 100 ? 'selected' : '' }}>100</option>
                <option value="200" {{ $pageLength == 200 ? 'selected' : '' }}>200</option>
                <option value="500" {{ $pageLength == 500 ? 'selected' : '' }}>500</option>
                <option value="-1" {{ $pageLength == -1 ? 'selected' : '' }}>All</option>
            </select>
            <span class="small">entries</span>
        </div>
    </div>
</div>

{{-- Per-column filter popup (rendered outside overflow container, positioned via JS) --}}
@if ($columnFilterEnabled)
<div id="{{ $id }}_colMenu" class="cfp-popup" style="display:none;" role="dialog" aria-modal="true" aria-label="Column filter">
    <div class="cfp-popup-inner">
        <div class="cfp-sort-section">
            <button type="button" class="cfp-sort-btn" data-dir="asc">
                <i class="mdi mdi-sort-ascending me-2" aria-hidden="true"></i>Sort Ascending
            </button>
            <button type="button" class="cfp-sort-btn" data-dir="desc">
                <i class="mdi mdi-sort-descending me-2" aria-hidden="true"></i>Sort Descending
            </button>
        </div>
        <div class="cfp-divider"></div>
        <div class="cfp-filter-section">
            <div class="cfp-filter-label">Filter</div>
            <select class="cfp-type cfp-type-1" aria-label="Filter condition 1 type"></select>
            <div class="cfp-val-wrap cfp-text-wrap cfp-val-wrap-1">
                <input type="text" class="cfp-val cfp-val-1" placeholder="Value…" aria-label="Filter value 1">
            </div>
            <div class="cfp-val-wrap cfp-select-wrap cfp-val-wrap-1" style="display:none;">
                <select class="cfp-val cfp-val-1-sel" aria-label="Filter value 1"></select>
            </div>
            <div class="cfp-connector-row">
                <select class="cfp-connector" aria-label="Connector">
                    <option value="and">And</option>
                    <option value="or">Or</option>
                </select>
            </div>
            <select class="cfp-type cfp-type-2" aria-label="Filter condition 2 type"></select>
            <div class="cfp-val-wrap cfp-text-wrap cfp-val-wrap-2">
                <input type="text" class="cfp-val cfp-val-2" placeholder="Value…" aria-label="Filter value 2">
            </div>
            <div class="cfp-val-wrap cfp-select-wrap cfp-val-wrap-2" style="display:none;">
                <select class="cfp-val cfp-val-2-sel" aria-label="Filter value 2"></select>
            </div>
            <div class="cfp-actions">
                <button type="button" class="btn btn-sm btn-outline-secondary cfp-clear-btn">Clear</button>
                <button type="button" class="btn btn-sm btn-primary cfp-apply-btn">Filter</button>
            </div>
        </div>
    </div>
</div>
@endif

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

        /* ── Per-column filter: trigger button (injected post-init, absolutely positioned) ── */
        #{{ $id }} thead th[data-cf-enabled] {
            position: relative !important;
            padding-right: 20px !important;
        }
        #{{ $id }} thead th .col-menu-trigger {
            position: absolute;
            right: 2px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            padding: 2px 3px;
            color: transparent;
            font-size: 1rem;
            line-height: 1;
            cursor: pointer;
            border-radius: 3px;
            transition: color 0.12s ease, background 0.12s ease;
            z-index: 2;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 18px;
            height: 20px;
        }
        #{{ $id }} thead th[data-cf-enabled]:hover .col-menu-trigger,
        #{{ $id }} thead th.sorting_asc .col-menu-trigger,
        #{{ $id }} thead th.sorting_desc .col-menu-trigger {
            color: #adb5bd;
        }
        #{{ $id }} thead th .col-menu-trigger:hover { color: #1F3BB3 !important; background: rgba(31,59,179,0.1); }
        #{{ $id }} thead th .col-menu-trigger.has-filter { color: #1F3BB3 !important; }
        #{{ $id }} thead th .col-menu-trigger.has-filter::after {
            content: '';
            position: absolute;
            top: 1px; right: 1px;
            width: 5px; height: 5px;
            border-radius: 50%;
            background: #1F3BB3;
        }

        /* ── Per-column filter: popup ── */
        .cfp-popup {
            position: fixed;
            z-index: 99999;
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            box-shadow: 0 6px 24px rgba(0,0,0,0.13);
            min-width: 220px;
            max-width: 280px;
            font-size: 0.875rem;
            font-family: 'Manrope', sans-serif;
        }
        .cfp-popup-inner { padding: 0.25rem 0; }
        .cfp-sort-section { padding: 0.25rem 0; }
        .cfp-sort-btn {
            display: flex;
            align-items: center;
            width: 100%;
            padding: 0.5rem 1rem;
            background: none;
            border: none;
            color: #212529;
            text-align: left;
            cursor: pointer;
            font-size: 0.875rem;
            transition: background 0.12s;
        }
        .cfp-sort-btn:hover { background: rgba(31,59,179,0.06); color: #1F3BB3; }
        .cfp-divider { border-top: 1px solid #dee2e6; margin: 0.25rem 0; }
        .cfp-filter-section { padding: 0.5rem 0.875rem 0.75rem; }
        .cfp-filter-label {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #6c757d;
            margin-bottom: 0.45rem;
        }
        /* ── Scoped inputs / selects — override theme's oversized padding ── */
        .cfp-popup select,
        .cfp-popup input[type="text"] {
            display: block;
            width: 100%;
            height: 32px;
            padding: 0 0.625rem;
            font-size: 0.8125rem;
            font-family: 'Manrope', sans-serif;
            font-weight: 400;
            line-height: 1.5;
            color: #212529;
            background-color: #fff;
            border: 1px solid #ced4da;
            border-radius: 4px;
            appearance: none;
            -webkit-appearance: none;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
            box-shadow: none;
            margin-bottom: 0.35rem;
        }
        .cfp-popup select {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath fill='none' stroke='%236c757d' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.5rem center;
            background-size: 12px 9px;
            padding-right: 1.75rem;
            cursor: pointer;
        }
        .cfp-popup input[type="text"]::placeholder {
            color: #adb5bd;
            opacity: 1;
        }
        .cfp-popup select:focus,
        .cfp-popup input[type="text"]:focus {
            border-color: #1F3BB3;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(31,59,179,0.15);
        }
        .cfp-connector-row {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 0.35rem;
        }
        .cfp-connector-row .cfp-connector { width: auto; flex-shrink: 0; }
        .cfp-actions {
            display: flex;
            gap: 0.5rem;
            justify-content: flex-end;
            margin-top: 0.6rem;
        }
    </style>
@endpush

@push('scripts')
    <script>
        var skipInit_{{ $jsSafeId }} = false;
        if (typeof window !== 'undefined') {
            skipInit_{{ $jsSafeId }} = window['skipAutoInit_{{ $jsSafeId }}'] === true;
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
                        initializeTableAfterReady_{{ $jsSafeId }}();
                    } else if (waitAttempts >= 50) { 
                        clearInterval(waitForDataTables);
                        console.error('DataTables failed to load after 5 seconds');
                    }
                }, 100);
                return;
            }

            initializeTableAfterReady_{{ $jsSafeId }}();
        });

        // Unique function name to prevent conflicts if multiple tables exist
        function initializeTableAfterReady_{{ $jsSafeId }}() {
            // MOVED UP: Define table variables in the main scope so inner functions can access them
            const tableId = '#{{ $id }}';
            let table;
            let initializationAttempted = false; // Track if we've tried to initialize

            // Check if server-side processing is enabled
            const isServerSide = {{ $serverSide || $ajaxUrl ? 'true' : 'false' }};
            console.log("Table is processing server side: ", isServerSide);

            // Legacy skip flag check
            if (!skipInit_{{ $jsSafeId }}) {
                var $tableCheck = $(tableId);
                var $wrapperCheck = $('#datatable-wrapper-{{ $id }}');
                skipInit_{{ $jsSafeId }} = (window['skipAutoInit_{{ $jsSafeId }}'] === true ||
                    $tableCheck.attr('data-server-side') === 'true' ||
                    $tableCheck.closest('[data-server-side="true"]').length > 0 ||
                    $wrapperCheck.attr('data-server-side') === 'true') && !isServerSide;
            }

            if (skipInit_{{ $jsSafeId }} && !isServerSide) {
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
                    var shouldSkip = window['skipAutoInit_{{ $jsSafeId }}'] === true ||
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
                    const bulkReturnBtn = $('#{{ $id }}_bulkReturnBtn');

                    if (checkedCount > 0) {
                        bulkActionsDiv.slideDown(200);
                        selectedCountSpan.text(checkedCount);
                        
                        @if ($bulkReturnEnabled)
                            // Check if all selected items are dispatched and from same vendor
                            let allDispatched = true;
                            let vendorNames = new Set();
                            let hasInStockOrConsumed = false;
                            
                            $(tableId + ' tbody .row-checkbox:checked').each(function() {
                                const $checkbox = $(this);
                                const availability = $checkbox.data('availability') || $checkbox.closest('tr').find('td').eq({{ $availColIdx }}).text().trim();
                                const vendorName = $checkbox.data('vendor-name') || $checkbox.closest('tr').find('td').eq({{ $vendorColIdx }}).text().trim();
                                
                                if (availability === 'In Stock' || availability === 'Consumed') {
                                    allDispatched = false;
                                    hasInStockOrConsumed = true;
                                } else if (availability === 'Dispatched') {
                                    if (vendorName && vendorName !== '-') {
                                        vendorNames.add(vendorName);
                                    }
                                }
                            });
                            
                            // Show return button only if all items are dispatched and from same vendor
                            if (allDispatched && !hasInStockOrConsumed && vendorNames.size === 1) {
                                bulkReturnBtn.show();
                            } else {
                                bulkReturnBtn.hide();
                            }
                        @endif
                    } else {
                        bulkActionsDiv.slideUp(200);
                        @if ($bulkReturnEnabled)
                            bulkReturnBtn.hide();
                        @endif
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

            function updatePaginationInfo() {
                if (!table || typeof table.page === 'undefined') return;
                try {
                    const info = table.page.info();
                    const firstRecord = info.recordsDisplay === 0 ? 0 : info.start + 1;
                    const text = `Showing ${firstRecord} to ${info.end} of ${info.recordsDisplay} entries`;
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
                const filterContainer_{{ $jsSafeId }} = $('#datatable-wrapper-{{ $id }}');
                let filterFunctions_{{ $jsSafeId }} = [];
                window['filterFunctions_{{ $jsSafeId }}'] = filterFunctions_{{ $jsSafeId }};

                window['applyFilters_{{ $jsSafeId }}'] = function() {
                    const table = window['table_{{ $jsSafeId }}'] || $(tableId).DataTable();
                    if (!table || typeof table.draw !== 'function') return;

                    // Use the isServerSide variable from outer scope
                    if (isServerSide) {
                        table.draw();
                        return;
                    }

                    const currentSearchFunctions = $.fn.dataTable.ext.search || [];
                    const existingTableFilters = window['filterFunctions_{{ $jsSafeId }}'] || [];
                    $.fn.dataTable.ext.search = currentSearchFunctions.filter(function(fn) {
                        return existingTableFilters.indexOf(fn) === -1;
                    });

                    filterFunctions_{{ $jsSafeId }} = [];
                    window['filterFunctions_{{ $jsSafeId }}'] = filterFunctions_{{ $jsSafeId }};
                    table.search('').columns().search('');

                    @foreach ($filters as $filter)
                        @if ($filter['type'] === 'select')
                            @if (isset($filter['select2']) && $filter['select2'])
                                const select2Val_{{ $loop->index }} = filterContainer_{{ $jsSafeId }}.find('.filter-select2[data-filter="{{ $filter['name'] }}"]').select2('val');
                                const filter{{ $loop->index }} = Array.isArray(select2Val_{{ $loop->index }}) ? select2Val_{{ $loop->index }}[0] : select2Val_{{ $loop->index }};
                            @else
                                const filter{{ $loop->index }} = filterContainer_{{ $jsSafeId }}.find('.filter-select[data-filter="{{ $filter['name'] }}"]').val();
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
                                    filterFunctions_{{ $jsSafeId }}.push(filterFn{{ $loop->index }});
                                    if (!$.fn.dataTable.ext.search) $.fn.dataTable.ext.search = [];
                                    $.fn.dataTable.ext.search.push(filterFn{{ $loop->index }});
                                @else
                                    table.column({{ $filter['column'] }}).search('^' + filter{{ $loop->index }} + '$', true, false);
                                @endif
                            }
                        @elseif ($filter['type'] === 'date')
                            const filter{{ $loop->index }} = filterContainer_{{ $jsSafeId }}.find('.filter-date[data-filter="{{ $filter['name'] }}"]').val();
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
                                    filterFunctions_{{ $jsSafeId }}.push(filterFn{{ $loop->index }});
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
                                    filterFunctions_{{ $jsSafeId }}.push(filterFn{{ $loop->index }});
                                    if (!$.fn.dataTable.ext.search) $.fn.dataTable.ext.search = [];
                                    $.fn.dataTable.ext.search.push(filterFn{{ $loop->index }});
                                @endif
                            }
                        @elseif ($filter['type'] === 'text')
                            const filter{{ $loop->index }} = filterContainer_{{ $jsSafeId }}.find('.filter-text[data-filter="{{ $filter['name'] }}"]').val();
                            if (filter{{ $loop->index }}) {
                                @if (str_contains($filter['name'], 'min'))
                                    const filterFn{{ $loop->index }} = function(settings, data, dataIndex) {
                                        if (settings.nTable.id !== '{{ $id }}') return true;
                                        const filterVal = parseFloat(filter{{ $loop->index }}) || 0;
                                        if (!filterVal) return true;
                                        const cellValue = parseFloat(String(data[{{ $filter['column'] }}]).replace(/[^0-9.-]+/g, '')) || 0;
                                        return cellValue >= filterVal;
                                    };
                                    filterFunctions_{{ $jsSafeId }}.push(filterFn{{ $loop->index }});
                                    if (!$.fn.dataTable.ext.search) $.fn.dataTable.ext.search = [];
                                    $.fn.dataTable.ext.search.push(filterFn{{ $loop->index }});
                                @else
                                    table.column({{ $filter['column'] }}).search(filter{{ $loop->index }});
                                @endif
                            }
                        @endif
                    @endforeach

                    window['filterFunctions_{{ $jsSafeId }}'] = filterFunctions_{{ $jsSafeId }};
                    table.draw();
                };

                $(document).off('click', '#{{ $id }}_applyFilters').on('click', '#{{ $id }}_applyFilters', function() {
                    if (typeof window['applyFilters_{{ $jsSafeId }}'] === 'function') {
                        window['applyFilters_{{ $jsSafeId }}']();
                    }
                });

                $(document).off('click', '#{{ $id }}_clearFilters').on('click', '#{{ $id }}_clearFilters', function() {
                    filterContainer_{{ $jsSafeId }}.find('.filter-select, .filter-date, .filter-text').val('');
                    filterContainer_{{ $jsSafeId }}.find('.filter-select2').each(function() {
                        $(this).val(null).trigger('change');
                    });

                    const currentSearchFunctions = $.fn.dataTable.ext.search || [];
                    const tableFilterFunctions = window['filterFunctions_{{ $jsSafeId }}'] || [];
                    $.fn.dataTable.ext.search = currentSearchFunctions.filter(function(fn) {
                        return tableFilterFunctions.indexOf(fn) === -1;
                    });

                    filterFunctions_{{ $jsSafeId }} = [];
                    window['filterFunctions_{{ $jsSafeId }}'] = filterFunctions_{{ $jsSafeId }};

                    const table = window['table_{{ $jsSafeId }}'] || $(tableId).DataTable();
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
                    window['_clearDomRows_{{ $jsSafeId }}'] = (domRowCount > 0);
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
                            @if ($actionsColumnEnabled)
                            {
                                orderable: false, searchable: false, targets: -1, className: 'text-center no-export', width: '140px', visible: true,
                                createdCell: function(td) {
                                    $(td).css({ 'width': '140px', 'min-width': '140px', 'max-width': '140px', 'white-space': 'nowrap', 'text-align': 'center' });
                                }
                            }
                            @endif
                        ],
                        buttons: [
                            @if ($exportEnabled)
                                { extend: 'excelHtml5', text: 'Excel', className: 'd-none', exportOptions: { columns: ':visible:not(.no-export)', modifier: { search: 'applied', page: 'all', order: 'applied' } } },
                                { extend: 'pdfHtml5', text: 'PDF', className: 'd-none', exportOptions: { columns: ':visible:not(.no-export)', modifier: { search: 'applied', page: 'all', order: 'applied' } }, orientation: 'landscape', pageSize: 'A4' },
                                { extend: 'print', text: 'Print', className: 'd-none', exportOptions: { columns: ':visible:not(.no-export)', modifier: { search: 'applied', page: 'all', order: 'applied' } } },
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
                                @if ($actionsColumnEnabled)
                                { data: {{ $bulkDeleteEnabled ? count($columns) + 1 : count($columns) }}, name: 'actions', orderable: false, searchable: false, className: 'text-center no-export', width: '140px' }
                                @endif
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

                            @if ($actionsColumnEnabled)
                            var $actionsHeader = $(tableId + ' thead th:last-child');
                            var $actionsCells = $(tableId + ' tbody td:last-child');
                            $actionsHeader.css({ 'width': '120px', 'min-width': '120px' });
                            $actionsCells.css({ 'width': '120px', 'min-width': '120px', 'white-space': 'nowrap' });
                            @endif

                            try {
                                var hasCheckbox = {{ $bulkDeleteEnabled ? 'true' : 'false' }};
                                var actionsEnabled = {{ $actionsColumnEnabled ? 'true' : 'false' }};
                                $(tableId + ' tbody tr').each(function() {
                                    var $row = $(this);
                                    var $cells = $row.children('td');
                                    $cells.each(function(idx) {
                                        var isCheckboxCol = hasCheckbox && idx === 0;
                                        var isActionsCol = actionsEnabled && idx === $cells.length - 1;
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
                                    updatePaginationInfo();
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

                            if (window['datatableInitComplete_{{ $jsSafeId }}']) return;

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
                                updatePaginationInfo();
                            }, 200);

                            window['datatableInitComplete_{{ $jsSafeId }}'] = true;

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
                    if (isServerSide && window['_clearDomRows_{{ $jsSafeId }}'] && dtConfig.hasOwnProperty('deferLoading')) {
                        delete dtConfig.deferLoading;
                    }
                    
                    table = $(tableId).DataTable(dtConfig);

                    if (table) {
                        window['table_{{ $jsSafeId }}'] = table;
                        window['datatable_{{ $jsSafeId }}'] = table;
                        table.on('draw page length search', updatePaginationInfo);
                    }

                } catch (err) {
                    console.error('DataTable initialization failed:', err);
                }

                setTimeout(function() {
                    if (table && typeof table.page !== 'undefined') {
                        updatePaginationInfo();
                        if (table.columns) table.columns.adjust();
                    }
                }, 300);

                $(document).off('change', '#{{ $id }}_length').on('change', '#{{ $id }}_length', function() {
                    const table = window['table_{{ $jsSafeId }}'] || $(tableId).DataTable();
                    if (table && typeof table.page === 'function') {
                        table.page.len($(this).val()).draw();
                        setTimeout(function() {
                             if (table && typeof table.page !== 'undefined') {
                                updatePaginationInfo();
                             }
                        }, 100);
                    }
                });

                var storageKey = 'datatable_colvis_{{ $jsSafeId }}_v2';
                localStorage.removeItem('datatable_colvis_{{ $jsSafeId }}');

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

                @if ($bulkReturnEnabled)
                    $('#{{ $id }}_bulkReturnBtn').on('click', function() {
                        const serialNumbers = [];
                        const vendorNames = new Set();
                        let allDispatched = true;
                        
                        $(tableId + ' tbody .row-checkbox:checked').each(function() {
                            const $checkbox = $(this);
                            const $row = $checkbox.closest('tr');
                            const availability = $checkbox.data('availability') || $row.find('td').eq({{ $availColIdx }}).text().trim();
                            const vendorName = $checkbox.data('vendor-name') || $row.find('td').eq({{ $vendorColIdx }}).text().trim();
                            const serialNumber = $checkbox.data('serial-number') || $row.find('td').eq({{ $serialColIdx }}).text().trim();
                            
                            if (availability !== 'Dispatched') {
                                allDispatched = false;
                            } else {
                                if (serialNumber) {
                                    serialNumbers.push(serialNumber);
                                    if (vendorName && vendorName !== '-') {
                                        vendorNames.add(vendorName);
                                    }
                                }
                            }
                        });

                        if (serialNumbers.length === 0) {
                            Swal.fire({ 
                                icon: 'warning', 
                                title: 'Invalid Selection', 
                                text: 'Please select only dispatched items that are in vendor custody.', 
                                confirmButtonText: 'OK' 
                            });
                            return;
                        }

                        if (!allDispatched) {
                            Swal.fire({ 
                                icon: 'error', 
                                title: 'Invalid Selection', 
                                text: 'You can only return items that are dispatched (in vendor custody). Items that are in stock or consumed cannot be returned.', 
                                confirmButtonText: 'OK' 
                            });
                            return;
                        }

                        if (vendorNames.size > 1) {
                            Swal.fire({ 
                                icon: 'error', 
                                title: 'Multiple Vendors', 
                                text: 'You can only return items from one vendor at a time. Please select items from a single vendor.', 
                                confirmButtonText: 'OK' 
                            });
                            return;
                        }

                        const vendorName = Array.from(vendorNames)[0] || 'Unknown Vendor';
                        
                        Swal.fire({
                            title: 'Are you sure?',
                            text: `Return ${serialNumbers.length} item(s) from ${vendorName}?`,
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonColor: '#ffc107',
                            confirmButtonText: 'Yes, return!',
                            cancelButtonText: 'Cancel'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $.ajax({
                                    url: '{{ $bulkReturnRoute ?? '#' }}',
                                    type: 'POST',
                                    data: { 
                                        _token: '{{ csrf_token() }}', 
                                        serial_numbers: serialNumbers 
                                    },
                                    success: function(response) {
                                        Swal.fire({ 
                                            icon: 'success', 
                                            title: 'Returned!', 
                                            text: response.message || `${serialNumbers.length} item(s) returned successfully`, 
                                            timer: 1500, 
                                            showConfirmButton: false 
                                        }).then(() => {
                                            if (table && typeof table.draw === 'function') {
                                                table.draw(false);
                                            } else {
                                                window.location.reload();
                                            }
                                        });
                                    },
                                    error: function(xhr) {
                                        Swal.fire({ 
                                            icon: 'error', 
                                            title: 'Error!', 
                                            text: xhr.responseJSON?.message || 'Failed to return items.', 
                                            confirmButtonText: 'OK' 
                                        });
                                    }
                                });
                            }
                        });
                    });
                @endif

                $(document).off('keyup', '#{{ $id }}_search').on('keyup', '#{{ $id }}_search', function() {
                    const table = window['table_{{ $jsSafeId }}'] || $(tableId).DataTable();
                    if (table && typeof table.search === 'function') table.search($(this).val()).draw();
                });

                $(document).off('keypress', '#{{ $id }}_search').on('keypress', '#{{ $id }}_search', function(e) {
                    if (e.which === 13) {
                         const table = window['table_{{ $jsSafeId }}'] || $(tableId).DataTable();
                         if (table && typeof table.search === 'function') table.search($(this).val()).draw();
                    }
                });

                $(document).off('click', tableId + ' tbody tr').on('click', tableId + ' tbody tr', function(e) {
                    const $target = $(e.target);
                    if ($target.closest('a, button, input, label, .row-checkbox, .select2-container').length) return;
                    $(this).toggleClass('dt-row-active');
                });

                @if ($exportEnabled)
                    const exportRoute_{{ $jsSafeId }} = @json($exportRouteUrl);
                    const exportMaxRows_{{ $jsSafeId }} = {{ $exportMaxRowsCap }};

                    function syncExportScopePanels_{{ $jsSafeId }}() {
                        const scope = $('input[name="{{ $id }}_export_scope"]:checked').val();
                        $('#{{ $id }}_exportPageRange').toggle(scope === 'page_range');
                        $('#{{ $id }}_exportRowLimit').toggle(scope === 'row_limit');
                        $('#{{ $id }}_exportDateRange').toggle(scope === 'date_range');
                    }

                    $('input[name="{{ $id }}_export_scope"]').on('change', syncExportScopePanels_{{ $jsSafeId }});
                    syncExportScopePanels_{{ $jsSafeId }}();

                    function collectDataTableExportParams_{{ $jsSafeId }}() {
                        const table = window['table_{{ $jsSafeId }}'] || ($(tableId).length && $.fn.DataTable.isDataTable(tableId) ? $(tableId).DataTable() : null);
                        const params = new URLSearchParams(window.location.search);
                        const scope = $('input[name="{{ $id }}_export_scope"]:checked').val() || 'filtered_all';

                        params.set('export_scope', scope);
                        params.set('format', 'xlsx');

                        if (scope === 'page_range') {
                            params.set('page_from', $('#{{ $id }}_export_page_from').val() || '1');
                            params.set('page_to', $('#{{ $id }}_export_page_to').val() || '1');
                        }
                        if (scope === 'row_limit') {
                            params.set('max_rows', $('#{{ $id }}_export_max_rows').val() || '1000');
                        }
                        if (scope === 'date_range') {
                            const dateCol = $('#{{ $id }}_export_date_column').val();
                            if (dateCol) params.set('date_column', dateCol);
                            const df = $('#{{ $id }}_export_date_from').val();
                            const dt = $('#{{ $id }}_export_date_to').val();
                            if (df) params.set('date_from', df);
                            if (dt) params.set('date_to', dt);
                        }

                        if (table && isServerSide) {
                            const settings = table.settings()[0];
                            const ajaxData = settings.oAjaxData || {};
                            if (ajaxData.search && ajaxData.search.value) {
                                params.set('search[value]', ajaxData.search.value);
                            }
                            if (ajaxData.order) {
                                ajaxData.order.forEach(function(ord, idx) {
                                    params.set('order[' + idx + '][column]', ord.column);
                                    params.set('order[' + idx + '][dir]', ord.dir);
                                });
                            }
                            if (ajaxData.column_filters) {
                                params.set('column_filters', ajaxData.column_filters);
                            }
                            params.set('length', ajaxData.length || {{ $pageLength }});

                            if (scope === 'current_page') {
                                const info = table.page.info();
                                params.set('start', info.start);
                                params.set('length', info.length);
                            } else if (scope === 'filtered_all') {
                                params.delete('start');
                            }
                        } else if (table) {
                            const searchVal = $('#{{ $id }}_search').val() || table.search();
                            if (searchVal) params.set('search[value]', searchVal);
                        }

                        @if (!empty($ajaxData))
                        try {
                            const ajaxPayload = {
                                search: { value: params.get('search[value]') || '' },
                                length: parseInt(params.get('length') || '{{ $pageLength }}', 10),
                            };
                            if (typeof {{ $ajaxData }} === 'function') {
                                {{ $ajaxData }}(ajaxPayload);
                                Object.keys(ajaxPayload).forEach(function(key) {
                                    if (['search', 'order', 'columns', 'draw', 'start', 'column_filters'].indexOf(key) >= 0) {
                                        return;
                                    }
                                    if (ajaxPayload[key] !== undefined && ajaxPayload[key] !== '') {
                                        params.set(key, ajaxPayload[key]);
                                    }
                                });
                            }
                        } catch (exportAjaxErr) {
                            console.warn('Export ajaxData callback failed', exportAjaxErr);
                        }
                        @endif

                        return params;
                    }

                    $(document).off('click', '#{{ $id }}_exportSubmit').on('click', '#{{ $id }}_exportSubmit', function() {
                        if (!exportRoute_{{ $jsSafeId }}) return;
                        const params = collectDataTableExportParams_{{ $jsSafeId }}();
                        const url = exportRoute_{{ $jsSafeId }} + (exportRoute_{{ $jsSafeId }}.indexOf('?') >= 0 ? '&' : '?') + params.toString();
                        window.location.href = url;
                    });

                    $(document).off('click', '#{{ $id }}_excel').on('click', '#{{ $id }}_excel', function(e) {
                        if (exportRoute_{{ $jsSafeId }}) {
                            e.preventDefault();
                            const modalEl = document.getElementById('{{ $id }}_exportModal');
                            if (modalEl && typeof bootstrap !== 'undefined') {
                                bootstrap.Modal.getOrCreateInstance(modalEl).show();
                            }
                            return;
                        }
                        const table = window['table_{{ $jsSafeId }}'] || $(tableId).DataTable();
                        if (table && typeof table.button === 'function') table.button('.buttons-excel').trigger();
                    });
                    $(document).off('click', '#{{ $id }}_pdf').on('click', '#{{ $id }}_pdf', function() {
                        const table = window['table_{{ $jsSafeId }}'] || $(tableId).DataTable();
                        if (table && typeof table.button === 'function') table.button('.buttons-pdf').trigger();
                    });
                    $(document).off('click', '#{{ $id }}_print').on('click', '#{{ $id }}_print', function() {
                        const table = window['table_{{ $jsSafeId }}'] || $(tableId).DataTable();
                        if (table && typeof table.button === 'function') table.button('.buttons-print').trigger();
                    });

                    $(document).off('click', '#{{ $id }}_columns').on('click', '#{{ $id }}_columns', function(e) {
                        e.preventDefault(); e.stopPropagation();
                        const table = window['table_{{ $jsSafeId }}'] || $(tableId).DataTable();
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

            if (!skipInit_{{ $jsSafeId }}) {
                setTimeout(initializeSelect2Filters, 500);
            }

            // Fallback initialization - but skip server-side tables in hidden tabs
            setTimeout(function() {
                if (!skipInit_{{ $jsSafeId }} && !$.fn.DataTable.isDataTable(tableId)) {
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

        @if ($columnFilterEnabled)
        /* ── SAP-style per-column filter ───────────────────────────────────── */
        (function() {
            var TABLE_ID   = '{{ $id }}';
            var JS_SAFE_ID = '{{ $jsSafeId }}';
            var IS_SERVER  = {{ $serverSide ? 'true' : 'false' }};
            var menuEl     = document.getElementById('{{ $id }}_colMenu');
            if (!menuEl) return;

            var activeColFilters = {};   // { colIdx: {type1,val1,connector,type2,val2} }
            var openColIdx       = null;

            /* ── Inject trigger buttons into header cells after DT init ── */
            function injectTriggers() {
                document.querySelectorAll('#{{ $id }} thead tr th[data-cf-enabled]').forEach(function(th) {
                    // Remove any stale empty buttons (left by a previous aborted inject)
                    th.querySelectorAll('.col-menu-trigger').forEach(function(old) { old.remove(); });

                    // Compute real DT column index (th position in row)
                    var allThs = th.closest('tr').querySelectorAll('th');
                    var colIdx = Array.prototype.indexOf.call(allThs, th);

                    var icon = document.createElement('i');
                    icon.className = 'mdi mdi-dots-vertical';

                    var btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'col-menu-trigger';
                    btn.setAttribute('data-table-id', TABLE_ID);
                    btn.setAttribute('data-col-idx', colIdx);
                    btn.setAttribute('data-col-type', th.getAttribute('data-cf-type') || 'text');
                    btn.setAttribute('data-col-options', th.getAttribute('data-cf-options') || '[]');
                    btn.setAttribute('aria-label', 'Column options');
                    btn.setAttribute('tabindex', '-1');
                    btn.appendChild(icon);
                    // Restore active-filter indicator if this column has a filter
                    if (activeColFilters[colIdx]) {
                        btn.classList.add('has-filter');
                    }
                    th.appendChild(btn);
                });
            }

            // Run once immediately (handles non-deferred init) and re-run after any draw
            // so buttons survive DataTables' header adjustments
            setTimeout(injectTriggers, 50);
            $(document).on('draw.dt', '#{{ $id }}', function() {
                setTimeout(injectTriggers, 0);
            });

            var OPERATORS = {
                text:   ['contains','starts_with','ends_with','equals','not_equals','gt','gte','lt','lte','is_empty','is_not_empty'],
                number: ['eq','neq','gt','gte','lt','lte'],
                date:   ['date_equals','before','after'],
                select: ['is','is_not'],
            };
            var OPERATOR_LABELS = {
                contains:'Contains', starts_with:'Starts with', ends_with:'Ends with',
                equals:'= Equals', not_equals:'≠ Not equals',
                gt:'> Greater than', gte:'≥ Greater than or equal',
                lt:'< Less than', lte:'≤ Less than or equal',
                is_empty:'Is empty', is_not_empty:'Is not empty',
                eq:'= Equals', neq:'≠ Not equals',
                date_equals:'Equals', before:'Before', after:'After',
                is:'Is', is_not:'Is not',
            };

            function evalCondition(cellRaw, op, value) {
                var cell = String(cellRaw || '').toLowerCase().trim();
                var val  = String(value  || '').toLowerCase().trim();
                if (op === 'is_empty')     return cell === '';
                if (op === 'is_not_empty') return cell !== '';
                if (!val) return true;
                switch (op) {
                    case 'contains':    return cell.indexOf(val) !== -1;
                    case 'starts_with': return cell.indexOf(val) === 0;
                    case 'ends_with':   return cell.slice(-val.length) === val;
                    case 'equals': case 'date_equals': case 'is': return cell === val;
                    case 'not_equals':  case 'is_not':             return cell !== val;
                    case 'gt':  return parseFloat(cell) > parseFloat(val);
                    case 'gte': return parseFloat(cell) >= parseFloat(val);
                    case 'lt':  return parseFloat(cell) < parseFloat(val);
                    case 'lte': return parseFloat(cell) <= parseFloat(val);
                    case 'before': return new Date(cell) < new Date(val);
                    case 'after':  return new Date(cell) > new Date(val);
                }
                return true;
            }

            /* ── Register client-side search function ── */
            if (!IS_SERVER) {
                $.fn.dataTable.ext.search.push(function(settings, data) {
                    if (settings.nTable.id !== TABLE_ID) return true;
                    for (var idx in activeColFilters) {
                        if (!activeColFilters.hasOwnProperty(idx)) continue;
                        var f    = activeColFilters[idx];
                        var cell = data[idx] !== undefined ? data[idx] : '';
                        var m1   = evalCondition(cell, f.type1, f.val1);
                        var hasSecond = f.val2 || f.type2 === 'is_empty' || f.type2 === 'is_not_empty';
                        if (!hasSecond) {
                            if (!m1) return false;
                        } else {
                            var m2 = evalCondition(cell, f.type2, f.val2);
                            var pass = f.connector === 'or' ? (m1 || m2) : (m1 && m2);
                            if (!pass) return false;
                        }
                    }
                    return true;
                });
            }

            /* ── Server-side: inject column_filters into AJAX request ── */
            if (IS_SERVER) {
                $(document).on('preXhr.dt', '#{{ $id }}', function(e, settings, data) {
                    data.column_filters = JSON.stringify(activeColFilters);
                });
            }

            /* ── Build operator <select> ── */
            function buildTypeSelect(sel, colType, selectedVal) {
                var ops = OPERATORS[colType] || OPERATORS.text;
                sel.innerHTML = '';
                for (var i = 0; i < ops.length; i++) {
                    var opt = document.createElement('option');
                    opt.value       = ops[i];
                    opt.textContent = OPERATOR_LABELS[ops[i]] || ops[i];
                    if (ops[i] === selectedVal) opt.selected = true;
                    sel.appendChild(opt);
                }
            }

            /* ── Show/hide text vs select value widget ── */
            function setValueWidget(condNum, colType, colOptions) {
                var textWrap = menuEl.querySelector('.cfp-text-wrap.cfp-val-wrap-' + condNum);
                var selWrap  = menuEl.querySelector('.cfp-select-wrap.cfp-val-wrap-' + condNum);
                var selEl    = menuEl.querySelector('.cfp-val-' + condNum + '-sel');
                if (colType === 'select' && colOptions && colOptions.length) {
                    textWrap.style.display = 'none';
                    selWrap.style.display  = '';
                    selEl.innerHTML = '<option value="">Any</option>';
                    for (var i = 0; i < colOptions.length; i++) {
                        var o = document.createElement('option');
                        o.value = o.textContent = colOptions[i];
                        selEl.appendChild(o);
                    }
                } else {
                    textWrap.style.display = '';
                    selWrap.style.display  = 'none';
                }
            }

            /* ── Position popup below trigger ── */
            function positionMenu(trigger) {
                var rect = trigger.getBoundingClientRect();
                menuEl.style.left = Math.min(rect.left, window.innerWidth - 290) + 'px';
                menuEl.style.top  = (rect.bottom + 4 + window.scrollY) + 'px';
            }

            /* ── Click: open popup ── */
            document.addEventListener('click', function(e) {
                var trigger = e.target.closest
                    ? e.target.closest('.col-menu-trigger[data-table-id="{{ $id }}"]')
                    : null;

                /* close on outside click */
                if (!trigger) {
                    if (menuEl && !menuEl.contains(e.target)) {
                        menuEl.style.display = 'none';
                        openColIdx = null;
                    }
                    return;
                }

                e.stopPropagation();
                var colIdx  = parseInt(trigger.dataset.colIdx, 10);
                var colType = trigger.dataset.colType || 'text';
                var colOpts = JSON.parse(trigger.dataset.colOptions || '[]');

                /* toggle closed if same trigger */
                if (openColIdx === colIdx && menuEl.style.display !== 'none') {
                    menuEl.style.display = 'none';
                    openColIdx = null;
                    return;
                }
                openColIdx = colIdx;

                var existing = activeColFilters[colIdx] || {};
                var defaultOp = (OPERATORS[colType] || OPERATORS.text)[0];

                buildTypeSelect(menuEl.querySelector('.cfp-type-1'), colType, existing.type1 || defaultOp);
                buildTypeSelect(menuEl.querySelector('.cfp-type-2'), colType, existing.type2 || defaultOp);
                setValueWidget(1, colType, colOpts);
                setValueWidget(2, colType, colOpts);

                menuEl.querySelector('.cfp-val-1').value     = existing.val1 || '';
                menuEl.querySelector('.cfp-val-2').value     = existing.val2 || '';
                menuEl.querySelector('.cfp-connector').value = existing.connector || 'and';
                if (existing.val1Sel !== undefined) menuEl.querySelector('.cfp-val-1-sel').value = existing.val1Sel;
                if (existing.val2Sel !== undefined) menuEl.querySelector('.cfp-val-2-sel').value = existing.val2Sel;

                menuEl.dataset.activeColIdx  = colIdx;
                menuEl.dataset.activeColType = colType;

                menuEl.style.position = 'fixed';
                menuEl.style.display  = '';
                var rect = trigger.getBoundingClientRect();
                menuEl.style.left = Math.min(rect.left, window.innerWidth - 290) + 'px';
                menuEl.style.top  = (rect.bottom + 4) + 'px';
            }, true);

            /* ── Popup button clicks ── */
            menuEl.addEventListener('click', function(e) {
                /* Sort */
                var sortBtn = e.target.closest ? e.target.closest('.cfp-sort-btn') : null;
                if (sortBtn) {
                    var dir = sortBtn.dataset.dir;
                    var idx = parseInt(menuEl.dataset.activeColIdx, 10);
                    var dt  = window['table_' + JS_SAFE_ID];
                    if (dt) dt.column(idx).order(dir).draw();
                    menuEl.style.display = 'none'; openColIdx = null;
                    return;
                }

                /* Apply */
                var applyBtn = e.target.closest ? e.target.closest('.cfp-apply-btn') : null;
                if (applyBtn) {
                    var idx     = parseInt(menuEl.dataset.activeColIdx, 10);
                    var colType = menuEl.dataset.activeColType || 'text';
                    var isSel   = colType === 'select';
                    var val1    = isSel ? menuEl.querySelector('.cfp-val-1-sel').value
                                       : menuEl.querySelector('.cfp-val-1').value.trim();
                    var val2    = isSel ? menuEl.querySelector('.cfp-val-2-sel').value
                                       : menuEl.querySelector('.cfp-val-2').value.trim();
                    var connector = menuEl.querySelector('.cfp-connector').value;
                    var type1   = menuEl.querySelector('.cfp-type-1').value;
                    var type2   = menuEl.querySelector('.cfp-type-2').value;

                    var hasFilter = val1 || type1 === 'is_empty' || type1 === 'is_not_empty';
                    if (hasFilter) {
                        activeColFilters[idx] = { type1: type1, val1: val1, connector: connector, type2: type2, val2: val2 };
                    } else {
                        delete activeColFilters[idx];
                    }

                    var trig = document.querySelector('.col-menu-trigger[data-table-id="{{ $id }}"][data-col-idx="' + idx + '"]');
                    if (trig) trig.classList.toggle('has-filter', !!activeColFilters[idx]);

                    menuEl.style.display = 'none'; openColIdx = null;

                    var dt = window['table_' + JS_SAFE_ID];
                    if (dt) {
                        if (IS_SERVER) dt.ajax.reload(null, false);
                        else dt.draw();
                    }
                    return;
                }

                /* Clear */
                var clearBtn = e.target.closest ? e.target.closest('.cfp-clear-btn') : null;
                if (clearBtn) {
                    var idx = parseInt(menuEl.dataset.activeColIdx, 10);
                    delete activeColFilters[idx];
                    menuEl.querySelector('.cfp-val-1').value = '';
                    menuEl.querySelector('.cfp-val-2').value = '';
                    var trig = document.querySelector('.col-menu-trigger[data-table-id="{{ $id }}"][data-col-idx="' + idx + '"]');
                    if (trig) trig.classList.remove('has-filter');
                    menuEl.style.display = 'none'; openColIdx = null;
                    var dt = window['table_' + JS_SAFE_ID];
                    if (dt) {
                        if (IS_SERVER) dt.ajax.reload(null, false);
                        else dt.draw();
                    }
                }
            });

            /* ── Reposition on scroll ── */
            window.addEventListener('scroll', function() {
                if (menuEl.style.display !== 'none' && openColIdx !== null) {
                    var trig = document.querySelector('.col-menu-trigger[data-table-id="{{ $id }}"][data-col-idx="' + openColIdx + '"]');
                    if (trig) {
                        var rect = trig.getBoundingClientRect();
                        menuEl.style.left = Math.min(rect.left, window.innerWidth - 290) + 'px';
                        menuEl.style.top  = (rect.bottom + 4) + 'px';
                    }
                }
            }, true);

            window.addEventListener('resize', function() {
                menuEl.style.display = 'none';
                openColIdx = null;
            });

        })();
        @endif

    </script>
@endpush
