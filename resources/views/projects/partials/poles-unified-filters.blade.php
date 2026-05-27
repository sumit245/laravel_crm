@php
    $tab = $tab ?? 'installed';
    $geo = $poleGeoFilters ?? [];
    $deepLink = $poleDeepLinkParams ?? [];
    $showBaseUrl = route('projects.show', $project);
@endphp

<div class="card mb-3 project-poles-filter-card" data-pole-filter-tab="{{ $tab }}">
    <div class="card-body py-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h6 class="card-title mb-0">Filters</h6>
            @if (!empty($geo['district']) || !empty($geo['block']) || !empty($geo['panchayat']) || request()->filled('filter_billed') || request()->filled('filter_rms') || ($tab === 'surveyed' && request()->filled('filter_installed')))
                <div class="d-flex flex-wrap gap-1 align-items-center project-pole-active-filters">
                    <span class="project-pole-active-filters-label small mb-0 me-1">Active:</span>
                    @foreach (['district' => 'District', 'block' => 'Block', 'panchayat' => 'Panchayat'] as $key => $label)
                        @if (!empty($geo[$key]))
                            <span class="badge project-pole-active-badge">{{ $label }}: {{ $geo[$key] }}</span>
                        @endif
                    @endforeach
                    @if ($tab === 'surveyed' && request()->filled('filter_installed'))
                        <span class="badge project-pole-active-badge">Installed: {{ request('filter_installed') === '1' ? 'Yes' : 'No' }}</span>
                    @endif
                    @if (request()->filled('filter_billed'))
                        <span class="badge project-pole-active-badge">Bill raised: {{ request('filter_billed') === '1' ? 'Yes' : 'No' }}</span>
                    @endif
                    @if (request()->filled('filter_rms'))
                        <span class="badge project-pole-active-badge">RMS: {{ ucfirst(request('filter_rms')) }}</span>
                    @endif
                </div>
            @endif
        </div>

        <div class="row g-3 align-items-end">
            <div class="col-md-6 col-lg-3">
                <label for="poleDistrict-{{ $tab }}" class="form-label">District</label>
                <select id="poleDistrict-{{ $tab }}" class="form-select project-pole-district" data-tab="{{ $tab }}">
                    <option value="">All districts</option>
                    @foreach ($poleDistricts as $districtRow)
                        <option value="{{ $districtRow->district }}" @selected(($geo['district'] ?? '') === $districtRow->district)>
                            {{ $districtRow->district }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 col-lg-3">
                <label for="poleBlock-{{ $tab }}" class="form-label">Block</label>
                <select id="poleBlock-{{ $tab }}" class="form-select project-pole-block" data-tab="{{ $tab }}" disabled>
                    <option value="">All blocks</option>
                </select>
            </div>
            <div class="col-md-6 col-lg-3">
                <label for="polePanchayat-{{ $tab }}" class="form-label">Panchayat</label>
                <select id="polePanchayat-{{ $tab }}" class="form-select project-pole-panchayat" data-tab="{{ $tab }}" disabled>
                    <option value="">All panchayats</option>
                </select>
            </div>

            @if ($tab === 'surveyed')
                <div class="col-md-6 col-lg-3">
                    <label for="poleFilterInstalled-{{ $tab }}" class="form-label">Installation status</label>
                    <select id="poleFilterInstalled-{{ $tab }}" class="form-select project-pole-status-filter" data-param="filter_installed">
                        <option value="" @selected(!request()->filled('filter_installed'))>All</option>
                        <option value="1" @selected(request('filter_installed') === '1')>Installed</option>
                        <option value="0" @selected(request('filter_installed') === '0')>Not installed</option>
                    </select>
                </div>
            @endif

            <div class="col-md-6 col-lg-3">
                <label for="poleFilterBilled-{{ $tab }}" class="form-label">Bill raised</label>
                <select id="poleFilterBilled-{{ $tab }}" class="form-select project-pole-status-filter" data-param="filter_billed">
                    <option value="" @selected(!request()->filled('filter_billed'))>All</option>
                    <option value="1" @selected(request('filter_billed') === '1')>Yes</option>
                    <option value="0" @selected(request('filter_billed') === '0')>No</option>
                </select>
            </div>
            <div class="col-md-6 col-lg-3">
                <label for="poleFilterRms-{{ $tab }}" class="form-label">RMS status</label>
                <select id="poleFilterRms-{{ $tab }}" class="form-select project-pole-status-filter" data-param="filter_rms">
                    <option value="" @selected(!request()->filled('filter_rms'))>All</option>
                    <option value="success" @selected(request('filter_rms') === 'success')>Success</option>
                    <option value="error" @selected(request('filter_rms') === 'error')>Error</option>
                    <option value="pending" @selected(request('filter_rms') === 'pending')>Pending</option>
                    <option value="partial" @selected(request('filter_rms') === 'partial')>Partial</option>
                </select>
            </div>

            <div class="col-12 col-lg-auto d-flex flex-wrap gap-2 ms-lg-auto">
                <button type="button" class="btn btn-primary project-pole-apply-btn" data-tab="{{ $tab }}">
                    <i class="mdi mdi-filter" aria-hidden="true"></i> Apply filters
                </button>
                <button type="button" class="btn btn-outline-secondary project-pole-clear-btn" data-tab="{{ $tab }}">
                    Clear filters
                </button>
            </div>
        </div>
    </div>
</div>
