@extends('layouts.main')

@section('content')
    <div class="content-wrapper p-2">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Add Site</h4>

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

                @if (isset($projectType) && $projectType == 1)
                    <!-- Streetlight Project Form -->
                    <form action="{{ route('sites.store') }}" method="POST" id="streetlightSiteForm">
                        @csrf
                        <input type="hidden" name="project_id" value="{{ $project->id ?? '' }}">
                        <input type="hidden" name="ward" id="ward_hidden" value="{{ old('ward') }}">

                        <h6 class="card-subtitle text-bold text-info">Streetlight Site Details</h6>
                        <div class="form-group row mt-5">
                            <div class="col-md-6">
                                <label for="state" class="form-label">State: <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('state') is-invalid @enderror" id="state" name="state"
                                    placeholder="Enter state" value="{{ old('state') }}" required>
                                @error('state')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="district" class="form-label">District: <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('district') is-invalid @enderror" id="district" name="district"
                                    placeholder="Enter district" value="{{ old('district') }}" required>
                                @error('district')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-6">
                                <label for="block" class="form-label">Block: <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('block') is-invalid @enderror" id="block" name="block"
                                    placeholder="Enter block" value="{{ old('block') }}" required>
                                @error('block')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="panchayat" class="form-label">Panchayat: <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('panchayat') is-invalid @enderror" id="panchayat" name="panchayat"
                                    placeholder="Enter panchayat" value="{{ old('panchayat') }}" required>
                                @error('panchayat')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
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
                                        placeholder="Enter ward number and press Space/Tab/Enter" 
                                        autocomplete="off">
                                    <small class="form-text text-muted">Enter ward numbers one at a time. Press Space, Tab, or Enter to add.</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="total_poles" class="form-label">Total Poles:</label>
                                <input type="number" class="form-control" id="total_poles" name="total_poles"
                                    placeholder="Auto-calculated (10 per ward)" value="{{ old('total_poles') }}" 
                                    min="0">
                                <small class="form-text text-muted">Automatically calculated as 10 poles per ward. You can edit if the actual count differs.</small>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-6">
                                <label for="mukhiya_contact" class="form-label">Mukhiya Contact:</label>
                                <input type="text" class="form-control @error('mukhiya_contact') is-invalid @enderror" id="mukhiya_contact" name="mukhiya_contact"
                                    placeholder="Enter mukhiya contact (optional)" value="{{ old('mukhiya_contact') }}">
                                @error('mukhiya_contact')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true" id="submitSpinner"></span>
                            <span id="submitText">Add Streetlight Site</span>
                        </button>
                    </form>
                @else
                    <!-- Rooftop Project Form -->
                    <form action="{{ route('sites.store') }}" method="POST">
                        @csrf

                        <!-- Basic Details -->
                        <h6 class="card-subtitle text-bold text-info">Basic Details</h6>
                        <div class="form-group row mt-5">
                            <div class="col-md-6">
                                <label for="state" class="form-label">State:</label>
                                <select class="form-select" id="state" name="state">
                                    <option value="">Select State</option>
                                    @foreach ($states as $state)
                                        <option value="{{ $state->id }}">{{ $state->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="district" class="form-label">City:</label>
                                <select class="form-select" id="district" name="district">
                                    <option value="">Select City</option>
                                    <!-- Cities will be dynamically populated using JavaScript -->
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label for="location" class="form-label">Location:</label>
                                <input type="text" class="form-control" id="location" placeholder="Enter location"
                                    name="location" value="{{ old('location') }}">
                            </div>
                            <div class="col-md-4">
                                <label for="project_id" class="form-label">Project Name:</label>
                                <select class="form-select" id="project_id" name="project_id"
                                    {{ isset($project) && $project ? 'disabled' : '' }}>
                                    <option value="">Select Project</option>
                                    @foreach ($projects as $proj)
                                        <option value="{{ $proj->id }}"
                                            {{ (isset($project) && $project && $project->id == $proj->id) || old('project_id') == $proj->id ? 'selected' : '' }}>
                                            {{ $proj->project_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @if (isset($project) && $project)
                                    <input type="hidden" name="project_id" value="{{ $project->id }}">
                                @endif
                            </div>
                            <div class="col-md-4">
                                <label for="site_name" class="form-label">Site Name:</label>
                                <input type="text" class="form-control" id="site_name" placeholder="Enter site name"
                                    name="site_name" value="{{ old('site_name') }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label for="ic_vendor_name" class="form-label">I&C Vendor Name</label>
                                <select class="form-select" id="ic_vendor_name" name="ic_vendor_name">
                                    <option value="">Select Vendor</option>
                                    @foreach ($vendors as $vendor)
                                        <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="site_engineer" class="form-label">Site Engineer</label>
                                <select class="form-select" id="site_engineer" name="site_engineer">
                                    <option value="">Select Site Engineer</option>
                                    @foreach ($staffs as $staff)
                                        <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="contact" class="form-label">Contact No:</label>
                                <input type="text" class="form-control" id="contact"
                                    placeholder="Enter contact number" name="contact_no"
                                    value="{{ old('contact_no') }}">
                            </div>
                        </div>

                        <hr />

                        <!-- Project Details -->
                        <h6 class="card-subtitle text-bold text-info">Project Details</h6>
                        <div class="form-group row mt-5">
                            <div class="col-md-4">
                                <label for="meterNumber" class="form-label">Meter Number:</label>
                                <input type="text" class="form-control" id="meterNumber"
                                    placeholder="Enter meter number" name="meter_number"
                                    value="{{ old('meter_number') }}">
                            </div>
                            <div class="col-md-4">
                                <label for="netMeterSI" class="form-label">Net Meter SI. No:</label>
                                <input type="text" class="form-control" id="netMeterSI"
                                    placeholder="Enter net meter SI number" name="net_meter_sr_no"
                                    value="{{ old('net_meter_sr_no') }}">
                            </div>
                            <div class="col-md-4">
                                <label for="solarMeterSI" class="form-label">Solar Meter SI No:</label>
                                <input type="text" class="form-control" id="solarMeterSI"
                                    placeholder="Enter solar meter SI number" name="solar_meter_sr_no"
                                    value="{{ old('solar_meter_sr_no') }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label for="capacity" class="form-label">Project Capacity:</label>
                                <input type="text" class="form-control" id="capacity"
                                    placeholder="Enter project capacity" name="project_capacity"
                                    value="{{ old('project_capacity') }}">
                            </div>
                            <div class="col-md-4">
                                <label for="caNumber" class="form-label">CA Number:</label>
                                <input type="text" class="form-control" id="caNumber" placeholder="Enter CA number"
                                    name="ca_number" value="{{ old('ca_number') }}">
                            </div>
                            <div class="col-md-4">
                                <label for="load" class="form-label">Sanction Load:</label>
                                <input type="text" class="form-control" id="load"
                                    placeholder="Enter sanction load" name="sanction_load"
                                    value="{{ old('sanction_load') }}">
                            </div>
                        </div>

                        <hr />

                        <!-- Load Enhancement Status and Site Survey Status Section -->
                        <h6 class="card-subtitle text-bold text-info">Status Information</h6>
                        <div class="form-group row mt-5">

                            <div class="col-md-6">
                                <label for="loadStatus" class="form-label">Load Enhancement Status:</label>
                                <select class="form-select" id="loadStatus" name="load_enhancement_status">
                                    <option value="">-- Select Status --</option>
                                    <option value="Yes"
                                        {{ old('load_enhancement_status') == 'Yes' ? 'selected' : '' }}>Yes</option>
                                    <option value="No" {{ old('load_enhancement_status') == 'No' ? 'selected' : '' }}>
                                        No</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="siteSurvey" class="form-label">Site Survey Status:</label>
                                <input type="text" class="form-control" id="siteSurvey"
                                    placeholder="Enter site survey status" name="site_survey_status"
                                    value="{{ old('site_survey_status') }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label for="inspectionDate" class="form-label">Material Inspection Date:</label>
                                <input onclick="document.getElementById('inspectionDate').showPicker()" type="date"
                                    class="form-control navbar-date-picker" id="inspectionDate"
                                    name="material_inspection_date" value="{{ old('material_inspection_date') }}">
                            </div>
                            <div class="col-md-4">
                                <label for="installationDate" class="form-label">SPP Installation Date:</label>
                                <input onclick="document.getElementById('installationDate').showPicker()" type="date"
                                    class="form-control" id="installationDate" name="spp_installation_date"
                                    value="{{ old('spp_installation_date') }}">
                            </div>
                            <div class="col-md-4">
                                <label for="commissioningDate" class="form-label">Commissioning Date:</label>
                                <input onclick="document.getElementById('commissioningDate').showPicker()" type="date"
                                    class="form-control" id="commissioningDate" name="commissioning_date"
                                    value="{{ old('commissioning_date') }}">
                            </div>
                        </div>

                        <hr />

                        <div class="form-group">
                            <label for="remarks" class="form-label">Remarks:</label>
                            <textarea class="form-control" style="height:80px;" id="remarks" placeholder="Enter remarks" name="remarks"
                                rows="16" cols="50">{{ old('remarks') }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">Add Site</button>
                    </form>
                @endif

            </div>
        </div>
    </div>

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
                0% { transform: scale(1); }
                50% { transform: scale(1.1); }
                100% { transform: scale(1); }
            }

            @keyframes chipError {
                0%, 100% { transform: translateX(0); }
                25% { transform: translateX(-5px); }
                75% { transform: translateX(5px); }
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
            $(document).ready(function() {
                // Rooftop form state/city dropdown
                $('#state').on('change', function() {
                    var idState = this.value;
                    $("#district").html('');
                    $.ajax({
                        url: "{{ url('api/fetch-cities') }}",
                        type: "POST",
                        data: {
                            state_id: idState,
                            _token: '{{ csrf_token() }}'
                        },
                        dataType: 'json',
                        success: function(res) {
                            $('#district').html('<option value="">-- Select City --</option>');
                            $.each(res.cities, function(key, value) {
                                $("#district").append('<option value="' + value.id + '">' +
                                    value.name + '</option>');
                            });
                        }
                    });
                });

                // Streetlight form ward chip functionality
                @if (isset($projectType) && $projectType == 1)
                let wardChips = [];
                const wardInput = document.getElementById('ward_input');
                const wardChipsContainer = document.getElementById('ward_chips_container');
                const wardHiddenInput = document.getElementById('ward_hidden');
                const totalPolesInput = document.getElementById('total_poles');

                // Initialize from old input if exists
                @if (old('ward'))
                    const oldWards = '{{ old('ward') }}'.split(',').map(w => w.trim()).filter(w => w);
                    oldWards.forEach(ward => {
                        if (ward && !isNaN(ward)) {
                            addWardChip(parseInt(ward));
                        }
                    });
                @endif

                // Update hidden input and total poles
                function updateWardData() {
                    const wardString = wardChips.sort((a, b) => a - b).join(',');
                    wardHiddenInput.value = wardString;
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
                function addWardChip(wardNumber) {
                    if (isNaN(wardNumber) || wardNumber <= 0) {
                        showToast('error', 'Please enter a valid ward number');
                        wardInput.classList.add('is-invalid');
                        setTimeout(() => wardInput.classList.remove('is-invalid'), 2000);
                        return false;
                    }
                    if (wardChips.includes(wardNumber)) {
                        showToast('warning', `Ward ${wardNumber} already exists`);
                        wardInput.classList.add('is-invalid');
                        setTimeout(() => wardInput.classList.remove('is-invalid'), 2000);
                        return false; // Duplicate
                    }
                    wardChips.push(wardNumber);
                    renderChips();
                    updateWardData();
                    
                    // Show success feedback
                    const chipElement = wardChipsContainer.querySelector(`[data-ward="${wardNumber}"]`);
                    if (chipElement) {
                        chipElement.classList.add('success');
                        setTimeout(() => chipElement.classList.remove('success'), 1000);
                    }
                    
                    showToast('success', `Ward ${wardNumber} added successfully`);
                    wardInput.classList.add('is-valid');
                    setTimeout(() => wardInput.classList.remove('is-valid'), 2000);
                    return true;
                }

                // Remove ward chip
                function removeWardChip(wardNumber) {
                    wardChips = wardChips.filter(w => w !== wardNumber);
                    renderChips();
                    updateWardData();
                    showToast('info', `Ward ${wardNumber} removed`);
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
                    
                    // Only allow numbers in edit input
                    input.addEventListener('input', function(e) {
                        e.target.value = e.target.value.replace(/[^0-9]/g, '');
                    });
                    
                    chipElement.classList.add('editing');
                    chipValue.style.display = 'none';
                    chipActions.style.display = 'none';
                    chipElement.appendChild(input);
                    input.focus();
                    input.select();

                    const saveEdit = () => {
                        const newValue = parseInt(input.value);
                        if (!isNaN(newValue) && newValue > 0) {
                            if (newValue === oldValue) {
                                // No change, just re-render
                                renderChips();
                            } else if (!wardChips.includes(newValue)) {
                                // Valid new value, update
                                wardChips = wardChips.map(w => w === oldValue ? newValue : w);
                                renderChips();
                                updateWardData();
                                
                                // Show success feedback
                                const chipElement = wardChipsContainer.querySelector(`[data-ward="${newValue}"]`);
                                if (chipElement) {
                                    chipElement.classList.add('success');
                                    setTimeout(() => chipElement.classList.remove('success'), 1000);
                                }
                                
                                showToast('success', `Ward updated from ${oldValue} to ${newValue}`);
                            } else {
                                // Duplicate value, remove the old one (new value already exists)
                                showToast('warning', `Ward ${newValue} already exists. Removing duplicate.`);
                                removeWardChip(oldValue);
                            }
                        } else {
                            // Invalid value, cancel edit and restore original
                            showToast('error', 'Invalid ward number. Edit cancelled.');
                            renderChips();
                        }
                    };

                    input.addEventListener('blur', saveEdit);
                    input.addEventListener('keydown', function(e) {
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
                    wardChips.sort((a, b) => a - b).forEach(ward => {
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
                wardChipsContainer.addEventListener('click', function(e) {
                    const button = e.target.closest('.ward-chip-btn');
                    if (!button) return;
                    
                    const chip = button.closest('.ward-chip');
                    const wardNumber = parseInt(chip.dataset.ward);
                    const action = button.dataset.action;

                    if (action === 'edit') {
                        editWardChip(wardNumber, chip);
                    } else if (action === 'delete') {
                        removeWardChip(wardNumber);
                    }
                });

                // Handle input events
                wardInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' || e.key === 'Tab' || e.key === ' ') {
                        e.preventDefault();
                        const value = wardInput.value.trim();
                        if (value && !isNaN(value) && parseInt(value) > 0) {
                            if (addWardChip(parseInt(value))) {
                                wardInput.value = '';
                                wardInput.classList.remove('is-invalid');
                            }
                        } else if (value) {
                            wardInput.classList.add('is-invalid');
                            showToast('error', 'Please enter a valid ward number');
                        }
                    }
                });

                // Real-time validation for all required fields
                const requiredFields = ['state', 'district', 'block', 'panchayat'];
                requiredFields.forEach(fieldId => {
                    const field = document.getElementById(fieldId);
                    if (field) {
                        field.addEventListener('blur', function() {
                            if (this.value.trim()) {
                                this.classList.remove('is-invalid');
                                this.classList.add('is-valid');
                            } else {
                                this.classList.remove('is-valid');
                                this.classList.add('is-invalid');
                            }
                        });
                        
                        field.addEventListener('input', function() {
                            if (this.value.trim()) {
                                this.classList.remove('is-invalid');
                            }
                        });
                    }
                });

                // Form validation on submit
                const streetlightForm = document.getElementById('streetlightSiteForm');
                if (streetlightForm) {
                    streetlightForm.addEventListener('submit', function(e) {
                        const submitBtn = document.getElementById('submitBtn');
                        const submitSpinner = document.getElementById('submitSpinner');
                        const submitText = document.getElementById('submitText');
                        
                        // Basic validation
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

                        // Show loading state
                        submitBtn.disabled = true;
                        submitSpinner.classList.remove('d-none');
                        submitText.textContent = 'Adding Site...';
                    });
                }

                // Only allow numbers and filter non-numeric characters
                wardInput.addEventListener('input', function(e) {
                    // Filter non-numeric characters
                    e.target.value = e.target.value.replace(/[^0-9]/g, '');
                    
                    // Real-time validation
                    const value = e.target.value.trim();
                    if (value && !isNaN(value) && parseInt(value) > 0) {
                        e.target.classList.remove('is-invalid');
                        e.target.classList.add('is-valid');
                    } else if (value) {
                        e.target.classList.remove('is-valid');
                        e.target.classList.add('is-invalid');
                    } else {
                        e.target.classList.remove('is-valid', 'is-invalid');
                    }
                });

                // Prevent paste of non-numeric content
                wardInput.addEventListener('paste', function(e) {
                    e.preventDefault();
                    const paste = (e.clipboardData || window.clipboardData).getData('text');
                    const numbers = paste.replace(/[^0-9]/g, '');
                    if (numbers) {
                        e.target.value = numbers;
                        // Trigger input event for validation
                        e.target.dispatchEvent(new Event('input'));
                    }
                });

                // Initialize total poles if no old value
                @if (!old('total_poles'))
                    updateWardData();
                @endif
                @endif
            });
        </script>
    @endpush
@endsection
