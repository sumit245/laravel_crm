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

  .action-buttons {
    display: flex;
    gap: 5px;
    flex-wrap: wrap;
  }

  .action-buttons .btn {
    flex: 1;
    min-width: 80px;
    font-size: 0.8rem;
    padding: 5px 8px;
  }

  .vendor-card, .engineer-card {
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
    background: #f8f9fa;
  }

  .detail-metric {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid #eee;
  }

  .detail-metric:last-child {
    border-bottom: none;
  }

  .metric-icon {
    width: 20px;
    height: 20px;
    margin-right: 8px;
  }
</style>

<div class="container my-4">
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
              <img src="{{ $user->image ?? '/placeholder.svg?height=50&width=50' }}" alt="Profile" class="profile-img me-3">
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

            <!-- Action buttons -->
            <div class="action-buttons mt-3">
             @if ($user->role === 'Project Manager')
               <button class="btn btn-sm btn-outline-success" onclick="showVendors({{ $user->id }}, '{{ $user->name }}', {{ json_encode($user->vendors ?? []) }})">
                 Vendors
               </button>
               <button class="btn btn-sm btn-outline-info" onclick="showSiteEngineers({{ $user->id }}, '{{ $user->name }}', {{ json_encode($user->siteEngineers ?? []) }})">
                 Engineers
               </button>
                 @endif
               <a href="{{ route('staff.show', $user->id) }}" class="btn btn-sm btn-primary">
                 Details
             </a>
            </div>
          </div>
        </div>
      @empty
        <p class="text-muted">No data for {{ $role }}</p>
      @endforelse
    </div>
  @endforeach
</div>

<!-- Vendors Modal -->
<div class="modal fade" id="vendorsModal" tabindex="-1" aria-labelledby="vendorsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="vendorsModalLabel">Vendors</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="vendorsContent">
          <!-- Vendors will be loaded here -->
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Site Engineers Modal -->
<div class="modal fade" id="siteEngineersModal" tabindex="-1" aria-labelledby="siteEngineersModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="siteEngineersModalLabel">Site Engineers</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="siteEngineersContent">
          <!-- Site Engineers will be loaded here -->
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Custom Date Range Modal (existing) -->
<div class="modal fade" id="customDateModal" tabindex="-1" aria-labelledby="customDateModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="customDateModalLabel">Select Custom Date Range</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="GET" action="{{ request()->url() }}" onsubmit="return validateDateRange()">
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              <label for="start_date" class="form-label">Start Date</label>
              <input type="date" class="form-control" id="start_date" name="start_date" 
                     value="{{ request('start_date') }}" onchange="updateEndDateMin()" required>
            </div>
            <div class="col-md-6">
              <label for="end_date" class="form-label">End Date</label>
              <input type="date" class="form-control" id="end_date" name="end_date" 
                     value="{{ request('end_date') }}" required>
            </div>
          </div>
          <div id="dateError" class="text-danger mt-2"></div>
          <input type="hidden" name="date_filter" value="custom">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Apply Filter</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  // Existing filter functions
  function filterTasks() {
    let selectedFilter = document.getElementById('taskFilter').value;

    if (selectedFilter === 'custom') {
      var customDateModal = new bootstrap.Modal(document.getElementById('customDateModal'));
      customDateModal.show();
    } else {
      let url = new URL(window.location.href);
      url.searchParams.set('date_filter', selectedFilter);
      url.searchParams.delete('start_date');
      url.searchParams.delete('end_date');
      window.location.href = url.toString();
    }
  }

  function updateEndDateMin() {
    const startDate = document.getElementById('start_date').value;
    const endDateInput = document.getElementById('end_date');

    if (startDate) {
      endDateInput.min = startDate;
      if (endDateInput.value && endDateInput.value < startDate) {
        endDateInput.value = startDate;
      }
    }
  }

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

  function showVendors(userId, userName, vendors) {
    document.getElementById('vendorsModalLabel').textContent = userName + ' - Vendors';
    
    let content = '';
    if (vendors && vendors.length > 0) {
      content = `<h6 class="mb-3"><i class="fas fa-users me-2"></i>Vendors (${vendors.length})</h6>`;
      vendors.forEach(vendor => {
        const statusClass = vendor.status === 'Active' ? 'bg-success' : 'bg-secondary';
        content += `
          <div class="vendor-card">
            <div class="d-flex justify-content-between align-items-start mb-3">
              <div>
                <h6 class="mb-1">${vendor.name}</h6>
                <span class="badge ${statusClass}">${vendor.status}</span>
              </div>
              <div class="text-end">
                <small class="text-muted">${vendor.projects} Projects</small>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <small><strong>Contact:</strong> ${vendor.contact}</small>
              </div>
              <div class="col-md-6">
                <small><strong>Email:</strong> ${vendor.email}</small>
              </div>
            </div>
          </div>
        `;
      });
    } else {
      content = '<p class="text-muted">No vendors assigned to this manager.</p>';
    }
    
    document.getElementById('vendorsContent').innerHTML = content;
    
    var modal = new bootstrap.Modal(document.getElementById('vendorsModal'));
    modal.show();
  }

  function showSiteEngineers(userId, userName, engineers) {
    document.getElementById('siteEngineersModalLabel').textContent = userName + ' - Site Engineers';
    
    let content = '';
    if (engineers && engineers.length > 0) {
      content = `<h6 class="mb-3"><i class="fas fa-map-pin me-2"></i>Site Engineers (${engineers.length})</h6>`;
      engineers.forEach(engineer => {
        content += `
          <div class="engineer-card">
            <div class="d-flex justify-content-between align-items-start mb-3">
              <div>
                <h6 class="mb-1">${engineer.name}</h6>
                <span class="badge bg-outline-primary">${engineer.experience}</span>
              </div>
              <div class="text-end">
                <small class="text-muted">${engineer.sites} Sites</small>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <small><strong>Contact:</strong> ${engineer.contact}</small>
              </div>
              <div class="col-md-6">
                <small><strong>Email:</strong> ${engineer.email}</small>
              </div>
            </div>
          </div>
        `;
      });
    } else {
      content = '<p class="text-muted">No site engineers assigned to this manager.</p>';
    }
    
    document.getElementById('siteEngineersContent').innerHTML = content;
    
    var modal = new bootstrap.Modal(document.getElementById('siteEngineersModal'));
    modal.show();
  }

  // Initialize on page load
  document.addEventListener('DOMContentLoaded', function() {
    updateEndDateMin();

    if (document.getElementById('taskFilter').value === 'custom') {
      if (!new URLSearchParams(window.location.search).has('start_date')) {
        var customDateModal = new bootstrap.Modal(document.getElementById('customDateModal'));
        customDateModal.show();
      }
    }
  });
</script>