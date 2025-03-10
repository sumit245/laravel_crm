@extends("layouts.main") <!-- Extend main.blade.php -->

@section("content")
  <div class="content-wrapper">
    <div class="row">
      <div class="col-sm-12">
        <div class="home-tab">
          <div class="d-sm-flex align-items-center justify-content-between border-bottom">
            <div class='d-inline-flex align-items-center'>
              <div class="dropdown">
                <button class="btn btn-primary dropdown-toggle" type="button" id="projectDropDown" data-bs-toggle="dropdown"
                  aria-haspopup="false" aria-expanded="false">
                  <span id="selectedProject" class="text-light">Select Project</span>
                </button>
                <div class="dropdown-menu hide" aria-labelledby="projectDropDown"
                  style="max-height: 400px; overflow-y: auto;">
                  <a class="dropdown-item py-3">
                    <p class="font-weight-medium float-left mb-0">Select Project</p>
                  </a>
                  <div class="dropdown-divider"></div>
                  @foreach ($projects as $category)
                    <a class="dropdown-item project-item" data-project-name="{{ $category->project_name }}"
                      data-project-id="{{ $category->id }}">
                      <div class="preview-item-content flex-grow py-2">
                        <p class="preview-subject ellipsis font-weight-medium text-dark">{{ $category->project_name }}</p>
                      </div>
                    </a>
                  @endforeach
                </div>
              </div>
              <a type="button" href="projects/create" class="btn btn-primary-outline">
                <span>Add Project</span>
              </a>
            </div>
            <div>
              <div class="btn-wrapper">
                <a href="#" class="btn btn-outline-dark">
                  <i class="icon-printer"></i> Print
                </a>
                <a href="#" class="btn btn-primary me-0 text-white">
                  <i class="icon-download"></i> Export
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="row mt-4">
      @if ($project->project_type == 1)
        {{-- Streetlight Project Stats --}}
        <div class="col-md-3">
          <div class="card card-rounded">
            <div class="card-body text-center">
              <h4>Total Panchayats</h4>
              <p class="fs-4 fw-bold">{{ $statistics["totalSites"] }}</p>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card card-rounded">
            <div class="card-body text-center">
              <h4>Total Poles</h4>
              <p class="fs-4 fw-bold">{{ $statistics["totalPoles"] }}</p>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card card-rounded">
            <div class="card-body text-center">
              <h4>Surveyed Poles</h4>
              <p class="fs-4 fw-bold">{{ $statistics["surveyedPoles"] }}</p>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card card-rounded">
            <div class="card-body text-center">
              <h4>Installed Poles</h4>
              <p class="fs-4 fw-bold">{{ $statistics["installedPoles"] }}</p>
            </div>
          </div>
        </div>
      @else
        {{-- Rooftop Project Stats --}}
        <div class="col-md-3">
          <div class="card card-rounded">
            <div class="card-body text-center">
              <h4>Total Sites</h4>
              <p class="fs-4 fw-bold">{{ $statistics["totalSites"] }}</p>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card card-rounded">
            <div class="card-body text-center">
              <h4>Completed Sites</h4>
              <p class="fs-4 fw-bold">{{ $statistics["completedTasks"] }}</p>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card card-rounded">
            <div class="card-body text-center">
              <h4>Pending Sites</h4>
              <p class="fs-4 fw-bold">{{ $statistics["pendingTasks"] }}</p>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card card-rounded">
            <div class="card-body text-center">
              <h4>In Progress</h4>
              <p class="fs-4 fw-bold">{{ $statistics["inProgressTasks"] }}</p>
            </div>
          </div>
        </div>
      @endif
    </div>

    @include("partials.performance")
  </div>
@endsection

@push("scripts")
  <script>
    document.addEventListener("DOMContentLoaded", function() {
      const projectItems = document.querySelectorAll(".project-item");
      const selectedProjectSpan = document.getElementById('selectedProject');

      projectItems.forEach((item) => {
        item.addEventListener("click", function(event) {
          event.preventDefault();
          const projectId = this.getAttribute("data-project-id");
          const projectName = this.getAttribute('data-project-name');
          selectedProjectSpan.textContent = projectName;
          sessionStorage.setItem("project_name", projectName);
          sessionStorage.setItem("project_id", projectId);
          window.location.href = "/dashboard?project_id=" + projectId;
        })
      });
      let storedProjectName = sessionStorage.getItem("project_name");
      if (storedProjectName) {
        selectedProjectSpan.textContent = storedProjectName;
      }
    });
  </script>
@endpush

@push("styles")
  <style>
    .parent-card {
      background-color: #dddddd;
    }

    .stats-card {
      background-color: white;
      /* border-radius: 0.6rem; */
      padding: .5rem;
      text-align: center;
      border: #dddddd 1px solid;
      height: 100%;
      transition: transform 0.2s;
    }

    .stats-title {
      font-size: .75rem;
      font-weight: 600;
      margin-bottom: 1rem;
      color: #333;
    }

    .stats-total {
      font-size: 1.2rem;
      font-weight: bold;
      margin-bottom: 1rem;
      color: #2d3748;
    }
  </style>
@endpush
