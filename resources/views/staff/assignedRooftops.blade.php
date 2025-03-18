<div class="row grid-margin bg-light">
  <div class="col-md-3 stretch-card px-0">
    <div class="card">
      <div class="card-body px-0">
        <h4 class="card-title text-primary px-2">Assigned Tasks
          <div class="badge badge-pill badge-outline-success">{{ $assignedTasksCount }}</div>
        </h4>
        <div class="list-wrapper list-wrapper-sm">
          <ul class="d-flex flex-column-reverse todo-list todo-list-custom">
            @if ($assignedTasks->count() > 0)
              @foreach ($assignedTasks as $task)
                <li>
                  {{-- For Rooftop Projects --}}
                  <div class="ms-3">
                    <p>{{ $task->site->site_name ?? "N/A" }}</p>
                    <small class="text-muted mb-0"><i class="ti-location-pin me-1"></i>{{ $task->site->location }},
                      {{ $task->site->districtRelation->name ?? "N/A" }}</small>
                    <small>{{ $task->start_date }}</small>
                    <small>{{ $task->end_date }}</small>
                    <i class="remove ti-close"></i>
                  </div>
                </li>
              @endforeach
            @else
              <p>No assigned tasks found.</p>
            @endif
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-3 stretch-card px-0">
    <div class="card px-0">
      <div class="card-body">
        <h4 class="card-title text-success">Completed Sites
          <div class="badge badge-pill badge-outline-success">{{ $completedTasksCount }}</div>
        </h4>
        <div class="list-wrapper list-wrapper-sm">
          <ul class="d-flex flex-column-reverse todo-list todo-list-custom">
            @if ($completedTasks->count() > 0)
              @foreach ($completedTasks as $task)
                <li>
                  {{-- For Rooftop Projects --}}
                  <div class="ms-3">
                    <p>{{ $task->site->site_name ?? "N/A" }}</p>
                    <small class="text-muted mb-0"><i class="ti-location-pin me-1"></i>{{ $task->site->location }},
                      {{ $task->site->districtRelation->name ?? "N/A" }}</small>
                    <small>{{ $task->start_date }}</small>
                    <small>{{ $task->end_date }}</small>
                  </div>
                </li>
              @endforeach
            @endif
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-3 stretch-card px-0">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title text-warning">Pending Sites
          <div class="badge badge-pill badge-outline-success">{{ $pendingTasksCount }}</div>
        </h4>
        <div class="list-wrapper list-wrapper-sm">
          <ul class="d-flex flex-column-reverse todo-list todo-list-custom">
            @if ($pendingTasksCount > 0)
              @foreach ($pendingTasks as $task)
                <li>
                  {{-- For Rooftop Projects --}}
                  <div class="ms-3">
                    <p>{{ $task->site->site_name ?? "N/A" }}</p>
                    <small class="text-muted mb-0"><i class="ti-location-pin me-1"></i>{{ $task->site->location }},
                      {{ $task->site->districtRelation->name ?? "N/A" }}</small>
                    <small>{{ $task->start_date }}</small>
                    <small>{{ $task->end_date }}</small>
                  </div>
                  <i class="remove ti-close"></i>
                </li>
              @endforeach
            @endif
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-3 stretch-card px-0">
    <div class="card px-0">
      <div class="card-body">
        <h4 class="card-title text-danger">Rejected Sites
          <div class="badge badge-pill badge-outline-success">{{ $rejectedTasksCount }}</div>
        </h4>
        <div class="list-wrapper list-wrapper-sm">
          <ul class="d-flex flex-column-reverse todo-list todo-list-custom">
            @if ($rejectedTasksCount > 0)
              @foreach ($rejectedTasks as $task)
                <li>
                  {{-- For Rooftop Projects --}}
                  <div class="ms-3">
                    <p>{{ $task->site->site_name ?? "N/A" }}</p>
                    <small class="text-muted mb-0"><i class="ti-location-pin me-1"></i>{{ $task->site->location }},
                      {{ $task->site->districtRelation->name ?? "N/A" }}</small>
                    <small>{{ $task->start_date }}</small>
                    <small>{{ $task->end_date }}</small>
                  </div>
                </li>
              @endforeach
            @endif
        </div>
      </div>
    </div>
  </div>
</div>
