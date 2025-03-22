@extends("layouts.main")

@section("content")
  <div class="container p-2">
    <h3 class="fw-bold">Surveyed Poles</h3>
    <p>Total Surveyed Poles: <strong>{{ $totalSurveyed }}</strong></p>

    <!-- Search and Filter Form -->
    <form method="GET" action="{{ route("surveyed.poles") }}" class="mb-3">
      <div class="row">
        <div class="col-md-3">
          <input type="text" name="search" class="form-control" placeholder="Search by Pole Number"
            value="{{ request("search") }}">
        </div>
        <div class="col-md-3">
          <select name="district" class="form-control">
            <option value="">Select District</option>
            <!-- Populate districts dynamically -->
            @foreach ($districts as $district)
              <option value="{{ $district->id }}" {{ request("district") == $district->id ? "selected" : "" }}>
                {{ $district->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <select name="block" class="form-control">
            <option value="">Select Block</option>
            <!-- Populate blocks dynamically -->
            @foreach ($blocks as $block)
              <option value="{{ $block->id }}" {{ request("block") == $block->id ? "selected" : "" }}>
                {{ $block->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <select name="panchayat" class="form-control">
            <option value="">Select Panchayat</option>
            <!-- Populate panchayats dynamically -->
            @foreach ($panchayats as $panchayat)
              <option value="{{ $panchayat->id }}" {{ request("panchayat") == $panchayat->id ? "selected" : "" }}>
                {{ $panchayat->name }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="row mt-2">
        <div class="col-md-3">
          <button type="submit" class="btn btn-primary">Filter</button>
          <a href="{{ route("surveyed.poles") }}" class="btn btn-secondary">Reset</a>
        </div>
        <div class="col-md-3">
          <a href="{{ route("poles.export") }}" class="btn btn-success">Export to Excel</a>
        </div>
      </div>
    </form>

    <table class="table-striped table">
      <thead>
        <tr>
          <th>Pole ID</th>
          <th>Complete Pole Number</th>
          <th>Survey Status</th>
          <th>Installation Status</th>
          <th>Location</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($poles as $pole)
          <tr>
            <td>{{ $pole->id }}</td>
            <td>{{ $pole->complete_pole_number }}</td>
            <td>{{ $pole->isSurveyDone ? "Completed" : "Pending" }}</td>
            <td>{{ $pole->isInstallationDone ? "Installed" : "Not Installed" }}</td>
            <td>{{ $pole->lat }}, {{ $pole->lng }}</td>
            <td>
              <a href="{{ route("poles.show", $pole->id) }}" class="btn btn-info">View</a>
              <!-- Add more action buttons as needed -->
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
@endsection
