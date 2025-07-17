@extends("layouts.main")

@section("content")
  <div class="container-fluid p-3">
    <div class="d-flex justify-content-between mb-3">
      <h3 class="fw-bold">New Hiring</h3>
    </div>

    <!-- Import Candidates and Send Emails in one line -->
    <div class="row mb-3">
      <div class="col-md-10">
        <form action="{{ route("import.candidates") }}" method="POST" enctype="multipart/form-data">
          @csrf
          <div class="input-group">
            <input type="file" name="file" class="form-control form-control-sm" required>
            <button type="submit" class="btn btn-sm btn-primary" title="Import Candidates">
              <i class="mdi mdi-upload"></i> Import Candidates
            </button>
          </div>
        </form>
        
      </div>
      <div class="col-md-2 d-flex align-items-end">
        <form action="{{ route("send.emails") }}" method="POST" class="w-100">
          @csrf
          <button type="submit" class="btn btn-sm btn-primary w-100">
            <i class="mdi mdi-email-send"></i> Send Emails
          </button>
        </form>
      </div>
    </div>

    <!-- Apply Filter -->
    <form method="GET" action="{{ route('candidates.index') }}" class="row g-3 mb-4">
      <div class="col-md-3">
        <label>From Date</label>
        <input type="date" name="from_date" id="from_date" value="{{ request('from_date') }}" class="form-control">
      </div>

      <div class="col-md-3">
        <label>To Date</label>
        <input type="date" name="to_date" id="to_date" value="{{ request('to_date') }}" class="form-control">
      </div>

      <div class="col-md-2">
        <label>Designation</label>
        <select name="designation" class="form-control select2">
          <option value="">All</option>
          @foreach ($candidates->pluck('designation')->unique() as $designation)
            <option value="{{ $designation }}" {{ request('designation') == $designation ? 'selected' : '' }}>
              {{ $designation }}
            </option>
          @endforeach
        </select>
      </div>

      <div class="col-md-2">
        <label>Department</label>
        <select name="department" class="form-control select2">
          <option value="">All</option>
          @foreach ($candidates->pluck('department')->unique() as $department)
            <option value="{{ $department }}" {{ request('department') == $department ? 'selected' : '' }}>
              {{ $department }}
            </option>
          @endforeach
        </select>
      </div>

      <div class="col-md-2">
        <label>Location</label>
        <select name="location" class="form-control select2">
          <option value="">All</option>
          @foreach ($candidates->pluck('location')->unique() as $location)
            <option value="{{ $location }}" {{ request('location') == $location ? 'selected' : '' }}>
              {{ $location }}
            </option>
          @endforeach
        </select>
      </div>

      <div class="col-md-12">
        <button type="submit" class="btn btn-primary">Apply Filters</button>
        <a href="{{ route('candidates.index') }}" class="btn btn-secondary">Reset</a>
      </div>
    </form>
    <!-- Candidate Data Table with scroll support -->
    <div class="table-responsive">
      <form method="POST" action="{{ route('candidates.bulkUpdate') }}">
        @csrf
        <div class="mb-3" id="bulk-action-buttons" style="display: none;">
          <button type="submit" name="action" value="accept" class="btn btn-success btn-sm">
            Accept
          </button>
          <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm">
            Reject
          </button>
        </div>
      <x-data-table id="candidateTable" class="table-striped table-sm">
        <x-slot:thead>
          <tr>
            <th>
              <input type="checkbox" id="select-all" />
            </th>
            <th>Name</th>
            <!-- <th>Email</th> -->
            <!-- <th>Phone</th> -->
            <th>Date of Offer</th>
            <!-- <th>Address</th> -->
            <th>Designation</th>
            <th>Department</th>
            <!-- <th>Location</th> -->
            <th>Experience</th>
            <!-- <th>Last Salary</th> -->
            <th>Candidate Status</th>
            <th>Company Response</th>
          </tr>
        </x-slot:thead>
        <x-slot:tbody>
          @foreach ($candidates as $index => $candidate)
            <tr>
              <td>
                <input type="checkbox" name="selected_candidates[]" value="{{ $candidate->id }}" class="candidate-checkbox" />
              </td>
              <td class="text-wrap">{{ $candidate->name }}</td>
              <!-- <td class="text-wrap">{{ $candidate->email }}</td> -->
              <!-- <td>{{ $candidate->phone }}</td> -->
              <td>{{ $candidate->date_of_offer }}</td>
              <!-- <td class="text-wrap">{{ $candidate->address }}</td> -->
              <td>{{ $candidate->designation }}</td>
              <td>{{ $candidate->department }}</td>
              <!-- <td>{{ $candidate->location }}</td> -->
              <td>{{ $candidate->experience }} yrs</td>
              <!-- <td>₹{{ number_format($candidate->last_salary, 2) }}</td> -->
              <td>
                <span class="badge 
                  @if ($candidate->status == 0) bg-warning 
                  @elseif ($candidate->status == 1) bg-info 
                  @elseif ($candidate->status == 2) bg-success 
                  @endif">
                  @if ($candidate->status == 0)
                    Pending
                  @elseif ($candidate->status == 1)
                    Emailed
                  @elseif ($candidate->status == 2)
                    Success
                  @endif
                </span>
             
              <a href="{{ route('admin-preview', $candidate->id) }}" class="btn btn-icon btn-info" data-toggle="tooltip" title="View Details">
                  <i class="mdi mdi-eye"></i>
              </a>

              @if ($candidate->id)
                <button
                  type="button"
                  class="btn btn-icon btn-danger delete-staff"
                  data-toggle="tooltip"
                  title="Delete Staff"
                  data-url="{{ route('candidates.destroy', $candidate->id) }}"
                  onclick="deleteCandidate(this)"
                >
                  <i class="mdi mdi-delete"></i>
                </button>
              @endif
            </td>
              <td class="text-center">
                <span class="badge 
                  @if (is_null($candidate->company_response)) bg-warning 
                  @elseif ($candidate->company_response == 1) bg-success 
                  @elseif ($candidate->company_response == 0) bg-danger 
                  @endif">
                  @if (is_null($candidate->company_response))
                    Pending
                  @elseif ($candidate->company_response == 1)
                    Approved
                  @elseif ($candidate->company_response == 0)
                    Rejected
                  @endif
                </span>
              </td>
            </tr>
          @endforeach
        </x-slot:tbody>
      </x-data-table>
      </form>
    </div>
  </div>


@endsection

@push('scripts')
<script>
    @if (session('success'))
    Swal.fire({
      icon: 'success',
      title: 'Success',
      text: {!! json_encode(session('success')) !!}
    });
  @endif

  @if (session('error'))
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: {!! json_encode(session('error')) !!}
    });
  @endif
   $(document).ready(function () {
    $('.select2').select2({
      placeholder: 'Select an option',
      allowClear: true
    });
  });
   const checkboxes = document.querySelectorAll('.candidate-checkbox');
  const selectAll = document.getElementById('select-all');
  const actionButtons = document.getElementById('bulk-action-buttons');

  function toggleActionButtons() {
    const anyChecked = Array.from(checkboxes).some(cb => cb.checked);
    actionButtons.style.display = anyChecked ? 'block' : 'none';
  }

  // Toggle buttons on individual checkbox change
  checkboxes.forEach(cb => cb.addEventListener('change', toggleActionButtons));

  // Toggle buttons on "select all"
  if (selectAll) {
    selectAll.addEventListener('change', function () {
      checkboxes.forEach(cb => cb.checked = this.checked);
      toggleActionButtons();
    });
  }
  document.getElementById('select-all').addEventListener('change', function () {
    const checkboxes = document.querySelectorAll('.candidate-checkbox');
    checkboxes.forEach(checkbox => checkbox.checked = this.checked);
  });

  // Auto-show date/time picker
  ['from_date', 'to_date'].forEach(id => {
    const el = document.getElementById(id);
    if (el) {
      el.addEventListener('click', function () {
        this.showPicker && this.showPicker();
      });
    }
  });
  function deleteCandidate(button) {
  const url = button.getAttribute('data-url');

  Swal.fire({
    title: 'Are you sure?',
    text: 'You won’t be able to revert this!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Yes, delete it!',
    cancelButtonText: 'Cancel',
    reverseButtons: true
  }).then((result) => {
    if (result.isConfirmed) {
      fetch(url, {
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
          'Accept': 'application/json',
        }
      })
      .then(response => {
        if (response.ok) {
          Swal.fire({
            icon: 'success',
            title: 'Deleted!',
            text: 'Candidate has been deleted.',
            timer: 2000,
            showConfirmButton: false
          }).then(() => {
            location.reload(); // or remove row from DOM
          });
        } else {
          return response.json().then(data => {
            throw new Error(data.message || 'Delete failed');
          });
        }
      })
      .catch(error => {
        console.error('Error:', error);
        Swal.fire({
          icon: 'error',
          title: 'Delete Failed',
          text: error.message || 'Something went wrong.',
        });
      });
    }
  });
}

</script>
@endpush

@push('styles')
<style>
  table.dataTable thead th,
  table.dataTable thead td,
  table.dataTable tfoot th,
  table.dataTable tfoot td {
      text-align: center;
  }

  .form-control:read-only,  .select2-container--default .select2-selection--single:read-only{
    background: none;
  }
   .select2-container--default .select2-selection--single:read-only{
    padding: 0;
   }
</style>
@endpush