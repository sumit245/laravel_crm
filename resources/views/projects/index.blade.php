@extends("layouts.main")

@section("content")
  <div class="container p-2">
    <div class="d-flex justify-content-between mb-3">
      <!-- Search box is added automatically by DataTables -->
      <div></div> <!-- Empty div to align with search box -->
      <a href="{{ route("projects.create") }}" class="btn btn-sm btn-primary" data-toggle="tooltip" title="Add New Project">
        <i class="mdi mdi-plus-circle"></i>
      </a>
    </div>
    <table id="projectsTable" class="table-striped table-bordered table-sm tab le mt-5">
      <thead>
        <tr>
          <th>#</th>
          <th>Project Name</th>
          <th>Work order number</th>
          <th>Start Date</th>
          <th>Order Value</th>
          <th>Actions</th>
        </tr> 
      </thead>
      <tbody>
        @foreach ($projects as $member)
          <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $member->project_name }}</td>
            <td>{{ $member->work_order_number }}</td>
            <td>{{ $member->start_date }}</td>
            <td>{{ $member->rate }}</td>
            <td>
              <!-- View Button -->
              <a href="{{ route("projects.show", $member->id) }}" class="btn btn-icon btn-info" data-toggle="tooltip"
                title="View Details">
                <i class="mdi mdi-eye"></i>
              </a>
              <!-- Edit Button -->
              <a href="{{ route("projects.edit", $member->id) }}" class="btn btn-icon btn-warning" data-toggle="tooltip"
                title="Edit Project">
                <i class="mdi mdi-pencil"></i>
              </a>
              <!-- Delete Button -->
              <button type="submit" class="btn btn-icon btn-danger delete-project" data-toggle="tooltip"
                title="Delete Project" data-id="{{ $member->id }}" data-name="{{ $member->project_name }}"
                data-url="{{ route("projects.destroy", $member->id) }}">
                <i class="mdi mdi-delete"></i>
              </button>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
@endsection

@push("scripts")
  <script>
    $(document).ready(function() {
      $('#projectsTable').DataTable({
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
          searchPlaceholder: 'Search Projects...'
        }
      });

      // Initialize tooltips
      $('[data-toggle="tooltip"]').tooltip();

      // Adjust search box alignment
      $('.dataTables_filter input').addClass('form-control form-control-sm');
    });
    $(document).ready(function() {
      // Handle delete button click
      $('.delete-project').on('click', function() {
        let staffId = $(this).data('id');
        let staffName = $(this).data('name');
        let deleteUrl = $(this).data('url');

        Swal.fire({
          title: `Are you sure?`,
          text: `You are about to delete ${staffName}. This action cannot be undone.`,
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#d33',
          cancelButtonColor: '#3085d6',
          confirmButtonText: 'Yes, delete it!',
          cancelButtonText: 'Cancel',
        }).then((result) => {
          if (result.isConfirmed) {
            // Perform deletion via AJAX
            $.ajax({
              url: deleteUrl,
              type: 'POST',
              data: {
                _method: 'DELETE', // Laravel requires this for delete requests
                _token: "{{ csrf_token() }}", // CSRF token for security
              },
              success: function(response) {
                Swal.fire(
                  'Deleted!',
                  `${staffName} has been deleted.`,
                  'success'
                );
                // Remove the row from the table
                $(`button[data-id="${staffId}"]`).closest('tr').remove();
              },
              error: function(xhr) {
                Swal.fire(
                  'Error!',
                  'There was an error deleting the project. Please try again.',
                  'error'
                );
              }
            });
          }
        });
      });
    });
  </script>
@endpush
