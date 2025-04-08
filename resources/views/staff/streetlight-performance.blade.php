<div class="tab-content mt-1" id="myTabContent">
  <ul class="nav nav-tabs fixed-navbar-project" id="myTab" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link active" id="panchayats-tab" data-bs-toggle="tab" data-bs-target="#sites" type="button"
        role="tab" aria-controls="sites" aria-selected="true">
        Assigned Tasks
      </button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="surveyed-tab" data-bs-toggle="tab" data-bs-target="#surveyed_poles" type="button"
        role="tab" aria-controls="surveyed-tab">
        Surveyed Poles
      </button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="installed-tab" data-bs-toggle="tab" data-bs-target="#installed_poles" type="button"
        role="tab" aria-controls="installed-tab" aria-selected="false">
        Installed Poles
      </button>
    </li>
  </ul>
  <!-- Sites Tab -->

  <div class="tab-pane fade show active" id="sites" role="tabpanel" aria-labelledby="panchayats-tab">
    @include("staff.assignedTasks")
  </div>

  <!-- Staffs Tab -->
  <div class="tab-pane fade" id="surveyed_poles" role="tabpanel" aria-labelledby="surveyed-tab">
    @include("staff.surveyedPoles")
  </div>

  <!-- Vendors Tab -->
  <div class="tab-pane fade" id="installed_poles" role="tabpanel" aria-labelledby="installed-tab">
    @include("staff.installedPoles")
  </div>
</div>
