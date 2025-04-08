<div class="row">
  <div class="col-12">
    <!-- Date filter -->
    <div class="d-flex justify-content-between align-items-center mb-3 p-2">
      <h3 class="fw-bold"></h3>
      <select class="form-select" style="width:9.375rem;" name="date_filter" id="taskFilter" onchange="filterTasks()">
        <option value="today" {{ request("date_filter") == "today" ? "selected" : "" }}>Today</option>
        <option value="this_week" {{ request("date_filter") == "this_week" ? "selected" : "" }}>This Week</option>
        <option value="this_month" {{ request("date_filter") == "this_month" ? "selected" : "" }}>This Month</option>
        <option value="all_time" {{ request("date_filter") == "all_time" ? "selected" : "" }}>All Time</option>
        <option value="custom" {{ request("date_filter") == "custom" ? "selected" : "" }}>Custom Range</option>
      </select>
    </div>
    <!-- Custom Date Range Modal -->
    <div class="modal fade" id="customDateModal" tabindex="-1" aria-labelledby="customDateModalLabel"
      aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="customDateModalLabel">Select Custom Date Range</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form id="customDateForm" action="{{ route("dashboard") }}" method="GET"
              onsubmit="return validateDateRange()">
              <input type="hidden" name="date_filter" value="custom">
              <div class="mb-3">
                <label for="start_date" class="form-label">Start Date</label>
                <input type="date" class="form-control" id="start_date" name="start_date"
                  value="{{ request("start_date", date("Y-m-d", strtotime("-30 days"))) }}"
                  onchange="updateEndDateMin()">
              </div>
              <div class="mb-3">
                <label for="end_date" class="form-label">End Date</label>
                <input type="date" class="form-control" id="end_date" name="end_date"
                  value="{{ request("end_date", date("Y-m-d")) }}">
                <div id="dateError" class="invalid-feedback"></div>
              </div>
              <div class="d-flex justify-content-end">
                <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Apply Filter</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    {{-- <div class="p-2"> --}}
    <table id="surveyedPolesTable" class="table-striped table-bordered table-sm table">
      <thead>
        <tr>
          <th>#</th>
          <th>Ward Name</th>
          <th>Complete Pole Numbers</th>
          <th>Location</th>
          <th>Beneficiary_Contact</th>
          <th>Remarks</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($surveyedPoles as $survey)
          <tr>
            <td>{{ $survey->id }}</td>
            <td>{{ $survey->ward_name ?? "N/A" }}</td>
            <td>{{ $survey->complete_pole_number ?? "N/A" }}</td>
            <td>{{ $survey->lat && $survey->lng ? $survey->lat . ", " . $survey->lng : "N/A" }}</td>
            <td>{{ $survey->beneficiary_contact ?? "N/A" }}</td>
            <td>{{ $survey->remarks ?? "N/A" }}</td>
            <td>
              <!-- View Button -->
              <a href="{{-- route("inventory.show", $member->id) --}}" class="btn btn-icon btn-info" data-toggle="tooltip" title="View Details">
                <i class="mdi mdi-eye"></i>
              </a>

              <!-- Delete Button -->

            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
    {{-- </div> --}}
  </div>

  @push("scripts")
    <script>
      $(document).ready(function() {
        $('#surveyedPolesTable').DataTable({
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
    </script>
  @endpush
