<div>
  <div class="d-flex justify-content-between mb-4">
    <div class="d-flex mx-2">
      <div class="card bg-success mx-2" style="min-width: 33%;">
        <div class="card-body">
          <h5 class="card-title">0</h5>
          <p class="card-text">Installation</p>
        </div>
      </div>
      <div class="card bg-warning mx-2" style="min-width: 33%;">
        <div class="card-body">
          <h5 class="card-title">0</h5>
          <p class="card-text">RMS</p>
        </div>
      </div>
      <div class="card bg-info mx-2" style="min-width: 33%;">
        <div class="card-body">
          <h5 class="card-title">0</h5>
          <p class="card-text">Final Inspection</p>
        </div>
      </div>
    </div>
    <a href="{{ route("tasks.create", $project->id) }}" class="btn btn-primary" style="max-height: 2.8rem;">Add
      Target</a>
  </div>
  <!-- Add your task-specific content here -->
</div>
