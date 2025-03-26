@extends("layouts.main") {{-- or the name of your base layout --}}

@section("content")
  <div class="content-wrapper">
    <div class="card p-2">
      <div class="card-body">
        <div class="row my-2">
          <div class="col-3 col-sm">
            <label class="font-10 text-uppercase mg-b-10 fw-bold">State</label>
            <p class="mg-b-0">{{ $state[0]->name }}</p>
          </div>
          <div class="col-3 col-sm">
            <label class="font-10 text-uppercase mg-b-10 fw-bold">Project Type</label>
            <p class="mg-b-0">{{ $project->project_type == 0 ? "Rooftop Installation" : "Streetlight Installation" }}</p>
          </div>
        </div>
        <div class="row">
          <!-- Project Details -->
          <div class="col-3 col-sm">
            <label class="font-10 text-uppercase mg-b-10 fw-bold">Project Name</label>
            <p class="mg-b-0">{{ $project->project_name }}</p>
          </div>
          <div class="col-3 col-sm">
            <label class="font-10 text-uppercase mg-b-10 fw-bold">Work Order Number</label>
            <p class="mg-b-0">{{ $project->work_order_number }}</p>
          </div>
          <div class="col-3 col-sm">
            <label class="font-10 text-uppercase mg-b-10 fw-bold">Start Date</label>
            <p class="mg-b-0">{{ $project->start_date }}</p>
          </div>
          <div class="col-3 col-sm">
            <label class="font-10 text-uppercase mg-b-10 fw-bold">End Date</label>
            <p class="mg-b-0">{{ $project->end_date }}</p>
          </div>
          <!-- Project Details -->
        </div>
        <div class="row my-2">
          <div class="col-3 col-sm">
            <label class="font-10 text-uppercase mg-b-10 fw-bold">Rate</label>
            <p class="mg-b-0">{{ $project->rate }}</p>
          </div>
          <div class="col-3 col-sm">
            <label class="font-10 text-uppercase mg-b-10 fw-bold">Project Capacity</label>
            <p class="mg-b-0">{{ $project->project_capacity }}</p>
          </div>
          <div class="col-3 col-sm">
            <label class="font-10 text-uppercase mg-b-10 fw-bold">Order Value</label>
            <p class="mg-b-0">{{ $project->total }}</p>
          </div>
          <div class="col-3 col-sm">
            <label class="font-10 text-uppercase mg-b-10 fw-bold">Description</label>
            <p class="mg-b-0">{{ $project->description }}</p>
          </div>
        </div>
        <hr class="my-4" />
        <!-- Tabs -->
        <ul class="nav nav-tabs fixed-navbar-project" id="myTab" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="sites-tab" data-bs-toggle="tab" data-bs-target="#sites" type="button"
              role="tab" aria-controls="sites" aria-selected="true">
              Sites
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="staff-tab" data-bs-toggle="tab" data-bs-target="#staff" type="button"
              role="tab" aria-controls="staff">
              Staff Management
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="vendors-tab" data-bs-toggle="tab" data-bs-target="#vendors" type="button"
              role="tab" aria-controls="vendors" aria-selected="true">
              Vendor Management
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
              Target
            </button>
          </li>
        </ul>

        <div class="tab-content" id="myTabContent">
          <!-- Sites Tab -->
          <div class="tab-pane fade show active" id="sites" role="tabpanel" aria-labelledby="sites-tab">
            @include("projects.project_site", [
                "sites" => $project->project_type == 1 ? $sites : $project->sites,
            ])
          </div>

          <!-- Staffs Tab -->
          <div class="tab-pane fade" id="staff" role="tabpanel" aria-labelledby="staff-tab">
            @include("projects.project_staff", [
                "engineers" => $engineers,
            ])
          </div>

          <!-- Vendors Tab -->
          <div class="tab-pane fade" id="vendors" role="tabpanel" aria-labelledby="vendors-tab">
            @include("projects.project_vendors", [
                "vendors" => $vendors,
            ])
          </div>

          <!-- Inventory Tab -->
          <div class="tab-pane fade" id="inventory" role="tabpanel" aria-labelledby="inventory-tab">
            @include("projects.project_inventory", [
                "stores" => $project->stores,
                "users" => $users,
            ])
          </div>

          <!-- Tasks Tab -->
          <div class="tab-pane fade" id="tasks" role="tabpanel" aria-labelledby="tasks-tab">
            @include("projects.project_task", [
                "tasks" => $project->tasks,
            ])
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
