@extends("layouts.main")

@section("content")
  <div class="container">
    <div class="row">
      <!-- Inventory Summary -->
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
            <p>Total Value: <span>{{ $totalBatteryValue }}</span></p>
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
            <p>Total Quantity: <span>{{ $totalLuminary }}</span> </p>
            <p>Total Value: <span>{{ $totalLuminaryValue }}</span></p>
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
              <img width="20" height="20"
                src="https://img.icons8.com/external-others-pike-picture/50/external-Steel-Frame-house-others-pike-picture-2.png"
                alt="external-Steel-Frame-house-others-pike-picture-2" />
            </div>
          </div>
          <div class="card-body">
            <p>Total Quantity: <span>{{ $totalStructure }}</span></p>
            <p>Total Value: <span>{{ $totalStructureValue }}</span></p>
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
            <p>Total Quantity: <span>{{ $totalModule }}</span> </p>
            <p>Total Value: <span>{{ $totalModuleValue }}</span></p>
            <p>Dispatched Quantity</p>
            <p>Dispatched Value</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Inventory Table -->
    <div class="mt-4">
      <button class="btn btn-primary mb-2" onclick="exportToCSV()">Export to CSV</button>
      <button class="btn btn-secondary mb-2" onclick="printTable()">Print Table</button>

      <table id="inventoryTable" class="table-striped table-bordered table">
        <thead>
          <tr>
            <th>Item Code</th>
            <th>Item Name</th>
            <th>Manufacturer</th>
            <th>Model</th>
            <th>Unit</th>
            <th>Quantity</th>
            <th>Rate</th>
            <th>Total Value</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($inventory as $item)
            <tr>
              <td>{{ $item->item_code }}</td>
              <td>{{ $item->item_name }}</td>
              <td>{{ $item->manufacturer }}</td>
              <td>{{ $item->model }}</td>
              <td>{{ $item->unit }}</td>
              <td>{{ $item->quantity }}</td>
              <td>{{ $item->rate }}</td>
              <td>{{ number_format($item->quantity * $item->rate, 2) }}</td>
              <td>
                <a href="#modal{{ $item->id }}" data-toggle="modal" class="btn btn-sm btn-info">Details</a>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

  <!-- Inventory Details Modal -->
  @foreach ($inventory as $item)
    <div class="modal fade" id="modal{{ $item->id }}" tabindex="-1" role="dialog">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Item Details: {{ $item->item_name }}</h5>
            <button type="button" class="close" data-dismiss="modal">&times;</button>
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

@section("scripts")
  <!-- Include jQuery and DataTables -->
  {{-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script> --}}

  <script>
    $(document).ready(function() {
      $('#inventoryTable').DataTable({
        "pageLength": 50,
        "ordering": true,
        "searching": true,
        "lengthChange": false,
        "pagination": true
      });
    });

    // Export to CSV function
    function exportToCSV() {
      let csvContent = "data:text/csv;charset=utf-8,";
      let rows = [
        ['Item Code', 'Item Name', 'Manufacturer', 'Model', 'Unit', 'Quantity', 'Rate', 'Total Value']
      ];

      @foreach ($inventory as $item)
        rows.push([
          "{{ $item->item_code }}",
          "{{ $item->item_name }}",
          "{{ $item->manufacturer }}",
          "{{ $item->model }}",
          "{{ $item->unit }}",
          "{{ $item->quantity }}",
          "{{ $item->rate }}",
          "{{ number_format($item->quantity * $item->rate, 2) }}"
        ]);
      @endforeach

      rows.forEach(row => {
        csvContent += row.join(",") + "\n";
      });

      const encodedUri = encodeURI(csvContent);
      const link = document.createElement("a");
      link.setAttribute("href", encodedUri);
      link.setAttribute("download", "inventory.csv");
      document.body.appendChild(link);
      link.click();
    }

    // Print table function
    function printTable() {
      let printContents = document.querySelector("#inventoryTable").outerHTML;
      let newWindow = window.open("", "_blank");
      newWindow.document.write("<html><head><title>Print Inventory</title></head><body>");
      newWindow.document.write(printContents);
      newWindow.document.write("</body></html>");
      newWindow.document.close();
      newWindow.print();
    }
  </script>
@endsection
