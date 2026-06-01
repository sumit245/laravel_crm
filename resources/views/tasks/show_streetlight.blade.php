@extends('layouts.main')

@php
    $site = $streetlightTask->site;
    $poleCollection = collect($poles ?? []);
    $installedPoleCollection = collect($installedPoles ?? []);
    $surveyedPoleCollection = collect($surveyedPoles ?? []);
    $installedPoleCount = $installedPoleCollection->count();
    $surveyedPoleCount = $surveyedPoleCollection->count();
    $statusText = $streetlightTask->status ?: 'Pending';
    $statusKey = match (strtolower((string) $statusText)) {
        'completed', 'complete' => 'completed',
        'in progress', 'progress', 'ongoing' => 'in-progress',
        default => 'pending',
    };
    $allottedSiteWards = $streetlightTask->siteWards
        ->sortBy(function ($ward) {
            return sprintf('%05d-%s', (int) $ward->ward_number, $ward->ward_type);
        })
        ->values();
    $allottedWardLabel = $streetlightTask->allotted_wards ?: 'N/A';
    if ($allottedSiteWards->isNotEmpty()) {
        $allottedWardLabel = $allottedSiteWards
            ->map(function ($ward) {
                if ($ward->ward_type === 'gp') {
                    return 'GP Ward ' . $ward->ward_number . ' (' . $ward->planned_poles . ' poles)';
                }

                return 'Ward ' . $ward->ward_number;
            })
            ->implode(', ');
    }
    $plannedWardPoleCount = (int) $allottedSiteWards->sum('planned_poles');
    $targetPoleCount = max($poleCollection->count(), $plannedWardPoleCount, (int) ($site->total_poles ?? 0));
    $pendingPoleCount = max(0, $targetPoleCount - $installedPoleCount);

    $personName = function ($person) {
        if (!$person) {
            return 'Unassigned';
        }

        return trim(($person->firstName ?? '') . ' ' . ($person->lastName ?? '')) ?: ($person->name ?? 'Unnamed');
    };
@endphp

@section('title', 'Target #' . $streetlightTask->id . ' · Streetlight Target')

@section('content')
<div class="task-detail-page">
    <div class="row g-3 mb-3">
        <div class="col-12 col-xl-8">
            <div class="card h-100">
                <div class="card-body p-3">
                    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
                        <div class="min-w-0">
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                <span class="task-status-badge task-status-{{ $statusKey }}">{{ $statusText }}</span>
                                <span class="text-muted small">Target #{{ $streetlightTask->id }}</span>
                            </div>
                            <h2 class="task-title mb-1">{{ $site->panchayat ?? 'Unknown Panchayat' }}</h2>
                            <p class="text-muted mb-0">
                                {{ $site->block ?? 'N/A' }} block, {{ $site->district ?? 'N/A' }} district
                            </p>
                        </div>
                        <div class="task-date-grid">
                            <div>
                                <span class="task-label">Assigned</span>
                                <strong>{{ $streetlightTask->created_at ? $streetlightTask->created_at->format('d-M-Y') : 'N/A' }}</strong>
                            </div>
                            <div>
                                <span class="task-label">Start</span>
                                <strong>{{ $streetlightTask->start_date ? \Carbon\Carbon::parse($streetlightTask->start_date)->format('d-M-Y') : 'N/A' }}</strong>
                            </div>
                            <div>
                                <span class="task-label">End</span>
                                <strong>{{ $streetlightTask->end_date ? \Carbon\Carbon::parse($streetlightTask->end_date)->format('d-M-Y') : 'N/A' }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="task-info-grid mt-3">
                        <div>
                            <span class="task-label">District</span>
                            <strong>{{ $site->district ?? 'N/A' }}</strong>
                        </div>
                        <div>
                            <span class="task-label">Block</span>
                            <strong>{{ $site->block ?? 'N/A' }}</strong>
                        </div>
                        <div>
                            <span class="task-label">Panchayat</span>
                            <strong>{{ $site->panchayat ?? 'N/A' }}</strong>
                        </div>
                        <div>
                            <span class="task-label">Mukhiya Contact</span>
                            <strong>{{ $site->mukhiya_contact ?? 'N/A' }}</strong>
                        </div>
                        <div class="task-info-wide">
                            <span class="task-label">Allotted Wards</span>
                            <strong>{{ $allottedWardLabel }}</strong>
                        </div>
                        <div>
                            <span class="task-label">Site Planned Poles</span>
                            <strong>{{ number_format((int) ($site->total_poles ?? 0)) }}</strong>
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
                            <h3 class="task-section-title mb-1">Generate JICR</h3>
                            <p class="text-muted small mb-0">Create report for this panchayat date range.</p>
                        </div>
                        <i class="mdi mdi-file-document-outline text-primary task-panel-icon" aria-hidden="true"></i>
                    </div>
                    <form id="jicrForm" action="{{ route('jicr.generate') }}" method="GET">
                        <input type="hidden" name="district" value="{{ $site->district }}">
                        <input type="hidden" name="block" value="{{ $site->block }}">
                        <input type="hidden" name="panchayat" value="{{ $site->panchayat }}">

                        <div class="row g-2">
                            <div class="col-12 col-sm-6">
                                <label for="fromDate" class="form-label small fw-semibold">From Date</label>
                                <input type="date" id="fromDate" name="from_date" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-12 col-sm-6">
                                <label for="toDate" class="form-label small fw-semibold">To Date</label>
                                <input type="date" id="toDate" name="to_date" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary btn-sm w-100 d-inline-flex align-items-center justify-content-center gap-2">
                                    <i class="mdi mdi-file-download-outline" aria-hidden="true"></i>
                                    Generate JICR
                                </button>
                            </div>
                        </div>
                    </form>
                    @if (!empty($showReport) && isset($data))
                        <div class="mt-3">
                            @include('jicr.show', ['data' => $data])
                        </div>
                    @endif
                    <div id="jicrReportContainer" class="mt-3"></div>
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
                        <p class="mb-0 fs-3 fw-semibold text-info">{{ number_format($surveyedPoleCount) }}</p>
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
                        <p class="mb-0 fs-3 fw-semibold text-success">{{ number_format($installedPoleCount) }}</p>
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

    <div class="row g-3 mb-3">
        @foreach ([
            ['label' => 'Project Manager', 'person' => $manager, 'icon' => 'mdi-account-tie'],
            ['label' => 'Vendor', 'person' => $vendor, 'icon' => 'mdi-account-hard-hat'],
            ['label' => 'Engineer', 'person' => $engineer, 'icon' => 'mdi-account-wrench'],
        ] as $assignment)
            <div class="col-12 col-lg-4">
                @if ($assignment['person'])
                    <a href="{{ route('staff.show', $assignment['person']->id) }}" class="task-assignment-card card h-100 text-decoration-none text-reset">
                        <div class="card-body p-3 d-flex align-items-center gap-3">
                            <img src="{{ $assignment['person']->image ?? asset('images/faces/face8.jpg') }}"
                                alt=""
                                class="task-avatar"
                                width="48"
                                height="48"
                                decoding="async"
                                onerror="this.onerror=null;this.src='{{ asset('images/faces/face8.jpg') }}';">
                            <div class="min-w-0 flex-grow-1">
                                <p class="text-muted small mb-1">{{ $assignment['label'] }}</p>
                                <h3 class="task-person-name mb-0">{{ $personName($assignment['person']) }}</h3>
                            </div>
                            <i class="mdi mdi-chevron-right text-muted" aria-hidden="true"></i>
                        </div>
                    </a>
                @else
                    <div class="card h-100">
                        <div class="card-body p-3 d-flex align-items-center gap-3">
                            <div class="task-avatar task-avatar-empty" aria-hidden="true">
                                <i class="mdi {{ $assignment['icon'] }}"></i>
                            </div>
                            <div>
                                <p class="text-muted small mb-1">{{ $assignment['label'] }}</p>
                                <h3 class="task-person-name mb-0">Unassigned</h3>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    <div class="card">
        <div class="card-body p-3">
            <ul class="nav nav-tabs nav-tabs-modern fixed-navbar-project" id="poleTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="installed-tab" data-bs-toggle="tab" data-bs-target="#installed" type="button"
                        role="tab" aria-controls="installed" aria-selected="true">
                        Installed Poles
                        <span class="badge bg-light text-dark badge-pill-xs">{{ number_format($installedPoleCount) }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="surveyed-tab" data-bs-toggle="tab" data-bs-target="#surveyed" type="button"
                        role="tab" aria-controls="surveyed" aria-selected="false">
                        Surveyed Poles
                        <span class="badge bg-light text-dark badge-pill-xs">{{ number_format($surveyedPoleCount) }}</span>
                    </button>
                </li>
            </ul>

            <div class="tab-content mt-3" id="poleTabsContent">
                <div class="tab-pane fade show active" id="installed" role="tabpanel" aria-labelledby="installed-tab">
                    <x-datatable
                        id="taskInstalledPoles"
                        title=""
                        :columns="[
                            ['title' => 'Pole Number', 'width' => '14%'],
                            ['title' => 'Ward', 'width' => '8%'],
                            ['title' => 'IMEI', 'width' => '12%'],
                            ['title' => 'SIM Number', 'width' => '12%'],
                            ['title' => 'Battery', 'width' => '12%'],
                            ['title' => 'Panel', 'width' => '12%'],
                            ['title' => 'Installed At', 'width' => '10%'],
                            ['title' => 'RMS Status', 'width' => '10%'],
                        ]"
                        :exportEnabled="true"
                        :importEnabled="false"
                        :bulkDeleteEnabled="false"
                        :actionsColumnEnabled="true"
                        pageLength="50"
                        searchPlaceholder="Search installed poles..."
                        :filters="[]"
                    >
                        @foreach ($installedPoleCollection as $pole)
                            <tr>
                                <td>
                                    @if ($pole->lat && $pole->lng)
                                        <button type="button" class="btn btn-link p-0 task-location-link" onclick="locateOnMap({{ $pole->lat }}, {{ $pole->lng }})">
                                            {{ $pole->complete_pole_number ?? 'N/A' }}
                                        </button>
                                    @else
                                        {{ $pole->complete_pole_number ?? 'N/A' }}
                                    @endif
                                </td>
                                <td>{{ $pole->ward_name ?? ($pole->ward_number ? (($pole->ward_type === 'gp' ? 'GP ' : '') . 'Ward ' . $pole->ward_number) : 'N/A') }}</td>
                                <td>{{ $pole->luminary_qr ?? 'N/A' }}</td>
                                <td>{{ $pole->sim_number ?? 'N/A' }}</td>
                                <td>{{ $pole->battery_qr ?? 'N/A' }}</td>
                                <td>{{ $pole->panel_qr ?? 'N/A' }}</td>
                                <td>{{ $pole->installed_at ? \Carbon\Carbon::parse($pole->installed_at)->format('d-M-Y') : 'N/A' }}</td>
                                <td>{{ $pole->rms_status ?? 'N/A' }}</td>
                                <td class="text-center">
                                    <a href="{{ route('poles.show', $pole->id) }}" class="btn btn-icon btn-info" data-bs-toggle="tooltip" title="View Details">
                                        <i class="mdi mdi-eye" aria-hidden="true"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </x-datatable>
                </div>

                <div class="tab-pane fade" id="surveyed" role="tabpanel" aria-labelledby="surveyed-tab">
                    <x-datatable
                        id="taskSurveyedPoles"
                        title=""
                        :columns="[
                            ['title' => 'Pole Number', 'width' => '14%'],
                            ['title' => 'Ward', 'width' => '8%'],
                            ['title' => 'Beneficiary', 'width' => '14%'],
                            ['title' => 'Beneficiary Contact', 'width' => '12%'],
                            ['title' => 'Surveyed At', 'width' => '10%'],
                            ['title' => 'Installed', 'width' => '8%'],
                            ['title' => 'Remarks', 'width' => '18%'],
                        ]"
                        :exportEnabled="true"
                        :importEnabled="false"
                        :bulkDeleteEnabled="false"
                        :actionsColumnEnabled="true"
                        pageLength="50"
                        searchPlaceholder="Search surveyed poles..."
                        :filters="[]"
                    >
                        @foreach ($surveyedPoleCollection as $pole)
                            <tr>
                                <td>
                                    @if ($pole->lat && $pole->lng)
                                        <button type="button" class="btn btn-link p-0 task-location-link" onclick="locateOnMap({{ $pole->lat }}, {{ $pole->lng }})">
                                            {{ $pole->complete_pole_number ?? 'N/A' }}
                                        </button>
                                    @else
                                        {{ $pole->complete_pole_number ?? 'N/A' }}
                                    @endif
                                </td>
                                <td>{{ $pole->ward_name ?? ($pole->ward_number ? (($pole->ward_type === 'gp' ? 'GP ' : '') . 'Ward ' . $pole->ward_number) : 'N/A') }}</td>
                                <td>{{ $pole->beneficiary ?? 'N/A' }}</td>
                                <td>{{ $pole->beneficiary_contact ?? 'N/A' }}</td>
                                <td>{{ $pole->surveyed_at ? \Carbon\Carbon::parse($pole->surveyed_at)->format('d-M-Y') : 'N/A' }}</td>
                                <td>
                                    <span class="badge bg-{{ $pole->isInstallationDone ? 'success' : 'secondary' }}">
                                        {{ $pole->isInstallationDone ? 'Yes' : 'No' }}
                                    </span>
                                </td>
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
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .task-detail-page {
        background: transparent;
    }

    .task-detail-page .card {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.05);
    }

    .task-title {
        color: #3f4654;
        font-size: 1.35rem;
        font-weight: 700;
        line-height: 1.25;
    }

    .task-section-title {
        color: #3f4654;
        font-size: 1rem;
        font-weight: 700;
    }

    .task-label {
        color: #7b8794;
        display: block;
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0;
        margin-bottom: 0.25rem;
        text-transform: uppercase;
    }

    .task-status-badge {
        align-items: center;
        border: 1px solid transparent;
        border-radius: 999px;
        display: inline-flex;
        font-size: 0.76rem;
        font-weight: 700;
        line-height: 1;
        min-height: 28px;
        padding: 0.45rem 0.75rem;
    }

    .task-status-pending {
        background: #fff3cd;
        border-color: #d39e00;
        color: #5c3b00;
    }

    .task-status-in-progress {
        background: #cfe2ff;
        border-color: #5b8def;
        color: #073b7a;
    }

    .task-status-completed {
        background: #d1e7dd;
        border-color: #479f76;
        color: #0f5132;
    }

    .task-info-grid {
        display: grid;
        gap: 0.85rem;
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .task-info-grid strong,
    .task-date-grid strong {
        color: #3f4654;
        display: block;
        font-size: 0.9rem;
        line-height: 1.35;
        overflow-wrap: anywhere;
    }

    .task-info-wide {
        grid-column: span 2;
    }

    .task-date-grid {
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        display: grid;
        gap: 0.75rem;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        min-width: min(100%, 360px);
        padding: 0.75rem;
    }

    .task-panel-icon {
        font-size: 1.75rem;
    }

    .task-avatar {
        border-radius: 50%;
        flex: 0 0 48px;
        height: 48px;
        object-fit: cover;
        width: 48px;
    }

    .task-avatar-empty {
        align-items: center;
        background: #f1f3f5;
        color: #6c757d;
        display: inline-flex;
        font-size: 1.4rem;
        justify-content: center;
    }

    .task-assignment-card {
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    .task-assignment-card:hover {
        border-color: #b7c8ff;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.08);
    }

    .task-person-name {
        color: #3f4654;
        font-size: 1rem;
        font-weight: 700;
        line-height: 1.25;
        overflow-wrap: anywhere;
    }

    .task-location-link {
        color: #0d6efd;
        font-size: inherit;
        text-align: left;
        text-decoration: none;
    }

    .task-location-link:hover {
        text-decoration: underline;
    }

    .task-detail-page .nav-tabs-modern {
        border-bottom: 1px solid #dee2e6;
        gap: 0.35rem;
    }

    .task-detail-page .nav-tabs-modern .nav-link {
        align-items: center;
        border-radius: 8px 8px 0 0;
        display: inline-flex;
        gap: 0.4rem;
        min-height: 40px;
    }

    .task-detail-page .datatable-wrapper {
        border: 0;
        box-shadow: none;
        padding-left: 0;
        padding-right: 0;
    }

    @media (max-width: 991.98px) {
        .task-info-grid,
        .task-date-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .task-info-wide {
            grid-column: span 2;
        }
    }

    @media (max-width: 575.98px) {
        .task-info-grid,
        .task-date-grid {
            grid-template-columns: 1fr;
        }

        .task-info-wide {
            grid-column: span 1;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    function locateOnMap(lat, lng) {
        if (lat && lng) {
            window.open(`https://www.google.com/maps?q=${lat},${lng}`, '_blank');
            return;
        }

        alert('Location coordinates not available.');
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (element) {
            new bootstrap.Tooltip(element);
        });
    });
</script>
@endpush
