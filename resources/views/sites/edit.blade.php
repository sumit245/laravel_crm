@extends('layouts.main')

@section('content')
    <div class="content-wrapper p-2">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="card-title mb-0">Update Site</h4>
                    <a href="{{ route('sites.index') }}" class="btn btn-light">
                        <i class="mdi mdi-arrow-left me-2"></i>Back to Sites
                    </a>
                </div>

                <!-- Display validation errors -->
                @if ($errors->any())
                    <script>
                        const validationErrors = {!! json_encode($errors->all()) !!};
                        Swal.fire({
                            title: 'Validation errors',
                            icon: 'error',
                            html: validationErrors.join('<br>'),
                            confirmButtonText: 'OK',
                            width: '600px'
                        });
                    </script>
                @endif

                @if (session('success'))
                    <script>
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: {!! json_encode(session('success')) !!},
                            showConfirmButton: false,
                            timer: 4000,
                            timerProgressBar: true
                        });
                    </script>
                @endif

                @if (session('error'))
                    <script>
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'error',
                            title: {!! json_encode(session('error')) !!},
                            showConfirmButton: false,
                            timer: 5000,
                            timerProgressBar: true
                        });
                    </script>
                @endif
                @if (isset($streetlight))
                    <form action="{{ route('sites.update', $streetlight->id) }}" method="POST" id="streetlightSiteForm">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="project_id" value="{{ $projectId }}">
                        <input type="hidden" name="ward" id="ward_hidden" value="{{ old('ward', $streetlight->ward ?? '') }}">

                        <h6 class="card-subtitle text-bold text-info">Streetlight Site Details</h6>
                        <div class="form-group row mt-5">
                            <div class="col-md-6">
                                <label for="state" class="form-label">State: <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('state') is-invalid @enderror" id="state"
                                    name="state" placeholder="Enter state" value="{{ old('state', $streetlight->state ?? '') }}"
                                    required>
                                @error('state')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="district" class="form-label">District: <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('district') is-invalid @enderror" id="district"
                                    name="district" placeholder="Enter district"
                                    value="{{ old('district', $streetlight->district ?? '') }}" required>
                                @error('district')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <div class="mt-2">
                                    <label for="district_code" class="form-label text-muted small">District Code:</label>
                                    <input type="text"
                                        class="form-control form-control-sm @error('district_code') is-invalid @enderror"
                                        id="district_code" name="district_code" placeholder="Prioritized for RMS Push"
                                        value="{{ old('district_code', $streetlight->district_code ?? '') }}">
                                    @error('district_code')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-6">
                                <label for="block" class="form-label">Block: <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('block') is-invalid @enderror" id="block"
                                    name="block" placeholder="Enter block" value="{{ old('block', $streetlight->block ?? '') }}"
                                    required>
                                @error('block')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <div class="mt-2">
                                    <label for="block_code" class="form-label text-muted small">Block Code:</label>
                                    <input type="text"
                                        class="form-control form-control-sm @error('block_code') is-invalid @enderror"
                                        id="block_code" name="block_code" placeholder="Prioritized for RMS Push"
                                        value="{{ old('block_code', $streetlight->block_code ?? '') }}">
                                    @error('block_code')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="panchayat" class="form-label">Panchayat: <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('panchayat') is-invalid @enderror" id="panchayat"
                                    name="panchayat" placeholder="Enter panchayat"
                                    value="{{ old('panchayat', $streetlight->panchayat ?? '') }}" required>
                                @error('panchayat')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <div class="mt-2">
                                    <label for="panchayat_code" class="form-label text-muted small">Panchayat Code:</label>
                                    <input type="text"
                                        class="form-control form-control-sm @error('panchayat_code') is-invalid @enderror"
                                        id="panchayat_code" name="panchayat_code" placeholder="Prioritized for RMS Push"
                                        value="{{ old('panchayat_code', $streetlight->panchayat_code ?? '') }}">
                                    @error('panchayat_code')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-6">
                                <label for="ward_input" class="form-label">Ward:</label>
                                <div class="ward-input-container">
                                    <div class="ward-chips-container mb-2" id="ward_chips_container">
                                        <!-- Chips will be added here dynamically -->
                                    </div>
                                    <input type="text" class="form-control" id="ward_input"
                                        placeholder="Enter ward number and press Space/Tab/Enter" autocomplete="off">
                                    <small class="form-text text-muted">Enter ward numbers one at a time. Press Space, Tab,
                                        or Enter to add.</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="total_poles" class="form-label">Total Poles:</label>
                                <input type="number" class="form-control" id="total_poles" name="total_poles"
                                    placeholder="Auto-calculated (10 per ward)"
                                    value="{{ old('total_poles', $streetlight->total_poles ?? '') }}" min="0">
                                <small class="form-text text-muted">Automatically calculated as 10 poles per ward. You can
                                    edit if the actual count differs.</small>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-6">
                                <label for="mukhiya_contact" class="form-label">Mukhiya Contact:</label>
                                <input type="text" class="form-control @error('mukhiya_contact') is-invalid @enderror"
                                    id="mukhiya_contact" name="mukhiya_contact" placeholder="Enter mukhiya contact (optional)"
                                    value="{{ old('mukhiya_contact', $streetlight->mukhiya_contact ?? '') }}">
                                @error('mukhiya_contact')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-4 d-flex justify-content-end">
                            <a href="{{ route('sites.index') }}" class="btn btn-light me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"
                                    id="submitSpinner"></span>
                                <span id="submitText">Update Streetlight Site</span>
                            </button>
                        </div>
                    </form>
                @else
                    <form action="{{ route('sites.update', $site->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Basic Details -->
                        <h6 class="card-subtitle text-bold text-info">Basic Details</h6>
                        <div class="col-md-4">
                            <label for="location">Breda serial code:</label>
                            <input type="text" class="form-control" id="location" name="location"
                                value="{{ old('breda_sl_no', $site->breda_sl_no) }}">
                        </div>
                        <div class="form-group row mt-5">
                            <div class="col-md-6">
                                <label for="state">State:</label>
                                <input type="text" class="form-control" id="state" name="state"
                                    value="{{ old('state', $site->state) }}" @disabled(true)>
                            </div>
                            <div class="col-md-6">
                                <label for="district">City:</label>
                                <input type="text" class="form-control" id="district" name="district"
                                    value="{{ old('district', $site->district) }}" @disabled(true)>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label for="location">Location:</label>
                                <input type="text" class="form-control" id="location" name="location"
                                    value="{{ old('location', $site->location) }}" @disabled(true)>
                            </div>
                            <div class="col-md-4">
                                <label for="project_id">Project Name:</label>
                                <input type="text" class="form-control" id="project_id" name="project_id"
                                    value="{{ old('project_id', $site->project_id) }}" @disabled(true)>
                            </div>
                            <div class="col-md-4">
                                <label for="site_name">Site Name:</label>
                                <input type="text" class="form-control" id="site_name" name="site_name"
                                    value="{{ old('site_name', $site->site_name) }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label for="vendorName">I&C Vendor Name:</label>
                                <input type="text" class="form-control" id="vendorName" name="ic_vendor_name"
                                    value="{{ old('vendorName', $site->ic_vendor_name) }}">
                            </div>
                            <div class="col-md-4">
                                <label for="site_engineer">Site Engineer:</label>
                                <input type="text" class="form-control" id="site_engineer" name="site_engineer"
                                    value="{{ old('site_engineer', $site->site_engineer) }}">
                            </div>
                            <div class="col-md-4">
                                <label for="contact">Contact No:</label>
                                <input type="text" class="form-control" id="contact" name="contact_no"
                                    value="{{ old('contact', $site->contact_no) }}">
                            </div>
                        </div>

                        <hr />

                        <!-- Project Details -->
                        <h6 class="card-subtitle text-bold text-info">Project Details</h6>
                        <div class="form-group row mt-5">
                            <div class="col-md-4">
                                <label for="meterNumber">Meter Number:</label>
                                <input type="text" class="form-control" id="meterNumber" name="meter_number"
                                    value="{{ old('meterNumber', $site->meter_number) }}">
                            </div>
                            <div class="col-md-4">
                                <label for="netMeterSI">Net Meter SI. No:</label>
                                <input type="text" class="form-control" id="netMeterSI" name="net_meter_sr_no"
                                    value="{{ old('netMeterSI', $site->net_meter_sr_no) }}">
                            </div>
                            <div class="col-md-4">
                                <label for="solarMeterSI">Solar Meter SI No:</label>
                                <input type="text" class="form-control" id="solarMeterSI" name="solar_meter_sr_no"
                                    value="{{ old('solarMeterSI', $site->solar_meter_sr_no) }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label for="capacity">Project Capacity:</label>
                                <input type="text" class="form-control" id="capacity" name="project_capacity"
                                    value="{{ old('capacity', $site->project_capacity) }}">
                            </div>
                            <div class="col-md-4">
                                <label for="caNumber">CA Number:</label>
                                <input type="text" class="form-control" id="caNumber" name="ca_number"
                                    value="{{ old('caNumber', $site->ca_number) }}">
                            </div>
                            <div class="col-md-4">
                                <label for="load">Sanction Load:</label>
                                <input type="text" class="form-control" id="load" name="sanction_load"
                                    value="{{ old('load', $site->sanction_load) }}">
                            </div>
                        </div>

                        <hr />

                        <!-- Status Information -->
                        <h6 class="card-subtitle text-bold text-info">Status Information</h6>
                        <div class="form-group row mt-5">
                            <div class="col-md-6">
                                <label for="loadStatus">Load Enhancement Status:</label>
                                <input type="text" class="form-control" id="loadStatus" name="load_enhancement_status"
                                    value="{{ old('loadStatus', $site->load_enhancement_status) }}">
                            </div>
                            <div class="col-md-6">
                                <label for="siteSurvey">Site Survey Status:</label>
                                <input type="text" class="form-control" id="siteSurvey" name="site_survey_status"
                                    value="{{ old('siteSurvey', $site->site_survey_status) }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label for="inspectionDate">Material Inspection Date:</label>
                                <input type="date" class="form-control" id="inspectionDate" name="material_inspection_date"
                                    value="{{ old('inspectionDate', $site->material_inspection_date) }}">
                            </div>
                            <div class="col-md-4">
                                <label for="installationDate">SPP Installation Date:</label>
                                <input type="date" class="form-control" id="installationDate" name="spp_installation_date"
                                    value="{{ old('installationDate', $site->spp_installation_date) }}">
                            </div>
                            <div class="col-md-4">
                                <label for="commissioningDate">Commissioning Date:</label>
                                <input type="date" class="form-control" id="commissioningDate" name="commissioning_date"
                                    value="{{ old('commissioningDate', $site->commissioning_date) }}">
                            </div>
                        </div>

                        <hr />

                        <div class="form-group">
                            <label for="remarks">Remarks:</label>
                            <textarea class="form-control @error('remarks') is-invalid @enderror" id="remarks" name="remarks"
                                rows="4">{{ old('remarks', $site->remarks) }}</textarea>
                            @error('remarks')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mt-4 d-flex justify-content-end">
                            <a href="{{ route('sites.index') }}" class="btn btn-light me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Site</button>
                        </div>
                    </form>
                @endif

            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        /* Consistent card styling to match theme */
        .content-wrapper .card {
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            border: 1px solid #e3e3e3;
        }

        /* Ward Chip Styles */
        .ward-input-container {
            position: relative;
        }

        .ward-chips-container {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            min-height: 2rem;
            padding: 0.5rem;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            background-color: #f8f9fa;
        }

        .ward-chip {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.5rem;
            background-color: #007bff;
            color: white;
            border-radius: 0.25rem;
            font-size: 0.875rem;
            gap: 0.5rem;
        }

        .ward-chip.editing {
            background-color: #ffc107;
            color: #212529;
        }

        .ward-chip-value {
            font-weight: 500;
        }

        .ward-chip-actions {
            display: inline-flex;
            gap: 0.25rem;
        }

        .ward-chip-btn {
            background: none;
            border: none;
            color: inherit;
            cursor: pointer;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            transition: background-color 0.2s;
        }

        .ward-chip-btn:hover {
            background-color: rgba(255, 255, 255, 0.3);
        }

        .ward-chip-btn.edit-btn {
            font-size: 0.75rem;
        }

        .ward-chip-btn.delete-btn {
            font-size: 0.875rem;
        }

        .ward-chip-input {
            width: 3rem;
            padding: 0.125rem 0.25rem;
            border: 1px solid #007bff;
            border-radius: 0.25rem;
            font-size: 0.875rem;
            text-align: center;
        }

        .ward-chip.success {
            background-color: #28a745;
            animation: chipSuccess 0.3s ease-in-out;
        }

        .ward-chip.error {
            background-color: #dc3545;
            animation: chipError 0.3s ease-in-out;
        }

        @keyframes chipSuccess {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.1);
            }

            100% {
                transform: scale(1);
            }
        }

        @keyframes chipError {

            0%,
            100% {
                transform: translateX(0);
            }

            25% {
                transform: translateX(-5px);
            }

            75% {
                transform: translateX(5px);
            }
        }

        .ward-input-container .form-control.is-invalid {
            border-color: #dc3545;
        }

        .ward-input-container .form-control.is-valid {
            border-color: #28a745;
        }

        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .form-control.is-invalid:focus {
            border-color: #dc3545;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
        }

        .form-control.is-valid:focus {
            border-color: #28a745;
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function () {
            // Streetlight form ward chip functionality
            @if (isset($streetlight))
                let wardChips = [];
                const wardInput = document.getElementById('ward_input');
                const wardChipsContainer = document.getElementById('ward_chips_container');
                const wardHiddenInput = document.getElementById('ward_hidden');
                const totalPolesInput = document.getElementById('total_poles');

                // Normalize ward value: allow positive integers and "GP"
                function normalizeWardValue(rawValue) {
                    if (!rawValue) return null;
                    let value = String(rawValue).trim().toUpperCase();

                    // Allow special ward code "GP"
                    if (value === 'GP') {
                        return 'GP';
                    }

                    // Allow only positive integer ward numbers
                    if (/^\d+$/.test(value)) {
                        const num = parseInt(value, 10);
                        if (num > 0) {
                            return String(num);
                        }
                    }

                    return null;
                }

                // Initialize from existing streetlight ward value
                @if (isset($streetlight) && $streetlight->ward)
                    const existingWards = '{{ $streetlight->ward }}'.split(',').map(w => w.trim()).filter(w => w);
                    existingWards.forEach(ward => {
                        const normalized = normalizeWardValue(ward);
                        if (normalized && !wardChips.includes(normalized)) {
                            wardChips.push(normalized);
                        }
                    });
                @endif

                    // Initialize from old input if exists (after validation errors)
                    @if (old('ward'))
                        const oldWards = '{{ old('ward') }}'.split(',').map(w => w.trim()).filter(w => w);
                        wardChips = [];
                        oldWards.forEach(ward => {
                            const normalized = normalizeWardValue(ward);
                            if (normalized && !wardChips.includes(normalized)) {
                                wardChips.push(normalized);
                            }
                        });
                    @endif

                    // Update hidden input and total poles
                    function updateWardData() {
                        // Sort for display: numeric wards ascending, then non-numeric (like GP)
                        const sorted = [...wardChips].sort((a, b) => {
                            const aIsNum = /^\d+$/.test(a);
                            const bIsNum = /^\d+$/.test(b);
                            if (aIsNum && bIsNum) {
                                return parseInt(a, 10) - parseInt(b, 10);
                            }
                            if (aIsNum && !bIsNum) return -1;
                            if (!aIsNum && bIsNum) return 1;
                            return a.localeCompare(b);
                        });

                        wardHiddenInput.value = sorted.join(',');
                        // Each ward entry (numeric or GP) contributes 10 poles by default
                        totalPolesInput.value = wardChips.length * 10;
                    }

                // Show toast notification
                function showToast(icon, message) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: icon,
                        title: message,
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                        didOpen: (toast) => {
                            toast.addEventListener('mouseenter', Swal.stopTimer);
                            toast.addEventListener('mouseleave', Swal.resumeTimer);
                        }
                    });
                }

                // Add ward chip
                function addWardChip(rawWardValue) {
                    const wardValue = normalizeWardValue(rawWardValue);
                    if (!wardValue) {
                        showToast('error', 'Please enter a valid ward (number or GP)');
                        wardInput.classList.add('is-invalid');
                        setTimeout(() => wardInput.classList.remove('is-invalid'), 2000);
                        return false;
                    }
                    if (wardChips.includes(wardValue)) {
                        showToast('warning', `Ward ${wardValue} already exists`);
                        wardInput.classList.add('is-invalid');
                        setTimeout(() => wardInput.classList.remove('is-invalid'), 2000);
                        return false;
                    }
                    wardChips.push(wardValue);
                    renderChips();
                    updateWardData();

                    const chipElement = wardChipsContainer.querySelector(`[data-ward="${wardValue}"]`);
                    if (chipElement) {
                        chipElement.classList.add('success');
                        setTimeout(() => chipElement.classList.remove('success'), 1000);
                    }

                    showToast('success', `Ward ${wardValue} added successfully`);
                    wardInput.classList.add('is-valid');
                    setTimeout(() => wardInput.classList.remove('is-valid'), 2000);
                    return true;
                }

                // Remove ward chip
                function removeWardChip(wardValue) {
                    wardChips = wardChips.filter(w => w !== wardValue);
                    renderChips();
                    updateWardData();
                    showToast('info', `Ward ${wardValue} removed`);
                }

                // Edit ward chip
                function editWardChip(oldValue, chipElement) {
                    const chipValue = chipElement.querySelector('.ward-chip-value');
                    const chipActions = chipElement.querySelector('.ward-chip-actions');
                    const input = document.createElement('input');
                    input.type = 'text';
                    input.className = 'ward-chip-input';
                    input.value = oldValue;
                    input.maxLength = 10;

                    input.addEventListener('input', function (e) {
                        // Allow only alphanumeric and convert to uppercase (for "GP" or numbers)
                        e.target.value = e.target.value.replace(/[^0-9a-zA-Z]/g, '').toUpperCase();
                    });

                    chipElement.classList.add('editing');
                    chipValue.style.display = 'none';
                    chipActions.style.display = 'none';
                    chipElement.appendChild(input);
                    input.focus();
                    input.select();

                    const saveEdit = () => {
                        const rawNew = input.value.trim();
                        const newValue = normalizeWardValue(rawNew);
                        if (newValue) {
                            if (newValue === oldValue) {
                                renderChips();
                            } else if (!wardChips.includes(newValue)) {
                                wardChips = wardChips.map(w => w === oldValue ? newValue : w);
                                renderChips();
                                updateWardData();

                                const chipElement = wardChipsContainer.querySelector(
                                    `[data-ward="${newValue}"]`);
                                if (chipElement) {
                                    chipElement.classList.add('success');
                                    setTimeout(() => chipElement.classList.remove('success'), 1000);
                                }

                                showToast('success', `Ward updated from ${oldValue} to ${newValue}`);
                            } else {
                                showToast('warning', `Ward ${newValue} already exists. Removing duplicate.`);
                                removeWardChip(oldValue);
                            }
                        } else {
                            showToast('error', 'Invalid ward. Edit cancelled.');
                            renderChips();
                        }
                    };

                    input.addEventListener('blur', saveEdit);
                    input.addEventListener('keydown', function (e) {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            saveEdit();
                        } else if (e.key === 'Escape') {
                            e.preventDefault();
                            renderChips();
                        }
                    });
                }

                // Render all chips
                function renderChips() {
                    wardChipsContainer.innerHTML = '';
                    // Keep same visual order as stored in wardChips
                    wardChips.forEach(ward => {
                        const chip = document.createElement('div');
                        chip.className = 'ward-chip';
                        chip.dataset.ward = ward;
                        chip.innerHTML = `
                                  <span class="ward-chip-value">${ward}</span>
                                  <div class="ward-chip-actions">
                                      <button type="button" class="ward-chip-btn edit-btn" 
                                          data-action="edit" 
                                          title="Edit">✎</button>
                                      <button type="button" class="ward-chip-btn delete-btn" 
                                          data-action="delete" 
                                          title="Delete">×</button>
                                  </div>
                              `;
                        wardChipsContainer.appendChild(chip);
                    });
                }

                // Event delegation for chip actions
                wardChipsContainer.addEventListener('click', function (e) {
                    const button = e.target.closest('.ward-chip-btn');
                    if (!button) return;

                    const chip = button.closest('.ward-chip');
                    const wardValue = chip.dataset.ward;
                    const action = button.dataset.action;

                    if (action === 'edit') {
                        editWardChip(wardValue, chip);
                    } else if (action === 'delete') {
                        removeWardChip(wardValue);
                    }
                });

                // Handle input events
                wardInput.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter' || e.key === 'Tab' || e.key === ' ') {
                        e.preventDefault();
                        const value = wardInput.value.trim();
                        const normalized = normalizeWardValue(value);
                        if (normalized) {
                            if (addWardChip(normalized)) {
                                wardInput.value = '';
                                wardInput.classList.remove('is-invalid');
                            }
                        } else if (value) {
                            wardInput.classList.add('is-invalid');
                            showToast('error', 'Please enter a valid ward (number or GP)');
                        }
                    }
                });

                // Real-time validation for all required fields
                const requiredFields = ['state', 'district', 'block', 'panchayat'];
                requiredFields.forEach(fieldId => {
                    const field = document.getElementById(fieldId);
                    if (field) {
                        field.addEventListener('blur', function () {
                            if (this.value.trim()) {
                                this.classList.remove('is-invalid');
                                this.classList.add('is-valid');
                            } else {
                                this.classList.remove('is-valid');
                                this.classList.add('is-invalid');
                            }
                        });

                        field.addEventListener('input', function () {
                            if (this.value.trim()) {
                                this.classList.remove('is-invalid');
                            }
                        });
                    }
                });

                // Form validation on submit
                const streetlightForm = document.getElementById('streetlightSiteForm');
                if (streetlightForm) {
                    streetlightForm.addEventListener('submit', function (e) {
                        const submitBtn = document.getElementById('submitBtn');
                        const submitSpinner = document.getElementById('submitSpinner');
                        const submitText = document.getElementById('submitText');

                        let isValid = true;
                        const requiredFields = ['state', 'district', 'block', 'panchayat'];

                        requiredFields.forEach(fieldId => {
                            const field = document.getElementById(fieldId);
                            if (!field.value.trim()) {
                                field.classList.add('is-invalid');
                                isValid = false;
                            } else {
                                field.classList.remove('is-invalid');
                                field.classList.add('is-valid');
                            }
                        });

                        if (!isValid) {
                            e.preventDefault();
                            showToast('error', 'Please fill in all required fields');
                            return false;
                        }

                        submitBtn.disabled = true;
                        submitSpinner.classList.remove('d-none');
                        submitText.textContent = 'Updating Site...';
                    });
                }

                // Allow numbers and special code "GP" and filter invalid characters
                wardInput.addEventListener('input', function (e) {
                    // Allow digits and letters for special ward "GP"
                    e.target.value = e.target.value.replace(/[^0-9a-zA-Z]/g, '').toUpperCase();

                    const value = e.target.value.trim();
                    const normalized = value ? normalizeWardValue(value) : null;
                    if (normalized) {
                        e.target.classList.remove('is-invalid');
                        e.target.classList.add('is-valid');
                    } else if (value) {
                        e.target.classList.remove('is-valid');
                        e.target.classList.add('is-invalid');
                    } else {
                        e.target.classList.remove('is-valid', 'is-invalid');
                    }
                });

                // Prevent paste of invalid content
                wardInput.addEventListener('paste', function (e) {
                    e.preventDefault();
                    const paste = (e.clipboardData || window.clipboardData).getData('text');
                    const cleaned = paste.replace(/[^0-9a-zA-Z]/g, '').toUpperCase();
                    if (cleaned) {
                        e.target.value = cleaned;
                        e.target.dispatchEvent(new Event('input'));
                    }
                });

                // Initial render and update
                renderChips();
                updateWardData();
            @endif
                });
    </script>
@endpush