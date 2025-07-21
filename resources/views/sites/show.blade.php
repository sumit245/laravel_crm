@extends("layouts.main")

@section("content")
<div class="min-h-screen bg-gray-50 p-4">
  <!-- Header -->
  <div class="bg-white border-2 border-dark p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center">
      <h1 class="h2 font-weight-bold mb-0">
        @if ($projectId == 11)
          {{ $streetlight->task_id ?? "LAK001" }}
        @else
          {{ $site->site_name ?? "LAK001" }}
        @endif
      </h1>
      <h2 class="h2 font-weight-bold mb-0">Streetlight Project</h2>
    </div>
    <div class="mt-2">
      <p class="h5 font-weight-medium mb-0">
        @if ($projectId == 11)
          {{ $streetlight->panchayat ?? "Mohammadpur" }}, {{ $streetlight->district ?? "Lakhisarai" }}-{{ $streetlight->state ?? "Bihar" }}
        @else
          {{ $site->location ?? "Mohammadpur" }}, {{ $site->districtRelation->name ?? "Lakhisarai" }}-{{ $site->stateRelation->name ?? "Bihar" }}
        @endif
      </p>
    </div>
  </div>

  <div class="d-flex" style="gap: 1.5rem;">
    <!-- Left Sidebar - Ward Buttons -->
    <div style="width: 250px;">
      @if ($projectId == 11)
        @php
          $wards = ['Ward 5', 'Ward 6', 'Ward 7', 'Ward 8']; // You can make this dynamic based on your data
        @endphp
        @foreach($wards as $index => $ward)
          <div class="card border-2 border-dark mb-3 ward-button cursor-pointer" data-ward="{{ $ward }}" onclick="loadWardData('{{ $ward }}')">
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
        <div class="d-flex justify-content-between align-items-start mb-4">
          <div>
            @if ($projectId == 11)
              <p class="h5 font-weight-medium mb-1">Engineer- Ram Kumar</p>
              <p class="h5 font-weight-medium mb-1">Vendor -Shyam Kumar</p>
              
              <!-- Surveyed Poles and Installed Lights Links -->
              <div class="mt-3">
                <a href="{{ route('surveyed.poles', ['ward' => 'selected_ward']) }}" class="text-primary text-decoration-none">
                  <strong>Poles Surveyed: {{ $surveyedPolesCount ?? 25 }}</strong>
                </a><br />
                <a href="{{ route('installed.poles', ['ward' => 'selected_ward']) }}" class="text-success text-decoration-none">
                  <strong>Installed Lights: {{ $installedPolesCount ?? 18 }}</strong>
                </a>
              </div>
            @else
              <p class="h5 font-weight-medium mb-1">Engineer- {{ $site->site_engineer ?? "Ram Kumar" }}</p>
              <p class="h5 font-weight-medium mb-1">Vendor- {{ $site->ic_vendor_name ?? "Shyam Kumar" }}</p>
              
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
            @if ($projectId == 11)
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

        <!-- Search and Action Buttons -->
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div class="input-group" style="width: 300px;">
            <div class="input-group-prepend">
              <span class="input-group-text"><i class="mdi mdi-magnify"></i></span>
            </div>
            <input type="text" class="form-control" placeholder="Search inventory" id="searchTable">
          </div>
          <div class="btn-group">
            <button class="btn btn-info btn-sm"><i class="mdi mdi-download"></i></button>
            <button class="btn btn-danger btn-sm"><i class="mdi mdi-upload"></i></button>
            <button class="btn btn-primary btn-sm"><i class="mdi mdi-share"></i></button>
          </div>
        </div>

        <!-- Main Data Table -->
        <div class="table-responsive border">
          <table class="table table-striped mb-0" id="mainDataTable">
            <thead class="thead-light">
              <tr>
                <th><input type="checkbox"></th>
                <th>Pole Number</th>
                <th>IMEI</th>
                <th>Sim Number</th>
                <th>Battery</th>
                <th>Panel</th>
                <th>Bill Raised</th>
                <th>RMS</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td colspan="9" class="text-center py-4 text-muted">No data available in table</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center mt-3">
          <small class="text-muted">Showing 0 to 0 of 0 entries</small>
          <div class="btn-group">
            <button class="btn btn-outline-secondary btn-sm" disabled>Previous</button>
            <button class="btn btn-outline-secondary btn-sm" disabled>Next</button>
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
</style>

<script>
// Ward button functionality
function loadWardData(ward) {
    // Remove active class from all ward buttons
    document.querySelectorAll('.ward-button').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Add active class to clicked ward button
    event.target.closest('.ward-button').classList.add('active');
    
    // Here you would typically make an AJAX call to load ward-specific data
    console.log('Loading data for:', ward);
    
    // Update the surveyed poles and installed lights counts for the selected ward
    updateWardCounts(ward);
    
    // Load data for the selected ward
    loadWardTableData(ward);
}

function updateWardCounts(ward) {
    // Update the links with ward-specific counts
    const surveyedLink = document.querySelector('a[href*="surveyed.poles"]');
    const installedLink = document.querySelector('a[href*="installed.poles"]');
    
    if (surveyedLink && installedLink) {
        // Update href to include selected ward
        surveyedLink.href = surveyedLink.href.replace('selected_ward', ward);
        installedLink.href = installedLink.href.replace('selected_ward', ward);
    }
}

function loadWardTableData(ward) {
    // Make AJAX call to get data for the ward
    // For now, just update the table with sample data
    const tableBody = document.querySelector('#mainDataTable tbody');
    tableBody.innerHTML = `
        <tr>
            <td><input type="checkbox"></td>
            <td>P-${ward}-001</td>
            <td>123456789012345</td>
            <td>9876543210</td>
            <td>BAT-001</td>
            <td>PAN-001</td>
            <td>₹5000</td>
            <td>Active</td>
            <td>
                <button class="btn btn-sm btn-primary">View</button>
                <button class="btn btn-sm btn-warning">Edit</button>
                <button class="btn btn-sm btn-success">Monitor</button>
            </td>
        </tr>
        <tr>
            <td><input type="checkbox"></td>
            <td>P-${ward}-002</td>
            <td>123456789012346</td>
            <td>9876543211</td>
            <td>BAT-002</td>
            <td>PAN-002</td>
            <td>₹5500</td>
            <td>Active</td>
            <td>
                <button class="btn btn-sm btn-primary">View</button>
                <button class="btn btn-sm btn-warning">Edit</button>
                <button class="btn btn-sm btn-success">Monitor</button>
            </td>
        </tr>
    `;
    
    // Update pagination info
    document.querySelector('.text-muted').textContent = `Showing 1 to 2 of 2 entries for ${ward}`;
}

// Search functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchTable');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('#mainDataTable tbody tr');
            
            tableRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
    
    // Initialize with first ward selected
    const firstWard = document.querySelector('.ward-button');
    if (firstWard) {
        firstWard.classList.add('active');
        loadWardData(firstWard.dataset.ward);
    }
});
</script>
@endsection