@extends("layouts.main")

@section("content")
  <div class="content-wrapper">
    <div class="card">
      <div class="card-body">
        <!-- Edit Button -->
        <div class="d-flex justify-content-between align-items-center mt-3">
          <h4 class="card-title">Personal Details</h4>
          <a href="{{ route('staff.edit', $staff->id) }}" class="btn btn-icon btn-warning" data-toggle="tooltip" title="Edit Staff">
            <i class="mdi mdi-pencil"> Edit</i>
          </a>
        </div>
        <hr />

        <!-- Personal Details -->
        <div class="d-flex justify-content-between">
          <div>
            <p><strong>Name:</strong> {{ $staff->firstName ?? 'N/A' }} {{ $staff->lastName ?? 'N/A' }}</p>
            <p><strong>Mobile Phone:</strong> {{ $staff->contactNo ?? 'N/A' }}</p>
            <p><strong>Email Address:</strong> {{ $staff->email ?? 'N/A' }}</p>
            <p><strong>Address:</strong> {{ $staff->address ?? 'N/A' }}</p>
            <p><strong>Role:</strong>
              {{ $staff->role == 1 ? 'Site Engineer' : ($staff->role == 2 ? 'Project Manager' : ($staff->role == 3 ? 'Vendor' : ($staff->role == 4 ? 'Store Incharge' : 'Coordinator'))) }}
            </p>
            <p><strong>Project:</strong> {{ $project->project_name ?? 'N/A' }}</p>

            @if ($staff->role != 2 && isset($staff->projectManager))
              <p><strong>Manager:</strong> {{ $staff->projectManager->firstName ?? 'N/A' }} {{ $staff->projectManager->lastName ?? 'N/A' }}</p>
            @endif

            @if ($isStreetlightProject)
              <div>
                @if ($staff->role == 1)
                  <a href="{{ route('surveyed.poles', ['site_engineer' => $staff->id, 'role' => 1]) }}" class="text-primary text-decoration-none">Poles Surveyed: {{ $surveyedPolesCount ?? 0 }}</a><br />
                  <a href="{{ route('installed.poles', ['site_engineer' => $staff->id, 'role' => 1]) }}" class="text-success text-decoration-none">Installed Lights: {{ $installedPolesCount ?? 0 }}</a>
                @elseif ($staff->role == 3)
                  <a href="{{ route('surveyed.poles', ['vendor' => $staff->id, 'role' => 1]) }}" class="text-primary text-decoration-none">Poles Surveyed: {{ $surveyedPolesCount ?? 0 }}</a><br />
                  <a href="{{ route('installed.poles', ['vendor' => $staff->id, 'role' => 1]) }}" class="text-success text-decoration-none">Installed Lights: {{ $installedPolesCount ?? 0 }}</a>
                @else
                  <a href="{{ route('surveyed.poles', ['project_manager' => $staff->id, 'role' => 1]) }}" class="text-primary text-decoration-none">Poles Surveyed: {{ $surveyedPolesCount ?? 0 }}</a><br />
                  <a href="{{ route('installed.poles', ['project_manager' => $staff->id, 'role' => 1]) }}" class="text-success text-decoration-none">Installed Lights: {{ $installedPolesCount ?? 0 }}</a>
                @endif
              </div>
            @endif
          </div>

          <!-- Staff Image -->
          <div>
            <img src="{{ $staff->image }}" alt="user-avatar" class="custom-image">
          </div>
        </div>

        <hr />

        <!-- Performance Today Section -->
        <h3>Performance Today</h3>
        @if ($project->project_type == 1)
          @include('staff.streetlight-performance')
        @endif

        <!-- Vendor and Site Engineer Section -->
      
      </div>
    </div>
  </div>
@endsection

@push("styles")
  <style>
    .custom-image {
      width: 150px;
      height: 150px;
      object-fit: cover;
      border-radius: 10px;
      margin-top: 50px;
      transform: translateY(-10px);
    }

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
@endpush

@push("scripts")
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@endpush
