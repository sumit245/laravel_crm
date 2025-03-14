@extends("layouts.main") {{-- or the name of your base layout --}}

@section("content")
  <div class="content-wrapper">

    <div class="row">
      <div class="col-md-12 grid-margin">
        <div class="card">
          <div class="card-body">
            <!-- Edit & Delete Buttons -->
            <div class="d-flex justify-content-between align-items-center mt-4">
              <h4 class="card-title">Personal Details</h4>
              <!-- Edit Button -->
              <a href="{{ route("uservendors.edit", $vendor->id) }}" class="btn btn-icon btn-warning" data-toggle="tooltip"
                title="Edit Vendor">
                <i class="mdi mdi-pencil"> Edit</i>
              </a>
            </div>
            <hr />
            <div class="d-flex justify-content-between">
              <!-- Personal Details -->
              <div class="">
                <p><strong>Name:</strong> {{ $vendor->name }}</p>
                <p><strong>Mobile Phone:</strong>{{ $vendor->contactNo }}</p>
                <p><strong>Email Address:</strong>{{ $vendor->email }}</p>
                <p><strong>Address:</strong> {{ $vendor->address }}</p>
                <p><strong>Role: </strong>
                  Vendor
                </p>
                @if (isset($vendor->siteEngineer))
                  <p><strong>Site Engineer: </strong> {{ $vendor->siteEngineer->firstName }}
                    {{ $vendor->siteEngineer->lastName }}</p>
                @endif
                @if (isset($vendor->projectManager))
                  <p><strong>Manager: </strong> {{ $vendor->projectManager->firstName }}
                    {{ $vendor->projectManager->lastName }}</p>
                @endif
              </div>
              <!-- Contact Details -->
              <div class="">
                <img src={{ $vendor->image }} alt="user-avatar" class="custom-image">
              </div>
            </div>
            <hr />
            <div class="row">
              <div class="col">
                <div class="list-header header-green">Assigned sites
                  <span class="btn-sm btn-rounded btn-light">{{ $assignedTasksCount }}</span>
                </div>
                <ul class="list-container">
                  @if ($assignedTasks->count() > 0)
                    @foreach ($assignedTasks as $task)
                      <li class="list-group-item">
                        @if ($task->site)
                          @if ($project->project_type == 1)
                            {{-- For Streetlight Projects --}}
                            <small></small> <strong>Block: <span>{{ $task->site->block ?? "N/A" }}</span></strong>
                            <strong>Panchayat: <span>{{ $task->site->panchayat ?? "N/A" }}</span></strong>
                            @php
                              // Explode ward string and remove empty values
                              $wards = array_filter(array_map("trim", explode(",", $task->site->ward)));
                            @endphp
                            @if (!empty($wards))
                              <div class="ward-list">
                                @foreach ($wards as $ward)
                                  <span class="ward-tag">Ward {{ $ward }}</span>
                                @endforeach
                              </div>
                            @endif
                          @else
                            {{-- For Rooftop Projects --}}
                            <h6>{{ $task->site->site_name ?? "N/A" }}</h6>
                            <p>{{ $task->site->location }}, {{ $task->site->districtRelation->name ?? "N/A" }}</p>
                          @endif
                        @endif
                        <div class="d-flex w-100 justify-content-between">
                          <strong>{{ $task->start_date }}</strong>
                          <strong>{{ $task->end_date }}</strong>
                        </div>
                      </li>
                    @endforeach
                  @else
                    <p>No rejected tasks</p>
                  @endif
                </ul>
              </div>

              <div class="col">
                <div class="list-header header-darkGreen">Completed Sites
                  <span class="btn-sm btn-rounded btn-light">{{ $completedTasksCount }}</span>
                </div>
                <ul class="list-container">
                  @if ($completedTasks->count() > 0)
                    @foreach ($completedTasks as $task)
                      <li class="list-group-item">
                        <a href="{{ route("tasks.show", $task->id) }}" class="text-decoration-none text-dark">
                          @if ($task->site)
                            @if ($project->project_type == 1)
                              {{-- For Streetlight Projects --}}
                              <small></small> <strong>Block: <span>{{ $task->site->block ?? "N/A" }}</span></strong>
                              <strong>Panchayat: <span>{{ $task->site->panchayat ?? "N/A" }}</span></strong>
                              @php
                                // Explode ward string and remove empty values
                                $wards = array_filter(array_map("trim", explode(",", $task->site->ward)));
                              @endphp
                              @if (!empty($wards))
                                <div class="ward-list">
                                  @foreach ($wards as $ward)
                                    <span class="ward-tag">Ward {{ $ward }}</span>
                                  @endforeach
                                </div>
                              @endif
                            @else
                              {{-- For Rooftop Projects --}}
                              <h6>{{ $task->site->site_name ?? "N/A" }}</h6>
                              <p>{{ $task->site->location }}, {{ $task->site->districtRelation->name ?? "N/A" }}</p>
                            @endif
                          @endif
                          <div class="d-flex w-100 justify-content-between">
                            <strong>{{ $task->start_date }}</strong>
                            <strong>{{ $task->end_date }}</strong>
                          </div>
                        </a>
                      </li>
                    @endforeach
                  @else
                    <p>No completed tasks</p>
                  @endif
                </ul>
              </div>

              <div class="col">
                <div class="list-header header-blue">Pending Sites
                  <span class="btn-sm btn-rounded btn-light">{{ $pendingTasksCount }}</span>
                </div>
                <ul class="list-container">
                  @if ($pendingTasksCount > 0)
                    @foreach ($pendingTasks as $task)
                      <li class="list-group-item">
                        @if ($task->site)
                          @if ($project->project_type == 1)
                            {{-- For Streetlight Projects --}}
                            <small></small> <strong>Block: <span>{{ $task->site->block ?? "N/A" }}</span></strong>
                            <strong>Panchayat: <span>{{ $task->site->panchayat ?? "N/A" }}</span></strong>
                            @php
                              // Explode ward string and remove empty values
                              $wards = array_filter(array_map("trim", explode(",", $task->site->ward)));
                            @endphp
                            @if (!empty($wards))
                              <div class="ward-list">
                                @foreach ($wards as $ward)
                                  <span class="ward-tag">Ward {{ $ward }}</span>
                                @endforeach
                              </div>
                            @endif
                          @else
                            {{-- For Rooftop Projects --}}
                            <h6>{{ $task->site->site_name ?? "N/A" }}</h6>
                            <p>{{ $task->site->location }}, {{ $task->site->districtRelation->name ?? "N/A" }}</p>
                          @endif
                        @endif
                        <div class="d-flex w-100 justify-content-between">
                          <strong>{{ $task->start_date }}</strong>
                          <strong>{{ $task->end_date }}</strong>
                        </div>
                      </li>
                    @endforeach
                  @else
                    <p>No rejected tasks</p>
                  @endif
                </ul>
              </div>

              <div class="col">
                <div class="list-header header-red">Rejected Sites
                  <span class="btn-sm btn-rounded btn-light">{{ $rejectedTasksCount }}</span>
                </div>
                <ul class="list-container">
                  @if ($rejectedTasks->count() > 0)
                    @foreach ($rejectedTasks as $task)
                      <li class="list-group-item">
                        @if ($task->site)
                          @if ($project->project_type == 1)
                            {{-- For Streetlight Projects --}}
                            <small></small> <strong>Block: <span>{{ $task->site->block ?? "N/A" }}</span></strong>
                            <strong>Panchayat: <span>{{ $task->site->panchayat ?? "N/A" }}</span></strong>
                            @php
                              // Explode ward string and remove empty values
                              $wards = array_filter(array_map("trim", explode(",", $task->site->ward)));
                            @endphp
                            @if (!empty($wards))
                              <div class="ward-list">
                                @foreach ($wards as $ward)
                                  <span class="ward-tag">Ward {{ $ward }}</span>
                                @endforeach
                              </div>
                            @endif
                          @else
                            {{-- For Rooftop Projects --}}
                            <h6>{{ $task->site->site_name ?? "N/A" }}</h6>
                            <p>{{ $task->site->location }}, {{ $task->site->districtRelation->name ?? "N/A" }}</p>
                          @endif
                        @endif
                        <div class="d-flex w-100 justify-content-between">
                          <strong>{{ $task->start_date }}</strong>
                          <strong>{{ $task->end_date }}</strong>
                        </div>
                      </li>
                    @endforeach
                  @else
                    <p>No rejected tasks</p>
                  @endif
                </ul>
              </div>
            </div>
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
