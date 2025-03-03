@php
  $awardIcons = ["🥇", "🥈", "🥉"]; // Gold, Silver, Bronze
@endphp

<div class="bg-light mt-4 p-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="fw-bold">Performance Overview</h3>
    <select class="form-select w-auto" id="dateFilter">
      <option value="today">Today</option>
      <option value="week">This Week</option>
      <option value="month">This Month</option>
      <option value="custom">Custom Range</option>
    </select>
  </div>
  <div class="row">
    <div class="col">
      <div class="card card-rounded">
        <div class="card-body">
          <div class="row">
            <div class="col-lg-12">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                  <h4 class="card-title card-title-dash">Top Project Managers</h4>
                </div>
              </div>
              <div class="mt-3">
                @foreach ($projectManagers as $projectManager)
                  <a href="{{ route("staff.show", $projectManager->id) }}" class="text-decoration-none text-dark">
                    <div class="wrapper d-flex align-items-center justify-content-start border-bottom py-2">
                      <img class="img-sm rounded-10" src={{ $projectManager->image }} alt="profile">
                      <div class="wrapper ms-3" style="width:100%;">
                        <p class="fw-bold mb-1 ms-1">{{ $projectManager->name }}</p>
                        <div>
                          <div class="d-flex justify-content-between align-items-center max-width-progress-wrap mb-1">
                            <p>{{ session(project_type) == 1 ? "Panchayats Done" : "Sites Done" }}</p>
                            <p class="text-success">{{ $projectManager->performance }}</p>
                          </div>
                          <div class="progress progress-md">
                            <div class="progress-bar bg-success" role="progressbar" style="width: 85%"
                              aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                          </div>
                        </div>
                        <div>
                          <a href="{{ route("surveyed.poles", ["project_manager" => $projectManager->id, "role" => 1]) }}"
                            class="text-primary text-decoration-none">Poles Surveyed:
                            {{ $projectManager->surveyed_poles_count ?? 0 }}</a> <br />
                          <a href="{{ route("installed.poles", ["project_manager" => $projectManager->id, "role" => 1]) }}"
                            class="text-success text-decoration-none">Installed Poles:
                            {{ $projectManager->surveyed_poles_count ?? 0 }}</a>
                        </div>
                      </div>
                    </div>
                  </a>
                @endforeach
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col">
      <div class="card card-rounded">
        <div class="card-body">
          <div class="row">
            <div class="col-lg-12">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                  <h4 class="card-title card-title-dash">Site Engineers</h4>
                </div>
              </div>
              <div class="mt-3">
                @foreach ($siteEngineers as $se)
                  <a href="{{ route("staff.show", $se->id) }}" class="text-decoration-none text-dark">
                    <div class="wrapper d-flex align-items-center justify-content-start border-bottom py-2">
                      <img class="img-sm rounded-10" src={{ $se->image }} alt="profile">
                      <div class="wrapper ms-3" style="width:100%;">
                        <p class="fw-bold mb-1 ms-1">{{ $se->name }}</p>
                        <div>
                          <div class="d-flex justify-content-between align-items-center max-width-progress-wrap mb-1">
                            <p>{{ session(project_type) == 1 ? "Panchayats Done" : "Sites Done" }}</p>
                            <p class="text-success">{{ $se->performance }}</p>
                          </div>
                          <div class="progress progress-md">
                            <div class="progress-bar bg-success" role="progressbar" style="width: 85%"
                              aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </a>
                @endforeach
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col">
      <div class="card card-rounded">
        <div class="card-body">
          <div class="row">
            <div class="col-lg-12">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                  <h4 class="card-title card-title-dash">Top Vendors</h4>
                </div>
              </div>
              <div class="mt-3">
                @foreach ($vendors as $vendor)
                  <a href="{{ route("uservendors.show", $vendor->id) }}" class="text-decoration-none text-dark">
                    <div class="wrapper d-flex align-items-center justify-content-start border-bottom py-2">
                      <img class="img-sm rounded-10" src={{ $vendor->image }} alt="profile">
                      <div class="wrapper ms-3" style="width:100%;">
                        <p class="fw-bold mb-1 ms-1">{{ $vendor->name }}</p>
                        <div>
                          <div class="d-flex justify-content-between align-items-center max-width-progress-wrap mb-1">
                            <p>{{ session("project_type") == 1 ? "Panchayats Done" : "Sites Done" }}</p>
                            <p class="text-success">{{ $vendor->performance }}</p>
                          </div>
                          <div class="progress progress-md">
                            <div class="progress-bar bg-success" role="progressbar" style="width: 85%"
                              aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </a>
                @endforeach
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
