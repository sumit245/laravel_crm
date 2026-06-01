@extends('layouts.main')

@php
    $isStreetlight = isset($projectType) && (int) $projectType === 1;
@endphp

@section('title', $isStreetlight ? (($site->panchayat ?? 'Site') . ' · Site') : (($site->site_name ?? 'Site') . ' · Site'))

@section('content')
@if ($isStreetlight)
    @php
        $normalizeWardLabel = function ($ward) {
            $ward = trim((string) $ward);
            if ($ward === '') {
                return '';
            }

            return ctype_digit($ward) ? 'Ward ' . $ward : $ward;
        };

        $normalWards = collect(explode(',', $site->ward))
            ->map(fn ($ward) => trim($ward))
            ->filter()
            ->map(fn ($ward) => $normalizeWardLabel($ward))
            ->unique()
            ->values();

        $gpWards = $site->siteWards->where('ward_type', 'gp')
            ->sortBy(fn ($ward) => (int) $ward->ward_number)
            ->map(fn ($ward) => [
                'label' => 'GP Ward ' . $ward->ward_number,
                'planned_poles' => $ward->planned_poles,
            ])
            ->values();

        $wardButtons = collect([['label' => 'All Wards', 'value' => '', 'planned_poles' => null]])
            ->merge($normalWards->map(fn ($ward) => ['label' => $ward, 'value' => $ward, 'planned_poles' => null]))
            ->merge($gpWards->map(fn ($ward) => ['label' => $ward['label'], 'value' => $ward['label'], 'planned_poles' => $ward['planned_poles']]));

        $poleWardLabel = function ($pole) use ($normalizeWardLabel) {
            if ($pole->ward_number) {
                return ($pole->ward_type === 'gp' ? 'GP ' : '') . 'Ward ' . $pole->ward_number;
            }

            return $normalizeWardLabel($pole->ward_name);
        };

        $poleCollection = collect($poles ?? []);
        $surveyedPoles = $poleCollection->where('isSurveyDone', 1)->values();
        $installedPoles = $poleCollection->where('isInstallationDone', 1)->values();
        $targetPoleCount = max((int) ($site->total_poles ?? 0), $poleCollection->count());
        $pendingPoleCount = max(0, $targetPoleCount - $installedPoles->count());
        $importRoute = route('sites.poles.import', ['siteId' => $site->id]);
        $importFormatUrl = route('sites.poles.exportFormat', ['siteId' => $site->id]);
        $bulkDeleteRoute = route('sites.poles.bulkDelete');
    @endphp

    <div class="site-detail-page">
        <div class="row g-3 mb-3">
            <div class="col-12 col-xl-8">
                <div class="card h-100">
                    <div class="card-body p-3">
                        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
                            <div class="min-w-0">
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                    <span class="site-chip">{{ $site->task_id ?? 'Site' }}</span>
                                    <span class="text-muted small">Streetlight Site</span>
                                </div>
                                <h2 class="site-title mb-1">{{ $site->panchayat ?? 'N/A' }}</h2>
                                <p class="text-muted mb-0">
                                    {{ $site->block ?? 'N/A' }} block, {{ $site->district ?? 'N/A' }} district, {{ $site->state ?? 'N/A' }}
                                </p>
                            </div>
                            <div class="site-date-grid">
                                <div>
                                    <span class="site-label">Start</span>
                                    <strong>{{ $streetlightTask?->start_date ? \Carbon\Carbon::parse($streetlightTask->start_date)->format('d-M-Y') : 'N/A' }}</strong>
                                </div>
                                <div>
                                    <span class="site-label">End</span>
                                    <strong>{{ $streetlightTask?->end_date ? \Carbon\Carbon::parse($streetlightTask->end_date)->format('d-M-Y') : 'N/A' }}</strong>
                                </div>
                            </div>
                        </div>

                        <div class="site-info-grid mt-3">
                            <div>
                                <span class="site-label">Manager</span>
                                <strong>{{ trim($managerName) ?: 'N/A' }}</strong>
                            </div>
                            <div>
                                <span class="site-label">Engineer</span>
                                <strong>{{ trim($engineerName) ?: 'N/A' }}</strong>
                            </div>
                            <div>
                                <span class="site-label">Vendor</span>
                                <strong>{{ trim($vendorName) ?: 'N/A' }}</strong>
                            </div>
                            <div>
                                <span class="site-label">Total Planned Poles</span>
                                <strong>{{ number_format($targetPoleCount) }}</strong>
                            </div>
                            <div>
                                <span class="site-label">Mukhiya Contact</span>
                                <strong>{{ $site->mukhiya_contact ?? 'N/A' }}</strong>
                            </div>
                            <div>
                                <span class="site-label">Wards</span>
                                <strong>{{ $normalWards->merge($gpWards->pluck('label'))->implode(', ') ?: 'N/A' }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-4">
                <div class="card h-100">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <h3 class="site-section-title mb-0">Pole Import</h3>
                            </div>
                            <i class="mdi mdi-file-upload-outline text-primary site-panel-icon" aria-hidden="true"></i>
                        </div>
                        <form action="{{ $importRoute }}" method="POST" enctype="multipart/form-data" class="site-import-form">
                            @csrf
                            <div class="input-group input-group-sm">
                                <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                                <button type="submit" class="btn btn-success d-inline-flex align-items-center gap-1">
                                    <i class="mdi mdi-upload" aria-hidden="true"></i>
                                    Import
                                </button>
                            </div>
                        </form>
                        <a href="{{ $importFormatUrl }}" class="btn btn-outline-primary btn-sm mt-2 d-inline-flex align-items-center gap-1">
                            <i class="mdi mdi-download" aria-hidden="true"></i>
                            Download Format
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-6 col-xl-3">
                <div class="card h-100">
                    <div class="card-body p-3 d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted mb-1 small fw-semibold text-uppercase">Target Poles</p>
                            <p class="mb-0 fs-3 fw-semibold">{{ number_format($targetPoleCount) }}</p>
                        </div>
                        <i class="mdi mdi-lightbulb-on-outline text-primary mdi-36px" aria-hidden="true"></i>
                    </div>
                </div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="card h-100">
                    <div class="card-body p-3 d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted mb-1 small fw-semibold text-uppercase">Surveyed</p>
                            <p class="mb-0 fs-3 fw-semibold text-info">{{ number_format($surveyedPoles->count()) }}</p>
                        </div>
                        <i class="mdi mdi-map-marker-check-outline text-info mdi-36px" aria-hidden="true"></i>
                    </div>
                </div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="card h-100">
                    <div class="card-body p-3 d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted mb-1 small fw-semibold text-uppercase">Installed</p>
                            <p class="mb-0 fs-3 fw-semibold text-success">{{ number_format($installedPoles->count()) }}</p>
                        </div>
                        <i class="mdi mdi-check-circle-outline text-success mdi-36px" aria-hidden="true"></i>
                    </div>
                </div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="card h-100">
                    <div class="card-body p-3 d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted mb-1 small fw-semibold text-uppercase">Pending</p>
                            <p class="mb-0 fs-3 fw-semibold text-warning">{{ number_format($pendingPoleCount) }}</p>
                        </div>
                        <i class="mdi mdi-clock-outline text-warning mdi-36px" aria-hidden="true"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-12 col-xl-2">
                <div class="card site-ward-panel">
                    <div class="card-body p-2">
                        <div class="site-label px-2 pt-1 mb-2">Ward Filter</div>
                        <div class="site-ward-list">
                            @foreach ($wardButtons as $ward)
                                <button type="button"
                                    class="ward-button {{ $loop->first ? 'active' : '' }}"
                                    data-ward="{{ $ward['value'] }}"
                                    onclick="loadWardData(event, '{{ $ward['value'] }}')">
                                    <span>{{ $ward['label'] }}</span>
                                    @if ($ward['planned_poles'])
                                        <small>{{ $ward['planned_poles'] }} poles</small>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-10">
                <div class="card">
                    <div class="card-body p-3">
                        <ul class="nav nav-tabs nav-tabs-modern fixed-navbar-project" id="sitePolesTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="surveyed-tab" data-bs-toggle="tab" data-bs-target="#surveyed-poles" type="button"
                                    role="tab" aria-controls="surveyed-poles" aria-selected="true">
                                    Surveyed Poles
                                    <span class="badge bg-light text-dark badge-pill-xs">{{ number_format($surveyedPoles->count()) }}</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="installed-tab" data-bs-toggle="tab" data-bs-target="#installed-poles" type="button"
                                    role="tab" aria-controls="installed-poles" aria-selected="false">
                                    Installed Lights
                                    <span class="badge bg-light text-dark badge-pill-xs">{{ number_format($installedPoles->count()) }}</span>
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content mt-3" id="sitePolesTabContent">
                            <div class="tab-pane fade show active" id="surveyed-poles" role="tabpanel" aria-labelledby="surveyed-tab">
                                <x-datatable
                                    id="siteSurveyedPolesTable"
                                    title=""
                                    :columns="[
                                        ['title' => 'Pole Number', 'width' => '16%'],
                                        ['title' => 'Ward', 'width' => '8%'],
                                        ['title' => 'Beneficiary', 'width' => '18%'],
                                        ['title' => 'Beneficiary Contact', 'width' => '14%'],
                                        ['title' => 'Surveyed At', 'width' => '12%'],
                                        ['title' => 'Remarks', 'width' => '20%'],
                                    ]"
                                    :exportEnabled="true"
                                    :importEnabled="false"
                                    :bulkDeleteEnabled="true"
                                    :bulkDeleteRoute="$bulkDeleteRoute"
                                    pageLength="50"
                                    searchPlaceholder="Search surveyed poles..."
                                    :filters="[]"
                                >
                                    @foreach ($surveyedPoles as $pole)
                                        @php $wardLabel = $poleWardLabel($pole); @endphp
                                        <tr data-ward="{{ $wardLabel }}" data-pole-id="{{ $pole->id }}">
                                            <td><input type="checkbox" class="row-checkbox" value="{{ $pole->id }}"></td>
                                            <td>{{ $pole->complete_pole_number ?? 'N/A' }}</td>
                                            <td>{{ $wardLabel ?: 'N/A' }}</td>
                                            <td>{{ $pole->beneficiary ?? 'N/A' }}</td>
                                            <td>{{ $pole->beneficiary_contact ?? 'N/A' }}</td>
                                            <td>{{ $pole->surveyed_at ? $pole->surveyed_at->format('d M Y') : 'N/A' }}</td>
                                            <td>{{ $pole->remarks ?? 'N/A' }}</td>
                                            <td class="text-center">
                                                <a href="{{ route('poles.show', $pole->id) }}" class="btn btn-icon btn-info" data-bs-toggle="tooltip" title="View Details">
                                                    <i class="mdi mdi-eye" aria-hidden="true"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </x-datatable>
                            </div>

                            <div class="tab-pane fade" id="installed-poles" role="tabpanel" aria-labelledby="installed-tab">
                                <x-datatable
                                    id="siteInstalledPolesTable"
                                    title=""
                                    :columns="[
                                        ['title' => 'Pole Number', 'width' => '14%'],
                                        ['title' => 'Ward', 'width' => '7%'],
                                        ['title' => 'Luminary', 'width' => '12%'],
                                        ['title' => 'Battery', 'width' => '12%'],
                                        ['title' => 'Panel', 'width' => '12%'],
                                        ['title' => 'Installed At', 'width' => '10%'],
                                        ['title' => 'Beneficiary Contact', 'width' => '18%'],
                                    ]"
                                    :exportEnabled="true"
                                    :importEnabled="false"
                                    :bulkDeleteEnabled="true"
                                    :bulkDeleteRoute="$bulkDeleteRoute"
                                    pageLength="50"
                                    searchPlaceholder="Search installed poles..."
                                    :filters="[]"
                                >
                                    @foreach ($installedPoles as $pole)
                                        @php $wardLabel = $poleWardLabel($pole); @endphp
                                        <tr data-ward="{{ $wardLabel }}" data-pole-id="{{ $pole->id }}">
                                            <td><input type="checkbox" class="row-checkbox" value="{{ $pole->id }}"></td>
                                            <td>{{ $pole->complete_pole_number ?? 'N/A' }}</td>
                                            <td>{{ $wardLabel ?: 'N/A' }}</td>
                                            <td>{{ $pole->luminary_qr ?? 'N/A' }}</td>
                                            <td>{{ $pole->battery_qr ?? 'N/A' }}</td>
                                            <td>{{ $pole->panel_qr ?? 'N/A' }}</td>
                                            <td>{{ $pole->installed_at ? $pole->installed_at->format('d M Y') : 'N/A' }}</td>
                                            <td>
                                                <div class="fw-semibold">{{ $pole->beneficiary ?? 'N/A' }}</div>
                                                <div class="text-muted small">{{ $pole->beneficiary_contact ?? 'N/A' }}</div>
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('poles.show', $pole->id) }}" class="btn btn-icon btn-info" data-bs-toggle="tooltip" title="View Details">
                                                    <i class="mdi mdi-eye" aria-hidden="true"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </x-datatable>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@else
    <div class="card">
        <div class="card-body p-4 text-center">
            <h2 class="h5 mb-2">{{ $site->site_name ?? 'Site Details' }}</h2>
            <p class="text-muted mb-0">Site details for non-streetlight projects.</p>
        </div>
    </div>
@endif
@endsection

@push('styles')
<style>
    .site-detail-page .card {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.05);
    }

    .site-title {
        color: #3f4654;
        font-size: 1.35rem;
        font-weight: 700;
        line-height: 1.25;
    }

    .site-section-title {
        color: #3f4654;
        font-size: 1rem;
        font-weight: 700;
    }

    .site-label {
        color: #7b8794;
        display: block;
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0;
        margin-bottom: 0.25rem;
        text-transform: uppercase;
    }

    .site-chip {
        align-items: center;
        background: #e8efff;
        border: 1px solid #b7c8ff;
        border-radius: 999px;
        color: #173b92;
        display: inline-flex;
        font-size: 0.76rem;
        font-weight: 700;
        min-height: 28px;
        padding: 0.45rem 0.75rem;
    }

    .site-info-grid {
        display: grid;
        gap: 0.85rem;
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .site-info-grid strong,
    .site-date-grid strong {
        color: #3f4654;
        display: block;
        font-size: 0.9rem;
        line-height: 1.35;
        overflow-wrap: anywhere;
    }

    .site-date-grid {
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        display: grid;
        gap: 0.75rem;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        min-width: min(100%, 260px);
        padding: 0.75rem;
    }

    .site-panel-icon {
        font-size: 1.75rem;
    }

    .site-ward-panel {
        position: sticky;
        top: 1rem;
    }

    .site-ward-list {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        max-height: 70vh;
        overflow-y: auto;
        padding: 0.15rem;
    }

    .ward-button {
        align-items: center;
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        color: #3f4654;
        display: flex;
        justify-content: space-between;
        min-height: 40px;
        padding: 0.55rem 0.65rem;
        text-align: left;
        transition: border-color 0.15s ease, background 0.15s ease, color 0.15s ease;
        width: 100%;
    }

    .ward-button:hover {
        background: #f5f7ff;
        border-color: #b7c8ff;
    }

    .ward-button.active {
        background: #1f3bb3;
        border-color: #1f3bb3;
        color: #fff;
    }

    .ward-button small {
        color: inherit;
        opacity: 0.8;
    }

    .site-detail-page .nav-tabs-modern {
        border-bottom: 1px solid #dee2e6;
        gap: 0.35rem;
    }

    .site-detail-page .nav-tabs-modern .nav-link {
        align-items: center;
        border-radius: 8px 8px 0 0;
        display: inline-flex;
        gap: 0.4rem;
        min-height: 40px;
    }

    .site-detail-page .datatable-wrapper {
        border: 0;
        box-shadow: none;
        padding-left: 0;
        padding-right: 0;
    }

    @media (max-width: 991.98px) {
        .site-info-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .site-ward-panel {
            position: static;
        }

        .site-ward-list {
            flex-direction: row;
            max-height: none;
            overflow-x: auto;
        }

        .ward-button {
            flex: 0 0 auto;
            min-width: 110px;
        }
    }

    @media (max-width: 575.98px) {
        .site-info-grid,
        .site-date-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    let currentWard = '';
    let siteWardFilterFn = null;
    const sitePoleTableIds = ['siteSurveyedPolesTable', 'siteInstalledPolesTable'];

    function ensureSiteWardFilter() {
        if (siteWardFilterFn) {
            return;
        }

        siteWardFilterFn = function (settings, data, dataIndex) {
            if (!sitePoleTableIds.includes(settings.nTable.id)) {
                return true;
            }

            if (!currentWard) {
                return true;
            }

            const table = window['table_' + settings.nTable.id] || $('#' + settings.nTable.id).DataTable();
            const row = table.row(dataIndex).node();
            return String($(row).attr('data-ward') || '') === String(currentWard);
        };

        if (!$.fn.dataTable.ext.search) {
            $.fn.dataTable.ext.search = [];
        }

        $.fn.dataTable.ext.search.push(siteWardFilterFn);
    }

    function redrawSitePoleTables() {
        sitePoleTableIds.forEach(function (tableId) {
            if ($.fn.DataTable.isDataTable('#' + tableId)) {
                $('#' + tableId).DataTable().draw();
            }
        });
    }

    function loadWardData(event, ward) {
        event.preventDefault();
        currentWard = ward || '';

        document.querySelectorAll('.ward-button').forEach(function (button) {
            button.classList.toggle('active', button.dataset.ward === currentWard);
        });

        ensureSiteWardFilter();
        redrawSitePoleTables();
    }

    document.addEventListener('DOMContentLoaded', function () {
        setTimeout(function () {
            ensureSiteWardFilter();
            redrawSitePoleTables();
        }, 800);

        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (element) {
            new bootstrap.Tooltip(element);
        });
    });

    window.loadWardData = loadWardData;
</script>
@endpush
