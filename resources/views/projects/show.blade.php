@extends("layouts.main") {{-- or the name of your base layout --}}

@section("content")
  <div class="content-wrapper">
    <div class="card p-2">
      <div class="card-body">
        <div class="row">
          <!-- Project Details -->
          <div class="col-6 col-sm">
            <label class="font-10 text-uppercase mg-b-10">Project Name</label>
            <p class="mg-b-0">{{ $project->project_name }}</p>
          </div>
          <div class="col-6 col-sm">
            <label class="font-10 text-uppercase mg-b-10">Work Order Number</label>
            <p class="mg-b-0">{{ $project->work_order_number }}</p>
          </div>
          <div class="col-6 col-sm">
            <label class="font-10 text-uppercase mg-b-10">Start Date</label>
            <p class="mg-b-0">{{ $project->start_date }}</p>
          </div>
          <div class="col-6 col-sm">
            <label class="font-10 text-uppercase mg-b-10">Order Value</label>
            <p class="mg-b-0">{{ $project->rate }}</p>
          </div>
        </div>
        <hr class="my-4" />
        <!-- Tabs -->
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

        <div class="tab-content" id="myTabContent">
          <!-- Sites Tab -->
          <div class="tab-pane fade show active" id="sites" role="tabpanel" aria-labelledby="sites-tab">
            @include("projects.project_site", ["sites" => $project->sites])
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
            @include("projects.project_task", ["tasks" => $project->tasks])
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection