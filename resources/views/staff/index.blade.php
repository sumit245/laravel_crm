@extends("layouts.main")

@section("content")
  <div class="container p-2">
    <div class="d-flex justify-content-between mb-3">
      <div></div>
      <a href="{{ route("staff.create") }}" class="btn btn-sm btn-primary" data-toggle="tooltip" title="Add New Staff">
        <i class="mdi mdi-plus-circle"></i>
      </a>
    </div>
    <table id="staffTable" class="table-striped table-bordered table">
      <thead>
        <tr>
          <th>#</th>
          <th>Name</th>
          <th>Email</th>
          <th>First Name</th>
          <th>Last Name</th>
          <th>Address</th>
          <th>Phone</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($staff as $member)
          <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $member->name }}</td>
            <td>{{ $member->email }}</td>
            <td>{{ $member->firstName }}</td>
            <td>{{ $member->lastName }}</td>
            <td>{{ $member->address }}</td>
            <td>{{ $member->contactNo }}</td>
            <td>
              <a href="{{ route("staff.show", $member->id) }}" class="btn btn-icon btn-info" data-toggle="tooltip"
                title="View Details">
                <i class="mdi mdi-eye"></i>
              </a>
              <a href="{{ route("staff.edit", $member->id) }}" class="btn btn-icon btn-warning" data-toggle="tooltip"
                title="Edit Staff">
                <i class="mdi mdi-pencil"></i>
              </a>
              <button type="submit" class="btn btn-icon btn-danger delete-staff" data-toggle="tooltip"
                title="Delete Staff" data-id="{{ $member->id }}" data-name="{{ $member->name }}"
                data-url="{{ route("staff.destroy", $member->id) }}">
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
            width: "10%"
          },
          {
            targets: 4,
            width: "10%"
          },
          {
            targets: 5,
            width: "20%"
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
