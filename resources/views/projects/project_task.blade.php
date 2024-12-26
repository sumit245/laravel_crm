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
              <label for="selectSite" class="form-label">Select Site</label>
              <select id="selectSite" name="sites[]" class="form-select" style="height: 200px !important;" multiple
                required>
                @foreach ($sites as $site)
                  <option value="{{ $site->id }}">{{ $site->site_name }}</option>
                @endforeach
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
            </div>
            <div class="mb-3">
              <label for="endDate" class="form-label">End Date</label>
              <input type="date" id="endDate" name="end_date" class="form-control" required>
            </div>
          </div>
          <div class="modal-footer">
            <button type="reset" class="btn btn-secondary">Reset</button>
            <button type="submit" class="btn btn-primary">Allot Target</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Table to display targets -->
  <div class="table-responsive mt-4">
    <table class="table-striped table">
      <thead>
        <tr>
          <th>#</th>
          <th>Site Name</th>
          <th>Activity</th>
          <th>Site Engineer</th>
          <th>Start Date</th>
          <th>End Date</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($targets as $index => $target)
          <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $target->site->site_name }}</td>
            <td>{{ $target->activity }}</td>
            <td>{{ $target->engineer->firstName }}</td>
            <td>{{ $target->start_date }}</td>
            <td>{{ $target->end_date }}</td>
            <td>
              <a href="{{ route("tasks.show", $target->id) }}" class="btn btn-sm btn-info">View</a>
              <a href="{{ route("tasks.edit", $target->id) }}" class="btn btn-sm btn-warning">Edit</a>
              <form action="{{ route("tasks.destroy", $target->id) }}" method="POST" style="display: inline-block;">
                @csrf
                @method("DELETE")
                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7">No targets found.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
