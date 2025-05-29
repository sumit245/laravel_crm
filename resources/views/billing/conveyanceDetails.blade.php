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
           
            <!-- Applied Amount -->
            <div class="col-md-3 mb-3">
                <div class="card text-white bg-primary shadow-sm h-100">
                    <div class="card-body d-flex flex-column justify-content-between">
                        <h6 class="card-title text-white fw-semibold mb-2">Applied Amount</h6>
                        <h4 class="mb-0">{{ $appliedAmount }}</h4>
                    </div>
                </div>
            </div>
            <!-- Disbursed Amount -->
            <div class="col-md-3 mb-3">
                <div class="card text-white bg-success shadow-sm h-100">
                    <div class="card-body d-flex flex-column justify-content-between">
                        <h6 class="card-title text-white fw-semibold mb-2">Disbursed Amount</h6>
                        <h4 class="mb-0">{{ $disbursedAmount }}</h4>
                    </div>
                </div>
            </div>
            <!-- Rejected Amount -->
            <div class="col-md-3 mb-3">
                <div class="card text-white bg-danger shadow-sm h-100">
                    <div class="card-body d-flex flex-column justify-content-between">
                        <h6 class="card-title text-white fw-semibold mb-2">Rejected Amount</h6>
                        <h4 class="mb-0">{{ $rejectedAmount}}</h4>
                    </div>
                </div>
            </div>
            <!-- Due Claim Amount -->
            <div class="col-md-3 mb-3">
                <div class="card text-white bg-warning shadow-sm h-100">
                    <div class="card-body d-flex flex-column justify-content-between">
                        <h6 class="card-title text-white fw-semibold mb-2">Due Claim Amount</h6>
                        <h4 class="mb-0">{{ $dueclaimAmount }}</h4>
                    </div>
                </div>
            </div>
            
        </div>
    </div>

    {{-- Vendor Cards --}}
    <div class="row mb-4">
    <div class="col-md-4 mb-4">
        <a>
            <div class="card d-flex flex-row align-items-center p-3 shadow-sm">
                <!-- Placeholder Image -->
                <img src="{{ asset($details->first()->user->image ?? 'images/default.png') }}" alt="Placeholder" class="rounded-circle" width="60" height="60">
                <div class="ms-3">
                    <h5 class="mb-0">{{ $details->first()->user->firstName ?? "N/A" }} {{ $details->first()->user->lastName ?? "N/A" }}</h5>
                    @if ($details->first()->user->role==0)
                        <small class="text-muted">Admin</small>
                    @elseif ($details->first()->user->role==1)
                        <small class="text-muted">Employee</small>
                    @elseif ($details->first()->user->role==3)
                        <small class="text-muted">Vendor</small>
                    @endif
                    <p class="mb-0">{{ $details->first()->user->email ?? "N/A" }}</p>
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
                        @foreach ($details as $detail)
                        <tr>
                            <td><input type="checkbox" class="checkboxItem" /></td>
                            <td>{{ $detail->time }}</td>
                            <td>{{ $detail->vehicle->category ?? 'N/A' }}</td>
                            <td>{{ $detail->to ?? 'N/A' }}</td>
                            <td>{{ $detail->from ?? 'N/A' }}</td>
                            <td>{{ $detail->kilometer ?? 'N/A' }}</td>
                            <td>{{ $detail->amount ?? 'N/A' }}</td>
                        </tr>
                        @endforeach
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
