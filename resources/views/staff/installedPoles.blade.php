<table id="installedPolesTable" class="table-striped table-bordered table-sm table mt-4">
  <thead>
    <tr>
      <th data-select="true">
        <input type="checkbox" id="selectAll" />
      </th>
      <th>Pole Number</th>
      <th>IMEI</th>
      <th>Sim Number</th>
      <th>Battery</th>
      <th>Panel</th>
      <th>Location</th>
      <th>RMS</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    @foreach ($installedPoles as $survey)
      <tr>
      <td>
    <input type="checkbox" name="selected_tasks[]" value="{{ $survey->id }}" class="task-checkbox" />
  </td>
        <td>{{ $survey->complete_pole_number ?? "N/A" }}</td>
        <td>{{ $survey->luminary_qr ?? "N/A" }}</td>
        <td>{{ $survey->sim_number ?? "N/A" }}</td>
        <td>{{ $survey->battery_qr ?? "N/A" }}</td>
        <td>{{ $survey->module_qr ?? "N/A" }}</td>
        <td onclick="locateOnMap({{ $survey->lat }}, {{ $survey->lng }})" style="cursor:pointer;"></td>
            <span class="text-primary">View Location</span>
            <td>{{ $survey->rms_status ?? "N/A" }}</td>
        <td>
          <!-- View Button -->
          <a href="{{ route("poles.show", $survey->id) }}" class="btn btn-icon btn-info" data-toggle="tooltip" title="View Details">
            <i class="mdi mdi-eye"></i>
          </a>

          <!-- Delete Button -->

        </td>
      </tr>
    @endforeach
  </tbody>
</table>

@push("scripts")
  <script>
    $(document).ready(function() {
      $('#installedPolesTable').DataTable({
        dom: "<'row d-flex align-items-center justify-content-between'" +
          "<'col-md-6 d-flex align-items-center' f>" +
          "<'col-md-6 d-flex justify-content-end' B>" +
          ">" +
          "<'row'<'col-sm-12'tr>>" +
          "<'row'<'col-sm-5 d-flex align-items-center' i><'col-sm-7 d-flex justify-content-start' p>>",
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

    // Custom date range validation
    function filterTasks() {
      let selectedFilter = document.getElementById('taskFilter').value;

      if (selectedFilter === 'custom') {
        // Show the custom date range modal
        var customDateModal = new bootstrap.Modal(document.getElementById('customDateModal'));
        customDateModal.show();
      } else {
        // Redirect with the selected filter
        let url = new URL(window.location.href);
        url.searchParams.set('date_filter', selectedFilter);

        // Remove any existing custom date parameters if they exist
        url.searchParams.delete('start_date');
        url.searchParams.delete('end_date');

        window.location.href = url.toString();
      }
    }
    // Update the minimum date for the end date input based on the start date
    function updateEndDateMin() {
      const startDate = document.getElementById('start_date').value;
      const endDateInput = document.getElementById('end_date');

      if (startDate) {
        endDateInput.min = startDate;

        // If current end date is before start date, update it
        if (endDateInput.value && endDateInput.value < startDate) {
          endDateInput.value = startDate;
        }
      }
    }

    function validateDateRange() {
      const startDate = document.getElementById('start_date').value;
      const endDate = document.getElementById('end_date').value;
      const dateError = document.getElementById('dateError');

      if (startDate && endDate && endDate < startDate) {
        dateError.textContent = 'End date cannot be earlier than start date';
        document.getElementById('end_date').classList.add('is-invalid');
        return false;
      }

      document.getElementById('end_date').classList.remove('is-invalid');
      return true;
    }
    // Check if we should show the date modal on page load
    document.addEventListener('DOMContentLoaded', function() {
      // Set up initial min date for end date
      updateEndDateMin();

      if (document.getElementById('taskFilter').value === 'custom') {
        // Only show if we're not already seeing results (i.e., no date params yet)
        if (!new URLSearchParams(window.location.search).has('start_date')) {
          var customDateModal = new bootstrap.Modal(document.getElementById('customDateModal'));
          customDateModal.show();
        }
      }
    });
    // Map script
    function locateOnMap(lat, lng) {
      if (lat && lng) {
        const url = `https://www.google.com/maps?q=${lat},${lng}`;
        window.open(url, '_blank');
      } else {
        alert('Location coordinates not available.');
      }
    }
  </script>
@endpush
