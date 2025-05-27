@extends("layouts.main")

@section("content")

<!-- Combined Row: Dashboard Cards and Export Buttons -->
<div class="row ms-0 mt-2 mb-4">
    <!-- Dashboard Cards -->
    <div class="col-md-3">
        <div class="card text-white bg-primary shadow-sm">
            <div class="card-body">
                <h5 class="card-title text-white">Applied Amount</h5>
                <h3>{{ $appliedAmount }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success shadow-sm">
            <div class="card-body">
                <h5 class="card-title text-white">Disbursed Amount</h5>
                <h3>{{ $disbursedAmount }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-danger shadow-sm">
            <div class="card-body">
                <h5 class="card-title text-white">Rejected Amount</h5>
                <h3>{{ $rejectedAmount }}</h3>
            </div>
        </div>
    </div>

    <!-- Export Buttons -->
    <!-- <div class="col-md-3 d-flex justify-content-end gap-2 align-items-end">
        <button class="btn btn-success btn-sm custom-btn" id="exportExcel">
            <i class="mdi mdi-file-excel fs-4"></i>
        </button>
        <button class="btn btn-danger btn-sm custom-btn" id="exportPDF">
            <i class="mdi mdi-file-pdf fs-4"></i>
        </button>
        <button class="btn btn-info btn-sm custom-btn me-2" id="printTable">
            <i class="mdi mdi-printer fs-4"></i>
        </button>
    </div> -->
</div>

<!-- Filters and Search -->
<div class="row mb-4 me-0 ms-0">

    <!-- <div class="col-md-3">
        <input type="text" class="form-control shadow-sm" placeholder="Search by Request ID, User, or Date">
    </div> -->
    <!-- <div class="col-md-3">
        <select class="form-select shadow-sm" aria-label="Filter by Users">
            <option selected>Users</option>
            <option value="1">Ava Martinez</option>
            <option value="2">Ethan Clark</option>
            <option value="3">Sophia Chen</option>
        </select>
    </div> -->
    <!-- <div class="col-md-3">
        <select class="form-select shadow-sm" aria-label="Filter by Locations">
            <option selected>Most Frequent Locations</option>
            <option value="1">New York, NY</option>
            <option value="2">San Francisco, CA</option>
            <option value="3">Chicago, IL</option>
        </select>
    </div> -->
    <div class="col-md-3">
    <h3 class="fw-bold">Performance Overview</h3>
        <select class="form-select w-auto" name="date_filter" id="taskFilter" onchange="filterTasks()">
            <option value="today" {{ request("date_filter") == "today" ? "selected" : "" }}>Today</option>
            <option value="this_week" {{ request("date_filter") == "this_week" ? "selected" : "" }}>This Week</option>
            <option value="this_month" {{ request("date_filter") == "this_month" ? "selected" : "" }}>This Month</option>
            <option value="all_time" {{ request("date_filter") == "all_time" ? "selected" : "" }}>All Time</option>
            <option value="custom" {{ request("date_filter") == "custom" ? "selected" : "" }}>Custom Range</option>
        </select>
    </div>
</div>

<!-- Approve/Reject Actions Button -->
<div class="d-flex justify-content-end mb-3">
    <button id="approveBtn" class="btn btn-body-tertiary bg-secondary me-2" style="display:none;">
        <i class="mdi mdi-check-circle-outline text-success me-2 fs-5 text-black"></i>Approve
    </button>
    <button id="rejectBtn" class="btn btn-danger me-2" style="display:none;">
        <i class="mdi mdi-close-circle-outline me-2 fs-5"></i>Reject
    </button>
</div>

<!-- DataTable -->
<div class="card shadow-sm border-0">
    <div class="card-header bg-primary text-white">
        <h4 class="mb-0">
            <i class="bi bi-table me-2"></i>Convenience Request Summary
        </h4>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <x-data-table id="convenienceTable" class="table table-bordered table-striped table-sm table mt-4">
                <x-slot:thead class="table-white">
                    <tr>
                        
                        <th><input type="checkbox" id="selectAll" /></th>
                        <th>Name</th>
                        <th>Employee Id</th>
                        <!-- <th>Department</th> -->
                        <th>Distance</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </x-slot:thead>
                <x-slot:tbody>
                    @foreach ($cons as $row)
                    <tr>
                        <td><input type="checkbox" class="checkboxItem" /></td>
                        <td>{{ $row->user->firstName ?? "N/A" }} {{ $row->user->lastName ?? "N/A" }}</td>
                        <td>{{ $row->user->id ?? "N/A" }}</td>
                        <!-- <td>{{ $row->department ?? "N/A" }}</td> -->
                        <td>{{ $row->kilometer ?? "N/A" }}</td>
                        <td>{{ ($row->amount ?? 0) }}</td>
                        <td>
                            @if ($row->status === null)
                                <span class="badge bg-warning text-dark">Pending</span>
                            @elseif ($row->status == 1)
                                <span class="badge bg-success">Accepted</span>
                            @elseif ($row->status == 0)
                                <span class="badge bg-danger">Rejected</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('convenience.details', $row->user_id) }}" class="btn btn-sm btn-info" data-toggle="tooltip" title="View Details">
                                <i class="mdi mdi-eye"></i>
                            </a>
                            @if ($row->status === null)
                                <form action="{{ route('conveyance.accept', $row->id) }}" method="POST" style="display: inline-block;" class="action-form" data-action="accept">
                                    @csrf
                                    <button type="button" class="btn btn-sm btn-warning action-btn" data-toggle="tooltip" title="Accept">
                                        <i class="mdi mdi-check"></i>
                                    </button>
                                </form>

                                <form action="{{ route('conveyance.reject', $row->id) }}" method="POST" style="display: inline-block;" class="action-form" data-action="reject">
                                    @csrf
                                    <button type="button" class="btn btn-sm btn-danger action-btn" data-toggle="tooltip" title="Reject">
                                        <i class="mdi mdi-close"></i>
                                    </button>
                                </form>
                            @endif
                            

                        </td>
                    </tr>
                    @endforeach
                </x-slot:tbody>
            </x-data-table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailsModalLabel">Employee Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Modal content goes here if needed -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
    $(document).on('click', '.action-btn', function(e) {
        e.preventDefault();
        
        const form = $(this).closest('.action-form');
        const action = form.data('action');
        const actionText = action === 'accept' ? 'Accept' : 'Reject';
        const actionColor = action === 'accept' ? '#ffc107' : '#dc3545';
        
        Swal.fire({
            title: `${actionText} this conveyance?`,
            text: `Are you sure you want to ${action} this conveyance request? This action cannot be undone.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: actionColor,
            cancelButtonColor: '#6c757d',
            confirmButtonText: `Yes, ${actionText}!`,
            cancelButtonText: 'Cancel',
        }).then((result) => {
            if (result.isConfirmed) {
                // Submit the form if confirmed
                form.submit();
            }
        });
    });
});
</script>
@endpush


@push('styles')
<style>
    .table-responsive {
        overflow: hidden;
    }
    #convenienceTable {
        width: 100% !important;
        table-layout: auto;
    }
    .table.mt-4 {
        margin-top: 1.5rem !important;
        margin-bottom: 0 !important;
    }
</style>
@endpush