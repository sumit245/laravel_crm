@extends("layouts.main")

@section("content")
  <div class="container">
    <div class="d-flex justify-content-between mb-4">
      <h2>New Hiring</h2>
      <div class="d-flex">
        <form action="{{ route("send.emails") }}" method="POST">
          @csrf
          <button type="submit" class="btn btn-sm btn-primary">Send Emails</button>
        </form>
      </div>
    </div>

    <!-- Upload Excel Form -->
    <form action="{{ route("import.candidates") }}" method="POST" enctype="multipart/form-data">
      @csrf
      <div class="input-group mb-3">
        <input type="file" name="file" class="form-control form-control-sm" required>
        <button type="submit" class="btn btn-sm btn-primary" data-toggle="tooltip" title="Import Candidates">
          <i class="mdi mdi-upload"></i> Import Candidates
        </button>
      </div>
    </form>

    <!-- Candidates Table -->
    <div class="card">
      <div class="card-header">
        <h4>Candidate List</h4>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table-bordered table-sm table text-wrap" style="table-layout: fixed; width: 100%;">
            <thead class="table-dark">
              <tr>
                <th style="width: 80px;">Sl. No</th>
                <th style="width: 100px;">Name</th>
                <th style="width: 200px;">Email</th>
                <th style="width: 140px;">Phone</th>
                <th style="width: 140px;">Date of Offer</th>
                <th style="width: 240px;">Address</th>
                <th style="width: 120px;">Designation</th>
                <th style="width: 120px;">Department</th>
                <th style="width: 120px;">Location</th>
                <th style="width: 100px;">Experience</th>
                <th style="width: 80px;">Last Salary</th>
                <th style="width: 100px;">Status</th>
                {{-- <th style="width: 80px;">Actions</th> --}}
              </tr>
            </thead>
            <tbody>
              @foreach ($candidates as $candidate)
                <tr>
                  <td>{{ $candidate->id }}</td>
                  <td class="text-wrap">{{ $candidate->name }}</td>
                  <td class="text-wrap">{{ $candidate->email }}</td>
                  <td class="text-wrap">{{ $candidate->phone }}</td>
                  <td class="text-wrap">{{ $candidate->date_of_offer }}</td>
                  <td class="text-wrap">{{ $candidate->address }}</td>
                  <td class="text-wrap">{{ $candidate->designation }}</td>
                  <td class="text-wrap">{{ $candidate->department }}</td>
                  <td class="text-wrap">{{ $candidate->location }}</td>
                  <td class="text-wrap">{{ $candidate->experience }} years</td>
                  <td class="text-wrap">₹{{ number_format($candidate->last_salary, 2) }}</td>
                  <td>
                    <span
                      class="badge @if ($candidate->status == "pending") bg-warning
                      @elseif ($candidate->status == "emailed") bg-info
                      @elseif ($candidate->status == "hired") bg-success
                      @elseif ($candidate->status == "rejected") bg-danger @endif">
                      {{ ucfirst($candidate->status) }}
                    </span>
                  </td>
                  <td>
                    {{-- Action buttons (optional) --}}
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center">
          {{ $candidates->links() }}
        </div>
      </div>
    </div>

  </div>
@endsection
