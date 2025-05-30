<div class="bg-light mt-4 p-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="fw-bold">Performance Overview</h3>
    <select class="form-select w-auto" name="date_filter" id="taskFilter" onchange="filterTasks()">
      <option value="today" {{ request("date_filter") == "today" ? "selected" : "" }}>Today</option>
      <option value="this_week" {{ request("date_filter") == "this_week" ? "selected" : "" }}>This Week</option>
      <option value="this_month" {{ request("date_filter") == "this_month" ? "selected" : "" }}>This Month</option>
      <option value="all_time" {{ request("date_filter") == "all_time" ? "selected" : "" }}>All Time</option>
      <option value="custom" {{ request("date_filter") == "custom" ? "selected" : "" }}>Custom Range</option>
    </select>
  </div>

  <!-- Custom Date Range Modal -->
  <div class="modal fade" id="customDateModal" tabindex="-1" aria-labelledby="customDateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="customDateModalLabel">Select Custom Date Range</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="customDateForm" action="{{ route("dashboard") }}" method="GET"
            onsubmit="return validateDateRange()">
            <input type="hidden" name="date_filter" value="custom">
            <div class="mb-3">
              <label for="start_date" class="form-label">Start Date</label>
              <input type="date" class="form-control" id="start_date" name="start_date"
                value="{{ request("start_date", date("Y-m-d", strtotime("-30 days"))) }}" onchange="updateEndDateMin()">
            </div>
            <div class="mb-3">
              <label for="end_date" class="form-label">End Date</label>
              <input type="date" class="form-control" id="end_date" name="end_date"
                value="{{ request("end_date", date("Y-m-d")) }}">
              <div id="dateError" class="invalid-feedback"></div>
            </div>
            <div class="d-flex justify-content-end">
              <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary">Apply Filter</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  @foreach ($rolePerformances as $role => $users)
    <div class="card mb-4">
      <div class="card-header">
        <h4>{{ $role }} Performance</h4>
      </div>
      <div class="card-body">
        @if ($users->isEmpty())
          <p class="text-muted">No data available for {{ $role }}.</p>
        @else
          <div class="table-responsive">
            <table class="select-table table">
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Targets</th>
                  @if (!$isStreetLightProject)
                    <th>Completed Targets</th>
                  @endif
                  @if ($isStreetLightProject)
                    <th>Surveyed Poles</th>
                  @else
                    <th>Submitted sites</th>
                  @endif
                  @if ($isStreetLightProject)
                    <th>Installed Lights</th>
                  @else
                    <th>Approved sites</th>
                  @endif
                  @if ($isStreetLightProject)
                    <th>Billed</th>
                  @else
                    <th>Billed sites</th>
                  @endif
                  <th>Performance (%)</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($users as $index => $user)
                  <tr onclick="window.location='{{ route("staff.show", $user->id) }}'" style="cursor: pointer;">
                    <td>
                      <div class="d-flex">
                        <img class="img-sm rounded-10" src={{ $user->image }} alt="profile">
                        <div>
                          <h6>
                            @if ($index == 0)
                              🥇
                            @elseif ($index == 1)
                              🥈
                            @elseif ($index == 2)
                              🥉
                            @endif
                            {{ $user->name }}
                          </h6>
                          @if ($user->role == "Vendor")
                            <p>{{ $user->vendor_name }}</p>
                          @endif
                        </div>
                      </div>
                    </td>
                    <td>{{ $user->totalTasks }}</td>
                    @if (!$isStreetLightProject)
                      <td>{{ $user->completedTasks }}</td>
                    @endif
                    @if ($isStreetLightProject)
                      <td>{{ $user->surveyedPoles ?? 0 }}</td>
                    @else
                      <td>{{ $user->submittedSites ?? 0 }}</td>
                    @endif
                    @if ($isStreetLightProject)
                      <td>{{ $user->installedPoles ?? 0 }}</td>
                    @else
                      <td>{{ $user->approvedSites ?? 0 }}</td>
                    @endif
                    @if ($isStreetLightProject)
                      <td> 0 </td>
                    @else
                      <td> 0 </td>
                    @endif
                    <td>
                      <div class="progress">
                        <div class="progress-bar" role="progressbar"
                          style="width: {{ round($user->performance, 0) }}%;"
                          aria-valuenow="{{ round($user->performance, 0) }}" aria-valuemin="0" aria-valuemax="100">
                        </div>
                      </div>
                      <span class="text-dark">{{ round($user->performance, 0) }}%</span>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @endif
      </div>
    </div>
  @endforeach
</div>

<script>
  function filterTasks() {
    let selectedFilter = document.getElementById('taskFilter').value;

    if (selectedFilter === 'custom') {
      // Show the custom date range modal
      var customDateModal = new bootstrap.Modal(document.getElementById('customDateModal'));
      customDateModal.show();
    } else {
      // Redirect with the selected filter
      let url = new URL(window.location.href);
      url.searchParams.set('date_filter', selectedFilter);

      // Remove any existing custom date parameters if they exist
      url.searchParams.delete('start_date');
      url.searchParams.delete('end_date');

      window.location.href = url.toString();
    }
  }

  // Update the minimum date for the end date input based on the start date
  function updateEndDateMin() {
    const startDate = document.getElementById('start_date').value;
    const endDateInput = document.getElementById('end_date');

    if (startDate) {
      endDateInput.min = startDate;

      // If current end date is before start date, update it
      if (endDateInput.value && endDateInput.value < startDate) {
        endDateInput.value = startDate;
      }
    }
  }

  // Validate the date range before form submission
  function validateDateRange() {
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;
    const dateError = document.getElementById('dateError');

    if (startDate && endDate && endDate < startDate) {
      dateError.textContent = 'End date cannot be earlier than start date';
      document.getElementById('end_date').classList.add('is-invalid');
      return false;
    }

    document.getElementById('end_date').classList.remove('is-invalid');
    return true;
  }

  // Check if we should show the date modal on page load
  document.addEventListener('DOMContentLoaded', function() {
    // Set up initial min date for end date
    updateEndDateMin();

    if (document.getElementById('taskFilter').value === 'custom') {
      // Only show if we're not already seeing results (i.e., no date params yet)
      if (!new URLSearchParams(window.location.search).has('start_date')) {
        var customDateModal = new bootstrap.Modal(document.getElementById('customDateModal'));
        customDateModal.show();
      }
    }
  });
</script>
