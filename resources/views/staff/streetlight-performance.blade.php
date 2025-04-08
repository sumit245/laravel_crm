<div class="tab-content mt-1" id="myTabContent">
  <ul class="nav nav-tabs fixed-navbar-project" id="myTab" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link active" id="sites-tab" data-bs-toggle="tab" data-bs-target="#sites" type="button"
        role="tab" aria-controls="sites" aria-selected="true">
        Assigned Tasks
      </button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="staff-tab" data-bs-toggle="tab" data-bs-target="#staff" type="button" role="tab"
        aria-controls="staff">
        Surveyed Poles
      </button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="vendors-tab" data-bs-toggle="tab" data-bs-target="#vendors" type="button"
        role="tab" aria-controls="vendors" aria-selected="false">
        Installed Poles
      </button>
    </li>
  </ul>
  <!-- Sites Tab -->

  <div class="tab-pane fade show active" id="sites" role="tabpanel" aria-labelledby="sites-tab">
    {{-- <div class="p-2">   --}}
    @include("staff.assignedTasks")
    {{-- </div> --}}
  </div>

  <!-- Staffs Tab -->
  <div class="tab-pane fade" id="staff" role="tabpanel" aria-labelledby="staff-tab">
    {{-- <div class="p-2">   --}}
    @include("staff.surveyedPoles")
    {{-- </div> --}}
  </div>

  <!-- Vendors Tab -->
  <div class="tab-pane fade" id="vendors" role="tabpanel" aria-labelledby="vendors-tab">
    {{-- <div class="p-2">   --}}
    @include("staff.installedPoles")
    {{-- </div> --}}
  </div>
</div>
