@extends("layouts.main")

@section("content")
  <!-- Combined Row: Dashboard Cards and Export Buttons -->
  <div class="row mb-4 ms-0 mt-2">
    <!-- Dashboard Cards -->
    <div class="col-md-3">
      <div class="card bg-primary text-white shadow-sm">
        <div class="card-body">
          <h5 class="card-title text-white">Applied Amount</h5>
          <h3>{{ $appliedAmount }}</h3>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card bg-success text-white shadow-sm">
        <div class="card-body">
          <h5 class="card-title text-white">Disbursed Amount</h5>
          <h3>{{ $disbursedAmount }}</h3>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card bg-danger text-white shadow-sm">
        <div class="card-body">
          <h5 class="card-title text-white">Rejected Amount</h5>
          <h3>{{ $rejectedAmount }}</h3>
        </div>
      </div>
    </div>

    <!-- Export Buttons -->
    <!-- <div class="col-md-3 d-flex justify-content-end align-items-end gap-2">
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
   
  </div>

  <!-- DataTable -->
  <div class="card border-0 shadow-sm">
    <div class="card-header bg-primary text-white">
      <h4 class="mb-0">
        <i class="bi bi-table me-2"></i>Convenience Request Summary
      </h4>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <x-datatable id="convenienceTable" 
            title="Convenience Requests" 
            :columns="[
                ['title' => 'Name', 'width' => '20%'],
                ['title' => 'Date', 'width' => '15%'],
                ['title' => 'Distance', 'width' => '12%'],
                ['title' => 'Amount', 'width' => '12%'],
                ['title' => 'Status', 'width' => '15%'],
              ]" 
            :exportEnabled="true" 
            :importEnabled="false" 
            :bulkDeleteEnabled="true"
            :bulkDeleteRoute="route('conveyance.bulkDelete')"
            pageLength="50" 
            searchPlaceholder="Search convenience requests...">
            @foreach ($cons as $row)
              <tr data-id="{{ $row->id }}">
                <td>{{ $row->user->firstName ?? "N/A" }} {{ $row->user->lastName ?? "N/A" }}</td>
                <td>{{ \Carbon\Carbon::parse($row->created_at)->format('d-m-Y') }}</td>
                <td>{{ $row->kilometer ?? "N/A" }}</td>
                <td>{{ $row->amount ?? 0 }}</td>
                <td class="text-center">
                  @if ($row->status === null)
                    <span class="badge bg-warning text-dark">Pending</span>
                  @elseif ($row->status == 1)
                    <span class="badge bg-success">Accepted</span>
                  @elseif ($row->status == 0)
                    <span class="badge bg-danger">Rejected</span>
                  @endif
                </td>
                <td class="text-center">
                  <a href="{{ route("convenience.details", $row->id) }}" class="btn btn-sm btn-info">
                    <i class="mdi mdi-eye"></i>
                  </a>
                </td>
              </tr>
            @endforeach
        </x-datatable>
        <form action="{{ route('conveyance.bulkAction') }}" method="POST" id="bulkActionForm">
          @csrf
          <input type="hidden" name="action_type" id="action_type">
            <div id="bulkButtons" style="display: none;">
              <button type="submit" class="btn btn-success btn-sm" onclick="submitBulkAction('accept')">Accept</button>
              <button type="submit" class="btn btn-danger btn-sm" onclick="submitBulkAction('reject')">Reject</button>
            </div>
        </form>
        
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

@push("scripts")
<script>
    @if (session('success'))
      Swal.fire({
        icon: 'success',
        title: 'Success',
        text: '{{ session('success') }}',
        confirmButtonColor: '#28a745'
      });
    @endif

    @if (session('error'))
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: '{{ session('error') }}',
        confirmButtonColor: '#dc3545'
      });
    @endif
  document.getElementById('selectAll').addEventListener('change', function () {
    const checkboxes = document.querySelectorAll('.checkboxItem');
    checkboxes.forEach(cb => cb.checked = this.checked);
    toggleBulkButtons();
  });

  document.querySelectorAll('.checkboxItem').forEach(cb => {
    cb.addEventListener('change', toggleBulkButtons);
  });

  function toggleBulkButtons() {
    const selected = document.querySelectorAll('.checkboxItem:checked');
    const bulkButtons = document.getElementById('bulkButtons');
    bulkButtons.style.display = selected.length > 0 ? 'block' : 'none';
  }

  function submitBulkAction(type) {
    event.preventDefault();

    const selected = document.querySelectorAll('.checkboxItem:checked');
    if (selected.length === 0) {
      alert('Please select at least one entry.');
      return;
    }

    // Clear old inputs
    document.querySelectorAll('input[name="ids[]"]').forEach(e => e.remove());

    // Add selected IDs to form
    selected.forEach(cb => {
      const row = cb.closest('tr');
      const id = row.getAttribute('data-id');
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = 'ids[]';
      input.value = id;
      document.getElementById('bulkActionForm').appendChild(input);
    });

    document.getElementById('action_type').value = type;
    document.getElementById('bulkActionForm').submit();
  }

  $(document).ready(function () {
    $(document).on('click', '.action-btn', function (e) {
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
          form.submit();
        }
      });
    });
  });
</script>
@endpush


@push("styles")
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
