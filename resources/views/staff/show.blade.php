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
        <h3 class="mt-5">Vendor and Site Engineer</h3>

        <div class="tab-content mt-1" id="vendorEngineerTabContent">
          <ul class="nav nav-tabs fixed-navbar-project" id="vendorEngineerTabs" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active" id="vendor-tab" data-bs-toggle="tab" data-bs-target="#vendor-content"
                type="button" role="tab" aria-controls="vendor-content" aria-selected="true">
                Vendor
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="site-engineer-tab" data-bs-toggle="tab"
                data-bs-target="#site-engineer-content" type="button" role="tab"
                aria-controls="site-engineer-content" aria-selected="false">
                Site Engineer
              </button>
            </li>
          </ul>

          <!-- Vendor Tab -->
          @foreach ($vendors as $vendor)
          <div class="tab-pane fade show active" id="vendor-content" role="tabpanel" aria-labelledby="vendor-tab">
            <div class="row mt-3">
              <div class="col-md-4 mb-3">
                <div class="performance-card">
                  <div class="d-flex align-items-center mb-3">
                    <img src="https://via.placeholder.com/50" alt="Profile" class="profile-img me-3">
                    <div>
                      <h6 class="mb-0">{{ $vendor->firstName }} {{ $vendor->lastName }}</h6>
                      <small class="text-muted">{{ $vendor->name }}</small>
                    </div>
                  </div>
                  <div class="mt-3 mb-4">
                    <div class="progress" style="height: 6px;">
                      <div class="progress-bar bg-success" style="width: 85%;"></div>
                    </div>
                    <div class="text-end mt-1">
                      <span class="badge badge-performance badge-high">85%</span>
                    </div>
                  </div>
                  <div class="metric">🎯 Targets: <strong></strong></div>
                  <div class="metric">✅ Completed: <strong></strong></div>
                  <div class="metric">🔍 Submitted Sites: <strong></strong></div>
                  <div class="metric">💡 Approved Sites: <strong></strong></div>
                  <div class="metric">🧾 Billed: <strong></strong></div>
                  <div class="text-end mt-3">
                    <a href="#" class="btn btn-sm btn-primary">See Details</a>
                  </div>
                </div>
              </div>
              <!-- <div class="col-12"><p class="text-muted">No vendors found.</p></div> -->
            </div>
          </div>
          @endforeach
          <!-- Site Engineer Tab -->
          @foreach ($siteEngineers as $engineer)
          <div class="tab-pane fade" id="site-engineer-content" role="tabpanel" aria-labelledby="site-engineer-tab">
            <div class="row mt-3">
              <div class="col-md-4 mb-3">
                <div class="performance-card">
                  <div class="d-flex align-items-center mb-3">
                    <img src="https://via.placeholder.com/50" alt="Profile" class="profile-img me-3">
                    <div>
                      <h6 class="mb-0"></h6>
                      <small class="text-muted">🥇 Site Engineer Team</small>
                    </div>
                  </div>
                  <div class="mt-3 mb-4">
                    <div class="progress" style="height: 6px;">
                      <div class="progress-bar bg-warning" style="width: 65%;"></div>
                    </div>
                    <div class="text-end mt-1">
                      <span class="badge badge-performance badge-medium">65%</span>
                    </div>
                  </div>
                  <div class="metric">🎯 Targets: <strong>90</strong></div>
                  <div class="metric">✅ Completed: <strong>70</strong></div>
                  <div class="metric">🔍 Surveyed Poles: <strong>60</strong></div>
                  <div class="metric">💡 Installed Lights: <strong>50</strong></div>
                  <div class="metric">🧾 Billed: <strong>0</strong></div>
                  <div class="text-end mt-3">
                    <a href="#" class="btn btn-sm btn-primary">See Details</a>
                  </div>
                </div>
              </div>
              <!-- <div class="col-12"><p class="text-muted">No site engineers found.</p></div> -->
            </div>
          </div>
        </div>
        @endforeach
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
