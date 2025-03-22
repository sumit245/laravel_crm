<div class="home-tab">

  <nav class="nav justify-content-center fixed-navbar">
    <a class="nav-link active" aria-current="page" href="#">Assigned Tasks</a>
    <a class="nav-link" href="#">Surveyed Poles</a>
    <a class="nav-link" href="#">Installed Poles</a>
    <a class="nav-link" href="#">Rejected Tasks</a>

  </nav>

  <table id="assignedTasksTable" class="table-striped table-bordered table-sm table">
    <thead>
      <tr>
        <th>#</th>
        <th>Site</th>
        <th>Engineer</th>
        <th>Installer</th>
        <th>Wards</th>
        <th>No. of Poles</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($assignedTasks as $task)
        <tr>
          <td>{{ $task->id }}</td>
          <td>
            {{ collect([$task->site->panchayat, $task->site->block, $task->site->district])->filter()->implode(", ") ?:
                "N/A" }}
          </td>
          <td>
            {{ collect([$task->engineer->firstName, $task->engineer->lastName])->filter()->implode(" ") ?:
                "N/A" }}
          </td>
          <td>{{ $task->site->vendor ?? "N/A" }}</td>
          <td>
            {{ $task->site->ward ? count(explode(",", $task->site->ward)) : "N/A" }}
          </td>
          <td>{{ $task->site->total_poles ?? "N/A" }}</td>
          <td>
            <!-- View Button -->
            <a href="{{ route("poles.show", $task->pole->id) }}" class="btn btn-icon btn-info" data-toggle="tooltip"
              title="View Details">
              <i class="mdi mdi-eye"></i>
            </a>

            <!-- Delete Button -->

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
      $('#assignedTasksTable').DataTable({
        dom: "<'row'<'col-sm-6 d-flex align-items-center'f><'col-sm-6 d-flex justify-content-end'B>>" +
          "<'row'<'col-sm-12'tr>>" +
          "<'row'<'col-sm-5'i><'col-sm-7'p>>",
        buttons: [{
            extend: 'excel',
            text: '<i class="mdi mdi-file-excel"></i>',
            className: 'btn btn-sm btn-success',
            titleAttr: 'Export to Excel' // Tooltip
          },
          {
            extend: 'pdf',
            text: '<i class="mdi mdi-file-pdf"></i>',
            className: 'btn btn-sm btn-danger',
            titleAttr: 'Export to PDF' // Tooltip
          },
          {
            extend: 'print',
            text: '<i class="mdi mdi-printer"></i>',
            className: 'btn btn-sm btn-info',
            titleAttr: 'Print Table' // Tooltip
          }
        ],
        paging: true,
        pageLength: 50, // Show 50 rows per page
        searching: true,
        ordering: true,
        responsive: true,
        language: {
          search: '',
          searchPlaceholder: 'Search Inventory'
        }
      });



      // Initialize tooltips
      $('[data-toggle="tooltip"]').tooltip();

      // Adjust search box alignment
      $('.dataTables_filter input').addClass('form-control form-control-sm');
    });
  </script>
@endpush
