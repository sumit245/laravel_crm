<style>
  .performance-card {
    border: 1px solid #dee2e6;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
    transition: box-shadow 0.3s ease;
    background: #fff;
  }

  .performance-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  }

  .profile-img {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
  }

  .metric {
    font-size: 0.9rem;
    margin-bottom: 5px;
  }

  .badge-performance {
    padding: 4px 8px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 500;
  }

  .badge-high {
    background-color: #d4edda;
    color: #155724;
  }

  .badge-medium {
    background-color: #fff3cd;
    color: #856404;
  }

  .badge-low {
    background-color: #f8d7da;
    color: #721c24;
  }
</style>

<div class="container my-4">
  <!-- <h2 class="mb-4 fw-bold">📊 Performance Overview</h2> -->
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

  @foreach ($rolePerformances as $role => $users)
    <h4 class="text-primary mb-3">{{ $role }}</h4>
    <div class="row">
      @forelse ($users as $index => $user)
        <div class="col-md-4">
          <div class="performance-card">
            <div class="d-flex align-items-center mb-3">
              <img src="{{ $user->image }}" alt="Profile" class="profile-img me-3">
              <div>
                <h6 class="mb-0">{{ $user->name }}</h6>
                <small class="text-muted">
                  @if ($index == 0) 🥇 @elseif ($index == 1) 🥈 @elseif ($index == 2) 🥉 @endif
                  {{ $user->role == 'Vendor' ? $user->vendor_name : '' }}
                </small>
              </div>
            </div>

            <!-- Progress bar -->
            <div class="mt-3 mb-4">
              <div class="progress" style="height: 6px;">
                <div class="progress-bar {{ $user->performance >= 80 ? 'bg-success' : ($user->performance >= 50 ? 'bg-warning' : 'bg-danger') }}"
                  role="progressbar"
                  style="width: {{ round($user->performance) }}%;"
                  aria-valuenow="{{ round($user->performance) }}" aria-valuemin="0" aria-valuemax="100">
                </div>
              </div>
              <div class="ms-auto text-end">
                <span class="badge badge-performance
                  {{ $user->performance >= 80 ? 'badge-high' : ($user->performance >= 50 ? 'badge-medium' : 'badge-low') }}">
                  {{ round($user->performance) }}%
                </span>
              </div>
            </div>

            <div class="metric">🎯 Targets: <strong>{{ $user->totalTasks }}</strong></div>
            @if (!$isStreetLightProject)
              <div class="metric">✅ Completed: <strong>{{ $user->completedTasks }}</strong></div>
            @endif
            <div class="metric">🔍 {{ $isStreetLightProject ? 'Surveyed Poles' : 'Submitted Sites' }}:
              <strong>{{ $isStreetLightProject ? $user->surveyedPoles ?? 0 : $user->submittedSites ?? 0 }}</strong></div>
            <div class="metric">💡 {{ $isStreetLightProject ? 'Installed Lights' : 'Approved Sites' }}:
              <strong>{{ $isStreetLightProject ? $user->installedPoles ?? 0 : $user->approvedSites ?? 0 }}</strong></div>
            <div class="metric">🧾 Billed: <strong>0</strong></div>

            <!-- See Details button -->
            <div class="text-end mt-3">
              <a href="{{ route('staff.show', $user->id) }}" class="btn btn-sm btn-primary">See Details</a>
            </div>
          </div>
        </div>
      @empty
        <p class="text-muted">No data for {{ $role }}</p>
      @endforelse
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