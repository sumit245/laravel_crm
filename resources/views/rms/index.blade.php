@extends('layouts.main')

@section('content')
    <div class="content-wrapper p-2">
        {{-- Display Success/Error Alerts --}}
        @if (session('success') || session('error'))
            <div class="alert alert-{{ session('success') ? 'success' : 'danger' }} alert-dismissible fade show"
                role="alert">
                {{ session('success', session('error')) }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card p-2">
            <form id="rmsForm" action="{{ route('rms.push') }}" method="POST">
                @csrf
                <div class="row p-4">
                    <div class="col-sm-4">
                        <div class="mb-3">
                            <label for="districtSelect" class="form-label">District</label>
                            <select id="districtSelect" class="form-select select2" name="district" style="width: 100%;" required>
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
                            <select id="blockSelect" class="form-select select2" name="block" style="width: 100%;" disabled required>
                                <option value="">Select a Block</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="mb-3">
                            <label for="panchayatSelect" class="form-label">Panchayat</label>
                            <select id="panchayatSelect" class="form-select select2" name="panchayat" style="width: 100%;" disabled required>
                                <option value="">Select a Panchayat</option>
                            </select>
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
            $('#wardSelect').select2({
                placeholder: "Select a Ward",
                multiple: true,
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

            // Handle form submission via AJAX
            $('#rmsForm').on('submit', function(e) {
                e.preventDefault();
                
                const district = $('#districtSelect').val();
                const block = $('#blockSelect').val();
                const panchayat = $('#panchayatSelect').val();
                
                if (!district || !block || !panchayat) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Incomplete Selection',
                        text: 'Please select District, Block, and Panchayat before pushing to RMS.',
                        confirmButtonText: 'OK'
                    });
                    return;
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
                        panchayat: panchayat
                    },
                    success: function(response) {
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
                    error: function(xhr) {
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
