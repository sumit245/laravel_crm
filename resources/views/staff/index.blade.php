@extends("layouts.main")

@section("content")
  <div class="content-wrapper">
    <div class="row">
      <div class="col-md-12 grid-margin">
        <div class="card">
          <div class="card-body">
            <h4 class="card-title">Staff Performance</h4>

            <!-- Filters (Optional) -->
            <div class="mb-3">
              <input type="text" id="searchInput" class="form-control" placeholder="Search staff by name...">
            </div>

            <table class="table-striped table" id="staffTable">
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Tasks Assigned</th>
                  <th>Pending</th>
                  <th>In Progress</th>
                  <th>Completed</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($staff as $staff)
                  <tr>
                    <td>{{ $staff->firstName }} {{ $staff->lastName }}</td>
                    <td>{{ $staff->totalTasks }}</td>
                    <td class="text-warning">{{ $staff->pendingTasks }}</td>
                    <td class="text-primary">{{ $staff->inProgressTasks }}</td>
                    <td class="text-success">{{ $staff->completedTasks }}</td>
                    <td>
                      <a href="{{ route("staff.show", $staff->id) }}" class="btn btn-sm btn-info">View</a>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

@push("scripts")
  <script>
    $(document).ready(function() {
      $('#staffTable').DataTable({
        dom: "<'row'<'col-sm-6 d-flex align-items-center'f><'col-sm-6 d-flex justify-content-end'B>>" +
          "<'row'<'col-sm-12'tr>>" +
          "<'row my-4'<'col-sm-5'i><'col-sm-7'p>>",
        buttons: [{
            extend: 'excel',
            text: '<i class="mdi mdi-file-excel text-light"></i>',
            className: 'btn btn-icon btn-dark',
            titleAttr: 'Export to Excel'
          },
          {
            extend: 'pdf',
            text: '<i class="mdi mdi-file-pdf"></i>',
            className: 'btn btn-icon btn-danger',
            titleAttr: 'Export to PDF'
          },
          {
            extend: 'print',
            text: '<i class="mdi mdi-printer"></i>',
            className: 'btn btn-icon btn-info',
            titleAttr: 'Print Table'
          }
        ],
        paging: true,
        pageLength: 50,
        searching: true,
        ordering: true,
        responsive: true,
        autoWidth: false,
        columnDefs: [{
            targets: 0,
            width: "5%"
          },
          {
            targets: 1,
            width: "10%"
          },
          {
            targets: 2,
            width: "10%"
          },
          {
            targets: 3,
            width: "14%"
          },
          {
            targets: 4,
            width: "16%"
          },
          {
            targets: 5,
            width: "10%"
          },
          {
            targets: 6,
            width: "10%"
          },
          {
            targets: 7,
            width: "10%"
          },
        ],
        language: {
          search: '',
          searchPlaceholder: 'Search Staff...'
        }
      });

      $('[data-toggle="tooltip"]').tooltip();
      $('.dataTables_filter input').addClass('form-control form-control-sm');

      $('.delete-staff').on('click', function() {
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
            $.ajax({
              url: deleteUrl,
              type: 'POST',
              data: {
                _method: 'DELETE',
                _token: "{{ csrf_token() }}",
              },
              success: function(response) {
                Swal.fire(
                  'Deleted!',
                  `${staffName} has been deleted.`,
                  'success'
                );
                $(`button[data-id="${staffId}"]`).closest('tr').remove();
              },
              error: function(xhr) {
                Swal.fire(
                  'Error!',
                  'There was an error deleting the staff member. Please try again.',
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
