@extends("layouts.main") @section("content") <div class="container">
    <h4>View Inventory for Store: {{ $storeName }} (Project ID: {{ $projectId }})</h4>
    <h4>Incharge Name: {{ $inchargeName }}</h4>
    <div class="row">
      <div class="col-sm-3 mb-2 mt-2">
        <div class="card bg-success">
          <div class="card-header">
            <div class="d-flex justify-content-between">
              <h3 class="card-title">Battery</h3>
              <i class="mdi mdi-battery"></i>
            </div>
          </div>
          <div class="card-body">
            <p>Total Quantity: <span>{{ $totalBattery }}</span></p>
            <p>Total Value</p>
            <p>Dispatched Quantity</p>
            <p>Dispatched Value</p>
          </div>
        </div>
      </div>
      <div class="col-sm-3 mb-2 mt-2">
        <div class="card bg-warning">
          <div class="card-header">
            <div class="d-flex justify-content-between">
              <h3 class="card-title">Luminary</h3>
              <i class="mdi mdi mdi-led-on"></i>
            </div>
          </div>
          <div class="card-body">
            <p>Total Quantity</p>
            <p>Total Value</p>
            <p>Dispatched Quantity</p>
            <p>Dispatched Value</p>
          </div>
        </div>
      </div>
      <div class="col-sm-3 mb-2 mt-2">
        <div class="card bg-primary">
          <div class="card-header">
            <div class="d-flex justify-content-between">
            <h3 class="card-title">Structure</h3>
            <i class="mdi mdi-sign-pole"></i>
            </div>
          </div>
          <div class="card-body">
            <p>Total Quantity</p><span></span>
            <p>Total Value</p>
            <p>Dispatched Quantity</p>
            <p>Dispatched Value</p>
          </div>
        </div>
      </div>
      <div class="col-sm-3 mb-2 mt-2">
        <div class="card bg-info">
          <div class="card-header">
            <div class="d-flex justify-content-between">
            <h3 class="card-title">Module</h3>
            <i class="mdi mdi-solar-panel"></i>
            </div>
          </div>
          <div class="card-body">
            <p>Total Quantity</p>
            <p>Total Value</p>
            <p>Dispatched Quantity</p>
            <p>Dispatched Value</p>
          </div>
        </div>
      </div>
      <div class="col-sm-3">
      </div>
      <div class="col-sm-3">
      </div>
      <div class="col-sm-3">
      </div>
    </div>
    <div class="mb-3">
      <a href="{{ url()->previous() }}" class="btn btn-secondary">Back</a>
      <button onclick="printTable()" class="btn btn-primary">Print Inventory</button>
      <button onclick="exportToCSV()" class="btn btn-success">Export Inventory</button>
    </div>
  </div>
  <!-- Commneted columns need to be added in the table -->
  @if ($inventory->isEmpty())
    <p>No inventory available for this store.</p>
  @else
    <table id="viewInventoryTable" :pageLength="50" class="table-striped table-bordered table-sm m-2 table">
      <thead>
        <tr>
          <th>#</th>
          @if ($projectType == 1)
            <th>Item Code</th>
            <th>Item Name</th>
            <!-- <th>Manufacturer</th> -->
            <th>Make</th>
            <th>Model</th>
            <th>Serial Number</th>
            <th>HSN Code</th>
            <th>Unit</th>
            {{-- <th>Store name</th> --}}
            <!-- <th>Rate</th> -->
            <!-- <th>Quantity</th> -->
            <!-- <th>Total Value</th> -->
            <!-- <th>Description</th> -->
            <!-- <th>Received Date</th>  -->
          @else
            <th>Category</th>
            <th>Sub Category</th>
            <th>Item Name</th>
            <th>Quantity</th>
            <th>Unit Price</th>
            <th>Total Value</th>
          @endif
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($inventory as $item)
          <tr>
            <td>{{ $loop->iteration }}</td>
            @if ($projectType == 1)
              <td>{{ $item->item_code }}</td>
              <td>{{ $item->item }}</td>
              <!-- <td>{{ $item->manufacturer }}</td> -->
              <td>{{ $item->make }}</td>
              <td>{{ $item->model }}</td>
              <td>{{ $item->serial_number }}</td>
              <td>{{ $item->hsn }}</td>
              <td>{{ $item->unit }}</td>
              {{-- <td>{{ $item->firstName }}</td> --}}
              <!-- <td>{{ $item->rate }}</td> -->
              <!-- <td>{{ $item->quantity }}</td> -->
              <!-- <td>{{ $item->total_value }}</td> -->
              <!-- <td>{{ $item->description }}</td> -->
              <!-- <td>{{ $item->received_date }}</td> -->
              <!-- Modal begins -->
              <!-- Modal begins -->
              <div class="modal fade" id="modal{{ $item->id }}" aria-hidden="true"
                aria-labelledby="exampleModalToggleLabel" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title" id="exampleModalToggleLabel"></h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      <!-- Row 1 -->
                      <div class="row">
                        <div class="col-4">
                          <h5><strong>Item Code</strong></h5>
                          <span>{{ $item->id }}</span>
                        </div>
                        <div class="col-4">
                          <h5><strong>Item Name</strong></h5>
                          <p>{{ $item->item }}</p>
                        </div>
                        <div class="col-4">
                          <h5><strong>Manufacturer</strong></h5>
                          <span>{{ $item->manufacturer }}</span>
                          <!-- <p class="manufacturer"></p> -->
                        </div>
                      </div>
                      <!-- Row 2 -->
                      <div class="row mt-3">
                        <div class="col-4">
                          <h5><strong>Model</strong></h5>
                          <span>{{ $item->model }}</span>
                        </div>
                        <div class="col-4">
                          <h5><strong>Serial Number</strong></h5>
                          <span>{{ $item->serial_number }}</span>
                        </div>
                        <div class="col-4">
                          <h5><strong>Make</strong></h5>
                          <span>{{ $item->make }}</span>
                        </div>
                      </div>
                      <!-- Row 3-->
                      <div class="row mt-3">
                        <div class="col-4">
                          <h5><strong>Rate</strong></h5>
                          <span>{{ $item->rate }}</span>
                        </div>
                        <div class="col-4">
                          <h5><strong>Quantity</strong></h5>
                          <span>{{ $item->quantity }}</span>
                        </div>
                        <div class="col-4">
                          <h5><strong>Total Value</strong></h5>
                          <span>{{ $item->total_value }}</span>
                        </div>

                      </div>
                      <!-- Row 4 -->
                      <div class="row mt-3">
                        <div class="col-4">
                          <h5><strong>HSN Code</strong></h5>
                          <span>
                            {{ $item->hsn }}
                          </span>
                        </div>
                        <div class="col-4">
                          <h5><strong>Description</strong></h5>
                          <span>{{ $item->description }}</span>
                        </div>
                        <div class="col-4">
                          <h5><strong>Unit</strong></h5>
                          <span>{{ $item->unit }}</span>
                        </div>
                      </div>
                      <!-- Row 5 -->
                      <div class="row mt-3">
                        <div class="col-6">
                          <h5><strong>Received Date</strong></h5>
                          <span>{{ $item->received_date }}</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              </div>
            @else
              <td>{{ $item->category }}</td>
              <td>{{ $item->sub_category }}</td>
              <td style="max-width:200px;word-wrap: break-word;white-space: normal;">{{ $item->productName }}</td>
              <td>{{ $item->initialQuantity }}</td>
              <td>{{ $item->rate }}</td>
              <td>{{ $item->total }}</td>
            @endif
            <td>
              <a href="#modal{{ $item->id }}" class="btn btn-info btn-icon eye-button" data-id="{{ $item->id }}"
                data-itemcode="{{ $item->item_code }}" data-itemname="{{ $item->item }}"
                data-manufacturer="{{ $item->manufacturer }}" data-make="{{ $item->make }}"
                data-model="{{ $item->model }}" data-serial="{{ $item->serial_number }}"
                data-hsn="{{ $item->hsn }}" data-unit="{{ $item->unit }}" data-rate="{{ $item->rate }}"
                data-quantity="{{ $item->quantity }}" data-totalvalue="{{ $item->total_value }}"
                data-description="{{ $item->description }}" data-receiveddate="{{ $item->received_date }}"
                data-bs-toggle="modal" data-bs-target="#modal{{ $item->id }}" data-toggle="tooltip"
                title="View Details">
                <i class="mdi mdi-eye"></i>
              </a>
              <a href="{{ route("inventory.editInventory", $item->id) }}" class="btn btn-warning btn-icon"
                data-toggle="tooltip" title="Edit Site">
                <i class="mdi mdi-pencil"></i>
              </a>
              <button class="btn btn-danger btn-icon delete-site" data-id="{{ $item->id }}"
                data-name="{{ $item->id }}">
                <i class="mdi mdi-delete"></i>
              </button>
            </td>
          </tr>
      </tbody>
  @endforeach
  </table>
  @endif
  <!-- Modal begins -->
  <!-- <a class="btn btn-primary" data-bs-toggle="modal" href="#exampleModalToggle" role="button">Open first modal</a> -->
  <!-- Modal ends -->
<script>
  function printTable() {
    const printContents = document.querySelector('table').outerHTML;
    const newWindow = window.open('', '_blank');
    newWindow.document.write(' < html > < head > < title > Print Inventory < /title> < /head> < body > ');
    newWindow.document.write(printContents);
    newWindow.document.write('</body> < /html>');
    newWindow.document.close();
    newWindow.print();
  }

  function exportToCSV() {
    const rows = [
      @if ($projectType == 1)
        ['Item Code', 'Item Name', 'Manufacturer', 'Make', 'Model', 'Serial Number', 'HSN Code', 'Unit', 'Rate',
          'Quantity', 'Total Value', 'Description', 'Received Date'
        ]
      @else
        ['Category', 'Sub Category', 'Item Name', 'Quantity', 'Unit Price', 'Total Value']
      @endif
    ];
    document.querySelectorAll('table tbody tr').forEach(row => {
      const rowData = [];
      row.querySelectorAll('td').forEach(cell => rowData.push(cell.innerText));
      rows.push(rowData);
    });
    const csvContent = rows.map(e => e.join(',')).join('\n');
    const blob = new Blob([csvContent], {
      type: 'text/csv;charset=utf-8;'
    });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.setAttribute('href', url);
    link.setAttribute('download', 'inventory.csv');
    link.style.display = 'none';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  }
  // Datatable initialization
  window.onload = function() {
    const table = $('#viewInventoryTable').DataTable({
      dom: "<'row'<'col-sm-6 d-flex align-items-center'f><'col-sm-6 d-flex justify-content-end'B>>" +
        "<'row'<'col-sm-12'tr>>" + "<'row'<'col-sm-5'i><'col-sm-7'p>>",
      buttons: [{
        extend: 'collection',
        text: ' < i class = "mdi mdi-menu" > < /i> Actions',
        className: 'btn btn-sm btn-secondary',
        buttons: [{
          text: 'Delete Selected',
          action: function() {
            performBulkAction('delete');
          }
        }, {
          text: 'Assign to Vendor',
          action: function() {
            performBulkAction('assign');
          }
        }, {
          text: 'Dispatch Inventory',
          action: function() {
            performBulkAction('dispatch');
          }
        }]
      }, {
        extend: 'excel',
        text: ' < i class = "mdi mdi-file-excel" > < /i>',
        className: 'btn btn-sm btn-success',
        titleAttr: 'Export to Excel'
      }, {
        extend: 'print',
        text: ' < i class = "mdi mdi-printer" > < /i>',
        className: 'btn btn-sm btn-info',
        titleAttr: 'Print Table'
      }],
      paging: true,
      pageLength: {
        {
          $pageLength ?? 10
        }
      },
      searching: true,
      responsive: true,
      language: {
        search: '',
        searchPlaceholder: 'Search...'
      },
      columnDefs: [{
        orderable: false,
        targets: [0, -1] // Targets the first column for select-checkbox
      }],
      ordering: true,
      select: {
        style: 'multi',
        selector: 'td:first-child'
      },
    });
    $('#selectAll').on('click', function() {
      const isChecked = $(this).is(':checked');
      table.rows().nodes().to$().find('input[type="checkbox"]').prop('checked', isChecked);
      if (isChecked) {
        table.rows().select();
      } else {
        table.rows().deselect();
      }
    });
    // Track individual row selection to update "Select All" state
    $('#viewInventoryTable tbody').on('click', 'input[type="checkbox"]', function() {
      const allChecked = $('#viewInventoryTable tbody input[type="checkbox"]:checked').length === table.rows()
        .count();
      $('#selectAll').prop('checked', allChecked);
    });
    // Bulk action function
    function performBulkAction(action) {
      const selectedIds = [];
      table.rows({
        selected: true
      }).data().each(function(rowData) {
        selectedIds.push(rowData[0]); // Assuming the ID is in the first column
      });
      if (selectedIds.length === 0) {
        alert('Please select at least one row.');
        return;
      }
      // Example: Show selected IDs in the console
      console.log(`Performing "${action}" on IDs: `, selectedIds);
      // Modal viewInventoryTable
      $(document).on('click', '.eye-button', function() {});
      // Perform the actual action here
      // Example:
      // $.ajax({
      //   url: `/bulk/${action}`,
      //   method: 'POST',
      //   data: { ids: selectedIds },
      //   success: function(response) {
      //     alert(`${action} successful!`);
      //     table.ajax.reload();
      //   },
      //   error: function() {
      //     alert(`Failed to perform ${action}.`);
      //   }
      // });
    }
  }
</script> @endsection
