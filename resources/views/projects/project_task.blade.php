<div>
  <div class="d-flex justify-content-between mb-4">
    <div class="d-flex mx-2">
      <div class="card bg-success mx-2" style="min-width: 33%;">
        <div class="card-body">
          <h5 class="card-title">{{ $installationCount }}</h5>
          <p class="card-text">Installation</p>
        </div>
      </div>
      <div class="card bg-warning mx-2" style="min-width: 33%;">
        <div class="card-body">
          <h5 class="card-title">{{ $rmsCount }}</h5>
          <p class="card-text">RMS</p>
        </div>
      </div>
      <div class="card bg-info mx-2" style="min-width: 33%;">
        <div class="card-body">
          <h5 class="card-title">{{ $inspectionCount }}</h5>
          <p class="card-text">Final Inspection</p>
        </div>
      </div>
    </div>
    <!-- Button to trigger modal -->
    <button type="button" class="btn btn-primary" style="max-height: 2.8rem;" data-bs-toggle="modal"
      data-bs-target="#addTargetModal">
      Add Target
    </button>
  </div>

  <!-- Modal for adding a target -->
  <div class="modal fade" id="addTargetModal" tabindex="-1" aria-labelledby="addTargetModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <form action="{{ route("tasks.store") }}" method="POST">
          @csrf
          <input type="hidden" name="project_id" value="{{ $project->id }}" />
          <div class="modal-header">
            <h5 class="modal-title" id="addTargetModalLabel">Add Target for Project: {{ $project->project_name }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="form-group mb-3">
              <label for="siteSearch" class="form-label">Search Site</label>
              <input type="text" id="siteSearch" placeholder="Search Site..." class="form-control">
              <div id="siteList"></div>

              <!-- Selected Sites -->
              <ul id="selectedSites"></ul>
              <!-- Hidden Select to Store Selected Sites -->
              <select id="selectedSitesSelect" name="sites[]" multiple class="d-none">
              </select>
            </div>
            <div class="mb-3">
              <label for="activity" class="form-label">Activity</label>
              <select id="activity" name="activity" class="form-select" required>
                <option value="Installation">Installation</option>
                <option value="RMS">RMS</option>
                <option value="Billing">Billing</option>
                <option value="Add Team">Add Team</option>
              </select>
            </div>
            <div class="mb-3">
              <label for="selectEngineer" class="form-label">Select Site Engineer</label>
              <select id="selectEngineer" name="engineer_id" class="form-select" required>
                @foreach ($engineers as $engineer)
                  <option value="{{ $engineer->id }}">{{ $engineer->firstName }} {{ $engineer->lastName }}</option>
                @endforeach
              </select>
            </div>
            <div class="mb-3">
              <label for="startDate" class="form-label">Start Date</label>
              <input type="date" id="startDate" name="start_date" class="form-control" required>
         
  @if ($project->project_type == 1)
    {{-- Streetlight Installation Specific Display --}}
    @include("projects.project_task_streetlight")
  @else
    {{-- Existing Rooftop Installation Code --}}
    @include("projects.project_task_rooftop")
  @endif
</div>
