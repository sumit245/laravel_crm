@extends("layouts.main")

@section("content")
  <div class="content-wrapper p-2">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title">Add Site</h4>

        <!-- Display validation errors -->
        @if ($errors->any())
          <div class="alert alert-danger">
            <ul>
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <form action="{{ route("sites.store") }}" method="POST">
          @csrf

          <!-- Basic Details -->
          <h6 class="card-subtitle text-bold text-info">Basic Details</h6>
          <div class="form-group row mt-5">
            <div class="col-md-6">
              <label for="state">State:</label>
              <select class="form-select" id="state" name="state">
                <option value="">Select State</option>
                @foreach ($states as $state)
                  <option value="{{ $state->id }}">{{ $state->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6">
              <label for="district">City:</label>
              <select class="form-select" id="district" name="district">
                <option value="">Select City</option>
                <!-- Cities will be dynamically populated using JavaScript -->
              </select>
            </div>
          </div>
          <div class="form-group row">
            <div class="col-md-4">
              <label for="location">Location:</label>
              <input type="text" class="form-control" id="location" placeholder="Enter location" name="location"
                value="{{ old("location") }}">
            </div>
            <div class="col-md-4">
              <label for="project_id">Project Name:</label>
              <select class="form-select" id="project_id" name="project_id">
                <option value="">Select Project</option>
                @foreach ($projects as $project)
                  <option value="{{ $project->id }}">{{ $project->project_name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-4">
              <label for="site_name">Site Name:</label>
              <input type="text" class="form-control" id="site_name" placeholder="Enter site name" name="site_name"
                value="{{ old("site_name") }}">
            </div>
          </div>
          <div class="form-group row">
            <div class="col-md-4">
              <label for="vendorName">I&C Vendor Name</label>
              <select class="form-select" id="vendorName" name="vendorName">
                <option value="">Select Vendor</option>
                @foreach ($vendors as $vendor)
                  <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-4">
              <label for="vendorName">Site Engineer</label>
              <select class="form-select" id="vendorName" name="vendorName">
                <option value="">Select Site Engineer</option>
                @foreach ($staffs as $staff)
                  <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-4">
              <label for="contact">Contact No:</label>
              <input type="text" class="form-control" id="contact" placeholder="Enter contact number" name="contact"
                value="{{ old("contact") }}">
            </div>
          </div>

          <hr />

          <!-- Project Details -->
          <h6 class="card-subtitle text-bold text-info">Project Details</h6>
          <div class="form-group row mt-5">
            <div class="col-md-4">
              <label for="meterNumber">Meter Number:</label>
              <input type="text" class="form-control" id="meterNumber" placeholder="Enter meter number"
                name="meterNumber" value="{{ old("meterNumber") }}">
            </div>
            <div class="col-md-4">
              <label for="netMeterSI">Net Meter SI. No:</label>
              <input type="text" class="form-control" id="netMeterSI" placeholder="Enter net meter SI number"
                name="netMeterSI" value="{{ old("netMeterSI") }}">
            </div>
            <div class="col-md-4">
              <label for="solarMeterSI">Solar Meter SI No:</label>
              <input type="text" class="form-control" id="solarMeterSI" placeholder="Enter solar meter SI number"
                name="solarMeterSI" value="{{ old("solarMeterSI") }}">
            </div>
          </div>
          <div class="form-group row">
            <div class="col-md-4">
              <label for="capacity">Project Capacity:</label>
              <input type="text" class="form-control" id="capacity" placeholder="Enter project capacity"
                name="capacity" value="{{ old("capacity") }}">
            </div>
            <div class="col-md-4">
              <label for="caNumber">CA Number:</label>
              <input type="text" class="form-control" id="caNumber" placeholder="Enter CA number" name="caNumber"
                value="{{ old("caNumber") }}">
            </div>
            <div class="col-md-4">
              <label for="load">Sanction Load:</label>
              <input type="text" class="form-control" id="load" placeholder="Enter sanction load"
                name="load" value="{{ old("load") }}">
            </div>
          </div>

          <hr />

          <!-- Load Enhancement Status and Site Survey Status Section -->
          <h6 class="card-subtitle text-bold text-info">Status Information</h6>
          <div class="form-group row mt-5">
            <div class="col-md-6">
              <label for="loadStatus">Load Enhancement Status:</label>
              <input type="text" class="form-control" id="loadStatus" placeholder="Enter load enhancement status"
                name="loadStatus" value="{{ old("loadStatus") }}">
            </div>
            <div class="col-md-6">
              <label for="siteSurvey">Site Survey Status:</label>
              <input type="text" class="form-control" id="siteSurvey" placeholder="Enter site survey status"
                name="siteSurvey" value="{{ old("siteSurvey") }}">
            </div>
          </div>
          <div class="form-group row">
            <div class="col-md-4">
              <label for="inspectionDate">Material Inspection Date:</label>
              <input type="date" class="form-control navbar-date-picker" id="inspectionDate" name="inspectionDate"
                value="{{ old("inspectionDate") }}">
            </div>
            <div class="col-md-4">
              <label for="installationDate">SPP Installation Date:</label>
              <input type="date" class="form-control" id="installationDate" name="installationDate"
                value="{{ old("installationDate") }}">
            </div>
            <div class="col-md-4">
              <label for="commissioningDate">Commissioning Date:</label>
              <input type="date" class="form-control" id="commissioningDate" name="commissioningDate"
                value="{{ old("commissioningDate") }}">
            </div>
          </div>

          <hr />

          <div class="form-group">
            <label for="remarks">Remarks:</label>
            <textarea class="form-control" style="height:80px;" id="remarks" placeholder="Enter remarks" name="remarks" rows="16" cols="50">{{ old("remarks") }}</textarea>
          </div>

          <button type="submit" class="btn btn-primary">Add Site</button>
        </form>

      </div>
    </div>
  </div>

  @push("scripts")
    <script>
      $(document).ready(function() {
        $('#state').on('change', function() {
          var idState = this.value;
          $("#district").html('');
          $.ajax({
            url: "{{ url("api/fetch-cities") }}",
            type: "POST",
            data: {
              state_id: idState,
              _token: '{{ csrf_token() }}'
            },
            dataType: 'json',
            success: function(res) {
              $('#district').html('<option value="">-- Select City --</option>');
              $.each(res.cities, function(key, value) {
                $("#district").append('<option value="' + value.id + '">' + value.name + '</option>');
              });
            }
          });
        });
      })
    </script>
  @endpush
@endsection
