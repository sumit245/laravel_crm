<div>
  <div class="d-flex justify-content-between mb-4">
    <div class="d-flex mx-2">
      <div class="card bg-success mx-2" style="min-width: 33%;">
        <div class="card-body">
          <h5 class="card-title">{{ $totalLights }}</h5>
          <p class="card-text">Total Lights</p>
        </div>
      </div>
      <div class="card bg-info mx-2" style="min-width: 33%;">
        <div class="card-body">
          <h5 class="card-title">{{ $installationDoneCount }}</h5>
          <p class="card-text">Installed Light</p>
        </div>
      </div>
    </div>
    <!-- Button to trigger modal -->
    <button type="button" class="btn btn-primary" style="max-height: 2.8rem;" data-bs-toggle="modal"
      data-bs-target="#addTargetModal">
      Add Target
    </button>
  </div>

  <!-- Modal for adding a target -->
  <div class="modal fade" id="addTargetModal" tabindex="-1" aria-labelledby="addTargetModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <form action="{{ route("tasks.store") }}" method="POST">
          @csrf
          <input type="hidden" name="project_id" value="{{ $project->id }}" />
          <div class="modal-header">
            <h5 class="modal-title" id="addTargetModalLabel">Add Target for Project: {{ $project->project_name }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <!-- District Search -->
            <div class="form-group mb-3">
              <label for="districtSearch" class="form-label">Search District</label>
              <select id="districtSearch" name="district" class="form-select">
                <option value="">Select District</option>
                @foreach ($districts as $district)
                  <option value="{{ $district->district }}">{{ $district->district }}</option>
                @endforeach
              </select>
            </div>

            <!-- Block Search (Dependent on District) -->
            <div class="form-group mb-3">
              <label for="blockSearch" class="form-label">Search Block</label>
              <select id="blockSearch" name="block_id" class="form-select" disabled>
                <option value="">Select Block</option>
              </select>
            </div>

            <!-- Panchayat Search (Dependent on Block) -->
            <div class="form-group mb-3">
              <label for="panchayatSearch" class="form-label">Select Panchayat</label>
              <select id="panchayatSearch" name="panchayats[]" class="form-select" multiple disabled>
                <option value="">Select Panchayat</option>
              </select>
            </div>
            {{-- <div class="form-group mb-3">
              <label for="panchayatSearch" class="form-label">Search By Panchayat</label>
              <input type="text" id="panchayatSearch" placeholder="Search Site..." class="form-control">
              <div id="siteList"></div>

              <!-- Selected Sites -->
              <ul id="selectedSites"></ul>
              <!-- Hidden Select to Store Selected Sites -->
              <select id="selectedSitesSelect" name="sites[]" multiple class="d-none">
              </select>
            </div> --}}
            <div class="form-group mb-3">
              <label for="selectEngineer" class="form-label">Select Site Engineer</label>
              <select id="selectEngineer" name="engineer_id" class="form-select" required>
                @foreach ($engineers as $engineer)
                  <option value="{{ $engineer->id }}">{{ $engineer->firstName }} {{ $engineer->lastName }}</option>
                @endforeach
              </select>
            </div>
            <div class="form-group mb-3">
              <label for="selectVendor" class="form-label">Select Vendor</label>
              <select id="selectVendor" name="vendor_id" class="form-select" required>
                @foreach ($vendors as $vendor)
                  <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="form-group mb-3">
              <label for="startDate" class="form-label">Start Date</label>
              <input type="date" id="startDate" name="start_date" class="form-control" required>
            </div>
            <div class="form-group mb-3">
              <label for="endDate" class="form-label">End Date</label>
              <input type="date" id="endDate" name="end_date" class="form-control" required>
            </div>
          </div>
          <div class="modal-footer">
            <button type="reset" class="btn btn-secondary">Reset</button>
            <button type="submit" class="btn btn-primary">Allot Target</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Table to display targets -->
  <div class="table-responsive mt-4">
    <table class="table-striped table">
      <thead>
        <tr>
          <th>Panchayat</th>
          <th>Engineer Name</th>
          <th>Vendor Name</th>
          <th>Assigned Date</th>
          <th>End Date</th>
          <th>Wards</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($targets as $light)
          <tr>
            <td>{{ $light->site->panchayat }}</td>
            <td>{{ $light->engineer->firstName ?? "N/A" }}</td>
            <td>{{ $light->vendor->name ?? "N/A" }}</td>
            <td>{{ $light->created_at }}</td>
            <td>{{ $light->end_date }}</td>
            <td>{{ $light->site->ward }}</td>
            <td>
              @if ($light->isInstallationDone)
                <span class="badge bg-success">Installed</span>
              @else
                <span class="badge bg-warning">Pending</span>
              @endif
            </td>
            <td>
              <a href="{{ route("tasks.show", [$light->id, "any" => ""]) }}?project_type=1"
                class="btn btn-info btn-sm">View</a>

              <a href="{{ route("tasks.edit", $light->id) }}" class="btn btn-warning btn-sm">Edit</a>
              <form action="{{ route("tasks.destroy", $light->id) }}" method="POST" style="display: inline-block;">
                @csrf
                @method("DELETE")
                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
              </form>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

@push("scripts")
  <script>
    $(document).ready(function() {

      // Fetch Blocks Based on Selected District
      $('#districtSearch').change(function() {
        let district = $(this).val();
        $('#blockSearch').html('<option value="">Select Block</option>').prop('disabled', true);
        $('#panchayatSearch').html('<option value="">Select Panchayat</option>').prop('disabled', true);

        if (district) {
          $.get(`/blocks-by-district/${district}`, function(blocks) {
            blocks.forEach(block => {
              $('#blockSearch').append(`<option value="${block}">${block}</option>`);
            });
            $('#blockSearch').prop('disabled', false);
          });
        }
      });

      // Fetch Panchayats Based on Selected Block
      $('#blockSearch').change(function() {
        let block = $(this).val();
        $('#panchayatSearch').html('<option value="">Select Panchayat</option>').prop('disabled', true);

        if (block) {
          $.get(`/panchayats-by-block/${block}`, function(panchayats) {
            panchayats.forEach(panchayat => {
              $('#panchayatSearch').append(`<option value="${panchayat}">${panchayat}</option>`);
            });
            $('#panchayatSearch').prop('disabled', false);
          });
        }
      });
      $('#siteSearch').on('keyup', function() {
        let query = $(this).val();
        if (query.length > 1) {
          $.ajax({
            url: "{{ route("streetlights.search") }}",
            method: 'GET',
            data: {
              search: query
            },
            success: function(response) {
              let html = '';
              response.forEach(site => {
                html += `<div>
                                    <input type="checkbox" class="siteCheckbox" data-name="${site.text}" value="${site.id}">
                                    ${site.text}
                                </div>`;
              });
              $('#siteList').html(html);
            }
          });
        } else {
          $('#siteList').html('');
        }
      });

      $(document).on('change', '.siteCheckbox', function() {
        let siteId = $(this).val();
        let siteName = $(this).data('name');

        if ($(this).is(':checked')) {
          // Add to selected list
          $('#selectedSites').append(`<li data-id="${siteId}">${siteName}</li>`);

          // Add to hidden select
          $('#selectedSitesSelect').append(`<option value="${siteId}" selected>${siteName}</option>`);
        } else {
          // Remove from selected list
          $(`#selectedSites li[data-id="${siteId}"]`).remove();

          // Remove from hidden select
          $(`#selectedSitesSelect option[value="${siteId}"]`).remove();
        }
      });
    });
  </script>
@endpush
