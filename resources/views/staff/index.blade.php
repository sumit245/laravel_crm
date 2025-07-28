@extends("layouts.main")

@section("content")
  <div class="container p-2">

   <!-- Header row: Title + Add button -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h3 class="fw-bold mb-0">Staff Management</h3>

      <a href="{{ route('staff.create') }}" class="btn btn-sm btn-primary" data-toggle="tooltip" title="Add New Staff">
        <i class="mdi mdi-plus-circle"></i>
      </a>
    </div>

    <!-- Import Staff form -->
    <form action="{{ route('import.staff') }}" method="POST" enctype="multipart/form-data">
      @csrf
      <div class="input-group input-group-sm mb-3" style="max-width: 600px;">
        <input type="file" name="file" class="form-control form-height form-control-sm" required>
        <button type="submit" class="btn btn-sm btn-primary" title="Import Staff">
          <i class="mdi mdi-upload"></i> Import Staff
        </button>
      </div>
    </form>

    <table id="staffTable" class="table-striped table-bordered table">
      <thead>
        <tr>
          <th>#</th>
          <th>Name</th>
          <th>Email</th>
          <th>Address</th>
          <th>Role</th>
          <th>Phone</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($staff as $member)
          <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $member->firstName }} {{ $member->lastName }}</td>
            <td>{{ $member->email }}</td>
            <td>{{ $member->address }}</td>
            @php
              $roles = [
                  1 => "Site Engineer",
                  2 => "Project Manager",
                  4 => "Store Incharge",
              ];
            @endphp
            <td>
              {{ $roles[$member->role] ?? "Coordinator" }}
            </td>
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
            className: 'btn btn-icon  btn-success',
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
            width: "4%"
          },
          {
            targets: 1,
            width: "16%"
          },
          {
            targets: 2,
            width: "20%"
          },
          {
            targets: 3,
            width: "20%"
          },
          {
            targets: 4,
            width: "10%"
          },
          {
            targets: 5,
            width: "10%"
          },
          {
            targets: 6,
            width: "20%"
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

@push('styles')
<style>
  table.dataTable thead th,
  table.dataTable thead td,
  table.dataTable tfoot th,
  table.dataTable tfoot td {
      text-align: center;
  }

  .form-control:read-only,  .select2-container--default .select2-selection--single:read-only{
    background: none;
  }
   .select2-container--default .select2-selection--single:read-only{
    padding: 0;
   }
  .form-height{
    height: 100%;
  }
</style>
@endpush