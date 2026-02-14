@extends('layouts.main')

@section('content')
    <div class="content-wrapper p-2">
        {{-- Display Success/Error Alerts --}}
        @if (session('success') || session('error'))
            <div class="alert alert-{{ session('success') ? 'success' : 'danger' }} alert-dismissible fade show" role="alert">
                {{ session('success', session('error')) }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card p-2">
            <form id="rmsForm" action="{{ route('rms.push') }}" method="POST">
                @csrf
                {{-- Hidden input for project_id is removed as we use the select dropdown --}}

                <div class="row p-4">
                    <div class="col-sm-12 mb-3">
                        <label for="projectSelect" class="form-label">Project</label>
                        <select id="projectSelect" class="form-select select2" name="project_id" style="width: 100%;"
                            required>
                            <option value="">Select a Project</option>
                            @foreach ($projects as $project)
                                <option value="{{ $project->id }}" {{ $project_id == $project->id ? 'selected' : '' }}>
                                    {{ $project->project_name }} ({{ $project->project_type == 1 ? 'Streetlight' : 'Other' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row p-4">
                    <div class="col-sm-4">
                        <div class="mb-3">
                            <label for="districtSelect" class="form-label">District</label>
                            <select id="districtSelect" class="form-select select2" name="district" style="width: 100%;"
                                required {{ !$project_id ? 'disabled' : '' }}>
                                <option value="">Select a District</option>
                                @foreach ($districts as $district)
                                    <option value="{{ $district->district }}">{{ $district->district }}</option>
                                @endforeach
                            </select>
                            {{-- Dynamic Code Input/Display --}}
                            <div id="districtCodeContainer" class="mt-2" style="display: none;">
                                <small class="text-success" id="districtCodeDisplay" style="display: none;"></small>
                                <div id="districtCodeInputDiv" style="display: none;">
                                    <label class="form-label text-danger">District Code (Required)*</label>
                                    <input type="text" id="districtCodeInput" name="district_code" class="form-control"
                                        placeholder="Enter District Code">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="mb-3">
                            <label for="blockSelect" class="form-label">Block</label>
                            <select id="blockSelect" class="form-select select2" name="block" style="width: 100%;" disabled
                                required>
                                <option value="">Select a Block</option>
                            </select>
                            {{-- Dynamic Code Input/Display --}}
                            <div id="blockCodeContainer" class="mt-2" style="display: none;">
                                <small class="text-success" id="blockCodeDisplay" style="display: none;"></small>
                                <div id="blockCodeInputDiv" style="display: none;">
                                    <label class="form-label text-danger">Block Code (Required)*</label>
                                    <input type="text" id="blockCodeInput" name="block_code" class="form-control"
                                        placeholder="Enter Block Code">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="mb-3">
                            <label for="panchayatSelect" class="form-label">Panchayat</label>
                            <select id="panchayatSelect" class="form-select select2" name="panchayat" style="width: 100%;"
                                disabled required>
                                <option value="">Select a Panchayat</option>
                            </select>
                            {{-- Dynamic Code Input/Display --}}
                            <div id="panchayatCodeContainer" class="mt-2" style="display: none;">
                                <small class="text-success" id="panchayatCodeDisplay" style="display: none;"></small>
                                <div id="panchayatCodeInputDiv" style="display: none;">
                                    <label class="form-label text-danger">Panchayat Code (Required)*</label>
                                    <input type="text" id="panchayatCodeInput" name="panchayat_code" class="form-control"
                                        placeholder="Enter Panchayat Code">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-3 p-4">
                    <button type="submit" class="btn btn-primary" id="pushToRmsBtn">
                        <i class="mdi mdi-cloud-upload"></i> Push To RMS
                    </button>
                    <a href="{{ route('rms.export') }}" class="btn btn-info ml-2">
                        <i class="mdi mdi-file-export"></i> View Export Report
                    </a>
                </div>
            </form>
        </div>
    </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            // Initialize Select2
            $('.select2').select2({
                width: '100%',
                allowClear: true
            });

            // Helper function to handle code input visibility
            function handleCodeInput(containerId, displayId, inputDivId, inputId, code, label) {
                if (code) {
                    $(displayId).text(label + ': ' + code).show();
                    $(inputDivId).hide();
                    $(inputId).val('').prop('required', false);
                } else {
                    $(displayId).hide();
                    $(inputDivId).show();
                    $(inputId).prop('required', true).val('');
                }
                $(containerId).show();
            }

            // 1. Project Selection
            $('#projectSelect').on('change', function () {
                var projectId = $(this).val();
                if (projectId) {
                    window.location.href = "{{ route('rms.index') }}?project_id=" + projectId;
                } else {
                    window.location.href = "{{ route('rms.index') }}";
                }
            });

            // 2. District Selection -> Fetch Blocks & Code
            $('#districtSelect').on('change', function () {
                var district = $(this).val();
                var projectId = $('#projectSelect').val();

                // Reset downstream
                $('#blockSelect').prop('disabled', true).empty().append('<option value="">Select a Block</option>');
                $('#panchayatSelect').prop('disabled', true).empty().append('<option value="">Select a Panchayat</option>');
                $('#districtCodeContainer, #blockCodeContainer, #panchayatCodeContainer').hide();

                if (district && projectId) {
                    var url = '/jicr/blocks/' + district + '?project_id=' + projectId;

                    $.ajax({
                        url: url,
                        type: 'GET',
                        dataType: 'json',
                        success: function (response) {
                            // Handle District Code
                            handleCodeInput('#districtCodeContainer', '#districtCodeDisplay', '#districtCodeInputDiv', '#districtCodeInput', response.district_code, 'District Code');

                            // Populate Blocks
                            $('#blockSelect').prop('disabled', false);
                            $.each(response.blocks, function (index, item) {
                                $('#blockSelect').append('<option value="' + item.block + '">' + item.block + '</option>');
                            });
                        },
                        error: function (xhr, status, error) {
                            console.error("AJAX Error:", status, error);
                        }
                    });
                }
            });

            // 3. Block Selection -> Fetch Panchayats & Code
            $('#blockSelect').on('change', function () {
                var block = $(this).val();
                var projectId = $('#projectSelect').val();

                // Reset downstream
                $('#panchayatSelect').prop('disabled', true).empty().append('<option value="">Select a Panchayat</option>');
                $('#blockCodeContainer, #panchayatCodeContainer').hide();

                // Re-show district code container 
                $('#districtCodeContainer').show();

                if (block && projectId) {
                    var url = '/jicr/panchayats/' + block + '?project_id=' + projectId;

                    $.ajax({
                        url: url,
                        type: 'GET',
                        dataType: 'json',
                        success: function (response) {
                            // Handle Block Code
                            handleCodeInput('#blockCodeContainer', '#blockCodeDisplay', '#blockCodeInputDiv', '#blockCodeInput', response.block_code, 'Block Code');

                            // Populate Panchayats
                            $('#panchayatSelect').prop('disabled', false);
                            $.each(response.panchayats, function (index, item) {
                                $('#panchayatSelect').append('<option value="' + item.panchayat + '">' + item.panchayat + '</option>');
                            });
                        },
                        error: function (xhr, status, error) {
                            console.error("AJAX Error:", status, error);
                        }
                    });
                }
            });

            // 4. Panchayat Selection -> Fetch Code
            $('#panchayatSelect').on('change', function () {
                var panchayat = $(this).val();
                var projectId = $('#projectSelect').val();

                $('#panchayatCodeContainer').hide();

                if (panchayat && projectId) {
                    var url = '/jicr/ward/' + panchayat + '?project_id=' + projectId;

                    $.ajax({
                        url: url,
                        type: 'GET',
                        dataType: 'json',
                        success: function (response) {
                            // Handle Panchayat Code
                            handleCodeInput('#panchayatCodeContainer', '#panchayatCodeDisplay', '#panchayatCodeInputDiv', '#panchayatCodeInput', response.panchayat_code, 'Panchayat Code');
                        },
                        error: function (xhr, status, error) {
                            console.error("AJAX Error:", status, error);
                        }
                    });
                }
            });

            // Handle form submission via AJAX
            $('#rmsForm').on('submit', function (e) {
                e.preventDefault();

                const district = $('#districtSelect').val();
                const block = $('#blockSelect').val();
                const panchayat = $('#panchayatSelect').val();
                const projectId = $('#projectSelect').val();

                // Get codes if they are entered
                const districtCode = $('#districtCodeInput').val();
                const blockCode = $('#blockCodeInput').val();
                const panchayatCode = $('#panchayatCodeInput').val();

                if (!district || !block || !panchayat || !projectId) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Incomplete Selection',
                        text: 'Please select Project, District, Block, and Panchayat before pushing to RMS.',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                // Client side validation for codes if visible/required
                if ($('#districtCodeInputDiv').is(':visible') && !districtCode) {
                    Swal.fire('Warning', 'Please enter District Code', 'warning'); return;
                }
                if ($('#blockCodeInputDiv').is(':visible') && !blockCode) {
                    Swal.fire('Warning', 'Please enter Block Code', 'warning'); return;
                }
                if ($('#panchayatCodeInputDiv').is(':visible') && !panchayatCode) {
                    Swal.fire('Warning', 'Please enter Panchayat Code', 'warning'); return;
                }

                // Show loading
                Swal.fire({
                    title: 'Pushing to RMS...',
                    text: 'Please wait while we push the data to RMS. This may take a few moments.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        district: district,
                        block: block,
                        panchayat: panchayat,
                        project_id: projectId,
                        district_code: districtCode,
                        block_code: blockCode,
                        panchayat_code: panchayatCode
                    },
                    success: function (response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Push Completed!',
                            html: `<strong>${response.message}</strong><br><br>
                                               <div class="text-left">
                                                   <p><strong>Success:</strong> ${response.success_count || 0} pole(s)</p>
                                                   <p><strong>Errors:</strong> ${response.error_count || 0} pole(s)</p>
                                               </div>`,
                            confirmButtonText: 'View Report',
                            showCancelButton: true,
                            cancelButtonText: 'Close'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = '{{ route("rms.export") }}?panchayat=' + encodeURIComponent(panchayat);
                            }
                        });
                    },
                    error: function (xhr) {
                        let errorMessage = 'An error occurred while pushing to RMS.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.responseText) {
                            try {
                                const response = JSON.parse(xhr.responseText);
                                errorMessage = response.message || errorMessage;
                            } catch (e) {
                                errorMessage = xhr.responseText.substring(0, 200);
                            }
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Push Failed',
                            text: errorMessage,
                            confirmButtonText: 'OK'
                        });
                    }
                });
            });
        });
    </script>
@endpush

@push('styles')
    <style>
        /* Optional: style the dropdown container */
        .select2-container--default .select2-selection--single {
            height: 38px;
            padding: 6px 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
    </style>
@endpush