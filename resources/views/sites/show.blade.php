@extends("layouts.main")

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

        @if ($projectId == 11)
          <!-- Row 1 -->
          <div class="row my-4">
            <div class="col-md-3 col-sm-3">
              <label class="font-10 text-uppercase mg-b-10">Site
                name</label>
              <p class="mg-b-0">{{ $streetlight->task_id ?? "N/A" }}</p>
            </div>
            <div class="col-md-3 col-sm-3">
              <label class="font-10 text-uppercase mg-b-10">State</label>
              <p class="mg-b-0">{{ $streetlight->state ?? "N/A" }}</p>
            </div>
            <div class="col-md-3 col-sm-3">
              <label class="font-10 text-uppercase mg-b-10">District</label>
              <p class="mg-b-0">{{ $streetlight->district ?? "N/A" }}</p>
            </div>
            <div class="col-md-3 col-sm-3">
              <label class="font-10 text-uppercase mg-b-10">Location</label>
              <p class="mg-b-0">{{ $streetlight->panchayat ?? "N/A" }}</p>
            </div>
          </div>

          <!-- Row 2 -->
          <div class="row my-4">

            <div class="col-md-3 col-sm-3">
              <label class="font-10 text-uppercase mg-b-10">Wards</label>
              <p class="mg-b-0">{{ $streetlight->ward ?? "N/A" }}</p>
            </div>
            <div class="col-md-3 col-sm-3">
              <label class="font-10 text-uppercase mg-b-10">Pole Number</label>
              <p class="mg-b-0">{{ $streetlight->complete_pole_number ?? "N/A" }}</p>
            </div>
            <div class="col-md-3 col-sm-3">
              <label class="font-10 text-uppercase mg-b-10">Contact No</label>
              <p class="mg-b-0">{{ $streetlight->mukhiya_contact ?? "N/A" }}</p>
            </div>
            <div class="col-md-3 col-sm-3">
              <label class="font-10 text-uppercase mg-b-10">Location</label>
              <p class="mg-b-0">{{ $streetlight->lat ?? "N/A" }}, {{ $streetlight->lng ?? "N/A" }}</p>
            </div>

          </div>

          <!-- Row 3 -->
          <div class="row my-4">
            <div class="col-md-3 col-sm-3">
              <label class="font-10 text-uppercase mg-b-10">Luminary QR</label>
              <p class="mg-b-0">{{ $streetlight->luminary_qr ?? "N/A" }}</p>
            </div>
            <div class="col-md-3 col-sm-3">
              <label class="font-10 text-uppercase mg-b-10">Battery QR</label>
              <p class="mg-b-0">{{ $streetlight->battery_qr ?? "N/A" }}</p>
            </div>
            <div class="col-md-3 col-sm-3">
              <label class="font-10 text-uppercase mg-b-10">Panel QR</label>
              <p class="mg-b-0">{{ $streetlight->panel_qr ?? "N/A" }}</p>
            </div>
            <div class="col-md-3 col-sm-3">
              <label class="font-10 text-uppercase mg-b-10">Total Poles</label>
              <p class="mg-b-0">{{ $streetlight->total_poles ?? "N/A" }}</p>
            </div>

          </div>
        @else
          <div class="row my-4">
            <div class="col-md-3 col-sm-3">
              <label class="font-10 text-uppercase mg-b-10">Site name</label>
              <p class="mg-b-0">{{ $site->site_name ?? "N/A" }}</p>
            </div>
            <div class="col-md-3 col-sm-3">
              <label class="font-10 text-uppercase mg-b-10">State</label>
              <p class="mg-b-0">{{ $site->stateRelation->name ?? "N/A" }}</p>
            </div>
            <div class="col-md-3 col-sm-3">
              <label class="font-10 text-uppercase mg-b-10">District</label>
              <p class="mg-b-0">{{ $site->districtRelation->name ?? "N/A" }}</p>
            </div>
            <div class="col-md-3 col-sm-3">
              <label class="font-10 text-uppercase mg-b-10">Location</label>
              <p class="mg-b-0">{{ $site->location ?? "N/A" }}</p>
            </div>

          </div>
          {{-- donr --}}
          <div class="row my-4">

            <div class="col-md-3 col-sm-3">
              <label class="font-10 text-uppercase mg-b-10">I&C Vendor Name</label>
              <p class="mg-b-0">{{ $site->ic_vendor_name ?? "N/A" }}</p>
            </div>
            <div class="col-md-3 col-sm-3">
              <label class="font-10 text-uppercase mg-b-10">Site Engineer</label>
              <p class="mg-b-0">{{ $site->site_engineer ?? "N/A" }}</p>
            </div>
            <div class="col-md-3 col-sm-3">
              <label class="font-10 text-uppercase mg-b-10">Contact No</label>
              <p class="mg-b-0">{{ $site->contact_no ?? "N/A" }}</p>
            </div>
            <div class="col-md-3 col-sm-3">
              <label class="font-10 text-uppercase mg-b-10">Project Serial</label>
              <p class="mg-b-0">{{ $site->project_id ?? "N/A" }}</p>
            </div>

          </div>
          {{-- done --}}
          <hr class="my-4">
          <div class="row my-4">
            <div class="col-md-3 col-sm-3">
              <label class="font-10 text-uppercase mg-b-10">Ca number</label>
              <p class="mg-b-0">{{ $site->ca_number ?? "N/A" }}</p>
            </div>
            <div class="col-md-3 col-sm-3">
              <label class="font-10 text-uppercase mg-b-10">Meter Number</label>
              <p class="mg-b-0">{{ $site->meter_number ?? "N/A" }}</p>
            </div>
            <div class="col-md-3 col-sm-3">
              <label class="font-10 text-uppercase mg-b-10">Net Meter SI. No</label>
              <p class="mg-b-0">{{ $site->net_meter_sr_no ?? "N/A" }}</p>
            </div>
            <div class="col-md-3 col-sm-3">
              <label class="font-10 text-uppercase mg-b-10">Solar Meter SI No</label>
              <p class="mg-b-0">{{ $site->solar_meter_sr_no ?? "N/A" }}</p>
            </div>

          </div>
          <div class="row my-4">
            <div class="col-md-3 col-sm-3">
              <label class="font-10 text-uppercase mg-b-10">Project Capacity</label>
              <p class="mg-b-0">{{ $site->project_capacity ?? "N/A" }}</p>
            </div>
            <div class="col-md-3 col-sm-3">
              <label class="font-10 text-uppercase mg-b-10">Sanction Load</label>
              <p class="mg-b-0">{{ $site->sanction_load ?? "N/A" }}</p>
            </div>
            <div class="col-md-3 col-sm-3">
              <label class="font-10 text-uppercase mg-b-10">Load Enhancement Status</label>
              <p class="mg-b-0">{{ $site->load_enhancement_status ?? "N/A" }}</p>
            </div>
            <div class="col-md-3 col-sm-3">
              <label class="font-10 text-uppercase mg-b-10">Site Survey Status</label>
              <p class="mg-b-0">{{ $site->site_survey_status ?? "N/A" }}</p>
            </div>
          </div>
          <hr />

          <div class="row my-4">
            <div class="col-md-4 col-sm-4">
              <label class="font-10 text-uppercase mg-b-10">Material Inspection Date</label>
              <p class="mg-b-0">{{ $site->material_inspection_date ?? "N/A" }}</p>
            </div>
            <div class="col-md-4 col-sm-4">
              <label class="font-10 text-uppercase mg-b-10">SPP Installation Date</label>
              <p class="mg-b-0">{{ $site->spp_installation_date ?? "N/A" }}</p>
            </div>
            <div class="col-md-4 col-sm-4">
              <label class="font-10 text-uppercase mg-b-10">Commissioning Date</label>
              <p class="mg-b-0">{{ $site->commissioning_date ?? "N/A" }}</p>
            </div>
            <div class="col-md-3 col-sm-3">
              <label class="font-10 text-uppercase mg-b-10">Remarks</label>
              <p class="mg-b-0">{{ $site->remarks ?? "N/A" }}</p>
            </div>

          </div>
        @endif

      </div>
    </div>
  </div>
@endsection
