@extends("layouts.main")

@section("content")
  <div class="container p-2">
    <div class="d-flex justify-content-between mb-3">
      <!-- Search box is added automatically by DataTables -->
      <div></div> <!-- Empty div to align with search box -->
      <a href="{{ route("sites.create") }}" class="btn btn-sm btn-primary" data-toggle="tooltip" title="Add New Site">
        <i class="mdi mdi-plus-circle"></i>
      </a>
    </div>
    <table id="siteTable" class="table-striped table-bordered table-sm table">
      <thead>
        <tr>
          <th>#</th>
          <th>Breda Sl No</th>
          <th>Site Name</th>
          <th>Location</th>
          <th>City</th>
          <th>State</th>
          <th>Vendor</th>
          <th>Site Contact</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($sites as $member)
          <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $member->breda_sl_no }}</td>
            <td>{{ $member->site_name }}</td>
            <td>{{ $member->location }}</td>
            <td>{{ $member->districtRelation->name ?? "N/A" }}</td>
            <td>{{ $member->stateRelation->name ?? "N/A" }}</td>
            <td>{{ $member->ic_vendor_name }}</td>
            <td>{{ $member->contact_no }}</td>
            <td>
              <!-- View Button -->
              <a href="{{ route("sites.show", $member->id) }}" class="btn btn-icon btn-info" data-toggle="tooltip"
                title="View Details">
                <i class="mdi mdi-eye"></i>
              </a>
              <!-- Edit Button -->
              <a href="{{ route("sites.edit", $member->id) }}" class="btn btn-icon btn-warning" data-toggle="tooltip"
                title="Edit Site">
                <i class="mdi mdi-pencil"></i>
              </a>
              <!-- Delete Button -->
              <button type="submit" class="btn btn-icon btn-danger delete-site" data-toggle="tooltip" title="Delete Site"
                data-id="{{ $member->id }}" data-name="{{ $member->project_name }}"
                data-url="{{ route("sites.destroy", $member->id) }}">
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
      $('#siteTable').DataTable({
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
          searchPlaceholder: 'Search Sites...'
        }
      });

      // Initialize tooltips
      $('[data-toggle="tooltip"]').tooltip();

      // Adjust search box alignment
      $('.dataTables_filter input').addClass('form-control form-control-sm');
    });
    $(document).ready(function() {
      // Handle delete button click
      $('.delete-site').on('click', function() {
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
              type: 'DELETE',
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
