@extends("layouts.main")

@section("content")
  <div class="container p-2">
    <h3 class="fw-bold">Surveyed Poles</h3>
    <p>Total Surveyed Poles: <strong>{{ $totalSurveyed }}</strong></p>

    <!-- Search and Filter Form -->
     {{--  
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
--}}
    <x-data-table id="surveyedPole" class="table-striped table">
      <x-slot:thead>
        <tr>
        <th data-select="true">
          <input type="checkbox" id="selectAll" />
        </th>
        
        <th>Ward Name</th>
        <th>Complete Pole Numbers</th>
        <th>Location</th>
        <th>Beneficiary_Contact</th>
        <th>Remarks</th>
        <th>Actions</th>
        </tr>
      </x-slot:thead>
      <x-slot:tbody>
        @foreach ($poles as $pole)
          <tr>
          
          <td>{{ $pole->ward_name ?? "N/A" }}</td>
          <td>{{ $pole->complete_pole_number ?? "N/A" }}</td>
          <td>{{ $pole->lat && $survey->lng ? $survey->lat .', '. $survey->lng : "N/A" }}</td>
          <td>{{ $pole->beneficiary_contact ?? "N/A" }}</td>
          <td>{{ $pole->remarks ?? "N/A" }}</td>
          <td>
            <!-- View Button -->
            <a href="{{-- route("inventory.show", $member->id) --}}" class="btn btn-icon btn-info" data-toggle="tooltip" title="View Details">
              <i class="mdi mdi-eye"></i>
            </a>

            <!-- Delete Button -->

          </td>
          </tr>
        @endforeach
      </x-slot:tbody>
    </x-data-table>
  </div>
@endsection
