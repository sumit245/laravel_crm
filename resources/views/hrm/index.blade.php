@extends("layouts.main")

@section("content")
  <div class="container-fluid p-3">
    <div class="d-flex justify-content-between mb-3">
      <h3 class="fw-bold">New Hiring</h3>
      <form action="{{ route("send.emails") }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-sm btn-primary">Send Emails</button>
      </form>
    </div>

    <!-- Upload Excel Form -->
    <form action="{{ route("import.candidates") }}" method="POST" enctype="multipart/form-data" class="mb-3">
      @csrf
      <div class="input-group">
        <input type="file" name="file" class="form-control form-control-sm" required>
        <button type="submit" class="btn btn-sm btn-primary" title="Import Candidates">
          <i class="mdi mdi-upload"></i> Import Candidates
        </button>
      </div>
    </form>

    <!-- Candidate Data Table with scroll support -->
    <div class="table-responsive">
      <x-data-table id="candidateTable" class="table-striped table-sm">
        <x-slot:thead>
          <tr>
            <th>Sl. No</th>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Date of Offer</th>
            <th>Address</th>
            <th>Designation</th>
            <th>Department</th>
            <th>Location</th>
            <th>Experience</th>
            <th>Last Salary</th>
            <th>Status</th>
          </tr>
        </x-slot:thead>
        <x-slot:tbody>
          @foreach ($candidates as $index => $candidate)
            <tr>
              <td>{{ $candidates->firstItem() + $index }}</td>
              <td class="text-wrap">{{ $candidate->name }}</td>
              <td class="text-wrap">{{ $candidate->email }}</td>
              <td>{{ $candidate->phone }}</td>
              <td>{{ $candidate->date_of_offer }}</td>
              <td class="text-wrap">{{ $candidate->address }}</td>
              <td>{{ $candidate->designation }}</td>
              <td>{{ $candidate->department }}</td>
              <td>{{ $candidate->location }}</td>
              <td>{{ $candidate->experience }} yrs</td>
              <td>₹{{ number_format($candidate->last_salary, 2) }}</td>
              <td>
                <span class="badge 
                  @if ($candidate->status === 'pending') bg-warning 
                  @elseif ($candidate->status === 'emailed') bg-info 
                  @elseif ($candidate->status === 'hired') bg-success 
                  @elseif ($candidate->status === 'rejected') bg-danger 
                  @endif">
                  {{ ucfirst($candidate->status) }}
                </span>
             
              <a href="#" class="btn btn-icon btn-info" data-toggle="tooltip"
                title="View Details">
                <i class="mdi mdi-eye"></i>
              </a>
              <button type="submit" class="btn btn-icon btn-danger delete-staff" data-toggle="tooltip"
                title="Delete Staff" 
                data-url="#">
                <i class="mdi mdi-delete"></i>
              </button>
            </td>
            </tr>
          @endforeach
        </x-slot:tbody>
      </x-data-table>
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-center mt-3">
      {{ $candidates->links() }}
    </div>
  </div>
@endsection


@push('styles')
<style>
  table.dataTable thead th,
  table.dataTable thead td,
  table.dataTable tfoot th,
  table.dataTable tfoot td {
      text-align: center;
  }
</style>
@endpush

