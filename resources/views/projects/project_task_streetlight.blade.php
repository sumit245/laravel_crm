<div>
  <div class="d-flex justify-content-between mb-0">
    <div class="d-flex mx-2">
      <div class="card bg-success mx-2" style="min-width: 33%;">
        <div class="card-body">
          <h5 class="card-title">{{ $totalPoles ?? 0 }}</h5>
          <p class="card-text">Total Poles</p>
        </div>
      </div>
      <div class="card bg-warning mx-2" style="min-width: 33%;">
        <div class="card-body">
          <h5 class="card-title">{{ $totalSurveyedPoles ?? 0 }}</h5>
          <p class="card-text">Surveyed Poles</p>
        </div>
      </div>
      <div class="card bg-info mx-2" style="min-width: 33%;">
        <div class="card-body">
          <h5 class="card-title">{{ $totalInstalledPoles ?? 0 }}</h5>
          <p class="card-text">Installed Lights</p>
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
            <div class="mb-3">
              <label for="panchayatSearch" class="form-label">Select Panchayat</label>
              <select id="panchayatSearch" name="sites[]" multiple="multiple" class="form-select" style="width: 100%;">
                <option value="">Select a Panchayat</option>
              </select>
            </div>

            <div class="mb-3">
              <label for="selectEngineer" class="form-label">Select Site Engineer</label>
              <select id="selectEngineer" name="engineer_id" class="form-select" required>

                @foreach ($assignedEngineers as $engineer)
                  <option value="{{ $engineer->id }}">{{ $engineer->firstName }} {{ $engineer->lastName }}</option>
                @endforeach

              </select>
            </div>
            <div class="form-group mb-3">
              <label for="selectVendor" class="form-label">Select Vendor</label>
              <select id="selectVendor" name="vendor_id" class="form-select" required>
                @foreach ($assignedVendors as $vendor)
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
   
  <div class="table-responsive mt-3">
    <table id="targetTable" class="table-striped table-bordered table-sm mt-4 table">
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
            <td>{{ $light->site->panchayat ?? "N/A" }}</td>
            <td>{{ $light->engineer->firstName ?? "N/A" }}</td>
            <td>{{ $light->vendor->name ?? "N/A" }}</td>
            <td>{{ $light->created_at }}</td>
            <td>{{ $light->end_date ?? "N/A" }}</td>
            <td>{{ $light->site->ward ?? "N/A" }}</td>
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

              <a href="{{ route("tasks.edit", $light->id) }}?project_id={{ $project->id }}" class="btn btn-warning btn-sm">Edit</a>
              
              <form action="{{ route("tasks.destroystreetlight", $light->id) }}" method="POST" style="display: inline-block;" class="delete-task-form">
                @csrf
                @method("DELETE")
                <button type="button" class="btn btn-danger btn-sm delete-task-btn">Delete</button>
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

    // Data tables script begins
    window.onload = function() {
    const table = $('#targetTable').DataTable({
      dom: "<'row'<'col-sm-6 d-flex align-items-center'f><'col-sm-6 d-flex justify-content-end'B>>" +
        "<'row'<'col-sm-12'tr>>" +
        "<'row'<'col-sm-5'i><'col-sm-7'p>>",
      buttons: [{
          extend: 'collection',
          text: '<i class="mdi mdi-menu"></i> Actions',
          className: 'btn btn-sm btn-secondary',
          buttons: [{
            text: 'Delete Selected',
            action: function() {
              performBulkAction('delete');
            }
          }]
        },
        {
          extend: 'excel',
          text: '<i class="mdi mdi-file-excel"></i>',
          className: 'btn btn-sm btn-success',
          titleAttr: 'Export to Excel'
        },
        {
          extend: 'print',
          text: '<i class="mdi mdi-printer"></i>',
          className: 'btn btn-sm btn-info',
          titleAttr: 'Print Table'
        }
      ],
      order:[],
      paging: true,
      pageLength: {{ $pageLength ?? 25 }},
      searching: true,
      responsive: true,
      language: {
        search: '',
        searchPlaceholder: 'Search...'
      },
      columnDefs: [{
        orderable: false,
        targets: [0, -1] // Targets the first column for select-checkbox
      }],
      ordering: true,
      select: {
        style: 'multi',
        selector: 'td:first-child'
      },

    });

    $('#selectAll').on('click', function() {
      const isChecked = $(this).is(':checked');
      table.rows().nodes().to$().find('input[type="checkbox"]').prop('checked', isChecked);
      if (isChecked) {
        table.rows().select();
      } else {
        table.rows().deselect();
      }
    });
    // Track individual row selection to update "Select All" state
    $('#targetTable tbody').on('click', 'input[type="checkbox"]', function() {
      const allChecked = $('#targetTable tbody input[type="checkbox"]:checked').length === table
        .rows()
        .count();
      $('#selectAll').prop('checked', allChecked);
    });
    // Bulk action function
    function performBulkAction(action) {
      const selectedIds = [];
      table.rows({
        selected: true
      }).data().each(function(rowData) {
        selectedIds.push(rowData[0]); // Assuming the ID is in the first column
      });

      if (selectedIds.length === 0) {
        alert('Please select at least one row.');
        return;
      }

      // Example: Show selected IDs in the console
      console.log(`Performing "${action}" on IDs: `, selectedIds);

      // Perform the actual action here
      // Example:
      // $.ajax({
      //   url: `/bulk/${action}`,
      //   method: 'POST',
      //   data: { ids: selectedIds },
      //   success: function(response) {
      //     alert(`${action} successful!`);
      //     table.ajax.reload();
      //   },
      //   error: function() {
      //     alert(`Failed to perform ${action}.`);
      //   }
      // });
    }
  }

    // Data table script ends

    // Delete target
    $(document).ready(function() {
  $('.delete-task-btn').on('click', function(e) {
    e.preventDefault();
    
    const form = $(this).closest('.delete-task-form');
    
    Swal.fire({
      title: 'Are you sure?',
      text: 'You are about to delete this task. This action cannot be undone.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Yes, delete it!',
      cancelButtonText: 'Cancel',
    }).then((result) => {
      if (result.isConfirmed) {
        form.submit();
      }
    });
  });
});
    
    $(document).ready(function() {
      $('#panchayatSearch').select2({
        placeholder: "Select a Panchayat",
        allowClear: true,
        dropdownParent: $('#addTargetModal'),
        ajax: {
          url: "{{ route("streetlights.search") }}", // Laravel route
          dataType: 'json',
          method: "GET",
          delay: 250,
          data: function(params) {
            return {
              search: params.term
            };
          },
          processResults: function(data) {
            console.log(data)
            return {
              results: data.map(item => ({
                id: item.id,
                text: item.text
              }))
            };
          }
        }
      });
      // Fetch Blocks Based on Selected District
      $('#districtSearch').change(function() {
        let district = $(this).val();
        $('#blockSearch').prop('disabled', false).empty().append('<option value="">Select a Block</option>');
        $('#panchayatSearch').prop('disabled', true).empty().append(
          '<option value="">Select a Panchayat</option>');

        if (district) {
          $.ajax({
            url: '/blocks-by-district/' + district,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
              console.log(data)
              $.each(data, function(index, block) {
                $('#blockSearch').append('<option value="' + block + '">' + block + '</option>');
              });

            },
            error: function(xhr, status, error) {
              console.error("AJAX Error:", status, error);
              console.log("Response:", xhr.responseText);
            }
          });
        }
      });

      // Fetch Panchayats Based on Selected Block
      $('#blockSearch').change(function() {
        let block = $(this).val();
        $('#panchayatSearch').prop('disabled', false).empty().append(
          '<option value="">Select a Panchayat</option>');

        if (block) { // You're checking 'district' instead of 'block'
          $.ajax({
            url: '/jicr/panchayats/' + block,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
              console.log(data);
              $.each(data, function(index, panchayat) {
                $('#panchayatSearch').append('<option value="' + panchayat.panchayat + '">' + panchayat
                  .panchayat +
                  '</option>');
              });
            },
            error: function(xhr, status, error) {
              console.error("AJAX Error:", status, error);
              console.log("Response:", xhr.responseText);
            }
          });
        }
      });
    });
  </script>
@endpush

@push("styles")
  <style>
    .select2-container--default .select2-selection--single {
      height: 38px;
      padding: 6px 12px;
      border: 1px solid #ccc;
      border-radius: 4px;
    }
  </style>
@endpush