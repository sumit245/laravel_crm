@extends("layouts.main")

@section("content")
  <div class="min-h-screen bg-gray-50 p-4">
    <!-- Header -->
    <div class="border-dark mb-4 border-2 bg-white p-4 rounded">
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
      @if (isset($projectType) && $projectType == 1)
        <div style="width: 250px;">
          @php
            $wards = collect(explode(",", $site->ward))
                ->map(fn($w) => "Ward " . trim($w))
                ->toArray();
          @endphp
          <div class="card border-dark ward-button active mb-3 cursor-pointer border-2 rounded" 
               data-ward=""
               onclick="loadWardData(event, '')">
            <div class="card-body p-3 text-center">
              <div class="h5 font-weight-bold">All Wards</div>
            </div>
          </div>
          @foreach ($wards as $ward)
            <div class="card border-dark ward-button mb-3 cursor-pointer border-2 rounded" 
                 data-ward="{{ $ward }}"
                 onclick="loadWardData(event, '{{ $ward }}')">
              <div class="card-body p-4 text-center">
                <div class="h4 font-weight-bold">{{ $ward }}</div>
              </div>
            </div>
          @endforeach
        </div>
      @endif

      <!-- Main Content -->
      <div class="flex-fill border-dark border-2 bg-white rounded">
        <div class="p-4">
          <!-- Engineer and Vendor Info -->
          <div class="d-flex justify-content-between align-items-start flex-wrap mb-4">
            <div>
              @if (isset($projectType) && $projectType == 1)
                <p class="h5 font-weight-medium mb-1">
                  <strong>Manager</strong> - {{ $managerName }}
                </p>
                <p class="h5 font-weight-medium mb-1">
                  <strong>Engineer</strong> - {{ $engineerName }}
                </p>
                <p class="h5 font-weight-medium mb-1">
                  <strong>Vendor</strong> - {{ $vendorName }}
                </p>
              @else
                <p class="h5 font-weight-medium mb-1">
                  <strong>Engineer</strong> - {{ $site->site_engineer ?? "Ram Kumar" }}
                </p>
                <p class="h5 font-weight-medium mb-1">
                  <strong>Vendor</strong> - {{ $site->ic_vendor_name ?? "Shyam Kumar" }}
                </p>
              @endif
            </div>

            <!-- Start Date and End Date with Carbon -->
            <div class="text-right">
              @if (isset($projectType) && $projectType == 1)
                <p class="h5 font-weight-medium mb-1"><strong>Start Date</strong>:
                  {{ \Carbon\Carbon::parse($streetlightTask?->start_date)->format("d/M/Y") }}</p>
                <p class="h5 font-weight-medium mb-1"><strong>End Date</strong>:
                  {{ \Carbon\Carbon::parse($streetlightTask?->end_date)->format("d/M/Y") }}</p>
              @else
                <p class="h5 font-weight-medium mb-1">Start Date:
                  {{ \Carbon\Carbon::parse($site->material_inspection_date)->format("d/M/Y") ?? "abc" }}</p>
                <p class="h5 font-weight-medium mb-1">End Date:
                  {{ \Carbon\Carbon::parse($site->commissioning_date)->format("d/M/Y") ?? "abc" }}</p>
              @endif
            </div>
          </div>

          @if (isset($projectType) && $projectType == 1)
            @php
              // Prepare columns for datatable
              $columns = [
                  ['title' => 'Pole Number'],
                  ['title' => 'Beneficiary'],
                  ['title' => 'Beneficiary Contact'],
                  ['title' => 'Ward'],
              ];

              // Prepare filters
              $filters = [];
              
              // Ward filter (select) - use data attribute for filtering
              if (isset($wardOptions) && !empty($wardOptions)) {
                  $filters[] = [
                      'name' => 'ward',
                      'label' => 'Ward',
                      'type' => 'select',
                      'column' => 3, // Ward column index
                      'options' => array_merge(['' => 'All Wards'], $wardOptions),
                      'width' => 3,
                      'useDataAttribute' => 'ward', // Filter by data-ward attribute
                  ];
              }

              // Beneficiary filter (text)
              $filters[] = [
                  'name' => 'beneficiary',
                  'label' => 'Beneficiary',
                  'type' => 'text',
                  'column' => 1, // Beneficiary column index
                  'width' => 3,
              ];

              // Pole Number filter (text)
              $filters[] = [
                  'name' => 'pole_number',
                  'label' => 'Pole Number',
                  'type' => 'text',
                  'column' => 0, // Pole Number column index
                  'width' => 3,
              ];

              // Prepare import/export URLs
              $importRoute = route('sites.poles.import', ['siteId' => $site->id]);
              $importFormatUrl = route('sites.poles.exportFormat', ['siteId' => $site->id]);
              $bulkDeleteRoute = route('sites.poles.bulkDelete');
            @endphp

            <!-- Tabs -->
            <ul class="nav nav-tabs mb-3" id="polesTab" role="tablist">
              <li class="nav-item" role="presentation">
                <button class="nav-link active" id="surveyed-tab" data-bs-toggle="tab" data-bs-target="#poles-tab-content" 
                        type="button" role="tab" aria-controls="poles-tab-content" aria-selected="true"
                        data-status="surveyed">
                  Surveyed Poles
                </button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="installed-tab" data-bs-toggle="tab" data-bs-target="#poles-tab-content" 
                        type="button" role="tab" aria-controls="poles-tab-content" aria-selected="false"
                        data-status="installed">
                  Installed Lights
                </button>
              </li>
            </ul>

            <!-- Single DataTable with all poles -->
            <div class="tab-content" id="polesTabContent">
              <div class="tab-pane fade show active" id="poles-tab-content" role="tabpanel">
                <x-datatable 
                  id="polesDataTable" 
                  :columns="$columns" 
                  :exportEnabled="true"
                  :importEnabled="true" 
                  :importRoute="$importRoute" 
                  :importFormatUrl="$importFormatUrl" 
                  :bulkDeleteEnabled="true" 
                  :bulkDeleteRoute="$bulkDeleteRoute"
                  pageLength="50"
                  searchPlaceholder="Search poles..." 
                  :filters="$filters">
                  @foreach ($poles ?? [] as $pole)
                    <tr data-ward="{{ $pole->ward_name }}" 
                        data-surveyed="{{ $pole->isSurveyDone ? 1 : 0 }}"
                        data-installed="{{ $pole->isInstallationDone ? 1 : 0 }}"
                        data-pole-id="{{ $pole->id }}">
                      <td>
                        <input type="checkbox" class="row-checkbox" value="{{ $pole->id }}">
                      </td>
                      <td>{{ $pole->complete_pole_number }}</td>
                      <td>{{ $pole->beneficiary ?? "N/A" }}</td>
                      <td>{{ $pole->beneficiary_contact ?? "N/A" }}</td>
                      <td>{{ $pole->ward_name ?? "N/A" }}</td>
                      <td class="text-center">
                        <a href="{{ route('poles.show', $pole->id) }}" 
                           class="btn btn-icon btn-info" 
                           data-toggle="tooltip" 
                           title="View Details">
                          <i class="mdi mdi-eye"></i>
                        </a>
                      </td>
                    </tr>
                  @endforeach
                </x-datatable>
              </div>
            </div>
          @else
            <!-- Non-streetlight project content can go here -->
            <div class="text-center p-4">
              <p class="text-muted">Site details for non-streetlight projects</p>
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>

 @push('styles')
  <style>
    .site-view-container {
      background: transparent;
    }

    .ward-button {
      transition: all 0.2s ease;
      cursor: pointer;
    }

    .ward-button:hover {
      background-color: var(--color-primary-soft, rgba(31, 59, 179, 0.08));
    }

    .ward-button.active {
      background-color: var(--color-primary, #1F3BB3);
      color: white;
      border-color: var(--color-primary-dark, #172d88);
    }

    .ward-button.active .h4,
    .ward-button.active .h5 {
      color: white;
    }

    /* Override global .nav styles for nav-tabs to ensure horizontal layout */
    #polesTab.nav.nav-tabs {
      position: static !important;
      display: flex !important;
      flex-direction: row !important;
      flex-wrap: nowrap !important;
      max-width: none !important;
      width: 100% !important;
      border-bottom: 2px solid #dee2e6;
      margin-bottom: 1.5rem;
    }

    #polesTab.nav.nav-tabs .nav-item {
      margin-bottom: -2px;
      flex-shrink: 0;
    }

    #polesTab.nav.nav-tabs .nav-link {
      border: none;
      border-bottom: 2px solid transparent;
      color: #6c757d;
      padding: 0.75rem 1.25rem;
      transition: all 0.3s ease;
      background-color: transparent;
      font-weight: 500;
      white-space: nowrap;
    }

    #polesTab.nav.nav-tabs .nav-link:hover {
      border-bottom-color: var(--color-primary, #1F3BB3);
      color: var(--color-primary, #1F3BB3);
      background-color: transparent;
    }

    #polesTab.nav.nav-tabs .nav-link.active {
      border-bottom-color: var(--color-primary, #1F3BB3);
      color: var(--color-primary, #1F3BB3);
      font-weight: 600;
      background-color: transparent;
    }

    .rounded {
      border-radius: 0.25rem;
    }
  </style>
 @endpush

  <!-- Scripts -->
  @push("scripts")
    <script>
      // Store filter function reference
      let polesTableFilterFn = null;
      let currentWard = null;
      let currentTab = 'surveyed';

      function loadWardData(event, ward) {
        event.preventDefault();
        currentWard = ward || null;
        
        // Update active state
        document.querySelectorAll('.ward-button').forEach(btn => btn.classList.remove('active'));
        if (event.currentTarget) {
          event.currentTarget.classList.add('active');
        }

        // Update ward filter in datatable
        const tableId = '#polesDataTable';
        const table = window['table_polesDataTable'] || $(tableId).DataTable();
        
        if (table && typeof table.draw === 'function') {
          // Set ward filter value
          const wardFilterSelect = $('#datatable-wrapper-polesDataTable').find('.filter-select[data-filter="ward"]');
          if (wardFilterSelect.length) {
            wardFilterSelect.val(ward || '').trigger('change');
          }
          
          // Also apply tab filter
          applyPolesTableFilters();
        } else {
          // If table not ready, just apply tab filter
          applyPolesTableFilters();
        }
      }

      function applyPolesTableFilters() {
        const tableId = '#polesDataTable';
        const table = window['table_polesDataTable'] || $(tableId).DataTable();
        
        if (!table || typeof table.draw !== 'function') {
          return;
        }

        // Remove existing filter function if it exists
        if (polesTableFilterFn && $.fn.dataTable.ext.search) {
          const index = $.fn.dataTable.ext.search.indexOf(polesTableFilterFn);
          if (index !== -1) {
            $.fn.dataTable.ext.search.splice(index, 1);
          }
        }

        // Create new filter function
        polesTableFilterFn = function(settings, data, dataIndex) {
          if (settings.nTable.id !== 'polesDataTable') return true;
          
          try {
            const $row = $(table.row(dataIndex).node());
            const rowSurveyed = $row.attr('data-surveyed');
            const rowInstalled = $row.attr('data-installed');
            const rowWard = $row.attr('data-ward');
            
            // Apply tab filter
            if (currentTab === 'surveyed' && rowSurveyed !== '1') {
              return false;
            }
            if (currentTab === 'installed' && rowInstalled !== '1') {
              return false;
            }
            
            // Apply ward filter
            if (currentWard && rowWard !== currentWard) {
              return false;
            }
            
            return true;
          } catch (e) {
            return true;
          }
        };

        // Add filter function
        if (!$.fn.dataTable.ext.search) {
          $.fn.dataTable.ext.search = [];
        }
        $.fn.dataTable.ext.search.push(polesTableFilterFn);
        
        // Redraw table
        table.draw();
      }

      document.addEventListener('DOMContentLoaded', function() {
        // Wait for datatable to initialize
        setTimeout(function() {
          // Apply initial filter for surveyed tab
          applyPolesTableFilters();

          // Handle tab switching
          $('#polesTab button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
            const target = $(e.target).data('status');
            if (target) {
              currentTab = target;
              applyPolesTableFilters();
            }
          });
        }, 800);
      });

      // Make functions globally available for ward buttons
      window.loadWardData = loadWardData;
    </script>
  @endpush

@endsection
