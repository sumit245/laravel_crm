@extends("layouts.main")

@section("content")
<div class="min-h-screen bg-gray-50 p-4">
  <!-- Header -->
  <div class="bg-white border-2 border-dark p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center">
      <h1 class="h2 font-weight-bold mb-0">
        @if (isset($projectId) && $projectId == 11)
          {{ $streetlight->task_id ?? "LAK001" }}
        @else
          {{ $site->site_name ?? "LAK001" }}
        @endif
      </h1>
      <h2 class="h2 font-weight-bold mb-0">Streetlight Project</h2>
    </div>
    <div class="mt-2">
      <p class="h5 font-weight-medium mb-0">
        @if (isset($projectId) && $projectId == 11)
          {{ $streetlight->panchayat ?? "Mohammadpur" }}, {{ $streetlight->district ?? "Lakhisarai" }} - {{ $streetlight->state ?? "Bihar" }}
        @else
          {{ $site->location ?? "Mohammadpur" }}, {{ $site->districtRelation->name ?? "Lakhisarai" }} - {{ $site->stateRelation->name ?? "Bihar" }}
        @endif
      </p>
    </div>
  </div>

  <div class="d-flex" style="gap: 1.5rem;">
    <!-- Left Sidebar - Ward Buttons -->
    <div style="width: 4xl;">
      @if (isset($projectId) && $projectId == 11)
        @php
          $wards = ['Ward 5', 'Ward 6', 'Ward 7', 'Ward 8']; // Replace with dynamic if needed
        @endphp
        @foreach($wards as $ward)
          <div class="card border-2 border-dark mb-3 ward-button cursor-pointer" data-ward="{{ $ward }}" onclick="loadWardData(event, '{{ $ward }}')">
            <div class="card-body text-center p-4">
              <div class="h4 font-weight-bold">{{ $ward }}</div>
            </div>
          </div>
        @endforeach
      @else
        <div class="card border-2 border-dark mb-3">
          <div class="card-body text-center p-4">
            <div class="h4 font-weight-bold">Site Details</div>
          </div>
        </div>
      @endif
    </div>

    <!-- Main Content -->
    <div class="flex-fill border-2 border-dark bg-white">
      <div class="p-4">
        <!-- Engineer and Vendor Info with Surveyed Poles and Installed Lights -->
        <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap">
          <div>
            @if (isset($projectId) && $projectId == 11)
              <p class="h5 font-weight-medium mb-1">Engineer - Ram Kumar</p>
              <p class="h5 font-weight-medium mb-1">Vendor - Shyam Kumar</p>
              
              <!-- Surveyed Poles and Installed Lights Links -->
              <div class="mt-3">
                <a href="{{ route('surveyed.poles', ['ward' => 'selected_ward']) }}" class="text-primary text-decoration-none" id="surveyedPolesLink">
                  <strong>Poles Surveyed: {{ $surveyedPolesCount ?? 25 }}</strong>
                </a><br />
                <a href="{{ route('installed.poles', ['ward' => 'selected_ward']) }}" class="text-success text-decoration-none" id="installedPolesLink">
                  <strong>Installed Lights: {{ $installedPolesCount ?? 18 }}</strong>
                </a>
              </div>
            @else
              <p class="h5 font-weight-medium mb-1">Engineer - {{ $site->site_engineer ?? "Ram Kumar" }}</p>
              <p class="h5 font-weight-medium mb-1">Vendor - {{ $site->ic_vendor_name ?? "Shyam Kumar" }}</p>
              
              <!-- Surveyed Poles and Installed Lights Links for Solar Projects -->
              <div class="mt-3">
                <a href="{{ route('surveyed.poles', ['site_id' => $site->id]) }}" class="text-primary text-decoration-none">
                  <strong>Poles Surveyed: {{ $surveyedPolesCount ?? 0 }}</strong>
                </a><br />
                <a href="{{ route('installed.poles', ['site_id' => $site->id]) }}" class="text-success text-decoration-none">
                  <strong>Installed Lights: {{ $installedPolesCount ?? 0 }}</strong>
                </a>
              </div>
            @endif
          </div>
          
          <!-- Right side - Start Date and End Date -->
          <div class="text-right">
            @if (isset($projectId) && $projectId == 11)
              <p class="h5 font-weight-medium mb-1">Start Date: abc</p>
              <p class="h5 font-weight-medium mb-1">End Date: abc</p>
            @else
              <p class="h5 font-weight-medium mb-1">Start Date: {{ $site->material_inspection_date ?? "abc" }}</p>
              <p class="h5 font-weight-medium mb-1">End Date: {{ $site->commissioning_date ?? "abc" }}</p>
            @endif
          </div>
        </div>

        <!-- Horizontal Line -->
        <hr class="mb-4">

        <h3>Performance Today</h3>
        @if (isset($project) && $project->project_type == 1)
          @include('staff.streetlight-performance')
        @endif

        <div class="tab-content mt-1" id="myTabContent">
          <ul class="nav nav-tabs fixed-navbar-project" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active" id="surveyed-tab" data-bs-toggle="tab" data-bs-target="#surveyed_poles" type="button"
                role="tab" aria-controls="surveyed_poles" aria-selected="true">
                Surveyed Poles
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="installed-tab" data-bs-toggle="tab" data-bs-target="#installed_poles" type="button"
                role="tab" aria-controls="installed_poles" aria-selected="false">
                Installed Lights
              </button>
            </li>
          </ul>
          
          <div class="tab-pane fade show active" id="surveyed_poles" role="tabpanel" aria-labelledby="surveyed-tab">
            <x-data-table id="mainDataTable" class="table table-striped table-bordered table-sm mt-3">
              <x-slot:thead>
                <tr>
                  <th><input type="checkbox" id="selectAll" /></th>
                  <th>Pole Number</th>
                  <th>Beneficiary</th>
                  <th>Beneficiary Contact</th>
                  <th>Location</th>
                  <th>Actions</th>
                </tr>
                </x-slot:thead>
               <x-slot:tbody>
                <!-- Initial sample data or empty -->
                <tr>
                  <td><input type="checkbox" /></td>
                  <td>P-Ward 5-001</td>
                  <td>N/A</td>
                  <td>N/A</td>
                  <td>BAT-001</td>
                  <td>
                    <a href="#" class="btn btn-icon btn-info" data-toggle="tooltip" title="View Details">
                      <i class="mdi mdi-eye"></i>
                    </a>
                  </td>
                </tr>
              </x-slot:tbody>
            </x-data-table>
          </div>

          <div class="tab-pane fade" id="installed_poles" role="tabpanel" aria-labelledby="installed-tab">
            <x-data-table id="installedDataTable" class="table table-striped table-bordered table-sm mt-3">
              <x-slot:thead>
                <tr>
                  <th><input type="checkbox" id="selectAllInstalled" /></th>
                   <th>Pole Number</th>
                    <th>IMEI</th>
                    <th>Sim Number</th>
                    <th>Battery</th>
                    <th>Panel</th>
                    <th>Bill Raised</th>
                    <th>RMS</th>
                    <th>Actions</th>
                </tr>
              </x-slot:thead>
               <x-slot:tbody>
                <!-- Initial sample data or empty -->
                <tr>
                  <td><input type="checkbox" /></td>
                  <td>P-Ward 5-001</td>
                  <td>N/A</td>
                  <td>N/A</td>
                  <td>N/A</td>
                  <td>N/A</td>
                  <td>N/A</td>
                  <td>N/A</td>
                  <td>
                    <a href="#" class="btn btn-icon btn-info" data-toggle="tooltip" title="View Details">
                      <i class="mdi mdi-eye"></i>
                    </a>
                  </td>
                </tr>
              </x-slot:tbody>
            </x-data-table>
          </div>
        </div>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
.cursor-pointer {
  cursor: pointer;
}

.flex-fill {
  flex: 1;
}

.ward-button:hover {
  background-color: #f8f9fa;
  transform: translateY(-2px);
  transition: all 0.3s ease;
}

.ward-button.active {
  background-color: #e3f2fd;
  border-color: #2196f3 !important;
}

.min-h-screen {
  min-height: 100vh;
}

.bg-gray-50 {
  background-color: #f9fafb;
}

.border-2 {
  border-width: 2px !important;
}

.border-dark {
  border-color: #343a40 !important;
}

/* Custom styling for the surveyed poles and installed lights links */
.text-primary {
  color: #007bff !important;
}

.text-success {
  color: #28a745 !important;
}

.text-decoration-none {
  text-decoration: none !important;
}

.text-decoration-none:hover {
  text-decoration: underline !important;
}

.d-flex {
  display: flex !important;
}

.justify-content-between {
  justify-content: space-between !important;
}

.align-items-center {
  align-items: center !important;
}

.mb-4 {
  margin-bottom: 1.5rem !important;
}

.p-4 {
  padding: 1.5rem !important;
}
</style>

@push("scripts")
<script>

function loadWardData(event, ward) {
    event.preventDefault();

    // Remove active class from all ward buttons
    document.querySelectorAll('.ward-button').forEach(btn => {
        btn.classList.remove('active');
    });

    // Add active class to clicked ward button
    event.currentTarget.classList.add('active');

    console.log('Loading data for:', ward);

    updateWardCounts(ward);
    loadWardTableData(ward);
    loadInstalledLightsData(ward); // Load installed lights data too
}

function updateWardCounts(ward) {
    const surveyedLink = document.getElementById('surveyedPolesLink');
    const installedLink = document.getElementById('installedPolesLink');

    if (surveyedLink && installedLink) {
        // Replace 'selected_ward' placeholder in href with actual ward
        surveyedLink.href = surveyedLink.href.replace(/selected_ward/g, encodeURIComponent(ward));
        installedLink.href = installedLink.href.replace(/selected_ward/g, encodeURIComponent(ward));
    }
}

// Initialize first ward as active on page load
document.addEventListener('DOMContentLoaded', function() {
    const firstWard = document.querySelector('.ward-button');
    if (firstWard) {
        firstWard.classList.add('active');
        loadWardData({ currentTarget: firstWard, preventDefault: () => {} }, firstWard.dataset.ward);
    }
});

</script>
@endpush


@endsection


