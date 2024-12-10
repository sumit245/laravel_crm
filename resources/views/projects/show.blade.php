@extends("layouts.main") {{-- or the name of your base layout --}}

@section("content")
  <div class="content-wrapper">
    <div class="row">
      <div class="col-md-12 grid-margin">
        <div class="card">
          <div class="card-body">
            <div class="row">
              <!-- Personal Details -->
              <div class="col-6 col-sm">
                <label class="tx-10 tx-medium tx-spacing-1 tx-color-03 tx-uppercase tx-sans mg-b-10">Project Name</label>
                <p class="mg-b-0">{{ $project->project_name }}</p>
              </div>
              <div class="col-6 col-sm">
                <label class="tx-10 tx-medium tx-spacing-1 tx-color-03 tx-uppercase tx-sans mg-b-10">Work Order
                  Number:</label>
                <p class="mg-b-0">{{ $project->work_order_number }}</p>
              </div>
              <div class="col-6 col-sm">
                <label class="tx-10 tx-medium tx-spacing-1 tx-color-03 tx-uppercase tx-sans mg-b-10">Start Date</label>
                <p class="mg-b-0">{{ $project->start_date }}</p>
              </div>
              <div class="col-6 col-sm">
                <label class="tx-10 tx-medium tx-spacing-1 tx-color-03 tx-uppercase tx-sans mg-b-10">Order Value</label>
                <p class="mg-b-0">{{ $project->rate }}</p>
              </div>
            </div>
          </div>
          <hr>

          <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active" id="sites-tab" data-bs-toggle="tab" data-bs-target="#sites" type="button"
                role="tab" aria-controls="sites" aria-selected="true">
                Sites
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="inventory-tab" data-bs-toggle="tab" data-bs-target="#inventory" type="button"
                role="tab" aria-controls="inventory" aria-selected="false">
                Inventory
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="tasks-tab" data-bs-toggle="tab" data-bs-target="#tasks" type="button"
                role="tab" aria-controls="tasks" aria-selected="false">
                Tasks
              </button>
            </li>
          </ul>

          <div class="tab-content mt-4" id="myTabContent">
            <!-- Sites tab -->
            <div class="tab-pane fade show active" id="sites" role="tabpanel" aria-labelledby="sites-tab">
              <div class="row my-4 text-center">
                <!-- Total Sites Card -->
                <div class="col-md-4">
                  <div class="card">
                    <div class="card-body">
                      <h4 class="card-title">Total Sites</h4>
                      <p class="card-text display-6">50</p>
                    </div>
                  </div>
                </div>

                <!-- Sites in progress -->
                <div class="col-md-4">
                  <div class="card">
                    <div class="card-body">
                      <h4 class="card-title">Sites In Progress</h4>
                      <p class="card-text display-6">20</p>
                    </div>
                  </div>
                </div>

                <!-- Completed Sites -->
                <div class="col-md-4">
                  <div class="card">
                    <div class="card-body">
                      <h4 class="card-title">Completed</h4>
                      <p class="card-text display-6">30</p>
                    </div>
                  </div>
                </div>
              </div>

            </div>

            <!-- inventory Tab -->
            <div class="tab-pane fade" id="inventory" role="tabpanel" aria-labelledby="inventory-tab">
              <div class="row my-4 text-center">
                <div class="col-md-2">
                  <div class="card">
                    <div class="card-body">
                      <h4 class="card-title">Store A</h4>
                    </div>
                  </div>
                </div>

                <div class="col-md-2">
                  <div class="card">
                    <div class="card-body">
                      <h4 class="card-title">Store B</h4>
                    </div>
                  </div>
                </div>

                <div class="col-md-2">
                  <div class="card">
                    <div class="card-body">
                      <h4 class="card-title">Store C</h4>
                    </div>
                  </div>
                </div>

                <div class="col-md-2">
                  <div class="card">
                    <div class="card-body">
                      <h4 class="card-title">Store D</h4>
                    </div>
                  </div>
                </div>

                <div class="col-md-2">
                  <div class="card">
                    <div class="card-body">
                      <h4 class="card-title">Store E</h4>
                    </div>
                  </div>
                </div>

                <div class="col-md-2">
                  <div class="card">
                    <div class="card-body">
                      <h4 class="card-title">Store F</h4>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Tasks tab -->
            <div class="tab-pane fade" id="tasks" role="tabpanel" aria-labelledby="tasks-tab">
              <div class="row my-4 text-center">
                <div class="col-md-4">
                  <div class="card">
                    <div class="card-body">
                      <h4 class="card-title">Installation</h4>
                      <p class="card-text display-6">10</p>
                    </div>
                  </div>
                </div>

                <div class="col-md-4">
                  <div class="card">
                    <div class="card-body">
                      <h4 class="card-title">RMS Status</h4>
                      <p class="card-text display-6">5</p>
                    </div>
                  </div>
                </div>

                <div class="col-md-4">
                  <div class="card">
                    <div class="card-body">
                      <h4 class="card-title">Final Inspection</h4>
                      <p class="card-text display-6">2</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Edit & Delete Buttons -->
          <div class="d-flex justify-content-end mt-4">
            <!-- Edit Button -->
            <a href="{{ route("staff.edit", $project->id) }}" class="btn btn-icon btn-warning" data-toggle="tooltip"
              title="Edit Staff">
              <i class="mdi mdi-pencil"> Edit</i>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
  </div>
@endsection
