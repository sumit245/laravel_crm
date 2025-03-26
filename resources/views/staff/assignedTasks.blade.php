<div class="home-tab">
  
  <!--
  <div class="d-inline-block align-items-center flex-row">
    <ul class="nav nav-tabs" role="tablist">
      <li class="nav-item" role="presentation">
        <a class="nav-link active" id="home-tab" data-bs-toggle="tab" href="#overview" role="tab"
          aria-controls="overview" aria-selected="true">Assigned Panchayats</a>
      </li>
      <li class="nav-item" role="presentation">
        <a class="nav-link" id="profile-tab" data-bs-toggle="tab" href="#audiences" role="tab"
          aria-controls="audiences" aria-selected="false">Surveyed Poles</a>
      </li>
      <li class="nav-item" role="presentation">
        <a class="nav-link" id="contact-tab" data-bs-toggle="tab" href="#demographics" role="tab"
          aria-controls="demographics" aria-selected="false">Installed Poles</a>
      </li>
    </ul>
  </div>
  -->
  <nav class="nav justify-content-center fixed-navbar">
    <a class="nav-link active" aria-current="page" href="#">Assigned Tasks</a>
    <a class="nav-link" href="#">Completed Tasks</a>
    <a class="nav-link" href="#">Pending Tasks</a>
    <a class="nav-link" href="#">Rejected Tasks</a>
    
  </nav>

    <table id="inventoryTable" class="table-striped table-bordered table-sm table">
      <thead>
        <tr>
          <th>#</th>
          <th>Panchayat</th>
          <th>Block</th>
          <th>District</th>
          <th>Engineer</th>
          <th>Installer</th>
          <th>Wards</th>
          <th>No. of Poles</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($assignedTasks as $task )
          <tr>
            <td>{{ $task->id }}</td>
            <td>{{ $task->sites->panchayat ?? "N/A" }}</td>
            <td>{{ $task->site->Block ?? "N/A"}}</td>
            <td>{{ $task->site->District ?? "N/A" }}</td>
            <td>{{ $task->site->Engineer ?? "N/A"}}</td>
            <td>{{ $task->site->Installer ?? "N/A" }}</td>
            <td>{{ $task->site->wards ?? "N/A" }}</td>
            <td>{{ $task->site->number_of_poles ?? "N/A" }}</td>
            <td>
              <!-- View Button -->
              <a href="{{-- route("inventory.show", $member->id) --}}" class="btn btn-icon btn-info" data-toggle="tooltip"
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

  </div>1
@push("scripts")
  <script>
   
    $(document).ready(function() {
      $('#inventoryTable').DataTable({
        dom: "<'row'<'col-sm-6 d-flex align-items-center'f><'col-sm-6 d-flex justify-content-end'B>>" +
          "<'row'<'col-sm-12'tr>>" +
          "<'row'<'col-sm-5'i><'col-sm-7'p>>",
        buttons: [
          // {
          //   extend: 'excel',
          //   text: '<i class="mdi mdi-file-excel"></i>',
          //   className: 'btn btn-sm btn-success',
          //   titleAttr: 'Export to Excel' // Tooltip
          // },
          // {
          //   extend: 'pdf',
          //   text: '<i class="mdi mdi-file-pdf"></i>',
          //   className: 'btn btn-sm btn-danger',
          //   titleAttr: 'Export to PDF' // Tooltip
          // },
          // {
          //   extend: 'print',
          //   text: '<i class="mdi mdi-printer"></i>',
          //   className: 'btn btn-sm btn-info',
          //   titleAttr: 'Print Table' // Tooltip
          // }
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
    
    $(document).ready(function() {
      // Handle delete button click
      $('.delete-item').on('click', function() {
        let inventoryId = $(this).data('id');
        let inventoryName = $(this).data('name');
        let deleteUrl = $(this).data('url');

        Swal.fire({
          title: `Are you sure?`,
          text: `You are about to delete ${inventoryName}. This action cannot be undone.`,
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
                  `${inventoryName} has been deleted.`,
                  'success'
                );
                // Remove the row from the table
                $(`button[data-id="${inventoryId}"]`).closest('tr').remove();
              },
              error: function(xhr) {
                Swal.fire(
                  'Error!',
                  'There was an error deleting the inventory. Please try again.',
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
