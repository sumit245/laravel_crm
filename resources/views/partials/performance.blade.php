<div class="bg-light mt-4 p-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="fw-bold">Performance Overview</h3>
    {{-- <form method="GET" action="{{ route("dashboard") }}"> --}}
    <select class="form-select w-auto" name="date_filter" id="taskFilter" onchange="filterTasks()">
      <option value="today" {{ request("date_filter") == "today" ? "selected" : "" }}>Today</option>
      <option value="this_week" {{ request("date_filter") == "this_week" ? "selected" : "" }}>This Week</option>
      <option value="this_month" {{ request("date_filter") == "this_month" ? "selected" : "" }}>This Month</option>
      <option value="all_time" {{ request("date_filter") == "all_time" ? "selected" : "" }}>All Time</option>
    </select>
    {{-- </form> --}}
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
                  <th>Total Tasks</th>
                  <th>Completed Tasks</th>
                  @if ($isStreetLightProject)
                    <th>Surveyed Poles</th>
                  @else
                    <th>Submitted sites</th>
                  @endif
                  @if ($isStreetLightProject)
                    <th>Installed Poles</th>
                  @else
                    <th>Approved sites</th>
                  @endif
                  <th>Performance (%)</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($users as $index => $user)
                  <tr>
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
                    <td>{{ $user->completedTasks }}</td>
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
                    <td>
                      <div class="progress">
                        <div class="progress-bar" role="progressbar"
                          style="width: {{ round($user->performance, 2) }}%;"
                          aria-valuenow="{{ round($user->performance, 2) }}" aria-valuemin="0" aria-valuemax="100">
                          {{ round($user->performance, 2) }}%
                        </div>
                      </div>
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
    let url = new URL(window.location.href);

    url.searchParams.set('date_filter', selectedFilter); // Update URL with selected filter

    window.location.href = url.toString(); // Reload page with new filter
  }
</script>
