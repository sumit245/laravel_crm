@extends('layouts.main')

@section('content')
<div class="container mt-2">

    {{-- Back Button --}}
    <div class="mb-3">
        <a href="{{ url()->previous() }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left-circle me-1"></i> Back
        </a>
    </div>

    {{-- Dashboard Cards --}}
    <div class="col-lg-12">
        <div class="row ms-0">
            @php
                $cards = [
                    ['title' => 'Applied Amount', 'amount' => '₹125,000', 'bg' => 'bg-primary'],
                    ['title' => 'Disbursed Amount', 'amount' => '₹100,000', 'bg' => 'bg-success'],
                    ['title' => 'Rejected Amount', 'amount' => '₹25,000', 'bg' => 'bg-danger'],
                    ['title' => 'Due Claim Amount', 'amount' => '₹10,000', 'bg' => 'bg-warning'],
                ];
            @endphp

            @foreach ($cards as $card)
            <div class="col-md-3 mb-3">
                <div class="card text-white {{ $card['bg'] }} shadow-sm h-100">
                    <div class="card-body d-flex flex-column justify-content-between">
                        <h6 class="card-title text-white fw-semibold mb-2">{{ $card['title'] }}</h6>
                        <h4 class="mb-0">{{ $card['amount'] }}</h4>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Vendor Cards --}}
    <div class="row mb-4">
    <div class="col-md-4 mb-4">
        <a>
            <div class="card d-flex flex-row align-items-center p-3 shadow-sm">
                <!-- Placeholder Image -->
                <img src="https://via.placeholder.com/60" alt="Placeholder" class="rounded-circle" width="60" height="60">
                <div class="ms-3">
                    <h5 class="mb-0">{{ $vendor->name ?? 'Yashvir Singh' }}</h5>
                    <small class="text-muted">Vendor</small>
                    <p class="mb-0">{{ $vendor->email ?? 'example@gmail.com' }}</p>
                </div>
            </div>
        </a>
    </div>
</div>


    {{-- Approve / Reject Buttons --}}
    <div class="d-flex justify-content-end mb-3">
        <button id="approveBtn" class="btn btn-success me-2" style="display:none;">
            <i class="mdi mdi-check-circle-outline me-2 fs-5"></i> Approve
        </button>
        <button id="rejectBtn" class="btn btn-danger" style="display:none;">
            <i class="mdi mdi-close-circle-outline me-2 fs-5"></i> Reject
        </button>
    </div>

    {{-- Travel Table --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">
                <i class="bi bi-table me-2"></i>Travel Summary
            </h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="travelTable" class="table table-bordered table-striped table-sm table mt-4">
                    <thead class="table-white">
                        <tr>
                            <th><input type="checkbox" id="selectAll" /></th>
                            <th>Date & Time</th>
                            <th>Vehicle</th>
                            <th>To</th>
                            <th>From</th>
                            <th>Distance (KM)</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @for ($i = 0; $i < 10; $i++)
                        <tr>
                            <td><input type="checkbox" class="checkboxItem" /></td>
                            <td>
                                @php $date = $details['start_date'] ?? null; @endphp
                                {{ $date ? \Carbon\Carbon::parse($date)->format('d-m-Y h:i A') : 'N/A' }}
                            </td>
                            <td>{{ $details['vehicle_number'] ?? 'N/A' }}</td>
                            <td>{{ $details['to'] ?? 'N/A' }}</td>
                            <td>{{ $details['from'] ?? 'N/A' }}</td>
                            <td>{{ $details['total_km'] ?? 'N/A' }}</td>
                            <td>{{ $details['amount'] ?? 'N/A' }}</td>
                        </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection

{{-- Scripts --}}
@push('scripts')
<script>
    $(document).ready(function () {
        $('#travelTable').DataTable({
            dom: "<'row d-flex align-items-center justify-content-between'" +
                "<'col-md-6 d-flex align-items-center' f>" +
                "<'col-md-6 d-flex justify-content-end' B>" +
                ">" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-5 d-flex align-items-center' i><'col-sm-7 d-flex justify-content-start' p>>",
            buttons: [
                {
                    extend: 'excel',
                    text: '<i class="mdi mdi-file-excel"></i>',
                    className: 'btn btn-sm btn-success',
                    titleAttr: 'Export to Excel'
                },
                {
                    extend: 'pdf',
                    text: '<i class="mdi mdi-file-pdf"></i>',
                    className: 'btn btn-sm btn-danger',
                    titleAttr: 'Export to PDF'
                },
                {
                    extend: 'print',
                    text: '<i class="mdi mdi-printer"></i>',
                    className: 'btn btn-sm btn-info',
                    titleAttr: 'Print Table'
                }
            ],
            paging: true,
            pageLength: 50,
            searching: true,
            ordering: true,
            order: [[1, 'desc']], // Sort by Date & Time descending
            responsive: true,
            language: {
                search: '',
                searchPlaceholder: 'Search Travel Records'
            }
        });

        $('.dataTables_filter input').addClass('form-control form-control-sm');
        $('[data-toggle="tooltip"]').tooltip();
    });

    // Handle Select All
    document.getElementById('selectAll').addEventListener('change', function(e) {
        document.querySelectorAll('.checkboxItem').forEach(cb => cb.checked = e.target.checked);
        toggleApproveRejectButtons();
    });

    // Handle individual checkbox toggle
    document.querySelectorAll('.checkboxItem').forEach(cb => {
        cb.addEventListener('change', toggleApproveRejectButtons);
    });

    function toggleApproveRejectButtons() {
        const selected = document.querySelectorAll('.checkboxItem:checked').length;
        document.getElementById('approveBtn').style.display = selected ? 'inline-block' : 'none';
        document.getElementById('rejectBtn').style.display = selected ? 'inline-block' : 'none';
    }
</script>
@endpush

{{-- Styles --}}
@push('styles')
<style>
    /* Remove horizontal scroll */
    .table-responsive {
        overflow: hidden;
    }

    /* Ensure table doesn't exceed container */
    #travelTable {
        width: 100% !important;
        table-layout: auto;
    }

    /* Adjust margin */
    .table.mt-4 {
        margin-top: 1.5rem !important;
        margin-bottom: 0 !important;
    }

    .btn.btn-secondary:hover {
        color: black !important;
    }

</style>
@endpush
