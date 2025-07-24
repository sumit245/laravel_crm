@extends("layouts.main")

@section("content")
  <div class="min-h-screen bg-gray-50 p-4">
    <!-- Header -->
    <div class="border-dark mb-4 border-2 bg-white p-4">
      <div class="d-flex justify-content-between align-items-center">
        <h1 class="h2 font-weight-bold mb-0">
          @if (isset($projectType) && $projectType == 1)
            {{ $site->task_id ?? 0 }}
          @else
            {{ $site->site_name ?? 0 }}
          @endif
        </h1>
        <h2 class="h2 font-weight-bold mb-0">Sugs Lloyd</h2>
      </div>
      <div class="mt-2">
        <p class="h5 font-weight-medium mb-0">
          @if (isset($projectType) && $projectType == 1)
            {{ $site->panchayat ?? "Demo Panchayat" }}, {{ $site->district ?? "Demo District" }} -
            {{ $site->state ?? "Demo State" }}
          @else
            {{ $site->location ?? "Demo Location" }}, {{ $site->districtRelation->name ?? "Demo District" }} -
            {{ $site->stateRelation->name ?? "Demo State" }}
          @endif
        </p>
      </div>
    </div>

    <div class="d-flex" style="gap: 1.5rem;">
      <!-- Left Sidebar - Ward Buttons -->
      <div style="width: 250px;">
        @if (isset($projectType) && $projectType == 1)
          @php
            $wards = collect(explode(",", $site->ward))
                ->map(fn($w) => "Ward " . trim($w))
                ->toArray();
          @endphp
          @foreach ($wards as $ward)
            <div class="card border-dark ward-button mb-3 cursor-pointer border-2" data-ward="{{ $ward }}"
              onclick="loadWardData(event, '{{ $ward }}')">
              <div class="card-body p-4 text-center">
                <div class="h4 font-weight-bold">{{ $ward }}</div>
              </div>
            </div>
          @endforeach
        @else
          <div class="card border-dark mb-3 border-2">
            <div class="card-body p-4 text-center">
              <div class="h4 font-weight-bold">Site Details</div>
            </div>
          </div>
        @endif
      </div>

      <!-- Main Content -->
      <div class="flex-fill border-dark border-2 bg-white">
        <div class="p-4">
          <!-- Engineer and Vendor Info -->
          <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap">
            <div>
              @if (isset($projectType) && $projectType == 1)
                <p class="h5 font-weight-medium mb-1">Manager - {{ $managerName }}</p>
                <p class="h5 font-weight-medium mb-1">Engineer - {{ $engineerName }}</p>
                <p class="h5 font-weight-medium mb-1">Vendor - {{ $vendorName }}</p>
              @else
                <p class="h5 font-weight-medium mb-1">Engineer - {{ $site->site_engineer ?? "Ram Kumar" }}</p>
                <p class="h5 font-weight-medium mb-1">Vendor - {{ $site->ic_vendor_name ?? "Shyam Kumar" }}</p>
              @endif
            </div>

            <!-- Start Date and End Date with Carbon -->
            <div class="text-right">
              @if (isset($projectType) && $projectType == 1)
                <p class="h5 font-weight-medium mb-1">Start Date:
                  {{ \Carbon\Carbon::parse($streetlightTask?->start_date)->format("d/M/Y") }}</p>
                <p class="h5 font-weight-medium mb-1">End Date:
                  {{ \Carbon\Carbon::parse($streetlightTask?->end_date)->format("d/M/Y") }}</p>
              @else
                <p class="h5 font-weight-medium mb-1">Start Date:
                  {{ \Carbon\Carbon::parse($site->material_inspection_date)->format("d/M/Y") ?? "abc" }}</p>
                <p class="h5 font-weight-medium mb-1">End Date:
                  {{ \Carbon\Carbon::parse($site->commissioning_date)->format("d/M/Y") ?? "abc" }}</p>
              @endif
            </div>
          </div>

          <!-- Tabs -->
          <ul class="nav nav-tabs fixed-navbar-project" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active" id="surveyed-tab" data-bs-toggle="tab" data-status="surveyed" type="button"
                role="tab" aria-selected="true">
                Surveyed Poles
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="installed-tab" data-bs-toggle="tab" data-status="installed" type="button"
                role="tab" aria-selected="false">
                Installed Lights
              </button>
            </li>
          </ul>

          <!-- Single DataTable -->
          <x-data-table id="polesDataTable" class="table-striped table-bordered table-sm mt-3 table">
            <x-slot:thead>
              <tr>
                <th>Pole Number</th>
                <th>Beneficiary</th>
                <th>Beneficiary Contact</th>
                <th>Ward</th>
                <th>Actions</th>
              </tr>
            </x-slot:thead>
            <x-slot:tbody>
              @foreach ($poles as $pole)
                <tr data-ward="{{ $pole->ward_name }}" data-surveyed="{{ $pole->isSurveyDone }}"
                  data-installed="{{ $pole->isInstallationDone }}">
                  <td>{{ $pole->complete_pole_number }}</td>
                  <td>{{ $pole->beneficiary ?? "N/A" }}</td>
                  <td>{{ $pole->beneficiary_contact ?? "N/A" }}</td>
                  <td>{{ $pole->ward_name ?? "N/A" }}</td>
                  <td>
                    <a href="#" class="btn btn-icon btn-info" data-toggle="tooltip" title="View Details">
                      <i class="mdi mdi-eye"></i>
                    </a>
                  </td>
                </tr>
              @endforeach
            </x-slot:tbody>
          </x-data-table>

        </div>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  @push("scripts")
    <script>
      let currentWard = null;
      let currentTab = 'surveyed';

      function filterTable() {
        document.querySelectorAll('#polesDataTable tbody tr').forEach(row => {
          const ward = row.getAttribute('data-ward');
          const surveyed = row.getAttribute('data-surveyed');
          const installed = row.getAttribute('data-installed');

          let show = true;

          if (currentWard && ward !== currentWard) show = false;
          if (currentTab === 'surveyed' && surveyed !== '1') show = false;
          if (currentTab === 'installed' && installed !== '1') show = false;

          row.style.display = show ? '' : 'none';
        });
      }

      function loadWardData(event, ward) {
        event.preventDefault();
        currentWard = ward;
        document.querySelectorAll('.ward-button').forEach(btn => btn.classList.remove('active'));
        event.currentTarget.classList.add('active');
        filterTable();
      }

      document.addEventListener('DOMContentLoaded', function() {
        // Tab switch filter
        document.querySelectorAll('[data-bs-toggle="tab"]').forEach(tab => {
          tab.addEventListener('click', function() {
            currentTab = this.getAttribute('data-status');
            filterTable();
          });
        });

        filterTable(); // initial filter
      });
    </script>
  @endpush

@endsection
