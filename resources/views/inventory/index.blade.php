@extends("layouts.main")

@section("content")
  <div class="container p-2">
    <div class="d-flex justify-content-between my-5">
      <!-- Search box is added automatically by DataTables -->
      <div></div> <!-- Empty div to align with search box -->
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
        <form action="{{ route("inventory.import") }}" method="POST" enctype="multipart/form-data">
          @csrf
          <div class="input-group">
            <input type="file" name="file" class="form-control form-control-sm" required>
            <button type="submit" class="btn btn-sm btn-primary" data-toggle="tooltip" title="Import Inventory">
              <i class="mdi mdi-upload"></i> Import
            </button>
          </div>
        </form>
        <a href="{{ route("inventory.create") }}" class="btn btn-sm btn-primary m-1" data-toggle="tooltip"
          title="Inward Inventory">
          <i class="mdi mdi-plus-circle"></i>
        </a>
        <a href="{{ route("inventory.create") }}" class="btn btn-sm btn-secondary m-1" data-toggle="tooltip"
          title="Dispatch Inventory">
          <i class="mdi mdi-minus-circle"></i>
        </a>
      </div>
    </div>
    <table id="inventoryTable" class="table-striped table-bordered table-sm table">
      <thead>
        <tr>
          
          <th>Item Code</th>
          <th>Item name</th>
          <th>Manufacturer</th>
          <th>Model</th>
          <th>Serial Number</th>
          <th>HSN Code</th>
          <th>Unit</th>
          <!-- <th>In</th> -->
          <!-- <th>Out</th> -->
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($inventory as $member)
          <tr>
            
            <td>{{ $member->item_code }}</td>
            <td>{{ $member->item }}</td>
            <td>{{ $member->manufacturer }}</td>
            <td>{{ $member->model }}</td>
            <td>{{ $member->serial_number }}</td>
            <td>{{ $member->hsn }}</td>
            <td>{{ $member->unit }}</td>
            <!-- <td>{{ $member->created_at }}</td> -->
            <!-- <td>{{  }}</td> -->
            <td>
              <!-- View Button -->
              <a href="#modal{{ $member->id }}" data-bs-toggle="modal" class="btn btn-sm btn-info">Details</a>
              <!-- Edit Button -->
              
              <!-- Delete Button -->
              
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  <!-- Inventory Details Modal -->
  @foreach ($inventory as $item)
    <div class="modal fade" id="modal{{ $item->id }}" tabindex="-1" role="dialog">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Item Details: {{ $item->item_name }}</h5>
            <button type="button" class="close" data-bs-dismiss="modal">&times;</button>
          </div>
          <div class="modal-body">
            <p><strong>Item Code:</strong> {{ $item->item_code }}</p>
            <p><strong>Manufacturer:</strong> {{ $item->manufacturer }}</p>
            <p><strong>Model:</strong> {{ $item->model }}</p>
            <p><strong>Unit:</strong> {{ $item->unit }}</p>
            <p><strong>Quantity:</strong> {{ $item->quantity }}</p>
            <p><strong>Rate:</strong> {{ $item->rate }}</p>
            <p><strong>Total Value:</strong> {{ number_format($item->quantity * $item->rate, 2) }}</p>
          </div>
        </div>
      </div>
    </div>
  @endforeach
@endsection

@push("scripts")
  <script>
    $(document).ready(function() {
      $('#inventoryTable').DataTable({
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
