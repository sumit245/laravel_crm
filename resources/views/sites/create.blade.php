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
              <label for="ic_vendor_name">I&C Vendor Name</label>
              <select class="form-select" id="ic_vendor_name" name="ic_vendor_name">
                <option value="">Select Vendor</option>
                @foreach ($vendors as $vendor)
                  <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-4">
              <label for="site_engineer">Site Engineer</label>
              <select class="form-select" id="site_engineer" name="site_engineer">
                <option value="">Select Site Engineer</option>
                @foreach ($staffs as $staff)
                  <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-4">
              <label for="contact">Contact No:</label>
              <input type="text" class="form-control" id="contact" placeholder="Enter contact number"
                name="contact_no" value="{{ old("contact_no") }}">
            </div>
          </div>

          <hr />

          <!-- Project Details -->
          <h6 class="card-subtitle text-bold text-info">Project Details</h6>
          <div class="form-group row mt-5">
            <div class="col-md-4">
              <label for="meterNumber">Meter Number:</label>
              <input type="text" class="form-control" id="meterNumber" placeholder="Enter meter number"
                name="meter_number" value="{{ old("meter_number") }}">
            </div>
            <div class="col-md-4">
              <label for="netMeterSI">Net Meter SI. No:</label>
              <input type="text" class="form-control" id="netMeterSI" placeholder="Enter net meter SI number"
                name="net_meter_sr_no" value="{{ old("net_meter_sr_no") }}">
            </div>
            <div class="col-md-4">
              <label for="solarMeterSI">Solar Meter SI No:</label>
              <input type="text" class="form-control" id="solarMeterSI" placeholder="Enter solar meter SI number"
                name="solar_meter_sr_no" value="{{ old("solar_meter_sr_no") }}">
            </div>
          </div>
          <div class="form-group row">
            <div class="col-md-4">
              <label for="capacity">Project Capacity:</label>
              <input type="text" class="form-control" id="capacity" placeholder="Enter project capacity"
                name="project_capacity" value="{{ old("project_capacity") }}">
            </div>
            <div class="col-md-4">
              <label for="caNumber">CA Number:</label>
              <input type="text" class="form-control" id="caNumber" placeholder="Enter CA number"
                name="ca_number" value="{{ old("ca_number") }}">
            </div>
            <div class="col-md-4">
              <label for="load">Sanction Load:</label>
              <input type="text" class="form-control" id="load" placeholder="Enter sanction load"
                name="sanction_load" value="{{ old("sanction_load") }}">
            </div>
          </div>

          <hr />

          <!-- Load Enhancement Status and Site Survey Status Section -->
          <h6 class="card-subtitle text-bold text-info">Status Information</h6>
          <div class="form-group row mt-5">

            <div class="col-md-6">
              <label for="loadStatus">Load Enhancement Status:</label>
              <select class="form-control" id="loadStatus" name="load_enhancement_status">
                <option value="">-- Select Status --</option>
                <option value="Yes" {{ old("load_enhancement_status") == "Yes" ? "selected" : "" }}>Yes</option>
                <option value="No" {{ old("load_enhancement_status") == "No" ? "selected" : "" }}>No</option>
              </select>
            </div>

            <div class="col-md-6">
              <label for="siteSurvey">Site Survey Status:</label>
              <input type="text" class="form-control" id="siteSurvey" placeholder="Enter site survey status"
                name="site_survey_status" value="{{ old("site_survey_status") }}">
            </div>
          </div>
          <div class="form-group row">
            <div class="col-md-4">
              <label for="inspectionDate">Material Inspection Date:</label>
              <input onclick="document.getElementById('inspectionDate').showPicker()" type="date" class="form-control navbar-date-picker" id="inspectionDate"
                name="material_inspection_date" value="{{ old("material_inspection_date") }}">
            </div>
            <div class="col-md-4">
              <label for="installationDate">SPP Installation Date:</label>
              <input onclick="document.getElementById('installationDate').showPicker()" type="date" class="form-control" id="installationDate" name="spp_installation_date"
                value="{{ old("spp_installation_date") }}">
            </div>
            <div class="col-md-4">
              <label for="commissioningDate">Commissioning Date:</label>
              <input onclick="document.getElementById('commissioningDate').showPicker()" type="date" class="form-control" id="commissioningDate" name="commissioning_date"
                value="{{ old("commissioning_date") }}">
            </div>
          </div>

          <hr />

          <div class="form-group">
            <label for="remarks">Remarks:</label>
            <textarea class="form-control" style="height:80px;" id="remarks" placeholder="Enter remarks" name="remarks"
              rows="16" cols="50">{{ old("remarks") }}</textarea>
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
