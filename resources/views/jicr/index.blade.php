@extends('layouts.main')

@section('content')
    <div class="content-wrapper p-2">
        <div class="card p-2">
            <form id="jicrForm" action="{{ route('jicr.generate') }}" method="GET">
                @csrf
                <div class="row p-4">
                    <div class="col-sm-4">
                        <div class="mb-3">
                            <label for="districtSelect" class="form-label">District</label>
                            <select id="districtSelect" class="form-select select2" name="district" style="width: 100%;">
                                <option value="">Select a District</option>
                                @foreach ($districts as $district)
                                    <option value="{{ $district->district }}">{{ $district->district }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="mb-3">
                            <label for="blockSelect" class="form-label">Block</label>
                            <select id="blockSelect" class="form-select" name="block" style="width: 100%;" disabled>
                                <option value="">Select a Block</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="mb-3">
                            <label for="panchayatSelect" class="form-label">Panchayat</label>
                            <select id="panchayatSelect" class="form-select" name="panchayat" style="width: 100%;" disabled>
                                <option value="">Select a Panchayat</option>
                            </select>
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
        $(document).ready(function() {
            $('#districtSelect').select2({
                placeholder: "Select a District",
                allowClear: true
            });

            $('#blockSelect').select2({
                placeholder: "Select a Block",
                allowClear: true
            });

            $('#panchayatSelect').select2({
                placeholder: "Select a Panchayat",
                allowClear: true
            });

            $('#districtSelect').on('change', function() {
                var district = $(this).val();
                $('#blockSelect').prop('disabled', false).empty().append(
                    '<option value="">Select a Block</option>');
                $('#panchayatSelect').prop('disabled', true).empty().append(
                    '<option value="">Select a Panchayat</option>');

                if (district) {
                    $.ajax({
                        url: '/jicr/blocks/' + district,
                        type: 'GET',
                        dataType: 'json',
                        success: function(data) {
                            $.each(data, function(index, block) {
                                $('#blockSelect').append('<option value="' + block
                                    .block + '">' + block.block +
                                    '</option>');
                            });
                        },
                        error: function(xhr, status, error) {
                            console.error("AJAX Error:", status, error);
                            console.log("Response:", xhr.responseText);
                        }
                    });
                }
            });

            $('#blockSelect').on('change', function() {
                var block = $(this).val();
                $('#panchayatSelect').prop('disabled', false).empty().append(
                    '<option value="">Select a Panchayat</option>');

                if (block) { // You're checking 'district' instead of 'block'
                    $.ajax({
                        url: '/jicr/panchayats/' + block,
                        type: 'GET',
                        dataType: 'json',
                        success: function(data) {
                            $.each(data, function(index, block) {
                                $('#panchayatSelect').append('<option value="' + block
                                    .panchayat + '">' + block
                                    .panchayat +
                                    '</option>');
                            });
                        },
                        error: function(xhr, status, error) {
                            console.error("AJAX Error:", status, error);
                            console.log("Response:", xhr.responseText);
                        }
                    });
                }
            });
            const now = new Date();
            const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
            // const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0);

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
