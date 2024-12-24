<div>
  <div class="d-flex justify-content-between mb-4">
    <h5>Sites</h5>
    <div class="d-flex">
      @if ($errors->any())
        <div class="alert alert-danger">
          <ul>
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif
      <form action="{{ route("sites.import", $project->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="input-group">
          <input type="file" name="file" class="form-control form-control-sm" required>
          <button type="submit" class="btn btn-sm btn-primary" data-toggle="tooltip" title="Import Inventory">
            <i class="mdi mdi-upload"></i> Import
          </button>
        </div>
      </form>
      <a href="{{ route("sites.create", $project->id) }}" class="btn btn-primary mx-1">Add Site</a>
    </div>
  </div>
  <table id="siteTable" class="table-striped table-bordered table-sm table">
    <thead>
      <tr>
        <th>#</th>
        <th>Site Name</th>
        <th>Address</th>
        <th>Vendor</th>
        <th>Site Contact</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($sites as $member)
        <tr>
          <td>{{ $loop->iteration }}</td>
          <td>{{ $member->site_name }}</td>
          <td>
            {{ $member->location }},
            {{ optional($member->districtRelation)->name ?? "Unknown District" }},
            {{ optional($member->stateRelation)->name ?? "Unknown State" }}
          </td>
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
