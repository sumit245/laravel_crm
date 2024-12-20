@extends("layouts.main") <!-- Extend main.blade.php -->

@section("content")
  <!-- Begin content section -->
  <div class="content-wrapper">
    {{-- Header Row --}}
    <div class="row">
      <div class="col-sm-12">
        <div class="home-tab">
          <div class="d-sm-flex align-items-center justify-content-between border-bottom">
            <div>
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
                  @foreach ($projects as $index => $category)
                    <a class="dropdown-item project-item" data-project-name="{{ $category->project_name }}"
                      data-project-id="{{ $category->id }}">
                      <div class="preview-item-content flex-grow py-2">
                        <p class="preview-subject ellipsis font-weight-medium text-dark">{{ $category->project_name }}</p>
                      </div>
                    </a>
                  @endforeach
                </div>
              </div>
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

    {{-- Statistics Row --}}
    <div class="row mt-4">
      <div class="col-sm-12">
        <div class="statistics-details d-flex align-items-center justify-content-between">
          @foreach ($statistics as $stat)
            <div style="border-right: 1px solid #8d8d8d; padding-inline: 2rem;">
              <a href="{{ $stat["link"] }}" class="text-decoration-none mb-3"
                style="color: inherit; flex: 1; min-width: 200px;">
                {{-- <div class="rounded border p-3 shadow-sm"> --}}
                <p class="statistics-title">{{ $stat["title"] }}</p>
                <h3 class="rate-percentage">{{ $stat["value"] }}</h3>
                <p class="{{ $stat["change_class"] }} d-flex">
                  <i class="mdi {{ $stat["change_icon"] }}"></i>
                  <span>{{ $stat["change_percentage"] }}</span>
                </p>
                {{-- </div> --}}
              </a>
            </div>
          @endforeach
        </div>
      </div>
    </div>

    {{-- Graph row --}}
    <div class="row my-4">
      <div class="col-lg-8 d-flex flex-column">
        <div class="row flex-grow">
          <div class="col-12 col-lg-4 col-lg-12 grid-margin stretch-card">
            <div class="card card-rounded">
              <div class="card-body">
                <div class="d-sm-flex justify-content-between align-items-start">
                  <div>
                    <h4 class="card-title card-title-dash">Performance Line Chart</h4>
                    <h5 class="card-subtitle card-subtitle-dash">This graph denotes expense vs income
                    </h5>
                  </div>
                  <div id="performance-line-legend"></div>
                </div>
                <div class="chartjs-wrapper mt-5">
                  <canvas id="performaneLine"></canvas>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-4 d-flex flex-column">
        <div class="row flex-grow">
          <div class="col-md-6 col-lg-12 grid-margin stretch-card">
            <div class="card bg-primary card-rounded">
              <div class="card-body pb-0">
                <h4 class="card-title card-title-dash mb-4 text-white">Status Summary</h4>
                <div class="row">
                  <div class="col-sm-4">
                    <p class="status-summary-ight-white mb-1">Closed Revenue</p>
                    <h2 class="text-info">357</h2>
                  </div>
                  <div class="col-sm-8">
                    <div class="status-summary-chart-wrapper pb-4">
                      <canvas id="status-summary"></canvas>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-6 col-lg-12 grid-margin stretch-card">
            <div class="card card-rounded">
              <div class="card-body">
                <div class="row">
                  <div class="col-sm-6">
                    <div class="d-flex justify-content-between align-items-center mb-sm-0 mb-2">
                      <div class="circle-progress-width">
                        <div id="totalVisitors" class="progressbar-js-circle pr-2"></div>
                      </div>
                      <div>
                        <p class="text-small mb-2">Total Projects</p>
                        <h4 class="fw-bold mb-0">2</h4>
                      </div>
                    </div>
                  </div>
                  <div class="col-sm-6">
                    <div class="d-flex justify-content-between align-items-center">
                      <div class="circle-progress-width">
                        <div id="visitperday" class="progressbar-js-circle pr-2"></div>
                      </div>
                      <div>
                        <p class="text-small mb-2">Total Sites</p>
                        <h4 class="fw-bold mb-0">1</h4>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="row flex-grow">
      <div class="col-12 grid-margin stretch-card">
        <div class="card card-rounded">
          <div class="card-body">
            <div class="d-sm-flex justify-content-between align-items-start">
              <div>
                <h4 class="card-title card-title-dash">Market Overview</h4>
                <p class="card-subtitle card-subtitle-dash">Lorem ipsum dolor sit amet consectetur
                  adipisicing elit</p>
              </div>
              <div>
                <div class="dropdown">
                  <button class="btn btn-secondary dropdown-toggle toggle-dark btn-lg mb-0 me-0" type="button"
                    id="dropdownMenuButton2" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> This
                    month </button>
                  <div class="dropdown-menu" aria-labelledby="dropdownMenuButton2">
                    <h6 class="dropdown-header">Settings</h6>
                    <a class="dropdown-item" href="#">Action</a>
                    <a class="dropdown-item" href="#">Another action</a>
                    <a class="dropdown-item" href="#">Something else here</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="#">Separated link</a>
                  </div>
                </div>
              </div>
            </div>
            <div class="d-sm-flex align-items-center justify-content-between mt-1">
              <div class="d-sm-flex align-items-center justify-content-between mt-4">
                <h2 class="fw-bold me-2">$36,2531.00</h2>
                <h4 class="me-2">USD</h4>
                <h4 class="text-success">(+1.37%)</h4>
              </div>
              <div class="me-3">
                <div id="marketing-overview-legend"></div>
              </div>
            </div>
            <div class="chartjs-bar-wrapper mt-3">
              <canvas id="marketingOverview"></canvas>
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
      const stateItems = document.querySelectorAll(".project-item");
      const selectedStateSpan = document.getElementById('selectedProject');

      stateItems.forEach((item) => {
        item.addEventListener("click", function(event) {
          event.preventDefault(); // Prevent default anchor behavior

          const stateId = this.getAttribute("data-project-id");
          const stateName = this.getAttribute('data-project-name');
          selectedStateSpan.textContent = stateName; // Update the dropdown toggle text

          // Example: Send stateId to server via AJAX
          fetch('/update-selected-state', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
              },
              body: JSON.stringify({
                state_id: stateId
              })
            })
            .then(response => response.json())
            .then(data => {
              console.log('Server Response:', data);
            })
            .catch(error => console.error('Error:', error));
        });
      });
    });
  </script>
@endpush
