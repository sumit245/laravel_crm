@extends("layouts.main")

@section("content")
  <!-- Combined Row: Dashboard Cards and Export Buttons -->
  <div class="row mb-4 ms-0 mt-2">
    <!-- Dashboard Cards -->
    <div class="col-md-3">
      <div class="card bg-primary text-white shadow-sm">
        <div class="card-body">
          <h5 class="card-title text-white">Applied Amount</h5>
          <h3>₹125,000</h3>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card bg-success text-white shadow-sm">
        <div class="card-body">
          <h5 class="card-title text-white">Disbursed Amount</h5>
          <h3>₹100,000</h3>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card bg-danger text-white shadow-sm">
        <div class="card-body">
          <h5 class="card-title text-white">Rejected Amount</h5>
          <h3>₹25,000</h3>
        </div>
      </div>
    </div>

    <!-- Export Buttons -->
    <!-- <div class="col-md-3 d-flex justify-content-end align-items-end gap-2">
          <button class="btn btn-success btn-sm custom-btn" id="exportExcel">
              <i class="mdi mdi-file-excel fs-4"></i>
          </button>
          <button class="btn btn-danger btn-sm custom-btn" id="exportPDF">
              <i class="mdi mdi-file-pdf fs-4"></i>
          </button>
          <button class="btn btn-info btn-sm custom-btn me-2" id="printTable">
              <i class="mdi mdi-printer fs-4"></i>
          </button>
      </div> -->
  </div>

  <!-- Filters and Search -->
  <div class="row mb-4 me-0 ms-0">
    <div class="col-md-3">
      <input type="text" class="form-control shadow-sm" placeholder="Search by Request ID, User, or Date">
    </div>
    <div class="col-md-3">
      <select class="form-select shadow-sm" aria-label="Filter by Users">
        <option selected>Users</option>
        <option value="1">Ava Martinez</option>
        <option value="2">Ethan Clark</option>
        <option value="3">Sophia Chen</option>
      </select>
    </div>
    <div class="col-md-3">
      <select class="form-select shadow-sm" aria-label="Filter by Locations">
        <option selected>Most Frequent Locations</option>
        <option value="1">New York, NY</option>
        <option value="2">San Francisco, CA</option>
        <option value="3">Chicago, IL</option>
      </select>
    </div>
    <div class="col-md-3">
      <select class="form-select shadow-sm" aria-label="Filter by Dates">
        <option selected>Dates</option>
        <option value="1">This Month</option>
        <option value="2">Last Month</option>
        <option value="3">Custom Range</option>
      </select>
    </div>
  </div>

  <!-- Approve/Reject Actions Button -->
  <div class="d-flex justify-content-end mb-3">
    <button id="approveBtn" class="btn btn-body-tertiary bg-secondary me-2" style="display:none;">
      <i class="mdi mdi-check-circle-outline text-success fs-5 me-2 text-black"></i>Approve
    </button>
    <button id="rejectBtn" class="btn btn-danger me-2" style="display:none;">
      <i class="mdi mdi-close-circle-outline fs-5 me-2"></i>Reject
    </button>
  </div>

  <!-- DataTable -->
  <div class="card border-0 shadow-sm">
    <div class="card-header bg-primary text-white">
      <h4 class="mb-0">
        <i class="bi bi-table me-2"></i>Convenience Request Summary
      </h4>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table id="convenienceTable" class="table-bordered table-striped table-sm mt-4 table table">
          <thead class="table-white">
            <tr>
              <th><input type="checkbox" id="selectAll" /></th>
              <th>Name</th>
              <th>Employee Id</th>
              <th>Department</th>
              <th>Objective</th>
              <th>Amount</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            @php
              $data = [
                  [
                      "name" => "John Doe",
                      "id" => "EMP123",
                      "dept" => "Sales",
                      "obj" => "Client Acquisition",
                      "amt" => "₹2,500",
                  ],
                  [
                      "name" => "Ava Martinez",
                      "id" => "EMP124",
                      "dept" => "Marketing",
                      "obj" => "Product Launch",
                      "amt" => "₹3,200",
                  ],
                  [
                      "name" => "Ethan Clark",
                      "id" => "EMP125",
                      "dept" => "Engineering",
                      "obj" => "Tech Conference",
                      "amt" => "₹4,000",
                  ],
                  [
                      "name" => "Sophia Chen",
                      "id" => "EMP126",
                      "dept" => "Operations",
                      "obj" => "Logistics Planning",
                      "amt" => "₹1,800",
                  ],
                  [
                      "name" => "David Lee",
                      "id" => "EMP127",
                      "dept" => "Sales",
                      "obj" => "Client Follow-up",
                      "amt" => "₹2,200",
                  ],
                  [
                      "name" => "Michael Johnson",
                      "id" => "EMP128",
                      "dept" => "HR",
                      "obj" => "Employee Wellness",
                      "amt" => "₹2,700",
                  ],
                  [
                      "name" => "Olivia Brown",
                      "id" => "EMP129",
                      "dept" => "Finance",
                      "obj" => "Budget Review",
                      "amt" => "₹3,000",
                  ],
                  [
                      "name" => "Lucas White",
                      "id" => "EMP130",
                      "dept" => "Marketing",
                      "obj" => "Ad Campaign",
                      "amt" => "₹4,900",
                  ],
                  [
                      "name" => "Emma Davis",
                      "id" => "EMP131",
                      "dept" => "Operations",
                      "obj" => "Inventory Management",
                      "amt" => "₹1,950",
                  ],
              ];
            @endphp

            @foreach ($data as $row)
              <tr>
                <td><input type="checkbox" class="checkboxItem" /></td>
                <td>{{ $row["name"] }}</td>
                <td>{{ $row["id"] }}</td>
                <td>{{ $row["dept"] }}</td>
                <td>{{ $row["obj"] }}</td>
                <td>{{ $row["amt"] }}</td>
                <td><span class="badge bg-warning text-dark">Pending</span></td>
                <td>
                  <a href="{{ route("convenience.details") }}" class="btn btn-sm btn-info" data-toggle="tooltip"
                    title="View Details">
                    <i class="mdi mdi-eye"></i>
                  </a>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Modal -->
  <div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="detailsModalLabel">Employee Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <!-- Modal content goes here if needed -->
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
@endsection

@push("scripts")
  <script>
    $(document).ready(function() {
      $('#convenienceTable').DataTable({
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
            titleAttr: 'Export to Excel'
          },
          {
            extend: 'pdf',
            text: '<i class="mdi mdi-file-pdf"></i>',
            className: 'btn btn-sm btn-danger',
            titleAttr: 'Export to PDF'
          },
          {
            extend: 'print',
            text: '<i class="mdi mdi-printer"></i>',
            className: 'btn btn-sm btn-info',
            titleAttr: 'Print Table'
          }
        ],
        paging: true,
        pageLength: 50,
        searching: true,
        ordering: true,
        responsive: true,
        order: [
          [1, 'asc']
        ],
        language: {
          search: '',
          searchPlaceholder: 'Search Requests'
        }
      });

      $('.dataTables_filter input').addClass('form-control form-control-sm');
      $('[data-toggle="tooltip"]').tooltip();
    });

    // Approve/Reject checkbox logic
    document.getElementById('selectAll').addEventListener('change', function(e) {
      document.querySelectorAll('.checkboxItem').forEach(cb => cb.checked = e.target.checked);
      toggleApproveRejectButtons();
    });

    document.querySelectorAll('.checkboxItem').forEach(cb => {
      cb.addEventListener('change', toggleApproveRejectButtons);
    });

    function toggleApproveRejectButtons() {
      const selected = document.querySelectorAll('.checkboxItem:checked').length;
      document.getElementById('approveBtn').style.display = selected ? 'inline-block' : 'none';
      document.getElementById('rejectBtn').style.display = selected ? 'inline-block' : 'none';
    }
  </script>
@endpush

@push("styles")
  <style>
    .table-responsive {
      overflow: hidden;
    }

    #convenienceTable {
      width: 100% !important;
      table-layout: auto;
    }

    .table.mt-4 {
      margin-top: 1.5rem !important;
      margin-bottom: 0 !important;
    }
  </style>
@endpush
