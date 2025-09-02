@extends("layouts.main")

@section("content")
  <div class="container-fluid p-3">
    <h3 class="fw-bold mt-2">Installed Lights</h3>
    <p>Total Installed Lights: <strong>{{ $totalInstalled }}</strong></p>

    <x-data-table id="installedPole" class="table-striped table">
      <x-slot:thead>
        <tr>
          <th data-select="true">
            <input type="checkbox" id="selectAll" />
          </th>
          <th>Pole Number</th>
          <th>IMEI</th>
          <th>Sim Number</th>
          <th>Battery</th>
          <th>Panel</th>
          <th>Bill Raised</th>
          <th>RMS</th>
          <th>Actions</th>
        </tr>
      </x-slot:thead>
      <x-slot:tbody>
        @foreach ($poles as $pole)
          <tr>
            <td>
              <input type="checkbox" id="selectAll" />
            </td>
            <td onclick="locateOnMap({{ $pole->lat }}, {{ $pole->lng }})" style="cursor:pointer;"> <span class="text-primary">{{ $pole->complete_pole_number ?? "N/A" }}</span></td>
            <td>{{ $pole->luminary_qr ?? "N/A" }}</td>
            <td>{{ $pole->sim_number ?? "N/A" }}</td>
            <td>{{ $pole->battery_qr ?? "N/A" }}</td>
            <td>{{ $pole->panel_qr ?? "N/A" }}</td>
            <td>0</td>
            <td>{{ $pole->rms_status ?? "N/A" }}</td>
            <td>
              <a href="{{ route("poles.show", $pole->id) }}" class="btn btn-icon btn-info" data-toggle="tooltip"
                title="View Details">
                <i class="mdi mdi-eye"></i>
              </a>

              <a href="{{ route("poles.edit", $pole->id) }}" class="btn btn-icon btn-warning">
                <i class="mdi mdi-pencil"></i>
              </a>

              <button type="button" class="btn btn-icon btn-danger delete-pole-btn" data-toggle="tooltip"
                title="Delete Pole"
                data-id="{{ $pole->id }}"
                data-name="{{ $pole->complete_pole_number ?? 'this pole' }}"
                data-url="{{ route('poles.destroy', $pole->id) }}">
                <i class="mdi mdi-delete"></i>
              </button>
            </td>
          </tr>
        @endforeach
      </x-slot:tbody>
    </x-data-table>
  </div>
@endsection

@push("scripts")
  <script>
    function locateOnMap(lat, lng) {
      if (lat && lng) {
        // Using a more standard Google Maps URL
        const url = `https://www.google.com/maps?q=${lat},${lng}`;
        window.open(url, '_blank');
      } else {
        alert('Location coordinates not available.');
      }
    }

    $(document).ready(function() {
      $('#installedPole').DataTable({
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
        columnDefs: [ /* ... your column defs ... */ ],
        language: {
          search: '',
          searchPlaceholder: 'Search...'
        }
      });

      $('[data-toggle="tooltip"]').tooltip();
      $('.dataTables_filter input').addClass('form-control form-control-sm');

      // --- MODIFIED JAVASCRIPT FOR DELETE CONFIRMATION ---
      // We target the new class '.delete-pole-btn'
      $('.delete-pole-btn').on('click', function() {
        // Get the data from the button
        let poleId = $(this).data('id');
        let poleName = $(this).data('name');
        let deleteUrl = $(this).data('url');
        
        // Show the confirmation dialog
        Swal.fire({
          title: `Are you sure?`,
          text: `You are about to delete pole "${poleName}". This action cannot be undone.`,
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#d33',
          cancelButtonColor: '#3085d6',
          confirmButtonText: 'Yes, delete it!',
          cancelButtonText: 'Cancel',
        }).then((result) => {
          // If the user confirms
          if (result.isConfirmed) {
            // We make the AJAX call to the delete route
            $.ajax({
              url: deleteUrl,
              type: 'POST',
              data: {
                _method: 'DELETE', // Laravel's method spoofing
                _token: "{{ csrf_token() }}",
              },
              success: function(response) {
                Swal.fire(
                  'Deleted!',
                  `Pole "${poleName}" has been deleted.`,
                  'success'
                );
                // Remove the table row with a fade-out effect for better UX
                $(`.delete-pole-btn[data-id="${poleId}"]`).closest('tr').fadeOut(500, function() {
                  $(this).remove();
                });
              },
              error: function(xhr) {
                Swal.fire(
                  'Error!',
                  'There was an error deleting the pole. Please try again.',
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

@push("styles")
    <style>
      #installedPole{
        margin-top: 15px;
      }
    </style>
@endpush