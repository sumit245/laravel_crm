@extends("layouts.main") {{-- or the name of your base layout --}}

@section("content")
  <div class="content-wrapper">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between my-2">
          <h6 class="card-subtitle text-bold text-info">Basic Details</h6>
          <a href="{{ route("sites.edit", $site->id) }}" class="btn btn-icon btn-warning" data-toggle="tooltip"
            title="Edit Site">
            <i class="mdi mdi-pencil"> Update Site</i>
          </a>
        </div>

        <div class="row my-4">

          <div class="col-md-3 col-sm-3">
            <label class="font-10 text-uppercase mg-b-10">Site
              name</label>
            <p class="mg-b-0">{{ $site->site_name }}</p>
          </div>
          <div class="col-md-3 col-sm-3">
            <label class="font-10 text-uppercase mg-b-10">State</label>
            <p class="mg-b-0">{{ $site->state }}</p>
          </div>
          <div class="col-md-3 col-sm-3">
            <label class="font-10 text-uppercase mg-b-10">District</label>
            <p class="mg-b-0">{{ $site->district }}</p>
          </div>
          <div class="col-md-3 col-sm-3">
            <label class="font-10 text-uppercase mg-b-10">Location</label>
            <p class="mg-b-0">{{ $site->location }}</p>
          </div>

        </div>
        {{-- donr --}}
        <div class="row my-4">

          <div class="col-md-3 col-sm-3">
            <label class="font-10 text-uppercase mg-b-10">I&C Vendor Name</label>
            <p class="mg-b-0">{{ $site->ic_vendor_name }}</p>
          </div>
          <div class="col-md-3 col-sm-3">
            <label class="font-10 text-uppercase mg-b-10">Site Engineer</label>
            <p class="mg-b-0">{{ $site->site_engineer }}</p>
          </div>
          <div class="col-md-3 col-sm-3">
            <label class="font-10 text-uppercase mg-b-10">Contact No</label>
            <p class="mg-b-0">{{ $site->contact_no }}</p>
          </div>
          <div class="col-md-3 col-sm-3">
            <label class="font-10 text-uppercase mg-b-10">Project Serial</label>
            <p class="mg-b-0">{{ $site->project_id }}</p>
          </div>

        </div>
        {{-- done --}}
        <hr class="my-4">
        <div class="row my-4">
          <div class="col-md-3 col-sm-3">
            <label class="font-10 text-uppercase mg-b-10">Ca number</label>
            <p class="mg-b-0">{{ $site->ca_number }}</p>
          </div>
          <div class="col-md-3 col-sm-3">
            <label class="font-10 text-uppercase mg-b-10">Meter Number</label>
            <p class="mg-b-0">{{ $site->meter_number }}</p>
          </div>
          <div class="col-md-3 col-sm-3">
            <label class="font-10 text-uppercase mg-b-10">Net Meter SI. No</label>
            <p class="mg-b-0">{{ $site->net_meter_sr_no }}</p>
          </div>
          <div class="col-md-3 col-sm-3">
            <label class="font-10 text-uppercase mg-b-10">Solar Meter SI No</label>
            <p class="mg-b-0">{{ $site->solar_meter_sr_no }}</p>
          </div>

        </div>
        <div class="row my-4">
          <div class="col-md-3 col-sm-3">
            <label class="font-10 text-uppercase mg-b-10">Project Capacity</label>
            <p class="mg-b-0">{{ $site->project_capacity }}</p>
          </div>
          <div class="col-md-3 col-sm-3">
            <label class="font-10 text-uppercase mg-b-10">Sanction Load</label>
            <p class="mg-b-0">{{ $site->sanction_load }}</p>
          </div>
          <div class="col-md-3 col-sm-3">
            <label class="font-10 text-uppercase mg-b-10">Load Enhancement Status</label>
            <p class="mg-b-0">{{ $site->load_enhancement_status }}</p>
          </div>
          <div class="col-md-3 col-sm-3">
            <label class="font-10 text-uppercase mg-b-10">Site Survey Status</label>
            <p class="mg-b-0">{{ $site->site_survey_status }}</p>
          </div>
        </div>
        <hr />

        <div class="row my-4">
          <div class="col-md-4 col-sm-4">
            <label class="font-10 text-uppercase mg-b-10">Material Inspection Date</label>
            <p class="mg-b-0">{{ $site->material_inspection_date }}</p>
          </div>
          <div class="col-md-4 col-sm-4">
            <label class="font-10 text-uppercase mg-b-10">SPP Installation Date</label>
            <p class="mg-b-0">{{ $site->spp_installation_date }}</p>
          </div>
          <div class="col-md-4 col-sm-4">
            <label class="font-10 text-uppercase mg-b-10">Commissioning Date</label>
            <p class="mg-b-0">{{ $site->commissioning_date }}</p>
          </div>
          <div class="col-md-3 col-sm-3">
            <label class="font-10 text-uppercase mg-b-10">Remarks</label>
            <p class="mg-b-0">{{ $site->remarks }}</p>
          </div>

        </div>

      </div>
    </div>
  </div>
@endsection
