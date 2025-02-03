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
      <div class="col-sm-12">
        <div class="card card-rounded">
          <div class="card-body">
            <h4 class="mb-3">Project Performance Overview</h4>
            <div class="table-responsive">
              <table class="table-hover table">
                <thead>
                  <tr>
                    <th>Role</th>
                    <th>Name</th>
                    <th>Performance</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($projectManagers as $pm)
                    <tr class="expandable-row" data-bs-toggle="collapse" data-bs-target="#pm-{{ $pm->id }}">
                      <td>Project Manager</td>
                      <td>{{ $pm->name }}</td>
                      <td><span class="badge bg-primary">{{ $pm->performance }}</span></td>
                    </tr>
                    <tr class="collapse" id="pm-{{ $pm->id }}" data-bs-parent="tbody">
                      <td colspan="3">
                        <table class="table-hover table">
                          @foreach ($pm->siteEngineers as $se)
                            <tr class="expandable-row" data-bs-toggle="collapse" data-bs-target="#se-{{ $se->id }}">
                              <td>Site Engineer</td>
                              <td>{{ $se->name }}</td>
                              <td><span class="badge bg-info">{{ $se->performance }}</span></td>
                            </tr>
                            <tr class="collapse" id="se-{{ $se->id }}" data-bs-parent="#pm-{{ $pm->id }}">
                              <td colspan="3">
                                <table class="table-hover table">
                                  @foreach ($se->vendors as $vendor)
                                    <tr>
                                      <td>Vendor</td>
                                      <td>{{ $vendor->name }}</td>
                                      <td><span class="badge bg-success">{{ $vendor->performance }}</span></td>
                                    </tr>
                                  @endforeach
                                </table>
                              </td>
                            </tr>
                          @endforeach
                        </table>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
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
