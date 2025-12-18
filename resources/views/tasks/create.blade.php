@extends('layouts.main')

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <h4 class="card-title">Create New Task</h4>
          <p class="text-muted">Project: <strong>{{ $project->project_name }}</strong></p>
          
          <form action="{{ route('tasks.store') }}" method="POST">
            @csrf
            <input type="hidden" name="project_id" value="{{ $project->id }}">
            
            @if($project->project_type == 1)
              <!-- Streetlight Project Form -->
              <div class="mb-3">
                <label for="sites" class="form-label">Select Panchayats (Sites)</label>
                <select id="sites" name="sites[]" class="form-select" multiple required>
                  @foreach($sites as $site)
                    <option value="{{ $site->id }}">{{ $site->panchayat ?? 'N/A' }}</option>
                  @endforeach
                </select>
                <small class="form-text text-muted">Hold Ctrl/Cmd to select multiple sites</small>
              </div>
              
              <div class="mb-3">
                <label for="engineer_id" class="form-label">Select Site Engineer</label>
                <select id="engineer_id" name="engineer_id" class="form-select">
                  <option value="">Select Engineer</option>
                  @foreach($engineers as $engineer)
                    <option value="{{ $engineer->id }}">{{ $engineer->firstName }} {{ $engineer->lastName }}</option>
                  @endforeach
                </select>
              </div>
              
              <div class="mb-3">
                <label for="vendor_id" class="form-label">Select Vendor</label>
                <select id="vendor_id" name="vendor_id" class="form-select">
                  <option value="">Select Vendor</option>
                  @foreach($vendors as $vendor)
                    <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                  @endforeach
                </select>
              </div>
            @else
              <!-- Rooftop Project Form -->
              <div class="mb-3">
                <label for="sites" class="form-label">Select Sites</label>
                <select id="sites" name="sites[]" class="form-select" multiple required>
                  @foreach($sites as $site)
                    <option value="{{ $site->id }}">{{ $site->site_name }}</option>
                  @endforeach
                </select>
                <small class="form-text text-muted">Hold Ctrl/Cmd to select multiple sites</small>
              </div>
              
              <div class="mb-3">
                <label for="activity" class="form-label">Activity</label>
                <select id="activity" name="activity" class="form-select" required>
                  <option value="Installation">Installation</option>
                  <option value="RMS">RMS</option>
                  <option value="Billing">Billing</option>
                  <option value="Add Team">Add Team</option>
                  <option value="Survey">Survey</option>
                </select>
              </div>
              
              <div class="mb-3">
                <label for="engineer_id" class="form-label">Select Site Engineer</label>
                <select id="engineer_id" name="engineer_id" class="form-select">
                  <option value="">Select Engineer</option>
                  @foreach($engineers as $engineer)
                    <option value="{{ $engineer->id }}">{{ $engineer->firstName }} {{ $engineer->lastName }}</option>
                  @endforeach
                </select>
              </div>
            @endif
            
            <div class="mb-3">
              <label for="start_date" class="form-label">Start Date</label>
              <input type="date" id="start_date" name="start_date" class="form-control" value="{{ date('Y-m-d') }}">
            </div>
            
            <div class="mb-3">
              <label for="end_date" class="form-label">End Date</label>
              <input type="date" id="end_date" name="end_date" class="form-control">
            </div>
            
            <div class="mb-3">
              <label for="description" class="form-label">Description</label>
              <textarea id="description" name="description" class="form-control" rows="3"></textarea>
            </div>
            
            <div class="d-flex justify-content-end">
              <a href="{{ route('tasks.index', ['project_id' => $project->id]) }}" class="btn btn-light me-2">Cancel</a>
              <button type="submit" class="btn btn-primary">Create Task</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
  $(document).ready(function() {
    // Initialize any plugins or form validation here
    $('[data-toggle="tooltip"]').tooltip();
  });
</script>
@endsection

