@extends('layouts.main')

@section('content')
    <div class="content-wrapper p-2">
        <div class="card p-2">
            <form id="jicrForm" action="{{ route('jicr.generate') }}" method="GET">
                @csrf
                <div class="row p-4">
                    <div class="col-sm-12 mb-3">
                        <label for="projectSelect" class="form-label">Project</label>
                        <select id="projectSelect" class="form-select select2" name="project_id" style="width: 100%;"
                            required>
                            <option value="">Select a Project</option>
                            @foreach ($projects as $proj)
                                <option value="{{ $proj->id }}" {{ $projectId == $proj->id ? 'selected' : '' }}>
                                    {{ $proj->project_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row p-4">
                    <div class="col-sm-4">
                        <div class="mb-3">
                            <label for="districtSelect" class="form-label">District</label>
                            <select id="districtSelect" class="form-select select2" name="district" style="width: 100%;" {{ !$projectId ? 'disabled' : '' }} required>
                                <option value="">Select a District</option>
                                @foreach ($districts as $district)
                                    <option value="{{ $district->district }}">{{ $district->district }}</option>
                                @endforeach
                            </select>
                            <span id="districtCodeDisplay" class="text-primary small fw-bold mt-1 d-block"></span>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="mb-3">
                            <label for="blockSelect" class="form-label">Block</label>
                            <select id="blockSelect" class="form-select select2" name="block" style="width: 100%;" disabled
                                required>
                                <option value="">Select a Block</option>
                            </select>
                            <span id="blockCodeDisplay" class="text-primary small fw-bold mt-1 d-block"></span>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="mb-3">
                            <label for="panchayatSelect" class="form-label">Panchayat</label>
                            <select id="panchayatSelect" class="form-select select2" name="panchayat" style="width: 100%;"
                                disabled required>
                                <option value="">Select a Panchayat</option>
                            </select>
                            <span id="panchayatCodeDisplay" class="text-primary small fw-bold mt-1 d-block"></span>
                        </div>
                    </div>
                </div>
                <div class="row p-4">
                    <div class="col-sm-3">
                        <div class="mb-3"><label for="fromDate" class="form-label">From Date</label><input type="date"
                                id="fromDate" name="from_date" class="form-control" required></div>
                    </div>
                    <div class="col-sm-3">
                        <div class="mb-3"><label for="toDate" class="form-label">To Date</label>
                            <input type="date" id="toDate" name="to_date" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">Generate JICR</button>
                </div>

        </div>
        </form>
        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif
        @if (!empty($showReport) && isset($data))
            @include('jicr.show', ['data' => $data])
        @endif

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

            // 1. Project Selection
            $('#projectSelect').on('change', function () {
                var projectId = $(this).val();
                if (projectId) {
                    window.location.href = "{{ route('jicr.index') }}?project_id=" + projectId;
                } else {
                    window.location.href = "{{ route('jicr.index') }}";
                }
            });

            // 2. District Selection -> Fetch Blocks & Code
            $('#districtSelect').on('change', function () {
                var district = $(this).val();
                var projectId = $('#projectSelect').val();

                $('#blockSelect').prop('disabled', false).empty().append(
                    '<option value="">Select a Block</option>');
                $('#panchayatSelect').prop('disabled', true).empty().append(
                    '<option value="">Select a Panchayat</option>');

                // Reset Code Displays
                $('#districtCodeDisplay').text('');
                $('#blockCodeDisplay').text('');
                $('#panchayatCodeDisplay').text('');

                if (district && projectId) {
                    $.ajax({
                        url: '/jicr/blocks/' + district + '?project_id=' + projectId,
                        type: 'GET',
                        dataType: 'json',
                        success: function (data) {
                            // Populate Blocks
                            $.each(data.blocks, function (index, item) {
                                $('#blockSelect').append('<option value="' + item
                                    .block + '">' + item.block +
                                    '</option>');
                            });

                            // Display District Code
                            if (data.district_code) {
                                $('#districtCodeDisplay').text('District Code: ' + data.district_code);
                            }
                        },
                        error: function (xhr, status, error) {
                            console.error("AJAX Error:", status, error);
                            console.log("Response:", xhr.responseText);
                        }
                    });
                }
            });

            // 3. Block Selection -> Fetch Panchayats & Code
            $('#blockSelect').on('change', function () {
                var block = $(this).val();
                var district = $('#districtSelect').val();
                var projectId = $('#projectSelect').val();

                $('#panchayatSelect').prop('disabled', false).empty().append(
                    '<option value="">Select a Panchayat</option>');

                // Reset Code Displays (downstream)
                $('#blockCodeDisplay').text('');
                $('#panchayatCodeDisplay').text('');

                if (block && projectId) {
                    $.ajax({
                        url: '/jicr/panchayats/' + encodeURIComponent(block) +
                            '?project_id=' + projectId +
                            '&district=' + encodeURIComponent(district),
                        type: 'GET',
                        dataType: 'json',
                        success: function (data) {
                            // Populate Panchayats
                            $.each(data.panchayats, function (index, item) {
                                $('#panchayatSelect').append('<option value="' + item
                                    .panchayat + '">' + item
                                        .panchayat +
                                    '</option>');
                            });

                            // Display Block Code
                            if (data.block_code) {
                                $('#blockCodeDisplay').text('Block Code: ' + data.block_code);
                            }
                        },
                        error: function (xhr, status, error) {
                            console.error("AJAX Error:", status, error);
                            console.log("Response:", xhr.responseText);
                        }
                    });
                }
            });

            // 4. Panchayat Selection -> Fetch & Display Code
            $('#panchayatSelect').on('change', function () {
                var panchayat = $(this).val();
                var block = $('#blockSelect').val();
                var district = $('#districtSelect').val();
                var projectId = $('#projectSelect').val();

                $('#panchayatCodeDisplay').text('');

                if (panchayat && projectId) {
                    $.ajax({
                        url: '/jicr/ward/' + encodeURIComponent(panchayat) +
                            '?project_id=' + projectId +
                            '&district=' + encodeURIComponent(district) +
                            '&block=' + encodeURIComponent(block),
                        type: 'GET',
                        dataType: 'json',
                        success: function (data) {
                            // Display Panchayat Code
                            if (data.panchayat_code) {
                                $('#panchayatCodeDisplay').text('Panchayat Code: ' + data.panchayat_code);
                            }
                        },
                        error: function (xhr, status, error) {
                            console.error("AJAX Error:", status, error);
                            console.log("Response:", xhr.responseText);
                        }
                    });
                }
            });

            const now = new Date();
            const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);

            document.getElementById('fromDate').valueAsDate = firstDay;
            document.getElementById('toDate').valueAsDate = firstDay;

            // Show session messages as toast using SweetAlert2
            @if (session()->has('success'))
                const successMsg = {!! json_encode(session('success')) !!};
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: successMsg,
                    showConfirmButton: false,
                    timer: 4000,
                    timerProgressBar: true
                });
            @endif

                @if (session()->has('error'))
                    const errorMsg = {!! json_encode(session('error')) !!};
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'error',
                        title: errorMsg,
                        showConfirmButton: false,
                        timer: 5000,
                        timerProgressBar: true
                    });
                @endif
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