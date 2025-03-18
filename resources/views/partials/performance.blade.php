<div class="bg-light mt-4 p-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="fw-bold">Performance Overview</h3>
    {{-- <form method="GET" action="{{ route("dashboard") }}"> --}}
    <select class="form-select w-auto" name="date_filter" id="taskFilter" onchange="filterTasks()">
      <option value="today" {{ request("date_filter") == "today" ? "selected" : "" }}>Today</option>
      <option value="this_week" {{ request("date_filter") == "this_week" ? "selected" : "" }}>This Week</option>
      <option value="this_month" {{ request("date_filter") == "this_month" ? "selected" : "" }}>This Month</option>
      <option value="all_time" {{ request("date_filter") == "all_time" ? "selected" : "" }}>All Time</option>
      <option value="custom" {{ request("date_filter") == "custom_time" ? "selected" : "" }}>Click here!</option>
    </select>
    <!--  -->
    
    <div class="avgrund-popin custom" style="width: 380px; height: 350px; margin-left: -200px; margin-top: -185px;">
      <!-- Custom Date Modal -->
    <div id="custom-date-modal" class="modal">
        <div class="modal-content">
            <!-- <span class="close">&times;</span> -->
            <h2>Select Date Range</h2>
            <label>Start Date:</label>
            <input type="date" id="start-date">
            <label>End Date:</label>
            <input type="date" id="end-date">
            <button id="apply-dates">Apply</button>
        </div>
    </div>
    </div>



    <!--  -->
    </div></div>
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
                  @if (!$isStreetLightProject)
                    <th>Completed Tasks</th>
                  @endif
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
                    <td>
                      <div class="progress">
                        <div class="progress-bar" role="progressbar"
                          style="width: {{ round($user->performance, 0) }}%;"
                          aria-valuenow="{{ round($user->performance, 0) }}" aria-valuemin="0" aria-valuemax="100">
                          {{ round($user->performance, 0) }}%
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
  
  // Custom date logic
  document.addEventListener("DOMContentLoaded", function () {
    const timeSelect = document.getElementById("time-select");
    const modal = document.getElementById("custom-date-modal");
    const closeModal = document.querySelector(".close");
    const applyBtn = document.getElementById("apply-dates");

    // Show modal when "Custom Time" is selected
    timeSelect.addEventListener("change", function () {
        if (timeSelect.value === "custom") {
            modal.style.display = "flex"; // Show modal
        }
    });


    // Apply button logic
    applyBtn.addEventListener("click", function () {
        const startDate = document.getElementById("start-date").value;
        const endDate = document.getElementById("end-date").value;

        if (startDate && endDate) {
            alert(`Selected Date Range: ${startDate} to ${endDate}`);
            modal.style.display = "none"; // Close modal
        } else {
            alert("Please select both start and end dates.");
        }
    });

    // Close modal when clicking outside it
    window.addEventListener("click", function (event) {
        if (event.target === modal) {
            modal.style.display = "none";
        }
    });
});




</script>
