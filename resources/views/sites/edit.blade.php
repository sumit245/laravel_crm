@extends("layouts.main")

@section("content")
  <div class="content-wrapper p-2">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title">Update Site</h4>

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

        <form action="{{ route("sites.update", $site->id) }}" method="POST">
          @csrf
          @method("PUT")

          <!-- Basic Details -->
          <h6 class="card-subtitle text-bold text-info">Basic Details</h6>
          <div class="form-group row mt-5">
            <div class="col-md-6">
              <label for="state">State:</label>
              <input type="text" class="form-control" id="state" name="state"
                value="{{ old("state", $site->state) }}" @disabled(true)>
            </div>
            <div class="col-md-6">
              <label for="district">City:</label>
              <input type="text" class="form-control" id="district" name="district"
                value="{{ old("district", $site->district) }}" @disabled(true)>
            </div>
          </div>
          <div class="form-group row">
            <div class="col-md-4">
              <label for="location">Location:</label>
              <input type="text" class="form-control" id="location" name="location"
                value="{{ old("location", $site->location) }}" @disabled(true)>
            </div>
            <div class="col-md-4">
              <label for="project_id">Project Name:</label>
              <input type="text" class="form-control" id="project_id" name="project_id"
                value="{{ old("project_id", $site->project_id) }}" @disabled(true)>
            </div>
            <div class="col-md-4">
              <label for="site_name">Site Name:</label>
              <input type="text" class="form-control" id="site_name" name="site_name"
                value="{{ old("site_name", $site->site_name) }}">
            </div>
          </div>
          <div class="form-group row">
            <div class="col-md-4">
              <label for="vendorName">I&C Vendor Name:</label>
              <input type="text" class="form-control" id="vendorName" name="ic_vendor_name"
                value="{{ old("vendorName", $site->ic_vendor_name) }}">
            </div>
            <div class="col-md-4">
              <label for="site_engineer">Site Engineer:</label>
              <input type="text" class="form-control" id="site_engineer" name="site_engineer"
                value="{{ old("site_engineer", $site->site_engineer) }}">
            </div>
            <div class="col-md-4">
              <label for="contact">Contact No:</label>
              <input type="text" class="form-control" id="contact" name="contact_no"
                value="{{ old("contact", $site->contact_no) }}">
            </div>
          </div>

          <hr />

          <!-- Project Details -->
          <h6 class="card-subtitle text-bold text-info">Project Details</h6>
          <div class="form-group row mt-5">
            <div class="col-md-4">
              <label for="meterNumber">Meter Number:</label>
              <input type="text" class="form-control" id="meterNumber" name="meter_number"
                value="{{ old("meterNumber", $site->meter_number) }}">
            </div>
            <div class="col-md-4">
              <label for="netMeterSI">Net Meter SI. No:</label>
              <input type="text" class="form-control" id="netMeterSI" name="net_meter_sr_no"
                value="{{ old("netMeterSI", $site->net_meter_sr_no) }}">
            </div>
            <div class="col-md-4">
              <label for="solarMeterSI">Solar Meter SI No:</label>
              <input type="text" class="form-control" id="solarMeterSI" name="solar_meter_sr_no"
                value="{{ old("solarMeterSI", $site->solar_meter_sr_no) }}">
            </div>
          </div>
          <div class="form-group row">
            <div class="col-md-4">
              <label for="capacity">Project Capacity:</label>
              <input type="text" class="form-control" id="capacity" name="project_capacity"
                value="{{ old("capacity", $site->project_capacity) }}">
            </div>
            <div class="col-md-4">
              <label for="caNumber">CA Number:</label>
              <input type="text" class="form-control" id="caNumber" name="ca_number"
                value="{{ old("caNumber", $site->ca_number) }}">
            </div>
            <div class="col-md-4">
              <label for="load">Sanction Load:</label>
              <input type="text" class="form-control" id="load" name="sanction_load"
                value="{{ old("load", $site->sanction_load) }}">
            </div>
          </div>

          <hr />

          <!-- Status Information -->
          <h6 class="card-subtitle text-bold text-info">Status Information</h6>
          <div class="form-group row mt-5">
            <div class="col-md-6">
              <label for="loadStatus">Load Enhancement Status:</label>
              <input type="text" class="form-control" id="loadStatus" name="load_enhancement_status"
                value="{{ old("loadStatus", $site->load_enhancement_status) }}">
            </div>
            <div class="col-md-6">
              <label for="siteSurvey">Site Survey Status:</label>
              <input type="text" class="form-control" id="siteSurvey" name="site_survey_status"
                value="{{ old("siteSurvey", $site->site_survey_status) }}">
            </div>
          </div>
          <div class="form-group row">
            <div class="col-md-4">
              <label for="inspectionDate">Material Inspection Date:</label>
              <input type="date" class="form-control" id="inspectionDate" name="material_inspection_date"
                value="{{ old("inspectionDate", $site->material_inspection_date) }}">
            </div>
            <div class="col-md-4">
              <label for="installationDate">SPP Installation Date:</label>
              <input type="date" class="form-control" id="installationDate" name="spp_installation_date"
                value="{{ old("installationDate", $site->spp_installation_date) }}">
            </div>
            <div class="col-md-4">
              <label for="commissioningDate">Commissioning Date:</label>
              <input type="date" class="form-control" id="commissioningDate" name="commissioning_date"
                value="{{ old("commissioningDate", $site->commissioning_date) }}">
            </div>
          </div>

          <hr />

          <div class="form-group">
            <label for="remarks">Remarks:</label>
            <textarea class="form-control" id="remarks" name="remarks" rows="4">{{ old("remarks", $site->remarks) }}</textarea>
          </div>

          <button type="submit" class="btn btn-primary">Update Site</button>
        </form>

      </div>
    </div>
  </div>
@endsection
