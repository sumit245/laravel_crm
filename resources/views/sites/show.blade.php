@extends("layouts.main")

@section("content")
<div class="min-h-screen bg-gray-50 p-4">
  <!-- Debug Information (remove in production) -->
  <div class="alert alert-info mb-3" style="font-size: 12px;">
    <strong>Debug Info:</strong>
    Site ID: {{ $site->id ?? 'N/A' }} | 
    Streetlight ID: {{ $streetlight->id ?? 'N/A' }} |
    Task ID: {{ $taskId ?? 'N/A' }} | 
    Has StreetlightTask: {{ isset($streetlightTask) && $streetlightTask ? 'Yes' : 'No' }} | 
    Wards Count: {{ count($polesByWard ?? []) }} |
    Project ID: {{ $projectId ?? 'N/A' }} |
    Project Type: {{ isset($project) ? $project->project_type : 'N/A' }}
    @if(!empty($polesByWard))
      <br>Wards: {{ implode(', ', $polesByWard) }}
    @endif
    @if(!empty($wardCounts))
      <br>Ward Counts: 
      @foreach($wardCounts as $ward => $counts)
        {{ $ward }}: S-{{ $counts['surveyed'] }}/I-{{ $counts['installed'] }} | 
      @endforeach
    @endif
    @if(isset($site))
      <br>Site Task ID: {{ $site->task_id ?? 'N/A' }} | Site Ward: {{ $site->ward ?? 'N/A' }}
    @endif
    @if(isset($streetlight))
      <br>Streetlight Task ID: {{ $streetlight->task_id ?? 'N/A' }} | Streetlight Ward: {{ $streetlight->ward ?? 'N/A' }}
    @endif
  </div>

  <!-- Header -->
  <div class="bg-white border-2 border-dark p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center">
      <h1 class="h2 font-weight-bold mb-0">
        @if (isset($streetlight))
          {{ $streetlight->task_id ?? "LAK001" }}
        @elseif(isset($site) && $site)
          {{ $site->site_name ?? $site->task_id ?? "LAK001" }}
        @else
          LAK001
        @endif
      </h1>
      <h2 class="h2 font-weight-bold mb-0">Streetlight Project</h2>
    </div>
    <div class="mt-2">
      <p class="h5 font-weight-medium mb-0">
        @if (isset($streetlight))
          {{ $streetlight->panchayat ?? "Mohammadpur" }}, {{ $streetlight->district ?? "Lakhisarai" }} - {{ $streetlight->state ?? "Bihar" }}
        @elseif(isset($site) && $site)
          {{ $site->panchayat ?? $site->location ?? "Mohammadpur" }}, {{ $site->district ?? ($site->districtRelation->name ?? "Lakhisarai") }} - {{ $site->state ?? ($site->stateRelation->name ?? "Bihar") }}
        @else
          Mohammadpur, Lakhisarai - Bihar
        @endif
      </p>
    </div>
  </div>

  <div class="d-flex" style="gap: 1.5rem;">
    <!-- Left Sidebar - Ward Buttons -->
    <div style="width: 4xl;">
      @if(isset($polesByWard) && !empty($polesByWard))
        @foreach($polesByWard as $index => $ward)
          <div class="card border-2 border-dark mb-3 ward-button cursor-pointer {{ $index === 0 ? 'active' : '' }}" 
               data-ward="{{ $ward }}" 
               data-task-id="{{ $taskId }}"
               onclick="loadWardData(event, '{{ $ward }}')">
            <div class="card-body text-center p-4">
              <div class="h4 font-weight-bold">{{ $ward }}</div>
              <div class="small text-muted">
                <div>Surveyed: {{ $wardCounts[$ward]['surveyed'] ?? 0 }}</div>
                <div>Installed: {{ $wardCounts[$ward]['installed'] ?? 0 }}</div>
              </div>
            </div>
          </div>
        @endforeach
      @else
        @php
          // Default wards if no database wards found
          $defaultWards = ['Ward 5', 'Ward 6', 'Ward 7', 'Ward 8'];
        @endphp
        @foreach($defaultWards as $index => $ward)
          <div class="card border-2 border-dark mb-3 ward-button cursor-pointer {{ $index === 0 ? 'active' : '' }}" 
               data-ward="{{ $ward }}" 
               data-task-id="{{ $taskId ?? '' }}"
               onclick="loadWardData(event, '{{ $ward }}')">
            <div class="card-body text-center p-4">
              <div class="h4 font-weight-bold">{{ $ward }}</div>
              <div class="small text-muted">
                <div>Sample Data</div>
              </div>
            </div>
          </div>
        @endforeach
      @endif
    </div>

    <!-- Main Content -->
    <div class="flex-fill border-2 border-dark bg-white">
      <div class="p-4">
        <!-- Engineer and Vendor Info with Surveyed Poles and Installed Lights -->
        <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap">
          <div>
            <p class="h5 font-weight-medium mb-1">Engineer - {{ $engineerName ?? 'Ram Kumar' }}</p>
            <p class="h5 font-weight-medium mb-1">Vendor - {{ $vendorName ?? 'Shyam Kumar' }}</p>
            
            <!-- Surveyed Poles and Installed Lights Links -->
            <div class="mt-3">
              <a href="#" class="text-primary text-decoration-none" id="surveyedPolesLink">
                <strong>Poles Surveyed: <span id="currentWardSurveyed">{{ $surveyedPolesCount ?? 25 }}</span></strong>
              </a><br />
              <a href="#" class="text-success text-decoration-none" id="installedPolesLink">
                <strong>Installed Lights: <span id="currentWardInstalled">{{ $installedPolesCount ?? 18 }}</span></strong>
              </a>
            </div>
          </div>
          
          <!-- Right side - Start Date and End Date -->
          <div class="text-right">
            <p class="h5 font-weight-medium mb-1">Start Date: {{ $startDate ?? 'abc' }}</p>
            <p class="h5 font-weight-medium mb-1">End Date: {{ $endDate ?? 'abc' }}</p>
          </div>
        </div>

        <!-- Horizontal Line -->
        <hr class="mb-4">
        
        <h3>Performance Today</h3>
        
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
              <x-slot:tbody id="surveyedTableBody">
                <!-- Initial sample data or empty -->
                <tr>
                  <td colspan="6" class="text-center">Click a ward to load data</td>
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
              <x-slot:tbody id="installedTableBody">
                <!-- Initial sample data or empty -->
                <tr>
                  <td colspan="9" class="text-center">Click a ward to load data</td>
                </tr>
              </x-slot:tbody>
            </x-data-table>
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
let currentTaskId = '{{ $taskId ?? "" }}';
let wardCounts = @json($wardCounts ?? []);
let hasStreetlightTask = {{ isset($streetlightTask) && $streetlightTask ? 'true' : 'false' }};

console.log('Page loaded with:', {
    currentTaskId: currentTaskId,
    wardCounts: wardCounts,
    hasStreetlightTask: hasStreetlightTask
});

function loadWardData(event, ward) {
    event.preventDefault();
    
    console.log('loadWardData called with ward:', ward);
    
    // Remove active class from all ward buttons
    document.querySelectorAll('.ward-button').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Add active class to clicked ward button
    event.currentTarget.classList.add('active');
    
    console.log('Loading data for:', ward);
    
    // Update ward counts
    updateWardCounts(ward);
    
    // Load ward table data if we have streetlight task
    if (hasStreetlightTask && currentTaskId) {
        console.log('Loading real data from database');
        loadWardTableData(ward, 'surveyed');
        loadWardTableData(ward, 'installed');
    } else {
        console.log('Loading sample data');
        // Load sample data for demo
        loadSampleData(ward);
    }
}

function updateWardCounts(ward) {
    const surveyedSpan = document.getElementById('currentWardSurveyed');
    const installedSpan = document.getElementById('currentWardInstalled');
    
    if (hasStreetlightTask && wardCounts[ward]) {
        // Update with real data
        if (surveyedSpan) surveyedSpan.textContent = wardCounts[ward].surveyed || 0;
        if (installedSpan) installedSpan.textContent = wardCounts[ward].installed || 0;
        console.log('Updated counts with real data:', wardCounts[ward]);
    } else {
        // Update with sample data based on ward
        const sampleData = getSampleDataForWard(ward);
        if (surveyedSpan) surveyedSpan.textContent = sampleData.surveyed;
        if (installedSpan) installedSpan.textContent = sampleData.installed;
        console.log('Updated counts with sample data:', sampleData);
    }
}

function getSampleDataForWard(ward) {
    const sampleData = {
        'Ward 5': { surveyed: 25, installed: 18 },
        'Ward 6': { surveyed: 30, installed: 22 },
        'Ward 7': { surveyed: 28, installed: 20 },
        'Ward 8': { surveyed: 32, installed: 25 }
    };
    return sampleData[ward] || { surveyed: 0, installed: 0 };
}

function loadSampleData(ward) {
    console.log('Loading sample data for ward:', ward);
    
    // Load sample data for surveyed poles
    const surveyedTableBody = document.getElementById('surveyedTableBody');
    const installedTableBody = document.getElementById('installedTableBody');
    
    if (surveyedTableBody) {
        surveyedTableBody.innerHTML = `
            <tr>
                <td><input type="checkbox" /></td>
                <td>P-${ward}-001</td>
                <td>Sample Beneficiary</td>
                <td>9876543210</td>
                <td>BAT-001</td>
                <td>
                    <a href="#" class="btn btn-icon btn-info" data-toggle="tooltip" title="View Details">
                        <i class="mdi mdi-eye"></i>
                    </a>
                </td>
            </tr>
            <tr>
                <td><input type="checkbox" /></td>
                <td>P-${ward}-002</td>
                <td>Another Beneficiary</td>
                <td>9876543211</td>
                <td>BAT-002</td>
                <td>
                    <a href="#" class="btn btn-icon btn-info" data-toggle="tooltip" title="View Details">
                        <i class="mdi mdi-eye"></i>
                    </a>
                </td>
            </tr>
        `;
    }
    
    if (installedTableBody) {
        installedTableBody.innerHTML = `
            <tr>
                <td><input type="checkbox" /></td>
                <td>P-${ward}-001</td>
                <td>IMEI123456</td>
                <td>SIM789012</td>
                <td>Battery OK</td>
                <td>Panel OK</td>
                <td>Yes</td>
                <td>Active</td>
                <td>
                    <a href="#" class="btn btn-icon btn-info" data-toggle="tooltip" title="View Details">
                        <i class="mdi mdi-eye"></i>
                    </a>
                </td>
            </tr>
        `;
    }
}

function loadWardTableData(ward, type) {
    console.log('loadWardTableData called:', { ward, type, currentTaskId });
    
    if (!currentTaskId) {
        console.log('No task ID available for loading ward data');
        return;
    }
    
    const tableBodyId = type === 'surveyed' ? 'surveyedTableBody' : 'installedTableBody';
    const tableBody = document.getElementById(tableBodyId);
    
    if (!tableBody) {
        console.log('Table body not found:', tableBodyId);
        return;
    }
    
    // Show loading state
    tableBody.innerHTML = '<tr><td colspan="' + (type === 'surveyed' ? '6' : '9') + '" class="text-center">Loading...</td></tr>';
    
    const requestData = {
        task_id: currentTaskId,
        ward_name: ward,
        type: type
    };
    
    console.log('Making AJAX request with data:', requestData);
    
    // Make AJAX request
    fetch('{{ route("sites.ward.poles") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify(requestData)
    })
    .then(response => {
        console.log('Response received:', response);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            renderTableData(data.data, type, tableBodyId);
        } else {
            console.error('API returned error:', data.message);
            tableBody.innerHTML = '<tr><td colspan="' + (type === 'surveyed' ? '6' : '9') + '" class="text-center text-danger">Error: ' + (data.message || 'Unknown error') + '</td></tr>';
        }
    })
    .catch(error => {
        console.error('AJAX Error:', error);
        tableBody.innerHTML = '<tr><td colspan="' + (type === 'surveyed' ? '6' : '9') + '" class="text-center text-danger">Error loading data: ' + error.message + '</td></tr>';
    });
}

function renderTableData(poles, type, tableBodyId) {
    console.log('renderTableData called:', { poles, type, tableBodyId });
    
    const tableBody = document.getElementById(tableBodyId);
    
    if (poles.length === 0) {
        const colSpan = type === 'surveyed' ? '6' : '9';
        tableBody.innerHTML = `<tr><td colspan="${colSpan}" class="text-center">No ${type} poles found for this ward</td></tr>`;
        return;
    }
    
    let html = '';
    poles.forEach(pole => {
        if (type === 'surveyed') {
            html += `
                <tr>
                    <td><input type="checkbox" /></td>
                    <td>${pole.pole_number || 'N/A'}</td>
                    <td>${pole.beneficiary_name || 'N/A'}</td>
                    <td>${pole.beneficiary_contact || 'N/A'}</td>
                    <td>${pole.location || 'N/A'}</td>
                    <td>
                        <a href="#" class="btn btn-icon btn-info" data-toggle="tooltip" title="View Details">
                            <i class="mdi mdi-eye"></i>
                        </a>
                    </td>
                </tr>
            `;
        } else {
            html += `
                <tr>
                    <td><input type="checkbox" /></td>
                    <td>${pole.pole_number || 'N/A'}</td>
                    <td>${pole.imei || 'N/A'}</td>
                    <td>${pole.sim_number || 'N/A'}</td>
                    <td>${pole.battery_info || 'N/A'}</td>
                    <td>${pole.panel_info || 'N/A'}</td>
                    <td>${pole.bill_raised || 'N/A'}</td>
                    <td>${pole.rms_status || 'N/A'}</td>
                    <td>
                        <a href="#" class="btn btn-icon btn-info" data-toggle="tooltip" title="View Details">
                            <i class="mdi mdi-eye"></i>
                        </a>
                    </td>
                </tr>
            `;
        }
    });
    
    tableBody.innerHTML = html;
    console.log('Table updated with', poles.length, 'rows');
}

// Initialize first ward as active on page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing first ward');
    
    const firstWard = document.querySelector('.ward-button');
    if (firstWard) {
        const wardName = firstWard.dataset.ward;
        console.log('First ward found:', wardName);
        
        firstWard.classList.add('active');
        
        // Load initial data
        loadWardData({ currentTarget: firstWard, preventDefault: () => {} }, wardName);
    } else {
        console.log('No ward buttons found');
    }
});
</script>
@endpush

@endsection
