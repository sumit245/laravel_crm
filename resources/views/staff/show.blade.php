@extends("layouts.main") {{-- or the name of your base layout --}}

@section("content")
  <div class="content-wrapper">
    <div class="card">
      <div class="card-body">
        <!-- Edit & Delete Buttons -->
        <div class="d-flex justify-content-between align-items-center mt-3">
          <h4 class="card-title">Personal Details</h4>
          <!-- Edit Button -->
          <a href="{{ route("staff.edit", $staff->id) }}" class="btn btn-icon btn-warning" data-toggle="tooltip"
            title="Edit Staff">
            <i class="mdi mdi-pencil"> Edit</i>
          </a>
        </div>
        <hr />
        <div class="d-flex justify-content-between">
          <!-- Personal Details -->
          <div class="">
            <p><strong>Name:</strong> {{ $staff->firstName ?? "N/A" }} {{ $staff->lastName ?? "N/A" }}</p>

            <p><strong>Mobile Phone:</strong>{{ $staff->contactNo ?? "N/A" }}</p>
            <p><strong>Email Address:</strong>{{ $staff->email ?? "N/A" }}</p>
            <p><strong>Address:</strong> {{ $staff->address ?? "N/A" }}</p>
            <p><strong>Role: </strong>
              {{ $staff->role == 1
                  ? "Site Engineer"
                  : ($staff->role == 2
                      ? "Project Manager"
                      : ($staff->role == 3
                          ? "Vendor"
                          : ($staff->role == 4
                              ? "Store Incharge"
                              : "Coordinator"))) }}
            </p>
            <p><strong>Project: </strong>{{ $project->project_name ?? "N/A" }}</p>
            @if ($staff->role != 2 && isset($staff->projectManager))
              <p><strong>Manager: </strong> {{ $staff->projectManager->firstName ?? "N/A" }}
                {{ $staff->projectManager->lastName ?? "N/A" }}</p>
            @endif
            @if ($isStreetlightProject)
              <div>
                @if ($staff->role == 1)
                  <a href="{{ route("surveyed.poles", ["site_engineer" => $staff->id, "role" => 1]) }}"
                    class="text-primary text-decoration-none">Poles Surveyed:
                    {{ $surveyedPolesCount ?? 0 }}</a> <br />
                  <a href="{{ route("installed.poles", ["site_engineer" => $staff->id, "role" => 1]) }}"
                    class="text-success text-decoration-none">Installed Lights:
                    {{ $installedPolesCount ?? 0 }}</a>
                @elseif($staff->role == 3)
                  <a href="{{ route("surveyed.poles", ["vendor" => $staff->id, "role" => 1]) }}"
                    class="text-primary text-decoration-none">Poles Surveyed:
                    {{ $surveyedPolesCount ?? 0 }}</a> <br />
                  <a href="{{ route("installed.poles", ["vendor" => $staff->id, "role" => 1]) }}"
                    class="text-success text-decoration-none">Installed lights:
                    {{ $installedPolesCount ?? 0 }}</a>
                @else
                  <a href="{{ route("surveyed.poles", ["project_manager" => $staff->id, "role" => 1]) }}"
                    class="text-primary text-decoration-none">Poles Surveyed:
                    {{ $surveyedPolesCount ?? 0 }}</a> <br />
                  <a href="{{ route("installed.poles", ["project_manager" => $staff->id, "role" => 1]) }}"
                    class="text-success text-decoration-none">Installed Lights:
                    {{ $installedPolesCount ?? 0 }}</a>
                @endif
              </div>
            @endif
          </div>
          <!-- Contact Details -->
          <div class="">
            <img src={{ $staff->image }} alt="user-avatar" class="custom-image">
          </div>
        </div>
        <hr />
        <!-- Tablist -->
        <h3>Performance Today</h3>
        @if ($project->project_type == 1)
          {{-- Implement date filters here --}}
          @include("staff.streetlight-performance")
        @endif

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
      margin-left: 0px;
      transform: translateY(-10px);
    }

    .user-card {
      border: 1px solid #dee2e6;
      margin-bottom: 8px;
      padding: 10px;
      cursor: pointer;
      transition: all 0.3s;
    }

    .user-card:hover {
      background-color: #f8f9fa;
    }

    .user-avatar {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      margin-right: 10px;
    }
  </style>
@endpush
