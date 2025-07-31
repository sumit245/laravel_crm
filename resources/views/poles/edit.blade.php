@extends("layouts.main")

@section("content")
  <div class="pd-20 pd-xl-25 container">
    <div class="d-flex align-items-center justify-content-between mg-b-25">
      <h6 class="mg-b-0">Edit Pole Details</h6>
      <div class="d-flex">
        <a href="{{ route("poles.show", $pole->id) }}" class="btn btn-sm btn-white d-flex align-items-center">
          <span class="d-none d-sm-inline mg-l-5">Cancel</span>
        </a>
      </div>
    </div>

    @if (session("success"))
      <div class="alert alert-success">
        {{ session("success") }}
      </div>
    @endif

    @if (session("error"))
      <div class="alert alert-danger">
        {{ session("error") }}
      </div>
    @endif

    @if ($errors->any())
      <div class="alert alert-danger">
        <ul class="mb-0">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form action="{{ route("poles.update", $pole->id) }}" method="POST">
      @csrf
      @method("PUT")
      
      <div class="row">
        <!-- Non-editable fields -->
        <div class="col-3 col-sm-3">
          <label class="tx-10 tx-medium tx-spacing-1 tx-color-03 tx-uppercase tx-sans mg-b-10">Complete Pole Number</label>
          <p class="mg-b-0 text-muted">{{ $pole->complete_pole_number }}</p>
          <small class="text-muted">Cannot be edited</small>
        </div>

        <!-- Editable Location -->
        <div class="col-3 col-sm-3">
          <label class="tx-10 tx-medium tx-spacing-1 tx-color-03 tx-uppercase tx-sans mg-b-10">Latitude</label>
          <input type="number" step="any" name="lat" class="form-control" value="{{ old("lat", $pole->lat) }}">
        </div>

        <div class="col-3 col-sm-3">
          <label class="tx-10 tx-medium tx-spacing-1 tx-color-03 tx-uppercase tx-sans mg-b-10">Longitude</label>
          <input type="number" step="any" name="lng" class="form-control" value="{{ old("lng", $pole->lng) }}">
        </div>

        <!-- Ward Name -->
        <div class="col-3 col-sm-3">
          <label class="tx-10 tx-medium tx-spacing-1 tx-color-03 tx-uppercase tx-sans mg-b-10">Ward Name</label>
          <input type="text" name="ward_name" class="form-control" value="{{ old("ward_name", $pole->ward_name) }}">
        </div>
      </div>

      <div class="row mt-3">
        <!-- Beneficiary -->
        <div class="col-3 col-sm-3">
          <label class="tx-10 tx-medium tx-spacing-1 tx-color-03 tx-uppercase tx-sans mg-b-10">Beneficiary</label>
          <input type="text" name="beneficiary" class="form-control" value="{{ old("beneficiary", $pole->beneficiary) }}">
        </div>

        <!-- Beneficiary Contact -->
        <div class="col-3 col-sm-3">
          <label class="tx-10 tx-medium tx-spacing-1 tx-color-03 tx-uppercase tx-sans mg-b-10">Beneficiary Contact</label>
          <input type="text" name="beneficiary_contact" class="form-control" value="{{ old("beneficiary_contact", $pole->beneficiary_contact) }}">
        </div>

        <!-- Survey Status -->
        <div class="col-3 col-sm-3">
          <label class="tx-10 tx-medium tx-spacing-1 tx-color-03 tx-uppercase tx-sans mg-b-10">Survey Status</label>
          <select name="isSurveyDone" class="form-control white-select">
            <option value="1" {{ old("isSurveyDone", $pole->isSurveyDone) ? "selected" : "" }}>Yes</option>
            <option value="0" {{ !old("isSurveyDone", $pole->isSurveyDone) ? "selected" : "" }}>No</option>
          </select>
        </div>

        <!-- Installation Status -->
        <div class="col-3 col-sm-3">
          <label class="tx-10 tx-medium tx-spacing-1 tx-color-03 tx-uppercase tx-sans mg-b-10">Installation Status</label>
          <select name="isInstallationDone" class="form-control white-select">
            <option value="1" {{ old("isInstallationDone", $pole->isInstallationDone) ? "selected" : "" }}>Yes</option>
            <option value="0" {{ !old("isInstallationDone", $pole->isInstallationDone) ? "selected" : "" }}>No</option>
          </select>
        </div>
      </div>

      <div class="row mt-3">
        <!-- Network Status -->
        <div class="col-3 col-sm-3">
          <label class="tx-10 tx-medium tx-spacing-1 tx-color-03 tx-uppercase tx-sans mg-b-10">Network Status</label>
          <select name="isNetworkAvailable" class="form-control white-select">
            <option value="1" {{ old("isNetworkAvailable", $pole->isNetworkAvailable) ? "selected" : "" }}>Yes</option>
            <option value="0" {{ !old("isNetworkAvailable", $pole->isNetworkAvailable) ? "selected" : "" }}>No</option>
          </select>
        </div>

        <!-- Non-editable Installer Name -->
        <div class="col-3 col-sm-3">
          <label class="tx-10 tx-medium tx-spacing-1 tx-color-03 tx-uppercase tx-sans mg-b-10">Installer Name</label>
          <p class="mg-b-0 text-muted">{{ $installer->name ?? "" }}</p>
          <small class="text-muted">Cannot be edited</small>
        </div>
      </div>

      <hr />

      <div class="row">
        <!-- QR Codes -->
        <div class="col-3 col-sm-3">
          <label class="tx-10 tx-medium tx-spacing-1 tx-color-03 tx-uppercase tx-sans mg-b-10">Luminary QR</label>
          <input type="text" name="luminary_qr" class="form-control" value="{{ old("luminary_qr", $pole->luminary_qr) }}">
          <small class="text-danger inventory-warning">Changing this will return old inventory</small>
        </div>

        <div class="col-3 col-sm-3">
          <label class="tx-10 tx-medium tx-spacing-1 tx-color-03 tx-uppercase tx-sans mg-b-10">Sim Number</label>
          <input type="text" name="sim_number" class="form-control" value="{{ old("sim_number", $pole->sim_number) }}">
        </div>

        <div class="col-3 col-sm-3">
          <label class="tx-10 tx-medium tx-spacing-1 tx-color-03 tx-uppercase tx-sans mg-b-10">Battery QR</label>
          <input type="text" name="battery_qr" class="form-control" value="{{ old("battery_qr", $pole->battery_qr) }}">
          <small class="text-danger inventory-warning">Changing this will return old inventory</small>
        </div>

        <div class="col-3 col-sm-3">
          <label class="tx-10 tx-medium tx-spacing-1 tx-color-03 tx-uppercase tx-sans mg-b-10">Panel QR</label>
          <input type="text" name="panel_qr" class="form-control" value="{{ old("panel_qr", $pole->panel_qr) }}">
          <small class="text-danger inventory-warning">Changing this will return old inventory</small>
        </div>
      </div>

      <hr />

      <div class="row">
        <!-- Non-editable fields -->
        <div class="col-3 col-sm-3">
          <label class="tx-10 tx-medium tx-spacing-1 tx-color-03 tx-uppercase tx-sans mg-b-10">Engineer</label>
          <p class="mg-b-0 text-muted">{{ $siteEngineer->name ?? "" }}</p>
          <small class="text-muted">Cannot be edited</small>
        </div>

        <div class="col-3 col-sm-3">
          <label class="tx-10 tx-medium tx-spacing-1 tx-color-03 tx-uppercase tx-sans mg-b-10">Project Manager</label>
          <p class="mg-b-0 text-muted">{{ $projectManager->name ?? "" }}</p>
          <small class="text-muted">Cannot be edited</small>
        </div>

        <div class="col-3 col-sm-3">
          <label class="tx-10 tx-medium tx-spacing-1 tx-color-03 tx-uppercase tx-sans mg-b-10">Installation Date</label>
          <p class="mg-b-0 text-muted">{{ $pole->created_at }}</p>
          <small class="text-muted">Cannot be edited</small>
        </div>

        <div class="col-3 col-sm-3">
          <label class="tx-10 tx-medium tx-spacing-1 tx-color-03 tx-uppercase tx-sans mg-b-10">Submitted at</label>
          <p class="mg-b-0 text-muted">{{ $pole->isInstallationDone == 1 ? $pole->updated_at : "" }}</p>
          <small class="text-muted">Cannot be edited</small>
        </div>
      </div>

      <hr />

      <div class="row">
        <!-- Remarks -->
        <div class="col-12">
          <label class="tx-10 tx-medium tx-spacing-1 tx-color-03 tx-uppercase tx-sans mg-b-10">Remarks</label>
          <textarea name="remarks" class="form-control" rows="3">{{ old("remarks", $pole->remarks) }}</textarea>
        </div>
      </div>

      <hr />

      <div class="row">
        <div class="col-12">
          <button type="submit" class="btn btn-primary">Update Pole Details</button>
          <a href="{{ route("poles.show", $pole->id) }}" class="btn btn-secondary ml-2">Cancel</a>
        </div>
      </div>
    </form>
  </div>
@endsection

@push("scripts")
  <script>
    // Add any additional JavaScript if needed
    $(document).ready(function() {
      // You can add form validation or other interactive features here
    });
  </script>
@endpush

@push("styles")
  <style>
    /* White background for select dropdowns */
    .white-select {
      background-color: #ffffff !important;
      color: #333333 !important;
    }
    
    .white-select option {
      background-color: #ffffff !important;
      color: #333333 !important;
    }
    
    /* Smaller red text for inventory warnings */
    .inventory-warning {
      font-size: 0.75rem !important;
      color: #dc3545 !important;
      font-weight: 500;
    }
  </style>
@endpush
