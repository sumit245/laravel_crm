@extends('layouts.main')

@section('title', $settingsPageTitle ?? 'Settings')

@section('content')
@php
    $activeSection = $activeSection ?? 'organization';
    $sectionIcons = [
        'organization'   => 'bi-building',
        'billing'        => 'bi-receipt',
        'vendor-earnings'=> 'bi-currency-rupee',
        'permissions'    => 'bi-shield-check',
        'notifications'  => 'bi-bell',
        'import-export'  => 'bi-arrow-left-right',
        'rms'            => 'bi-diagram-3',
        'dashboard'      => 'bi-speedometer2',
        'pole-number-format' => 'bi-upc-scan',
        'audit-log'      => 'bi-clock-history',
    ];
@endphp

<div class="container-fluid settings-page">
    <div class="row g-3 g-lg-4 settings-page-layout">
        <div class="col-lg-3 col-xl-2 settings-nav-col">
            <div class="settings-nav border rounded bg-white">
                <div class="px-3 py-3 border-bottom d-flex align-items-center gap-2">
                    <i class="bi bi-gear-fill text-muted"></i>
                    <h5 class="mb-0 fw-semibold">Admin Settings</h5>
                </div>
                <div class="nav flex-column">
                    @foreach($sections as $key => $label)
                        <a class="nav-link {{ $activeSection === $key ? 'active' : '' }}" href="{{ route('settings.section', $key) }}">
                            <i class="bi {{ $sectionIcons[$key] ?? 'bi-gear' }} settings-nav-icon"></i>{{ $label }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-lg-9 col-xl-10 settings-content-col">
            @if(session('success'))
                <div class="alert alert-success" role="status">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger" role="alert">
                    <strong>Please fix the following:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @includeIf("settings.partials.$activeSection")
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .settings-page > .row {
        align-items: flex-start;
    }

    @media (min-width: 992px) {
        .settings-page .settings-nav-col {
            padding-right: 1rem;
        }

        .settings-page .settings-content-col {
            padding-left: 0.5rem;
        }
    }

    .settings-page .col-lg-9,
    .settings-page .col-xl-10 {
        min-width: 0;
    }

    .settings-page .settings-nav {
        position: sticky;
        top: 80px;
        overflow: hidden;
        z-index: 2;
        border-color: #dee2e6 !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
    }

    .settings-page .settings-nav > .border-bottom {
        background: #f8f9fa;
    }

    .settings-page .settings-nav .nav-link {
        color: #495057;
        border-radius: 0;
        padding: 0.75rem 1rem 0.75rem 0.875rem;
        font-size: 0.9375rem;
        border-left: none;
        box-shadow: none;
    }

    .settings-page .settings-nav .nav-link:hover:not(.active) {
        background: rgba(0, 0, 0, 0.03);
        color: #212529;
    }

    .settings-page .settings-nav .nav-link.active {
        color: #1F3BB3;
        background: rgba(31, 59, 179, 0.08);
        font-weight: 600;
        box-shadow: inset 2px 0 0 #1F3BB3;
    }

    .settings-page .settings-nav .settings-nav-icon {
        width: 1.1em;
        margin-right: 0.5rem;
        opacity: 0.65;
        flex-shrink: 0;
    }

    .settings-page .settings-nav .nav-link.active .settings-nav-icon {
        opacity: 1;
        color: #1F3BB3;
    }

    .settings-card {
        border: 1px solid #dee2e6;
        border-radius: 4px;
        background: #fff;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
        overflow: hidden;
    }

    .settings-card-header {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #dee2e6;
        background: #f8f9fa;
    }

    .settings-card-header h4 {
        font-size: 1.125rem;
        font-weight: 600;
        color: #212529;
    }

    .settings-card-body {
        padding: 1.25rem;
    }

    .settings-table th,
    .settings-table td {
        vertical-align: middle;
    }

    .settings-table .form-control,
    .settings-table .form-select {
        min-width: 120px;
    }

    .settings-actions {
        white-space: nowrap;
    }

    /* Static settings tables — same visual language as the shared datatable component (no DataTables JS) */
    .settings-page .settings-datatable-wrap {
        border: 1px solid #dee2e6;
        border-radius: 4px;
        overflow: hidden;
        background: #fff;
    }

    .settings-page .settings-datatable-wrap .table {
        margin-bottom: 0;
    }

    .settings-page .settings-datatable-wrap .table thead th {
        background: #f8f9fa;
        color: #495057;
        font-size: 0.8125rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.02em;
        border-bottom: 2px solid #dee2e6;
        padding: 0.625rem 0.75rem;
        vertical-align: middle;
        white-space: nowrap;
    }

    .settings-page .settings-datatable-wrap .table tbody td {
        vertical-align: middle;
        padding: 0.5rem 0.75rem;
    }

    .settings-page .settings-datatable-wrap .table tbody td.settings-row-actions {
        text-align: center;
        white-space: nowrap;
        width: 140px;
        min-width: 140px;
    }

    .settings-page .settings-datatable-wrap .settings-row-actions .btn-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 6px 10px;
        margin: 0 2px;
        border-radius: 4px;
        background-color: transparent !important;
        border: 1px solid #dee2e6 !important;
        box-shadow: none !important;
        vertical-align: middle;
    }

    .settings-page .settings-datatable-wrap .settings-row-actions .btn-icon i {
        font-size: 1rem;
        line-height: 1;
    }

    /* Row actions — match resources/views/components/datatable.blade.php */
    .settings-page .settings-datatable-wrap .settings-row-actions .btn-icon.btn-warning {
        background-color: #ffc107 !important;
        border-color: #ffc107 !important;
        color: #212529 !important;
    }

    .settings-page .settings-datatable-wrap .settings-row-actions .btn-icon.btn-warning:hover {
        background-color: #e0a800 !important;
        border-color: #d39e00 !important;
        color: #212529 !important;
    }

    .settings-page .settings-datatable-wrap .settings-row-actions .btn-icon.btn-info {
        background-color: #17a2b8 !important;
        border-color: #17a2b8 !important;
        color: #fff !important;
    }

    .settings-page .settings-datatable-wrap .settings-row-actions .btn-icon.btn-info:hover {
        background-color: #138496 !important;
        border-color: #117a8b !important;
        color: #fff !important;
    }

    .settings-page .settings-datatable-wrap .settings-scope-badge {
        display: inline-block;
        font-size: 0.75rem;
        font-weight: 600;
        line-height: 1.2;
        padding: 0.35em 0.65em;
        border-radius: 0.25rem;
        letter-spacing: 0.02em;
        border: none;
    }

    .settings-page .settings-datatable-wrap .settings-scope-badge-ward-normal {
        background-color: #1F3BB3;
        color: #fff;
    }

    .settings-page .settings-datatable-wrap .settings-scope-badge-ward-gp {
        background-color: #087990;
        color: #fff;
    }

    .settings-page .settings-datatable-wrap .settings-scope-badge-active {
        background-color: #198754;
        color: #fff;
    }

    .settings-page .settings-datatable-wrap .settings-scope-badge-inactive {
        background-color: #495057;
        color: #fff;
    }

    /* Billing / TA-DA — matches legacy billing settings & CRM tables */
    .billing-settings.settings-card {
        padding: 0;
    }

    .billing-settings .settings-card-body {
        padding: 0;
    }

    .billing-settings .billing-tabs-nav {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        background: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
        padding: 0 1rem;
    }

    /* The global theme .nav rule applies position:fixed; max-width:220px for the sidebar.
       Reset those inherited values so the billing tabs ul behaves as a normal flex item. */
    .billing-settings .billing-tabs-nav .nav.nav-tabs {
        position: static !important;
        max-width: none !important;
    }

    .billing-settings .billing-tabs-nav .nav-tabs {
        flex: 1 1 0%;
        min-width: 0;
        border-bottom: none;
        flex-wrap: nowrap;
        overflow-x: auto;
        scrollbar-width: none;
        align-items: flex-end;
        margin-bottom: -1px;
    }

    .billing-settings .billing-tabs-nav .nav-tabs::-webkit-scrollbar {
        display: none;
    }

    .billing-settings .billing-tabs-nav .nav-link {
        color: #495057;
        border: none;
        border-bottom: 2px solid transparent;
        border-radius: 0;
        padding: 0.875rem 1rem;
        font-size: 0.9375rem;
        font-weight: 500;
        background: transparent;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        transition: color 0.15s ease, border-color 0.15s ease;
    }

    .billing-settings .billing-tabs-nav .nav-link:hover:not(.active) {
        color: #1F3BB3;
        border-bottom-color: rgba(31, 59, 179, 0.35);
        background: transparent;
    }

    .billing-settings .billing-tabs-nav .nav-link.active {
        color: #1F3BB3;
        background: transparent;
        border-bottom-color: #1F3BB3;
        font-weight: 600;
    }

    .billing-settings .billing-tab-action-wrap {
        flex-shrink: 0;
        display: flex;
        align-items: center;
    }

    .billing-settings .billing-tab-panel {
        padding: 1.25rem;
        background: #fff;
    }

    .billing-settings .billing-tab-description {
        margin-bottom: 0.875rem;
    }

    .billing-settings .billing-data-card {
        border-color: #dee2e6 !important;
        margin-bottom: 0;
    }

    .billing-settings .billing-data-card > .card-body {
        padding: 1rem 1.25rem 1.25rem;
    }

    .billing-settings .tier-legend {
        font-size: 0.8125rem;
        color: #6c757d;
    }

    .billing-settings .billing-row-actions {
        white-space: nowrap;
    }

    /* Toolbar: neutral outline buttons instead of saturated colour blocks */
    .billing-settings .datatable-wrapper .btn-group .btn-success,
    .billing-settings .datatable-wrapper .btn-group .btn-danger,
    .billing-settings .datatable-wrapper .btn-group .btn-info,
    .billing-settings .datatable-wrapper .btn-group .btn-secondary {
        background-color: #fff !important;
        border-color: #ced4da !important;
        color: #495057 !important;
        font-weight: 500;
    }

    .billing-settings .datatable-wrapper .btn-group .btn-success:hover {
        color: #198754 !important;
        border-color: #198754 !important;
        background-color: #f8fff9 !important;
    }

    .billing-settings .datatable-wrapper .btn-group .btn-danger:hover {
        color: #dc3545 !important;
        border-color: #dc3545 !important;
        background-color: #fff5f5 !important;
    }

    .billing-settings .datatable-wrapper .btn-group .btn-info:hover {
        color: #0dcaf0 !important;
        border-color: #0dcaf0 !important;
        background-color: #f0fcff !important;
    }

    .billing-settings .datatable-wrapper .btn-group .btn-secondary:hover {
        color: #212529 !important;
        border-color: #adb5bd !important;
        background-color: #f8f9fa !important;
    }

    /* Row actions: subtle ghost icons — no saturated yellow/red tiles */
    .billing-settings .datatable-wrapper .btn-icon.btn-warning,
    .billing-settings .datatable-wrapper .btn-icon.btn-danger {
        width: 30px;
        height: 30px;
        min-width: 30px !important;
        padding: 0 !important;
        border-radius: 4px !important;
        background-color: transparent !important;
        border: 1px solid #dee2e6 !important;
        color: #6c757d !important;
        box-shadow: none !important;
        transition: color 0.12s ease, border-color 0.12s ease, background-color 0.12s ease;
    }

    .billing-settings .datatable-wrapper .btn-icon.btn-warning:hover {
        color: #1F3BB3 !important;
        border-color: #1F3BB3 !important;
        background-color: rgba(31, 59, 179, 0.06) !important;
    }

    .billing-settings .datatable-wrapper .btn-icon.btn-danger:hover {
        color: #dc3545 !important;
        border-color: #dc3545 !important;
        background-color: rgba(220, 53, 69, 0.06) !important;
    }

    .billing-settings .datatable-wrapper .table thead th {
        background: #f8f9fa;
        color: #495057;
        font-size: 0.8125rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }

    .billing-settings .datatable-wrapper .input-group-text {
        background: #f8f9fa;
        border-color: #ced4da;
        color: #6c757d;
    }

    .billing-settings .billing-cell-truncate {
        max-width: 280px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .billing-settings .billing-empty-state {
        border: 1px dashed #dee2e6;
        border-radius: 4px;
        background: #f8f9fa;
    }

    .billing-settings .billing-submit-btn[aria-busy="true"] {
        pointer-events: none;
        opacity: 0.85;
    }

    @media (prefers-reduced-motion: reduce) {
        .billing-settings .datatable-wrapper .btn,
        .billing-settings .billing-tabs-nav .nav-link {
            transition: none;
        }
    }
</style>
@endpush
