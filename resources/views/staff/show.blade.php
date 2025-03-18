@extends("layouts.main") {{-- or the name of your base layout --}}

@section("content")
  <div class="content-wrapper">
    <div class="card">
      <div class="card-body">
        <!-- Edit & Delete Buttons -->
        <div class="d-flex justify-content-between align-items-center mt-4">
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
            <p><strong>Name:</strong> {{ $staff->firstName }} {{ $staff->lastName }}</p>

            <p><strong>Mobile Phone:</strong>{{ $staff->contactNo }}</p>
            <p><strong>Email Address:</strong>{{ $staff->email }}</p>
            <p><strong>Address:</strong> {{ $staff->address }}</p>
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
            <p>{{ $staff->project_type }}</p>
            @if ($staff->role != 2 && isset($staff->projectManager))
              <p><strong>Manager: </strong> {{ $staff->projectManager->firstName }}
                {{ $staff->projectManager->lastName }}</p>
            @endif
            @if ($isStreetlightProject)
              <div>
                @if ($staff->role == 1)
                  <a href="{{ route("surveyed.poles", ["site_engineer" => $staff->id, "role" => 1]) }}"
                    class="text-primary text-decoration-none">Poles Surveyed:
                    {{ $surveyedPolesCount }}</a> <br />
                  <a href="{{ route("installed.poles", ["site_engineer" => $staff->id, "role" => 1]) }}"
                    class="text-success text-decoration-none">Installed Poles:
                    {{ $installedPolesCount ?? 0 }}</a>
                @elseif($staff->role == 3)
                  <a href="{{ route("surveyed.poles", ["vendor" => $staff->id, "role" => 1]) }}"
                    class="text-primary text-decoration-none">Poles Surveyed:
                    {{ $surveyedPolesCount }}</a> <br />
                  <a href="{{ route("installed.poles", ["vendor" => $staff->id, "role" => 1]) }}"
                    class="text-success text-decoration-none">Installed Poles:
                    {{ $installedPolesCount ?? 0 }}</a>
                @else
                  <a href="{{ route("surveyed.poles", ["project_manager" => $staff->id, "role" => 1]) }}"
                    class="text-primary text-decoration-none">Poles Surveyed:
                    {{ $surveyedPolesCount }}</a> <br />
                  <a href="{{ route("installed.poles", ["project_manager" => $staff->id, "role" => 1]) }}"
                    class="text-success text-decoration-none">Installed Poles:
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
        <div class="row">
          <div class="col-12">
            @if ($project->project_type == 1)
              {{-- For Streetlight Projects --}}
              @include("staff.assignedTasks")
            @else
              @include("staff.assignedRooftops")
            @endif
          </div>
        </div>
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

    .image-container {
      position: relative;
      margin-left: 250px;
      transform: translateY(-20px);
    }

    .row {
      display: flex;
      flex-wrap: wrap;
      gap: 16px;
      margin-top: 20px;
    }

    .col {
      flex: 1;
      min-width: 200px;
    }

    .list-header {
      padding: 10px;
      color: white;
      border-radius: 4px 4px 0 0;
      text-align: center;
    }

    .list-container {
      background-color: white;
      padding: 10px;
      border: 1px solid #dee2e6;
      text-align: center;
    }

    .header-green {
      background-color: #28a745;
    }

    .header-red {
      background-color: #dc3545;
    }

    .header-blue {
      background-color: #2d1cc0;
      color: rgb(255, 255, 255);
    }

    .header-darkGreen {
      background-color: #0a3314;
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

    .nested-list {
      margin-left: 20px;
      display: none;
    }

    .status-badge {
      background-color: #e9ecef;
      padding: 2px 8px;
      border-radius: 12px;
      font-size: 0.8rem;
    }

    .position-text {
      color: #6c757d;
      font-size: 0.9rem;
    }

    .ward-list {
      display: flex;
      flex-wrap: wrap;
      gap: 5px;
      margin-top: 5px;
    }

    .ward-tag {
      background-color: #007bff;
      color: white;
      padding: 2px 4px;
      border-radius: 4px;
      font-size: 0.65rem;
      display: inline-block;
    }
  </style>
@endpush
