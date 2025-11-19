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
    <div class="d-flex gap-2">
      <select class="form-select w-auto" name="date_filter" id="taskFilter" onchange="filterTasks()">
        <option value="today" {{ request("date_filter") == "today" ? "selected" : "" }}>Today</option>
        <option value="this_week" {{ request("date_filter") == "this_week" ? "selected" : "" }}>This Week</option>
        <option value="this_month" {{ request("date_filter") == "this_month" ? "selected" : "" }}>This Month</option>
        <option value="all_time" {{ request("date_filter") == "all_time" ? "selected" : "" }}>All Time</option>
        <option value="custom" {{ request("date_filter") == "custom" ? "selected" : "" }}>Custom Range</option>
      </select>
      <a href="{{ route('performance.index', ['project_id' => $project->id ?? request('project_id')]) }}" class="btn btn-primary">
        <i class="bi bi-eye"></i> View Detailed Performance
      </a>
    </div>
  </div>

  @if(auth()->user()->role == 0)
    {{-- Admin View: Project Managers --}}
    @if(is_array($rolePerformances) && count($rolePerformances) > 0)
      <h5 class="mb-3">📋 Project Managers</h5>
      <div class="row">
        @foreach(array_slice($rolePerformances, 0, 3) as $managerData)
          <div class="col-md-4 mb-3">
            <div class="performance-card">
              <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="d-flex align-items-center gap-2">
                  <img src="{{ $managerData['user']->image ?? asset('images/default-avatar.png') }}" 
                       alt="{{ $managerData['user']->name }}" 
                       class="rounded-circle profile-img">
                  <div>
                    <h6 class="mb-0">{{ $managerData['user']->firstName }} {{ $managerData['user']->lastName }}</h6>
                    <small class="text-muted">Project Manager</small>
                  </div>
                </div>
                <span class="badge {{ $managerData['metrics']['performance_percentage'] >= 80 ? 'badge-high' : ($managerData['metrics']['performance_percentage'] >= 50 ? 'badge-medium' : 'badge-low') }}">
                  {{ $managerData['metrics']['performance_percentage'] }}%
                </span>
              </div>
              <div class="progress mb-2" style="height: 6px;">
                <div class="progress-bar {{ $managerData['metrics']['performance_percentage'] >= 80 ? 'bg-success' : ($managerData['metrics']['performance_percentage'] >= 50 ? 'bg-warning' : 'bg-danger') }}" 
                     style="width: {{ $managerData['metrics']['performance_percentage'] }}%"></div>
              </div>
              <div class="metric">🎯 Tasks: <strong>{{ $managerData['metrics']['total_tasks'] ?? 0 }}</strong></div>
              @if($isStreetLightProject ?? false)
                <div class="metric">💡 Installed: <strong>{{ $managerData['metrics']['installed_poles'] ?? 0 }}</strong></div>
              @else
                <div class="metric">✅ Completed: <strong>{{ $managerData['metrics']['completed_tasks'] ?? 0 }}</strong></div>
              @endif
            </div>
          </div>
        @endforeach
      </div>
    @else
      <div class="alert alert-info">No performance data available for the selected period.</div>
    @endif

  @elseif(auth()->user()->role == 2)
    {{-- Project Manager View: Engineers and Vendors --}}
    @if(isset($rolePerformances['engineers']) && count($rolePerformances['engineers']) > 0)
      <h5 class="mb-3">👷 Site Engineers</h5>
      <div class="row">
        @foreach(array_slice($rolePerformances['engineers'], 0, 3) as $engineerData)
          <div class="col-md-4 mb-3">
            <div class="performance-card">
              <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="d-flex align-items-center gap-2">
                  <img src="{{ $engineerData['user']->image ?? asset('images/default-avatar.png') }}" 
                       alt="{{ $engineerData['user']->name }}" 
                       class="rounded-circle profile-img">
                  <div>
                    <h6 class="mb-0">{{ $engineerData['user']->firstName }} {{ $engineerData['user']->lastName }}</h6>
                    <small class="text-muted">Site Engineer</small>
                  </div>
                </div>
                <span class="badge {{ $engineerData['metrics']['performance_percentage'] >= 80 ? 'badge-high' : ($engineerData['metrics']['performance_percentage'] >= 50 ? 'badge-medium' : 'badge-low') }}">
                  {{ $engineerData['metrics']['performance_percentage'] }}%
                </span>
              </div>
              <div class="progress mb-2" style="height: 6px;">
                <div class="progress-bar {{ $engineerData['metrics']['performance_percentage'] >= 80 ? 'bg-success' : ($engineerData['metrics']['performance_percentage'] >= 50 ? 'bg-warning' : 'bg-danger') }}" 
                     style="width: {{ $engineerData['metrics']['performance_percentage'] }}%"></div>
              </div>
              @if($isStreetLightProject ?? false)
                <div class="metric">💡 Installed: <strong>{{ $engineerData['metrics']['installed_poles'] ?? 0 }}</strong></div>
              @else
                <div class="metric">✅ Completed: <strong>{{ $engineerData['metrics']['completed_tasks'] ?? 0 }}</strong></div>
              @endif
            </div>
          </div>
        @endforeach
      </div>
    @endif

  @elseif(auth()->user()->role == 1)
    {{-- Site Engineer View: Vendors --}}
    @if(isset($rolePerformances['vendors']) && count($rolePerformances['vendors']) > 0)
      <h5 class="mb-3">🔧 Vendors</h5>
      <div class="row">
        @foreach(array_slice($rolePerformances['vendors'], 0, 3) as $vendorData)
          <div class="col-md-4 mb-3">
            <div class="performance-card">
              <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="d-flex align-items-center gap-2">
                  <img src="{{ $vendorData['user']->image ?? asset('images/default-avatar.png') }}" 
                       alt="{{ $vendorData['user']->name }}" 
                       class="rounded-circle profile-img">
                  <div>
                    <h6 class="mb-0">{{ $vendorData['user']->firstName }} {{ $vendorData['user']->lastName }}</h6>
                    <small class="text-muted">Vendor</small>
                  </div>
                </div>
                <span class="badge {{ $vendorData['metrics']['performance_percentage'] >= 80 ? 'badge-high' : ($vendorData['metrics']['performance_percentage'] >= 50 ? 'badge-medium' : 'badge-low') }}">
                  {{ $vendorData['metrics']['performance_percentage'] }}%
                </span>
              </div>
              <div class="progress mb-2" style="height: 6px;">
                <div class="progress-bar {{ $vendorData['metrics']['performance_percentage'] >= 80 ? 'bg-success' : ($vendorData['metrics']['performance_percentage'] >= 50 ? 'bg-warning' : 'bg-danger') }}" 
                     style="width: {{ $vendorData['metrics']['performance_percentage'] }}%"></div>
              </div>
              @if($isStreetLightProject ?? false)
                <div class="metric">💡 Installed: <strong>{{ $vendorData['metrics']['installed_poles'] ?? 0 }}</strong></div>
              @else
                <div class="metric">✅ Completed: <strong>{{ $vendorData['metrics']['completed_tasks'] ?? 0 }}</strong></div>
              @endif
            </div>
          </div>
        @endforeach
      </div>
    @endif
  @endif
</div>


<!-- Custom Date Range Modal -->
<div class="modal fade" id="customDateModal" tabindex="-1" aria-labelledby="customDateModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="customDateModalLabel">Select Custom Date Range</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="GET" action="{{ request()->url() }}">
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              <label for="start_date" class="form-label">Start Date</label>
              <input type="date" class="form-control" id="start_date" name="start_date" 
                     value="{{ request('start_date') }}" required>
            </div>
            <div class="col-md-6">
              <label for="end_date" class="form-label">End Date</label>
              <input type="date" class="form-control" id="end_date" name="end_date" 
                     value="{{ request('end_date') }}" required>
            </div>
          </div>
          <input type="hidden" name="date_filter" value="custom">
          @if(request('project_id'))
            <input type="hidden" name="project_id" value="{{ request('project_id') }}">
          @endif
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
</script>